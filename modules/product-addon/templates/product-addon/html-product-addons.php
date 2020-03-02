<?php
$product        = wc_get_product( $post );
$exists         = (bool) $product->get_id();
$exclude_global = ! empty( $product->get_meta( '_product_addons_exclude_global' ) ) ? 1 : 0;
$product_addons = array_filter( (array) $product->get_meta( '_product_addons' ) );
?>


<div id="dokan-product-addons-options" class="dokan-product-addons-options dokan-edit-row dokan-clearfix">
    <div class="dokan-section-heading" data-togglehandler="dokan_product_addons_options">
        <h2><i class="fa fa-wrench" aria-hidden="true"></i> <?php _e( 'Add-ons', 'dokan' ); ?><span class=""></h2>
        <p class=""><?php _e( 'Manage addon fields for this product.', 'dokan' ); ?></p>

        <a href="#" class="dokan-section-toggle">
            <i class="fa fa-sort-desc fa-flip-vertical" aria-hidden="true"></i>
        </a>
        <div class="dokan-clearfix"></div>
    </div>
    <div class="dokan-section-content">
        <?php include( dirname( __FILE__ ) . '/html-addon-panel.php' ); ?>
    </div>
</div>
