<?php

namespace DokanPro\Modules\Elementor\Abstracts;

use Dokan\Traits\Singleton;

abstract class ModuleBase {

    use Singleton;

    /**
     * Runs after first instance
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function boot() {
        add_action( 'elementor/widgets/widgets_registered', [ $this, 'init_widgets' ] );
    }

    /**
     * Module name
     *
     * @since 1.0.0
     *
     * @return void
     */
    abstract public function get_name();

    /**
     * Module widgets
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function get_widgets() {
        return [];
    }

    /**
     * Register module widgets
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function init_widgets() {
        $widget_manager = dokan_elementor()->elementor()->widgets_manager;

        foreach ( $this->get_widgets() as $widget ) {
            $class_name = "\\DokanPro\\Modules\\Elementor\\Widgets\\{$widget}";

            if ( class_exists( $class_name ) ) {
                $widget_manager->register_widget_type( new $class_name() );
            }
        }
    }
}
