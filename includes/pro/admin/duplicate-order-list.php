<div class="wrap">
    <h2><?php _e( 'Duplicate Orders', 'dokan' ); ?></h2>


    <?php
        $pagenum = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
        $limit   = 20;
        $offset  = ( $pagenum - 1 ) * $limit;        
    ?>

        <form method="post" action="" id="dokan-duplicate-orders-action">
            <?php wp_nonce_field( 'dokan_duplicate_orders_bulk_action', 'dokan_duplicate_orders_bulk_action_nonce' ); ?>

            <table class="widefat duplicate-order-table">
                <thead>
                    <tr>
                        <th class="check-column">
                            <input type="checkbox" class="dokan-duplicate-orders-allcheck">
                        </th>
                        <th><?php _e( 'Order No.', 'dokan' ); ?></th>
                        <th><?php _e( 'Seller Name', 'dokan' ); ?></th>                        
                        <th><?php _e( 'Amount', 'dokan' ); ?></th>
                        <th><?php _e( 'Actions', 'dokan' ); ?></th>                       
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th class="check-column">
                            <input type="checkbox" class="dokan-duplicate-orders-allcheck">
                        </th>
                        <th><?php _e( 'Order No.', 'dokan' ); ?></th>
                        <th><?php _e( 'Seller Name', 'dokan' ); ?></th>                        
                        <th><?php _e( 'Amount', 'dokan' ); ?></th>
                        <th><?php _e( 'Actions', 'dokan' ); ?></th>
                    </tr>
                </tfoot>
                <?php
        if ( $duplicate_orders ) {
            $count = 0;
            foreach ( $duplicate_orders as $d_order ) {
                $order = new WC_Order( $d_order->ID );                
            ?>
                    <tr class="<?php echo ( $count % 2 ) == 0 ? 'alternate': 'odd'; ?>" data-order-id="<?php echo $order->id;?>">
                        <th class="check-column">
                            <input type="checkbox" name="id[<?php echo $order->id;?>]" value="<?php echo $order->id;?>">                           
                        </th>    
                        <td class="order_actions" data-colname="Order">
                            <div class="tips">
                                <a href="<?php echo admin_url('post.php?post='.$order->id.'&action=edit')?>" class="row-title">
                                    <strong>#<?php echo $order->id ?></strong>
                                </a><?php  _e( 'by' , 'dokan' ) ?> 
                                <a href="user-edit.php?user_id=<?php echo $order->customer_user; ?>"><?php echo get_user_meta($order->customer_user, 'first_name', true).' '.get_user_meta($order->customer_user, 'last_name', true); ?></a>
                                    <small class="meta email"><a href="mailto:<?php echo get_user_meta($order->customer_user, 'email', true) ?>"><?php echo get_user_meta($order->customer_user, 'email', true) ?></a></small>
                            </div>
                            <strong><?php _e( 'Sub Order of' , 'dokan' ) ?><a href="<?php echo admin_url('post.php?post='.$order->post->post_parent.'&action=edit')?>"> #<?php echo $order->post->post_parent ?></a></strong>
                        </td>
                        
                        
                        <td><?php echo get_user_meta( $order->post->post_author, 'nickname', true ) ?></td>
                       
                        <td class="" data-colname="Total">
                            <?php echo $order->get_formatted_order_total(); ?>
                            <small class="meta">                                
                                <?php echo __( ' via ' , 'dokan' ).$order->payment_method_title; ?>
                            </small>
                        </td>
                        <td class="order_actions" data-colname="Actions">
                            <p>
                                <a class="button tips view" href="<?php echo admin_url('post.php?post='.$order->id.'&action=edit')?>"><?php _e( 'View', 'dokan' ) ?></a>
                                <a class="button tips delete-action dokan-order-action-delete" ><?php _e( 'Delete', 'dokan' ) ?></a>
                            </p>
                        </td>
                        
<!--                        <td><?php //echo date_i18n( 'M j, Y g:ia', strtotime( $row->date ) ); ?></td>-->
                    </tr>
                    <?php
                $count++;
            }

        } else {
            ?>
                <tr>
                    <td colspan="8">
                        <?php _e( 'No results found', 'dokan' ) ?>
                    </td>
                </tr>
                <?php
            }
            ?>
            </table>

            <div class="tablenav bottom">

                <div class="alignleft actions bulkactions">
                    <select name="dokan_withdraw_bulk">
                        <option value="-1" selected="selected"><?php _e( 'Bulk Actions', 'dokan' ); ?></option>

                        <?php if ( $duplicate_orders ) { ?>
                            <option value="delete"><?php _e( 'Delete', 'dokan' ); ?></option>
                            
                        <?php } ?>
                    </select>
               </div>

                <?php /*if ( $duplicate_orders ) {
                    $counts = dokan_get_withdraw_count();
                    $num_of_pages = ceil( $counts[$status] / $limit );
                    $page_links = paginate_links( array(
                        'base'      => add_query_arg( 'paged', '%#%' ),
                        'format'    => '',
                        'prev_text' => __( '&laquo;', 'aag' ),
                        'next_text' => __( '&raquo;', 'aag' ),
                        'total'     => $num_of_pages,
                        'current'   => $pagenum
                    ) );

                    if ( $page_links ) {
                        echo '<div class="tablenav-pages">' . $page_links . '</div>';
                    }
                } */?>
            </div>

        </form>
    
</div>
<style type="text/css">
    .duplicate-order-table {
        margin-top: 10px;
    }

    .duplicate-order-table td, .duplicate-order-table th {
        vertical-align: top;
    }

    .custom-spinner {
        background: url('images/spinner-2x.gif') no-repeat;
        background-position: 43% 9px;
        background-size: 20px 20px;
        opacity: .4;
        filter: alpha(opacity=40);
        width: 20px;
        height: 20px;
    }
   
</style>


            