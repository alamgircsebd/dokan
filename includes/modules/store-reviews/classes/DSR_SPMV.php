<?php

class DSR_SPMV {

    /**
     * Product vendors
     *
     * @since DOKAN_PRO_SINCE
     *
     * @var array
     */
    protected $product_vendors = [];

    /**
     * Vendor ratings
     *
     * @since DOKAN_PRO_SINCE
     *
     * @var array
     */
    protected $vendor_ratings = [];

    /**
     * Class constructor
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    public function __construct() {
        add_filter( 'dokan_spmv_show_order_options', array( $this, 'add_show_order_option' ) );
        add_filter( 'dokan_spmv_cloned_product_order', array( $this, 'set_cloned_product_order' ), 10, 4 );
    }

    /**
     * Add show order option
     *
     * @since DOKAN_PRO_SINCE
     *
     * @param array $options
     *
     * @return array
     */
    public function add_show_order_option( $options ) {
        $options[] = array(
            'name'  => 'top_rated_vendor',
            'label' => __( 'Top rated vendor', 'dokan' ),
        );

        return $options;
    }

    /**
     * Filter cloned product order
     *
     * @since DOKAN_PRO_SINCE
     *
     * @param string      $price
     * @param \WC_Product $a
     * @param \WC_Product $b
     * @param string      $show_order
     *
     * @return int|double
     */
    public function set_cloned_product_order( $price, $a, $b, $show_order ) {
        if ( 'top_rated_vendor' === $show_order ) {
            return $this->get_product_vendor_review( $b ) - $this->get_product_vendor_review( $a );
        }

        return $price;
    }

    /**
     * Review rating for a vendor
     *
     * @since DOKAN_PRO_SINCE
     *
     * @param \WC_Product $product
     *
     * @return double
     */
    protected function get_product_vendor_review( $product ) {
        $product_id = $product->get_id();

        if ( isset( $this->product_vendors[ $product_id ] ) ) {
            $vendor_id = $this->product_vendors[ $product_id ];
        } else {
            $vendor     = dokan_get_vendor_by_product( $product_id );
            $vendor_id  = $vendor->get_id();

            $this->product_vendors[ $product_id ] = $vendor_id;
        }

        if ( isset( $this->vendor_ratings[ $vendor_id ] ) ) {
            $rating = $this->vendor_ratings[ $vendor_id ];
        } else {
            $seller_rating = dokan_get_seller_rating( $vendor_id );

            if ( $seller_rating['rating'] > 0 ) {
                $rating = $seller_rating['rating'];
            } else {
                $rating = 0;
            }
        }

        return $rating;
    }
}
