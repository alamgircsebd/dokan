(function($){

    var Dokan_RMA = {

        init: function() {
            $( 'input#dokan_rma_product_override' ).on( 'change', this.toggleProductrmaOption );

            $( 'select#dokan-warranty-type' ).on( 'change', this.toggleTypeContent );
            $( 'select#dokan-warranty-length' ).on( 'change', this.toggleLenghtContent );

            $( 'table.dokan-rma-addon-warranty-table').on( 'click', 'a.add-item', this.addRow );
            $( 'table.dokan-rma-addon-warranty-table').on( 'click', 'a.remove-item', this.removeRow );

            $( 'form#dokan-update-request-status' ).on( 'submit', this.changeRequestStatus );

            this.initialize();


        },

        initialize: function() {
            $( 'select#dokan-warranty-type' ).trigger( 'change' );
            $( 'input#dokan_rma_product_override' ).trigger( 'change' );
        },

        addRow: function(e){
            e.preventDefault();
            var row = $(this).closest('tr').first().clone().appendTo($(this).closest('tbody'));
            row.find('input').val('');
            row.find('select').val('days');
        },

        removeRow: function(e) {
            e.preventDefault();

            if( $(this).closest('tbody').find( 'tr' ).length == 1 ){
                return;
            }

            $(this).closest('tr').remove();
        },

        toggleProductrmaOption: function(e) {
            e.preventDefault();
            var self = $(this);

            if ( self.is( ':checked' ) ) {
                $('.dokan-product-rma-option-wrapper').slideDown();
            } else {
                $('.dokan-product-rma-option-wrapper').slideUp( 300, function() {
                    $(this).hide();
                });
            }

        },

        toggleTypeContent: function(e) {
            e.preventDefault();

            var self = $(this),
                hide_classes = '.hide_if_no_warranty',
                show_classes = '.show_if_no_warranty',
                val  = self.val();

            $.each( [ 'included_warranty', 'addon_warranty' ], function( index, value ) {
                hide_classes = hide_classes + ', .hide_if_' + value;
                show_classes = show_classes + ', .show_if_' + value;
            });

            $(hide_classes).show();
            $(show_classes).hide();

            $('.show_if_' + val ).show();
            $('.hide_if_' + val ).hide();

            if ( val === 'included_warranty' ) {
                $( 'select#dokan-warranty-length' ).trigger( 'change' );
            }
        },

        toggleLenghtContent: function(e) {
            e.preventDefault();

            var self = $(this),
                hide_classes = '.hide_if_lifetime, .hide_if_limited',
                show_classes = '.show_if_lifetime, .show_if_limited',
                val = self.val();

            $(hide_classes).show();
            $(show_classes).hide();

            $('.show_if_' + val ).show();
            $('.hide_if_' + val ).hide();
        },

        changeRequestStatus: function(e) {
            e.preventDefault();

            var self = $(this),
                data = {
                    action: 'dokan-update-return-request',
                    nonce: DokanRMA.nonce,
                    formData: self.serialize()
                }

            jQuery( '.dokan-status-update-panel' ).block({ message: null, overlayCSS: { background: '#fff url(' + dokan.ajax_loader + ') no-repeat center', opacity: 0.6 } });

            $.post( DokanRMA.ajaxurl, data, function(resp){
                if ( resp.success ) {
                    jQuery( '.dokan-status-update-panel' ).unblock();
                } else {
                    alert( resp.data );
                }
            });
        }

    }

    $(document).ready(function(){
        Dokan_RMA.init();
    });

})(jQuery);
