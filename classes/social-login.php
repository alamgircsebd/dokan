<?php

Class Dokan_Social_Login {
    
    private $base_url;
    /**
     * Load automatically when class instantiated
     *
     * @since 2.4
     *
     * @uses actions|filter hooks
     */
    public function __construct() {
        $this->base_url = dokan_get_page_url( 'myaccount', 'woocommerce' ). '/social-register';
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
            $instance = new Dokan_Social_Login();
        }

        return $instance;
    }

    public function init_hooks() {
        
        //Hybrid auth action
        add_action( 'init', array( $this, 'init_session' ) );
        add_action( 'init', array( $this, 'monitor_autheticate_requests' ) );
        
        //add settings menu page
        add_filter( 'dokan_settings_sections', array( $this, 'dokan_social_api_settings' ) );
        add_filter( 'dokan_settings_fields', array( $this, 'dokan_social_settings_fields' ) );
        
        // add social buttons on registration form
        add_action( 'woocommerce_register_form_end', array( $this, 'render_social_logins' ) );
        // add hybridauth library
        
        
        //add custom my account end-point
        add_filter( 'dokan_query_var_filter', array( $this, 'register_support_queryvar' ) );
        add_action( 'dokan_load_custom_template', array( $this, 'load_template_from_plugin' ) );
    }
    
    public function init_session() {
        if ( session_id() == '' ) {
            session_start();
        }
    }
    
    /**
     * Monitors Url for Hauth Request and process Hauth for authentication
     *
     * @global type $current_user
     *
     * @return void
     */
    public function monitor_autheticate_requests() {
        global $current_user;

        if ( !class_exists( 'WeDevs_Dokan' )){
            return;
        }

        $config = array(
            'base_url'   => $this->base_url,
            "debug_mode" => false,

            'providers' => array(
                "Facebook" => array(
                    "enabled" => true,
                    "keys"    => array( "id" => "", "secret" => "" ),
                    "scope"   => "email, public_profile, user_friends"
                ),
                "Google"   => array(
                    "enabled"         => true,
                    "keys"            => array( "id" => "", "secret" => "" ),
                    "scope"           => "https://www.googleapis.com/auth/userinfo.profile ". // optional
                                        "https://www.googleapis.com/auth/userinfo.email"   , // optional
                    "access_type"     => "offline",
                    "approval_prompt" => "force",
                    "hd"              => home_url()
                ),
                "LinkedIn" => array(
                    "enabled" => true,
                    "keys"    => array( "key" => "", "secret" => "" ),
                ),
                "Twitter"  => array(
                    "enabled" => true,
                    "keys"    => array( "key" => "", "secret" => "" ),
                ),
            )
        );
        //var_dump($config);

        //facebook config from admin
        $fb_id     = dokan_get_option( 'fb_app_id', 'dokan_social_api' );
        $fb_secret = dokan_get_option( 'fb_app_secret', 'dokan_social_api' );
        if ( $fb_id != '' && $fb_secret != '' ) {
            $config['providers']['Facebook']['keys']['id']     = $fb_id;
            $config['providers']['Facebook']['keys']['secret'] = $fb_secret;
        }
        //google config from admin
        $g_id     = dokan_get_option( 'google_app_id', 'dokan_social_api' );
        $g_secret = dokan_get_option( 'google_app_secret', 'dokan_social_api' );
        if ( $g_id != '' && $g_secret != '' ) {
            $config['providers']['Google']['keys']['id']     = $g_id;
            $config['providers']['Google']['keys']['secret'] = $g_secret;
        }
        //linkedin config from admin
        $l_id     = dokan_get_option( 'linkedin_app_id', 'dokan_social_api' );
        $l_secret = dokan_get_option( 'linkedin_app_secret', 'dokan_social_api' );
        if ( $l_id != '' && $l_secret != '' ) {
            $config['providers']['LinkedIn']['keys']['key']    = $l_id;
            $config['providers']['LinkedIn']['keys']['secret'] = $l_secret;
        }
        //Twitter config from admin
        $twitter_id     = dokan_get_option( 'twitter_app_id', 'dokan_social_api' );
        $twitter_secret = dokan_get_option( 'twitter_app_secret', 'dokan_social_api' );
        if ( $twitter_id != '' && $twitter_secret != '' ) {
            $config['providers']['Twitter']['keys']['key']    = $twitter_id;
            $config['providers']['Twitter']['keys']['secret'] = $twitter_secret;
        }

        /**
         * Filter the Config array of Hybridauth
         *
         * @since 1.0.0
         *
         * @param array $config
         */
        $config = apply_filters( 'dokan_social_providers_config', $config );

        if ( isset( $_GET['hauth_start'] ) || isset( $_GET['hauth_done'] ) ) {
            require_once DOKAN_PRO_INC . '/lib/Hybrid/Endpoint.php';

            Hybrid_Endpoint::process();
            exit;
        }
        
        
     

        if ( isset( $_GET['dokan_reg_dc'] ) ) {

            $seller_profile = dokan_get_store_info( $current_user->ID );

            $provider_dc = sanitize_text_field( $_GET['dokan_reg_dc'] );

            $seller_profile['dokan-social-api'][$provider_dc] = '';

            update_user_meta( $current_user->ID, 'dokan_profile_settings', $seller_profile );

            return;
        }
           
        if ( !isset( $_GET['dokan_reg'] ) ) {
            return;
        }

        $hybridauth = new Hybrid_Auth( $config );
        $provider   = $_GET['dokan_reg'];
        
        try {
            if ( $provider != '' ) {
                $adapter        = $hybridauth->authenticate( $provider );
                var_dump( $adapter );
                $user_profile   = $adapter->getUserProfile();
                $seller_profile = dokan_get_store_info( $current_user->ID );

                $seller_profile['dokan-social-api'][$provider] = (array) $user_profile;

                update_user_meta( $current_user->ID, 'dokan_profile_settings', $seller_profile );
                $seller_profile = dokan_get_store_info( $current_user->ID );
            }
        } catch ( Exception $e ) {
           $this->e_msg = $e->getMessage();
        }
    }

    public function dokan_social_api_settings( $sections ) {
        $sections[] = array(
            'id'    => 'dokan_social_api',
            'title' => __( 'Social API', 'dokan' ),
        );
        return $sections;
    }

    public function dokan_social_settings_fields( $settings_fields ) {
        
        $settings_fields['dokan_social_api'] = array(
            'facebook_app_label'  => array(
                'name'  => 'fb_app_label',
                'label' => __( 'Facebook App Settings', 'dokan-social-api' ),
                'type'  => "html",
                'desc'  => '<a target="_blank" href="https://developers.facebook.com/apps/">' . __( 'Create an App', 'dokan-social-api' ) . '</a> if you don\'t have one and fill App ID and Secret below.',
            ),
            'facebook_app_url'    => array(
                'name'  => 'fb_app_url',
                'label' => __( 'Site Url', 'dokan-social-api' ),
                'type'  => 'html',
                'desc'  => "<input class='regular-text' type='text' disabled value=" . $this->base_url . '?hauth.done=Facebook' . '>',
            ),
            'facebook_app_id'     => array(
                'name'  => 'fb_app_id',
                'label' => __( 'App Id', 'dokan-social-api' ),
                'type'  => 'text',
            ),
            'facebook_app_secret' => array(
                'name'  => 'fb_app_secret',
                'label' => __( 'App Secret', 'dokan-social-api' ),
                'type'  => 'text',
            ),
            'twitter_app_label'   => array(
                'name'  => 'twitter_app_label',
                'label' => __( 'Twitter App Settings', 'dokan-social-api' ),
                'type'  => 'html',
                'desc'  => '<a target="_blank" href="https://apps.twitter.com/">' . __( 'Create an App', 'dokan-social-api' ) . '</a> if you don\'t have one and fill Consumer key and Secret below.',
            ),
            'twitter_app_url'     => array(
                'name'  => 'twitter_app_url',
                'label' => __( 'Callback URL', 'dokan-social-api' ),
                'type'  => 'html',
                'desc'  => "<input class='regular-text' type='text' disabled value=" . $this->base_url . '?hauth.done=Twitter' . '>',
            ),
            'twitter_app_id'      => array(
                'name'  => 'twitter_app_id',
                'label' => __( 'Consumer Key', 'dokan-social-api' ),
                'type'  => 'text',
            ),
            'twitter_app_secret'  => array(
                'name'  => 'twitter_app_secret',
                'label' => __( 'Consumer Secret', 'dokan-social-api' ),
                'type'  => 'text',
            ),
            'google_app_label'    => array(
                'name'  => 'google_app_label',
                'label' => __( 'Google App Settings', 'dokan-social-api' ),
                'type'  => 'html',
                'desc'  => '<a target="_blank" href="https://console.developers.google.com/project">' . __( 'Create an App', 'dokan-social-api' ) . '</a> if you don\'t have one and fill Client ID and Secret below.',
            ),
            'google_app_url'      => array(
                'name'  => 'google_app_url',
                'label' => __( 'Redirect URI', 'dokan-social-api' ),
                'type'  => 'html',
                'desc'  => "<input class='regular-text' type='text' disabled value=" . $this->base_url . '?hauth.done=Google' . '>',
            ),
            'google_app_id'       => array(
                'name'  => 'google_app_id',
                'label' => __( 'Client ID', 'dokan-social-api' ),
                'type'  => 'text',
            ),
            'google_app_secret'   => array(
                'name'  => 'google_app_secret',
                'label' => __( 'Client secret', 'dokan-social-api' ),
                'type'  => 'text',
            ),
            'linkedin_app_label'  => array(
                'name'  => 'linkedin_app_label',
                'label' => __( 'Linkedin App Settings', 'dokan-social-api' ),
                'type'  => 'html',
                'desc'  => '<a target="_blank" href="https://www.linkedin.com/developer/apps">' . __( 'Create an App', 'dokan-social-api' ) . '</a> if you don\'t have one and fill Client ID and Secret below.',
            ),
            'linkedin_app_url'    => array(
                'name'  => 'linkedin_app_url',
                'label' => __( 'Redirect URL', 'dokan-social-api' ),
                'type'  => 'html',
                'desc'  => "<input class='regular-text' type='text' disabled value=" . $this->base_url . '?hauth.done=LinkedIn' . '>',
            ),
            'linkedin_app_id'     => array(
                'name'  => 'linkedin_app_id',
                'label' => __( 'Client ID', 'dokan-social-api' ),
                'type'  => 'text',
            ),
            'linkedin_app_secret' => array(
                'name'  => 'linkedin_app_secret',
                'label' => __( 'Client Secret', 'dokan-social-api' ),
                'type'  => 'text',
            ),
        );
        
        return $settings_fields;
    }
    
    /**
     * Register dokan query vars
     *
     * @since 1.0
     *
     * @param array $vars
     *
     * @return array new $vars
     */
    function register_support_queryvar( $vars ) {
        $vars[] = 'social-register';

        return $vars;
    }
    
    /**
     * Register page templates
     *
     * @since 1.0
     *
     * @param array $query_vars
     *
     * @return array $query_vars
     */
    function load_template_from_plugin( $query_vars ) {

        if ( isset( $query_vars['social-register'] ) ) {
            $template = DOKAN_PRO_DIR . '/templates/global/social-register.php';
            include $template;
        }
    }
    
    public function render_social_logins() {
        
        
        ?>
        <div class="jssocials-shares">
            <div class="jssocials-share jssocials-share-facebook">
                <a target="_blank" href="https://facebook.com/sharer/sharer.php?u=http%3A%2F%2Flocalhost%2Ftestwp%2Fstore%2Fseller_a%2F" class="jssocials-share-link">
                    <i class="fa fa-facebook jssocials-share-logo"></i>
                </a>
            </div>
            <div class="jssocials-share jssocials-share-twitter">
                <a target="_blank" href="https://twitter.com/share?url=http%3A%2F%2Flocalhost%2Ftestwp%2Fstore%2Fseller_a%2F&amp;text=my%20store" class="jssocials-share-link">
                    <i class="fa fa-twitter jssocials-share-logo"></i>
                </a>
            </div>
            <div class="jssocials-share jssocials-share-googleplus">
                <a target="_blank" href="https://plus.google.com/share?url=http%3A%2F%2Flocalhost%2Ftestwp%2Fstore%2Fseller_a%2F" class="jssocials-share-link">
                    <i class="fa fa-google jssocials-share-logo"></i>
                </a>
            </div>
            <div class="jssocials-share jssocials-share-linkedin">
                <a target="_blank" href="https://www.linkedin.com/shareArticle?mini=true&amp;url=http%3A%2F%2Flocalhost%2Ftestwp%2Fstore%2Fseller_a%2F" class="jssocials-share-link">
                    <i class="fa fa-linkedin jssocials-share-logo"></i>
                </a>
            </div>
        <?php
    }

}

$dokan_social_login = Dokan_Social_Login::init();
