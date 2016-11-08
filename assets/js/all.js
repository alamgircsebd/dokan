/**
 * Admin helper functions
 *
 * @package WeDevs Framework
 */
jQuery(function($) {

    window.WeDevs_Admin = {

        /**
         * Image Upload Helper Function
         **/
        imageUpload: function (e) {
            e.preventDefault();

            var self = $(this),
                inputField = self.siblings('input.image_url');

            tb_show('', 'media-upload.php?post_id=0&amp;type=image&amp;TB_iframe=true');

            window.send_to_editor = function (html) {
                var url = $(html).attr('src');
                //if we find an image, get the src
                if($(html).find('img').length > 0) {
                    url = $(html).find('img').attr('src');
                }

                inputField.val(url);

                var image = '<img src="' + url + '" alt="image" />';
                    image += '<a href="#" class="remove-image"><span>Remove</span></a>';

                self.siblings('.image_placeholder').empty().append(image);
                tb_remove();
            }
        },

        removeImage: function (e) {
            e.preventDefault();
            var self = $(this);

            self.parent('.image_placeholder').siblings('input.image_url').val('');
            self.parent('.image_placeholder').empty();
        }
    }

    // settings api - radio_image
    $('.dokan-settings-radio-image button').on('click', function (e) {
        e.preventDefault();

        var btn = $(this),
            template = btn.data('template'),
            input = btn.data('input'),
            container = btn.parents('.dokan-settings-radio-image-container');

        $('#' + input).val(template);

        container.find('.active').removeClass('active').addClass('not-active');

        btn.parents('.dokan-settings-radio-image').addClass('active').removeClass('not-active');
    });
});


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
            action: 'dokan_delete_order_note',
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
                el = self.closest('.dokan-panel');

            var product = self.attr('rel').split(",")[0];
            var file = self.attr('rel').split(",")[1];

            if (product > 0) {

                $(el).block({ message: null, overlayCSS: { background: '#fff url(' + dokan.ajax_loader + ') no-repeat center', opacity: 0.6 } });

                var data = {
                    action: 'dokan_revoke_access_to_download',
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

            $('#tracking-modal').on('shown.bs.modal', function () {
                $('#tracking-modal').focus();
            });
            $( "#shipped-date" ).datepicker({
                dateFormat: "yy-mm-dd"
            });
            //saving note
            $( 'body' ).on('click','#add-tracking-details', this.insertShippingTrackingInfo);

            $( '#woocommerce-order-items' )
                .on( 'click', 'button.refund-items', this.refund_items )
                .on( 'click', '.cancel-action', this.cancel )

                // Refunds
                .on( 'click', 'button.do-api-refund, button.do-manual-refund', this.refunds.do_refund )
                .on( 'change', '.refund input.refund_line_total, .refund input.refund_line_tax', this.refunds.input_changed )
                .on( 'change keyup', '.wc-order-refund-items #refund_amount', this.refunds.amount_changed )
                .on( 'change', 'input.refund_order_item_qty', this.refunds.refund_quantity_changed )

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
        },

        insertShippingTrackingInfo: function(e){
            e.preventDefault();

            var shipping_tracking_info = {
                shipping_provider: $('#shipping_provider').val(),
                shipping_number: $('#tracking_number').val(),
                shipped_date: $('#shipped-date').val(),
                action: $('#action').val(),
                post_id: $('#post-id').val(),
                security: $('#security').val()
            };

            $('#dokan-order-notes').block({ message: null, overlayCSS: { background: '#fff url(' + dokan.ajax_loader + ') no-repeat center', opacity: 0.6 } });

            $.post( dokan.ajaxurl, shipping_tracking_info, function(response) {
                $('ul.order_notes').prepend( response );
                $('#dokan-order-notes').unblock();
                $('#tracking-modal').modal('hide');
            });

            return false;

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
                action:   'dokan_load_order_items',
                security: dokan_refund.order_item_nonce
            };

            dokan_seller_meta_boxes_order_items.block();            

            $.ajax({
                url:  dokan_refund.ajax_url,
                data: data,
                type: 'POST',
                success: function( response ) {
                    $( '.dokan-panel-default #woocommerce-order-items' ).empty();
                    $( '.dokan-panel-default #woocommerce-order-items' ).append( response );
                    // wc_meta_boxes_order.init_tiptip();
                }
            });
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
                        action:                 'dokan_refund_request',
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
                            window.alert( response.data );
                            dokan_seller_meta_boxes_order_items.reload_items();
                        } else {
                            window.alert( response.data );
                            dokan_seller_meta_boxes_order_items.unblock();
                        }
                    });
                } else {
                    dokan_seller_meta_boxes_order_items.unblock();
                }
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
    };

    dokan_seller_meta_boxes_order_items.init();

})(jQuery);


