<?php

/**
 * Include Dokan Report Abuse template
 *
 * @since 1.0.0
 *
 * @param string $name
 * @param array  $args
 *
 * @return void
 */
function dokan_report_abuse_template( $name, $args = [] ) {
    dokan_get_template( "$name.php", $args, DOKAN_REPORT_ABUSE_VIEWS, trailingslashit( DOKAN_REPORT_ABUSE_VIEWS ) );
}

/**
 * Create abuse report
 *
 * @since DOKAN_PRO_SINCE
 *
 * @param array $args
 *
 * @return array
 */
function dokan_report_abuse_create_report( $args ) {
    global $wpdb;

    $defaults = [
        'reason'        => '',
        'product_id'     => 0,
        'customer_id'    => 0,
        'customer_name'  => '',
        'customer_email' => '',
        'description'    => '',
    ];

    $args = wp_parse_args( $args, $defaults );

    $report       = [];
    $placeholders = [];

    if ( empty( $args['reason'] ) ) {
        return new WP_Error( 'missing_reason', esc_html__( 'Missing reason param.', 'dokan' ) );
    }

    $args['reason'] = wp_trim_words( $args['reason'], 191 );

    $report['reason'] = $args['reason'];
    $placeholders[]   = '%s';

    if ( empty( $args['product_id'] ) ) {
        return new WP_Error( 'missing_product_id', esc_html__( 'Missing product_id param.', 'dokan' ) );
    }

    $product = wc_get_product( $args['product_id'] );

    if ( ! $product instanceof WC_Product ) {
        return new WP_Error( 'invalid_product_id', esc_html__( 'Product not found.', 'dokan' ) );
    }

    $report['product_id'] = $args['product_id'];
    $placeholders[]       = '%d';

    $vendor = dokan_get_vendor_by_product( $product );

    $report['vendor_id'] = $vendor->get_id();
    $placeholders[]      = '%d';

    $customer = null;

    if ( ! empty( $args['customer_id'] ) ) {
        $customer    = new WC_Customer( $args['customer_id'] );
        $customer_id = $customer->get_id();

        if ( ! $customer_id ) {
            return new WP_Error( 'invalid_customer_id', esc_html__( 'Customer not found.', 'dokan' ) );
        }
    }

    $option = get_option( 'dokan_report_abuse', [] );

    if ( isset( $option['reported_by_logged_in_users_only'] ) && 'on' === $option['reported_by_logged_in_users_only'] ) {
        if ( empty( $customer ) ) {
            return new WP_Error( 'user_must_logged_in', esc_html__( 'User must login to report an abuse.', 'dokan' ) );
        }

        $report['customer_id'] = $customer_id;
        $placeholders[]        = '%d';
    } else if ( $customer ) {
        $report['customer_id'] = $customer_id;
        $placeholders[]        = '%d';
    } else {
        if ( empty( $args['customer_name'] ) ) {
            return new WP_Error( 'missing_field', esc_html__( 'customer_name is required.', 'dokan' ) );
        } else if ( empty( $args['customer_email'] ) ) {
            return new WP_Error( 'missing_field', esc_html__( 'customer_email is required.', 'dokan' ) );
        } else if ( ! is_email( $args['customer_email'] ) ) {
            return new WP_Error( 'missing_field', esc_html__( 'Invalid customer_email.', 'dokan' ) );
        }

        $report['customer_name'] = wp_trim_words( $args['customer_name'], 191 );
        $placeholders[]          = '%s';

        $report['customer_email'] = wp_trim_words( $args['customer_email'], 100 );
        $placeholders[]           = '%s';
    }

    if ( ! empty( $args['description'] ) ) {
        $report['description'] = $args['description'];
        $placeholders[]        = '%s';
    }

    $report['created_at'] = current_time( 'mysql' );
    $placeholders[]       = '%s';

    $inserted = $wpdb->insert(
        $wpdb->prefix . 'dokan_report_abuse_reports',
        $report,
        $placeholders
    );

    if ( ! $inserted ) {
        return new WP_Error( 'unable_to_create_report', esc_html__( 'Unable to create abuse report.', 'dokan' ) );
    }

    $report = $wpdb->get_row(
        "select * from {$wpdb->prefix}dokan_report_abuse_reports where id = {$wpdb->insert_id}"
    );

    /**
     * Fires after created an abuse report
     *
     * @since DOKAN_PRO_SINCE
     *
     * @param object            $report
     * @param \WC_Product       $product
     * @param \Dokan_Vendor     $vendor
     * @param null|\WC_Customer $customer
     */
    do_action( 'dokan_report_abuse_created_report', $report, $product, $vendor, $customer );

    return $report;
}

