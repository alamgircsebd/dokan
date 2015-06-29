<?php

/**
 * Tempalte shortcode class file
 *
 * @load all shortcode for template  rendering
 */
class Dokan_Template_Shortcodes {

    public static $errors;
    public static $product_cat;
    public static $post_content;
    public static $validated;
    public static $validate;

    /**
     *  Dokan template shortcodes __constract
     *  Initial loaded when class create an instanace
     */
    function __construct() {

        add_action( 'template_redirect', array( $this, 'handle_all_submit' ), 11 );
        add_action( 'template_redirect', array( $this, 'handle_delete_product' ) );

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
            if ( dokan_get_option( 'product_style', 'dokan_selling', 'old' ) == 'old' ) {
                dokan_get_template_part( 'products/new-product' );
            } elseif ( dokan_get_option( 'product_style', 'dokan_selling', 'old' ) == 'new' ) {
                dokan_get_template_part( 'products/new-product-single' );
            }
            return;
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
     * Handle all the form POST submit
     *
     * @return void
     */
    function handle_all_submit() {

        if ( ! is_user_logged_in() ) {
            return;
        }

        if ( ! dokan_is_user_seller( get_current_user_id() ) ) {
            return;
        }

        $errors = array();
        self::$product_cat  = -1;
        self::$post_content = __( 'Details about your product...', 'dokan' );

        if ( ! $_POST ) {
            return;
        }

        if ( isset( $_POST['dokan_add_product'] ) && wp_verify_nonce( $_POST['dokan_add_new_product_nonce'], 'dokan_add_new_product' ) ) {

            $post_title     = trim( $_POST['post_title'] );
            $post_content   = trim( $_POST['post_content'] );
            $post_excerpt   = isset( $_POST['post_excerpt'] ) ? trim( $_POST['post_excerpt'] ) : '';
            $price          = floatval( $_POST['_regular_price'] );
            $featured_image = absint( $_POST['feat_image_id'] );

            if ( empty( $post_title ) ) {

                $errors[] = __( 'Please enter product title', 'dokan' );
            }

            if( dokan_get_option( 'product_category_style', 'dokan_selling', 'single' ) == 'single' ) {
                $product_cat    = intval( $_POST['product_cat'] );
                if ( $product_cat < 0 ) {
                    $errors[] = __( 'Please select a category', 'dokan' );
                }
            } else {
                if( !isset( $_POST['product_cat'] ) && empty( $_POST['product_cat'] ) ) {
                    $errors[] = __( 'Please select atleast one category', 'dokan' );
                }
            }

            self::$errors = apply_filters( 'dokan_can_add_product', $errors );

            if ( !self::$errors ) {

                if( isset( $_POST['dokan_product_id'] ) && empty( $_POST['dokan_product_id'] ) ) {
                    $product_status = dokan_get_new_post_status();
                    $post_data = apply_filters( 'dokan_insert_product_post_data', array(
                        'post_type'    => 'product',
                        'post_status'  => $product_status,
                        'post_title'   => $post_title,
                        'post_content' => $post_content,
                        'post_excerpt' => $post_excerpt,
                    ) );

                    $product_id = wp_insert_post( $post_data );

                } else {
                    $post_id = (int)$_POST['dokan_product_id'];
                    $product_info = apply_filters( 'dokan_update_product_post_data', array(
                        'ID'             => $post_id,
                        'post_title'     => sanitize_text_field( $_POST['post_title'] ),
                        'post_content'   => $_POST['post_content'],
                        'post_excerpt'   => $_POST['post_excerpt'],
                        'post_status'    => isset( $_POST['post_status'] ) ? $_POST['post_status'] : 'pending',
                        'comment_status' => isset( $_POST['_enable_reviews'] ) ? 'open' : 'closed'
                    ) );

                    $product_id = wp_update_post( $product_info );
                }

                if ( $product_id ) {

                    /** set images **/
                    if ( $featured_image ) {
                        set_post_thumbnail( $product_id, $featured_image );
                    }

                    if( isset( $_POST['product_tag'] ) && !empty( $_POST['product_tag'] ) ) {
                        $tags_ids = array_map( 'intval', (array)$_POST['product_tag'] );
                        wp_set_object_terms( $product_id, $tags_ids, 'product_tag' );
                    }

                    /** set product category * */
                    if( dokan_get_option( 'product_category_style', 'dokan_selling', 'single' ) == 'single' ) {
                        wp_set_object_terms( $product_id, (int) $_POST['product_cat'], 'product_cat' );
                    } else {
                        if( isset( $_POST['product_cat'] ) && !empty( $_POST['product_cat'] ) ) {
                            $cat_ids = array_map( 'intval', (array)$_POST['product_cat'] );
                            wp_set_object_terms( $product_id, $cat_ids, 'product_cat' );
                        }
                    }

                    /** Set Product type by default simple */
                    if( isset( $_POST['_create_variation'] ) && $_POST['_create_variation'] == 'yes' ) {
                        wp_set_object_terms( $product_id, 'variable', 'product_type' );
                    } else {
                        wp_set_object_terms( $product_id, 'simple', 'product_type' );
                    }


                    update_post_meta( $product_id, '_regular_price', $price );
                    update_post_meta( $product_id, '_sale_price', '' );
                    update_post_meta( $product_id, '_price', $price );
                    update_post_meta( $product_id, '_visibility', 'visible' );

                    dokan_new_process_product_meta( $product_id );

                    if( isset( $_POST['dokan_product_id'] ) && empty( $_POST['dokan_product_id'] ) ) {
                        do_action( 'dokan_new_product_added', $product_id, $post_data );
                    }

                    if( isset( $_POST['dokan_product_id'] ) && empty( $_POST['dokan_product_id'] ) ) {
                        if ( dokan_get_option( 'product_add_mail', 'dokan_general', 'on' ) == 'on' ) {
                            Dokan_Email::init()->new_product_added( $product_id, $product_status );
                        }
                    }

                    wp_redirect( add_query_arg( array( 'message' => 'success' ), dokan_edit_product_url( $product_id ) ) );
                    exit;
                }
            }
        }

        if ( isset( $_POST['add_product'] ) && wp_verify_nonce( $_POST['dokan_add_new_product_nonce'], 'dokan_add_new_product' ) ) {
            $post_title     = trim( $_POST['post_title'] );
            $post_content   = trim( $_POST['post_content'] );
            $post_excerpt   = trim( $_POST['post_excerpt'] );
            $price          = floatval( $_POST['price'] );
            $featured_image = absint( $_POST['feat_image_id'] );

            if ( empty( $post_title ) ) {

                $errors[] = __( 'Please enter product title', 'dokan' );
            }

            if( dokan_get_option( 'product_category_style', 'dokan_selling', 'single' ) == 'single' ) {
                $product_cat    = intval( $_POST['product_cat'] );
                if ( $product_cat < 0 ) {
                    $errors[] = __( 'Please select a category', 'dokan' );
                }
            } else {
                if( !isset( $_POST['product_cat'] ) && empty( $_POST['product_cat'] ) ) {
                    $errors[] = __( 'Please select atleast one category', 'dokan' );
                }
            }

            self::$errors = apply_filters( 'dokan_can_add_product', $errors );

            if ( !self::$errors ) {

                $product_status = dokan_get_new_post_status();
                $post_data = apply_filters( 'dokan_insert_product_post_data', array(
                        'post_type'    => 'product',
                        'post_status'  => $product_status,
                        'post_title'   => $post_title,
                        'post_content' => $post_content,
                        'post_excerpt' => $post_excerpt,
                    ) );

                $product_id = wp_insert_post( $post_data );

                if ( $product_id ) {

                    /** set images **/
                    if ( $featured_image ) {
                        set_post_thumbnail( $product_id, $featured_image );
                    }

                    if( isset( $_POST['product_tag'] ) && !empty( $_POST['product_tag'] ) ) {
                        $tags_ids = array_map( 'intval', (array)$_POST['product_tag'] );
                        wp_set_object_terms( $product_id, $tags_ids, 'product_tag' );
                    }

                    /** set product category * */
                    if( dokan_get_option( 'product_category_style', 'dokan_selling', 'single' ) == 'single' ) {
                        wp_set_object_terms( $product_id, (int) $_POST['product_cat'], 'product_cat' );
                    } else {
                        if( isset( $_POST['product_cat'] ) && !empty( $_POST['product_cat'] ) ) {
                            $cat_ids = array_map( 'intval', (array)$_POST['product_cat'] );
                            wp_set_object_terms( $product_id, $cat_ids, 'product_cat' );
                        }
                    }

                    /** Set Product type by default simple */
                    wp_set_object_terms( $product_id, 'simple', 'product_type' );

                    update_post_meta( $product_id, '_regular_price', $price );
                    update_post_meta( $product_id, '_sale_price', '' );
                    update_post_meta( $product_id, '_price', $price );
                    update_post_meta( $product_id, '_visibility', 'visible' );

                    do_action( 'dokan_new_product_added', $product_id, $post_data );

                    if ( dokan_get_option( 'product_add_mail', 'dokan_general', 'on' ) == 'on' ) {
                        Dokan_Email::init()->new_product_added( $product_id, $product_status );
                    }

                    wp_redirect( dokan_edit_product_url( $product_id ) );
                    exit;
                }
            }
        }


        if ( isset( $_GET['product_id'] ) ) {
            $post_id = intval( $_GET['product_id'] );
        } else {
            global $post, $product;

            if ( !empty( $post ) ) {
                $post_id = $post->ID;
            }
        }


        if ( isset( $_POST['update_product'] ) && wp_verify_nonce( $_POST['dokan_edit_product_nonce'], 'dokan_edit_product' ) ) {
            $post_title     = trim( $_POST['post_title'] );
            if ( empty( $post_title ) ) {

                $errors[] = __( 'Please enter product title', 'dokan' );
            }

            if( dokan_get_option( 'product_category_style', 'dokan_selling', 'single' ) == 'single' ) {
                $product_cat    = intval( $_POST['product_cat'] );
                if ( $product_cat < 0 ) {
                    $errors[] = __( 'Please select a category', 'dokan' );
                }
            } else {
                if( !isset( $_POST['product_cat'] ) && empty( $_POST['product_cat'] ) ) {
                    $errors[] = __( 'Please select atleast one category', 'dokan' );
                }
            }

            self::$errors = apply_filters( 'dokan_can_edit_product', $errors );

            if ( !self::$errors ) {

                $product_info = array(
                    'ID'             => $post_id,
                    'post_title'     => sanitize_text_field( $_POST['post_title'] ),
                    'post_content'   => $_POST['post_content'],
                    'post_excerpt'   => $_POST['post_excerpt'],
                    'post_status'    => isset( $_POST['post_status'] ) ? $_POST['post_status'] : 'pending',
                    'comment_status' => isset( $_POST['_enable_reviews'] ) ? 'open' : 'closed'
                );

                wp_update_post( $product_info );

                /** Set Product tags */
                if( isset( $_POST['product_tag'] ) ) {
                    $tags_ids = array_map( 'intval', (array)$_POST['product_tag'] );
                } else {
                    $tags_ids = array();
                }
                wp_set_object_terms( $post_id, $tags_ids, 'product_tag' );


                /** set product category * */

                if( dokan_get_option( 'product_category_style', 'dokan_selling', 'single' ) == 'single' ) {
                    wp_set_object_terms( $post_id, (int) $_POST['product_cat'], 'product_cat' );
                } else {
                    if( isset( $_POST['product_cat'] ) && !empty( $_POST['product_cat'] ) ) {
                        $cat_ids = array_map( 'intval', (array)$_POST['product_cat'] );
                        wp_set_object_terms( $post_id, $cat_ids, 'product_cat' );
                    }
                }

                wp_set_object_terms( $post_id, 'simple', 'product_type' );

                /**  Process all variation products meta */
                dokan_process_product_meta( $post_id );

                /** set images **/
                $featured_image = absint( $_POST['feat_image_id'] );
                if ( $featured_image ) {
                    set_post_thumbnail( $post_id, $featured_image );
                }

                $edit_url = dokan_edit_product_url( $post_id );
                wp_redirect( add_query_arg( array( 'message' => 'success' ), $edit_url ) );
                exit;
            }
        }


    }

    /**
     * Handle delete product link
     *
     * @return void
     */
    function handle_delete_product() {

        if ( ! is_user_logged_in() ) {
            return;
        }

        if ( ! dokan_is_user_seller( get_current_user_id() ) ) {
            return;
        }

        dokan_delete_product_handler();
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
