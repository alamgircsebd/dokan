<?php
/**
 * Dokan settings Class
 *
 * @author weDves
 */
class Dokan_Template_Main {

	/**
     * Load autometically when class inistantiate
     * hooked up all actions and filters
     *
     * @since 2.4
     */
    function __construct() {
        add_action( 'dokan_dashboard_content_before', array( $this, 'get_dashboard_side_navigation' ), 10 );
    }

    /**
     * Get Dashboard Side Navigations
     *
     * @since 2.4
     *
     * @return void
     */
    public function get_dashboard_side_navigation() {
    	global $wp;
    	$request = $wp->request;
    	$active = explode('/', $request );
    	unset( $active[0] );

    	if ( $active ) {
    		$active_menu = implode( '/', $active );
    	} else {
    		$active_menu = 'dashboard';
    	}

    	dokan_get_template_part( 'global/dashboard-nav', '', array( 'active_menu' => $active_menu ) );
    }


}