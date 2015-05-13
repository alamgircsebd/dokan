<?php

/**
 * Dokan SEO class
 * Integrates Dokan SEO template in front-end Settings menu
 *  
 * @author WeDevs
 */
class Dokan_Store_Seo {

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

        add_action( 'wp_ajax_insert_meta_data', array( $this, 'insert_meta_data' ) );
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

}
$seo = Dokan_Store_Seo::init();

