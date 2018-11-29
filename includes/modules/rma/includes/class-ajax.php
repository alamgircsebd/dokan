<?php

/**
* Ajax handling class
*/
class Dokan_RMA_Ajax {

    /**
     * Load autometically when class initiate
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_action( 'wp_ajax_dokan-update-return-request', [ $this, 'update_status' ], 10 );
    }

    /**
     * Update request status
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function update_status() {
        parse_str( $_POST['formData'], $data );

        if ( ! wp_verify_nonce( $_POST['nonce'], 'dokan_rma_nonce' ) ) {
            wp_send_json_error( __( 'Invalid nonce', 'dokan' ) );
        }

        $request = new Dokan_RMA_Warranty_Request();
        $updated = $request->update( $data );

        if ( is_wp_error( $updated ) ) {
            wp_send_json_error( $updated->get_error_message() );
        }

        wp_send_json_success( __( 'Status changed successfully', 'dokan' ) );
    }
}
