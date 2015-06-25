jQuery(function($) {

    $('.tips').tooltip();
    $('select.grant_access_id').chosen();

    $('ul.order-status').on('click', 'a.dokan-edit-status', function(e) {
        $(this).addClass('dokan-hide').closest('li').next('li').removeClass('dokan-hide');

        return false;
    });

    $('ul.order-status').on('click', 'a.dokan-cancel-status', function(e) {
        $(this).closest('li').addClass('dokan-hide').prev('li').find('a.dokan-edit-status').removeClass('dokan-hide');

        return false;
    });

    $('form#dokan-order-status-form').on('submit', function(e) {
        e.preventDefault();

        var self = $(this),
            li = self.closest('li');

        li.block({ message: null, overlayCSS: { background: '#fff url(' + dokan.ajax_loader + ') no-repeat center', opacity: 0.6 } });

        $.post( dokan.ajaxurl, self.serialize(), function(response) {
            li.unblock();

            var prev_li = li.prev();

            li.addClass('dokan-hide');
            prev_li.find('label').replaceWith(response);
            prev_li.find('a.dokan-edit-status').removeClass('dokan-hide');
        });
    });

    $('form#add-order-note').on( 'submit', function(e) {
        e.preventDefault();

        if (!$('textarea#add-note-content').val()) return;

        $('#dokan-order-notes').block({ message: null, overlayCSS: { background: '#fff url(' + dokan.ajax_loader + ') no-repeat center', opacity: 0.6 } });

        $.post( dokan.ajaxurl, $(this).serialize(), function(response) {
            $('ul.order_notes').prepend( response );
            $('#dokan-order-notes').unblock();
            $('#add-note-content').val('');
        });

        return false;

    })

    $('#dokan-order-notes').on( 'click', 'a.delete_note', function() {

        var note = $(this).closest('li.note');

        $('#dokan-order-notes').block({ message: null, overlayCSS: { background: '#fff url(' + dokan.ajax_loader + ') no-repeat center', opacity: 0.6 } });

        var data = {
            action: 'woocommerce_delete_order_note',
            note_id: $(note).attr('rel'),
            security: $('#delete-note-security').val()
        };

        $.post( dokan.ajaxurl, data, function(response) {
            $(note).remove();
            $('#dokan-order-notes').unblock();
        });

        return false;

    });

    $('.order_download_permissions').on('click', 'button.grant_access', function() {
        var self = $(this),
            product = $('select.grant_access_id').val();

        if (!product) return;

        $('.order_download_permissions').block({ message: null, overlayCSS: { background: '#fff url(' + dokan.ajax_loader + ') no-repeat center', opacity: 0.6 } });

        var data = {
            action: 'dokan_grant_access_to_download',
            product_ids: product,
            loop: $('.order_download_permissions .panel').size(),
            order_id: self.data('order-id'),
            security: self.data('nonce')
        };

        $.post(dokan.ajaxurl, data, function( response ) {

            if ( response ) {

                $('#accordion').append( response );

            } else {

                alert('Could not grant access - the user may already have permission for this file or billing email is not set. Ensure the billing email is set, and the order has been saved.');

            }

            $( '.datepicker' ).datepicker();
            $('.order_download_permissions').unblock();

        });

        return false;
    });

    $('.order_download_permissions').on('click', 'button.revoke_access', function(e){
        e.preventDefault();
        var answer = confirm('Are you sure you want to revoke access to this download?');

        if (answer){

            var self = $(this),
                el = self.closest('.panel');

            var product = self.attr('rel').split(",")[0];
            var file = self.attr('rel').split(",")[1];

            if (product > 0) {

                $(el).block({ message: null, overlayCSS: { background: '#fff url(' + dokan.ajax_loader + ') no-repeat center', opacity: 0.6 } });

                var data = {
                    action: 'woocommerce_revoke_access_to_download',
                    product_id: product,
                    download_id: file,
                    order_id: self.data('order-id'),
                    security: self.data('nonce')
                };

                $.post(dokan.ajaxurl, data, function(response) {
                    // Success
                    $(el).fadeOut('300', function(){
                        $(el).remove();
                    });
                });

            } else {
                $(el).fadeOut('300', function(){
                    $(el).remove();
                });
            }

        }

        return false;
    });

});


