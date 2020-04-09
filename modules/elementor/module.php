<?php

namespace WeDevs\DokanPro\Modules\Elementor;

final class Module {

    /**
     * Module version
     *
     * @since 2.9.11
     *
     * @var string
     */
    public $version = '2.9.11';

    /**
     * Exec after first instance has been created
     *
     * @since 2.9.11
     *
     * @return void
     */
    public function __construct() {
        add_action( 'admin_notices', [ $this, 'admin_notices' ] );
        add_action( 'elementor_pro/init', [ $this, 'init' ] );
    }

    /**
     * Load module
     *
     * @since 2.9.11
     *
     * @return void
     */
    public function init() {
        $this->define_constants();
        $this->instances();
    }

    /**
     * Module constants
     *
     * @since 2.9.11
     *
     * @return void
     */
    private function define_constants() {
        define( 'DOKAN_ELEMENTOR_VERSION' , $this->version );
        define( 'DOKAN_ELEMENTOR_FILE' , __FILE__ );
        define( 'DOKAN_ELEMENTOR_PATH' , dirname( DOKAN_ELEMENTOR_FILE ) );
        define( 'DOKAN_ELEMENTOR_INCLUDES' , DOKAN_ELEMENTOR_PATH . '/includes' );
        define( 'DOKAN_ELEMENTOR_URL' , plugins_url( '', DOKAN_ELEMENTOR_FILE ) );
        define( 'DOKAN_ELEMENTOR_ASSETS' , DOKAN_ELEMENTOR_URL . '/assets' );
        define( 'DOKAN_ELEMENTOR_VIEWS', DOKAN_ELEMENTOR_PATH . '/views' );
    }

    /**
     * Create module related class instances
     *
     * @since 2.9.11
     *
     * @return void
     */
    private function instances() {
        \WeDevs\DokanPro\Modules\Elementor\Templates::instance();
        \WeDevs\DokanPro\Modules\Elementor\StoreWPWidgets::instance();
        \WeDevs\DokanPro\Modules\Elementor\Bootstrap::instance();
    }

    /**
     * Show admin notices
     *
     * @since 2.9.11
     *
     * @return 1.0.0
     */
    public function admin_notices() {
        $notice = '';

        if ( ! class_exists( '\Elementor\Plugin' ) || ! class_exists( '\ElementorPro\Plugin' ) ) {
            $notice = sprintf(
                __( 'Dokan Elementor module requires both %s and %s to be activated', 'dokan' ),
                '<strong>Elementor</strong>',
                '<strong>Elementor Pro</strong>'
            );
        }

        if ( defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION , '2.5.15', '<' ) ) {
            $notice = sprintf(
                __( 'Dokan Elementor module requires atleast %s.', 'dokan' ),
                '<strong>Elementor v2.5.15</strong>'
            );
        } else if ( defined( 'ELEMENTOR_PRO_VERSION' ) && version_compare( ELEMENTOR_PRO_VERSION , '2.5.3', '<' ) ) {
            $notice = sprintf(
                __( 'Dokan Elementor module requires atleast %s.', 'dokan' ),
                '<strong>Elementor Pro v2.5.3</strong>'
            );
        }

        if ( $notice ) {
            printf( '<div class="error"><p>' . $notice . '</p></div>' );
        }
    }

    /**
     * Elementor\Plugin instance
     *
     * @since 2.9.11
     *
     * @return \Elementor\Plugin
     */
    public function elementor() {
        return \Elementor\Plugin::instance();
    }

    /**
     * Is editing or preview mode running
     *
     * @since 2.9.11
     *
     * @return bool
     */
    public function is_edit_or_preview_mode() {
        $is_edit_mode    = $this->elementor()->editor->is_edit_mode();
        $is_preview_mode = $this->elementor()->preview->is_preview_mode();

        if ( empty( $is_edit_mode ) && empty( $is_preview_mode ) ) {
            if ( ! empty( $_REQUEST['action'] ) && ! empty( $_REQUEST['editor_post_id'] ) ) {
                $is_edit_mode = true;
            } else if ( ! empty( $_REQUEST['preview'] ) && $_REQUEST['preview'] && ! empty( $_REQUEST['theme_template_id'] ) ) {
                $is_preview_mode = true;
            }
        }

        if ( $is_edit_mode || $is_preview_mode ) {
            return true;
        }

        return false;
    }

    /**
     * Default dynamic store data for widgets
     *
     * @since 2.9.11
     *
     * @param string $prop
     *
     * @return mixed
     */
    public function get_store_data( $prop = null ) {
        $store_data = \WeDevs\DokanPro\Modules\Elementor\StoreData::instance();

        return $store_data->get_data( $prop );
    }

    /**
     * Social network name mapping to elementor icon names
     *
     * @since 2.9.11
     *
     * @return array
     */
    public function get_social_networks_map() {
        $map = [
            'fb'        => 'fa fa-facebook',
            'gplus'     => 'fa fa-google',
            'twitter'   => 'fa fa-twitter',
            'pinterest' => 'fa fa-pinterest',
            'linkedin'  => 'fa fa-linkedin',
            'youtube'   => 'fa fa-youtube',
            'instagram' => 'fa fa-instagram',
            'flickr'    => 'fa fa-flickr',
        ];

        return apply_filters( 'dokan_elementor_social_network_map', $map );
    }
}
