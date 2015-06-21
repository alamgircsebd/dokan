<?php

/**
* Dokan Pro Report Class
*
* @since 2.4
*
* @package dokan
*
*/
class Dokan_Pro_Reports {

    /**
     * Load autometically when class inistantiate
     *
     * @since 2.4
     *
     * @uses actions|filter hooks
     */
    public function __construct() {
        add_filter( 'dokan_get_seller_dashboard_nav', array( $this, 'add_reports_menu' ) );
        add_action( 'dokan_load_custom_template', array( $this, 'load_reports_template' ) );

        add_action( 'dokan_report_content_area_header', array( $this, 'dokan_report_header_render' ) );
        add_action( 'dokan_report_content', array( $this, 'render_review_content' ) );
    }

    /**
     * Add Report Menu
     *
     * @since 2.4
     *
     * @param array $urls
     *
     * @return array
     */
    public function add_reports_menu( $urls ) {

        $urls['report'] = array(
            'title' => __( 'Reports', 'dokan'),
            'icon'  => '<i class="fa fa-line-chart"></i>',
            'url'   => dokan_get_navigation_url( 'reports' ),
            'pos'   => 60
        );

        return $urls;
    }

    /**
     * Load Report Main Template
     *
     * @since 2.4
     *
     * @param  array $query_vars
     *
     * @return void
     */
    public function load_reports_template( $query_vars ) {

        if ( isset( $query_vars['reports'] ) ) {
            dokan_get_template_part( 'report/reports', '', array( 'pro'=>true ) );
            return;
        }

    }

    /**
     * Render Report Header Template
     *
     * @since 2.4
     *
     * @return void
     */
    public function dokan_report_header_render() {
        dokan_get_template_part( 'report/header', '', array( 'pro' => true ) );
    }

    public function render_review_content() {

        global $woocommerce;

        require_once DOKAN_INC_DIR . '/reports.php';

        $charts = dokan_get_reports_charts();
        $link = dokan_get_navigation_url( 'reports' );
        $current = isset( $_GET['chart'] ) ? $_GET['chart'] : 'overview';

        dokan_get_template_part( 'report/content', '', array(
            'pro' => true,
            'charts' => $charts,
            'link' => $link,
            'current' => $current,
        ) );
    }

}