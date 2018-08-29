<?php

class Dokan_ShipStation_Hooks {

    public function __construct() {
        add_action( 'woocommerce_api_wc_shipstation', array( $this, 'init_shipstation_api' ) );
    }

    public function init_shipstation_api() {
        require_once DOKAN_SHIPSTATION_INCLUDES . '/abstract-class-dokan-shipstation-api-request.php';
        require_once DOKAN_SHIPSTATION_INCLUDES . '/class-dokan-shipstation-api.php';

        new Dokan_ShipStation_Api();
    }
}

