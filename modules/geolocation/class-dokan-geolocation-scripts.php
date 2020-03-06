<?php

/**
 * Geolocation Module Scripts
 *
 * @since 1.0.0
 */
class Dokan_Geolocation_Scripts {

    /**
     * Class constructor
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'register_styles' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );

        add_filter( 'dokan_google_maps_script_query_args', array( $this, 'add_gmap_script_query_args' ) );
    }

    /**
     * Register module styles
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function register_styles() {
        wp_register_style( 'dokan-geo-locations-map', DOKAN_GEOLOCATION_ASSETS . '/css/dokan-geolocation-locations-map.css', array(), DOKAN_PRO_PLUGIN_VERSION );
        wp_register_style( 'dokan-geo-filters', DOKAN_GEOLOCATION_ASSETS . '/css/dokan-geolocation-filters.css', array(), DOKAN_PRO_PLUGIN_VERSION );
    }

    /**
     * Register module scripts
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function register_scripts() {
        $source = dokan_get_option( 'map_api_source', 'dokan_appearance', 'google_maps' );

        $js_src = ( 'mapbox' === $source ) ? '/js/dokan-geolocation-locations-map-mapbox.js' : '/js/dokan-geolocation-locations-map-google-maps.js';

        wp_register_script( 'dokan-geo-locations-map', DOKAN_GEOLOCATION_ASSETS . $js_src, array( 'jquery' ), DOKAN_PRO_PLUGIN_VERSION, true );

        wp_register_script( 'dokan-geo-filters-store-lists', DOKAN_GEOLOCATION_ASSETS . '/js/dokan-geolocation-store-lists-filters.js', array( 'jquery', 'google-maps' ), DOKAN_GEOLOCATION_VERSION, true );
        wp_register_script( 'dokan-geo-filters', DOKAN_GEOLOCATION_ASSETS . '/js/dokan-geolocation-filters.js', array( 'jquery', 'underscore', 'dokan-maps' ), DOKAN_PRO_PLUGIN_VERSION, true );
    }

    /**
     * Add google map script url query args
     *
     * Geolocation module requires 'places' library for autocomple feature
     *
     * @since 1.0.0
     *
     * @param array $query_args
     */
    public function add_gmap_script_query_args( $query_args ) {
        $query_args['libraries'] = 'places';

        return $query_args;
    }
}
