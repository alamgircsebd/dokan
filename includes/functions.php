<?php

/**
 *  General Fnctions for Dokan Pro features
 *
 *  @since 2.4
 *
 *  @package dokan
 */

/**
 * Returns Current User Profile progress bar HTML
 *
 * @since 2.1
 *
 * @return output
 */
if ( !function_exists( 'dokan_get_profile_progressbar' ) ) {

	function dokan_get_profile_progressbar() {
        $profile_info  = dokan_get_store_info( dokan_get_current_user_id() );
        $progress      = isset( $profile_info['profile_completion']['progress'] ) ? $profile_info['profile_completion']['progress'] : '';
        $next_todo     = isset( $profile_info['profile_completion']['next_todo'] ) ? $profile_info['profile_completion']['next_todo'] : '';
        $progress_vals = isset( $profile_info['profile_completion']['progress_vals'] ) ? $profile_info['profile_completion']['progress_vals'] : 0;

        if ( strpos( $next_todo, '-' ) !== false ) {
            $next_todo     = substr( $next_todo, strpos( $next_todo, '-' ) + 1 );
            $progress_vals = isset( $profile_info['profile_completion']['progress_vals'] ) ? $profile_info['profile_completion']['progress_vals'] : 0;
            $progress_vals = isset( $progress_vals['social_val'][$next_todo] ) ? $progress_vals['social_val'][$next_todo] : 0;
        } else {
            $progress_vals = isset( $progress_vals[$next_todo] ) ? $progress_vals[$next_todo] : 15;
        }

	    ob_start();

	    dokan_get_template_part( 'global/profile-progressbar', '', array( 'pro'=>true, 'progress' => $progress, 'next_todo' => $next_todo, 'value' => $progress_vals ) );

	    $output = ob_get_clean();

	    return $output;
	}

}

/**
 * Dokan progressbar translated string
 *
 * @param  string $string
 * @param  int $value
 * @param  int $progress
 *
 * @return string
 */
function dokan_progressbar_translated_string( $string = '', $value = 15, $progress = 0 ) {

    if ( 100 === absint( $progress ) ) {
        return __( 'Congratulation, your profile is fully completed', 'dokan' );
    }

    switch ( $string ) {
        case 'profile_picture_val':
            return sprintf( __( 'Add Profile Picture to gain %s%% progress', 'dokan' ), number_format_i18n( $value ) );
            break;

        case 'phone_val':
            return sprintf( __( 'Add Phone to gain %s%% progress', 'dokan' ), number_format_i18n( $value ) );
            break;

        case 'banner_val':
            return sprintf( __( 'Add Banner to gain %s%% progress', 'dokan' ), number_format_i18n( $value ) );
            break;

        case 'store_name_val':
            return sprintf( __( 'Add Store Name to gain %s%% progress', 'dokan' ), number_format_i18n( $value ) );
            break;

        case 'address_val':
            return sprintf( __( 'Add address to gain %s%% progress', 'dokan' ), number_format_i18n( $value ) );
            break;

        case 'payment_method_val':
            return sprintf( __( 'Add a Payment method to gain %s%% progress', 'dokan' ), number_format_i18n( $value ) );
            break;

        case 'map_val':
            return sprintf( __( 'Add Map location to gain %s%% progress', 'dokan' ), number_format_i18n( $value ) );
            break;

        case 'fb':
            return sprintf( __( 'Add facebook to gain %s%% progress', 'dokan' ), number_format_i18n( $value ) );

        case 'gplus':
            return sprintf( __( 'Add Google to gain %s%% progress', 'dokan' ), number_format_i18n( $value ) );

        case 'twitter':
            return sprintf( __( 'Add Twitter to gain %s%% progress', 'dokan' ), number_format_i18n( $value ) );

        case 'youtube':
            return sprintf( __( 'Add Youtube to gain %s%% progress', 'dokan' ), number_format_i18n( $value ) );

        case 'linkedin':
            return sprintf( __( 'Add LinkedIn to gain %s%% progress', 'dokan' ), number_format_i18n( $value ) );

        default:
            return sprintf( __( 'Start with adding a Banner to gain profile progress', 'dokan' ) );
            break;
    }
}

