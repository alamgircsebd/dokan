<?php
/**
 * Dokan Dashboard Settings Store Form Template
 *
 * @since 2.4
 */
?>
<?php

    $gravatar   = isset( $profile_info['gravatar'] ) ? absint( $profile_info['gravatar'] ) : 0;
    $banner     = isset( $profile_info['banner'] ) ? absint( $profile_info['banner'] ) : 0;
    $storename  = isset( $profile_info['store_name'] ) ? esc_attr( $profile_info['store_name'] ) : '';
    $store_ppp  = isset( $profile_info['store_ppp'] ) ? esc_attr( $profile_info['store_ppp'] ) : '';
    $phone      = isset( $profile_info['phone'] ) ? esc_attr( $profile_info['phone'] ) : '';
    $show_email = isset( $profile_info['show_email'] ) ? esc_attr( $profile_info['show_email'] ) : 'no';
    $show_more_ptab = isset( $profile_info['show_more_ptab'] ) ? esc_attr( $profile_info['show_more_ptab'] ) : 'yes';

    $is_enable_op_discount = dokan_get_option( 'discount_edit', 'dokan_selling' );
    $is_enable_op_discount = $is_enable_op_discount ? $is_enable_op_discount : array();

    $is_enable_order_discount = isset( $profile_info['show_min_order_discount'] ) ? $profile_info['show_min_order_discount'] : 'no';
    $setting_minimum_order_amount = isset( $profile_info['setting_minimum_order_amount'] ) ? $profile_info['setting_minimum_order_amount'] : '';
    $setting_order_percentage = isset( $profile_info['setting_order_percentage'] ) ? $profile_info['setting_order_percentage'] : '';

    $address         = isset( $profile_info['address'] ) ? $profile_info['address'] : '';
    $address_street1 = isset( $profile_info['address']['street_1'] ) ? $profile_info['address']['street_1'] : '';
    $address_street2 = isset( $profile_info['address']['street_2'] ) ? $profile_info['address']['street_2'] : '';
    $address_city    = isset( $profile_info['address']['city'] ) ? $profile_info['address']['city'] : '';
    $address_zip     = isset( $profile_info['address']['zip'] ) ? $profile_info['address']['zip'] : '';
    $address_country = isset( $profile_info['address']['country'] ) ? $profile_info['address']['country'] : '';
    $address_state   = isset( $profile_info['address']['state'] ) ? $profile_info['address']['state'] : '';

    $map_location   = isset( $profile_info['location'] ) ? esc_attr( $profile_info['location'] ) : '';
    $map_address    = isset( $profile_info['find_address'] ) ? esc_attr( $profile_info['find_address'] ) : '';
    $dokan_category = isset( $profile_info['dokan_category'] ) ? $profile_info['dokan_category'] : '';
    $enable_tnc     = isset( $profile_info['enable_tnc'] ) ? $profile_info['enable_tnc'] : '';
    $store_tnc      = isset( $profile_info['store_tnc'] ) ? $profile_info['store_tnc'] : '' ;

    if ( is_wp_error( $validate ) ) {
        $storename    = $_POST['dokan_store_name'];
        $map_location = $_POST['location'];
        $map_address  = $_POST['find_address'];

        $address_street1 = $_POST['dokan_address']['street_1'];
        $address_street2 = $_POST['dokan_address']['street_2'];
        $address_city    = $_POST['dokan_address']['city'];
        $address_zip     = $_POST['dokan_address']['zip'];
        $address_country = $_POST['dokan_address']['country'];
        $address_state   = $_POST['dokan_address']['state'];
    }

    $dokan_appearance = get_option( 'dokan_appearance' );

