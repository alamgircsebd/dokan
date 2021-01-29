<?php

namespace DokanPro\Modules\Subscription;

use DokanPro\Modules\Subscription\SubscriptionPack;
use WeDevs\Dokan\Traits\Singleton;

/**
 * DPS Helper Class
 */
class Helper {

    use Singleton;

    /**
     * Get a sellers remaining product count
     *
     * @param  int $vendor_id
     *
     * @return int|0 on failure
     */
    public static function get_vendor_remaining_products( $vendor_id ) {
        $vendor = dokan()->vendor->get( $vendor_id )->subscription;

        if ( ! $vendor ) {
            return 0;
        }

        return $vendor->get_remaining_products();
    }

    /**
     * Check if its vendor subscribed pack
     *
     * @param integer $product_id
     *
     * @return boolean
     * @throws \Exception
     */
    public static function is_vendor_subscribed_pack( $product_id ) {
        $user_id              = get_current_user_id();
        $current_date         = dokan_current_datetime();
        $product_pack_enddate = self::get_pack_end_date( $user_id );
        $product_package_id   = get_user_meta( $user_id, 'product_package_id', true );

        // if product_id is not same as current purchased package id, return false
        if ( (int) $product_package_id !== (int) $product_id ) {
            return false;
        }

        if ( empty( $product_pack_enddate ) ) {
            return false;
        }

        if ( $product_pack_enddate === 'unlimited' ) {
            return true;
        }

        $validation_date = $current_date->modify( $product_pack_enddate );
        if ( $current_date < $validation_date ) {
            return true;
        }

        return false;
    }

    /**
     * Check package renew for seller
     *
     * @param integer $product_id
     *
     * @return boolean
     * @throws \Exception
     */
    public static function pack_renew_seller( $product_id ) {
        $user_id              = dokan_get_current_user_id(); // in case user is vendor staff, we need vendor user id
        $current_date         = dokan_current_datetime();
        $product_pack_enddate = self::get_pack_end_date( $user_id );
        $product_package_id   = get_user_meta( $user_id, 'product_package_id', true );

        // if product_id is not same as current purchased package id, return false
        if ( (int) $product_package_id !== (int) $product_id ) {
            return false;
        }

        if ( empty( $product_pack_enddate ) ) {
            return false;
        }

        // if product pack end date is unlimited, user does not need to renew their package
        if ( $product_pack_enddate === 'unlimited' ) {
            return false;
        }

        $validation_date = $current_date->modify( $product_pack_enddate );
        if ( $current_date > $validation_date ) {
            return true;
        }

        return false;

        // old code is for reference only, will remove in future release
        /*
        $date = date( 'Y-m-d', strtotime( current_time( 'mysql' ) ) );
        $validation_date = date( 'Y-m-d', strtotime( self::get_pack_end_date( dokan_get_current_user_id() ) ) );

        $datetime1 = new \DateTime( $date );
        $datetime2 = new \DateTime( $validation_date );

        $interval = $datetime1->diff( $datetime2 );

        $interval = $interval->format( '%r%d' );

        if ( (int) $interval <= 3 && (int) $interval >= 0 && ( get_user_meta( get_current_user_id(), 'product_package_id', true ) == $product_id ) ) {
            return true;
        }

        return false;
        */
    }



    /**
     * Returns a readable recurring period
     *
     * @param  string $period
     *
     * @return string
     */
    public static function recurring_period( $period ) {
        switch ($period) {
            case 'day':
                return __( 'day', 'dokan' );

            case 'week':
                return __( 'week', 'dokan' );

            case 'month':
                return __( 'month', 'dokan' );

            case 'year':
                return __( 'year', 'dokan' );

            default:
                return apply_filters( 'dps_recurring_text', $period );
        }
    }