/**
 * Get refund counts, used in admin area
 *
 *  @since 2.4.11
 *  @since 3.0.0 Move the logic to Refund manager class
 *
 * @global WPDB $wpdb
 * @return array
 */
function dokan_get_refund_count( $seller_id = null ) {
    return dokan_pro()->refund->get_status_counts( $seller_id );
}


/**
 * Get get seller coupon
 *
 *  @since 2.4.12
 *
 * @param int $seller_id
 *
 * @return array
 */
function dokan_get_seller_coupon( $seller_id, $show_on_store = false ) {
    $args = array(
        'post_type'   => 'shop_coupon',
        'post_status' => 'publish',
        'author'      => $seller_id,
    );

    if ( $show_on_store ) {
        $args['meta_query'][] = array(
            'key'   => 'show_on_store',
            'value' => 'yes',
        );
    }

    $coupons = get_posts( $args );

    return $coupons;
}

/**
* Get refund localize data
*
* @since 2.6
*
* @return void
**/
function dokan_get_refund_localize_data() {
    return array(
        'mon_decimal_point'             => wc_get_price_decimal_separator(),
        'remove_item_notice'            => __( 'Are you sure you want to remove the selected items? If you have previously reduced this item\'s stock, or this order was submitted by a customer, you will need to manually restore the item\'s stock.', 'dokan' ),
        'i18n_select_items'             => __( 'Please select some items.', 'dokan' ),
        'i18n_do_refund'                => __( 'Are you sure you wish to process this refund request? This action cannot be undone.', 'dokan' ),
        'i18n_delete_refund'            => __( 'Are you sure you wish to delete this refund? This action cannot be undone.', 'dokan' ),
        'remove_item_meta'              => __( 'Remove this item meta?', 'dokan' ),
        'ajax_url'                      => admin_url( 'admin-ajax.php' ),
        'order_item_nonce'              => wp_create_nonce( 'order-item' ),
        'post_id'                       => isset( $_GET['order_id'] ) ? $_GET['order_id'] : '',
        'currency_format_num_decimals'  => wc_get_price_decimals(),
        'currency_format_symbol'        => get_woocommerce_currency_symbol(),
        'currency_format_decimal_sep'   => esc_attr( wc_get_price_decimal_separator() ),
        'currency_format_thousand_sep'  => esc_attr( wc_get_price_thousand_separator() ),
        'currency_format'               => esc_attr( str_replace( array( '%1$s', '%2$s' ), array( '%s', '%v' ), get_woocommerce_price_format() ) ), // For accounting JS
        'rounding_precision'            => wc_get_rounding_precision(),
    );
}

/**
 * Get review page url of a seller
 *
 * @param int $user_id
 * @return string
 */
function dokan_get_review_url( $user_id ) {
    if ( ! $user_id ) {
        return '';
    }

    $userstore = dokan_get_store_url( $user_id );

    return apply_filters( 'dokan_get_seller_review_url', $userstore . 'reviews' );
}

/**
 *
 */
function dokan_render_order_table_items( $order_id ) {
    $data  = get_post_meta( $order_id );
    $order = new WC_Order( $order_id );

    dokan_get_template_part( 'orders/views/html-order-items', '', array(
        'pro'   => true,
        'data'  => $data,
        'order' => $order
    ) );
}

/**
 * Get best sellers list
 *
 * @param  integer $limit
 * @return array
 */
function dokan_get_best_sellers( $limit = 5 ) {
    global  $wpdb;

    $cache_key = 'dokan-best-seller-' . $limit;
    $seller = wp_cache_get( $cache_key, 'widget' );

    if ( false === $seller ) {

        $qry = "SELECT seller_id, display_name, SUM( net_amount ) AS total_sell
            FROM {$wpdb->prefix}dokan_orders AS o,{$wpdb->users} AS u
            LEFT JOIN {$wpdb->usermeta} AS umeta on umeta.user_id=u.ID
            WHERE o.seller_id = u.ID AND umeta.meta_key = 'dokan_enable_selling' AND umeta.meta_value = 'yes'
            GROUP BY o.seller_id
            ORDER BY total_sell DESC LIMIT ".$limit;

        $seller = $wpdb->get_results( $qry );
        wp_cache_set( $cache_key, $seller, 'widget', 3600*6 );
    }

    return $seller;
}

