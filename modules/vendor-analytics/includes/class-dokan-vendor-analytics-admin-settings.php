<?php

use WeDevs\Dokan\Exceptions\DokanException;

class Dokan_Vendor_Analytics_Admin_Settings {

    /**
     * Class constructor
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function __construct() {
        add_action( 'woocommerce_api_dokan_vendor_analytics', array( $this, 'save_token' ) );
        add_filter( 'dokan_settings_sections', array( $this, 'add_settings_section' ) );
        add_filter( 'dokan_settings_fields', array( $this, 'add_settings_fields' ) );
        add_filter( 'dokan_settings_refresh_option_dokan_vendor_analytics_profile', array( $this, 'refresh_admin_settings_option_profile' ) );
    }

    /**
     * Save token got from api authentication
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function save_token() {
        $token_params = array(
            'access_token',
            'expires_in',
            'refresh_token',
            'scope',
            'token_type',
            'created',
        );

        $token = array();

        foreach ( $token_params as $param ) {
            if ( empty( $_GET[ $param ] ) ) {
                die( sprintf( __( '%s param not found in token', 'dokan' ), $param ) );
            } else {
                $token[ $param ] = $_GET[ $param ];
            }
        }

        $options = array(
            'token'    => json_encode( $token ),
            'profiles' => array(),
        );

        update_option( 'dokan_vendor_analytics_google_api_data', $options, false );

        wp_safe_redirect( admin_url( 'admin.php?page=dokan#/settings' ) );
        exit;
    }

    /**
     * Add admin settings section
     *
     * @since 1.0.0
     *
     * @param array $sections
     *
     * @return array
     */
    public function add_settings_section( $sections ) {
        $sections['dokan_vendor_analytics'] = array(
            'id'    => 'dokan_vendor_analytics',
            'title' => __( 'Vendor Analytics', 'dokan' ),
            'icon'  => 'dashicons-chart-area'
        );

        return $sections;
    }

    /**
     * Add admin settings fields
     *
     * @since 1.0.0
     *
     * @param array $settings_fields
     *
     * @return array
     */
    public function add_settings_fields( $settings_fields ) {
        $api_data  = get_option( 'dokan_vendor_analytics_google_api_data', array() );
        $token_raw = ! empty( $api_data['token'] ) ? $api_data['token'] : '{}';
        $token     = json_decode( $token_raw, true );
        $profiles  = ! empty( $api_data['profiles'] ) ? $api_data['profiles'] : array();

        $analytics_fields = array();

        if ( empty( $token['access_token'] ) && empty( $token['refresh_token'] ) ) {
            $auth_url = dokan_vendor_analytics_get_auth_url();

            $analytics_fields = array(
                'authenticate_user'  => array(
                    'name'  => 'authenticate_user',
                    'label' => __( 'Authenticate', 'dokan' ),
                    'type'  => 'html',
                    'desc'  => sprintf( '<a href="%s">%s</a>', $auth_url, __( 'Log in with Google Analytics Account', 'dokan' ) ),
                ),
            );
        } else {
            if ( empty( $profiles ) ) {
                $profiles = dokan_vendor_analytics_api_get_profiles();
            }

            $analytics_fields = array(
                'profile'  => array(
                    'name'        => 'profile',
                    'label'       => __( 'Analytics Profile', 'dokan' ),
                    'type'        => 'select',
                    'placeholder' => __( 'Select your profile', 'dokan' ),
                    'grouped'     => true,
                    'options'     => $profiles,
                    'refresh_options' => array(
                        'messages' => array(
                            'refreshing' => __( 'Refreshing profiles', 'dokan' ),
                            'refreshed'  => __( 'Profiles refreshed!', 'dokan' ),
                        ),
                    ),
                ),
            );
        }

        $settings_fields['dokan_vendor_analytics'] = $analytics_fields;

        return $settings_fields;
    }

    /**
     * Refresh profiles in admin settings
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return array
     */
    public function refresh_admin_settings_option_profile() {
        $profiles = dokan_vendor_analytics_api_get_profiles();

        if ( is_wp_error( $profiles ) ) {
            throw new DokanException(
                $profiles->get_error_code(),
                $profiles->get_error_message()
            );
        }

        return $profiles;
    }
}
