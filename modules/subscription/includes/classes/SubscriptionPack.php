<?php

namespace DokanPro\Modules\Subscription;

use DokanPro\Modules\Subscription\Abstracts\VendorSubscription;

defined( 'ABSPATH' ) || exit;

/**
 * Dokan Subscription Pack Class
 */
class SubscriptionPack extends VendorSubscription {
    /**
     * Hold Pack ID
     *
     * @var integer
     */
    public $pack_id = 0;

    /**
     * Constructor mtehod
     *
     * @param int $id
     * @param int $vendor_id
     *
     * @return void
     */
    public function __construct( $id = null, $vendor_id = null ) {
        if ( $id ) {
            $this->pack_id = $id;
        }

        if ( $vendor_id ) {
            $this->vendor_id = $vendor_id;
        }
    }

    /**
     * Get vendor id
     *
     * @return int
     */
    public function get_vendor() {
        return $this->vendor_id;
    }

    /**
     * Get the all the subscription packages
     *
     * @param array $args
     *
     * @return array
     */
    public function all( $args = [] ) {
        return $this->get_packages( $args );
    }

    /**
     * Get all subscription packages
     *
     * @param array $args
     *
     * @return object
     */
    public function get_packages( $args = [] ) {
        $defaults = [
            'post_type' => 'product',
            'post_status' => 'publish',
            'tax_query' => [
                [
                    'taxonomy' => 'product_type',
                    'field'    => 'slug',
                    'terms'    => 'product_pack'
                ]
            ],
            'posts_per_page' => -1,
            'orderby'        => 'menu_order title',
            'order'          => 'ASC'
        ];

        $args = wp_parse_args( $args, $defaults );

        return new \WP_Query( apply_filters( 'dps_get_subscription_pack_arg', $args ) );
    }

    /**
     * Get individiual pack id (ei: dokan->subscription->get( $pack_id )->pack_details())
     *
     * @param $pack_id
     *
     * @return class instance
     */
    public function get( $pack_id ) {
        $this->pack_id = $pack_id;

        return $this;
    }

    /**
     * Get object ID
     *
     * @return int $pack_id
     */
    public function get_id() {
        return $this->pack_id;
    }

    /**
     * Get allowed product types against a subscription pack
     *
     * @return array|empty array on failure
     */
    public function get_allowed_product_types() {
        $types = [];

        if ( $this->get_id() ) {
            $types = get_post_meta( $this->get_id(), 'dokan_subscription_allowed_product_types', true );
        }

        return $types ? $types : [];
    }

    /**
     * Get allowed categories against a subscription pack
     *
     * @return array|empty array on failure
     */
    public function get_allowed_product_categories() {
        $categories = [];

        if ( $this->get_id() ) {
            $categories = get_post_meta( $this->get_id(), '_vendor_allowed_categories', true );
        }

        return $categories;
    }

    /**
     * Is gallary image upload restricted against a subscription pack
     *
     * @return boolean
     */
    public function is_gallery_image_upload_restricted() {
        $restricted = get_post_meta( $this->get_id(), '_enable_gallery_restriction', true );

        return 'yes' === $restricted ? true : false;
    }

    /**
     * Gallary image upload count
     *
     * @return int
     */
    public function gallery_image_upload_count() {
        $count = get_post_meta( $this->get_id(), '_gallery_image_restriction_count', true );

        return ! empty($count) ? intval( $count ) : -1;
    }

    /**
     * Is trial
     *
     * @return boolean
     */
    public function is_trial() {
        $is_trial = get_post_meta( $this->get_id(), 'dokan_subscription_enable_trial', true );

        return 'yes' === $is_trial ? true : false;
    }

    /**
     * Get trial subscription range (ei: how many days or weeks)
     *
     * @return int
     */
    public function get_trial_range() {
        return get_post_meta( $this->get_id(), 'dokan_subscription_trail_range', true );
    }

    /**
     * Get trial subscription period typs (ei; dyas, weeks, months)
     *
     * @return string
     */
    public function get_trial_period_types() {
        return get_post_meta( $this->get_id(), 'dokan_subscription_trial_period_types', true );
    }

