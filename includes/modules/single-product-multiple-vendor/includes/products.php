<?php

/**
* Product related functionality
*
* @package Dokan
*/
class Dokan_SPMV_Products {

    /**
     * Load autometically when class initiate
     *
     * @since 1.0.0
     */
    public function __construct() {
        $enable_option = dokan_get_option( 'enable_pricing', 'dokan_spmv', 'on' );
        $display_position = dokan_get_option( 'available_vendor_list_position', 'dokan_spmv', 'below_tabs' );

        if ( 'off' == $enable_option ) {
            return;
        }

        add_action( 'template_redirect', array( $this, 'handle_sell_item_action' ), 10 );
        add_action( 'woocommerce_single_product_summary', array( $this, 'show_sell_now_btn' ), 32 );

        if ( 'below_tabs' == $display_position ) {

            add_action( 'woocommerce_after_single_product_summary', array( $this, 'show_vendor_comparison' ), 1 );

        } else if ( 'inside_tabs' == $display_position ) {

            add_filter( 'woocommerce_product_tabs', array( $this, 'show_vendor_comparison_inside_tab' ) );

        } else if ( 'after_tabs' == $display_position  ) {
            add_action( 'woocommerce_after_single_product_summary', array( $this, 'show_vendor_comparison' ), 12 );
        }
    }

