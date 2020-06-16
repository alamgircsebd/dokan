<?php

namespace WeDevs\DokanPro\Modules\Stripe\Abstracts;

use Exception;
use Stripe\Source;
use WC_Payment_Tokens;
use WC_Payment_Gateway_CC;
use Stripe\PaymentIntent;
use WeDevs\DokanPro\Modules\Stripe\Helper;
use WeDevs\DokanPro\Modules\Stripe\Customer;
use WeDevs\Dokan\Withdraw\Manager\Withdraw;
use WeDevs\Dokan\Exceptions\DokanException;

abstract class StripePaymentGateway extends WC_Payment_Gateway_CC {

    /**
     * Check if this gateway is enabled and available in the user's country
     *
     * @since 3.0.3
     *
     * @return bool
     */
    public function is_available() {
        return Helper::is_ready();
    }

    /**
     * Get payment source. This can be a new token/source or existing WC token.
     * If user is logged in and/or has WC account, create an account on Stripe.
     * This way we can attribute the payment to the user to better fight fraud.
     *
     * @since 3.0.3
     *
     * @param string $user_id
     * @param bool $force_save_source Should we force save payment source.
     *
     * @return object
     */
    public function prepare_source( $user_id, $force_save_source = false, $existing_customer_id = null ) {
        global $woocommerce;

        $customer = new Customer( $user_id );

        if ( ! empty( $existing_customer_id ) ) {
            $customer->set_id( $existing_customer_id );
        }

        $posted             = wp_unslash( $_POST );
        $source_object      = '';
        $source_id          = '';
        $wc_token_id        = '';
        $payment_method     = isset( $posted['payment_method'] ) ? wc_clean( $posted['payment_method'] ) : 'dokan-stripe-connect';
        $is_token           = false;
        $setup_future_usage = false;

        // New CC info was entered and we have a new source to process.
        if ( ! empty( $posted['stripe_source'] ) ) {
            $source_object    = $this->get_source_object( wc_clean( $posted['stripe_source'] ) );
            $source_id        = $source_object->id;
            $maybe_saved_card = ! empty( $posted[ 'wc-' . $payment_method . '-new-payment-method' ] );

            /**
             * This is true if the user wants to store the card to their account.
             * Criteria to save to file is they are logged in, they opted to save or product requirements and the source is
             * actually reusable. Either that or force_save_source is true.
             */
            if ( ( $user_id && Helper::save_cards() && $maybe_saved_card && 'reusable' === $source_object->usage ) || $force_save_source ) {
                $response           = $customer->add_source( $source_object->id );
                $setup_future_usage = true;
            }

        } elseif ( $this->is_using_saved_payment_method() ) {
            $wc_token_id = wc_clean( $posted[ 'wc-' . $payment_method . '-payment-token' ] );
            $wc_token    = WC_Payment_Tokens::get( $wc_token_id );

            if ( ! $wc_token || $wc_token->get_user_id() !== get_current_user_id() ) {
                WC()->session->set( 'refresh_totals', true );
                throw new DokanException( 'Invalid payment method', __( 'Invalid payment method. Please input a new card number.', 'dokan' ) );
            }

            $source_id = $wc_token->get_token();

            if ( $this->is_type_legacy_card( $source_id ) ) {
                $is_token = true;
            }
        } elseif ( ! empty( $posted['stripe_token' ] ) && 'new' !== $posted['stripe_token'] ) {
            $stripe_token     = wc_clean( $posted['stripe_token'] );
            $source_id        = $stripe_token;
            $is_token         = true;
            $maybe_saved_card = ! empty( $posted[ 'wc-' . $payment_method . '-new-payment-method' ] );

            /**
             * This is true if the user wants to store the card to their account.
             * Criteria to save to file is they are logged in, they opted to save or product requirements and the source is
             * actually reusable. Either that or force_save_source is true.
             */
            if ( ( $user_id && Helper::save_cards() && $maybe_saved_card ) || $force_save_source ) {
                $response           = $customer->add_source( $stripe_token );
                $setup_future_usage = true;
            }
        }

        $customer_id = $customer->get_id();

        if ( ! $customer_id ) {
            $customer->set_id( $customer->create_customer( [ 'source' => $source_id ] ) );
            $customer_id = $customer->get_id();
        } else {
            $customer->update_customer();
        }

        if ( empty( $source_object ) && ! $is_token ) {
            $source_object = $this->get_source_object( $source_id );
        }

        return (object) [
            'customer'           => $customer_id,
            'token_id'           => $wc_token_id,
            'source'             => $source_id,
            'source_object'      => $source_object,
            'setup_future_usage' => $setup_future_usage ? 'off_session' : null
        ];
    }

