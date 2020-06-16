<?php

namespace WeDevs\DokanPro\Modules\Stripe\WebhooksEvents;

use Stripe\Subscription;
use WeDevs\DokanPro\Modules\Stripe\Helper;
use WeDevs\DokanPro\Modules\Stripe\Interfaces\WebhookHandleable;

defined( 'ABSPATH' ) || exit;

/**
 * It does happen on subscription plan switching
 *
 * @since 3.0.3
 */
class InvoicePaymentSucceeded implements WebhookHandleable {

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
        $invoice      = $this->event->data->object;
        $vendor_id    = Helper::get_vendor_id_by_subscription( $invoice->subscription );
        $subscription = Subscription::retrieve( $invoice->subscription );
        $period_start = date( 'Y-m-d H:i:s', $subscription->current_period_start );
        $period_end   = date( 'Y-m-d H:i:s', $subscription->current_period_end );
        $order_id     = get_user_meta( $vendor_id, 'product_order_id', true );

        if ( $invoice->paid ) {
            update_user_meta( $vendor_id, 'product_pack_startdate', $period_start );
            update_user_meta( $vendor_id, 'product_pack_enddate', $period_end );
            update_user_meta( $vendor_id, 'can_post_product', '1' );
            update_user_meta( $vendor_id, 'has_pending_subscription', false );
            update_user_meta( $vendor_id, 'dokan_has_active_cancelled_subscrption', false );

            if ( ! empty( $invoice->charge ) ) {
                update_post_meta( $order_id, '_stripe_subscription_charge_id', $invoice->charge );
            }
        }
    }
}