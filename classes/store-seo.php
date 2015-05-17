<?php

/**
 * Dokan SEO class
 * Integrates Dokan SEO template in front-end Settings menu
 *  
 * @author WeDevs
 */
class Dokan_Store_Seo {

    public $feedback    = false;
    private $store_info = false;

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
        if ( !dokan_is_store_page() ) {
            return;
        }

        if ( dokan_get_option( 'store_seo', 'dokan_general' ) === 'off' ) {
            return;
        }

        $this->store_info = dokan_get_store_info( get_query_var( 'author' ) );

        if ( class_exists( 'All_in_One_SEO_Pack' ) ) {

            add_filter( 'aioseop_title', array( $this, 'replace_title' ), 500 );
            add_filter( 'aioseop_keywords', array( $this, 'replace_keywords' ), 100 );
            add_filter( 'aioseop_description', array( $this, 'replace_desc' ), 100 );
        } elseif ( class_exists( 'WPSEO_Frontend' ) ) {

            add_filter( 'wp_title', array( $this, 'replace_title' ), 500 );
            add_filter( 'wpseo_metakeywords', array( $this, 'replace_keywords' ) );
            add_filter( 'wpseo_metadesc', array( $this, 'replace_desc' ) );
            
            add_filter( 'wpseo_opengraph_title', array( $this, 'replace_og_title' ) );
            add_filter( 'wpseo_opengraph_desc', array( $this, 'replace_og_desc' ) );
            add_filter( 'wpseo_opengraph_image', array( $this, 'replace_og_img' ) );
            add_action( 'wpseo_opengraph', array( $this, 'print_og_img' ), 20 );
            
            
        } else {

            add_filter( 'wp_title', array( $this, 'replace_title' ), 500 );
            add_action( 'wp_head', array( $this, 'print_tags' ), 1 );
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
        $meta_values = $this->store_info;

        if ( !isset( $meta_values['store_seo'] ) || $meta_values == false ) {
            return;
        }

        $desc     = $meta_values['store_seo']['dokan-seo-meta-desc'];
        $keywords = $meta_values['store_seo']['dokan-seo-meta-keywords'];
        $og_title = $meta_values['store_seo']['dokan-seo-og-title'];
        $og_desc  = $meta_values['store_seo']['dokan-seo-og-desc'];
        $og_img  = $meta_values['store_seo']['dokan-seo-og-image'];

        if ( $desc ) {
            echo PHP_EOL . '<meta name="description" content="' . $this->print_saved_meta( $desc ) . '"/>';
        }
        if ( $keywords ) {
            echo PHP_EOL . '<meta name="keywords" content="' . $this->print_saved_meta( $keywords ) . '"/>';
        }
        if ( $og_title ) {
            echo PHP_EOL . '<meta property="og:title" content="' . $this->print_saved_meta( $og_title ) . '"/>';
        }
        if ( $og_desc ) {
            echo PHP_EOL . '<meta property="og:description" content="' . $this->print_saved_meta( $og_desc ) . '"/>';
        }
        if ( $og_img ) {
            echo PHP_EOL . '<meta property="og:image" content="' . $this->replace_og_img( $og_img ) . '"/>';
        }
    }

    /*
     * Replace title meta of other SEO plugin 
     * 
     * @param title
     * @since 1.0.0
     * 
     * @return string title
     */

    function replace_title( $title ) {
        //get title

        $title_default = $title;

        $meta_values = $this->store_info;

        if ( !isset( $meta_values['store_seo'] ) || $meta_values == false ) {
            return $title_default;
        }

        $title = $meta_values['store_seo']['dokan-seo-title'];

        if ( $title ) {
            return $title;
        } else {
            return $title_default;
        }
    }

    /*
     * Replace keywords meta of other SEO plugin 
     * 
     * @param keywords
     * @since 1.0.0
     * 
     * @return keywords
     */

    function replace_keywords( $keywords ) {

        $keywords_default = $keywords;

        $meta_values = $this->store_info;

        if ( !isset( $meta_values['store_seo'] ) || $meta_values == false ) {
            return $keywords_default;
        }

        $keywords = $meta_values['store_seo']['dokan-seo-meta-keywords'];

        if ( $keywords )
            return $keywords;
        else
            return $keywords_default;
    }

    /*
     * Replace description meta of other SEO plugin 
     * @param desc
     * @since 1.0.0
     * 
     * @return desc
     */