?>
<?php do_action( 'dokan_settings_before_form', $current_user, $profile_info ); ?>

    <form method="post" id="store-form"  action="" class="dokan-form-horizontal">

        <?php wp_nonce_field( 'dokan_store_settings_nonce' ); ?>

        <?php if ( ! empty( $dokan_appearance['store_header_template'] ) && 'layout3' !== $dokan_appearance['store_header_template'] ): ?>
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
                    <p class="help-block">
                        <?php
                        /**
                         * Filter `dokan_banner_upload_help`
                         *
                         * @since 2.4.10
                         */
                        $general_settings = get_option( 'dokan_general', [] );
                        $banner_width = ! empty( $general_settings['store_banner_width'] ) ? $general_settings['store_banner_width'] : 625;
                        $banner_height = ! empty( $general_settings['store_banner_height'] ) ? $general_settings['store_banner_height'] : 300;

                        $help_text = sprintf(
                            __('Upload a banner for your store. Banner size is (%sx%s) pixels.', 'dokan' ),
                            $banner_width, $banner_height
                        );

                        echo apply_filters( 'dokan_banner_upload_help', $help_text );
                        ?>
                    </p>
                </div>
            </div> <!-- .dokan-banner -->

            <?php do_action( 'dokan_settings_after_banner', $current_user, $profile_info ); ?>
        <?php endif; ?>

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
                    <a href="#" class="dokan-pro-gravatar-drag dokan-btn dokan-btn-default"><i class="fa fa-cloud-upload"></i> <?php _e( 'Upload Photo', 'dokan' ); ?></a>
                </div>
            </div>
        </div>

        <div class="dokan-form-group">
            <label class="dokan-w3 dokan-control-label" for="dokan_store_name"><?php _e( 'Store Name', 'dokan' ); ?></label>

            <div class="dokan-w5 dokan-text-left">
                <input id="dokan_store_name" required value="<?php echo $storename; ?>" name="dokan_store_name" placeholder="<?php _e( 'store name', 'dokan'); ?>" class="dokan-form-control" type="text">
            </div>
        </div>

        <div class="dokan-form-group">
            <label class="dokan-w3 dokan-control-label" for="dokan_store_ppp"><?php _e( 'Store Product Per Page', 'dokan' ); ?></label>

            <div class="dokan-w5 dokan-text-left">
                <input id="dokan_store_ppp" value="<?php echo $store_ppp; ?>" name="dokan_store_ppp" placeholder="10" class="dokan-form-control" type="number">
            </div>
        </div>
         <!--address-->

        <?php
        $verified = false;

        if ( isset( $profile_info['dokan_verification']['info']['store_address']['v_status'] ) ) {
            if ( $profile_info['dokan_verification']['info']['store_address']['v_status'] == 'approved' ){
                $verified = true;
            }
        }

        dokan_seller_address_fields( $verified );

        ?>
        <!--address-->

        <div class="dokan-form-group">
            <label class="dokan-w3 dokan-control-label" for="setting_phone"><?php _e( 'Phone No', 'dokan' ); ?></label>
            <div class="dokan-w5 dokan-text-left">
                <input id="setting_phone" value="<?php echo $phone; ?>" name="setting_phone" placeholder="<?php _e( '+123456..', 'dokan' ); ?>" class="dokan-form-control input-md" type="text">
            </div>
        </div>

        <?php if ( ! is_int( key( $is_enable_op_discount ) ) && array_key_exists("order-discount", $is_enable_op_discount ) == "order-discount" ) : ?>
            <div class="dokan-form-group">
                <label class="dokan-w3 dokan-control-label"><?php _e( 'Discount ', 'dokan' ); ?></label>
                <div class="dokan-w5 dokan-text-left">
                    <div class="checkbox">
                        <label class="dokan-control-label" for="lbl_setting_minimum_quantity">
                            <input type="hidden" name="setting_show_minimum_discount_option" value="no">
                            <input id="lbl_setting_minimum_quantity" type="checkbox" name="setting_show_minimum_order_discount_option" value="yes"<?php checked( $is_enable_order_discount, 'yes' ); ?>>
                            <?php _e( 'Enable storewide discount', 'dokan' ); ?>
                        </label>
                    </div>
                    <div class="show_if_needs_sw_discount <?php echo ($is_enable_order_discount=='yes') ? '' : 'hide_if_order_discount' ;?>">
                        <div class="dokan-text-left dokan-form-group">
                            <input id="setting_minimum_order_amount" value="<?php echo $setting_minimum_order_amount; ?>" name="setting_minimum_order_amount" placeholder="<?php _e( 'Minimum Order Amount', 'dokan' ); ?>" class="dokan-form-control input-md" type="number">
                        </div>
                        <div class="dokan-text-left dokan-form-group">
                            <input id="setting_order_percentage" value="<?php echo $setting_order_percentage; ?>" name="setting_order_percentage" placeholder="<?php _e( 'Percentage', 'dokan' ); ?>" class="dokan-form-control input-md" type="number" min="1" max="100">
                        </div>
                    </div>
                </div>
            </div>
        <?php endif;?>

        <div class="dokan-form-group">
            <label class="dokan-w3 dokan-control-label"><?php _e( 'Email', 'dokan' ); ?></label>
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
            <label class="dokan-w3 dokan-control-label"><?php _e( 'More product', 'dokan' ); ?></label>
            <div class="dokan-w5 dokan-text-left">
                <div class="checkbox">
                    <label>
                        <input type="hidden" name="setting_show_more_ptab" value="no">
                        <input type="checkbox" name="setting_show_more_ptab" value="yes"<?php checked( $show_email, 'yes' ); ?>> <?php _e( 'Enable tab on product single page view', 'dokan' ); ?>
                    </label>
                </div>
            </div>
        </div>


        <div class="dokan-form-group">
            <label class="dokan-w3 dokan-control-label" for="setting_map"><?php _e( 'Map', 'dokan' ); ?></label>

            <div class="dokan-w6 dokan-text-left">
                <input id="dokan-map-lat" type="hidden" name="location" value="<?php echo $map_location; ?>" size="30" />

                <div class="dokan-map-wrap">
                    <div class="dokan-map-search-bar">
                        <input id="dokan-map-add" type="text" class="dokan-map-search" value="<?php echo $map_address; ?>" name="find_address" placeholder="<?php _e( 'Type an address to find', 'dokan' ); ?>" size="30" />
                        <a href="#" class="dokan-map-find-btn" id="dokan-location-find-btn" type="button"><?php _e( 'Find Address', 'dokan' ); ?></a>
                    </div>

                    <div class="dokan-google-map" id="dokan-map"></div>
                </div>
            </div> <!-- col.md-4 -->
        </div> <!-- .dokan-form-group -->

        <!--terms and conditions enable or not -->
        <?php
        $tnc_enable = dokan_get_option( 'seller_enable_terms_and_conditions', 'dokan_general', 'off' );
        if ( $tnc_enable == 'on' ) :
            ?>
            <div class="dokan-form-group">
                <label class="dokan-w3 dokan-control-label"><?php _e( 'Terms and Conditions', 'dokan' ); ?></label>
                <div class="dokan-w5 dokan-text-left dokan_tock_check">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="dokan_store_tnc_enable" value="on" <?php echo $enable_tnc == 'on' ? 'checked':'' ; ?> name="dokan_store_tnc_enable" ><?php _e( 'Show terms and conditions in store page', 'dokan' ); ?>
                        </label>
                    </div>
                </div>
            </div>
            <div class="dokan-form-group" id="dokan_tnc_text">
                <label class="dokan-w3 dokan-control-label" for="dokan_store_tnc"><?php _e( 'TOC Details', 'dokan' ); ?></label>
                <div class="dokan-w8 dokan-text-left">
                    <?php
                        $settings = array(
                            'editor_height' => 200,
                            'media_buttons' => false,
                            'teeny'         => true,
                            'quicktags'     => false
                        );
                        wp_editor( $store_tnc, 'dokan_store_tnc', $settings );
                    ?>
                </div>
            </div>

        <?php endif;?>


        <?php do_action( 'dokan_settings_form_bottom', $current_user, $profile_info ); ?>

        <div class="dokan-form-group">

            <div class="dokan-w4 ajax_prev dokan-text-left" style="margin-left:24%;">
                <input type="submit" name="dokan_update_store_settings" class="dokan-btn dokan-btn-danger dokan-btn-theme" value="<?php esc_attr_e( 'Update Settings', 'dokan' ); ?>">
            </div>
        </div>
    </form>

    <?php do_action( 'dokan_settings_after_form', $current_user, $profile_info ); ?>

