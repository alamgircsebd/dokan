<?php

class Dokan_Store_Category {

    public function __construct() {
        add_filter( 'dokan_settings_fields', array( $this, 'add_admin_settings' ) );
        $category_type = dokan_get_option( 'store_category_type', 'dokan_general', 'none' );

        if ( 'single' === $category_type || 'multiple' === $category_type ) {
            $this->register_taxonomy();
            add_action( 'dokan_settings_after_store_name', array( $this, 'add_store_category_option' ) );
            add_action( 'dokan_seller_wizard_store_setup_after_address_field', array( $this, 'seller_wizard_add_store_category_option' ) );
            add_action( 'dokan_store_profile_saved', array( $this, 'after_store_profile_saved' ) );
            add_action( 'dokan_store_profile_saved_via_rest', array( $this, 'after_store_profile_saved' ) );
            add_action( 'dokan_seller_wizard_store_field_save', array( $this, 'after_seller_wizard_store_field_save' ) );

        }
    }

    public function add_admin_settings( $dokan_settings_fields ) {
        $dokan_settings_fields['dokan_general']['store_category_type'] = array(
            'name'    => 'store_category_type',
            'label'   => __( 'Store category', 'dokan' ),
            'type'    => 'select',
            'options' => array(
                'none'     => __( 'None', 'dokan' ),
                'single'   => __( 'Single', 'dokan' ),
                'multiple' => __( 'Multiple', 'dokan' ),
            ),
            'default' => 'none'
        );

        return $dokan_settings_fields;
    }

    private function register_taxonomy() {
        register_taxonomy(
            'store_category',
            'dokan_seller',
            array(
                'hierarchical' => false,
                'label'        => __( 'Store Categories', 'dokan' ),
                'show_ui'      => false,
                'query_var'    => true,
                'capabilities' => array(
                    'manage_terms' => 'manage_woocommerce',
                    'edit_terms'   => 'manage_woocommerce',
                    'delete_terms' => 'manage_woocommerce',
                    'assign_terms' => 'manage_woocommerce',
                ),
                'rewrite'      => array(
                    'slug'         => 'store-category',
                    'with_front'   => false,
                    'hierarchical' => true,
                ),
                'show_in_rest' => true,
            )
        );
    }

    public function add_store_category_option( $current_user, $args = array(), $template_name = 'settings/store-form-categories' ) {
        $store_categories = get_the_terms( $current_user, 'store_category' );

        if ( ! term_exists( 'Uncategorized', 'store_category' ) ) {
            wp_insert_term( 'Uncategorized', 'store_category' );
        }

        $categories = get_terms( array(
            'taxonomy'   => 'store_category',
            'hide_empty' => false,
        ) );

        $store_categories = wp_get_object_terms( $current_user, 'store_category', array( 'fields' => 'ids' ) );
        $category_type    = dokan_get_option( 'store_category_type', 'dokan_general', 'none' );
        $is_multiple      = ( 'multiple' === $category_type ) || false;

        $defaults = array(
            'pro'              => true,
            'categories'       => $categories,
            'store_categories' => $store_categories,
            'is_multiple'      => $is_multiple,
            'label'            => $is_multiple ? __( 'Store Categories', 'dokan' ) : __( 'Store Category', 'dokan' ),
        );

        $args = wp_parse_args( $args, $defaults );

        dokan_get_template_part( $template_name, '', $args );
    }

    public function seller_wizard_add_store_category_option( $wizard ) {
        $current_user = get_current_user_id();
        $this->add_store_category_option( $current_user, array(), 'settings/seller-wizard-store-form-categories' );
    }

    public function after_store_profile_saved( $store_id ) {
        $store_categories = ! empty( $_POST['dokan_store_categories'] ) ? $_POST['dokan_store_categories'] : null;
        dokan_set_store_categories( $store_id, $store_categories );
    }

    public function after_seller_wizard_store_field_save( $wizard ) {
        $store_categories = ! empty( $_POST['dokan_store_categories'] ) ? $_POST['dokan_store_categories'] : null;
        dokan_set_store_categories( $wizard->store_id, $store_categories );
    }
}
