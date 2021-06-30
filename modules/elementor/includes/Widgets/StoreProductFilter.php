<?php

namespace WeDevs\DokanPro\Modules\Elementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class StoreProductFilter extends Widget_Base {

    /**
     * Widget name
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return string
     */
    public function get_name() {
        return 'dokan-store-product-filter';
    }

    /**
     * Widget title
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return string
     */
    public function get_title() {
        return __( 'Dokan Store Product Filter', 'dokan' );
    }

    /**
     * Widget icon class
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return string
     */
    public function get_icon() {
        return 'eicon-filter';
    }

    /**
     * Widget categories
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return array
     */
    public function get_categories() {
        return [ 'dokan-store-elements-single' ];
    }

    /**
     * Widget keywords
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return array
     */
    public function get_keywords() {
        return [ 'dokan', 'product', 'vendor', 'store-product-filter' ];
    }

    /**
     * Register HTML widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since DOKAN_PRO_SINCE
     * @access protected
     */
    protected function _register_controls() {
        parent::_register_controls();

        $this->start_controls_section(
            'section_title',
            [
                'label' => __( 'Store Product Filter', 'dokan' ),
            ]
        );

        $this->add_control(
            'filter_product_name',
            [
                'label'        => __( 'Filter by Product', 'dokan' ),
                'type'         => 'switcher',
                'label_on'     => __( 'Show', 'dokan' ),
                'label_off'    => __( 'Hide', 'dokan' ),
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        $this->add_control(
            'filter_product_name_placeholder',
            [
                'label'       => __( 'Product Placeholder', 'dokan' ),
                'default'     => __( 'Enter product name', 'dokan' ),
                'placeholder' => __( 'Enter product name', 'dokan' ),
            ]
        );

        $this->add_control(
            'filter_orderby',
            [
                'label'        => __( 'Filter by Orderby', 'dokan' ),
                'type'         => 'switcher',
                'label_on'     => __( 'Show', 'dokan' ),
                'label_off'    => __( 'Hide', 'dokan' ),
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        $this->add_control(
            'text',
            [
                'label'       => __( 'Button Label', 'dokan' ),
                'default'     => __( 'Search', 'dokan' ),
                'placeholder' => __( 'Search', 'dokan' ),
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Frontend render method
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    protected function render() {
        if ( ! dokan_is_store_page() && ! dokan_elementor()->is_edit_or_preview_mode() ) {
            return;
        }

        $show_default_orderby    = 'menu_order' === apply_filters( 'dokan_default_store_products_orderby', get_option( 'woocommerce_default_catalog_orderby', 'menu_order' ) );
        $catalog_orderby_options = function_exists( 'dokan_store_product_catalog_orderby' ) ? dokan_store_product_catalog_orderby() : array();

        $default_orderby = wc_get_loop_prop( 'is_search' ) ? 'relevance' : apply_filters( 'dokan_default_store_products_orderby', get_option( 'woocommerce_default_catalog_orderby', '' ) );
        $orderby = isset( $_GET['product_orderby'] ) ? wc_clean( wp_unslash( $_GET['product_orderby'] ) ) : $default_orderby;

        if ( wc_get_loop_prop( 'is_search' ) ) {
            $catalog_orderby_options = array_merge( array( 'relevance' => __( 'Relevance', 'dokan' ) ), $catalog_orderby_options );

            unset( $catalog_orderby_options['menu_order'] );
        }

        if ( ! $show_default_orderby ) {
            unset( $catalog_orderby_options['menu_order'] );
        }

        if ( ! wc_review_ratings_enabled() ) {
            unset( $catalog_orderby_options['rating'] );
        }

        if ( ! array_key_exists( $orderby, $catalog_orderby_options ) ) {
            $orderby = current( array_keys( $catalog_orderby_options ) );
        }

        $button_label        = $this->get_settings( 'text' );
        $filter_product      = $this->get_settings( 'filter_product_name' );
        $product_placeholder = $this->get_settings( 'filter_product_name_placeholder' );
        $filter_orderby      = $this->get_settings( 'filter_orderby' );
        $store_user          = dokan()->vendor->get( get_query_var( 'author' ) );
        $store_id            = $store_user->get_id();
        ?>
        <div class="dokan-store-products-filter-area dokan-clearfix">
            <form class="dokan-store-products-ordeby" method="get">
                <?php if ( 'yes' === $filter_product ) : ?>
                    <input type="text" autocomplete="off" name="product_name" class="product-name-search dokan-store-products-filter-search" placeholder="<?php echo esc_attr( $product_placeholder ); ?>" data-store_id="<?php echo esc_attr( $store_id ); ?>">
                    <div id="dokan-store-products-search-result" class="dokan-ajax-store-products-search-result"></div>
                    <input type="submit" name="search_store_products" class="search-store-products dokan-btn-theme" value="<?php echo esc_attr( $button_label ); ?>">
                <?php endif; ?>
                <?php if ( 'yes' === $filter_orderby ) : ?>
                    <select name="product_orderby" class="orderby orderby-search" aria-label="<?php esc_attr_e( 'Shop order', 'dokan' ); ?>" onchange='if(this.value != 0) { this.form.submit(); }'>
                        <?php foreach ( $catalog_orderby_options as $id => $name ) : ?>
                            <option value="<?php echo esc_attr( $id ); ?>" <?php selected( $orderby, $id ); ?>><?php echo esc_html( $name ); ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
                <input type="hidden" name="paged" value="1" />
            </form>
        </div>
        <?php
    }
}
