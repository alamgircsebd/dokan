<?php

class Dokan_Store_Category {

    public function __construct() {
        $this->register_taxonomy();
    }

    private function register_taxonomy() {
        register_taxonomy(
            'store_category',
            'dokan_seller',
            array(
                'hierarchical'          => false,
                'label'                 => __( 'Store Categories', 'dokan' ),
                'show_ui'               => false,
                'query_var'             => true,
                'capabilities'          => array(
                    'manage_terms' => 'shop_manager',
                    'edit_terms'   => 'shop_manager',
                    'delete_terms' => 'shop_manager',
                    'assign_terms' => 'dokandar',
                ),
                'rewrite'               => array(
                    'slug'         => 'store-category',
                    'with_front'   => false,
                    'hierarchical' => true,
                ),
                'show_in_rest' => true,
            )
        );
    }
}