    function replace_desc( $desc ) {

        $desc_default = $desc;

        $meta_values = $this->store_info;

        if ( !isset( $meta_values['store_seo'] ) || $meta_values == false ) {
            return $desc_default;
        }

        $desc = $meta_values['store_seo']['dokan-seo-meta-desc'];

        if ( $desc )
            return $desc;
        else
            return $desc_default;
    }
    
   /*replace OG tag title for WP_SEO
    * @since 1.0.0
    */
    function replace_og_title( $title ) {

        $title_default = $title;

        $meta_values = $this->store_info;

        if ( !isset( $meta_values['store_seo'] ) || $meta_values == false ) {
            return $title_default;
        }

        $title = $meta_values['store_seo']['dokan-seo-og-title'];

        if ( $title ) {
            return $title;
        } else {
            return $title_default;
        }
    }

   /*replace OG tag description for WP_SEO
    * @since 1.0.0
    */
    function replace_og_desc( $desc ){
        
        $desc_default = $desc;

        $meta_values = $this->store_info;

        if ( !isset( $meta_values['store_seo'] ) || $meta_values == false ) {
            return $desc_default;
        }

        $desc = $meta_values['store_seo']['dokan-seo-og-desc'];

        if ( $desc )
            return $desc;
        else
            return $desc_default;
        
    }
    
   /*replace OG tag Image for WP_SEO
    * @since 1.0.0
    */
    function replace_og_img( $img ){
        $img_default = $img;

        $meta_values = $this->store_info;

        if ( !isset( $meta_values['store_seo'] ) || $meta_values == false ) {
            return $img_default;
        }

        $img = $meta_values['store_seo']['dokan-seo-og-image'];

        if ( $img )           
            return wp_get_attachment_url($img);                    
        else
            return $img_default;       
    }
    
    function print_og_img(){
        $meta_values = $this->store_info;

        if ( !isset( $meta_values['store_seo'] ) || $meta_values == false ) {
            return;
        }
        
        $og_img  = $meta_values['store_seo']['dokan-seo-og-image'];
        
        if ( $og_img ) {
            echo '<meta property="og:image" content="' . $this->replace_og_img( $og_img ) . '"/>';
        }
        
    }

    /*
     * print SEO meta input form on frontend
     * 
     * @since 1.0.0
     * 
     */

    function frontend_meta_form() {
        $current_user   = get_current_user_id();
        $seller_profile = dokan_get_store_info( $current_user );
        $seo_meta       = isset( $seller_profile['store_seo'] ) ? $seller_profile['store_seo'] : array();

        $default_store_seo = array(
            'dokan-seo-title'         => false,
            'dokan-seo-meta-desc'     => false,
            'dokan-seo-meta-keywords' => false,
            'dokan-seo-og-title'      => false,
            'dokan-seo-og-desc'       => false,
            'dokan-seo-og-image'      => false,
        );

        $seo_meta = wp_parse_args( $seo_meta, $default_store_seo );
        ?>  
        <div class="dokan-alert dokan-hide" id="dokan-seo-feedback"></div>
        <form method="post" id="dokan-store-seo-form"  action="" class="dokan-form-horizontal">

            <div class="dokan-form-group">
                <label class="dokan-w3 dokan-control-label" for="dokan-seo-title"><?php _e( 'SEO Title :', 'dokan' ); ?></label>
                <div class="dokan-w5 dokan-text-left">
                    <input id="dokan-seo-title" value="<?php echo $this->print_saved_meta( $seo_meta['dokan-seo-title'] ) ?>" name="dokan-seo-title" placeholder=" " class="dokan-form-control input-md" type="text">
                </div>                         
            </div>

            <div class="dokan-form-group">
                <label class="dokan-w3 dokan-control-label" for="dokan-seo-meta-desc"><?php _e( 'Meta Description :', 'dokan' ); ?></label>
                <div class="dokan-w5 dokan-text-left">
                    <textarea class="dokan-form-control" rows="3" id="dokan-seo-meta-desc" name="dokan-seo-meta-desc"><?php echo $this->print_saved_meta( $seo_meta['dokan-seo-meta-desc'] ) ?></textarea>
                </div>                         
            </div>

            <div class="dokan-form-group">
                <label class="dokan-w3 dokan-control-label" for="dokan-seo-meta-keywords"><?php _e( 'Meta Keywords :', 'dokan' ); ?></label>
                <div class="dokan-w7 dokan-text-left">
                    <input id="dokan-seo-meta-keywords" value="<?php echo $this->print_saved_meta( $seo_meta['dokan-seo-meta-keywords'] ) ?>" name="dokan-seo-meta-keywords" placeholder=" " class="dokan-form-control input-md" type="text">
                </div>                         
            </div>
            
            <?php $this->print_fb_meta_form( $seo_meta ); ?>
                
            <?php wp_nonce_field( 'dokan_store_seo_form_action', 'dokan_store_seo_form_nonce' ); ?>

            <div class="dokan-form-group" style="margin-left: 23%">   
                <input type="submit" id='dokan-store-seo-form-submit' class="dokan-left dokan-btn dokan-btn-theme" value="<?php esc_attr_e( 'Save Changes', 'dokan' ); ?>">
            </div>
        </form>
        <?php
    }
    
