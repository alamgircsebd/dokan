<?php
namespace DokanPro\Modules\Stripe;

/**
 * Stripe Helper class
 *
 * @since DOKAN_PRO_SINCE
 */
class Helper {
    /**
     * Get the stripe SDK
     *
     * @since DOKAN_PRO_SINCE
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
     * @since DOKAN_PRO_SINCE
     *
     * @return boolean
     */
    public static function is_3d_secure_enabled() {
        $settings = get_option( 'woocommerce_dokan-stripe-connect_settings' );

        if ( empty( $settings['enable_3d_secure'] ) || 'yes' !== $settings['enable_3d_secure'] ) {
            return false;
        }

        return true;
    }

    /**
     * Check wheter the gateway in test mode or not
     *
     * @since DOKAN_PRO_SINCE
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
     * @since DOKAN_PRO_SINCE
     *
     * @return boolean
     */
    public static function has_subscription_module() {
        return class_exists( 'Dokan_Product_Subscription' );
    }

    /**
     * Set stripe app info
     *
     * @since DOKAN_PRO_SINCE
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
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    public static function set_api_version() {
        \Stripe\Stripe::setApiVersion( '2019-05-16' );
    }
}