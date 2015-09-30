<?php
/**
 * Dokan Dashbaord Variation Attribute
 * popup Template
 *
 * @since 2.4
 *
 * @package dokan
 */
?>

<script type="text/html" id="tmpl-dokan-single-attribute">
    <div id="dokan-single-attribute-wrapper" class="white-popup">
        <form action="" method="post" id="dokan-single-attribute-form">
            <table class="dokan-table dokan-single-attribute-options-table">
                <thead>
                    <tr>
                        <th><?php _e( 'Option Name', 'dokan' ) ?> <span class="tips" title="" data-original-title="Enter you variation attribute option name">[?]</span></th>
                        <th width="22%"><?php _e( 'Option Values', 'dokan' ) ?> <span class="tips" title="" data-original-title="Enter attribute options values corresponding options name">[?]</span></th>
                        <th width="7%">
                            <span class="dokan-loading dokan-attr-option-loading dokan-hide"></span>
                        </th>
                        <th width="29%">
                            <select name="predefined_attribute" id="predefined_attribute" class="dokan-form-control">
                                <option value=""><?php _e( 'Custom Attribute', 'dokan' ); ?></option>

                                <# if ( !_.isNull( data.attribute_taxonomies ) ) { #>
                                    <# _.each( data.attribute_taxonomies, function( tax_val, tax_key ) { #>
                                        <option value="{{ tax_val.attribute_name }}">{{ tax_val.attribute_label }}</option>
                                    <# }); #>
                                <# } #>
                            </select>
                        </th>
                        <th><a href="#" class="dokan-btn dokan-btn-default add_single_attribute_option"><?php _e( 'Add Option', 'dokan' ) ?></a></th>
                    </tr>
                </thead>
                <tbody>
                    <# if ( !_.isNull( data.attribute_data ) ){ #>
                        <# _.each( data.attribute_data, function( attr_val, attr_key ) { #>
                        <tr class="dokan-single-attribute-options">
                            <td width="20%">
                                <# if ( attr_val.is_taxonomy ) { #>
                                    <input type="text" disabled="disabled" value="{{ attr_val.label }}" class="dokan-form-control dokan-single-attribute-option-name-label" data-attribute_name="{{attr_val.data_attr_name}}">
                                    <input type="hidden" name="attribute_names[]" value="{{attr_val.name}}" class="dokan-single-attribute-option-name">
                                    <input type="hidden" name="attribute_is_taxonomy[]" value="1">
                                <# } else { #>
                                    <input type="text" name="attribute_names[]" value="{{attr_val.name}}" class="dokan-form-control dokan-single-attribute-option-name">
                                    <input type="hidden" name="attribute_is_taxonomy[]" value="0">
                                <# } #>
                            </td>
                            <td colspan="3">
                                <# if ( attr_val.is_taxonomy ) { #>
                                    <input type="text" name="attribute_values[]" value="{{ attr_val.term_value.replace(/\|/g, ',' ) }}" class="dokan-form-control dokan-single-attribute-option-values">
                                <# } else { #>
                                    <input type="text" name="attribute_values[]" value="{{ attr_val.value.replace(/\|/g, ',' ) }}" class="dokan-form-control dokan-single-attribute-option-values">
                                <# } #>
                            </td>
                            <td><button class="dokan-btn dokan-btn-theme remove_single_attribute"><i class="fa fa-trash-o"></i></button></td>
                        </tr>
                        <# }) #>
                    <# } else { #>
                        <tr colspan="3" class="dokan-single-attribute-options">
                            <td width="20%">
                                <input type="text" name="attribute_names[]" value="" class="dokan-form-control dokan-single-attribute-option-name">
                                <input type="hidden" name="attribute_is_taxonomy[]" value="0">
                            </td>
                            <td><input type="text" name="attribute_values[]" value="" class="dokan-form-control dokan-single-attribute-option-values"></td>
                            <td><button class="dokan-btn dokan-btn-theme remove_single_attribute"><i class="fa fa-trash-o"></i></button></td>
                        </tr>
                    <# } #>
                </tbody>
            </table>
            <input type="hidden" name="product_id" value="<?php echo $post_id ?>">
            <input type="submit" class="dokan-btn dokan-btn-theme dokan-right" name="dokan_new_attribute_option_save" value="<?php esc_attr_e( 'Save', 'dokan' ); ?>">
            <span class="dokan-loading dokan-save-single-attr-loader dokan-hide"></span>
            <div class="dokan-clearfix"></div>
        </form>
    </div>
</script>