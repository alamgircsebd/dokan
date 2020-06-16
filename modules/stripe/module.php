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
        $this->set_controllers();
    }

    /**
     * Set controllers
     *
     * @since 3.0.3
     *
     * @return void
     */
    private function set_controllers() {
        $this->container['constants']                 = new Constants();
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