<?php

/**
 * Class Dokan_Pro_Admin_Settings
 *
 * Class for load Admin functionality for Pro Version
 *
 * @since 2.4
 *
 * @author weDevs <info@wedevs.com>
 */
class Dokan_Pro_Admin_Settings {

    /**
     * Constructor for the Dokan_Pro_Admin_Settings class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @return void
     */
    public function __construct() {
        add_action( 'dokan_admin_menu', array( $this, 'load_admin_settings' ), 10, 2 );
        add_action( 'admin_init', array($this, 'tools_page_handler') );
        add_filter( 'dokan_settings_fields', array( $this, 'load_settings_sections_fields' ), 10 );
        add_action( 'wp_before_admin_bar_render', array( $this, 'render_pro_admin_toolbar' ) );
    }

    /**
     * Load Admin Pro settings
     *
     * @since 2.4
     *
     * @param  string $capability
     * @param  intiger $menu_position
     *
     * @return void
     */
    public function load_admin_settings( $capability, $menu_position ) {
        $refund      = dokan_get_refund_count();
        $refund_text = __( 'Refund Request', 'dokan' );

        if ( $refund['pending'] ) {
            $refund_text = sprintf( __( 'Refund Request %s', 'dokan' ), '<span class="awaiting-mod count-1"><span class="pending-count">' . $refund['pending'] . '</span></span>');
        }
        add_submenu_page( 'dokan', __( 'Refund Request', 'dokan' ), $refund_text, $capability, 'dokan-refund', array($this, 'refund_request') );
        add_submenu_page( 'dokan', __( 'Sellers Listing', 'dokan' ), __( 'All Sellers', 'dokan' ), $capability, 'dokan-sellers', array($this, 'seller_listing') );
        $report       = add_submenu_page( 'dokan', __( 'Earning Reports', 'dokan' ), __( 'Earning Reports', 'dokan' ), $capability, 'dokan-reports', array($this, 'report_page') );
        $announcement = add_submenu_page( 'dokan', __( 'Announcement', 'dokan' ), __( 'Announcement', 'dokan' ), $capability, 'edit.php?post_type=dokan_announcement' );
        add_submenu_page( 'dokan', __( 'Tools', 'dokan' ), __( 'Tools', 'dokan' ), $capability, 'dokan-tools', array($this, 'tools_page') );

        add_action( $report, array($this, 'report_scripts' ) );
        add_action( 'admin_print_scripts-post-new.php', array( $this, 'announcement_scripts' ), 11 );
        add_action( 'admin_print_scripts-post.php', array( $this, 'announcement_scripts' ), 11 );

        add_submenu_page( 'dokan', __( 'Help', 'dokan' ), __( '<span style="color:#f18500">Help</span>', 'dokan' ), $capability, 'dokan-help', array($this, 'help_page') );
    }

