<?php

namespace WeDevs\DokanPro\Refund;

use WeDevs\DokanPro\Refund\Ajax;

class Hooks {

    /**
     * Hooks related to Dokan Pro Refund
     *
     * @since 3.0.0
     *
     * @return void
     */
    public function __construct() {
        add_action( 'wp_ajax_dokan_refund_request', [ Ajax::class, 'dokan_refund_request' ] );
        add_action( 'wp_ajax_woocommerce_refund_line_items', [ Ajax::class, 'intercept_wc_ajax_request' ], 1 );
        add_action( 'dokan_pro_refund_approved', [ self::class, 'after_refund_approved' ] );
    }

    /**
     * After refund approval hook
     *
     * @since 3.0.0
     *
     * @param \WeDevs\DokanPro\Refund\Refund $refund
     *
     * @return void
     */
    public static function after_refund_approved( $refund ) {
        $vendor       = dokan()->vendor->get( $refund->get_seller_id() );
        $vendor_email = $vendor->get_email();

        do_action( 'dokan_refund_processed_notification', $vendor_email, $refund->get_order_id(), 'approved', $refund->get_refund_amount(), $refund->get_refund_reason() );
    }
}
