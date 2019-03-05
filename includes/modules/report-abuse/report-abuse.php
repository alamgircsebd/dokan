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
    }
}

/**
 * Load Dokan Plugin when all plugins loaded
 *
 * @return \DokanReportAbuse
 */
function dokan_abuse_report() {
    return DokanReportAbuse::instance();
}

dokan_abuse_report();
