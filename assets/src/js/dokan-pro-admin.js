;(function($){

    var Dokan_Admin = {

        init: function() {
            $('.dokan-modules').on( 'change', 'input.dokan-toggle-module', this.toggleModule );

            $( 'body' ).on( 'click', '.shipment-item-details-tab-toggle', function() {
                var shipment_id = $(this).data( 'shipment_id' );

                $('.shipment_body_' + shipment_id).toggle();
                $('.shipment_footer_' + shipment_id).toggle();
                $('.shipment_notes_area_' + shipment_id).toggle();

                $(this).find('span').toggleClass( 'dashicons-arrow-down-alt2 dashicons-arrow-up-alt2' );
                
                return false;
            });

            $('body').on('click', '.shipment-notes-details-tab-toggle', function(e) {
                e.preventDefault();

                var shipment_id = $(this).data( 'shipment_id' );

                $(".shipment-list-notes-inner-area" + shipment_id).toggle();
                
                $(this).find('span').toggleClass( 'dashicons-arrow-down-alt2 dashicons-arrow-up-alt2' );
            });
        },

        toggleModule: function(e) {
            var self = $(this);

            if ( self.is(':checked') ) {
                // Enabled
                var mesg = dokan_admin.activating,
                    data = {
                        action: 'dokan-toggle-module',
                        type: 'activate',
                        module: self.closest( 'li' ).data( 'module' ),
                        nonce: dokan_admin.nonce
                    };
            } else {
                // Disbaled
                var mesg = dokan_admin.deactivating,
                    data = {
                        action: 'dokan-toggle-module',
                        type: 'deactivate',
                        module: self.closest( 'li' ).data( 'module' ),
                        nonce: dokan_admin.nonce
                    };
            }

            self.closest('.plugin-card').block({
                message: mesg,
                overlayCSS: { background: '#222', opacity: 0.7 },
                css: {
                    fontSize: '19px',
                    color:      '#fff',
                    border:     'none',
                    backgroundColor:'none',
                    cursor:     'wait'
                },
            });

            wp.ajax.send( 'dokan-toggle-module', {
                data: data,
                success: function(response) {

                },

                error: function(error) {
                    if ( error.error === 'plugin-exists' ) {
                        wp.ajax.send( 'dokan-toggle-module', {
                            data: data
                        });
                    }
                },

                complete: function(resp) {
                    $('.blockMsg').text(resp.data);
                    setTimeout( function() {
                        self.closest('.plugin-card').unblock();
                    }, 1000)
                }
            });
        }
    };

    $(document).ready(function(){
        Dokan_Admin.init();

        $( '.dokan-email-verification-germanized-notice' ).on( 'click', '.notice-dismiss', function( event, el ) {
            var $notice       = $(this).parent('.notice.is-dismissible');
            var dismiss_nonce = $notice.attr('data-dismiss-nonce');
            var data          = {
                action : "woocommerce_germanized_double_opt_in_ajax",
                opt_in_security  : dismiss_nonce
            };

            $.post( ajaxurl, data );

        });

    });
})(jQuery);
