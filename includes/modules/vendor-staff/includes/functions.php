<?php

/**
 * Dokan get all vendor staffs
 *
 * @return array
 */
function dokan_get_all_vendor_staffs( $args ) {

    $defaults = array(
        'number' => 10,
        'offset' => 0,
        'vendor_id' => get_current_user_id(),
        'orderby' => 'registered',
        'order' => 'desc'
    );

    $args = wp_parse_args( $args, $defaults );

    $args['role'] = 'vendor_staff';
    $args['meta_query'] = array(
        array(
            'key'     => '_vendor_id',
            'value'   => $args['vendor_id'],
            'compare' => '='
        )
    );

    $user_search = new WP_User_Query( $args );
    $staffs     = $user_search->get_results();
    return array( 'total_users' => $user_search->total_users, 'staffs' => $staffs );
}

function dokan_get_staff_capabilities() {
    return apply_filters( 'dokan_get_staff_capabilities', array(
        'dokan_view_sales_overview',
        'dokan_view_sales_report_chart',
        'dokan_view_announcement',
        'dokan_view_order_report',
        'dokan_view_review_reports',
        'dokan_view_product_status_report',
        'dokan_add_product',
        'dokan_edit_product',
        'dokan_delete_product',
        'dokan_view_product',
        'dokan_duplicate_product',
        'dokan_import_product',
        'dokan_export_product',
        'dokan_view_order',
        'dokan_manage_order',
        'dokan_manage_order_note',
        'dokan_manage_reviews',
        'dokan_view_overview_menu',
        'dokan_view_product_menu',
        'dokan_view_order_menu',
        'dokan_view_review_menu',
        'dokan_view_store_settings_menu',
        'dokan_view_store_shipping_menu',
        'dokan_view_store_social_menu',
        'dokan_view_store_seo_menu',
    ) );
}