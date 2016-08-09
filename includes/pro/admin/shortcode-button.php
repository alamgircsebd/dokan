<?php


class Dokan_shortcodes_button {
    
    public function __construct() {
        
        add_filter( 'mce_external_plugins',  array( $this, 'enqueue_plugin_scripts' ) );
        add_filter( 'mce_buttons',  array( $this, 'register_buttons_editor' ) );

    }
    
    /**
     * * Singleton object
     *
     * @staticvar boolean $instance
     *
     * @return \self
     */
    public static function init() {
        static $instance = false;

        if ( !$instance ) {
            $instance = new Dokan_shortcodes_button();
        }

        return $instance;
    }

    
    function enqueue_plugin_scripts( $plugin_array ) {
        //enqueue TinyMCE plugin script with its ID.
        $plugin_array["dokan_button"] =  DOKAN_PLUGIN_ASSEST . "/js/dokan-tmc-button.js";
        return $plugin_array;
    }
    
    function register_buttons_editor( $buttons ) {
        //register buttons with their id.
        array_push( $buttons, "dokan_button" );
       
        return $buttons;
    }

}

Dokan_shortcodes_button::init();
