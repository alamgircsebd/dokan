<?php

namespace DokanPro\Modules\Elementor\Documents;

use ElementorPro\Modules\ThemeBuilder\Documents\Single;

class Store extends Single {

    /**
     * Document properties
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return array
     */
    public static function get_properties() {
        $properties = parent::get_properties();

        $properties['location'] = 'single';
        $properties['condition_type'] = 'general';

        return $properties;
    }

    /**
     * Document name
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return string
     */
    public function get_name() {
        return 'store';
    }

    /**
     * Document title
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return string
     */
    public static function get_title() {
        return __( 'Single Store', 'dokan' );
    }

    /**
     * Elementor builder panel categories
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return array
     */
    protected static function get_editor_panel_categories() {
        $categories = [
            'dokan-store-elements-single' => [
                'title' => __( 'Store', 'dokan' ),
                'active' => true,
            ],
        ];

        $categories += parent::get_editor_panel_categories();

        return $categories;
    }

    /**
     * Document library type
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return string
     */
    public function get_remote_library_type() {
        return 'single store';
    }
}