;(function($){

    var variantsHolder = $('#variants-holder');
    var product_gallery_frame;
    var product_featured_frame;
    var $image_gallery_ids = $('#product_image_gallery');
    var $product_images = $('#product_images_container ul.product_images');

    var Dokan_Editor = {

        /**
         * Constructor function
         */
        init: function() {

            product_type = 'simple';

            $('.product-edit-container').on( 'click', '.dokan-section-heading', this.toggleProductSection );

            $('.product-edit-container').on('click', 'input[type=checkbox]#_downloadable', this.downloadable );
            $('.product-edit-container').on('click', 'a.sale-schedule', this.showDiscountSchedule );

            // Tab view variants
            $('#product-attributes').on('click', '.add-variant-category', this.variants.addCategory );
            $('#variants-holder').on('click', '.box-header .row-remove', this.variants.removeCategory );
            $('#variants-holder').on('click', '.item-action a.row-add', this.variants.addItem );
            $('#variants-holder').on('click', '.item-action a.row-remove', this.variants.removeItem );

            $('body, #variable_product_options').on( 'click', '.sale_schedule', this.variants.saleSchedule );
            $('body, #variable_product_options').on( 'click', '.cancel_sale_schedule', this.variants.cancelSchedule );
            $('#variable_product_options').on('woocommerce_variations_added', this.variants.onVariantAdded );
            this.variants.dates();
            this.variants.initSaleSchedule();

            // save attributes
            $('.save_attributes').on('click', this.variants.save );

            // gallery
            $('#dokan-product-images').on('click', 'a.add-product-images', this.gallery.addImages );
            $('#dokan-product-images').on( 'click', 'a.action-delete', this.gallery.deleteImage );
            $('#dokan-product-images').on( 'click', 'a.delete', this.gallery.deleteImage );
            this.gallery.sortable();

            // featured image
            $('body, .product-edit-container').on('click', 'a.dokan-feat-image-btn', this.featuredImage.addImage );
            $('body, .product-edit-container').on('click', 'a.dokan-remove-feat-image', this.featuredImage.removeImage );

            // post status change
            $('.dokan-toggle-sidebar').on('click', 'a.dokan-toggle-edit', this.sidebarToggle.showStatus );
            $('.dokan-toggle-sidebar').on('click', 'a.dokan-toggle-save', this.sidebarToggle.saveStatus );
            $('.dokan-toggle-sidebar').on('click', 'a.dokan-toggle-cacnel', this.sidebarToggle.cancel );

            // new product design variations
            $('.product-edit-container').on( 'change', 'input[type=checkbox]#_manage_stock', this.editProduct.showManageStock );
            $( '.product-edit-container' ).on( 'click', 'a.upload_file_button', this.fileDownloadable );
            $('.product_lot_discount').on('change', 'input[type=checkbox]#_is_lot_discount', this.editProduct.showLotDiscountWrapper );
            $('body').on( 'click', '.upload_image_button', this.editProduct.loadVariationImage );

            // shipping
            $('.product-edit-new-container, #product-shipping').on('change', 'input[type=checkbox]#_overwrite_shipping', this.editProduct.shipping.showHideOverride );
            $('.product-edit-new-container').on('change', 'input[type=checkbox]#_disable_shipping', this.editProduct.shipping.disableOverride );
            $('#product-shipping').on('click', '#_disable_shipping', this.shipping.disableOverride );

            // File inputs
            $('body').on('click', 'a.insert-file-row', function(){
                $(this).closest('table').find('tbody').append( $(this).data( 'row' ) );
                return false;
            });

            $('body').on('click', 'a.delete', function(){
                $(this).closest('tr').remove();
                return false;
            });

            this.editProduct.shipping.showHideOverride();
            this.editProduct.shipping.disableOverride();
            this.shipping.disableOverride();
            $('#_disable_shipping').trigger('change');
            $('#_overwrite_shipping').trigger('change');

            $( 'body' ).on( 'submit', 'form.dokan-product-edit-form', this.inputValidate );
            $( '.hide_if_lot_discount' ).hide();
            $( '.hide_if_order_discount' ).hide();

            // For new desing in product page
            $( '.dokan-product-listing' ).on( 'click', 'a.dokan-add-new-product', this.addProductPopup );

            this.loadSelect2();
            this.attribute.sortable();
            this.checkProductPostboxToggle();
            $( '.product-edit-container .dokan-product-attribute-wrapper' ).on( 'click', 'a.dokan-product-toggle-attribute, .dokan-product-attribute-heading', this.attribute.toggleAttribute );
            $( '.product-edit-container .dokan-product-attribute-wrapper' ).on( 'click', 'a.add_new_attribute', this.attribute.addNewAttribute );
            $( '.product-edit-container .dokan-product-attribute-wrapper' ).on( 'keyup', 'input.dokan-product-attribute-name', this.attribute.dynamicAttrNameChange );
            $( '.dokan-product-attribute-wrapper ul.dokan-attribute-option-list' ).on( 'click', 'button.dokan-select-all-attributes', this.attribute.selectAllAttr );
            $( '.dokan-product-attribute-wrapper ul.dokan-attribute-option-list' ).on( 'click', 'button.dokan-select-no-attributes', this.attribute.selectNoneAttr );
            $( '.dokan-product-attribute-wrapper ul.dokan-attribute-option-list' ).on( 'click', 'button.dokan-add-new-attribute', this.attribute.addNewExtraAttr );
            $( '.product-edit-container .dokan-product-attribute-wrapper' ).on( 'click', 'a.dokan-product-remove-attribute', this.attribute.removeAttribute );
            $( '.product-edit-container .dokan-product-attribute-wrapper' ).on( 'click', 'a.dokan-save-attribute', this.attribute.saveAttribute );
        },

        checkProductPostboxToggle: function() {
            var toggle = JSON.parse( localStorage.getItem( 'toggleClasses' ) );

            $.each( toggle, function(el, i) {
                var wrapper    = $( '.' + el.replace( /_/g, '-' ) ),
                    content    = wrapper.find( '.dokan-section-content' ),
                    targetIcon = wrapper.find( 'i.fa-sort-desc' );

                if ( i ) {
                    content.show();
                    targetIcon.removeClass( 'fa-flip-horizointal' ).addClass( 'fa-flip-vertical' );
                    targetIcon.css( 'marginTop', '9px' );
                } else {
                    content.hide();
                    targetIcon.removeClass( 'fa-flip-vertical' ).addClass( 'fa-flip-horizointal' );
                    targetIcon.css( 'marginTop', '0px' );
                }
            });
        },

        toggleProductSection: function(e) {
            e.preventDefault();

            var self = $(this);
            if ( JSON.parse( localStorage.getItem( 'toggleClasses' ) ) != null ) {
                var toggleClasses = JSON.parse( localStorage.getItem( 'toggleClasses' ) );
            } else {
                var toggleClasses = {};
            }

            self.closest( '.dokan-edit-row' ).find('.dokan-section-content').slideToggle( 300, function() {
                if( $(this).is( ':visible' ) ) {
                    var targetIcon = self.find( 'i.fa-sort-desc' );
                    targetIcon.removeClass( 'fa-flip-horizointal' ).addClass( 'fa-flip-vertical' );
                    targetIcon.css( 'marginTop', '9px' );
                    toggleClasses[self.data('togglehandler')] = true;
                } else {
                    var targetIcon = self.find( 'i.fa-sort-desc' );
                    targetIcon.removeClass( 'fa-flip-vertical' ).addClass( 'fa-flip-horizointal' );
                    targetIcon.css( 'marginTop', '0px' );
                    toggleClasses[self.data('togglehandler')] = false;
                }

                localStorage.setItem( 'toggleClasses', JSON.stringify( toggleClasses ) );
            });

        },

        loadSelect2: function() {
            $('.dokan-select2').select2();
        },

        addProductPopup: function (e) {
            e.preventDefault();
            Dokan_Editor.openProductPopup();
        },

        openProductPopup: function() {
            var productTemplate = wp.template( 'dokan-add-new-product' );
            $.magnificPopup.open({
                fixedContentPos: true,
                items: {
                    src: productTemplate().trim(),
                    type: 'inline'
                },
                callbacks: {
                    open: function() {
                        $(this.content).closest('.mfp-wrap').removeAttr('tabindex');
                        Dokan_Editor.loadSelect2();

                        $('.sale_price_dates_from, .sale_price_dates_to').on('focus', function() {
                            $(this).css('z-index', '99999');
                        });

                        Dokan_Editor.variants.dates();
                        $( 'body' ).on( 'click', '.product-container-footer input[type="submit"]', Dokan_Editor.createNewProduct );
                    }
                }
            });
        },

        createNewProduct: function (e) {
            e.preventDefault();

            var self = $(this),
                form = self.closest('form#dokan-add-new-product-form'),
                btn_id = self.attr('data-btn_id');

            form.find( 'span.dokan-show-add-product-error' ).html('');
            form.find( 'span.dokan-add-new-product-spinner' ).css( 'display', 'inline-block' );


            if ( form.find( 'input[name="post_title"]' ).val() == '' ) {
                $( 'span.dokan-show-add-product-error' ).html( 'Product title is required' );
                form.find( 'span.dokan-add-new-product-spinner' ).css( 'display', 'none' );
                return;
            }

            if ( form.find( 'select[name="product_cat"]' ).val() == '-1' ) {
                $( 'span.dokan-show-add-product-error' ).html( 'Product category is required' );
                form.find( 'span.dokan-add-new-product-spinner' ).css( 'display', 'none' );
                return;
            }

            var data = {
                action:   'dokan_create_new_product',
                postdata: form.serialize(),
                _wpnonce : dokan.nonce
            };

            $.post( dokan.ajaxurl, data, function( resp ) {
                if ( resp.success ) {
                    if ( btn_id == 'create_new' ) {
                        $.magnificPopup.close();
                        window.location.href = resp.data;
                    } else {
                        $('.dokan-dahsboard-product-listing-wrapper').load( window.location.href + ' table.product-listing-table' );
                        $.magnificPopup.close();
                        Dokan_Editor.openProductPopup();
                    }
                } else {
                    $( 'span.dokan-show-add-product-error' ).html( resp.data );
                }
                form.find( 'span.dokan-add-new-product-spinner' ).css( 'display', 'none' );
            });
        },

        attribute: {

            toggleAttribute: function(e) {
                e.preventDefault();

                var self = $(this),
                    list = self.closest('li'),
                    item = list.find('.dokan-product-attribute-item');

                if ( $(item).hasClass('dokan-hide') ) {
                    self.closest('.dokan-product-attribute-heading').css({ borderBottom: '1px solid #e3e3e3' });
                    $(item).slideDown( 200, function() {
                        self.find('i.fa').removeClass('fa-flip-horizointal').addClass('fa-flip-vertical');
                        $(this).removeClass('dokan-hide');
                        if ( ! $(e.target).hasClass( 'dokan-product-attribute-heading' ) ) {
                            $(e.target).closest('a').css('top', '12px');
                        } else if ( $(e.target).hasClass( 'dokan-product-attribute-heading' ) ) {
                            self.find( 'a.dokan-product-toggle-attribute' ).css('top', '12px');
                        }
                    });
                } else {
                    $(item).slideUp( 200, function() {
                        $(this).addClass('dokan-hide');
                        self.find('i.fa').removeClass('fa-flip-vertical').addClass('fa-flip-horizointal');
                        if ( ! $(e.target).hasClass('dokan-product-attribute-heading') ) {
                            $(e.target).closest('a').css('top', '7px');
                        } else if ( $(e.target).hasClass( 'dokan-product-attribute-heading' ) ) {
                            self.find('a.dokan-product-toggle-attribute').css('top', '7px');
                        }
                        self.closest('.dokan-product-attribute-heading').css({ borderBottom: 'none' });

                    })
                }
                return false;
            },

            sortable: function() {
                $('.dokan-product-attribute-wrapper ul').sortable({
                    items: 'li.product-attribute-list',
                    cursor: 'move',
                    scrollSensitivity:40,
                    forcePlaceholderSize: true,
                    forceHelperSize: false,
                    helper: 'clone',
                    opacity: 0.65,
                    placeholder: 'dokan-sortable-placeholder',
                    start:function(event,ui){
                        ui.item.css('background-color','#f6f6f6');
                    },
                    stop:function(event,ui){
                        ui.item.removeAttr('style');
                    },
                    update: function(event, ui) {
                        var attachment_ids = '';
                        Dokan_Editor.attribute.reArrangeAttribute();
                    }
                });
            },

            dynamicAttrNameChange: function(e) {
                e.preventDefault();
                var self = $(this),
                    value = self.val();

                if ( value == '' ) {
                    self.closest( 'li' ).find( 'strong' ).html( 'Attribute Name' );
                } else {
                    self.closest( 'li' ).find( 'strong' ).html( value );
                }
            },

            selectAllAttr: function(e) {
                e.preventDefault();
                $( this ).closest( 'li.product-attribute-list' ).find( 'select.dokan_attribute_values option' ).attr( 'selected', 'selected' );
                $( this ).closest( 'li.product-attribute-list' ).find( 'select.dokan_attribute_values' ).change();
                return false;
            },

            selectNoneAttr: function(e) {
                e.preventDefault();
                $( this ).closest( 'li.product-attribute-list' ).find( 'select.dokan_attribute_values option' ).removeAttr( 'selected' );
                $( this ).closest( 'li.product-attribute-list' ).find( 'select.dokan_attribute_values' ).change();
                return false;
            },

            reArrangeAttribute: function() {
                var attributeWrapper = $('.dokan-product-attribute-wrapper').find('ul.dokan-attribute-option-list');
                attributeWrapper.find( 'li.product-attribute-list' ).css( 'cursor', 'default' ).each(function( i ) {
                    $(this).find('.attribute_position').val(i);
                });
            },

            addNewExtraAttr: function(e) {
                e.preventDefault();

                var $wrapper           = $(this).closest( 'li.product-attribute-list' );
                var attribute          = $wrapper.data( 'taxonomy' );
                var new_attribute_name = window.prompt( dokan.new_attribute_prompt );

                if ( new_attribute_name ) {

                    var data = {
                        action:   'dokan_add_new_attribute',
                        taxonomy: attribute,
                        term:     new_attribute_name,
                        _wpnonce : dokan.nonce
                    };

                    $.post( dokan.ajaxurl, data, function( response ) {
                        if ( response.error ) {
                            window.alert( response.error );
                        } else if ( response.slug ) {
                            $wrapper.find( 'select.dokan_attribute_values' ).append( '<option value="' + response.slug + '" selected="selected">' + response.name + '</option>' );
                            $wrapper.find( 'select.dokan_attribute_values' ).change();
                        }

                    });
                }
            },

            addNewAttribute: function(e) {
                e.preventDefault();

                var self  = $(this),
                    attrWrap  = self.closest('.dokan-attribute-type').find('select#predefined_attribute'),
                    attribute = attrWrap.val();

                var data = {
                    action   : 'dokan_get_pre_attribute',
                    taxonomy : attribute,
                    _wpnonce : dokan.nonce
                };

                self.closest('.dokan-attribute-type').find('span.dokan-attribute-spinner').removeClass('dokan-hide');

                $.post( dokan.ajaxurl, data, function( resp ) {
                    if ( resp.success ) {
                        var attributeWrapper = $('.dokan-product-attribute-wrapper').find('ul.dokan-attribute-option-list');
                        $html = $.parseHTML(resp.data);
                        $($html).find('.dokan-product-attribute-item').removeClass('dokan-hide');
                        $($html).find('i.fa.fa-sort-desc').removeClass('fa-flip-horizointal').addClass('fa-flip-vertical');
                        $($html).find('a.dokan-product-toggle-attribute').css('top','12px');
                        $($html).find('.dokan-product-attribute-heading').css({ borderBottom: '1px solid #e3e3e3' });

                        attributeWrapper.append( $html );
                        $( 'select#product_type' ).trigger('change');
                        Dokan_Editor.loadSelect2();
                        Dokan_Editor.attribute.reArrangeAttribute();
                    };

                    self.closest('.dokan-attribute-type').find('span.dokan-attribute-spinner').addClass('dokan-hide');

                    if ( attribute ) {
                        attrWrap.find( 'option[value="' + attribute + '"]' ).attr( 'disabled','disabled' );
                        attrWrap.val( '' );
                    }
                });
            },

            removeAttribute: function(evt) {
                evt.stopPropagation();

                if ( window.confirm( dokan.remove_attribute ) ) {
                    var $parent = $( this ).closest('li.product-attribute-list');

                    $parent.fadeOut( 300, function() {
                        if ( $parent.is( '.taxonomy' ) ) {
                            $( 'select.dokan_attribute_taxonomy' ).find( 'option[value="' + $parent.data( 'taxonomy' ) + '"]' ).removeAttr( 'disabled' );
                        }
                        $(this).remove();
                        Dokan_Editor.attribute.reArrangeAttribute();
                    });
                }

                return false;
            },

            saveAttribute: function(e) {
                e.preventDefault();

                var self = $(this),
                    data = {
                        post_id:  $('#dokan-edit-product-id').val(),
                        data:     $( 'ul.dokan-attribute-option-list' ).find( 'input, select, textarea' ).serialize(),
                        action:   'dokan_save_attributes'
                    };

                $( '.dokan-product-attribute-wrapper' ).block({
                    message: null,
                    fadeIn: 50,
                    fadeOut: 1000,
                    overlayCSS: {
                        background: '#fff',
                        opacity: 0.6
                    }
                });

                $.post( dokan.ajaxurl, data, function( resp ) {
                    // Load variations panel.
                    $( '#dokan-variable-product-options' ).load( window.location.toString() + ' #dokan-variable-product-options-inner', function() {
                        $( '#dokan-variable-product-options' ).trigger( 'reload' );
                        $( 'select#product_type' ).trigger('change');
                        $( '.dokan-product-attribute-wrapper' ).unblock();
                    });
                });

            }
        },

        inputValidate: function( e ) {
            e.preventDefault();

            if ( $( '#post_title' ).val().trim() == '' ) {
                $( '#post_title' ).focus();
                $( 'div.dokan-product-title-alert' ).removeClass('dokan-hide');
                return;
            }else{
                $( 'div.dokan-product-title-alert' ).hide();
            }

            if ( $( 'select.product_cat' ).val() == -1 ) {
                $( 'select.product_cat' ).focus();
                $( 'div.dokan-product-cat-alert' ).removeClass('dokan-hide');
                return;
            }else{
                $( 'div.dokan-product-cat-alert' ).hide();
            }
            $( 'input[type=submit]' ).attr( 'disabled', 'disabled' );
            this.submit();
        },

        downloadable: function() {
            if ( $(this).prop('checked') ) {
                $(this).closest('aside').find('.dokan-side-body').removeClass('dokan-hide');
            } else {
                $(this).closest('aside').find('.dokan-side-body').addClass('dokan-hide');
            }
        },

        showDiscountSchedule: function(e) {
            e.preventDefault();
            $('.sale-schedule-container').slideToggle('fast');
        },

        editProduct: {

            showManageStock: function(e) {
                if ( $(this).is(':checked') ) {
                    $('.show_if_stock').slideDown('fast');
                } else {
                    $('.show_if_stock').slideUp('fast');
                }
            },

            showLotDiscountWrapper: function(){
                if ( $( this ).is(':checked') ) {
                    $('.show_if_needs_lot_discount' ).slideDown('slow');
                } else {
                    $('.show_if_needs_lot_discount' ).slideUp('slow');
                }
            },

            loadVariationImage: function(e) {
                e.preventDefault();
                var variable_image_frame;
                var $button                = $(this);
                var post_id                = $button.attr('rel');
                var $parent                = $button.closest('.upload_image');
                setting_variation_image    = $parent;
                placeholder_iamge          = dokan.dokan_placeholder_img_src;
                setting_variation_image_id = post_id;

                e.preventDefault();

                if ( $button.is('.dokan-img-remove') ) {

                    setting_variation_image.find( '.upload_image_id' ).val( '' );
                    setting_variation_image.find( 'img' ).attr( 'src', placeholder_iamge );
                    setting_variation_image.find( '.upload_image_button' ).removeClass( 'dokan-img-remove' );
                    $button.closest( '.dokan-product-variation-itmes' ).addClass( 'variation-needs-update' );
                    $( 'button.cancel-variation-changes, button.save-variation-changes' ).removeAttr( 'disabled' );
                    $( '.dokan-product-variation-wrapper' ).trigger( 'dokan_variations_input_changed' );

                } else {

                    // If the media frame already exists, reopen it.
                    if ( variable_image_frame ) {
                        variable_image_frame.uploader.uploader.param( 'post_id', setting_variation_image_id );
                        variable_image_frame.open();
                        return;
                    } else {
                        wp.media.model.settings.post.id = setting_variation_image_id;
                        wp.media.model.settings.type = 'dokan';
                    }

                    // Create the media frame.
                    variable_image_frame = wp.media.frames.variable_image = wp.media({
                        // Set the title of the modal.
                        title: dokan.i18n_choose_image,
                        button: {
                            text: dokan.i18n_set_image
                        }
                    });

                    // When an image is selected, run a callback.
                    variable_image_frame.on( 'select', function() {

                        attachment = variable_image_frame.state().get('selection').first().toJSON();

                        setting_variation_image.find( '.upload_image_id' ).val( attachment.id );
                        setting_variation_image.find( '.upload_image_button' ).addClass( 'dokan-img-remove' );
                        setting_variation_image.find( 'img' ).attr( 'src', attachment.url );
                        $button.closest( '.dokan-product-variation-itmes' ).addClass( 'variation-needs-update' );
                        $( 'button.cancel-variation-changes, button.save-variation-changes' ).removeAttr( 'disabled' );
                        $( '.dokan-product-variation-wrapper' ).trigger( 'dokan_variations_input_changed' );

                        wp.media.model.settings.post.id = setting_variation_image_id;
                    });

                    // Finally, open the modal.
                    variable_image_frame.open();
                }

            },

            shipping: {
                showHideOverride: function() {
                    if ( $('#_overwrite_shipping').is(':checked') ) {
                        $('.show_if_override').show();
                    } else {
                        $('.show_if_override').hide();
                    }
                },

                disableOverride: function() {
                    if ( $('#_disable_shipping').is(':checked') ) {
                        $('.show_if_needs_shipping').show();
                        $( '#_overwrite_shipping').trigger('change')
                    } else {
                        $('.show_if_needs_shipping').hide();
                    }
                }
            }
        },

        variants: {
            addCategory: function (e) {
                e.preventDefault();

                var product_types = $('#product_type').val();
                var check = $(this).closest('p.toolbar').find('select.select-attribute').val();
                var row = $('.inputs-box').length;

                if ( check == '' ) {
                    var category = wp.template('sc-category');
                    variantsHolder.append( category( { row:row } ) ).children(':last').hide().fadeIn();
                } else {
                    var data = {
                        row: row,
                        name: check,
                        type: product_types,
                        action: 'dokan_pre_define_attribute',
                    };

                    $('#product-attributes .toolbar').block({ message: null, overlayCSS: { background: '#fff', opacity: 0.6 } });

                    $.post( dokan.ajaxurl, data, function(resp) {
                        if ( resp.success ) {
                            variantsHolder.append(resp.data).children(':last').hide().fadeIn();
                        }
                        $('#product-attributes .toolbar').unblock();

                    });
                }

                if ( product_type === 'simple' ) {
                    variantsHolder.find('.show_if_variable').hide();
                }

            },

            removeCategory: function (e) {
                e.preventDefault();

                if ( confirm('Sure?') ) {
                    $(this).parents('.inputs-box').fadeOut(function() {
                        $(this).remove();
                    });
                }
            },

            addItem: function (e) {
                e.preventDefault();

                var self = $(this),
                    wrap = self.closest('.inputs-box'),
                    list = self.closest('ul.option-couplet');

                var col = list.find('li').length,
                    row = wrap.data('count');


                var template = _.template( $('#tmpl-sc-category-item').html() );
                self.closest('li').after(template({'row': row, 'col': col}));
            },

            removeItem: function (e) {
                e.preventDefault();

                var options = $(this).parents('ul').find('li');

                // don't remove if only one option is there
                if ( options.length > 1 ) {
                    $(this).parents('li').fadeOut(function() {
                        $(this).remove();
                    });
                }
            },

            save: function() {

                var data = {
                    post_id: $(this).data('id'),
                    data:  $('.woocommerce_attributes').find('input, select, textarea').serialize(),
                    action:  'dokan_save_attributes'
                };

                var this_page = window.location.toString();

                $('#variants-holder').block({ message: null, overlayCSS: { background: '#fff', opacity: 0.6 } });
                $.post(ajaxurl, data, function(resp) {

                    $('#variable_product_options').block({ message: null, overlayCSS: { background: '#fff', opacity: 0.6 } });
                    $('#variable_product_options').load( this_page + ' #variable_product_options_inner', function() {
                        $('#variable_product_options').unblock();
                    } );

                    // fire change events for varaiations
                    $('input.variable_is_downloadable, input.variable_is_virtual, input.variable_manage_stock').trigger('change');

                    $('#variants-holder').unblock();
                });
            },

            initSaleSchedule: function() {
                // Sale price schedule
                $('.sale_price_dates_fields').each(function() {

                    var $these_sale_dates = $(this);
                    var sale_schedule_set = false;
                    var $wrap = $these_sale_dates.closest( 'div, table' );

                    $these_sale_dates.find('input').each(function(){
                        if ( $(this).val() != '' )
                            sale_schedule_set = true;
                    });

                    if ( sale_schedule_set ) {
                        $wrap.find('.sale_schedule').hide();
                        $wrap.find('.sale_price_dates_fields').show();
                    } else {
                        $wrap.find('.sale_schedule').show();
                        $wrap.find('.sale_price_dates_fields').hide();
                    }
                });
            },

            saleSchedule: function() {
                var $wrap = $(this).closest( '.dokan-product-field-content', 'div, table' );

                $(this).hide();
                $wrap.find('.cancel_sale_schedule').show();
                $wrap.find('.sale_price_dates_fields').show();

                return false;
            },

            cancelSchedule: function() {
                var $wrap = $(this).closest( '.dokan-product-field-content', 'div, table' );

                $(this).hide();
                $wrap.find('.sale_schedule').show();
                $wrap.find('.sale_price_dates_fields').hide();
                $wrap.find('.sale_price_dates_fields').find('input').val('');

                return false;
            },

            dates: function() {
                var dates = $( ".sale_price_dates_fields input" ).datepicker({
                    defaultDate: "",
                    dateFormat: "yy-mm-dd",
                    numberOfMonths: 1,
                    onSelect: function( selectedDate ) {
                        var option = $(this).is('#_sale_price_dates_from, .sale_price_dates_from') ? "minDate" : "maxDate";

                        var instance = $( this ).data( "datepicker" ),
                            date = $.datepicker.parseDate(
                                instance.settings.dateFormat ||
                                $.datepicker._defaults.dateFormat,
                                selectedDate, instance.settings );
                        dates.not( this ).datepicker( "option", option, date );
                    }
                });
            },

            onVariantAdded: function() {
                Dokan_Editor.variants.dates();
            }
        },

        gallery: {

            addImages: function(e) {
                e.preventDefault();

                var attachment_ids = $image_gallery_ids.val();

                if ( product_gallery_frame ) {
                    product_gallery_frame.open();
                    return;
                }

                // Create the media frame.
                product_gallery_frame = wp.media.frames.downloadable_file = wp.media({
                    // Set the title of the modal.
                    title: dokan.i18n_choose_gallery,
                    button: {
                        text: dokan.i18n_choose_gallery_btn_text,
                    },
                    multiple: true
                });

                // When an image is selected, run a callback.
                product_gallery_frame.on( 'select', function() {

                    var selection = product_gallery_frame.state().get('selection');

                    selection.map( function( attachment ) {

                        attachment = attachment.toJSON();

                        if ( attachment.id ) {
                            attachment_ids = attachment_ids ? attachment_ids + "," + attachment.id : attachment.id;

                            $product_images.append('\
                                <li class="image" data-attachment_id="' + attachment.id + '">\
                                    <img src="' + attachment.url + '" />\
                                    <a href="#" class="action-delete">&times;</a>\
                                </li>');
                        }

                    } );

                    $image_gallery_ids.val( attachment_ids );
                });

                product_gallery_frame.open();
            },

            deleteImage: function(e) {
                e.preventDefault();

                $(this).closest('li.image').remove();

                var attachment_ids = '';

                $('#product_images_container ul li.image').css('cursor','default').each(function() {
                    var attachment_id = $(this).attr( 'data-attachment_id' );
                    attachment_ids = attachment_ids + attachment_id + ',';
                });

                $image_gallery_ids.val( attachment_ids );

                return false;
            },

            sortable: function() {
                // Image ordering
                $product_images.sortable({
                    items: 'li.image',
                    cursor: 'move',
                    scrollSensitivity:40,
                    forcePlaceholderSize: true,
                    forceHelperSize: false,
                    helper: 'clone',
                    opacity: 0.65,
                    placeholder: 'dokan-sortable-placeholder',
                    start:function(event,ui){
                        ui.item.css('background-color','#f6f6f6');
                    },
                    stop:function(event,ui){
                        ui.item.removeAttr('style');
                    },
                    update: function(event, ui) {
                        var attachment_ids = '';

                        $('#product_images_container ul li.image').css('cursor','default').each(function() {
                            var attachment_id = jQuery(this).attr( 'data-attachment_id' );
                            attachment_ids = attachment_ids + attachment_id + ',';
                        });

                        $image_gallery_ids.val( attachment_ids );
                    }
                });
            }
        },

        featuredImage: {

            addImage: function(e) {
                e.preventDefault();

                var self = $(this);

                if ( product_featured_frame ) {
                    product_featured_frame.open();
                    return;
                }

                product_featured_frame = wp.media({
                    // Set the title of the modal.
                    title: dokan.i18n_choose_featured_img,
                    button: {
                        text: dokan.i18n_choose_featured_img_btn_text,
                    }
                });

                product_featured_frame.on('select', function() {
                    var selection = product_featured_frame.state().get('selection');

                    selection.map( function( attachment ) {
                        attachment = attachment.toJSON();

                        // set the image hidden id
                        self.siblings('input.dokan-feat-image-id').val(attachment.id);

                        // set the image
                        var instruction = self.closest('.instruction-inside');
                        var wrap = instruction.siblings('.image-wrap');

                        // wrap.find('img').attr('src', attachment.sizes.thumbnail.url);
                        wrap.find('img').attr('src', attachment.url);

                        instruction.addClass('dokan-hide');
                        wrap.removeClass('dokan-hide');
                    });
                });

                product_featured_frame.open();
            },

            removeImage: function(e) {
                e.preventDefault();

                var self = $(this);
                var wrap = self.closest('.image-wrap');
                var instruction = wrap.siblings('.instruction-inside');

                instruction.find('input.dokan-feat-image-id').val('0');
                wrap.addClass('dokan-hide');
                instruction.removeClass('dokan-hide');
            }
        },

        fileDownloadable: function(e) {
            e.preventDefault();

            var self = $(this),
                downloadable_frame;

            if ( downloadable_frame ) {
                downloadable_frame.open();
                return;
            }

            downloadable_frame = wp.media({
                title: dokan.i18n_choose_file,
                button: {
                    text: dokan.i18n_choose_file_btn_text,
                },
                multiple: true
            });

            downloadable_frame.on('select', function() {
                var selection = downloadable_frame.state().get('selection');

                selection.map( function( attachment ) {
                    attachment = attachment.toJSON();

                    self.closest('tr').find( 'input.wc_file_url, input.wc_variation_file_url').val(attachment.url);
                });
            });

            downloadable_frame.on( 'ready', function() {
                downloadable_frame.uploader.options.uploader.params = {
                    type: 'downloadable_product'
                };
            });

            downloadable_frame.open();
        },

        sidebarToggle: {
            showStatus: function(e) {
                var container = $(this).siblings('.dokan-toggle-select-container');

                if (container.is(':hidden')) {
                    container.slideDown('fast');

                    $(this).hide();
                }

                return false;
            },

            saveStatus: function(e) {
                var container = $(this).closest('.dokan-toggle-select-container');

                container.slideUp('fast');
                container.siblings('a.dokan-toggle-edit').show();

                // update the text
                var text = $('option:selected', container.find('select.dokan-toggle-select')).text();
                container.siblings('.dokan-toggle-selected-display').html(text);

                return false;
            },

            cancel: function(e) {
                var container = $(this).closest('.dokan-toggle-select-container');

                container.slideUp('fast');
                container.siblings('a.dokan-toggle-edit').show();

                return false;
            }
        },

        shipping: {
            disableOverride: function() {
                if ( $('#_disable_shipping').is(':checked') ) {
                    $('.hide_if_disable').hide();
                } else {
                    $('.hide_if_disable').show();
                    Dokan_Editor.editProduct.shipping.showHideOverride();
                }
            }
        }
    };

    // On DOM ready
    $(function() {
        Dokan_Editor.init();

        // PRODUCT TYPE SPECIFIC OPTIONS.
        $( 'select#product_type' ).change( function() {
            // Get value.
            var select_val = $( this ).val();

            if ( 'variable' === select_val ) {
                $( 'input#_manage_stock' ).change();
                $( 'input#_downloadable' ).prop( 'checked', false );
                $( 'input#_virtual' ).removeAttr( 'checked' );
            }

            show_and_hide_panels();

            $( document.body ).trigger( 'dokan-product-type-change', select_val, $( this ) );

        }).change();

        $('.product-edit-container').on('change', 'input#_downloadable, input#_virtual', function() {
            show_and_hide_panels();
        });

        function show_and_hide_panels() {
            var product_type    = $( 'select#product_type' ).val();
            var is_virtual      = $( 'input#_virtual:checked' ).length;
            var is_downloadable = $( 'input#_downloadable:checked' ).length;

            // Hide/Show all with rules.
            var hide_classes = '.hide_if_downloadable, .hide_if_virtual';
            var show_classes = '.show_if_downloadable, .show_if_virtual';

            $.each( [ 'simple', 'variable' ], function( index, value ) {
                hide_classes = hide_classes + ', .hide_if_' + value;
                show_classes = show_classes + ', .show_if_' + value;
            });

            $( hide_classes ).show();
            $( show_classes ).hide();

            // Shows rules.
            if ( is_downloadable ) {
                $( '.show_if_downloadable' ).show();
            }
            if ( is_virtual ) {
                $( '.show_if_virtual' ).show();
            }

            $( '.show_if_' + product_type ).show();

            // Hide rules.
            if ( is_downloadable ) {
                $( '.hide_if_downloadable' ).hide();
            }
            if ( is_virtual ) {
                $( '.hide_if_virtual' ).hide();
            }

            $( '.hide_if_' + product_type ).hide();

            $( 'input#_manage_stock' ).change();
        }

        // Sale price schedule.
        $( '.sale_price_dates_fields' ).each( function() {
            var $these_sale_dates = $( this );
            var sale_schedule_set = false;
            var $wrap = $these_sale_dates.closest( 'div, table' );

            $these_sale_dates.find( 'input' ).each( function() {
                if ( '' !== $( this ).val() ) {
                    sale_schedule_set = true;
                }
            });

            if ( sale_schedule_set ) {
                $wrap.find( '.sale_schedule' ).hide();
                $wrap.find( '.sale_price_dates_fields' ).show();
            } else {
                $wrap.find( '.sale_schedule' ).show();
                $wrap.find( '.sale_price_dates_fields' ).hide();
            }
        });

        $( '.product-edit-container' ).on( 'click', '.sale_schedule', function() {
            var $wrap = $( '.product-edit-container, div, table' );

            $( this ).hide();
            $wrap.find( '.cancel_sale_schedule' ).show();
            $wrap.find( '.sale_price_dates_fields' ).show();

            return false;
        });
        $( '.product-edit-container' ).on( 'click', '.cancel_sale_schedule', function() {
            var $wrap = $( '.product-edit-container, div, table' );

            $( this ).hide();
            $wrap.find( '.sale_schedule' ).show();
            $wrap.find( '.sale_price_dates_fields' ).hide();
            $wrap.find( '.sale_price_dates_fields' ).find( 'input' ).val('');

            return false;
        });
    });

})(jQuery);
/* global wp, dokan, dokan_refund, accounting */
jQuery( function( $ ) {

    /**
     * Variations actions
     */
    var Dokan_Product_Variation_Actions = {

        /**
         * Initialize variations actions
         */
        init: function() {
            $( '#dokan-variable-product-options' )
                .on( 'change', 'input.variable_is_downloadable', this.variable_is_downloadable )
                .on( 'change', 'input.variable_is_virtual', this.variable_is_virtual )
                .on( 'change', 'input.variable_manage_stock', this.variable_manage_stock )
                .on( 'click', '.expand_all', this.expand_all )
                .on( 'click', '.close_all', this.close_all )
                // .on( 'click', 'button.notice-dismiss', this.notice_dismiss )
                .on( 'click', 'h3 .sort', this.set_menu_order )
                .on( 'reload', this.reload );

            $( 'input.variable_is_downloadable, input.variable_is_virtual, input.variable_manage_stock' ).change();
            $( '.dokan-product-variation-wrapper' ).on( 'dokan_variations_loaded', this.variations_loaded );
            $( document.body ).on( 'dokan_variations_added', this.variation_added );
        },

        /**
         * Reload UI
         *
         * @param {Object} event
         * @param {Int} qty
         */
        reload: function() {
            Dokan_Product_Variation_Ajax.load_variations( 1 );
            Dokan_Product_Variation_PageNav.set_paginav( 0 );
        },

        /**
         * Check if variation is downloadable and show/hide elements
         */
        variable_is_downloadable: function() {
            $( this ).closest( '.dokan-product-variation-itmes' ).find( '.show_if_variation_downloadable' ).hide();

            if ( $( this ).is( ':checked' ) ) {
                $( this ).closest( '.dokan-product-variation-itmes' ).find( '.show_if_variation_downloadable' ).show();
            }
        },

        /**
         * Check if variation is virtual and show/hide elements
         */
        variable_is_virtual: function() {
            $( this ).closest( '.dokan-product-variation-itmes' ).find( '.hide_if_variation_virtual' ).show();

            if ( $( this ).is( ':checked' ) ) {
                $( this ).closest( '.dokan-product-variation-itmes' ).find( '.hide_if_variation_virtual' ).hide();
            }
        },

        /**
         * Check if variation manage stock and show/hide elements
         */
        variable_manage_stock: function() {
            $( this ).closest( '.dokan-product-variation-itmes' ).find( '.show_if_variation_manage_stock' ).hide();

            if ( $( this ).is( ':checked' ) ) {
                $( this ).closest( '.dokan-product-variation-itmes' ).find( '.show_if_variation_manage_stock' ).show();
            }
        },

        expand_all: function(e) {
            $(this).closest( '#dokan-variable-product-options-inner' ).find( '.dokan-product-variation-itmes > .dokan-variable-attributes' ).show();
            return false;
        },

        close_all: function(e) {
            $(this).closest( '#dokan-variable-product-options-inner' ).find( '.dokan-product-variation-itmes > .dokan-variable-attributes' ).hide();
            return false;
        },

        /**
         * Notice dismiss
         */
        // notice_dismiss: function() {
        //     $( this ).closest( 'div.notice' ).remove();
        // },

        /**
         * Run actions when variations is loaded
         *
         * @param {Object} event
         * @param {Int} needsUpdate
         */
        variations_loaded: function( event, needsUpdate ) {
            needsUpdate = needsUpdate || false;

            var wrapper = $( '.dokan-product-variation-wrapper' );

            if ( ! needsUpdate ) {
                // Show/hide downloadable, virtual and stock fields
                $( 'input.variable_is_downloadable, input.variable_is_virtual, input.variable_manage_stock', wrapper ).change();

                // Open sale schedule fields when have some sale price date
                $( '.dokan-product-variation-itmes', wrapper ).each( function( index, el ) {
                    var $el       = $( el ),
                        date_from = $( '.sale_price_dates_from', $el ).val(),
                        date_to   = $( '.sale_price_dates_to', $el ).val();

                    if ( '' !== date_from || '' !== date_to ) {
                        $( 'a.sale_schedule', $el ).click();
                    }
                });

                // Remove variation-needs-update classes
                $( '.dokan-variations-container .variation-needs-update', wrapper ).removeClass( 'variation-needs-update' );

                // Disable cancel and save buttons
                $( 'button.cancel-variation-changes, button.save-variation-changes', wrapper ).attr( 'disabled', 'disabled' );
            }

            $( '.toggle-variation-content', wrapper ).on( 'click', function(e) {
                e.preventDefault();

                var self = $('.toggle-variation-content');

                self.closest('.dokan-product-variation-itmes').find('.dokan-variable-attributes').slideToggle( 300, function() {
                    if ( $(this).is( ':visible' ) ) {
                        self.removeClass( 'fa-flip-horizointal' ).addClass( 'fa-flip-vertical' );
                    } else {
                        self.removeClass( 'fa-flip-vertical' ).addClass( 'fa-flip-horizointal' );
                    }
                } );
            } );

            $('.tips').tooltip();

            // Datepicker fields
            $( '.sale_price_dates_fields', wrapper ).each( function() {
                var dates = $( this ).find( 'input' ).datepicker({
                    defaultDate:     '',
                    dateFormat:      'yy-mm-dd',
                    numberOfMonths:  1,
                    showButtonPanel: true,
                    onSelect:        function( selectedDate ) {
                        var option   = $( this ).is( '.sale_price_dates_from' ) ? 'minDate' : 'maxDate',
                            instance = $( this ).data( 'datepicker' ),
                            date     = $.datepicker.parseDate( instance.settings.dateFormat || $.datepicker._defaults.dateFormat, selectedDate, instance.settings );

                        dates.not( this ).datepicker( 'option', option, date );
                        $( this ).change();
                    }
                });
            });

            // Allow sorting
            $( '.dokan-variations-container', wrapper ).sortable({
                items:                '.dokan-product-variation-itmes',
                cursor:               'move',
                axis:                 'y',
                handle:               '.sort',
                scrollSensitivity:    40,
                forcePlaceholderSize: true,
                helper:               'clone',
                opacity:              0.65,
                stop:                 function() {
                    Dokan_Product_Variation_Actions.variation_row_indexes();
                }
            });
        },

        /**
         * Run actions when added a variation
         *
         * @param {Object} event
         * @param {Int} qty
         */
        variation_added: function( event, qty ) {
            if ( 1 === qty ) {
                Dokan_Product_Variation_Actions.variations_loaded( null, true );
            }
        },

        /**
         * Lets the user manually input menu order to move items around pages
         */
        set_menu_order: function( event ) {
            event.preventDefault();
            var $menu_order  = $( this ).closest( '.dokan-product-variation-itmes' ).find('.variation_menu_order');
            var value        = window.prompt( dokan.i18n_enter_menu_order, $menu_order.val() );

            if ( value != null ) {
                // Set value, save changes and reload view
                $menu_order.val( parseInt( value, 10 ) ).change();
                Dokan_Product_Variation_Ajax.save_variations();
            }
        },

        /**
         * Set menu order
         */
        variation_row_indexes: function() {
            var wrapper      = $( '#dokan-variable-product-options' ).find( '.dokan-variations-container' ),
                current_page = parseInt( wrapper.attr( 'data-page' ), 10 ),
                offset       = parseInt( ( current_page - 1 ) * dokan.variations_per_page, 10 );

            $( '.dokan-variations-container .dokan-product-variation-itmes' ).each( function ( index, el ) {
                $( '.variation_menu_order', el ).val( parseInt( $( el ).index( '.dokan-variations-container .dokan-product-variation-itmes' ), 10 ) + 1 + offset ).change();
            });
        }
    };

    /**
     * Product variations metabox ajax methods
     */
    var Dokan_Product_Variation_Ajax = {

        /**
         * Initialize variations ajax methods
         */
        init: function() {
            this.load_variations();
            this.initial_load();

            $( '#dokan-variable-product-options' )
                .on( 'click', 'button.save-variation-changes', this.save_variations )
                .on( 'click', 'button.cancel-variation-changes', this.cancel_variations )
                .on( 'click', '.remove_variation', this.remove_variation );

            $( document.body )
                .on( 'change', '#dokan-variable-product-options .dokan-variations-container :input', this.input_changed )
                .on( 'change', '.dokan-variations-defaults select', this.defaults_changed );

            $( 'form.dokan-product-edit-form' ).on( 'submit', this.save_on_submit );

            $( '#dokan-variable-product-options' ).on( 'click', 'a.do_variation_action', this.do_variation_action );
        },

        /**
         * Check if have some changes before leave the page
         *
         * @return {Bool}
         */
        check_for_changes: function() {
            var need_update = $( '#dokan-variable-product-options' ).find( '.dokan-variations-container .variation-needs-update' );

            if ( 0 < need_update.length ) {
                if ( window.confirm( dokan.i18n_edited_variations ) ) {
                    Dokan_Product_Variation_Ajax.save_changes();
                } else {
                    need_update.removeClass( 'variation-needs-update' );
                    return false;
                }
            }

            return true;
        },

        /**
         * Block edit screen
         */
        block: function() {
            $( '.dokan-product-variation-wrapper' ).block({
                message: null,
                fadeIn: 100,
                fadeOut: 2000,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });
        },

        /**
         * Unblock edit screen
         */
        unblock: function() {
            $( '.dokan-product-variation-wrapper' ).unblock();
        },

        /**
         * Initial load variations
         *
         * @return {Bool}
         */
        initial_load: function() {
            if ( 0 === $( '#dokan-variable-product-options' ).find( '.dokan-variations-container .dokan-product-variation-itmes' ).length ) {
                Dokan_Product_Variation_PageNav.go_to_page();
            }
        },

        /**
         * Load variations via Ajax
         *
         * @param {Int} page (default: 1)
         * @param {Int} per_page (default: 10)
         */
        load_variations: function( page, per_page ) {
            page     = page || 1;
            per_page = per_page || dokan.variations_per_page;

            var wrapper = $( '#dokan-variable-product-options' ).find( '.dokan-variations-container' );

            Dokan_Product_Variation_Ajax.block();

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
                    wrapper.empty().append( response ).attr( 'data-page', page );

                    $( '.dokan-product-variation-wrapper' ).trigger( 'dokan_variations_loaded' );

                    Dokan_Product_Variation_Ajax.unblock();
                }
            });
        },

        /**
         * Ger variations fields and convert to object
         *
         * @param  {Object} fields
         *
         * @return {Object}
         */
        get_variations_fields: function( fields ) {
            var data = $( ':input', fields ).serializeJSON();

            $( '.dokan-variations-defaults select' ).each( function( index, element ) {
                var select = $( element );
                data[ select.attr( 'name' ) ] = select.val();
            });

            return data;
        },

        /**
         * Save variations changes
         *
         * @param {Function} callback Called once saving is complete
         */
        save_changes: function( callback ) {
            var wrapper     = $( '#dokan-variable-product-options' ).find( '.dokan-variations-container' ),
                need_update = $( '.variation-needs-update', wrapper ),
                data        = {};

            // Save only with products need update.
            if ( 0 < need_update.length ) {
                Dokan_Product_Variation_Ajax.block();

                data                 = Dokan_Product_Variation_Ajax.get_variations_fields( need_update );
                data.action          = 'dokan_save_variations';
                data.security        = dokan.save_variations_nonce;
                data.product_id      = $( '#dokan-edit-product-id' ).val();
                data['product-type'] = $( '#product_type' ).val();

                $.ajax({
                    url: dokan.ajaxurl,
                    data: data,
                    type: 'POST',
                    success: function( response ) {
                        // Allow change page, delete and add new variations
                        need_update.removeClass( 'variation-needs-update' );
                        $( 'button.cancel-variation-changes, button.save-variation-changes' ).attr( 'disabled', 'disabled' );

                        $( '.dokan-product-variation-wrapper' ).trigger( 'dokan_variations_saved' );

                        if ( typeof callback === 'function' ) {
                            callback( response );
                        }

                        Dokan_Product_Variation_Ajax.unblock();
                    }
                });
            }
        },

        /**
         * Save variations
         *
         * @return {Bool}
         */
        save_variations: function() {
            $( '#dokan-variable-product-options' ).trigger( 'dokan_variations_save_variations_button' );

            Dokan_Product_Variation_Ajax.save_changes( function( error ) {
                var wrapper = $( '#dokan-variable-product-options' ).find( '.dokan-variations-container' ),
                    current = wrapper.attr( 'data-page' );

                $( '#dokan-variable-product-options' ).find( '#dokan_errors' ).remove();

                if ( error ) {
                    wrapper.before( error );
                }

                $( '.dokan-variations-defaults select' ).each( function() {
                    $( this ).attr( 'data-current', $( this ).val() );
                });

                Dokan_Product_Variation_PageNav.go_to_page( current );
            });

            return false;
        },

        /**
         * Save on post form submit
         */
        save_on_submit: function( e ) {
            var need_update = $( '#dokan-variable-product-options' ).find( '.dokan-variations-container .variation-needs-update' );

            if ( 0 < need_update.length ) {
                e.preventDefault();
                $( '#dokan-variable-product-options' ).trigger( 'dokan_variations_save_variations_on_submit' );
                Dokan_Product_Variation_Ajax.save_changes( Dokan_Product_Variation_Ajax.save_on_submit_done );
            }
        },

        /**
         * After saved, continue with form submission
         */
        save_on_submit_done: function() {
            $( 'form.dokan-product-edit-form' ).submit();
        },

        /**
         * Discart changes.
         *
         * @return {Bool}
         */
        cancel_variations: function() {
            var current = parseInt( $( '#dokan-variable-product-options' ).find( '.dokan-variations-container' ).attr( 'data-page' ), 10 );

            $( '#dokan-variable-product-options' ).find( '.dokan-variations-container .variation-needs-update' ).removeClass( 'variation-needs-update' );
            $( '.dokan-variations-defaults select' ).each( function() {
                $( this ).val( $( this ).attr( 'data-current' ) );
            });

            Dokan_Product_Variation_PageNav.go_to_page( current );

            return false;
        },

        /**
         * Add variation
         *
         * @return {Bool}
         */
        add_variation: function() {
            Dokan_Product_Variation_Ajax.block();

            var data = {
                action: 'dokan_add_variation',
                post_id: $( '#dokan-edit-product-id' ).val(),
                loop: $( '.dokan-product-variation-itmes' ).length,
                security: dokan.add_variation_nonce
            };

            $.post( dokan.ajaxurl, data, function( response ) {
                var variation = $( response );
                variation.addClass( 'variation-needs-update' );

                $( '#dokan-variable-product-options' ).find( '.dokan-variations-container' ).prepend( variation );
                $( 'button.cancel-variation-changes, button.save-variation-changes' ).removeAttr( 'disabled' );
                $( '.dokan-product-variation-wrapper' ).trigger( 'dokan_variations_added', 1 );
                Dokan_Product_Variation_Ajax.unblock();
            });

            return false;
        },

        /**
         * Remove variation
         *
         * @return {Bool}
         */
        remove_variation: function() {
            Dokan_Product_Variation_Ajax.check_for_changes();

            if ( window.confirm( dokan.i18n_remove_variation ) ) {
                var variation     = $( this ).attr( 'rel' ),
                    variation_ids = [],
                    data          = {
                        action: 'dokan_remove_variation'
                    };

                Dokan_Product_Variation_Ajax.block();

                if ( 0 < variation ) {
                    variation_ids.push( variation );

                    data.variation_ids = variation_ids;
                    data.security      = dokan.delete_variations_nonce;

                    $.post( dokan.ajaxurl, data, function() {
                        var wrapper      = $( '#dokan-variable-product-options' ).find( '.dokan-variations-container' ),
                            current_page = parseInt( wrapper.attr( 'data-page' ), 10 ),
                            total_pages  = Math.ceil( ( parseInt( wrapper.attr( 'data-total' ), 10 ) - 1 ) / dokan.variations_per_page ),
                            page         = 1;

                        $( '.dokan-product-variation-wrapper' ).trigger( 'dokan_variations_removed' );

                        if ( current_page === total_pages || current_page <= total_pages ) {
                            page = current_page;
                        } else if ( current_page > total_pages && 0 !== total_pages ) {
                            page = total_pages;
                        }

                        Dokan_Product_Variation_PageNav.go_to_page( page, -1 );
                    });

                } else {
                    Dokan_Product_Variation_Ajax.unblock();
                }
            }

            return false;
        },

        /**
         * Link all variations (or at least try :p)
         *
         * @return {Bool}
         */
        link_all_variations: function() {
            Dokan_Product_Variation_Ajax.check_for_changes();

            if ( window.confirm( dokan.i18n_link_all_variations ) ) {
                Dokan_Product_Variation_Ajax.block();

                var data = {
                    action: 'dokan_link_all_variations',
                    post_id: $('#dokan-edit-product-id').val(),
                    security: dokan.link_variation_nonce
                };

                $.post( dokan.ajaxurl, data, function( response ) {
                    var count = parseInt( response, 10 );

                    if ( 1 === count ) {
                        window.alert( count + ' ' + dokan.i18n_variation_added );
                    } else if ( 0 === count || count > 1 ) {
                        window.alert( count + ' ' + dokan.i18n_variations_added );
                    } else {
                        window.alert( dokan.i18n_no_variations_added );
                    }

                    if ( count > 0 ) {
                        Dokan_Product_Variation_PageNav.go_to_page( 1, count );
                        $( '.dokan-product-variation-wrapper' ).trigger( 'dokan_variations_added', count );
                    } else {
                        Dokan_Product_Variation_Ajax.unblock();
                    }
                });
            }

            return false;
        },

        /**
         * Add new class when have changes in some input
         */
        input_changed: function() {
            $( this )
                .closest( '.dokan-product-variation-itmes' )
                .addClass( 'variation-needs-update' );

            $( 'button.cancel-variation-changes, button.save-variation-changes' ).removeAttr( 'disabled' );

            $( '.dokan-product-variation-wrapper' ).trigger( 'dokan_variations_input_changed' );
        },

        /**
         * Added new .variation-needs-update class when defaults is changed
         */
        defaults_changed: function() {
            $( this )
                .closest( '#dokan-variable-product-options' )
                .find( '.dokan-product-variation-itmes:first' )
                .addClass( 'variation-needs-update' );

            $( 'button.cancel-variation-changes, button.save-variation-changes' ).removeAttr( 'disabled' );

            $( '#dokan-variable-product-options' ).trigger( 'dokan_variations_defaults_changed' );
        },

        /**
         * Actions
         */
        do_variation_action: function() {
            var do_variation_action = $( 'select.variation-actions' ).val(),
                data       = {},
                changes    = 0,
                value;

            switch ( do_variation_action ) {
                case 'add_variation' :
                    Dokan_Product_Variation_Ajax.add_variation();
                    return;
                case 'link_all_variations' :
                    Dokan_Product_Variation_Ajax.link_all_variations();
                    return;
                case 'delete_all' :
                    if ( window.confirm( dokan.i18n_delete_all_variations ) ) {
                        if ( window.confirm( dokan.i18n_last_warning ) ) {
                            data.allowed = true;
                            changes      = parseInt( $( '#dokan-variable-product-options' ).find( '.dokan-variations-container' ).attr( 'data-total' ), 10 ) * -1;
                        }
                    }
                    break;
                case 'variable_regular_price_increase' :
                case 'variable_regular_price_decrease' :
                case 'variable_sale_price_increase' :
                case 'variable_sale_price_decrease' :
                    value = window.prompt( dokan.i18n_enter_a_value_fixed_or_percent );

                    if ( value != null ) {
                        if ( value.indexOf( '%' ) >= 0 ) {
                            data.value = accounting.unformat( value.replace( /\%/, '' ), dokan_refund.mon_decimal_point ) + '%';
                        } else {
                            data.value = accounting.unformat( value, dokan_refund.mon_decimal_point );
                        }
                    }
                    break;
                case 'variable_regular_price' :
                case 'variable_sale_price' :
                case 'variable_stock' :
                case 'variable_weight' :
                case 'variable_length' :
                case 'variable_width' :
                case 'variable_height' :
                case 'variable_download_limit' :
                case 'variable_download_expiry' :
                    value = window.prompt( dokan.i18n_enter_a_value );

                    if ( value != null ) {
                        data.value = value;
                    }
                    break;
                case 'variable_sale_schedule' :
                    data.date_from = window.prompt( dokan.i18n_scheduled_sale_start );
                    data.date_to   = window.prompt( dokan.i18n_scheduled_sale_end );

                    if ( null === data.date_from ) {
                        data.date_from = false;
                    }

                    if ( null === data.date_to ) {
                        data.date_to = false;
                    }
                    break;
                default :
                    $( 'select.variation-actions' ).trigger( do_variation_action );
                    data = $( 'select.variation-actions' ).triggerHandler( do_variation_action + '_ajax_data', data );
                    break;
            }

            if ( 'delete_all' === do_variation_action && data.allowed ) {
                $( '#dokan-variable-product-options' ).find( '.variation-needs-update' ).removeClass( 'variation-needs-update' );
            } else {
                Dokan_Product_Variation_Ajax.check_for_changes();
            }

            Dokan_Product_Variation_Ajax.block();

            $.ajax({
                url: dokan.ajaxurl,
                data: {
                    action:       'dokan_bulk_edit_variations',
                    security:     dokan.bulk_edit_variations_nonce,
                    product_id:   $( '#dokan-edit-product-id' ).val(),
                    product_type: $( '#product_type' ).val(),
                    bulk_action:  do_variation_action,
                    data:         data
                },
                type: 'POST',
                success: function() {
                    Dokan_Product_Variation_PageNav.go_to_page( 1, changes );
                }
            });
        }
    };

    /**
     * Product variations pagenav
     */
    var Dokan_Product_Variation_PageNav = {

        /**
         * Initialize products variations meta box
         */
        init: function() {
            $( document.body )
                .on( 'dokan_variations_added', this.update_single_quantity )
                .on( 'change', '.dokan-variations-pagenav .page-selector', this.page_selector )
                .on( 'click', '.dokan-variations-pagenav .first-page', this.first_page )
                .on( 'click', '.dokan-variations-pagenav .prev-page', this.prev_page )
                .on( 'click', '.dokan-variations-pagenav .next-page', this.next_page )
                .on( 'click', '.dokan-variations-pagenav .last-page', this.last_page );
        },

        /**
         * Set variations count
         *
         * @param {Int} qty
         *
         * @return {Int}
         */
        update_variations_count: function( qty ) {
            var wrapper        = $( '#dokan-variable-product-options' ).find( '.dokan-variations-container' ),
                total          = parseInt( wrapper.attr( 'data-total' ), 10 ) + qty,
                displaying_num = $( '.dokan-variations-pagenav .displaying-num' );

            // Set the new total of variations
            wrapper.attr( 'data-total', total );

            if ( 1 === total ) {
                displaying_num.text( dokan.i18n_variation_count_single.replace( '%qty%', total ) );
            } else {
                displaying_num.text( dokan.i18n_variation_count_plural.replace( '%qty%', total ) );
            }

            return total;
        },

        /**
         * Update variations quantity when add a new variation
         *
         * @param {Object} event
         * @param {Int} qty
         */
        update_single_quantity: function( event, qty ) {
            if ( 1 === qty ) {
                var page_nav = $( '.dokan-variations-pagenav' );

                Dokan_Product_Variation_PageNav.update_variations_count( qty );

                if ( page_nav.is( ':hidden' ) ) {
                    $( 'option, optgroup', 'select.variation-actions' ).show();
                    $( 'select.variation-actions' ).val( 'add_variation' );
                    $( '#dokan-variable-product-options' ).find( '.dokan-variation-action-toolbar' ).show();
                    page_nav.show();
                    $( '.pagination-links', page_nav ).hide();
                }
            }
        },

        /**
         * Set the pagenav fields
         *
         * @param {Int} qty
         */
        set_paginav: function( qty ) {
            var wrapper          = $( '#dokan-variable-product-options' ).find( '.dokan-variations-container' ),
                new_qty          = Dokan_Product_Variation_PageNav.update_variations_count( qty ),
                toolbar          = $( '#dokan-variable-product-options' ).find( '.dokan-variation-action-toolbar' ),
                variation_action = $( 'select.variation-actions' ),
                page_nav         = $( '.dokan-variations-pagenav' ),
                displaying_links = $( '.pagination-links', page_nav ),
                total_pages      = Math.ceil( new_qty / dokan.variations_per_page ),
                options          = '';

            // Set the new total of pages
            wrapper.attr( 'data-total_pages', total_pages );

            $( '.total-pages', page_nav ).text( total_pages );

            // Set the new pagenav options
            for ( var i = 1; i <= total_pages; i++ ) {
                options += '<option value="' + i + '">' + i + '</option>';
            }

            $( '.page-selector', page_nav ).empty().html( options );

            // Show/hide pagenav
            if ( 0 === new_qty ) {
                toolbar.not( '.toolbar-top, .toolbar-buttons' ).hide();
                page_nav.hide();
                $( 'option, optgroup', variation_action ).hide();
                $( 'select.variation-actions' ).val( 'add_variation' );
                $( 'option[data-global="true"]', variation_action ).show();

            } else {
                toolbar.show();
                page_nav.show();
                $( 'option, optgroup', variation_action ).show();
                $( 'select.variation-actions' ).val( 'add_variation' );

                // Show/hide links
                if ( 1 === total_pages ) {
                    displaying_links.hide();
                } else {
                    displaying_links.show();
                }
            }
        },

        /**
         * Check button if enabled and if don't have changes
         *
         * @return {Bool}
         */
        check_is_enabled: function( current ) {
            return ! $( current ).hasClass( 'disabled' );
        },

        /**
         * Change "disabled" class on pagenav
         */
        change_classes: function( selected, total ) {
            var first_page = $( '.dokan-variations-pagenav .first-page' ),
                prev_page  = $( '.dokan-variations-pagenav .prev-page' ),
                next_page  = $( '.dokan-variations-pagenav .next-page' ),
                last_page  = $( '.dokan-variations-pagenav .last-page' );

            if ( 1 === selected ) {
                first_page.addClass( 'disabled' );
                prev_page.addClass( 'disabled' );
            } else {
                first_page.removeClass( 'disabled' );
                prev_page.removeClass( 'disabled' );
            }

            if ( total === selected ) {
                next_page.addClass( 'disabled' );
                last_page.addClass( 'disabled' );
            } else {
                next_page.removeClass( 'disabled' );
                last_page.removeClass( 'disabled' );
            }
        },

        /**
         * Set page
         */
        set_page: function( page ) {
            $( '.dokan-variations-pagenav .page-selector' ).val( page ).first().change();
        },

        /**
         * Navigate on variations pages
         *
         * @param {Int} page
         * @param {Int} qty
         */
        go_to_page: function( page, qty ) {
            page = page || 1;
            qty  = qty || 0;

            Dokan_Product_Variation_PageNav.set_paginav( qty );
            Dokan_Product_Variation_PageNav.set_page( page );
        },

        /**
         * Paginav pagination selector
         */
        page_selector: function() {
            var selected = parseInt( $( this ).val(), 10 ),
                wrapper  = $( '#dokan-variable-product-options' ).find( '.dokan-variations-container' );

            $( '.dokan-variations-pagenav .page-selector' ).val( selected );

            Dokan_Product_Variation_Ajax.check_for_changes();
            Dokan_Product_Variation_PageNav.change_classes( selected, parseInt( wrapper.attr( 'data-total_pages' ), 10 ) );
            Dokan_Product_Variation_Ajax.load_variations( selected );
        },

        /**
         * Go to first page
         *
         * @return {Bool}
         */
        first_page: function() {
            if ( Dokan_Product_Variation_PageNav.check_is_enabled( this ) ) {
                Dokan_Product_Variation_PageNav.set_page( 1 );
            }

            return false;
        },

        /**
         * Go to previous page
         *
         * @return {Bool}
         */
        prev_page: function() {
            if ( Dokan_Product_Variation_PageNav.check_is_enabled( this ) ) {
                var wrapper   = $( '#dokan-variable-product-options' ).find( '.dokan-variations-container' ),
                    prev_page = parseInt( wrapper.attr( 'data-page' ), 10 ) - 1,
                    new_page  = ( 0 < prev_page ) ? prev_page : 1;

                Dokan_Product_Variation_PageNav.set_page( new_page );
            }

            return false;
        },

        /**
         * Go to next page
         *
         * @return {Bool}
         */
        next_page: function() {
            if ( Dokan_Product_Variation_PageNav.check_is_enabled( this ) ) {
                var wrapper     = $( '#dokan-variable-product-options' ).find( '.dokan-variations-container' ),
                    total_pages = parseInt( wrapper.attr( 'data-total_pages' ), 10 ),
                    next_page   = parseInt( wrapper.attr( 'data-page' ), 10 ) + 1,
                    new_page    = ( total_pages >= next_page ) ? next_page : total_pages;

                Dokan_Product_Variation_PageNav.set_page( new_page );
            }

            return false;
        },

        /**
         * Go to last page
         *
         * @return {Bool}
         */
        last_page: function() {
            if ( Dokan_Product_Variation_PageNav.check_is_enabled( this ) ) {
                var last_page = $( '#dokan-variable-product-options' ).find( '.dokan-variations-container' ).attr( 'data-total_pages' );

                Dokan_Product_Variation_PageNav.set_page( last_page );
            }

            return false;
        }
    };

    // On DOM ready
    $(function() {
        if ( $( '#dokan-variable-product-options' ).length ) {
            Dokan_Product_Variation_Actions.init();
            Dokan_Product_Variation_Ajax.init();
            Dokan_Product_Variation_PageNav.init();
        }
    });


});
;(function($){

    var Dokan_Comments = {

        init: function() {
            $('#dokan-comments-table').on('click', '.dokan-cmt-action', this.setCommentStatus);
            $('.dokan-check-all').on('click', this.toggleCheckbox);
        },

        toggleCheckbox: function() {
            $(".dokan-check-col").prop('checked', $(this).prop('checked'));
        },

        setCommentStatus: function(e) {
            e.preventDefault();

            var self = $(this),
                comment_id = self.data('comment_id'),
                comment_status = self.data('cmt_status'),
				page_status = self.data('page_status'),
				post_type = self.data('post_type'),
				curr_page = self.data('curr_page'),
                tr = self.closest('tr'),
                data = {
                    'action': 'dokan_comment_status',
                    'comment_id': comment_id,
                    'comment_status': comment_status,
					'page_status': page_status,
					'post_type': post_type,
					'curr_page': curr_page,
					'nonce': dokan.nonce
                };


            $.post(dokan.ajaxurl, data, function(resp){

                if(page_status === 1) {
                    if ( comment_status === 1 || comment_status === 0) {
                        tr.fadeOut(function() {
                            tr.replaceWith(resp.data['content']).fadeIn();
                        });

                    } else {
                        tr.fadeOut(function() {
                            $(this).remove();
                        });
                    }
                } else {
                    tr.fadeOut(function() {
                        $(this).remove();
                    });
                }

                if(resp.data['pending'] == null) resp.data['pending'] = 0;
                if(resp.data['spam'] == null) resp.data['spam'] = 0;
                if(resp.data['trash'] == null) resp.data['trash'] = 0;
				if(resp.data['approved'] == null) resp.data['approved'] = 0;

                $('.comments-menu-approved').text(resp.data['approved']);
                $('.comments-menu-pending').text(resp.data['pending']);
                $('.comments-menu-spam').text(resp.data['spam']);
				$('.comments-menu-trash').text(resp.data['trash']);
            });
        }

    };

    $(function(){
        Dokan_Comments.init();
    });

})(jQuery);
jQuery(function($) {
    var api = wp.customize;

    $('.datepicker').datepicker({
        dateFormat: 'yy-mm-dd'
    });

    $('.tips').tooltip();

    // // set dashboard menu height
    // var dashboardMenu = $('ul.dokan-dashboard-menu'),
    //     contentArea = $('.dokan-dashboard-content');

    // if ( $(window).width() > 767) {
    //     if ( contentArea.height() > dashboardMenu.height() ) {
    //         dashboardMenu.css({ height: contentArea.height() });
    //     }
    // }

    function showTooltip(x, y, contents) {
        jQuery('<div class="chart-tooltip">' + contents + '</div>').css({
            top: y - 16,
            left: x + 20
        }).appendTo("body").fadeIn(200);
    }

    var prev_data_index = null;
    var prev_series_index = null;

    jQuery(".chart-placeholder").bind("plothover", function(event, pos, item) {
        if (item) {
            if (prev_data_index != item.dataIndex || prev_series_index != item.seriesIndex) {
                prev_data_index = item.dataIndex;
                prev_series_index = item.seriesIndex;

                jQuery(".chart-tooltip").remove();

                if (item.series.points.show || item.series.enable_tooltip) {

                    var y = item.series.data[item.dataIndex][1];

                    tooltip_content = '';

                    if (item.series.prepend_label)
                        tooltip_content = tooltip_content + item.series.label + ": ";

                    if (item.series.prepend_tooltip)
                        tooltip_content = tooltip_content + item.series.prepend_tooltip;

                    tooltip_content = tooltip_content + y;

                    if (item.series.append_tooltip)
                        tooltip_content = tooltip_content + item.series.append_tooltip;

                    if (item.series.pie.show) {

                        showTooltip(pos.pageX, pos.pageY, tooltip_content);

                    } else {

                        showTooltip(item.pageX, item.pageY, tooltip_content);

                    }

                }
            }
        } else {
            jQuery(".chart-tooltip").remove();
            prev_data_index = null;
        }
    });

});

