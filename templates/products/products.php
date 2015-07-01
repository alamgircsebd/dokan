<?php
$action = isset( $_GET['action'] ) ? $_GET['action'] : 'listing';

if ( $action == 'edit' ) {
    dokan_get_template_part( 'products/product-edit');
} else {
    dokan_get_template_part( 'products/products-listing');
}