    /**
     * Get source object by source id.
     *
     * @since 3.0.3
     *
     * @param string $source_id The source ID to get source object for.
     */
    public function get_source_object( $source_id = '' ) {
        if ( empty( $source_id ) ) {
            return '';
        }

        try {
            $source_object = Source::retrieve( $source_id );
        } catch( Exception $e ) {
            throw new DokanException( 'get_source_object', $e->getMessage() );
        }

        return $source_object;
    }

    /**
     * Checks if payment is via saved payment source.
     *
     * @since 3.0.3
     *
     * @return bool
     */
    public function is_using_saved_payment_method() {
        $payment_method = isset( $_POST['payment_method'] ) ? wc_clean( $_POST['payment_method'] ) : 'dokan-stripe-connect';

        return ( isset( $_POST[ 'wc-' . $payment_method . '-payment-token' ] ) && 'new' !== $_POST[ 'wc-' . $payment_method . '-payment-token' ] );
    }

    /**
     * Checks whether a source exists.
     *
     * @since 3.0.3
     *
     * @param object $prepared_source The source that should be verified.
     *
     * @throws \DokanException
     */
    public function validate_source( $prepared_source ) {
        if ( empty( $prepared_source->source ) ) {
            throw new DokanException(
                'wrong',
                __( 'Payment processing failed. Please retry.', 'dokan' )
            );
        }

        if ( ! empty( $prepared_source->source_object->status ) && 'consumed' === $prepared_source->source_object->status ) {
            throw new DokanException(
                'wrong',
                sprintf(
                    __( 'Payment processing failed. Please try again with a different card. If it\'s a saved card, <a href="%s" target="_blank">remove it first</a> and try again.', 'dokan' ),
                    wc_get_account_endpoint_url( 'payment-methods' )
                )
            );
        }
    }

    /**
     * Save source to order.
     *
     * @since 3.0.3
     *
     * @param WC_Order $order For to which the source applies.
     * @param stdClass $source Source information.
     *
     * @return void
     */
    public function save_source_to_order( $order, $source ) {
        if ( $source->customer ) {
            $order->update_meta_data( 'dokan_stripe_customer_id', $source->customer );
        }

        if ( $source->source ) {
            $order->update_meta_data( 'dokan_stripe_source_id', $source->source );
        }

        if ( is_callable( [ $order, 'save' ] ) ) {
            $order->save();
        }
    }

    /**
     * Validates that the order meets the minimum order amount
     * set by Stripe.
     *
     * @since 3.0.3
     *
     * @param object $order
     *
     * @return void
     */
    public function validate_minimum_order_amount( $order ) {
        if ( $order->get_total() * 100 < $this->get_minimum_amount() ) {
            throw new Exception( 'Did not meet minimum amount', sprintf( __( 'Sorry, the minimum allowed order total is %1$s to use this payment method.', 'dokan' ), wc_price( $this->get_minimum_amount() / 100 ) ) );
        }
    }

    /**
     * Checks Stripe minimum order value authorized per currency
     *
     * @since 3.0.3
     *
     * @return int
     */
    public function get_minimum_amount() {
        // Check order amount
        switch ( get_woocommerce_currency() ) {
            case 'USD':
            case 'CAD':
            case 'EUR':
            case 'CHF':
            case 'AUD':
            case 'SGD':
                $minimum_amount = 50;
                break;
            case 'GBP':
                $minimum_amount = 30;
                break;
            case 'DKK':
                $minimum_amount = 250;
                break;
            case 'NOK':
            case 'SEK':
                $minimum_amount = 300;
                break;
            case 'JPY':
                $minimum_amount = 5000;
                break;
            case 'MXN':
                $minimum_amount = 1000;
                break;
            case 'HKD':
                $minimum_amount = 400;
                break;
            default:
                $minimum_amount = 50;
                break;
        }

        return $minimum_amount;
    }

