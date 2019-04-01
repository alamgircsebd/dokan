<?php

namespace DokanPro\ReportAbuse;

class AdminSingleProduct {

    /**
     * Class constructor
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    public function __construct() {
        add_action( 'add_meta_boxes', [ self::class, 'add_abuse_report_meta_box' ] );
    }

    /**
     * Add metabox in product editing page
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    public static function add_abuse_report_meta_box() {
        add_meta_box( 'dokan_report_abuse_reports', __( 'Abuse Reports', 'dokan' ), [ self::class, 'meta_box' ], 'product', 'normal', 'core' );
    }

    /**
     * Abuse Reports metabox
     *
     * @since DOKAN_PRO_SINCE
     *
     * @param \WP_Post $post
     *
     * @return void
     */
    public static function meta_box( $post ) {
        $reports = dokan_report_abuse_get_reports( [
            'product_id' => $post->ID,
        ] );

        dokan_report_abuse_template( 'report-abuse-admin-single-product', [
            'reports'     => $reports,
            'date_format' => get_option( 'date_format', 'F j, Y' ),
            'time_format' => get_option( 'time_format', 'g:i a' ),
        ] );
    }
}
