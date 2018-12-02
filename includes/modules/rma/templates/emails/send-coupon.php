<?php
/**
 * New Auction Product Email.
 *
 * An email sent to the admin when a new Product is created by vendor.
 *
 * @class       Dokan_Auction_Email
 * @version     2.7.1
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p><?php _e( 'Hello,', 'dokan' ); ?></p>

<p><?php _e( 'A new Coupon', 'dokan' ); ?> </p>
<p><?php _e( 'Summary of the product:', 'dokan' ); ?></p>
<hr>

<p><?php _e( 'The product is currently in "publish" status. So, everyone can view the product.', 'dokan' ); ?></p>

<?php

do_action( 'woocommerce_email_footer', $email );