    /**
     * Check is seller is elligible for sell this item
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function is_valid_user( $product_id ) {
        if ( ! is_user_logged_in() ) {
            return false;
        }

        $user_id = get_current_user_id();

        if ( ! dokan_is_user_seller( $user_id ) ) {
            return false;
        }

        $product_author = get_post_field( 'post_author', $product_id );

        if ( $user_id == $product_author ) {
            return false;
        }

        if ( $this->check_already_cloned( $product_id ) ) {
            return false;
        }

        return true;
    }

    /**
     * Check already cloned this product
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function check_already_cloned( $product_id ) {
        global $wpdb;

        $map_id = get_post_meta( $product_id, '_has_multi_vendor', true );
        $user_id = get_current_user_id();

        if ( empty( $map_id ) ) {
            return false;
        }

        $sql     = "SELECT * FROM `{$wpdb->prefix}dokan_product_map` WHERE `map_id`= '$map_id' AND `seller_id` = '$user_id'";
        $results = $wpdb->get_row( $sql );

        if ( $results ) {
            return true;
        }

        return false;
    }

    /**
     * Handle sell item form submission
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function handle_sell_item_action() {

        if ( ! isset( $_POST['dokan_sell_this_item'] ) ) {
            return;
        }

        $user_id = ! empty( $_POST['user_id'] ) ? $_POST['user_id'] : 0;
        $product_id = ! empty( $_POST['product_id'] ) ? $_POST['product_id'] : 0;

        if ( ! $user_id || ! $product_id ) {
            return;
        }

        if ( ! $this->is_valid_user( $product_id ) ) {
            return;
        }

        if ( ! wp_verify_nonce( $_POST['dokan-sell-item-nonce'], 'dokan-sell-item-action' ) ) {
            return;
        }

        $wo_dup = new WC_Admin_Duplicate_Product();
        $update_product_ids = array();

        // Compatibility for WC 3.0+
        if ( version_compare( WC_VERSION, '2.7', '>' ) ) {
            // For latest version 3.0+
            $product = wc_get_product( $product_id );
            $clone_product =  $wo_dup->product_duplicate( $product );
            $clone_product_id =  $clone_product->get_id();
        } else {
            // For older version < 3.0+
            $post = get_post( $product_id );
            $product = wc_get_product( $product_id );
            $clone_product_id =  $wo_dup->duplicate_product( $post );
        }

        $product_status = apply_filters( 'dokan_cloned_product_status', dokan_get_new_post_status() );

        $has_multivendor = get_post_meta( $product_id, '_has_multi_vendor', true );

        if ( ! $has_multivendor ) {
            $has_multivendor = $this->get_next_map_id();
            $update_product_ids[] = $product_id;
        }

        $update_product_ids[] = $clone_product_id;

        if ( $this->set_map_id( $has_multivendor, $update_product_ids ) ){
            update_post_meta( $product_id, '_has_multi_vendor', $has_multivendor );
            update_post_meta( $clone_product_id, '_has_multi_vendor', $has_multivendor );
        }

        wp_update_post(
            array(
                'ID' => intval( $clone_product_id ),
                'post_title' => $product->get_title(),
                'post_status' => $product_status,
                'post_author' => $user_id
            )
        );

        wp_redirect( dokan_edit_product_url( $clone_product_id ) );
        exit();
    }

    /**
     * Show vendor comparison inside product tabs
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function show_vendor_comparison_inside_tab( $tabs ) {
        $title = dokan_get_option( 'available_vendor_list_title', 'dokan_spmv', __( 'Other Available Vendor', 'dokan' ) );

        $tabs['vendor_comaprison'] = array(
            'title'    => $title,
            'priority' => 100,
            'callback' => array( $this, 'show_vendor_comparison' )
        );

        return $tabs;
    }

    /**
     * Added Sell this item btn
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function show_sell_now_btn() {
        global $product;

        if ( ! $this->is_valid_user( $product->get_id() ) ) {
            return;
        }

        $sell_item_btn_txt = dokan_get_option( 'sell_item_btn', 'dokan_spmv', __( 'Sell This Item', 'dokan' ) );

        ?>
        <form method="post">
            <?php wp_nonce_field( 'dokan-sell-item-action', 'dokan-sell-item-nonce' ); ?>
            <button name="dokan_sell_this_item" class="dokan-btn dokan-btn-theme"><?php echo $sell_item_btn_txt; ?></button>
            <input type="hidden" name="product_id" value="<?php echo $product->get_id(); ?>">
            <input type="hidden" name="user_id" value="<?php echo get_current_user_id(); ?>">
        </form>
        <?php
    }

    /**
     * Show Vendor comparison
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function show_vendor_comparison() {
        global $product;

        if ( ! $product ) {
            return;
        }

        $lists = $this->get_other_reseller_vendors( $product->get_id() );

        if ( $lists ) {
            ?>
            <div class="dokan-other-vendor-camparison">

                <h3>
                    <?php echo dokan_get_option( 'available_vendor_list_title', 'dokan_spmv', __( 'Other Available Vendor', 'dokan' ) ); ?>
                </h3>

                <table class="table dokan-table dokan-other-vendor-camparison-table">
                    <thead>
                        <tr>
                            <th><?php _e( 'Vendor', 'dokan' ); ?></th>
                            <th><?php _e( 'Price', 'dokan' ); ?></th>
                            <th><?php _e( 'Rating', 'dokan' ); ?></th>
                            <th></th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ( $lists as $key => $list ): ?>
                            <?php
                                $product_obj    = wc_get_product( $list->product_id );
                                $post_author_id = get_post_field( 'post_author', $product_obj->get_id() );
                                $seller_info    = dokan_get_store_info( $post_author_id );
                                $rating_count   = $product_obj->get_rating_count();
                                $review_count   = $product_obj->get_review_count();
                                $average        = $product_obj->get_average_rating();

                                if ( ! $product_obj->is_visible() ) {
                                    continue;
                                }
                            ?>
                            <tr <?php echo ( $list->product_id == $product->get_id() ) ? 'class="active"' : ''; ?>>

                                <td>
                                    <?php echo get_avatar( $post_author_id, 30 ); ?>
                                    <a href="<?php echo dokan_get_store_url( $post_author_id ); ?>"><?php echo $seller_info['store_name'] ?></a>
                                </td>

                                <td class="td-price"><?php echo $product_obj->get_price_html(); ?></td>

                                <td>
                                    <div class="woocommerce-product-rating">
                                        <?php echo wc_get_rating_html( $average, $rating_count ); ?>
                                        <?php if ( comments_open() ) : ?><a href="#reviews" class="woocommerce-review-link" rel="nofollow">(<?php printf( _n( '%s customer review', '%s customer reviews', $review_count, 'woocommerce' ), '<span class="count">' . esc_html( $review_count ) . '</span>' ); ?>)</a><?php endif ?>
                                    </div>
                                </td>

                                <td>
                                    <a href="<?php echo dokan_get_store_url( $post_author_id ); ?>" class="dokan-btn dokan-btn-theme tips" title="<?php _e( 'View Store', 'dokan' ); ?>">
                                        <i><i class="fa fa-external-link"></i></i>
                                    </a>
                                    <a href="<?php echo $product_obj->get_permalink(); ?>" class="dokan-btn dokan-btn-theme tips" title="<?php _e( 'View Product', 'dokan' ); ?>">
                                        <i class="fa fa-eye" aria-hidden="true"></i>
                                    </a>
                                    <?php if ( 'simple' == $product_obj->get_type() ): ?>
                                        <?php
                                            echo sprintf( '<a href="%s" data-quantity="%s" data-product_id="%s" data-product_sku="%s" class="%s" title="%s">%s</a>',
                                                esc_url( $product_obj->add_to_cart_url() ),
                                                1,
                                                esc_attr( $product_obj->get_id() ),
                                                esc_attr( $product_obj->get_sku() ),
                                                'dokan-btn dokan-btn-theme tips',
                                                __( 'Add to cart', 'dokan' ),
                                                '<i class="fa fa-shopping-cart"></i>'
                                            );
                                        ?>
                                    <?php elseif ( 'variable' == $product_obj->get_type() ) : ?>
                                        <a href="<?php echo $product_obj->get_permalink(); ?>" class="dokan-btn dokan-btn-theme tips" title="<?php _e( 'Select Options', 'dokan' ); ?>"><i class="fa fa-bars"></i></a>
                                    <?php endif ?>

                                </td>

                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>

            <style>
                .dokan-other-vendor-camparison {
                    clear: both;
                    margin: 10px 0px 20px;
                }

                .dokan-other-vendor-camparison h3 {
                    margin-bottom: 15px;
                }
            </style>

            <script>
                ;(function($) {
                    $(document).ready( function() {
                        $('.tips').tooltip();
                    })
                })(jQuery);
            </script>
            <?php
        }
    }

    /**
     * Get mapping ID for next execution
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function get_next_map_id() {
        global $wpdb;

        $sql = "SELECT MAX(`map_id`) as max_id FROM `{$wpdb->prefix}dokan_product_map`";
        $current_id = $wpdb->get_var( $sql );

        if ( ! $current_id ) {
            return 1;
        }

        return $current_id+1;
    }

    /**
     * Set mapping ids for product
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function set_map_id( $map_id, $product_ids ) {
        global $wpdb;

        $values = array();
        foreach ( $product_ids as $product_id ) {
            $values[] = '(' . $map_id . ',' . $product_id . ')';
        }

        $values = implode( ',', $values );

        $result = $wpdb->query( "INSERT INTO `{$wpdb->prefix}dokan_product_map`
            ( map_id, product_id )
            VALUES $values"
        );

        if ( $result ) {
            return true;
        }

        return false;
    }

    /**
     * Get other reseller vendors
     *
     * @since 1.0.0
     *
     * @param integer $product_id
     *
     * @return void
     */
    public function get_other_reseller_vendors( $product_id ) {
        global $wpdb;

        if ( ! $product_id ) {
            return false;
        }

        $has_multivendor = get_post_meta( $product_id, '_has_multi_vendor', true );

        if ( empty( $has_multivendor ) ) {
            return false;
        }

        $sql     = "SELECT `product_id` FROM `{$wpdb->prefix}dokan_product_map` WHERE `map_id`= '$has_multivendor'";
        $results = $wpdb->get_results( $sql );

        if ( $results ) {
            return $results;
        }

        return false;
    }
}