<script type="text/javascript">

    (function($) {
        var dokan_address_wrapper = $( '.dokan-address-fields' );
        var dokan_address_select = {
            init: function () {

                dokan_address_wrapper.on( 'change', 'select.country_to_state', this.state_select );
            },
            state_select: function () {
                var states_json = wc_country_select_params.countries.replace( /&quot;/g, '"' ),
                    states = $.parseJSON( states_json ),
                    $statebox = $( '#dokan_address_state' ),
                    input_name = $statebox.attr( 'name' ),
                    input_id = $statebox.attr( 'id' ),
                    input_class = $statebox.attr( 'class' ),
                    value = $statebox.val(),
                    selected_state = '<?php echo $address_state; ?>',
                    input_selected_state = '<?php echo $address_state; ?>',
                    country = $( this ).val();

                if ( states[ country ] ) {

                    if ( $.isEmptyObject( states[ country ] ) ) {

                        $( 'div#dokan-states-box' ).slideUp( 2 );
                        if ( $statebox.is( 'select' ) ) {
                            $( 'select#dokan_address_state' ).replaceWith( '<input type="text" class="' + input_class + '" name="' + input_name + '" id="' + input_id + '" required />' );
                        }

                        $( '#dokan_address_state' ).val( 'N/A' );

                    } else {
                        input_selected_state = '';

                        var options = '',
                            state = states[ country ];

                        for ( var index in state ) {
                            if ( state.hasOwnProperty( index ) ) {
                                if ( selected_state ) {
                                    if ( selected_state == index ) {
                                        var selected_value = 'selected="selected"';
                                    } else {
                                        var selected_value = '';
                                    }
                                }
                                options = options + '<option value="' + index + '"' + selected_value + '>' + state[ index ] + '</option>';
                            }
                        }

                        if ( $statebox.is( 'select' ) ) {
                            $( 'select#dokan_address_state' ).html( '<option value="">' + wc_country_select_params.i18n_select_state_text + '</option>' + options );
                        }
                        if ( $statebox.is( 'input' ) ) {
                            $( 'input#dokan_address_state' ).replaceWith( '<select type="text" class="' + input_class + '" name="' + input_name + '" id="' + input_id + '" required ></select>' );
                            $( 'select#dokan_address_state' ).html( '<option value="">' + wc_country_select_params.i18n_select_state_text + '</option>' + options );
                        }
                        $( '#dokan_address_state' ).removeClass( 'dokan-hide' );
                        $( 'div#dokan-states-box' ).slideDown();

                    }
                } else {


                    if ( $statebox.is( 'select' ) ) {
                        input_selected_state = '';
                        $( 'select#dokan_address_state' ).replaceWith( '<input type="text" class="' + input_class + '" name="' + input_name + '" id="' + input_id + '" required="required"/>' );
                    }
                    $( '#dokan_address_state' ).val(input_selected_state);

                    if ( $( '#dokan_address_state' ).val() == 'N/A' ){
                        $( '#dokan_address_state' ).val('');
                    }
                    $( '#dokan_address_state' ).removeClass( 'dokan-hide' );
                    $( 'div#dokan-states-box' ).slideDown();
                }
            }
        }

        $(function() {
            dokan_address_select.init();

            $('#setting_phone').keydown(function(e) {
                // Allow: backspace, delete, tab, escape, enter and .
                if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 91, 107, 109, 110, 187, 189, 190]) !== -1 ||
                     // Allow: Ctrl+A
                    (e.keyCode == 65 && e.ctrlKey === true) ||
                     // Allow: home, end, left, right
                    (e.keyCode >= 35 && e.keyCode <= 39)) {
                         // let it happen, don't do anything
                    return;
                }

                // Ensure that it is a number and stop the keypress
                if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                    e.preventDefault();
                }
            });
            <?php
            $locations = explode( ',', $map_location );
            $def_lat = isset( $locations[0] ) ? $locations[0] : 90.40714300000002;
            $def_long = isset( $locations[1] ) ? $locations[1] : 23.709921;
            ?>
            var def_zoomval = 12;
            var def_longval = '<?php echo $def_long; ?>';
            var def_latval = '<?php echo $def_lat; ?>';

            try {
                var curpoint = new google.maps.LatLng(def_latval, def_longval),
                    geocoder   = new window.google.maps.Geocoder(),
                    $map_area = $('#dokan-map'),
                    $input_area = $( '#dokan-map-lat' ),
                    $input_add = $( '#dokan-map-add' ),
                    $find_btn = $( '#dokan-location-find-btn' );

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

            } catch( e ) {
                console.log( 'Google API not found.' );
            }

            autoCompleteAddress();

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
