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

/**
 * Dokan_Seller_Vacation class
 *
 * @class Dokan_Seller_Vacation The class that holds the entire Dokan_Seller_Vacation plugin
 */
class Dokan_Seller_Vacation {

    /**
     * Constructor for the Dokan_Seller_Vacation class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @uses register_activation_hook()
     * @uses register_deactivation_hook()
     * @uses is_admin()
     * @uses add_action()
     */
    public function __construct() {
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

        // Localize our plugin
        add_action( 'init', array( $this, 'custom_post_status_vacation' ) );

        // Loads frontend scripts and styles
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

        add_action( 'dokan_settings_form_bottom', array( $this, 'add_vacation_settings_form' ), 10, 2 );
        add_action( 'dokan_store_profile_saved', array( $this, 'save_vacation_settings' ), 18 );
        add_action( 'wp_ajax_dokan_seller_vacation_save_item', array( $this, 'ajax_vacation_save_item' ) );
        add_action( 'wp_ajax_dokan_seller_vacation_delete_item', array( $this, 'ajax_vacation_delete_item' ) );

        add_filter( 'dokan_product_listing_query', array( $this, 'modified_product_listing_query' ) );
        add_filter( 'dokan_get_post_status', array( $this, 'show_vacation_status_listing' ), 12 );
        add_filter( 'dokan_get_post_status_label_class', array( $this, 'show_vacation_status_listing_label' ), 12 );

        add_action( 'check_daily_is_vacation_is_set_action', array( $this, 'check_daily_is_vacation_is_set' ) );
        add_action( 'dokan_product_listing_status_filter', array( $this, 'add_vacation_product_listing_filter'), 10, 2 );
        add_action( 'dokan_store_profile_frame_after', array( $this, 'show_vacation_message' ), 10, 2 );
    }

    /**
     * Initializes the Dokan_Seller_Vacation() class
     *
     * Checks for an existing Dokan_Seller_Vacation() instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new Dokan_Seller_Vacation();
        }

        return $instance;
    }

    /**
     * Placeholder for activation function
     *
     * Nothing being called here yet.
     */
    public function activate() {
        wp_schedule_event( time(), 'daily', 'check_daily_is_vacation_is_set_action');
    }

