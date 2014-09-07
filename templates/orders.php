<?php
/**
 * Template Name: Dashboard - Orders
 */

// dokan_redirect_login();
// dokan_redirect_if_not_seller();

// get_header();
// dokan_frontend_dashboard_scripts();
?>

<?php dokan_get_template( dirname(__FILE__) . '/dashboard-nav.php', array( 'active_menu' => 'order' ) ); ?>

<div class="dokan-dashboard-content dokan-orders-content">

    <article class="dokan-orders-area">

        <?php if ( isset( $_GET['order_id'] ) ) { ?>
            <a href="<?php echo dokan_get_navigation_url( 'orders' ) ; ?>" class="dokan-btn"><?php _e( '&larr; Orders', 'dokan' ); ?></a>
        <?php } else {
            dokan_order_listing_status_filter();
        } ?>

        <?php
        $order_id = isset( $_GET['order_id'] ) ? intval( $_GET['order_id'] ) : 0;

        if ( $order_id ) {
            dokan_get_template_part( 'orders/order-details' );
        } else {
            dokan_get_template_part( 'orders/listing' );
        }
        ?>

    </article>
    
</div> <!-- #primary .content-area -->