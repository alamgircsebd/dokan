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
        $order            = wc_get_order( $refund->get_order_id() );
        $seller_id        = $refund->get_seller_id();
        $vendor_token     = get_user_meta( $seller_id, '_stripe_connect_access_key', true );
        $vendor_charge_id = $order->get_meta( "_dokan_stripe_charge_id_{$seller_id}" );

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

        // if vendor charge id is not found, meaning it's a not purcahsed with sitripe so return early
        if ( ! $vendor_charge_id ) {
            return true;
        }

        Helper::bootstrap_stripe();

        try {
            $stripe_refund = StripeRefund::create( [
                'charge'                 => $vendor_charge_id,
                'amount'                 => Helper::get_stripe_amount( $refund->get_refund_amount() ),
                'reason'                 => __( 'requested_by_customer', 'dokan' ),
                'refund_application_fee' => $refund_application_fee
            ], $vendor_token );

            if ( ! $stripe_refund->id ) {
                dokan_log( sprintf( __( 'Stripe refund ID is not found for Dokan Refund ID %s', 'dokan' ), $refund->get_id() ), 'error' );
            }

            $order->add_order_note( sprintf( __( 'Refund Processed Via Stripe ( Refund ID: %s )', 'dokan' ), $stripe_refund->id ) );

            $refund = $refund->approve();

            if ( is_wp_error( $refund ) ) {
                dokan_log( $refund->get_error_message(), 'error' );
            }

        } catch( Exception $e ) {
            dokan_log( $e->getMessage(), 'error' );
        }
    }
}
