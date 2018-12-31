;(function($){

    var DokanWholesale = {

        init: function() {
            $('body').on( 'click', 'a#dokan-become-wholesale-customer-btn', this.makeWholesaleCustomer )
        },

        makeWholesaleCustomer: function(e) {
            e.preventDefault();

            var self = $(this),
                url = dokan.rest.root + dokan.rest.version + '/wholesale/register',
                data = {
                    id : self.data('id')
                };

            jQuery( '.dokan-wholesale-migration-wrapper' ).block({ message: null, overlayCSS: { background: '#fff url(' + dokan.ajax_loader + ') no-repeat center', opacity: 0.6 } });

            $.post( url, data, function( resp ) {
                if ( resp.wholesale_status == 'active' ) {
                    self.closest('li').html( '<div class="woocommerce-message" style="margin-bottom:0px">' + dokan.wholesale.activeStatusMessage + '</div>' );
                } else {
                    self.closest('li').html( '<div class="woocommerce-info" style="margin-bottom:0px">' + dokan.wholesale.deactiveStatusMessage + '</div>' );
                }
                jQuery( '.dokan-wholesale-migration-wrapper' ).unblock();
            } );
        }
    };

    $(document).ready(function(){
        DokanWholesale.init();
    });

})(jQuery);
