<?php

namespace WeDevs\DokanPro\Modules\Stripe\WebhooksEvents;

use WeDevs\DokanPro\Modules\Stripe\Helper;
use WeDevs\DokanPro\Modules\Stripe\Interfaces\WebhookHandleable;

defined( 'ABSPATH' ) || exit;

class SubscriptionUpdated implements WebhookHandleable {

    /**
     * Event holder
     *
     * @var null
     */
    private $event = null;

    /**
     * Constructor method
     *
     * @since 3.0.3
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
     * @since 3.0.3
     *
     * @return void
     */
    public function handle() {
        $subscription = $this->event->data->object;

        if ( 'active' !== $subscription->status ) {
            return;
        }

        $vendor_id    = Helper::get_vendor_id_by_subscription( $subscription->id );
        $period_start = date( 'Y-m-d H:i:s', $subscription->current_period_start );
        $period_end   = date( 'Y-m-d H:i:s', $subscription->current_period_end );
        $order_id     = get_user_meta( $vendor_id, 'product_order_id', true );

        update_user_meta( $vendor_id, 'product_pack_startdate', $period_start );
        update_user_meta( $vendor_id, 'product_pack_enddate', $period_end );
        update_user_meta( $vendor_id, 'can_post_product', '1' );
        update_user_meta( $vendor_id, 'has_pending_subscription', false );

        if ( ! empty( $subscription->cancel_at ) ) {
            update_user_meta( $vendor_id, 'product_pack_enddate', date( 'Y-m-d H:i:s', $subscription->cancel_at ) );
            update_user_meta( $vendor_id, 'dokan_has_active_cancelled_subscrption', true );
        } else {
            update_user_meta( $vendor_id, 'dokan_has_active_cancelled_subscrption', false );
        }
    }
}