<?php

/**
 * Dokan get coupon types
 *
 * @since 3.0.0
 *
 * @return array
 */
function dokan_get_coupon_types() {
    return apply_filters( 'dokan_get_coupon_types', [
        'percent'       => __( 'Percentage discount', 'dokan' ),
        'fixed_cart'    => __( 'Fixed cart discount', 'dokan' ),
        'fixed_product' => __( 'Fixed product discount', 'dokan' ),
    ] );
}

/**
 * Dokan get vendor product list for coupon
 *
 * @return [type] [description]
 */
function dokan_get_coupon_products_list() {
    global $wpdb;

    $user_id = dokan_get_current_user_id();

    $sql = "SELECT $wpdb->posts.* FROM $wpdb->posts
            WHERE $wpdb->posts.post_author IN ( $user_id )
                AND $wpdb->posts.post_type = 'product'
                AND ( ( $wpdb->posts.post_status = 'publish' OR $wpdb->posts.post_status = 'draft' OR $wpdb->posts.post_status = 'pending') )
            ORDER BY $wpdb->posts.post_date DESC";

    return $wpdb->get_results( $sql );
}
