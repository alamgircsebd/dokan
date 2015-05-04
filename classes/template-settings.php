<?php
/**
 * Dokan settings Class
 *
 * @author weDves
 */
class Dokan_Template_Settings {

    public static function init() {
        static $instance = false;

        if ( !$instance ) {
            $instance = new Dokan_Template_Settings();
        }

        return $instance;
    }

    /**
     * Save settings via ajax
     *
     * @return void
     */
    function ajax_settings() {

        $_POST['dokan_update_profile'] = '';

        switch( $_POST['form_id'] ) {
            case 'profile-form':
                if ( !wp_verify_nonce( $_POST['_wpnonce'], 'dokan_profile_settings_nonce' ) ) {
                    wp_send_json_error( __( 'Are you cheating?', 'dokan' ) );
                }
                $ajax_validate =  $this->profile_validate();
                break;
            case 'store-form':
                if ( !wp_verify_nonce( $_POST['_wpnonce'], 'dokan_store_settings_nonce' ) ) {
                    wp_send_json_error( __( 'Are you cheating?', 'dokan' ) );
                }
                $ajax_validate =  $this->store_validate();
                break;
            case 'payment-form':
                if ( !wp_verify_nonce( $_POST['_wpnonce'], 'dokan_payment_settings_nonce' ) ) {
                    wp_send_json_error( __( 'Are you cheating?', 'dokan' ) );
                }
                $ajax_validate =  $this->payment_validate();
                break;

        }


        if ( is_wp_error( $ajax_validate ) ) {
            wp_send_json_error( $ajax_validate->errors );
        }

        // we are good to go
        $save_data = $this->insert_settings_info();

        $progress_bar = dokan_get_profile_progressbar();
        $success_msg = __( 'Your information has been saved successfully', 'dokan' ) ;

        $data = array(
            'progress' => $progress_bar,
            'msg'      => $success_msg,
        );

        //wp_send_json_success( __( 'Your information has been saved successfully', 'dokan' ) );
        //wp_send_json_success(  array( 'success' => true, 'data'=> $data ) );
        wp_send_json_success( $data );

    }

    /**
     * Validate settings submission
     *
     * @return void
     */
    function validate() {

        if ( !isset( $_POST['dokan_update_profile'] ) ) {
            return false;
        }

        if ( !wp_verify_nonce( $_POST['_wpnonce'], 'dokan_settings_nonce' ) ) {
            wp_die( __( 'Are you cheating?', 'dokan' ) );
        }

        $error = new WP_Error();

        $dokan_name = sanitize_text_field( $_POST['dokan_store_name'] );

        if ( empty( $dokan_name ) ) {
            $error->add( 'dokan_name', __( 'Dokan name required', 'dokan' ) );
        }

        if ( isset( $_POST['setting_category'] ) ) {

            if ( !is_array( $_POST['setting_category'] ) || !count( $_POST['setting_category'] ) ) {
                $error->add( 'dokan_type', __( 'Dokan type required', 'dokan' ) );
            }
        }

        if ( !empty( $_POST['setting_paypal_email'] ) ) {
            $email = filter_var( $_POST['setting_paypal_email'], FILTER_VALIDATE_EMAIL );
            if ( empty( $email ) ) {
                $error->add( 'dokan_email', __( 'Invalid email', 'dokan' ) );
            }
        }

        if ( $error->get_error_codes() ) {
            return $error;
        }

        return true;

    }

    /**
     * validate profile settings
     * @return bool|WP_Error
     */
    function profile_validate() {

        if ( !isset( $_POST['dokan_update_profile_settings'] ) ) {
            return false;
        }

        if ( !wp_verify_nonce( $_POST['_wpnonce'], 'dokan_profile_settings_nonce' ) ) {
            wp_die( __( 'Are you cheating?', 'dokan' ) );
        }

        $error = new WP_Error();

        if ( isset( $_POST['setting_category'] ) ) {

            if ( !is_array( $_POST['setting_category'] ) || !count( $_POST['setting_category'] ) ) {
                $error->add( 'dokan_type', __( 'Dokan type required', 'dokan' ) );
            }
        }

        if ( !empty( $_POST['setting_paypal_email'] ) ) {
            $email = filter_var( $_POST['setting_paypal_email'], FILTER_VALIDATE_EMAIL );
            if ( empty( $email ) ) {
                $error->add( 'dokan_email', __( 'Invalid email', 'dokan' ) );
            }
        }

        if ( $error->get_error_codes() ) {
            return $error;
        }

        return true;

    }

