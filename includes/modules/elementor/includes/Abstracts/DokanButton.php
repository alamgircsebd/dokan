<?php

namespace DokanPro\Modules\Elementor\Abstracts;

use DokanPro\Modules\Elementor\Traits\PositionControls;
use Elementor\Controls_Manager;
use Elementor\Widget_Button;

abstract class DokanButton extends Widget_Button {

    use PositionControls;

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
     * Register widget controls
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    protected function _register_controls() {
        parent::_register_controls();

        $this->update_control(
            'icon_align',
            [
                'default' => 'right',
            ]
        );

        $this->update_control(
            'button_text_color',
            [
                'default' => dokan_get_option( 'btn_text', 'dokan_colors', '#ffffff' ),
            ]
        );

        $this->update_control(
            'background_color',
            [
                'default' => dokan_get_option( 'btn_primary', 'dokan_colors', '#f05025' ),
            ]
        );

        $this->update_control(
            'border_color',
            [
                'default' => dokan_get_option( 'btn_primary_border', 'dokan_colors', '#f05025' ),
            ]
        );

        $this->add_control(
            'float',
            [
                'label'     => __( 'Float', 'dokan' ),
                'type'      => Controls_Manager::SELECT,
                'default'   => 'none',
                'options'   => [
                    'none'  => __( 'None', 'dokan' ),
                    'left'  => __( 'Left', 'dokan' ),
                    'right' => __( 'Right', 'dokan' ),
                ],
                'selectors' => [
                    '{{WRAPPER}}' => 'float: {{VALUE}}',
                ]
            ],
            [
                'position' => [ 'of' => 'align' ]
            ]
        );

        $this->add_control(
            'icon_font_size',
            [
                'label'      => __( 'Icon Font Size', 'dokan' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem' ],
                'range'      => [
                    'px' => [
                        'min' => 1,
                        'max' => 200,
                    ],
                ],
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .elementor-button .elementor-align-icon-right' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .elementor-button .elementor-align-icon-left'  => 'font-size: {{SIZE}}{{UNIT}};',],
            ],
            [
                'position' => [ 'of' => 'typography_font_size' ]
            ]
        );

        $this->add_control(
            'icon_line_height',
            [
                'label'           => __( 'Icon Line Height', 'dokan' ),
                'type'            => Controls_Manager::SLIDER,
                'desktop_default' => [
                    'unit' => 'em',
                ],
                'tablet_default'  => [
                    'unit' => 'em',
                ],
                'mobile_default'  => [
                    'unit' => 'em',
                ],
                'range'           => [
                    'px' => [
                        'min' => 1,
                    ],
                ],
                'responsive'      => true,
                'size_units'      => [ 'px', 'em' ],
                'selectors'       => [
                    '{{WRAPPER}} .elementor-button .elementor-align-icon-right' => 'line-height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .elementor-button .elementor-align-icon-left'  => 'line-height: {{SIZE}}{{UNIT}};',
                ],
            ],
            [
                'position' => [ 'of' => 'typography_line_height' ]
            ]
        );

        $this->add_position_controls();
    }

    /**
     * Button wrapper class
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return string
     */
    protected function get_button_wrapper_class() {
        return 'dokan-btn-wrap';
    }

    /**
     * Set wrapper classes
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    protected function get_html_wrapper_class() {
        return parent::get_html_wrapper_class() . ' ' . $this->get_button_wrapper_class() . ' elementor-widget-' . parent::get_name();
    }

    /**
     * Button class
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return string
     */
    protected function get_button_class() {
        return 'dokan-btn';
    }

    /**
     * Frontend render method
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    protected function render() {
        $this->add_render_attribute(
            'button',
            'class',
            [ $this->get_button_class() ]
        );

        parent::render();
    }
}
