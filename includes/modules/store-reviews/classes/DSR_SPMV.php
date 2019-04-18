<?php

class DSR_SPMV {

    /**
     * Class constructor
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    public function __construct() {
        add_filter( 'dokan_spmv_show_order_options', array( $this, 'add_show_order_option' ) );
    }

    /**
     * Add show order option
     *
     * @since DOKAN_PRO_SINCE
     *
     * @param array $options
     *
     * @return array
     */
    public function add_show_order_option( $options ) {
        $options[] = array(
            'name'  => 'top_rated_vendor',
            'label' => __( 'Top rated vendor', 'dokan' ),
        );

        return $options;
    }
}