/**
 * Get featured sellers list
 *
 * @param  integer $limit
 * @return array
 */
function dokan_get_feature_sellers( $count = 5 ) {
    $args = array(
        'role'         => 'seller',
        'meta_query'   => array(
            array(
                'key'   => 'dokan_feature_seller',
                'value' => 'yes',
            ),
            array(
                'key'   => 'dokan_enable_selling',
                'value' => 'yes',
            )
        ),
        'offset'       => $count
    );

    $users = get_users( apply_filters( 'dokan_get_feature_sellers_args', $args ) );

    $args = array(
        'role'       => 'administrator',
        'meta_key'   => 'dokan_feature_seller',
        'meta_value' => 'yes',
        'offset'     => $count
    );
    $admins = get_users( $args );

    $sellers = array_merge( $admins, $users );
    return $sellers;
}


/**
 * Generate Customer to Vendor migration template
 *
 * @since 2.6.4
 *
 * @param array $atts ShortCode attributes
 *
 * @return void Render template for account update
 */
if ( !function_exists( 'dokan_render_customer_migration_template' ) ) {

    function dokan_render_customer_migration_template( $atts ) {

        ob_start();
        dokan_get_template_part( 'global/update-account', '', array( 'pro' => true ) );
        ?>
            <script>
            // Dokan Register
            jQuery(function($) {
                $('.user-role input[type=radio]').on('change', function() {
                    var value = $(this).val();

                    if ( value === 'seller') {
                        $('.show_if_seller').slideDown();
                        if ( $( '.tc_check_box' ).length > 0 )
                            $('input[name=register]').attr('disabled','disabled');
                    } else {
                        $('.show_if_seller').slideUp();
                        if ( $( '.tc_check_box' ).length > 0 )
                            $( 'input[name=register]' ).removeAttr( 'disabled' );
                    }
                });

               $( '.tc_check_box' ).on( 'click', function () {
                    var chk_value = $( this ).val();
                    if ( $( this ).prop( "checked" ) ) {
                        $( 'input[name=register]' ).removeAttr( 'disabled' );
                        $( 'input[name=dokan_migration]' ).removeAttr( 'disabled' );
                    } else {
                        $( 'input[name=register]' ).attr( 'disabled', 'disabled' );
                        $( 'input[name=dokan_migration]' ).attr( 'disabled', 'disabled' );
                    }
                } );

                if ( $( '.tc_check_box' ).length > 0 ){
                    $( 'input[name=dokan_migration]' ).attr( 'disabled', 'disabled' );
                }

                $('#company-name').on('focusout', function() {
                    var value = $(this).val().toLowerCase().replace(/-+/g, '').replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');
                    $('#seller-url').val(value);
                    $('#url-alart').text( value );
                    $('#seller-url').focus();
                });

                $('#seller-url').keydown(function(e) {
                    var text = $(this).val();

                    // Allow: backspace, delete, tab, escape, enter and .
                    if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 91, 109, 110, 173, 189, 190]) !== -1 ||
                         // Allow: Ctrl+A
                        (e.keyCode == 65 && e.ctrlKey === true) ||
                         // Allow: home, end, left, right
                        (e.keyCode >= 35 && e.keyCode <= 39)) {
                             // let it happen, don't do anything
                            return;
                    }

                    if ((e.shiftKey || (e.keyCode < 65 || e.keyCode > 90) && (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105) ) {
                        e.preventDefault();
                    }
                });

                $('#seller-url').keyup(function(e) {
                    $('#url-alart').text( $(this).val() );
                });

                $('#shop-phone').keydown(function(e) {
                    // Allow: backspace, delete, tab, escape, enter and .
                    if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 91, 107, 109, 110, 187, 189, 190]) !== -1 ||
                         // Allow: Ctrl+A
                        (e.keyCode == 65 && e.ctrlKey === true) ||
                         // Allow: home, end, left, right
                        (e.keyCode >= 35 && e.keyCode <= 39)) {
                             // let it happen, don't do anything
                             return;
                    }

                    // Ensure that it is a number and stop the keypress
                    if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                        e.preventDefault();
                    }
                });

                $('#seller-url').on('focusout', function() {
                    var self = $(this),
                    data = {
                        action : 'shop_url',
                        url_slug : self.val(),
                        _nonce : dokan.nonce,
                    };

                    if ( self.val() === '' ) {
                        return;
                    }

                    var row = self.closest('.form-row');
                    row.block({ message: null, overlayCSS: { background: '#fff url(' + dokan.ajax_loader + ') no-repeat center', opacity: 0.6 } });

                    $.post( dokan.ajaxurl, data, function(resp) {

                        if ( resp == 0){
                            $('#url-alart').removeClass('text-success').addClass('text-danger');
                            $('#url-alart-mgs').removeClass('text-success').addClass('text-danger').text(dokan.seller.notAvailable);
                        } else {
                            $('#url-alart').removeClass('text-danger').addClass('text-success');
                            $('#url-alart-mgs').removeClass('text-danger').addClass('text-success').text(dokan.seller.available);
                        }

                        row.unblock();

                    } );

                });
            });
            </script>
        <?php

        return ob_get_clean();
    }

}

