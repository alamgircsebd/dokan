<?php
use DokanPro\Modules\Stripe\Helper;

/**
 * Dokan_Stripe_Connect class
 */
class Dokan_Stripe_Connect extends Dokan_Stripe_Gateway {

    /**
     * Consturctor method
     *
     * @since 2.9.13
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Process payment
     *
     * @since 2.9.13
     *
     * @param int $order_id
     *
     * @return array
     */
    public function process_payment( $order_id ) {
        global $woocommerce, $wpdb;

        $customer_id   = 0;
        $stripe_token  = isset( $_POST['stripe_token'] ) ? wc_clean( $_POST['stripe_token'] ) : '';
        $order         = wc_get_order( $order_id );
        $all_withdraws = [];

        if ( Helper::is_3d_secure_enabled() ) {
            if ( $this->is_subscription_order( $order ) ) {
                $this->process_subscription_payment( $order_id, $order, $customer_id, $stripe_token );

                return [
                    'result' => 'success',
                    'redirect' => $this->get_return_url( $order )
                ];

            } else {
                $this->process_seller_payment( $order_id, $order, $customer_id, $stripe_token );
            }

            if ( 'succeeded' !== $this->get_payment_intent_status() ) {
                throw new Exception( __( 'Payment intent is not successful. Please try agin with a different card.', 'dokan' ) );
            }

            return [
                'result'   => 'success',
                'redirect' => $this->get_return_url( $order )
            ];
        }

        try {
            if ( isset( $_POST['stripe_customer_id'] ) && $_POST['stripe_customer_id'] !== 'new' && is_user_logged_in() ) {

                $customer_ids = get_user_meta( get_current_user_id(), '_stripe_customer_id', false );

                if ( isset( $customer_ids[ $_POST['stripe_customer_id'] ]['customer_id'] ) ) {
                    $customer_id = $customer_ids[ $_POST['stripe_customer_id'] ]['customer_id'];
                } else {
                    throw new Exception( __( 'Invalid card.', 'dokan' ) );
                }
            } else if ( empty( $stripe_token ) ) {
                throw new Exception( __( 'Please make sure your card details have been entered correctly and that your browser supports JavaScript.', 'dokan' ) );
            }

            $order_total = dokan_get_prop( $order, 'order_total', 'get_total' );

            if ( $order_total * 100 < 50 ) {
                throw new Exception( __( 'Minimum order total is 0.50', 'dokan' ) );
            }

            if ( is_user_logged_in() && ! $customer_id && $stripe_token ) {

                $customer_id = $this->add_customer( $order, $stripe_token );

            } else if ( !is_user_logged_in() ) {

                if ( !empty( $woocommerce->session->stripe_guest_user_token ) ) {

                    $customer_id = $woocommerce->session->stripe_guest_user_token;

                } else {

                    $customer_id = $this->add_customer( $order, $stripe_token );

                    $woocommerce->session->set( 'stripe_guest_user_token', $customer_id );
                }
            }

            if ( $this->is_subscription_order( $order ) ) {
                $this->process_subscription_payment( $order_id, $order, $customer_id, $stripe_token );
            } else {
                $this->process_seller_payment( $order_id, $order, $customer_id, $stripe_token );
            }

        } catch( Exception $e ) {
            /* Add order note*/
            $order->add_order_note( sprintf( __( 'Stripe Payment Error: %s', 'dokan' ), $e->getMessage() ) );
            update_post_meta( $order_id, '_dwh_stripe_charge_error', $e->getMessage());

            wc_add_notice( __( 'Error: ', 'dokan' ) . $e->getMessage() );
            return;
        }

        return [
            'result'   => 'success',
            'redirect' => $this->get_return_url( $order )
        ];
    }
}
