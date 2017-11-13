<?php
$action = isset( $_GET['action'] ) ? $_GET['action'] : 'listing';

if ( $action == 'edit' ) {
    dokan_get_template_part( 'auction/auction-product-edit', '', array( 'is_auction' => true ) );
} else {
    dokan_get_template_part( 'auction/auction-products-listing', '', array( 'is_auction' => true ) );
}