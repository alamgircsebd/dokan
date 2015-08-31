<?php

/**
 * Ajax handling for Dokan in Admin area
 *
 * @since 2.2
 *
 * @author weDevs <info@wedevs.com>
 */
class Dokan_Pro_Admin_Ajax {

	/**
	 *  Load autometically all actions
	 */
	function __construct() {
        add_action( 'wp_ajax_regen_sync_table', array( $this, 'regen_sync_order_table' ) );
        add_action( 'wp_ajax_check_duplicate_suborders', array( $this, 'check_duplicate_suborders' ) );
        add_action( 'wp_ajax_print_duplicate_suborders', array( $this, 'print_duplicate_suborders' ) );
        add_action( 'wp_ajax_dokan_duplicate_order_delete', array( $this, 'dokan_duplicate_order_delete' ) );
	}

	/**
     * Initializes the Dokan_Template_Withdraw class
     *
     * Checks for an existing Dokan_Template_Withdraw instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new Dokan_Pro_Admin_Ajax();
        }

        return $instance;
    }

	/**
	 *  Handle sync order table via ajax
	 *
	 *  @return json success|error|data
	 */
    function regen_sync_order_table() {
        global $wpdb;

        parse_str( $_POST['data'], $data );

        if ( ! wp_verify_nonce( $data['_wpnonce'], 'regen_sync_table' ) ) {
            wp_send_json_error();
        }

        $limit        = $data['limit'];
        $offset       = $data['offset'];
        $total_orders = isset( $_POST['total_orders'] ) ? $_POST['total_orders'] : 0;

        if ( $offset == 0 ) {
            $wpdb->query( 'TRUNCATE TABLE ' . $wpdb->dokan_orders );
            $total_orders = $wpdb->get_var( "SELECT count(ID) FROM " . $wpdb->posts . "
                WHERE post_type = 'shop_order'"  );
        }

        $sql = "SELECT ID FROM " . $wpdb->posts . "
                WHERE post_type = 'shop_order'
                LIMIT %d,%d";

        $orders = $wpdb->get_results( $wpdb->prepare($sql, $offset * $limit, $limit ) );

        if ( $orders ) {
            foreach ( $orders as $order) {
                dokan_sync_order_table( $order->ID );
            }

            $sql = "SELECT * FROM " . $wpdb->dokan_orders;
            $generated = $wpdb->get_results( $sql );

            $done        = count( $generated );
            wp_send_json_success( array(
                'offset'       => $offset + 1,
                'total_orders' => $total_orders,
                'done'         => $done,
                'message'      => sprintf( __( '%d orders sync completed out of %d', 'dokan' ), $done, $total_orders )
            ) );
        } else {
            $dashboard_link = sprintf( '<a href="%s">%s</a>', admin_url( 'admin.php?page=dokan' ), __( 'Go to Dashboard &rarr;', 'dokan' ) );
            wp_send_json_success( array(
                'offset'  => 0,
                'done'    => 'All',
                'message' => sprintf( __( 'All orders has been synchronized. %s', 'dokan' ), $dashboard_link )
            ) );
        }
    }
    
    /**
     * Remove duplicate sub-orders if found 
     * 
     * @since 2.4.4
     * 
     */
    function check_duplicate_suborders(){
        if(session_id() == ''){
            session_start();
        }
        
        global $wpdb;
        
        parse_str( $_POST['data'], $data );

        if ( ! wp_verify_nonce( $data['_wpnonce'], 'regen_sync_table' ) ) {
            wp_send_json_error();
        }
        
        $limit        = $data['limit'];
        $offset       = $data['offset'];        
        $prev_done    = $_POST['done'];
        
        $total_orders = isset( $_POST['total_orders'] ) ? $_POST['total_orders'] : 0;

        if ( $offset == 0 ) {
            
            unset($_SESSION['dokan_duplicate_order_ids']);
            $total_orders = $wpdb->get_var( "SELECT count(ID) FROM " . $wpdb->prefix . "posts
                WHERE post_type = 'shop_order'
                AND ID IN(
                    SELECT post_parent FROM " . $wpdb->prefix . "posts
                    WHERE post_type = 'shop_order'
                    GROUP BY post_parent
                )" );
        }

        $sql = "SELECT ID FROM " . $wpdb->prefix . "posts
                WHERE post_type = 'shop_order'
                AND ID IN(
                    SELECT post_parent FROM " . $wpdb->prefix . "posts
                    WHERE post_type = 'shop_order'
                    GROUP BY post_parent
                )
                 LIMIT %d,%d";

        $orders = $wpdb->get_results( $wpdb->prepare( $sql, $offset * $limit, $limit ) );
        
        $duplicate_orders = isset( $_SESSION['dokan_duplicate_order_ids'] ) ? $_SESSION['dokan_duplicate_order_ids'] : array();

        if ( $orders ) {
            foreach ( $orders as $order ) {

                $sellers_count = count( dokan_get_sellers_by( $order->ID ) );

                $sub_order_ids = dokan_get_suborder_ids_by( $order->ID );

                if ( $sellers_count < count( $sub_order_ids ) ) {
                    $duplicate_orders = array_merge( array_slice( $sub_order_ids, $sellers_count ), $duplicate_orders );
                }
            }

            if ( count( $duplicate_orders ) ) {
                $_SESSION['dokan_duplicate_order_ids'] = $duplicate_orders;
            }
            $done = $prev_done + count($orders);
            wp_send_json_success( array(
                'offset'       => $offset + 1,
                'total_orders' => $total_orders,
                'done'         => $done,
                'message'      => sprintf( __( '%d orders checked out of %d', 'dokan' ), $done, $total_orders )
            ) );
        } else {
            if( count( $duplicate_orders ) ){
               wp_send_json_success( array(
                    'offset'  => 0,
                    'done'    => 'All',
                    'message' => sprintf( __( 'All orders are checked and we found some duplicate orders', 'dokan' ) ),
                    'duplicate' => true 
                ) );
            }
            $dashboard_link = sprintf( '<a href="%s">%s</a>', admin_url( 'admin.php?page=dokan' ), __( 'Go to Dashboard &rarr;', 'dokan' ) );
            wp_send_json_success( array(
                    'offset'  => 0,
                    'done'    => 'All',
                    'message' => sprintf( __( 'All orders has been synchronized. %s', 'dokan' ), $dashboard_link )
                ) );
            
        }
//            foreach ( $duplicate_orders as $duplicate_id ) {
//                wp_delete_post( (int) $duplicate_id->ID );
//            }
        
    }
    
    function print_duplicate_suborders() {
        if(session_id() == ''){
            session_start();
        }
        $duplicate_orders = isset( $_SESSION['dokan_duplicate_order_ids'] ) ? $_SESSION['dokan_duplicate_order_ids'] : array();
        
        ob_start();
        
        require_once DOKAN_INC_DIR.'/pro/admin/duplicate-order-list.php';
        
        $html = ob_get_clean();
        
        wp_send_json_success( array(
            'html'  => $html,
        ) );
        
        
    }
    function dokan_duplicate_order_delete(){
        
        $duplicate_order_id = (int) $_POST['order_id'];
        if(!$duplicate_order_id){
            wp_send_json_error();
        }
        
        if ( wp_delete_post( $duplicate_order_id ) ) {
            wp_send_json_success( array(
                'status' => 'deleted',
            ) );
        }
    }
}