// Dokan Register

jQuery(function($) {
    $('.user-role input[type=radio]').on('change', function() {
        var value = $(this).val();

        if ( value === 'seller') {
            $('.show_if_seller').slideDown();
            if ( $( '.tc_check_box' ).length > 0 )
                $('input[name=register]').attr('disabled','disabled');
        } else {
            $('.show_if_seller').slideUp();
            if ( $( '.tc_check_box' ).length > 0 )
                $( 'input[name=register]' ).removeAttr( 'disabled' );
        }
    });

   $( '.tc_check_box' ).on( 'click', function () {
        var chk_value = $( this ).val();
        if ( $( this ).prop( "checked" ) ) {
            $( 'input[name=register]' ).removeAttr( 'disabled' );
            $( 'input[name=dokan_migration]' ).removeAttr( 'disabled' );
        } else {
            $( 'input[name=register]' ).attr( 'disabled', 'disabled' );
            $( 'input[name=dokan_migration]' ).attr( 'disabled', 'disabled' );
        }
    } );

    if ( $( '.tc_check_box' ).length > 0 ){
        $( 'input[name=dokan_migration]' ).attr( 'disabled', 'disabled' );
    }

    $('#company-name').on('focusout', function() {
        var value = $(this).val().toLowerCase().replace(/-+/g, '').replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');
        $('#seller-url').val(value);
        $('#url-alart').text( value );
        $('#seller-url').focus();
    });

    $('#seller-url').keydown(function(e) {
        var text = $(this).val();

        // Allow: backspace, delete, tab, escape, enter and .
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 91, 109, 110, 173, 189, 190]) !== -1 ||
             // Allow: Ctrl+A
            (e.keyCode == 65 && e.ctrlKey === true) ||
             // Allow: home, end, left, right
            (e.keyCode >= 35 && e.keyCode <= 39)) {
                 // let it happen, don't do anything
                return;
        }

        if ((e.shiftKey || (e.keyCode < 65 || e.keyCode > 90) && (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105) ) {
            e.preventDefault();
        }
    });

    $('#seller-url').keyup(function(e) {
        $('#url-alart').text( $(this).val() );
    });

    $('#shop-phone').keydown(function(e) {
        // Allow: backspace, delete, tab, escape, enter and .
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 91, 107, 109, 110, 187, 189, 190]) !== -1 ||
             // Allow: Ctrl+A
            (e.keyCode == 65 && e.ctrlKey === true) ||
             // Allow: home, end, left, right
            (e.keyCode >= 35 && e.keyCode <= 39)) {
                 // let it happen, don't do anything
                 return;
        }

        // Ensure that it is a number and stop the keypress
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });

    $('#seller-url').on('focusout', function() {
        var self = $(this),
        data = {
            action : 'shop_url',
            url_slug : self.val(),
            _nonce : dokan.nonce,
        };

        if ( self.val() === '' ) {
            return;
        }

        var row = self.closest('.form-row');
        row.block({ message: null, overlayCSS: { background: '#fff url(' + dokan.ajax_loader + ') no-repeat center', opacity: 0.6 } });

        $.post( dokan.ajaxurl, data, function(resp) {

            if ( resp == 0){
                $('#url-alart').removeClass('text-success').addClass('text-danger');
                $('#url-alart-mgs').removeClass('text-success').addClass('text-danger').text(dokan.seller.notAvailable);
            } else {
                $('#url-alart').removeClass('text-danger').addClass('text-success');
                $('#url-alart-mgs').removeClass('text-danger').addClass('text-success').text(dokan.seller.available);
            }

            row.unblock();

        } );

    });
});

