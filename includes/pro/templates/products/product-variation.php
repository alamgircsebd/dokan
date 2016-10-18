<?php
/**
 * Dokan Dashboard Product Variation Template
 *
 * @since 2.4
 *
 * @package dokan
 */
?>

<div class="dokan-attribute-variation-options dokan-edit-row dokan-clearfix">
    <div class="dokan-section-heading">
        <h2><?php _e( 'Attribute and Variation', 'dokan' ); ?></h2>
        <p><?php _e( 'Manage attributes and variations for this variable product.', 'dokan' ); ?></p>
    </div>
    <div class="dokan-section-content">
        <div class="dokan-product-attribute-wrapper">
            <div class="dokan-attribute-type">
                <select name="predefined_attribute" id="predefined_attribute" class="dokan-w5 dokan-form-control dokan_attribute_taxonomy" data-predefined_attr='<?php echo json_encode( $attribute_taxonomies ); ?>'>
                    <option value=""><?php _e( 'Custom Attribute', 'dokan' ); ?></option>
                    <?php
                    if ( ! empty( $attribute_taxonomies ) ) {
                        foreach ( $attribute_taxonomies as $tax ) {
                            $attribute_taxonomy_name = wc_attribute_taxonomy_name( $tax->attribute_name );
                            $label = $tax->attribute_label ? $tax->attribute_label : $tax->attribute_name;
                            echo '<option value="' . esc_attr( $attribute_taxonomy_name ) . '">' . esc_html( $label ) . '</option>';
                        }
                    }
                    ?>
                </select>
                <a href="#" class="dokan-btn dokan-btn-default add_new_attribute"><?php _e( 'Add attribute', 'dokan' ) ?></a>
                <span class="dokan-spinner dokan-attribute-spinner dokan-hide"></span>
            </div>
            <ul class="dokan-attribute-option-list"></ul>
        </div>

        <div class="dokan-product-variation-wrapper">
            <div class="dokan-variation-type">
                <select id="dokan-create-<!-- variation" class="dokan-form-control dokan-w5 dokan-create-variation" style="margin-right: 10px;">
                    <option value="create_custom"><?php _e( 'Add new variation', 'dokan' ) ?></option>
                    <option value="link_all_variations"><?php _e( 'Create variations from all attributes', 'dokan' ) ?></option>
                    <option value="delete_all_variation"><?php _e( 'Delete all variations', 'dokan' ) ?></option>
                </select>
                <a href="#" class="dokan- -->btn dokan-btn-default dokan_process_variation"><?php _e( 'Go', 'dokan' ) ?></a>

                <!-- <a href="#" class="dokan-btn dokan-btn-default dokan_create_all_variation"><?php _e( 'Create variation for all attribute', 'dokan' ) ?></a> -->
                <!-- <a href="#" class="dokan-btn dokan-btn-default dokan_create_custom_variation"><?php _e( 'Add Custom variation', 'dokan' ) ?></a> -->
                <span class="dokan-spinner dokan-attribute-spinner dokan-hide"></span>
            </div>
        </div>
    </div>
</div>