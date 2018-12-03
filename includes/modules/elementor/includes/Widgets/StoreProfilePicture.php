<?php

namespace DokanPro\Modules\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Widget_Image;

class StoreProfilePicture extends Widget_Image {

    /**
     * Widget name
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_name() {
        return 'dokan-store-profile-picture';
    }

    /**
     * Widget title
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_title() {
        return __( 'Profile Picture', 'dokan' );
    }

    /**
     * Widget icon class
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_icon() {
        return 'eicon-image';
    }

    /**
     * Widget categories
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function get_categories() {
        return [ 'dokan-store-elements-single' ];
    }

    /**
     * Widget keywords
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function get_keywords() {
        return [ 'dokan', 'store', 'vendor', 'profile', 'picture', 'image', 'avatar' ];
    }

    /**
     * Register widget controls
     *
     * @since 1.0.0
     *
     * @return void
     */
    protected function _register_controls() {
        parent::_register_controls();

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
    }

    protected function get_html_wrapper_class() {
        return parent::get_html_wrapper_class() . ' elementor-widget-' . parent::get_name();
    }
}
