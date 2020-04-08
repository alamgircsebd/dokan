<?php
/**
 * New Order Email ( plain text )
 *
 * An email sent to the vendor when a new order is created by customer.
 *
 * @class       VendorNewOrder
 * @version     2.6.8
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
echo "= " . $email_heading . " =\n\n";
echo sprintf( __( 'You have received an order from %s.', 'dokan-lite' ), $order->get_formatted_billing_full_name() ) . "\n\n";
echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo "\n" . esc_html( wc_strtoupper( esc_html__( 'Billing address', 'dokan-lite' ) ) ) . "\n\n";
echo preg_replace( '#<br\s*/?>#i', "\n", $order->get_formatted_billing_address() ) . "\n"; // WPCS: XSS ok.

if ( $order->get_billing_phone() ) {
	echo $order->get_billing_phone() . "\n"; // WPCS: XSS ok.
}

if ( ! wc_ship_to_billing_address_only() && $order->needs_shipping_address() ) {
	$shipping = $order->get_formatted_shipping_address();

	if ( $shipping ) {
		echo "\n" . esc_html( wc_strtoupper( esc_html__( 'Shipping address', 'dokan-lite' ) ) ) . "\n\n";
		echo preg_replace( '#<br\s*/?>#i', "\n", $shipping ) . "\n"; // WPCS: XSS ok.
	}
}

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";
echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );