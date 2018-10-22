<?php
/*
  Plugin Name: Vendor Analytics
  Plugin URI: https://wedevs.com/
  Description: A plugin for store and product analytics for vendor
  Version: 1.0
  Author: weDevs
  Author URI: https://wedevs.com/
  Thumbnail Name: analytics.png
  License: GPL2
 */

/**
 * Copyright (c) 2017 weDevs (email: info@wedevs.com). All rights reserved.
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
if ( !defined( 'ABSPATH' ) )
    exit;

/**
 * Dokan_Vendor_Analytics class
 *
 * @class Dokan_Vendor_Analytics The class that holds the entire Dokan_Vendor_Analytics plugin
 */
class Dokan_Vendor_Analytics {

    /**
     * Constructor for the Dokan_Vendor_Analytics class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @uses is_admin()
     * @uses add_action()
     */
    public function __construct() {
        $this->define_constant();
        $this->includes();
        $this->initiate();

        add_filter( 'dokan_get_dashboard_nav', array( $this, 'add_analytics_page' ), 15 );
        add_filter( 'dokan_query_var_filter', array( $this, 'add_endpoint' ) );
        add_action( 'dokan_load_custom_template', array( $this, 'load_analytics_template' ), 16 );
        add_filter( 'dokan_set_template_path', array( $this, 'load_vendor_analytics_templates' ), 11, 3 );
        add_action( 'dokan_analytics_content_area_header', array( $this, 'analytics_header_render' ) );
        add_action( 'dokan_analytics_content', array( $this, 'render_analytics_content' ) );
        add_action( 'dokan_rewrite_rules_loaded', array( $this, 'add_rewrite_rules' ) );
    }

    /**
     * Initializes the Dokan_Vendor_Analytics() class
     *
     * Checks for an existing Dokan_Vendor_Analytics() instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;
        if ( !$instance ) {
            $instance = new Dokan_Vendor_Analytics();
        }

        return $instance;
    }

    /**
     * Define all constant
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function define_constant() {
        define( 'DOKAN_VENDOR_ANALYTICS_DIR', dirname( __FILE__ ) );
        define( 'DOKAN_VENDOR_ANALYTICS_URL' , plugins_url( '', __FILE__ ) );
        define( 'DOKAN_VENDOR_ANALYTICS_ASSETS' , DOKAN_VENDOR_ANALYTICS_URL . '/assets' );
        define( 'DOKAN_VENDOR_ANALYTICS_VIEWS', DOKAN_VENDOR_ANALYTICS_DIR . '/views' );
        define( 'DOKAN_VENDOR_ANALYTICS_INC_DIR', DOKAN_VENDOR_ANALYTICS_DIR . '/includes' );
        define( 'DOKAN_VENDOR_ANALYTICS_TOOLS_DIR', DOKAN_VENDOR_ANALYTICS_DIR . '/tools' );
    }

    /**
     * Includes all files
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function includes() {
        include_once DOKAN_VENDOR_ANALYTICS_TOOLS_DIR . '/src/Dokan/autoload.php';
        require_once DOKAN_VENDOR_ANALYTICS_INC_DIR . '/functions.php';
        require_once DOKAN_VENDOR_ANALYTICS_INC_DIR . '/class-analytics-reports.php';
        require_once DOKAN_VENDOR_ANALYTICS_INC_DIR . '/class-dokan-vendor-analytics-admin-settings.php';
    }

    /**
     * Inistantiate all class
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function initiate() {
        new Dokan_Vendor_Analytics_Reports();
        new Dokan_Vendor_Analytics_Admin_Settings();
    }

    /**
     * Flush rewrite endpoind after activation
     *
     * @since 1.0.0
     *
     * @return void
     */
    function add_rewrite_rules() {
        if ( get_transient( 'dokan-vendor-analytics' ) ) {
            flush_rewrite_rules( true );
            delete_transient( 'dokan-vendor-analytics' );
        }
    }

    /**
     * Activate functions
     *
     * @since 1.0.0
     *
     * @return void
     */
    public static function activate() {
    }

    /**
     * Deactivate functions
     *
     * @since 1.0.0
     *
     * @return void
     */
    public static function deactivate() {
    }

    /**
     * Add staffs endpoint to the end of Dashboard
     *
     * @param array $query_var
     */
    function add_endpoint( $query_var ) {
        $query_var['analytics'] = 'analytics';

        return $query_var;
    }

    /**
    * Get plugin path
    *
    * @since 2.8
    *
    * @return void
    **/
    public function plugin_path() {
        return untrailingslashit( plugin_dir_path( __FILE__ ) );
    }

    /**
     * Render Analytics Header Template
     *
     * @since 2.4
     *
     * @return void
     */
    public function analytics_header_render() {
        dokan_get_template_part( 'vendor-analytics/header', '', array( 'is_vendor_analytics' => true ) );
    }

    /**
     * Render Analytics Content
     *
     * @return void
     */
    public function render_analytics_content() {
        global $woocommerce;

        $tabs  = dokan_get_analytics_tabs();
        $link    = dokan_get_navigation_url( 'analytics' );
        $current = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';

        dokan_get_template_part( 'vendor-analytics/content', '', array(
            'is_vendor_analytics' => true,
            'tabs' => $tabs,
            'link' => $link,
            'current' => $current,
        ) );
    }

    /**
    * Load Dokan vendor_staff templates
    *
    * @since 2.8
    *
    * @return void
    **/
    public function load_vendor_analytics_templates( $template_path, $template, $args ) {
        if ( isset( $args['is_vendor_analytics'] ) && $args['is_vendor_analytics'] ) {
            return $this->plugin_path() . '/templates';
        }

        return $template_path;
    }

    /**
     * Load tools template
     *
     * @since  1.0
     *
     * @param  array $query_vars
     *
     * @return string
     */
    function load_analytics_template( $query_vars ) {

        if ( isset( $query_vars['analytics'] ) ) {
            if ( ! current_user_can( 'seller' ) ) {
                dokan_get_template_part('global/dokan-error', '', array( 'deleted' => false, 'message' => __( 'You have no permission to view this page', 'dokan' ) ) );
            } else {
                dokan_get_template_part( 'vendor-analytics/analytics', '', array( 'is_vendor_analytics' => true ) );
            }
        }
    }



    /**
     * Add staffs page in seller dashboard
     *
     * @param array $urls
     *
     * @return array $urls
     */
    public function add_analytics_page( $urls ) {
        if ( dokan_is_seller_enabled( get_current_user_id() ) && current_user_can( 'seller' ) ) {
            $urls['analytics'] = array(
                'title' => __( 'Analytics', 'dokan' ),
                'icon'  => '<i class="fa fa-area-chart"></i>',
                'url'   => dokan_get_navigation_url( 'analytics' ),
                'pos'   => 181
            );
        }

        return $urls;
    }

}

$vendor_staff = Dokan_Vendor_Analytics::init();

dokan_register_activation_hook( __FILE__, array( 'Dokan_Vendor_Analytics', 'activate' ) );
dokan_register_deactivation_hook( __FILE__, array( 'Dokan_Vendor_Analytics', 'deactivate' ) );
