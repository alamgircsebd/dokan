<?php

/**
 * Tempalte shortcode class file
 *
 * @load all shortcode for template  rendering
 */
class Dokan_Template_Shortcodes {

    /**
     *  Dokan template shortcodes __constract
     *  Initial loaded when class create an instanace
     *
     *  @since 2.4
     */
    function __construct() {
        add_shortcode( 'dokan-dashboard', array( $this, 'load_template_files' ) );
        add_shortcode( 'dokan-best-selling-product', array( $this, 'best_selling_product_shortcode' ) );
        add_shortcode( 'dokan-top-rated-product', array( $this, 'top_rated_product_shortcode' ) );
        add_shortcode( 'dokan-stores', array( $this, 'store_listing' ) );
        add_shortcode( 'dokan-my-orders', array( $this, 'my_orders_page' ) );
    }

    /**
     * Singleton method
     *
     * @return self
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new Dokan_Template_Shortcodes();
        }

        return $instance;
    }

    /**
     * Load template files
     *
     * Based on the query vars, load the appropriate template files
     * in the frontend user dashboard.
     *
     * @return void
     */
    public function load_template_files() {
        global $wp;

        if ( ! function_exists( 'WC' ) ) {
            return sprintf( __( 'Please install <a href="%s"><strong>WooCommerce</strong></a> plugin first', 'dokan' ), 'http://wordpress.org/plugins/woocommerce/' );
        }

        if ( isset( $wp->query_vars['products'] ) ) {
            dokan_get_template_part( 'products/products' );
            return;
        }

        if ( isset( $wp->query_vars['new-product'] ) ) {
            do_action( 'dokan_render_new_product_template', $wp->query_vars );
        }

        if ( isset( $wp->query_vars['orders'] ) ) {
            dokan_get_template_part( 'orders/orders' );
            return;
        }

        if ( isset( $wp->query_vars['withdraw'] ) ) {
            dokan_get_template_part( 'withdraw/withdraw' );
            return;
        }

        if ( isset( $wp->query_vars['settings'] ) ) {
            dokan_get_template_part('settings/store');
            return;
        }

        if ( isset( $wp->query_vars['page'] ) ) {
            dokan_get_template_part( 'dashboard/dashboard' );
            return;
        }

        do_action( 'dokan_load_custom_template', $wp->query_vars );
    }

    /**
     * Render best selling products
     *
     * @param  array  $atts
     *
     * @return string
     */
    function best_selling_product_shortcode( $atts ) {
        /**
        * Filter return the number of best selling product per page.
        *
        * @since 2.2
        *
        * @param array
        */
        $per_page = shortcode_atts( apply_filters( 'dokan_best_selling_product_per_page', array(
            'no_of_product' => 8
        ), $atts ) );

        ob_start();
        ?>
        <ul>
            <?php
            $best_selling_query = dokan_get_best_selling_products();
            ?>
            <?php while ( $best_selling_query->have_posts() ) : $best_selling_query->the_post(); ?>

                <?php wc_get_template_part( 'content', 'product' ); ?>

            <?php endwhile; ?>
        </ul>
        <?php

        return ob_get_clean();
    }

    /**
     * Render top rated products via shortcode
     *
     * @param  array  $atts
     *
     * @return string
     */
    function top_rated_product_shortcode( $atts ) {
        /**
        * Filter return the number of top rated product per page.
        *
        * @since 2.2
        *
        * @param array
        */
        $per_page = shortcode_atts( apply_filters( 'dokan_top_rated_product_per_page', array(
            'no_of_product' => 8
        ), $atts ) );

        ob_start();
        ?>
        <ul>
            <?php
            $best_selling_query = dokan_get_top_rated_products();
            ?>
            <?php while ( $best_selling_query->have_posts() ) : $best_selling_query->the_post(); ?>

                <?php wc_get_template_part( 'content', 'product' ); ?>

            <?php endwhile; ?>
        </ul>
        <?php

        return ob_get_clean();
    }

