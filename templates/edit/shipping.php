<?php 

global $post;
$user_id = get_current_user_id();

$_disable_shipping = get_post_meta( $post->ID, '_disable_shipping', true );
$_overwrite_shipping = get_post_meta( $post->ID, '_overwrite_shipping', true );
$_additional_price = get_post_meta( $post->ID, '_additional_price', true );
$_additional_qty = get_post_meta( $post->ID, '_additional_qty', true );

$dps_shipping_type_price = get_user_meta( $user_id, '_dps_shipping_type_price', true );
$dps_additional_qty = get_user_meta( $user_id, '_dps_additional_qty', true );
?>
<?php do_action( 'dokan_product_options_shipping_before' ); ?>

<div class="dokan-form-horizontal dokan-product-shipping">
    <input type="hidden" name="product_shipping_class" value="0">

    <div class="dokan-form-group show_if_simple">
        <label class="dokan-w4 dokan-control-label" for="_disable_shipping" style="margin-top:9px"><?php _e( 'Disable Shipping', 'dokan' ); ?></label>
        <div class="dokan-w5 dokan-text-left">
            <div class="checkbox">
                <label>
                    <input type="hidden" name="_disable_shipping" value="no">
                    <input type="checkbox" name="_disable_shipping" value="yes" <?php checked( 'yes', $_disable_shipping, true ); ?>> <?php _e( 'Disable shipping for this product', 'dokan' ); ?>
                </label>
            </div>
        </div>
    </div>

    <div class="dokan-form-group show_if_simple">
        <label class="dokan-w4 dokan-control-label" for="_backorders"><?php _e( 'Weight (kg)', 'dokan' ); ?></label>
        <div class="dokan-w4 dokan-text-left">
            <?php dokan_post_input_box( $post->ID, '_weight' ); ?>
        </div>
    </div>

    <div class="dokan-form-group show_if_simple">
        <label class="dokan-w4 dokan-control-label" for="_backorders"><?php _e( 'Dimensions (cm)', 'dokan' ); ?></label>
        <div class="dokan-w8 dokan-text-left product-dimension">
            <?php dokan_post_input_box( $post->ID, '_length', array( 'class' => 'form-control col-sm-1', 'placeholder' => __( 'length', 'dokan' ) ), 'number' ); ?>
            <?php dokan_post_input_box( $post->ID, '_width', array( 'class' => 'form-control col-sm-1', 'placeholder' => __( 'width', 'dokan' ) ), 'number' ); ?>
            <?php dokan_post_input_box( $post->ID, '_height', array( 'class' => 'form-control col-sm-1', 'placeholder' => __( 'height', 'dokan' ) ), 'number' ); ?>
        </div>
    </div>

    <div class="dokan-form-group show_if_simple">
        <label class="dokan-w4 dokan-control-label" for="_overwrite_shipping" style="margin-top:9px"><?php _e( 'Overwrite Shipping', 'dokan' ); ?></label>
        <div class="dokan-w5 dokan-text-left">
            <div class="checkbox">
                <label>
                    <input type="hidden" name="_overwrite_shipping" value="no">
                    <input type="checkbox" name="_overwrite_shipping" value="yes" <?php checked( 'yes', $_overwrite_shipping, true ); ?>> <?php _e( 'Overwrite default shipping cost for this product', 'dokan' ); ?>
                </label>
            </div>
        </div>
    </div>


    <div class="dokan-form-group dokan-shipping-price dokan-shipping-type-price">
        <label class="dokan-w4 dokan-control-label" for="shipping_type_price"><?php _e( 'Additional cost', 'dokan' ); ?></label>

        <div class="dokan-w4 dokan-text-left">
            <input id="shipping_type_price" value="<?php echo $_additional_price; ?>" name="_additional_price" placeholder="9.99" class="dokan-form-control" type="number" step="any">
        </div>
    </div>

    <div class="dokan-form-group dokan-shipping-price dokan-shipping-add-qty">
        <label class="dokan-w4 dokan-control-label" for="dps_additional_qty"><?php _e( 'Per Qty Additional Price', 'dokan' ); ?></label>

        <div class="dokan-w4 dokan-text-left">
            <input id="additional_qty" value="<?php echo ( $_additional_qty ) ? $_additional_qty : $dps_additional_qty; ?>" name="_additional_qty" placeholder="9.99" class="dokan-form-control" type="number" step="any">
        </div>
    </div>
    

    <?php do_action( 'dokan_product_options_shipping' ); ?>

</div>

