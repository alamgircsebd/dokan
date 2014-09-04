<?php
/**
 * Template Name: New Product
 */

// dokan_redirect_login();
// dokan_redirect_if_not_seller();

// $errors = array();
// $product_cat = -1;
// $post_content = __( 'Details about your product...', 'dokan' );


// get_header();

// dokan_frontend_dashboard_scripts();
?>

<?php dokan_get_template( dirname(__FILE__) . '/dashboard-nav.php', array( 'active_menu' => 'product' ) ); ?>

<div class="dokan-dashboard-content">
    
    <div class="dokan-new-product-area">
        <?php if ( Dokan_Template_Shortcodes::$errors ) { ?>
            <div class="dokan-dashboard-contentdokan-alert dokan-alert-danger">
                <a class="close" data-dismiss="alert">&times;</a>

                <?php foreach ( Dokan_Template_Shortcodes::$errors as $error) { ?>

                    <strong>Error!</strong> <?php echo $error ?>.<br>

                <?php } ?>
            </div>
        <?php } ?>

        <?php

        $can_sell = apply_filters( 'dokan_can_post', true );

        if ( $can_sell ) {

            if ( dokan_is_seller_enabled( get_current_user_id() ) ) { ?>

            <form class="form" method="post">

                <div class="row product-edit-container dokan-clearfix">
                    <div class="content-half-part">
                        <div class="dokan-feat-image-upload">
                            <div class="instruction-inside">
                                <input type="hidden" name="feat_image_id" class="dokan-feat-image-id" value="0">
                                <i class="fa fa-cloud-upload"></i>
                                <a href="#" class="dokan-feat-image-btn btn btn-sm"><?php _e( 'Upload a product cover image', 'dokan' ); ?></a>
                            </div>

                            <div class="image-wrap dokan-hide">
                                <a class="close dokan-remove-feat-image">&times;</a>
                                    <img src="" alt="">
                            </div>
                        </div>
                    </div>
                    <div class="content-half-part">
                        <div class="dokan-form-group">
                            <input class="form-control" name="post_title" id="post-title" type="text" placeholder="<?php esc_attr_e( 'Product name..', 'dokan' ); ?>" value="<?php echo dokan_posted_input( 'post_title' ); ?>">
                        </div>

                        <div class="dokan-form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><?php echo get_woocommerce_currency_symbol(); ?></span>
                                <input class="form-control" name="price" id="product-price" type="text" placeholder="9.99" value="<?php echo dokan_posted_input( 'price' ); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <textarea name="post_excerpt" id="post-excerpt" rows="5" class="form-control" placeholder="<?php esc_attr_e( 'Short description about the product...', 'dokan' ); ?>"><?php echo dokan_posted_textarea( 'post_excerpt' ); ?></textarea>
                        </div>

                        <div class="form-group">
                        <?php
                        wp_dropdown_categories( array(
                            'show_option_none' => __( '- Select a category -', 'dokan' ),
                            'hierarchical' => 1,
                            'hide_empty' => 0,
                            'name' => 'product_cat',
                            'id' => 'product_cat',
                            'taxonomy' => 'product_cat',
                            'title_li' => '',
                            'class' => 'product_cat form-control',
                            'exclude' => '',
                            'selected' => Dokan_Template_Shortcodes::$product_cat,
                        ) );
                        ?>
                        </div>
                    </div>
                </div>

                <!-- <textarea name="post_content" id="" cols="30" rows="10" class="span7" placeholder="Describe your product..."><?php echo dokan_posted_textarea( 'post_content' ); ?></textarea> -->
                <div class="form-group">
                    <?php wp_editor( Dokan_Template_Shortcodes::$post_content, 'post_content', array('editor_height' => 50, 'quicktags' => false, 'media_buttons' => false, 'teeny' => true, 'editor_class' => 'post_content') ); ?>
                </div>

                <?php do_action( 'dokan_new_product_form' ); ?>

                <div class="form-group">
                    <input type="submit" name="add_product" class="btn btn-primary" value="<?php esc_attr_e( 'Add Product', 'dokan' ); ?>"/>
                </div>

            </form>

            <?php } else { ?>

                <?php dokan_seller_not_enabled_notice(); ?>

            <?php } ?>

        <?php } else { ?>

            <?php do_action( 'dokan_can_post_notice' ); ?>

        <?php } ?>
    </div><!-- .dokan-new-product-area -->
</div><!-- .dokan-dashboard-content -->