    /**
     * Locks an order for payment intent processing for 5 minutes.
     *
     * @since 3.0.3
     *
     * @param WC_Order $order  The order that is being paid.
     * @param stdClass $intent The intent that is being processed.
     *
     * @return bool            A flag that indicates whether the order is already locked.
     */
    public function lock_order_payment( $order, $intent = null ) {
        $order_id       = $order->get_id();
        $transient_name = 'wc_stripe_processing_intent_' . $order_id;
        $processing     = get_transient( $transient_name );

        // Block the process if the same intent is already being handled.
        if ( "-1" === $processing || ( isset( $intent->id ) && $processing === $intent->id ) ) {
            return true;
        }

        // Save the new intent as a transient, eventually overwriting another one.
        set_transient( $transient_name, empty( $intent ) ? '-1' : $intent->id, 5 * MINUTE_IN_SECONDS );

        return false;
    }

    /**
     * Unlocks an order for processing by payment intents.
     *
     * @since 3.0.3
     *
     * @param WC_Order $order The order that is being unlocked.
     *
     * @return void
     */
    public function unlock_order_payment( $order ) {
        $order_id = $order->get_id();
        delete_transient( 'wc_stripe_processing_intent_' . $order_id );
    }

    /**
     * Store extra meta data for an order from a Stripe Response.
     *
     * @since 3.0.3
     */
    public function process_response( $response, $order ) {

        $order_id = $order->get_id();
        $captured = ( isset( $response->captured ) && $response->captured ) ? 'yes' : 'no';

        if ( 'yes' === $captured ) {
            /**
             * Charge can be captured but in a pending state. Payment methods
             * that are asynchronous may take couple days to clear. Webhook will
             * take care of the status changes.
             */
            if ( 'pending' === $response->status ) {
                $order_stock_reduced = $order->get_meta( '_order_stock_reduced', true );

                if ( ! $order_stock_reduced ) {
                    wc_reduce_stock_levels( $order_id );
                }

                $order->set_transaction_id( $response->id );
                $order->update_status( 'on-hold', sprintf( __( 'Stripe charge awaiting payment: %s.', 'dokan' ), $response->id ) );
            }

            if ( 'succeeded' === $response->status ) {
                $order->payment_complete( $response->id );

                /* translators: transaction id */
                $message = sprintf( __( 'Stripe charge complete (Charge ID: %s)', 'dokan' ), $response->id );
                $order->add_order_note( $message );
            }

            if ( 'failed' === $response->status ) {
                $localized_message = __( 'Payment processing failed. Please retry.', 'dokan' );
                $order->add_order_note( $localized_message );
                throw new \WC_Stripe_Exception( print_r( $response, true ), $localized_message );
            }
        } else {
            $order->set_transaction_id( $response->id );

            if ( $order->has_status( [ 'pending', 'failed' ] ) ) {
                wc_reduce_stock_levels( $order_id );
            }

            $order->update_status( 'on-hold', sprintf( __( 'Stripe charge authorized (Charge ID: %s). Process order to take payment, or cancel to remove the pre-authorization.', 'woocommerce-gateway-stripe' ), $response->id ) );
        }

        if ( is_callable( [ $order, 'save' ] ) ) {
            $order->save();
        }

        do_action( 'dokan_gateway_stripe_process_response', $response, $order );

        return $response;
    }
    /**
     * Retrieves the payment intent, associated with an order.
     *
     * @since 3.0.3
     *
     * @param WC_Order $order The order to retrieve an intent for.
     *
     * @return obect|bool     Either the intent object or `false`.
     */
    public function get_intent_from_order( $order ) {
        $intent_id = $order->get_meta( 'dokan_stripe_intent_id' );

        if ( ! $intent_id ) {
            return false;
        }

        try {
            $intent = PaymentIntent::retrieve( $intent_id );
        } catch( Exception $e ) {
            return false;
        }

        return $intent;
    }


    /**
     * Get charge id from from an order
     *
     * @since 3.0.3
     *
     * @param \WC_Order $order
     *
     * @return string|false on failure
     */
    public function get_charge_id_from_order( $order ) {
        try {
            $intent = $this->get_intent_from_order( $order );
        } catch ( Exception $e ) {
            return false;
        }

        if ( $intent && is_object( $intent ) ) {
            $charges    = ! empty( $intent->charges->data ) ? $intent->charges->data : [];
            $charge_ids = wp_list_pluck( $charges, 'id' );

            return is_array( $charge_ids ) ? $charge_ids[0] : false;
        }

        return false;
    }

