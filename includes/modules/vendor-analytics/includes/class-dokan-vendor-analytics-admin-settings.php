<?php

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
                $client = dokan_vendor_analytics_client();
                $client->setAccessToken( $token_raw );

                $service = new Dokan_Service_Analytics( $client );
                $profile_items = $service->management_accountSummaries->listManagementAccountSummaries()->getItems();

                if ( ! empty( $profile_items ) ) {
                    foreach ( $profile_items as $item ) {
                        foreach ( $item['webProperties'] as $web_properties ) {
                            $group = array(
                                'group_label' => $web_properties->getName(),
                                'group_values' => array(),
                            );

                            foreach ( $web_properties->getProfiles() as $profile ) {
                                $group['group_values'][] = array(
                                    'label' => $profile->name . ' (' . $web_properties->id . ')',
                                    'value' => 'ga:' . $profile->id,
                                );
                            }

                            $profiles[] = $group;
                        }
                    }

                    $api_data['profiles'] = $profiles;

                    update_option( 'dokan_vendor_analytics_google_api_data', $api_data, false );
                }
            }

            $analytics_fields = array(
                'profile'  => array(
                    'name'        => 'profile',
                    'label'       => __( 'Analytics Profile', 'dokan' ),
                    'type'        => 'select',
                    'placeholder' => __( 'Select your profile', 'dokan' ),
                    'grouped'     => true,
                    'options'     => $profiles,
                ),
            );
        }

        $settings_fields['dokan_vendor_analytics'] = $analytics_fields;

        return $settings_fields;
    }
}
