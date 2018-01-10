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

        <header class="dokan-dashboard-header">
            <span class="left-header-content">
                <h1 class="entry-title">
                    <?php _e( 'Stuffs', 'dokan' ); ?>

                    <span class="left-header-content dokan-right">
                        <a href="<?php echo add_query_arg( array( 'view' => 'add_stuffs' ), dokan_get_navigation_url( 'stuffs' ) ); ?>" class="dokan-btn dokan-btn-theme dokan-right"><i class="fa fa-user">&nbsp;</i> <?php _e( 'Add new Stuffs', 'dokan' ); ?></a>
                    </span>
                </h1>
            </span>
            <div class="dokan-clearfix"></div>
        </header><!-- .entry-header -->

        <?php

            /**
             *  dokan_stuffs_content_inside_before hook
             *
             *  @hooked show_seller_enable_message
             *
             *  @since 2.4
             */
            do_action( 'dokan_stuffs_content_inside_before' );
        ?>


        <article class="dokan-stuffs-area">

            <?php

                $seller_id    = get_current_user_id();
                $paged        = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;
                $limit        = 10;
                $offset       = ( $paged - 1 ) * $limit;
                $stuffs       = dokan_get_all_vendor_stuffs( array( 'number' => $limit, 'offset' => $offset ) );

                if ( count( $stuffs['stuffs'] ) > 0 ) {
                    ?>
                    <table class="dokan-table dokan-table-striped vendor-stuff-table">
                        <thead>
                            <tr>
                                <th><?php _e( 'Name', 'dokan' ); ?></th>
                                <th><?php _e( 'Email', 'dokan' ); ?></th>
                                <th><?php _e( 'Phone', 'dokan' ); ?></th>
                                <th><?php _e( 'Registered Date', 'dokan' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ( $stuffs['stuffs'] as $stuff ) {
                                ?>
                                <tr >
                                    <td>
                                        <?php
                                            $delete_url =  wp_nonce_url( add_query_arg( array( 'action' => 'delete_stuff', 'stuff_id' => $stuff->ID ), dokan_get_navigation_url( 'stuffs' ) ), 'stuff_delete_nonce', '_stuff_delete_nonce' );
                                            $edit_url   = add_query_arg( array( 'view' => 'add_stuffs', 'action' => 'edit', 'stuff_id' => $stuff->ID ), dokan_get_navigation_url( 'stuffs' ) );
                                        ?>

                                        <?php echo sprintf( '<a href="%s">%s</a>', esc_url( $edit_url ), $stuff->display_name ); ?>
                                        <div class="row-actions">

                                            <?php if ( current_user_can( 'seller' ) ): ?>
                                                <span class="edit"><a href="<?php echo $edit_url; ?>"><?php _e( 'Edit', 'dokan' ); ?></a> | </span>
                                            <?php endif; ?>

                                            <?php if ( current_user_can( 'seller' ) ): ?>
                                                <span class="delete"><a  href="<?php echo $delete_url; ?>"  onclick="return confirm('<?php esc_attr_e( 'Are you sure want to delete', 'dokan' ); ?>');"><?php _e( 'Delete', 'dokan' ); ?></a></span>
                                            <?php endif ?>
                                        </div>
                                    </td>
                                    <td><?php echo $stuff->user_email; ?></td>
                                    <td><?php echo get_user_meta( $stuff->ID, '_stuff_phone', true ); ?></td>
                                    <td><?php echo dokan_date_time_format( $stuff->user_registered ); ?></td>
                                </tr>
                            <?php } ?>

                        </tbody>

                    </table>

                    <?php
                    $user_count = $stuffs['total_users'];
                    $num_of_pages = ceil( $user_count / $limit );

                    $base_url  = dokan_get_navigation_url( 'stuffs' );

                    if ( $num_of_pages > 1 ) {
                        echo '<div class="pagination-wrap">';
                        $page_links = paginate_links( array(
                            'current'   => $paged,
                            'total'     => $num_of_pages,
                            'base'      => $base_url. '%_%',
                            'format'    => '?pagenum=%#%',
                            'add_args'  => false,
                            'type'      => 'array',
                        ) );

                        echo "<ul class='pagination'>\n\t<li>";
                        echo join("</li>\n\t<li>", $page_links);
                        echo "</li>\n</ul>\n";
                        echo '</div>';
                    }
                    ?>

                <?php } else { ?>

                    <div class="dokan-error">
                        <?php _e( 'No Stuffs found', 'dokan' ); ?>
                    </div>

                <?php } ?>

        </article>

        <style>

            table.vendor-stuff-table tbody .row-actions {
                font-size: 12px;
            }
        </style>


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