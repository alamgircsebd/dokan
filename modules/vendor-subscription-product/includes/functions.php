<?php

/**
* Get vendors subscripton by orders
*
* @since 1.0.0
*
* @param Array $user_orders
*
* @return Array
*/
function dokan_vps_get_vendor_subscriptons_by_orders( $user_orders, $seller_id ) {
    $user_subscriptions = array();

    if ( $user_orders ) {
        foreach ( $user_orders as $order ) {
            $the_subscriptions = wcs_get_subscriptions_for_order( $order->order_id );
            foreach ( $the_subscriptions as $skey => $the_subscription ) {
                $subscription_products = $the_subscription->get_items();
                foreach ( $subscription_products as $pkey => $subscription_product ) {
                    if( $seller_id == get_post_field( 'post_author', $subscription_product->get_product_id() ) ){
                        $user_subscriptions[$skey] = $the_subscription;
                    }
                }
            }
        }
        if ( $user_subscriptions ) {
            return $user_subscriptions;
        } else {
            return false;
        }
    }

    return false;
}

/**
 * Get all the orders from a specific seller
 *
 * @global Object $wpdb
 *
 * @param Integer $seller_id
 * @param String $status
 * @param String $order_date
 * @param Integer $limit
 * @param Integer $offset
 * @param Integer $customer_id
 *
 * @return Array
 */
function dokan_vps_get_seller_orders( $seller_id, $status = 'all', $order_date = NULL, $limit = 10, $offset = 0, $customer_id = null, $relation = null ) {
    global $wpdb;

    $cache_group                 = 'dokan_seller_data_'.$seller_id;
    $cache_key                   = 'dokan-seller-orders-' . $status . '-' . $seller_id;
    $orders                      = wp_cache_get( $cache_key, $cache_group );

    $join_meta                   = "LEFT JOIN $wpdb->postmeta AS pm ON pm.post_id = p.ID";
    $where_customer              = $customer_id ? sprintf( "pm.meta_key = '_customer_user' AND pm.meta_value = %d AND", $customer_id ) : '';
    $where_subscription_relation = $relation ? sprintf( "pm.meta_key = 'subscription_order_type' AND pm.meta_value = '%s' AND", $relation  ) : '';

    if ( $orders === false ) {
        $status_where = ( $status == 'all' ) ? '' : $wpdb->prepare( ' AND order_status = %s', $status );
        $date_query   = ( $order_date ) ? $wpdb->prepare( ' AND DATE( p.post_date ) = %s', $order_date ) : '';

        $orders = $wpdb->get_results( $wpdb->prepare( "SELECT do.order_id, p.post_date
                FROM {$wpdb->prefix}dokan_orders AS do
                LEFT JOIN $wpdb->posts AS p ON do.order_id = p.ID
                {$join_meta}
                WHERE
                        do.seller_id = %d AND
                        {$where_customer}
                        {$where_subscription_relation}
                        p.post_status != 'trash'
                        {$date_query}
                        {$status_where}
                GROUP BY do.order_id
                ORDER BY p.post_date DESC
                LIMIT %d, %d", $seller_id, $offset, $limit
        ) );

        wp_cache_set( $cache_key, $orders, $cache_group );
        dokan_cache_update_group( $cache_key, $cache_group );
    }

    return $orders;
}


/**
 * Get the orders total from a specific seller
 *
 * @global object $wpdb
 * @param int $seller_id
 * @return array
 */
