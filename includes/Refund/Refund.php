<?php

namespace WeDevs\DokanPro\Refund;

use WP_Error;
use WeDevs\Dokan\Abstracts\DokanModel;

class Refund extends DokanModel {

    /**
     * The model data
     *
     * @since 3.0.0
     *
     * @var array
     */
    protected $data = [];

    /**
     * Class constructor
     *
     * @since 3.0.0
     *
     * @param array $data
     */
    public function __construct( $data = [] ) {
        $defaults = [
            'id'              => 0,
            'order_id'        => 0,
            'seller_id'       => 0,
            'refund_amount'   => 0,
            'refund_reason'   => '',
            'item_qtys'       => null,
            'item_totals'     => null,
            'item_tax_totals' => null,
            'restock_items'   => null,
            'date'            => current_time( 'mysql' ),
            'status'          => 0,
            'method'          => 'false',
        ];

        $data = wp_parse_args( $data, $defaults );

        $this->set_data( $data );
    }

    /**
     * Set model data
     *
     * @since 3.0.0
     *
     * @param array $data
     *
     * @return void
     */
    protected function set_data( $data ) {
        $data = wp_unslash( $data );

        $this->set_id( $data['id'] )
            ->set_order_id( $data['order_id'] )
            ->set_seller_id( $data['seller_id'] )
            ->set_refund_amount( $data['refund_amount'] )
            ->set_refund_reason( $data['refund_reason'] )
            ->set_item_qtys( $data['item_qtys'] )
            ->set_item_totals( $data['item_totals'] )
            ->set_item_tax_totals( $data['item_tax_totals'] )
            ->set_restock_items( $data['restock_items'] )
            ->set_date( $data['date'] )
            ->set_status( $data['status'] )
            ->set_method( $data['method'] );
    }

    /**
     * Set `id` property
     *
     * @since 3.0.0
     *
     * @param int $set_id
     *
     * @return \WeDevs\DokanPro\Refund\Refund
     */
    public function set_id( $id ) {
        $this->data['id'] = $id;
        return $this;
    }

    /**
     * Set `order_id` property
     *
     * @since 3.0.0
     *
     * @param int $set_order_id
     *
     * @return \WeDevs\DokanPro\Refund\Refund
     */
    public function set_order_id( $order_id ) {
        $this->data['order_id'] = $order_id;
        return $this;
    }

    /**
     * Set `seller_id` property
     *
     * @since 3.0.0
     *
     * @param int $set_seller_id
     *
     * @return \WeDevs\DokanPro\Refund\Refund
     */
    public function set_seller_id( $seller_id ) {
        $this->data['seller_id'] = $seller_id;
        return $this;
    }

    /**
     * Set `refund_amount` property
     *
     * @since 3.0.0
     *
     * @param string $set_refund_amount
     *
     * @return \WeDevs\DokanPro\Refund\Refund
     */
    public function set_refund_amount( $refund_amount ) {
        $this->data['refund_amount'] = $refund_amount;
        return $this;
    }

    /**
     * Set `refund_reason` property
     *
     * @since 3.0.0
     *
     * @param string $set_refund_reason
     *
     * @return \WeDevs\DokanPro\Refund\Refund
     */
    public function set_refund_reason( $refund_reason ) {
        $this->data['refund_reason'] = $refund_reason;
        return $this;
    }

    /**
     * Set `item_qtys` property
     *
     * @since 3.0.0
     *
     * @param array $set_item_qtys
     *
     * @return \WeDevs\DokanPro\Refund\Refund
     */
    public function set_item_qtys( $item_qtys ) {
        $this->data['item_qtys'] = $item_qtys;
        return $this;
    }

    /**
     * Set `item_totals` property
     *
     * @since 3.0.0
     *
     * @param array $set_item_totals
     *
     * @return \WeDevs\DokanPro\Refund\Refund
     */
    public function set_item_totals( $item_totals ) {
        $this->data['item_totals'] = $item_totals;
        return $this;
    }

