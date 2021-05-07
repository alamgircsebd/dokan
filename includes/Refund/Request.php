<?php

namespace WeDevs\DokanPro\Refund;

use ArrayAccess;
use WP_Error;

/**
 * A helper class to mimic WP_REST_Request
 *
 * Useful when a request comes from other than REST, for example Ajax request.
 *
 * @see \WeDevs\DokanPro\Refund\Ajax for usage
 */
class Request implements ArrayAccess {

    /**
     * The refund model
     *
     * @since 3.0.0
     *
     * @var null|\WeDevs\DokanPro\Refund\Refund
     */
    protected $model = null;

    /**
     * Required params
     *
     * @since 3.0.0
     *
     * @var null|array
     */
    protected $required = null;

    /**
     * Errors during request process
     *
     * @since 3.0.0
     *
     * @var null|array
     */
    protected $error = null;

    /**
     * Class constructor
     *
     * @since 3.0.0
     *
     * @param array $data
     */
    public function __construct( $data ) {
        $this->model = new Refund( $data );
    }

    /**
     * ArrayAccess method override
     *
     * @since 3.0.0
     *
     * @param string $offset
     *
     * @return bool
     */
    public function offsetExists( $offset ) {
        $data = $this->get_params();
        return isset( $data[ $offset ] );
    }

    /**
     * ArrayAccess offset method override
     *
     * @since 3.0.0
     *
     * @param string $offset
     * @param mixed  $value
     *
     * @return void
     */
    public function offsetSet( $offset, $value ) {
        $this->set_param( $offset, $value );
    }

    /**
     * ArrayAccess method override
     *
     * @since 3.0.0
     *
     * @param string $offset
     *
     * @return mixed
     */
    public function offsetGet( $offset ) {
        return $this->get_param( $offset );
    }

    /**
     * ArrayAccess method override
     *
     * @since 3.0.0
     *
     * @param string $offset
     *
     * @return void
     */
    public function offsetUnset( $offset ) {
        // not using this method here!
    }

    /**
     * Get refund model
     *
     * @since 3.0.0
     *
     * @return \WeDevs\DokanPro\Refund\Refund
     */
    public function get_model() {
        return $this->model;
    }

    /**
     * Get model data
     *
     * @since 3.0.0
     *
     * @return array
     */
    public function get_params() {
        return $this->model->get_data();
    }

    /**
     * Set model param/data
     *
     * @since 3.0.0
     *
     * @param string $param
     * @param mixed  $value
     *
     * @return void
     */
    public function set_param( $param, $value ) {
        $setter = "set_$param";
        $this->model->$setter( $value );
    }

    /**
     * Get a model param value
     *
     * @since 3.0.0
     *
     * @param string $param
     *
     * @return mixed
     */
    public function get_param( $param ) {
        $data = $this->get_params();

        if ( isset( $data[ $param ] ) ) {
            return $data[ $param ];
        }

        return null;
    }

    /**
     * Add error
     *
     * @since 3.0.0
     *
     * @param \WP_Error $error
     *
     * @return void
     */
    protected function add_error( $error ) {
        if ( ! $this->has_error() ) {
            $this->error = new WP_Error();
        }

        $this->error->add( $error->get_error_code(), $error->get_error_message(), $error->get_error_data() );
    }

    /**
     * Set required fields/params
     *
     * @since 3.0.0
     *
     * @param array $required
     *
     * @return void
     */
    public function set_required( $required ) {
        $this->required = $required;
    }

    /**
     * Checks if Request has any error
     *
     * @since 3.0.0
     *
     * @return bool
     */
    public function has_error() {
        return ! is_null( $this->get_error() );
    }

    /**
     * Get request error
     *
     * @since 3.0.0
     *
     * @return \WP_Error
     */
    public function get_error() {
        return $this->error;
    }

    /**
     * Validate a request
     *
     * @since 3.0.0
     *
     * @return void
     */
    public function validate() {
        $data = $this->model->get_data();

        if ( is_array( $this->required ) ) {
            $missing_required = [];

            foreach ( $this->required as $param ) {
                if ( empty( $data[ $param ] ) ) {
                    $missing_required[] = $param;
                }
            }

            if ( $missing_required ) {
                $this->add_error( new WP_Error(
                    'dokan_pro_missing_params',
                    sprintf( __( 'Missing parameter(s): %s', 'dokan' ), implode( ', ', $missing_required ) ),
                    [
                        'status' => 400,
                        'params' => $missing_required,
                    ]
                ) );

                return;
            }
        }

        $this->validate_refund_amount( $data );

        foreach ( $data as $param => $value ) {
            $method = "validate_$param";

            if ( method_exists( Validator::class, $method ) ) {
                $validate = Validator::$method( $value, $this, $param );

                if ( is_wp_error( $validate ) ) {
                    $this->add_error( $validate );
                }
            }
        }
    }

