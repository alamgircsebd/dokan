<?php

namespace DokanPro\Modules\Elementor;

use DokanPro\Modules\Elementor\Abstracts\ModuleBase;
use DokanPro\Modules\Elementor\Conditions\Store as StoreCondition;
use DokanPro\Modules\Elementor\Documents\Store as StoreDocument;

class Module extends ModuleBase {

    /**
     * Widget group
     *
     * @since DOKAN_PRO_SINCE
     */
    const DOKAN_GROUP = 'dokan';

    /**
     * Run after first instance
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    public function boot() {
        parent::boot();

        add_action( 'elementor/documents/register', [ $this, 'register_documents' ] );
        add_action( 'elementor/dynamic_tags/register_tags', [ $this, 'register_tags' ] );
        add_action( 'elementor/controls/controls_registered', [ $this, 'register_controls' ] );
        add_action( 'elementor/editor/footer', [ $this, 'add_editor_templates' ], 9 );
        add_action( 'elementor/theme/register_conditions', [ $this, 'register_conditions' ] );
        add_filter( 'dokan_locate_template', [ $this, 'locate_template_for_store_page' ], 10, 3 );
        add_action( 'elementor/widgets/wordpress/widget_args', [ $this, 'add_widget_args' ], 10, 2 );
    }

    /**
     * Name of the elementor module
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return string
     */
    public function get_name() {
        return 'dokan';
    }

    /**
     * Module widgets
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return array
     */
    public function get_widgets() {
        return [
            'StoreBanner',
            'StoreName',
            'StoreProfilePicture',
            'StoreInfo',
            'StoreShareButton',
            'StoreSupportButton',
        ];
    }

    /**
     * Register module documents
     *
     * @since DOKAN_PRO_SINCE
     *
     * @param Elementor\Core\Documents_Manager $documents_manager
     *
     * @return void
     */
    public function register_documents( $documents_manager ) {
        $this->docs_types = [
            'store' => StoreDocument::get_class_full_name(),
        ];

        foreach ( $this->docs_types as $type => $class_name ) {
            $documents_manager->register_document_type( $type, $class_name );
        }
    }

    /**
     * Register module tags
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    public function register_tags() {
        $tags = [
            'StoreBanner',
            'StoreName',
            'StoreProfilePicture',
            'StoreInfo',
            'StoreSupportButton',
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
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    public function register_controls() {
        $controls = [
            'SortableList',
            'DynamicHidden',
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
     * @since DOKAN_PRO_SINCE
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

    /**
     * Register condition for the module
     *
     * @since DOKAN_PRO_SINCE
     *
     * @param \ElementorPro\Modules\ThemeBuilder\Classes\Conditions_Manager $conditions_manager
     *
     * @return void
     */
    public function register_conditions( $conditions_manager ) {
        $condition = new StoreCondition();
        $conditions_manager->get_condition( 'general' )->register_sub_condition( $condition );
    }

    /**
     * Filter to show the elementor built store template
     *
     * @since DOKAN_PRO_SINCE
     *
     * @param string $template
     * @param string $template_name
     * @param string $template_path
     *
     * @return string
     */
    public static function locate_template_for_store_page( $template, $template_name, $template_path ) {
        if ( dokan_is_store_page() ) {
            $page_templates_module = dokan_elementor()->elementor()->modules_manager->get_modules( 'page-templates' );

            $page_templates_module->set_print_callback( function() {
                \ElementorPro\Modules\ThemeBuilder\Module::instance()->get_locations_manager()->do_location( 'single' );
            } );

            $template_path = $page_templates_module->get_template_path( $page_templates_module::TEMPLATE_HEADER_FOOTER );

            return $template_path;
        }

        return $template;
    }

    /**
     * Add store widget args in Elementor ecosystem
     *
     * @since 1.0.0
     *
     * @param array             $default_widget_args
     * @param \Widget_WordPress $widget_wordpress
     *
     * @return array
     */
    public static function add_widget_args( $default_widget_args, $widget_wordpress ) {
        $widget_class_name = get_class( $widget_wordpress->get_widget_instance() );

        if ( 0 === strpos( $widget_class_name, 'Dokan_Store_') ) {
            $widget = $widget_wordpress->get_widget_instance();

            $id = str_replace( 'REPLACE_TO_ID', $widget_wordpress->get_id(), $widget->id );
            $default_widget_args['before_widget'] = sprintf( '<aside id="%1$s" class="widget dokan-store-widget %2$s">', $id, $widget->widget_options['classname'] );
            $default_widget_args['after_widget']  = '</aside>';
            $default_widget_args['before_title']  = '<h3 class="widget-title">';
            $default_widget_args['after_title']   = '</h3>';
        }

        return $default_widget_args;
    }
}
