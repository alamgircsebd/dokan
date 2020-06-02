<?php

namespace WeDevs\DokanPro\Install;

/**
* Dokan Pro Installer file
*/

class Installer {

    /**
     * Load automatically when class initiate
     *
     * @since 2.8.0
     */
    public function do_install() {
        $this->create_shipping_tables();
        $this->maybe_activate_modules();
    }

    /**
     * Create Shipping Tables
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function create_shipping_tables() {
        global $wpdb;

        $sqls = [
            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dokan_shipping_zone_methods` (
              `instance_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `method_id` varchar(255) NOT NULL DEFAULT '',
              `zone_id` int(11) unsigned NOT NULL,
              `seller_id` int(11) NOT NULL,
              `is_enabled` tinyint(1) NOT NULL DEFAULT '1',
              `settings` longtext,
              PRIMARY KEY (`instance_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;",

            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dokan_shipping_zone_locations` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `seller_id` int(11) DEFAULT NULL,
              `zone_id` int(11) DEFAULT NULL,
              `location_code` varchar(255) DEFAULT NULL,
              `location_type` varchar(255) DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
        ];

        include_once ABSPATH . 'wp-admin/includes/upgrade.php';

        foreach ( $sqls as $sql ) {
            dbDelta( $sql );
        }
    }

    /**
     * Maybe Activate modules
     *
     * For the first time activation after installation,
     * activate all pro modules.
     *
     * @since 2.8.0
     * @since 3.0.0 Using module manager to activate all modules
     *
     * @return void
     * */
    public function maybe_activate_modules() {
        global $wpdb;

        if ( ! class_exists( 'WeDevs_Dokan' ) ) {
            return;
        }

        $modules = ! empty( dokan_pro()->module ) ? dokan_pro()->module : new \WeDevs\DokanPro\Module();

        $has_installed = $wpdb->get_var( $wpdb->prepare(
            "select option_id from {$wpdb->options} where option_name = %s",
            $modules::ACTIVE_MODULES_DB_KEY
        ) );

        if ( $has_installed ) {
            return;
        }

        $modules->activate_modules( $modules->get_all_module_ids() );
    }
}
