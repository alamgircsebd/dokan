<?php

namespace WeDevs\DokanPro\Refund;

class Refunds {

    /**
     * Query arguments
     *
     * @var array
     */
    protected $args = [];

    /**
     * Refund results
     *
     * @var array
     */
    protected $refunds = [];

    /**
     * Total refund found
     *
     * @var null|int
     */
    protected $total = null;

    /**
     * Maximum number of pages
     *
     * @var null|int
     */
    protected $max_num_pages = null;

    /**
     * Class constructor
     *
     * @since DOKAN_PRO_SINCE
     *
     * @param array $args
     *
     * @return void
     */
    public function __construct( $args = [] ) {
        $defaults = [
            'limit'         => 10,
            'page'          => 1,
            'no_found_rows' => false,
        ];

        $this->args = wp_parse_args( $args, $defaults );
        $this->query();
    }

    /**
     * Get refunds
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return array
     */
    public function get_refunds() {
        return $this->refunds;
    }

    /**
     * Query refunds
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return \WeDevs\Dokan\Refund\Refunds
     */
    public function query() {
        global $wpdb;

        $args = $this->args;

        // @note: empty variables may use in future
        $fields = '*';
        $join = '';
        $where = '';
        $groupby = '';
        $orderby = '';
        $limits = '';
        $query_args = [ 1, 1 ];

        if ( isset( $args['ids'] ) && is_array( $args['ids'] ) ) {
            $ids = array_map( 'absint', $args['ids'] );
            $ids = array_filter( $ids );

            $placeholders = [];
            foreach ( $ids as $id ) {
                $placeholders[] = '%d';
                $query_args[] = $id;
            }

            $where .= ' and id in ( ' . implode( ',', $placeholders ) . ' )';
        }

        if ( isset( $args['order_id'] ) ) {
            $where       .= ' and order_id = %d';
            $query_args[] = $args['order_id'];
        }

        if ( isset( $args['seller_id'] ) ) {
            $where       .= ' and seller_id = %d';
            $query_args[] = $args['seller_id'];
        }

        if ( isset( $args['refund_amount'] ) ) {
            $where       .= ' and refund_amount = %s';
            $query_args[] = $args['refund_amount'];
        }

        if ( isset( $args['refund_reason'] ) ) {
            $where .= ' and refund_reason = %s';
            $query_args[] = $args['refund_reason'];
        }

        if ( isset( $args['date'] ) ) {
            $where .= ' and date = %s';
            $query_args[] = $args['date'];
        }

        if ( isset( $args['status'] ) ) {
            $where .= ' and status = %d';
            $query_args[] = $args['status'];
        }

        if ( ! empty( $args['limit'] ) ) {
            $limit  = absint( $args['limit'] );
            $page   = absint( $args['page'] );
            $page   = $page ? $page : 1;
            $offset = ( $page - 1 ) * $limit;

            $limits       = 'LIMIT %d, %d';
            $query_args[] = $offset;
            $query_args[] = $limit;
        }

        $found_rows = '';
        if ( ! $args['no_found_rows'] && ! empty( $limits ) ) {
            $found_rows = 'SQL_CALC_FOUND_ROWS';
        }

        $refunds = $wpdb->get_results( $wpdb->prepare(
            "SELECT $found_rows $fields FROM {$wpdb->dokan_refund} $join WHERE %d=%d $where $groupby $orderby $limits",
            ...$query_args
        ), ARRAY_A );

        if ( ! empty( $refunds ) ) {
            foreach ( $refunds as $refund ) {
                $this->refunds[] = new Refund( $refund );
            }
        }

        return $this;
    }

    /**
     * Get total number of refunds
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return int
     */
    public function get_total() {
        global $wpdb;

        if ( ! isset( $this->total ) ) {
            $this->total = absint( $wpdb->get_var( "SELECT FOUND_ROWS()" ) );
        }

        return $this->total;
    }

    /**
     * Get maximum number of pages
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return int
     */
    public function get_maximum_num_pages() {
        $total = $this->get_total();

        if ( ! $this->max_num_pages && $total && ! empty( $this->args['limit'] ) ) {
            $limit = absint( $this->args['limit'] );
            $this->max_num_pages = ceil( $total / $limit );
        }

        return $this->max_num_pages;
    }
}
