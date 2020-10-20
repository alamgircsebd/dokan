<?php

namespace WeDevs\DokanPro\Modules\Stripe;

use WeDevs\Dokan\Traits\ChainableContainer;
use WeDevs\DokanPro\Modules\Stripe\Gateways\RegisterGateways;
use WeDevs\DokanPro\Modules\Stripe\Subscriptions\ProductSubscription;
use WeDevs\DokanPro\Modules\Stripe\WithdrawMethods\RegisterWithdrawMethods;

class Module {

    use ChainableContainer;

    /**
     * Constructor method
     *
     * @since 3.0.3
     *
     * @return void
     */
    public function __construct() {
        $this->define_constants();
        $this->set_controllers();
    }

    /**
     * Define module constants
     *
     * @since 3.0.3
     *
     * @return void
     */
    private function define_constants() {
        define( 'DOKAN_STRIPE_FILE', __FILE__ );
        define( 'DOKAN_STRIPE_PATH', dirname( DOKAN_STRIPE_FILE ) );
        define( 'DOKAN_STRIPE_ASSETS', plugin_dir_url( DOKAN_STRIPE_FILE ) . 'assets/' );
        define( 'DOKAN_STRIPE_TEMPLATE_PATH', dirname( DOKAN_STRIPE_FILE ) . '/templates/' );
    }

    /**
     * Set controllers
     *
     * @since 3.0.3
     *
     * @return void
     */
    private function set_controllers() {
        $this->container['webhook']                   = new WebhookHandler();
        $this->container['register_gateways']         = new RegisterGateways();
        $this->container['register_withdraw_methods'] = new RegisterWithdrawMethods();
        $this->container['intent_controller']         = new IntentController();
        $this->container['product_subscription']      = new ProductSubscription();
        $this->container['payment_tokens']            = new PaymentTokens();
        $this->container['refund']                    = new Refund();
        $this->container['validation']                = new Validation();
        $this->container['store_progress']            = new StoreProgress();
        $this->container['vendor_profile']            = new VendorProfile();
    }
}