    /**
     * Set `item_tax_totals` property
     *
     * @since 3.0.0
     *
     * @param array $set_item_tax_totals
     *
     * @return \WeDevs\DokanPro\Refund\Refund
     */
    public function set_item_tax_totals( $item_tax_totals ) {
        $this->data['item_tax_totals'] = $item_tax_totals;
        return $this;
    }

    /**
     * Set `restock_items` property
     *
     * @since 3.0.0
     *
     * @param array $set_restock_items
     *
     * @return \WeDevs\DokanPro\Refund\Refund
     */
    public function set_restock_items( $restock_items ) {
        $this->data['restock_items'] = $restock_items;
        return $this;
    }

    /**
     * Set `date` property
     *
     * @since 3.0.0
     *
     * @param string $set_date
     *
     * @return \WeDevs\DokanPro\Refund\Refund
     */
    public function set_date( $date ) {
        $this->data['date'] = $date;
        return $this;
    }

    /**
     * Set `status` property
     *
     * @since 3.0.0
     *
     * @param string $set_status
     *
     * @return \WeDevs\DokanPro\Refund\Refund
     */
    public function set_status( $status ) {
        $this->data['status'] = $status;
        return $this;
    }

    /**
     * Set `method` property
     *
     * @since 3.0.0
     *
     * @param string $set_method
     *
     * @return \WeDevs\DokanPro\Refund\Refund
     */
    public function set_method( $method ) {
        $this->data['method'] = $method;
        return $this;
    }

    /**
     * Get `id` property
     *
     * @since 3.0.0
     *
     * @return int
     */
    public function get_id() {
        return $this->data['id'];
    }

    /**
     * Get `order_id` property
     *
     * @since 3.0.0
     *
     * @return int
     */
    public function get_order_id() {
        return $this->data['order_id'];
    }

    /**
     * Get `seller_id` property
     *
     * @since 3.0.0
     *
     * @return int
     */
    public function get_seller_id() {
        return $this->data['seller_id'];
    }

    /**
     * Get `refund_amount` property
     *
     * @since 3.0.0
     *
     * @return string
     */
    public function get_refund_amount() {
        return $this->data['refund_amount'];
    }

    /**
     * Get `refund_reason` property
     *
     * @since 3.0.0
     *
     * @return string
     */
    public function get_refund_reason() {
        return $this->data['refund_reason'];
    }

    /**
     * Get `item_qtys` property
     *
     * @since 3.0.0
     *
     * @return array
     */
    public function get_item_qtys() {
        return $this->data['item_qtys'];
    }

    /**
     * Get `item_totals` property
     *
     * @since 3.0.0
     *
     * @return array
     */
    public function get_item_totals() {
        return $this->data['item_totals'];
    }

    /**
     * Get `item_tax_totals` property
     *
     * @since 3.0.0
     *
     * @return array
     */
    public function get_item_tax_totals() {
        return $this->data['item_tax_totals'];
    }

    /**
     * Get `restock_items` property
     *
     * @since 3.0.0
     *
     * @return array
     */
    public function get_restock_items() {
        return $this->data['restock_items'];
    }

    /**
     * Get `date` property
     *
     * @since 3.0.0
     *
     * @return string
     */
    public function get_date() {
        return $this->data['date'];
    }

    /**
     * Get `status` property
     *
     * @since 3.0.0
     *
     * @return string
     */
    public function get_status() {
        return $this->data['status'];
    }

    /**
     * Get `status_name` property
     *
     * @since 3.0.0
     *
     * @return string
     */
    public function get_status_name() {
        $status_name = dokan_pro()->refund->get_status_names();
        return $status_name[ $this->get_status() ];
    }

    /**
     * Get `method` property
     *
     * @since 3.0.0
     *
     * @return string
     */
    public function get_method() {
        return $this->data['method'];
    }

    /**
     * Prepare model for DB insertion
     *
     * @since 3.0.0
     *
     * @return array
     */
    protected function prepare_for_db() {
        $data = $this->get_data();

        $data['item_qtys']       = is_array( $data['item_qtys'] ) ? json_encode( $data['item_qtys'] ) : null;
        $data['item_totals']     = is_array( $data['item_totals'] ) ? json_encode( $data['item_totals'] ) : null;
        $data['item_tax_totals'] = is_array( $data['item_tax_totals'] ) ? json_encode( $data['item_tax_totals'] ) : null;

        return $data;
    }

