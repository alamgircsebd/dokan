<?php

namespace WeDevs\DokanPro\Modules\Wholesale;

class Module {

    /**
     * Load automatically when class initiate
     *
     * @since 2.9.5
     */
    public function __construct() {
        $this->define();
        $this->includes();
        $this->initiate();
        $this->hooks();
    }

    /**
     * hooks
     *
     * @since 2.9.5
     *
     * @return void
     */
    public function define() {
        define( 'DOKAN_WHOLESALE_DIR', dirname( __FILE__ ) );
        define( 'DOKAN_WHOLESALE_INC_DIR', DOKAN_WHOLESALE_DIR . '/includes' );
        define( 'DOKAN_WHOLESALE_ASSETS_DIR', plugins_url( 'assets', __FILE__ ) );
    }

    /**
    * Get plugin path
    *
    * @since 2.9.5
    *
    * @return void
    **/
    public function plugin_path() {
        return untrailingslashit( plugin_dir_path( __FILE__ ) );
    }

    /**
     * includes all necessary class a functions file
     *
     * @since 2.9.5
     *
     * @return void
     */
    public function includes() {
        require_once DOKAN_WHOLESALE_INC_DIR . '/functions.php';

        if ( is_admin() ) {
            require_once DOKAN_WHOLESALE_INC_DIR . '/clas-admin.php';
        }

        // Load all helper functions
        require_once DOKAN_WHOLESALE_INC_DIR . '/class-customer.php';
        require_once DOKAN_WHOLESALE_INC_DIR . '/class-vendor.php';
        require_once DOKAN_WHOLESALE_INC_DIR . '/class-cart-checkout.php';
    }

    /**
     * Initiate all classes
     *
     * @since 2.9.5
     *
     * @return void
     */
    public function initiate() {
        if ( is_admin() ) {
            new \Dokan_Wholesale_Admin();
        }

        new \Dokan_Wholesale_Customer();
        new \Dokan_Wholesale_Vendor();
        new \Dokan_Wholesale_Cart_Checkout();
    }

    /**
     * Init all hooks
     *
     * @since 2.9.5
     *
     * @return void
     */
    public function hooks() {
        add_action( 'wp_enqueue_scripts', [ $this, 'load_scripts' ] );
        add_filter( 'dokan_set_template_path', [ $this, 'load_wholesale_templates' ], 10, 3 );
        add_filter( 'dokan_rest_api_class_map', [ $this, 'rest_api_class_map' ] );
        add_filter( 'dokan_frontend_localize_script', [ $this, 'add_localize_data' ] );
    }

    /**
     * Load scripts
     *
     * @since 2.9.5
     *
     * @return void
    */
    public function load_scripts() {
        global $wp, $post;

        if ( is_account_page() ) {
            wp_enqueue_script( 'dokan-wholesale-script', DOKAN_WHOLESALE_ASSETS_DIR . '/js/scripts.js', array( 'jquery' ), DOKAN_PLUGIN_VERSION, true );
        }

        if ( $post ) {
            $product = wc_get_product( $post->ID );

            if ( $product ) {
                wp_enqueue_script( 'dokan-wholesale-script', DOKAN_WHOLESALE_ASSETS_DIR . '/js/scripts.js', array( 'jquery' ), DOKAN_PLUGIN_VERSION, true );
                wp_localize_script( 'dokan-wholesale-script', 'DokanWholesale', [
                        'currency_symbol'   => get_woocommerce_currency_symbol(),
                        'check_permission'  => dokan_wholesale_can_see_price(),
                        'variation_wholesale_string' => apply_filters( 'dokan_variable_product_wholesale_string', [
                            'wholesale_price' => __( 'Wholesale Price', 'dokan' ),
                            'minimum_quantity' => __( 'Minimum Quantity', 'dokan' )
                        ] )
                    ]
                );
            }
        }
    }

    /**
     * Set template path for Wholesale
     *
     * @since 2.9.5
     *
     * @return void
     */
    public function load_wholesale_templates( $template_path, $template, $args ) {
        if ( isset( $args['is_wholesale'] ) && $args['is_wholesale'] ) {
            return $this->plugin_path() . '/templates';
        }

        return $template_path;
    }

    /**
     * REST API classes Mapping
     *
     * @since 2.9.5
     *
     * @return void
     */
    public function rest_api_class_map( $class_map ) {
        $class_map[ DOKAN_WHOLESALE_INC_DIR . '/api/class-wholesale-controller.php'] = 'Dokan_REST_Wholesale_Controller';

        return $class_map;
    }

    /**
     * Set some localize data for wholesales
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function add_localize_data( $data ) {
        $data['wholesale'] = [
            'activeStatusMessage' => __( 'You are succefully converted as a wholesale customer', 'dokan' ),
            'deactiveStatusMessage' => __( 'Your wholesale customer request send to the admin. Please wait for approval', 'dokan' ),
        ];

        return $data;
    }
}
