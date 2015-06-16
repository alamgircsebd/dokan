<?php
/**
 *  Dokan Dashboard Orders Template
 *
 *  Load order related template
 *
 *  @since 2.4
 *
 *  @package dokan
 */
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
        do_action( 'dokan_order_content_before' );

    ?>

    <div class="dokan-dashboard-content dokan-orders-content">

        <?php

            /**
             *  dokan_dashboard_content_before hook
             *
             *  @hooked get_dashboard_side_navigation
             *
             *  @since 2.4
             */
            do_action( 'dokan_dashboard_content_inside_before' );
            do_action( 'dokan_order_content_inside_before' );
        ?>


        <article class="dokan-orders-area">

            <?php

                /**
                 *  dokan_order_inside_content Hook
                 *
                 *  @hooked dokan_order_listing_status_filter
                 *  @hooked dokan_order_main_content
                 *
                 *  @since 2.4
                 */
                do_action( 'dokan_order_inside_content' );

            ?>

        </article>
    </div> <!-- #primary .content-area -->
</div><!-- .dokan-dashboard-wrap -->