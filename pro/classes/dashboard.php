<?php

/**
*  Dashboard Template Class
*
*  A template for frontend dashboard rendering items
*
*  @since 2.4
*
*  @author weDevs <info@wedevs.com>
*/
class Dokan_Pro_Dashboard extends Dokan_Template_Dashboard {

	/**
     * Constructor for the WeDevs_Dokan class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @uses add_action()
     *
     */
	public function __construct() {

		$this->comment_counts = $this->get_comment_counts();

		add_action( 'dokan_dashboard_before_widgets', array( $this, 'dokan_show_profile_progressbar' ), 10 );
		add_action( 'dokan_dashboard_left_widgets', array( $this, 'get_review_widget' ) , 16 );
		add_action( 'dokan_dashboard_right_widgets', array( $this, 'get_announcement_widget' ) , 12);
	}

	public function dokan_show_profile_progressbar() {
		echo dokan_get_profile_progressbar();
	}

	public function get_review_widget() {
		dokan_get_template_part( 'dashboard/review-widget', '', array(
				'pro' => true,
				'comment_counts'=> $this->comment_counts,
				'reviews_url'=> dokan_get_navigation_url('reviews'),
			)
		);
	}

	public function get_announcement_widget() {
		$template_notice = Dokan_Pro_Notice::init();
        $query = $template_notice->get_announcement_by_users( apply_filters( 'dokan_announcement_list_number', 3 ) );

		dokan_get_template_part( 'dashboard/announcement-widget', '', array(
				'pro' => true,
				'notices'=> $query->posts,
				'announcement_url'=> dokan_get_navigation_url('announcement'),
			)
		);
	}
}