<?php

/**
 * Dokan Pro Product Class
 *
 * @since 2.4
 *
 * @package dokan
 */
class Dokan_Pro_Products {

    /**
     * Load autometically when class initiate
     *
     * @since 2.4
     *
     * @uses actions
     * @uses filters
     */
    public function __construct() {
        add_action( 'dokan_product_edit_after_inventory_variants', array( $this, 'load_shipping_tax_content' ), 10, 2 );
        add_action( 'dokan_product_edit_after_inventory_variants', array( $this, 'load_linked_product_content' ), 15, 2 );
        add_action( 'dokan_product_edit_after_inventory_variants', array( $this, 'load_variations_content' ), 20, 2 );
        add_action( 'dokan_product_edit_after_inventory_variants', array( $this, 'load_lot_discount_content' ), 25, 2 );
        add_action( 'dokan_dashboard_wrap_after', array( $this, 'load_variations_js_template' ), 10, 2 );
        add_action( 'dokan_render_new_product_template', array( $this, 'render_new_product_template' ), 10 );
        add_action( 'dokan_render_product_edit_template', array( $this, 'load_product_edit_template' ), 11 );
        add_action( 'woocommerce_product_options_advanced', array($this,'add_per_product_commission_options' ),15 );
        add_action( 'woocommerce_process_product_meta_simple', array($this,'save_per_product_commission_options' ),15 );
        add_action( 'woocommerce_process_product_meta_variable', array($this,'save_per_product_commission_options' ),15 );
        add_action( 'dokan_product_updated', array( $this, 'set_product_type' ), 11 );
        add_action( 'dokan_product_updated', array( $this, 'save_pro_product_data' ), 12 );
        add_action( 'dokan_product_updated', array( $this, 'updated_product_email' ), 20 );
        add_action( 'dokan_product_listin_row_action', array( $this, 'product_row_action' ), 10 );
        add_action( 'template_redirect', array( $this, 'handle_duplicate_product' ), 10 );
        add_action( 'dokan_product_dashboard_errors', array( $this, 'display_duplicate_message' ), 10 );

        add_filter( 'dokan_get_product_edit_template', array( $this, 'render_product_edit_template' ), 10 );
        add_filter( 'dokan_update_product_post_data', array( $this, 'save_product_post_data' ), 10 );
    }

    /**
     * Inistantiate the Dokan_Pro_Products class
     *
     * @since 2.4
     *
     * @return object
     */
    public static function init() {
        static $instance = false;

        if ( !$instance ) {
            $instance = new Dokan_Pro_Products();
        }

        return $instance;
    }

    /**
     * Render New Product Template
     *
     * @since 2.4
     *
     * @param  array $query_vars
     *
     * @return void
     */
    public function render_new_product_template( $query_vars ) {
        if ( isset( $query_vars['new-product'] ) ) {
            dokan_get_template_part( 'products/new-product' );
        }
    }

    /**
     * Load Product Edit Template
     *
     * @since 2.4
     *
     * @return void
     */
    public function load_product_edit_template() {
        if ( dokan_get_option( 'product_style', 'dokan_selling', 'old' ) == 'old' ) {
            dokan_get_template_part( 'products/product-edit', '', array( 'pro' => true ) );
        } else {
            dokan_get_template_part( 'products/new-product-single' );
        }
    }

    /**
     * Load Variation Content
     *
     * @since 2.4
     *
     * @param  object $post
     * @param  integer $post_id
     *
     * @return void
     */
    public function load_variations_content( $post, $post_id ) {
        $_has_attribute       = get_post_meta( $post_id, '_has_attribute', true );
        $_create_variations   = get_post_meta( $post_id, '_create_variation', true );
        $product_attributes   = get_post_meta( $post_id, '_product_attributes', true );
        $attribute_taxonomies = wc_get_attribute_taxonomies();

        dokan_get_template_part( 'products/product-variation', '', array(
            'pro'                  => true,
            'post_id'              => $post_id,
            '_has_attribute'       => $_has_attribute,
            '_create_variations'   => $_create_variations,
            'product_attributes'   => $product_attributes,
            'attribute_taxonomies' => $attribute_taxonomies,
        ) );
    }

