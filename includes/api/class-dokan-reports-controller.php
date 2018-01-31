<?php

/**
 * REST API Reports controller
 *
 * Handles requests to the /reports endpoint.
 *
 * @author   Dokan
 * @category API
 * @package  Dokan/API
 * @since    2.8
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class Dokan_REST_Reports_Controller extends WP_REST_Controller {

    /**
     * Endpoint namespace.
     *
     * @var string
     */
    protected $namespace = 'dokan/v1';

    /**
     * Route name
     *
     * @var string
     */
    protected $base = 'reports';

    /**
     * Register all routes related with reports
     *
     * @return void
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . '(?P<seller_id>\d+)/' . $this->base, array(
            array(
                'methods'  => WP_REST_Server::READABLE,
                'callback' => array( $this, 'get_report' ),
                'args'     => array(
                    'type' => array(
                        'default' => 'sales_overview',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'start_date' => array(
                        'default' => date( 'Y-m-01', current_time( 'timestamp' ) ),
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'end_date' => array(
                        'default' => date( 'Y-m-d', strtotime( 'midnight', current_time( 'timestamp' ) ) ),
                        'sanitize_callback' => 'sanitize_text_field',
                    )
                )
            ),
        ) );
    }
    
    /**
     * Get single report
     *
     * @return void
     */
    public function get_report( $request ) {
        
        $params = $request->get_params();
        error_log( print_r( $params, true ) );
        $seller_id = $params['seller_id'];
        
        if ( !dokan_is_user_seller( $seller_id ) ) {
            return new WP_Error( 'invalid_seller', 'Invalid Seller ID', array( 'status' => 404 ) );
        }
        
        switch ( $params['type'] ) {
            case 'sales_overview':
                $data = $this->get_sales_overview( $request );
                break;

            default:
                return new WP_Error( 'invalid_type', 'Invalid Report Type', array( 'status' => 404 ) );
        }
        
        $response = rest_ensure_response( $data );
        return $response;
    }
    
    public function  get_sales_overview( $request ) {
        
        $params     = $request->get_params();
        $seller_id  = $params['seller_id'];
        $start_date = $params['start_date'];
        $end_date   = $params['end_date'];
        
        $order_totals = dokan_get_order_report_data( array(
            'data'         => array(
                '_order_total'    => array(
                    'type'     => 'meta',
                    'function' => 'SUM',
                    'name'     => 'total_sales'
                ),
                '_order_shipping' => array(
                    'type'     => 'meta',
                    'function' => 'SUM',
                    'name'     => 'total_shipping'
                ),
                'ID'              => array(
                    'type'     => 'post_data',
                    'function' => 'COUNT',
                    'name'     => 'total_orders'
                )
            ),
            'filter_range' => true,
        ), $start_date, $end_date, $seller_id );

        $total_items    = absint( dokan_get_order_report_data( array(
            'data'         => array(
                '_qty' => array(
                    'type'            => 'order_item_meta',
                    'order_item_type' => 'line_item',
                    'function'        => 'SUM',
                    'name'            => 'order_item_qty'
                )
            ),
            'query_type'   => 'get_var',
            'filter_range' => true
        ), $start_date, $end_date, $seller_id ) );

        // Get discount amounts in range
        $total_coupons = dokan_get_order_report_data( array(
            'data'         => array(
                'discount_amount' => array(
                    'type'            => 'order_item_meta',
                    'order_item_type' => 'coupon',
                    'function'        => 'SUM',
                    'name'            => 'discount_amount'
                )
            ),
            'where'        => array(
                array(
                    'key'      => 'order_item_type',
                    'value'    => 'coupon',
                    'operator' => '='
                )
            ),
            'query_type'   => 'get_var',
            'filter_range' => true
        ), $start_date, $end_date, $seller_id );

        $average_sales = $order_totals->total_sales / ( 30 + 1 );
        
        $data = array(
            'total_sales'    => $order_totals->total_sales,
            'total_shipping' => $order_totals->total_shipping,
            'total_orders'   => absint( $order_totals->total_orders ),
            'total_items'    => $total_items,
            'total_coupons'  => $total_coupons,
            'average_sales'  => $average_sales,
        );
        
        return $data;
    }
}
