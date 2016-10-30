;(function($){

    var Dokan_Product_Variation = {

        init: function() {
            // this.load_variations();
        },

        block: function() {
            $( '.dokan-product-variation-wrapper' ).block({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });
        },

        unblock: function() {
            $( '.dokan-product-variation-wrapper' ).unblock();
        },

        load_variations: function( page, per_page ) {
            page     = page || 1;
            per_page = per_page || dokan.variations_per_page;

            var wrapper = $( '#dokan-variable-product-options' ).find( '.dokan-variations-container' );

            Dokan_Product_Variation.block();

            $.ajax({
                url: dokan.ajaxurl,
                data: {
                    action:     'dokan_load_variations',
                    security:   dokan.load_variations_nonce,
                    product_id: $('#dokan-edit-product-id').val(),
                    attributes: wrapper.data( 'attributes' ),
                    page:       page,
                    per_page:   per_page
                },
                type: 'POST',
                success: function( response ) {
                    console.log( response );
                    wrapper.empty().append( response ).attr( 'data-page', page );

                    $( '.dokan-product-variation-wrapper' ).trigger( 'dokan_variations_loaded' );

                    Dokan_Product_Variation.unblock();
                }
            });
        },

    }

    // On DOM ready
    $(function() {
        if ( $( '#dokan-variable-product-options' ).length ) {
            Dokan_Product_Variation.init();
        }
    });

})(jQuery);