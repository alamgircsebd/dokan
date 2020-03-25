<?php

namespace WeDevs\DokanPro\Modules\Stripe;

use DokanPro\Modules\Stripe\Helper;

/**
 * Dokan Stripe Main class
 *
 * @author weDevs<info@wedevs.com>
 */
class Module {

    /**
     * Constructor
     */
    public function __construct() {
        $this->load_constants();
        $this->load_files();

        /** All actions */
        add_action( 'init', array( $this, 'init' ) );
        add_action( 'template_redirect', array( $this, 'stripe_check_connect' ), 20 );

        add_filter( 'woocommerce_payment_gateways', array( $this, 'register_gateway' ) );

        add_filter( 'dokan_withdraw_methods', array( $this, 'register_dokan_withdraw_gateway' ) );
        add_filter( 'dokan_get_dashboard_nav', array( $this, 'remove_withdraw_page' ) );
        add_filter( 'dokan_query_var_filter', array( $this, 'remove_withdraw_query_var' ), 80 );

        // Handle recurring subscription cancel
        add_action( 'dps_cancel_recurring_subscription', array( $this, 'cancel_recurring_subscription' ), 10, 2 );

        add_action( 'edit_user_profile', array( $this, 'stripe_admin_menu') , 50 );
        add_action( 'show_user_profile', array( $this, 'stripe_admin_menu') , 50 );
        add_action( 'personal_options_update', array( $this, 'stripe_admin_functions') , 50 );
        add_action( 'edit_user_profile_update', array( $this, 'stripe_admin_functions') , 50 );
        add_action( 'template_redirect', array( $this, 'delete_stripe_account') , 50 );
        add_action( 'init', array( $this, 'handle_stripe_webhook') , 10 );

        add_action( 'dokan_store_profile_saved', array( $this, 'save_stripe_progress' ), 8, 2 );
        add_action( 'woocommerce_after_checkout_validation', array( $this, 'check_vendor_configure_stripe' ), 15, 2 );

        // approve refund request automatically such as stripe connect
        add_action( 'dokan_refund_request_created', [ $this, 'after_refund_request_created' ] );

        // set guest customer billing data to session
        add_filter( 'woocommerce_checkout_fields', [ $this, 'trigger_update_checkout_on_change' ] );
        add_action( 'woocommerce_checkout_update_order_review', [ $this, 'set_email_prop_to_session' ] );
    }

    /**
     * Load plugin constants
     *
     * @return void
     */
    private function load_constants() {
        $this->define( 'DOKAN_STRIPE_FILE', __FILE__ );
        $this->define( 'DOKAN_STRIPE_PATH', __DIR__ );
        $this->define( 'DOKAN_STRIPE_CLASSES', __DIR__ . '/classes/' );
        $this->define( 'DOKAN_STRIPE_LIBS', __DIR__ . '/libs/' );
        $this->define( 'DOKAN_STRIPE_ABSTRACT', __DIR__ . '/abstracts/' );
        $this->define( 'DOKAN_STRIPE_ASSETS', plugin_dir_url( __FILE__ ) . 'assets/' );
        $this->define( 'DOKAN_STRIPE_TEMPLATE_PATH', __DIR__ . '/templates/' );
    }

    /**
     * Define constants
     *
     * @param string $name
     * @param  string $path
     *
     * @return void
     */
    private function define( $name, $path ) {
        if ( ! defined( $name ) ) {
            define( $name, $path );
        }
    }

    /**
     * Load files
     *
     * @return void
     */
    private function load_files() {
        require_once DOKAN_STRIPE_ABSTRACT . 'abstract-class-dokan-stripe-gateway.php';
        require_once DOKAN_STRIPE_CLASSES . 'class-helper.php';
        require_once DOKAN_STRIPE_CLASSES . 'class-dokan-stripe-transaction.php';
        require_once DOKAN_STRIPE_CLASSES . 'class-dokan-stripe-connect-wrapper.php';
        require_once DOKAN_STRIPE_CLASSES . 'class-dokan-stripe-subscription.php';
    }

