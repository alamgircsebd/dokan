<?php

namespace DokanPro\ReportAbuse;

use WP_REST_Controller;
use WP_REST_Server;

class RestController extends WP_REST_Controller {

    /**
     * Endpoint namespace.
     *
     * @var string
     */
    protected $namespace = 'dokan/v1';

    /**
     * Route base.
     *
     * @var string
     */
    protected $rest_base = 'abuse-reports';

    /**
     * Register routes
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_items' ],
                'permission_callback' => [ $this, 'is_dokandar' ]
            ]
        ] );
    }

    /**
     * Permission callback
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return bool
     */
    public function is_dokandar() {
        return current_user_can( 'dokandar' );
    }

    /**
     * Get reports
     *
     * @since DOKAN_PRO_SINCE
     *
     * @param \WP_REST_Request $request
     *
     * @return \WP_REST_Response
     */
    public function get_items( $request ) {
        global $wpdb;

        $per_page = 20;
        $page     = ! empty( $request['page'] ) ? $request['page'] : 1;

        $data = dokan_report_abuse_get_reports( [
            'page' => $page
        ] );

        $response = rest_ensure_response( $data );

        $total = $wpdb->get_var( 'select count(*) from ' . $wpdb->prefix . 'dokan_report_abuse_reports' );
        $response->header( 'X-Dokan-AbuseReports-Total', $total );

        $max_pages = ceil( $total / $per_page );
        $response->header( 'X-Dokan-AbuseReports-TotalPages', (int) $max_pages );

        return $response;
    }
}
