<div class="dokan-dashboard-wrap">
    <?php dokan_get_template( 'dashboard-nav.php', array( 'active_menu' => 'order' ) ); ?>

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
                ?>
                <form action="" method="GET" class="dokan-left">
                    <div class="dokan-form-group">
                        <label for="from">Date:</label> <input type="text" class="datepicker" name="order_date" id="order_date_filter" value="<?php echo isset( $_GET['order_date'] ) ? sanitize_key( $_GET['order_date'] ) : ''; ?>">
                        <input type="submit" name="dokan_order_filter" value="Filter">
                        <input type="hidden" name="order_status" value="<?php echo isset( $_GET['order_status'] ) ? sanitize_key( $_GET['order_status'] ) : 'all'; ?>">
                    </div>
                </form>

                <form action="" method="POST" class="dokan-right">
                    <div class="dokan-form-group">
                        <input type="submit" name="dokan_order_export_all" value="Export All">
                        <input type="submit" name="dokan_order_export_filtered" value="Export Filtered">
                        <input type="hidden" name="order_date" value="<?php echo isset( $_GET['order_date'] ) ? sanitize_key( $_GET['order_date'] ) : ''; ?>">
                        <input type="hidden" name="order_status" value="<?php echo isset( $_GET['order_status'] ) ? sanitize_key( $_GET['order_status'] ) : 'all'; ?>">
                    </div>
                </form>

                <div class="dokan-clearfix"></div>

                <?php
                dokan_get_template_part( 'orders/listing' );
            }
            ?>

        </article>
    </div> <!-- #primary .content-area -->
</div><!-- .dokan-dashboard-wrap -->