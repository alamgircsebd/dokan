<?php

namespace WeDevs\DokanPro\Modules\Stripe\Subscriptions;

use Exception;
use Stripe\Plan;
use Stripe\Coupon;
use Stripe\Product;
use Stripe\Customer;
use Stripe\Subscription;
use WeDevs\DokanPro\Modules\Stripe\Helper as StripeHelper;
use WeDevs\DokanPro\Modules\Stripe\Subscriptions\InvoiceEmail;
use DokanPro\Modules\Subscription\Helper as SubscriptionHelper;
use WeDevs\DokanPro\Modules\Stripe\Abstracts\StripePaymentGateway;

defined( 'ABSPATH' ) || exit;

/**
 * Dokan Stripe Subscriptoin Class
 *
 * @since 2.9.13
 */
class ProductSubscription extends StripePaymentGateway {

    /**
     * Source id holder
     *
     * @var string
     */
    protected $source_id;

    /**
     * Plan id holder
     *
     * @var string
     */
    protected $plan_id;

    /**
     * Stripe Customer id holder
     *
     * @var string
     */
    protected $stripe_customer;

    /**
     * Constructor method
     *
     * @since 2.9.13
     */
    public function __construct() {
        StripeHelper::bootstrap_stripe();
        $this->hooks();
    }

    /**
     * All the hooks
     *
     * @since 2.9.13
     *
     * @return void
     */
    public function hooks() {
        add_action( 'wp_ajax_dokan_send_source', [ $this, 'process_recurring_subscripton' ] );
        add_action( 'dokan_process_subscription_order', [ $this, 'process_subscription' ], 10, 3 );
        add_action( 'dps_cancel_recurring_subscription', [ $this, 'cancel_subscription' ], 10, 3 );
        add_action( 'dps_activate_recurring_subscription', [ $this, 'activate_subscription' ], 10, 2 );
        add_action( 'dokan_remove_subscription_forcefully', [ $this, 'remove_subscription' ], 10, 2 );

        // dokan_invoice_payment_action_required send an email agains this hook with `hosted_invoice_url`;
        add_filter( 'woocommerce_email_classes', [ $this, 'load_emails' ] );
        add_filter( 'woocommerce_email_actions', [ $this, 'load_actions' ] );
    }

    /**
     * Process recurring subscription paid with stripe 3ds
     *
     * @return void
     */
    public function process_recurring_subscripton() {
        $data = wp_unslash( $_POST );

        if ( empty( $data['nonce'] ) || ! wp_verify_nonce( $data['nonce'], 'dokan_reviews' ) ) {
            return;
        }

        if ( empty( $data['action'] ) || 'dokan_send_source' !== $data['action'] ) {
            return;
        }

        $this->source_id  = ! empty( $data['source_id'] ) ? wc_clean( $data['source_id'] ) : '';
        $this->product_id = ! empty( $data['product_id'] ) ? wc_clean( $data['product_id'] ) : '';

        $subscription = $this->setup_subscription();

        if ( $subscription ) {
            wp_send_json( $subscription );
        }
    }

