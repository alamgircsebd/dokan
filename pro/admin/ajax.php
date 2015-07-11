<?php

/**
*  Ajax handling for Dokan in Admin area
*
*  @since 2.2
*
*  @author weDevs <info@wedevs.com>
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

        if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'regen_sync_table' ) ) {
            wp_send_json_error();
        }
        $limit         = $_POST['limit'];

        $table_name = $wpdb->prefix . 'dokan_orders';

        if ( $_POST['offset'] == 0 ) {
            $wpdb->query( 'TRUNCATE TABLE ' . $table_name );
        }

        $args = array( 'posts_per_page' => $limit, 'offset'=> $_POST['offset'], 'post_type' => 'shop_order', 'post_status' => 'any' );

        $orders = get_posts( $args );

        if ( $orders ) {
            foreach ( $orders as $order) {
                if ( count( get_posts( array( 'post_parent' => $order->ID, 'post_type' => $order->post_type, 'post_status' => 'any' ) ) ) ) {
                    return;
                } else {
                    dokan_sync_order_table( $order->ID );
                }
            }
            $sql = "SELECT * FROM " . $table_name;
            $generated = $wpdb->get_results( $sql );
            // re-calculate stats
            $done        = count( $generated );
            wp_send_json_success( array(
                'offset'  => $_POST['offset'] + 1,
                'done'    => $done,
                'message' => sprintf( __( '%d order sync completed', 'dokan' ), $done )
            ) );
        } else {
            $done        = 'All';
            wp_send_json_success( array(
                'offset'  => 0,
                'done'    => $done,
                'message' => sprintf( __( '%s order sync completed', 'dokan' ), $done )
            ) );
        }
    }
}