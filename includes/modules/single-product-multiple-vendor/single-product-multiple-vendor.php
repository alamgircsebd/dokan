<?php
/*
Plugin Name: Single Product Multiple Vendor
Plugin URI: https://wedevs.com/products/dokan/dokan-simple-auctions/
Description: A module that offer for multiple vendor to sell a single product
Version: 1.0.0
Author: weDevs
Author URI: https://wedevs.com/
Thumbnail Name: auction.png
*/

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* className
*/
class Dokan_Single_Product_Multi_Vendor {

    /**
     * Load autometically when class initiate
     *
     * @since 1.0.0
     */
    public function __construct() {

        $this->define();

        $this->includes();

        $this->initiate();

        $this->hooks();
    }

    /**
     * Initializes the Dokan_Auction() class
     *
     * Checks for an existing Dokan_Auction() instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new Dokan_Single_Product_Multi_Vendor();
        }

        return $instance;
    }

    /**
     * hooks
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function define() {
        define( 'DOKAN_SPMV_DIR', dirname( __FILE__ ) );
        define( 'DOKAN_SPMV_INC_DIR', DOKAN_SPMV_DIR . '/includes' );
    }

    /**
     * includes all necessary class a functions file
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function includes() {
        if ( is_admin() ) {
            require_once DOKAN_SPMV_INC_DIR . '/admin.php';
        }
    }

    /**
     * Initiate all classes
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function initiate() {
        if ( is_admin() ) {
            new Dokan_SPMV_Admin();
        }
    }

    /**
     * Init all hooks
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function hooks() {
        $enable_option = dokan_get_option( 'enable_pricing', 'dokan_spmv', 'on' );

        if ( 'off' == $enable_option ) {
            return;
        }

        // Hook all necessary filter and actions
    }

}

Dokan_Single_Product_Multi_Vendor::init();

