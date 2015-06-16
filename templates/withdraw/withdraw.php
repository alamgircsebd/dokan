<?php
$user_id        = get_current_user_id();
$dokan_withdraw = Dokan_Template_Withdraw::init();
?>
<div class="dokan-dashboard-wrap">

    <?php

        /**
         *  dokan_dashboard_content_before hook
         *
         *  @hooked get_dashboard_side_navigation
         *
         *  @since 2.4
         */
        do_action( 'dokan_dashboard_content_before' );
    ?>

    <div class="dokan-dashboard-content dokan-withdraw-content">

        <?php

            /**
             *  dokan_withdraw_content_inside_before hook
             *
             *  @since 2.4
             */
            do_action( 'dokan_withdraw_content_inside_before' );
        ?>

        <article class="dokan-withdraw-area">

            <?php
                /**
                 * dokan_withdraw_header_render hook
                 *
                 * @hooked dokan_coupon_header_render
                 *
                 * @since 2.4
                 */
                do_action( 'dokan_withdraw_content_area_header' );
            ?>

            <div class="entry-content">

                <?php
                    /**
                     * dokan_withdraw_header_render hook
                     *
                     * @hooked dokan_coupon_header_render
                     *
                     * @since 2.4
                     */
                    do_action( 'dokan_withdraw_content' );
                ?>

                <?php
                    if ( is_wp_error( Dokan_Template_Withdraw::$validate) ) {
                        $messages = Dokan_Template_Withdraw::$validate->get_error_messages();

                        foreach( $messages as $message ) {
                            ?>
                            <div class="dokan-alert dokan-alert-danger" style="width: 55%; margin-left: 10%;">
                                <button type="button" class="dokan-close" data-dismiss="alert">&times;</button>
                                <strong><?php echo $message; ?></strong>
                            </div>

                            <?php
                        }
                    }
                ?>

                <?php $current = isset( $_GET['type'] ) ? $_GET['type'] : 'pending'; ?>
                <ul class="list-inline subsubsub">
                    <li<?php echo $current == 'pending' ? ' class="active"' : ''; ?>>
                        <a href="<?php echo dokan_get_navigation_url( 'withdraw' ); ?>"><?php _e( 'Withdraw Request', 'dokan' ); ?></a>
                    </li>
                    <li<?php echo $current == 'approved' ? ' class="active"' : ''; ?>>
                        <a href="<?php echo add_query_arg( array( 'type' => 'approved' ), dokan_get_navigation_url( 'withdraw' ) ); ?>"><?php _e( 'Approved Requests', 'dokan' ); ?></a>
                    </li>
                </ul>

                <div class="dokan-alert dokan-alert-warning">
                    <strong><?php printf( __( 'Current Balance: %s', 'dokan' ), dokan_get_seller_balance( $user_id ) ); ?></strong>
                </div>

                <?php if ( $current == 'pending' ) {
                    $dokan_withdraw->withdraw_form( Dokan_Template_Withdraw::$validate );
                } elseif ( $current == 'approved' ) {
                    $dokan_withdraw->user_approved_withdraws( $user_id );
                } ?>

            </div><!-- .entry-content -->

        </article>

        <?php

            /**
             *  dokan_withdraw_content_inside_after hook
             *
             *  @since 2.4
             */
            do_action( 'dokan_withdraw_content_inside_after' );
        ?>
    </div><!-- .dokan-dashboard-content -->

     <?php
        /**
         *  dokan_dashboard_content_after hook
         *
         *  @since 2.4
         */
        do_action( 'dokan_dashboard_content_after' );
    ?>
</div><!-- .dokan-dashboard-wrap -->