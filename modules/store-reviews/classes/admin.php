<?php

/**
* Admin class for store reviews
*
* @since 1.0.0
*/
class DSR_Admin {

    /**
     * Load autometically when class initiate
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_action( 'dokan_admin_menu', array( $this, 'load_store_review_menu' ), 10, 2 );
        add_filter( 'dokan-admin-routes', array( $this, 'vue_admin_routes' ) );
        add_action( 'dokan-vue-admin-scripts', array( $this, 'vue_admin_enqueue_scripts' ) );
    }

    /**
     * Initializes the DSR_Admin() class
     *
     * Checks for an existing DSR_Admin() instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;
        
        if ( !$instance ) {
            $instance = new DSR_Admin();
        }

        return $instance;
    }

    /**
     * Load store review menu
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function load_store_review_menu( $capability, $menu_position ) {
        global $submenu;
        
        $submenu['dokan'][] = [ __( 'Store Reviews', 'dokan' ), $capability, 'admin.php?page=dokan#/store-reviews' ];
    }

    /**
     * Load store review routes
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function vue_admin_routes( $routes ) {
        $routes[] = [
            'path'      => '/store-reviews',
            'name'      => 'Store Reviews',
            'component' => 'StoreReviews'
        ];

        return $routes;
    }

    /**
     * Load admin vue scripts
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function vue_admin_enqueue_scripts() {
        wp_enqueue_style( 'dsr-admin-css', DOKAN_SELLER_RATINGS_PLUGIN_ASSEST . '/css/admin.css', false, time() );
        wp_enqueue_script( 'dsr-admin', DOKAN_SELLER_RATINGS_PLUGIN_ASSEST . '/js/admin.js', array( 'jquery', 'dokan-vue-vendor', 'dokan-vue-bootstrap' ), false, true );
    }
}

$dsr_admin = DSR_Admin::init();