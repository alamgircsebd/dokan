<?php
/**
 * Refund base class
 *
 * @author wedDevs <info@wedevs.com>
 *
 * @since 2.4.11
 *
 * @package dokan
 */

class Dokan_Pro_Refund {

    /**
     * Constructor method
     *
     * @since 2.9.13
     */
    public function __construct() {
        $this->hooks();
    }

    /**
     * All the hooks
     *
     * @since 2.9.13
     *
     * @return void
     */
    public function hooks() {
        add_action( 'wp_ajax_woocommerce_refund_line_items', [ $this, 'manipulate_ajax_request' ], 1 );
    }

    /**
     * manipulate wc ajax request
     *
     * @return void
     */
    public function manipulate_ajax_request() {
        check_ajax_referer( 'order-item', 'security' );
        $removed = remove_action( 'wp_ajax_woocommerce_refund_line_items', [ 'WC_AJAX', 'refund_line_items' ], 10 );

        if ( ! $removed ) {
            return;
        }

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return wp_send_json_error( [
                'error' => __( 'You don\'t have permission to create refund.', 'dokan' )
            ] );
        }

        $order_id  = ! empty( $_POST['order_id'] ) ? $_POST['order_id'] : 0;
        $order     = wc_get_order( $order_id );

        if ( $order->get_meta( 'has_sub_order' ) ) {
            return wp_send_json_error( [
                'error' => __( 'Please try to process refund from the sub orders.', 'dokan' )
            ] );
        }

        $seller_id = dokan_get_seller_id_by_order( $order_id );

        if ( ! $seller_id ) {
            return wp_send_json_error( [
                'error' => __( 'Vendor not found.', 'dokan' )
            ] );
        }

        // pass seller_id and status to `insert_refund` method with the $_POST variable
        $_POST['status']    = 0;
        $_POST['seller_id'] = $seller_id;

        $posted_data = wp_unslash( $_POST );
        $refund_id   = $this->insert_refund( $posted_data );

        if ( ! $refund_id ) {
            wp_send_json_error( [
                'error' => __( 'Unable to insert refund.', 'dokan' )
            ] );
        }

        if ( ! class_exists( 'Dokan_REST_Refund_Controller' ) ) {
            require_once DOKAN_PRO_INC . '/api/class-refund-controller.php';
        }

        global $wpdb;

        $sql         = "SELECT * FROM `{$wpdb->prefix}dokan_refund` WHERE `id`={$refund_id}";
        $refund_data = $wpdb->get_row( $sql );

        $refund_api     = new Dokan_REST_Refund_Controller;
        $approve_refund = $refund_api->approve_refund_request( $refund_data );

        if ( ! $approve_refund ) {
            return wp_send_json_error( [
                'error' => __( 'Unable to approve refund request', 'dokan' )
            ] );
        }

        $order->add_order_note( __( 'Refund has been made by admin.', 'dokan' ) );
        $order->save();

        $this->update_status( $refund_id, $this->get_status_code( 'completed' ) );
        $response = [];

        if ( did_action( 'woocommerce_order_fully_refunded' ) ) {
            $response['status'] = 'fully_refunded';
        }

        wp_send_json_success( $response );
    }

    /**
     * Initializes the Dokan_Template_Refund class
     *
     * Checks for an existing Dokan_Template_Refund instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new Dokan_Pro_Refund();
        }

        return $instance;
    }

    /**
     * Update refund status
     *
     * @since 2.4.11
     *
     * @param  integer $row_id
     * @param  integer $order_id
     * @param  string $status
     *
     * @return void
     */
    function update_status( $row_id, $status ) {
        global $wpdb;

        // 0 -> pending
        // 1 -> approve
        // 2 -> cancelled
        $wpdb->query( $wpdb->prepare(
            "UPDATE {$wpdb->prefix}dokan_refund
            SET status = %d WHERE id = %d",
            $status, $row_id
        ) );
    }

    /**
     * Insert an refund request
     *
     * @since 2.4.11
     *
     * @param  array  $data
     *
     * @return boolean
     */
    function insert_refund( $data = array() ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'dokan_refund';

        $postdata = array(
            'order_id'        => (int) $data['order_id'],
            'seller_id'       => (int) $data['seller_id'],
            'refund_amount'   => wc_format_decimal( sanitize_text_field( $data['refund_amount'] ), wc_get_price_decimals() ),
            'refund_reason'   => $data['refund_reason'],
            'item_qtys'       => $data['line_item_qtys'],
            'item_totals'     => $data['line_item_totals'],
            'item_tax_totals' => $data['line_item_tax_totals'],
            'restock_items'   => $data['restock_refunded_items'],
            'date'            => current_time( 'mysql' ),
            'status'          => (int) $data['status'],
            'method'          => $data['api_refund'],
        );

        $format = array( '%d', '%d', '%f', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s' );

        $wpdb->insert( $table_name, $postdata, $format );

        return $wpdb->insert_id;
    }

    /**
     * Check if has already pending refund request
     *
     * @since 2.4.11
     *
     * @return boolean
     */
    function has_pending_refund_request( $order_id ) {
        global $wpdb;

        $wpdb->dokan_refund = $wpdb->prefix . 'dokan_refund';

        $sql    = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}dokan_refund WHERE status = %d AND order_id = %d", 0, $order_id );
        $status = $wpdb->get_results( $sql );

        if ( $status ) {
            return true;
        }

        return false;
    }

    /**
     * Get refund request
     *
     * @since 2.4.11
     *
     * @param  integer   $status
     * @param  integer   $limit
     * @param  integer   $offset
     *
     * @return array
     */
    function get_refund_requests( $status = 0, $limit = 10, $offset = 0 ) {
        global $wpdb;

        $sql    = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}dokan_refund WHERE status = %d LIMIT %d, %d", $status, $offset, $limit );
        $result = $wpdb->get_results( $sql );

        return $result;
    }

    /**
     * Delete a refund request
     *
     * @since 2.4.11
     *
     * @param  integer
     *
     * @return void
     */
    function delete_refund( $id ) {
        global $wpdb;
        $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}dokan_refund WHERE id = %d", $id ) );
    }

    /**
     * Get status code by status type
     *
     * @since 2.4.11
     *
     * @param  string
     *
     * @return integer
     */
    function get_status_code( $status ) {
        switch ( $status ) {
            case 'pending':
                return 0;
                break;

            case 'completed':
                return 1;
                break;

            case 'cancelled':
                return 2;
                break;
        }
    }

    /**
     * Print status messages
     *
     * @since 2.4.11
     *
     * @param  string  $status
     *
     * @return void
     */
    function request_status( $status ) {
        switch ( $status ) {
            case 0:
                return '<span class="label label-danger">' . __( 'Pending Reivew', 'dokan' ) . '</span>';
                break;

            case 1:
                return '<span class="label label-warning">' . __( 'Accepted', 'dokan' ) . '</span>';
                break;
        }
    }
}
