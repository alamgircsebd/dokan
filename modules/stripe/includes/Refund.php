<?php

namespace WeDevs\DokanPro\Modules\Stripe;

use Exception;
use Stripe\Refund as StripeRefund;
use WeDevs\Dokan\Exceptions\DokanException;

defined( 'ABSPATH' ) || exit;

class Refund {

    /**
     * Constructor method
     *
     * @since 3.0.3
     *
     * @return void
     */
    public function __construct() {
        $this->hooks();
    }

    /**
     * Init all the hooks
     *
     * @since 3.0.3
     *
     * @return void
     */
    private function hooks() {
        add_action( 'dokan_refund_request_created', [ $this, 'process_refund' ] );
        add_action( 'dokan_refund_request_created', [ $this, 'process_3ds_refund' ] );
    }

    /**
     * Process refund request
     *
     * @param  int $refund_id
     * @param  array $data
     *
     * @return void
     */
    public function process_refund( $refund ) {
        // get code editor suggestion on refund object
        if ( ! $refund instanceof \WeDevs\DokanPro\Refund\Refund ) {
            return;
        }

        // check if refund is approvable
        if ( ! dokan_pro()->refund->is_approvable( $refund->get_order_id() ) ) {
            dokan_log( sprintf( 'Stripe Non3DS Refund: This refund is not allowed to approve, Refund ID: %1$s, Order ID: %2$s', $refund->get_id(), $refund->get_order_id() ) );
            return;
        }

        $order = wc_get_order( $refund->get_order_id() );

        // return if $order is not instance of WC_Order
        if ( ! $order instanceof \WC_Order ) {
            return;
        }

        // return if not paid with dokan stripe payment gateway
        if ( 'dokan-stripe-connect' !== $order->get_payment_method() ) {
            return;
        }

        // Get parent order id, because charge id is stored on parent order id
        $parent_order_id = $order->get_parent_id() ? $order->get_parent_id() : $order->get_id();

        // get intent id of the parent order
        $payment_intent_id = get_post_meta( $parent_order_id, 'dokan_stripe_intent_id', true );
        if ( ! empty( $payment_intent_id ) ) {
            // if payment is processed with stripe3ds, return from here
            return;
        }

        $seller_id        = $refund->get_seller_id();
        $vendor_token     = get_user_meta( $seller_id, '_stripe_connect_access_key', true );
        $vendor_charge_id = $order->get_meta( "_dokan_stripe_charge_id_{$seller_id}" );

        // if vendor charge id is not found, meaning charge was captured from admin stripe account
        if ( ! $vendor_charge_id ) {
            /**
             * Todo: if vendor charge id is not found, possible that charge was made on admin stripe account (eg: non connected vendors)
             * Implement automatic refund from admin account and negative charge vendors so that admin trace
             */
            $order->add_order_note(
                sprintf(
                /* translators: 1) Refund ID, 2) Order ID */
                    __( 'Dokan Stripe Refund Error: Automatic refund is not possible for this order. Reason: No vendor charge id found. Refund id: %1$s, Order ID: %2$s', 'dokan' ),
                    $refund->get_id(), $refund->get_order_id()
                )
            );
            return;
        }

        /**
         * If admin has earning from an order, only then refund application fee
         *
         * @since 3.0.0
         *
         * @see https://stripe.com/docs/api/refunds/create#create_refund-refund_application_fee
         *
         * @var string
         */
        $refund_application_fee = dokan()->commission->get_earning_by_order( $order, 'admin' ) ? true : false;

        Helper::bootstrap_stripe();

        try {
            $stripe_refund = StripeRefund::create(
                [
                    'charge'                 => $vendor_charge_id,
                    'amount'                 => Helper::get_stripe_amount( $refund->get_refund_amount() ),
                    'reason'                 => __( 'requested_by_customer', 'dokan' ),
                    'refund_application_fee' => $refund_application_fee,
                ],
                $vendor_token
            );

            /* translators: 1) Stripe refund id */
            $order->add_order_note( sprintf( __( 'Refund Processed Via Seller Stripe Account ( Refund ID: %s )', 'dokan' ), $stripe_refund->id ) );

            $args = [
                'stripe_non_3ds'    => true,
                'refund_id'         => $stripe_refund->id,
                'refunded_account'  => 'seller',
            ];

            $refund = $refund->approve( $args );

            if ( is_wp_error( $refund ) ) {
                dokan_log( $refund->get_error_message(), 'error' );
            }
        } catch ( Exception $e ) {
            $error_message = sprintf(
                /* translators: 1) Refund ID, 2) Order ID */
                __( 'Dokan Stripe Refund Error: Automatic refund was not successful for this order. Manual Refund Required. Reason: %1$s. Refund id: %2$s, Order ID: %3$s', 'dokan' ),
                $refund->get_id(), $refund->get_order_id()
            );
            dokan_log( $error_message, 'error' );
        }
    }

