<?php

/**
 * Get Seller status counts, used in admin area
 *
 * @since 2.6.6
 *
 * @global WPDB $wpdb
 * @return array
 */
function dokan_wholesale_get_customer_status_count() {
    $args = [
        'role__in'   => [ 'seller', 'customer' ],
        'fields'     => 'ID',
        'meta_query' => [
            [
                'key'     => '_is_dokan_wholesale_customer',
                'compare' => 'EXISTS'
            ]
        ],
    ];

    $total_users = new WP_User_Query( $args );

    $args['meta_query'][] = [
        'key'     => '_dokan_wholesale_customer_status',
        'value'   => 'active',
        'compare' => '='
    ];

    $active_users   = new WP_User_Query( $args );
    $total_count    = $total_users->get_total();
    $inactive_count = $total_count - $active_users->get_total();

    $counts =  array(
        'total'    => $total_count,
        'active'   => $active_users->get_total(),
        'deactive' => $inactive_count,
    );

    return $counts;
}