    /**
     * Setup subscription data
     *
     * @since 3.0.3
     *
     * @return \Stripe\Subscriptoin
     */
    public function setup_subscription() {
        $dokan_subscription = dokan()->subscription->get( $this->product_id );
        $product_pack       = $dokan_subscription->get_product();
        $product_pack_name  = $product_pack->get_title() . ' #' . $product_pack->get_id();
        $product_pack_id    = $product_pack->get_slug() . '-' . $product_pack->get_id();
        $vendor_id          = get_current_user_id();

        if ( $dokan_subscription->is_recurring() ) {
            $subscription_interval = $dokan_subscription->get_recurring_interval();
            $subscription_period   = $dokan_subscription->get_period_type();
            $subscription_length   = $dokan_subscription->get_period_length();
            $trial_period_days     = $dokan_subscription->is_trial() ? $dokan_subscription->get_trial_period_length() : 0;

            // if vendor already has used a trial pack, create a new plan without trial period
            if ( SubscriptionHelper::has_used_trial_pack( $vendor_id ) ) {
                $trial_period_days = 0;
                $product_pack_id   = $product_pack_id . '-' . random_int( 1, 999999 );
            }

            try {
                $stripe_plan   = Plan::retrieve( $product_pack_id );
                $this->plan_id = $stripe_plan->id;
            } catch ( Exception $e ) {
                $stripe_product = Product::create( [
                   'name' => $product_pack_name,
                   'type' => 'service'
                ] );

                $stripe_plan = Plan::create( [
                    'amount'            => StripeHelper::get_stripe_amount( $product_pack->get_price() ),
                    'interval'          => $subscription_period,
                    'interval_count'    => $subscription_interval,
                    'currency'          => strtolower( get_woocommerce_currency() ),
                    'id'                => $product_pack_id,
                    'product'           => $stripe_product->id,
                    'trial_period_days' => $trial_period_days
                ] );

                $this->plan_id = $stripe_plan->id;
            }

            $subscription = $this->maybe_create_subscription();

            if ( empty( $subscription->id ) ) {
                $error = [
                    'code'    => 'subscription_not_created',
                    'message' => __( 'Unable to create subscription', 'dokan' )
                ];

                return wp_send_json_error( $error, 422 );
            }

            $add_s = ( $subscription_interval != 1 ) ? 's' : '';
            update_user_meta( $vendor_id, 'can_post_product', '1' );
            update_user_meta( $vendor_id, '_stripe_subscription_id', $subscription->id );
            update_user_meta( $vendor_id, 'product_package_id', $product_pack->get_id() );
            update_user_meta( $vendor_id, 'product_no_with_pack', get_post_meta( $product_pack->get_id(), '_no_of_product', true ) );
            update_user_meta( $vendor_id, 'product_pack_startdate', date( 'Y-m-d H:i:s' ) );
            update_user_meta( $vendor_id, 'product_pack_enddate', date( 'Y-m-d H:i:s', strtotime( "+" . $subscription_interval . " " . $subscription_period . "" . $add_s ) ) );
            update_user_meta( $vendor_id, '_customer_recurring_subscription', 'active' );
            update_user_meta( $vendor_id, 'dokan_has_active_cancelled_subscrption', false );

            // need to remove these meta data. Update it on webhook reponse
            $this->setup_commissions( $product_pack, $vendor_id );
            do_action( 'dokan_vendor_purchased_subscription', $vendor_id );

            return $subscription;
        }
    }

    /**
     * Process recurring, non-recurring and (stripe 3ds non-recurring subscriptions)
     *
     * @since 3.0.3
     *
     * @param \WC_Order $order
     * @param \Stripe\Intent $intent
     * @param bool $is_recurring
     *
     * @return void
     */
    public function process_subscription( $order, $intent, $is_recurring = false ) {
        $product_pack       = StripeHelper::get_subscription_product_by_order( $order );
        $dokan_subscription = dokan()->subscription->get( $product_pack->get_id() );
        $vendor_id          = get_current_user_id();

        if ( is_object( $intent ) ) {
            $this->stripe_customer = $intent->customer;
        } else {
            $this->stripe_customer = $intent;
        }

        if ( $is_recurring ) {
            $this->product_id = $product_pack->get_id();
            $subscription     = $this->setup_subscription();

            update_user_meta( get_current_user_id(), 'product_order_id', $order->get_id() );
            $order->add_order_note( sprintf( __( 'Order %s payment is completed via %s on (Charge IDs: %s)', 'dokan' ), $order->get_order_number(), StripeHelper::get_gateway_title(), $subscription->id ) );
            $order->payment_complete();
        } else {
            // Vendor is purchasing non-recurring subscription, so if there is any recurring pack, cancel it first
            $previous_subscription = get_user_meta( $vendor_id, '_stripe_subscription_id', true );

            if ( $previous_subscription ) {
                $this->cancel_now( $previous_subscription, $dokan_subscription );
            }

            $pack_validity = get_post_meta( $product_pack->get_id(), '_pack_validity', true );
            update_user_meta( $vendor_id, 'previous_subscription', false );
            update_user_meta( $vendor_id, 'product_package_id', $product_pack->get_id() );
            update_user_meta( $vendor_id, 'product_order_id', $order->get_id() );
            update_user_meta( $vendor_id, 'product_no_with_pack', get_post_meta( $product_pack->get_id(), '_no_of_product', true ) );
            update_user_meta( $vendor_id, 'product_pack_startdate', date( 'Y-m-d H:i:s' ) );
            update_user_meta( $vendor_id, 'product_pack_enddate', date( 'Y-m-d H:i:s', strtotime( "+$pack_validity days" ) ) );
            update_user_meta( $vendor_id, 'can_post_product', '1' );
            update_user_meta( $vendor_id, '_customer_recurring_subscription', false );
            update_user_meta( $vendor_id, 'dokan_has_active_cancelled_subscrption', false );
        }

        $this->setup_commissions( $product_pack, $vendor_id );
        do_action( 'dokan_vendor_purchased_subscription', $vendor_id );
    }