    /**
     * Get a list of options of all the product types
     *
     * @return strings
     */
    public static function get_product_types_options() {
        $selected      = dokan()->subscription->get( get_the_ID() )->get_allowed_product_types();
        $product_types = dokan_get_product_types();
        $output        = '';

        if ( ! $product_types ) {
            return;
        }

        unset( $product_types['external'] );

        foreach ( $product_types as $value => $label ) {
            $output .= '<option value="' . esc_attr( $value ) . '" ';
            $output .= in_array( $value, $selected ) ? ' selected="selected"' : '';
            $output .= '>' . esc_html( $label ) . '</option>';
        }

        echo $output;
    }

    /**
     * Get a list of options for trail period
     *
     * @return string
     */
    public static function get_trial_period_options() {
        $subscription   = dokan()->subscription->get( get_the_ID() );
        $selected_range = $subscription->get_trial_range();
        $range_output   = '';
        $period_range   = range( 1, 30 );
        $range_output   .= '<select name="dokan_subscription_trail_range" class="dokan-subscription-range" style="margin-right: 10px">';

        foreach ( $period_range as $range ) {
            $range_output .= '<option value="' . esc_attr( $range ) . '"';
            $range_output .= selected( $selected_range, $range, false );
            $range_output .= '>' . __( $range, 'dokan' ) . '</option>';
        }

        $range_output .= '</select>';
        echo $range_output;

        $selected_period_types = $subscription->get_trial_period_types();
        $period_types_output   = '';
        $period_types          = apply_filters( 'dokan_subscription_trial_period_types', [
            'day'   => __( 'Day(s)', 'dokan' ),
            'week'  => __( 'Week(s)', 'dokan' ),
            'month' => __( 'Month(s)', 'dokan' ),
            'year'  => __( 'Year(s)', 'dokan' )
        ] );

        $period_types_output .= '<select name="dokan_subscription_trial_period_types">';

        foreach ( $period_types as $key => $value ) {
            $period_types_output .= '<option value="' . esc_attr( $key ) . '"';
            $period_types_output .= selected( $selected_period_types, $key, false );
            $period_types_output .= '>' . $value . '</option>';
        }

        $period_types_output .= '</select>';
        echo $period_types_output;
    }

    /**
     * Get vendor subscription pack id
     *
     * @return int|null on failure
     */
    public static function get_subscription_pack_id() {
        $user_id = dokan_get_current_user_id();

        if ( ! $user_id || ! dokan_is_user_seller( $user_id ) ) {
            return null;
        }

        $subscription_pack_id = get_user_meta( $user_id, 'product_package_id', true );

        if ( ! $subscription_pack_id ) {
            return null;
        }

        return $subscription_pack_id;
    }

    /**
     * Is gallary image upload restricted
     *
     * @return boolean
     */
    public static function is_gallery_image_upload_restricted() {
        return get_post_meta( self::get_subscription_pack_id(), '_enable_gallery_restriction', true );
    }

    /**
     * Get allowed product types of a vendor
     *
     * @return array|empty array on failure
     */
    public static function get_vendor_allowed_product_types() {
        $types  = [];
        $vendor = dokan()->vendor->get( dokan_get_current_user_id() )->subscription;

        if ( $vendor ) {
            $types = $vendor->get_allowed_product_types();
        }

        return $types ? $types : [];
    }

    /**
     * Get allowed product cateogories of a vendor
     *
     * @return array|empty array on failure
     */
    public static function get_vendor_allowed_product_categories() {
        $categories = [];

        $vendor = dokan()->vendor->get( dokan_get_current_user_id() )->subscription;

        if ( $vendor ) {
            $categories = $vendor->get_allowed_product_categories();
        }

        return $categories;
    }

    /**
     * Get subscription recurring interval strings
     *
     * @return string
     */
    public static function get_subscription_period_interval_strings( $interval = '' ) {

        $intervals = array( 1 => _x( 'every', 'period interval (eg "$10 _every_ 2 weeks")', 'dokan' ) );

        foreach ( range( 2, 6 ) as $i ) {
            // translators: period interval, placeholder is ordinal (eg "$10 every _2nd/3rd/4th_", etc)
            $intervals[ $i ] = sprintf( _x( 'every %s', 'period interval with ordinal number (e.g. "every 2nd"', 'dokan' ), self::append_numeral_suffix( $i ) );
        }

        $intervals = apply_filters( 'dokan_pro_subscription_period_interval_strings', $intervals );

        if ( empty( $interval ) ) {
            return $intervals;
        } else {
            return $intervals[ $interval ];
        }
    }