    /**
     * Placeholder for deactivation function
     *
     * Nothing being called here yet.
     */
    public function deactivate() {
        wp_clear_scheduled_hook( 'check_daily_is_vacation_is_set_action' );
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
        global $wp;

        if( isset( $wp->query_vars['settings'] ) ) {
            wp_enqueue_style( 'dokan-sv-styles', plugins_url( 'assets/css/style.css', __FILE__ ), false, date( 'Ymd' ) );
            wp_enqueue_script( 'dokan-sv-scripts', plugins_url( 'assets/js/script.js', __FILE__ ), array( 'jquery', 'jquery-ui-datepicker', 'dokan-moment' ), false, true );

            wp_localize_script( 'dokan-sv-scripts', 'dokanSellerVacation', array(
                'i18n' => array(
                    'vacation_date_is_not_set' => __( 'No vacation is set', 'dokan' ),
                    'invalid_from_date'        => __( 'Invalid from date', 'dokan' ),
                    'invalid_to_date'          => __( 'Invalid to date', 'dokan' ),
                    'empty_message'            => __( 'Message is empty', 'dokan' ),
                    'save'                     => __( 'Save', 'dokan' ),
                    'saving'                   => __( 'Saving', 'dokan' ),
                    'confirm_delete'           => __( 'Are you sure you want to delete this item?', 'dokan' ),
                ),
            ) );
        }
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
     * Add Vacation Settings is Dokan Settings page
     * @param object $current_user
     * @param array $profile_info
     * @return void
     */
    public function add_vacation_settings_form( $current_user, $profile_info ) {

        $closing_style_options = array(
            'instantly' => __( 'Instantly Close', 'dokan' ),
            'datewise'  => __( 'Date Wise Close', 'dokan' ),
        );

        $setting_go_vacation      = isset( $profile_info['setting_go_vacation'] ) ? esc_attr( $profile_info['setting_go_vacation'] ) : 'no';
        $settings_closing_style   = isset( $profile_info['settings_closing_style'] ) ? esc_attr( $profile_info['settings_closing_style'] ) : 'open';
        $setting_vacation_message = isset( $profile_info['setting_vacation_message'] ) ? esc_attr( $profile_info['setting_vacation_message'] ) : '';

        $show_schedules            = dokan_validate_boolean( $setting_go_vacation ) && ( 'datewise' === $settings_closing_style );
        $seller_vacation_schedules = $this->get_vacation_schedules( $profile_info );
        ?>
        <fieldset id="dokan-seller-vacation-settings">
            <div class="dokan-form-group goto_vacation_settings">
                <label class="dokan-w3 dokan-control-label" for="setting_go_vacation"><?php _e( 'Go to Vacation', 'dokan' ); ?></label>
                <div class="dokan-w9">
                    <div class="checkbox dokan-text-left">
                        <label>
                            <input type="hidden" name="setting_go_vacation" value="no">
                            <input type="checkbox" name="setting_go_vacation" id="dokan-seller-vacation-activate" id="dokan-seller-vacation-field-go-vacation" value="yes"<?php checked( $setting_go_vacation, 'yes' ); ?>> <?php _e( 'Want to go vacation by closing our store publically', 'dokan' ); ?>
                        </label>
                    </div>
                </div>
            </div>
            <div class="dokan-form-group dokan-text-left <?php echo dokan_validate_boolean( $setting_go_vacation ) ? '' : 'dokan-hide'; ?>" id="dokan-seller-vacation-closing-style">
                <label class="dokan-w3 dokan-control-label" for="settings_closing_style"><?php _e( 'Closing Style', 'dokan' ); ?></label>
                <div class="dokan-w5">
                    <label>
                       <select class="form-control" name="settings_closing_style">
                           <?php foreach ( $closing_style_options as $key => $closing_style_option ): ?>
                                <option value="<?php echo $key; ?>" <?php selected( $key, $settings_closing_style ); ?>><?php echo $closing_style_option; ?></option>
                           <?php endforeach ?>
                       </select>
                    </label>
                </div>
            </div>
            <div class="dokan-text-left <?php echo $show_schedules ? '' : 'dokan-hide'; ?>" id="dokan-seller-vacation-vacation-dates">
                <div class="dokan-form-group">
                    <label class="dokan-w3 dokan-control-label"><?php _e( 'Date Range', 'dokan' ); ?></label>
                    <div class="dokan-w6">
                        <div class="row">
                            <div class="col-md-6 dokan-seller-vacation-datepickers">
                                <?php _e( 'From', 'dokan' ) ?> <input type="text" class="form-control" id="dokan-seller-vacation-date-from" name="dokan_seller_vacation_datewise_from">
                            </div>
                            <div class="col-md-6 dokan-seller-vacation-datepickers">
                                <?php _e( 'To', 'dokan' ) ?> <input type="text" class="form-control" id="dokan-seller-vacation-date-to" name="dokan_seller_vacation_datewise_to">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="dokan-form-group">
                    <label class="dokan-w3 dokan-control-label"><?php _e( 'Set Vacation Message', 'dokan' ); ?></label>
                    <div class="dokan-w6">
                        <textarea class="form-control" id="dokan-seller-vacation-message" rows="5" name="dokan_seller_vacation_datewise_message"></textarea>
                        <button
                            type="button"
                            class="dokan-btn dokan-btn-default dokan-btn-sm"
                            id="dokan-seller-vacation-save-edit"
                            disabled
                        ><i class="fa fa-check"></i> <span><?php _e( 'Save', 'dokan' ); ?></span></button>
                        <button
                            type="button"
                            class="dokan-btn dokan-btn-default dokan-btn-sm"
                            id="dokan-seller-vacation-cancel-edit"
                            disabled
                        ><i class="fa fa-times"></i> <?php _e( 'Cancel', 'dokan' ); ?></button>
                    </div>
                </div>

                <div class="dokan-form-group">
                    <label class="dokan-w3 dokan-control-label"><?php _e( 'Vacation List', 'dokan' ); ?></label>

                    <div class="dokan-w9">
                        <table class="dokan-table dokan-table-striped" id="dokan-seller-vacation-list-table">
                            <thead>
                                <tr>
                                    <th class="dokan-seller-vacation-list-from"><?php _e( 'From', 'dokan' ); ?></th>
                                    <th class="dokan-seller-vacation-list-to"><?php _e( 'To', 'dokan' ); ?></th>
                                    <th class="dokan-seller-vacation-list-message"><?php _e( 'Message', 'dokan' ); ?></th>
                                    <th class="dokan-seller-vacation-list-action"></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                <input type="hidden" id="dokan-seller-vacation-schedules" value="<?php echo esc_attr( json_encode( $seller_vacation_schedules ) ); ?>">
            </div>

            <div class="dokan-text-left <?php echo $show_schedules ? 'dokan-hide' : ''; ?>" id="dokan-seller-vacation-vacation-instant-vacation-message">
                <div class="dokan-form-group">
                    <label class="dokan-w3 dokan-control-label"><?php _e( 'Set Vacation Message', 'dokan' ); ?></label>
                    <div class="dokan-w6">
                        <textarea class="form-control" id="dokan-seller-vacation-message" rows="5" name="setting_vacation_message"><?php echo $setting_vacation_message; ?></textarea>
                    </div>
                </div>

            </div>
        </fieldset>
        <?php
    }

    /**
     * Save vacation settings with store settings
     * @param  integer $store_id
     * @param  array $dokan_settings
     * @return void
     */
    public function save_vacation_settings( $store_id ) {
        $dokan_settings = dokan_get_store_info( $store_id );

        if( !isset( $_POST['setting_go_vacation'] ) ) {
            return;
        }

        $dokan_settings['setting_go_vacation']      = $_POST['setting_go_vacation'];
        $dokan_settings['settings_closing_style']   = $_POST['settings_closing_style'];
        $dokan_settings['setting_vacation_message'] = $_POST['setting_vacation_message'];

        if ( $_POST['settings_closing_style'] == '' ) {
            return;
        }
        if ( $_POST['setting_go_vacation'] == 'yes' ) {
            update_user_meta( $store_id, 'dokan_enable_seller_vacation', true );
        }

        if ( $_POST['settings_closing_style'] == 'instantly' && $_POST['setting_go_vacation'] == 'yes' ) {
            $this->update_product_status( 'publish', 'vacation' );
        }

        update_user_meta( $store_id, 'dokan_profile_settings', $dokan_settings );

        if ( $_POST['setting_go_vacation'] == 'no' ) {
            $dokan_enable_seller_vacation = get_user_meta( $store_id, 'dokan_enable_seller_vacation', true );

            if( $dokan_enable_seller_vacation ) {
                $this->update_product_status( 'vacation', 'publish' );
                update_user_meta( $store_id, 'dokan_enable_seller_vacation', false );
            }
            return;
        }

        if ( $dokan_settings['settings_closing_style'] == 'datewise' ) {
            $this->check_daily_is_vacation_is_set();
        }
    }

    /**
     * Update post status
     * @param  string $old_status
     * @param  string $new_status
     * @return void
     */
    function update_product_status( $old_status, $new_status ) {
        global $wpdb;

        $seller_id = get_current_user_id();
        $table = $wpdb->prefix . 'posts';

        $data = array(
            'post_status' => $new_status,
        );

        $where = array(
            'post_author' => $seller_id,
            'post_status' => $old_status,
            'post_type'   => 'product'
        );

        $rows_affected = $wpdb->update( $table, $data, $where );
    }

    /**
     * Decides whether to show or not the vacation message
     *
     * @since 2.9.7
     *
     * @param array $store_info
     *
     * @return bool
     */
    public function should_show_message( $store_info ) {
        //if vacation is not enabled return
        if ( ! isset( $store_info['setting_go_vacation'] ) || $store_info['setting_go_vacation'] == 'no' ) {
            return false;
        }

        //disable showing vacation notice
        $show = false;

        //if closing type instant show notice
        if ( $store_info['settings_closing_style'] == 'instantly' ) {
            $show = true;
        } else {

            if ( $store_info['settings_close_from'] == '' || $store_info['settings_close_to'] == '' ) {
                return false;
            }

            $from_date = date( 'Y-m-d', strtotime( $store_info['settings_close_from'] ) );
            $to_date   = date( 'Y-m-d', strtotime( $store_info['settings_close_to'] ) );
            $now       = date( 'Y-m-d' );

            if ( $from_date <= $now && $to_date >= $now ) {
                $show = true;
            }
        }

        if ( $show ) {
            return true;
        }

        return false;
    }

    /**
     * Show Vacation message in store page
     * @param  array $store_user
     * @param  array $store_info
     * @return void
     */
    function show_vacation_message( $store_user, $store_info ) {
        if ( $this->should_show_message( $store_info ) ):
            ?>
                <div class="dokan-alert dokan-alert-info">
                    <p><?php _e( $store_info['setting_vacation_message'], 'dokan' ); ?></p>
                </div>
            <?php
        endif;
    }

    /**
     * Check Daily twice is Datewise vacation is enable
     * @return void
     */
    function check_daily_is_vacation_is_set() {

        $users = new WP_User_Query( array(
            'role'         => 'seller',
            'fields'       => 'ID',
            'meta_key'     => 'dokan_enable_selling',
            'meta_value'   => 'yes',
            'meta_compare' => '=',
        ) );

        foreach ( $users->results as $key => $seller_id ) {

            $profile_info = dokan_get_store_info( $seller_id );

            $settings_closing_style   = isset( $profile_info['settings_closing_style'] ) ? esc_attr( $profile_info['settings_closing_style'] ) : 'open';
            $settings_close_from      = isset( $profile_info['settings_close_from'] ) ? esc_attr( $profile_info['settings_close_from'] ) : '';
            $settings_close_to        = isset( $profile_info['settings_close_to'] ) ? esc_attr( $profile_info['settings_close_to'] ) : '';
            $setting_vacation_message = isset( $profile_info['setting_vacation_message'] ) ? esc_attr( $profile_info['setting_vacation_message'] ) : '';

            if ( $settings_closing_style  == 'instantly' || $settings_closing_style == '' ) {
                return;
            }

            if ( $settings_close_to == '' || $settings_close_from == '' ) {
                return;
            }

            $from_date = date( 'Y-m-d', strtotime( $settings_close_from ) );
            $to_date = date( 'Y-m-d', strtotime( $settings_close_to ) );
            $now = date( 'Y-m-d' );

            if ( $from_date <= $now && $to_date >= $now ) {
                // Date is within beginning and ending time
                $this->update_product_status( 'publish', 'vacation' );
            } else {
                // Date is not within beginning and ending time
                $this->update_product_status( 'vacation', 'publish' );
            }
        }
    }

    /**
     * Add vacation link in product listing filter
     * @param string $status_class
     * @param object $post_counts
     */
    function add_vacation_product_listing_filter( $status_class, $post_counts ) {
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
    function show_vacation_status_listing( $status ) {
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
    function modified_product_listing_query( $args ) {

        if ( isset( $_GET['post_status'] ) && $_GET['post_status'] == 'vacation' ) {
            $args['post_status'] = $_GET['post_status'];
            return $args;
        }

        $this->check_daily_is_vacation_is_set();

        if ( is_array( $args['post_status'] ) ) {
            $args['post_status'][] = 'vacation';
            return $args;
        }
        return $args;
    }

    /**
     * Get vacation schedules
     *
     * @since DOKAN_PRO_SINCE
     *
     * @param array $profile_info
     *
     * @return array
     */
    public function get_vacation_schedules( $profile_info ) {
        $vacation_schedules = array();

        if ( isset( $profile_info['seller_vacation_schedules'] ) && is_array( $profile_info['seller_vacation_schedules'] ) ) {
            $vacation_schedules = $profile_info['seller_vacation_schedules'];
        } else if ( isset( $profile_info['settings_close_from'] ) && isset( $profile_info['settings_close_to'] ) ) {
            $vacation_schedules = array(
                array(
                    'from'    => $profile_info['settings_close_from'],
                    'to'      => $profile_info['settings_close_to'],
                    'message' => ! empty( $profile_info['setting_vacation_message'] ) ? $profile_info['setting_vacation_message'] : '',
                )
            );
        }

        return $vacation_schedules;
    }

    /**
     * Save vacation item via AJAX request
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    public function ajax_vacation_save_item() {
        check_ajax_referer( 'dokan_reviews' );

        try {
            $post_data = wp_unslash( $_POST );

            if ( empty( $post_data['data']['item'] ) ) {
                throw new Dokan_Exception( 'parameter_is_missing', __( 'Item parameter is missing', 'dokan' ), 400 );
            }

            $store_id = get_current_user_id();
            $index = isset( $post_data['data']['index'] ) ? absint( $post_data['data']['index'] ) : null;
            $item = $post_data['data']['item'];

            if ( empty( $item['from'] ) || empty( $item['to'] ) || empty( $item['message'] ) ) {
                throw new Dokan_Exception( 'invalid_parameter', __( 'Invalid item parameter', 'dokan' ), 400 );
            }

            $from = strtotime( $item['from'] );
            $to   = strtotime( $item['to'] );
            $current_time = current_time( 'timestamp' );

            if ( $to < $from || $from < $current_time || $to < $current_time ) {
                throw new Dokan_Exception( 'invalid_date_range', __( 'Invalid date range', 'dokan' ), 400 );
            }

            $from    = date( 'Y-m-d', strtotime( $item['from'] ) );
            $to      = date( 'Y-m-d', strtotime( $item['to'] ) );
            $message = sanitize_text_field( $item['message'] );

            $vendor_settings = dokan_get_store_info( $store_id );
            $schedules       = $this->get_vacation_schedules( $vendor_settings );
            $total_schedules = count( $schedules );
            $item            = array(
                'from'    => $from,
                'to'      => $to,
                'message' => $message,
            );

            if ( ! isset( $index ) ) {
                $schedules[] = $item;
            } else if ( isset( $schedules[ $index ] ) ) {
                $schedules[ $index ] = $item;
            }

            usort( $schedules, [ $this, 'sort_by_date_asc' ] );

            $vendor_settings['setting_go_vacation']       = 'yes';
            $vendor_settings['settings_closing_style']    = 'datewise';
            $vendor_settings['seller_vacation_schedules'] = $schedules;

            update_user_meta( $store_id, 'dokan_profile_settings', $vendor_settings );

            wp_send_json_success( array(
                'schedules'  => $schedules,
            ) );
        } catch ( Exception $e ) {
            $this->send_json_error( $e, __( 'Unable to save vacation data', 'dokan' ) );
        }
    }

    /**
     * Delete vacation item via AJAX request
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    public function ajax_vacation_delete_item() {
        check_ajax_referer( 'dokan_reviews' );

        try {
            $post_data = wp_unslash( $_POST );

            if ( ! isset( $post_data['index'] ) ) {
                throw new Dokan_Exception( 'missing_item_index', __( 'Item index is missing', 'dokan' ) );
            }

            $index = absint( $post_data['index'] );

            $store_id        = get_current_user_id();
            $vendor_settings = dokan_get_store_info( $store_id );
            $schedules       = $this->get_vacation_schedules( $vendor_settings );

            if ( ! isset( $schedules[ $index ] ) ) {
                throw new Dokan_Exception( 'invalid_item_index', __( 'Invalid item index', 'dokan' ) );
            }

            unset( $schedules[ $index ] );

            usort( $schedules, [ $this, 'sort_by_date_asc' ] );

            $vendor_settings['setting_go_vacation']       = 'yes';
            $vendor_settings['settings_closing_style']    = 'datewise';
            $vendor_settings['seller_vacation_schedules'] = $schedules;

            update_user_meta( $store_id, 'dokan_profile_settings', $vendor_settings );

            wp_send_json_success( array(
                'schedules'  => $schedules,
            ) );
        } catch ( Exception $e ) {
            $this->send_json_error( $e, __( 'Unable to delete vacation item', 'dokan' ) );
        }
    }

    /**
     * Sort vacation list by date `from` ascending order
     *
     * @since DOKAN_PRO_SINCE
     *
     * @param string $a
     * @param string $b
     *
     * @return int
     */
    public function sort_by_date_asc( $a, $b ) {
        return strtotime( $a['from'] ) - strtotime( $b['from'] );
    }

    /**
     * Send JSON error on AJAX response
     *
     * @since DOKAN_PRO_SINCE
     *
     * @param \Exception $e
     * @param string $default_message
     *
     * @return void
     */
    protected function send_json_error( Exception $e, $default_message ) {
        if ( $e instanceof Dokan_Exception ) {
            wp_send_json_error(
                new WP_Error( $e->get_error_code(), $e->get_message() ),
                $e->get_status_code()
            );
        }

        wp_send_json_error(
            new WP_Error( 'something_went_wrong', $default_message ),
            422
        );
    }
}

Dokan_Seller_Vacation::init();
