<?php

namespace WeDevs\DokanPro\Modules\Stripe\WebhooksEvents;

use WeDevs\DokanPro\Modules\Stripe\Helper;
use WeDevs\DokanPro\Modules\Stripe\Interfaces\WebhookHandleable;

defined( 'ABSPATH' ) || exit;

class InvoicePaymentFailed implements WebhookHandleable {

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
        $invoice   = $this->event->data->object;
        $vendor_id = Helper::get_vendor_id_by_subscription( $invoice->subscription );

        // Terminate user to update product
        update_user_meta( $vendor_id, 'can_post_product', '0' );

        // Make sure this is final attempt
        if ( isset( $invoice->next_payment_attempt ) && $invoice->next_payment_attempt == null ) {
            delete_user_meta( $vendor_id, 'product_package_id' );
            delete_user_meta( $vendor_id, '_stripe_subscription_id' );
            delete_user_meta( $vendor_id, 'product_order_id' );
            delete_user_meta( $vendor_id, 'product_no_with_pack' );
            delete_user_meta( $vendor_id, 'product_pack_startdate' );
            delete_user_meta( $vendor_id, 'product_pack_enddate' );
            delete_user_meta( $vendor_id, 'can_post_product' );
            delete_user_meta( $vendor_id, '_customer_recurring_subscription' );
            delete_user_meta( $vendor_id, 'dokan_seller_percentage' );
        }
    }
}