    /**
     * Filter gateways
     *
     * @param  array $gateways
     *
     * @return array
     */
    public function filter_gateways( $gateways ) {
        if ( !empty( WC()->cart->cart_contents ) ) {
            foreach ( WC()->cart->cart_contents as $key => $values ) {
                if ( dokan_get_prop( $values['data'], 'product_type', 'get_type') == 'product_pack' ) {
                    unset( $gateways['dokan-stripe-connect'] );
                    break;
                } else {
                    unset( $gateways['stripe'] );
                    break;
                }
            }
        }
        return $gateways;
    }

    /**
     * Validate checkout if vendor has configured stripe account
     *
     * @since 2.8.0
     *
     * @return void
     */
    public function check_vendor_configure_stripe( $data, $errors ) {
        $settings = get_option('woocommerce_dokan-stripe-connect_settings');

        // bailout if the gateway is not enabled
        if ( isset( $settings['enabled'] ) && $settings['enabled'] == 'yes' ) {
            if ( 'dokan-stripe-connect' == $data['payment_method'] ) {
                if ( isset( $settings['allow_non_connected_sellers'] ) && 'yes' === $settings['allow_non_connected_sellers'] ) {
                    return;
                }

                foreach ( WC()->cart->get_cart() as $item ) {
                    $product_id = $item['data']->get_id();
                    $available_vendors[get_post_field( 'post_author', $product_id )][] = $item['data'];
                }

                // if it's subscription product return early
                $subscription_product = wc_get_product( $product_id );

                if ( $subscription_product && 'product_pack' === $subscription_product->get_type() ) {
                    return;
                }

                $vendor_names = array();

                foreach ( array_keys( $available_vendors ) as $vendor_id ) {
                    $vendor = dokan()->vendor->get( $vendor_id );
                    $access_token = get_user_meta( $vendor_id, '_stripe_connect_access_key', true );

                    if ( empty( $access_token ) ) {
                        $vendor_products = array();

                        foreach ( $available_vendors[$vendor_id] as $product ) {
                            $vendor_products[] = sprintf( '<a href="%s">%s</a>', $product->get_permalink(), $product->get_name() );
                        }
                        $vendor_names[$vendor_id] = array(
                            'name' => sprintf( '<a href="%s">%s</a>', esc_url( $vendor->get_shop_url() ), $vendor->get_shop_name() ),
                            'products' => implode( ', ', $vendor_products )
                        );
                    }
                }

                foreach ( $vendor_names as $vendor_id => $data ) {
                    $errors->add( 'stipe-not-configured', sprintf(__('<strong>Error!</strong> You cannot complete your purchase until <strong>%s</strong> has enabled Stripe as a payment gateway. Please remove %s to continue.', 'dokan'), $data['name'], $data['products'] ) );
                }
            }
        }

    }

    /**
     * Init localisations and files
     */
    public function init() {
        if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
            return;
        }

