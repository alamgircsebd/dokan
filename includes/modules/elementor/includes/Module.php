<?php

namespace DokanPro\Modules\Elementor;

use DokanPro\Modules\Elementor\Abstracts\ModuleBase;
use DokanPro\Modules\Elementor\Documents\Store;

class Module extends ModuleBase {

    /**
     * Widget group
     *
     * @since 1.0.0
     */
    const DOKAN_GROUP = 'dokan';

    /**
     * Run after first instance
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function boot() {
        parent::boot();

        add_action( 'elementor/documents/register', [ $this, 'register_documents' ] );
        add_action( 'elementor/dynamic_tags/register_tags', [ $this, 'register_tags' ] );
        add_action( 'elementor/controls/controls_registered', [ $this, 'register_controls' ] );
        add_action( 'elementor/editor/footer', [ $this, 'add_editor_templates' ], 9 );
    }

    /**
     * Name of the elementor module
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_name() {
        return 'dokan';
    }

    /**
     * Module widgets
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function get_widgets() {
        return [
            'StoreName',
            'StoreProfilePicture',
            'StoreInfo',
        ];
    }

    /**
     * Register module documents
     *
     * @since 1.0.0
     *
     * @param Elementor\Core\Documents_Manager $documents_manager
     *
     * @return void
     */
    public function register_documents( $documents_manager ) {
        $this->docs_types = [
            'store' => Store::get_class_full_name(),
        ];

        foreach ( $this->docs_types as $type => $class_name ) {
            $documents_manager->register_document_type( $type, $class_name );
        }
    }

    /**
     * Register module tags
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function register_tags() {
        $tags = [
            'StoreName',
            'StoreProfilePicture',
            'StoreInfo',
        ];

        $module = dokan_elementor()->elementor()->dynamic_tags;

        $module->register_group( self::DOKAN_GROUP, [
            'title' => __( 'Dokan', 'dokan' ),
        ] );

        foreach ( $tags as $tag ) {
            $module->register_tag( "\\DokanPro\\Modules\\Elementor\\Tags\\{$tag}" );
        }
    }

    /**
     * Register controls
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function register_controls() {
        $controls = [
            'SortableList',
        ];

        $controls_manager = dokan_elementor()->elementor()->controls_manager;

        foreach ( $controls as $control ) {
            $control_class = "\\DokanPro\\Modules\\Elementor\\Controls\\{$control}";
            $controls_manager->register_control( $control_class::CONTROL_TYPE, new $control_class() );
        }
    }

    /**
     * Add editor templates
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function add_editor_templates() {
        $template_names = [
            'sortable-list-row',
        ];

        foreach ( $template_names as $template_name ) {
            dokan_elementor()->elementor()->common->add_template( DOKAN_ELEMENTOR_VIEWS . "/editor-templates/$template_name.php" );
        }
    }
}
