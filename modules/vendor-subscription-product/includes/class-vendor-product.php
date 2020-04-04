<?php

/**
* Vendor Subscription Product
*
* @since 1.0.0
*
* @package dokan
*/
class Dokan_VSP_Product {

    /**
    * Replaced templates list
    *
    * @since    1.0.0
    * @access   private
    * @var      string    $version    The current version of this plugin.
    */
    private $templates;

    /**
    * Replaced templates parts list
    *
    * @since    1.0.0
    * @access   private
    * @var      string    $version    The current version of this plugin.
    */
    private $templates_parts;

    /**
     * Load autometically when class initiate
     *
     * @since 1.0.0
     */
    public function __construct() {
        // $this->templates = array(
        //     'products/new-product-single.php',
        // );

        // $this->templates_parts = array(
        //     'products/download-virtual',
        //     'products/product-variation',
        //     'products/edit/html-product-attribute',
        //     'products/edit/html-product-variation'
        // );

        add_action( 'dokan_product_types', [ $this, 'add_subscription_product_type' ], 20 );
        add_action( 'dokan_product_edit_after_pricing', [ $this, 'load_subscription_fields' ], 20, 2 );
        add_action( 'dokan_product_after_variation_pricing', [ $this, 'load_variation_subscription_fields' ], 10, 3 );
        add_action( 'dokan_product_updated', [ $this, 'handle_subscription_metadata' ], 10, 1 );
        add_action( 'woocommerce_save_product_variation', [ $this, 'save_variation_metadata' ], 10, 2 );
        add_filter( 'woocommerce_checkout_update_order_meta', [ $this, 'sync_parent_order_with_dokan' ], 30 );
        add_filter( 'wcs_new_order_created', [ $this, 'sync_renewal_order_with_dokan' ], 15, 3 );
    }

    /**
     * Add subscription product type
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function add_subscription_product_type( $types ) {
        $types['subscription']          = __( 'Simple subscription', 'dokan' );
        $types['variable-subscription'] = __( 'Variable subscription', 'dokan' );

        return $types;
    }

    /**
     * Load subscription fields
     *
     * @param object $post
     * @param integer $post_id
     *
     * @return void
     */
    function load_subscription_fields( $post, $post_id ) {
        dokan_get_template_part( 'subscription/price', '', [ 'is_subscription_product' => true, 'post' => $post, 'post_id' => $post_id ] );
    }

