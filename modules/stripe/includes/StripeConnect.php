<?php

namespace WeDevs\DokanPro\Modules\Stripe;

use WC_AJAX;
use Exception;
use WeDevs\DokanPro\Modules\Stripe\Payment_Tokens;
use WeDevs\Dokan\Exceptions\DokanException;
use WeDevs\DokanPro\Modules\Stripe\Helper;
use DokanPro\Modules\Subscription\Helper as SubscriptionHelper;
use WeDevs\DokanPro\Modules\Stripe\DokanStripe;
use WeDevs\DokanPro\Modules\Stripe\Abstracts\StripePaymentGateway;

defined( 'ABSPATH' ) || exit;

class StripeConnect extends StripePaymentGateway {

    /**
     * Constructor method
     *
     * @since 3.0.3
     *
     * @return vois
     */
    public function __construct() {
        $this->id                 = 'dokan-stripe-connect';
        $this->method_title       = __( 'Dokan Stripe Connect', 'dokan' );
        $this->method_description = __( 'Have your customers pay with credit card.', 'dokan' );
        $this->icon               = DOKAN_STRIPE_ASSETS . 'images/cards.png';
        $this->has_fields         = true;
        $this->supports           = [ 'products', 'refund', 'subscriptions', 'tokenization' ];

        $this->init_form_fields();
        $this->init_settings();

        $this->title           = $this->get_option( 'title' );
        $this->description     = $this->get_option( 'description' );
        $this->enabled         = $this->get_option( 'enabled' );
        $this->testmode        = 'yes' === $this->get_option( 'testmode' );
        $this->secret_key      = $this->testmode ? $this->get_option( 'test_secret_key' ) : $this->get_option( 'secret_key' );
        $this->publishable_key = $this->testmode ? $this->get_option( 'test_publishable_key' ) : $this->get_option( 'publishable_key' );
        $this->saved_cards     = 'yes' === $this->get_option( 'saved_cards' );
        $this->checkout_modal  = 'yes' === $this->get_option( 'stripe_checkout' );
        $this->checkout_locale = $this->get_option( 'stripe_checkout_locale' );
        $this->checkout_image  = $this->get_option( 'stripe_checkout_image' );
        $this->checkout_label  = $this->get_option( 'stripe_checkout_label' );
        $this->currency        = strtolower( get_woocommerce_currency() );
        $this->stripe_meta_key = '_dokan_stripe_charge_id_';

        Helper::bootstrap_stripe();

        $this->hooks();
    }

    /**
     * Initialise Gateway Settings Form Fields
     *
     * @since 3.0.3
     *
     * @return void
     */
    public function init_form_fields() {
        $this->form_fields = require( dirname( __FILE__ ) . '/Settings/StripeConnect.php' );
    }