    /**
     * Validate store settings
     * @return bool|WP_Error
     */
    function store_validate() {

        if ( !isset( $_POST['dokan_update_store_settings'] ) ) {
            return false;
        }

        if ( !wp_verify_nonce( $_POST['_wpnonce'], 'dokan_store_settings_nonce' ) ) {
            wp_die( __( 'Are you cheating?', 'dokan' ) );
        }

        $error = new WP_Error();

        $dokan_name = sanitize_text_field( $_POST['dokan_store_name'] );

        if ( empty( $dokan_name ) ) {
            $error->add( 'dokan_name', __( 'Dokan name required', 'dokan' ) );
        }

        if ( isset( $_POST['setting_category'] ) ) {

            if ( !is_array( $_POST['setting_category'] ) || !count( $_POST['setting_category'] ) ) {
                $error->add( 'dokan_type', __( 'Dokan type required', 'dokan' ) );
            }
        }

        if ( !empty( $_POST['setting_paypal_email'] ) ) {
            $email = filter_var( $_POST['setting_paypal_email'], FILTER_VALIDATE_EMAIL );
            if ( empty( $email ) ) {
                $error->add( 'dokan_email', __( 'Invalid email', 'dokan' ) );
            }
        }

        if ( $error->get_error_codes() ) {
            return $error;
        }

        return true;

    }

    /**
     * validate payment settings
     * @return bool|WP_Error
     */
    function payment_validate() {

        if ( !isset( $_POST['dokan_update_payment_settings'] ) ) {
            return false;
        }

        if ( !wp_verify_nonce( $_POST['_wpnonce'], 'dokan_payment_settings_nonce' ) ) {
            wp_die( __( 'Are you cheating?', 'dokan' ) );
        }

        $error = new WP_Error();


        if ( !empty( $_POST['setting_paypal_email'] ) ) {
            $email = filter_var( $_POST['setting_paypal_email'], FILTER_VALIDATE_EMAIL );
            if ( empty( $email ) ) {
                $error->add( 'dokan_email', __( 'Invalid email', 'dokan' ) );
            }
        }

        if ( $error->get_error_codes() ) {
            return $error;
        }

        return true;

    }

