<?php

namespace DokanPro\Modules\Elementor\Widgets;

use DokanPro\Modules\Elementor\Abstracts\DokanButton;
use Elementor\Controls_Manager;

class StoreSupportButton extends DokanButton {

    /**
     * Widget name
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return string
     */
    public function get_name() {
        return 'dokan-store-support-button';
    }

    /**
     * Widget title
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return string
     */
    public function get_title() {
        return __( 'Store Support Button', 'dokan' );
    }

    /**
     * Widget icon class
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return string
     */
    public function get_icon() {
        return 'eicon-person';
    }

    /**
     * Widget keywords
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return array
     */
    public function get_keywords() {
        return [ 'dokan', 'store', 'vendor', 'button', 'support' ];
    }

    /**
     * Register widget controls
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    protected function _register_controls() {
        parent::_register_controls();

        $this->update_control(
            'text',
            [
                'dynamic'   => [
                    'default' => dokan_elementor()->elementor()->dynamic_tags->tag_data_to_tag_text( null, 'dokan-store-support-button-tag' ),
                    'active'  => true,
                ],
                'selectors' => [
                    '{{WRAPPER}} > .elementor-widget-container > .elementor-button-wrapper > .dokan-store-support-btn' => 'width: auto; margin: 0;',
                ]
            ]
        );

        $this->update_control(
            'link',
            [
                'type' => Controls_Manager::HIDDEN,
            ]
        );
    }

    /**
     * Button wrapper class
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return string
     */
    protected function get_button_wrapper_class() {
        return 'dokan-store-support-btn-wrap';
    }
    /**
     * Button class
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return string
     */
    protected function get_button_class() {
        return 'dokan-store-support-btn';
    }

    protected function render() {
        if ( dokan_is_store_page() ) {
            if ( ! class_exists( 'Dokan_Store_Support' ) ) {
                return;
            }

            $id = dokan_elementor()->get_store_data( 'id' );

            if ( ! $id ) {
                return;
            }

            $store_support  = \Dokan_Store_Support::init();
            $support_button = $store_support->get_support_button( $id );

            if ( ! $support_button['show'] ) {
                return;
            }
        }

        parent::render();
    }
}
