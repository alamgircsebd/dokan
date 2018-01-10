<?php

/**
 * Dokan get all vendor stuffs
 *
 * @return array
 */
function dokan_get_all_vendor_stuffs( $args ) {

    $defaults = array(
        'number' => 10,
        'offset' => 0,
        'vendor_id' => get_current_user_id(),
        'orderby' => 'registered',
        'order' => 'desc'
    );

    $args = wp_parse_args( $args, $defaults );

    $args['role'] = 'vendor_stuff';
    $args['meta_query'] = array(
        array(
            'key'     => '_vendor_id',
            'value'   => $args['vendor_id'],
            'compare' => '='
        )
    );

    $user_search = new WP_User_Query( $args );
    $stuffs     = $user_search->get_results();
    return array( 'total_users' => $user_search->total_users, 'stuffs' => $stuffs );
}