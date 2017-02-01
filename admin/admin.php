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
        add_action( 'admin_init', array( $this, 'tools_page_handler' ) );
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
        $refund_text = __( 'Refund Request', 'dokan-pro' );

        if ( $refund['pending'] ) {
            $refund_text = sprintf( __( 'Refund Request %s', 'dokan-pro' ), '<span class="awaiting-mod count-1"><span class="pending-count">' . $refund['pending'] . '</span></span>' );
        }

        add_submenu_page( 'dokan', __( 'Refund Request', 'dokan-pro' ), $refund_text, $capability, 'dokan-refund', array( $this, 'refund_request' ) );
        add_submenu_page( 'dokan', __( 'Vendors Listing', 'dokan-pro' ), __( 'All Vendors', 'dokan-pro' ), $capability, 'dokan-sellers', array( $this, 'seller_listing' ) );
        $report       = add_submenu_page( 'dokan', __( 'Earning Reports', 'dokan-pro' ), __( 'Earning Reports', 'dokan-pro' ), $capability, 'dokan-reports', array( $this, 'report_page' ) );
        $announcement = add_submenu_page( 'dokan', __( 'Announcement', 'dokan-pro' ), __( 'Announcement', 'dokan-pro' ), $capability, 'edit.php?post_type=dokan_announcement' );
        add_submenu_page( 'dokan', __( 'Tools', 'dokan-pro' ), __( 'Tools', 'dokan-pro' ), $capability, 'dokan-tools', array( $this, 'tools_page' ) );

        add_action( $report, array( $this, 'report_scripts' ) );
        add_action( 'admin_print_scripts-post-new.php', array( $this, 'announcement_scripts' ), 11 );
        add_action( 'admin_print_scripts-post.php', array( $this, 'announcement_scripts' ), 11 );

        add_submenu_page( 'dokan', __( 'Help', 'dokan-pro' ), __( '<span style="color:#f18500">Help</span>', 'dokan-pro' ), $capability, 'dokan-help', array( $this, 'help_page' ) );
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
            'product_add_mail'           => array(
                'name'    => 'product_add_mail',
                'label'   => __( 'Product Mail Notification', 'dokan-pro' ),
                'desc'    => __( 'Email notification on new product submission', 'dokan-pro' ),
                'type'    => 'checkbox',
                'default' => 'on'
            ),
            'enable_tc_on_reg'           => array(
                'name'    => 'enable_tc_on_reg',
                'label'   => __( 'Enable Terms and Condition', 'dokan-pro' ),
                'desc'    => __( 'Enable Terms and Condition check on registration form', 'dokan-pro' ),
                'type'    => 'checkbox',
                'default' => 'on'
            ),
            'store_banner_width' => array(
                'name'    => 'store_banner_width',
                'label'   => __( 'Store Banner width', 'dokan-pro' ),
                'type'    => 'text',
                'default' => 625
            ),
            'store_banner_height' => array(
                'name'    => 'store_banner_height',
                'label'   => __( 'Store Banner height', 'dokan-pro' ),
                'type'    => 'text',
                'default' => 300
            ),
        );

        $new_settings_fields['dokan_selling'] = array(
            'product_style'          => array(
                'name'    => 'product_style',
                'label'   => __( 'Add/Edit Product Style', 'dokan-pro' ),
                'desc'    => __( 'The style you prefer for vendor to add or edit products. ', 'dokan-pro' ),
                'type'    => 'select',
                'default' => 'old',
                'options' => array(
                    'old' => __( 'Tab View', 'dokan-pro' ),
                    'new' => __( 'Flat View', 'dokan-pro' )
                )
            ),
            'product_category_style' => array(
                'name'    => 'product_category_style',
                'label'   => __( 'Category Selection', 'dokan-pro' ),
                'desc'    => __( 'What option do you prefer for vendor to select product category? ', 'dokan-pro' ),
                'type'    => 'select',
                'default' => 'single',
                'options' => array(
                    'single'   => __( 'Single', 'dokan-pro' ),
                    'multiple' => __( 'Multiple', 'dokan-pro' )
                )
            ),
            'product_status'         => array(
                'name'    => 'product_status',
                'label'   => __( 'New Product Status', 'dokan-pro' ),
                'desc'    => __( 'Product status when a vendor creates a product', 'dokan-pro' ),
                'type'    => 'select',
                'default' => 'pending',
                'options' => array(
                    'publish' => __( 'Published', 'dokan-pro' ),
                    'pending' => __( 'Pending Review', 'dokan-pro' )
                )
            ),
            'discount_edit' => array(
                'name'    => 'discount_edit',
                'label'   => __( 'Discount Editing', 'dokan-pro' ),
                'desc'    => __( 'Vendor can add order and product discount', 'dokan-pro' ),
                'type'    => 'multicheck',
                'default' => array( 'product-discount' => __( 'Discount product', 'dokan-pro' ), 'order-discount' => __( 'Discount Order', 'dokan-pro' ) ),
                'options' => array( 'product-discount' => __( 'Discount product', 'dokan-pro' ), 'order-discount' => __( 'Discount Order', 'dokan-pro' ) )
            )
        );

        $new_settings_fields['dokan_withdraw'] = array(
            'withdraw_order_status' => array(
                'name'    => 'withdraw_order_status',
                'label'   => __( 'Order Status for Withdraw', 'dokan-pro' ),
                'desc'    => __( 'Order status for which vendor can make a withdraw request.', 'dokan-pro' ),
                'type'    => 'multicheck',
                'default' => array( 'wc-completed' => __( 'Completed', 'dokan-pro' ), 'wc-processing' => __( 'Processing', 'dokan-pro' ), 'wc-on-hold' => __( 'On-hold', 'dokan-pro' ) ),
                'options' => array( 'wc-completed' => __( 'Completed', 'dokan-pro' ), 'wc-processing' => __( 'Processing', 'dokan-pro' ), 'wc-on-hold' => __( 'On-hold', 'dokan-pro' ) )
            ),
            'withdraw_date_limit'   => array(
                'name'    => 'withdraw_date_limit',
                'label'   => __( 'Withdraw Threshold', 'dokan-pro' ),
                'desc'    => __( 'Days, ( Make order matured to make a withdraw request) <br> Value "0" will inactive this option', 'dokan-pro' ),
                'default' => '0',
                'type'    => 'text',
            ),
        );

        $settings_fields['dokan_general']  = array_merge( $settings_fields['dokan_general'], $new_settings_fields['dokan_general'] );
        $settings_fields['dokan_selling']  = array_merge( $settings_fields['dokan_selling'], $new_settings_fields['dokan_selling'] );
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
        include dirname( __FILE__ ) . '/refund.php';
    }

    /**
     * Seller Listing template
     *
     * @since 2.4
     *
     * @return void
     */
    function seller_listing() {
        include dirname( __FILE__ ) . '/sellers.php';
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
        include dirname( __FILE__ ) . '/reports.php';
    }

    /**
     * Tools Template
     *
     * @since 2.4
     *
     * @return void
     */
    function tools_page() {
        include dirname( __FILE__ ) . '/tools.php';
    }

    /**
     * Plugin help page
     *
     * @since 2.4.9
     *
     * @return void
     */
    function help_page() {
        include dirname( __FILE__ ) . '/help.php';
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
            $page_created = get_option( 'dokan_pages_created', false );

            $pages = array(
                array(
                    'post_title' => __( 'Dashboard', 'dokan-pro' ),
                    'slug'       => 'dashboard',
                    'page_id'    => 'dashboard',
                    'content'    => '[dokan-dashboard]'
                ),
                array(
                    'post_title' => __( 'Store List', 'dokan-pro' ),
                    'slug'       => 'store-listing',
                    'page_id'    => 'my_orders',
                    'content'    => '[dokan-stores]'
                ),
            );
            $dokan_pages = array() ;
            if ( ! $page_created ) {

                foreach ( $pages as $page ) {
                    $page_id = wp_insert_post( array(
                        'post_title'     => $page['post_title'],
                        'post_name'      => $page['slug'],
                        'post_content'   => $page['content'],
                        'post_status'    => 'publish',
                        'post_type'      => 'page',
                        'comment_status' => 'closed'
                            ) );
                    $dokan_pages[$page['slug']] = $page_id ;
                }
                update_option( 'dokan_pages', $dokan_pages );
                flush_rewrite_rules();
            } else {
                foreach ( $pages as $page ) {

                    if ( !$this->dokan_page_exist( $page['slug'] ) ) {
                        $page_id = wp_insert_post( array(
                            'post_title'     => $page['post_title'],
                            'post_name'      => $page['slug'],
                            'post_content'   => $page['content'],
                            'post_status'    => 'publish',
                            'post_type'      => 'page',
                            'comment_status' => 'closed'
                                ) );
                        $dokan_pages[$page['slug']] = $page_id ;
                        update_option( 'dokan_pages', $dokan_pages );
                    }

                }

                flush_rewrite_rules();
            }
            update_option( 'dokan_pages_created', 1 );
            wp_redirect( admin_url( 'admin.php?page=dokan-tools&msg=page_installed' ) );
            exit;
        }
    }

    /**
     * Check a Donan shortcode  page exist or not
     *
     * @since 2.5
     *
     * @param type $slug
     *
     * @return boolean
     */
    function dokan_page_exist( $slug ) {

        $page_created = get_option( 'dokan_pages_created', false );
        if ( ! $page_created ) {
            return FALSE;
        }
        $page_list = get_option( 'dokan_pages', '' );
        $page = get_post( $page_list[$slug] );

        if ( $page == null ) {
            return FALSE;
        } else {

            return TRUE;
        }
    }

    function render_pro_admin_toolbar() {

        global $wp_admin_bar;

        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        $wp_admin_bar->remove_menu( 'dokan-dashboard' );
        $wp_admin_bar->remove_menu( 'dokan-withdraw' );
        $wp_admin_bar->remove_menu( 'dokan-settings' );

        $wp_admin_bar->add_menu( array(
            'id'     => 'dokan-dashboard',
            'parent' => 'dokan-pro',
            'title'  => __( 'Dokan Dashboard', 'dokan-pro' ),
            'href'   => admin_url( 'admin.php?page=dokan' )
        ) );

        $wp_admin_bar->add_menu( array(
            'id'     => 'dokan-withdraw',
            'parent' => 'dokan-pro',
            'title'  => __( 'Withdraw', 'dokan-pro' ),
            'href'   => admin_url( 'admin.php?page=dokan-withdraw' )
        ) );

        $wp_admin_bar->add_menu( array(
            'id'     => 'dokan-sellers',
            'parent' => 'dokan-pro',
            'title'  => __( 'All Vendors', 'dokan-pro' ),
            'href'   => admin_url( 'admin.php?page=dokan-sellers' )
        ) );

        $wp_admin_bar->add_menu( array(
            'id'     => 'dokan-reports',
            'parent' => 'dokan-pro',
            'title'  => __( 'Earning Reports', 'dokan-pro' ),
            'href'   => admin_url( 'admin.php?page=dokan-reports' )
        ) );

        $wp_admin_bar->add_menu( array(
            'id'     => 'dokan-settings',
            'parent' => 'dokan-pro',
            'title'  => __( 'Settings', 'dokan-pro' ),
            'href'   => admin_url( 'admin.php?page=dokan-settings' )
        ) );
    }
}

// End of Dokan_Pro_Admin_Settings class;