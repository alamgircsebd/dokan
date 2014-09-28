<?php

/**
 * Dokan installer class
 *
 * @author weDevs
 */
class Dokan_Installer {

    private $theme_version = 1.2;

    function do_install() {

        // upgrades
        $this->do_upgrades();

        // installs
        $this->user_roles();
        $this->setup_pages();
        $this->woocommerce_settings();
        $this->create_tables();

        dokan_generate_sync_table();

        flush_rewrite_rules();

        update_option( 'dokan_theme_version', $this->theme_version );
    }

    /**
     * Perform all the upgrade routines
     *
     * @return void
     */
    function do_upgrades() {
        $installed_version = get_option( 'dokan_theme_version' );

        // may be it's the first install
        if ( ! $installed_version ) {
            return false;
        }

        // do upgrades
        if ( version_compare( $installed_version, '1.2', '<' ) ) {
            $this->update_to_12();
        }
    }

    /**
     * Update to version 1.2
     *
     * @return void
     */
    public function update_to_12() {
        // regenerate sync table for woocommerce 2.2 order status changes
        dokan_generate_sync_table();

        update_option( 'dokan_theme_version', '1.2' );
    }

    function woocommerce_settings() {
        update_option( 'woocommerce_enable_myaccount_registration', 'yes' );
    }

    /**
     * Init dokan user roles
     *
     * @since Dokan 1.0
     * @global WP_Roles $wp_roles
     */
    function user_roles() {
        global $wp_roles;

        if ( class_exists( 'WP_Roles' ) && !isset( $wp_roles ) ) {
            $wp_roles = new WP_Roles();
        }

        add_role( 'seller', __( 'Seller', 'dokan' ), array(
            'read'                   => true,
            'publish_posts'          => true,
            'edit_posts'             => true,
            'delete_published_posts' => true,
            'edit_published_posts'   => true,
            'delete_posts'           => true,
            'manage_categories'      => true,
            'moderate_comments'      => true,
            'unfiltered_html'        => true,
            'upload_files'           => true,
            'dokandar'               => true
        ) );

        $wp_roles->add_cap( 'shop_manager', 'dokandar' );
        $wp_roles->add_cap( 'administrator', 'dokandar' );
    }

    function setup_pages() {
        $meta_key = '_wp_page_template';

        // return if pages were created before
        $page_created = get_option( 'dokan_pages_created', false );
        if ( $page_created ) {
            return;
        }

        $pages = array(
            array(
                'post_title' => __( 'Dashboard', 'dokan' ),
                'slug'       => 'dashboard',
                'page_id'    => 'dashboard',
                'content'    => '[dokan-dashboard]'
            ),
            array(
                'post_title' => __( 'Store List', 'dokan' ),
                'slug'       => 'store-listing',
                'page_id'    => 'store_listing',
                'content'    => '[dokan-stores]'
            ),
            array(
                'post_title' => __( 'My Orders', 'dokan' ),
                'slug'       => 'my-orders',
                'page_id'    => 'my_orders',
                'content'    => '[dokan-my-orders]'
            ),
        );

        $dokan_page_settings = array();

        if ( $pages ) {
            foreach ($pages as $page) {
                $page_id = $this->create_page( $page );

                if ( $page_id ) {
                    $dokan_page_settings[$page['page_id']] = $page_id;

                    if ( isset( $page['child'] ) && count( $page['child'] ) > 0 ) {
                        foreach ($page['child'] as $child_page) {
                            $child_page_id = $this->create_page( $child_page );

                            if ( $child_page_id ) {
                                $dokan_page_settings[$child_page['page_id']] = $child_page_id;

                                wp_update_post( array( 'ID' => $child_page_id, 'post_parent' => $page_id ) );
                            }
                        }
                    } // if child
                } // if page_id
            } // end foreach
        } // if pages

        update_option( 'dokan_pages', $dokan_page_settings );
        update_option( 'dokan_pages_created', true );
    }

    function create_page( $page ) {
        $meta_key = '_wp_page_template';
        $page_obj = get_page_by_path( $page['post_title'] );

        if ( ! $page_obj ) {
            $page_id = wp_insert_post( array(
                'post_title'     => $page['post_title'],
                'post_name'      => $page['slug'],
                'post_content'   => $page['content'],
                'post_status'    => 'publish',
                'post_type'      => 'page',
                'comment_status' => 'closed'
            ) );

            if ( $page_id && !is_wp_error( $page_id ) ) {

                if ( isset( $page['template'] ) ) {
                    update_post_meta( $page_id, $meta_key, $page['template'] );
                }

                return $page_id;
            }
        }

        return false;
    }

    function create_tables() {
        include_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $this->create_withdraw_table();
        $this->create_sync_table();
    }

    function create_withdraw_table() {
        global $wpdb;

        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->dokan_withdraw} (
               `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
               `user_id` bigint(20) unsigned NOT NULL,
               `amount` float(11) NOT NULL,
               `date` timestamp NOT NULL,
               `status` int(1) NOT NULL,
               `method` varchar(30) NOT NULL,
               `note` text NOT NULL,
               `ip` varchar(15) NOT NULL,
              PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";

        dbDelta( $sql );
    }

    function create_sync_table() {
        global $wpdb;

        $sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dokan_orders` (
          `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
          `order_id` bigint(20) DEFAULT NULL,
          `seller_id` bigint(20) DEFAULT NULL,
          `order_total` float(11,2) DEFAULT NULL,
          `net_amount` float(11,2) DEFAULT NULL,
          `order_status` varchar(30) DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `order_id` (`order_id`),
          KEY `seller_id` (`seller_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

        dbDelta( $sql );
    }
}
