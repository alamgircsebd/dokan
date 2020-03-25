import '../less/filters.less';
import './bootstrap-dropdown';

/**
 * Dokan Geolocation Module: Filter Form
 */
( function( $ ) {
    function GeolocationFilter ( form ) {
        this.form = null;
        this.slider = null;
        this.slider_value = 0;
        this.href = '';
        this.base_url = '';
        this.queries = {};
        this.query_params = [];
        this.scope = null;
        this.switchable_scope = null;
        this.display = '';
        this.s = '';
        this.dokan_seller_search = '';
        this.product_cat = '';
        this.latitude = 0.00;
        this.longitude = 0.00;
        this.address = '';
        this.distance = 0;
        this.geocoder = null;
        this.isStoreCategoryOn = false;

        this.form = form;

        this.init();
    }

    GeolocationFilter.prototype.init = function () {
        var self = this;

        self.set_query_params();

        self.form.on( 'submit', function (e) {
            e.preventDefault();
        } );

        self.display_form();

        self.s = self.form.find( '[name="s"]' ).val();
        self.dokan_seller_search = self.form.find( '[name="dokan_seller_search"]' ).val();
        self.latitude = self.form.find( '[name="latitude"]' ).val();
        self.longitude = self.form.find( '[name="longitude"]' ).val();

        self.slider = self.form.find( '.dokan-range-slider' );
        self.slider_value = self.slider.prev( '.dokan-range-slider-value' ).find( 'span' );

        self.slider.on( 'input', function () {
            self.slider_value.html( $( this ).val() );
        } );

        self.slider.on( 'change', function () {
            self.set_param( 'distance', $( this ).val() );
        } );

        self.form.find( '[name="s"], [name="dokan_seller_search"]' ).on( 'blur', function () {
            self.set_search_term( $( this ).val() );
        } );

        self.form.find( '[name="s"], [name="dokan_seller_search"]' ).on( 'keypress', function ( e ) {
            if ( 13 === e.which ) {
                self.set_search_term( $( this ).val() );
            }
        } );

        // On category page, category is already selected. So, we are setting the product_cat query.
        var product_cats = self.form.find( '[name="product_cat"]' );

        if ( product_cats.val() ) {
            self.queries['product_cat'] = product_cats.val();
        }

        product_cats.on( 'change', function () {
            var category = $(this).val();

            self.set_param( 'product_cat', category );
        } );

        self.form.find( '[name="store_category"]' ).on( 'change', function () {
            var category = $(this).val();

            self.set_param( 'store_category', category );
        } );

        self.form.find( '.dokan-geo-filters-search-btn' ).on( 'click', function ( e ) {
            e.preventDefault();

            self.redirect( self.switchable_scope );
        } );

        self.bind_address_input();
    };

    GeolocationFilter.prototype.display_form = function () {
        var self = this;

        self.form.find( '.dokan-geolocation-filters-loading' ).remove();
        self.form.find( '.dokan-row' ).removeClass( 'dokan-hide' );
        self.scope = self.form.data( 'scope' );
        self.display = self.form.data( 'display' );
        self.isStoreCategoryOn = self.form.find( '#store-category-dropdown' ).length;

        if ( 'inline' !== self.display ) {
            self.form.find( '.dokan-geo-filters-column' ).addClass( 'dokan-w12' );

        } else if ( 'product' === self.scope ) {
            self.form.find( '.dokan-geo-filters-column' ).addClass( 'dokan-w4' );

        } else if ( 'vendor' === self.scope && self.isStoreCategoryOn ) {
            self.form.find( '.dokan-geo-filters-column' ).addClass( 'dokan-w4' );

        } else if ( 'vendor' === self.scope ) {
            self.form.find( '.dokan-geo-filters-column' ).addClass( 'dokan-w6' );

        } else {
            self.form.find( '.dokan-geo-filters-column' ).addClass( 'dokan-w3' );
        }

        if ( ! self.scope ) {
            self.switchable_scope = 'product';
            self.form.find( '[name="store_category"]' ).parent().addClass( 'dokan-hide' );
        }

        var scope_switch = self.form.find( '.dokan-geo-filter-scope-switch a' ),
            scope_label  = self.form.find( '.dokan-geo-filter-scope' );

        scope_switch.on( 'click', function ( e ) {
            e.preventDefault();

            var scope = $( this ).data( 'switch-scope' );

            if ( 'product' === scope ) {
                self.form.find( '[name="s"]' ).removeClass( 'dokan-hide' );
                self.form.find( '[name="dokan_seller_search"]' ).addClass( 'dokan-hide' );
                self.form.find( '.dokan-geo-product-categories' ).removeClass( 'dokan-hide' );
                self.form.find( '.dokan-geo-filters-column' ).removeClass( 'dokan-w4' ).addClass( 'dokan-w3' );
                self.form.find( '[name="store_category"]' ).parent().addClass( 'dokan-hide' );
            } else {
                var removeClass = self.isStoreCategoryOn ? 'dokan-w4' : 'dokan-w3';
                var addClass = self.isStoreCategoryOn ? 'dokan-w3' : 'dokan-w4';

                self.form.find( '[name="s"]' ).addClass( 'dokan-hide' );
                self.form.find( '[name="dokan_seller_search"]' ).removeClass( 'dokan-hide' );
                self.form.find( '.dokan-geo-product-categories' ).addClass( 'dokan-hide' );
                self.form.find( '.dokan-geo-filters-column' ).removeClass( removeClass ).addClass( addClass );
                self.form.find( '[name="store_category"]' ).parent().removeClass( 'dokan-hide' );
            }

            scope_label.html( $( this ).html() );
            self.switchable_scope = scope;
        } );
    };

    GeolocationFilter.prototype.navigatorGetCurrentPosition = function ( callback ) {
        var self = this;

        // Locate button functions
        var locate_btn = self.form.find( '.locate-icon' ),
            loader = locate_btn.next();

        if ( navigator.geolocation ) {
            locate_btn.removeClass( 'dokan-hide' ).on( 'click', function () {
                locate_btn.addClass( 'dokan-hide' );
                loader.removeClass( 'dokan-hide' );

                navigator.geolocation.getCurrentPosition( function( position ) {
                    locate_btn.removeClass( 'dokan-hide' );
                    loader.addClass( 'dokan-hide' );

                    self.latitude = position.coords.latitude;
                    self.longitude = position.coords.longitude;

                    callback();
                });
            });
        }
    };

    GeolocationFilter.prototype.bind_address_input = function () {
        if ( window.google && google.maps ) {
            this.bind_google_map();
        } else if ( $( '[name="dokan_mapbox_access_token"]' ).val() ) {
            this.bind_mapbox();
        }
    };

    GeolocationFilter.prototype.bind_google_map = function() {
        var self = this;

        self.geocoder = new google.maps.Geocoder;

        // Autocomplete location address
        var address_input = self.form.find( '.location-address input' ),
            autocomplete = new google.maps.places.Autocomplete( address_input.get(0) );

        autocomplete.addListener( 'place_changed', function () {
            var place = autocomplete.getPlace(),
                location = place.geometry.location;

            self.latitude = location.lat();
            self.longitude = location.lng();

            self.set_address( place.formatted_address );
        } );

        self.navigatorGetCurrentPosition( function () {
            self.geocoder.geocode( {
                location: {
                    lat: self.latitude,
                    lng: self.longitude,
                }
            }, function ( results, status ) {
                var address = '';

                if ( 'OK' === status ) {
                    address = results[0].formatted_address;
                }

                self.set_address( address );
                address_input.val( address );
            } );
        } );
    };

    GeolocationFilter.prototype.bind_mapbox = function() {
        var self = this;

        var address_input = self.form.find( '.location-address input' );
        var input = address_input.get( 0 );

        var suggestions = new Suggestions( input, [], {
            minLength: 3,
            limit: 3,
            hideOnBlur: false,
        } );

        suggestions.getItemValue = function( item ) {
            return item.place_name;
        };

        address_input.on( 'change', function () {
            if ( suggestions.selected ) {
                var location = suggestions.selected;

                self.latitude = location.geometry.coordinates[1];
                self.longitude = location.geometry.coordinates[0];

                self.set_address( location.place_name );
            }
        } );

        var address_search = _.debounce( function ( search, text ) {
            if ( search.cancel ) {
                search.cancel();
            }

            self.mapboxGetPlaces( text, function ( features ) {
                suggestions.update( features );
            } );
        }, 250 );

        address_input.on( 'input', function () {
            var input_text = $( this ).val();
            address_search( address_search, input_text );
        } );

        self.navigatorGetCurrentPosition( function () {
            self.mapboxGetPlaces( {
                lng: self.longitude,
                lat: self.latitude,
            }, function ( features ) {
                if ( features && features.length ) {
                    var address = features[0].place_name;

                    self.set_address( address );
                    address_input.val( address );
                }
            } );
        } );
    };

    GeolocationFilter.prototype.mapboxGetPlaces = function ( search, callback ) {
        if ( ! search ) {
            return;
        }

        var url_origin = 'https://api.mapbox.com';
        var access_token = $( '[name="dokan_mapbox_access_token"]' ).val();

        if ( search.lng && search.lat ) {
            search = search.lng + '%2C' + search.lat;
        }

        var url = url_origin + '/geocoding/v5/mapbox.places/' + search + '.json?access_token=' + access_token + '&cachebuster=' + +new Date() + '&autocomplete=true';

        $.ajax( {
            url: url,
            method: 'get',
        } ).done( function ( response ) {
            if ( response.features && typeof callback === 'function' ) {
                callback( response.features );
            }
        } );
    };

    GeolocationFilter.prototype.set_search_term = function ( s ) {
        this.set_param( 's', s );
        this.set_param( 'dokan_seller_search', s );
    };

    GeolocationFilter.prototype.set_address = function ( address ) {
        this.set_param( 'address', address );

        if ( ! this.distance ) {
            var distance = 0,
                slider_val = this.slider.val();

            if ( slider_val ) {
                distance = slider_val;

            } else {
                var min = parseInt( this.slider.attr( 'min' ), 10 ),
                    max = parseInt( this.slider.attr( 'max' ), 10 );

                distance = Math.ceil( ( min + max ) / 2 );
            }

            this.set_param( 'distance', distance );
        }

        this.set_param( 'latitude', this.latitude );
        this.set_param( 'longitude', this.longitude );
    };

    GeolocationFilter.prototype.set_query_params = function () {
        var self = this;

        self.href = window.location.href;

        var search = window.location.search;

        self.base_url = self.href.replace( search, '' );

        search.replace( '?', '' ).split( '&' ).forEach( function ( query ) {
            if ( ! query ) {
                return;
            }

            query = query.split( '=' );

            var param = query[0].toLowerCase(),
                value = query[1];

            switch ( param ) {
                case 'distance':
                    self.distance = parseInt( value, 10 );
                    break;

                case 'latitude':
                    self.latitude = parseFloat( value );
                    break;

                case 'longitude':
                    self.longitude = parseFloat( value );
                    break;

                case 'address':
                    self.address = value;
            }

            if ( self.query_params.indexOf( param ) < 0 ) {
                self.query_params.push( param );
            }

            self.queries[param] = value;
        } );
    };

    GeolocationFilter.prototype.set_param = function ( param, val ) {
        if ( this.query_params.indexOf( param ) < 0 ) {
            this.query_params.push( param );
        }

        this[param] = val;

        if ( val ) {
            this.queries[param] = val;
        } else {
            delete this.queries[param];
        }

        if ( this.scope ) {
            if ( 'distance' === param && ( ! this.latitude || ! this.longitude ) ) {
                return;
            }

            this.redirect( this.scope );
        }
    };

    GeolocationFilter.prototype.redirect = function ( scope ) {
        var search = [],
            param = '';

        for ( param in this.queries ) {
            if ( [ 'post_type', 'dokan_seller_search', 's' ].indexOf( param ) < 0 ) {
                if ( param === 'distance' && ( ! this.latitude || ! this.longitude ) ) {
                    continue;
                }

                search.push( param + '=' + this.queries[ param ] );
            }
        }

        var s = this.s || '',
            dokan_seller_search = this.dokan_seller_search || '',
            base_url = '';

        if ( 'product' === scope ) {
            if ( s ) {
                search.push( 's=' + s );
            }

            search.push( 'post_type=product' );
            base_url = this.form.find( '[name="wc_shop_page"]' ).val();
        } else {
            if ( dokan_seller_search ) {
                search.push( 'dokan_seller_search=' + dokan_seller_search );
            }

            base_url = this.form.find( '[name="dokan_store_listing_page"]' ).val();
        }

        window.location.href = base_url + '?' + search.join( '&' );
    };

    $( '.dokan-geolocation-location-filters' ).each( function () {
        new GeolocationFilter( $( this ) );
    } );
} )( jQuery );
