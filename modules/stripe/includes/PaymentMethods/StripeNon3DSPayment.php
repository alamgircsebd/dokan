<?php

namespace WeDevs\DokanPro\Modules\Stripe\PaymentMethods;

use Stripe\Token;
use Stripe\Charge;
use Stripe\BalanceTransaction;
use WeDevs\DokanPro\Modules\Stripe\Helper;
use WeDevs\Dokan\Exceptions\DokanException;
use WeDevs\DokanPro\Modules\Stripe\Customer;
use WeDevs\DokanPro\Modules\Stripe\StripeConnect;
use WeDevs\DokanPro\Modules\Stripe\Interfaces\Payable;

class StripeNon3DSPayment extends StripeConnect implements Payable {

    /**
     * Hold all the data of order object.
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
        $order         = $this->order;
        $customer_id   = get_current_user_id();
        $all_withdraws = [];

        $this->validate_minimum_order_amount( $order );

        $stripe_customer = ! empty( $_POST['dokan_stripe_customer_id'] ) ? wc_clean( $_POST['dokan_stripe_customer_id'] ) : null;
        $prepared_source = $this->prepare_source( $customer_id, false, $stripe_customer );
        $this->validate_source( $prepared_source );

        try {
            if ( Helper::is_subscription_order( $order ) ) {
                $product_pack = Helper::get_subscription_product_by_order( $order );
                $subscription = dokan()->subscription->get( $product_pack->get_id() );

                if ( ! $subscription->is_recurring() ) {
                    $currency   = strtolower( get_woocommerce_currency() );
                    $order_desc = sprintf( __( '%s - Order %s', 'dokan' ), esc_html( get_bloginfo( 'name' ) ), $order->get_order_number() );
                    $charge     = Charge::create( [
                        'amount'      => Helper::get_stripe_amount( $order->get_total() ),
                        'currency'    => $currency,
                        'description' => $order_desc,
                        'customer'    => $prepared_source->customer
                    ] );

                    $order->add_order_note(
                        sprintf(
                            __( 'Order %s payment completed via %s on Charge ID: %s', 'dokan' ),
                            $order->get_order_number(),
                            $this->title,
                            $charge->id
                        )
                    );
                    $order->payment_complete();
                }

                do_action( 'dokan_process_subscription_order', $order, $stripe_customer, $subscription->is_recurring() );
            } else {
                $this->process_seller_payment( $order, $prepared_source );
            }
        } catch( Exception $e ) {
            $order->add_order_note( sprintf( __( 'Stripe Payment Error: %s', 'dokan' ), $e->getMessage() ) );
            update_post_meta( $order_id, '_dwh_stripe_charge_error', $e->getMessage());
            wc_add_notice( __( 'Error: ', 'dokan' ) . $e->getMessage() );
            return;
        }

        $response = [
            'result'   => 'success',
            'redirect' => $this->get_return_url( $order )
        ];

        return $response;
    }

    /**
    * Process Seller payment
    *
    * @since 1.3.3
    *
    * @return void
    **/
    public function process_seller_payment( $order, $prepared_source ) {
        $currency      = strtolower( get_woocommerce_currency() );
        $order_desc    = sprintf( __( '%s - Order %s', 'dokan' ), esc_html( get_bloginfo( 'name' ) ), $order->get_order_number() );
        $charge_ids    = [];
        $all_withdraws = [];
        $all_orders    = $this->get_all_orders_to_be_processed( $order );

        if ( ! $all_orders ) {
            throw new Exception( __( 'No orders found to process!', 'dokan' ) );
        }

        foreach ( $all_orders as $tmp_order ) {
            $tmp_order_id = $tmp_order->get_id();
            $seller_id    = dokan_get_seller_id_by_order( $tmp_order_id );
            $dokan_order  = $this->get_dokan_order( $tmp_order_id, $seller_id );

            if ( ! $dokan_order ) {
                throw new Exception( __( 'Something went wrong and the order can not be processed!', 'dokan' ) );
            }

            $order_total     = (float) $dokan_order->order_total;
            $application_fee = $order_total - (float) $dokan_order->net_amount;
            $vendor_earning  = $order_total - $application_fee;

            if ( $dokan_order->order_total == 0 ) {
                $tmp_order->add_order_note( sprintf( __( 'Order %s payment completed', 'dokan' ), $tmp_order->get_order_number() ) );
                continue;
            }

            $access_token = get_user_meta( $seller_id, '_stripe_connect_access_key', true );

            if ( ! empty( $access_token ) ) {
                // if it's guest user, try to create a token
                $token = Token::create( [ 'customer' => $prepared_source->customer ], $access_token );
            } else if ( Helper::allow_non_connected_sellers() ) {
                $token = false;
            } else {
                throw new Exception( __( 'Unable to process with Stripe gateway', 'dokan' ) );
            }

            if ( $token ) {
                $charge = Charge::create( [
                    'amount'          => Helper::get_stripe_amount( $order_total ),
                    'currency'        => $currency,
                    'application_fee' => Helper::get_stripe_amount( $application_fee ),
                    'description'     => $order_desc,
                    'source'          => ! empty( $token->id ) ? $token->id : $prepared_source->source,
                    ], $access_token );
            } else {
                $order_desc = sprintf( __( '%s - Order %s, suborder of %s', 'dokan' ), esc_html( get_bloginfo( 'name' ) ), $tmp_order_id, $order->get_order_number() );
                $charge     = Charge::create( [
                    'amount'      => Helper::get_stripe_amount( $order_total ),
                    'currency'    => $currency,
                    'description' => $order_desc,
                    'customer'    => $prepared_source->customer
                ] );

                $tmp_order->add_order_note( sprintf( __( 'Vendor payment transferred to admin account since the vendor had not connected to Stripe.', 'dokan' ) ) );
            }

            $charge_ids[ $seller_id ] = $charge->id;
            update_post_meta( $tmp_order_id, $this->stripe_meta_key . $seller_id, $charge->id );

            if ( $order->get_id() !== $tmp_order_id && $token ) {
                $tmp_order->add_order_note(
                    sprintf(
                        __( 'Order %s payment completed via %s on Charge ID: %s', 'dokan' ),
                        $tmp_order->get_order_number(),
                        $this->title,
                        $charge->id
                    )
                );
            }

            // Only process withdraw request once vendor get paid.
            if ( ! is_null( $token ) ) {
                $withdraw_data = [
                    'user_id'  => $seller_id,
                    'amount'   => $vendor_earning,
                    'order_id' => $tmp_order_id,
                ];

                $all_withdraws[] = $withdraw_data;
            }
        }

        $order->add_order_note(
            sprintf(
                __( 'Order %s payment is completed via %s on (Charge ID: %s)', 'dokan' ),
                $order->get_order_number(),
                $this->title,
                implode( ', ', $charge_ids )
            )
        );

        $order->payment_complete();
        $this->insert_into_vendor_balance( $all_withdraws );
        $this->process_seller_withdraws( $all_withdraws );

        // update gateway fee
        if ( ! empty( $access_token ) && ! empty( $charge->balance_transaction  ) ) {
            $balance_transaction = BalanceTransaction::retrieve( [ 'id' => $charge->balance_transaction ], $access_token );

            if ( $balance_transaction ) {
                $fee = Helper::format_gateway_balance_fee( $balance_transaction );

                update_post_meta( $tmp_order_id, 'dokan_gateway_stripe_fee', $fee );

                $tmp_order->add_order_note( sprintf( __( 'Payment gateway processing fee %s', 'dokan' ), $fee ) );
            }
        }

        foreach ( $charge_ids as $seller_id => $charge_id ) {
            $meta_key = $this->stripe_meta_key . $seller_id;
            update_post_meta( $order->get_id(), $meta_key, $charge_id );
        }
    }
}