    /**
     * Takes a number and returns the number with its relevant suffix appended, eg. for 2, the function returns 2nd
     *
     * @since 1.0
     */
    public static function append_numeral_suffix( $number ) {

        // Handle teens: if the tens digit of a number is 1, then write "th" after the number. For example: 11th, 13th, 19th, 112th, 9311th. http://en.wikipedia.org/wiki/English_numerals
        if ( strlen( $number ) > 1 && 1 == substr( $number, -2, 1 ) ) {
            // translators: placeholder is a number, this is for the teens
            $number_string = sprintf( __( '%sth', 'woocommerce-subscriptions' ), $number );
        } else { // Append relevant suffix
            switch ( substr( $number, -1 ) ) {
                case 1:
                    // translators: placeholder is a number, numbers ending in 1
                    $number_string = sprintf( __( '%sst', 'woocommerce-subscriptions' ), $number );
                    break;
                case 2:
                    // translators: placeholder is a number, numbers ending in 2
                    $number_string = sprintf( __( '%snd', 'woocommerce-subscriptions' ), $number );
                    break;
                case 3:
                    // translators: placeholder is a number, numbers ending in 3
                    $number_string = sprintf( __( '%srd', 'woocommerce-subscriptions' ), $number );
                    break;
                default:
                    // translators: placeholder is a number, numbers ending in 4-9, 0
                    $number_string = sprintf( __( '%sth', 'woocommerce-subscriptions' ), $number );
                    break;
            }
        }

        return apply_filters( 'woocommerce_numeral_suffix', $number_string, $number );
    }


    /**
     * Returns an array of subscription lengths.
     *
     * PayPal Standard Allowable Ranges
     * D – for days; allowable range is 1 to 90
     * W – for weeks; allowable range is 1 to 52
     * M – for months; allowable range is 1 to 24
     * Y – for years; allowable range is 1 to 5
     *
     * @since 3.2.0
     */
    public static function get_non_cached_subscription_ranges() {

        foreach ( array( 'day', 'week', 'month', 'year' ) as $period ) {

            $subscription_lengths = array(
                _x( 'Never expire', 'Subscription length', 'dokan' ),
            );

            switch ( $period ) {
                case 'day':
                    $subscription_lengths[] = _x( '1 day', 'Subscription lengths. e.g. "For 1 day..."', 'dokan' );
                    $subscription_range = range( 2, 90 );
                    break;
                case 'week':
                    $subscription_lengths[] = _x( '1 week', 'Subscription lengths. e.g. "For 1 week..."', 'dokan' );
                    $subscription_range = range( 2, 52 );
                    break;
                case 'month':
                    $subscription_lengths[] = _x( '1 month', 'Subscription lengths. e.g. "For 1 month..."', 'dokan' );
                    $subscription_range = range( 2, 24 );
                    break;
                case 'year':
                    $subscription_lengths[] = _x( '1 year', 'Subscription lengths. e.g. "For 1 year..."', 'dokan' );
                    $subscription_range = range( 2, 5 );
                    break;
            }

            foreach ( $subscription_range as $number ) {
                $subscription_range[ $number ] = self::get_subscription_period_strings( $number, $period );
            }

            // Add the possible range to all time range
            $subscription_lengths += $subscription_range;

            $subscription_ranges[ $period ] = $subscription_lengths;
        }

        return $subscription_ranges;
    }