    /**
     * Get trial period length (ei: number of days)
     *
     * @return int
     */
    public function get_trial_period_length() {
        $range  = $this->get_trial_range();
        $types  = $this->get_trial_period_types();
        $length = 0;

        if ( ! $range || ! $types ) {
            return 0;
        }

        switch ( $types ) {
            case 'week':
                $length = 7 * $range;
                break;

            case 'month':
                $length = 30 * $range;
                break;

            case 'year':
                $length = 365 * $range;
                break;

            default:
                $length = $range;
                break;
        }

        return absint( $length );
    }

    /**
     * Get trial end time (ei: required for paypal)
     *
     * @return timestamp
     */
    public function get_trial_end_time() {
        $length = $this->get_trial_period_length();

        if ( ! $length ) {
            return 0;
        }

        $date_time = dokan_current_datetime()->modify( "+ {$length} days" );

        return intval( $date_time->getTimestamp() );
    }

    /**
     * @return string
     */
    public function get_product_pack_end_date() {
        $end_date       = 'unlimited';
        $subscription_length    = $this->get_period_length(); //_dokan_subscription_length : billing cycle stops
        $subscription_period    = $this->get_period_type(); //_dokan_subscription_period : day week month year
        $pack_validity          = absint( $this->get_pack_valid_days() ); //_pack_validity

        if ( $this->is_recurring() && $subscription_length > 0 ) {
            // if subscription_length is greater that zero product pack enddate will be equal to subscription_length
            try {
                $add_s          = $subscription_length > 1 ? 's' : '';
                $date_time      = dokan_current_datetime()
                                  ->modify( "+ {$subscription_length} {$subscription_period}{$add_s}" );

                // now add trial time if exists.
                if ( $this->is_trial() ) {
                    $trial_length = $this->get_trial_period_length();
                    $date_time = $date_time->modify( "+ $trial_length days" );
                }
                // finally get formatted end date
                $end_date = $date_time->format( 'Y-m-d H:i:s' );

            } catch ( \Exception $exception ) {
                $end_date = 'unlimited';
            }
        } elseif ( ! $this->is_recurring() && $pack_validity !== 0 ) {
            try {
                $date_time = dokan_current_datetime()
                            ->modify( "+{$pack_validity} days" );
                $end_date = $date_time->format( 'Y-m-d H:i:s' );
            } catch ( \Exception $exception ) {
                $end_date = 'unlimited';
            }
        }

        return $end_date;
    }

    /**
     * Get number of products against a subscripton pack
     *
     * @return int
     */
    public function get_number_of_products() {
        return get_post_meta( $this->get_id(), '_no_of_product', true );
    }

    /**
     * Get subscription product instance
     *
     * @return object|false on failure
     */
    public function get_product() {
        return wc_get_product( $this->get_id() );
    }

    /**
     * Get subscirption pack title
     *
     * @return stirng
     */
    public function get_package_title() {
        $package = $this->get_product();

        return $package ? $package->get_title() : '';
    }

    /**
     * Get valid days of a subscription pack
     *
     * @return int
     */
    public function get_pack_valid_days() {
        return get_post_meta( $this->get_id(), '_pack_validity', true );
    }

    /**
     * Check if is recurring pack
     *
     * @return boolean
     */
    public function is_recurring() {
        $is_recurring = get_post_meta( $this->get_id(), '_enable_recurring_payment', true );

        return 'yes' === $is_recurring ? true : false;
    }

    /**
     * Get subscription pack recurring interval
     *
     * @return int
     */
    public function get_recurring_interval() {
        return (int) get_post_meta( $this->get_id(), '_dokan_subscription_period_interval', true );
    }

    /**
     * Get subscription pack period type (ei: day, month, year)
     *
     * @return string
     */
    public function get_period_type() {
        return get_post_meta( $this->get_id(), '_dokan_subscription_period', true );
    }

    /**
     * Get subscription pack period lenght
     *
     * @return int
     */
    public function get_period_length() {
        return absint( get_post_meta( $this->get_id(), '_dokan_subscription_length', true ) );
    }

    /**
     * Get subscription pack price
     *
     * @return flaot
     */
    public function get_price() {
        $package = $this->get_product();

        return $package ? $package->get_price() : 0;
    }
}