    /*
     * print social meta input fields
     * 
     * @since 1.0.0
     * 
     */
    function print_fb_meta_form( $seo_meta ){
        ?>
        
        <div class="dokan-form-group">
                <label class="dokan-w3 dokan-control-label" for="dokan-seo-og-title"><?php _e( 'Facebook Title :', 'dokan' ); ?></label>
                <div class="dokan-w5 dokan-text-left">
                    <input id="dokan-seo-og-title" value="<?php echo $this->print_saved_meta( $seo_meta['dokan-seo-og-title'] ) ?>" name="dokan-seo-og-title" placeholder=" " class="dokan-form-control input-md" type="text">
                </div>                         
        </div>
        
        <div class="dokan-form-group">
                <label class="dokan-w3 dokan-control-label" for="dokan-seo-og-desc"><?php _e( 'Facebook Description :', 'dokan' ); ?></label>
                <div class="dokan-w5 dokan-text-left">
                    <textarea class="dokan-form-control" rows="3" id="dokan-seo-og-desc" name="dokan-seo-og-desc"><?php echo $this->print_saved_meta( $seo_meta['dokan-seo-og-desc'] ) ?></textarea>
                </div>               
        </div>
        <?php
        $og_image = $seo_meta['dokan-seo-og-image'] ? $seo_meta['dokan-seo-og-image'] : 0;
        $og_image_url = $og_image ? wp_get_attachment_thumb_url( $og_image ) : '';
        
        ?>
        <div class="dokan-form-group ">
            <label class="dokan-w3 dokan-control-label" for="dokan-seo-og-image"><?php _e( 'Facebook Image :', 'dokan' ); ?></label>
            <div class="dokan-w5 dokan-gravatar dokan-seo-image">
                <div class="dokan-left gravatar-wrap<?php echo $og_image ? '' : ' dokan-hide'; ?>">                    
                    <input type="hidden" class="dokan-file-field" value="<?php echo $og_image; ?>" name="dokan-seo-og-image">
                    <img class="dokan-gravatar-img" src="<?php echo esc_url( $og_image_url ); ?>">
                    <a class="dokan-close dokan-remove-gravatar-image">&times;</a>
                </div>

                <div class="gravatar-button-area <?php echo $og_image ? ' dokan-hide' : ''; ?>">
                    <a href="#" class="dokan-gravatar-drag dokan-btn dokan-btn-default dokan-left"><i class="fa fa-cloud-upload"></i> <?php _e( 'Upload Photo', 'dokan' ); ?></a>
                </div>
            </div>
        </div>
        
        
        
        
        <?php
    }

    /* check meta data and print
     * @since 1.0.0
     * @param bool || string $val
     * 
     * @return string $val
     */

    function print_saved_meta( $val ) {
        if ( $val == false )
            return '';
        else
            return esc_attr( $val );
    }
    
    
    function dokan_seo_form_handler() {
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
            'dokan-seo-og-title'      => false,
            'dokan-seo-og-desc'       => false,
            'dokan-seo-og-image'      => false,
        );

        $current_user   = get_current_user_id();
        $seller_profile = dokan_get_store_info( $current_user );

        $seller_profile['store_seo'] = wp_parse_args( $postdata, $default_store_seo );

        //unset( $seller_profile['store_seo'] );

        update_user_meta( $current_user, 'dokan_profile_settings', $seller_profile );

        wp_send_json_success( 'Your Changes Have been Updated' );
    }

}

$seo = Dokan_Store_Seo::init();