//dokan settings

(function($) {

    $.validator.setDefaults({ ignore: ":hidden" });

    var validatorError = function(error, element) {
        var form_group = $(element).closest('.form-group');
        form_group.addClass('has-error').append(error);
    };

    var validatorSuccess = function(label, element) {
        $(element).closest('.form-group').removeClass('has-error');
    };

    var api = wp.customize;

    var Dokan_Settings = {
        init: function() {
            var self = this;

            //image upload
            $('a.dokan-banner-drag').on('click', this.imageUpload);
            $('a.dokan-remove-banner-image').on('click', this.removeBanner);

            $('a.dokan-pro-gravatar-drag').on('click', this.gragatarImageUpload);
            $('a.dokan-gravatar-drag').on('click', this.simpleImageUpload);
            $('a.dokan-remove-gravatar-image').on('click', this.removeGravatar);

            this.validateForm(self);

            return false;
        },

        calculateImageSelectOptions: function(attachment, controller) {
            var xInit = parseInt(dokan_refund.store_banner_dimension.width, 10),
                yInit = parseInt(dokan_refund.store_banner_dimension.height, 10),
                flexWidth = !! parseInt(dokan_refund.store_banner_dimension['flex-width'], 10),
                flexHeight = !! parseInt(dokan_refund.store_banner_dimension['flex-height'], 10),
                ratio, xImg, yImg, realHeight, realWidth,
                imgSelectOptions;

            realWidth = attachment.get('width');
            realHeight = attachment.get('height');

            this.headerImage = new api.HeaderTool.ImageModel();
            this.headerImage.set({
                themeWidth: xInit,
                themeHeight: yInit,
                themeFlexWidth: flexWidth,
                themeFlexHeight: flexHeight,
                imageWidth: realWidth,
                imageHeight: realHeight
            });

            controller.set( 'canSkipCrop', ! this.headerImage.shouldBeCropped() );

            ratio = xInit / yInit;
            xImg = realWidth;
            yImg = realHeight;

            if ( xImg / yImg > ratio ) {
                yInit = yImg;
                xInit = yInit * ratio;
            } else {
                xInit = xImg;
                yInit = xInit / ratio;
            }

            imgSelectOptions = {
                handles: true,
                keys: true,
                instance: true,
                persistent: true,
                imageWidth: realWidth,
                imageHeight: realHeight,
                x1: 0,
                y1: 0,
                x2: xInit,
                y2: yInit
            };

            if (flexHeight === false && flexWidth === false) {
                imgSelectOptions.aspectRatio = xInit + ':' + yInit;
            }
            if (flexHeight === false ) {
                imgSelectOptions.maxHeight = yInit;
            }
            if (flexWidth === false ) {
                imgSelectOptions.maxWidth = xInit;
            }

            return imgSelectOptions;
        },

        onSelect: function() {
            this.frame.setState('cropper');
        },

        onCropped: function(croppedImage) {
            var url = croppedImage.url,
                attachmentId = croppedImage.attachment_id,
                w = croppedImage.width,
                h = croppedImage.height;
            this.setImageFromURL(url, attachmentId, w, h);
        },

        onSkippedCrop: function(selection) {
            var url = selection.get('url'),
                w = selection.get('width'),
                h = selection.get('height');
            this.setImageFromURL(url, selection.id, w, h);
        },

        setImageFromURL: function(url, attachmentId, width, height) {
            if ( $(this.uploadBtn).hasClass('dokan-banner-drag') ) {
                var wrap = $(this.uploadBtn).closest('.dokan-banner');
                wrap.find('input.dokan-file-field').val(attachmentId);
                wrap.find('img.dokan-banner-img').attr('src', url);
                $(this.uploadBtn).parent().siblings('.image-wrap', wrap).removeClass('dokan-hide');
                $(this.uploadBtn).parent('.button-area').addClass('dokan-hide');
            } else if ( $(this.uploadBtn).hasClass('dokan-pro-gravatar-drag') ) {
                var wrap = $(this.uploadBtn).closest('.dokan-gravatar');
                wrap.find('input.dokan-file-field').val(attachmentId);
                wrap.find('img.dokan-gravatar-img').attr('src', url);
                $(this.uploadBtn).parent().siblings('.gravatar-wrap', wrap).removeClass('dokan-hide');
                $(this.uploadBtn).parent('.gravatar-button-area').addClass('dokan-hide');
            }
        },

        removeImage: function() {
            api.HeaderTool.currentHeader.trigger('hide');
            api.HeaderTool.CombinedList.trigger('control:removeImage');
        },

        imageUpload: function(e) {
            e.preventDefault();

            var file_frame,
                settings = Dokan_Settings;

            settings.uploadBtn = this;

            settings.frame = wp.media({
                multiple: false,
                button: {
                    text: dokan_refund.selectAndCrop,
                    close: false
                },
                states: [
                    new wp.media.controller.Library({
                        title:     dokan_refund.chooseImage,
                        library:   wp.media.query({ type: 'image' }),
                        multiple:  false,
                        date:      false,
                        priority:  20,
                        suggestedWidth: dokan_refund.store_banner_dimension.width,
                        suggestedHeight: dokan_refund.store_banner_dimension.height
                    }),
                    new wp.media.controller.Cropper({
                        suggestedWidth: 5000,
                        imgSelectOptions: settings.calculateImageSelectOptions
                    })
                ]
            });

            settings.frame.on('select', settings.onSelect, settings);
            settings.frame.on('cropped', settings.onCropped, settings);
            settings.frame.on('skippedcrop', settings.onSkippedCrop, settings);

            settings.frame.open();

        },

        calculateImageSelectOptionsProfile: function(attachment, controller) {
            var xInit = 150,
                yInit = 150,
                flexWidth = !! parseInt(dokan_refund.store_banner_dimension['flex-width'], 10),
                flexHeight = !! parseInt(dokan_refund.store_banner_dimension['flex-height'], 10),
                ratio, xImg, yImg, realHeight, realWidth,
                imgSelectOptions;

            realWidth = attachment.get('width');
            realHeight = attachment.get('height');

            this.headerImage = new api.HeaderTool.ImageModel();
            this.headerImage.set({
                themeWidth: xInit,
                themeHeight: yInit,
                themeFlexWidth: flexWidth,
                themeFlexHeight: flexHeight,
                imageWidth: realWidth,
                imageHeight: realHeight
            });

            controller.set( 'canSkipCrop', ! this.headerImage.shouldBeCropped() );

            ratio = xInit / yInit;
            xImg = realWidth;
            yImg = realHeight;

            if ( xImg / yImg > ratio ) {
                yInit = yImg;
                xInit = yInit * ratio;
            } else {
                xInit = xImg;
                yInit = xInit / ratio;
            }

            imgSelectOptions = {
                handles: true,
                keys: true,
                instance: true,
                persistent: true,
                imageWidth: realWidth,
                imageHeight: realHeight,
                x1: 0,
                y1: 0,
                x2: xInit,
                y2: yInit
            };

            if (flexHeight === false && flexWidth === false) {
                imgSelectOptions.aspectRatio = xInit + ':' + yInit;
            }
            if (flexHeight === false ) {
                imgSelectOptions.maxHeight = yInit;
            }
            if (flexWidth === false ) {
                imgSelectOptions.maxWidth = xInit;
            }

            return imgSelectOptions;
        },

        simpleImageUpload : function(e) {
            e.preventDefault();
             var file_frame,
                self = $(this);

            // If the media frame already exists, reopen it.
            if ( file_frame ) {
                file_frame.open();
                return;
            }

            // Create the media frame.
            file_frame = wp.media.frames.file_frame = wp.media({
                title: jQuery( this ).data( 'uploader_title' ),
                button: {
                    text: jQuery( this ).data( 'uploader_button_text' )
                },
                multiple: false
            });

            // When an image is selected, run a callback.
            file_frame.on( 'select', function() {
                var attachment = file_frame.state().get('selection').first().toJSON();

                var wrap = self.closest('.dokan-gravatar');
                wrap.find('input.dokan-file-field').val(attachment.id);
                wrap.find('img.dokan-gravatar-img').attr('src', attachment.url);
                self.parent().siblings('.gravatar-wrap', wrap).removeClass('dokan-hide');
                self.parent('.gravatar-button-area').addClass('dokan-hide');

            });

            // Finally, open the modal
            file_frame.open();
        },

        gragatarImageUpload: function(e) {
            e.preventDefault();

            var file_frame,
                settings = Dokan_Settings;

            settings.uploadBtn = this;

            settings.frame = wp.media({
                multiple: false,
                button: {
                    text: dokan_refund.selectAndCrop,
                    close: false
                },
                states: [
                    new wp.media.controller.Library({
                        title:     dokan_refund.chooseImage,
                        library:   wp.media.query({ type: 'image' }),
                        multiple:  false,
                        date:      false,
                        priority:  20,
                        suggestedWidth: 150,
                        suggestedHeight: 150
                    }),
                    new wp.media.controller.Cropper({
                        imgSelectOptions: settings.calculateImageSelectOptionsProfile
                    })
                ]
            });

            settings.frame.on('select', settings.onSelect, settings);
            settings.frame.on('cropped', settings.onCropped, settings);
            settings.frame.on('skippedcrop', settings.onSkippedCrop, settings);

            settings.frame.open();

        },

        submitSettings: function(form_id) {

            if ( typeof tinyMCE != 'undefined' ) {
                tinyMCE.triggerSave();
            }

            var self = $( "form#" + form_id ),
                form_data = self.serialize() + '&action=dokan_settings&form_id=' + form_id;

            self.find('.ajax_prev').append('<span class="dokan-loading"> </span>');
            $.post(dokan.ajaxurl, form_data, function(resp) {

                self.find('span.dokan-loading').remove();
                $('html,body').animate({scrollTop:100});

               if ( resp.success ) {
                    // Harcoded Customization for template-settings function
                      $('.dokan-ajax-response').html( $('<div/>', {
                        'class': 'dokan-alert dokan-alert-success',
                        'html': '<p>' + resp.data.msg + '</p>',
                    }) );

                    $('.dokan-ajax-response').append(resp.data.progress);

                }else {
                    $('.dokan-ajax-response').html( $('<div/>', {
                        'class': 'dokan-alert dokan-alert-danger',
                        'html': '<p>' + resp.data + '</p>'
                    }) );
                }
            });
        },

        validateForm: function(self) {

            $("form#settings-form, form#profile-form, form#store-form, form#payment-form").validate({
                //errorLabelContainer: '#errors'
                submitHandler: function(form) {
                    self.submitSettings( form.getAttribute('id') );
                },
                errorElement: 'span',
                errorClass: 'error',
                errorPlacement: validatorError,
                success: validatorSuccess
            });

        },

        removeBanner: function(e) {
            e.preventDefault();

            var self = $(this);
            var wrap = self.closest('.image-wrap');
            var instruction = wrap.siblings('.button-area');

            wrap.find('input.dokan-file-field').val('0');
            wrap.addClass('dokan-hide');
            instruction.removeClass('dokan-hide');
        },

        removeGravatar: function(e) {
            e.preventDefault();

            var self = $(this);
            var wrap = self.closest('.gravatar-wrap');
            var instruction = wrap.siblings('.gravatar-button-area');

            wrap.find('input.dokan-file-field').val('0');
            wrap.addClass('dokan-hide');
            instruction.removeClass('dokan-hide');
        },
    };

    var Dokan_Withdraw = {

        init: function() {
            var self = this;

            this.withdrawValidate(self);
        },

        withdrawValidate: function(self) {
            $('form.withdraw').validate({
                //errorLabelContainer: '#errors'

                errorElement: 'span',
                errorClass: 'error',
                errorPlacement: validatorError,
                success: validatorSuccess
            })
        }
    };

    var Dokan_Coupons = {
        init: function() {
            var self = this;
            this.couponsValidation(self);
        },

        couponsValidation: function(self) {
            $("form.coupons").validate({
                errorElement: 'span',
                errorClass: 'error',
                errorPlacement: validatorError,
                success: validatorSuccess
            });
        }
    };

    var Dokan_Seller = {
        init: function() {
            this.validate(this);
        },

        validate: function(self) {
            // e.preventDefault();

            $('form#dokan-form-contact-seller').validate({
                errorPlacement: validatorError,
                success: validatorSuccess,
                submitHandler: function(form) {

                    $(form).block({ message: null, overlayCSS: { background: '#fff url(' + dokan.ajax_loader + ') no-repeat center', opacity: 0.6 } });

                    var form_data = $(form).serialize();
                    $.post(dokan.ajaxurl, form_data, function(resp) {
                        $(form).unblock();

                        if ( typeof resp.data !== 'undefined' ) {
                            $(form).find('.ajax-response').html(resp.data);
                        }

                        $(form).find('input[type=text], input[type=email], textarea').val('').removeClass('valid');
                    });
                }
            });
        }
    };

    var Dokan_Add_Seller = {
        init: function() {
            this.validate(this);
        },

        validate: function(self) {

            $('form.register').validate({
                errorPlacement: validatorError,
                success: validatorSuccess,
                submitHandler: function(form) {
                    form.submit();
                }
            });
        }
    };

    $(function() {
        Dokan_Settings.init();
        Dokan_Withdraw.init();
        Dokan_Coupons.init();
        Dokan_Seller.init();
        Dokan_Add_Seller.init();
    });

})(jQuery);

