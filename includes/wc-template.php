<?php
/**
 * Injects seller name on cart and other areas
 *
 * @param array $item_data
 * @param array $cart_item
 * @return array
 */
function dokan_product_seller_info( $item_data, $cart_item ) {
    $seller_info = dokan_get_store_info( $cart_item['data']->post->post_author );

    $item_data[] = array(
        'name'  => __( 'Seller', 'dokan' ),
        'value' => $seller_info['store_name']
    );

    return $item_data;
}

add_filter( 'woocommerce_get_item_data', 'dokan_product_seller_info', 10, 2 );


/**
 * Adds a seller tab in product single page
 *
 * @param array $tabs
 * @return array
 */
function dokan_seller_product_tab( $tabs) {

    $tabs['seller'] = array(
        'title'    => __( 'Seller Info', 'dokan' ),
        'priority' => 90,
        'callback' => 'dokan_product_seller_tab'
    );

    return $tabs;
}

add_filter( 'woocommerce_product_tabs', 'dokan_seller_product_tab' );


/**
 * Prints seller info in product single page
 *
 * @global WC_Product $product
 * @param type $val
 */
function dokan_product_seller_tab( $val ) {
    global $product;

    $author     = get_user_by( 'id', $product->post->post_author );
    $store_info = dokan_get_store_info( $author->ID );
    ?>
    <h2><?php _e( 'Seller Information', 'dokan' ); ?></h2>
    <ul class="list-unstyled">

        <?php if ( !empty( $store_info['store_name'] ) ) { ?>
            <li class="store-name">
                <span><?php _e( 'Store Name:', 'dokan' ); ?></span>
                <span class="details">
                    <?php echo esc_html( $store_info['store_name'] ); ?>
                </span>
            </li>
        <?php } ?>

        <li class="seller-name">
            <span>
                <?php _e( 'Seller:', 'dokan' ); ?>
            </span>

            <span class="details">
                <?php printf( '<a href="%s">%s</a>', dokan_get_store_url( $author->ID ), $author->display_name ); ?>
            </span>
        </li>
        <?php if ( !empty( $store_info['address'] ) ) { ?>
            <li class="store-address">
                <span><b><?php _e( 'Address:', 'dokan' ); ?></b></span>
                <span class="details">
                    <?php echo dokan_get_seller_address( $author->ID ) ?>
                </span>
            </li>
        <?php } ?>

        <li class="clearfix">
            <?php dokan_get_readable_seller_rating( $author->ID ); ?>
        </li>
    </ul>

    <?php
}


/**
 * Show sub-orders on a parent order if available
 *
 * @param WC_Order $parent_order
 * @return void
 */
function dokan_order_show_suborders( $parent_order ) {

    $sub_orders = get_children( array(
        'post_parent' => $parent_order->id,
        'post_type'   => 'shop_order',
        'post_status' => array( 'wc-pending', 'wc-completed', 'wc-processing', 'wc-on-hold' )
    ) );

    if ( ! $sub_orders ) {
        return;
    }

    $statuses = wc_get_order_statuses();

    dokan_get_template_part( 'sub-orders', '', array( 'sub_orders' => $sub_orders, 'statuses' => $statuses ) );
}

add_action( 'woocommerce_order_details_after_order_table', 'dokan_order_show_suborders' );

/**
 * Default seller image
 *
 * @return string
 */
function dokan_get_no_seller_image() {
    $image = DOKAN_PLUGIN_ASSEST. '/images/no-seller-image.png';

    return apply_filters( 'dokan_no_seller_image', $image );
}

/**
 * Override Customer Orders array
 *
 * @param post_arg_query array()
 *
 * @return array() post_arg_query
 */
function dokan_get_customer_main_order( $customer_orders ) {
    $customer_orders['post_parent'] = 0;
    return $customer_orders;
}

add_filter( 'woocommerce_my_account_my_orders_query', 'dokan_get_customer_main_order');