    /**
     * Save a model
     *
     * @since 3.0.0
     *
     * @return \WeDevs\DokanPro\Refund\Refund
     */
    public function save() {
        if ( ! $this->get_id() ) {
            return $this->create();
        } else {
            return $this->update();
        }
    }

    /**
     * Create a model
     *
     * @since 3.0.0
     *
     * @return \WeDevs\DokanPro\Refund\Refund
     */
    protected function create() {
        global $wpdb;

        unset( $this->data['id'] );

        $data = $this->prepare_for_db();

        $inserted = $wpdb->insert(
            $wpdb->dokan_refund,
            $data,
            [ '%d', '%d', '%f', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s' ]
        );

        if ( $inserted !== 1 ) {
            return new WP_Error( 'dokan_refund_create_error', __( 'Could not create new refund', 'dokan' ) );
        }

        $refund = dokan_pro()->refund->get( $wpdb->insert_id );

        /**
         * Fires after created a refund request
         *
         * @since 3.0.0
         *
         * @param \WeDevs\DokanPro\Refund\Refund $refund
         */
        do_action( 'dokan_refund_request_created', $refund );

        return $refund;
    }

    /**
     * Update a model
     *
     * @since 3.0.0
     *
     * @return \WeDevs\DokanPro\Refund\Refund
     */
    protected function update() {
        global $wpdb;

        $data = $this->prepare_for_db();

        $updated = $wpdb->update(
            $wpdb->dokan_refund,
            [
                'order_id'        => $data['order_id'],
                'seller_id'       => $data['seller_id'],
                'refund_amount'   => $data['refund_amount'],
                'refund_reason'   => $data['refund_reason'],
                'item_qtys'       => $data['item_qtys'],
                'item_totals'     => $data['item_totals'],
                'item_tax_totals' => $data['item_tax_totals'],
                'restock_items'   => $data['restock_items'],
                'date'            => $data['date'],
                'status'          => $data['status'],
                'method'          => $data['method'],

            ],
            [ 'id' => $this->get_id() ],
            [ '%d', '%d', '%f', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s' ],
            [ '%d' ]
        );

        if ( $updated !== 1 ) {
            return new WP_Error( 'dokan_refund_update_error', __( 'Could not update refund', 'dokan' ) );
        }

        /**
         * Action based on refund status
         *
         * @since 3.0.0
         *
         * @param \WeDevs\Dokan\Refund\Refund $this
         */
        do_action( 'dokan_refund_request_' . dokan_pro()->refund->get_status_name( $this->get_status() ), $this );

        /**
         * Fires after update a refund
         *
         * @since 3.0.0
         *
         * @param \WeDevs\Dokan\Refund\Refund $this
         */
        do_action( 'dokan_refund_updated', $this );

        return $this;
    }

    /**
     * Delete a model
     *
     * @since 3.0.0
     *
     * @return \WeDevs\DokanPro\Refund\Refund
     */
    public function delete() {
        global $wpdb;

        $deleted = $wpdb->delete(
            $wpdb->dokan_refund,
            [ 'id' => $this->data['id'] ],
            [ '%d' ]
        );

        if ( ! $deleted ) {
            return new WP_Error( 'dokan_pro_refund_error_delete', __( 'Could not delete refund request', 'dokan' ) );
        }

        /**
         * Fires after delete a refund
         *
         * @since 3.0.0
         *
         * @param array $data
         */
        do_action( 'dokan_pro_refund_deleted', $this );

        return $this;
    }

    /**
     * Approve a refund
     *
     * @since 3.0.0
     *
     * @return \WeDevs\DokanPro\Refund\Refund
     */
    public function approve() {
        global $wpdb;

        if ( ! dokan_pro()->refund->is_approvable( $this->get_order_id() ) ) {
            return new WP_Error( 'dokan_pro_refund_error_approve', __( 'This refund is not allowed to approve', 'dokan' ) );
        }

        $order                  = wc_get_order( $this->get_order_id() );
        $api_refund             = dokan_validate_boolean( $this->get_method() );
        $restock_refunded_items = dokan_validate_boolean( $this->get_restock_items() );
        $vendor_refund          = 0;
        $tax_refund             = 0;
        $shipping_refund        = 0;

        $shipping_fee_recipient = dokan_get_option( 'shipping_fee_recipient', 'dokan_general', 'seller' );
        $tax_fee_recipient      = dokan_get_option( 'tax_fee_recipient', 'dokan_general', 'seller' );

        // Prepare line items which we are refunding.
        $line_items = [];
        $item_ids   = array_unique( array_merge( array_keys( $this->get_item_qtys(), $this->get_item_totals() ) ) );

        foreach ( $item_ids as $item_id ) {
            $line_items[ $item_id ] = array(
                'qty'          => 0,
                'refund_total' => 0,
                'refund_tax'   => array(),
            );
        }

        foreach ( $this->get_item_qtys() as $item_id => $qty ) {
            $line_items[ $item_id ]['qty'] = max( $qty, 0 );
        }

        // If `_dokan_admin_fee` is found means, the commission has been calculated for this order without the `Dokan_Commission` class.
        // So we'll calculate refund without using the `Dokan_Commission` class to keep backward compatability.
        if ( get_post_meta( $this->get_order_id(), '_dokan_admin_fee', true ) ) {
            foreach ( $this->get_item_totals() as $item_id => $total ) {
                $item = $order->get_item( $item_id );

                if ( 'line_item' == $item['type'] ) {
                    $percentage_type    = dokan_get_commission_type( $this->get_seller_id(), $item['product_id'] );
                    $vendor_percentage  = dokan_get_seller_percentage( $this->get_seller_id(), $item['product_id'] );
                    $vendor_refund      += $percentage_type == 'percentage' ? (float) ( $total * $vendor_percentage ) / 100 : (float) ( $total * ( ( $item['subtotal'] - $vendor_percentage ) / $item['subtotal'] ) );
                }

                $line_items[$item_id]['refund_total'] = wc_format_decimal( $total );
            }
        } else {
            // Set `order_id` so that `Dokan_Commission::prepare_for_calculation()` method can access the intended WC_Order.
            dokan()->commission->set_order_id( $this->get_order_id() );

            foreach ( $this->get_item_totals() as $item_id => $requested_refund ) {
                $item                                 = $order->get_item( $item_id );
                $line_items[$item_id]['refund_total'] = wc_format_decimal( $requested_refund );

                if ( 'line_item' === $item->get_type() ) {
                    $existing_refunds = $order->get_total_refunded_for_item( $item_id );

                    // If quantity of line_item is more than one, set `line_item_quantity` to increase commission by quantity for flat commission type.
                    if ( $item->get_quantity() > 1 ) {
                        dokan()->commission->set_order_qunatity( $item->get_quantity() );
                    }

                    // On order refund, set `_dokan_item_total` to `item->get_total()` so that `flat commission rate && additional_fee` can be splited properly among all the line_items.
                    update_post_meta( $this->get_order_id(), '_dokan_item_total', $item->get_total() );

                    $vendor_earning  = dokan()->commission->get_earning_by_product( $item['product_id'], 'seller', $item->get_total() - $existing_refunds );
                    $line_item_total = $item->get_total() - $existing_refunds;

                    if ( ! $line_item_total ) {
                        continue;
                    }

                    $vendor_percentage = ( $vendor_earning * 100 ) / $line_item_total;

                    if ( $requested_refund ) {
                        $vendor_refund += ( $requested_refund * $vendor_percentage ) / 100;
                    }
                }

                if ( 'shipping' === $item->get_type() ) {
                    $shipping_refund += $requested_refund;
                }
            }
        }

        foreach ( $this->get_item_tax_totals() as $item_id => $tax_totals ) {
            foreach ( $tax_totals as $total_tax ) {
                $tax_refund += $total_tax;
                $line_items[ $item_id ]['refund_tax'] = wc_format_decimal( $total_tax );
            }
        }

        $arr = [
            'amount'         => $this->get_refund_amount(),
            'reason'         => $this->get_refund_reason(),
            'order_id'       => $this->get_order_id(),
            'line_items'     => $line_items,
            'refund_payment' => $api_refund,
            'restock_items'  => $restock_refunded_items,
        ];

        // Create the refund object.
        $refund = wc_create_refund( $arr );

        if ( 'seller' == $shipping_fee_recipient ) {
            $vendor_refund += $shipping_refund;
        }

        if ( 'seller' === $tax_fee_recipient ) {
            $vendor_refund += $tax_refund;
        }

        // if paid via automatic payment such as stripe
        if ( 'dokan-stripe-connect' === $order->get_payment_method() && ! $order->get_meta( 'paid_with_dokan_3ds' ) ) {
            $wpdb->insert( $wpdb->dokan_vendor_balance,
                [
                    'vendor_id'     => $this->get_seller_id(),
                    'trn_id'        => $this->get_order_id(),
                    'trn_type'      => 'dokan_refund',
                    'perticulars'   => __( 'Paid Via Stripe', 'dokan' ),
                    'debit'         => $vendor_refund,
                    'credit'        => 0,
                    'status'        => 'wc-completed', // see: Dokan_Vendor->get_balance() method
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

        $wpdb->insert( $wpdb->dokan_vendor_balance,
            [
                'vendor_id'     => $this->get_seller_id(),
                'trn_id'        => $this->get_order_id(),
                'trn_type'      => 'dokan_refund',
                'perticulars'   => $this->get_refund_reason(),
                'debit'         => 0,
                'credit'        => $vendor_refund,
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

        // update the order table with new refund amount
        $order_data = $wpdb->get_row( $wpdb->prepare(
            "select * from $wpdb->dokan_orders where order_id = %d",
            $this->get_order_id()
        ) );

        if ( isset( $order_data->order_total, $order_data->net_amount ) ) {
            $new_total_amount = $order_data->order_total - $this->get_refund_amount();
            $new_net_amount   = $order_data->net_amount - $vendor_refund;

            // insert on dokan sync table
            $wpdb->update(
                $wpdb->dokan_orders,
                [
                    'order_total' => $new_total_amount,
                    'net_amount'  => $new_net_amount,
                ],
                [
                    'order_id' => $this->get_order_id(),
                ],
                [
                    '%f',
                    '%f',
                ],
                [
                    '%d'
                ]
            );
        }

        if ( dokan_is_sub_order( $this->get_order_id() ) ) {
            $parent_order_id = wp_get_post_parent_id( $this->get_order_id() );

            // Create the refund object for parent order.
            $refund = wc_create_refund(
                [
                    'amount'         => $this->get_refund_amount(),
                    'reason'         => $this->get_refund_reason(),
                    'order_id'       => $parent_order_id,
                    'line_items'     => [],
                    'refund_payment' => $api_refund,
                    'restock_items'  => false,
                ]
            );
        }

        $current_user = wp_get_current_user();

        $order->add_order_note( sprintf(
            __( 'Refund request approved by %s' ),
            $current_user->get( 'user_nicename' )
        ) );

        $this->set_status( dokan_pro()->refund->get_status_code( 'completed' ) );

        $refund = $this->save();

        if ( is_wp_error( $refund ) ) {
            return $refund;
        }

        /**
         * Fires after approve a refund request
         *
         * @since 3.0.0
         *
         * @param \WeDevs\DokanPro\Refund\Refund $refund
         */
        do_action( 'dokan_pro_refund_approved', $this );

        return $this;
    }

    /**
     * Cancel a refund request
     *
     * @since 3.0.0
     *
     * @return \WeDevs\DokanPro\Refund\Refund
     */
    public function cancel() {
        $this->set_status( dokan_pro()->refund->get_status_code( 'cancelled' ) );

        $refund = $this->save();

        if ( is_wp_error( $refund ) ) {
            return $refund;
        }

        /**
         * Fires after cancel a refund request
         *
         * @since 3.0.0
         *
         * @param \WeDevs\DokanPro\Refund\Refund $refund
         */
        do_action( 'dokan_pro_refund_cancelled', $this );

        return $this;
    }
}