    /**
     * Return an i18n'ified associative array of all possible subscription periods.
     *
     * @param int (optional) An interval in the range 1-6
     * @param string (optional) One of day, week, month or year. If empty, all subscription ranges are returned.
     * @return string|array
     * @since 2.0
     */
    public static function get_subscription_period_strings( $number = 1, $period = '' ) {

        // phpcs:disable Generic.Functions.FunctionCallArgumentSpacing.TooMuchSpaceAfterComma
        $translated_periods = apply_filters( 'dokan_pro_subscription_periods',
            array(
                // translators: placeholder is number of days. (e.g. "Bill this every day / 4 days")
                'day'   => sprintf( _nx( 'day',   '%s days',   $number, 'Subscription billing period.', 'dokan' ), $number ), // phpcs:ignore WordPress.WP.I18n.MissingSingularPlaceholder,WordPress.WP.I18n.MismatchedPlaceholders
                // translators: placeholder is number of weeks. (e.g. "Bill this every week / 4 weeks")
                'week'  => sprintf( _nx( 'week',  '%s weeks',  $number, 'Subscription billing period.', 'dokan' ), $number ), // phpcs:ignore WordPress.WP.I18n.MissingSingularPlaceholder,WordPress.WP.I18n.MismatchedPlaceholders
                // translators: placeholder is number of months. (e.g. "Bill this every month / 4 months")
                'month' => sprintf( _nx( 'month', '%s months', $number, 'Subscription billing period.', 'dokan' ), $number ), // phpcs:ignore WordPress.WP.I18n.MissingSingularPlaceholder,WordPress.WP.I18n.MismatchedPlaceholders
                // translators: placeholder is number of years. (e.g. "Bill this every year / 4 years")
                'year'  => sprintf( _nx( 'year',  '%s years',  $number, 'Subscription billing period.', 'dokan' ), $number ), // phpcs:ignore WordPress.WP.I18n.MissingSingularPlaceholder,WordPress.WP.I18n.MismatchedPlaceholders
            ),
            $number
        );
        // phpcs:enable

        return ( ! empty( $period ) ) ? $translated_periods[ $period ] : $translated_periods;
    }

    /**
     * Retaining the API, it makes use of the transient functionality.
     *
     * @param string $period
     * @return bool|mixed
     */
    public static function get_subscription_ranges( $subscription_period = '' ) {
        static $dokan_subscription_locale_ranges = array();

        if ( ! is_string( $subscription_period ) ) {
            $subscription_period = '';
        }

        $locale = function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();

        if ( ! isset( $dokan_subscription_locale_ranges[ $locale ] ) ) {
            $dokan_subscription_locale_ranges[ $locale ] = self::get_non_cached_subscription_ranges();
        }

        $subscription_ranges = apply_filters( 'woocommerce_subscription_lengths', $dokan_subscription_locale_ranges[ $locale ], $subscription_period );

        if ( ! empty( $subscription_period ) ) {
            return $subscription_ranges[ $subscription_period ];
        } else {
            return $subscription_ranges;
        }
    }

    /**
     * Is subscription module is enabled
     *
     * @return boolean
     */
    public static function is_subscription_module_enabled() {
        $is_enabled = dokan_get_option( 'enable_pricing', 'dokan_product_subscription' );

        return 'on' === $is_enabled ? true : false;
    }

    /**
     * Is subscription is enalbed on registration
     *
     * @return boolean
     */
    public static function is_subscription_enabled_on_registration() {
        $is_enabled = dokan_get_option( 'enable_subscription_pack_in_reg', 'dokan_product_subscription' );

        return 'on' === $is_enabled ? true : false;
    }

    /**
     * Check is product is subscription or not
     *
     * @param integer $product_id
     * @return boolean
     */
    public static function is_subscription_product( $product_id ) {
        $product = wc_get_product( $product_id );

        if ( $product && 'product_pack' === $product->get_type() ) {
            return true;
        }

        return false;
    }

    /**
     * Checks the cart to see if it contains a subscription product renewal.
     * Returns the cart_item containing the product renewal, else false.
     *
     * @return boolean
     */
    public static function cart_contains_subscription_renewal( $role = '' ) {
        $contains_renewal = false;

        if ( !empty( WC()->cart->cart_contents ) ) {
            foreach ( WC()->cart->cart_contents as $cart_item ) {
                if ( isset( $cart_item['subscription_renewal'] ) && ( empty( $role ) || $role === $cart_item['subscription_renewal']['role'] ) ) {
                    $contains_renewal = $cart_item;
                    break;
                }
            }
        }

        return $contains_renewal;
    }

