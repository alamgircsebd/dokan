<?php
/**
 * Dokan Dashboard Product Variation Template
 *
 * @since 2.4
 *
 * @package dokan
 */
?>

<div class="dokan-divider-top"></div>
    <div class="dokan-clearfix dokan-variation-container">
        <label class="checkbox-inline form-label hide_if_variation" for="_has_attribute">
            <input name="_has_attribute" value="no" type="hidden">
            <input name="_has_attribute" id="_has_attribute" value="yes" type="checkbox" <?php checked( $_has_attribute, 'yes' ); ?>>
            <?php _e( 'This product has multiple options', 'dokan' ); ?>
            <span><?php _e( 'e.g. Multiple sizes and/or colors', 'dokan' ); ?></span>
        </label>

        <?php if ( $_create_variations != 'yes' ): ?>
            <div class="dokan-side-body dokan-attribute-content-wrapper dokan-hide">
                <table class="dokan-table dokan-attribute-options-table">
                    <thead>
                        <tr>
                            <th><?php _e( 'Option Name', 'dokan' ) ?> <span class="tips" title="" data-original-title="<?php _e( 'Enter your variation attribute option name', 'dokan' ); ?>">[?]</span></th>
                            <th width="22%"><?php _e( 'Option Values', 'dokan' ) ?> <span class="tips" title="" data-original-title="<?php _e( 'Enter attribute options values corresponding options name', 'dokan') ?>">[?]</span></th>
                            <th width="7%">
                                <span class="dokan-loading dokan-attr-option-loading dokan-hide"></span>
                            </th>
                            <th width="29%">
                                <select name="predefined_attribute" id="predefined_attribute" class="dokan-form-control" data-predefined_attr='<?php echo json_encode( $attribute_taxonomies ); ?>'>
                                    <option value=""><?php _e( 'Custom Attribute', 'dokan' ); ?></option>
                                    <?php
                                    if ( !empty( $attribute_taxonomies ) ) { ?>
                                        <?php foreach ( $attribute_taxonomies as $key => $value ) { ?>
                                            <option value="<?php echo $value->attribute_name; ?>"><?php echo $value->attribute_label; ?></option>
                                        <?php }
                                    }?>
                                </select>
                            </th>
                            <th><a href="#" class="dokan-btn dokan-btn-default add_attribute_option"><?php _e( 'Add Option', 'dokan' ) ?></a></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ( $product_attributes ): ?>
                        <?php foreach( $product_attributes as $attribute ): ?>
                        <tr class="dokan-attribute-options">
                            <td width="20%">
                                <?php if ( $attribute['is_taxonomy'] ): ?>
                                    <?php $tax = get_taxonomy( $attribute['name'] ); ?>
                                    <input type="text" disabled="disabled" value="<?php echo $tax->label; ?>" class="dokan-form-control dokan-attribute-option-name-label" data-attribute_name="<?php echo wc_sanitize_taxonomy_name( str_replace( 'pa_', '', $attribute['name'] ) ); ?>">
                                    <input type="hidden" name="attribute_names[]" value="<?php echo esc_attr( $attribute['name'] ); ?>" class="dokan-attribute-option-name">
                                <?php else: ?>
                                    <input type="text" name="attribute_names[]" value="<?php echo $attribute['name']; ?>" class="dokan-form-control dokan-attribute-option-name">
                                <?php endif ?>
                                <input type="hidden" name="attribute_is_taxonomy[]" value="<?php echo ( $attribute['is_taxonomy'] ) ? 1 : 0 ?>">
                            </td>
                            <?php
                            if ( $attribute['is_taxonomy'] ) {
                                $tax = get_taxonomy( $attribute['name'] );
                                $attribute_name = $tax->labels->name;
                                $options = wp_get_post_terms( $post_id, $attribute['name'], array('fields' => 'names') );
                            } else {
                                $attribute_name = $attribute['name'];
                                $options = array_map( 'trim', explode('|', $attribute['value'] ) );
                            }
                            ?>
                            <td colspan="4"><input type="text" name="attribute_values[]" value="<?php echo implode( ',', $options ); ?>" class="dokan-form-control dokan-attribute-option-values"></td>
                            <td><button class="dokan-btn dokan-btn-theme remove_attribute"><i class="fa fa-trash-o"></i></button></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                        <tr class="dokan-attribute-options">
                            <td width="20%">
                                <input type="text" name="attribute_names[]" value="" class="dokan-form-control dokan-attribute-option-name">
                                <input type="hidden" name="attribute_is_taxonomy[]" value="0">
                            </td>
                            <td colspan="4"><input type="text" name="attribute_values[]" value="" class="dokan-form-control dokan-attribute-option-values"></td>
                            <td><button class="dokan-btn dokan-btn-theme remove_attribute"><i class="fa fa-trash-o"></i></button></td>
                        </tr>
                    <?php endif ?>

                        <tr class="dokan-attribute-is-variations">
                            <td colspan="6">
                                <label class="checkbox-inline form-label" for="_create_variation">
                                    <input name="_create_variation" value="no" type="hidden">
                                    <input name="_create_variation" id="_create_variation" value="yes" type="checkbox">
                                    <?php _e( 'Create variation using those attribute options', 'dokan' ); ?>
                                </label>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="dokan-variation-content-wrapper"></div>

        <?php elseif ( $_create_variations == 'yes' ): ?>

            <?php dokan_get_template_part( 'products/edit/load_variation_template', '', array( 'pro' => true, 'post_id' => $post_id ) )    ?>

                <?php do_action( 'dokan_product_edit_after_variations' ); ?>

            <div class="dokan-divider-top"></div>

            <label class="checkbox-inline form-label" for="_create_variation">
                <input name="_create_variation" value="no" type="hidden">
                <input name="_create_variation" id="_create_variation" value="yes" class="dokan_create_variation" type="checkbox" <?php checked( $_create_variations, 'yes' ); ?>>
                <?php _e( 'Use those above variations', 'dokan' ); ?><span> (<?php _e( 'If unchecked, then no variation will be created', 'dokan' ); ?>)</span>
            </label>
            <input type="hidden" name="_variation_product_update" value="<?php esc_attr_e( 'yes', 'dokan' ); ?>">
        <?php endif ?>
    </div><!-- .dokan-divider-top -->
