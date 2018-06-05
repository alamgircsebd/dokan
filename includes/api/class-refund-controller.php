<?php

/**
* Dokan Refund API Controller
*
* @since 2.8.0
*
* @package dokan
*/
class Dokan_REST_Refund_Controller extends Dokan_REST_Controller {

    /**
     * Endpoint namespace.
     *
     * @var string
     */
    protected $namespace = 'dokan/v1';

    /**
     * Route base.
     *
     * @var string
     */
    protected $base = 'refund';

    /**
     * Register all routes releated with stores
     *
     * @return void
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->base, array(
            'args' => array(
                'id' => array(
                    'description' => __( 'Unique identifier for the object.', 'dokan' ),
                    'type'        => 'integer',
                ),
            ),
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_refunds' ),
                'args'                => array_merge( $this->get_collection_params(),  array(
                    'status' => array(
                        'type'        => 'string',
                        'description' => __( 'Refund status', 'dokan' ),
                        'required'    => false,
                    ),
                ) ),
                'permission_callback' => array( $this, 'refund_permissions_check' ),
            ),
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'create_refund' ),
                'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
                'permission_callback' => array( $this, 'create_refund_permissions_check' ),
            ),
        ) );

        register_rest_route( $this->namespace, '/' . $this->base . '/(?P<id>[\d]+)/', array(
            'args' => array(
                'id' => array(
                    'description' => __( 'Unique identifier for the object.', 'dokan' ),
                    'type'        => 'integer',
                ),
            ),
            array(
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => array( $this, 'change_refund_status' ),
                'args'                => array(
                    'status' => array(
                        'type'        => 'string',
                        'description' => __( 'Refund status', 'dokan' ),
                        'required'    => false,
                    )
                ),
                'permission_callback' => array( $this, 'refund_permissions_check' ),
            ),

            array(
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => array( $this, 'delete_refund' ),
                'permission_callback' => array( $this, 'refund_permissions_check' ),
            ),

        ) );

        register_rest_route( $this->namespace, '/' . $this->base . '/batch', array(
            array(
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => array( $this, 'batch_items' ),
                'permission_callback' => array( $this, 'refund_permissions_check' ),
                'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
            ),
        ) );
    }

    /**
     * Map withdraw status
     *
     * @since 2.8.0
     *
     * @return array
     */
    protected function get_status( $status ) {
        $statuses = array(
            0 => 'pending',
            1 => 'completed',
            2 => 'cancelled'
        );

        if ( is_string( $status ) ) {
            return array_search( $status, $statuses );
        } else{
            return isset( $statuses[$status] ) ? $statuses[$status] : '';
        }

        return $statuses;
    }

    /**
     * Get withdraws
     *
     * @since 2.8.0
     *
     * @return object
     */
    public function get_refunds( $request ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            return new WP_Error( 'access_denied', __( 'You do not have permission', 'dokan' ), array( 'status' => 404 ) );
        }

        $status = ! empty( $request['status'] ) ? $request['status'] : '';
        $refund = new Dokan_Pro_Admin_Refund();

        $limit = $request['per_page'];
        $offset = ( $request['page'] - 1 ) * $request['per_page'];

        $refund_count = dokan_get_refund_count();

