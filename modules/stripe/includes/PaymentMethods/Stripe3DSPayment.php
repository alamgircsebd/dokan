<?php

namespace WeDevs\DokanPro\Modules\Stripe\PaymentMethods;

use Exception;
use Stripe\PaymentIntent;
use WeDevs\DokanPro\Modules\Stripe\Helper;
use WeDevs\Dokan\Exceptions\DokanException;
use WeDevs\DokanPro\Modules\Stripe\StripeConnect;
use WeDevs\DokanPro\Modules\Stripe\Interfaces\Payable;

class Stripe3DSPayment extends StripeConnect implements Payable {

    /**
     * Hold the order order object
     *
     * @var \WC_Order
     */
    protected $order = null;

    /**
     * Constructor method
     *
     * @since 3.0.3
     *
     * @param \WC_Order
     *
     * @return void
     */
    public function __construct( $order ) {
        $this->order = $order;
        parent::__construct();
    }

    /**
     * Pay for the order
     *
     * @since 3.0.3
     *
     * @return array
     */
    public function pay() {
        $order              = $this->order;
        $stripe_customer_id = null;
        $force_save_source  = false;

        // If it's a recurring subscription order.
        if ( Helper::is_subscription_order( $order ) ) {
            $order_items        = $order->get_items();
            $product_pack_item  = reset( $order_items );
            $product_pack       = wc_get_product( $product_pack_item->get_product_id() );
            $dokan_subscription = dokan()->subscription->get( $product_pack->get_id() );

            if ( $dokan_subscription->is_recurring() ) {
                update_user_meta( get_current_user_id(), 'product_order_id', $order->get_id() );
                $order->add_order_note( sprintf( __( 'Order payment is completed via stripe with 3d secure', 'dokan' ) ) );
                $order->payment_complete();

                return [
                    'result'   => 'success',
                    'redirect' => $this->get_return_url( $order )
                ];
            }
        }

        $this->validate_minimum_order_amount( $order );

        // Check whether there is an existing intent.
        $intent = $this->get_intent_from_order( $order );

        if ( ! empty( $intent->customer ) ) {
            $stripe_customer_id = $intent->customer;
        }

        $prepared_source = $this->prepare_source( get_current_user_id(), $force_save_source, $stripe_customer_id );

        $this->validate_source( $prepared_source );

        if ( $force_save_source ) {
            $this->save_source_to_order( $order, $prepared_source );
        }

        if ( $intent ) {
            $intent = $this->update_existing_intent( $intent, $order, $prepared_source );
        } else {
            $intent = $this->create_intent( $order, $prepared_source );
        }

        return $this->process_intent_status( $intent, $prepared_source, $order );
    }

    /**
     * Process intent status
     *
     * @since 3.0.3
     *
     * @param \PaymentIntent $intent
     * @param string $prepared_source
     * @param \WC_Order $order
     *
     * @return array
     */
    public function process_intent_status( $intent, $prepared_source, $order ) {
        if ( 'requires_confirmation' === $intent->status ) {
            $intent = $this->confirm_intent( $intent, $prepared_source );
        }

        if ( 'requires_action' === $intent->status ) {
            /**
             * This URL contains only a hash, which will be sent to `checkout.js` where it will be set like this:
             * `window.location = result.redirect`
             * Once this redirect is sent to JS, the `onHashChange` function will execute `handleCardPayment`.
             */
            return [
                'result'                => 'success',
                'redirect'              => $this->get_return_url( $order ),
                'payment_intent_secret' => $intent->client_secret,
            ];
        }

        if ( 'succeeded' === $intent->status ) {
            $order->payment_complete();
            do_action( 'dokan_stripe_payment_completed', $order, $intent );

            return [
                'result'   => 'success',
                'redirect' => $this->get_return_url( $order )
            ];
        }

        return [
            'result'   => 'fail',
            'redirect' => ''
        ];
    }

    /**
     * Create a new PaymentIntent
     *
     * @since 3.0.3
     *
     * @param \WC_Order $order
     * @param object $prepared_source The source that is used for the payment
     *
     * @return object
     */
    public function create_intent( $order, $prepared_source ) {
        $description = sprintf(
            __( '%1$s - Order %2$s', 'dokan' ),
            wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
            $order->get_order_number()
        );

        $request = [
            'source'               => $prepared_source->source,
            'amount'               => Helper::get_stripe_amount( $order->get_total() ),
            'currency'             => strtolower( $order->get_currency() ),
            'description'          => $description,
            'setup_future_usage'   => $prepared_source->setup_future_usage,
            'capture_method'       => 'automatic',
            'payment_method_types' => [
                'card',
            ],
        ];

        if ( $prepared_source->customer ) {
            $request['customer'] = $prepared_source->customer;
        }

        try {
            $intent = PaymentIntent::create( $request );
        } catch ( Exception $e ) {
            throw new DokanException( 'unable_to_create_payment_intent', $e->getMessage() );
        }

        $this->save_intent_to_order( $order, $intent );

        return $intent;
    }

    /**
     * Saves intent to order.
     *
     * @since 3.0.3
     *
     * @param \WC_Order $order For to which the source applies.
     * @param \Stripe\Paymentintent $intent Payment intent information.
     *
     * @return void
     */
    public function save_intent_to_order( $order, $intent ) {
        $order->update_meta_data( 'dokan_stripe_intent_id', $intent->id );

        if ( is_callable( [ $order, 'save' ] ) ) {
            $order->save();
        }
    }

    /**
     * Updates an existing intent with updated amount, source, and customer.
     *
     * @since 3.0.3
     *
     * @param object   $intent          The existing intent object.
     * @param WC_Order $order           The order.
     * @param object   $prepared_source Currently selected source.
     *
     * @return object                   An updated intent.
     */
    public function update_existing_intent( $intent, $order, $prepared_source ) {
        $request = [];

        if ( $prepared_source->source !== $intent->source ) {
            $request['source'] = $prepared_source->source;
        }

        $new_amount = Helper::get_stripe_amount( $order->get_total() );

        if ( $intent->amount !== $new_amount ) {
            $request['amount'] = $new_amount;
        }

        if ( $prepared_source->customer && $intent->customer !== $prepared_source->customer ) {
            $request['customer'] = $prepared_source->customer;
        }

        if ( empty( $request ) ) {
            return $intent;
        }

        try {
            $intent = PaymentIntent::update(
                $intent->id,
                $request
            );
        } catch ( Exception $e ) {
            throw new Exception( 'payment_intent_error', $e->getMessage() );
        }

        return $intent;
    }

    /**
     * Confirms an intent if it is the `requires_confirmation` state.
     *
     * @since 3.0.3
     *
     * @param object   $intent          The intent to confirm.
     * @param object   $prepared_source The source that is being charged.
     *
     * @return \Stripe\Paymentintent
     **/
    public function confirm_intent( $intent, $prepared_source ) {
        if ( 'requires_confirmation' !== $intent->status ) {
            return $intent;
        }

        // Try to confirm the intent (if 3DS is not required).
        $confirm_request = [
            'source' => $prepared_source->source,
        ];

        try {
            $confirmed_intent = $intent->confirm( $confirm_request );
        } catch ( Exception $e ) {
            throw new DokanException( 'unable_to_confirm_intent', $e->getMessage() );
        }

        return $confirmed_intent;
    }
}