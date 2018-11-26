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
            // 'StoreAddress',
            // 'StorePhone',
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
        ];

        $module = dokan_elementor()->elementor()->dynamic_tags;

        $module->register_group( self::DOKAN_GROUP, [
            'title' => __( 'Dokan', 'dokan' ),
        ] );

        foreach ( $tags as $tag ) {
            $module->register_tag( "\\DokanPro\\Modules\\Elementor\\Tags\\{$tag}" );
        }
    }
}
