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
        add_action( 'dokan_render_admin_toolbar', array( $this, 'render_pro_admin_toolbar' ) );
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

        remove_submenu_page( 'dokan', 'dokan-pro-features' );

        if ( $refund['pending'] ) {
            $refund_text = sprintf( __( 'Refund Request %s', 'dokan' ), '<span class="awaiting-mod count-1"><span class="pending-count">' . $refund['pending'] . '</span></span>' );
        }

        add_submenu_page( 'dokan', __( 'Refund Request', 'dokan' ), $refund_text, $capability, 'dokan-refund', array( $this, 'refund_request' ) );
        add_submenu_page( 'dokan', __( 'Vendors Listing', 'dokan' ), __( 'All Vendors', 'dokan' ), $capability, 'dokan-sellers', array( $this, 'seller_listing' ) );
        $report       = add_submenu_page( 'dokan', __( 'Earning Reports', 'dokan' ), __( 'Earning Reports', 'dokan' ), $capability, 'dokan-reports', array( $this, 'report_page' ) );
        $announcement = add_submenu_page( 'dokan', __( 'Announcement', 'dokan' ), __( 'Announcement', 'dokan' ), $capability, 'edit.php?post_type=dokan_announcement' );
        add_submenu_page( 'dokan', __( 'Tools', 'dokan' ), __( 'Tools', 'dokan' ), $capability, 'dokan-tools', array( $this, 'tools_page' ) );

        add_action( $report, array( $this, 'report_scripts' ) );
        add_action( 'admin_print_scripts-post-new.php', array( $this, 'announcement_scripts' ), 11 );
        add_action( 'admin_print_scripts-post.php', array( $this, 'announcement_scripts' ), 11 );

        add_submenu_page( 'dokan', __( 'Help', 'dokan' ), __( '<span style="color:#f18500">Help</span>', 'dokan' ), $capability, 'dokan-help', array( $this, 'help_page' ) );
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
                'label'   => __( 'Product Mail Notification', 'dokan' ),
                'desc'    => __( 'Email notification on new product submission', 'dokan' ),
                'type'    => 'checkbox',
                'default' => 'on'
            ),
            'enable_tc_on_reg'           => array(
                'name'    => 'enable_tc_on_reg',
                'label'   => __( 'Enable Terms and Condition', 'dokan' ),
                'desc'    => __( 'Enable Terms and Condition check on registration form', 'dokan' ),
                'type'    => 'checkbox',
                'default' => 'on'
            ),
            'store_banner_width' => array(
                'name'    => 'store_banner_width',
                'label'   => __( 'Store Banner width', 'dokan' ),
                'type'    => 'text',
                'default' => 625
            ),
            'store_banner_height' => array(
                'name'    => 'store_banner_height',
                'label'   => __( 'Store Banner height', 'dokan' ),
                'type'    => 'text',
                'default' => 300
            ),
        );

        $new_settings_fields['dokan_selling'] = array(
            'product_style'          => array(
                'name'    => 'product_style',
                'label'   => __( 'Add/Edit Product Style', 'dokan' ),
                'desc'    => __( 'The style you prefer for vendor to add or edit products. ', 'dokan' ),
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
                'desc'    => __( 'What option do you prefer for vendor to select product category? ', 'dokan' ),
                'type'    => 'select',
                'default' => 'single',
                'options' => array(
                    'single'   => __( 'Single', 'dokan' ),
                    'multiple' => __( 'Multiple', 'dokan' )
                )
            ),
            'product_status'         => array(
                'name'    => 'product_status',
                'label'   => __( 'New Product Status', 'dokan' ),
                'desc'    => __( 'Product status when a vendor creates a product', 'dokan' ),
                'type'    => 'select',
                'default' => 'pending',
                'options' => array(
                    'publish' => __( 'Published', 'dokan' ),
                    'pending' => __( 'Pending Review', 'dokan' )
                )
            ),
            'vendor_duplicate_product' => array(
                'name'    => 'vendor_duplicate_product',
                'label'   => __( 'Duplicate product', 'dokan' ),
                'desc'    => __( 'Allow vendor to duplicate their product', 'dokan' ),
                'type'    => 'checkbox',
                'default' => 'on'
            ),
            'discount_edit' => array(
                'name'    => 'discount_edit',
                'label'   => __( 'Discount Editing', 'dokan' ),
                'desc'    => __( 'Vendor can add order and product discount', 'dokan' ),
                'type'    => 'multicheck',
                'default' => array( 'product-discount' => __( 'Discount product', 'dokan' ), 'order-discount' => __( 'Discount Order', 'dokan' ) ),
                'options' => array( 'product-discount' => __( 'Discount product', 'dokan' ), 'order-discount' => __( 'Discount Order', 'dokan' ) )
            )
        );

        $new_settings_fields['dokan_withdraw'] = array(
            'withdraw_order_status' => array(
                'name'    => 'withdraw_order_status',
                'label'   => __( 'Order Status for Withdraw', 'dokan' ),
                'desc'    => __( 'Order status for which vendor can make a withdraw request.', 'dokan' ),
                'type'    => 'multicheck',
                'default' => array( 'wc-completed' => __( 'Completed', 'dokan' ), 'wc-processing' => __( 'Processing', 'dokan' ), 'wc-on-hold' => __( 'On-hold', 'dokan' ) ),
                'options' => array( 'wc-completed' => __( 'Completed', 'dokan' ), 'wc-processing' => __( 'Processing', 'dokan' ), 'wc-on-hold' => __( 'On-hold', 'dokan' ) )
            ),
            'withdraw_date_limit'   => array(
                'name'    => 'withdraw_date_limit',
                'label'   => __( 'Withdraw Threshold', 'dokan' ),
                'desc'    => __( 'Days, ( Make order matured to make a withdraw request) <br> Value "0" will inactive this option', 'dokan' ),
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
        wp_enqueue_style( 'dokan-admin-report', DOKAN_PRO_PLUGIN_ASSEST . '/css/admin.css' );
        wp_enqueue_style( 'jquery-ui' );
        wp_enqueue_style( 'dokan-chosen-style' );

        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_script( 'dokan-flot' );
        wp_enqueue_script( 'dokan-chart' );
        wp_enqueue_script( 'dokan-chosen' );
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
            wp_enqueue_script( 'dokan-chosen' );
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

    function render_pro_admin_toolbar( $wp_admin_bar ) {

        $wp_admin_bar->remove_menu( 'dokan-pro-features' );

        $wp_admin_bar->add_menu( array(
            'id'     => 'dokan-sellers',
            'parent' => 'dokan',
            'title'  => __( 'All Vendors', 'dokan' ),
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
}

// End of Dokan_Pro_Admin_Settings class;