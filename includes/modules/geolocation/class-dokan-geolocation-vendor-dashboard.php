<?php

/**
 * Vendor dashboard functionalities
 *
 * @since 1.0.0
 */
class Dokan_Geolocation_Vendor_Dashboard {

    /**
     * Class constructor
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function __construct() {
        add_action( 'dokan_store_profile_saved', array( $this, 'save_vendor_geodata' ), 10, 2 );
        add_action( 'dokan_product_edit_after_options', array( $this, 'add_product_editor_options' ) );
        add_action( 'dokan_product_updated', array( $this, 'update_product_settings' ) );
    }

    /**
     * Use store settings option
     *
     * @since 1.0.0
     *
     * @param int $post_id
     *
     * @return string
     */
    public function use_store_settings( $post_id ) {
        $use_store_settings = get_post_meta( $post_id, '_dokan_geolocation_use_store_settings', true );

        if ( empty( $use_store_settings ) || 'yes' === $use_store_settings ) {
            return 'yes';
        } else {
            return 'no';
        }
    }

    /**
     * Save vendor geodata
     *
     * @since 1.0.0
     *
     * @param int   $store_id
     * @param array $dokan_settings
     *
     * @return void
     */
    public function save_vendor_geodata( $store_id, $dokan_settings ) {
        if ( isset( $dokan_settings['location'] ) && isset( $dokan_settings['find_address'] ) ) {
            $location = explode( ',', $dokan_settings['location'] );

            if ( 2 !== count( $location ) ) {
                return;
            }

            update_usermeta( $store_id, 'geo_latitude', $location[0] );
            update_usermeta( $store_id, 'geo_longitude', $location[1] );
            update_usermeta( $store_id, 'geo_public', 1 );
            update_usermeta( $store_id, 'geo_address', $dokan_settings['find_address'] );
        }
    }

    /**
     * Add product editor options/settings
     *
     * @since 1.0.0
     *
     * @param int $post_id
     *
     * @return void
     */
    public function add_product_editor_options( $post_id ) {
        $use_store_settings = $this->use_store_settings( $post_id );

        if ( ! $use_store_settings ) {
            $store_id      = dokan_get_current_user_id();
            $geo_latitude  = get_user_meta( $store_id, 'geo_latitude', true );
            $geo_longitude = get_user_meta( $store_id, 'geo_longitude', true );
            $geo_public    = get_user_meta( $store_id, 'geo_public', true );
            $geo_address   = get_user_meta( $store_id, 'geo_address', true );

        } else {
            $geo_latitude  = get_post_meta( $post_id, 'geo_latitude', true );
            $geo_longitude = get_post_meta( $post_id, 'geo_longitude', true );
            $geo_public    = get_post_meta( $post_id, 'geo_public', true );
            $geo_address   = get_post_meta( $post_id, 'geo_address', true );
        }

        if ( ! $geo_latitude || ! $geo_longitude ) {
            $default_locations = dokan_geo_get_default_location();
            $geo_latitude  = $default_locations['latitude'];
            $geo_longitude = $default_locations['longitude'];
        }

        $args = array(
            'post_id'            => $post_id,
            'use_store_settings' => $use_store_settings,
            'geo_latitude'       => $geo_latitude,
            'geo_longitude'      => $geo_longitude,
            'geo_public'         => $geo_public,
            'geo_address'        => $geo_address,
        );

        dokan_geo_get_template( 'product-editor-options', $args );
    }

    /**
     * Update product settings
     *
     * @since 1.0.0
     *
     * @param int $post_id
     *
     * @return void
     */
    public function update_product_settings( $post_id ) {
        $store_id      = dokan_get_current_user_id();
        $geo_latitude  = get_user_meta( $store_id, 'geo_latitude', true );
        $geo_longitude = get_user_meta( $store_id, 'geo_longitude', true );
        $geo_public    = get_user_meta( $store_id, 'geo_public', true );
        $geo_address   = get_user_meta( $store_id, 'geo_address', true );

        $use_store_settings = ( 'yes' === $_POST['_dokan_geolocation_use_store_settings'] ) ? 'yes' : 'no';

        update_post_meta( $post_id, '_dokan_geolocation_use_store_settings', $use_store_settings );

        if ( 'yes' !== $use_store_settings ) {
            $geo_latitude  = ! empty( $_POST['_dokan_geolocation_product_geo_latitude'] ) ? $_POST['_dokan_geolocation_product_geo_latitude'] : null;
            $geo_longitude = ! empty( $_POST['_dokan_geolocation_product_geo_longitude'] ) ? $_POST['_dokan_geolocation_product_geo_longitude'] : null;
            $geo_address   = ! empty( $_POST['_dokan_geolocation_product_geo_address'] ) ? $_POST['_dokan_geolocation_product_geo_address'] : null;
        }

        update_post_meta( $post_id, 'geo_latitude', $geo_latitude );
        update_post_meta( $post_id, 'geo_longitude', $geo_longitude );
        update_post_meta( $post_id, 'geo_public', $geo_public );
        update_post_meta( $post_id, 'geo_address', $geo_address );
    }
}
