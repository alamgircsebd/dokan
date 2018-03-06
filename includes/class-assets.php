<?php

/**
 * Scripts and Styles Class
 */
class Dokan_Pro_Assets {

    function __construct() {

        if ( is_admin() ) {
            add_action( 'admin_enqueue_scripts', [ $this, 'register' ], 5 );
        } else {
            add_action( 'wp_enqueue_scripts', [ $this, 'register' ], 5 );
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
        $prefix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '.min' : '';

        $scripts = [
            'dokan-vue-vendor' => [
                'src'       => DOKAN_PRO_PLUGIN_ASSEST . '/js/vendor.js',
                'deps'      => [ 'jquery' ],
                'version'   => filemtime( DOKAN_PRO_DIR . '/assets/js/vendor.js' ),
                'in_footer' => true
            ],
            'dokan-vue-admin' => [
                'src'       => DOKAN_PRO_PLUGIN_ASSEST . '/js/vueAdmin.js',
                'deps'      => [ 'jquery', 'dokan-vue-vendor' ],
                'version'   => filemtime( DOKAN_PRO_DIR . '/assets/js/vueAdmin.js' ),
                'in_footer' => true
            ]
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
            'dokan-vue-admin' => [
                'src'     =>  DOKAN_PRO_PLUGIN_ASSEST . '/css/vueAdmin.css',
                'version' => filemtime( DOKAN_PRO_DIR . '/assets/css/vueAdmin.css' ),
            ]
        ];

        return $styles;
    }

}
