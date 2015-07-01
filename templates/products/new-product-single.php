<?php

global $post;

$from_shortcode = false;

if( isset( $post->ID ) && $post->ID && $post->post_type == 'product' ) {
    $post_id = $post->ID;
    $post_title = $post->post_title;
    $post_content = $post->post_content;
    $post_excerpt = $post->post_excerpt;
    $post_status = $post->post_status;
} else {
    $post_id = NULL;
    $post_title = '';
    $post_content = '';
    $post_excerpt = '';
    $post_status = 'pending';
    $from_shortcode = true;

}

if ( isset( $_GET['product_id'] ) ) {
    $post_id        = intval( $_GET['product_id'] );
    $post           = get_post( $post_id );
    $post_title     = $post->post_title;
    $post_content   = $post->post_content;
    $post_excerpt   = $post->post_excerpt;
    $post_status    = $post->post_status;
    $product        = get_product( $post_id );
    $from_shortcode = true;
}

$_regular_price         = get_post_meta( $post_id, '_regular_price', true );
$_sale_price            = get_post_meta( $post_id, '_sale_price', true );
$is_discount            = !empty( $_sale_price ) ? true : false;
$_sale_price_dates_from = get_post_meta( $post_id, '_sale_price_dates_from', true );
$_sale_price_dates_to   = get_post_meta( $post_id, '_sale_price_dates_to', true );

$_sale_price_dates_from = !empty( $_sale_price_dates_from ) ? date_i18n( 'Y-m-d', $_sale_price_dates_from ) : '';
$_sale_price_dates_to   = !empty( $_sale_price_dates_to ) ? date_i18n( 'Y-m-d', $_sale_price_dates_to ) : '';
$show_schedule          = false;

if ( !empty( $_sale_price_dates_from ) && !empty( $_sale_price_dates_to ) ) {
    $show_schedule = true;
}

$_featured          = get_post_meta( $post_id, '_featured', true );
// $_weight            = get_post_meta( $post_id, '_weight', true );
// $_length            = get_post_meta( $post_id, '_length', true );
// $_width             = get_post_meta( $post_id, '_width', true );
// $_height            = get_post_meta( $post_id, '_height', true );
$_downloadable      = get_post_meta( $post_id, '_downloadable', true );
$_stock             = get_post_meta( $post_id, '_stock', true );
$_stock_status      = get_post_meta( $post_id, '_stock_status', true );
$_visibility        = get_post_meta( $post_id, '_visibility', true );
$_enable_reviews    = $post->comment_status;


if ( ! $from_shortcode ) {
    get_header();
}
?>
<?php

    /**
     *  dokan_dashboard_content_before hook
     *
     *  @since 2.4
     */
    do_action( 'dokan_dashboard_wrap_before' );
?>

