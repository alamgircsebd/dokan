<?php

namespace DokanPro\Modules\Elementor\Widgets;

use DokanPro\Modules\Elementor\Abstracts\DokanButton;
use Elementor\Controls_Manager;

class StoreLiveChatButton extends DokanButton {

    /**
     * Widget name
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return string
     */
    public function get_name() {
        return 'dokan-store-live-chat-button';
    }

    /**
     * Widget title
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return string
     */
    public function get_title() {
        return __( 'Store Live Chat Button', 'dokan' );
    }

    /**
     * Widget icon class
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return string
     */
    public function get_icon() {
        return 'eicon-comments';
    }

    /**
     * Widget keywords
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return array
     */
    public function get_keywords() {
        return [ 'dokan', 'store', 'vendor', 'button', 'support', 'live', 'chat', 'message' ];
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
                    'default' => dokan_elementor()->elementor()->dynamic_tags->tag_data_to_tag_text( null, 'dokan-store-live-chat-button-tag' ),
                    'active'  => true,
                ],
                'selectors' => [
                    '{{WRAPPER}} > .elementor-widget-container > .elementor-button-wrapper > .dokan-store-live-chat-btn' => 'width: auto; margin: 0;',
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
        return parent::get_button_wrapper_class() . ' dokan-store-live-chat-btn-wrap';
    }
    /**
     * Button class
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return string
     */
    protected function get_button_class() {
        $classes = 'dokan-store-live-chat-btn ';

        $classes .= is_user_logged_in() ? 'dokan-live-chat' : 'dokan-live-chat-login';

        return $classes;
    }

    /**
     * Render button
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    protected function render() {
        if ( ! dokan_is_store_page() ) {
            parent::render();
        }

        if ( ! class_exists( 'Dokan_Live_Chat_Start' ) ) {
            return;
        }

        $id = dokan_elementor()->get_store_data( 'id' );

        if ( ! $id ) {
            return;
        }

        $live_chat = \Dokan_Live_Chat_Start::init();

        $store = dokan()->vendor->get( $id )->get_shop_info();

        if ( ! isset( $store['live_chat'] ) || $store['live_chat'] !== 'yes' ) {
            return;
        }

        if ( dokan_get_option( 'chat_button_seller_page', 'dokan_live_chat' ) !== 'on' ) {
            return;
        }

        if ( ! is_user_logged_in() ) {
            parent::render();
            return $live_chat->login_to_chat();
        }

        parent::render();
        echo do_shortcode( '[dokan-live-chat]' );
    }
}
