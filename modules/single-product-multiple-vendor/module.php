<?php

namespace WeDevs\DokanPro\Modules\SPMV;

class Module {

    /**
     * Load automatically when class initiate
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->define();

        $this->includes();

        $this->initiate();

        $this->hooks();

        add_action( 'dokan_activated_module_spmv', array( self::class, 'activate' ) );
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
        define( 'DOKAN_SPMV_ASSETS_DIR', plugins_url( 'assets', __FILE__ ) );
        define( 'DOKAN_SPMV_VIEWS', DOKAN_SPMV_DIR . '/views' );
    }

    /**
     * includes all necessary class a functions file
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function includes() {
        require_once DOKAN_SPMV_INC_DIR . '/functions.php';
        require_once DOKAN_SPMV_INC_DIR . '/product-duplicator.php';

        if ( is_admin() ) {
            require_once DOKAN_SPMV_INC_DIR . '/admin.php';
            require_once DOKAN_SPMV_INC_DIR . '/products-admin.php';
        }

        require_once DOKAN_SPMV_INC_DIR . '/products.php';
        require_once DOKAN_SPMV_INC_DIR . '/product-visibility.php';
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
            new \Dokan_SPMV_Admin();
        }

        new \Dokan_SPMV_Products();
    }

    /**
     * Init all hooks
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function hooks() {
        $enable_option = dokan_get_option( 'enable_pricing', 'dokan_spmv', 'off' );

        if ( 'off' == $enable_option ) {
            return;
        }

        if ( is_admin() ) {
            new \Dokan_SPMV_Products_Admin();
        }

        new \Dokan_SPMV_Product_Visibility();
    }

    /**
     * Create Mapping table for product and vendor
     *
     * @since 1.0.0
     *
     * @return void
     */
    public static function activate() {
        global $wpdb;

        $sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dokan_product_map` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `map_id` bigint(20) DEFAULT NULL,
                `product_id` bigint(20) DEFAULT NULL,
                `seller_id` bigint(20) DEFAULT NULL,
                `is_trash` tinyint(4) NOT NULL DEFAULT '0',
                `visibility` tinyint(1) DEFAULT '1',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

        include_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }
}