    /**
     * Maybe create subscription
     *
     * @since DOKAN_PROS_SINCE
     *
     * @return Stripe\Subscription
     */
    protected function maybe_create_subscription() {
        $vendor_subscription      = dokan()->vendor->get( get_current_user_id() )->subscription;
        $already_has_subscription = get_user_meta( get_current_user_id(), '_stripe_subscription_id', true );

        if ( $already_has_subscription && $vendor_subscription && $vendor_subscription->has_recurring_pack() ) {
            try {
                $subscription = Subscription::retrieve( $already_has_subscription );
            } catch ( Exception $e ) {
                return $this->create_subscription();
            }

            // if subscription status is incomplete, cancel it first as incomplete subscription can't be updated
            if ( 'incomplete' === $subscription->status ) {
                $subscription->cancel();
                return $this->create_subscription();
            }

            // if subscription status is incomplete_expired, try to create a new subscription
            if ( 'incomplete_expired' === $subscription->status ) {
                return $this->create_subscription();
            }

            $upgrade = Subscription::update( $already_has_subscription, [
                'cancel_at_period_end' => false,
                'items' => [
                    [
                        'id'   => $subscription->items->data[0]->id,
                        'plan' => $this->plan_id
                    ]
                ],
                'proration_behavior' => 'create_prorations',
                'coupon'  => $this->get_coupon()
            ] );

            $vendor_subscription->reset_active_cancelled_subscription();

            return $upgrade;
        }

        return $this->create_subscription();
    }

    /**
     * Create subscription
     *
     * @since 2.9.13
     *
     * @return Stripe\Subscription
     */
    protected function create_subscription() {
        // Lets carge the vendor while creating new subscription
        $prepared_source = $this->prepare_source( get_current_user_id() );
        $this->validate_source( $prepared_source );
        $this->stripe_customer = $prepared_source->customer;

        $subscription = Subscription::create( [
            'expand'   => ['latest_invoice.payment_intent'],
            'customer' => $this->stripe_customer,
            'items'    => [
                [
                    'plan' => $this->plan_id,
                ],
            ],
            'coupon'          => $this->get_coupon(),
            'trial_from_plan' => true,
        ] );

        return $subscription;
    }

    /**
     * Get coupon id for a subscription
     *
     * @since  2.9.14
     *
     * @return Stripe\Coupon::id |null on failure
     */
    protected function get_coupon() {
        $discount = WC()->cart->get_discount_total();

        if ( ! $discount ) {
            return;
        }

        $coupon = Coupon::create( [
            'duration'   => 'once',
            'id'         => $discount .'_OFF_' . random_int( 1, 999999 ),
            'amount_off' => StripeHelper::get_stripe_amount( $discount ),
            'currency'   => strtolower( get_woocommerce_currency() )
        ] );

        return $coupon->id;
    }

    /**
    * Cancel stripe subscription
    *
    * @since 3.0.3
    *
    * @param int $order_id
    * @param int $vendor_id
    * @param bool $immediately Force subscription to be cancelled immediately. [since 3.0.3]
    *
    * @return void
    **/
    public function cancel_subscription( $order_id, $vendor_id, $cancel_immediately ) {
        $order = wc_get_order( $order_id );

        if ( ! $order || 'dokan-stripe-connect' !== $order->get_payment_method() ) {
            return;
        }

        $vendor_subscription = dokan()->vendor->get( $vendor_id )->subscription;
        $subscription_id     = get_user_meta( $vendor_id, '_stripe_subscription_id', true );

        if ( ! $vendor_subscription || ! $vendor_subscription->has_recurring_pack() ) {
            return;
        }

        if ( $cancel_immediately ) {
            return $this->cancel_now( $subscription_id, $vendor_subscription );
        }

        try {
            Subscription::update(
                $subscription_id,
                [
                    // Cancel the subscription at the end of the current billing period
                    'cancel_at_period_end' => true,
                ]
            );
            $vendor_subscription->set_active_cancelled_subscription();
        } catch ( Exception $e ) {
            if ( StripeHelper::is_no_such_subscription_error( $e->getMessage() ) ) {
                do_action( 'dokan_remove_subscription_forcefully', $vendor_subscription, $subscription_id );
            } else {
                dokan_log( sprintf( __( 'Unable to cancel subscription with stripe. More details: %s', 'dokan' ), $e->getMessage() ) );
            }
        }
    }

