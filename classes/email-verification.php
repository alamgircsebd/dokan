<?php

/**
 * Dokan Email Verification class
 *
 * @since 2.6.8
 *
 * @package dokan-pro
 *
 */

Class Dokan_Email_Verification {

    private $base_url;

    /**
     * Load automatically when class instantiated
     *
     * @since 2.6.8
     *
     * @uses actions|filter hooks
     */
    public function __construct() {
        $this->base_url = dokan_get_page_url( 'myaccount', 'woocommerce' );
        $this->init_hooks();
    }

    /**
     * Instantiate the class
     *
     * @since 2.6
     *
     * @return object
     */
    public static function init() {
        static $instance = false;

        if ( !$instance ) {
            $instance = new Dokan_Email_Verification();
        }

        return $instance;
    }

    /**
     * call actions and hooks
     */
    public function init_hooks() {
        //add settings menu page
        add_filter( 'dokan_settings_sections', array( $this, 'dokan_email_verification_settings' ) );
        add_filter( 'dokan_settings_fields', array( $this, 'dokan_email_settings_fields' ) );

        if ( 'on' != dokan_get_option( 'enabled', 'dokan_email_verification' ) ) {
           return;
        }
        
        add_action( 'woocommerce_created_customer', array( $this,'send_verification_email'), 80, 3 );
        add_action( 'init', array( $this,'validate_email_link' ) );
        add_action( 'woocommerce_email_footer', array( $this,'add_activation_link' ) ); 
        //add_action( 'init', array( $this, 'verifiy_email' ) );
        
    }
    
    /**
     * 
     * @param type $customer_id
     * @param type $new_customer_data
     * @param type $password_generated
     */
    function send_verification_email( $customer_id, $new_customer_data, $password_generated ) {
        $user            = get_user_by( 'id', $customer_id );
        $code            = sha1( $customer_id . $user->user_email . time() );
        
        $activation_link = add_query_arg( array('dokan_email_verification' => $code, 'id' => $customer_id ), get_permalink( get_option('woocommerce_myaccount_page_id') ) );
        
        // update user meta
        add_user_meta( $customer_id, '_dokan_email_verification_key', $code, true );
        add_user_meta( $customer_id, '_dokan_email_user_active', 0, true);
        error_log($activation_link);
        wp_logout();
        
        wc_add_notice( "Please Check your Email and complete Email verification to continue." );
        do_action( 'woocommerce_set_cart_cookies',  true );
        
        dokan_redirect_login();
        
    }
    
     /**
     * Validate Email from link
     */
    function validate_email_link() {

        if ( !isset( $_GET['dokan_email_verification'] ) && empty( $_GET['dokan_email_verification'] ) ) {
            return;
        }

        if ( !isset( $_GET['id'] ) && empty( $_GET['id'] ) ) {
            return;
        }

        $user_id = intval( $_GET['id'] );
        $activation_key = $_GET['dokan_email_verification'];

        if ( get_user_meta( $user_id, '_dokan_email_verification_key', true ) != $activation_key ) {
            return;
        }

        delete_user_meta( $user_id, '_dokan_email_user_active' );
        delete_user_meta( $user_id, '_dokan_email_verification_key' );
        
        wc_add_notice( "Account Activated successfully" );
        do_action( 'woocommerce_set_cart_cookies',  true );
        
        dokan_redirect_login();
    }
    
    function add_activation_link( $email ) {
        
        error_log("Email triggerred");
        if ( $email->id != 'customer_new_account' ) {
            return;
        }
        
        $user = get_user_by( 'email', $email->user_email );
        
        $verification_key = get_user_meta( $user->ID, '_dokan_email_verification_key', true );
         error_log("Found key : ". $verification_key);
        if ( empty( $verification_key ) ) {
            return;
        }
        
        $verification_link = add_query_arg( array('dokan_email_verification' => $verification_key, 'id' => $user->ID ), wp_login_url() );
         error_log("Found link : ". $verification_link);
        echo "<p>Visit this link to verify your profile : </p>".$verification_link;
    }

    /**
     * Filter admin menu settings section
     *
     * @param type $sections
     *
     * @return array
     */
    public function dokan_email_verification_settings( $sections ) {
        $sections[] = array(
            'id'    => 'dokan_email_verification',
            'title' => __( 'Email Verification', 'dokan' ),
            'icon'  => 'dashicons-networking'
        );
        return $sections;
    }

    /**
     * Render settings fields for admin settings section
     *
     * @param array $settings_fields
     *
     * @return array
     */
    public function dokan_email_settings_fields( $settings_fields ) {

        $settings_fields['dokan_email_verification'] = array(
            'enabled' => array(
                'name'  => 'enabled',
                'label' => __( 'Enable Email Verification', 'dokan' ),
                'type'  => "checkbox",
                'desc'  => __( 'Enabling this will add Email verification after registration form to allow users to verify their emails', 'dokan' ),
            ),
            'email_sent_page' => array(
                    'name'    => 'email_sent_page',
                    'label'   => __( 'Info page after registration', 'dokan-lite' ),
                    'type'    => 'select',
                    'options' => $this->get_post_type('page')
                ),
        );

        return $settings_fields;
    }
    
    /**
     * Get Post Type array
     *
     * @param  string $post_type
     *
     * @return array
     */
    public function get_post_type( $post_type ) {
        $pages_array = array( '-1' => __( '- select -', 'dokan-lite' ) );
        $pages = get_posts( array('post_type' => $post_type, 'numberposts' => -1) );

        if ( $pages ) {
            foreach ($pages as $page) {
                $pages_array[$page->ID] = $page->post_title;
            }
        }

        return $pages_array;
    }

}

$dokan_email_v = Dokan_Email_Verification::init();
