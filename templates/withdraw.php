<?php
/**
 * Template Name: Dashboard - Withdraw
 */

// dokan_redirect_login();
// dokan_redirect_if_not_seller();

$user_id = get_current_user_id();
$dokan_withdraw = Dokan_Template_Withdraw::init();

$validate = $dokan_withdraw->validate();

if( $validate !== false && !is_wp_error( $validate ) ) {
    // perform requests
    $dokan_withdraw->insert_withdraw_info();
}

$dokan_withdraw->cancel_pending();

?>

<?php dokan_get_template( dirname(__FILE__) . '/dashboard-nav.php', array('active_menu' => 'withdraw') ); ?>

<div id="primary" class="content-area col-md-10 col-sm-9">
    <div id="content" class="site-content" role="main">

        <article>
            <header class="entry-header">
                <h1 class="entry-title"><?php _e( 'Withdraw', 'dokan' ); ?></h1>
            </header><!-- .entry-header -->

            <div class="entry-content">

                <?php if ( is_wp_error($validate) ) {
                    $messages = $validate->get_error_messages();

                    foreach( $messages as $message ) {
                        ?>
                        <div class="alert alert-danger" style="width: 55%; margin-left: 10%;">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <strong><?php echo $message; ?></strong>
                        </div>

                        <?php
                    }
                } ?>
            </div><!-- .entry-content -->

            <?php $current = isset( $_GET['type'] ) ? $_GET['type'] : 'pending'; ?>
            <ul class="list-inline subsubsub">
                <li<?php echo $current == 'pending' ? ' class="active"' : ''; ?>>
                    <a href="<?php echo dokan_get_navigation_url( 'withdraw' ); ?>"><?php _e( 'Withdraw Request', 'dokan' ); ?></a>
                </li>
                <li<?php echo $current == 'approved' ? ' class="active"' : ''; ?>>
                    <a href="<?php echo add_query_arg( array( 'type' => 'approved' ), dokan_get_navigation_url( 'withdraw' ) ); ?>"><?php _e( 'Approved Requests', 'dokan' ); ?></a>
                </li>
            </ul>

            <div class="alert alert-warning">
                <strong><?php printf( __( 'Current Balance: %s', 'dokan' ), dokan_get_seller_balance( $user_id ) ); ?></strong>
            </div>

            <?php if ( $current == 'pending' ) {
                $dokan_withdraw->withdraw_form( $validate );
            } elseif ( $current == 'approved' ) {
                $dokan_withdraw->user_approved_withdraws( $user_id );
            } ?>

        </article>

    </div><!-- #content .site-content -->
</div><!-- #primary .content-area -->