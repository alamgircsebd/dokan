<?php

namespace DokanPro\Modules\Elementor\Documents;

use Elementor\Modules\Library\Documents\Library_Document;

class Store extends Library_Document {

    /**
     * Document name
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_name() {
        return 'store';
    }

    /**
     * Document title
     *
     * @since 1.0.0
     *
     * @return string
     */
    public static function get_title() {
        return __( 'Single Store', 'dokan' );
    }

    /**
     * Elementor builder panel categories
     *
     * @since 1.0.0
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
     * @since 1.0.0
     *
     * @return string
     */
    public function get_remote_library_type() {
        return 'single store';
    }

    /**
     * Set edit url
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_edit_url() {
        $url = parent::get_edit_url();

        if ( isset( $_GET['action'] ) && 'elementor_new_post' === $_GET['action'] ) {
            $url .= '#library';
        }

        return $url;
    }
}