    /**
     * Load subscription fields for variations
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function load_variation_subscription_fields( $loop, $variation_data, $variation ) {
        dokan_get_template_part( 'subscription/variation-price', '', [ 'is_subscription_product' => true, 'loop' => $loop, 'variation_data' => $variation_data, 'variation' => $variation ] );
    }

    /**
     * Handle subscription metadata
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function handle_subscription_metadata( $post_id ) {
        global $woocommerce, $wpdb;

        if ( $_POST['product_type'] == 'subscription' ){

            $subscription_price = isset( $_POST['_subscription_price'] ) ? wc_format_decimal( $_POST['_subscription_price'] ) : '';
            $sale_price         = wc_format_decimal( $_POST['_sale_price'] );

            update_post_meta( $post_id, '_subscription_price', $subscription_price );

            // Set sale details - these are ignored by WC core for the subscription product type
            update_post_meta( $post_id, '_regular_price', $subscription_price );
            update_post_meta( $post_id, '_sale_price', $sale_price );

            $site_offset = get_option( 'gmt_offset' ) * 3600;

            // Save the timestamps in UTC time, the way WC does it.
            $date_from = ( ! empty( $_POST['_sale_price_dates_from'] ) ) ? wcs_date_to_time( $_POST['_sale_price_dates_from'] ) - $site_offset : '';
            $date_to   = ( ! empty( $_POST['_sale_price_dates_to'] ) ) ? wcs_date_to_time( $_POST['_sale_price_dates_to'] ) - $site_offset : '';

            $now = gmdate( 'U' );

            if ( ! empty( $date_to ) && empty( $date_from ) ) {
                $date_from = $now;
            }

            update_post_meta( $post_id, '_sale_price_dates_from', $date_from );
            update_post_meta( $post_id, '_sale_price_dates_to', $date_to );

            // Update price if on sale
            if ( '' !== $sale_price && ( ( empty( $date_to ) && empty( $date_from ) ) || ( $date_from < $now && ( empty( $date_to ) || $date_to > $now ) ) ) ) {
                $price = $sale_price;
            } else {
                $price = $subscription_price;
            }

            update_post_meta( $post_id, '_price', stripslashes( $price ) );

            // Make sure trial period is within allowable range
            $subscription_ranges = wcs_get_subscription_ranges();

            $max_trial_length = count( $subscription_ranges[ $_POST['_subscription_trial_period'] ] ) - 1;

            $_POST['_subscription_trial_length'] = absint( $_POST['_subscription_trial_length'] );

            if ( $_POST['_subscription_trial_length'] > $max_trial_length ) {
                $_POST['_subscription_trial_length'] = $max_trial_length;
            }

            update_post_meta( $post_id, '_subscription_trial_length', $_POST['_subscription_trial_length'] );

            $_POST['_subscription_sign_up_fee']       = wc_format_decimal( $_POST['_subscription_sign_up_fee'] );
            $_POST['_subscription_one_time_shipping'] = isset( $_POST['_subscription_one_time_shipping'] ) ? 'yes' : 'no';

            $subscription_fields = array(
                '_subscription_sign_up_fee',
                '_subscription_period',
                '_subscription_period_interval',
                '_subscription_length',
                '_subscription_trial_period',
                '_subscription_limit',
                '_subscription_one_time_shipping',
            );

            foreach ( $subscription_fields as $field_name ) {
                if ( isset( $_POST[ $field_name ] ) ) {
                    update_post_meta( $post_id, $field_name, stripslashes( $_POST[ $field_name ] ) );
                }
            }

            // Set month as the default billing period
            if ( ! isset( $_POST['_subscription_period'] ) ) {
                $_POST['_subscription_period'] = 'month';
            }

            if ( 'year' == $_POST['_subscription_period'] ) { // save the day & month for the date rather than just the day

                $_POST[ WC_Subscriptions_Synchroniser::$post_meta_key ] = array(
                    'day'    => isset( $_POST[ WC_Subscriptions_Synchroniser::$post_meta_key_day ] ) ? $_POST[ WC_Subscriptions_Synchroniser::$post_meta_key_day ] : 0,
                    'month'  => isset( $_POST[ WC_Subscriptions_Synchroniser::$post_meta_key_month ] ) ? $_POST[ WC_Subscriptions_Synchroniser::$post_meta_key_month ] : '01',
                );

            } else {

                if ( ! isset( $_POST[ WC_Subscriptions_Synchroniser::$post_meta_key ] ) ) {
                    $_POST[ WC_Subscriptions_Synchroniser::$post_meta_key ] = 0;
                }
            }

            update_post_meta( $post_id, WC_Subscriptions_Synchroniser::$post_meta_key, $_POST[ WC_Subscriptions_Synchroniser::$post_meta_key ] );
        } elseif ( $_POST['product_type'] == 'variable-subscription' ) {
            dokan_save_variations( $post_id );
        }
    }

    /**
     * Save variation subscription meta
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function save_variation_metadata( $variation_id, $index ) {
        if ( ! dokan_is_user_seller( dokan_get_current_user_id() ) ) {
            return;
        }

        if ( isset( $_POST['variable_subscription_sign_up_fee'][ $index ] ) ) {
            $subscription_sign_up_fee = wc_format_decimal( $_POST['variable_subscription_sign_up_fee'][ $index ] );
            update_post_meta( $variation_id, '_subscription_sign_up_fee', $subscription_sign_up_fee );
        }

        if ( isset( $_POST['variable_subscription_price'][ $index ] ) ) {
            $subscription_price = wc_format_decimal( $_POST['variable_subscription_price'][ $index ] );
            update_post_meta( $variation_id, '_subscription_price', $subscription_price );
            update_post_meta( $variation_id, '_regular_price', $subscription_price );
        }

        // Make sure trial period is within allowable range
        $subscription_ranges = wcs_get_subscription_ranges();
        $max_trial_length    = count( $subscription_ranges[ $_POST['variable_subscription_trial_period'][ $index ] ] ) - 1;

        $_POST['variable_subscription_trial_length'][ $index ] = absint( $_POST['variable_subscription_trial_length'][ $index ] );

        if ( $_POST['variable_subscription_trial_length'][ $index ] > $max_trial_length ) {
            $_POST['variable_subscription_trial_length'][ $index ] = $max_trial_length;
        }

        // Work around a WPML bug which means 'variable_subscription_trial_period' is not set when using "Edit Product" as the product translation interface
        if ( $_POST['variable_subscription_trial_length'][ $index ] < 0 ) {
            $_POST['variable_subscription_trial_length'][ $index ] = 0;
        }

        $subscription_fields = array(
            '_subscription_period',
            '_subscription_period_interval',
            '_subscription_length',
            '_subscription_trial_period',
            '_subscription_trial_length',
        );

        foreach ( $subscription_fields as $field_name ) {
            if ( isset( $_POST[ 'variable' . $field_name ][ $index ] ) ) {
                update_post_meta( $variation_id, $field_name, wc_clean( $_POST[ 'variable' . $field_name ][ $index ] ) );
            }
        }

        $day_field   = 'variable' . WC_Subscriptions_Synchroniser::$post_meta_key_day;
        $month_field = 'variable' . WC_Subscriptions_Synchroniser::$post_meta_key_month;

        if ( 'year' == $_POST['variable_subscription_period'][ $index ] ) { // save the day & month for the date rather than just the day

            $_POST[ 'variable' . WC_Subscriptions_Synchroniser::$post_meta_key ][ $index ] = array(
                'day'    => isset( $_POST[ $day_field ][ $index ] ) ? $_POST[ $day_field ][ $index ] : 0,
                'month'  => isset( $_POST[ $month_field ][ $index ] ) ? $_POST[ $month_field ][ $index ] : 0,
            );

        } elseif ( ! isset( $_POST[ 'variable' . WC_Subscriptions_Synchroniser::$post_meta_key ][ $index ] ) ) {
            $_POST[ 'variable' . WC_Subscriptions_Synchroniser::$post_meta_key ][ $index ] = 0;
        }

        update_post_meta( $variation_id, WC_Subscriptions_Synchroniser::$post_meta_key, $_POST[ 'variable' . WC_Subscriptions_Synchroniser::$post_meta_key ][ $index ] );
    }

    /**
    * Sync new order with dokan
    *
    * @param Integer $order_id
    *
    * @return void
    */
    public function sync_parent_order_with_dokan( $order_id ){
        if( get_post_type( $order_id ) == 'shop_order' ){
            if ( ! add_post_meta( $order_id, 'subscription_order_type', 'Parent' ) ) {
                update_post_meta ( $order_id, 'subscription_order_type', 'Parent' );
            }
        }
    }