    /**
     * Sanitize a request
     *
     * @since 3.0.0
     *
     * @return void
     */
    public function sanitize() {
        $data = $this->model->get_data();

        foreach ( $data as $param => $value ) {
            $method = "sanitize_$param";

            if ( method_exists( Sanitizer::class, $method ) ) {
                $sanitized = Sanitizer::$method( $value, $this, $param );

                if ( is_wp_error( $sanitized ) ) {
                    $this->add_error( $sanitized );
                } else {
                    $this->set_param( $param, $sanitized );
                }
            }
        }
    }

    /**
     * Validate amount for over issue.
     *
     * @param array $data Refund request data.
     *
     * @since 3.2.3
     *
     * @return void
     */
    public function validate_refund_amount( $data ) {
        global $wpdb;
        $order_id                = $data['order_id'];
        $order                   = wc_get_order( $order_id );
        $item_totals_request     = json_decode( $data['item_totals'], true );
        $item_tax_totals_request = json_decode( $data['item_tax_totals'], true );
        $already_refunded        = array();
        $results                 = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}dokan_refund WHERE order_id=%d AND status != %d",
                $order_id,
                2  // 2 is refund status cancel.
            )
        );

        foreach ( $results as $previous_refund ) {
            if ( ! empty( $previous_refund->item_totals ) ) {
                $item_totals_arr = json_decode( $previous_refund->item_totals, true );

                foreach ( $item_totals_arr as $line_item_id => $amount ) {
                    $already_refunded[ $line_item_id ]['item_totals'] = ( isset( $already_refunded[ $line_item_id ]['item_totals'] ) ) ? ( (float) $already_refunded[ $line_item_id ]['item_totals'] + (float) $amount ) : (float) $amount;
                }
            }

            if ( ! empty( $previous_refund->item_tax_totals ) ) {
                $item_tax_totals_arr = json_decode( $previous_refund->item_tax_totals, true );

                foreach ( $item_tax_totals_arr as $line_item_id => $amount_array ) {
                    $already_refunded[ $line_item_id ]['item_tax_totals'] = ( isset( $already_refunded[ $line_item_id ]['item_tax_totals'] ) ) ? ( (float) $already_refunded[ $line_item_id ]['item_tax_totals'] + (float) array_sum( $amount_array ) ) : (float) array_sum( $amount_array );
                }
            }
            unset( $item_totals_arr );
            unset( $item_tax_totals_arr );
        }

        foreach ( $order->get_items( array( 'line_item', 'shipping', 'fee' ) ) as $order_line_item_id => $order_line_item ) {
            $refunded_amount = ( isset( $already_refunded[ $order_line_item_id ]['item_totals'] ) && ! empty( $already_refunded[ $order_line_item_id ]['item_totals'] ) ) ? $already_refunded[ $order_line_item_id ]['item_totals'] : 0.00;
            $refunded_tax    = ( isset( $already_refunded[ $order_line_item_id ]['item_tax_totals'] ) && ! empty( $already_refunded[ $order_line_item_id ]['item_tax_totals'] ) ) ? $already_refunded[ $order_line_item_id ]['item_tax_totals'] : 0.00;

            if ( isset( $item_totals_request[ $order_line_item_id ] ) && ( $order_line_item->get_total() < ( $refunded_amount + wc_format_decimal( $item_totals_request[ $order_line_item_id ] ) ) ) ) {
                $this->add_error(
                    new WP_Error(
                        'dokan_pro_refund_error_excess_refund',
                        __( 'Refund amount is more than permitted amount.', 'dokan' )
                    )
                );
            }

            if ( isset( $item_totals_request[ $order_line_item_id ] ) && ( wc_format_decimal( $item_totals_request[ $order_line_item_id ] < 0 ) ) ) {
                $this->add_error(
                    new WP_Error(
                        'dokan_pro_refund_error_negative_refund_amount',
                        __( 'Refund amount is negative number.', 'dokan' )
                    )
                );
            }

            if ( isset( $item_tax_totals_request[ $order_line_item_id ] ) && ( $order_line_item->get_total_tax() < ( $refunded_tax + (float) array_sum( $item_tax_totals_request[ $order_line_item_id ] ) ) ) ) {
                $this->add_error(
                    new WP_Error(
                        'dokan_pro_refund_error_tax_excess_refund',
                        __( 'Refund tax amount is more than permitted amount.', 'dokan' )
                    )
                );
            }

            if ( isset( $item_tax_totals_request[ $order_line_item_id ] ) && ( min( $item_tax_totals_request[ $order_line_item_id ] ) < 0 ) ) {
                $this->add_error(
                    new WP_Error(
                        'dokan_pro_refund_error_negative_refund_tax_amount',
                        __( 'Refund tax amount is negative number.', 'dokan' )
                    )
                );
            }
        }
    }
}