function dokan_vps_get_seller_orders_number( $seller_id, $status = 'all', $relation = null ) {
    global $wpdb;

    $cache_group = 'dokan_seller_data_'.$seller_id;
    $cache_key   = 'dokan-seller-orders-count-' . $status . '-' . $seller_id;
    $count       = wp_cache_get( $cache_key, $cache_group );
    $join_meta        = "LEFT JOIN $wpdb->postmeta AS pm ON pm.post_id = p.ID";
    $where_subscription_relation = $relation ? sprintf( "pm.meta_key = 'subscription_order_type' AND pm.meta_value = '%s' AND", $relation  ) : '';

    if ( $count === false ) {
        $status_where = ( $status == 'all' ) ? '' : $wpdb->prepare( ' AND order_status = %s', $status );

        $result = $wpdb->get_row( $wpdb->prepare( "SELECT COUNT(do.order_id) as count
                        FROM {$wpdb->prefix}dokan_orders AS do
                        LEFT JOIN $wpdb->posts AS p ON do.order_id = p.ID
                        {$join_meta}
                        WHERE
                                do.seller_id = %d AND
                                {$where_subscription_relation}
                                p.post_status != 'trash'
                                {$status_where}", $seller_id ) );

        $count  = $result->count;

        wp_cache_set( $cache_key, $count, $cache_group );
        dokan_cache_update_group( $cache_key, $cache_group );
    }

    return $count;
}

/**
* Get translated string of order status
*
* @param string $status
* @return string
*/
function dokan_vps_get_subscription_status_translated( $status ) {
    switch ($status) {
        case 'completed':
        case 'wc-completed':
            return __( 'Completed', 'dokan' );
            break;

        case 'active':
        case 'wc-active':
            return __( 'Active', 'dokan' );
            break;

        case 'expired':
        case 'wc-expired':
            return __( 'Expired', 'dokan' );
            break;

        case 'pending':
        case 'wc-pending':
            return __( 'Pending Payment', 'dokan' );
            break;

        case 'on-hold':
        case 'wc-on-hold':
            return __( 'On-hold', 'dokan' );
            break;

        case 'processing':
        case 'wc-processing':
            return __( 'Processing', 'dokan' );
            break;

        case 'refunded':
        case 'wc-refunded':
            return __( 'Refunded', 'dokan' );
            break;

        case 'cancelled':
        case 'wc-cancelled':
            return __( 'Cancelled', 'dokan' );
            break;

        case 'failed':
        case 'wc-failed':
            return __( 'Failed', 'dokan' );
            break;

        default:
            return apply_filters( 'dokan_vps_get_order_status_translated', '', $status );
            break;
    }
}

/**
* Get bootstrap label class based on order status
*
* @param string $status
* @return string
*/
function dokan_vps_get_subscription_status_class( $status ) {
    switch ( $status ) {
        case 'completed':
        case 'wc-completed':
        case 'active':
        case 'wc-active':
            return 'success';
            break;

        case 'pending':
        case 'wc-pending':
            return 'danger';
            break;

        case 'on-hold':
        case 'wc-on-hold':
            return 'warning';
            break;

        case 'processing':
        case 'wc-processing':
            return 'info';
            break;

        case 'refunded':
        case 'wc-refunded':
            return 'default';
            break;

        case 'cancelled':
        case 'wc-cancelled':
            return 'default';
            break;

        case 'failed':
        case 'wc-failed':
        case 'expired':
        case 'wc-expired':
            return 'danger';
            break;

        default:
            return apply_filters( 'dokan_get_order_status_class', '', $status );
            break;
    }
}

/**
 * Display Date format for subscriptions
 *
 * @since 1.0.0
 *
 * @return void
 */
function dokan_vps_get_date_content( $subscription, $column ) {
    $date_type_map = array( 'last_payment_date' => 'last_order_date_created' );
    $date_type     = array_key_exists( $column, $date_type_map ) ? $date_type_map[ $column ] : $column;

    if ( 0 == $subscription->get_time( $date_type, 'gmt' ) ) {
        $column_content = '-';
    } else {
        $column_content = sprintf( '<time class="%s" title="%s">%s</time>', esc_attr( $column ), esc_attr( date( __( 'Y/m/d g:i:s A', 'woocommerce-subscriptions' ) , $subscription->get_time( $date_type, 'site' ) ) ), esc_html( $subscription->get_date_to_display( $date_type ) ) );

        if ( 'next_payment_date' == $column && $subscription->payment_method_supports( 'gateway_scheduled_payments' ) && ! $subscription->is_manual() && $subscription->has_status( 'active' ) ) {
            $column_content .= '<div class="woocommerce-help-tip" data-tip="' . esc_attr__( 'This date should be treated as an estimate only. The payment gateway for this subscription controls when payments are processed.', 'woocommerce-subscriptions' ) . '"></div>';
        }
    }

    return $column_content;
}

