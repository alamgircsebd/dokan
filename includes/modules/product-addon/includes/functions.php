<?php

function dokan_pa_convert_type_name( $type = '' ) {
    switch ( $type ) {
        case 'checkboxes':
            $name = __( 'Checkbox', 'woocommerce-product-addons' );
            break;
        case 'custom_price':
            $name = __( 'Price', 'woocommerce-product-addons' );
            break;
        case 'input_multiplier':
            $name = __( 'Quantity', 'woocommerce-product-addons' );
            break;
        case 'custom_text':
            $name = __( 'Short Text', 'woocommerce-product-addons' );
            break;
        case 'custom_textarea':
            $name = __( 'Long Text', 'woocommerce-product-addons' );
            break;
        case 'file_upload':
            $name = __( 'File Upload', 'woocommerce-product-addons' );
            break;
        case 'select':
            $name = __( 'Dropdown', 'woocommerce-product-addons' );
            break;
        case 'multiple_choice':
        default:
            $name = __( 'Multiple Choice', 'woocommerce-product-addons' );
            break;
    }

    return $name;
}
