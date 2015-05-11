<?php
//$dokan_template_settings = Dokan_Template_Settings::init();
//$validate                = $dokan_template_settings->profile_validate();
//
//if ( $validate !== false && !is_wp_error( $validate ) ) {
//   $dokan_template_settings->insert_settings_info();
//}
?>

<div class="dokan-dashboard-wrap">
    <?php dokan_get_template( 'dashboard-nav.php', array( 'active_menu' => 'settings/seo' ) ); ?>

    <div class="dokan-dashboard-content dokan-settings-content">
        <article class="dokan-settings-area">
            <header class="dokan-dashboard-header">
                <h1 class="entry-title">
                    <?php _e( 'Store SEO', 'dokan' ); ?>
                    <small>&rarr; <a href="<?php echo dokan_get_store_url( get_current_user_id() ); ?>"><?php _e( 'Visit Store', 'dokan' ); ?></a></small>
                </h1>
            </header><!-- .dokan-dashboard-header -->

            <form method="post" id="dokan-store-seo-form"  action="" class="dokan-form-horizontal">

                <div class="dokan-form-group">
                    <label class="dokan-w3 dokan-control-label" for="dokan-seo-title"><?php _e( 'SEO Title :', 'dokan' ); ?></label>
                    <div class="dokan-w5 dokan-text-left">
                        <input id="dokan-seo-title" value="" name="dokan-seo-title" placeholder=" " class="dokan-form-control input-md" type="text">
                    </div>                         
                </div>

                <div class="dokan-form-group">
                    <label class="dokan-w3 dokan-control-label" for="dokan-seo-meta-desc"><?php _e( 'Meta Description :', 'dokan' ); ?></label>
                    <div class="dokan-w5 dokan-text-left">
                        <textarea class="dokan-form-control" rows="3" id="dokan-seo-meta-desc" name="dokan-seo-meta-desc"></textarea>
                    </div>                         
                </div>

                <div class="dokan-form-group">
                    <label class="dokan-w3 dokan-control-label" for="dokan-seo-meta-keywords"><?php _e( 'Meta Keywords :', 'dokan' ); ?></label>
                    <div class="dokan-w7 dokan-text-left">
                        <input id="dokan-seo-meta-keywords" value="" name="dokan-seo-meta-keywords" placeholder=" " class="dokan-form-control input-md" type="text">
                    </div>                         
                </div>



                <?php wp_nonce_field( 'dokan_store_seo_form_action', 'dokan_store_seo_form_nonce' ); ?>

                <div class="dokan-form-group" style="margin-left: 23%">   
                    <input type="submit" id='dokan-store-seo-form-submit' class="dokan-left dokan-btn dokan-btn-theme" value="<?php esc_attr_e( 'Save Changes', 'dokan' ); ?>">
                </div>

            </form>

        </article>
    </div><!-- .dokan-dashboard-content -->
</div><!-- .dokan-dashboard-wrap -->