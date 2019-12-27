<?php

namespace WeDevs\DokanPro\Brands;

class Hooks {

    public function __construct() {
        add_action( 'yith_wcbr_init', [ $this, 'init' ], 11 );
    }

    /**
     * Initiate functionalities
     *
     * @since 2.9.7
     *
     * @return void
     */
    public function init() {
        dokan_pro()->brands->set_is_active( true );

        if ( class_exists( 'YITH_WCBR_Premium' ) ) {
            dokan_pro()->brands->set_is_premium_active( true );
        }

        dokan_pro()->brands->set_settings( [
            'mode' => dokan_get_option( 'product_brands_mode', 'dokan_selling', 'single' ),
        ] );

        add_filter( 'dokan_settings_fields', [ AdminSettings::class, 'add_admin_settings_fields' ], 11, 2 );
        add_action( 'dokan_new_product_after_product_tags', [ FormFields::class, 'new_product_form_field' ] );
        add_action( 'dokan_product_edit_after_product_tags', [ FormFields::class, 'product_edit_form_field' ], 10, 2 );
        add_action( 'dokan_new_product_added', [ FormFields::class, 'set_product_brands' ], 10, 2 );
        add_action( 'dokan_product_updated', [ FormFields::class, 'set_product_brands' ], 10, 2 );
    }
}