        if ( ! empty( $status ) ) {
            if ( $status == 'pending' ) {
                $total_count = $refund_count['pending'];
            } elseif( $status == 'completed' ) {
                $total_count = $refund_count['completed'];
            } else {
                $total_count = $refund_count['cancelled'];
            }

            $refunds = $refund->get_refund_requests( $this->get_status( $status ), $limit, $offset );
        } else {
            global $wpdb;
            $sql    = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}dokan_refund LIMIT %d, %d", $offset, $limit );
            $refunds = $wpdb->get_results( $sql );
            $total_count = array_sum( $refund_count );
        }

        $data = array();
        foreach ( $refunds as $key => $value ) {
            $resp   = $this->prepare_response_for_object( $value, $request );
            $data[] = $this->prepare_response_for_collection( $resp );
        }

        $response       = rest_ensure_response( $data );
        $refund_count   = dokan_get_refund_count();

        if (  current_user_can( 'manage_options' ) ) {
            $response->header( 'X-Status-Pending', $refund_count['pending'] );
            $response->header( 'X-Status-Completed', $refund_count['completed'] );
            $response->header( 'X-Status-Cancelled', $refund_count['cancelled'] );
        }

        $response = $this->format_collection_response( $response, $request, $total_count );
        return $response;
    }

    /**
     * Cancel withdraw status
     *
     * @since 2.8.0
     *
     * @return void
     */
    public function change_refund_status( $request ) {
        global $wpdb;

        $store_id = dokan_get_current_user_id();

        if ( empty( $request['id'] ) ) {
            return new WP_Error( 'no_id', __( 'Invalid Refund ID', 'dokan' ), array( 'status' => 404 ) );
        }

        if ( empty( $request['order_id'] ) ) {
            return new WP_Error( 'no_order_id', __( 'Invalid Order ID', 'dokan' ), array( 'status' => 404 ) );
        }

        $status = ! empty( $request['status'] ) ? $request['status'] : 'cancelled';

        if ( ! current_user_can( 'manage_options' ) ) {
            return new WP_Error( 'cancel_request', __( 'Vendor can only create refund request', 'dokan' ), array( 'status' => 400 ) );
        }

        $sql    = "SELECT * FROM `{$wpdb->prefix}dokan_refund` WHERE `id`={$request['id']}";
        $result = $wpdb->get_row( $sql );

        if ( $result->status != '0' ) {
            return new WP_Error( 'not_cancel_request', __( 'This refund is not pending. Only pending request status can be changed', 'dokan' ), array( 'status' => 400 ) );
        }

        $refund = new Dokan_Pro_Admin_Refund();

        $status_code = $this->get_status( $status );

        $refund->update_status( $request['id'], $request['order_id'], $status_code );
        $response = $wpdb->get_row( $sql );

        return rest_ensure_response( $this->prepare_response_for_object( $response, $request ) );
    }

    /**
     * Delete a refund
     *
     * @since 2.8.0
     *
     * @return void
     */
    public function delete_refund( $request ) {
        global $wpdb;

        $refund_id = !empty( $request['id'] ) ? intval( $request['id'] ) : 0;

        if ( !$refund_id ) {
            return new WP_Error( 'no_id', __( 'Invalid Refund ID', 'dokan' ), array( 'status' => 404 ) );
        }

        $sql    = "SELECT * FROM `{$wpdb->prefix}dokan_refund` WHERE `id`={$refund_id}";
        $result = $wpdb->get_row( $sql );

        if ( empty( $result->id ) ) {
            return new WP_Error( 'no_refund', __( 'No refund found for deleting', 'dokan' ), array( 'status' => 404 ) );
        }

        $refund = new Dokan_Pro_Admin_Refund();

        $refund->delete_refund( $request['id'] );

        return rest_ensure_response( $this->prepare_response_for_object( $result, $request ) );
    }

    /**
     * Make a withdraw request
     *
     * @since 2.8.0
     *
     * @return void
     */
    public function create_refund( $request ) {
        global $wpdb;

        $store_id                           = dokan_get_current_user_id();
        $request['seller_id']               = dokan_get_seller_id_by_order($request['order_id']);
        $request['refund_reason']           = $request['reason'] ? $request['reason'] : '';
        $request['line_item_qtys']          = $request['line_item_qtys'] ? $request['line_item_qtys'] : '';
        $request['line_item_totals']        = $request['line_item_totals'] ? $request['line_item_totals'] : '';
        $request['line_item_tax_totals']    = $request['line_item_tax_totals'] ? $request['line_item_tax_totals'] : '';
        $request['restock_refunded_items']  = $request['restock_refunded_items'] ? 'true' : 'false';
        $request['status']                  = 0;
        $request['api_refund']              = $request['method'] ? $request['method'] : 'false';

        // Validate that the refund can occur
        $amount     = wc_format_decimal( sanitize_text_field( $request['refund_amount'] ), wc_get_price_decimals() );
        $order      = wc_get_order( $request['order_id'] );
        $max_refund = wc_format_decimal( $order->get_total() - $order->get_total_refunded(), wc_get_price_decimals() );

        $refund = new Dokan_Pro_Admin_Refund;

        if ( $store_id != $request['seller_id'] && ! $this->refund_permissions_check() ) {
            return new WP_Error( 'cheating', __( 'Cheating uh!', 'dokan' ), array( 'status' => 404 ) );
        }

        if ( empty( $store_id ) ) {
            return new WP_Error( 'no_store_found', __( 'No vendor found', 'dokan' ), array( 'status' => 404 ) );
        }

        if ( empty( $request['order_id'] ) ) {
            return new WP_Error( 'no_order_found', __( 'No Order found', 'dokan' ), array( 'status' => 404 ) );
        }

        if ( empty( $request['refund_amount'] ) ) {
            return new WP_Error( 'no_amount_found', __( 'No Amount found', 'dokan' ), array( 'status' => 404 ) );
        }

        if ( ! $amount || $max_refund < $amount || 0 > $amount ) {

            return new WP_Error( 'invalid_amount', __( 'Invalid refund amount', 'dokan' ), array( 'status' => 404 ) );

        }

        if ( $refund->has_pending_refund_request( $request['order_id'] ) ) {
            return new WP_Error( 'duplicate', __( 'You have already a processing refund request for this order.', 'dokan' ), array( 'status' => 404 ) );
        }

        $update = $refund->insert_refund( $request );

        $data_info['id']      = $wpdb->insert_id;
        $data_info['user']    = $this->get_user_data( $request['seller_id'] );
        $data_info['created'] = mysql_to_rfc3339( date( 'Y-m-d h:i:s' ) );

        return rest_ensure_response( $data_info );
    }

    /**
     * Approve, Pending and cancel bulk action
     *
     * JSON data format for sending to API
     *     {
     *         "approved" : [
     *             "1", "9", "7"
     *         ],
     *         "pending" : [
     *             "2"
     *         ],
     *         "cancelled" : [
     *             "5"
     *         ]
     *     }
     *
     * @since 2.8.0
     *
     * @return void
     */
    public function batch_items( $request ) {
        global $wpdb;

        $params = $request->get_params();

        if ( empty( $params ) ) {
            return new WP_Error( 'no_item_found', __( 'No items found for bulk updating', 'dokan' ), array( 'status' => 404 ) );
        }

        if ( ! $this->refund_permissions_check() ) {
            return new WP_Error( 'cheating', __( 'Cheating uh!', 'dokan' ), array( 'status' => 404 ) );
        }

        $allowed_status = array( 'approved', 'cancelled', 'pending' );

        foreach ( $params as $status => $value ) {
            if ( in_array( $status, $allowed_status ) ) {
                foreach ( $value as $refund_id ) {
                    $status_code = $this->get_status( $status );

                    $wpdb->query( $wpdb->prepare(
                        "UPDATE {$wpdb->prefix}dokan_refund
                        SET status = %d WHERE id = %d",
                        $status_code, $refund_id
                    ) );
                }
            }
        }

        return true;
    }

    /**
     * Prepare data for response
     *
     * @since 2.8.0
     *
     * @return data
     */
    public function prepare_response_for_object( $object, $request ) {

        $data = array(
            'id'           => $object->id,
            'order_id'     => $object->order_id,
            'vendor'       => $this->get_user_data( $object->seller_id ),
            'amount'       => floatval( $object->refund_amount ),
            'reason'       => $object->refund_reason,
            'item_qtys'    => $object->item_qtys,
            'item_totals'  => $object->item_totals,
            'tax_totals'   => $object->item_tax_totals,
            'restock_items'=> $object->restock_items,
            'created'      => mysql_to_rfc3339( $object->date ),
            'status'       => $this->get_status( (int) $object->status ),
            'method'       => $object->method == false ? 'cash' : $object->method,
        );

        $response      = rest_ensure_response( $data );
        $response->add_links( $this->prepare_links( $object, $request ) );

        return apply_filters( "dokan_rest_prepare_refund_object", $response, $object, $request );
    }

    /**
     * Prepare links for the request.
     *
     * @param WC_Data         $object  Object data.
     * @param WP_REST_Request $request Request object.
     *
     * @return array                   Links for the given post.
     */
    protected function prepare_links( $object, $request ) {
        $links = array(
            'self' => array(
                'href' => rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->base, $object->id ) ),
            ),
            'collection' => array(
                'href' => rest_url( sprintf( '/%s/%s', $this->namespace, $this->base ) ),
            ),
        );

        return $links;
    }

    /**
     * Get user data
     *
     * @since 2.8.0
     *
     * @return return object
     */
    public function get_user_data( $user_id ) {
        $vendor = dokan()->vendor->get( $user_id );

        return array(
            'id'         => $vendor->get_id(),
            'store_name' => $vendor->get_shop_name(),
            'email'      => $vendor->get_email(),
            'first_name' => $vendor->get_first_name(),
            'last_name'  => $vendor->get_last_name()
        );
    }

    /**
     * Check permission for getting refund
     *
     * @since 2.8.0
     *
     * @return void
     */
    public function create_refund_permissions_check() {
        return current_user_can( 'dokandar' );
    }

    /**
     * Check permission for getting refund
     *
     * @since 2.8.0
     *
     * @return void
     */
    public function refund_permissions_check() {
        return current_user_can( 'manage_options' );
    }

    /**
     * Get the Coupon's schema, conforming to JSON Schema.
     *
     * @return array
     */
    public function get_item_schema() {
        $schema = array(
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => 'Refund',
            'type'       => 'object',
            'properties' => array(
                'id' => array(
                    'description' => __( 'Unique identifier for the object.', 'dokan' ),
                    'type'        => 'integer',
                    'context'     => array( 'view' ),
                    'readonly'    => true,
                ),
                'order' => array(
                    'description' => __( 'Order ID', 'dokan' ),
                    'type'        => 'integer',
                    'context'     => array( 'view' ),
                ),
                'vendor' => array(
                    'description' => __( 'Vendor ID', 'dokan' ),
                    'type'        => 'integer',
                    'context'     => array( 'view' ),
                ),
                'amount' => array(
                    'description' => __( 'The amount requested for refund. Should always be numeric', 'dokan' ),
                    'type'        => 'string',
                    'context'     => array( 'view' ),
                ),
                'reason' => array(
                    'description' => __( "Refund Reason", 'dokan' ),
                    'type'        => 'string',
                    'context'     => array( 'view' ),
                ),
                'item_qty' => array(
                    'description' => __( "Item Quantity", 'dokan' ),
                    'type'        => 'string',
                    'context'     => array( 'view' ),
                ),
                'item_total' => array(
                    'description' => __( "Item Total", 'dokan' ),
                    'type'        => 'string',
                    'context'     => array( 'view' ),
                ),
                'tax_total' => array(
                    'description' => __( "Tax Total", 'dokan' ),
                    'type'        => 'string',
                    'context'     => array( 'view' ),
                ),
                'restock' => array(
                    'description' => __( "Restock Items", 'dokan' ),
                    'type'        => 'string',
                    'context'     => array( 'view' ),
                ),
                'created_data' => array(
                    'description' => __( "The date the Refund request has beed created in the site's timezone.", 'dokan' ),
                    'type'        => 'date-time',
                    'context'     => array( 'view' ),
                ),
                'status' => array(
                    'description' => __( "Refund status", 'dokan' ),
                    'type'        => 'integer',
                    'context'     => array( 'view' ),
                ),
                'method' => array(
                    'description' => __( "Refund Method", 'dokan' ),
                    'type'        => 'string',
                    'context'     => array( 'view' ),
                ),
            ),
        );

        return $this->add_additional_fields_schema( $schema );
    }

}