    /**
     * Checks the cart to see if it contains a subscription product
     *
     * @return void
     */
    public static function cart_contains_subscription() {
        global $woocommerce;

        $contains_subscription = false;

        if ( self::cart_contains_subscription_renewal( 'child' ) ) {

            $contains_subscription = false;

        } else if ( ! empty( WC()->cart->cart_contents ) ) {
            foreach ( WC()->cart->cart_contents as $cart_item ) {
                if ( self::is_subscription_product( $cart_item['product_id'] ) ) {
                    $contains_subscription = true;
                    break;
                }
            }
        }

        return $contains_subscription;
    }

    /**
     * Removes all subscription products from the shopping cart.
     *
     * @return void
     */
    public static function remove_subscriptions_from_cart() {

        foreach ( WC()->cart->cart_contents as $cart_item_key => $cart_item ) {
            if ( self::is_subscription_product( $cart_item['product_id'] ) ) {
                WC()->cart->set_quantity( $cart_item_key, 0 );
            }
        }
    }

    /**
     * Helper function for loggin
     *
     * @param string $message
     */
    public static function log( $message ) {
        $message = sprintf( "[%s] %s: %s", date( 'd.m.Y h:i:s' ), __( 'Dokan Vendor Subscription: ', 'dokan' ), $message );
        dokan_log( $message );
    }

    /**
     * Delete Subscription pack
     *
     * @param integer $customer_id
     *
     * @return void
     */
    public static function delete_subscription_pack( $customer_id, $order_id ) {
        if ( $order_id != get_user_meta( $customer_id, 'product_order_id', true ) ) {
            return;
        }

        do_action( 'dokan_subscription_cancelled', $customer_id, get_user_meta( $customer_id, 'product_package_id', true ) );

        delete_user_meta( $customer_id, 'product_order_id' );
        delete_user_meta( $customer_id, 'product_pack_enddate' );
        delete_user_meta( $customer_id, 'product_package_id' );
        delete_user_meta( $customer_id, 'product_no_with_pack' );
        delete_user_meta( $customer_id, 'product_pack_startdate' );
        delete_user_meta( $customer_id, 'can_post_product' );
        delete_user_meta( $customer_id, 'dokan_admin_percentage' );
        delete_user_meta( $customer_id, 'dokan_has_active_cancelled_subscrption' );
        delete_user_meta( $customer_id, 'dokan_vendor_subscription_cancel_email' );
        delete_user_meta( $customer_id, '_paypal_subscriber_ID' );
        delete_user_meta( $customer_id, '_customer_recurring_subscription' );

        // make product status draft after subscriptions is got cancelled.
        if ( Helper::check_vendor_has_existing_product( $customer_id ) ) {
            Helper::make_product_draft( $customer_id );
        }
    }

    /**
     * Check if a vendor has existing product
     *
     * @param int  $user_id
     *
     * @return boolean
     */
    public static function check_vendor_has_existing_product( $user_id ) {
        $query = get_posts( "post_type=product&author=$user_id&post_status=any" );

        self::log( 'Product exist check: As the package has expired of user #' . $user_id . ' we are checking if he has any product' );

        if ( $query ) {
            return true;
        }

        return false;
    }

    /**
     * Upadate Product Status
     *
     * @param int $user_id
     *
     * @return void
     */
    public static function make_product_draft( $user_id ) {
        global $wpdb;

        $status = dokan_get_option( 'product_status_after_end', 'dokan_product_subscription', 'draft' );
        self::log ( 'Product status check: As the package has expired of user #' . $user_id . ', we are changing his existing product status to ' . $status );
        $wpdb->query( "UPDATE $wpdb->posts SET post_status = '$status' WHERE post_author = '$user_id' AND post_type = 'product' AND post_status='publish'" );
    }