add_shortcode( 'dokan-customer-migration', 'dokan_render_customer_migration_template' );

/**
 * Send announcement email
 *
 * @since 2.8.2
 *
 * @param $announcement_id
 *
 * @return void
 */
function dokan_send_announcement_email( $announcement_id ) {
    $announcement = new \WeDevs\DokanPro\Admin\Announcement();
    $announcement->trigger_mail( $announcement_id );
}

add_action( 'dokan_after_announcement_saved', 'dokan_send_announcement_email' );

/**
 * Send email for scheduled announcement
 *
 * @since 2.9.13
 *
 * @param WP_Post $post
 *
 * @return void
 */
function dokan_send_scheduled_announcement_email( $post ) {
    if ( 'dokan_announcement' !== $post->post_type ) {
        return;
    }

    $announcement = new \WeDevs\DokanPro\Admin\Announcement();
    $announcement->trigger_mail( $post->ID );
}

add_action( 'future_to_publish', 'dokan_send_scheduled_announcement_email' );

/**
 * Set store categories
 *
 * @since 2.9.2
 *
 * @param int            $store_id
 * @param array|int|null $categories
 *
 * @return array|WP_Error Term taxonomy IDs of the affected terms.
 */
function dokan_set_store_categories( $store_id, $categories = null ) {
    if ( ! is_array( $categories ) ) {
        $categories = array( $categories );
    }

    $categories = array_map( 'absint', $categories );
    $categories = array_filter( $categories );

    if ( empty( $categories ) ) {
        $categories = array( dokan_get_default_store_category_id() );
    }

    return wp_set_object_terms( $store_id, $categories, 'store_category' );
}

/**
 * Checks if store category feature is on or off
 *
 * @since 2.9.2
 *
 * @return bool
 */
function dokan_is_store_categories_feature_on() {
    return 'none' !== dokan_get_option( 'store_category_type', 'dokan_general', 'none' );
}

/**
 * Get the default store category id
 *
 * @since 2.9.2
 *
 * @return int
 */
function dokan_get_default_store_category_id() {
    $default_category = get_option( 'default_store_category', null );

    if ( ! $default_category ) {
        $uncategorized_id = term_exists( 'Uncategorized', 'store_category' );

        if ( ! $uncategorized_id ) {
            $uncategorized_id = wp_insert_term( 'Uncategorized', 'store_category' );
        }

        $default_category = $uncategorized_id['term_id'];

        dokan_set_default_store_category_id( $default_category );
    }

    return absint( $default_category );
}

/**
 * Set the default store category id
 *
 * Make sure to category exists before calling
 * this function.
 *
 * @since 2.9.2
 *
 * @param int $category_id
 *
 * @return bool
 */
