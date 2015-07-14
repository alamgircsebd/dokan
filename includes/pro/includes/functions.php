<?php

/**
 *  General Fnctions for Dokan Pro features
 *
 *  @since 2.4
 *
 *  @package dokan
 */

/**
 * Returns Current User Profile progress bar HTML
 *
 * @since 2.1
 *
 * @return output
 */
if ( !function_exists( 'dokan_get_profile_progressbar' ) ) {

	function dokan_get_profile_progressbar() {
	    global $current_user;

	    $profile_info = dokan_get_store_info( $current_user->ID );
	    $progress     = isset( $profile_info['profile_completion']['progress'] ) ? $profile_info['profile_completion']['progress'] : 0;
	    $next_todo    = isset( $profile_info['profile_completion']['next_todo'] ) ? $profile_info['profile_completion']['next_todo'] : __('Start with adding a Banner to gain profile progress','dokan');

	    ob_start();

	    if (  strlen( trim( $next_todo ) ) != 0 ) {
	    	dokan_get_template_part( 'global/profile-progressbar', '', array( 'pro'=>true, 'progress'=>$progress, 'next_todo' => $next_todo ) );
	    }

	    $output = ob_get_clean();

	    return $output;
	}

}

