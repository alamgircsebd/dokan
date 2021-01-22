;(function($) {

    var pricingPane = $('#woocommerce-product-data');

    if ( pricingPane.length ) {
        pricingPane.find('.pricing').addClass('show_if_product_pack').end()
            .find('.inventory_tab').addClass('hide_if_product_pack').end()
            .find('.shipping_tab').addClass('hide_if_product_pack').end()
            .find('.linked_product_tab').addClass('hide_if_product_pack').end()
            .find('.attributes_tab').addClass('hide_if_product_pack').end()
            .find('._no_of_product_field').hide().end()
            .find('._pack_validity_field').hide().end()
            .find('#_tax_status').parent().parent().addClass('show_if_product_pack').end()
    }

    $('body').on('woocommerce-product-type-change',function(event, select_val){

        $('._no_of_product_field').hide();
        $('._pack_validity_field').hide();
        $('._enable_recurring_payment_field').hide();
        $('.dokan_subscription_pricing').hide();
        $('._sale_price_field').show();
        $('.dokan_subscription_trial_period').hide();

        if ( select_val == 'product_pack' ) {
            $('._no_of_product_field').show();
            $('._pack_validity_field').show();
            $('._enable_recurring_payment_field').show();
            $('._sale_price_field').hide();

            if ( $('#dokan_subscription_enable_trial').is(':checked') ) {
                $('.dokan_subscription_trial_period').show();
            }

            if ( $( '#_enable_recurring_payment' ).is( ":checked" ) ) {
                $('.dokan_subscription_pricing').show();
                $('._pack_validity_field').hide();
            }

        }

    });

    $('.woocommerce_options_panel').on('change', '#dokan_subscription_enable_trial', function() {
        $('.dokan_subscription_trial_period').hide();

        if ( $(this).is(':checked') ) {
            $('.dokan_subscription_trial_period').fadeIn();
        }
    });

    $('.woocommerce_options_panel').on('change', '#_enable_recurring_payment', function() {

        $('.dokan_subscription_pricing').hide();
        $('._pack_validity_field').show();

        if ( $(this).is(':checked') ) {
            $('.dokan_subscription_pricing').fadeIn();
            $('._pack_validity_field').hide();
        }
    });

    // Update subscription ranges when subscription period or interval is changed
    $('#woocommerce-product-data').on('change','[name^="_dokan_subscription_period"], [name^="_dokan_subscription_period_interval"]',function(){
        setDokanSubscriptionLengths();
    });

    function setDokanSubscriptionLengths(){
        $('[name^="_dokan_subscription_length"]').each(function(){
            var $lengthElement = $(this),
                selectedLength = $lengthElement.val(),
                hasSelectedLength = false,
                periodSelector;

            periodSelector = '#_dokan_subscription_period';
            billingInterval = parseInt($('#_dokan_subscription_period_interval').val());

            $lengthElement.empty();

            $.each( dokanSubscription.subscriptionLengths[ $(periodSelector).val() ], function(length,description) {
                if(parseInt(length) == 0 || 0 == (parseInt(length) % billingInterval)) {
                    $lengthElement.append($('<option></option>').attr('value',length).text(description));
                }
            });

            $lengthElement.children('option').each(function(){
                if (this.value == selectedLength) {
                    hasSelectedLength = true;
                    return false;
                }
            });

            if(hasSelectedLength){
                $lengthElement.val(selectedLength);
            } else {
                $lengthElement.val(0);
            }
        });
    }

})(jQuery);
