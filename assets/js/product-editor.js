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
            //$('.product_lot_discount').on('change', 'input[type=checkbox]#_is_lot_discount', this.editProduct.showLotDiscountWrapper );
            //$('.product-edit-new-container, .product_lot_discount').on('change', 'input[type=checkbox]#_is_lot_discount', this.newProductDesign.showLotDiscountWrapper );
            $('.product-edit-new-container, .product_lot_discount').on('change', 'input[type=checkbox]#_is_lot_discount', this.editProduct.showLotDiscountWrapper );
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
                    $( '.show_if_needs_lot_discount' ).slideDown('slow');
                } else {
                    $( '.show_if_needs_lot_discount' ).slideUp('slow');
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