// Shipping tab js
(function($){
    $(document).ready(function(){

        $('.dokan-shipping-location-wrapper').on('change', '.dps_country_selection', function() {
            var self = $(this),
                data = {
                    country_id : self.find(':selected').val(),
                    action  : 'dps_select_state_by_country'
                };

                if ( self.val() == '' || self.val() == 'everywhere' ) {
                    self.closest('.dps-shipping-location-content').find('table.dps-shipping-states tbody').html('');
                } else {
                    $.post( dokan.ajaxurl, data, function(response) {
                        if( response.success ) {
                            self.closest('.dps-shipping-location-content').find('table.dps-shipping-states tbody').html(response.data);
                        }
                    });
                }
        });

    });
})(jQuery);


(function($){

    $(document).ready(function(){

        $('.dps-main-wrapper').on('click', 'a.dps-shipping-add', function(e) {
            e.preventDefault();

            html = $('#dps-shipping-hidden-lcoation-content');
            var row = $(html).first().clone().appendTo($('.dokan-shipping-location-wrapper')).show();
            $('.dokan-shipping-location-wrapper').find('.dps-shipping-location-content').first().find('a.dps-shipping-remove').show();

            $('.tips').tooltip();

            row.removeAttr('id');
            row.find('input,select').val('');
            row.find('a.dps-shipping-remove').show();
        });

        $('.dokan-shipping-location-wrapper').on('click', 'a.dps-shipping-remove', function(e) {
            e.preventDefault();
            $(this).closest('.dps-shipping-location-content').remove();
            $dpsElm = $('.dokan-shipping-location-wrapper').find('.dps-shipping-location-content');

            if( $dpsElm.length == 1) {
                $dpsElm.first().find('a.dps-shipping-remove').hide();
            }
        });

        $('.dokan-shipping-location-wrapper').on('click', 'a.dps-add', function(e) {
            e.preventDefault();

            var row = $(this).closest('tr').first().clone().appendTo($(this).closest('table.dps-shipping-states'));
            row.find('input,select').val('');
            row.find('a.dps-remove').show();
            $('.tips').tooltip();
        });

        $('.dokan-shipping-location-wrapper').on('click', 'a.dps-remove', function(e) {
            e.preventDefault();

            if( $(this).closest('table.dps-shipping-states').find( 'tr' ).length == 1 ){
                $(this).closest('.dps-shipping-location-content').find('td.dps_shipping_location_cost').show();
            }

            $(this).closest('tr').remove();


        });

        $('.dokan-shipping-location-wrapper').on('change keyup', '.dps_state_selection', function() {
            var self = $(this);

            if( self.val() == '' || self.val() == '-1' ) {
                self.closest('.dps-shipping-location-content').find('td.dps_shipping_location_cost').show();
            } else {
                self.closest('.dps-shipping-location-content').find('td.dps_shipping_location_cost').hide();
            }
        });

        $('.dokan-shipping-location-wrapper .dps_state_selection').trigger('change');
        $('.dokan-shipping-location-wrapper .dps_state_selection').trigger('keyup');

        $wrap = $('.dokan-shipping-location-wrapper').find('.dps-shipping-location-content');

        if( $wrap.length == 1) {
            $wrap.first().find('a.dps-shipping-remove').hide();
        }

    });

})(jQuery);

