<?php 

/**
*  Tempalte shortcode class file
*
*  @load all shortcode for template  rendering
*/		
class Dokan_Template_Shortcodes {
	
	function __construct() {
		add_shortcode( 'dokan-dashboard', array( $this, 'load_template_files' ) );
	}

	public static function init() {
        static $instance = false;

        if( !$instance ) {
            $instance = new Dokan_Template_Shortcodes();
        }

        return $instance;
    }


    public function load_template_files() {
    	global $wp;

	    if ( isset( $wp->query_vars['reports'] ) ) {
	        return dokan_get_template_part( 'reports' );
	    }

	    if ( isset( $wp->query_vars['products'] ) ) {
	        return dokan_get_template_part( 'products' ); 
	    }

	    if ( isset( $wp->query_vars['new-product'] ) ) {
	        return dokan_get_template_part( 'new-product' ); 
	    }

	    if ( isset( $wp->query_vars['orders'] ) ) {
	        return dokan_get_template_part( 'orders' ); 
	    }

	    if ( isset( $wp->query_vars['coupons'] ) ) {
	        return dokan_get_template_part( 'coupons' ); 
	    }

	    if ( isset( $wp->query_vars['reviews'] ) ) {
	        return dokan_get_template_part( 'reviews' ); 
	    }

	    if ( isset( $wp->query_vars['withdraw'] ) ) {
	        return dokan_get_template_part( 'withdraw' ); 
	    }

	    if ( isset( $wp->query_vars['settings'] ) ) {
	        return dokan_get_template_part( 'settings' ); 
	    }


	    return dokan_get_template_part( 'dashboard' );
    }

}