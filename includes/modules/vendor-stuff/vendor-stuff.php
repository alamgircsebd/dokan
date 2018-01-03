<?php
/*
  Plugin Name: Vendor Stuff Manager
  Plugin URI: https://wedevs.com/
  Description: A plugin for manage store via vendor stuffs
  Version: 1.0
  Author: weDevs
  Author URI: https://wedevs.com/
  Thumbnail Name: import-export.png
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
 * Dokan_Vendor_Stuff class
 *
 * @class Dokan_Vendor_Stuff The class that holds the entire Dokan_Vendor_Stuff plugin
 */
class Dokan_Vendor_Stuff {

    /**
     * Constructor for the Dokan_Vendor_Stuff class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @uses is_admin()
     * @uses add_action()
     */
    public function __construct() {
        add_action( 'init', array( $this, 'init_hooks' ) );
    }

    /**
     * Initializes the Dokan_Vendor_Stuff() class
     *
     * Checks for an existing Dokan_Vendor_Stuff() instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;
        if ( !$instance ) {
            $instance = new Dokan_Vendor_Stuff();
        }

        return $instance;
    }

    function init_hooks() {

    }

    /**
     * Enqueue admin scripts
     *
     * Allows plugin assets to be loaded.
     *
     * @uses wp_enqueue_script()
     * @uses wp_localize_script()
     * @uses wp_enqueue_style
     */
    public function enqueue_scripts() {

    }

    /**
     * Activate functions
     *
     * @since 1.0.0
     *
     * @return void
     */
    public static function activate() {
        global $wp_roles;

        if ( class_exists( 'WP_Roles' ) && ! isset( $wp_roles ) ) {
            $wp_roles = new WP_Roles();
        }

        add_role( 'vendor_stuff', __( 'Vendor Stuff', 'dokan' ), array(
            'read'     => true,
            'dokandar' => true
        ) );
    }

}

$vendor_stuff = Dokan_Vendor_Stuff::init();

dokan_register_activation_hook( __FILE__, array( 'Dokan_Vendor_Stuff', 'activate' ) );

