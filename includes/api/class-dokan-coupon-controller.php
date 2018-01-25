<?php

/**
 * REST API Coupons controller
 *
 * Handles requests to the /coupons endpoint.
 *
 * @author   Dokan
 * @category API
 * @package  Dokan/API
 * @since    2.8
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class Dokan_REST_Coupon_Controller extends WP_REST_Controller {

    /**
     * Endpoint namespace.
     *
     * @var string
     */
    protected $namespace = 'dokan/v1';

    /**
     * Route name
     *
     * @var string
     */
    protected $base = 'coupons';

    /**
     * Register all routes related with coupons
     *
     * @return void
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . '(?P<seller_id>\d+)/' . $this->base, array(
            array(
                'methods'  => WP_REST_Server::READABLE,
                'callback' => array( $this, 'get_coupons' ),
            ),
        ) );

        register_rest_route( $this->namespace, '/' . '(?P<seller_id>\d+)/' . $this->base . '/(?P<id>\d+)', array(
            array(
                'methods'  => WP_REST_Server::READABLE,
                'callback' => array( $this, 'get_coupon' ),
            ),
        ) );
    }

    protected function get_object( $id ) {
        return new WC_Coupon( $id );
    }

    /**
     * Get formatted item data.
     *
     * @since  3.0.0
     * @param  WC_Data $object WC_Data instance.
     * @return array
     */
    protected function get_formatted_item_data( $object ) {
        $data = $object->get_data();

        $format_decimal = array( 'amount', 'minimum_amount', 'maximum_amount' );
        $format_date    = array( 'date_created', 'date_modified', 'date_expires' );
        $format_null    = array( 'usage_limit', 'usage_limit_per_user', 'limit_usage_to_x_items' );

        // Format decimal values.
        foreach ( $format_decimal as $key ) {
            $data[$key] = wc_format_decimal( $data[$key], 2 );
        }

        // Format date values.
        foreach ( $format_date as $key ) {
            $datetime            = $data[$key];
            $data[$key]          = wc_rest_prepare_date_response( $datetime, false );
            $data[$key . '_gmt'] = wc_rest_prepare_date_response( $datetime );
        }

        // Format null values.
        foreach ( $format_null as $key ) {
            $data[$key] = $data[$key] ? $data[$key] : null;
        }

        return array(
            'id'                          => $object->get_id(),
            'code'                        => $data['code'],
            'amount'                      => $data['amount'],
            'date_created'                => $data['date_created'],
            'date_created_gmt'            => $data['date_created_gmt'],
            'date_modified'               => $data['date_modified'],
            'date_modified_gmt'           => $data['date_modified_gmt'],
            'discount_type'               => $data['discount_type'],
            'description'                 => $data['description'],
            'date_expires'                => $data['date_expires'],
            'date_expires_gmt'            => $data['date_expires_gmt'],
            'usage_count'                 => $data['usage_count'],
            'individual_use'              => $data['individual_use'],
            'product_ids'                 => $data['product_ids'],
            'excluded_product_ids'        => $data['excluded_product_ids'],
            'usage_limit'                 => $data['usage_limit'],
            'usage_limit_per_user'        => $data['usage_limit_per_user'],
            'limit_usage_to_x_items'      => $data['limit_usage_to_x_items'],
            'free_shipping'               => $data['free_shipping'],
            'product_categories'          => $data['product_categories'],
            'excluded_product_categories' => $data['excluded_product_categories'],
            'exclude_sale_items'          => $data['exclude_sale_items'],
            'minimum_amount'              => $data['minimum_amount'],
            'maximum_amount'              => $data['maximum_amount'],
            'email_restrictions'          => $data['email_restrictions'],
            'used_by'                     => $data['used_by'],
            'meta_data'                   => $data['meta_data'],
        );
    }

    /**
     * Get coupons
     *
     * @return void
     */
    public function get_coupons( $request ) {

        $seller_id = $request['seller_id'];

        if ( !dokan_is_user_seller( $seller_id ) ) {
            return new WP_Error( 'invalid_seller', 'Invalid Seller Id', array( 'status' => 404 ) );
        }

        $coupons = dokan_get_seller_coupon( $seller_id );
        
        $response_data = array();
        
        foreach ( $coupons as $coupon ) {
            $response_data[] = $this->get_formatted_item_data( $this->get_object( $coupon->ID ) );
        }
        
        return new WP_REST_Response( $response_data, 200 );
    }

    /**
     * Get single coupon
     *
     * @return void
     */
    public function get_coupon( $request ) {

        $seller_id = $request['seller_id'];
        $coupon_id = $request['id'];

        if ( !dokan_is_user_seller( $seller_id ) ) {
            return new WP_Error( 'invalid_seller', 'Invalid Seller ID', array( 'status' => 404 ) );
        }
        
        $coupon = $this->get_object( $coupon_id );
        
        if ( !$coupon ) {
            return new WP_Error( 'invalid_coupon', 'Invalid Coupon ID', array( 'status' => 404 ) );
        }
        
        return new WP_REST_Response( $this->get_formatted_item_data( $coupon ), 200 );
    }

}
