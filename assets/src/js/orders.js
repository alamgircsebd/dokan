;(function($) {
    /**
     * Order Shipping Status Tracking Panel
     */
    var dokan_shipping_status_tracking_order_items = {
        init: function() {

            let formatMap = {
                // Day
                d: 'dd',
                D: 'D',
                j: 'd',
                l: 'DD',

                // Month
                F: 'MM',
                m: 'mm',
                M: 'M',
                n: 'm',

                // Year
                o: 'yy', // not exactly same. see php date doc for details
                Y: 'yy',
                y: 'y'
            }

            let i = 0;
            let char = '';
            let datepickerFormat = '';

            for (i = 0; i < dokan.i18n_date_format.length; i++) {
                char = dokan.i18n_date_format[i];

                if (char in formatMap) {
                    datepickerFormat += formatMap[char];
                } else {
                    datepickerFormat += char;
                }
            }

            $( ".shipped_status_date" ).datepicker({
                dateFormat: datepickerFormat,
                minDate: 0,
            });

            $('body').on('change', '#shipping_status_provider', function(e) {
                e.preventDefault();

                var shipping_provider = $(this).val();

                if ( shipping_provider == 'sp-other' ) {
                    $(".tracking-status-other-url").show();
                } else {
                    $(".tracking-status-other-url").hide();
                }
            });

            $('body').on('change', '.update_shipping_provider', function(e) {
                e.preventDefault();

                var shipment_id = $(this).data('shipment_id');

                if ( $(this).val() == 'sp-other' ) {
                    $(".tracking-status-other-area-" + shipment_id).show();
                } else {
                    $(".tracking-status-other-area-" + shipment_id).hide();
                }
            });

            $('body').on('click', '#create-tracking-status-action', function(e) {
                e.preventDefault();

                $(this).hide();
                $("#dokan-order-shipping-status-tracking").show();
                $(".no-shipment-found-desc").hide();
            });

            $('body').on('click', '#cancel-tracking-status-details', function(e) {
                e.preventDefault();

                $("#dokan-order-shipping-status-tracking").hide();
                $("#create-tracking-status-action").show();
                $(".no-shipment-found-desc").show();
            });

            $('body').on('click', '.shipment-notes-details-tab-toggle', function(e) {
                e.preventDefault();

                var shipment_id = $(this).data( 'shipment_id' );

                $(".shipment-list-notes-inner-area" + shipment_id).toggle();
                
                $(this).find('span').toggleClass( 'dashicons-arrow-down-alt2 dashicons-arrow-up-alt2' );
            });

            $( 'body' ).on( 'click', '.shipment-item-details-tab-toggle', function() {
                var shipment_id = $(this).data( 'shipment_id' );

                $('.shipment_body_' + shipment_id).toggle();
                $('.shipment_footer_' + shipment_id).toggle();

                $(this).find('span').toggleClass( 'dashicons-arrow-down-alt2 dashicons-arrow-up-alt2' );
                
                return false;
            });

            $( 'body' ).on( 'click', '.shipment_order_item_select', function() {
                var order_item_id = $(this).data( 'order_item_id' );

                $('.shipping_order_item_qty_' + order_item_id).toggle();
            });

            //saving shipping status tracking info
            $( 'body' ).on('click','#add-tracking-status-details', this.insertShippingStatusTrackingInfo);
            $( 'body' ).on('click','#update-tracking-status-details', this.updateShippingStatusTrackingInfo);
        },

        insertShippingStatusTrackingInfo: function(e){
            e.preventDefault();

            $('#add-tracking-status-details').prop('disabled', true);
            $('#shipment-update-response-area').html('');

            // Get line item shippments
            var item_qty = {};
            var item_id  = {};

            $( '.shipping-tracking input.shipping_order_item_qty' ).each(function( index, item ) {
                if ( $( item ).closest( 'tr' ).data( 'shipping_order_item_id' ) ) {
                    var order_item_id = $( item ).closest( 'tr' ).data( 'shipping_order_item_id' );

                    if ( item.value && $('#shipment_order_item_select_' + order_item_id).is(":checked") ) {
                        item_qty[ order_item_id ] = item.value;
                    }
                }
            });

            item_id = $('input[name="shipping_order_item_id[]"]').map(function(){ 
                if ( this.value && $('#shipment_order_item_select_' + this.value).is(":checked") ) {
                    return this.value; 
                }
            }).get();

            var shipping_tracking_info = {
                shipping_provider: $('#shipping_status_provider').val(),
                shipping_number:   $('#tracking_status_number').val(),
                shipped_date:      $('#shipped_status_date').val(),
                shipped_status:    $('#shipping_status').val(),
                is_notify:         $('#shipped_status_is_notify').is(":checked") ? $('#shipped_status_is_notify').val() : 'off',
                action:            $('#action').val(),
                post_id:           $('#post-id').val(),
                security:          $('#security').val(),
                other_provider:    $('#tracking_status_other_provider').val(),
                other_p_url:       $('#tracking_status_other_p_url').val(),
                shipment_comments: $('#tracking_status_comments').val(),
                item_id:           JSON.stringify( item_id, null, '' ),
                item_qty:          JSON.stringify( item_qty, null, '' ),
            };

            $('#dokan-order-shipping-status-tracking').block({ message: null, overlayCSS: { background: '#fff url(' + dokan.ajax_loader + ') no-repeat center', opacity: 0.6 } });

            $.post( dokan.ajaxurl, shipping_tracking_info, function(response) {
                if ( response ) {
                    $('#shipment-update-response-area').html( response );
                } else {
                    $('#shipment-update-response-area').html( dokan.shipment_status_error_msg );
                }
                
                $('#dokan-order-shipping-status-tracking').unblock();

                if ( response ) {
                    location.reload();
                }

                $('#add-tracking-status-details').prop('disabled', false);
            });

            return false;
        },

        updateShippingStatusTrackingInfo: function(e){
            e.preventDefault();

            var shipment_id = $(this).data( 'shipment_id' );

            $('#shipment-update-response-area_' + shipment_id).html( '' );

            var shipping_tracking_info = {
                shipped_status:         $('#update_shipping_status_' + shipment_id).val(),
                shipping_provider:      $('#update_shipping_provider_' + shipment_id).val(),
                shipped_status_date:    $('#shipped_status_date_' + shipment_id).val(),
                tracking_status_number: $('#tracking_status_number_' + shipment_id).val(),
                status_other_provider:  $('#tracking_status_other_provider_' + shipment_id).val(),
                status_other_p_url:     $('#tracking_status_other_p_url_' + shipment_id).val(),
                shipment_comments:      $('#tracking_status_comments_' + shipment_id).val(),
                is_notify:              $('#shipment_update_is_notify_' + shipment_id).is(":checked") ? $('#shipment_update_is_notify_' + shipment_id).val() : 'off',
                action:                 'dokan_update_shipping_status_tracking_info',
                shipment_id:            shipment_id,
                post_id:                $('#post-id').val(),
                security:               $('#security_update').val(),
                security:               $('#security_update').val(),
            };

            $('.shipment_id_' + shipment_id ).block({ message: null, overlayCSS: { background: '#fff url(' + dokan.ajax_loader + ') no-repeat center', opacity: 0.6 } });

            $.post( dokan.ajaxurl, shipping_tracking_info, function(response) {
                $('.shipment_id_' + shipment_id).unblock();
                if ( response ) {
                    $('.status_label_' + shipment_id).html(response);
                    $('#tracking_status_comments_' + shipment_id).val('');
                    $('#shipment-update-response-area_' + shipment_id).html( dokan.shipment_status_update_msg );

                    setTimeout(function(){ 
                        $('#shipment-update-response-area_' + shipment_id).html( '' ); 
                    }, 3000);

                    if ( response == 'Delivered' || response == 'Cancelled' ) {
                        location.reload();
                    }
                }
            });

            return false;
        },
    };

    dokan_shipping_status_tracking_order_items.init();

})(jQuery);