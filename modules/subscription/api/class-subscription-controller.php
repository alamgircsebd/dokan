<?php

use DokanPro\Modules\Subscription\Helper;
use WeDevs\Dokan\Abstracts\DokanRESTController;

/**
* Subscription API controller
*
* @since 2.8.0
*
* @package dokan
*/
class Dokan_REST_Subscription_Controller extends DokanRESTController {

    /**
     * Endpoint namespace.
     *
     * @var string
     */
    protected $namespace = 'dokan/v1';

    /**
     * Route name
     *
     * @var string
     */
    protected $base = 'subscription';



    /**
     * Register all routes related with coupons
     *
     * @return void
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_subscription' ],
                'permission_callback' => [ $this, 'check_permission' ],
                'args'                => $this->get_collection_params(),
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->base . '/(?P<id>[\d]+)/', [
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'update_subscription' ],
                'permission_callback' => [ $this, 'check_permission' ],
                'args'                => $this->get_collection_params(),
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->base . '/batch', [
            [
                'methods'  => WP_REST_Server::EDITABLE,
                'callback' => [ $this, 'batch_update' ],
                'permission_callback' => [ $this, 'check_permission' ],
            ],
        ] );
    }

    /**
     * Check permission
     *
     * @since 2.9.3
     *
     * @return boolean
     */
    public function check_permission() {
        return current_user_can( 'manage_options' );
    }

    /**
     * Get all subscription
     *
     * @since 2.9.3
     *
     * @return object
     */
    public function get_subscription( $request ) {
        $params = $request->get_params();

        $args = apply_filters( 'dokan_get_subscription_args', [
            'role' => 'seller',
            'meta_query' => [
                [
                    'key'   => 'can_post_product',
                    'value' => '1'
                ],
                [
                    'key'   => 'dokan_enable_selling',
                    'value' => 'yes'
                ],
            ]
        ] );

        if ( ! empty( $params['per_page'] ) ) {
            $args['number'] = $params['per_page'];
        }

        if ( ! empty( $params['offset'] ) ) {
            $args['offset'] = $params['offset'];
        }

        if ( ! empty( $params['orderby'] ) ) {
            $args['orderby'] = $params['orderby'];
        }

        if ( ! empty( $params['order'] ) ) {
            $args['order'] = $params['order'];
        }

        if ( ! empty( $params['search'] ) ) {
            $args['search'] = $params['search'];
        }

        if ( ! empty( $params['paged'] ) ) {
            $args['paged'] = $params['paged'];
        }

        $user_query  = new WP_User_Query( $args );
        $users       = $user_query->get_results();
        $total_users = $user_query->get_total();

        if ( ! $users ) {
            return new WP_Error( 'no_subscription', __( 'No subscription found.', 'dokan' ), [ 'status' => 200 ] );
        }

        $data = [];

        foreach ( $users as $user ) {
            $data[] = $this->prepare_item_for_response( $user, $request );
        }

        $response = rest_ensure_response( $data );
        $response = $this->format_collection_response( $response, $request, $total_users );

        return $response;
    }

    /**
     * Update subscription
     *
     * @param \WP_REST_Request $request
     *
     * @return \WP_REST_Response
     */
    public function update_subscription( $request ) {
        $user_id            = (int) $request->get_param( 'id' );
        $action             = $request->get_param( 'action' );
        $status             = get_terms( 'shop_order_status' );
        $cancel_immediately = dokan_validate_boolean( $request->get_param( 'immediately' ) );

        if ( is_wp_error( $status ) ) {
            register_taxonomy( 'shop_order_status', array( 'shop_order' ), array( 'rewrite' => false ) );
        }

        $order_id = get_user_meta( $user_id, 'product_order_id', true );
        $vendor   = dokan()->vendor->get( $user_id )->subscription;
        $user     = new \WP_User( $user_id );

        if ( ! $order_id || ! $vendor ) {
            return new WP_Error( 'no_subscription', __( 'No subscription is found to be updated.', 'dokan' ), [ 'status' => 200 ] );
        }

        if ( 'activate' === $action ) {
            if ( $vendor->has_recurring_pack() && $vendor->has_active_cancelled_subscrption() ) {
                Helper::log( 'Subscription re-activattion check: Admin has re-activate Subscription of User #' . $user_id . ' on order #' . $order_id );
                do_action( 'dps_activate_recurring_subscription', $order_id, $user_id );
            }

            if ( ! $vendor->has_recurring_pack() ) {
                Helper::log( 'Subscription re-activattion check: Admin has re-activate Subscription of User #' . $user_id . ' on order #' . $order_id );
                do_action( 'dps_activate_non_recurring_subscription', $order_id, $user_id );
            }
        }

        if ( 'cancel' === $action ) {
            if ( $vendor->has_recurring_pack() ) {
                Helper::log( 'Subscription cancellation check: Admin has canceled Subscription of User #' . $user_id . ' on order #' . $order_id );
                do_action( 'dps_cancel_recurring_subscription', $order_id, $user_id, $cancel_immediately );
            } elseif ( ! $vendor->has_recurring_pack() ) {
                Helper::log( 'Subscription cancellation check: Admin has canceled Subscription of User #' . $user_id . ' on order #' . $order_id );
                do_action( 'dps_cancel_non_recurring_subscription', $order_id, $user_id, $cancel_immediately );
            }
        }

        $response = $this->prepare_item_for_response( $user, $request );
        $response = rest_ensure_response( $response );

        return $response;
    }

