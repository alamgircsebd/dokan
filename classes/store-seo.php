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
        add_action( 'wp_ajax_nopriv_insert_meta_data', array( $this, 'insert_meta_data' ) );
    }
    
    /*
     * Adds proper hooks for output of meta tags
     * 
     */
    function output_meta_tags() {

        if ( class_exists( 'All_in_One_SEO_Pack' ) ) {

            //apply_filters( 'aioseop_title', $title );
            //apply_filters( 'aioseop_description', $this->get_main_description( $post ) );
            //apply_filters( 'aioseop_keywords', $keywords );

            add_filter( 'aioseop_title', array( $this, 'replace_title' ), 10, 2 );
            add_filter( 'aioseop_keywords', array( $this, 'replace_kw' ), 10, 2 );
            add_filter( 'aioseop_description', array( $this, 'replace_desc' ), 10, 2 );
            
        } elseif ( class_exists( 'WPSEO_Frontend' ) ) {

            //apply_filters( 'wptitle', $title );
            //apply_filters( 'wpmetakeywords', trim( $keywords ) );
            //apply_filters( 'wpmetadesc', trim( $metadesc ) );
            
            add_filter( 'wptitle', array( $this, 'replace_title' ), 10, 2 );
            add_filter( 'wpmetakeywords', array( $this, 'replace_kw' ), 10, 2 );
            add_filter( 'wpmetadesc', array( $this, 'replace_desc' ), 10, 2 );            
            
        } else {
            
            add_action( 'wp_head', array( $this, 'print_tags' ) );
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
        
        echo '<meta test/>';
        
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
        
        return $desc;
    }

}

$seo = new Dokan_Store_Seo();
$seo->output_meta_tags();

