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

        $table_name = $wpdb->prefix . 'dokan_orders';

        if ( $offset == 0 ) {
            $wpdb->query( 'TRUNCATE TABLE ' . $table_name );
            $total_orders = $wpdb->get_var( "SELECT count(ID) FROM " . $wpdb->prefix . "posts
                WHERE post_type LIKE 'shop_order'"  );
        }
        
        $sql = "SELECT ID FROM " . $wpdb->prefix . "posts
                WHERE post_type LIKE 'shop_order'
                LIMIT %d,%d";

        $orders = $wpdb->get_results( $wpdb->prepare($sql, $offset * $limit, $limit ) );

        if ( $orders ) {
            foreach ( $orders as $order) {
                dokan_sync_order_table( $order->ID );
            }
            $sql = "SELECT * FROM " . $table_name;
            $generated = $wpdb->get_results( $sql );

            $done        = count( $generated );
            wp_send_json_success( array(
                'offset'  => $offset + 1,
                'total_orders'  => $total_orders,
                'done'    => $done,
                'message' => sprintf( __( '%d orders sync completed of total %d', 'dokan' ), $done, $total_orders )
            ) );
        } else {
            $done        = 'All';
            wp_send_json_success( array(
                'offset'  => 0,
                'done'    => $done,
                'message' => sprintf( __( '%s orders sync completed', 'dokan' ), $done )
            ) );
        }
    }
}