jQuery( function( $ ) {
    var publicKey = dokan_stripe_connect_params.key;
    var stripe    = Stripe( publicKey );
    var elements  = stripe.elements();

    window.dokanStripeCard = elements.create( 'card' );
    cardPlaceholder        = $( '#dokan-stripe-card-element' );

    if ( cardPlaceholder.length ) {
        dokanStripeCard.mount( '#dokan-stripe-card-element' );
    }

    dokanStripeCard.on('change', function(event) {
        var displayError = $( '.stripe-source-errors' );

        if ( event.error ) {
            displayError.text( event.error.message );
        } else {
            displayError.text( '' );
        }
    });

    $( 'form.checkout' ).one( 'checkout_place_order_dokan-stripe-connect', function( event ) {
        return stripeFormHandler();
    });

    function stripeFormHandler() {
        var clientSecret = $( '#dokan-payment-client-secret' ).val();
        var subscriptionProductId = $( '#dokan-subscription-product-id' ).val();

        if ( subscriptionProductId.length ) {
            var form = $( 'form.checkout, form#order_review' );
            form.block( { message: null, overlayCSS: { background: '#fff url(' + woocommerce_params.plugin_url + '/assets/images/ajax-loader.gif) no-repeat center', opacity: 0.6 } } );

            stripe.createToken( dokanStripeCard ).then( function( result ) {
                if ( result.error ) {
                    form.unblock();
                    displayError.text( result.error.message );
                } else {
                    handleToken( result.token, subscriptionProductId );
                }
            });

            return false;
        }

        handleCardPayment( clientSecret, dokanStripeCard );

        return false;
    }

    /**
     * HandleToken
     *
     * @description Only required to create subscription
     */
    function handleToken( token, subscriptionProductId ) {
        $.ajax( {
            url: dokan.ajaxurl,
            method: 'POST',
            data: {
                action: 'dokan_send_token',
                token: token.id,
                nonce: dokan.nonce,
                product_id: subscriptionProductId
            }
        } )
        .done( function( response ) {
            if ( typeof response !== 'undefined'
                && response.data
                && response.data.code
                && 'subscription_not_created' === response.data.code
                ) {
                return console.log( response.data.code.message )
            }

            if ( typeof response !== 'undefined'
                && response.status
                && 'trialing' === response.status
                ) {
                return maybeSubmitTheForm( response );
            }

            if ( typeof response !== 'undefined'
                && response.status
                && 'active' === response.status
                ) {
                return maybeSubmitTheForm( response );
            }

            if ( typeof response !== 'undefined'
                && response.latest_invoice
                && response.latest_invoice.payment_intent
                && response.latest_invoice.payment_intent.client_secret
                ) {
                var paymentIntentSecret = response.latest_invoice.payment_intent.client_secret;
                handleCardPayment( paymentIntentSecret, dokanStripeCard );
            }
        } );
    }

    function handleCardPayment( secret, card ) {
        stripe.handleCardPayment(
            secret, card, {
                payment_method_data: {
                    billing_details: {
                        name:  $( '#dokan-payment-customer-name' ).val(),
                        email: $( '#dokan-payment-customer-email' ).val(),
                        address: {
                            city: $( '#dokan-payment-customer-city' ).val(),
                            state: $( '#dokan-payment-customer-state' ).val(),
                            country:  $( '#dokan-payment-customer-country' ).val(),
                            line1:  $( '#dokan-payment-customer-address_1' ).val(),
                            line2: $( '#dokan-payment-customer-address_2' ).val(),
                            postal_code: $( '#dokan-payment-customer-postal_code' ).val(),
                        }
                    }
                }
            }
        )
        .then( function( response ) {
            return maybeSubmitTheForm( response );
        } );
    }

    function maybeSubmitTheForm( response ) {
        var form = $( 'form.checkout, form#order_review' );

        if ( response.error && response.error.message ) {
            form.unblock();
            console.log( response.error.message );
            return;
        }

        form.unbind( 'checkout_place_order_dokan-stripe-connect' );
        form.submit();
    }
});