    /**
     * Save store settings
     *
     * @return void
     */
    function insert_settings_info() {

        $store_id = get_current_user_id();

        $prev_dokan_settings = get_user_meta( $store_id, 'dokan_profile_settings', true );


        if ( wp_verify_nonce( $_POST['_wpnonce'], 'dokan_profile_settings_nonce' ) ) {

            //update profile settings info
            $social = $_POST['settings']['social'];

            $dokan_settings =  array(
                'social'       => array(
                    'fb'        => filter_var( $social['fb'], FILTER_VALIDATE_URL ),
                    'gplus'     => filter_var( $social['gplus'], FILTER_VALIDATE_URL ),
                    'twitter'   => filter_var( $social['twitter'], FILTER_VALIDATE_URL ),
                    'linkedin'  => filter_var( $social['linkedin'], FILTER_VALIDATE_URL ),
                    'youtube'   => filter_var( $social['youtube'], FILTER_VALIDATE_URL ),
                    'flickr'    => filter_var( $social['flickr'], FILTER_VALIDATE_URL ),
                    'instagram' => filter_var( $social['instagram'], FILTER_VALIDATE_URL ),
                ),
                'phone'        => sanitize_text_field( $_POST['setting_phone'] ),
                'show_email'   => sanitize_text_field( $_POST['setting_show_email'] ),
                'gravatar'     => absint( $_POST['dokan_gravatar'] ),
            );


        }elseif ( wp_verify_nonce( $_POST['_wpnonce'], 'dokan_store_settings_nonce' ) ) {

            //update store setttings info
            $dokan_settings = array(
                'store_name'   => sanitize_text_field( $_POST['dokan_store_name'] ),
                'address'      => strip_tags( $_POST['setting_address'] ),
                'location'     => sanitize_text_field( $_POST['location'] ),
                'find_address' => sanitize_text_field( $_POST['find_address'] ),
                'banner'       => absint( $_POST['dokan_banner'] ),
            );

        }elseif ( wp_verify_nonce( $_POST['_wpnonce'], 'dokan_payment_settings_nonce' ) ) {

            //update payment settings info
            $dokan_settings = array(
                'payment'      => array(),
            );

            if ( isset( $_POST['settings']['bank'] ) ) {
                $bank = $_POST['settings']['bank'];

                $dokan_settings['payment']['bank'] = array(
                    'ac_name'   => sanitize_text_field( $bank['ac_name'] ),
                    'ac_number' => sanitize_text_field( $bank['ac_number'] ),
                    'bank_name' => sanitize_text_field( $bank['bank_name'] ),
                    'bank_addr' => sanitize_text_field( $bank['bank_addr'] ),
                    'swift'     => sanitize_text_field( $bank['swift'] ),
                );
            }

            if ( isset( $_POST['settings']['paypal'] ) ) {
                $dokan_settings['payment']['paypal'] = array(
                    'email' => filter_var( $_POST['settings']['paypal']['email'], FILTER_VALIDATE_EMAIL )
                );
            }

            if ( isset( $_POST['settings']['skrill'] ) ) {
                $dokan_settings['payment']['skrill'] = array(
                    'email' => filter_var( $_POST['settings']['skrill']['email'], FILTER_VALIDATE_EMAIL )
                );
            }

        }

        /*$social = $_POST['settings']['social'];

        $dokan_settings = array(
            'store_name'   => sanitize_text_field( $_POST['dokan_store_name'] ),
            'social'       => array(
                'fb'        => filter_var( $social['fb'], FILTER_VALIDATE_URL ),
                'gplus'     => filter_var( $social['gplus'], FILTER_VALIDATE_URL ),
                'twitter'   => filter_var( $social['twitter'], FILTER_VALIDATE_URL ),
                'linkedin'  => filter_var( $social['linkedin'], FILTER_VALIDATE_URL ),
                'youtube'   => filter_var( $social['youtube'], FILTER_VALIDATE_URL ),
                'flickr'    => filter_var( $social['flickr'], FILTER_VALIDATE_URL ),
                'instagram' => filter_var( $social['instagram'], FILTER_VALIDATE_URL ),
            ),
            'payment'      => array(),
            'phone'        => sanitize_text_field( $_POST['setting_phone'] ),
            'show_email'   => sanitize_text_field( $_POST['setting_show_email'] ),
            'address'      => strip_tags( $_POST['setting_address'] ),
            'location'     => sanitize_text_field( $_POST['location'] ),
            'find_address' => sanitize_text_field( $_POST['find_address'] ),
            'banner'       => absint( $_POST['dokan_banner'] ),
            'gravatar'     => absint( $_POST['dokan_gravatar'] ),
        );

        if ( isset( $_POST['settings']['bank'] ) ) {
            $bank = $_POST['settings']['bank'];

            $dokan_settings['payment']['bank'] = array(
                'ac_name'   => sanitize_text_field( $bank['ac_name'] ),
                'ac_number' => sanitize_text_field( $bank['ac_number'] ),
                'bank_name' => sanitize_text_field( $bank['bank_name'] ),
                'bank_addr' => sanitize_text_field( $bank['bank_addr'] ),
                'swift'     => sanitize_text_field( $bank['swift'] ),
            );
        }

        if ( isset( $_POST['settings']['paypal'] ) ) {
            $dokan_settings['payment']['paypal'] = array(
                'email' => filter_var( $_POST['settings']['paypal']['email'], FILTER_VALIDATE_EMAIL )
            );
        }

        if ( isset( $_POST['settings']['skrill'] ) ) {
            $dokan_settings['payment']['skrill'] = array(
                'email' => filter_var( $_POST['settings']['skrill']['email'], FILTER_VALIDATE_EMAIL )
            );
        }*/

        $dokan_settings = array_merge($prev_dokan_settings,$dokan_settings);

        $profile_completeness = $this->calculate_profile_completeness_value( $dokan_settings );
        $dokan_settings['profile_completion'] = $profile_completeness;

        update_user_meta( $store_id, 'dokan_profile_settings', $dokan_settings );

        do_action( 'dokan_store_profile_saved', $store_id, $dokan_settings );

        if ( ! defined( 'DOING_AJAX' ) ) {
            $_GET['message'] = 'profile_saved';
        }
    }