    /**
    * Render product lot dicount options
    *
    * @since 2.6
    *
    * @return void
    **/
    public function load_lot_discount_content( $post, $post_id ) {
        $_is_lot_discount       = get_post_meta( $post_id, '_is_lot_discount', true );
        $_lot_discount_quantity = get_post_meta( $post_id, '_lot_discount_quantity', true );
        $_lot_discount_amount   = get_post_meta( $post_id, '_lot_discount_amount', true );
        $is_enable_op_discount  = dokan_get_option( 'discount_edit', 'dokan_selling' );
        $is_enable_op_discount  = $is_enable_op_discount ? $is_enable_op_discount : array();

        dokan_get_template_part( 'products/product-lot-discount', '', array(
            'pro'                    => true,
            'post_id'                => $post_id,
            '_is_lot_discount'       => $_is_lot_discount,
            '_lot_discount_quantity' => $_lot_discount_quantity,
            '_lot_discount_amount'   => $_lot_discount_amount,
            'is_enable_op_discount'  => $is_enable_op_discount,
        ) );

    }


    /**
     * Load Variation popup content when edit product
     *
     * @since 2.4
     *
     * @param  object $post
     * @param  integer $post_id
     *
     * @return void
     */
    public function load_variations_js_template( $post, $post_id ) {
        dokan_get_template_part( 'products/edit/tmpl-add-attribute', '', array( 'pro' => true, 'post_id' => $post_id ) );
    }

    /**
     * Load Shipping and tax content
     *
     * @since 2.4
     *
     * @param  object $post
     * @param  integer $post_id
     *
     * @return void
     */
    public function load_shipping_tax_content( $post, $post_id ) {

        $user_id                 = get_current_user_id();
        $processing_time         = dokan_get_shipping_processing_times();
        $_required_tax           = get_post_meta( $post_id, '_required_tax', true );
        $_disable_shipping       = ( get_post_meta( $post_id, '_disable_shipping', true ) ) ? get_post_meta( $post_id, '_disable_shipping', true ) : 'no';
        $_additional_price       = get_post_meta( $post_id, '_additional_price', true );
        $_additional_qty         = get_post_meta( $post_id, '_additional_qty', true );
        $_processing_time        = get_post_meta( $post_id, '_dps_processing_time', true );
        $dps_shipping_type_price = get_user_meta( $user_id, '_dps_shipping_type_price', true );
        $dps_additional_qty      = get_user_meta( $user_id, '_dps_additional_qty', true );
        $dps_pt                  = get_user_meta( $user_id, '_dps_pt', true );
        $classes_options         = $this->get_tax_class_option();
        $porduct_shipping_pt     = ( $_processing_time ) ? $_processing_time : $dps_pt;

        dokan_get_template_part( 'products/product-shipping-content', '', array(
            'pro'                     => true,
            'post'                    => $post,
            'post_id'                 => $post_id,
            'user_id'                 => $user_id,
            'processing_time'         => $processing_time,
            '_required_tax'           => $_required_tax,
            '_disable_shipping'       => $_disable_shipping,
            '_additional_price'       => $_additional_price,
            '_additional_qty'         => $_additional_qty,
            '_processing_time'        => $_processing_time,
            'dps_shipping_type_price' => $dps_shipping_type_price,
            'dps_additional_qty'      => $dps_additional_qty,
            'dps_pt'                  => $dps_pt,
            'classes_options'         => $classes_options,
            'porduct_shipping_pt'     => $porduct_shipping_pt,
        ) );
    }

    /**
    * Render linked product content
    *
    * @since 2.6.6
    *
    * @return void
    **/
    public function load_linked_product_content( $post, $post_id ) {
        $upsells_ids = get_post_meta( $post_id, '_upsell_ids', true );
        $crosssells_ids = get_post_meta( $post_id, '_crosssell_ids', true );

        dokan_get_template_part( 'products/linked-product-content', '', array(
            'pro'            => true,
            'post'           => $post,
            'post_id'        => $post_id,
            'upsells_ids'    => $upsells_ids,
            'crosssells_ids' => $crosssells_ids
        ) );
    }

