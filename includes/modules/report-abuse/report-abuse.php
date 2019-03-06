<?php
/*
* Plugin Name: Report Abuse
* Plugin URI: https://wedevs.com/products/plugins/dokan/
* Description: CHANGE_THIS_DESCRIPTION
* Version: DOKAN_PRO_SINCE
* Author: weDevs
* Author URI: https://wedevs.com/
* Thumbnail Name: abuse-report.png
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

use Dokan\Traits\Singleton;

final class DokanReportAbuse {

    use Singleton;

    /**
     * Exec after first instance has been created
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    public function boot() {
        $this->define_constants();
        $this->includes();
        $this->instances();

        dokan_register_activation_hook( __FILE__, [ self::class, 'activate' ] );
    }

    /**
     * Module constants
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    private function define_constants() {
        define( 'DOKAN_REPORT_ABUSE_FILE' , __FILE__ );
        define( 'DOKAN_REPORT_ABUSE_PATH' , dirname( DOKAN_REPORT_ABUSE_FILE ) );
        define( 'DOKAN_REPORT_ABUSE_INCLUDES' , DOKAN_REPORT_ABUSE_PATH . '/includes' );
        define( 'DOKAN_REPORT_ABUSE_URL' , plugins_url( '', DOKAN_REPORT_ABUSE_FILE ) );
        define( 'DOKAN_REPORT_ABUSE_ASSETS' , DOKAN_REPORT_ABUSE_URL . '/assets' );
        define( 'DOKAN_REPORT_ABUSE_VIEWS', DOKAN_REPORT_ABUSE_PATH . '/views' );
    }

    /**
     * Include module related files
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    private function includes() {
        require_once DOKAN_REPORT_ABUSE_INCLUDES . '/AdminSettings.php';
    }

    /**
     * Create module related class instances
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    private function instances() {
        new \DokanPro\ReportAbuse\AdminSettings();
    }

    /**
     * Executes on module activation
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    public static function activate() {
        $option = get_option( 'dokan_report_abuse', [] );

        if ( empty( $option['abuse_reasons'] ) ) {
            $option['abuse_reasons'] = [
                [
                    'id' => 'other',
                    'value' => __( 'Other', 'dokan' )
                ]
            ];

            update_option( 'dokan_report_abuse', $option, false );
        }

        self::create_tables();
    }

    /**
     * Create module related tables
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    private static function create_tables() {
        global $wpdb;

        $collate = '';

        if ( $wpdb->has_cap( 'collation' ) ) {
            if ( ! empty($wpdb->charset ) ) {
                $collate .= "AUTO_INCREMENT=1 DEFAULT CHARACTER SET $wpdb->charset";
            }

            if ( ! empty($wpdb->collate ) ) {
                $collate .= " AUTO_INCREMENT=1 COLLATE $wpdb->collate";
            }
        }

        include_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $request_table = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dokan_report_abuse_reports` (
          `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
          `reason` VARCHAR(191) NOT NULL,
          `product_id` BIGINT(20) NOT NULL,
          `vendor_id` BIGINT(20) NOT NULL,
          `customer_id` BIGINT(20) DEFAULT NULL,
          `customer_name` VARCHAR(191) DEFAULT NULL,
          `customer_email` VARCHAR(100) DEFAULT NULL,
          `description` TEXT DEFAULT NULL,
          `created_at` DATETIME NOT NULL,
          INDEX `reason` (`reason`),
          INDEX `product_id` (`product_id`),
          INDEX `vendor_id` (`vendor_id`)
        ) $collate";

        dbDelta( $request_table );
    }

    /**
     * Create abuse report
     *
     * @since DOKAN_PRO_SINCE
     *
     * @param array $args
     * @param bool  $send_notification
     *
     * @return array
     */
    public function create( $args, $send_notification = true ) {
        global $wpdb;

        $defaults = [
            'reason'        => '',
            'product_id'     => 0,
            'customer_id'    => 0,
            'customer_name'  => '',
            'customer_email' => '',
            'description'    => '',
        ];

        $args = wp_parse_args( $args, $defaults );

        if ( empty( $args['product_id'] ) ) {
            return new WP_Error( 'missing_product_id', __( 'Missing product_id param.', 'dokan' ) );
        }

        $product = wc_get_product( $args['product_id'] );

        if ( ! $product instanceof WC_Product ) {
            return new WP_Error( 'invalid_product_id', __( 'Product not found.', 'dokan' ) );
        }

        $vendor = dokan_get_vendor_by_product( $product );

        $customer = null;

        if ( ! empty( $args['customer_id'] ) ) {
            $customer = new WC_Customer( $args['customer_id'] );

            if ( ! $customer->get_id() ) {
                return new WP_Error( 'invalid_customer_id', __( 'Customer not found.', 'dokan' ) );
            }
        }

        $args['reason']         = wp_trim_words( $args['reason'], 191 );
        $args['customer_name']  = wp_trim_words( $args['customer_name'], 191 );
        $args['customer_email'] = wp_trim_words( $args['customer_email'], 100 );

        $report = [
            'reason'         => $args['reason'],
            'product_id'     => $args['product_id'],
            'vendor_id'      => $vendor->get_id(),
            'customer_id'    => $args['customer_id'],
            'customer_name'  => $args['customer_name'],
            'customer_email' => $args['customer_email'],
            'description'    => $args['description'],
            'created_at'     => current_time( 'mysql' ),
        ];

        $inserted = $wpdb->insert(
            $wpdb->prefix . 'dokan_report_abuse_reports',
            $report,
            [
                '%s',
                '%d',
                '%d',
                '%d',
                '%s',
                '%s',
                '%s',
                '%s',
            ]
        );

        if ( ! $inserted ) {
            return new WP_Error( 'unable_to_create_report', __( 'Unable to create abuse report.', 'dokan' ) );
        }

        /**
         * Fires after created an abuse report
         *
         * @since DOKAN_PRO_SINCE
         *
         * @param array             $report
         * @param bool              $send_notification
         * @param \WC_Product       $product
         * @param \Dokan_Vendor     $vendor
         * @param null|\WC_Customer $customer
         */
        do_action( 'dokan_report_abuse_created_report', $report, $send_notification, $product, $vendor, $customer );

        return $report;
    }
}

/**
 * Load Dokan Plugin when all plugins loaded
 *
 * @return \DokanReportAbuse
 */
function dokan_report_abuse() {
    return DokanReportAbuse::instance();
}

dokan_report_abuse();
