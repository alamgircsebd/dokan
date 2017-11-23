<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Add generic mappings.
 *
 * @since 3.1.0
 * @param array $mappings
 * @return array
 */
function wc_importer_generic_mappings( $mappings ) {
    $generic_mappings = array(
        __( 'Title', 'dpi_plugin' )         => 'name',
        __( 'Product Title', 'dpi_plugin' ) => 'name',
        __( 'Price', 'dpi_plugin' )         => 'regular_price',
        __( 'Parent SKU', 'dpi_plugin' )    => 'parent_id',
        __( 'Quantity', 'dpi_plugin' )      => 'stock_quantity',
    );

    return array_merge( $mappings, $generic_mappings );
}

add_filter( 'woocommerce_csv_product_import_mapping_default_columns', 'wc_importer_generic_mappings' );
