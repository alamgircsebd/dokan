<?php
/**
 * The Template for displaying all single posts.
 *
 * @package dokan
 * @package dokan - 2014 1.0
 */

$store_user = get_userdata( get_query_var( 'author' ) );
$store_info = dokan_get_store_info( $store_user->ID );

$scheme = is_ssl() ? 'https' : 'http';
wp_enqueue_script( 'google-maps', $scheme . '://maps.google.com/maps/api/js?sensor=true' );

get_header();
?>

<?php get_sidebar( 'store' ); ?>

<div id="primary" class="content-area dokan-single-store">
    <div id="content" class="site-content store-page-wrap woocommerce" role="main">

        <?php dokan_get_template_part( 'store-header' ); ?>

        <?php do_action( 'dokan_store_profile_frame_after', $store_user, $store_info ); ?>

        <?php if ( have_posts() ) { ?>

            <div class="seller-items">

                <?php woocommerce_product_loop_start(); ?>

                    <?php while ( have_posts() ) : the_post(); ?>

                        <?php wc_get_template_part( 'content', 'product' ); ?>

                    <?php endwhile; // end of the loop. ?>

                <?php woocommerce_product_loop_end(); ?>

            </div>

            <?php dokan_content_nav( 'nav-below' ); ?>

        <?php } else { ?>

            <p class="dokan-info"><?php _e( 'No products were found of this seller!', 'dokan' ); ?></p>

        <?php } ?>
    </div>

    </div><!-- #content .site-content -->
</div><!-- #primary .content-area -->

<?php get_footer(); ?>