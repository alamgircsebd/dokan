<?php

/**
 * Dokan SEO class
 * Integrates Dokan SEO template in front-end Settings menu
 *  
 * @author WeDevs
 */
class Dokan_Store_Seo {
    
    public $feedback  = false;
    public $seller_id = false;

    public function __construct() {
       
        $this->init_hooks();        
    }

    public static function init() {
        static $instance = false;

        if ( !$instance ) {
            $instance = new Dokan_Store_Seo();
        }

        return $instance;
    }
    
    /*
     * Init hooks and filters
     * 
     */
    function init_hooks() {

        add_action( 'wp_ajax_dokan_seo_form_handler', array( $this, 'dokan_seo_form_handler' ) );
        add_action( 'template_redirect', array( $this, 'output_meta_tags' ) );
    }
    
    /*
     * Adds proper hooks for output of meta tags
     * 
     */
    function output_meta_tags() {
        if ( ! dokan_is_store_page() ) {
            return;
        }
        
        if ( class_exists( 'All_in_One_SEO_Pack' ) ) {

            //apply_filters( 'aioseop_title', $title );
            //apply_filters( 'aioseop_description', $this->get_main_description( $post ) );
            //apply_filters( 'aioseop_keywords', $keywords );

            add_filter( 'wp_title', array( $this, 'replace_title' ),500 );
            add_filter( 'aioseop_keywords', array( $this, 'replace_keywords' ), 100 );
            add_filter( 'aioseop_description', array( $this, 'replace_desc' ), 100 );
            
        } elseif ( class_exists( 'WPSEO_Frontend' ) ) {
                      
            add_filter( 'wp_title', array( $this, 'replace_title' ),500 );
            add_filter( 'wpseo_metakeywords', array( $this, 'replace_keywords' ) );
            add_filter( 'wpseo_metadesc', array( $this, 'replace_desc' ) );            
            
        } else {
            
            add_filter( 'wp_title', array( $this, 'replace_title' ), 500 );
            add_action( 'wp_head', array( $this, 'print_tags' ), 500 );
            
        }
    }
    
    /*
     * prints out default meta tags from user meta
     * 
     * @since 1.0.0
     * @param none
     * 
     * @return void            
     */
    function print_tags() {
        //get values of title,desc and keywords
        
        $desc  = "Description, this is the long desciption";
        $keywords = "DOKAN, SEO, STORE, QQQ";
        ?>
        <meta name="description" content="<?php echo esc_attr($desc) ?>"/>
        <meta name="keywords" content="<?php echo esc_attr($keywords) ?>"/>
        <?php
    }
    
    /*
     * Replace title meta of other SEO plugin 
     * 
     * @param title
     * @since 1.0.0
     * 
     * @return string title
     */
    function replace_title($title){
        //get title
        
        $title = 'DOKAN_STORE_SEO';
        
        return $title;
    }
    
    /*
     * Replace keywords meta of other SEO plugin 
     * 
     * @param keywords
     * @since 1.0.0
     * 
     * @return keywords
     */
    function replace_keywords($keywords){
        
        $keywords = "DOKAN, SEO, STORE, QQQ";
        return $keywords;
    }
    
     /*
     * Replace description meta of other SEO plugin 
     * @param desc
     * @since 1.0.0
     * 
     * @return desc
     */
    function replace_desc(){
        
        $desc  = "Description, this is the long desciption";
        return $desc;
    }
    
    
    /*
    * print SEO meta form on frontend
    * 
    * @since 1.0.0
    * 
    */
    function frontend_meta_form(){
        $current_user   = get_current_user_id();
        $seller_profile = dokan_get_store_info( $current_user );
        $seo_meta = isset( $seller_profile['store_seo'] ) ? $seller_profile['store_seo'] : array();
        
        $default_store_seo = array(
            'dokan-seo-title'         => false,
            'dokan-seo-meta-desc'     => false,
            'dokan-seo-meta-keywords' => false,
        );
        
        $seo_meta = wp_parse_args( $seo_meta, $default_store_seo );
        
        
    ?>  
        <div class="dokan-alert dokan-hide" id="dokan-seo-feedback"></div>
        <form method="post" id="dokan-store-seo-form"  action="" class="dokan-form-horizontal">

                <div class="dokan-form-group">
                    <label class="dokan-w3 dokan-control-label" for="dokan-seo-title"><?php _e( 'SEO Title :', 'dokan' ); ?></label>
                    <div class="dokan-w5 dokan-text-left">
                        <input id="dokan-seo-title" value="<?php echo $this->print_saved_meta($seo_meta['dokan-seo-title']) ?>" name="dokan-seo-title" placeholder=" " class="dokan-form-control input-md" type="text">
                    </div>                         
                </div>

                <div class="dokan-form-group">
                    <label class="dokan-w3 dokan-control-label" for="dokan-seo-meta-desc"><?php _e( 'Meta Description :', 'dokan' ); ?></label>
                    <div class="dokan-w5 dokan-text-left">
                        <textarea class="dokan-form-control" rows="3" id="dokan-seo-meta-desc" name="dokan-seo-meta-desc"><?php echo $this->print_saved_meta($seo_meta['dokan-seo-meta-desc']) ?></textarea>
                    </div>                         
                </div>

                <div class="dokan-form-group">
                    <label class="dokan-w3 dokan-control-label" for="dokan-seo-meta-keywords"><?php _e( 'Meta Keywords :', 'dokan' ); ?></label>
                    <div class="dokan-w7 dokan-text-left">
                        <input id="dokan-seo-meta-keywords" value="<?php echo $this->print_saved_meta($seo_meta['dokan-seo-meta-keywords']) ?>" name="dokan-seo-meta-keywords" placeholder=" " class="dokan-form-control input-md" type="text">
                    </div>                         
                </div>

                <?php wp_nonce_field( 'dokan_store_seo_form_action', 'dokan_store_seo_form_nonce' ); ?>

                <div class="dokan-form-group" style="margin-left: 23%">   
                    <input type="submit" id='dokan-store-seo-form-submit' class="dokan-left dokan-btn dokan-btn-theme" value="<?php esc_attr_e( 'Save Changes', 'dokan' ); ?>">
                </div>
            </form>
    <?php  
    }
    
   /*check meta data and print
    * @since 1.0.0
    * @param bool || string $val
    * 
    * @return string $val
    */
    function print_saved_meta( $val ) {
        if ( $val == false )
            return '';
        else
            return esc_attr ($val);
    }

    function dokan_seo_form_handler(){
        parse_str( $_POST['data'], $postdata );

        if ( !wp_verify_nonce( $postdata['dokan_store_seo_form_nonce'], 'dokan_store_seo_form_action' ) ) {
            wp_send_json_error( __( 'Are you cheating?', 'dokan' ) );
        }
        
        unset( $postdata['dokan_store_seo_form_nonce'] );
        unset( $postdata['_wp_http_referer'] );
        
        $default_store_seo = array(
            'dokan-seo-title'         => false,
            'dokan-seo-meta-desc'     => false,
            'dokan-seo-meta-keywords' => false,
        );

        $current_user   = get_current_user_id();
        $seller_profile = dokan_get_store_info( $current_user );
        
        $seller_profile['store_seo'] = wp_parse_args( $postdata, $default_store_seo );
        
        update_user_meta( $current_user, 'dokan_profile_settings', $seller_profile );
        
        wp_send_json_success('Your Changes Have been Updated');
    }
}
$seo = Dokan_Store_Seo::init();