<div class="dokan-dashboard-wrap">

    <?php

        /**
         *  dokan_dashboard_content_before hook
         *
         *  @hooked get_dashboard_side_navigation
         *
         *  @since 2.4
         */
        do_action( 'dokan_dashboard_content_before' );
    ?>

    <div class="dokan-dashboard-content dokan-product-edit">

        <?php

            /**
             *  dokan_dashboard_content_before hook
             *
             *  @hooked get_dashboard_side_navigation
             *
             *  @since 2.4
             */
            do_action( 'dokan_dashboard_content_inside_before' );
            do_action( 'dokan_before_new_product' );
        ?>

        <header class="dokan-dashboard-header dokan-clearfix">
            <h1 class="entry-title">
                <?php if ( !$post_id ): ?>
                    <?php _e( 'Add New Product', 'dokan' ); ?>
                <?php else: ?>
                    <?php _e( 'Edit Product', 'dokan' ); ?>
                    <span class="dokan-label <?php echo dokan_get_post_status_label_class( $post->post_status ); ?> dokan-product-status-label">
                        <?php echo dokan_get_post_status( $post->post_status ); ?>
                    </span>

                    <?php if ( $post->post_status == 'publish' ) { ?>
                        <span class="dokan-right">
                            <a class="view-product dokan-btn dokan-btn-sm" href="<?php echo get_permalink( $post->ID ); ?>" target="_blank"><?php _e( 'View Product', 'dokan' ); ?></a>
                        </span>
                    <?php } ?>

                    <?php if ( $_visibility == 'hidden' ) { ?>
                        <span class="dokan-right dokan-label dokan-label-default dokan-product-hidden-label"><i class="fa fa-eye-slash"></i> <?php _e( 'Hidden', 'dokan' ); ?></span>
                    <?php } ?>

                <?php endif ?>
            </h1>
        </header><!-- .entry-header -->

        <div class="product-edit-new-container">
            <?php if ( Dokan_Template_Products::$errors ) { ?>
                <div class="dokan-alert dokan-alert-danger">
                    <a class="dokan-close" data-dismiss="alert">&times;</a>

                    <?php foreach ( Dokan_Template_Products::$errors as $error) { ?>

                        <strong><?php _e( 'Error!', 'dokan' ); ?></strong> <?php echo $error ?>.<br>

                    <?php } ?>
                </div>
            <?php } ?>

            <?php if ( isset( $_GET['message'] ) && $_GET['message'] == 'success') { ?>
                <div class="dokan-message">
                    <button type="button" class="dokan-close" data-dismiss="alert">&times;</button>
                    <strong><?php _e( 'Success!', 'dokan' ); ?></strong> <?php _e( 'The product has been saved successfully.', 'dokan' ); ?>

                    <?php if ( $post->post_status == 'publish' ) { ?>
                        <a href="<?php echo get_permalink( $post_id ); ?>" target="_blank"><?php _e( 'View Product &rarr;', 'dokan' ); ?></a>
                    <?php } ?>
                </div>
            <?php } ?>

            <?php
            $can_sell = apply_filters( 'dokan_can_post', true );

            if ( $can_sell ) {

                if ( dokan_is_seller_enabled( get_current_user_id() ) ) { ?>

                    <form class="dokan-product-edit-form" role="form" method="post">

                        <?php if ( $post_id ): ?>
                            <?php do_action( 'dokan_product_data_panel_tabs' ); ?>
                        <?php endif; ?>
                        <?php do_action( 'dokan_product_edit_before_main' ); ?>

                        <div class="dokan-form-top-area">

                            <div class="content-half-part">

                                <div class="dokan-form-group">
                                    <input type="hidden" name="dokan_product_id" value="<?php echo $post_id; ?>">

                                    <label for="post_title" class="form-label"><?php _e( 'Title', 'dokan' ); ?></label>
                                    <?php dokan_post_input_box( $post_id, 'post_title', array( 'placeholder' => __( 'Product name..', 'dokan' ), 'value' => $post_title ) ); ?>
                                </div>

                                <div class="hide_if_variation dokan-clearfix">

                                    <div class="dokan-form-group dokan-clearfix dokan-price-container">

                                        <div class="content-half-part regular-price">
                                            <label for="_regular_price" class="form-label"><?php _e( 'Price', 'dokan' ); ?></label>

                                            <div class="dokan-input-group">
                                                <span class="dokan-input-group-addon"><?php echo get_woocommerce_currency_symbol(); ?></span>
                                                <?php dokan_post_input_box( $post_id, '_regular_price', array( 'placeholder' => __( '0.00', 'dokan' ) ), 'number' ); ?>
                                            </div>
                                        </div>

                                        <div class="content-half-part sale-price">
                                            <label for="_sale_price" class="form-label"><?php _e( 'Discounted Price', 'dokan' ); ?></label>

                                            <div class="dokan-input-group">
                                                <span class="dokan-input-group-addon"><?php echo get_woocommerce_currency_symbol(); ?></span>
                                                <?php dokan_post_input_box( $post_id, '_sale_price', array( 'placeholder' => __( '0.00', 'dokan' ) ), 'number' ); ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="discount-price dokan-form-group">
                                        <label>
                                            <input type="checkbox" <?php checked( $is_discount, true ); ?> class="sale-schedule"> <?php _e( 'Schedule Discounted Price', 'dokan' ); ?>
                                        </label>
                                    </div>

                                    <div class="sale-schedule-container dokan-clearfix dokan-form-group">
                                        <div class="content-half-part from">
                                            <div class="dokan-input-group">
                                                <span class="dokan-input-group-addon"><?php _e( 'From', 'dokan' ); ?></span>
                                                <input type="text" name="_sale_price_dates_from" class="dokan-form-control datepicker" value="<?php echo esc_attr( $_sale_price_dates_from ); ?>" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" placeholder="YYYY-MM-DD">
                                            </div>
                                        </div>

                                        <div class="content-half-part to">
                                            <div class="dokan-input-group">
                                                <span class="dokan-input-group-addon"><?php _e( 'To', 'dokan' ); ?></span>
                                                <input type="text" name="_sale_price_dates_to" class="dokan-form-control datepicker" value="<?php echo esc_attr( $_sale_price_dates_to ); ?>" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" placeholder="YYYY-MM-DD">
                                            </div>
                                        </div>
                                    </div><!-- .sale-schedule-container -->
                                </div>

                                <?php if ( dokan_get_option( 'product_category_style', 'dokan_selling', 'single' ) == 'single' ): ?>
                                    <div class="dokan-form-group">
                                        <label for="product_cat" class="form-label"><?php _e( 'Category', 'dokan' ); ?></label>
                                        <?php
                                        $product_cat = -1;
                                        $term = array();
                                        $term = wp_get_post_terms( $post_id, 'product_cat', array( 'fields' => 'ids') );

                                        if ( $term ) {
                                            $product_cat = reset( $term );
                                        }

                                        wp_dropdown_categories( array(
                                            'show_option_none' => __( '- Select a category -', 'dokan' ),
                                            'hierarchical'     => 1,
                                            'hide_empty'       => 0,
                                            'name'             => 'product_cat',
                                            'id'               => 'product_cat',
                                            'taxonomy'         => 'product_cat',
                                            'title_li'         => '',
                                            'class'            => 'product_cat dokan-form-control chosen',
                                            'exclude'          => '',
                                            'selected'         => $product_cat,
                                        ) );
                                        ?>
                                    </div>
                                <?php elseif ( dokan_get_option( 'product_category_style', 'dokan_selling', 'single' ) == 'multiple' ): ?>
                                    <div class="dokan-form-group dokan-list-category-box">
                                        <h5><?php _e( 'Choose a category', 'dokan' );  ?></h5>
                                        <ul class="dokan-checkbox-cat">
                                            <?php
                                            $term = array();
                                            $term = wp_get_post_terms( $post_id, 'product_cat', array( 'fields' => 'ids') );

                                            include_once DOKAN_LIB_DIR.'/class.category-walker.php';
                                            wp_list_categories(array(
                                                'walker'       => new DokanCategoryWalker(),
                                                'title_li'     => '',
                                                'id'           => 'product_cat',
                                                'hide_empty'   => 0,
                                                'taxonomy'     => 'product_cat',
                                                'hierarchical' => 1,
                                                'selected'     => $term
                                            ));
                                            ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>

                                <div class="dokan-form-group">
                                    <label for="product_tag" class="form-label"><?php _e( 'Tags', 'dokan' ); ?></label>
                                    <?php
                                    require_once DOKAN_LIB_DIR.'/class.tag-walker.php';
                                    $term = wp_get_post_terms( $post_id, 'product_tag', array( 'fields' => 'ids') );
                                    $selected = ( $term ) ? $term : array();
                                    $drop_down_tags = wp_dropdown_categories( array(
                                        'show_option_none' => __( '', 'dokan' ),
                                        'hierarchical'     => 1,
                                        'hide_empty'       => 0,
                                        'name'             => 'product_tag[]',
                                        'id'               => 'product_tag',
                                        'taxonomy'         => 'product_tag',
                                        'title_li'         => '',
                                        'class'            => 'product_tags dokan-form-control chosen',
                                        'exclude'          => '',
                                        'selected'         => $selected,
                                        'echo'             => 0,
                                        'walker'           => new Dokan_Walker_Tag_Multi()
                                    ) );

                                    echo str_replace( '<select', '<select data-placeholder="'.__( 'Select product tags','dokan' ).'" multiple="multiple" ', $drop_down_tags );

                                    ?>
                                </div>
                            </div><!-- .content-half-part -->

                            <div class="content-half-part featured-image">

                                <div class="dokan-feat-image-upload">
                                    <?php
                                    $wrap_class        = ' dokan-hide';
                                    $instruction_class = '';
                                    $feat_image_id     = 0;

                                    if ( has_post_thumbnail( $post_id ) ) {
                                        $wrap_class        = '';
                                        $instruction_class = ' dokan-hide';
                                        $feat_image_id     = get_post_thumbnail_id( $post_id );
                                    }
                                    ?>

                                    <div class="instruction-inside<?php echo $instruction_class; ?>">
                                        <input type="hidden" name="feat_image_id" class="dokan-feat-image-id" value="<?php echo $feat_image_id; ?>">

                                        <i class="fa fa-cloud-upload"></i>
                                        <a href="#" class="dokan-feat-image-btn btn btn-sm"><?php _e( 'Upload a product cover image', 'dokan' ); ?></a>
                                    </div>

                                    <div class="image-wrap<?php echo $wrap_class; ?>">
                                        <a class="close dokan-remove-feat-image">&times;</a>
                                        <?php if ( $feat_image_id ) { ?>
                                            <?php echo get_the_post_thumbnail( $post_id, apply_filters( 'single_product_large_thumbnail_size', 'shop_single' ), array( 'height' => '', 'width' => '' ) ); ?>
                                        <?php } else { ?>
                                            <img height="" width="" src="" alt="">
                                        <?php } ?>
                                    </div>
                                </div><!-- .dokan-feat-image-upload -->

                                <div class="dokan-product-gallery">
                                    <div class="dokan-side-body" id="dokan-product-images">
                                        <div id="product_images_container">
                                            <ul class="product_images dokan-clearfix">
                                                <?php
                                                $product_images = get_post_meta( $post_id, '_product_image_gallery', true );
                                                $gallery = explode( ',', $product_images );

                                                if ( $gallery ) {
                                                    foreach ($gallery as $image_id) {
                                                        if ( empty( $image_id ) ) {
                                                            continue;
                                                        }

                                                        $attachment_image = wp_get_attachment_image_src( $image_id, 'thumbnail' );
                                                        ?>
                                                        <li class="image" data-attachment_id="<?php echo $image_id; ?>">
                                                            <img src="<?php echo $attachment_image[0]; ?>" alt="">
                                                            <a href="#" class="action-delete" title="<?php esc_attr_e( 'Delete image', 'dokan' ); ?>">&times;</a>
                                                        </li>
                                                        <?php
                                                    }
                                                }
                                                ?>
                                            </ul>

                                            <input type="hidden" id="product_image_gallery" name="product_image_gallery" value="<?php echo esc_attr( $product_images ); ?>">
                                        </div>

                                        <a href="#" class="add-product-images dokan-btn dokan-btn-sm dokan-btn-success"><?php _e( '+ Add more images', 'dokan' ); ?></a>
                                    </div>
                                </div> <!-- .product-gallery -->
                            </div><!-- .content-half-part -->
                        </div><!-- .dokan-form-top-area -->

                        <div class="dokan-product-short-description">
                            <label for="post_excerpt" class="form-label"><?php _e( 'Short Description', 'dokan' ); ?></label>
                            <?php wp_editor( esc_textarea( wpautop( $post_excerpt ) ), 'post_excerpt', array('editor_height' => 50, 'quicktags' => false, 'media_buttons' => false, 'teeny' => true, 'editor_class' => 'post_excerpt') ); ?>
                        </div>

                        <div class="dokan-product-description">
                            <label for="post_content" class="form-label"><?php _e( 'Description', 'dokan' ); ?></label>
                            <?php wp_editor( esc_textarea( wpautop( $post_content ) ), 'post_content', array('editor_height' => 50, 'quicktags' => false, 'media_buttons' => false, 'teeny' => true, 'editor_class' => 'post_content') ); ?>
                        </div>

                        <?php do_action( 'dokan_new_product_form' ); ?>

                        <?php if ( $post_id ): ?>
                            <?php do_action( 'dokan_product_edit_after_main' ); ?>
                        <?php endif; ?>

                        <div class="dokan-product-inventory dokan-edit-row dokan-clearfix">
                            <div class="dokan-side-left">
                                <h2><?php _e( 'Inventory & Variants', 'dokan' ); ?></h2>

                                <p>
                                    <?php _e( 'Manage inventory, and configure the options for selling this product.', 'dokan' ); ?>
                                </p>
                            </div>

                            <div class="dokan-side-right">
                                <div class="dokan-form-group hide_if_variation" style="width: 50%;">
                                    <label for="_sku" class="form-label"><?php _e( 'SKU', 'dokan' ); ?> <span><?php _e( '(Stock Keeping Unit)', 'dokan' ); ?></span></label>
                                    <?php dokan_post_input_box( $post_id, '_sku' ); ?>
                                </div>

                                <div class="dokan-form-group hide_if_variation">
                                    <?php dokan_post_input_box( $post_id, '_manage_stock', array( 'label' => __( 'Enable product stock management', 'dokan' ) ), 'checkbox' ); ?>
                                </div>

                                <div class="show_if_stock dokan-stock-management-wrapper dokan-form-group dokan-clearfix">

                                    <div class="dokan-w3 hide_if_variation">
                                        <label for="_stock" class="dokan-form-label"><?php _e( 'Quantity', 'dokan' ); ?></label>
                                        <input type="number" name="_stock" placeholder="<?php __( '1', 'dokan' ); ?>" value="<?php echo wc_stock_amount( $_stock ); ?>" min="0" step="1">
                                    </div>

                                    <div class="dokan-w3 hide_if_variation">
                                        <label for="_stock_status" class="dokan-form-label"><?php _e( 'Stock Status', 'dokan' ); ?></label>

                                        <?php dokan_post_input_box( $post_id, '_stock_status', array( 'options' => array(
                                            'instock'     => __( 'In Stock', 'dokan' ),
                                            'outofstock' => __( 'Out of Stock', 'dokan' ),
                                        ) ), 'select' ); ?>
                                    </div>

                                    <div class="dokan-w3 hide_if_variation">
                                        <label for="_backorders" class="dokan-form-label"><?php _e( 'Allow Backorders', 'dokan' ); ?></label>

                                        <?php dokan_post_input_box( $post_id, '_backorders', array( 'options' => array(
                                            'no'     => __( 'Do not allow', 'dokan' ),
                                            'notify' => __( 'Allow but notify customer', 'dokan' ),
                                            'yes'    => __( 'Allow', 'dokan' )
                                        ) ), 'select' ); ?>
                                    </div>
                                </div><!-- .show_if_stock -->

                                <div class="dokan-form-group">
                                    <?php dokan_post_input_box( $post_id, '_sold_individually', array('label' => __( 'Allow only one quantity of this product to be bought in a single order', 'dokan' ) ), 'checkbox' ); ?>
                                </div>

                                <?php if ( $post_id ): ?>
                                    <?php do_action( 'dokan_product_edit_after_inventory' ); ?>
                                <?php endif; ?>

                                <div class="dokan-divider-top dokan-clearfix downloadable downloadable_files hide_if_variation">
                                    <label class="dokan-checkbox-inline dokan-form-label" for="_downloadable">
                                        <input type="checkbox" id="_downloadable" name="_downloadable" value="yes" <?php checked( $_downloadable, 'yes' ); ?>>
                                        <?php _e( 'This is a downloadable product', 'dokan' ); ?>
                                    </label>


                                    <?php if ( $post_id ): ?>
                                        <?php do_action( 'dokan_product_edit_before_sidebar' ); ?>
                                    <?php endif; ?>
                                    <div class="dokan-side-body dokan-download-wrapper<?php echo ( $_downloadable == 'yes' ) ? '' : ' dokan-hide'; ?>">

                                        <table class="dokan-table dokan-table-condensed">
                                            <tfoot>
                                                <tr>
                                                    <th colspan="2">
                                                        <a href="#" class="insert-file-row dokan-btn dokan-btn-sm dokan-btn-success" data-row="<?php
                                                            $file = array(
                                                                'file' => '',
                                                                'name' => ''
                                                            );
                                                            ob_start();
                                                            include DOKAN_INC_DIR . '/woo-views/html-product-download.php';
                                                            echo esc_attr( ob_get_clean() );
                                                        ?>"><?php _e( 'Add File', 'dokan' ); ?></a>
                                                    </th>
                                                </tr>
                                            </tfoot>
                                            <thead>
                                                <tr>
                                                    <th><?php _e( 'Name', 'dokan' ); ?> <span class="tips" title="<?php _e( 'This is the name of the download shown to the customer.', 'dokan' ); ?>">[?]</span></th>
                                                    <th><?php _e( 'File URL', 'dokan' ); ?> <span class="tips" title="<?php _e( 'This is the URL or absolute path to the file which customers will get access to.', 'dokan' ); ?>">[?]</span></th>
                                                    <th><?php _e( 'Action', 'dokan' ); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $downloadable_files = get_post_meta( $post_id, '_downloadable_files', true );

                                                if ( $downloadable_files ) {
                                                    foreach ( $downloadable_files as $key => $file ) {
                                                        include DOKAN_INC_DIR . '/woo-views/html-product-download.php';
                                                    }
                                                }
                                                ?>
                                            </tbody>
                                        </table>

                                        <div class="dokan-clearfix">
                                            <div class="content-half-part">
                                                <label for="_download_limit" class="form-label"><?php _e( 'Download Limit', 'dokan' ); ?></label>
                                                <?php dokan_post_input_box( $post_id, '_download_limit', array( 'placeholder' => __( 'e.g. 4', 'dokan' ) ) ); ?>
                                            </div><!-- .content-half-part -->

                                            <div class="content-half-part">
                                                <label for="_download_expiry" class="form-label"><?php _e( 'Download Expiry', 'dokan' ); ?></label>
                                                <?php dokan_post_input_box( $post_id, '_download_expiry', array( 'placeholder' => __( 'Number of days', 'dokan' ) ) ); ?>
                                            </div><!-- .content-half-part -->
                                        </div>

                                    </div> <!-- .dokan-side-body -->
                                </div> <!-- .downloadable -->

                                <?php do_action( 'dokan_product_edit_after_downloadable', $post, $post_id ); ?>
                                <?php do_action( 'dokan_product_edit_after_sidebar', $post, $post_id ); ?>

                            </div><!-- .dokan-side-right -->
                        </div><!-- .dokan-product-inventory -->

                        <?php do_action( 'dokan_product_edit_after_inventory_variants', $post, $post_id ); ?>

                        <div class="dokan-other-options dokan-edit-row dokan-clearfix">
                            <div class="dokan-side-left">
                                <h2><?php _e( 'Other Options', 'dokan' ); ?></h2>
                            </div>

                            <div class="dokan-side-right">
                                <?php if ( $post_id ): ?>
                                    <div class="dokan-form-group">
                                        <label for="post_status" class="form-label"><?php _e( 'Product Status', 'dokan' ); ?></label>
                                        <?php if ( $post_status != 'pending' ) { ?>
                                            <?php $post_statuses = apply_filters( 'dokan_post_status', array(
                                                'publish' => __( 'Online', 'dokan' ),
                                                'draft'   => __( 'Draft', 'dokan' )
                                            ), $post ); ?>

                                            <select id="post_status" class="dokan-form-control" name="post_status">
                                                <?php foreach ( $post_statuses as $status => $label ) { ?>
                                                    <option value="<?php echo $status; ?>"<?php selected( $post_status, $status ); ?>><?php echo $label; ?></option>
                                                <?php } ?>
                                            </select>
                                        <?php } else { ?>
                                            <?php $pending_class = $post_status == 'pending' ? '  dokan-label dokan-label-warning': ''; ?>
                                            <span class="dokan-toggle-selected-display<?php echo $pending_class; ?>"><?php echo dokan_get_post_status( $post_status ); ?></span>
                                        <?php } ?>
                                    </div>
                                <?php endif ?>

                                <div class="dokan-form-group">
                                    <label for="_visibility" class="form-label"><?php _e( 'Visibility', 'dokan' ); ?></label>
                                    <?php dokan_post_input_box( $post_id, '_visibility', array( 'options' => array(
                                        'visible' => __( 'Catalog or Search', 'dokan' ),
                                        'catalog' => __( 'Catalog', 'dokan' ),
                                        'search'  => __( 'Search', 'dokan' ),
                                        'hidden'  => __( 'Hidden', 'dokan ')
                                    ) ), 'select' ); ?>
                                </div>

                                <div class="dokan-form-group">
                                    <label for="_purchase_note" class="form-label"><?php _e( 'Purchase Note', 'dokan' ); ?></label>
                                    <?php dokan_post_input_box( $post_id, '_purchase_note', array( 'placeholder' => __( 'Customer will get this info in their order email', 'dokan' ) ), 'textarea' ); ?>
                                </div>

                                <div class="dokan-form-group">
                                    <?php $_enable_reviews = ( $post->comment_status == 'open' ) ? 'yes' : 'no'; ?>
                                    <?php dokan_post_input_box( $post_id, '_enable_reviews', array('value' => $_enable_reviews, 'label' => __( 'Enable product reviews', 'dokan' ) ), 'checkbox' ); ?>
                                </div>

                            </div>
                        </div><!-- .dokan-other-options -->

                        <?php if ( $post_id ): ?>
                            <?php do_action( 'dokan_product_edit_after_options' ); ?>
                        <?php endif; ?>

                        <?php wp_nonce_field( 'dokan_add_new_product', 'dokan_add_new_product_nonce' ); ?>
                        <input type="submit" name="dokan_add_product" class="dokan-btn dokan-btn-theme dokan-btn-lg btn-block" value="<?php esc_attr_e( 'Save Product', 'dokan' ); ?>"/>

                    </form>

                <?php } else { ?>

                    <?php dokan_seller_not_enabled_notice(); ?>

                <?php } ?>

            <?php } else { ?>

                <?php do_action( 'dokan_can_post_notice' ); ?>

            <?php } ?>
        </div> <!-- #primary .content-area -->
    </div>

    <?php

        /**
         *  dokan_dashboard_content_after hook
         *
         *  @since 2.4
         */
        do_action( 'dokan_dashboard_content_after' );
    ?>

</div><!-- .dokan-dashboard-wrap -->
<div class="dokan-clearfix"></div>

<?php

    /**
     *  dokan_dashboard_content_before hook
     *
     *  @since 2.4
     */
    do_action( 'dokan_dashboard_wrap_after', $post, $post_id );

    wp_reset_postdata();

    if ( ! $from_shortcode ) {
        get_footer();
    }
?>

