<?php

namespace WeDevs\DokanPro;

/**
 * Scripts and Styles Class
 */
class Assets {

    private $script_version;

    public function __construct() {
        $this->script_version = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? time() : DOKAN_PRO_PLUGIN_VERSION;

        if ( is_admin() ) {
            add_action( 'admin_enqueue_scripts', [ $this, 'register' ], 5 );
            add_action( 'dokan-vue-admin-scripts', [ $this, 'enqueue_admin_scripts' ] );
        } else {
            add_action( 'wp_enqueue_scripts', [ $this, 'register' ], 5 );
            add_action( 'dokan_enqueue_scripts', [ $this, 'enqueue_frontend_scripts' ], 5 );
        }
    }

    /**
     * Enqueue admin scripts
     *
     * @return void
     */
    public function enqueue_admin_scripts() {
        global $wp_version;

        wp_enqueue_style( 'dokan-pro-vue-admin' );
        wp_enqueue_style( 'woocommerce_select2', WC()->plugin_url() . '/assets/css/select2.css', [], WC_VERSION );
        wp_enqueue_script( 'dokan-pro-vue-admin' );

        if ( version_compare( $wp_version, '5.3', '<' ) ) {
            wp_enqueue_style( 'dokan-pro-wp-version-before-5-3' );
        }
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
                'i18n'              => array( 'dokan' => dokan_get_jed_locale_data( 'dokan' ) ),
                'processing_time'   => dokan_get_shipping_processing_times()
            );

            wp_localize_script( 'dokan-pro-vue-frontend-shipping', 'dokanShipping', $localize_array );
        }
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
        $scripts = [
            'dokan-pro-vue-admin' => [
                'src'       => DOKAN_PRO_PLUGIN_ASSEST . '/js/vue-pro-admin.js',
                'deps'      => [ 'jquery', 'dokan-vue-vendor', 'dokan-vue-bootstrap', 'selectWoo' ],
                'version'   => $this->script_version,
                'in_footer' => true
            ],

            'dokan-pro-vue-frontend-shipping' => [
                'src'       => DOKAN_PRO_PLUGIN_ASSEST . '/js/vue-pro-frontend-shipping.js',
                'deps'      => [ 'jquery', 'dokan-vue-vendor', 'dokan-vue-bootstrap' ],
                'version'   => $this->script_version,
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
                'version' => $this->script_version,
            ],
            'dokan-pro-vue-frontend-shipping' => [
                'src'     =>  DOKAN_PRO_PLUGIN_ASSEST . '/css/vue-pro-frontend-shipping.css',
                'version' => $this->script_version,
            ],
            'dokan-pro-wp-version-before-5-3' => [
                'src'     =>  DOKAN_PRO_PLUGIN_ASSEST . '/css/wp-version-before-5-3.css',
                'version' => $this->script_version,
            ],
        ];

        return $styles;
    }
}
