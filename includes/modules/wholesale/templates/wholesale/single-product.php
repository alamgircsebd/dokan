<?php
/**
 * Single product template
 * For disolaying wholesale price as ver perdon settings
 *
 * @since DOKAN_PRO_SINCE
 *
 * @package dokan
 */
?>

<p class="dokan-wholesale-meta">
    <?php echo apply_filters( 'dokan_product_wholesale_price_html', sprintf( '%s: <strong>%s</strong> ( %s: <strong>%s</strong> )', __( 'Wholesale Price', 'dokan' ), wc_price( $wholesale_price ), __( 'Minimum Quantity', 'dokan' ), $wholesale_quantity ) ); ?>
</p>
