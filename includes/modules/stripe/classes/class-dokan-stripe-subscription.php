<?php
use DokanPro\Modules\Stripe\Helper as Stripe_Helper;
use DokanPro\Modules\Subscription\Helper;

/**
 * Dokan Stripe Subscriptoin Class
 *
 * @since DOKAN_PRO_SINCE
 */
class Dokan_Stripe_Subscription {
    /**
     * Token holder
     *
     * @var string
     */
    public $token = '';

    /**
     * Customer id holder
     *
     * @var string
     */
    public $customer_id = '';

    /**
     * Subscription product id holder
     *
     * @var string
     */
    public $product_id = '';

    /**
     * Constructor method
     *
     * @since DOKAN_PRO_SINCE
     */
    public function __construct() {
        $this->load_stripe_SDK();
        $this->hooks();
    }

    /**
     * Load stripe SDK
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    public function load_stripe_SDK() {
        Stripe_Helper::get_stripe();
        Stripe_Helper::set_app_info();
        Stripe_Helper::set_api_version();

        if ( Stripe_Helper::is_test_mode() ) {
            \Stripe\Stripe::setVerifySslCerts( false );
        }

        $settings = get_option('woocommerce_dokan-stripe-connect_settings');

        if ( ! empty( $settings['testmode'] ) && 'yes' === $settings['testmode'] ) {
            $secret_key = $settings['test_secret_key'];
        } else {
            $secret_key = $settings['secret_key'];
        }

        \Stripe\Stripe::setApiKey( $secret_key );
    }

    /**
     * All the hooks
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    public function hooks() {
        add_action( 'wp_ajax_dokan_send_token', [ $this, 'prepare_subscription_data' ] );
    }

    /**
     * Prepare subscriptoin data
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return object
     */
    public function prepare_subscription_data() {
        if ( ! Stripe_Helper::has_subscription_module() ) {
            return;
        }

        $data = wp_unslash( $_POST );

        if ( empty( $data['nonce'] ) || ! wp_verify_nonce( $data['nonce'], 'dokan_reviews' ) ) {
            return;
        }

        if ( empty( $data['action'] ) || 'dokan_send_token' !== $data['action'] ) {
            return;
        }

        $this->token      = ! empty( $data['token'] ) ? wc_clean( $data['token'] ) : '';
        $this->product_id = ! empty( $data['product_id'] ) ? wc_clean( $data['product_id'] ) : '';

        $product_pack       = wc_get_product( $this->product_id );
        $product_pack_name  = $product_pack->get_title() . ' #' . $product_pack->get_id();
        $product_pack_id    = $product_pack->get_slug() . '-' . $product_pack->get_id();
        $dokan_subscription = dokan()->subscription->get( $product_pack->get_id() );

        if ( $dokan_subscription->is_recurring() ) {
            $subscription_interval = $dokan_subscription->get_recurring_interval();
            $subscription_period   = $dokan_subscription->get_period_type();
            $subscription_length   = $dokan_subscription->get_period_length();
            $trial_period_days     = $dokan_subscription->is_trial() ? $dokan_subscription->get_trial_period_length() : 0;

            // if vendor already has used a trial pack, create a new plan without trial period
            if ( Helper::has_used_trial_pack( get_current_user_id() ) ) {
                $trial_period_days = 0;
                $product_pack_id   = $product_pack_id . '-' . random_int( 1, 99999 );
            }

            if ( Stripe_Helper::is_3d_secure_enabled() ) {
                $this->create_customer();

                try {
                    $stripe_plan   = \Stripe\Plan::retrieve( $product_pack_id );
                    $this->plan_id = $stripe_plan->id;
                } catch ( Exception $e ) {
                    $stripe_product = \Stripe\Product::create( [
                       'name' => $product_pack_name,
                       'type' => 'service'
                    ] );

                    $stripe_plan = \Stripe\Plan::create( [
                        'amount'            => $product_pack->get_price() * 100,
                        'interval'          => $subscription_period,
                        'interval_count'    => $subscription_interval,
                        'currency'          => strtolower( get_woocommerce_currency() ),
                        'id'                => $product_pack_id,
                        'product'           => $stripe_product->id,
                        'trial_period_days' => $trial_period_days
                    ] );

                    $this->plan_id = $stripe_plan->id;
                }
            }

            $subscription = $this->maybe_create_subscription();

            if ( empty( $subscription->id ) ) {
                $error = [
                    'code'    => 'subscription_not_created',
                    'message' => __( 'Unable to create subscription', 'dokan' )
                ];

                return wp_send_json_error( $error, 422 );
            }

            $add_s            = ( $subscription_interval != 1 ) ? 's' : '';
            $customer_user_id = get_current_user_id();

            update_user_meta( $customer_user_id, '_stripe_subscription_id', $subscription->id );
            update_user_meta( $customer_user_id, 'product_package_id', $product_pack->get_id() );
            update_user_meta( $customer_user_id, 'product_no_with_pack', get_post_meta( $product_pack->get_id(), '_no_of_product', true ) );
            update_user_meta( $customer_user_id, 'product_pack_startdate', date( 'Y-m-d H:i:s' ) );
            update_user_meta( $customer_user_id, 'product_pack_enddate', date( 'Y-m-d H:i:s', strtotime( "+" . $subscription_interval . " " . $subscription_period . "" . $add_s ) ) );
            update_user_meta( $customer_user_id, '_customer_recurring_subscription', 'active' );
            update_user_meta( $customer_user_id, 'has_pending_subscription', true );

            $admin_commission      = get_post_meta( $product_pack->get_id(), '_subscription_product_admin_commission', true );
            $admin_commission_type = get_post_meta( $product_pack->get_id(), '_subscription_product_admin_commission_type', true );

            if ( ! empty( $admin_commission ) && ! empty( $admin_commission_type ) ) {
                update_user_meta( $customer_user_id, 'dokan_admin_percentage', $admin_commission );
                update_user_meta( $customer_user_id, 'dokan_admin_percentage_type', $admin_commission_type );
            } else {
                update_user_meta( $customer_user_id, 'dokan_admin_percentage', '' );
            }

            do_action( 'dokan_vendor_purchased_subscription', $customer_user_id );

            return wp_send_json( $subscription );
        }
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
                $subscription = \Stripe\Subscription::retrieve( $already_has_subscription );
            } catch ( Exception $e ) {
                throw new Exception( __( 'Unable to find previous subscription', 'dokan' ), 404 );
            }

            // if subscription status is incomplete, cancel it first as incomplete subscription can't be updated
            if ( 'incomplete' === $subscription->status ) {
                $subscription->cancel();
                return $this->create_subscription();
            }

            $upgrade = \Stripe\Subscription::update( $already_has_subscription, [
                'cancel_at_period_end' => false,
                'items' => [
                    [
                        'id'   => $subscription->items->data[0]->id,
                        'plan' => $this->plan_id
                    ]
                ],
                'prorate' => true
            ] );

            return $upgrade;
        }

        return $this->create_subscription();
    }

    /**
     * Create subscription
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return Stripe\Subscription
     */
    protected function create_subscription() {
        $subscription = \Stripe\Subscription::create( [
            'expand'   => ['latest_invoice.payment_intent'],
            'customer' => $this->customer_id,
            'items'    => [
                [
                    'plan' => $this->plan_id,
                ],
            ],
            'trial_from_plan' => true
        ] );

        return $subscription;
    }

    /**
     * Create customer
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    public function create_customer() {
        if ( $this->customer_id ) {
            return;
        }

        $customer = \Stripe\Customer::create( [
            'source' => $this->token
        ] );

        $this->customer_id = $customer->id;
    }
}

new Dokan_Stripe_Subscription();