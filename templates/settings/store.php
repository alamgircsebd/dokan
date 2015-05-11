<?php
$dokan_template_settings = Dokan_Template_Settings::init();
$validate                = $dokan_template_settings->validate();

if ( $validate !== false && !is_wp_error( $validate ) ) {
   $dokan_template_settings->insert_settings_info();
}

$scheme = is_ssl() ? 'https' : 'http';
wp_enqueue_script( 'google-maps', $scheme . '://maps.google.com/maps/api/js?sensor=true' );
?>

<div class="dokan-dashboard-wrap">
    <?php dokan_get_template( 'dashboard-nav.php', array( 'active_menu' => 'settings/store' ) ); ?>

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
            $banner         = isset( $profile_info['banner'] ) ? absint( $profile_info['banner'] ) : 0;
            $storename      = isset( $profile_info['store_name'] ) ? esc_attr( $profile_info['store_name'] ) : '';
            $phone          = isset( $profile_info['phone'] ) ? esc_attr( $profile_info['phone'] ) : '';
            $show_email     = isset( $profile_info['show_email'] ) ? esc_attr( $profile_info['show_email'] ) : 'no';

            // bank
            $address        = isset( $profile_info['address'] ) ? esc_textarea( $profile_info['address'] ) : '';
            $map_location   = isset( $profile_info['location'] ) ? esc_attr( $profile_info['location'] ) : '';
            $map_address    = isset( $profile_info['find_address'] ) ? esc_attr( $profile_info['find_address'] ) : '';
            $dokan_category = isset( $profile_info['dokan_category'] ) ? $profile_info['dokan_category'] : '';


            if ( is_wp_error( $validate ) ) {
                $storename    = $_POST['dokan_store_name'];

                $address      = $_POST['setting_address'];
                $map_location = $_POST['location'];
                $map_address  = $_POST['find_address'];
            }
            ?>

            <div class="dokan-ajax-response">
                <?php echo dokan_get_profile_progressbar(); ?>
            </div>

            <?php do_action( 'dokan_settings_before_form', $current_user, $profile_info ); ?>

            <form method="post" id="store-form"  action="" class="dokan-form-horizontal">

                <?php wp_nonce_field( 'dokan_store_settings_nonce' ); ?>

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
                    <label class="dokan-w3 dokan-control-label" for="dokan_store_name"><?php _e( 'Store Name', 'dokan' ); ?></label>

                    <div class="dokan-w5 dokan-text-left">
                        <input id="dokan_store_name" required value="<?php echo $storename; ?>" name="dokan_store_name" placeholder="store name" class="dokan-form-control" type="text">
                    </div>
                </div>

                <div class="dokan-form-group">
                    <label class="dokan-w3 dokan-control-label" for="setting_address"><?php _e( 'Address', 'dokan' ); ?></label>
                    <div class="dokan-w5 dokan-text-left">
                        <textarea class="dokan-form-control" rows="4" id="setting_address" name="setting_address"><?php echo $address; ?></textarea>
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

            <!--settings updated content ends-->
        </article>
    </div><!-- .dokan-dashboard-content -->
</div><!-- .dokan-dashboard-wrap -->