    /**
     * Get taxes options value
     *
     * @since 2.4
     *
     * @return array
     */
    function get_tax_class_option() {
        $tax_classes = array_filter( array_map( 'trim', explode( "\n", get_option( 'woocommerce_tax_classes' ) ) ) );
        $classes_options = array();
        $classes_options[''] = __( 'Standard', 'dokan' );

        if ( $tax_classes ) {

            foreach ( $tax_classes as $class ) {
                $classes_options[ sanitize_title( $class ) ] = esc_html( $class );
            }
        }

        return $classes_options;
    }

    /**
     * add per product commission options
     *
     * @since 2.4.12
     *
     * @return void
     */
    function add_per_product_commission_options() {
        woocommerce_wp_text_input(
            array(
                'id'            => '_per_product_commission',
                'label'         => __( 'Vendor Commission (%)', 'dokan' ),
                'wrapper_class' => 'per-product-commission show_if_simple show_if_variable',
                'description'   => __( 'Override the default commission (%) vendor will get from this product', 'dokan' ),
                'data_type'     => 'price'
            )
        );
    }

    /**
     * save per product commission options
     *
     * @since 2.4.12
     *
     * @param  integer $post_id
     *
     * @return void
     */
    function save_per_product_commission_options( $post_id ) {
        if ( isset( $_POST['_per_product_commission'] ) ) {
            $value = empty( $_POST['_per_product_commission'] ) ? '' : (float) $_POST['_per_product_commission'];
            update_post_meta( $post_id, '_per_product_commission', $value );
        }
    }

    /**
    * Rewrite product edit template
    *
    * @since 2.6
    *
    * @return void
    **/
    public function render_product_edit_template( $template ) {
        if ( dokan_get_option( 'product_style', 'dokan_selling', 'old' ) === 'old' ) {
            return dokan_locate_template( 'products/product-edit.php', '', DOKAN_PRO_DIR. '/templates/', true );
        }
        return $template;
    }

    /**
     * Save extra product data
     *
     * @since  2.5.3
     *
     * @param  integer $post_id
     *
     * @return void
     */
    public function save_pro_product_data( $post_id ) {
        if ( ! $post_id ) {
            return;
        }

        $is_virtual   = isset( $_POST['_virtual'] ) ? 'yes' : 'no';
        $product_type = empty( $_POST['product_type'] ) ? 'simple' : stripslashes( $_POST['product_type'] );

        // Save lot discount options
        $is_lot_discount = isset( $_POST['_is_lot_discount'] ) ? $_POST['_is_lot_discount'] : 'no';
        if ( $is_lot_discount == 'yes' ) {
            $lot_discount_quantity = isset( $_POST['_lot_discount_quantity'] ) ? $_POST['_lot_discount_quantity'] : 0;
            $lot_discount_amount   = isset( $_POST['_lot_discount_amount'] ) ? $_POST['_lot_discount_amount'] : 0;
            if ( $lot_discount_quantity == '0' || $lot_discount_amount == '0' ) {
                update_post_meta( $post_id, '_lot_discount_quantity', $lot_discount_quantity );
                update_post_meta( $post_id, '_lot_discount_amount', $lot_discount_amount );
                update_post_meta( $post_id, '_is_lot_discount', 'no' );
            } else {
                update_post_meta( $post_id, '_lot_discount_quantity', $lot_discount_quantity );
                update_post_meta( $post_id, '_lot_discount_amount', $lot_discount_amount );
                update_post_meta( $post_id, '_is_lot_discount', $is_lot_discount );
            }
        } else if ( $is_lot_discount == 'no' ) {
            update_post_meta( $post_id, '_lot_discount_quantity', 0 );
            update_post_meta( $post_id, '_lot_discount_amount', 0 );
            update_post_meta( $post_id, '_is_lot_discount', 'no' );
        }

        // Dimensions
        if ( 'no' == $is_virtual ) {

            if ( isset( $_POST['_weight'] ) ) {
                update_post_meta( $post_id, '_weight', ( '' === $_POST['_weight'] ) ? '' : wc_format_decimal( $_POST['_weight'] )  );
            }

            if ( isset( $_POST['_length'] ) ) {
                update_post_meta( $post_id, '_length', ( '' === $_POST['_length'] ) ? '' : wc_format_decimal( $_POST['_length'] )  );
            }

            if ( isset( $_POST['_width'] ) ) {
                update_post_meta( $post_id, '_width', ( '' === $_POST['_width'] ) ? '' : wc_format_decimal( $_POST['_width'] )  );
            }

            if ( isset( $_POST['_height'] ) ) {
                update_post_meta( $post_id, '_height', ( '' === $_POST['_height'] ) ? '' : wc_format_decimal( $_POST['_height'] )  );
            }
        } else {
            update_post_meta( $post_id, '_weight', '' );
            update_post_meta( $post_id, '_length', '' );
            update_post_meta( $post_id, '_width', '' );
            update_post_meta( $post_id, '_height', '' );
        }

        //Save shipping meta data
        update_post_meta( $post_id, '_disable_shipping', stripslashes( isset( $_POST['_disable_shipping'] ) ? $_POST['_disable_shipping'] : 'no' ) );

        if ( isset( $_POST['_overwrite_shipping'] ) && $_POST['_overwrite_shipping'] == 'yes' ) {
            update_post_meta( $post_id, '_overwrite_shipping', stripslashes( $_POST['_overwrite_shipping'] ) );
        } else {
            update_post_meta( $post_id, '_overwrite_shipping', 'no' );
        }

        update_post_meta( $post_id, '_additional_price', stripslashes( isset( $_POST['_additional_price'] ) ? $_POST['_additional_price'] : ''  ) );
        update_post_meta( $post_id, '_additional_qty', stripslashes( isset( $_POST['_additional_qty'] ) ? $_POST['_additional_qty'] : ''  ) );
        update_post_meta( $post_id, '_dps_processing_time', stripslashes( isset( $_POST['_dps_processing_time'] ) ? $_POST['_dps_processing_time'] : ''  ) );

        // Save shipping class
        $product_shipping_class = ( isset( $_POST['product_shipping_class'] ) && $_POST['product_shipping_class'] > 0 && 'external' !== $product_type ) ? absint( $_POST['product_shipping_class'] ) : '';
        wp_set_object_terms( $post_id, $product_shipping_class, 'product_shipping_class' );

        // Cross sells and upsells
        $upsells    = isset( $_POST['upsell_ids'] ) ? array_map( 'intval', $_POST['upsell_ids'] ) : array();
        $crosssells = isset( $_POST['crosssell_ids'] ) ? array_map( 'intval', $_POST['crosssell_ids'] ) : array();

        update_post_meta( $post_id, '_upsell_ids', $upsells );
        update_post_meta( $post_id, '_crosssell_ids', $crosssells );

        // Save variations
        if ( $product_type == 'variable' ) {
            dokan_save_variations( $post_id );
        }

    }