/**
 * Report Abuse Form
 *
 * @since DOKAN_PRO_SINCE
 *
 * @param array $args
 * @param bool  $echo
 *
 * @return void|string
 */
function dokan_report_abuse_report_form( $args = [], $echo = false ) {
    $defaults = [
        'text'                 => esc_html__( 'Why are you reporting this?', 'dokan' ),
        'id'                   => 'dokan-report-abuse-form',
        'option_list_classes'  => '',
        'option_label_classes' => '',
    ];

    $args   = wp_parse_args( $args, $defaults );
    $option = get_option( 'dokan_report_abuse', [] );

    if ( empty( $option['reported_by_logged_in_users_only'] ) || 'on' !== $option['reported_by_logged_in_users_only'] ) {
        $option['reported_by_logged_in_users_only'] = 'off';
    }

    if ( empty( $option['abuse_reasons'] ) ) {
        $option['abuse_reasons'] = [];

        $option['abuse_reasons'][] = [
            'id'    => 'other',
            'value' => esc_html__( 'Other', 'dokan' ),
        ];
    }

    $args = array_merge( $args, $option );

    if ( $echo ) {
        dokan_report_abuse_template( 'report-form', $args );
    } else {
        ob_start();
        dokan_report_abuse_template( 'report-form', $args );
        return ob_get_clean();
    }
}

/**
 * Get abuse reports
 *
 * @since DOKAN_PRO_SINCE
 *
 * @param array $args
 *
 * @return array
 */
function dokan_report_abuse_get_reports( $args = [] ) {
    global $wpdb;

    $defaults = [
        'per_page' => 20,
        'page'     => 1
    ];

    $args = wp_parse_args( $args, $defaults );

    $limit = 20;

    $offset = $args['per_page'] * ( $args['page'] - 1 );

    $results = $wpdb->get_results(
        $wpdb->prepare(
            'select * from ' . $wpdb->prefix . 'dokan_report_abuse_reports'
            . ' order by id desc'
            . ' limit %d offset %d',
            $limit, $offset
        )
    );

    $reports = [];

    foreach ( $results as $i => $result ) {
        $reports[ $i ]['id']     = absint( $result->id );
        $reports[ $i ]['reason'] = $result->reason;

        $product = wc_get_product( $result->product_id );
        $reports[ $i ]['product'] = [
            'id'        => $product->get_id(),
            'title'     => $product->get_title(),
            'admin_url' => admin_url( sprintf( 'post.php?post=%d&action=edit', $product->get_id() ) ),
        ];

        $vendor = dokan_get_vendor( $result->vendor_id );
        $reports[ $i ]['vendor'] = [
            'id'        => $vendor->get_id(),
            'name'      => $vendor->get_shop_name(),
            'admin_url' => admin_url( sprintf( 'user-edit.php?user_id=%d', $vendor->get_id() ) ),
        ];

        if ( $result->customer_id ) {
            $customer       = new WC_Customer( $result->customer_id );
            $customer_name  = $customer->get_username();
            $customer_email = $customer->get_email();
            $admin_url      = admin_url( sprintf( 'user-edit.php?user_id=%d', $customer->get_id() ) );
        } else {
            $customer_name  = $result->customer_name;
            $customer_email = $result->customer_email;
            $admin_url      = null;
        }

        $reports[ $i ]['reported_by'] = [
            'id'        => absint( $result->customer_id ),
            'name'      => $customer_name,
            'email'     => $customer_email,
            'admin_url' => $admin_url,
        ];

        $reports[ $i ]['description'] = $result->description;
        $reports[ $i ]['reported_at'] = mysql_to_rfc3339( $result->created_at );
    }

    return $reports;
}
