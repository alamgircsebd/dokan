<?php
$user_id        = get_current_user_id();
$orders_counts  = dokan_count_orders( $user_id );
$post_counts    = dokan_count_posts( 'product', $user_id );
$comment_counts = dokan_count_comments( 'product', $user_id );
$pageviews      = (int) dokan_author_pageviews( $user_id );
$earning        = dokan_author_total_sales( $user_id );

$products_url   = dokan_get_navigation_url( 'products' );
$orders_url     = dokan_get_navigation_url( 'orders' );
$reviews_url    = dokan_get_navigation_url( 'reviews' );
?>

<div class="dokan-dashboard-wrap">

    <?php dokan_get_template( 'dashboard-nav.php', array( 'active_menu' => 'dashboard' ) ); ?>

    <div class="dokan-dashboard-content">

        <?php
        if ( ! dokan_is_seller_enabled( $user_id ) ) {
            dokan_seller_not_enabled_notice();
        }
        ?>

        <article class="dashboard-content-area">

            <div class="dokan-w6 dokan-dash-left">
                <div class="dashboard-widget big-counter">
                    <ul class="list-inline">
                        <li>
                            <div class="title"><?php _e( 'Pageview', 'dokan' ); ?></div>
                            <div class="count"><?php echo dokan_number_format( $pageviews ); ?></div>
                        </li>
                        <li>
                            <div class="title"><?php _e( 'Order', 'dokan' ); ?></div>
                            <div class="count">
                                <?php
                                $total = $orders_counts->{'wc-completed'} + $orders_counts->{'wc-processing'} + $orders_counts->{'wc-on-hold'};
                                echo number_format_i18n( $total, 0 );
                                ?>
                            </div>
                        </li>
                        <li>
                            <div class="title"><?php _e( 'Sales', 'dokan' ); ?></div>
                            <div class="count"><?php echo woocommerce_price( $earning ); ?></div>
                        </li>
                    </ul>
                </div> <!-- .big-counter -->

                <div class="dashboard-widget orders">
                    <div class="widget-title"><i class="fa fa-shopping-cart"></i> Orders</div>

                    <?php
                    $order_data = array(
                        array( 'value' => $orders_counts->{'wc-completed'}, 'color' => '#73a724'),
                        array( 'value' => $orders_counts->{'wc-pending'}, 'color' => '#999'),
                        array( 'value' => $orders_counts->{'wc-processing'}, 'color' => '#21759b'),
                        array( 'value' => $orders_counts->{'wc-cancelled'}, 'color' => '#d54e21'),
                        array( 'value' => $orders_counts->{'wc-refunded'}, 'color' => '#e6db55'),
                        array( 'value' => $orders_counts->{'wc-on-hold'}, 'color' => '#f0ad4e'),
                    );
                    ?>

                    <div class="content-half-part">
                        <ul class="list-unstyled list-count">
                            <li>
                                <a href="<?php echo $orders_url; ?>">
                                    <span class="title"><?php _e( 'Total', 'dokan' ); ?></span> <span class="count"><?php echo $orders_counts->total; ?></span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo add_query_arg( array( 'order_status' => 'wc-completed' ), $orders_url ); ?>" style="color: <?php echo $order_data[0]['color']; ?>">
                                    <span class="title"><?php _e( 'Completed', 'dokan' ); ?></span> <span class="count"><?php echo number_format_i18n( $orders_counts->{'wc-completed'}, 0 ); ?></span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo add_query_arg( array( 'order_status' => 'wc-pending' ), $orders_url ); ?>" style="color: <?php echo $order_data[1]['color']; ?>">
                                    <span class="title"><?php _e( 'Pending', 'dokan' ); ?></span> <span class="count"><?php echo number_format_i18n( $orders_counts->{'wc-pending'}, 0 );; ?></span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo add_query_arg( array( 'order_status' => 'wc-processing' ), $orders_url ); ?>" style="color: <?php echo $order_data[2]['color']; ?>">
                                    <span class="title"><?php _e( 'Processing', 'dokan' ); ?></span> <span class="count"><?php echo number_format_i18n( $orders_counts->{'wc-processing'}, 0 );; ?></span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo add_query_arg( array( 'order_status' => 'wc-cancelled' ), $orders_url ); ?>" style="color: <?php echo $order_data[3]['color']; ?>">
                                    <span class="title"><?php _e( 'Cancelled', 'dokan' ); ?></span> <span class="count"><?php echo number_format_i18n( $orders_counts->{'wc-cancelled'}, 0 ); ?></span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo add_query_arg( array( 'order_status' => 'wc-refunded' ), $orders_url ); ?>" style="color: <?php echo $order_data[4]['color']; ?>">
                                    <span class="title"><?php _e( 'Refunded', 'dokan' ); ?></span> <span class="count"><?php echo number_format_i18n( $orders_counts->{'wc-refunded'}, 0 ); ?></span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo add_query_arg( array( 'order_status' => 'wc-on-hold' ), $orders_url ); ?>" style="color: <?php echo $order_data[5]['color']; ?>">
                                    <span class="title"><?php _e( 'On hold', 'dokan' ); ?></span> <span class="count"><?php echo number_format_i18n( $orders_counts->{'wc-on-hold'}, 0 ); ?></span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="content-half-part">
                        <canvas id="order-stats"></canvas>
                    </div>
                </div> <!-- .orders -->

                <div class="dashboard-widget reviews">
                    <div class="widget-title"><i class="fa fa-comments"></i> Reviews</div>

                    <ul class="list-unstyled list-count">
                        <li>
                            <a href="<?php echo $reviews_url; ?>">
                                <span class="title"><?php _e( 'All', 'dokan' ); ?></span> <span class="count"><?php echo $comment_counts->total; ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo add_query_arg( array( 'comment_status' => 'hold' ), $reviews_url ); ?>">
                                <span class="title"><?php _e( 'Pending', 'dokan' ); ?></span> <span class="count"><?php echo $comment_counts->moderated; ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo add_query_arg( array( 'comment_status' => 'spam' ), $reviews_url ); ?>">
                                <span class="title"><?php _e( 'Spam', 'dokan' ); ?></span> <span class="count"><?php echo $comment_counts->spam; ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo add_query_arg( array( 'comment_status' => 'trash' ), $reviews_url ); ?>">
                                <span class="title"><?php _e( 'Trash', 'dokan' ); ?></span> <span class="count"><?php echo $comment_counts->trash; ?></span>
                            </a>
                        </li>
                    </ul>
                </div> <!-- .reviews -->

            </div> <!-- .col-md-6 -->

            <div class="dokan-w6 dokan-dash-right">
                <div class="dashboard-widget sells-graph">
                    <div class="widget-title"><i class="fa fa-credit-card"></i> <?php _e( 'Sales', 'dokan' ); ?></div>

                    <?php
                    require_once DOKAN_DIR . '/includes/reports.php';

                    dokan_dashboard_sales_overview();
                    ?>
                </div> <!-- .sells-graph -->


                <div class="dashboard-widget products">
                    <div class="widget-title">
                        <i class="icon-briefcase"></i> <?php _e( 'Products', 'dokan' ); ?>

                        <span class="pull-right">
                            <a href="<?php echo dokan_get_navigation_url( 'new-product' ); ?>" class="btn btn-theme btn-sm"><?php _e( '+ Add new product', 'dokan' ); ?></a>
                        </span>
                    </div>

                    <ul class="list-unstyled list-count">
                        <li>
                            <a href="<?php echo $products_url; ?>">
                                <span class="title"><?php _e( 'Total', 'dokan' ); ?></span> <span class="count"><?php echo $post_counts->total; ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo add_query_arg( array( 'post_status' => 'publish' ), $products_url ); ?>">
                                <span class="title"><?php _e( 'Live', 'dokan' ); ?></span> <span class="count"><?php echo $post_counts->publish; ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo add_query_arg( array( 'post_status' => 'draft' ), $products_url ); ?>">
                                <span class="title"><?php _e( 'Offline', 'dokan' ); ?></span> <span class="count"><?php echo $post_counts->draft; ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo add_query_arg( array( 'post_status' => 'pending' ), $products_url ); ?>">
                                <span class="title"><?php _e( 'Pending Review', 'dokan' ); ?></span> <span class="count"><?php echo $post_counts->pending; ?></span>
                            </a>
                        </li>
                    </ul>
                </div> <!-- .products -->

            </div>
        </article><!-- .dashboard-content-area -->
    </div><!-- .dokan-dashboard-content -->
</div><!-- .dokan-dashboard-wrap -->

<script type="text/javascript">
    jQuery(function($) {
        var order_stats = <?php echo json_encode( $order_data ); ?>;

        var ctx = $("#order-stats").get(0).getContext("2d");
        new Chart(ctx).Doughnut(order_stats);
    });
</script>