    /**
     * Alert before 2 days end of subscription
     *
     * @return boolean
     */
    public static function alert_before_two_days( $user_id ) {
        // check if email already sent to client
        if ( 'yes' === get_user_meta( $user_id, 'dokan_vendor_subscription_cancel_email', true ) ) {
            return false;
        }

        // if product pack end date is unlimited return false
        if ( 'unlimited' === self::get_pack_end_date( $user_id ) ) {
            return false;
        }

        $alert_days = dokan_get_option( 'no_of_days_before_mail', 'dokan_product_subscription' );

        if ( (int) $alert_days === 0 ) {
            $alert_days = 2;
        }

        $current_date   = dokan_current_datetime();
        $alert_date     = dokan_current_datetime()->modify( self::get_pack_end_date( $user_id ) )->modify( "- $alert_days days" );

        if ( $current_date >= $alert_date ) {
            return true;
        }

        return false;
    }

    /**
     * Get pack end date
     *
     * @return string
     */
    public static function get_pack_end_date( $vendor_id ) {
        return get_user_meta( $vendor_id, 'product_pack_enddate', true );
    }

    /**
     * Update can_post_product flag on subscripton expire
     *
     * @return boolean
     */
    public static function maybe_cancel_subscription( $vendor_id ) {
        if ( 'unlimited' === self::get_pack_end_date( $vendor_id ) ) {
            return false;
        }

        $current_date    = dokan_current_datetime();
        $validation_date = $current_date->modify( self::get_pack_end_date( $vendor_id ) );

        if ( $current_date > $validation_date ) {
            self::log( 'Subscription validity check ( ' . $current_date->format( 'Y-m-d' ) . ' ): checking subscription pack validity of user #' . $vendor_id . '. This users subscription pack will expire on ' . $validation_date->format( 'Y-m-d' ) );
            return true;
        }

        return false;
    }

    /**
     * Determine if the user has used a free pack before
     *
     * @param int $user_id
     *
     * @return boolean
     */
    public static function has_used_trial_pack( $user_id ) {
        $has_used = get_user_meta( $user_id, 'dokan_used_trial_pack', true );

        if ( ! $has_used ) {
            return false;
        }

        return true;
    }

    /**
     * Make product status publish
     *
     * @param int $user_id
     *
     * @return void
     */
    public static function make_product_publish( $user_id ) {
        global $wpdb;

        $wpdb->query( "UPDATE $wpdb->posts SET post_status = 'publish' WHERE post_author = '$user_id' AND post_type = 'product' AND post_status != 'publish'" );
    }

    /**
     * Add used trial pack
     *
     * @param int $user_id
     * @param int $pack_id
     *
     * @return void
     */
    public static function add_used_trial_pack( $user_id, $pack_id ) {
        $subscription = dokan()->subscription->get( $pack_id );

        if ( empty( $subscription->pack_id ) ) {
            return false;
        }

        if ( ! $subscription->is_trial() ) {
            return false;
        }

        update_user_meta( $user_id, 'dokan_used_trial_pack', true );
    }

    /**
     * Check wheter vendor is subscribed or not
     *
     * @since 2.9.13
     *
     * @param int $vendor_id
     *
     * @return boolean
     */
    public static function vendor_has_subscription( $vendor_id ) {
        return get_user_meta( $vendor_id, 'product_package_id', true );
    }

    /**
     * Check wheter the pack is recurring or not
     *
     * @since 2.9.13
     *
     * @param int $pack_id
     *
     * @return boolean
     */
    public static function is_recurring_pack( $pack_id ) {
        $subscription = new SubscriptionPack( $pack_id );

        return $subscription->is_recurring();
    }

    /**
     * Check wheter vendor can publish unlimited products or not
     *
     * @since 2.9.13
     *
     * @param int $vendor_id
     *
     * @return boolean
     */
    public static function vendor_can_publish_unlimited_products( $vendor_id ) {
        return '-1' === self::get_vendor_remaining_products( $vendor_id );
    }
}

Helper::instance();
