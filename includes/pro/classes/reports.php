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
        add_action( 'dokan_report_content_inside_before', array( $this, 'show_seller_enable_message' ) );
        add_filter( 'dokan_get_dashboard_nav', array( $this, 'add_reports_menu' ) );
        add_action( 'dokan_load_custom_template', array( $this, 'load_reports_template' ) );
        add_action( 'dokan_report_content_area_header', array( $this, 'report_header_render' ) );
        add_action( 'dokan_report_content', array( $this, 'render_review_content' ) );
        add_action( 'template_redirect', array( $this, 'handle_statement' ) );
    }

    /**
     * Export statement
     *
     * @return vois
     */
    function handle_statement() {
        if ( ! is_user_logged_in() ) {
            return;
        }

        if ( ! dokan_is_user_seller( get_current_user_id() ) ) {
            return;
        }

        if ( isset( $_GET['dokan_statement_export_all'] ) ) {
            $start_date = date( 'Y-m-01', current_time('timestamp') );
            $end_date = date( 'Y-m-d', strtotime( 'midnight', current_time( 'timestamp' ) ) );

            if ( isset( $_GET['start_date'] ) ) {
                $start_date = $_GET['start_date'];
            }

            if ( isset( $_GET['end_date'] ) ) {
                $end_date = $_GET['end_date'];
            }
 
            $filename = "Statement-".time();
            header( "Content-Type: application/csv; charset=" . get_option( 'blog_charset' ) );
            header( "Content-Disposition: attachment; filename=$filename.csv" );
            $currency = get_woocommerce_currency_symbol();
            $headers = array(
                'date'     => __( 'Date', 'dokan' ),
                'order_id' => __( 'ID', 'dokan' ),
                'type'     => __( 'Type', 'dokan' ),
                'sales'    => __( 'Sales', 'dokan' ),
                'amount'   => __( 'Amounts', 'dokan' ),
                'balance'  => __( 'Balance', 'dokan' ),
            );

            foreach ( (array)$headers as $label ) {
                echo $label .', ';
            }

            echo "\r\n";

            $order     = dokan_get_seller_orders_by_date( $start_date, $end_date );
            $refund    = dokan_get_seller_refund_by_date( $start_date, $end_date );
            $widthdraw = dokan_get_seller_withdraw_by_date( $start_date, $end_date );

            $table_data = array_merge( $order, $refund, $widthdraw );
            $statements = [];

            foreach (  $table_data as $key => $data ) {
                $date = isset( $data->post_date ) ? strtotime( $data->post_date ) : strtotime( $data->date );
                $statements[$date] = $data;
            }
            
            ksort( $statements );

            $net_amount = 0;

            foreach ( $statements as $key => $statement ) {
                if ( isset( $statement->post_date ) ) {
                    $type       = __( 'Order', 'dokan' );
                    $url        = add_query_arg( array( 'order_id' => $statement->order_id ), dokan_get_navigation_url('orders') );
                    $id         = $statement->order_id;
                    $sales      =  $statement->order_total;
                    $amount     =  $statement->net_amount;
                    $net_amount = $net_amount + $statement->net_amount;
                    $net_amount_print =  $net_amount;

                } else if ( isset( $statement->refund_amount ) ) {
                    $type   = __( 'Refund', 'dokan' );
                    $url    = add_query_arg( array( 'order_id' => $statement->order_id ), dokan_get_navigation_url('orders') );
                    $id     = $statement->order_id;
                    $sales  =  0;
                    $amount = '-'. $statement->refund_amount;
                    $net_amount = $net_amount - $statement->refund_amount;
                    $net_amount_print =  $net_amount;

                } else {
                    $type       = __( 'Withdraw', 'dokan' );
                    $url        = add_query_arg( array( 'type' => 'approved' ), dokan_get_navigation_url('withdraw') );
                    $id         = $statement->id;
                    $sales      =  0;
                    $amount     = '-'. $statement->amount;
                    $net_amount = $net_amount - $statement->amount;
                    $net_amount_print =  $net_amount;
                }


                echo date( 'Y-m-d', $key ) . ', ';
                echo '#' .$id . ', ';
                echo $type . ', ';
                echo $sales . ', ';
                echo $amount . ', ';
                echo $net_amount_print . ', ';
                
                echo "\r\n";
            }

             exit();
        }
    }

    /**
     * Singleton object
     *
     * @staticvar boolean $instance
     *
     * @return \self
     */
    public static function init() {

        static $instance = false;

        if ( !$instance ) {
            $instance = new Dokan_Pro_Reports();
        }

        return $instance;
    }

    /**
     * Show Seller Enable Error Message
     *
     * @since 2.4
     *
     * @return void
     */
    public function show_seller_enable_message() {
        $user_id = get_current_user_id();

        if ( ! dokan_is_seller_enabled( $user_id ) ) {
            echo dokan_seller_not_enabled_notice();
        }
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

        $urls['reports'] = array(
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
    public function report_header_render() {
        dokan_get_template_part( 'report/header', '', array( 'pro' => true ) );
    }

    /**
     * Render Review Content
     *
     * @return [type] [description]
     */
    public function render_review_content() {

        global $woocommerce;

        require_once DOKAN_PRO_INC . '/reports.php';

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
