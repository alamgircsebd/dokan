<?php
/**
 *  Dokan Dashboard Stuffs Template
 *
 *  Load Stuffs related template
 *
 *  @since 2.4
 *
 *  @package dokan
 */
?>

<?php
$is_edit = ( isset( $_GET['action'] ) && $_GET['action'] == 'edit' && ! empty( $_GET['stuff_id'] ) ) ? $_GET['stuff_id'] : 0;
$first_name = '';
$last_name = '';
$email = '';
$phone = '';
$button_name = ! $is_edit ? __( 'Create Stuff', 'dokan' ) : __( 'Update Stuff', 'dokan' );
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
        do_action( 'dokan_stuffs_content_before' );

    ?>

    <div class="dokan-dashboard-content dokan-stuffs-content">

        <?php

            /**
             *  dokan_stuffs_content_inside_before hook
             *
             *  @hooked show_seller_enable_message
             *
             *  @since 2.4
             */
            do_action( 'dokan_add_stuffs_content_inside_before' );
        ?>

        <header class="dokan-dashboard-header">
            <span class="left-header-content">
                <h1 class="entry-title">
                    <?php
                        if ( !$is_edit ) {
                            _e( 'Add New Stuff', 'dokan' );
                        } else {
                            _e( 'Edit Stuff', 'dokan' );
                        }
                    ?>
                </h1>
            </span>
            <div class="dokan-clearfix"></div>
        </header><!-- .entry-header -->

        <article class="dokan-stuffs-area">
            <?php

                do_action( 'dokan_add_stuff_content' );

            ?>
        </article>


        <?php

            /**
             *  dokan_Stuffs_content_inside_after hook
             *
             *  @since 2.4
             */
            do_action( 'dokan_Stuffs_content_inside_after' );
        ?>

    </div> <!-- #primary .content-area -->

    <?php

        /**
         *  dokan_dashboard_content_after hook
         *  dokan_Stuffs_content_after hook
         *
         *  @since 2.4
         */
        do_action( 'dokan_dashboard_content_after' );
        do_action( 'dokan_Stuffs_content_after' );

    ?>

</div><!-- .dokan-dashboard-wrap -->