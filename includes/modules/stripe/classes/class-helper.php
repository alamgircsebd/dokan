<?php
namespace DokanPro\Modules\Stripe;

/**
 * Stripe Helper class
 *
 * @since 2.9.13
 */
class Helper {
    /**
     * Get the stripe SDK
     *
     * @since 2.9.13
     *
     * @return string | false on failure
     */
    public static function get_stripe() {
        $file = DOKAN_STRIPE_LIBS . 'stripe-init.php';

        return ! file_exists( $file ) ? false : require_once $file;
    }

    /**
     * Check wheter the 3d secure is enabled or not
     *
     * @since 2.9.13
     *
     * @return boolean
     */
    public static function is_3d_secure_enabled() {
        $settings   = get_option( 'woocommerce_dokan-stripe-connect_settings' );
        $is_enabled = true;

        if ( empty( $settings['enable_3d_secure'] ) || 'yes' !== $settings['enable_3d_secure'] ) {
            $is_enabled = false;
        }

        return apply_filters( 'dokan_is_3d_secure_enabled', $is_enabled );
    }

    /**
     * Check wheter the gateway in test mode or not
     *
     * @since 2.9.13
     *
     * @return boolean
     */
    public static function is_test_mode() {
        $settings = get_option( 'woocommerce_dokan-stripe-connect_settings' );

        if ( empty( $settings['testmode'] ) || 'yes' !== $settings['testmode'] ) {
            return false;
        }

        return 'yes' === $settings['testmode'];
    }

    /**
     * Check wheter subscription module is enabled or not
     *
     * @since 2.9.13
     *
     * @return boolean
     */
    public static function has_subscription_module() {
        return class_exists( 'Dokan_Product_Subscription' );
    }

    /**
     * Set stripe app info
     *
     * @since 2.9.13
     *
     * @return void
     */
    public static function set_app_info() {
        \Stripe\Stripe::setAppInfo(
            'Dokan Stripe-Connect',
            DOKAN_PRO_PLUGIN_VERSION,
            'https://wedevs.com/dokan/modules/stripe-connect/',
            'pp_partner_Ee9F0QbhSGowvH'
        );
    }

    /**
     * Set stripe API version
     *
     * @since 2.9.13
     *
     * @return void
     */
    public static function set_api_version() {
        \Stripe\Stripe::setApiVersion( '2019-05-16' );
    }

    /**
     * Is stripe active
     *
     * @since  DOKAN_PRO_SINCE
     *
     * @return boolean
     */
    public function is_active() {
        $settings = get_option( 'woocommerce_dokan-stripe-connect_settings' );

        if ( empty( $settings['enabled'] ) || 'yes' !== $settings['enabled'] ) {
            return false;
        }

        return true;
    }
}
