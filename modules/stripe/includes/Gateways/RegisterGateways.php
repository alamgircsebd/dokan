<?php

namespace WeDevs\DokanPro\Modules\Stripe\Gateways;

defined( 'ABSPATH' ) || exit;

class RegisterGateways {

    /**
     * Constructor method
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    public function __construct() {
        $this->hooks();
    }

    /**
     * Init all the hooks
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    private function hooks() {
        add_filter( 'woocommerce_payment_gateways', [ $this, 'register_gateway' ] );
    }

    /**
     * Register payment gateway
     *
     * @since DOKAN_PRO_SINCE
     *
     * @param array $gateways
     *
     * @return array
     */
    public function register_gateway( $gateways ) {
        $gateways[] = '\WeDevs\DokanPro\Modules\Stripe\StripeConnect';

        return $gateways;
    }
}