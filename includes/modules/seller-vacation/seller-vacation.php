<?php
/*
Plugin Name: Seller Vacation
Plugin URI: https://wedevs.com/products/plugins/dokan/seller-vacation/
Description: Using this plugin seller can go to vacation by closing their stores
Version: 1.2.0
Author: weDevs
Author URI: https://wedevs.com/
Thumbnail Name: seller-vacation.png
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

use Dokan\Traits\Singleton;

/**
 * Dokan_Seller_Vacation class
 *
 * @class Dokan_Seller_Vacation The class that holds the entire Dokan_Seller_Vacation plugin
 */
class Dokan_Seller_Vacation {

    use Singleton;

    /**
     * Constructor for the Dokan_Seller_Vacation class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @since 2.9.10
     *
     * @return void
     */
    public function boot() {
        $this->define_constants();
        $this->includes();
        $this->instances();

        add_action( 'init', array( $this, 'custom_post_status_vacation' ) );

        add_filter( 'dokan_product_listing_query', array( $this, 'modified_product_listing_query' ) );
        add_filter( 'dokan_get_post_status', array( $this, 'show_vacation_status_listing' ), 12 );
        add_filter( 'dokan_get_post_status_label_class', array( $this, 'show_vacation_status_listing_label' ), 12 );

        add_action( 'dokan_product_listing_status_filter', array( $this, 'add_vacation_product_listing_filter'), 10, 2 );
        add_action( 'dokan_store_profile_frame_after', array( $this, 'show_vacation_message' ), 10, 2 );
    }

    /**
     * Module constants
     *
     * @since 2.9.10
     *
     * @return void
     */
    private function define_constants() {
        define( 'DOKAN_SELLER_VACATION_FILE' , __FILE__ );
        define( 'DOKAN_SELLER_VACATION_PATH' , dirname( DOKAN_SELLER_VACATION_FILE ) );
        define( 'DOKAN_SELLER_VACATION_INCLUDES' , DOKAN_SELLER_VACATION_PATH . '/includes' );
        define( 'DOKAN_SELLER_VACATION_URL' , plugins_url( '', DOKAN_SELLER_VACATION_FILE ) );
        define( 'DOKAN_SELLER_VACATION_ASSETS' , DOKAN_SELLER_VACATION_URL . '/assets' );
        define( 'DOKAN_SELLER_VACATION_VIEWS', DOKAN_SELLER_VACATION_PATH . '/views' );
    }

    /**
     * Include module related files
     *
     * @since 2.9.10
     *
     * @return void
     */
    private function includes() {
        require_once DOKAN_SELLER_VACATION_INCLUDES . '/functions.php';
        require_once DOKAN_SELLER_VACATION_INCLUDES . '/class-dokan-seller-vacation-install.php';
        require_once DOKAN_SELLER_VACATION_INCLUDES . '/class-dokan-seller-vacation-store-settings.php';
        require_once DOKAN_SELLER_VACATION_INCLUDES . '/class-dokan-seller-vacation-ajax.php';
        require_once DOKAN_SELLER_VACATION_INCLUDES . '/class-dokan-seller-vacation-cron.php';
    }

    /**
     * Create module related class instances
     *
     * @since 2.9.10
     *
     * @return void
     */
    private function instances() {
        new Dokan_Seller_Vacation_Install();
        new Dokan_Seller_Vacation_Store_Settings();
        new Dokan_Seller_Vacation_Ajax();
        new Dokan_Seller_Vacation_Cron();
    }

    /**
     * Register custom post status "vacation"
     * @return void
     */
    public function custom_post_status_vacation() {
        register_post_status( 'vacation', array(
            'label'                     => _x( 'Vacation', 'dokan' ),
            'public'                    => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop( 'Vacation <span class="count">(%s)</span>', 'Vacation <span class="count">(%s)</span>' )
        ) );
    }

    /**
     * Show Vacation message in store page
     * @param  array $store_user
     * @param  array $store_info
     * @return void
     */
    public function show_vacation_message( $store_user, $store_info, $raw_output = false ) {
        $vendor = dokan()->vendor->get( $store_user->ID );

        if ( dokan_seller_vacation_is_seller_on_vacation( $vendor->get_id() ) ) {
            $shop_info = $vendor->get_shop_info();

            $message = '';

            if ( 'datewise' !== $shop_info['settings_closing_style'] ) {
                $message = $store_info['setting_vacation_message'];
            } else {
                $schedules    = dokan_seller_vacation_get_vacation_schedules( $shop_info );
                $current_time = date( 'Y-m-d', current_time( 'timestamp' ) );

                foreach ( $schedules as $schedule ) {
                    $from = $schedule['from'];
                    $to   = $schedule['to'];

                    if ( $from <= $current_time && $current_time <= $to ) {
                        $message = $schedule['message'];
                        break;
                    }
                }
            }

            if ( $raw_output ) {
                echo esc_html( $message );
            } else {
                dokan_seller_vacation_get_template( 'vacation-message', array(
                    'message' => ! empty( $message ) ? $message : __( 'I\'m on vacation.', 'dokan' ),
                ) );
            }
        }
    }

    /**
     * Add vacation link in product listing filter
     * @param string $status_class
     * @param object $post_counts
     */
    public function add_vacation_product_listing_filter( $status_class, $post_counts ) {
        ?>
        <li<?php echo $status_class == 'vacation' ? ' class="active"' : ''; ?>>
            <a href="<?php echo add_query_arg( array( 'post_status' => 'vacation' ), get_permalink() ); ?>"><?php printf( __( 'Vacation (%d)', 'dokan' ), $post_counts->vacation ); ?></a>
        </li>
        <?php
    }

    /**
     * Show Vacation status with product in product listing
     * @param  string $value
     * @param  string $status
     * @return string
     */
    public function show_vacation_status_listing( $status ) {
        $status['vacation'] = __( 'In vacation', 'dokan' );
        return $status;
    }

    /**
    * Get vacation status label
    *
    * @since 1.2
    *
    * @return void
    **/
    public function show_vacation_status_listing_label( $labels ) {
        $labels['vacation'] = 'dokan-label-info';
        return $labels;
    }

    /**
     * Modified Porduct query
     * @param  array $args
     * @return array
     */
    public function modified_product_listing_query( $args ) {

        if ( isset( $_GET['post_status'] ) && $_GET['post_status'] == 'vacation' ) {
            $args['post_status'] = $_GET['post_status'];
            return $args;
        }

        if ( is_array( $args['post_status'] ) ) {
            $args['post_status'][] = 'vacation';
            return $args;
        }
        return $args;
    }
}

Dokan_Seller_Vacation::instance();
