<?php

/**
 * Scripts and Styles Class
 */
class Dokan_Pro_Assets {

    function __construct() {

        if ( is_admin() ) {
            add_action( 'admin_enqueue_scripts', [ $this, 'register' ], 5 );
            add_action( 'dokan-vue-admin-scripts', [ $this, 'enqueue_admin_scripts' ] );
        } else {
            add_action( 'wp_enqueue_scripts', [ $this, 'register' ], 5 );
            add_action( 'dokan_enqueue_scripts', [ $this, 'enqueue_frontend_scripts' ], 5 );
        }
    }

    public function enqueue_admin_scripts() {
        wp_enqueue_script( 'dokan-pro-vue-admin' );
        wp_enqueue_style( 'dokan-pro-vue-admin' );
    }

    /**
     * Enqueue forntend scripts
     *
     * @since 2.8.0
     *
     * @return void
     */
    public function enqueue_frontend_scripts() {
        global $wp;

        if ( isset( $wp->query_vars['settings'] ) && $wp->query_vars['settings'] == 'shipping' ) {
            wp_enqueue_style( 'dokan-vue-bootstrap' );
            wp_enqueue_style( 'dokan-pro-vue-frontend-shipping' );
            wp_enqueue_script( 'dokan-pro-vue-frontend-shipping' );

            $localize_array = array(
                'nonce'             => wp_create_nonce( 'dokan_shipping_nonce' ),
                'allowed_countries' => WC()->countries->get_allowed_countries(),
                'continents'        => WC()->countries->get_continents(),
                'states'            => WC()->countries->get_states(),
                'shipping_class'    => WC()->shipping->get_shipping_classes(),
                'i18n'              => array(
                    'zone_name'                     => __( 'Zone Name', 'dokan' ),
                    'zone_location'                 => __( 'Zone Location', 'dokan' ),
                    'limit_zone_location'           => __( 'Limit your zone location', 'dokan' ),
                    'select_country'                => __( 'Select Country', 'dokan' ),
                    'select_state'                  => __( 'Select States', 'dokan' ),
                    'select_postcode'               => __( 'Set your postcode', 'dokan' ),
                    'shipping_method'               => __( 'Shipping Method', 'dokan' ),
                    'shipping_method_help'          => __( 'Add your shipping method for appropiate zone', 'dokan' ),
                    'method_title'                  => __( 'Method Title', 'dokan' ),
                    'enabled'                       => __( 'Enabled', 'dokan' ),
                    'status'                        => __( 'Status', 'dokan' ),
                    'description'                   => __( 'Description', 'dokan' ),
                    'edit'                          => __( 'Edit', 'dokan' ),
                    'delete'                        => __( 'Delete', 'dokan' ),
                    'no_method_found'               => __( 'No method found', 'dokan' ),
                    'add_method'                    => __( 'Add Shipping Method', 'dokan' ),
                    'select_method'                 => __( 'Select a Method', 'dokan' ),
                    'choose_shipping_help_text'     => __( 'Choose the shipping method you wish to add. Only shipping methods which support zones are listed.', 'dokan' ),
                    'flat_rate'                     => __( 'Flat Rate', 'dokan' ),
                    'local_pickup'                  => __( 'Local Pickup', 'dokan' ),
                    'free_pickup'                   => __( 'Free Shipping', 'dokan' ),
                    'title'                         => __( 'Title', 'dokan' ),
                    'cost'                          => __( 'Cost', 'dokan' ),
                    'tax_status'                    => __( 'Tax Status', 'dokan' ),
                    'none'                          => __( 'None', 'dokan' ),
                    'taxable'                       => __( 'Taxable', 'dokan' ),
                    'shipping_class_cost'           => __( 'Shipping Class Cost', 'dokan' ),
                    'shipping_class_cost_help_text' => __( 'These costs can optionally be added based on the', 'dokan' ),
                    'no_shipping_class_cost'        => __( 'No shipping class cost', 'dokan' ),
                    'no_shipping_zone_found'        => __( 'No shipping zone found for configuration. Please contact with admin for manage your store shipping', 'dokan' ),
                    'calculation_type'              => __( 'Calculation type', 'dokan' ),
                    'per_class'                     => __( 'Per class: Charge shipping for each shipping class individually', 'dokan' ),
                    'per_order'                     => __( 'Per order: Charge shipping for the most expensive shipping class', 'dokan' ),
                    'save_settings'                 => __( 'Save Settings', 'dokan' ),
                    'regions'                       => __( 'Region(s)', 'dokan' ),
                    'cost_desc'                     =>  __( 'Enter a cost (excl. tax) or sum, e.g. <code>10.00 * [qty]</code>. Use <code>[qty]</code> for the number of items, <code>[cost]</code> for the total cost of items, and <code>[fee percent="10" min_fee="20" max_fee=""]</code> for percentage based fees.', 'dokan' ),
                    'free_shipping_requires'        =>  __( 'Free shipping requires...', 'dokan' ),
                    'coupon'                        =>  __( 'A valid free shipping coupon', 'dokan' ),
                    'minimum_order'                 =>  __( 'A minimum order amount', 'dokan' ),
                    'minimum_order_and_coupon'      =>  __( 'A minimum order amount AND a coupon', 'dokan' ),
                    'minimum_order_or_coupon'       =>  __( 'A minimum order amount OR a coupon', 'dokan' ),
                    'minimum_order_amount'          =>  __( 'Minimum order amount for free shipping', 'dokan' ),
                    'postcode_help_text'            =>  __( 'Postcodes need to be comma separated', 'dokan' )
                )
            );

            wp_localize_script( 'dokan-pro-vue-frontend-shipping', 'dokanShipping', $localize_array );
        }


        // wp_enqueue_script( 'dokan-pro-vue-frontend' );
        // wp_enqueue_style( 'dokan-pro-vue-frontend' );
    }

