<?php

/**
 * Dokan Dahsboard Withdraw class
 *
 * @author weDevs
 *
 * @since 2.4
 *
 * @package dokan
 */
class Dokan_Template_Withdraw extends Dokan_Withdraw {

    public static $validate;

    public function __construct() {
        add_action( 'template_redirect', array( $this, 'handle_withdraws' ) );
        add_action( 'dokan_withdraw_content_area_header', array( $this, 'dokan_withdraw_header_render' ), 10 );
    }

    /**
     * Initializes the Dokan_Template_Withdraw class
     *
     * Checks for an existing Dokan_Template_Withdraw instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new Dokan_Template_Withdraw();
        }

        return $instance;
    }

    public function dokan_withdraw_header_render() {
        dokan_get_template_part( 'withdraw/header' );
    }

    /**
     * Handle Withdraw form submission
     *
     * @return void
     */
    function handle_withdraws() {
        // Withdraw functionality
        self::$validate = $this->validate();

        if ( self::$validate !== false && !is_wp_error( self::$validate ) ) {
            $this->insert_withdraw_info();
        }

        $this->cancel_pending();
    }


    /**
     * Cancel an withdraw request
     *
     * @return void
     */
    function cancel_pending() {

        if ( isset( $_GET['action'] ) && $_GET['action'] == 'dokan_cancel_withdrow' ) {

            if ( !wp_verify_nonce( $_GET['_wpnonce'], 'dokan_cancel_withdrow' ) ) {
                wp_die( __( 'Are you cheating?', 'dokan' ) );
            }

            global $current_user, $wpdb;

            $row_id = absint( $_GET['id'] );

            $this->update_status( $row_id, $current_user->ID, 2 );

            wp_redirect( add_query_arg( array( 'message' => 'request_cancelled' ), dokan_get_navigation_url( 'withdraw' ) ) );
        }
    }

    /**
     * Validate an withdraw request
     *
     * @return void
     */
    function validate() {

        if ( !isset( $_POST['withdraw_submit'] ) ) {
            return false;
        }

        if ( !wp_verify_nonce( $_POST['dokan_withdraw_nonce'], 'dokan_withdraw' ) ) {
            wp_die( __( 'Are you cheating?', 'dokan' ) );
        }

        $error           = new WP_Error();
        $limit           = $this->get_withdraw_limit();
        $balance         = dokan_get_seller_balance( get_current_user_id(), false );
        $withdraw_amount = (float) $_POST['witdraw_amount'];

        if ( empty( $_POST['witdraw_amount'] ) ) {
            $error->add( 'dokan_empty_withdrad', __( 'Withdraw amount required ', 'dokan' ) );
        } elseif ( $withdraw_amount > $balance ) {

            $error->add( 'enough_balance', __( 'You don\'t have enough balance for this request', 'dokan' ) );
        } elseif ( $withdraw_amount < $limit ) {
            $error->add( 'dokan_withdraw_amount', sprintf( __( 'Withdraw amount must be greater than %d', 'dokan' ), $this->get_withdraw_limit() ) );
        }

        if ( empty( $_POST['withdraw_method'] ) ) {
            $error->add( 'dokan_withdraw_method', __( 'withdraw method required', 'dokan' ) );
        }

        if ( $error->get_error_codes() ) {
            return $error;
        }

        return true;
    }

    /**
     * Insert withdraw info
     *
     * @return void
     */
    function insert_withdraw_info() {

        global $current_user, $wpdb;

        $amount = floatval( $_POST['witdraw_amount'] );
        $method = $_POST['withdraw_method'];

        $data_info = array(
            'user_id' => $current_user->ID,
            'amount'  => $amount,
            'status'  => 0,
            'method'  => $method,
            'ip'      => dokan_get_client_ip(),
            'notes'   => ''
        );

        $update = $this->insert_withdraw( $data_info );
        Dokan_Email::init()->new_withdraw_request( $current_user, $amount, $method );

        wp_redirect( add_query_arg( array( 'message' => 'request_success' ), dokan_get_navigation_url( 'withdraw' ) ) );
    }

    /**
     * List withdraw request for a user
     *
     * @param  int  $user_id
     *
     * @return void
     */
    function withdraw_requests( $user_id ) {
        $withdraw_requests = $this->get_withdraw_requests( $user_id );

        if ( $withdraw_requests ) {
            ?>
            <table class="table table-striped">
                <tr>
                    <th><?php _e( 'Amount', 'dokan' ); ?></th>
                    <th><?php _e( 'Method', 'dokan' ); ?></th>
                    <th><?php _e( 'Date', 'dokan' ); ?></th>
                    <th><?php _e( 'Cancel', 'dokan' ); ?></th>
                    <th><?php _e( 'Status', 'dokan' ); ?></th>
                </tr>

                <?php foreach ( $withdraw_requests as $request ) { ?>

                    <tr>
                        <td><?php echo wc_price( $request->amount ); ?></td>
                        <td><?php echo dokan_withdraw_get_method_title( $request->method ); ?></td>
                        <td><?php echo dokan_format_time( $request->date ); ?></td>
                        <td>
                            <?php
                            $url = add_query_arg( array(
                                'action' => 'dokan_cancel_withdrow',
                                'id'     => $request->id
                            ), dokan_get_navigation_url( 'withdraw' ) );
                            ?>
                            <a href="<?php echo wp_nonce_url( $url, 'dokan_cancel_withdrow' ); ?>">
                                <?php _e( 'Cancel', 'dokan' ); ?>
                            </a>
                        </td>
                        <td><?php echo $this->request_status( $request->status ); ?></td>
                    </tr>

                <?php } ?>

            </table>
            <?php
        }
    }