    /**
     * Checks to see if request is invalid and that
     * they are worth retrying.
     *
     * @since 3.0.3
     *
     * @param array $error
     */
    public function is_retryable_error( $error ) {
        return (
            'invalid_request_error' === $error->type ||
            'idempotency_error' === $error->type ||
            'rate_limit_error' === $error->type ||
            'api_connection_error' === $error->type ||
            'api_error' === $error->type
        );
    }

    /**
     * Checks to see if error is of same idempotency key
     * error due to retries with different parameters.
     *
     * @since 3.0.3
     *
     * @param array $error
     */
    public function is_same_idempotency_error( $error ) {
        return (
            $error &&
            'idempotency_error' === $error->type &&
            preg_match( '/Keys for idempotent requests can only be used with the same parameters they were first used with./i', $error->message )
        );
    }

    /**
     * Checks to see if error is of invalid request
     * error and it is no such customer.
     *
     * @since 3.0.3
     *
     * @param array $error
     */
    public function is_no_such_customer_error( $error ) {
        return (
            $error &&
            'invalid_request_error' === $error->type &&
            preg_match( '/No such customer/i', $error->message )
        );
    }

    /**
     * Checks to see if error is of invalid request
     * error and it is no such token.
     *
     * @since 3.0.3
     *
     * @param array $error
     */
    public function is_no_such_token_error( $error ) {
        return (
            $error &&
            'invalid_request_error' === $error->type &&
            preg_match( '/No such token/i', $error->message )
        );
    }

    /**
     * Checks to see if error is of invalid request
     * error and it is no such source.
     *
     * @since 3.0.3
     *
     * @param array $error
     */
    public function is_no_such_source_error( $error ) {
        return (
            $error &&
            'invalid_request_error' === $error->type &&
            preg_match( '/No such source/i', $error->message )
        );
    }

    /**
     * Checks to see if error is of invalid request
     * error and it is no such source linked to customer.
     *
     * @since 3.0.3
     *
     * @param array $error
     */
    public function is_no_linked_source_error( $error ) {
        return (
            $error &&
            'invalid_request_error' === $error->type &&
            preg_match( '/does not have a linked source with ID/i', $error->message )
        );
    }

    /**
     * Customer param wrong? The user may have been deleted on stripe's end. Remove customer_id. Can be retried without.
     *
     * @since 3.0.3
     *
     * @param object   $error The error that was returned from Stripe's API.
     * @param WC_Order $order The order those payment is being processed.
     * @return bool           A flag that indicates that the customer does not exist and should be removed.
     */
    public function maybe_remove_non_existent_customer( $error, $order ) {
        if ( ! $this->is_no_such_customer_error( $error ) ) {
            return false;
        }

        delete_user_option( $order->get_customer_id(), 'dokan_stripe_customer_id' );
        $order->delete_meta_data( 'dokan_stripe_customer_id' );
        $order->save();

        return true;
    }

    /**
     * Insert withdraw data into vendor balnace table
     *
     * @since 3.0.3
     *
     * @param  array $all_withdraw
     *
     * @return void
     */
    public function insert_into_vendor_balance( $all_withdraws ) {
        if ( ! $all_withdraws ) {
            return;
        }

        global $wpdb;

        foreach ( $all_withdraws as $withdraw ) {
            $wpdb->insert( $wpdb->prefix . 'dokan_vendor_balance',
                [
                    'vendor_id'     => $withdraw['user_id'],
                    'trn_id'        => $withdraw['order_id'],
                    'trn_type'      => 'dokan_withdraw',
                    'perticulars'   => 'Paid Via Stripe',
                    'debit'         => 0,
                    'credit'        => $withdraw['amount'],
                    'status'        => 'approved',
                    'trn_date'      => current_time( 'mysql' ),
                    'balance_date'  => current_time( 'mysql' ),
                ],
                [
                    '%d',
                    '%d',
                    '%s',
                    '%s',
                    '%f',
                    '%f',
                    '%s',
                    '%s',
                    '%s',
                ]
            );
        }
    }

