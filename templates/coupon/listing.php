<?php
/**
 *  Dashboard Coupon listing template
 *
 *  @since 2.4
 *
 *  @package dokan
 */
?>

<table class="dokan-table">
    <thead>
        <tr>
            <th><?php _e('Code', 'dokan'); ?></th>
            <th><?php _e('Coupon type', 'dokan'); ?></th>
            <th><?php _e('Coupon amount', 'dokan'); ?></th>
            <th><?php _e('Product IDs', 'dokan'); ?></th>
            <th><?php _e('Usage / Limit', 'dokan'); ?></th>
            <th><?php _e('Expiry date', 'dokan'); ?></th>
        </tr>
    </thead>

    <?php
        foreach( $coupons->coupons as $key => $coupon ) {
            ?>
            <tr>
                <td class="coupon-code" data-title="<?php _e( 'Code', 'dokan' ); ?>">
                    <?php $edit_url =  wp_nonce_url( add_query_arg( array( 'post' => $coupon->get_id(), 'action' => 'edit', 'view' => 'add_coupons' ), dokan_get_navigation_url( 'coupons' ) ), '_coupon_nonce', 'coupon_nonce_url' ); ?>
                    <div class="code">
                        <?php if ( current_user_can( 'dokan_edit_coupon' ) ): ?>
                            <a href="<?php echo $edit_url; ?>"><span><?php echo esc_attr( $coupon->get_code() ); ?></span></a>
                        <?php else: ?>
                            <a href=""><span><?php echo esc_attr( $coupon->get_code() ); ?></span></a>
                        <?php endif ?>
                    </div>

                    <div class="row-actions">
                        <?php $del_url = wp_nonce_url( add_query_arg( array( 'post' => $coupon->get_id(), 'action' => 'delete' ), dokan_get_navigation_url( 'coupons' ) ) ,'_coupon_del_nonce', 'coupon_del_nonce' ); ?>

                        <?php if ( current_user_can( 'dokan_edit_coupon' ) ): ?>
                            <span class="edit"><a href="<?php echo $edit_url; ?>"><?php _e( 'Edit', 'dokan' ); ?></a> | </span>
                        <?php endif; ?>

                        <?php if ( current_user_can( 'dokan_delete_coupon' ) ): ?>
                            <span class="delete"><a  href="<?php echo $del_url; ?>"  onclick="return confirm('<?php esc_attr_e( 'Are you sure want to delete', 'dokan' ); ?>');"><?php _e( 'delete', 'dokan' ); ?></a></span>
                        <?php endif ?>
                    </div>
                </td>

                <td data-title="<?php _e( 'Coupon type', 'dokan' ); ?>">
                    <?php
                    $discount_type = $coupon->get_discount_type();
                    $types         = dokan_get_coupon_types();

                    printf( __( '%s', 'dokan' ), $types[$discount_type] );
                    ?>
                </td>

                <td data-title="<?php _e( 'Coupon amount', 'dokan' ); ?>">
                    <?php echo esc_attr( wc_format_localized_price( $coupon->get_amount() ) ); ?>
                </td>

                <td data-title="<?php _e( 'Product IDs', 'dokan' ); ?>">
                    <?php
                        $product_ids = ! empty( $coupon->get_product_ids() ) ? $coupon->get_product_ids() : array();

                        if ( sizeof( $product_ids ) > 0 ) {
                            if ( count( $product_ids ) > 12 ) {
                                $product_ids = array_slice( $product_ids, 0, 12 );
                                echo sprintf( '%s... <a href="%s">%s</a>', esc_html( implode( ', ', $product_ids ) ), esc_url( $edit_url ), __( 'See all', 'dokan' ) );
                            } else {
                                echo esc_html( implode( ', ', $product_ids ) );
                            }
                        } else {
                            echo '&ndash;';
                        }
                    ?>
                </td>

                <td data-title="<?php _e( 'Usage / Limit', 'dokan' ); ?>">
                    <?php

                        $usage_count = absint( $coupon->get_usage_count() );
                        $usage_limit = esc_html( $coupon->get_usage_limit() );

                        if ( $usage_limit )
                            printf( __( '%s / %s', 'dokan' ), $usage_count, $usage_limit );
                        else
                            printf( __( '%s / &infin;', 'dokan' ), $usage_count );

                        do_action( 'dokan_coupon_list_after_usages_limit', $coupon );
                     ?>
                </td>

                <td data-title="<?php _e( 'Expiry date', 'dokan' ); ?>">
                    <?php
                        $expiry_date = $coupon->get_date_expires();

                        if ( $expiry_date ) {
                            echo esc_html( $expiry_date->date_i18n( 'F j, Y' ) );
                        } else {
                            echo '&ndash;';
                        }
                    ?>
                </td>
                <td class="diviader"></td>
            </tr>
            <?php
        }
    ?>
</table>

<?php
    $pagenum      = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;
    $num_of_pages = ceil( $coupons->total / $coupons->per_page );
    $base_url     = dokan_get_navigation_url( 'coupons' );

    $page_links = paginate_links( array(
        'base'      => $base_url. '%_%',
        'format'    => '?pagenum=%#%',
        'add_args'  => false,
        'prev_text' => __( '&laquo;', 'aag' ),
        'next_text' => __( '&raquo;', 'aag' ),
        'total'     => $num_of_pages,
        'current'   => $pagenum,
        'type'      => 'array'
    ) );

    if ( $page_links ) {
        echo "<ul class='pagination'>\n\t<li>";
        echo join("</li>\n\t<li>", $page_links);
        echo "</li>\n</ul>\n";
        echo '</div>';
    }

?>