// For Announcement scripts;
(function($){

    $(document).ready(function(){
        $( '.dokan-announcement-wrapper' ).on( 'click', 'a.remove_announcement', function(e) {
            e.preventDefault();

            if( confirm( dokan.delete_confirm ) ) {

                var self = $(this),
                    data = {
                        'action' : 'dokan_announcement_remove_row',
                        'row_id' : self.data('notice_row'),
                        '_wpnonce' : dokan.nonce
                    };
                self.closest('.dokan-announcement-wrapper-item').append('<span class="dokan-loading" style="position:absolute;top:2px; right:15px"> </span>');
                var row_count = $('.dokan-announcement-wrapper-item').length;
                $.post( dokan.ajaxurl, data, function(response) {
                    if( response.success ) {
                        self.closest('.dokan-announcement-wrapper-item').find( 'span.dokan-loading' ).remove();
                        self.closest('.dokan-announcement-wrapper-item').fadeOut(function(){
                            $(this).remove();
                            if( row_count == 1 ) {
                                $( '.dokan-announcement-wrapper' ).html( response.data );
                            }
                        });
                    } else {
                        alert( dokan.wrong_message );
                    }
                });
            }

        });
    });

})(jQuery);
//dokan store seo form submit
(function($){

    var wrapper = $( '.dokan-store-seo-wrapper' );
    var Dokan_Store_SEO = {

        init : function() {
            wrapper.on( 'submit', 'form#dokan-store-seo-form', this.form.validate );
        },

        form : {

            validate : function(e){
                e.preventDefault();

                var self = $( this ),
                data = {
                    action: 'dokan_seo_form_handler',
                    data: self.serialize(),
                };

                Dokan_Store_SEO.form.submit( data );

                return false;
            },

            submit : function( data ){
                var feedback = $('#dokan-seo-feedback');
                feedback.fadeOut();

                $.post( dokan.ajaxurl, data, function ( resp ) {
                    if ( resp.success == true ) {
                        feedback.html(resp.data);
                        feedback.removeClass('dokan-hide');
                        feedback.addClass('dokan-alert-success');
                        feedback.fadeIn();
                    } else {
                        feedback.html(resp.data);
                        feedback.addClass('dokan-alert-danger');
                        feedback.removeClass('dokan-hide');
                        feedback.fadeIn();
                    }
                } )
            }

        },
    };

    $(function() {
        Dokan_Store_SEO.init();
    });

})(jQuery);

