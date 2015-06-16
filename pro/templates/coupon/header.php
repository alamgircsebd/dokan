<?php
/**
 *  Dahboard Coupon Header Template
 *
 *  @since 2.4
 *
 *  @package dokan
 */
?>
<header class="dokan-dashboard-header dokan-clearfix">
    <span class="left-header-content dokan-left">
        <h1 class="entry-title">
            <?php _e( 'Coupon', 'dokan' ); ?>
        <?php if ( $is_edit_page ) {
            printf( '<small> - %s</small>', __( 'Edit Coupon', 'dokan' ) );
        } ?>
        </h1>
    </span>

    <?php if ( !$is_edit_page ) { ?>
        <span class="left-header-content dokan-right">
            <a href="<?php echo add_query_arg( array( 'view' => 'add_coupons'), dokan_get_navigation_url( 'coupons' ) ); ?>" class="dokan-btn dokan-btn-theme dokan-right"><i class="fa fa-gift">&nbsp;</i> <?php _e( 'Add new Coupon', 'dokan' ); ?></a>
        </span>
    <?php } ?>
</header><!-- .entry-header -->
