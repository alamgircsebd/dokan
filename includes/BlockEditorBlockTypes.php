<?php

namespace WeDevs\DokanPro;

class BlockEditorBlockTypes {

    /**
     * Class constructor
     *
     * @since 2.9.16
     *
     * @return void
     */
    public function __construct() {
        add_filter( 'block_categories', [ $this, 'add_block_category' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'register_scripts' ], 9 );
        add_action( 'init', [ $this, 'register_block_types' ] );
    }

    /**
     * Add Dokan Block category
     *
     * @since 2.9.16
     *
     * @param array $categories
     */
    public function add_block_category( $categories ) {
        return array_merge( $categories, [
            [
                'slug'  => 'dokan',
                'title' => __( 'Dokan', 'dokan' ),
                'icon'  => 'wordpress',
            ]
        ] );
    }

    /**
     * Register block scripts
     *
     * @since 2.9.16
     *
     * @return void
     */
    public function register_scripts() {
        $screen = get_current_screen();

        if ( 'page' !== $screen->post_type ) {
            return;
        }

        $version = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? time() : DOKAN_PRO_PLUGIN_VERSION;

        wp_register_script(
            'dokan-blocks-editor-script',
            DOKAN_PRO_PLUGIN_ASSEST . '/js/dokan-blocks-editor-script.js',
            [ 'wp-blocks', 'wp-i18n', 'wp-element' ],
            $version,
            true
        );
    }

    /**
     * Register block types
     *
     * @since 2.9.16
     *
     * @return void
     */
    public function register_block_types() {
        register_block_type( 'dokan/shortcode', [
            'editor_script'   => 'dokan-blocks-editor-script',
            'render_callback' => [ $this, 'render_shortcode' ],
        ] );
    }

    /**
     * Render shortcode block content
     *
     * @since 2.9.16
     *
     * @param array  $attributes
     * @param string $content
     *
     * @return void
     */
    public function render_shortcode( $attributes, $content ) {
        if ( ! empty( $content ) ) {
            $content = trim( $content );
            return wpautop( $content );
        }
    }
}
