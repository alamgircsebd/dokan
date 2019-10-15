<?php

/**
* Frontend vendor product addons
*/
class Dokan_Product_Addon_Frontend {

    /**
     * Load autometically when class initiate
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_filter( 'dokan_get_dashboard_settings_nav', [ $this, 'add_settings_menu' ] );
        add_filter( 'dokan_dashboard_settings_heading_title', [ $this, 'load_settings_header' ], 11, 2 );
        add_filter( 'dokan_dashboard_settings_helper_text', [ $this, 'load_helper' ], 10, 2 );
        add_action( 'dokan_render_settings_content', [ $this, 'render_settings_content' ], 10 );
    }

    /**
     * Initializes the Dokan_Product_Addon_Frontend() class
     *
     * Checks for an existing Dokan_Product_Addon_Frontend() instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new Dokan_Product_Addon_Frontend();
        }

        return $instance;
    }

    /**
     * Add settings menu for global addons
     *
     * @since 1.0.0
     *
     * @param array $settings_tab
     */
    public function add_settings_menu( $settings_tab ) {
        $settings_tab['product-addon'] = [
            'title' => __( 'Addons', 'dokan'),
            'icon'  => '<i class="fa fa-user"></i>',
            'url'   => dokan_get_navigation_url( 'settings/product-addon' ),
            'pos'   => 40
        ];

        return $settings_tab;
    }

    /**
     * Load product addon settings header
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function load_settings_header( $header, $query_vars ) {
        if ( $query_vars == 'product-addon' ) {
            $header = __( 'Product Addons', 'dokan' );
        }

        return $header;
    }

    /**
     * Load Helper Text for addon contents
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function load_helper( $helper_txt, $query_var ) {
        if ( $query_var == 'product-addon' ) {
            $helper_txt = __( 'Set your field type for product addons which is applicable for all product or specific product category globally. You can control this setting seperately from individual products', 'dokan' );
        }

        return $helper_txt;
    }

    /**
     * Render settings contents
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function render_settings_content( $query_vars ) {
        if ( isset( $query_vars['settings'] ) && $query_vars['settings'] == 'product-addon' ) {
            if ( ! empty( $_GET['add'] ) || ! empty( $_GET['edit'] ) ) {
                if ( $_POST ) {
                    // $edit_id = $this->save_global_addons();

                    if ( $edit_id ) {
                        echo '<div class="updated"><p>' . esc_html__( 'Add-on saved successfully', 'woocommerce-product-addons' ) . '</p></div>';
                    }

                    $reference      = wc_clean( $_POST['addon-reference'] );
                    $priority       = absint( $_POST['addon-priority'] );
                    $objects        = ! empty( $_POST['addon-objects'] ) ? array_map( 'absint', $_POST['addon-objects'] ) : array();
                    $product_addons = array_filter( (array) $this->get_posted_product_addons() );
                }
                if ( ! empty( $_GET['edit'] ) ) {

                    $edit_id      = absint( $_GET['edit'] );
                    $global_addon = get_post( $edit_id );

                    if ( ! $global_addon ) {
                        echo '<div class="error">' . esc_html__( 'Error: Add-on not found', 'woocommerce-product-addons' ) . '</div>';
                        return;
                    }

                    $reference      = $global_addon->post_title;
                    $priority       = get_post_meta( $global_addon->ID, '_priority', true );
                    $objects        = (array) wp_get_post_terms( $global_addon->ID, apply_filters( 'woocommerce_product_addons_global_post_terms', array( 'product_cat' ) ), array( 'fields' => 'ids' ) );
                    $product_addons = array_filter( (array) get_post_meta( $global_addon->ID, '_product_addons', true ) );

                    if ( get_post_meta( $global_addon->ID, '_all_products', true ) == 1 ) {
                        $objects[] = 0;
                    }
                } elseif ( ! empty( $edit_id ) ) {

                    $global_addon   = get_post( $edit_id );
                    $reference      = $global_addon->post_title;
                    $priority       = get_post_meta( $global_addon->ID, '_priority', true );
                    $objects        = (array) wp_get_post_terms( $global_addon->ID, apply_filters( 'woocommerce_product_addons_global_post_terms', array( 'product_cat' ) ), array( 'fields' => 'ids' ) );
                    $product_addons = array_filter( (array) get_post_meta( $global_addon->ID, '_product_addons', true ) );

                    if ( get_post_meta( $global_addon->ID, '_all_products', true ) == 1 ) {
                        $objects[] = 0;
                    }
                } else {

                    $global_addons_count = wp_count_posts( 'global_product_addon' );
                    $reference           = __( 'Add-ons Group', 'woocommerce-product-addons' ) . ' #' . ( $global_addons_count->publish + 1 );
                    $priority            = 10;
                    $objects             = array( 0 );
                    $product_addons      = array();
                }

                include( DOKAN_PRODUCT_ADDON_DIR . '/views/html-global-admin-add.php' );
            } else {

                if ( ! empty( $_GET['delete'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'delete_addon' ) ) {
                    wp_delete_post( absint( $_GET['delete'] ), true );
                    echo '<div class="updated"><p>' . esc_html__( 'Add-on deleted successfully', 'woocommerce-product-addons' ) . '</p></div>';
                }

                include( DOKAN_PRODUCT_ADDON_DIR . '/views/html-global-admin.php' );
            }
        }
    }

}
