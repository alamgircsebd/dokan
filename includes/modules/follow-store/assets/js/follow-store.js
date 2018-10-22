(function($) {
    $( '.dokan-follow-store-button', 'body' ).on( 'click', function (e) {
        e.preventDefault();

        var button = $( this ),
            vendor_id = parseInt( button.data( 'vendor-id' ) );

        button.toggleClass( 'dokan-follow-store-button-working' );

        $.ajax( {
            url: dokan.ajaxurl,
            method: 'post',
            dataType: 'json',
            data: {
                action: 'dokan_follow_store_toggle_status',
                _nonce: dokanFollowStore._nonce,
                vendor_id: vendor_id
            }

        } ).fail( function ( e ) {
            var response = e.responseJSON.data.pop();

            alert(response.message);

        } ).always( function () {
            button.toggleClass( 'dokan-follow-store-button-working' );

        } ).done( function ( response ) {
            if ( response.data && response.data.status ) {
                if ( response.data.status === 'following' ) {
                    button
                        .attr( 'data-status', 'following' )
                        .children( '.dokan-follow-store-button-label-current' )
                        .html( dokanFollowStore.button_labels.following );
                } else {
                    button
                        .attr( 'data-status', '' )
                        .children( '.dokan-follow-store-button-label-current' )
                        .html( dokanFollowStore.button_labels.follow );
                }
            }
        } );
    } );
})(jQuery);
