;(function($) {

var wrapper = $( '.dps-pack-wrappper' );
var Dokan_Subscription_details = {

    init : function() {
        wrapper.on( 'change', 'select#dokan-subscription-pack', this.show_details );
        this.show_details();
        this.cancel();
    },
    show_details : function(){
        id = $( 'select#dokan-subscription-pack' ).val();
        $('.dps-pack').hide();
        $('.dps-pack-'+id).show();
    },
    cancel: function() {
        $( '.seller_subs_info input[type="submit"]' ).on( 'click', function( e ) {
            var confirm = window.confirm( dokanSubscription.cancel_string );

            if ( ! confirm ) {
                e.preventDefault();
            }
        } );
    }


};

$(function() {
    Dokan_Subscription_details.init();
});

})(jQuery);
