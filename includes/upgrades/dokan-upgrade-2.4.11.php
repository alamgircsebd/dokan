<?php
/**
 * Upgrade capabilities for sellers
 *
 * @since 2.4.11
 *
 * @return void
 */
function dokan_upgrade_seller_cap_2410() {
	global $wp_roles;
	$wp_roles->add_cap( 'seller', 'edit_shop_orders' );
}

dokan_upgrade_seller_cap_2411();