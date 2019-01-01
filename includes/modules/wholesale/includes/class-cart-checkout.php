<?php

/**
* Cart and checkout handler Class
*
* @since DOKAN_PRO_SINCE
*/
class Dokan_Wholesale_Cart_Checkout {

    /**
     * Load autometically when class initiate
     *
     * @since DOKAN_PRO_SINCE
     */
    public function __construct() {
        add_action( 'woocommerce_before_add_to_cart_button', [ $this, 'show_wholesale_price' ], 10 );
        add_filter( 'woocommerce_available_variation', [ $this, 'add_variation_data' ], 10, 3 );
        add_action( 'woocommerce_before_calculate_totals', [ $this, 'calculate_cart' ], 12, 1 );
        add_action( 'woocommerce_before_mini_cart' , [ $this , 'recalculate_cart_totals' ] );
        add_action( 'woocommerce_after_cart_item_name', [ $this, 'show_wholesale_info' ], 10, 2 );
        add_action( 'woocommerce_checkout_create_order_line_item', [ $this, 'order_item_meta' ], 10, 3 );
        add_filter( 'woocommerce_order_item_display_meta_key', [ $this, 'change_wholesale_item_meta_title' ], 20, 3 );
    }

    /**
     * Get formatter data for wholesale
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    public function change_wholesale_item_meta_title( $key, $meta, $item ) {
        if ( '_dokan_item_wholesale' === $meta->key ) {
            $key = __( 'Wholesale Item' , 'dokan' );
        }

        return $key;
    }

    /**
     * Add warranty data to all variations
     *
     * @param $data
     * @param $product
     * @param $variation
     *
     * @return mixed
     */
    function add_variation_data( $data, $product, $variation ) {
        $variation_id = ( version_compare( WC_VERSION, '3.0', '<' ) && isset( $variation->variation_id ) ) ? $variation->variation_id : $variation->get_id();
        $wholesale     = get_post_meta( $variation_id, '_dokan_wholesale_meta', true );

        $data['_enable_wholesale']   = ! empty( $wholesale['enable_wholesale'] ) ? $wholesale['enable_wholesale'] : 'no';
        $data['_wholesale_price']    = ! empty( $wholesale['price'] ) ? $wholesale['price'] : '';
        $data['_wholesale_quantity'] = ! empty( $wholesale['quantity'] ) ? $wholesale['quantity'] : '';

        return $data;
    }

    /**
     * Show wholesale price
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    public function show_wholesale_price() {
        global $product;

        if ( ! current_user_can( 'dokan_wholesale_customer' ) ) {
            return;
        }

        $wholesale = get_post_meta( $product->get_id(), '_dokan_wholesale_meta', true );

        if ( ! isset( $wholesale['enable_wholesale'] ) ) {
            return;
        }

        if ( 'no' == $wholesale['enable_wholesale'] ) {
            return;
        }

        if ( empty( $wholesale['price'] ) ) {
            return;
        }

        dokan_get_template_part( 'wholesale/single-product', '', [
            'is_wholesale'       => true,
            'user_id'            => dokan_get_current_user_id(),
            'product'            => $product,
            'enable_wholesale'   => ! empty( $wholesale['enable_wholesale'] ) ? $wholesale['enable_wholesale'] : 'no',
            'wholesale_price'    => ! empty( $wholesale['price'] ) ? $wholesale['price'] : '',
            'wholesale_quantity' => ! empty( $wholesale['quantity'] ) ? $wholesale['quantity'] : ''
        ] );
    }

    /**
     * Calculate cart item for wholesales
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    public function calculate_cart( $cart_obj ) {
        if ( ! current_user_can( 'dokan_wholesale_customer' ) ) {
            return;
        }

        foreach ( $cart_obj->get_cart() as $cart_key => $cart ) {
            $product_id = ! empty( $cart['variation_id'] ) ? $cart['variation_id'] : $cart['product_id'];
            $wholesale  = get_post_meta( $product_id, '_dokan_wholesale_meta', true );

            WC()->cart->cart_contents[$cart_key]['wholesale'] = $wholesale;

            if ( ! isset( $wholesale['enable_wholesale'] ) ) {
                continue;
            }

            if ( 'no' == $wholesale['enable_wholesale'] ) {
                continue;
            }

            if ( empty( $wholesale['price'] ) ) {
                continue;
            }

            if ( $wholesale['quantity'] <= 0 ) {
                continue;
            }

            if (  $wholesale['quantity'] <= $cart['quantity'] ) {
                $cart['data']->set_price( $wholesale['price'] );
            }
        }
    }

    /**
     * Recalculate mini cart when applied wholesale
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function recalculate_cart_totals() {
        WC()->cart->calculate_totals();
    }

    /**
     * Display wholesale info
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function show_wholesale_info( $cart_item, $cart_item_key ) {
        if ( ! current_user_can( 'dokan_wholesale_customer' ) ) {
            return;
        }

        if ( isset( $cart_item['wholesale'] ) ) {
            if ( 'yes' == $cart_item['wholesale']['enable_wholesale'] ) {
                $remaining_qty = absint( $cart_item['wholesale']['quantity'] - $cart_item['quantity'] );
                if ( $remaining_qty > 0 ) {
                    echo '<br>';
                    echo sprintf( __( 'For wholesale price buy <strong>%d</strong> more units' ), $remaining_qty );
                }
            }
        }
    }

    /**
     * Order item meta added for wholesale product
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function order_item_meta( $item, $cart_item_key, $values ) {
        $cart_contents  = WC()->cart->get_cart();

        if ( ! current_user_can( 'dokan_wholesale_customer' ) ) {
            return;
        }

        $cart_item = $cart_contents[$cart_item_key];

        if ( isset( $cart_item['wholesale'] ) ) {
            if ( 'yes' == $cart_item['wholesale']['enable_wholesale'] ) {
                if ( $cart_item['wholesale']['quantity'] <= $cart_item['quantity'] ) {
                    $item_id = $item->save();
                    error_log( print_r( 'Yes found wholesale item', true ) );
                    wc_add_order_item_meta( $item_id, '_dokan_item_wholesale', 'yes' );
                }
            }
        }
    }

}
