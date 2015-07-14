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
        foreach( $coupons as $key => $post ) {
            ?>
            <tr>
                <td class="coupon-code">
                    <?php $edit_url =  wp_nonce_url( add_query_arg( array('post' => $post->ID, 'action' => 'edit', 'view' => 'add_coupons'), dokan_get_navigation_url( 'coupons' ) ), '_coupon_nonce', 'coupon_nonce_url' ); ?>
                    <div class="code">
                        <a href="<?php echo $edit_url; ?>"><span><?php echo esc_attr( $post->post_title ); ?></span></a>
                    </div>

                    <div class="row-actions">
                        <?php $del_url = wp_nonce_url( add_query_arg( array('post' => $post->ID, 'action' => 'delete'), dokan_get_navigation_url( 'coupons' ) ) ,'_coupon_del_nonce', 'coupon_del_nonce'); ?>

                        <span class="edit"><a href="<?php echo $edit_url; ?>"><?php _e( 'Edit', 'dokan' ); ?></a> | </span>
                        <span class="delete"><a  href="<?php echo $del_url; ?>"  onclick="return confirm('<?php esc_attr_e( 'Are you sure want to delete', 'dokan' ); ?>');"><?php _e('delete', 'dokan'); ?></a></span>
                    </div>
                </td>

                <td>
                    <?php
                    $discount_type = get_post_meta( $post->ID, 'discount_type', true );
                    $type = '';

                    if ( $discount_type == 'fixed_product' ) {
                        $type = __( 'Fixed Amount', 'dokan' );
                    } elseif ( $discount_type == 'percent_product' ) {
                        $type = __( 'Percent', 'dokan' );
                    }

                    echo $type;
                    ?>
                </td>

                <td>
                    <?php echo esc_attr( get_post_meta( $post->ID, 'coupon_amount', true ) ); ?>
                </td>

                <td>
                    <?php
                        $product_ids = get_post_meta( $post->ID, 'product_ids', true );
                        $product_ids = $product_ids ? array_map( 'absint', explode( ',', $product_ids ) ) : array();

                        if ( sizeof( $product_ids ) > 0 )
                            echo esc_html( implode( ', ', $product_ids ) );
                        else
                        echo '&ndash;';
                    ?>
                </td>

                <td>
                    <?php

                        $usage_count = absint( get_post_meta( $post->ID, 'usage_count', true ) );
                        $usage_limit = esc_html( get_post_meta($post->ID, 'usage_limit', true) );

                        if ( $usage_limit )
                            printf( __( '%s / %s', 'dokan' ), $usage_count, $usage_limit );
                        else
                            printf( __( '%s / &infin;', 'dokan' ), $usage_count );
                     ?>
                </td>

                <td>
                    <?php
                        $expiry_date = get_post_meta($post->ID, 'expiry_date', true);

                        if ( $expiry_date )
                            echo esc_html( date_i18n( 'F j, Y', strtotime( $expiry_date ) ) );
                        else
                            echo '&ndash;';
                    ?>
                </td>
            </tr>
            <?php
        }
    ?>
</table>
