<?php

/**
* Product Visibility handler
*/
class Dokan_SPMV_Product_Visibility {

    public function __construct() {
        add_action( 'dokan_after_saving_settings', [ $this, 'after_saving_settings' ], 10, 2 );
        add_action( 'dokan_spmv_create_clone_product', [ $this, 'after_create_clone' ], 10, 3 );
    }

    /**
     * Fires right save admin settings
     *
     * @since DOKAN_PRO_SINCE
     *
     * @param string $option_name
     * @param mixed  $option_value
     *
     * @return void
     */
    public function after_saving_settings( $option_name, $option_value ) {
        global $wpdb;

        if ( 'dokan_spmv' !== $option_name ) {
            return;
        }

        $updater_file = DOKAN_SPMV_INC_DIR . '/dokan-spmv-update-product-visibility.php';

        include_once $updater_file;
        $processor = new Dokan_SPMV_Update_Product_Visibility();

        $processor->cancel_process();

        $map_ids = $wpdb->get_col( "select map_id from {$wpdb->prefix}dokan_product_map group by map_id" );

        $item = [
            'map_ids' => $map_ids,
        ];

        $processor->push_to_queue( $item );
        $processor->save()->dispatch();

        $processes = get_option( 'dokan_background_processes', [] );
        $processes['Dokan_Geolocation_Update_Product_Visibility'] = $updater_file;

        update_option( 'dokan_background_processes', $processes, 'no' );
    }

    /**
     * Fires after cloning a product
     *
     * @since DOKAN_PRO_SINCE
     *
     * @param int $cloned_product_id
     * @param int $product_id
     * @param int $map_id
     *
     * @return void
     */
    public function after_create_clone( $cloned_product_id, $product_id, $map_id ) {
        dokan_spmv_update_clone_visibilities( $map_id );
    }
}
