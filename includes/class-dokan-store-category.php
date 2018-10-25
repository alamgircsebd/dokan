<?php

class Dokan_Store_Category {

    /**
     * Class constructor
     *
     * @since 2.9.2
     */
    public function __construct() {
        $this->register_taxonomy();

        add_filter( 'dokan_settings_fields', array( $this, 'add_admin_settings' ) );
        add_action( 'dokan_after_saving_settings', array( $this, 'set_default_category' ), 10, 2 );

        if ( dokan_is_store_categories_feature_on() ) {
            add_action( 'dokan_settings_after_store_name', array( $this, 'add_store_category_option' ) );
            add_action( 'dokan_seller_wizard_store_setup_after_address_field', array( $this, 'seller_wizard_add_store_category_option' ) );
            add_action( 'dokan_store_profile_saved', array( $this, 'after_store_profile_saved' ) );
            add_action( 'dokan_store_profile_saved_via_rest', array( $this, 'after_store_profile_saved' ) );
            add_action( 'dokan_seller_wizard_store_field_save', array( $this, 'after_seller_wizard_store_field_save' ) );
        }
    }

    /**
     * Register Store Category
     *
     * @since 2.9.2
     *
     * @return void
     */
    private function register_taxonomy() {
        register_taxonomy(
            'store_category',
            'dokan_seller',
            array(
                'hierarchical' => false,
                'label'        => __( 'Store Categories', 'dokan' ),
                'show_ui'      => false,
                'query_var'    => dokan_is_store_categories_feature_on(),
                'capabilities' => array(
                    'manage_terms' => 'manage_woocommerce',
                    'edit_terms'   => 'manage_woocommerce',
                    'delete_terms' => 'manage_woocommerce',
                    'assign_terms' => 'manage_woocommerce',
                ),
                'rewrite'      => array(
                    'slug'         => 'store-category',
                    'with_front'   => false,
                    'hierarchical' => false,
                ),
                'show_in_rest' => dokan_is_store_categories_feature_on(),
            )
        );
    }

    /**
     * Add admin settings
     *
     * @since 2.9.2
     *
     * @param array $dokan_settings_fields
     *
     * @return array
     */
    public function add_admin_settings( $dokan_settings_fields ) {
        $dokan_settings_fields['dokan_general']['store_category_type'] = array(
            'name'    => 'store_category_type',
            'label'   => __( 'Store Category', 'dokan' ),
            'type'    => 'select',
            'options' => array(
                'none'     => __( 'None', 'dokan' ),
                'single'   => __( 'Single', 'dokan' ),
                'multiple' => __( 'Multiple', 'dokan' ),
            ),
            'default' => 'none'
        );

        $default_category = dokan_get_default_store_category_id();

        $categories = get_terms( array(
            'taxonomy'   => 'store_category',
            'hide_empty' => false,
        ) );

        $options = wp_list_pluck( $categories, 'name', 'term_id' );

        $dokan_settings_fields['dokan_general']['store_category_default'] = array(
            'name'    => 'store_category_default',
            'label'   => __( 'Default Store Category', 'dokan' ),
            'type'    => 'select',
            'options' => $options,
            'default' => $default_category,
        );

        return $dokan_settings_fields;
    }

    /**
     * Set default category
     *
     * @since 2.9.2
     *
     * @param string $option_key
     * @param array  $option_value
     */
    public function set_default_category( $option_key, $option_value ) {
        if ( 'dokan_general' !== $option_key ) {
            return;
        }

        if ( ! empty( $option_value['store_category_default'] ) ) {
            update_option( 'default_store_category', $option_value['store_category_default'], false );
        }
    }

    /**
     * Add Store Category option in provided template
     *
     * @since 2.9.2
     *
     * @param int    $current_user
     * @param array  $args
     * @param string $template_name
     *
     * @return void
     */
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

    /**
     * Add Store Categories option in seller wizard
     *
     * @since 2.9.2
     *
     * @param Dokan_Seller_Setup_Wizard $wizard
     *
     * @return void
     */
    public function seller_wizard_add_store_category_option( $wizard ) {
        $current_user = get_current_user_id();
        $this->add_store_category_option( $current_user, array(), 'settings/seller-wizard-store-form-categories' );
    }

    /**
     * Set store categories after store file is saved
     *
     * @since 2.9.2
     *
     * @param int $store_id
     *
     * @return void
     */
    public function after_store_profile_saved( $store_id ) {
        $store_categories = ! empty( $_POST['dokan_store_categories'] ) ? $_POST['dokan_store_categories'] : null;
        dokan_set_store_categories( $store_id, $store_categories );
    }

    /**
     * Set store categories after wizard settings is saved
     *
     * @since 2.9.2
     *
     * @param Dokan_Seller_Setup_Wizard $wizard
     *
     * @return void
     */
    public function after_seller_wizard_store_field_save( $wizard ) {
        $store_categories = ! empty( $_POST['dokan_store_categories'] ) ? $_POST['dokan_store_categories'] : null;
        dokan_set_store_categories( $wizard->store_id, $store_categories );
    }
}