    /**
     * Displays the store lists
     *
     * @param  array $atts
     *
     * @return string
     */
    function store_listing( $atts ) {
        global $post;

        /**
        * Filter return the number of store listing number per page.
        *
        * @since 2.2
        *
        * @param array
        */
        $attr = shortcode_atts( apply_filters( 'dokan_store_listing_per_page', array(
                'per_page' => 10,
            ) ), $atts );

        $paged  = max( 1, get_query_var( 'paged' ) );
        $limit  = $attr['per_page'];
        $offset = ( $paged - 1 ) * $limit;

        $sellers = dokan_get_sellers( $limit, $offset );

        ob_start();

        if ( $sellers['users'] ) {
            ?>
            <ul class="dokan-seller-wrap">
                <?php
                foreach ( $sellers['users'] as $seller ) {
                    $store_info = dokan_get_store_info( $seller->ID );
                    $banner_id  = isset( $store_info['banner'] ) ? $store_info['banner'] : 0;
                    $store_name = isset( $store_info['store_name'] ) ? esc_html( $store_info['store_name'] ) : __( 'N/A', 'dokan' );
                    $store_url  = dokan_get_store_url( $seller->ID );
                    ?>

                    <li class="dokan-single-seller">
                        <div class="dokan-store-thumbnail">

                            <a href="<?php echo $store_url; ?>">
                                <?php if ( $banner_id ) {
                                    $banner_url = wp_get_attachment_image_src( $banner_id, 'medium' );
                                    ?>
                                    <img class="dokan-store-img" src="<?php echo esc_url( $banner_url[0] ); ?>" alt="<?php echo esc_attr( $store_name ); ?>">
                                <?php } else { ?>
                                    <img class="dokan-store-img" src="<?php echo dokan_get_no_seller_image(); ?>" alt="<?php _e( 'No Image', 'dokan' ); ?>">
                                <?php } ?>
                            </a>

                            <div class="dokan-store-caption">
                                <h3><a href="<?php echo $store_url; ?>"><?php echo $store_name; ?></a></h3>

                                <address>
                                    <?php if ( isset( $store_info['address'] ) ) {
                                        $address = esc_html( $store_info['address'] );
                                        echo nl2br( $address );
                                    } ?>

                                    <?php if ( isset( $store_info['phone'] ) && !empty( $store_info['phone'] ) ) { ?>
                                        <br>
                                        <abbr title="<?php _e( 'Phone', 'dokan' ); ?>"><?php _e( 'P:', 'dokan' ); ?></abbr> <?php echo esc_html( $store_info['phone'] ); ?>
                                    <?php } ?>

                                </address>

                                <p><a class="dokan-btn dokan-btn-theme" href="<?php echo $store_url; ?>"><?php _e( 'Visit Store', 'dokan' ); ?></a></p>

                            </div> <!-- .caption -->
                        </div> <!-- .thumbnail -->
                    </li> <!-- .single-seller -->
                <?php } ?>

            </ul> <!-- .dokan-seller-wrap -->

            <?php
            $user_count = $sellers['count'];
            $num_of_pages = ceil( $user_count / $limit );

            if ( $num_of_pages > 1 ) {
                echo '<div class="pagination-container clearfix">';
                $page_links = paginate_links( array(
                        'current'   => $paged,
                        'total'     => $num_of_pages,
                        'base'      => str_replace( $post->ID, '%#%', esc_url( get_pagenum_link( $post->ID ) ) ),
                        'type'      => 'array',
                        'prev_text' => __( '&larr; Previous', 'dokan' ),
                        'next_text' => __( 'Next &rarr;', 'dokan' ),
                    ) );

                if ( $page_links ) {
                    $pagination_links  = '<div class="pagination-wrap">';
                    $pagination_links .= '<ul class="pagination"><li>';
                    $pagination_links .= join( "</li>\n\t<li>", $page_links );
                    $pagination_links .= "</li>\n</ul>\n";
                    $pagination_links .= '</div>';

                    echo $pagination_links;
                }

                echo '</div>';
            }
            ?>

            <?php
        } else {
            ?>

            <p class="dokan-error"><?php _e( 'No seller found!', 'dokan' ); ?></p>

            <?php
        }

        $content = ob_get_clean();

        return apply_filters( 'dokan_seller_listing', $content, $attr );
    }

    /**
     * Render my orders page
     *
     * @return string
     */
    function my_orders_page() {
        return dokan_get_template_part( 'my-orders' );
    }

}