    /**
    * Added duplicate row action
    *
    * @since 2.6.3
    *
    * @return void
    **/
    public function product_row_action( $product ) {
        if ( dokan_get_option( 'vendor_duplicate_product', 'dokan_selling', 'on' ) == 'off' ) {
            return;
        }

        $html = '| <span class="duplicate"><a href="' . wp_nonce_url( add_query_arg( array( 'action' => 'dokan-duplicate-product', 'product_id' => $product->get_id() ), dokan_get_navigation_url('products') ), 'dokan-duplicate-product' ) . '">' . __( 'Duplicate', 'dokan' ) . '</a></span>';
        echo apply_filters( 'dokan_product_listing_duplicate_row_action', $html, $product );
    }

    /**
    * Handle duplicate product action
    *
    * @since 2.6.3
    *
    * @return void
    **/
    public function handle_duplicate_product() {

        if ( ! is_user_logged_in() ) {
            return;
        }

        if ( dokan_get_option( 'vendor_duplicate_product', 'dokan_selling', 'on' ) == 'off' ) {
            return;
        }

        if ( ! dokan_is_user_seller( get_current_user_id() ) ) {
            return;
        }

        if ( class_exists( 'Dokan_Product_Subscription' ) ) {
            if ( ! Dokan_Product_Subscription::can_post_product() ) {
                return;
            }
        }

        if ( isset( $_GET['action'] ) && $_GET['action'] == 'dokan-duplicate-product' ) {
            $product_id = isset( $_GET['product_id'] ) ? (int) $_GET['product_id'] : 0;

            if ( !$product_id ) {
                wp_redirect( add_query_arg( array( 'message' => 'error' ), dokan_get_navigation_url( 'products' ) ) );
                return;
            }

            if ( !wp_verify_nonce( $_GET['_wpnonce'], 'dokan-duplicate-product' ) ) {
                wp_redirect( add_query_arg( array( 'message' => 'error' ), dokan_get_navigation_url( 'products' ) ) );
                return;
            }

            if ( !dokan_is_product_author( $product_id ) ) {
                wp_redirect( add_query_arg( array( 'message' => 'error' ), dokan_get_navigation_url( 'products' ) ) );
                return;
            }

            $wo_dup = new WC_Admin_Duplicate_Product();

            // Compatibility for WC 3.0+
            if ( version_compare( WC_VERSION, '2.7', '>' ) ) {
                $product = wc_get_product( $product_id );
                $clone_product =  $wo_dup->product_duplicate( $product );
                $clone_product_id =  $clone_product->get_id();
            } else {
                $post = get_post( $product_id );
                $clone_product_id =  $wo_dup->duplicate_product( $post );
            }

            $product_status = dokan_get_new_post_status();
            wp_update_post( array( 'ID' => intval( $clone_product_id ), 'post_status' => $product_status ) );

            $redirect = apply_filters( 'dokan_redirect_after_product_duplicating', dokan_get_navigation_url( 'products' ), $product_id, $clone_product_id );
            wp_redirect( add_query_arg( array( 'message' => 'product_duplicated' ),  $redirect ) );
            exit;
        }
    }