//localize Validation messages
(function($){
    var dokan_messages = DokanValidateMsg;

    dokan_messages.maxlength   = $.validator.format( dokan_messages.maxlength_msg );
    dokan_messages.minlength   = $.validator.format( dokan_messages.minlength_msg );
    dokan_messages.rangelength = $.validator.format( dokan_messages.rangelength_msg );
    dokan_messages.range       = $.validator.format( dokan_messages.range_msg );
    dokan_messages.max         = $.validator.format( dokan_messages.max_msg );
    dokan_messages.min         = $.validator.format( dokan_messages.min_msg );

    $.validator.messages = dokan_messages;

    $(document).on('click','#dokan_store_tnc_enable',function(e) {
        if($(this).is(':checked')) {
            $('#dokan_tnc_text').show();
        }else {
            $('#dokan_tnc_text').hide();
        }
    }).ready(function(e){
        if($('#dokan_store_tnc_enable').is(':checked')) {
            $('#dokan_tnc_text').show();
        }else {
            $('#dokan_tnc_text').hide();
        }
    });

})(jQuery);

;(function($) {
    function resize_dummy_image() {
        var width = dokan_refund.store_banner_dimension.width,
            height = (dokan_refund.store_banner_dimension.height / dokan_refund.store_banner_dimension.width) * $('#dokan-content').width();

        $('.profile-info-img.dummy-image').css({
            height: height
        });
    }

    resize_dummy_image();

    $(window).on('resize', function (e) {
        resize_dummy_image();
    });

})(jQuery);