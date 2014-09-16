<?php

/**
*  Tempalte shortcode class file
*
*  @load all shortcode for template  rendering
*/
class Dokan_Template_Shortcodes {

	public static $errors;
	public static $product_cat;
	public static $post_content;
	public static $validated;
	public static $validate;

	function __construct() {

		add_action( 'template_redirect', array( $this, 'handle_all_submit' ), 11 );
        add_shortcode( 'dokan-dashboard', array( $this, 'load_template_files' ) );
        add_shortcode( 'dokan-best-selling-product', array( $this, 'best_selling_product_shortcode' ) );
		add_shortcode( 'dokan-top-rated-product', array( $this, 'top_rated_product_shortcode' ) );
	}

	public static function init() {
        static $instance = false;

        if( !$instance ) {
            $instance = new Dokan_Template_Shortcodes();
        }

        return $instance;
    }


    public function load_template_files() {
    	global $wp;

	    if ( isset( $wp->query_vars['reports'] ) ) {
	        return dokan_get_template_part( 'reports' );
	    }

	    if ( isset( $wp->query_vars['products'] ) ) {
	        return dokan_get_template_part( 'products' );
	    }

	    if ( isset( $wp->query_vars['new-product'] ) ) {
	        return dokan_get_template_part( 'new-product' );
	    }

	    if ( isset( $wp->query_vars['orders'] ) ) {
	        return dokan_get_template_part( 'orders' );
	    }

	    if ( isset( $wp->query_vars['coupons'] ) ) {
	        return dokan_get_template_part( 'coupons' );
	    }

	    if ( isset( $wp->query_vars['reviews'] ) ) {
	        return dokan_get_template_part( 'reviews' );
	    }

	    if ( isset( $wp->query_vars['withdraw'] ) ) {
	        return dokan_get_template_part( 'withdraw' );
	    }

	    if ( isset( $wp->query_vars['settings'] ) ) {
	        return dokan_get_template_part( 'settings' );
	    }

        //do_action( 'dokan_dashboard_template_render' );

	    return apply_filters( 'dokan_dashboard_template_render',  dokan_get_template_part( 'dashboard' ) );
    }

    function handle_all_submit() {
    	$errors = array();
        self::$product_cat = -1;
        self::$post_content = __( 'Details about your product...', 'dokan' );

        if ( ! $_POST ) {
            return;
        }

        if ( isset( $_POST['add_product'] ) ) {
            $post_title = trim( $_POST['post_title'] );
            $post_content = trim( $_POST['post_content'] );
            $post_excerpt = trim( $_POST['post_excerpt'] );
            $price = floatval( $_POST['price'] );
            $product_cat = intval( $_POST['product_cat'] );
            $featured_image = absint( $_POST['feat_image_id'] );

            if ( empty( $post_title ) ) {
                $errors[] = __( 'Please enter product title', 'dokan' );
            }

            if ( $product_cat < 0 ) {
                $errors[] = __( 'Please select a category', 'dokan' );
            }

            self::$errors = apply_filters( 'dokan_can_add_product', $errors );

            if ( !self::$errors ) {

                $product_status = dokan_get_new_post_status();
                $post_data = array(
                    'post_type'    => 'product',
                    'post_status'  => $product_status,
                    'post_title'   => $post_title,
                    'post_content' => $post_content,
                    'post_excerpt' => $post_excerpt,
                );

                $product_id = wp_insert_post( $post_data );

                if ( $product_id ) {

                    /** set images **/
                    if ( $featured_image ) {
                        set_post_thumbnail( $product_id, $featured_image );
                    }

                    /** set product category * */
                    wp_set_object_terms( $product_id, (int) $_POST['product_cat'], 'product_cat' );
                    wp_set_object_terms( $product_id, 'simple', 'product_type' );

                    update_post_meta( $product_id, '_regular_price', $price );
                    update_post_meta( $product_id, '_sale_price', '' );
                    update_post_meta( $product_id, '_price', $price );
                    update_post_meta( $product_id, '_visibility', 'visible' );

                    do_action( 'dokan_new_product_added', $product_id, $post_data );

                    Dokan_Email::init()->new_product_added( $product_id, $product_status );

                    wp_redirect( dokan_edit_product_url( $product_id ) );
                }
            }
        }

        if ( isset( $_GET['product_id'] ) ) {
            $post_id = intval( $_GET['product_id'] );
        } else {
            global $post, $product;
            $post_id = $post->ID;
        }


        if ( isset( $_POST['update_product']) ) {
            $product_info = array(
                'ID'             => $post_id,
                'post_title'     => sanitize_text_field( $_POST['post_title'] ),
                'post_content'   => $_POST['post_content'],
                'post_excerpt'   => $_POST['post_excerpt'],
                'post_status'    => isset( $_POST['post_status'] ) ? $_POST['post_status'] : 'pending',
                'comment_status' => isset( $_POST['_enable_reviews'] ) ? 'open' : 'closed'
            );

            wp_update_post( $product_info );

            /** set product category * */
            wp_set_object_terms( $post_id, (int) $_POST['product_cat'], 'product_cat' );
            wp_set_object_terms( $post_id, 'simple', 'product_type' );

            dokan_process_product_meta( $post_id );

            /** set images **/
            $featured_image = absint( $_POST['feat_image_id'] );
            if ( $featured_image ) {
                set_post_thumbnail( $post_id, $featured_image );
            }

            $edit_url = dokan_edit_product_url( $post_id );
            wp_redirect( add_query_arg( array( 'message' => 'success' ), $edit_url ) );
        }


		dokan_delete_product_handler();

		// Coupon functionality
		$dokan_template_coupons = Dokan_Template_Coupons::init();

		self::$validated = $dokan_template_coupons->validate();

		if ( !is_wp_error( self::$validated ) ) {
		    $dokan_template_coupons->coupons_create();
		}

		$dokan_template_coupons->coupun_delete();

		// Withdraw functionality
		$dokan_withdraw = Dokan_Template_Withdraw::init();
		self::$validate = $dokan_withdraw->validate();

		if( self::$validate !== false && !is_wp_error( self::$validate ) ) {
		    $dokan_withdraw->insert_withdraw_info();
		}

		$dokan_withdraw->cancel_pending();

    }

    function best_selling_product_shortcode( $atts ) {
        $per_page = shortcode_atts( array(
            'no_of_product' => 8
        ), $atts );

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

    function top_rated_product_shortcode( $atts ) {
        $per_page = shortcode_atts( array(
            'no_of_product' => 8
        ), $atts );

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

}