<?php

namespace DokanPro\Modules\Elementor\Widgets;

use DokanPro\Modules\Elementor\Controls\SortableList;
use DokanPro\Modules\Elementor\Traits\PositionControls;
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Widget_Icon_List;

class StoreInfo extends Widget_Icon_List {

    // use PositionControls;

    /**
     * Widget name
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_name() {
        return 'dokan-store-info';
    }

    /**
     * Widget title
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_title() {
        return __( 'Store Info', 'dokan' );
    }

    /**
     * Widget icon class
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_icon() {
        return 'eicon-bullet-list';
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
        return [ 'dokan', 'store', 'vendor', 'info', 'summery', 'informations' ];
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
            'section_icon',
            [
                'label' => __( 'Store Info', 'dokan' ),
            ]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'title',
            [
                'label'       => __( 'Title', 'dokan' ),
                'type'        => Controls_Manager::HIDDEN,
                'default'     => __( 'Title', 'dokan' ),
            ]
        );

        $repeater->add_control(
            'text',
            [
                'type'    => Controls_Manager::HIDDEN
            ]
        );

        $repeater->add_control(
            'icon',
            [
                'label'       => __( 'Icon', 'dokan' ),
                'type'        => Controls_Manager::ICON,
                'label_block' => true,
                'default'     => 'fa fa-check',
            ]
        );

        $repeater->add_control(
            'show',
            [
                'type'    => Controls_Manager::HIDDEN,
                'default' => true,
            ]
        );

        $this->update_control(
            'icon_list',
            [
                'type'    => SortableList::CONTROL_TYPE,
                'fields'  => $repeater->get_controls(),
                'default' => dokan_elementor()->elementor()->dynamic_tags->get_tag_data_content( null, 'dokan-store-info' ),
            ]
        );
    }

    /**
     * Set wrapper classes
     *
     * @since 1.0.0
     *
     * @return void
     */
    protected function get_html_wrapper_class() {
        return parent::get_html_wrapper_class() . ' dokan-store-info elementor-page-title elementor-widget-' . parent::get_name();
    }

    /**
     * Render icon list widget output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since 1.0.0
     *
     * @return void
     */
    protected function render() {
        $settings = $this->get_settings_for_display();

        $this->add_render_attribute( 'icon_list', 'class', 'elementor-icon-list-items' );
        $this->add_render_attribute( 'list_item', 'class', 'elementor-icon-list-item' );

        if ( 'inline' === $settings['view'] ) {
            $this->add_render_attribute( 'icon_list', 'class', 'elementor-inline-items' );
            $this->add_render_attribute( 'list_item', 'class', 'elementor-inline-item' );
        }
        ?>
        <ul <?php echo $this->get_render_attribute_string( 'icon_list' ); ?>>
            <?php
            foreach ( $settings['icon_list'] as $index => $item ) :
                $repeater_setting_key = $this->get_repeater_setting_key( 'text', 'icon_list', $index );

                $this->add_render_attribute( $repeater_setting_key, 'class', 'elementor-icon-list-text' );

                $this->add_inline_editing_attributes( $repeater_setting_key );

                if ( $item['show'] ):
                ?>
                    <li class="elementor-icon-list-item" >
                        <?php
                        if ( ! empty( $item['icon'] ) ) :
                            ?>
                            <span class="elementor-icon-list-icon">
                                <i class="<?php echo esc_attr( $item['icon'] ); ?>" aria-hidden="true"></i>
                            </span>
                        <?php endif; ?>
                        <span <?php echo $this->get_render_attribute_string( $repeater_setting_key ); ?>><?php echo $item['text']; ?></span>
                    </li>
                <?php
                endif;
            endforeach;
            ?>
        </ul>
        <?php
    }

    /**
     * Render icon list widget output in the editor.
     *
     * Written as a Backbone JavaScript template and used to generate the live preview.
     *
     * @since 1.0.0
     *
     * @return void
     */
    protected function _content_template() {
        ?>
        <#
            view.addRenderAttribute( 'icon_list', 'class', 'elementor-icon-list-items' );
            view.addRenderAttribute( 'list_item', 'class', 'elementor-icon-list-item' );

            if ( 'inline' == settings.view ) {
                view.addRenderAttribute( 'icon_list', 'class', 'elementor-inline-items' );
                view.addRenderAttribute( 'list_item', 'class', 'elementor-inline-item' );
            }
        #>
        <# if ( settings.icon_list ) { #>
            <ul {{{ view.getRenderAttributeString( 'icon_list' ) }}}>
            <# _.each( settings.icon_list, function( item, index ) {
                    var iconTextKey = view.getRepeaterSettingKey( 'text', 'icon_list', index );

                    view.addRenderAttribute( iconTextKey, 'class', 'elementor-icon-list-text' );

                    view.addInlineEditingAttributes( iconTextKey ); #>

                    <# if ( item.show ) { #>
                        <li {{{ view.getRenderAttributeString( 'list_item' ) }}}>
                            <# if ( item.icon ) { #>
                            <span class="elementor-icon-list-icon">
                                <i class="{{ item.icon }}" aria-hidden="true"></i>
                            </span>
                            <# } #>
                            <span {{{ view.getRenderAttributeString( iconTextKey ) }}}>{{{ item.text }}}</span>
                        </li>
                    <# } #>
                <#
                } ); #>
            </ul>
        <#  } #>
        <?php
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
