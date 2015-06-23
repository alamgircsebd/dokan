<?php

/**
*  Dokan Pro Template Settings class
*
*  @since 2.4
*
*  @package dokan
*/
class Dokan_Pro_Settings extends Dokan_Template_Settings {

    /**
     * Load autometically when class initiate
     *
     * @since 2.4
     *
     * @uses actions hook
     * @uses filter hook
     *
     * @return void
     */
    public function __construct() {
        add_filter( 'dokan_get_dashboard_settings_nav', array( $this, 'load_settings_menu' ), 10 );
    }


    public function load_settings_menu( $sub_settins ) {

        $dokan_shipping_option = get_option( 'woocommerce_dokan_product_shipping_settings' );
        $enable_shipping       = ( isset( $dokan_shipping_option['enabled'] ) ) ? $dokan_shipping_option['enabled'] : 'yes';

        if ( $enable_shipping == 'yes' ) {
            $sub_settins['shipping'] = array(
                'title' => __( 'Shipping', 'dokan'),
                'icon'  => '<i class="fa fa-truck"></i>',
                'url'   => dokan_get_navigation_url( 'settings/shipping' ),
                'pos'   => 70
            );
        }

        $sub_settins['social'] = array(
            'title' => __( 'Social Profile', 'dokan'),
            'icon'  => '<i class="fa fa-share-alt-square"></i>',
            'url'   => dokan_get_navigation_url( 'settings/social' ),
            'pos'   => 90
        );

        if ( dokan_get_option( 'store_seo', 'dokan_general', 'on' ) === 'on' ) {
            $sub_settins['seo'] = array(
                'title' => __( 'Store SEO', 'dokan' ),
                'icon'  => '<i class="fa fa-globe"></i>',
                'url'   => dokan_get_navigation_url( 'settings/seo' ),
                'pos'   => 110
            );
        }

        return $sub_settins;
    }


}