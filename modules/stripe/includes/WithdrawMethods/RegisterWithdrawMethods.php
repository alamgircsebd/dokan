<?php

namespace WeDevs\DokanPro\Modules\Stripe\WithdrawMethods;

use Stripe\OAuth;
use Stripe\Error\OAuth\OAuthBase;
use WeDevs\DokanPro\Modules\Stripe\Helper;

defined( 'ABSPATH' ) || exit;

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
        add_filter( 'template_redirect', [ $this, 'connect_vendor_to_stripe' ] );
        add_filter( 'template_redirect', [ $this, 'delete_stripe_account' ] );
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

        if ( ! is_ssl() ) {
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
     * @param array $store_settings
     *
     * @return void
     */
    public function stripe_authorize_button( $store_settings ) {
        $vendor_id           = get_current_user_id();
        $key                 = get_user_meta( $vendor_id, '_stripe_connect_access_key', true );
        $connected_vendor_id = get_user_meta( $vendor_id, 'dokan_connected_vendor_id', true );
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

                    $url = OAuth::authorizeUrl( [
                        'scope' => 'read_write',
                    ] );

                    ?>
                    <br/>
                    <a class="dokan-stripe-connect-link" href="<?php echo $url; ?>" target="_TOP">
                        <img src="<?php echo esc_url( DOKAN_STRIPE_ASSETS . 'images/blue.png' ); ?>" width="190" height="33" data-hires="true">
                    </a>
                    <?php

                } else {
                    ?>
                    <div class="dokan-alert dokan-alert-success">
                        <?php _e( 'Your account is connected with Stripe', 'dokan' ); ?>
                        <a  class="dokan-btn dokan-btn-danger dokan-btn-theme" href="<?php echo wp_nonce_url( add_query_arg( array( 'action' => 'dokan-disconnect-stripe' ), dokan_get_navigation_url( 'settings/payment' ) ), 'dokan-disconnect-stripe' ); ?>"><?php _e( 'Disconnect', 'dokan' ); ?></a>
                    </div>
                    <?php
                }
            ?>
        </div>
        <?php
    }

    /**
     * Connect vendor to stripe
     *
     * @since 3.0.3
     *
     * @return void
     */
    public function connect_vendor_to_stripe() {
        if ( ! empty( $_GET['state'] ) && 'wepay' == $_GET['state'] ) {
            return;
        }

        if ( empty( $_GET['scope'] ) || empty( $_GET['code'] ) ) {
            return;
        }

        try {
            $resp = OAuth::token( [
                'code'       => $_GET['code'],
                'grant_type' => 'authorization_code',
            ] );
        } catch ( OAuthBase $e ) {
            wp_send_json( 'Something went wrong: ' . $e->getMessage() );
        }

        update_user_meta( get_current_user_id(), 'dokan_connected_vendor_id', $resp->stripe_user_id );
        update_user_meta( get_current_user_id(), '_stripe_connect_access_key', $resp->access_token );
        wp_redirect( dokan_get_navigation_url( 'settings/payment' ) );
        exit;
    }

    /**
     * Delete vendor stripe account
     *
     * @since 3.0.3
     *
     * @return void
     */
    public function delete_stripe_account() {
        $vendor_id = get_current_user_id();

        if ( ! $vendor_id || ! dokan_is_user_seller( $vendor_id ) ) {
            return;
        }

        if ( isset( $_GET['action'] ) && $_GET['action'] == 'dokan-disconnect-stripe' ) {
            if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'dokan-disconnect-stripe' ) ) {
                return;
            }

            delete_user_meta( $vendor_id, '_stripe_connect_access_key');
            delete_user_meta( $vendor_id, 'dokan_connected_vendor_id');
            wp_redirect( dokan_get_navigation_url( 'settings/payment' ) );
            exit;
        }
    }
}