    /**
     * Show alert messages
     *
     * @return void
     */
    function show_alert_messages() {
        $type    = isset( $_GET['message'] ) ? $_GET['message'] : '';
        $message = '';

        switch ( $type ) {
            case 'request_cancelled':
                $message = __( 'Your request has been cancelled successfully!', 'dokan' );
                break;

            case 'request_success':
                $message = __( 'Your request has been received successfully and is under review!', 'dokan' );
                break;

            case 'request_error':
                $message = __( 'Unknown error!', 'dokan' );
                break;
        }

        if ( ! empty( $message ) ) {
            ?>
            <div class="dokan-alert dokan-alert-danger">
                <button type="button" class="dokan-close" data-dismiss="alert">&times;</button>
                <strong><?php echo $message; ?></strong>
            </div>
            <?php
        }
    }

    /**
     * Generate withdraw request form
     *
     * @param  string  $validate
     *
     * @return void
     */
    function withdraw_form( $validate = '' ) {
        global $current_user;

        // show alert messages
        $this->show_alert_messages();

        $balance = $this->get_user_balance( $current_user->ID );
        if ( $balance < 0 ) {
            printf( '<div class="dokan-alert dokan-alert-danger">%s</div>', sprintf( __( 'You already withdrawed %s. This amount will deducted from your next balance.', 'dokan' ), wc_price( $balance ) ) );
        }

        if ( $this->has_pending_request( $current_user->ID ) ) {
            ?>
            <div class="dokan-alert dokan-alert-warning">
                <p><strong><?php _e( 'You\'ve already pending withdraw request(s).', 'dokan' ); ?></strong></p>
                <p><?php _e( 'Until it\'s been cancelled or approved, you can\'t submit any new request.', 'dokan' ) ?></p>
            </div>

            <?php
            $this->withdraw_requests( $current_user->ID );
            return;

        } else if ( !$this->has_withdraw_balance( $current_user->ID ) ) {

            printf( '<div class="dokan-alert dokan-alert-danger">%s</div>', __( 'You don\'t have sufficient balance for a withdraw request!', 'dokan' ) );

            return;
        }

        $payment_methods = dokan_withdraw_get_active_methods();

        if ( is_wp_error( $validate ) ) {
            $amount          = $_POST['witdraw_amount'];
            $withdraw_method = $_POST['withdraw_method'];
        } else {
            $amount          = '';
            $withdraw_method = '';
        }
        ?>
        <div class="dokan-alert dokan-alert-danger" style="display: none;">
            <button type="button" class="dokan-close" data-dismiss="alert">&times;</button>
            <strong class="jquery_error_place"></strong>
        </div>

        <span class="ajax_table_shown"></span>
        <form class="dokan-form-horizontal withdraw" role="form" method="post">

            <div class="dokan-form-group">
                <label for="withdraw-amount" class="dokan-w3 dokan-control-label">
                    <?php _e( 'Withdraw Amount', 'dokan' ); ?>
                </label>

                <div class="dokan-w5 dokan-text-left">
                    <div class="dokan-input-group">
                        <span class="dokan-input-group-addon"><?php echo get_woocommerce_currency_symbol(); ?></span>
                        <input name="witdraw_amount" required number min="<?php echo esc_attr( dokan_get_option( 'withdraw_limit', 'dokan_selling', 50 ) ); ?>" class="dokan-form-control" id="withdraw-amount" name="price" type="number" placeholder="0.00" value="<?php echo $amount; ?>"  >
                    </div>
                </div>
            </div>

            <div class="dokan-form-group">
                <label for="withdraw-method" class="dokan-w3 dokan-control-label">
                    <?php _e( 'Payment Method', 'dokan' ); ?>
                </label>

                <div class="dokan-w5 dokan-text-left">
                    <select class="dokan-form-control" required name="withdraw_method" id="withdraw-method">
                        <?php foreach ( $payment_methods as $method_name ) { ?>
                            <option <?php selected( $withdraw_method, $method_name );  ?>value="<?php echo esc_attr( $method_name ); ?>"><?php echo dokan_withdraw_get_method_title( $method_name ); ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <div class="dokan-form-group">
                <div class="dokan-w3 ajax_prev" style="margin-left:23%; width: 200px;">
                    <?php wp_nonce_field( 'dokan_withdraw', 'dokan_withdraw_nonce' ); ?>
                    <input type="submit" class="dokan-btn dokan-btn-theme" value="<?php esc_attr_e( 'Submit Request', 'dokan' ); ?>" name="withdraw_submit">
                </div>
            </div>
        </form>
        <?php
    }

    /**
     * Print the approved user withdraw requests
     *
     * @param  int  $user_id
     *
     * @return void
     */
    function user_approved_withdraws( $user_id ) {
        $requests = $this->get_withdraw_requests( $user_id, 1, 100 );

        if ( $requests ) {
            ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th><?php _e( 'Amount', 'dokan' ); ?></th>
                        <th><?php _e( 'Method', 'dokan' ); ?></th>
                        <th><?php _e( 'Date', 'dokan' ); ?></th>
                    </tr>
                </thead>
                <tbody>

                <?php foreach ( $requests as $row ) { ?>
                    <tr>
                        <td><?php echo wc_price( $row->amount ); ?></td>
                        <td><?php echo dokan_withdraw_get_method_title( $row->method ); ?></td>
                        <td><?php echo date_i18n( 'M j, Y g:ia', strtotime( $row->date ) ); ?></td>
                    </tr>
                <?php } ?>

                </tbody>
            </table>

        <?php } else { ?>
            <div class="dokan-alert dokan-alert-warning">
                <strong><?php _e( 'Error!', 'dokan' ); ?></strong> <?php _e( 'Sorry, no transactions found!', 'dokan' ); ?>
            </div>
            <?php
        }
    }

}
