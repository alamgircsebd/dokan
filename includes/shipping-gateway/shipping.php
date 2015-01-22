<?php

class Dokan_WC_Per_Product_Shipping extends WC_Shipping_Method {
    /**
     * Constructor for your shipping class
     *
     * @access public
     * @return void
     */
    public function __construct() {
        $this->id                 = 'dokan_per_product';
        $this->method_title       = __( 'Dokan Per Product Shipping' );
        $this->method_description = __( 'Enable sellers to set shipping cost per product and per country' );

        $this->enabled      = $this->get_option( 'enabled' );
        $this->title        = $this->get_option( 'title' );

        $this->init();
    }

    /**
     * Init your settings
     *
     * @access public
     * @return void
     */
    function init() {
        // Load the settings API
        $this->init_form_fields(); // This is part of the settings API. Override the method to add your own settings
        $this->init_settings(); // This is part of the settings API. Loads settings you previously init.

        $type       = $this->get_option( 'type' );
        $this->type = empty( $type ) ? 'item' : $type;

        // Save settings in admin if you have any defined
        add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
    }


    /**
     * Initialise Gateway Settings Form Fields
     *
     * @access public
     * @return void
     */
    function init_form_fields() {

        $this->form_fields = array(
            'enabled' => array(
                'title'         => __( 'Enable/Disable', 'dokan-shipping' ),
                'type'          => 'checkbox',
                'label'         => __( 'Enable Shipping', 'dokan-shipping' ),
                'default'       => 'yes'
            ),
            'title' => array(
                'title'         => __( 'Method Title', 'dokan-shipping' ),
                'type'          => 'text',
                'description'   => __( 'This controls the title which the user sees during checkout.', 'dokan-shipping' ),
                'default'       => __( 'Regular Shipping', 'dokan-shipping' ),
                'desc_tip'      => true,
            ),
            'type' => array(
                'title'         => __( 'Cost Added...', 'dokan-shipping' ),
                'type'          => 'select',
                'default'       => 'order',
                'options'       => array(
                    'seller'    => __( 'Per Seller - charge shipping for the products from a seller as a whole', 'dokan-shipping' ),
                    'item'      => __( 'Per Item - charge shipping for each item individually', 'dokan-shipping' ),
                ),
            ),
        );

    }

    /**
     * calculate_shipping function.
     *
     * @access public
     * @param mixed $package
     * @return void
     */
    public function calculate_shipping( $package ) {
        // var_dump( $package );
        $products = $package['contents'];
        $destination = isset( $package['destination']['country'] ) ? $package['destination']['country'] : '';

        // var_dump( $products, $destination );
        // die();
        $amount = 0.0;

        if ( $products ) {

            if ( $this->type == 'seller' ) {
                $amount = $this->calculate_per_seller( $products, $destination );
            } else {
                // calculate per item
                $amount = $this->calculate_per_items( $products, $destination );
            }

        }

    	$rate = array(
            'id'    => $this->id,
            'label' => $this->title,
            'cost'  => $amount,
            'taxes' => false
        );

        // Register the rate
        $this->add_rate( $rate );
    }

    /**
     * Check if shipping for this product is enabled
     *
     * @param  int  $product_id
     * @return boolean
     */
    public static function is_product_enabled( $product_id ) {
        $enabled = get_post_meta( $product_id, '_dps_ship_enable', true );

        if ( $enabled != 'yes' ) {
            return false;
        }

        return true;
    }

    /**
     * Get product shipping costs
     *
     * @param  int $product_id
     * @return array
     */
    public static function get_product_costs( $product_id ) {
        $cost = get_post_meta( $product_id, '_dps_rates', true );
        $cost = is_array( $cost ) ? $cost : array();

        return $cost;
    }

    /**
     * Calculate shipping per items
     *
     * @param  array $products
     * @param  array $destination
     * @return float
     */
    private function calculate_per_items( $products, $destination ) {
        $amount = 0.0;

        foreach ($products as $product) {

            if ( ! self::is_product_enabled( $product['product_id'] ) ) {
                continue;
            }

            // calculate cost
            $cost = self::get_product_costs( $product['product_id'] );

            if ( array_key_exists( $destination, $cost ) ) {
                // for countries
                $amount += $cost[$destination];

            } elseif ( array_key_exists( 'everywhere', $cost ) ) {
                // for everywhere
                $amount += $cost['everywhere'];
            }
        }

        return $amount;
    }

    /**
     * Calculate shipping per seller
     *
     * @param  array $products
     * @param  array $destination
     * @return float
     */
    private function calculate_per_seller( $products, $destination ) {
        $amount = 0.0;

        $seller_products = array();

        foreach ($products as $product) {
            $seller_id                     = get_post_field( 'post_author', $product['product_id'] );
            $seller_products[$seller_id][] = $product;
        }

        if ( $seller_products ) {
            foreach ($seller_products as $seller_id => $products) {

                $seller_costs = array();
                foreach ($products as $product) {
                    if ( ! self::is_product_enabled( $product['product_id'] ) ) {
                        continue;
                    }

                    // calculate cost
                    $cost = self::get_product_costs( $product['product_id'] );

                    if ( array_key_exists( $destination, $cost ) ) {
                        // for countries
                        $seller_costs[] = $cost[$destination];

                    } elseif ( array_key_exists( 'everywhere', $cost ) ) {
                        // for everywhere
                        $seller_costs[] = $cost['everywhere'];
                    }
                }

                if ( $seller_costs ) {
                    $cost   = max($seller_costs);
                    $amount += $cost;
                }
            }
        }

        return $amount;
    }
}