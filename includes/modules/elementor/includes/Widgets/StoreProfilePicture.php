<?php

namespace DokanPro\Modules\Elementor\Widgets;

use DokanPro\Modules\Elementor\Traits\PositionControls;
use Elementor\Controls_Manager;
use Elementor\Widget_Image;

class StoreProfilePicture extends Widget_Image {

    use PositionControls;

    /**
     * Widget name
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return string
     */
    public function get_name() {
        return 'dokan-store-profile-picture';
    }

    /**
     * Widget title
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return string
     */
    public function get_title() {
        return __( 'Profile Picture', 'dokan' );
    }

    /**
     * Widget icon class
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return string
     */
    public function get_icon() {
        return 'eicon-image';
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
        return [ 'dokan', 'store', 'vendor', 'profile', 'picture', 'image', 'avatar' ];
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
            'section_image',
            [
                'label' => __( 'Profile Picture', 'dokan' ),
            ]
        );

        $this->update_control(
            'image',
            [
                'dynamic' => [
                    'default' => dokan_elementor()->elementor()->dynamic_tags->tag_data_to_tag_text( null, 'dokan-store-profile-picture' ),
                ],
            ],
            [
                'recursive' => true,
            ]
        );

        $this->update_control(
            'caption_source',
            [
                'type' => Controls_Manager::HIDDEN,
            ]
        );

        $this->update_control(
            'caption',
            [
                'type' => Controls_Manager::HIDDEN,
            ]
        );

        $this->update_control(
            'link_to',
            [
                'type' => Controls_Manager::HIDDEN,
            ]
        );

        $this->add_position_controls();
    }

    protected function get_html_wrapper_class() {
        return parent::get_html_wrapper_class() . ' elementor-widget-' . parent::get_name();
    }
}
