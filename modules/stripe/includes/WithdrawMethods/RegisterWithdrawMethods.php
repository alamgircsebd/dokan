<?php

namespace WeDevs\DokanPro\Modules\Stripe\WithdrawMethods;

use Exception;
use WeDevs\DokanPro\Modules\Stripe\Auth;
use WeDevs\DokanPro\Modules\Stripe\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * @todo These methods should be refactored to put in different related classes
 */
class RegisterWithdrawMethods {

    /**
     * Constructor method
     *
     * @since 3.0.3
     *
     * @return void
     */
    public function __construct() {
        add_action( 'admin_notices', [ $this, 'admin_notices' ] );

        if ( ! Helper::is_ready() ) {
            return;
        }

        Helper::bootstrap_stripe();
        $this->hooks();
    }

    /**
     * Init all the hooks
     *
     * @since 3.0.3
     *
     * @return void
     */
    private function hooks() {
        add_filter( 'dokan_withdraw_methods', [ $this, 'register_methods' ] );
        add_filter( 'dokan_get_processing_fee', [ $this, 'get_order_processing_fee' ], 10, 2 );
        add_filter( 'dokan_get_processing_gateway_fee', [ $this, 'get_processing_gateway_fee' ], 10, 3 );
        add_action( 'template_redirect', [ $this, 'authorize_vendor' ] );
        add_action( 'template_redirect', [ $this, 'deauthorize_vendor' ] );
    }

    /**
     * Show admin notices
     *
     * @since 3.0.4
     *
     * @return 1.0.0
     */
    public function admin_notices() {
        if ( ! Helper::is_enabled() || ! Helper::get_secret_key() || ! Helper::get_client_id() ) {
            $notice = sprintf(
                    __( 'Please insert Live %s credential to use Live Mode', 'dokan' ),
                    '<strong>Stripe</strong>'
                );
            printf( '<div class="error"><p>' . $notice . '</p></div>' );
        }

        if ( ! is_ssl() && ! Helper::is_test_mode() ) {
           $notice = sprintf(
                    __( '%s requires %s', 'dokan' ),
                    '<strong>Dokan Stripe Connect</strong>',
                    '<strong>SSL</strong>'
                );
            printf( '<div class="error"><p>' . $notice . '</p></div>' );
        }
    }

    /**
     * Register methods
     *
     * @since 3.0.3
     *
     * @param array $methods
     *
     * @return array
     */
    public function register_methods( $methods ) {
        $methods['dokan-stripe-connect'] = [
            'title'    => __( 'Stripe', 'dokan' ),
            'callback' => [ $this, 'stripe_authorize_button' ]
        ];

        return $methods;
    }

    /**
     * This enables dokan vendors to connect their stripe account to the site stripe gateway account
     *
     * @since 3.0.3
     *
     * @return void
     */
    public function stripe_authorize_button() {
        $vendor_id           = get_current_user_id();
        $key                 = get_user_meta( $vendor_id, '_stripe_connect_access_key', true );
        $connected_vendor_id = get_user_meta( $vendor_id, 'dokan_connected_vendor_id', true );
        $auth_url            = '#';
        $disconnect_url      = '#';

        if ( empty( $key ) && empty( $connected_vendor_id ) ) {
            $auth_url = Auth::get_vendor_authorize_url();
        } else {
            $disconnect_url = Auth::get_vendor_deauthorize_url();
        }

        Helper::get_template( 'vendor-settings-payment', [
            'vendor_id'           => $vendor_id,
            'key'                 => $key,
            'connected_vendor_id' => $connected_vendor_id,
            'auth_url'            => $auth_url,
            'disconnect_url'      => $disconnect_url,
        ] );
    }

    /**
     * Authorize vendor
     *
     * @since 3.0.3
     *
     * @return void
     */
    public function authorize_vendor() {
        if ( ! isset( $_GET['state'] ) || false === strpos( $_GET['state'], 'dokan-stripe-connect' ) ) {
            return;
        }

        if ( empty( $_GET['code'] ) ) {
            return;
        }

        $get_data = wp_unslash( $_GET );

        $nonce = str_replace( 'dokan-stripe-connect:', '', $get_data['state'] );

        if ( ! wp_verify_nonce( $nonce, 'dokan-stripe-vendor-authorize' ) ) {
            return;
        }

        try {
            $response = Auth::get_vendor_token( $get_data['code'] );
        } catch ( Exception $e ) {
            dokan_log(
                sprintf(
                    "[Stripe Connect] Unable to authorize vendor. \nException Message: %s\nError Trace:\n%s",
                    $e->getMessage(),
                    $e->getTraceAsString()
                ),
                'error'
            );

            wp_die(
                __( 'Unable to authorize your store. Please contact the site admin.', 'dokan' ),
                __( 'Authorization Error', 'dokan' )
            );
        }

        update_user_meta( get_current_user_id(), 'dokan_connected_vendor_id', $response->stripe_user_id );
        update_user_meta( get_current_user_id(), '_stripe_connect_access_key', $response->access_token );
        wp_redirect( dokan_get_navigation_url( 'settings/payment' ) );
        exit;
    }

    /**
     * Deauthorize vendor
     *
     * @since 3.0.3
     *
     * @return void
     */
    public function deauthorize_vendor() {
        if ( ! isset( $_GET['action'] ) || 'dokan-disconnect-stripe' !== $_GET['action'] ) {
            return;
        }

        if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'dokan-stripe-vendor-deauthorize' ) ) {
            return;
        }

        $vendor_id = get_current_user_id();

        if ( ! $vendor_id || ! dokan_is_user_seller( $vendor_id ) ) {
            return;
        }

        try {
            Auth::deauthorize( [
                'stripe_user_id' => get_user_meta( $vendor_id, 'dokan_connected_vendor_id', true ),
            ] );
        } catch( Exception $e ) {
            dokan_log(
                sprintf(
                    "[Stripe Connect] Unable to deauthorize vendor. \nException Message: %s\nError Trace:\n%s",
                    $e->getMessage(),
                    $e->getTraceAsString()
                ),
                'error'
            );
        }

        delete_user_meta( $vendor_id, '_stripe_connect_access_key' );
        delete_user_meta( $vendor_id, 'dokan_connected_vendor_id' );

        wp_redirect( dokan_get_navigation_url( 'settings/payment' ) );
        exit;
    }

    /**
     * Order processing fee for Stripe
     *
     * @since DOKAN_PRO_SINCE
     *
     * @param float     $processing_fee
     * @param \WC_Order $order
     *
     * @return float
     */
    public function get_order_processing_fee( $processing_fee, $order ) {
        if ( 'dokan-stripe-connect' === $order->get_payment_method() ) {
            $stripe_processing_fee = $order->get_meta( 'dokan_gateway_fee' );

            // In old module, we were saving fee as `dokan_gateway_stripe_fee` meta
            if ( ! $stripe_processing_fee ) {
                $stripe_processing_fee = $order->get_meta( 'dokan_gateway_stripe_fee' );
            }

            if ( $stripe_processing_fee ) {
                $processing_fee = $stripe_processing_fee;
            }
        }

        return $processing_fee;
    }

    public function get_processing_gateway_fee( $gateway_fee, $suborder, $order ) {
        if ( 'dokan-stripe-connect' === $order->get_payment_method() ) {
            $order_processing_fee = dokan()->commission->get_processing_fee( $order );
            $gateway_fee          = Helper::calculate_processing_fee_for_suborder( $order_processing_fee, $suborder, $order );
        }

        return $gateway_fee;
    }
}