    /**
    * Sync new order with dokan
    *
    * @param Object $new_order
    * @param Object $subscription
    * @param String $type
    *
    * @return Object
    */
    public function sync_renewal_order_with_dokan( $new_order, $subscription, $type ) {
        global $wpdb;

        $order_id = $new_order->get_id();

        if ( dokan_is_order_already_exists( $order_id ) ) {
            return;
        }

        if ( get_post_meta( $order_id, 'has_sub_order', true ) == '1' ) {
            return;
        }

        $order              = $new_order;
        $seller_id          = dokan_get_seller_id_by_order( $order_id );
        $order_total        = $order->get_total();
        $order_status       = dokan_get_prop( $order, 'status' );
        $admin_commission   = dokan()->commission->get_earning_by_order( $order, 'admin' );
        $net_amount         = $order_total - $admin_commission;
        $net_amount         = apply_filters( 'dokan_order_net_amount', $net_amount, $order );
        $threshold_day      = dokan_get_option( 'withdraw_date_limit', 'dokan_withdraw', 0 );
        $threshold_day      = $threshold_day ? $threshold_day : 0;

        dokan_delete_sync_duplicate_order( $order_id, $seller_id );

        // make sure order status contains "wc-" prefix
        if ( stripos( $order_status, 'wc-' ) === false ) {
            $order_status = 'wc-' . $order_status;
        }

        $wpdb->insert( $wpdb->prefix . 'dokan_orders',
            array(
                'order_id'     => $order_id,
                'seller_id'    => $seller_id,
                'order_total'  => $order_total,
                'net_amount'   => $net_amount,
                'order_status' => $order_status,
            ),
            array(
                '%d',
                '%d',
                '%f',
                '%f',
                '%s',
            )
        );

        $wpdb->insert( $wpdb->prefix . 'dokan_vendor_balance',
            array(
                'vendor_id'     => $seller_id,
                'trn_id'        => $order_id,
                'trn_type'      => 'dokan_orders',
                'perticulars'   => 'New order',
                'debit'         => $net_amount,
                'credit'        => 0,
                'status'        => $order_status,
                'trn_date'      => current_time( 'mysql' ),
                'balance_date'  => date( 'Y-m-d h:i:s', strtotime( current_time( 'mysql' ) . ' + '.$threshold_day.' days' ) ),
            ),
            array(
                '%d',
                '%d',
                '%s',
                '%s',
                '%f',
                '%f',
                '%s',
                '%s',
                '%s',
            )
        );

        if ( get_post_type( $order_id ) == 'shop_order' ) {
            if ( ! add_post_meta( $order_id, 'subscription_order_type', 'Renewal' ) ) {
                update_post_meta ( $order_id, 'subscription_order_type', 'Renewal' );
            }
        }

        return $new_order;
    }

}