    /**
    * Show duplicate success message
    *
    * @since 2.6.3
    *
    * @return void
    **/
    public function display_duplicate_message( $type ) {
        if ( 'product_duplicated' == $type ) {
            dokan_get_template_part( 'global/dokan-success', '', array( 'deleted' => true, 'message' => __( 'Product succesfully duplicated', 'dokan' ) ) );
        }
    }

    /**
     * Set product type
     *
     * @since 2.5.3
     *
     * @param integer $post_id
     */
    public function set_product_type( $post_id ) {
        if ( isset( $_POST['product_type'] ) ) {
            wp_set_object_terms( $post_id, $_POST['product_type'], 'product_type' );
        }
    }

    /**
     * Set Additional product Post Data
     *
     * @since 2.6.3
     *
     * @param Object $product
     *
     * @return $product
     */
    public function save_product_post_data( $product ) {
        //update product status to pending-review if set by admin
        if ( $product['post_status'] == 'publish' && dokan_get_option( 'edited_product_status', 'dokan_selling' ) == 'on' ) {
            $product['post_status'] = 'pending';
        }

        return $product;
    }

    /**
     * Send email to admin once a product is updated
     *
     * @since 2.6.5
     *
     * @param int $product_id
     *
     * @param string $status
     */
    function updated_product_email( $product_id ) {

        if ( dokan_get_option( 'edited_product_status', 'dokan_selling', 'off' ) != 'on' ) {
            return;
        }
        $template = 'emails/product-updated-pending';
        $email = Dokan_Email::init();
        ob_start();
        dokan_get_template_part( $template , '', array( 'pro' => true ) );
        $body = ob_get_clean();

        $product       = wc_get_product( $product_id );
        $seller_id     = get_post_field( 'post_author', $product_id );
        $seller        = get_user_by( 'id', $seller_id );
        $category      = wp_get_post_terms( dokan_get_prop( $product, 'id' ), 'product_cat', array( 'fields' => 'names' ) );
        $category_name = $category ? reset( $category ) : 'N/A';

        $find = array(
            '%title%',
            '%price%',
            '%seller_name%',
            '%seller_url%',
            '%category%',
            '%product_link%',
            '%site_name%',
            '%site_url%'
        );

        $replace = array(
            $product->get_title(),
            $email->currency_symbol( $product->get_price() ),
            $seller->display_name,
            dokan_get_store_url( $seller->ID ),
            $category_name,
            admin_url( 'post.php?action=edit&post=' . $product_id ),
            $email->get_from_name(),
            home_url(),
        );

        $body    = str_replace( $find, $replace, $body );
        $subject = sprintf( __( '[%s]A Product is updated and pending review', 'dokan-lite' ), $email->get_from_name() );

        $email->send( $email->admin_email(), $subject, $body );
        do_action( 'dokan_after_updated_product_email', $email->admin_email(), $subject, $body );
    }

}