    /**
     * Batch update subscription
     *
     * @param \WP_REST_Request $request
     *
     * @return \WP_REST_Response
     */
    public function batch_update( $request ) {
        $action   = ! empty( $request['action'] ) ? $request['action'] : '';
        $user_ids = ! empty( $request['user_ids'] ) ? $request['user_ids'] : '';

        if ( ! $user_ids ) {
            return new WP_Error( 'no_subscription', __( 'No subscription is found to be updated.', 'dokan' ), [ 'status' => 200 ] );
        }

        $status = get_terms( 'shop_order_status' );

        if ( is_wp_error( $status ) ) {
            register_taxonomy( 'shop_order_status', array( 'shop_order' ), array( 'rewrite' => false ) );
        }

        foreach ( $user_ids as $user_id ) {
            $order_id = get_user_meta( $user_id, 'product_order_id', true );

            if ( ! $order_id ) {
                return new WP_Error( 'no_subscription', __( 'No subscription is found to be updated.', 'dokan' ), [ 'status' => 200 ] );
            }

            $vendor = dokan()->vendor->get( $user_id )->subscription;

            if ( 'activate' === $action ) {
                if ( $vendor->has_recurring_pack() && $vendor->has_active_cancelled_subscrption() ) {
                    Helper::log( 'Subscription activation check: Admin has activated Subscription of User #' . $user_id . ' on order #' . $order_id );
                    do_action( 'dps_activate_recurring_subscription', $order_id, $user_id );
                }
            }

            if ( 'cancel' === $action ) {
                if ( $vendor->has_recurring_pack() && ! $vendor->has_active_cancelled_subscrption() ) {
                    $cancel_immediately = false;
                    Helper::log( 'Subscription cancellation check: Admin has canceled Subscription of User #' . $user_id . ' on order #' . $order_id );
                    do_action( 'dps_cancel_recurring_subscription', $order_id, $user_id, $cancel_immediately );
                } elseif ( ! $vendor->has_recurring_pack() ) {
                    Helper::log( 'Subscription cancellation check: Admin has canceled Subscription of User #' . $user_id . ' on order #' . $order_id );
                    do_action( 'dps_cancel_non_recurring_subscription', $order_id, $user_id );
                }
            }
        }

        $response = rest_ensure_response( $user_ids );

        return $response;
    }

    /**
     * Prepare a single sinle subscription output for response.
     *
     * @param Object $user
     * @param WP_REST_Request $request Request object.
     *
     * @return WP_REST_Response $response Response data.
     */
    public function prepare_item_for_response( $user, $request ) {
        $subscription = dokan()->vendor->get( $user->ID )->subscription;

        if ( ! $subscription ) {
            return new WP_Error( 'no_subscription', __( 'No subscription is found to be deleted.', 'dokan' ), [ 'status' => 200 ] );
        }

        $end_date = $subscription->get_pack_end_date();

        $data = [
            'id'                       => $user->ID,
            'user_link'                => get_edit_user_link( $user->ID ),
            'user_name'                => $user->data->user_nicename,
            'subscription_id'          => $subscription->get_id(),
            'subscription_title'       => $subscription->get_package_title(),
            'start_date'               => date_i18n( get_option( 'date_format' ), strtotime( $subscription->get_pack_start_date() ) ),
            'end_date'                 => 'unlimited' === $end_date ? __( 'Unlimited', 'dokan' ) : date_i18n( get_option( 'date_format' ), strtotime( $end_date ) ),
            'current_date'             => date_i18n( get_option( 'date_format' ) ),
            'status'                   => $subscription->has_subscription(),
            'is_recurring'             => $subscription->has_recurring_pack(),
            'has_active_cancelled_sub' => $subscription->has_active_cancelled_subscrption(),
        ];

        $context = ! empty( $request['context'] ) ? $request['context'] : 'view';
        $data    = $this->add_additional_fields_to_object( $data, $request );
        $data    = $this->filter_response_by_context( $data, $context );

        return apply_filters( 'dokan_rest_prepare_subscription', $data, $user, $request );
    }
}