    /**
     * Show the settings form
     *
     * @param  string
     *
     * @return void
     */
    function setting_field( $validate = '' ) {
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

        $banner         = isset( $profile_info['banner'] ) ? absint( $profile_info['banner'] ) : 0;
        $storename      = isset( $profile_info['store_name'] ) ? esc_attr( $profile_info['store_name'] ) : '';
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
        $address        = isset( $profile_info['address'] ) ? esc_textarea( $profile_info['address'] ) : '';
        $map_location   = isset( $profile_info['location'] ) ? esc_attr( $profile_info['location'] ) : '';
        $map_address    = isset( $profile_info['find_address'] ) ? esc_attr( $profile_info['find_address'] ) : '';
        $dokan_category = isset( $profile_info['dokan_category'] ) ? $profile_info['dokan_category'] : '';


        if ( is_wp_error( $validate ) ) {
            $social       = $_POST['settings']['social'];
            $storename    = $_POST['dokan_store_name'];

            $fb           = esc_url( $social['fb'] );
            $twitter      = esc_url( $social['twitter'] );
            $gplus        = esc_url( $social['gplus'] );
            $linkedin     = esc_url( $social['linkedin'] );
            $youtube      = esc_url( $social['youtube'] );

            $phone        = $_POST['setting_phone'];
            $address      = $_POST['setting_address'];
            $map_location = $_POST['location'];
            $map_address  = $_POST['find_address'];
        }
        ?>

            <div class="dokan-ajax-response">
                <?php echo dokan_get_profile_progressbar(); ?>
            </div>

            <?php do_action( 'dokan_settings_before_form', $current_user, $profile_info ); ?>

            <form method="post" id="settings-form"  action="" class="dokan-form-horizontal">

                <?php wp_nonce_field( 'dokan_settings_nonce' ); ?>

                <div class="dokan-banner">

                    <div class="image-wrap<?php echo $banner ? '' : ' dokan-hide'; ?>">
                        <?php $banner_url = $banner ? wp_get_attachment_url( $banner ) : ''; ?>
                        <input type="hidden" class="dokan-file-field" value="<?php echo $banner; ?>" name="dokan_banner">
                        <img class="dokan-banner-img" src="<?php echo esc_url( $banner_url ); ?>">

                        <a class="close dokan-remove-banner-image">&times;</a>
                    </div>

                    <div class="button-area<?php echo $banner ? ' dokan-hide' : ''; ?>">
                        <i class="fa fa-cloud-upload"></i>

                        <a href="#" class="dokan-banner-drag dokan-btn dokan-btn-info dokan-theme"><?php _e( 'Upload banner', 'dokan' ); ?></a>
                        <p class="help-block"><?php _e( '(Upload a banner for your store. Banner size is (825x300) pixel. )', 'dokan' ); ?></p>
                    </div>
                </div> <!-- .dokan-banner -->

                <?php do_action( 'dokan_settings_after_banner', $current_user, $profile_info ); ?>

                <div class="dokan-form-group">
                    <label class="dokan-w3 dokan-control-label" for="dokan_store_name"><?php _e( 'Store Name', 'dokan' ); ?></label>

                    <div class="dokan-w5 dokan-text-left">
                        <input id="dokan_store_name" required value="<?php echo $storename; ?>" name="dokan_store_name" placeholder="store name" class="dokan-form-control" type="text">
                    </div>
                </div>

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
                    <label class="dokan-w3 dokan-control-label" for="settings[social][fb]"><?php _e( 'Social Profile', 'dokan' ); ?></label>

                    <div class="dokan-w5 dokan-text-left">
                        <div class="dokan-input-group dokan-form-group">
                            <span class="dokan-input-group-addon"><i class="fa fa-facebook-square"></i></span>
                            <input id="settings[social][fb]" value="<?php echo $fb; ?>" name="settings[social][fb]" class="dokan-form-control" placeholder="http://" type="text">
                        </div>
                        <div class="dokan-input-group dokan-form-group">
                            <span class="dokan-input-group-addon"><i class="fa fa-google-plus"></i></span>
                            <input id="settings[social][gplus]" value="<?php echo $gplus; ?>" name="settings[social][gplus]" class="dokan-form-control" placeholder="http://" type="text">
                        </div>
                        <div class="dokan-input-group dokan-form-group">
                            <span class="dokan-input-group-addon"><i class="fa fa-twitter"></i></span>
                            <input id="settings[social][twitter]" value="<?php echo $twitter; ?>" name="settings[social][twitter]" class="dokan-form-control" placeholder="http://" type="text">
                        </div>

                        <div class="dokan-input-group dokan-form-group">
                            <span class="dokan-input-group-addon"><i class="fa fa-linkedin"></i></span>
                            <input id="settings[social][linkedin]" value="<?php echo $linkedin; ?>" name="settings[social][linkedin]" class="dokan-form-control" placeholder="http://" type="text">
                        </div>

                        <div class="dokan-input-group dokan-form-group">
                            <span class="dokan-input-group-addon"><i class="fa fa-youtube"></i></span>
                            <input id="settings[social][youtube]" value="<?php echo $youtube; ?>" name="settings[social][youtube]" class="dokan-form-control" placeholder="http://" type="text">
                        </div>

                        <div class="dokan-input-group dokan-form-group">
                            <span class="dokan-input-group-addon"><i class="fa fa-instagram"></i></span>
                            <input id="settings[social][instagram]" value="<?php echo $instagram; ?>" name="settings[social][instagram]" class="dokan-form-control" placeholder="http://" type="text">
                        </div>

                        <div class="dokan-input-group dokan-form-group">
                            <span class="dokan-input-group-addon"><i class="fa fa-flickr"></i></span>
                            <input id="settings[social][flickr]" value="<?php echo $flickr; ?>" name="settings[social][flickr]" class="dokan-form-control" placeholder="http://" type="text">
                        </div>

                    </div>
                </div>

                <!-- payment tab -->
                <div class="dokan-form-group">
                    <label class="dokan-w3 dokan-control-label" for="dokan_setting"><?php _e( 'Payment Method', 'dokan' ); ?></label>
                    <div class="dokan-w6">

                        <?php $methods = dokan_withdraw_get_active_methods(); ?>
                        <div id="payment_method_tab">
                            <ul class="dokan_tabs" style="margin-bottom: 10px; margin-left:0px;">
                                <?php
                                $count = 0;
                                foreach ( $methods as $method_key ) {
                                    $method = dokan_withdraw_get_method( $method_key );
                                    ?>
                                    <li<?php echo ( $count == 0 ) ? ' class="active"' : ''; ?>><a href="#dokan-payment-<?php echo $method_key; ?>" data-toggle="tab"><?php echo $method['title']; ?></a></li>
                                    <?php
                                    $count++;
                                } ?>
                            </ul>

                            <!-- Tab panes -->
                            <div class="tabs_container">

                                <?php
                                $count = 0;
                                foreach ( $methods as $method_key ) {
                                    $method = dokan_withdraw_get_method( $method_key );
                                    ?>
                                    <div class="tab-pane<?php echo ( $count == 0 ) ? ' active': ''; ?>" id="dokan-payment-<?php echo $method_key; ?>">
                                        <?php if ( is_callable( $method['callback'] ) ) {
                                            call_user_func( $method['callback'], $profile_info );
                                        } ?>
                                    </div>
                                    <?php
                                    $count++;
                                } ?>
                            </div> <!-- .tabs_container -->
                        </div> <!-- .payment method tab -->
                    </div> <!-- .dokan-w4 -->
                </div> <!-- .dokan-form-group -->

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
                    <label class="dokan-w3 dokan-control-label" for="setting_address"><?php _e( 'Address', 'dokan' ); ?></label>
                    <div class="dokan-w5 dokan-text-left">
                        <textarea class="dokan-form-control" rows="4" id="setting_address" name="setting_address"><?php echo $address; ?></textarea>
                    </div>
                </div>

                <div class="dokan-form-group">
                    <label class="dokan-w3 dokan-control-label" for="setting_map"><?php _e( 'Map', 'dokan' ); ?></label>

                    <div class="dokan-w4 dokan-text-left">
                        <input id="dokan-map-lat" type="hidden" name="location" value="<?php echo $map_location; ?>" size="30" />

                        <div class="dokan-input-group">
                            <span class="">
                                <input id="dokan-map-add" type="text" class="dokan-form-control" value="<?php echo $map_address; ?>" name="find_address" placeholder="<?php _e( 'Type an address to find', 'dokan' ); ?>" size="30" />
                                <a href="#" class="" id="dokan-location-find-btn" type="button"><?php _e( 'Find Address', 'dokan' ); ?></a>
                            </span>
                        </div><!-- /input-group -->

                        <div class="dokan-google-map" id="dokan-map" style="width:400px; height:300px;"></div>
                    </div> <!-- col.md-4 -->
                </div> <!-- .dokan-form-group -->

                <?php do_action( 'dokan_settings_form_bottom', $current_user, $profile_info ); ?>

                <div class="dokan-form-group">

                    <div class="dokan-w4 ajax_prev dokan-text-left" style="margin-left:24%;">
                        <input type="submit" name="dokan_update_profile" class="dokan-btn dokan-btn-danger dokan-btn-theme" value="<?php esc_attr_e( 'Update Settings', 'dokan' ); ?>">
                    </div>
                </div>

            </form>

            <?php do_action( 'dokan_settings_after_form', $current_user, $profile_info ); ?>

                <script type="text/javascript">

                    (function($) {
                        $(function() {
                            <?php
                            $locations = explode( ',', $map_location );
                            $def_lat = isset( $locations[0] ) ? $locations[0] : 90.40714300000002;
                            $def_long = isset( $locations[1] ) ? $locations[1] : 23.709921;
                            ?>
                            var def_zoomval = 12;
                            var def_longval = '<?php echo $def_long; ?>';
                            var def_latval = '<?php echo $def_lat; ?>';
                            var curpoint = new google.maps.LatLng(def_latval, def_longval),
                                geocoder   = new window.google.maps.Geocoder(),
                                $map_area = $('#dokan-map'),
                                $input_area = $( '#dokan-map-lat' ),
                                $input_add = $( '#dokan-map-add' ),
                                $find_btn = $( '#dokan-location-find-btn' );

                            autoCompleteAddress();

                            $find_btn.on('click', function(e) {
                                e.preventDefault();

                                geocodeAddress( $input_add.val() );
                            });

                            var gmap = new google.maps.Map( $map_area[0], {
                                center: curpoint,
                                zoom: def_zoomval,
                                mapTypeId: window.google.maps.MapTypeId.ROADMAP
                            });

                            var marker = new window.google.maps.Marker({
                                position: curpoint,
                                map: gmap,
                                draggable: true
                            });

                            window.google.maps.event.addListener( gmap, 'click', function ( event ) {
                                marker.setPosition( event.latLng );
                                updatePositionInput( event.latLng );
                            } );

                            window.google.maps.event.addListener( marker, 'drag', function ( event ) {
                                updatePositionInput(event.latLng );
                            } );

                            function updatePositionInput( latLng ) {
                                $input_area.val( latLng.lat() + ',' + latLng.lng() );
                            }

                            function updatePositionMarker() {
                                var coord = $input_area.val(),
                                    pos, zoom;

                                if ( coord ) {
                                    pos = coord.split( ',' );
                                    marker.setPosition( new window.google.maps.LatLng( pos[0], pos[1] ) );

                                    zoom = pos.length > 2 ? parseInt( pos[2], 10 ) : 12;

                                    gmap.setCenter( marker.position );
                                    gmap.setZoom( zoom );
                                }
                            }

                            function geocodeAddress( address ) {
                                geocoder.geocode( {'address': address}, function ( results, status ) {
                                    if ( status == window.google.maps.GeocoderStatus.OK ) {
                                        updatePositionInput( results[0].geometry.location );
                                        marker.setPosition( results[0].geometry.location );
                                        gmap.setCenter( marker.position );
                                        gmap.setZoom( 15 );
                                    }
                                } );
                            }

                            function autoCompleteAddress(){
                                if (!$input_add) return null;

                                $input_add.autocomplete({
                                    source: function(request, response) {
                                        // TODO: add 'region' option, to help bias geocoder.
                                        geocoder.geocode( {'address': request.term }, function(results, status) {
                                            response(jQuery.map(results, function(item) {
                                                return {
                                                    label     : item.formatted_address,
                                                    value     : item.formatted_address,
                                                    latitude  : item.geometry.location.lat(),
                                                    longitude : item.geometry.location.lng()
                                                };
                                            }));
                                        });
                                    },
                                    select: function(event, ui) {

                                        $input_area.val(ui.item.latitude + ',' + ui.item.longitude );

                                        var location = new window.google.maps.LatLng(ui.item.latitude, ui.item.longitude);

                                        gmap.setCenter(location);
                                        // Drop the Marker
                                        setTimeout( function(){
                                            marker.setValues({
                                                position    : location,
                                                animation   : window.google.maps.Animation.DROP
                                            });
                                        }, 1500);
                                    }
                                });
                            }

                        });
                    })(jQuery);
                </script>

                <script>
                    (function($){
                        $(document).ready(function(){
                            $('#payment_method_tab').easytabs();
                        });
                    })(jQuery)
                </script>

                <script type="text/javascript">

                    jQuery(function($){
                        // $('#setting_category').chosen({
                        //     width: "95%"
                        // }).change(function() {
                        //     $("form#settings-form").validate().element("#setting_category");
                        // });
                    })

                </script>

        <?php
    }

