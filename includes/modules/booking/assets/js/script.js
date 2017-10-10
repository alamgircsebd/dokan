    ;
    ( function ( $ ) {



    } )( jQuery );
// Remove a person type
    jQuery( '#bookings_persons' ).on( 'click', 'button.remove_booking_person', function ( e ) {
        e.preventDefault();
        var answer = confirm( wc_bookings_writepanel_js_params.i18n_remove_person );
        if ( answer ) {

            var el = jQuery( this ).parent().parent();

            var person = jQuery( this ).attr( 'rel' );

            if ( person > 0 ) {

                jQuery( el ).block( { message: null } );

                var data = {
                    action: 'woocommerce_remove_bookable_person',
                    person_id: person,
                    security: wc_bookings_writepanel_js_params.nonce_delete_person
                };

                jQuery.post( wc_bookings_writepanel_js_params.ajax_url, data, function ( response ) {
                    jQuery( el ).fadeOut( '300', function () {
                        jQuery( el ).remove();
                    } );
                } );

            } else {
                jQuery( el ).fadeOut( '300', function () {
                    jQuery( el ).remove();
                } );
            }

        }
        return false;
    } );