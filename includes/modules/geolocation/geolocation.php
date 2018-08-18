<?php
/*
Plugin Name: Geolocation
Plugin URI: https://wedevs.com/products/plugins/dokan/
Description: Search Products and Vendors by geolocation.
Version: 1.0.0
Author: weDevs
Author URI: https://wedevs.com/
Thumbnail Name: geolocation.png
License: GPL2
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

class Dokan_Geolocation {

    /**
     * Module version
     *
     * @var string
     *
     * @since 1.0.0
     */
    public $version = '1.0.0';

    /**
     * Class constructor
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function __construct() {
        $this->define_constants();
        $this->includes();
        $this->hooks();
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
        define( 'DOKAN_GEOLOCATION_VERSION' , $this->version );
        define( 'DOKAN_GEOLOCATION_PATH' , dirname( __FILE__ ) );
        define( 'DOKAN_GEOLOCATION_URL' , plugins_url( '', __FILE__ ) );
        define( 'DOKAN_GEOLOCATION_ASSETS' , DOKAN_GEOLOCATION_URL . '/assets' );
        define( 'DOKAN_GEOLOCATION_VIEWS', DOKAN_GEOLOCATION_PATH . '/views' );
    }

    /**
     * Add action and filter hooks
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function hooks() {
        add_action( 'dokan_store_profile_saved', array( $this, 'save_vendor_geodata' ), 10, 2 );
        add_action( 'dokan_product_edit_after_options', array( $this, 'add_product_editor_options' ) );
        add_action( 'dokan_product_updated', array( $this, 'update_product_settings' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'widgets_init', array( $this, 'register_widget' ) );
    }

    /**
     * Include module related files
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function includes() {
        require_once DOKAN_GEOLOCATION_PATH . '/functions.php';
        require_once DOKAN_GEOLOCATION_PATH . '/class-dokan-geolocation-scripts.php';
        require_once DOKAN_GEOLOCATION_PATH . '/class-dokan-geolocation-shortcode.php';
        require_once DOKAN_GEOLOCATION_PATH . '/class-dokan-geolocation-widget-filters.php';
        require_once DOKAN_GEOLOCATION_PATH . '/class-dokan-geolocation-widget-product-location.php';
        require_once DOKAN_GEOLOCATION_PATH . '/class-dokan-geolocation-product-query.php';
        require_once DOKAN_GEOLOCATION_PATH . '/class-dokan-geolocation-product-view.php';
        require_once DOKAN_GEOLOCATION_PATH . '/class-dokan-geolocation-vendor-query.php';
        require_once DOKAN_GEOLOCATION_PATH . '/class-dokan-geolocation-vendor-view.php';
        require_once DOKAN_GEOLOCATION_PATH . '/class-geolocation-single-product.php';
        require_once DOKAN_GEOLOCATION_PATH . '/class-geolocation-admin-settings.php';
    }

    /**
     * Create module related class instances
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function instances() {
        new Dokan_Geolocation_Scripts();
        new Dokan_Geolocation_Shortcode();
        new Dokan_Geolocation_Product_Query();
        new Dokan_Geolocation_Product_View();
        new Dokan_Geolocation_Vendor_Query();
        new Dokan_Geolocation_Vendor_View();
        new Dokan_Geolocation_Single_Product();
        new Dokan_Geolocation_Admin_Settings();
    }

    /**
     * Use store settings option
     *
     * @since 1.0.0
     *
     * @param int $post_id
     *
     * @return string
     */
    public function use_store_settings( $post_id ) {
        $use_store_settings = get_post_meta( $post_id, '_dokan_geolocation_use_store_settings', true );

        if ( empty( $use_store_settings ) || 'yes' === $use_store_settings ) {
            return 'yes';
        } else {
            return 'no';
        }
    }

    /**
     * Save vendor geodata
     *
     * @since 1.0.0
     *
     * @param int   $store_id
     * @param array $dokan_settings
     *
     * @return void
     */
    public function save_vendor_geodata( $store_id, $dokan_settings ) {
        if ( isset( $dokan_settings['location'] ) && isset( $dokan_settings['find_address'] ) ) {
            $location = explode( ',', $dokan_settings['location'] );

            if ( 2 !== count( $location ) ) {
                return;
            }

            update_usermeta( $store_id, 'geo_latitude', $location[0] );
            update_usermeta( $store_id, 'geo_longitude', $location[1] );
            update_usermeta( $store_id, 'geo_public', 1 );
            update_usermeta( $store_id, 'geo_address', $dokan_settings['find_address'] );
        }
    }

    /**
     * Add product editor options/settings
     *
     * @since 1.0.0
     *
     * @param int $post_id
     *
     * @return void
     */
    public function add_product_editor_options( $post_id ) {
        $use_store_settings = $this->use_store_settings( $post_id );

        if ( ! $use_store_settings ) {
            $store_id      = dokan_get_current_user_id();
            $geo_latitude  = get_user_meta( $store_id, 'geo_latitude', true );
            $geo_longitude = get_user_meta( $store_id, 'geo_longitude', true );
            $geo_public    = get_user_meta( $store_id, 'geo_public', true );
            $geo_address   = get_user_meta( $store_id, 'geo_address', true );

        } else {
            $geo_latitude  = get_post_meta( $post_id, 'geo_latitude', true );
            $geo_longitude = get_post_meta( $post_id, 'geo_longitude', true );
            $geo_public    = get_post_meta( $post_id, 'geo_public', true );
            $geo_address   = get_post_meta( $post_id, 'geo_address', true );
        }

        if ( ! $geo_latitude || ! $geo_longitude ) {
            $default_locations = dokan_geo_get_default_location();
            $geo_latitude  = $default_locations['latitude'];
            $geo_longitude = $default_locations['longitude'];
        }

        $args = array(
            'post_id'            => $post_id,
            'use_store_settings' => $use_store_settings,
            'geo_latitude'       => $geo_latitude,
            'geo_longitude'      => $geo_longitude,
            'geo_public'         => $geo_public,
            'geo_address'        => $geo_address,
        );

        dokan_get_template( 'product-editor-options.php', $args, DOKAN_GEOLOCATION_VIEWS, trailingslashit( DOKAN_GEOLOCATION_VIEWS ) );
    }

    /**
     * Update product settings
     *
     * @since 1.0.0
     *
     * @param int $post_id
     *
     * @return void
     */
    public function update_product_settings( $post_id ) {
        $store_id      = dokan_get_current_user_id();
        $geo_latitude  = get_user_meta( $store_id, 'geo_latitude', true );
        $geo_longitude = get_user_meta( $store_id, 'geo_longitude', true );
        $geo_public    = get_user_meta( $store_id, 'geo_public', true );
        $geo_address   = get_user_meta( $store_id, 'geo_address', true );

        $use_store_settings = ( 'yes' === $_POST['_dokan_geolocation_use_store_settings'] ) ? 'yes' : 'no';

        update_post_meta( $post_id, '_dokan_geolocation_use_store_settings', $use_store_settings );

        if ( 'yes' !== $use_store_settings ) {
            $geo_latitude  = ! empty( $_POST['_dokan_geolocation_product_geo_latitude'] ) ? $_POST['_dokan_geolocation_product_geo_latitude'] : null;
            $geo_longitude = ! empty( $_POST['_dokan_geolocation_product_geo_longitude'] ) ? $_POST['_dokan_geolocation_product_geo_longitude'] : null;
            $geo_address   = ! empty( $_POST['_dokan_geolocation_product_geo_address'] ) ? $_POST['_dokan_geolocation_product_geo_address'] : null;
        }

        update_post_meta( $post_id, 'geo_latitude', $geo_latitude );
        update_post_meta( $post_id, 'geo_longitude', $geo_longitude );
        update_post_meta( $post_id, 'geo_public', $geo_public );
        update_post_meta( $post_id, 'geo_address', $geo_address );
    }

    /**
     * Enqueue module scripts
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function enqueue_scripts() {
        wp_enqueue_style( 'dokan-geolocation', DOKAN_GEOLOCATION_ASSETS . '/css/geolocation.css', array(), $this->version );
        wp_enqueue_script( 'dokan-geolocation', DOKAN_GEOLOCATION_ASSETS . '/js/geolocation.js', array( 'jquery', 'google-maps' ), $this->version, true );
    }

    public function register_widget() {
        register_widget( 'Dokan_Geolocation_Widget_Filters' );
        register_widget( 'Dokan_Geolocation_Widget_Product_Location' );
    }
}

new Dokan_Geolocation();
