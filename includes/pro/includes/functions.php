<?php

/**
 *  General Fnctions for Dokan Pro features
 *
 *  @since 2.4
 *
 *  @package dokan
 */

/**
 * Returns Current User Profile progress bar HTML
 *
 * @since 2.1
 *
 * @return output
 */
if ( !function_exists( 'dokan_get_profile_progressbar' ) ) {

	function dokan_get_profile_progressbar() {
	    global $current_user;

	    $profile_info = dokan_get_store_info( $current_user->ID );
	    $progress     = isset( $profile_info['profile_completion']['progress'] ) ? $profile_info['profile_completion']['progress'] : 0;
	    $next_todo    = isset( $profile_info['profile_completion']['next_todo'] ) ? $profile_info['profile_completion']['next_todo'] : __('Start with adding a Banner to gain profile progress','dokan');

	    ob_start();

	    if (  strlen( trim( $next_todo ) ) != 0 ) {
	    	dokan_get_template_part( 'global/profile-progressbar', '', array( 'pro'=>true, 'progress'=>$progress, 'next_todo' => $next_todo ) );
	    }

	    $output = ob_get_clean();

	    return $output;
	}

}

/**
 * Get refund counts, used in admin area
 *
 *  @since 2.4.11
 *
 * @global WPDB $wpdb
 * @return array
 */
function dokan_get_refund_count() {
    global $wpdb;

    $cache_key = 'dokan_refund_count';
    $counts = wp_cache_get( $cache_key );

    if ( false === $counts ) {

        $counts = array( 'pending' => 0, 'completed' => 0, 'cancelled' => 0 );
        $sql = "SELECT COUNT(id) as count, status FROM {$wpdb->prefix}dokan_refund GROUP BY status";
        $result = $wpdb->get_results( $sql );

        if ( $result ) {
            foreach ($result as $row) {
                if ( $row->status == '0' ) {
                    $counts['pending'] = (int) $row->count;
                } elseif ( $row->status == '1' ) {
                    $counts['completed'] = (int) $row->count;
                } elseif ( $row->status == '2' ) {
                    $counts['cancelled'] = (int) $row->count;
                }
            }
        }
    }

    return $counts;
}


/**
 * Get get seller coupon
 *
 *  @since 2.4.12
 *
 * @param int $seller_id
 *
 * @return array
 */
function dokan_get_seller_coupon( $seller_id, $show_on_store = false ) {
    $args = array(
        'post_type'   => 'shop_coupon',
        'post_status' => 'publish',
        'author'      => $seller_id,
    );

    if ( $show_on_store ) {
        $args['meta_query'][] = array(
            'key'   => 'show_on_store',
            'value' => 'yes',
        );
    }

    $coupons = get_posts( $args );

    return $coupons;
}
