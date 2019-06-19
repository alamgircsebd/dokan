<?php
/**
 * The Template for displaying vendor biography.
 *
 * @package dokan
 */

$store_user = get_userdata( get_query_var( 'author' ) );
$store_info = dokan_get_store_info( $store_user->ID );

get_header( 'shop' );
?>

<?php do_action( 'woocommerce_before_main_content' ); ?>

<?php if ( dokan_get_option( 'enable_theme_store_sidebar', 'dokan_general', 'off' ) == 'off' ) { ?>
    <div id="dokan-secondary" class="dokan-clearfix dokan-w3 dokan-store-sidebar" role="complementary" style="margin-right:3%;">
        <div class="dokan-widget-area widget-collapse">
            <?php
            if ( ! dynamic_sidebar( 'sidebar-store' ) ) {
                $args = array(
                    'before_widget' => '<aside class="widget %s">',
                    'after_widget'  => '</aside>',
                    'before_title'  => '<h3 class="widget-title">',
                    'after_title'   => '</h3>',
                );

                if ( class_exists( 'Dokan_Store_Location' ) ) {
                    the_widget( 'Dokan_Store_Category_Menu', array( 'title' => __( 'Store Category', 'dokan' ) ), $args );

                    if ( dokan_get_option( 'store_map', 'dokan_general', 'on' ) == 'on' ) {
                        the_widget( 'Dokan_Store_Location', array( 'title' => __( 'Store Location', 'dokan' ) ), $args );
                    }

                    if ( dokan_get_option( 'store_open_close', 'dokan_general', 'on' ) == 'on' ) {
                        the_widget( 'Dokan_Store_Open_Close', array( 'title' => __( 'Store Time', 'dokan-lite' ) ), $args );
                    }

                    if( dokan_get_option( 'contact_seller', 'dokan_general', 'on' ) == 'on' ) {
                        the_widget( 'Dokan_Store_Contact_Form', array( 'title' => __( 'Contact Vendor', 'dokan' ) ), $args );
                    }
                }
            }

            do_action( 'dokan_sidebar_store_after', $store_user, $store_info ); ?>
        </div>
    </div><!-- #secondary .widget-area -->
    <?php
} else {
    get_sidebar( 'store' );
}
?>

<div id="dokan-primary" class="dokan-single-store dokan-w8">
    <div id="dokan-content" class="store-review-wrap woocommerce" role="main">

        <?php dokan_get_template_part( 'store-header' ); ?>

        <div id="vendor-biography">
            <div id="comments">
            <?php do_action( 'dokan_vendor_biography_tab_before', $store_user, $store_info ); ?>

            <h2 class="headline"><?php echo apply_filters( 'dokan_vendor_biography_title', __( 'Vendor Biography', 'dokan' ) ); ?></h2>

            <?php
                if ( ! empty( $store_info['vendor_biography'] ) ) {
                    printf( '%s', $store_info['vendor_biography'] );
                }
            ?>

            <?php do_action( 'dokan_vendor_biography_tab_after', $store_user, $store_info ); ?>
            </div>
        </div>

    </div><!-- #content .site-content -->
</div><!-- #primary .content-area -->

<?php do_action( 'woocommerce_after_main_content' ); ?>

<?php get_footer(); ?>
