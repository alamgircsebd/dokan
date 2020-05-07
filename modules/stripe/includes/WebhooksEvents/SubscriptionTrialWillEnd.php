<?php

namespace WeDevs\DokanPro\Modules\Stripe\WebhooksEvents;

use WeDevs\DokanPro\Modules\Stripe\Helper;
use WeDevs\DokanPro\Modules\Stripe\Interfaces\WebhookHandleable;

defined( 'ABSPATH' ) || exit;

class SubscriptionTrialWillEnd implements WebhookHandleable {

    /**
     * Event holder
     *
     * @var null
     */
    private $event = null;

    /**
     * Constructor method
     *
     * @since DOKAN_PRO_SINCE
     *
     * @param \Stripe\Event $event
     *
     * @return void
     */
    public function __construct( $event ) {
        $this->event = $event;
    }

    /**
     * Hanle the event
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    public function handle() {
        $subscription = $this->event->data->object;
        do_action( 'dokan_vendor_subscription_will_end', Helper::get_vendor_id_by_subscription( $subscription->id ), $subscription );
    }
}