    /**
     * Init all the hooks
     *
     * @since 3.0.3
     *
     * @return void
     */
    public function hooks() {
        add_action( 'wp_enqueue_scripts', [ $this, 'payment_scripts' ] );
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [ $this, 'process_admin_options' ] );
        add_filter( 'woocommerce_payment_successful_result', [ $this, 'modify_successful_payment_result' ], 99999, 2 );
        add_action( 'woocommerce_checkout_order_review', [ $this, 'set_subscription_data' ] );
        add_action( 'woocommerce_customer_save_address', [ $this, 'show_update_card_notice' ], 10, 2 );
    }

    /**
     * Checks if gateway should be available to use
     *
     * @since 3.0.3
     *
     * @return bool
     */
    public function is_available() {
        if ( is_add_payment_method_page() && ! $this->saved_cards && ! Helper::is_3d_secure_enabled() ) {
            return false;
        }

        return parent::is_available();
    }

    /**
     * Adds a notice for customer when they update their billing address.
     *
     * @since 3.0.3
     *
     * @param int    $user_id      The ID of the current user.
     * @param string $load_address The address to load.
     *
     * @return void
     */
    public function show_update_card_notice( $user_id, $load_address ) {
        if ( ! $this->saved_cards || ! Payment_Tokens::customer_has_saved_methods( $user_id ) || 'billing' !== $load_address ) {
            return;
        }

        wc_add_notice( sprintf( __( 'If your billing address has been changed for saved payment methods, be sure to remove any %1$ssaved payment methods%2$s on file and re-add them.', 'dokan' ), '<a href="' . esc_url( wc_get_endpoint_url( 'payment-methods' ) ) . '" class="wc-stripe-update-card-notice" style="text-decoration:underline;">', '</a>' ), 'notice' );
    }

    /**
     * Setup subcription data
     *
     * @since 3.0.3
     *
     * @return void
     */
    public function set_subscription_data() {
        if ( empty( WC()->session ) ) {
            return;
        }

        $session = WC()->session;

        foreach ( $session->cart as $data ) {
            $product_id = ! empty( $data['product_id'] ) ? $data['product_id'] : 0;
            break;
        }

        $contains_subscription_product = ! empty( $product_id )
            && Helper::has_subscription_module()
            && SubscriptionHelper::is_subscription_product( $product_id );

        if ( $contains_subscription_product ) {
            // @todo in the upcoming version, allow saving cards while purchasing subscription product
            set_transient( $this->id . '_contains_subscription_product', true, MINUTE_IN_SECONDS );
        }

        if ( $contains_subscription_product && SubscriptionHelper::is_recurring_pack( $product_id ) ) {
            $name        = $session->customer['first_name'] . ' ' . $session->customer['last_name'];
            $address_1   = $session->customer['address_1'];
            $address_2   = $session->customer['address_2'];
            $city        = $session->customer['city'];
            $state       = $session->customer['state'];
            $country     = $session->customer['country'];
            $postal_code = $session->customer['postcode'];
            $email       = $session->customer['email'];
            ?>
            <div class="dokan-stripe-intent">
                <input type="hidden" name="dokan_payment_customer_name" id="dokan-payment-customer-name" value="<?php echo esc_attr( $name ); ?>">
                <input type="hidden" name="dokan_payment_customer_email" id="dokan-payment-customer-email" value="<?php echo esc_attr( $email ); ?>">
                <input type="hidden" name="dokan_payment_customer_address_1" id="dokan-payment-customer-address_1" value="<?php echo esc_attr( $address_1 ); ?>">
                <input type="hidden" name="dokan_payment_customer_address_2" id="dokan-payment-customer-address_2" value="<?php echo esc_attr( $address_2 ); ?>">
                <input type="hidden" name="dokan_payment_customer_postal_code" id="dokan-payment-customer-postal_code" value="<?php echo esc_attr( $postal_code ); ?>">
                <input type="hidden" name="dokan_payment_customer_city" id="dokan-payment-customer-city" value="<?php echo esc_attr( $city ); ?>">
                <input type="hidden" name="dokan_payment_customer_state" id="dokan-payment-customer-state" value="<?php echo esc_attr( $state ); ?>">
                <input type="hidden" name="dokan_payment_customer_country" id="dokan-payment-customer-country" value="<?php echo esc_attr( $country ); ?>">
                <input type="hidden" name="dokan_subscription_product_id" id="dokan-subscription-product-id" value="<?php echo esc_attr( $product_id ); ?>">
            </div>
            <?php
        }
    }

    /**
     * Enqueue assets
     *
     * @since 3.0.3
     *
     * @return void
     */
    public function payment_scripts() {
        if ( ! is_checkout() && ! is_add_payment_method_page() && ! isset( $_GET['pay_for_order'] ) ) {
            return;
        }

        wp_enqueue_style( 'dokan_stripe', DOKAN_STRIPE_ASSETS . 'css/stripe.css' );

        if ( ! Helper::is_3d_secure_enabled() && $this->checkout_modal && ! is_add_payment_method_page() ) {
            wp_enqueue_script( 'stripe', 'https://checkout.stripe.com/v2/checkout.js', '', '2.0', true );
            wp_enqueue_script( 'dokan_stripe', plugins_url( 'assets/js/stripe-checkout.js', dirname( __FILE__ ) ), [ 'stripe' ], false, true );
        } else if ( ! Helper::is_3d_secure_enabled() && ! is_add_payment_method_page() ) {
            wp_enqueue_script( 'stripe', 'https://js.stripe.com/v1/', '', '1.0', true );
            wp_enqueue_script( 'dokan_stripe', plugins_url( 'assets/js/stripe.js', dirname( __FILE__ ) ), [ 'jquery','stripe' ], false, false );
        }

        if ( Helper::is_3d_secure_enabled() || is_add_payment_method_page() ) {
            wp_enqueue_script( 'stripe', 'https://js.stripe.com/v3/', [], '', true );
            wp_enqueue_script( 'dokan_stripe', plugins_url( 'assets/js/stripe-3ds.js', dirname( __FILE__ ) ), [ 'jquery', 'stripe' ], false, true );
        }

        $stripe_params = [
            'is_3ds'                => Helper::is_3d_secure_enabled(),
            'key'                   => $this->publishable_key,
            'is_checkout'           => is_checkout() & empty( $_GET['pay_for_order'] ) ? 'yes' : 'no',
            'is_pay_for_order_page' => is_wc_endpoint_url( 'order-pay' ) ? 'yes' : 'no',
            'name'                  => get_bloginfo( 'name' ),
            'description'           => get_bloginfo ( 'description' ),
            'label'                 => sprintf( __( '%s', 'dokan') , $this->checkout_label ),
            'locale'                => $this->checkout_locale,
            'image'                 => $this->checkout_image,
            'i18n_terms'            => __( 'Please accept the terms and conditions first', 'dokan' ),
            'i18n_required_fields'  => __( 'Please fill in required checkout fields first', 'dokan' ),
            'invalid_request_error' => __( 'Unable to process this payment, please try again or use alternative method.', 'dokan' ),
            'email_invalid'         => __( 'Invalid email address, please correct and try again.', 'dokan' ),
        ];

        if ( is_checkout_pay_page() || isset( $_GET['pay_for_order'] ) && 'true' === $_GET['pay_for_order'] ) {
            if ( is_checkout_pay_page() && isset( $_GET['order'] ) && isset( $_GET['order_id'] ) ) {
                $order_key = urldecode( $_GET['order'] );
                $order_id  = absint( $_GET['order_id'] );
                $order     = wc_get_order( $order_id );
            }

            // If we're on the pay page we need to pass stripe.js the address of the order.
            if ( isset( $_GET['pay_for_order'] ) && 'true' === $_GET['pay_for_order'] ) {
                $order_key = urldecode( $_GET['key'] );
                $order_id  = wc_get_order_id_by_order_key( $order_key );
                $order     = wc_get_order( $order_id );
            }

            if ( empty( $order ) ) {
                return;
            }

            if ( dokan_get_prop( $order, 'id') == $order_id && dokan_get_prop( $order, 'order_key') == $order_key ) {
                $stripe_params['billing_first_name'] = dokan_get_prop( $order , 'billing_first_name');
                $stripe_params['billing_last_name']  = dokan_get_prop( $order , 'billing_last_name');
                $stripe_params['billing_address_1']  = dokan_get_prop( $order , 'billing_address_1');
                $stripe_params['billing_address_2']  = dokan_get_prop( $order , 'billing_address_2');
                $stripe_params['billing_state']      = dokan_get_prop( $order , 'billing_state');
                $stripe_params['billing_city']       = dokan_get_prop( $order , 'billing_city');
                $stripe_params['billing_postcode']   = dokan_get_prop( $order , 'billing_postcode');
                $stripe_params['billing_country']    = dokan_get_prop( $order , 'billing_country');
            }
        }

        wp_localize_script( 'dokan_stripe', 'dokan_stripe_connect_params', apply_filters( 'dokan_stripe_js_params', $stripe_params) );
    }

    /**
     * Admin Panel Options
     * - Options for bits like 'title' and availability on a country-by-country basis
     */
    public function admin_options() {
        ?>
        <h3><?php _e( 'Stripe Connect', 'dokan' ); ?></h3>
        <p><?php _e( 'Stripe works by adding credit card fields on the checkout and then sending the details to Stripe for verification.', 'dokan' ); ?></p>
        <?php
            echo '<p>' . sprintf( __( 'Recurring subscription requires webhooks to be configured. Go to <a href="%s">webhook</a> and set your webhook url <code>%s</code>. Otherwise recurring payment not working automatically', 'dokan' ), 'https://dashboard.stripe.com/account/webhooks', add_query_arg( array( 'webhook' => 'dokan' ), home_url('/') ) ) . '</p>';
         ?>
        <?php if ( in_array( get_option( 'woocommerce_currency' ), array( 'AED','AFN','ALL','AMD','ANG','AOA','ARS','AUD','AWG','AZN','BAM','BBD','BDT','BGN','BIF','BMD','BND','BOB','BRL','BSD','BWP','BZD','CAD','CDF','CHF','CLP','CNY','COP','CRC','CVE','CZK','DJF','DKK','DOP','DZD','EEK','EGP','ETB','EUR','FJD','FKP','GBP','GEL','GIP','GMD','GNF','GTQ','GYD','HKD','HNL','HRK','HTG','HUF','IDR','ILS','INR','ISK','JMD','JPY','KES','KGS','KHR','KMF','KRW','KYD','KZT','LAK','LBP','LKR','LRD','LSL','LTL','LVL','MAD','MDL','MGA','MKD','MNT','MOP','MRO','MUR','MVR','MWK','MXN','MYR','MZN','NAD','NGN','NIO','NOK','NPR','NZD','PAB','PEN','PGK','PHP','PKR','PLN','PYG','QAR','RON','RSD','RUB','RWF','SAR','SBD','SCR','SEK','SGD','SHP','SLL','SOS','SRD','STD','SVC','SZL','THB','TJS','TOP','TRY','TTD','TWD','TZS','UAH','UGX','USD','UYU','UZS','VEF','VND','VUV','WST','XAF','XCD','XOF','XPF','YER','ZAR','ZMW' ) ) ) { ?>
        <table class="form-table">
            <?php $this->generate_settings_html(); ?>
        </table><!--/.form-table-->

        <?php } else { ?>

        <div class="inline error">
            <p>
                <strong><?php _e( 'Gateway Disabled', 'dokan' ); ?></strong>
                <?php echo __( 'Choose a currency supported by Stripe as your store currency to enable Stripe Connect.', 'dokan' ); ?>
            </p>
        </div>
        <?php }
    }

    /**
     * Payment form on checkout page
     *
     * @since 3.0.3
     *
     * @return void
     */
    public function payment_fields() {
        $user                 = wp_get_current_user();
        $display_tokenization = $this->supports( 'tokenization' ) && is_checkout() && $this->saved_cards;
        $total                = WC()->cart->total;
        $user_email           = '';
        $description          = $this->get_description();
        $description          = ! empty( $description ) ? $description : '';
        $firstname            = '';
        $lastname             = '';

        // If paying from order, we need to get total from order not cart.
        if ( isset( $_GET['pay_for_order'] ) && ! empty( $_GET['key'] ) ) { // wpcs: csrf ok.
            $order      = wc_get_order( wc_get_order_id_by_order_key( wc_clean( $_GET['key'] ) ) ); // wpcs: csrf ok, sanitization ok.
            $total      = $order->get_total();
            $user_email = $order->get_billing_email();
        } else {
            if ( $user->ID ) {
                $user_email = get_user_meta( $user->ID, 'billing_email', true );
                $user_email = $user_email ? $user_email : $user->user_email;
            }
        }

        if ( is_add_payment_method_page() ) {
            $firstname = $user->user_firstname;
            $lastname  = $user->user_lastname;
        }

        ob_start();
        echo '<div
            id="dokan-stripe-payment-data"
            data-email="' . esc_attr( $user_email ) . '"
            data-full-name="' . esc_attr( $firstname . ' ' . $lastname ) . '"
            data-currency="' . esc_attr( strtolower( get_woocommerce_currency() ) ) . '"
        >';

        if ( $this->testmode ) {
            $description .= ' ' . sprintf( __( 'TEST MODE ENABLED. In test mode, you can use the card number 4242424242424242 with any CVC and a valid expiration date or check the <a href="%s" target="_blank">Testing Stripe documentation</a> for more card numbers.', 'dokan' ), 'https://stripe.com/docs/testing' );
        }

        $description                   = trim( $description );
        $contains_subscription_product = get_transient( $this->id . '_contains_subscription_product' );
        echo apply_filters( 'dokan_stripe_description', wpautop( wp_kses_post( $description ) ), $this->id ); // wpcs: xss ok.

        if ( ! $contains_subscription_product && $display_tokenization ) {
            $this->tokenization_script();
            $this->saved_payment_methods();
        }

        $this->elements_form();

        if ( ! $contains_subscription_product
            && ! is_add_payment_method_page()
            && apply_filters( 'dokan_stripe_display_save_payment_method_checkbox', $display_tokenization ) ) {
            $this->save_payment_method_checkbox();
        }

        do_action( 'dokan_stripe_cards_payment_fields', $this->id );
        echo '</div>';
        ob_end_flush();
    }

    /**
     * Renders the Stripe elements form
     *
     * @since 3.0.3
     *
     * @return void
     */
    public function elements_form() {
        ?>
        <fieldset id="wc-<?php echo esc_attr( $this->id ); ?>-cc-form" class="wc-credit-card-form wc-payment-form" style="background:transparent;">
            <?php do_action( 'woocommerce_credit_card_form_start', $this->id ); ?>

            <?php if ( Helper::is_3d_secure_enabled() || is_add_payment_method_page() ) : ?>
                <label for="dokan-stripe-card-element">
                    <?php esc_html_e( 'Credit or debit card', 'dokan' ); ?>
                </label>

                <div id="dokan-stripe-card-element" class="dokan-stripe-elements-field">
                    <!-- a Stripe Element will be inserted here. -->
                </div>

                <div class="dokan-stripe-intent"></div>
                <div class="stripe-source-errors" role="alert">
                    <!-- Used to display form errors -->
                </div>

            <?php else : ?>
                <div
                    class="stripe_new_card"
                    data-amount="<?php echo esc_attr( Helper::get_stripe_amount( WC()->cart->total ) ); ?>"
                    data-currency="<?php echo esc_attr( strtolower( get_woocommerce_currency() ) ); ?>"
                >
                    <?php
                        if ( ! $this->checkout_modal ) {
                            $this->form();
                        }
                    ?>
                </div>
            <?php endif; ?>

            <?php do_action( 'woocommerce_credit_card_form_end', $this->id ); ?>
            <div class="clear"></div>
        </fieldset>
        <?php
    }

    /**
     * Process payment for the order
     *
     * @since 3.0.3
     *
     * @param int $oder_id
     *
     * @return array
     */
    public function process_payment( $order_id ) {
        $order          = wc_get_order( $order_id );
        $payment_method = Helper::payment_method(); // 3ds or non_3ds
        $response       = [];

        try {
            $response = DokanStripe::process( $order )->with( $payment_method )->pay();
        } catch( DokanException $e ) {
            throw new DokanException(
                'dokan_process_payment_failed',
                $e->get_message()
            );
        }

        return $response;
    }

    /**
     * Attached to `woocommerce_payment_successful_result` with a late priority,
     * this method will combine the "naturally" generated redirect URL from
     * WooCommerce and a payment/setup intent secret into a hash, which contains both
     * the secret, and a proper URL, which will confirm whether the intent succeeded.
     *
     * @since 3.0.3
     *
     * @param array $result   The result from `process_payment`.
     * @param int   $order_id The ID of the order which is being paid for.
     *
     * @return array
     */
    public function modify_successful_payment_result( $result, $order_id ) {
        // Only redirects with intents need to be modified.
        if ( ! isset( $result['payment_intent_secret'] ) ) {
            return $result;
        }

        // Put the final thank you page redirect into the verification URL.
        $verification_url = add_query_arg(
            [
                'order'       => $order_id,
                'nonce'       => wp_create_nonce( 'dokan_stripe_confirm_pi' ),
                'redirect_to' => rawurlencode( $result['redirect'] ),
            ],
            WC_AJAX::get_endpoint( 'dokan_stripe_verify_intent' )
        );

        return [
            'result'   => 'success',
            'redirect' => sprintf( '#confirm-pi-%s:%s', $result['payment_intent_secret'], rawurlencode( $verification_url ) )
        ];
    }
}
