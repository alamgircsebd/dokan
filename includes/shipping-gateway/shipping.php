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
        $destination_country = isset( $package['destination']['country'] ) ? $package['destination']['country'] : '';
        $destination_state = isset( $package['destination']['state'] ) ? $package['destination']['state'] : '';

        // var_dump( $package['destination'] );
        // die();
        $amount = 0.0;

        if ( $products ) {

            if ( $this->type == 'seller' ) {
                $amount = $this->calculate_per_seller( $products, $destination_country, $destination_state );
            } else {
                // calculate per item
                $amount = $this->calculate_per_items( $products, $destination_country, $destination_state );
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
     * Check if shipping for this product is enabled
     *
     * @param  int  $product_id
     * @return boolean
     */
    public static function is_shipping_enabled_for_seller( $seller_id ) {
        $enabled = get_user_meta( $seller_id, '_dps_shipping_enable', true );

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
     * Get product shipping costs
     *
     * @param  int $product_id
     * @return array
     */
    public static function get_seller_country_shipping_costs( $seller_id ) {
        $country_cost = get_user_meta( $seller_id, '_dps_country_rates', true );
        $country_cost = is_array( $country_cost ) ? $country_cost : array();

        return $country_cost;
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
    private function calculate_per_seller( $products, $destination_country, $destination_state  ) {
        $amount = 0.0;

        $seller_products = array();
        // var_dump( $dps_enable_shipping, $dps_shipping_type_price, $dps_additional_product, $dps_additional_qty, $dps_country_rates ); 
        foreach ($products as $product) {
            $seller_id                     = get_post_field( 'post_author', $product['product_id'] );
            $seller_products[$seller_id][] = $product;
        }

        //var_dump( $seller_products );

        if ( $seller_products ) {
            
            foreach ( $seller_products as $seller_id => $products ) {

                // if( self::is_shipping_enabled_for_seller( $seller_id ) ) {
                //     continue;
                // }

                foreach ( $products as $product ) {

                    if( get_post_meta( $product['product_id'], '_overwrite_shipping', true ) == 'yes' ) {

                        $product_overwide_shipping = get_post_meta( $product['product_id'], '_shipping_type_price', true );
                        $product_overwide_qty      = get_post_meta( $product['product_id'], '_additional_qty', true );

                        if( $product['quantity'] > 1 ) {
                            $price[$seller_id]['override'][] = $product_overwide_shipping; 
                        } else {
                            $price[$seller_id]['override'][] = $product_overwide_shipping + ( ( $product['quantity'] - 1 ) * $product_overwide_qty );
                        }

                    } else {
                        
                        $default_shipping_price     = get_user_meta( $seller_id, '_dps_shipping_type_price', true );
                        $default_shipping_add_price = get_user_meta( $seller_id, '_dps_additional_product', true );
                        $default_shipping_qty_price = get_user_meta( $seller_id, '_dps_additional_qty', true );

                        if ( $product['quantity'] > 1 ) {
                            $price[ $seller_id ]['storewide'][] = $default_shipping_price;
                        } else {
                            $price[ $seller_id ]['storewide'][] = $default_shipping_price + ( ( $product['quantity'] - 1 ) * $default_shipping_qty_price );
                        }

                    }

                    // if ( array_key_exists('shipping', $product) ) {
                    //     $price[ $seller_id ]['override'][] = $product['shipping'];
                    // } else {
                    //     $price[ $seller_id ]['storewide'][] = $shipping;
                    // }
                }

                $dps_country_rates = get_user_meta( $user_id, '_dps_country_rates', true );
                $dps_state_rates   = get_user_meta( $user_id, '_dps_state_rates', true );
                
                //$default_shipping_price = get_user_meta( $seller_id, '_dps_shipping_type_price', true );

                // var_dump( $products );

                // foreach ( $products as $product ) {
                    
                //     $product_id = $product['product_id'];

                //     if( $i > 1 ) {
                //         $add_product_price = get_user_meta( $seller_id, '_dps_additional_product', false );
                //         $per_seller_cost = array_merge( $default_shipping_price, $add_product_price );
                //     }

                //     if( $product['quantity'] > 1 ) {
                //         $add_qty_price = get_user_meta( $seller_id, '_dps_additional_qty', true );
                //         $per_seller_cost_qtywise = ($product['quantity'] - 1 ) * $add_qty_price;
                //     }
                //     // calculate cost
                //     // $cost = self::get_product_costs( $product['product_id'] );

                //     // if ( array_key_exists( $destination, $cost ) ) {
                //     //     // for countries
                //     //     $seller_costs[] = $cost[$destination];

                //     // } elseif ( array_key_exists( 'everywhere', $cost ) ) {
                //     //     // for everywhere
                //     //     $seller_costs[] = $cost['everywhere'];
                //     // }
                //     $i++;
                // }


                // if ( $seller_costs ) {
                //     $cost   = max($seller_costs);
                //     $amount += $cost;
                // }
            
            } 
        }


        // var_dump( $price );
 
        return $amount;
    }
}