    /**
     * Automatically process withdrwal for sellers per order
     *
     * @since 3.0.3
     *
     * @param array $all_withdraws
     *
     * @return void
     */
    public function process_seller_withdraws( $all_withdraws ){
        $IP = dokan_get_client_ip();

        foreach ( $all_withdraws as $withdraw_data ) {
            $data = [
                'date'   => current_time( 'mysql' ),
                'status' => 1,
                'method' => 'dokan-stripe-connect',
                'notes'  => sprintf( __( 'Order %d payment Auto paid via %s', 'dokan' ), $withdraw_data['order_id'], Helper::get_gateway_title() ),
                'ip'     => $IP
            ];

            $data = array_merge( $data, $withdraw_data );
            dokan()->withdraw->insert_withdraw( $data );
        }
    }

    /**
     * Get order details
     *
     * @since 3.0.3
     *
     * @param  int  $order_id
     * @param  int  $seller_id
     *
     * @return array
     */
    public function get_dokan_order( $order_id, $seller_id ) {
        global $wpdb;

        $sql = "SELECT *
        FROM {$wpdb->prefix}dokan_orders AS do
        WHERE
        do.seller_id = %d AND
        do.order_id = %d";

        return $wpdb->get_row( $wpdb->prepare( $sql, $seller_id, $order_id ) );
    }

    public function get_all_orders_to_be_processed( $order ) {
        $has_suborder = $order->get_meta( 'has_sub_order' );
        $all_orders   = [];

        if ( $has_suborder ) {
            $sub_order_ids = get_children( [ 'post_parent' => $order->get_id(), 'post_type' => 'shop_order', 'fields' => 'ids' ] );

            foreach ( $sub_order_ids as $sub_order_id ) {
                $sub_order    = wc_get_order( $sub_order_id );
                $all_orders[] = $sub_order;
            }
        } else {
            $all_orders[] = $order;
        }

        return apply_filters( 'dokan_get_all_orders_to_be_processed', $all_orders );
    }

    /**
     * Checks if source is of legacy type card.
     *
     * @since 3.0.3
     *
     * @param string $source_id
     *
     * @return bool
     */
    public function is_type_legacy_card( $source_id ) {
        return ( preg_match( '/^card_/', $source_id ) );
    }

    /**
     * Displays the save to account checkbox.
     *
     * @since 3.0.3
     */
    public function save_payment_method_checkbox() {
        printf(
            '<p class="form-row woocommerce-SavedPaymentMethods-saveNew">
                <input id="wc-%1$s-new-payment-method" name="wc-%1$s-new-payment-method" type="checkbox" value="true" style="width:auto;" />
                <label for="wc-%1$s-new-payment-method" style="display:inline;">%2$s</label>
            </p>',
            esc_attr( $this->id ),
            esc_html( apply_filters( 'dokan_stripe_save_to_account_text', __( 'Save payment information to my account for future purchases.', 'dokan' ) ) )
        );
    }

    /**
     * Add payment method via account screen.
     *
     * @since 3.0.3
     *
     * @return array
     */
    public function add_payment_method() {
        $error     = false;
        $error_msg = __( 'There was a problem adding the payment method.', 'dokan' );
        $source_id = '';

        $posted = wp_unslash( $_POST );

        if ( empty( $posted['stripe_source'] ) && empty( $posted['stripe_token'] ) || ! is_user_logged_in() ) {
            $error = true;
        }

        $stripe_customer = new Customer( get_current_user_id() );
        $source          = ! empty( $posted['stripe_source'] ) ? wc_clean( $posted['stripe_source'] ) : '';
        $source_object   = $this->get_source_object( $source );

        if ( ! is_object( $source_object ) ) {
            $error = true;
        }

        if ( ! empty( $source_object->id ) ) {
            $source_id = $source_object->id;
        } elseif ( ! empty( $posted['stripe_token'] ) ) {
            $source_id = wc_clean( $posted['stripe_token'] );
        } else {
            $error = true;
        }

        $response = $stripe_customer->add_source( $source_id );

        if ( ! $response || is_wp_error( $response ) ) {
            $error = true;
        }

        if ( $error ) {
            wc_add_notice( $error_msg, 'error' );
            dokan_log( 'Add payment method Error: ' . $error_msg );
            return;
        }

        do_action( 'dokan_stripe_connect_add_payment_method_' . $posted['payment_method'] . '_success', $source_id, $source_object );

        return [
            'result'   => 'success',
            'redirect' => wc_get_endpoint_url( 'payment-methods' ),
        ];
    }
}