    /**
     * Load all pro settings field
     *
     * @since 2.4
     *
     * @param  array $settings_fields
     *
     * @return array
     */
    public function load_settings_sections_fields( $settings_fields ) {
        $new_settings_fields['dokan_general'] = array(
            'store_map' => array(
                'name'    => 'store_map',
                'label'   => __( 'Show Map on Store Page', 'dokan' ),
                'desc'    => __( 'Enable showing Store location map on store left sidebar', 'dokan' ),
                'type'    => 'checkbox',
                'default' => 'on'
            ),
            'gmap_api_key' => array(
                'name'    => 'gmap_api_key',
                'label'   => __( 'Google Map API key', 'dokan' ),
                'desc'    => __( '<a href="https://developers.google.com/maps/documentation/javascript/" target="_blank">API Key</a> is needed to display map on store page', 'dokan' ),
                'type'    => 'text',
            ),
            'store_seo' => array(
                'name'    => 'store_seo',
                'label'   => __( 'Enable Store SEO', 'dokan' ),
                'desc'    => __( 'Sellers can manage their Store page SEO', 'dokan' ),
                'type'    => 'checkbox',
                'default' => 'on'
            ),
            'contact_seller' => array(
                'name'    => 'contact_seller',
                'label'   => __( 'Show Contact Form on Store Page', 'dokan' ),
                'desc'    => __( 'Enable showing contact seller form on store left sidebar', 'dokan' ),
                'type'    => 'checkbox',
                'default' => 'on'
            ),
            'enable_theme_store_sidebar' => array(
                'name'    => 'enable_theme_store_sidebar',
                'label'   => __( 'Enable Store Sidebar From Theme', 'dokan' ),
                'desc'    => __( 'Enable showing Store Sidebar From Your Theme.', 'dokan' ),
                'type'    => 'checkbox',
                'default' => 'off'
            ),
            'product_add_mail' => array(
                'name'    => 'product_add_mail',
                'label'   => __( 'Product Mail Notification', 'dokan' ),
                'desc'    => __( 'Email notification on new product submission', 'dokan' ),
                'type'    => 'checkbox',
                'default' => 'on'
            ),
            'enable_tc_on_reg' => array(
                'name'    => 'enable_tc_on_reg',
                'label'   => __( 'Enable Terms and Condition', 'dokan' ),
                'desc'    => __( 'Enable Terms and Condition check on registration form', 'dokan' ),
                'type'    => 'checkbox',
                'default' => 'on'
            ),
        );

        $new_settings_fields['dokan_selling'] = array(

            'product_style' => array(
                'name'    => 'product_style',
                'label'   => __( 'Add/Edit Product Style', 'dokan' ),
                'desc'    => __( 'The style you prefer for seller to add or edit products. ', 'dokan' ),
                'type'    => 'select',
                'default' => 'old',
                'options' => array(
                    'old' => __( 'Tab View', 'dokan' ),
                    'new' => __( 'Flat View', 'dokan' )
                )
            ),
            'product_category_style' => array(
                'name'    => 'product_category_style',
                'label'   => __( 'Category Selection', 'dokan' ),
                'desc'    => __( 'What option do you prefer for seller to select product category? ', 'dokan' ),
                'type'    => 'select',
                'default' => 'single',
                'options' => array(
                    'single' => __( 'Single', 'dokan' ),
                    'multiple' => __( 'Multiple', 'dokan' )
                )
            ),
            'product_status' => array(
                'name'    => 'product_status',
                'label'   => __( 'New Product Status', 'dokan' ),
                'desc'    => __( 'Product status when a seller creates a product', 'dokan' ),
                'type'    => 'select',
                'default' => 'pending',
                'options' => array(
                    'publish' => __( 'Published', 'dokan' ),
                    'pending' => __( 'Pending Review', 'dokan' )
                )
            ),
            'review_edit' => array(
                'name'    => 'review_edit',
                'label'   => __( 'Review Editing', 'dokan' ),
                'desc'    => __( 'Seller can edit product reviews', 'dokan' ),
                'type'    => 'checkbox',
                'default' => 'off'
            ),
        );

        $new_settings_fields['dokan_withdraw'] = array(

            'withdraw_order_status' => array(
                'name'    => 'withdraw_order_status',
                'label'   => __( 'Order Status for Withdraw', 'dokan' ),
                'desc'    => __( 'Order status for which seller can make a withdraw request.', 'dokan' ),
                'type'    => 'multicheck',
                'default' => array( 'wc-completed' => __( 'Completed', 'dokan' ), 'wc-processing' => __( 'Processing', 'dokan' ), 'wc-on-hold' => __( 'On-hold', 'dokan' ) ),
                'options' => array( 'wc-completed' => __( 'Completed', 'dokan' ), 'wc-processing' => __( 'Processing', 'dokan' ), 'wc-on-hold' => __( 'On-hold', 'dokan' ) )
            ),
            'withdraw_date_limit' => array(
                'name'    => 'withdraw_date_limit',
                'label'   => __( 'Withdraw Threshold', 'dokan' ),
                'desc'    => __( 'Days, ( Make order matured to make a withdraw request) <br> Value "0" will inactive this option', 'dokan' ),
                'default' => '0',
                'type'    => 'text',
            ),
        );

        $settings_fields['dokan_general'] = array_merge( $settings_fields['dokan_general'], $new_settings_fields['dokan_general'] );
        $settings_fields['dokan_selling'] = array_merge( $settings_fields['dokan_selling'], $new_settings_fields['dokan_selling'] );
        $settings_fields['dokan_withdraw'] = array_merge( $settings_fields['dokan_withdraw'], $new_settings_fields['dokan_withdraw'] );

        return $settings_fields;
    }

