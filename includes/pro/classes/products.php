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
        add_action( 'dokan_single_product_edit_after_sidebar', array( $this, 'load_variations_content' ), 10, 2);
        add_action( 'dokan_dashboard_wrap_after', array( $this, 'load_variations_js_template' ), 10, 2);
        add_action( 'dokan_product_edit_after_inventory_variants', array( $this, 'load_shipping_tax_content' ), 10, 2);
        add_action( 'dokan_render_new_product_template', array( $this, 'render_new_product_template' ), 10 );
        add_action( 'dokan_render_product_edit_template', array( $this, 'load_product_edit_template' ), 11 );
        // Add per product commission option in backend for addmin
        add_action( 'woocommerce_product_options_pricing', array($this,'add_per_product_commission_options' ),15 );
        add_action( 'woocommerce_process_product_meta_simple', array($this,'save_per_product_commission_options' ),15 );
        add_action( 'woocommerce_process_product_meta_variable', array($this,'save_per_product_commission_options' ),15 );
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
            if ( dokan_get_option( 'product_style', 'dokan_selling', 'old' ) == 'old' ) {
                dokan_get_template_part( 'products/new-product', '', array( 'pro' => true ) );
            } else {
                dokan_get_template_part( 'products/new-product-single' );
            }
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
        dokan_get_template_part( 'products/product-edit', '', array( 'pro' => true ) );
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

        $_has_attribute     = get_post_meta( $post_id, '_has_attribute', true );
        $_create_variations = get_post_meta( $post_id, '_create_variation', true );
        $product_attributes = get_post_meta( $post_id, '_product_attributes', true );
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
        if ( $post_id ) {
            echo '<div class="variation-single-content">';
            dokan_get_template_part( 'products/edit/variation-popup', '', array( 'pro' => true, 'post_id' => $post_id ) );
            echo '</div>';
        }

        dokan_get_template_part( 'products/edit/variation-table', '', array( 'pro' => true, 'post_id' => $post_id ) );
        dokan_get_template_part( 'products/edit/variation-attribute-popup', '', array( 'pro' => true, 'post_id' => $post_id ) );
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
                'id'           => '_per_product_commission',
                'label'        => __( 'Commission (%)', 'dokan' ),
                'wrapper_class'=> 'per-product-commission',
                'description'  => __( 'Enter commission (%) seller will get from this product', 'dokan' ),
                'data_type' => 'price'
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

        if ( isset( $_POST['_per_product_commission'] ) && ! empty( $_POST['_per_product_commission'] ) ) {
            update_post_meta( $post_id, '_per_product_commission', (float) $_POST['_per_product_commission'] );
        }
    }
}