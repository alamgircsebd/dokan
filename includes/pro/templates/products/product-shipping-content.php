<?php
/**
 * Dokan Dashboard Product shipping Content
 *
 * @since 2.4
 *
 * @package dokan
 */
?>

<?php do_action( 'dokan_product_options_shipping_before', $post_id ); ?>

<?php if ( 'yes' == get_option( 'woocommerce_calc_shipping' ) || 'yes' == get_option( 'woocommerce_calc_taxes' ) ): ?>
<div class="dokan-product-shipping-tax dokan-edit-row dokan-clearfix <?php echo ( 'no' == get_option('woocommerce_calc_shipping') ) ? 'woocommerce-no-shipping' : '' ?> <?php echo ( 'no' == get_option('woocommerce_calc_taxes') ) ? 'woocommerce-no-tax' : '' ?>">
    <div class="dokan-side-left">
        <h2><?php _e( 'Shipping & Tax', 'dokan' ); ?></h2>

        <p>
            <?php _e( 'Manage shipping and tax for this product', 'dokan' ); ?>
        </p>
    </div>

    <div class="dokan-side-right">
        <?php
            $dokan_shipping_option  = get_option( 'woocommerce_dokan_product_shipping_settings' );
            $dokan_shipping_enabled = ( isset( $dokan_shipping_option['enabled'] ) ) ? $dokan_shipping_option['enabled'] : 'yes';
            $store_shipping         = get_user_meta( get_current_user_id(), '_dps_shipping_enable', true );
        ?>
        <?php if( 'yes' == get_option('woocommerce_calc_shipping') ): ?>
            <div class="dokan-clearfix hide_if_downloadable dokan-shipping-container">
                <input type="hidden" name="product_shipping_class" value="0">
                <div class="dokan-form-group">
                    <label class="dokan-checkbox-inline" for="_disable_shipping">
                        <input type="checkbox" id="_disable_shipping" name="_disable_shipping" <?php checked( $_disable_shipping, 'no' ); ?>>
                        <?php _e( 'This product requires shipping', 'dokan' ); ?>
                    </label>
                </div>

                <div class="show_if_needs_shipping dokan-shipping-dimention-options">
                    <?php dokan_post_input_box( $post_id, '_weight', array( 'class' => 'form-control', 'placeholder' => __( 'weight (' . esc_html( get_option( 'woocommerce_weight_unit' ) ) . ')', 'dokan' ) ), 'number' ); ?>
                    <?php dokan_post_input_box( $post_id, '_length', array( 'class' => 'form-control', 'placeholder' => __( 'length (' . esc_html( get_option( 'woocommerce_dimension_unit' ) ) . ')', 'dokan' ) ), 'number' ); ?>
                    <?php dokan_post_input_box( $post_id, '_width', array( 'class' => 'form-control', 'placeholder' => __( 'width (' . esc_html( get_option( 'woocommerce_dimension_unit' ) ) . ')', 'dokan' ) ), 'number' ); ?>
                    <?php dokan_post_input_box( $post_id, '_height', array( 'class' => 'form-control', 'placeholder' => __( 'height (' . esc_html( get_option( 'woocommerce_dimension_unit' ) ) . ')', 'dokan' ) ), 'number' ); ?>
                    <div class="dokan-clearfix"></div>
                </div>

                <?php if ( $post_id ): ?>
                    <?php do_action( 'dokan_product_options_shipping' ); ?>
                <?php endif; ?>
                <div class="show_if_needs_shipping dokan-form-group">
                    <label class="control-label" for="product_shipping_class"><?php _e( 'Shipping Class', 'dokan' ); ?></label>
                    <div class="dokan-text-left">
                        <?php
                        // Shipping Class
                        $classes = get_the_terms( $post->ID, 'product_shipping_class' );
                        if ( $classes && ! is_wp_error( $classes ) ) {
                            $current_shipping_class = current($classes)->term_id;
                        } else {
                            $current_shipping_class = '';
                        }

                        $args = array(
                            'taxonomy'          => 'product_shipping_class',
                            'hide_empty'        => 0,
                            'show_option_none'  => __( 'No shipping class', 'dokan' ),
                            'name'              => 'product_shipping_class',
                            'id'                => 'product_shipping_class',
                            'selected'          => $current_shipping_class,
                            'class'             => 'dokan-form-control'
                        );
                        ?>

                        <?php wp_dropdown_categories( $args ); ?>
                        <p class="help-block"><?php _e( 'Shipping classes are used by certain shipping methods to group similar products.', 'dokan' ); ?></p>
                    </div>
                </div>
                <?php if( $dokan_shipping_enabled == 'yes' && $store_shipping == 'yes' ) : ?>
                    <div class="show_if_needs_shipping dokan-shipping-product-options">

                        <div class="dokan-form-group">
                            <?php dokan_post_input_box( $post_id, '_overwrite_shipping', array( 'label' => __( 'Override default shipping cost for this product', 'dokan' ) ), 'checkbox' ); ?>
                        </div>

                        <div class="dokan-form-group show_if_override">
                            <label class="dokan-control-label" for="_additional_product_price"><?php _e( 'Additional cost', 'dokan' ); ?></label>
                            <input id="_additional_product_price" value="<?php echo $_additional_price; ?>" name="_additional_price" placeholder="9.99" class="dokan-form-control" type="number" step="any">
                        </div>

                        <div class="dokan-form-group show_if_override">
                            <label class="dokan-control-label" for="dps_additional_qty"><?php _e( 'Per Qty Additional Price', 'dokan' ); ?></label>
                            <input id="additional_qty" value="<?php echo ( $_additional_qty ) ? $_additional_qty : $dps_additional_qty; ?>" name="_additional_qty" placeholder="1.99" class="dokan-form-control" type="number" step="any">
                        </div>

                        <div class="dokan-form-group show_if_override">
                            <label class="dokan-control-label" for="dps_additional_qty"><?php _e( 'Processing Time', 'dokan' ); ?></label>
                            <select name="_dps_processing_time" id="_dps_processing_time" class="dokan-form-control">
                                <?php foreach ( $processing_time as $processing_key => $processing_value ): ?>
                                      <option value="<?php echo $processing_key; ?>" <?php selected( $porduct_shipping_pt, $processing_key ); ?>><?php echo $processing_value; ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ( 'yes' == get_option('woocommerce_calc_shipping') && 'yes' == get_option( 'woocommerce_calc_taxes' ) ): ?>
            <div class="dokan-divider-top hide_if_downloadable"></div>
        <?php endif ?>

        <?php if ( 'yes' == get_option( 'woocommerce_calc_taxes' ) ) { ?>
        <div class="dokan-clearfix dokan-tax-container">
            <div class="dokan-form-group">
                <label for="_required_tax" class="dokan-form-label">
                <input type="hidden" name="_required_tax" value="no">
                <input type="checkbox" id="_required_tax" name="_required_tax" value="yes" <?php checked( $_required_tax, 'yes' ); ?>>
                <?php _e( 'The product requires Tax', 'dokan' ); ?>
                </label>
            </div>
            <div class="show_if_needs_tax dokan-tax-product-options">
                <div class="dokan-form-group dokan-w">
                    <label class="dokan-control-label" for="_tax_status"><?php _e( 'Tax Status', 'dokan' ); ?></label>
                    <div class="dokan-text-left">
                        <?php dokan_post_input_box( $post_id, '_tax_status', array( 'options' => array(
                            'taxable'   => __( 'Taxable', 'dokan' ),
                            'shipping'  => __( 'Shipping only', 'dokan' ),
                            'none'      => _x( 'None', 'Tax status', 'dokan' )
                            ) ), 'select'
                        ); ?>
                    </div>
                </div>

                <div class="dokan-form-group dokan-w">
                    <label class="dokan-control-label" for="_tax_class"><?php _e( 'Tax Class', 'dokan' ); ?></label>
                    <div class="dokan-text-left">
                        <?php dokan_post_input_box( $post_id, '_tax_class', array( 'options' => $classes_options ), 'select' ); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>
    </div><!-- .dokan-side-right -->
</div><!-- .dokan-product-inventory -->
<?php endif; ?>

<?php do_action( 'dokan_product_edit_after_shipping', $post_id ); ?>
