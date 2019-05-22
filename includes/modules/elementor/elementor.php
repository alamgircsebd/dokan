<?php
/*
* Plugin Name: Elementor
* Plugin URI: https://wedevs.com/products/plugins/dokan/
* Description: Elementor Page Builder widgets for Dokan
* Version: DOKAN_PRO_SINCE
* Author: weDevs
* Author URI: https://wedevs.com/
* Thumbnail Name: elementor.png
* License: GPL2
*/

/**
 * Copyright (c) 2016 weDevs (email: info@wedevs.com ). All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * **********************************************************************
 */

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) exit;

final class DokanElementor {

    /**
     * Module version
     *
     * @since DOKAN_PRO_SINCE
     *
     * @var string
     */
    public $version = 'DOKAN_PRO_SINCE';

    /**
     * Singleton class instance holder
     *
     * @since DOKAN_PRO_SINCE
     *
     * @var object
     */
    protected static $instance;

    /**
     * Make a class instance
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return object
     */
    public static function instance() {
        if ( ! isset( static::$instance ) && ! ( static::$instance instanceof static ) ) {
            static::$instance = new static();

            if ( method_exists( static::$instance, 'boot' ) ) {
                static::$instance->boot();
            }
        }

        return static::$instance;
    }

    /**
     * Exec after first instance has been created
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    public function boot() {
        add_action( 'admin_notices', [ $this, 'admin_notices' ] );
        add_action( 'elementor_pro/init', [ $this, 'init' ] );
    }

    /**
     * Load module
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    public function init() {
        $this->define_constants();
        $this->includes();
        $this->instances();
    }

    /**
     * Module constants
     *
     * @since DOKAN_PRO_SINCE
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
     * Include module related files
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    private function includes() {
        require_once DOKAN_ELEMENTOR_PATH . '/vendor/autoload.php';
    }

    /**
     * Create module related class instances
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    private function instances() {
        \DokanPro\Modules\Elementor\Templates::instance();
        \DokanPro\Modules\Elementor\StoreWPWidgets::instance();
        \DokanPro\Modules\Elementor\Module::instance();
    }

    /**
     * Show admin notices
     *
     * @since DOKAN_PRO_SINCE
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
     * @since DOKAN_PRO_SINCE
     *
     * @return \Elementor\Plugin
     */
    public function elementor() {
        return \Elementor\Plugin::instance();
    }

    /**
     * Is editing or preview mode running
     *
     * @since DOKAN_PRO_SINCE
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
     * @since DOKAN_PRO_SINCE
     *
     * @param string $prop
     *
     * @return mixed
     */
    public function get_store_data( $prop = null ) {
        $store_data = \DokanPro\Modules\Elementor\StoreData::instance();

        return $store_data->get_data( $prop );
    }

    /**
     * Social network name mapping to elementor icon names
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return array
     */
    public function get_social_networks_map() {
        $map = [
            'fb'        => 'fa fa-facebook',
            'gplus'     => 'fa fa-google-plus',
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

/**
 * Load Dokan Plugin when all plugins loaded
 *
 * @return \DokanElementor
 */
function dokan_elementor() {
    return DokanElementor::instance();
}

dokan_elementor();
