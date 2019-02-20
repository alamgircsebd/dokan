<?php

namespace DokanPro\Modules\Elementor\Documents;

use ElementorPro\Modules\ThemeBuilder\Documents\Single;

class Store extends Single {

    /**
     * Class constructor
     *
     * @since DOKAN_PRO_SINCE
     *
     * @param array $data
     *
     * @return void
     */
    public function __construct( $data = [] ) {
        parent::__construct( $data );

        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ], 11 );
    }

    /**
     * Enqueue document related scripts
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    public function enqueue_scripts() {
        if ( ! dokan_elementor()->is_edit_or_preview_mode() ) {
            wp_enqueue_style(
                'dokan-elementor-doc-store',
                DOKAN_ELEMENTOR_ASSETS . '/css/dokan-elementor-document-store.css',
                [],
                DOKAN_ELEMENTOR_VERSION
            );
        }
    }

    /**
     * Document properties
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return array
     */
    public static function get_properties() {
        $properties = parent::get_properties();

        $properties['location']       = 'single';
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
                'title'  => __( 'Store', 'dokan' ),
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