    /**
     * Calculate Profile Completeness meta value
     *
     * @since 2.1
     *
     * @param  array  $dokan_settings
     *
     * @return array
     */
    function calculate_profile_completeness_value( $dokan_settings ) {

        $profile_val = 0;
        $next_add    = '';
        $track_val   = array();

        $progress_values = array(
           'banner_val'          => 15,
           'profile_picture_val' => 15,
           'store_name_val'      => 10,
           'social_val'          => array(
               'fb'       => 2,
               'gplus'    => 2,
               'twitter'  => 2,
               'youtube'  => 2,
               'linkedin' => 2,
           ),
           'payment_method_val'  => 15,
           'phone_val'           => 10,
           'address_val'         => 10,
           'map_val'             => 15,
        );

        // setting values for completion
        $progress_values = apply_filters('dokan_profile_completion_values', $progress_values);

        extract( $progress_values );

        //settings wise completeness section
        if( isset( $dokan_settings['gravatar'] ) ):
            if ( $dokan_settings['gravatar'] != 0 ) {
                $profile_val           = $profile_val + $profile_picture_val;
                $track_val['gravatar'] = $profile_picture_val;
            } else {
                if ( strlen( $next_add ) == 0 ) {
                    $next_add = sprintf(__( 'Add Profile Picture to gain %s%% progress', 'dokan' ), $profile_picture_val);
                }
            }
        endif;

        // Calculate Social profiles
        if( isset( $dokan_settings['social'] ) ):

            foreach ( $dokan_settings['social'] as $key => $value ) {

                if ( isset( $social_val[$key] ) && $value != false ) {
                    $profile_val     = $profile_val + $social_val[$key];
                    $track_val[$key] = $social_val[$key];
                }

                if ( isset( $social_val[$key] ) && $value == false ) {

                    if ( strlen( $next_add ) == 0 ) {
                        //replace keys to nice name
                        $nice_name = ( $key === 'fb' ) ? __( 'Facebook', 'dokan' ) : ( ( $key === 'gplus' ) ? __( 'Google+', 'dokan' ) : $key);
                        $next_add = sprintf( __( 'Add %s profile link to gain %s%% progress', 'dokan' ), $nice_name, $social_val[$key] );
                    }
                }
            }
        endif;

        //calculate completeness for phone
        if( isset( $dokan_settings['phone'] ) ):

            if ( strlen( trim( $dokan_settings['phone'] ) ) != 0 ) {
                $profile_val        = $profile_val + $phone_val;
                $track_val['phone'] = $phone_val;
            } else {
                if ( strlen( $next_add ) == 0 ) {
                    $next_add = sprintf( __( 'Add Phone to gain %s%% progress', 'dokan' ), $phone_val );
                }
            }

        endif;

        //calculate completeness for banner
        if( isset( $dokan_settings['banner'] ) ):

            if ( $dokan_settings['banner'] != 0 ) {
                $profile_val         = $profile_val + $banner_val;
                $track_val['banner'] = $banner_val;
            } else {
                $next_add = sprintf(__( 'Add Banner to gain %s%% progress', 'dokan' ), $banner_val);
            }

        endif;

        //calculate completeness for store name
        if( isset( $dokan_settings['store_name'] ) ):
            if ( isset( $dokan_settings['store_name'] ) ) {
                $profile_val             = $profile_val + $store_name_val;
                $track_val['store_name'] = $store_name_val;
            } else {
                if ( strlen( $next_add ) == 0 ) {
                    $next_add = sprintf( __( 'Add Store Name to gain %s%% progress', 'dokan' ), $store_name_val );
                }
            }
        endif;

        //calculate completeness for address
        if( isset( $dokan_settings['address'] ) ):
            if ( strlen( trim( $dokan_settings['address'] ) ) != 0 ) {
                $profile_val          = $profile_val + $address_val;
                $track_val['address'] = $address_val;
            } else {
                if ( strlen( $next_add ) == 0 ) {
                    $next_add = sprintf(__( 'Add address to gain %s%% progress', 'dokan' ),$address_val);
                }
            }
        endif;



            // Calculate Payment method val for Bank
            if ( isset( $dokan_settings['payment']['bank'] ) ) {
                $count_bank = true;

                // if any of the values for bank details are blank, check_bank will be set as false
                foreach ( $dokan_settings['payment']['bank'] as $value ) {
                    if ( strlen( trim( $value )) == 0)   {
                        $count_bank = false;
                    }
                }

                if ( $count_bank ) {
                    $profile_val        = $profile_val + $payment_method_val;
                    $track_val['Bank']  = $payment_method_val;
                    $payment_method_val = 0;
                    $payment_added      = 'true';
                }
            }

            // Calculate Payment method val for Paypal
            if ( isset( $dokan_settings['payment']['paypal'] ) ) {
                if ( $dokan_settings['payment']['paypal']['email'] != false ) {

                    $profile_val         = $profile_val + $payment_method_val;
                    $track_val['paypal'] = $payment_method_val;
                    $payment_method_val  = 0;
                }
            }

            // Calculate Payment method val for skrill
            if ( isset( $dokan_settings['payment']['skrill'] ) ) {
                if ( $dokan_settings['payment']['skrill']['email'] != false ) {

                    $profile_val         = $profile_val + $payment_method_val;
                    $track_val['skrill'] = $payment_method_val;
                    $payment_method_val  = 0;
                }
            }

            // set message if no payment method found
            if ( strlen( $next_add ) == 0 && $payment_method_val !=0 ) {
                $next_add = sprintf( __( 'Add a Payment method to gain %s%% progress', 'dokan' ), $payment_method_val );
            }

            if ( isset( $dokan_settings['location'] ) && strlen(trim($dokan_settings['location'])) != 0 ) {
                $profile_val           = $profile_val + $map_val;
                $track_val['location'] = $map_val;
            } else {
                if ( strlen( $next_add ) == 0 ) {
                    $next_add = sprintf( __( 'Add Map location to gain %s%% progress', 'dokan' ), $map_val );
                }
            }

        $track_val['next_todo'] = $next_add;
        $track_val['progress'] = $profile_val;

        return $track_val;
    }

    function get_dokan_categories() {
        $dokan_category = array(
            'book'       => __( 'Book', 'dokan' ),
            'dress'      => __( 'Dress', 'dokan' ),
            'electronic' => __( 'Electronic', 'dokan' ),
        );

        return apply_filters( 'dokan_category', $dokan_category );
    }
}
