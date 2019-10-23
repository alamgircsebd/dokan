<?php
/*
Plugin Name: Product Addon
Plugin URI: http://wedevs.com/
Description: WooCommerce Product Addon support
Version: 1.0.0
Thumbnail Name: ajax-live-search.png
Author: weDevs
Author URI: http://wedevs.com/
License: GPL2
*/

/**
 * Copyright (c) 2014 weDevs (email: info@wedevs.com). All rights reserved.
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

/**
 * Dokan_Product_Addon class
 *
 * @class Dokan_Product_Addon The class that holds the entire Dokan_Product_Addon plugin
 */
class Dokan_Product_Addon {

    /**
     * The plugins which are dependent for this plugin
     *
     * @since 1.0.0
     *
     * @var array
     */
    private $depends_on = array();

    /**
     * Displa dependency error if not present
     *
     * @since 1.0.0
     *
     * @var array
     */
    private $dependency_error = array();

    /**
     * Constructor for the Dokan_Product_Addon class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @uses is_admin()
     * @uses add_action()
     */
    public function __construct() {

        $this->depends_on['WC_Product_Addons'] = array(
            'name'   => 'WC_Product_Addons',
            'notice' => sprintf( __( '<b>Dokan Product Addon </b> requires %sWooCommerce Product addons plugin%s to be installed & activated first !' , 'dokan' ), '<a target="_blank" href="https://woocommerce.com/products/product-add-ons/">', '</a>' ),
        );

        if ( ! $this->check_if_has_dependency() ) {
            add_action( 'admin_notices', array ( $this, 'dependency_notice' ) );
            return;
        }

        $this->define();

        $this->includes();

        $this->initiate();

        $this->hooks();
    }

    /**
     * Initializes the Dokan_Product_Addon() class
     *
     * Checks for an existing Dokan_Product_Addon() instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new Dokan_Product_Addon();
        }

        return $instance;
    }

    /**
     * hooks
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function define() {
        define( 'DOKAN_PRODUCT_ADDON_DIR', dirname( __FILE__ ) );
        define( 'DOKAN_PRODUCT_ADDON_INC_DIR', DOKAN_PRODUCT_ADDON_DIR . '/includes' );
        define( 'DOKAN_PRODUCT_ADDON_ASSETS_DIR', plugins_url( 'assets', __FILE__ ) );
    }

    /**
    * Get plugin path
    *
    * @since 1.5.1
    *
    * @return void
    **/
    public function plugin_path() {
        return untrailingslashit( plugin_dir_path( __FILE__ ) );
    }

    /**
     * Includes all necessary class a functions file
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function includes() {
        require_once DOKAN_PRODUCT_ADDON_INC_DIR . '/class-frontend.php';
        require_once DOKAN_PRODUCT_ADDON_INC_DIR . '/class-vendor-product.php';

        // Load all helper functions
        require_once DOKAN_PRODUCT_ADDON_INC_DIR . '/functions.php';
    }

    /**
     * Initiate all classes
     *
     * @return void
     */
    public function initiate() {
        Dokan_Product_Addon_Frontend::init();
        Dokan_Product_Addon_Vendor_Product::init();
    }

     /**
     * Init all hooks
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function hooks() {
        add_action( 'wp_enqueue_scripts', [ $this, 'load_scripts' ] );
    }

    /**
     * Load global scripts
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function load_scripts() {
        global $wp;

        if ( isset( $wp->query_vars['settings'] ) && $wp->query_vars['settings'] == 'product-addon' ) {
            $this->enqueue_scripts();
        }

        if ( get_query_var( 'edit' ) && is_singular( 'product' ) ) {
            $this->enqueue_scripts();
        }
    }

    /**
     * Print error notice if dependency not active
     *
     * @since 1.0.0
     */
    function dependency_notice(){
        $errors = '';
        $error = '';
        foreach ( $this->dependency_error as $error ) {
            $errors .= '<p>' . $error . '</p>';
        }
        $message = '<div class="error">' . $errors . '</div>';

        echo $message;
    }

    /**
     * Check whether is their has any dependency or not
     *
     * @return boolean
     */
    function check_if_has_dependency() {
        $res = true;

        foreach ( $this->depends_on as $class ) {
            if ( ! class_exists( $class['name'] ) ) {
                $this->dependency_error[] = $class['notice'];
                $res = false;
            }
        }

        return $res;
    }

    /**
     * Enqueue scripts
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function enqueue_scripts() {
        wp_enqueue_style( 'dokan-pa-style', DOKAN_PRODUCT_ADDON_ASSETS_DIR . '/css/main.css', false , DOKAN_PLUGIN_VERSION, 'all' );
        wp_enqueue_script( 'dokan-pa-script', DOKAN_PRODUCT_ADDON_ASSETS_DIR . '/js/scripts.js', array( 'jquery' ), DOKAN_PLUGIN_VERSION, true );
        wp_enqueue_script( 'dokan-pa-addons-script', DOKAN_PRODUCT_ADDON_ASSETS_DIR . '/js/addons.js', array( 'jquery', 'dokan-pa-script' ), DOKAN_PLUGIN_VERSION, true );
        $params = array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => array(
                'get_addon_options' => wp_create_nonce( 'wc-pao-get-addon-options' ),
                'get_addon_field'   => wp_create_nonce( 'wc-pao-get-addon-field' ),
            ),
            'i18n'     => array(
                'required_fields'       => __( 'All fields must have a title and/or option name. Please review the settings highlighted in red border.', 'dokan' ),
                'limit_price_range'         => __( 'Limit price range', 'dokan' ),
                'limit_quantity_range'      => __( 'Limit quantity range', 'dokan' ),
                'limit_character_length'    => __( 'Limit character length', 'dokan' ),
                'restrictions'              => __( 'Restrictions', 'dokan' ),
                'confirm_remove_addon'      => __( 'Are you sure you want remove this add-on field?', 'dokan' ),
                'confirm_remove_option'     => __( 'Are you sure you want delete this option?', 'dokan' ),
                'add_image_swatch'          => __( 'Add Image Swatch', 'dokan' ),
                'add_image'                 => __( 'Add Image', 'dokan' ),
            ),
        );

        wp_localize_script( 'jquery', 'wc_pao_params', apply_filters( 'wc_pao_params', $params ) );
    }

}

add_action( 'plugins_loaded', 'dokan_pa_load', 30 );

function dokan_pa_load() {
    $dokan_product_addon = Dokan_Product_Addon::init();
}

