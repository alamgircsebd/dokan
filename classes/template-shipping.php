<?php
/**
 * Dokan Shipping Class
 *
 * @author weDves
 */

class Dokan_Template_Shipping {

    public static function init() {
        static $instance = false;

        if ( !$instance ) {
            $instance = new Dokan_Template_Shipping();
        }

        return $instance;
    }

    public function __construct() {

        add_action( 'woocommerce_shipping_init', array($this, 'include_shipping' ) );
        add_action( 'woocommerce_shipping_methods', array($this, 'register_shipping' ) );
    }


    /**
     * Include main shipping integration
     *
     * @return void
     */
    function include_shipping() {
        require_once DOKAN_INC_DIR . '/shipping-gateway/shipping.php';
    }

    /**
     * Register shipping method
     *
     * @param array $methods
     * @return array
     */
    function register_shipping( $methods ) {
        $methods[] = 'Dokan_WC_Per_Product_Shipping';

        return $methods;
    }
    
}