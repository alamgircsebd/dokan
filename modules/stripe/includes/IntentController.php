<?php

namespace WeDevs\DokanPro\Modules\Stripe;

use WeDevs\DokanPro\Modules\Stripe\Helper;
use WeDevs\Dokan\Exceptions\DokanException;
use WeDevs\DokanPro\Modules\Stripe\DokanStripe;
use WeDevs\DokanPro\Modules\Stripe\Abstracts\StripePaymentGateway;

defined( 'ABSPATH' ) || exit;

class IntentController extends StripePaymentGateway {

    /**
     * Constructor method
     *
     * @since 3.0.3
     */
    public function __construct() {
        Helper::bootstrap_stripe();
        $this->hooks();
    }

    /**
     * Hooks
     *
     * @since 3.0.3
     *
     * @return void
     */
    public function hooks() {
        add_action( 'wc_ajax_dokan_stripe_verify_intent', [ $this, 'verify_intent' ] );
        add_action( 'dokan_stripe_payment_completed', [ $this, 'process_vendor_payment' ], 10, 2 );
    }

    /**
     * Loads the order from the current request.
     *
     * @since 3.0.3
     * @throws WC_Stripe_Exception An exception if there is no order ID or the order does not exist.
     *
     * @return WC_Order
     */
    protected function get_order_from_request() {
        if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['nonce'] ), 'dokan_stripe_confirm_pi' ) ) {
            throw new DokanException( 'missing-nonce', __( 'CSRF verification failed.', 'dokan' ) );
        }

        $order_id = null;

        if ( isset( $_GET['order'] ) && absint( $_GET['order'] ) ) {
            $order_id = absint( $_GET['order'] );
        }

        $order = wc_get_order( $order_id );

        if ( ! $order ) {
            throw new DokanException( 'missing-order', __( 'Missing order ID for payment confirmation', 'dokan' ) );
        }

        return $order;
    }

    /**
     * Handles successful PaymentIntent authentications.
     *
     * @since 3.0.3
     *
     * @return void
     */
    public function verify_intent() {
        global $woocommerce;

        try {
            $order = $this->get_order_from_request();
        } catch ( DokanException $e ) {
            $message = sprintf( __( 'Payment verification error: %s', 'dokan' ), $e->get_message() );

            wc_add_notice( esc_html( $message ), 'error' );

            $redirect_url = $woocommerce->cart->is_empty()
                ? get_permalink( wc_get_page_id( 'shop' ) )
                : wc_get_checkout_url();

            $this->handle_error( $e, $redirect_url );
        }

        try {
            $this->verify_intent_after_checkout( $order );

            if ( ! isset( $_GET['is_ajax'] ) ) {
                $redirect_url = isset( $_GET['redirect_to'] ) // wpcs: csrf ok.
                    ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) // wpcs: csrf ok.
                    : $gateway->get_return_url( $order );

                wp_safe_redirect( $redirect_url );
            }
            exit;
        } catch ( DokanException $e ) {
            $this->handle_error( $e, $gateway->get_return_url( $order ) );
        }
    }

    /**
     * Handles exceptions during intent verification.
     *
     * @since 3.0.3
     *
     * @param DokanException $e
     * @param string $redirect_url An URL to use if a redirect is needed.
     */
    protected function handle_error( $e, $redirect_url ) {
        // Log the exception before redirecting.
        $message = sprintf( 'PaymentIntent verification exception: %s', $e->get_message() );

        // `is_ajax` is only used for PI error reporting, a response is not expected.
        if ( isset( $_GET['is_ajax'] ) ) {
            exit;
        }

        wp_safe_redirect( $redirect_url );
        exit;
    }

    /**
     * Executed between the "Checkout" and "Thank you" pages, this
     * method updates orders based on the status of associated PaymentIntents.
     *
     * @since 3.0.3
     *
     * @param WC_Order $order The order which is in a transitional state
     *
     * @return void
     */
    public function verify_intent_after_checkout( $order ) {
        $intent = $this->get_intent_from_order( $order );

        // No intent, redirect to the order received page for further actions.
        if ( ! $intent ) {
            return;
        }

        // A webhook might have modified or locked the order while the intent was retreived. This ensures we are reading the right status.
        clean_post_cache( $order->get_id() );
        $order = wc_get_order( $order->get_id() );

        if ( ! $order->has_status( [ 'pending', 'failed' ] ) ) {
            // If payment has already been completed, this function is redundant.
            return;
        }

        if ( $this->lock_order_payment( $order, $intent ) ) {
            return;
        }

        if ( 'succeeded' === $intent->status ) {
            WC()->cart->empty_cart();
            $order->payment_complete();
            do_action( 'dokan_stripe_payment_completed', $order, $intent );
        } else if ( 'requires_capture' === $intent->status ) {
            // Proceed with the payment completion.
            $this->handle_intent_verification_success( $order, $intent );
        } else if ( 'requires_payment_method' === $intent->status ) {
            // `requires_payment_method` means that SCA got denied for the current payment method.
            $this->handle_intent_verification_failure( $order, $intent );
        }

        $this->unlock_order_payment( $order );
    }

    /**
     * Called after an intent verification succeeds, this allows
     * specific APNs or children of this class to modify its behavior.
     *
     * @since 3.0.3
     *
     * @param WC_Order $order The order whose verification succeeded.
     * @param stdClass $intent The Payment Intent object.
     */
    protected function handle_intent_verification_success( $order, $intent ) {
        $this->process_response( end( $intent->charges->data ), $order );
    }

    /**
     * Called after an intent verification fails, this allows
     * specific APNs or children of this class to modify its behavior.
     *
     * @param WC_Order $order The order whose verification failed.
     * @param stdClass $intent The Payment Intent object.
     */
    protected function handle_intent_verification_failure( $order, $intent ) {
        $this->failed_sca_auth( $order, $intent );
    }

    /**
     * Checks if the payment intent associated with an order failed and records the event.
     *
     * @since 3.0.3
     * @param \WC_Order $order  The order which should be checked.
     * @param object   $intent The intent, associated with the order.
     *
     * @return void
     */
    public function failed_sca_auth( $order, $intent ) {
        // If the order has already failed, do not repeat the same message.
        if ( $order->has_status( 'failed' ) ) {
            return;
        }

        // Load the right message and update the status.
        $status_message = isset( $intent->last_payment_error )
            /* translators: 1) The error message that was received from Stripe. */
            ? sprintf( __( 'Stripe SCA authentication failed. Reason: %s', 'dokan' ), $intent->last_payment_error->message )
            : __( 'Stripe SCA authentication failed.', 'dokan' );
        $order->update_status( 'failed', $status_message );
    }

    /**
     * Process vendor payment
     *
     * @since 3.0.3
     *
     * @param \WC_Order $order
     *
     * @return void
     */
    public function process_vendor_payment( $order, $intent ) {
        if ( Helper::is_subscription_order( $order ) ) {
            $is_recurring = false;
            do_action( 'dokan_process_subscription_order', $order, $intent, $is_recurring );
            return;
        }

        $all_withdraws = [];
        $currency      = $order->get_currency();
        $charge_id     = $this->get_charge_id_from_order( $order );
        $all_orders    = $this->get_all_orders_to_be_processed( $order );

        if ( ! $charge_id ) {
            throw new DokanException( 'dokan_charge_id_not_found', __( 'No charge id is found to process the order!', 'dokan' ) );
        }

        if ( ! $all_orders ) {
            throw new DokanException( 'dokan_no_order_found', __( 'No orders found to be processed!', 'dokan' ) );
        }

        foreach ( $all_orders as $tmp_order ) {
            $tmp_order_id        = $tmp_order->get_id();
            $vendor_id           = dokan_get_seller_id_by_order( $tmp_order_id );
            $vendor_raw_earning  = dokan()->commission->get_earning_by_order( $tmp_order, 'seller' );
            $vendor_earning      = Helper::get_stripe_amount( $vendor_raw_earning );
            $connected_vendor_id = get_user_meta( $vendor_id, 'dokan_connected_vendor_id', true );

            if ( ! $connected_vendor_id ) {
                $tmp_order->add_order_note( sprintf( __( 'Vendor\'s payment will be transferred to admin account since the vendor had not connected to Stripe.', 'dokan' ) ) );
                continue;
            }

            DokanStripe::transfer()->amount( $vendor_earning )->from( $charge_id )->to( $connected_vendor_id );

            if ( $order->get_id() !== $tmp_order_id ) {
                $tmp_order->update_meta_data( 'paid_with_dokan_3ds', true );
                $tmp_order->add_order_note(
                    sprintf(
                        __( 'Order %s payment is completed via %s with 3d secure on (Charge ID: %s)', 'dokan' ),
                        $tmp_order->get_order_number(),
                        $this->title,
                        $charge_id
                    )
                );
            }

            $withdraw_data = [
                'user_id'  => $vendor_id,
                'amount'   => $vendor_raw_earning,
                'order_id' => $tmp_order_id,
            ];

            $all_withdraws[] = $withdraw_data;
        }

        $this->insert_into_vendor_balance( $all_withdraws );
        $this->process_seller_withdraws( $all_withdraws );
        $order->add_order_note(
            sprintf(
                __( 'Order %s payment is completed via %s 3d secure. (Charge ID: %s)', 'dokan' ),
                $order->get_order_number(),
                $this->title,
                $charge_id
            )
        );
    }
}
