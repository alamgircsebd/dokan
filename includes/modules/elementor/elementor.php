<?php
/*
* Plugin Name: Elementor
* Plugin URI: https://wedevs.com/products/plugins/dokan/
* Description: Elementor Page Builder widgets for Dokan
* Version: 1.0.0
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
     * @since 1.0.0
     *
     * @var string
     */
    public $version = '1.0.0';

    /**
     * Singleton class instance holder
     *
     * @since 1.0.0
     *
     * @var object
     */
    protected static $instance;

    /**
     * Make a class instance
     *
     * @since 1.0.0
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
     * @since 1.0.0
     *
     * @return void
     */
    public function boot() {
        add_action( 'elementor/init', [ $this, 'init' ] );
    }

    /**
     * Load module
     *
     * @since 1.0.0
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
     * @since 1.0.0
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
     * @since 1.0.0
     *
     * @return void
     */
    private function includes() {
        require_once DOKAN_ELEMENTOR_PATH . '/vendor/autoload.php';
    }

    /**
     * Create module related class instances
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function instances() {
        \DokanPro\Modules\Elementor\Module::instance();
    }

    /**
     * Elementor\Plugin instance
     *
     * @since 1.0.0
     *
     * @return \Elementor\Plugin
     */
    public function elementor() {
        return \Elementor\Plugin::instance();
    }

    /**
     * Default dynamic store data for widgets
     *
     * @since 1.0.0
     *
     * @param string $prop
     *
     * @return mixed
     */
    public function default_store_data( $prop = null ) {
        $data = [
            'name'            => __( 'Store Name', 'dokan' ),
            'profile_picture' => [
                'id'  => 0,
                'url' => get_avatar_url( 0 ),
            ]
        ];

        $use_last_store_data_in_builder = dokan_get_option( 'use_last_store_data_in_builder', 'dokan_elementor', true );

        if ( $use_last_store_data_in_builder ) {
            $store = dokan()->vendor->get_vendors( [
                'number' => 1,
            ] );

            if ( ! empty( $store ) ) {
                $store = array_pop( $store );

                $data['name'] = $store->get_name();
                $data['profile_picture'] = [
                    'id' => $store->get_info_part( 'gravatar' ),
                    'url' => $store->get_avatar(),
                ];
            }
        }

        return ( $prop && isset( $data[ $prop ] ) ) ? $data[ $prop ] : $data;
    }
}

/**
 * Load Dokan Plugin when all plugins loaded
 *
 * @return void
 */
function dokan_elementor() {
    return DokanElementor::instance();
}

dokan_elementor();