/*global woocommerce_admin_meta_boxes, woocommerce_admin, accounting */
;(function($) {
    /**
     * Order Items Panel
     */
    var dokan_seller_meta_boxes_order_items = {
        init: function() {

            $( '#woocommerce-order-items' )
                .on( 'click', 'button.refund-items', this.refund_items )
                .on( 'click', '.cancel-action', this.cancel )
                .on( 'click', 'input.check-column', this.bulk_actions.check_column )
                .on( 'click', '.do_bulk_action', this.bulk_actions.do_bulk_action )
                .on( 'click', 'button.save-action', this.save_line_items )
                .on( 'click', 'a.edit-order-item', this.edit_item )
                .on( 'click', 'a.delete-order-item', this.delete_item )

                // Refunds
                .on( 'click', '.delete_refund', this.refunds.delete_refund )
                .on( 'click', 'button.do-api-refund, button.do-manual-refund', this.refunds.do_refund )
                .on( 'change', '.refund input.refund_line_total, .refund input.refund_line_tax', this.refunds.input_changed )
                .on( 'change keyup', '.wc-order-refund-items #refund_amount', this.refunds.amount_changed )
                .on( 'change', 'input.refund_order_item_qty', this.refunds.refund_quantity_changed )

                // Qty
                .on( 'change', 'input.quantity', this.quantity_changed )

                // Subtotal/total
                .on( 'keyup', '.woocommerce_order_items .split-input input:eq(0)', function() {
                    var $subtotal = $( this ).next();
                    if ( $subtotal.val() === '' || $subtotal.is( '.match-total' ) ) {
                        $subtotal.val( $( this ).val() ).addClass( 'match-total' );
                    }
                })

                .on( 'keyup', '.woocommerce_order_items .split-input input:eq(1)', function() {
                    $( this ).removeClass( 'match-total' );
                })

                // Meta
                .on( 'click', 'button.add_order_item_meta', this.item_meta.add )
                .on( 'click', 'button.remove_order_item_meta', this.item_meta.remove );

            // $( 'body' )
            //     .on( 'wc_backbone_modal_loaded', this.backbone.init )
            //     .on( 'wc_backbone_modal_response', this.backbone.response );
        },

        block: function() {
            $( '#woocommerce-order-items' ).block({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });
        },

        unblock: function() {
            $( '#woocommerce-order-items' ).unblock();
        },

        reload_items: function() {
            var data = {
                order_id: dokan_refund.post_id,
                action:   'woocommerce_load_order_items',
                security: dokan_refund.order_item_nonce
            };

            dokan_seller_meta_boxes_order_items.block();

            $.ajax({
                url:  dokan_refund.ajax_url,
                data: data,
                type: 'POST',
                success: function( response ) {
                    $( '#woocommerce-order-items .inside' ).empty();
                    $( '#woocommerce-order-items .inside' ).append( response );
                    // wc_meta_boxes_order.init_tiptip();
                    dokan_seller_meta_boxes_order_items.unblock();
                }
            });
        },

        // When the qty is changed, increase or decrease costs
        quantity_changed: function() {
            var $row          = $( this ).closest( 'tr.item' );
            var qty           = $( this ).val();
            var o_qty         = $( this ).attr( 'data-qty' );
            var line_total    = $( 'input.line_total', $row );
            var line_subtotal = $( 'input.line_subtotal', $row );

            // Totals
            var unit_total = accounting.unformat( line_total.attr( 'data-total' ), dokan_refund.mon_decimal_point ) / o_qty;
            line_total.val(
                parseFloat( accounting.formatNumber( unit_total * qty, dokan_refund.rounding_precision, '' ) )
                    .toString()
                    .replace( '.', dokan_refund.mon_decimal_point )
            );

            var unit_subtotal = accounting.unformat( line_subtotal.attr( 'data-subtotal' ), dokan_refund.mon_decimal_point ) / o_qty;
            line_subtotal.val(
                parseFloat( accounting.formatNumber( unit_subtotal * qty, dokan_refund.rounding_precision, '' ) )
                    .toString()
                    .replace( '.', dokan_refund.mon_decimal_point )
            );

            // Taxes
            $( 'td.line_tax', $row ).each(function() {
                var line_total_tax = $( 'input.line_tax', $( this ) );
                var unit_total_tax = accounting.unformat( line_total_tax.attr( 'data-total_tax' ), dokan_refund.mon_decimal_point ) / o_qty;
                if ( 0 < unit_total_tax ) {
                    line_total_tax.val(
                        parseFloat( accounting.formatNumber( unit_total_tax * qty, dokan_refund.rounding_precision, '' ) )
                            .toString()
                            .replace( '.', dokan_refund.mon_decimal_point )
                    );
                }

                var line_subtotal_tax = $( 'input.line_subtotal_tax', $( this ) );
                var unit_subtotal_tax = accounting.unformat( line_subtotal_tax.attr( 'data-subtotal_tax' ), dokan_refund.mon_decimal_point ) / o_qty;
                if ( 0 < unit_subtotal_tax ) {
                    line_subtotal_tax.val(
                        parseFloat( accounting.formatNumber( unit_subtotal_tax * qty, dokan_refund.rounding_precision, '' ) )
                            .toString()
                            .replace( '.', dokan_refund.mon_decimal_point )
                    );
                }
            });

            $( this ).trigger( 'quantity_changed' );
        },

        refund_items: function() {
            $( 'div.wc-order-refund-items' ).slideDown();
            $( 'div.wc-order-bulk-actions' ).slideUp();
            $( 'div.wc-order-totals-items' ).slideUp();
            $( '#woocommerce-order-items div.refund' ).show();
            $( '.wc-order-edit-line-item .wc-order-edit-line-item-actions' ).hide();
            return false;
        },

        cancel: function() {
            $( this ).closest( 'div.wc-order-data-row' ).slideUp();
            $( 'div.wc-order-bulk-actions' ).slideDown();
            $( 'div.wc-order-totals-items' ).slideDown();
            $( '#woocommerce-order-items div.refund' ).hide();
            $( '.wc-order-edit-line-item .wc-order-edit-line-item-actions' ).show();

            // Reload the items
            if ( 'true' === $( this ).attr( 'data-reload' ) ) {
                dokan_seller_meta_boxes_order_items.reload_items();
            }

            return false;
        },

        edit_item: function() {
            $( this ).closest( 'tr' ).find( '.view' ).hide();
            $( this ).closest( 'tr' ).find( '.edit' ).show();
            $( this ).hide();
            $( 'button.add-line-item' ).click();
            $( 'button.cancel-action' ).attr( 'data-reload', true );
            return false;
        },

        delete_item: function() {
            var answer = window.confirm( dokan_refund.remove_item_notice );

            if ( answer ) {
                var $item         = $( this ).closest( 'tr.item, tr.fee, tr.shipping' );
                var order_item_id = $item.attr( 'data-order_item_id' );

                dokan_seller_meta_boxes_order_items.block();

                var data = {
                    order_item_ids: order_item_id,
                    action:         'woocommerce_remove_order_item',
                    security:       dokan_refund.order_item_nonce
                };

                $.ajax({
                    url:     dokan_refund.ajax_url,
                    data:    data,
                    type:    'POST',
                    success: function( response ) {
                        $item.remove();
                        dokan_seller_meta_boxes_order_items.unblock();
                    }
                });
            }
            return false;
        },

        save_line_items: function() {
            var data = {
                order_id: dokan_refund.post_id,
                items:    $( 'table.woocommerce_order_items :input[name], .wc-order-totals-items :input[name]' ).serialize(),
                action:   'woocommerce_save_order_items',
                security: dokan_refund.order_item_nonce
            };

            dokan_seller_meta_boxes_order_items.block();

            $.ajax({
                url:  dokan_refund.ajax_url,
                data: data,
                type: 'POST',
                success: function( response ) {
                    $( '#woocommerce-order-items .inside' ).empty();
                    $( '#woocommerce-order-items .inside' ).append( response );
                    // wc_meta_boxes_order.init_tiptip();
                    dokan_seller_meta_boxes_order_items.unblock();
                }
            });

            $( this ).trigger( 'items_saved' );

            return false;
        },

        refunds: {

            do_refund: function() {
                dokan_seller_meta_boxes_order_items.block();

                if ( window.confirm( dokan_refund.i18n_do_refund ) ) {
                    var refund_amount = $( 'input#refund_amount' ).val();
                    var refund_reason = $( 'input#refund_reason' ).val();

                    // Get line item refunds
                    var line_item_qtys       = {};
                    var line_item_totals     = {};
                    var line_item_tax_totals = {};

                    $( '.refund input.refund_order_item_qty' ).each(function( index, item ) {
                        if ( $( item ).closest( 'tr' ).data( 'order_item_id' ) ) {
                            if ( item.value ) {
                                line_item_qtys[ $( item ).closest( 'tr' ).data( 'order_item_id' ) ] = item.value;
                            }
                        }
                    });

                    $( '.refund input.refund_line_total' ).each(function( index, item ) {
                        if ( $( item ).closest( 'tr' ).data( 'order_item_id' ) ) {
                            line_item_totals[ $( item ).closest( 'tr' ).data( 'order_item_id' ) ] = accounting.unformat( item.value, dokan_refund.mon_decimal_point );
                        }
                    });

                    $( '.refund input.refund_line_tax' ).each(function( index, item ) {
                        if ( $( item ).closest( 'tr' ).data( 'order_item_id' ) ) {
                            var tax_id = $( item ).data( 'tax_id' );

                            if ( ! line_item_tax_totals[ $( item ).closest( 'tr' ).data( 'order_item_id' ) ] ) {
                                line_item_tax_totals[ $( item ).closest( 'tr' ).data( 'order_item_id' ) ] = {};
                            }

                            line_item_tax_totals[ $( item ).closest( 'tr' ).data( 'order_item_id' ) ][ tax_id ] = accounting.unformat( item.value, dokan_refund.mon_decimal_point );
                        }
                    });

                    var data = {
                        action:                 'woocommerce_refund_line_items',
                        order_id:               dokan_refund.post_id,
                        refund_amount:          refund_amount,
                        refund_reason:          refund_reason,
                        line_item_qtys:         JSON.stringify( line_item_qtys, null, '' ),
                        line_item_totals:       JSON.stringify( line_item_totals, null, '' ),
                        line_item_tax_totals:   JSON.stringify( line_item_tax_totals, null, '' ),
                        api_refund:             $( this ).is( '.do-api-refund' ),
                        restock_refunded_items: $( '#restock_refunded_items:checked' ).size() ? 'true' : 'false',
                        security:               dokan_refund.order_item_nonce
                    };

                    $.post( dokan_refund.ajax_url, data, function( response ) {
                        if ( true === response.success ) {
                            dokan_seller_meta_boxes_order_items.reload_items();

                            if ( 'fully_refunded' === response.data.status ) {
                                // Redirect to same page for show the refunded status
                                window.location.href = window.location.href;
                            }
                        } else {
                            console.log(response);
                            window.alert( response.data.error );
                            dokan_seller_meta_boxes_order_items.unblock();
                        }
                    });
                } else {
                    dokan_seller_meta_boxes_order_items.unblock();
                }
            },

            delete_refund: function() {
                if ( window.confirm( dokan_refund.i18n_delete_refund ) ) {
                    var $refund   = $( this ).closest( 'tr.refund' );
                    var refund_id = $refund.attr( 'data-order_refund_id' );

                    dokan_seller_meta_boxes_order_items.block();

                    var data = {
                        action:    'woocommerce_delete_refund',
                        refund_id: refund_id,
                        security:  dokan_refund.order_item_nonce,
                    };

                    $.ajax({
                        url:     dokan_refund.ajax_url,
                        data:    data,
                        type:    'POST',
                        success: function( response ) {
                            dokan_seller_meta_boxes_order_items.reload_items();
                        }
                    });
                }
                return false;
            },

            input_changed: function() {
                var refund_amount = 0;
                var $items        = $( '.woocommerce_order_items' ).find( 'tr.item, tr.fee, tr.shipping' );

                $items.each(function() {
                    var $row               = $( this );
                    var refund_cost_fields = $row.find( '.refund input:not(.refund_order_item_qty)' );

                    refund_cost_fields.each(function( index, el ) {
                        refund_amount += parseFloat( accounting.unformat( $( el ).val() || 0, dokan_refund.mon_decimal_point ) );
                    });
                });

                $( '#refund_amount' )
                    .val( accounting.formatNumber(
                        refund_amount,
                        dokan_refund.currency_format_num_decimals,
                        '',
                        dokan_refund.mon_decimal_point
                    ) )
                    .change();
            },

            amount_changed: function() {
                var total = accounting.unformat( $( this ).val(), dokan_refund.mon_decimal_point );

                $( 'button .wc-order-refund-amount .amount' ).text( accounting.formatMoney( total, {
                    symbol:    dokan_refund.currency_format_symbol,
                    decimal:   dokan_refund.currency_format_decimal_sep,
                    thousand:  dokan_refund.currency_format_thousand_sep,
                    precision: dokan_refund.currency_format_num_decimals,
                    format:    dokan_refund.currency_format
                } ) );
            },

            // When the refund qty is changed, increase or decrease costs
            refund_quantity_changed: function() {
                var $row              = $( this ).closest( 'tr.item' );
                var qty               = $row.find( 'input.quantity' ).val();
                var refund_qty        = $( this ).val();
                var line_total        = $( 'input.line_total', $row );
                var refund_line_total = $( 'input.refund_line_total', $row );

                // Totals
                var unit_total = accounting.unformat( line_total.attr( 'data-total' ), dokan_refund.mon_decimal_point ) / qty;

                refund_line_total.val(
                    parseFloat( accounting.formatNumber( unit_total * refund_qty, dokan_refund.rounding_precision, '' ) )
                        .toString()
                        .replace( '.', dokan_refund.mon_decimal_point )
                ).change();

                // Taxes
                $( 'td.line_tax', $row ).each( function() {
                    var line_total_tax        = $( 'input.line_tax', $( this ) );
                    var refund_line_total_tax = $( 'input.refund_line_tax', $( this ) );
                    var unit_total_tax = accounting.unformat( line_total_tax.attr( 'data-total_tax' ), dokan_refund.mon_decimal_point ) / qty;

                    if ( 0 < unit_total_tax ) {
                        refund_line_total_tax.val(
                            parseFloat( accounting.formatNumber( unit_total_tax * refund_qty, dokan_refund.rounding_precision, '' ) )
                                .toString()
                                .replace( '.', dokan_refund.mon_decimal_point )
                        ).change();
                    } else {
                        refund_line_total_tax.val( 0 ).change();
                    }
                });

                // Restock checkbox
                if ( refund_qty > 0 ) {
                    $( '#restock_refunded_items' ).closest( 'tr' ).show();
                } else {
                    $( '#restock_refunded_items' ).closest( 'tr' ).hide();
                    $( '.woocommerce_order_items input.refund_order_item_qty' ).each( function() {
                        if ( $( this ).val() > 0 ) {
                            $( '#restock_refunded_items' ).closest( 'tr' ).show();
                        }
                    });
                }

                $( this ).trigger( 'refund_quantity_changed' );
            }
        },

        item_meta: {

            add: function() {
                var $button = $( this );
                var $item = $button.closest( 'tr.item' );

                var data = {
                    order_item_id: $item.attr( 'data-order_item_id' ),
                    action:        'woocommerce_add_order_item_meta',
                    security:      dokan_refund.order_item_nonce
                };

                dokan_seller_meta_boxes_order_items.block();

                $.ajax({
                    url: dokan_refund.ajax_url,
                    data: data,
                    type: 'POST',
                    success: function( response ) {
                        $item.find('tbody.meta_items').append( response );
                        dokan_seller_meta_boxes_order_items.unblock();
                    }
                });

                return false;
            },

            remove: function() {
                if ( window.confirm( dokan_refund.remove_item_meta ) ) {
                    var $row = $( this ).closest( 'tr' );

                    var data = {
                        meta_id:  $row.attr( 'data-meta_id' ),
                        action:   'woocommerce_remove_order_item_meta',
                        security: dokan_refund.order_item_nonce
                    };

                    dokan_seller_meta_boxes_order_items.block();

                    $.ajax({
                        url: dokan_refund.ajax_url,
                        data: data,
                        type: 'POST',
                        success: function( response ) {
                            $row.hide();
                            dokan_seller_meta_boxes_order_items.unblock();
                        }
                    });
                }
                return false;
            }
        },

        bulk_actions: {

            check_column: function(){
                if ( $( this ).is( ':checked' ) ) {
                    $( '#woocommerce-order-items' ).find( '.check-column input' ).attr( 'checked', 'checked' );
                } else {
                    $( '#woocommerce-order-items' ).find( '.check-column input' ).removeAttr( 'checked' );
                }
            },

            do_bulk_action: function() {
                var action        = $( this ).closest( '.bulk-actions' ).find( 'select' ).val();
                var selected_rows = $( '#woocommerce-order-items' ).find( '.check-column input:checked' );
                var item_ids      = [];

                $( selected_rows ).each( function() {
                    var $item = $( this ).closest( 'tr' );

                    if ( $item.attr( 'data-order_item_id' ) ) {
                        item_ids.push( $item.attr( 'data-order_item_id' ) );
                    }
                } );

                if ( item_ids.length === 0 ) {
                    window.alert( dokan_refund.i18n_select_items );
                    return;
                }

                if ( dokan_seller_meta_boxes_order_items.bulk_actions[ action ] ) {
                    dokan_seller_meta_boxes_order_items.bulk_actions[action]( selected_rows, item_ids );
                }

                return false;
            },

            delete: function( selected_rows, item_ids ) {
                if ( window.confirm( dokan_refund.remove_item_notice ) ) {

                    dokan_seller_meta_boxes_order_items.block();

                    var data = {
                        order_item_ids: item_ids,
                        action:         'woocommerce_remove_order_item',
                        security:       dokan_refund.order_item_nonce
                    };

                    $.ajax({
                        url: dokan_refund.ajax_url,
                        data: data,
                        type: 'POST',
                        success: function( response ) {
                            $( selected_rows ).each(function() {
                                $( this ).closest( 'tr' ).remove();
                            });
                            dokan_seller_meta_boxes_order_items.unblock();
                        }
                    });
                }
            },

            increase_stock: function( selected_rows, item_ids ) {
                dokan_seller_meta_boxes_order_items.block();

                var quantities = {};

                $( selected_rows ).each(function() {

                    var $item = $( this ).closest( 'tr.item, tr.fee' );
                    var $qty  = $item.find( 'input.quantity' );

                    quantities[ $item.attr( 'data-order_item_id' ) ] = $qty.val();
                });

                var data = {
                    order_id:       dokan_refund.post_id,
                    order_item_ids: item_ids,
                    order_item_qty: quantities,
                    action:         'woocommerce_increase_order_item_stock',
                    security:       dokan_refund.order_item_nonce
                };

                $.ajax({
                    url: dokan_refund.ajax_url,
                    data: data,
                    type: 'POST',
                    success: function( response ) {
                        window.alert( response );
                        dokan_seller_meta_boxes_order_items.unblock();
                    }
                });
            },

            reduce_stock: function( selected_rows, item_ids ) {
                dokan_seller_meta_boxes_order_items.block();

                var quantities = {};

                $( selected_rows ).each(function() {

                    var $item = $( this ).closest( 'tr.item, tr.fee' );
                    var $qty  = $item.find( 'input.quantity' );

                    quantities[ $item.attr( 'data-order_item_id' ) ] = $qty.val();
                });

                var data = {
                    order_id:       dokan_refund.post_id,
                    order_item_ids: item_ids,
                    order_item_qty: quantities,
                    action:         'woocommerce_reduce_order_item_stock',
                    security:       dokan_refund.order_item_nonce
                };

                $.ajax({
                    url: dokan_refund.ajax_url,
                    data: data,
                    type: 'POST',
                    success: function( response ) {
                        window.alert( response );
                        dokan_seller_meta_boxes_order_items.unblock();
                    }
                } );
            }
        },
    };

    dokan_seller_meta_boxes_order_items.init();

})(jQuery);