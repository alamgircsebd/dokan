<?php

namespace DokanPro\Modules\Elementor\Widgets;

use Elementor\Widget_Heading;

class StoreName extends Widget_Heading {

    /**
     * Widget name
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_name() {
        return 'dokan-store-name';
    }

    /**
     * Widget title
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_title() {
        return __( 'Store Name', 'dokan' );
    }

    /**
     * Widget icon class
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_icon() {
        return 'eicon-site-title';
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
        return [ 'dokan', 'store', 'vendor', 'name', 'heading' ];
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
            'title',
            [
                'dynamic' => [
                    'default' => dokan_elementor()->elementor()->dynamic_tags->tag_data_to_tag_text( null, 'dokan-store-name-tag' ),
                ],
            ],
            [
                'recursive' => true,
            ]
        );

        $this->update_control(
            'header_size',
            [
                'default' => 'h1',
            ]
        );

        $this->remove_control( 'link' );
    }

    /**
     * Frontend render method
     *
     * @since 1.0.0
     *
     * @return void
     */
    protected function render() {
        $this->add_render_attribute(
            '_wrapper',
            'class',
            [ 'store-name', 'elementor-page-title', 'elementor-widget-' . parent::get_name() ]
        );

        parent::render();
    }

    /**
     * Elementor builder content template
     *
     * @since 1.0.0
     *
     * @return void
     */
    protected function _content_template() {
        ?>
            <#
                settings.link = {url: ''};
                view.addRenderAttribute( '_wrapper', 'class', [ 'store-name' ] );
            #>
        <?php

        parent::_content_template();
    }

    /**
     * Render widget plain content
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function render_plain_content() {}
}