function dokan_set_default_store_category_id( $category_id ) {
    $general_settings = get_option( 'dokan_general', array() );
    $general_settings['store_category_default'] = $category_id;

    $updated_settings = update_option( 'dokan_general', $general_settings );
    $updated_default = update_option( 'default_store_category', $category_id, false );

    return $updated_settings && $updated_default;
}

/**
 * Check if the refund request is allowed to be approved
 *
 * @param int $order_id
 *
 * @return boolean
 */
function dokan_is_refund_allowed_to_approve( $order_id ) {
    if ( ! $order_id ) {
        return false;
    }

    $order                       = wc_get_order( $order_id );
    $order_status                = 'wc-' . $order->get_status();
    $active_order_status         = dokan_withdraw_get_active_order_status();

    if ( in_array( $order_status, $active_order_status ) ) {
        return true;
    }

    return false;
}

/**
 * Nomalize shipping postcode that contains '-' or space
 *
 * @since  2.9.14
 *
 * @param  string $code
 *
 * @return string
 */
function dokan_normalize_shipping_postcode( $code ) {
    return str_replace( [ ' ', '-' ], '', $code );
}

/**
 * Dokan add combine commission
 *
 * @since  2.9.14
 *
 * @param  float $earning  [earning for a vendor or admin]
 * @param  float $commission_rate
 * @param  string $commission_type
 * @param  float $additional_fee
 * @param  float $product_price
 * @param  int $order_id
 *
 * @return float
 */
function dokan_add_combine_commission( $earning, $commission_rate, $commission_type, $additional_fee, $product_price, $order_id ) {
    if ( 'combine' === $commission_type ) {
        // vendor will get 100 percent if commission rate > 100
        if ( $commission_rate > 100 ) {
            return (float) $product_price;
        }

        // If `_dokan_item_total` returns `non-falsy` value that means, the request comes from the `order refund request`.
        // So modify `additional_fee` to the correct amount to get refunded. (additional_fee/item_total)*product_price.
        // Where `product_price` means item_total - refunded_total_for_item.
        $item_total = get_post_meta( $order_id, '_dokan_item_total', true );

        if ( $order_id && $item_total ) {
            $order          = wc_get_order( $order_id );
            $additional_fee = ( $additional_fee / $item_total ) * $product_price;
        }

        $earning       = ( (float) $product_price * $commission_rate ) / 100;
        $total_earning = $earning + $additional_fee;
        $earning       = (float) $product_price - $total_earning;
    }

    return $earning;
}

add_filter( 'dokan_prepare_for_calculation', 'dokan_add_combine_commission', 10, 6 );

/**
 * Dokan save admin additional_fee
 *
 * @since 2.9.14
 *
 * @param int $vendor_id
 * @param array $data
 *
 * @return void
 */
function dokan_save_admin_additional_commission( $vendor_id, $data ) {
    if ( ! current_user_can( 'manage_woocommerce' ) ) {
        return;
    }

    if ( isset( $data['admin_additional_fee'] ) ) {
        $vendor = dokan()->vendor->get( $vendor_id );
        $vendor->update_meta( 'dokan_admin_additional_fee', wc_format_decimal( $data['admin_additional_fee'] ) );
    }
}

add_action( 'dokan_before_update_vendor', 'dokan_save_admin_additional_commission', 10, 2 );

/**
 * Include Dokan Pro template
 *
 * Modules should have their own get
 * template function, like `dokan_geo_get_template`
 * used in Geolocation module.
 *
 * @since 3.0.0
 *
 * @param string $name
 * @param array  $args
 *
 * @return void
 */
function dokan_pro_get_template( $name, $args = [] ) {
    dokan_get_template( "$name.php", $args, 'dokan', trailingslashit( DOKAN_PRO_TEMPLATE_DIR ) );
}

/**
 * Dokan register deactivation hook description]
 *
 * @param string $file     full file path
 * @param array|string $function callback function
 *
 * @return void
 */
function dokan_register_deactivation_hook( $file, $function ) {
    if ( file_exists( $file ) ) {
        require_once $file;
        $base_name = plugin_basename( $file );
        add_action( "dokan_deactivate_{$base_name}", $function );
    }
}
