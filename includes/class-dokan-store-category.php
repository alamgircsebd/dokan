<?php

class Dokan_Store_Category {

    public function __construct() {
        $this->register_taxonomy();

        add_action( 'dokan_settings_after_store_name', array( $this, 'add_store_category_option' ), 10, 2 );
        add_action( 'dokan_store_profile_saved', array( $this, 'after_store_profile_saved' ), 10, 2 );
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

    public function add_store_category_option( $current_user, $profile_info ) {
        $store_categories = get_the_terms( $current_user, 'store_category' );

        if ( ! term_exists( 'Uncategorized', 'store_category' ) ) {
            wp_insert_term( 'Uncategorized', 'store_category' );
        }

        $categories = get_terms( array(
            'taxonomy'   => 'store_category',
            'hide_empty' => false,
        ) );

        $store_categories = wp_get_object_terms( $current_user, 'store_category', array( 'fields' => 'ids' ) );

        dokan_get_template_part( 'settings/store-form-categories', '', array(
            'pro'              => true,
            'categories'       => $categories,
            'store_categories' => $store_categories
        ));
    }

    public function after_store_profile_saved( $store_id, $dokan_settings ) {
        $store_categories = ! empty( $_POST['dokan_store_categories'] ) ? $_POST['dokan_store_categories'] : null;

        dokan_set_store_categories( $store_id, $store_categories );
    }
}