        include_once dirname( __FILE__ ) . '/classes/class-dokan-stripe-connect.php';
        include_once dirname( __FILE__ ) . '/classes/class-dokan-stripe-connect-saved-cards.php';
    }

    /**
     * Register the gateway for use
     */
    public function register_gateway( $methods ) {
        $methods[] = 'Dokan_Stripe_Connect';

        return $methods;
    }

    /**
     * Check to connect with stripe
     *
     * @return void
     */
    function stripe_check_connect() {
        if ( !empty( $_GET['state'] ) && 'wepay' == $_GET['state'] ) {
            return;
        }

        if ( empty( $_GET['scope'] ) || empty( $_GET['code'] ) ) {
            return;
        }

        $settings   = get_option('woocommerce_dokan-stripe-connect_settings');
        $client_id  = $settings['testmode'] == 'yes' ? $settings['test_client_id'] : $settings['client_id'];
        $secret_key = $settings['testmode'] == 'yes' ? $settings['test_secret_key'] : $settings['secret_key'];

        Helper::set_app_info();
        Helper::set_api_version();
        \Stripe\Stripe::setApiKey( $secret_key );
        \Stripe\Stripe::setClientId( $client_id );

        if ( Helper::is_test_mode() ) {
            \Stripe\Stripe::setVerifySslCerts( false );
        }

        try {
            $resp = \Stripe\OAuth::token( [
                'grant_type' => 'authorization_code',
                'code' => $_GET['code'],
            ] );
        } catch ( \Stripe\Error\OAuth\OAuthBase $e ) {
            wp_send_json( 'Something went wrong: ' . $e->getMessage() );
        }

        update_user_meta( get_current_user_id(), 'dokan_connected_vendor_id', $resp->stripe_user_id );
        update_user_meta( get_current_user_id(), '_stripe_connect_access_key', $resp->access_token );

        wp_redirect( dokan_get_navigation_url( 'settings/payment' ) );
        exit;
    }

    /**
     * Register the stripe gateway for withdraw
     *
     * @param  array  $methods
     *
     * @return array
     */
    function register_dokan_withdraw_gateway( $methods ) {
        $settings = get_option('woocommerce_dokan-stripe-connect_settings');

        if ( isset( $settings['enabled'] ) && $settings['enabled'] != 'yes' ) {
            return $methods;
        }

        $methods['dokan-stripe-connect'] = array(
            'title'    => __( 'Stripe', 'dokan' ),
            'callback' => array( $this, 'stripe_authorize_button' )
        );

        return $methods;
    }

    /**
     * This enables dokan vendors to connect their stripe account to the site stripe gateway account
     *
     * @param array $store_settings
     */
    function stripe_authorize_button( $store_settings ) {
        $store_user = wp_get_current_user();
        $settings   = get_option('woocommerce_dokan-stripe-connect_settings');

        if ( ! $settings ) {
            _e( 'Stripe gateway is not configured. Please contact admin.', 'dokan' );
            return;
        }

        if ( ! isset( $settings['enabled'] ) || $settings['enabled'] == 'no' ) {
            return;
        }

        $client_id           = $settings['testmode'] == 'yes' ? $settings['test_client_id'] : $settings['client_id'];
        $secret_key          = $settings['testmode'] == 'yes' ? $settings['test_secret_key'] : $settings['secret_key'];
        $key                 = get_user_meta( $store_user->ID, '_stripe_connect_access_key', true );
        $connected_vendor_id = get_user_meta( $store_user->ID, 'dokan_connected_vendor_id', true );
        ?>

        <style type="text/css" media="screen">
            .dokan-stripe-connect-container {
                border: 1px solid #eee;
                padding: 15px;
            }

            .dokan-stripe-connect-container .dokan-alert {
                margin-bottom: 0;
            }
        </style>

        <div class="dokan-stripe-connect-container">
            <input type="hidden" name="settings[stripe]" value="<?php echo empty( $key ) ? 0 : 1; ?>">
            <?php
                if ( empty( $key ) && empty( $connected_vendor_id ) ) {

                    echo '<div class="dokan-alert dokan-alert-danger">';
                        _e( 'Your account is not connected to Stripe. Connect your Stripe account to receive payouts.', 'dokan' );
                    echo '</div>';

                    Helper::set_app_info();
                    Helper::set_api_version();
                    \Stripe\Stripe::setApiKey( $secret_key );
                    \Stripe\Stripe::setClientId( $client_id );

                    if ( Helper::is_test_mode() ) {
                        \Stripe\Stripe::setVerifySslCerts( false );
                    }

                    $url = \Stripe\OAuth::authorizeUrl( [
                        'scope' => 'read_write',
                    ] );

                    ?>
                    <br/>
                    <a class="clear" href="<?php echo $url; ?>" target="_TOP">
                        <img src="<?php echo plugins_url( '/assets/images/blue.png', DOKAN_STRIPE_FILE ); ?>" width="190" height="33" data-hires="true">
                    </a>
                    <?php

                } else {
                    ?>
                    <div class="dokan-alert dokan-alert-success">
                        <?php _e( 'Your account is connected with Stripe.', 'dokan' ); ?>
                        <a  class="dokan-btn dokan-btn-danger dokan-btn-theme" href="<?php echo wp_nonce_url( add_query_arg( array( 'action' => 'dokan-disconnect-stripe' ), dokan_get_navigation_url( 'settings/payment' ) ), 'dokan-disconnect-stripe' ); ?>"><?php _e( 'Disconnect', 'dokan' ); ?></a>
                    </div>
                    <?php
                }
            ?>
        </div>
        <?php
    }

    /**
     * Remove withdraw page if stripe is enabled
     *
     * @param  array  $urls
     *
     * @return array
     */
    public function remove_withdraw_page( $urls ) {
        $withdraw_settings = get_option( 'dokan_withdraw' );
        $hide_withdraw_option = isset( $withdraw_settings['hide_withdraw_option'] ) ? $withdraw_settings['hide_withdraw_option'] : 'off';

        if ( $hide_withdraw_option == 'on' ) {
            $settings = get_option( 'woocommerce_dokan-stripe-connect_settings' );
            // bailout if the gateway is not enabled
            if ( isset( $settings['enabled'] ) && $settings['enabled'] !== 'yes' ) {
                return $urls;
            }

            if ( array_key_exists( 'withdraw', $urls ) ) {
                unset( $urls['withdraw'] );
            }

            return $urls;
        }

        return $urls;
    }

    /**
     * Remove withdraw query var disable access to withdraw template
     *
     * @since 1.3
     *
     * @param array $query_vars
     *
     * @return array $query_vars
     */
    public function remove_withdraw_query_var( $query_vars ) {
        $withdraw_settings = get_option( 'dokan_withdraw' );
        $hide_withdraw_option = isset( $withdraw_settings['hide_withdraw_option'] ) ? $withdraw_settings['hide_withdraw_option'] : 'off';

        if ( $hide_withdraw_option == 'on' ) {
            $key = array_search( 'withdraw', $query_vars );

            if ( $key != FALSE ) {
                unset( $query_vars[$key] );
                $query_vars = array_values( $query_vars );
            }

            return $query_vars;
        }

        return $query_vars;
    }

    public function delete_stripe_account() {
        $user_id = get_current_user_id();

        if ( !is_user_logged_in() ) {
            return;
        }

        if ( !dokan_is_user_seller( $user_id ) ) {
            return;
        }

        if ( isset( $_GET['action'] ) && $_GET['action'] == 'dokan-disconnect-stripe' ) {

            if ( !wp_verify_nonce( $_GET['_wpnonce'], 'dokan-disconnect-stripe' ) ) {
                return;
            }

            delete_user_meta( $user_id, '_stripe_connect_access_key');
            delete_user_meta( $user_id, 'dokan_connected_vendor_id');
            wp_redirect( dokan_get_navigation_url( 'settings/payment' ) );
            exit;
        }
    }

    /**
    * Handle webhook for recurring
    *
    * @since 1.3.3.
    *
    * @return void
    **/
    public function handle_stripe_webhook() {

        if ( isset( $_GET['webhook'] ) && $_GET['webhook'] == 'dokan' ) {
            global $wpdb;

            $stripe_options = get_option('woocommerce_dokan-stripe-connect_settings');
            $secret_key     = $stripe_options['testmode'] == 'yes' ? $stripe_options['test_secret_key'] : $stripe_options['secret_key'];

            Helper::set_app_info();
            Helper::set_api_version();
            \Stripe\Stripe::setApiKey( $secret_key );

            if ( Helper::is_test_mode() ) {
                \Stripe\Stripe::setVerifySslCerts( false );
            }

            // retrieve the request's body and parse it as JSON
            $body = @file_get_contents( 'php://input' );

            // grab the event information
            $event_json = json_decode( $body );

            // this will be used to retrieve the event from Stripe
            $event_id = $event_json->id;

            if( isset( $event_json->id ) ) {
                try {

                    // to verify this is a real event, we re-retrieve the event from Stripe
                    $event = \Stripe\Event::retrieve( $event_id );
                    $invoice = $event->data->object;

                    // successful payment, both one time and recurring payments
                    if ( 'invoice.payment_succeeded' == $event->type ) {
                        $user_id      = $wpdb->get_var( "SELECT `user_id` FROM $wpdb->usermeta WHERE `meta_key` = '_stripe_subscription_id' AND `meta_value`='$invoice->subscription'" );
                        $period_start = date( 'Y-m-d H:i:s', $invoice->period_start );
                        $period_end   = date( 'Y-m-d H:i:s', $invoice->period_end );
                        $order_id     = get_user_meta( $user_id, 'product_order_id', true );

                        if ( $invoice->paid ) {
                            update_user_meta( $user_id, 'product_pack_startdate', $period_start );
                            update_user_meta( $user_id, 'product_pack_enddate', $period_end );
                            update_user_meta( $user_id, 'can_post_product', '1' );
                            update_user_meta( $user_id, 'has_pending_subscription', false );

                            if ( !empty( $invoice->charge ) ) {
                                update_post_meta( $order_id, '_stripe_subscription_charge_id', $invoice->charge );
                            }
                        }
                    }

                    // failed payment
                    if ( 'invoice.payment_failed' == $event->type ) {
                        $user_id = $wpdb->get_var( "SELECT `user_id` FROM $wpdb->usermeta WHERE `meta_key` = '_stripe_subscription_id' AND `meta_value`='$invoice->subscription'" );

                        // Terminate user to update product
                        update_user_meta( $user_id, 'can_post_product', '0' );

                        // Make sure this is final attempt
                        if ( isset( $invoice->next_payment_attempt ) && $invoice->next_payment_attempt == null ) {
                            delete_user_meta( $user_id, 'product_package_id' );
                            delete_user_meta( $user_id, '_stripe_subscription_id' );
                            delete_user_meta( $user_id, 'product_order_id' );
                            delete_user_meta( $user_id, 'product_no_with_pack' );
                            delete_user_meta( $user_id, 'product_pack_startdate' );
                            delete_user_meta( $user_id, 'product_pack_enddate' );
                            delete_user_meta( $user_id, 'can_post_product' );
                            delete_user_meta( $user_id, '_customer_recurring_subscription' );
                            delete_user_meta( $user_id, 'dokan_seller_percentage' );
                        }
                    }

                    if ( 'charge.dispute.created' == $event->type ) {
                        $charge_id = $invoice->charge;
                        $charge  = \Stripe\Charge::retrieve( $charge_id );
                        $charge_invoice  = \Stripe\Invoice::retrieve( $charge->invoice );
                        $settings = get_option('woocommerce_dokan-stripe-connect_settings');

                        $user_id = $wpdb->get_var( "SELECT `user_id` FROM $wpdb->usermeta WHERE `meta_key` = '_stripe_subscription_id' AND `meta_value`='$charge_invoice->subscription'" );
                        $order_id = get_user_meta( $user_id, 'product_order_id', true );
                        $order = wc_get_order( $order_id );

                        $order->set_status( 'on-hold' );
                        $order->save();

                        update_user_meta( $user_id, 'can_post_product', '0' );

                        $order->add_order_note( sprintf( __( 'Order %s status is now on-hold due to dispute via %s on (Charge IDs: %s)', 'dokan' ), $order->get_order_number(), $settings['title'], $charge_id ) );
                    }

                    if ( 'charge.dispute.closed' == $event->type ) {

                        if ( 'won' == $invoice->status ) {
                            $charge_id = $invoice->charge;
                            $charge  = \Stripe\Charge::retrieve( $charge_id );
                            $charge_invoice  = \Stripe\Invoice::retrieve( $charge->invoice );
                            $settings = get_option('woocommerce_dokan-stripe-connect_settings');

                            $user_id = $wpdb->get_var( "SELECT `user_id` FROM $wpdb->usermeta WHERE `meta_key` = '_stripe_subscription_id' AND `meta_value`='$charge_invoice->subscription'" );
                            $order_id = get_user_meta( $user_id, 'product_order_id', true );
                            $order = wc_get_order( $order_id );

                            $order->set_status( 'completed' );
                            $order->save();

                            update_user_meta( $user_id, 'can_post_product', '1' );

                            $order->add_order_note( sprintf( __( 'Order %s status is now completed due to dispute resolved in your favour via %s on (Charge IDs: %s)', 'dokan' ), $order->get_order_number(), $settings['title'], $charge_id ) );
                        }
                    }

                    if ( 'customer.subscription.trial_will_end' == $event->type ) {
                        // it will trigger 3 days before an trail ends
                        do_action( 'dokan_vendor_subscription_will_end' );
                    }

                    // update pack end date
                    if ( 'customer.subscription.created' == $event->type ) {
                        $invoice = $event->data->object;
                        $user_id = $wpdb->get_var( "SELECT `user_id` FROM $wpdb->usermeta WHERE `meta_key` = '_stripe_subscription_id' AND `meta_value`='$invoice->id'" );

                        update_user_meta( $user_id, 'product_pack_enddate', date( 'Y-m-d H:i:s', $invoice->current_period_end ) );
                    }

                    // it does happen on subscription plan switching
                    if ( 'customer.subscription.updated' === $event->type ) {
                        if ( 'active' !== $invoice->status ) {
                            return;
                        }

                        $user_id      = $wpdb->get_var( "SELECT `user_id` FROM $wpdb->usermeta WHERE `meta_key` = '_stripe_subscription_id' AND `meta_value`='$invoice->id'" );
                        $period_start = date( 'Y-m-d H:i:s', $invoice->current_period_start );
                        $period_end   = date( 'Y-m-d H:i:s', $invoice->current_period_end );
                        $order_id     = get_user_meta( $user_id, 'product_order_id', true );

                        update_user_meta( $user_id, 'product_pack_startdate', $period_start );
                        update_user_meta( $user_id, 'product_pack_enddate', $period_end );
                        update_user_meta( $user_id, 'can_post_product', '1' );
                        update_user_meta( $user_id, 'has_pending_subscription', false );
                    }

                } catch ( \Exception $e ) {
                    // something failed, perhaps log a notice or email the site admin
                }
            }
        }
    }

    /**
    * Handle recurring subscription cancelation
    *
    * @since 1.3.3
    *
    * @return void
    **/
    public function cancel_recurring_subscription( $order_id, $user_id ) {
        if ( ! $order_id ) {
            return;
        }

        if ( $order_id != get_user_meta( $user_id, 'product_order_id', true ) ) {
            return;
        }

        $order = wc_get_order( $order_id );

        if ( 'dokan-stripe-connect' == $order->get_payment_method() ) {

            try {
                \Dokan_Stripe_Connect::cancel_recurring_subscription( $user_id );
            } catch ( \Exception $e ) {

            }

            delete_user_meta( $user_id, 'product_package_id' );
            delete_user_meta( $user_id, '_stripe_subscription_id' );
            delete_user_meta( $user_id, 'product_order_id' );
            delete_user_meta( $user_id, 'product_no_with_pack' );
            delete_user_meta( $user_id, 'product_pack_startdate' );
            delete_user_meta( $user_id, 'product_pack_enddate' );
            delete_user_meta( $user_id, 'can_post_product' );
            delete_user_meta( $user_id, '_customer_recurring_subscription' );
            delete_user_meta( $user_id, 'dokan_seller_percentage' );

        }
    }

    /**
     * Admin functions for controlling user Stripe Accounts
     *
     * @param array $store_settings
     */
    function stripe_admin_functions( $user_id ) {

        if ( !dokan_is_user_seller( $user_id ) || ! current_user_can( 'manage_options' )  ) {
            return $user_id;
        }

        $stripe_settings = get_option('woocommerce_dokan-stripe-connect_settings');

        if ( ! $stripe_settings ) {
            return $user_id;
        }

        if ( isset( $_POST['disconnect_user_stripe'] ) ) {
            delete_user_meta( $user_id, 'dokan_connected_vendor_id');
            delete_user_meta( $user_id, '_stripe_connect_access_key');
        }

        return $user_id;
    }

    /**
    * This is admin menu for controlling Seller Stripe status
    *
    * @param array $store_settings
    */
    function stripe_admin_menu( $user ) {

        if ( ! dokan_is_user_seller( $user->ID ) || ! current_user_can( 'manage_options' )  ) {
            return $user;
        }

        $stripe_key = get_user_meta( $user->ID, '_stripe_connect_access_key', true );
        $connected_vendor_id = get_user_meta( $user->ID, 'dokan_connected_vendor_id', true );
        ?>
        <h3><?php _e('Dokan Stripe Settings','dokan');?></h3>
        <?php
        if ( ! empty( $stripe_key ) || ! empty( $connected_vendor_id ) ) : ?>
            <?php submit_button( __( 'Disconnect User Stripe Account', 'dokan' ) ,'delete', 'disconnect_user_stripe'); ?>
        <?php else : ?>
            <h4><?php _e("User account not connected to Stripe",'dokan');?></h4>
        <?php
        endif;
    }

    /**
    * Save stripe progress settings data
    *
    * @since 2.8
    *
    * @return void
    **/
    public function save_stripe_progress( $store_id, $dokan_settings ) {
        if ( ! $store_id ) {
            return;
        }

        $dokan_settings = get_user_meta( $store_id, 'dokan_profile_settings', true );

        if ( isset( $_POST['settings']['stripe'] ) ) {
            $dokan_settings['payment']['stripe'] = $_POST['settings']['stripe'];
        }

        update_user_meta( $store_id, 'dokan_profile_settings', $dokan_settings );
    }

    /**
     * Process refund request
     *
     * @param  int $refund_id
     * @param  array $data
     *
     * @return void
     */
    public function after_refund_request_created( $refund ) {
        $order            = wc_get_order( $refund->get_order_id() );
        $seller_id        = $refund->get_seller_id();
        $vendor_token     = get_user_meta( $seller_id, '_stripe_connect_access_key', true );
        $vendor_charge_id = $order->get_meta( "_dokan_stripe_charge_id_{$seller_id}" );

        /**
         * If admin has earning from an order, only then refund application fee
         *
         * @since 3.0.0
         *
         * @see https://stripe.com/docs/api/refunds/create#create_refund-refund_application_fee
         *
         * @var string
         */
        $refund_application_fee = dokan()->commission->get_earning_by_order( $order, 'admin' ) ? true : false;

        // if vendor charge id is not found, meaning it's a not purcahsed with sitripe so return early
        if ( ! $vendor_charge_id ) {
            return true;
        }

        $stripe_options = get_option( 'woocommerce_dokan-stripe-connect_settings' );
        $secret_key     = $stripe_options['testmode'] == 'yes' ? $stripe_options['test_secret_key'] : $stripe_options['secret_key'];

        Helper::set_app_info();
        Helper::set_api_version();
        \Stripe\Stripe::setApiKey( $secret_key );

        if ( Helper::is_test_mode() ) {
            \Stripe\Stripe::setVerifySslCerts( false );
        }

        try {
            $stripe_refund = \Stripe\Refund::create( [
                'charge'                 => $vendor_charge_id,
                'amount'                 => Helper::get_stripe_amount( $refund->get_refund_amount() ),
                'reason'                 => __( 'requested_by_customer', 'dokan' ),
                'refund_application_fee' => $refund_application_fee
            ], $vendor_token );

            if ( ! $stripe_refund->id ) {
                dokan_log( sprintf( __( 'Stripe refund ID is not found for Dokan Refund ID %s', 'dokan' ), $refund->get_id() ), 'error' );
            }

            $order->add_order_note( sprintf( __( 'Refund Processed Via Stripe ( Refund ID: %s )', 'dokan' ), $stripe_refund->id ) );

            $refund = $refund->approve();

            if ( is_wp_error( $refund ) ) {
                dokan_log( $refund->get_error_message(), 'error' );
            }

        } catch( \Exception $e ) {
            dokan_log( $e->getMessage(), 'error' );
        }
    }

    /**
     * Trigger update checkout on field change
     *
     * @since 2.9.13
     *
     * @param array $fileds
     *
     * @return array
     */
    public function trigger_update_checkout_on_change( $fields ) {
        if ( is_user_logged_in() ) {
            return $fields;
        }

        $fields['billing']['billing_email']['class'][] = 'update_totals_on_change';

        return $fields;
    }

    /**
     * Set guest customer email to session
     *
     * @since 2.9.13
     *
     * @param string $post_data
     *
     * @return void
     */
    public function set_email_prop_to_session( $post_data ) {
        if ( is_user_logged_in() ) {
            return;
        }

        parse_str( $post_data, $data );
        $billing_email = ! empty( $data['billing_email'] ) ? wc_clean( $data['billing_email'] ) : 'guest@customer.com';

        WC()->session->__unset( 'billing_email' );
        WC()->session->set( 'billing_email', $billing_email );
    }
}
