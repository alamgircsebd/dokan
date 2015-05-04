<?php
$dokan_template_settings = Dokan_Template_Settings::init();
$validate                = $dokan_template_settings->profile_validate();

if ( $validate !== false && !is_wp_error( $validate ) ) {
   $dokan_template_settings->insert_settings_info();
}

$scheme = is_ssl() ? 'https' : 'http';
wp_enqueue_script( 'google-maps', $scheme . '://maps.google.com/maps/api/js?sensor=true' );
?>

<div class="dokan-dashboard-wrap">
    <?php dokan_get_template( 'dashboard-nav.php', array( 'active_menu' => 'profile-settings' ) ); ?>

    <div class="dokan-dashboard-content dokan-settings-content">
        <article class="dokan-settings-area">
            <header class="dokan-dashboard-header">
                <h1 class="entry-title">
                    <?php _e( 'Settings', 'dokan' );?>
                    <small>&rarr; <a href="<?php echo dokan_get_store_url( get_current_user_id() ); ?>"><?php _e( 'Visit Store', 'dokan' ); ?></a></small>
                </h1>
            </header><!-- .dokan-dashboard-header -->

            <?php if ( is_wp_error( $validate ) ) {
                $messages = $validate->get_error_messages();

                foreach( $messages as $message ) {
                    ?>
                    <div class="dokan-alert dokan-alert-danger" style="width: 40%; margin-left: 25%;">
                        <button type="button" class="dokan-close" data-dismiss="alert">&times;</button>
                        <strong><?php echo $message; ?></strong>
                    </div>

                    <?php
                }
            } ?>

            <?php //$dokan_template_settings->setting_field($validate); ?>

            <!--settings updated content-->
            <?php
            global $current_user;

            if ( isset( $_GET['message'] ) ) {
                ?>
                <div class="dokan-alert dokan-alert-success">
                    <button type="button" class="dokan-close" data-dismiss="alert">&times;</button>
                    <strong><?php _e( 'Your profile has been updated successfully!', 'dokan' ); ?></strong>
                </div>
            <?php
            }

            $profile_info   = dokan_get_store_info( $current_user->ID );

            $gravatar       = isset( $profile_info['gravatar'] ) ? absint( $profile_info['gravatar'] ) : 0;

            $fb             = isset( $profile_info['social']['fb'] ) ? esc_url( $profile_info['social']['fb'] ) : '';
            $twitter        = isset( $profile_info['social']['twitter'] ) ? esc_url( $profile_info['social']['twitter'] ) : '';
            $gplus          = isset( $profile_info['social']['gplus'] ) ? esc_url ( $profile_info['social']['gplus'] ) : '';
            $linkedin       = isset( $profile_info['social']['linkedin'] ) ? esc_url( $profile_info['social']['linkedin'] ) : '';
            $youtube        = isset( $profile_info['social']['youtube'] ) ? esc_url( $profile_info['social']['youtube'] ) : '';
            $flickr         = isset( $profile_info['social']['flickr'] ) ? esc_url( $profile_info['social']['flickr'] ) : '';
            $instagram      = isset( $profile_info['social']['instagram'] ) ? esc_url( $profile_info['social']['instagram'] ) : '';

            // bank
            $phone          = isset( $profile_info['phone'] ) ? esc_attr( $profile_info['phone'] ) : '';
            $show_email     = isset( $profile_info['show_email'] ) ? esc_attr( $profile_info['show_email'] ) : 'no';


            if ( is_wp_error( $validate ) ) {
                $social       = $_POST['settings']['social'];

                $fb           = esc_url( $social['fb'] );
                $twitter      = esc_url( $social['twitter'] );
                $gplus        = esc_url( $social['gplus'] );
                $linkedin     = esc_url( $social['linkedin'] );
                $youtube      = esc_url( $social['youtube'] );
                $phone        = $_POST['setting_phone'];

            }
            $social_field_array = apply_filters( 'dokan_profile_social_fields', array(
                    'fb' => array(
                        'icon' => 'facebook-square',
                        'value' => $fb,
                    ),
                    'gplus' => array(
                        'icon' => 'google-plus',
                        'value' => $gplus,
                    ),
                    'twitter' => array(
                        'icon' => 'twitter',
                        'value' => $twitter,
                    ),
                    'linkedin' => array(
                        'icon' => 'linkedin',
                        'value' => $linkedin,
                    ),
                    'youtube' => array(
                        'icon' => 'youtube',
                        'value' => $youtube,
                    ),
                    'instagram' => array(
                        'icon' => 'instagram',
                        'value' => $instagram,
                    ),
                    'flickr' => array(
                        'icon' => 'flickr',
                        'value' => $flickr,
                    ),
                )
            );
            ?>

            <div class="dokan-ajax-response">
                <?php echo dokan_get_profile_progressbar(); ?>
            </div>

            <?php do_action( 'dokan_settings_before_form', $current_user, $profile_info ); ?>

            <form method="post" id="profile-form"  action="" class="dokan-form-horizontal"><?php ///settings-form ?>

                <?php wp_nonce_field( 'dokan_profile_settings_nonce' ); ?>

                <div class="dokan-form-group">
                    <label class="dokan-w3 dokan-control-label" for="dokan_gravatar"><?php _e( 'Profile Picture', 'dokan' ); ?></label>

                    <div class="dokan-w5 dokan-gravatar">
                        <div class="dokan-left gravatar-wrap<?php echo $gravatar ? '' : ' dokan-hide'; ?>">
                            <?php $gravatar_url = $gravatar ? wp_get_attachment_url( $gravatar ) : ''; ?>
                            <input type="hidden" class="dokan-file-field" value="<?php echo $gravatar; ?>" name="dokan_gravatar">
                            <img class="dokan-gravatar-img" src="<?php echo esc_url( $gravatar_url ); ?>">
                            <a class="dokan-close dokan-remove-gravatar-image">&times;</a>
                        </div>
                        <div class="gravatar-button-area<?php echo $gravatar ? ' dokan-hide' : ''; ?>">
                            <a href="#" class="dokan-gravatar-drag dokan-btn dokan-btn-default"><i class="fa fa-cloud-upload"></i> <?php _e( 'Upload Photo', 'dokan' ); ?></a>
                        </div>
                    </div>
                </div>

                <div class="dokan-form-group">
                    <label class="dokan-w3 dokan-control-label" for="setting_phone"><?php _e( 'Phone No', 'dokan' ); ?></label>
                    <div class="dokan-w5 dokan-text-left">
                        <input id="setting_phone" value="<?php echo $phone; ?>" name="setting_phone" placeholder="+123456.." class="dokan-form-control input-md" type="text">
                    </div>
                </div>

                <div class="dokan-form-group">
                    <label class="dokan-w3 dokan-control-label" for="setting_phone"><?php _e( 'Email', 'dokan' ); ?></label>
                    <div class="dokan-w5 dokan-text-left">
                        <div class="checkbox">
                            <label>
                                <input type="hidden" name="setting_show_email" value="no">
                                <input type="checkbox" name="setting_show_email" value="yes"<?php checked( $show_email, 'yes' ); ?>> <?php _e( 'Show email address in store', 'dokan' ); ?>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="dokan-form-group">
                    <label class="dokan-w3 dokan-control-label" for="settings[social][fb]"><?php _e( 'Social Profile', 'dokan' ); ?></label>

                    <div class="dokan-w5 dokan-text-left">
                        <?php
                        foreach( $social_field_array as $social_word => $social_array ) {
                            ?>
                            <div class="dokan-input-group dokan-form-group">
                                <span class="dokan-input-group-addon"><i class="fa fa-<?php echo isset( $social_array['icon'] )?$social_array['icon']:''; ?>"></i></span>
                                <input id="settings[social][<?php echo $social_word; ?>]" value="<?php echo $social_array['value']; ?>" name="settings[social][<?php echo $social_word; ?>]" class="dokan-form-control" placeholder="http://" type="text">
                            </div>
                        <?php
                        }
                        ?>
                    </div>
                </div>

                <?php do_action( 'dokan_settings_form_bottom', $current_user, $profile_info ); ?>

                <div class="dokan-form-group">
                    <div class="dokan-w4 ajax_prev dokan-text-left" style="margin-left:24%;">
                        <input type="submit" name="dokan_update_profile_settings" class="dokan-btn dokan-btn-danger dokan-btn-theme" value="<?php esc_attr_e( 'Update Settings', 'dokan' ); ?>">
                    </div>
                </div>

            </form>

            <?php do_action( 'dokan_settings_after_form', $current_user, $profile_info ); ?>
            <!--settings updated content end-->

        </article>
    </div><!-- .dokan-dashboard-content -->
</div><!-- .dokan-dashboard-wrap -->