    /**
     * Load Report Scripts
     *
     * @since 2.4
     *
     * @return void
     */
    function report_scripts() {
        Dokan_Admin_Settings::report_scripts();
    }

    /**
     * Seller announcement scripts
     *
     * @since 2.4
     *
     * @return void
     */
    function announcement_scripts() {
        global $post_type;

        if ( 'dokan_announcement' == $post_type ) {
            wp_enqueue_style( 'dokan-chosen-style' );
            wp_enqueue_script( 'chosen' );
        }
    }

    /**
     * Refund request template
     *
     * @since 2.4.11
     *
     * @return void
     */
    function refund_request() {
        include dirname(__FILE__) . '/refund.php';
    }

    /**
     * Seller Listing template
     *
     * @since 2.4
     *
     * @return void
     */
    function seller_listing() {
        include dirname(__FILE__) . '/sellers.php';
    }

    /**
     * Report Tempalte
     *
     * @since 2.4
     *
     * @return void
     */
    function report_page() {
        global $wpdb;
        include dirname(__FILE__) . '/reports.php';
    }

    /**
     * Tools Template
     *
     * @since 2.4
     *
     * @return void
     */
    function tools_page() {
        include dirname(__FILE__) . '/tools.php';
    }

    /**
     * Plugin help page
     *
     * @since 2.4.9
     *
     * @return void
     */
    function help_page() {
        include dirname(__FILE__) . '/help.php';
    }

    /**
     * Tools Toggole Handler
     *
     * @since 2.4
     *
     * @return void
     */
    function tools_page_handler() {
        if ( isset( $_GET['dokan_action'] ) && current_user_can( 'manage_options' ) ) {
            $action = $_GET['dokan_action'];

            check_admin_referer( 'dokan-tools-action' );

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
                    'page_id'    => 'my_orders',
                    'content'    => '[dokan-stores]'
                ),
            );

            foreach ($pages as $page) {
                $page_id = wp_insert_post( array(
                    'post_title'     => $page['post_title'],
                    'post_name'      => $page['slug'],
                    'post_content'   => $page['content'],
                    'post_status'    => 'publish',
                    'post_type'      => 'page',
                    'comment_status' => 'closed'
                ) );

                if ( $page['slug'] == 'dashboard' ) {
                    update_option( 'dokan_pages', array( 'dashboard' => $page_id ) );
                }
            }

            flush_rewrite_rules();

            wp_redirect( admin_url( 'admin.php?page=dokan-tools&msg=page_installed' ) );
            exit;
        }
    }

    function render_pro_admin_toolbar() {

        global $wp_admin_bar;

        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        $wp_admin_bar->remove_menu('dokan-dashboard');
        $wp_admin_bar->remove_menu('dokan-withdraw');
        $wp_admin_bar->remove_menu('dokan-settings');

        $wp_admin_bar->add_menu( array(
            'id'     => 'dokan-dashboard',
            'parent' => 'dokan',
            'title'  => __( 'Dokan Dashboard', 'dokan' ),
            'href'   => admin_url( 'admin.php?page=dokan' )
        ) );

        $wp_admin_bar->add_menu( array(
            'id'     => 'dokan-withdraw',
            'parent' => 'dokan',
            'title'  => __( 'Withdraw', 'dokan' ),
            'href'   => admin_url( 'admin.php?page=dokan-withdraw' )
        ) );

        $wp_admin_bar->add_menu( array(
            'id'     => 'dokan-sellers',
            'parent' => 'dokan',
            'title'  => __( 'All Sellers', 'dokan' ),
            'href'   => admin_url( 'admin.php?page=dokan-sellers' )
        ) );

        $wp_admin_bar->add_menu( array(
            'id'     => 'dokan-reports',
            'parent' => 'dokan',
            'title'  => __( 'Earning Reports', 'dokan' ),
            'href'   => admin_url( 'admin.php?page=dokan-reports' )
        ) );

        $wp_admin_bar->add_menu( array(
            'id'     => 'dokan-settings',
            'parent' => 'dokan',
            'title'  => __( 'Settings', 'dokan' ),
            'href'   => admin_url( 'admin.php?page=dokan-settings' )
        ) );
    }

} // End of Dokan_Pro_Admin_Settings class;