    /**
     * Register our app scripts and styles
     *
     * @return void
     */
    public function register() {
        $this->register_scripts( $this->get_scripts() );
        $this->register_styles( $this->get_styles() );
    }

    /**
     * Register scripts
     *
     * @param  array $scripts
     *
     * @return void
     */
    private function register_scripts( $scripts ) {
        foreach ( $scripts as $handle => $script ) {
            $deps      = isset( $script['deps'] ) ? $script['deps'] : false;
            $in_footer = isset( $script['in_footer'] ) ? $script['in_footer'] : false;
            $version   = isset( $script['version'] ) ? $script['version'] : DOKAN_PRO_PLUGIN_VERSION;

            wp_register_script( $handle, $script['src'], $deps, $version, $in_footer );
        }
    }

    /**
     * Register styles
     *
     * @param  array $styles
     *
     * @return void
     */
    public function register_styles( $styles ) {
        foreach ( $styles as $handle => $style ) {
            $deps    = isset( $style['deps'] ) ? $style['deps'] : false;
            $version = isset( $style['version'] ) ? $style['version'] : DOKAN_PRO_PLUGIN_VERSION;

            wp_register_style( $handle, $style['src'], $deps, $version );
        }
    }

    /**
     * Get all registered scripts
     *
     * @return array
     */
    public function get_scripts() {
        $prefix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '.min' : '';

        $scripts = [
            'dokan-pro-vue-admin' => [
                'src'       => DOKAN_PRO_PLUGIN_ASSEST . '/js/vue-pro-admin.js',
                'deps'      => [ 'jquery', 'dokan-vue-vendor', 'dokan-vue-bootstrap' ],
                'version'   => filemtime( DOKAN_PRO_DIR . '/assets/js/vue-pro-admin.js' ),
                'in_footer' => true
            ],

            'dokan-pro-vue-frontend' => [
                'src'       => DOKAN_PRO_PLUGIN_ASSEST . '/js/vue-pro-frontend.js',
                'deps'      => [ 'jquery', 'dokan-vue-vendor', 'dokan-vue-bootstrap' ],
                'version'   => filemtime( DOKAN_PRO_DIR . '/assets/js/vue-pro-frontend.js' ),
                'in_footer' => true
            ],

            'dokan-pro-vue-frontend-shipping' => [
                'src'       => DOKAN_PRO_PLUGIN_ASSEST . '/js/vue-pro-frontend-shipping.js',
                'deps'      => [ 'jquery', 'dokan-vue-vendor', 'dokan-vue-bootstrap' ],
                'version'   => filemtime( DOKAN_PRO_DIR . '/assets/js/vue-pro-frontend-shipping.js' ),
                'in_footer' => true
            ],
        ];

        return $scripts;
    }

    /**
     * Get registered styles
     *
     * @return array
     */
    public function get_styles() {

        $styles = [
            'dokan-pro-vue-admin' => [
                'src'     =>  DOKAN_PRO_PLUGIN_ASSEST . '/css/vue-pro-admin.css',
                'version' => filemtime( DOKAN_PRO_DIR . '/assets/css/vue-pro-admin.css' ),
            ],
            'dokan-pro-vue-frontend' => [
                'src'     =>  DOKAN_PRO_PLUGIN_ASSEST . '/css/vue-pro-frontend.css',
                'version' => time() //filemtime( DOKAN_PRO_DIR . '/assets/css/vue-pro-frontend.css' ),
            ],
            'dokan-pro-vue-frontend-shipping' => [
                'src'     =>  DOKAN_PRO_PLUGIN_ASSEST . '/css/vue-pro-frontend-shipping.css',
                'version' => filemtime( DOKAN_PRO_DIR . '/assets/css/vue-pro-frontend-shipping.css' ),
            ],
        ];

        return $styles;
    }

}
