;(function($) {
    $('.pack_content_wrapper').on('click','.buy_product_pack', function(evt) {
        url = $(this).attr('href');
    });

    var wrapper = $( '.dps-pack-wrappper' );
    var Dokan_Subscription_details = {
        init : function() {
            wrapper.on( 'change', 'select#dokan-subscription-pack', this.show_details );
            this.show_details();
            this.cancel();
            this.activate();
        },
        show_details : function(){
            id = $( 'select#dokan-subscription-pack' ).val();
            $('.dps-pack').hide();
            $('.dps-pack-'+id).show();
        },
        cancel: function() {
            $( '.seller_subs_info input[name="dps_cancel_subscription"]' ).on( 'click', function( e ) {
                var confirm = window.confirm( dokanSubscription.cancel_string );

                if ( ! confirm ) {
                    e.preventDefault();
                }
            } );
        },
        activate: function() {
            $( '.seller_subs_info input[name="dps_activate_subscription"]' ).on( 'click', function( e ) {
                var confirm = window.confirm( dokanSubscription.activate_string );

                if ( ! confirm ) {
                    e.preventDefault();
                }
            } );
        },
    };

    $(function() {
        Dokan_Subscription_details.init();
    });
})(jQuery);