    /**
     * This method will refund payments collected with stripe 3ds
     *
     * @param \WeDevs\DokanPro\Refund\Refund $refund
     * @throws Exception
     * @since DOKAN_PRO_SINCE
     */
    public function process_3ds_refund( $refund ) {
        // get code editor suggestion on refund object
        if ( ! $refund instanceof \WeDevs\DokanPro\Refund\Refund ) {
            return;
        }

        $order = wc_get_order( $refund->get_order_id() );

        // return if $order is not instance of WC_Order
        if ( ! $order instanceof \WC_Order ) {
            return;
        }

        // return if not paid with dokan stripe payment gateway
        if ( 'dokan-stripe-connect' !== $order->get_payment_method() ) {
            return;
        }

        // check if user paid with stripe3ds ( check if _stripe_intent_id exists ) we can use paid_with_dokan_3ds meta exists,
        // but parent order doesn't have this meta. so using _stripe_intent_id is safer for both single or multivendor orders

        // Get parent order id, because charge id is stored on parent order id
        $parent_order_id = $order->get_parent_id() ? $order->get_parent_id() : $order->get_id();

        // get intent id of the parent order
        $payment_intent_id = get_post_meta( $parent_order_id, 'dokan_stripe_intent_id', true );
        if ( empty( $payment_intent_id ) ) {
            return;
        }

        // check if refund is approvable
        if ( ! dokan_pro()->refund->is_approvable( $refund->get_order_id() ) ) {
            dokan_log( sprintf( 'Stripe 3ds Refund: This refund is not allowed to approve, Refund ID: %1$s, Order ID: %2$s', $refund->get_id(), $refund->get_order_id() ) );
            return;
        }

        // now process refund.
        /**
         * Related Documentation:
         * https://stripe.com/docs/connect/charges-transfers
         * https://stripe.com/docs/api/refunds
         * https://stripe.com/docs/api/transfer_reversals/create?lang=php
         * We need to process refund from admin stripe account and then we need to reverse the transfer created
         * on vendor account.
         */

        Helper::bootstrap_stripe();

        // Step 1: check if transfer id exists
        $transfer_id = $order->get_meta( '_dokan_stripe_transfer_id' );

        if ( empty( $transfer_id ) ) {
            // we can't automatically reverse vendor balance, so manual refund and approval is required
            // add order note
            $order->add_order_note( __( 'Dokan Stripe 3ds Refund Error: Automatic refund is not possible for this order.', 'dokan' ) );
            return;
        }

        // step 2: process customer refund on stripe end
        try {
            $stripe_refund = StripeRefund::create(
                [
                    'payment_intent'    => $payment_intent_id,
                    'amount'            => Helper::get_stripe_amount( $refund->get_refund_amount() ),
                    'reason'            => 'requested_by_customer',
                ]
            );

            /* translators: 1) refund amount 2) refund currency 3) transaction id 4) refund message */
            $refund_message = sprintf( __( 'Refunded from admin stripe account: %1$s %2$s - Refund ID: %3$s - %4$s', 'dokan' ), $refund->get_refund_amount(), $order->get_currency(), $stripe_refund->id, $refund->get_refund_reason() );
            $order_note_id = $order->add_order_note( $refund_message );
        } catch ( Exception $e ) {
            $error_message = sprintf( 'Dokan Stripe 3ds Refund Error: Refund failed on Stripe End. Manual Refund Required. Refund ID: %1$s, Order ID: %2$s, Error Message: %3$s', $refund->get_id(), $refund->get_order_id(), $e->getMessage() );
            dokan_log( $error_message, 'error' );
            $order->add_order_note( $error_message );
            return;
        }

        $args = [
            'stripe_3ds'    => true,
            'transfer_id'   => $transfer_id,
        ];

        // get balance transaction for refund amount, we need to deduct gateway charge from vendor refund amount
        $gateway_fee_refunded           = abs( Helper::format_gateway_balance_fee( $stripe_refund->balance_transaction ) );
        $args['gateway_fee_refunded']   = ! empty( $gateway_fee_refunded ) ? $gateway_fee_refunded : 0;

        // Step 3: Try to approve the refund.
        $refund = $refund->approve( $args );

        if ( is_wp_error( $refund ) ) {
            dokan_log( $refund->get_error_message(), 'error' );
        }
    }
}
