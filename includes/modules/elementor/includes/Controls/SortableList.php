<?php

namespace DokanPro\Modules\Elementor\Controls;

use Elementor\Control_Repeater;

class SortableList extends Control_Repeater {

    /**
     * Control type
     *
     * @since DOKAN_PRO_SINCE
     *
     * @var string
     */
    const CONTROL_TYPE = 'sortable_list';

    /**
     * Get repeater control type.
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return string
     */
    public function get_type() {
        return self::CONTROL_TYPE;
    }

    /**
     * Get repeater control default settings.
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return array
     */
    protected function get_default_settings() {
        return [
            'fields'        => [],
            'title_field'   => '',
            'prevent_empty' => true,
            'is_repeater'   => true,
            'item_actions'  => [
                'sort' => true,
            ],
        ];
    }

    /**
     * Render repeater control output in the editor.
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    public function content_template() {
        ?>
        <label>
            <span class="elementor-control-title">{{{ data.label }}}</span>
        </label>
        <div class="elementor-repeater-fields-wrapper"></div>
        <?php
    }

    /**
     * Enqueue control scripts
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    public function enqueue() {
        wp_enqueue_style(
            'dokan-elementor-control-sortable-list',
            DOKAN_ELEMENTOR_ASSETS . '/css/dokan-elementor-control-sortable-list.css',
            [],
            DOKAN_ELEMENTOR_VERSION
        );

        wp_enqueue_script(
            'dokan-elementor-control-sortable-list',
            DOKAN_ELEMENTOR_ASSETS . '/js/dokan-elementor-control-sortable-list.js',
            [ 'elementor-editor' ],
            DOKAN_ELEMENTOR_VERSION,
            true
        );
    }
}