    /**
    * Cancel stripe subscription
    *
    * @since 3.0.3
    *
    * @return void
    **/
    public function activate_subscription( $order_id, $vendor_id ) {
        $order = wc_get_order( $order_id );

        if ( ! $order || 'dokan-stripe-connect' !== $order->get_payment_method() ) {
            return;
        }

        $vendor_subscription = dokan()->vendor->get( $vendor_id )->subscription;
        $subscription_id     = get_user_meta( $vendor_id, '_stripe_subscription_id', true );

        if ( ! $vendor_subscription || ! $vendor_subscription->has_recurring_pack() ) {
            return;
        }

        try {
            // Reactivate the subscription
            Subscription::update(
                $subscription_id,
                [
                    'cancel_at_period_end' => false,
                ]
            );
            $vendor_subscription->reset_active_cancelled_subscription();
        } catch ( Exception $e ) {
            dokan_log( sprintf( __( 'Unable to re-activate subscription with stripe. More details: %s', 'dokan' ), $e->getMessage() ) );
        }
    }

    /**
     * Setup commissions
     *
     * @since 3.0.3
     *
     * @param Object $product_pack
     * @param int $vendor_id
     *
     * @return void
     */
    protected function setup_commissions( $product_pack, $vendor_id ) {
        $admin_commission      = get_post_meta( $product_pack->get_id(), '_subscription_product_admin_commission', true );
        $admin_additional_fee  = get_post_meta( $product_pack->get_id(), '_subscription_product_admin_additional_fee', true );
        $admin_commission_type = get_post_meta( $product_pack->get_id(), '_subscription_product_admin_commission_type', true );

        if ( ! empty( $admin_commission ) && ! empty( $admin_additional_fee ) && ! empty( $admin_commission_type ) ) {
            update_user_meta( $vendor_id, 'dokan_admin_percentage', $admin_commission );
            update_user_meta( $vendor_id, 'dokan_admin_additional_fee', $admin_additional_fee );
            update_user_meta( $vendor_id, 'dokan_admin_percentage_type', $admin_commission_type );
        } else if ( ! empty( $admin_commission ) && ! empty( $admin_commission_type ) ) {
            update_user_meta( $vendor_id, 'dokan_admin_percentage', $admin_commission );
            update_user_meta( $vendor_id, 'dokan_admin_percentage_type', $admin_commission_type );
        } else {
            update_user_meta( $vendor_id, 'dokan_admin_percentage', '' );
        }
    }

    /**
     * Cancel the subscription immediately
     *
     * @since 3.0.3
     *
     * @param string $subscription_id
     * @param Object Vendor_subscription
     *
     * @return void
     */
    protected function cancel_now( $subscription_id, $vendor_subscription ) {
        try {
            $subscription = Subscription::retrieve( $subscription_id );
            $subscription->cancel();
            $vendor_subscription->reset_active_cancelled_subscription();
        } catch ( Exception $e ) {
            if ( StripeHelper::is_no_such_subscription_error( $e->getMessage() ) ) {
                do_action( 'dokan_remove_subscription_forcefully', $vendor_subscription, $subscription_id );
            } else {
                dokan_log( sprintf( __( 'Unable to cancel subscription with stripe. More details: %s', 'dokan' ), $e->getMessage() ) );
            }
        }
    }

    /**
     * Load email class
     *
     * @since 3.0.3
     *
     * @param array $emails
     *
     * @return array
     */
    public function load_emails( $emails ) {
        $emails['InvoiceEmail'] = new InvoiceEmail();

        return $emails;
    }

    /**
     * Load email actions
     *
     * @since 3.0.3
     *
     * @param array $actions
     *
     * @return array
     */
    public function load_actions( $actions ) {
        $actions[] = 'dokan_invoice_payment_action_required';

        return $actions;
    }

    /**
     * Remove subscription forcefully. In case webhook is disabled or didn't work for some reason
     * Cancel the subscription in vendor's end. subscription is already removed in stripe's end.
     *
     * @since 3.0.3
     *
     * @return void
     */
    public function remove_subscription( $vendor_subscription, $subscription_id ) {
        $vendor_id = $vendor_subscription->get_vendor();
        $order_id  = get_user_meta( $vendor_id, 'product_order_id', true );

        if ( $vendor_subscription->has_recurring_pack() ) {
            SubscriptionHelper::delete_subscription_pack( $vendor_id, $order_id );
            delete_user_meta( $vendor_id, '_stripe_subscription_id' );
        }
    }
}
