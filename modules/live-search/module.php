<?php

namespace WeDevs\DokanPro\Modules\LiveSearch;

/**
 * Dokan_Live_Search class
 *
 * @class Dokan_Live_Search The class that holds the entire Dokan_Live_Search plugin
 */
class Module {

    /**
     * Constructor for the Dokan_Live_Search class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @uses is_admin()
     * @uses add_action()
     */
    public function __construct() {
        include_once 'classes/class-dokan-live-search.php';

        // Widget initialization hook
        add_action( 'widgets_init',array($this,'initialize_widget_register' ) );

        // Loads frontend scripts and styles
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

        // removing redirection to single product page
        add_filter( 'woocommerce_redirect_single_search_result', '__return_false' );

    }

    /**
     * Enqueue admin scripts
     *
     * Allows plugin assets to be loaded.
     *
     * @uses wp_enqueue_script()
     * @uses wp_localize_script()
     * @uses wp_enqueue_style()
     */
    public function enqueue_scripts() {
        wp_enqueue_style( 'dokan-ls-custom-style', plugins_url( 'assets/css/style.css', __FILE__ ), false, date( 'Ymd' ) );
        wp_enqueue_script( 'dokan-ls-custom-js', plugins_url( 'assets/js/script.js', __FILE__ ), array( 'jquery' ), false, true );

        wp_localize_script( 'dokan-ls-custom-js', 'dokanLiveSearch', array(
            'ajaxurl'      => admin_url( 'admin-ajax.php' ),
            'loading_img'  => plugins_url( 'assets/images/loading.gif', __FILE__ ),
            'currentTheme' => wp_get_theme()->stylesheet,
            'themeTags'    => apply_filters( 'dokan_ls_theme_tags', array() )
        ));
    }

    /**
     * Callback for Widget Initialization
     *
     * @return void
     */
    public function initialize_widget_register(){
        register_widget( 'Dokan_Live_Search_Widget' );
    }
}
