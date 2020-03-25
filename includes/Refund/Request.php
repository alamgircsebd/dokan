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
}
