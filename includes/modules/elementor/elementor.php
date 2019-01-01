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
        add_action( 'elementor/init', [ $this, 'init' ] );
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
        \DokanPro\Modules\Elementor\Module::instance();
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
     * Default dynamic store data for widgets
     *
     * @since DOKAN_PRO_SINCE
     *
     * @param string $prop
     *
     * @return mixed
     */
    public function default_store_data( $prop = null ) {
        // Defaults are intentionally skipped from translating
        $data = [
            'banner'          => [
                'id'  => 0,
                'url' => DOKAN_PLUGIN_ASSEST . '/images/default-store-banner.png',
            ],
            'name'            => 'Store Name',
            'profile_picture' => [
                'id'  => 0,
                'url' => get_avatar_url( 0 ),
            ],
            'address'    => 'New York, United States (US)',
            'phone'      => '123-456-7890',
            'email'      => 'mail@store.com',
            'rating'     => '5 rating from 100 reviews',
            'open_close' => 'Store Opensss',
        ];

        /**
         * Filter to modify default
         *
         * @since DOKAN_PRO_SINCE
         *
         * @param array $data
         */
        $data = apply_filters( 'dokan_elementor_default_store_data_defaults', $data );

        $store = dokan()->vendor->get( get_query_var( 'author' ) );

        if ( ! $store->id ) {
            $use_last_store_data_in_builder = dokan_get_option( 'use_last_store_data_in_builder', 'dokan_elementor', true );

            if ( $use_last_store_data_in_builder ) {
                $store = dokan()->vendor->get_vendors( [
                    'number' => 1,
                ] );

                if ( ! empty( $store ) ) {
                    $store = array_pop( $store );
                }
            }
        }

        if ( $store->id ) {
            $banner_id = $store->get_info_part( 'banner' );

            if ( $banner_id ) {
                $data['banner'] = [
                    'id'  => $banner_id,
                    'url' => $store->get_banner(),
                ];
            }

            $data['name'] = $store->get_name();

            $profile_picture_id = $store->get_info_part( 'gravatar' );

            if ( $profile_picture_id ) {
                $data['profile_picture'] = [
                    'id'  => $profile_picture_id,
                    'url' => $store->get_avatar(),
                ];
            }

            $address = dokan_get_seller_short_address( $store->get_id(), false );

            if ( ! empty( $address ) ) {
                $data['address'] = $address;
            }

            $phone = $store->get_phone();

            if ( ! empty( $phone ) ) {
                $data['phone'] = $phone;
            }

            $email = $store->get_email();

            if ( ! empty( $email ) ) {
                $data['email'] = $email;
            }

            $rating = $store->get_readable_rating( false );

            if ( ! empty( $rating ) ) {
                $data['rating'] = $rating;
            }

            $store_info               = $store->get_shop_info();
            $dokan_store_time_enabled = isset( $store_info['dokan_store_time_enabled'] ) ? $store_info['dokan_store_time_enabled'] : '';
            $store_open_notice        = isset( $store_info['dokan_store_open_notice'] ) && ! empty( $store_info['dokan_store_open_notice'] ) ? $store_info['dokan_store_open_notice'] : __( 'Store Open', 'dokan-lite' );
            $store_closed_notice      = isset( $store_info['dokan_store_close_notice'] ) && ! empty( $store_info['dokan_store_close_notice'] ) ? $store_info['dokan_store_close_notice'] : __( 'Store Closed', 'dokan-lite' );
            $show_store_open_close    = dokan_get_option( 'store_open_close', 'dokan_general', 'on' );

            if ( $show_store_open_close == 'on' && $dokan_store_time_enabled == 'yes') {
                if ( dokan_is_store_open( $store->get_id() ) ) {
                    $data['open_close'] = esc_attr( $store_open_notice );
                } else {
                    $data['open_close'] = esc_attr( $store_closed_notice );
                }
            }

            /**
             * Filter to modify store data
             *
             * @since DOKAN_PRO_SINCE
             *
             * @param array $data
             */
            $data = apply_filters( 'dokan_elementor_default_store_data_store', $data );
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
