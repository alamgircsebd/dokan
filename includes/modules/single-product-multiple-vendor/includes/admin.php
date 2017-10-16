<?php

/**
* Admin class
*
* @package Dokan Pro
*/
class Dokan_SPMV_Admin {

    /**
     * Load autometically when class initiate
     *
     * @since 1.0.0
     */
    public function __construct() {
        // settings section
        add_filter( 'dokan_settings_sections', array( $this, 'add_new_section_admin_panael' ) );
        add_filter( 'dokan_settings_fields', array( $this, 'add_new_setting_field_admin_panael' ), 12, 1 );
    }

    /**
     * Add new Section in admin dokan settings
     *
     * @param array  $sections
     *
     * @return array
     */
    function add_new_section_admin_panael( $sections ) {
        $sections['dokan_spmv'] = array(
            'id'    => 'dokan_spmv',
            'title' => __( 'Single Product MultiVendor', 'dokan' )
        );

        return $sections;
    }

    /**
     * Add new Settings field in admin settings area
     *
     * @param array  $settings_fields
     *
     * @return array
     */
    function add_new_setting_field_admin_panael( $settings_fields ) {

        $settings_fields['dokan_spmv'] = array(
            array(
                'name'  => 'enable_pricing',
                'label' => __( 'Enable Single Product Multiple Vendor', 'dokan' ),
                'desc'  => __( 'Enable Single Product Multiple Vendor functionality', 'dokan' ),
                'type'  => 'checkbox'
            )
        );

        return $settings_fields;
    }

}