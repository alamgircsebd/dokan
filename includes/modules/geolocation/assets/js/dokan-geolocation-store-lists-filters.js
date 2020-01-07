;(function($) {
    const geoLocationStoreLists = {
        slider: null,
        sliderValue: 0,
        distance: 0,

        init: function() {
            const self = geoLocationStoreLists;

            this.slider = $( '.store-lists-other-filter-wrap .dokan-geolocation-location-filters .dokan-range-slider' );
            this.sliderValue = this.slider.prev( '.dokan-range-slider-value' ).find( 'span' );

            this.slider.on( 'input', this.setSliderValue );
            this.slider.on( 'change', this.setDistance );

            self.bindAddressInput();

            $(' #dokan-store-listing-filter-wrap .dokan-geolocation-filters-loading' ).remove();
        },

        bindAddressInput: function() {
            if ( window.google && google.maps ) {
                this.bindeGoogleMap();
            } else if ( $( '[name="dokan_mapbox_access_token"]' ).val() ) {
                this.bindMapbox();
            }
        },

        bindeGoogleMap: function () {
            const self = geoLocationStoreLists;
            const locationAddress = $( '.location-address' );

            self.geocoder = new google.maps.Geocoder;

            // Autocomplete location address
            let address_input = locationAddress.find( 'input' );
                autocomplete = new google.maps.places.Autocomplete( address_input.get(0) );

            autocomplete.addListener( 'place_changed', function () {
                const place = autocomplete.getPlace();

                if ( place ) {
                    const location = place.geometry.location;

                    self.latitude = location.lat();
                    self.longitude = location.lng();

                    self.setAddress( place.formatted_address );
                }
            } );

            self.navigatorGetCurrentPosition( function() {
                self.geocoder.geocode( {
                    location: {
                        lat: self.latitude,
                        lng: self.longitude,
                    }
                }, function ( results, status ) {
                    let address = '';

                    if ( 'OK' === status ) {
                        address = results[0].formatted_address;
                    }

                    self.setAddress( address );
                    address_input.val( address );
                } );
            });
        },

        bindMapbox: function() {
            const self = geoLocationStoreLists;
            const locationAddress = $( '.location-address' );
            const address_input = locationAddress.find( 'input' );
            const input = address_input.get( 0 );

            const suggestions = new Suggestions( input, [], {
                minLength: 3,
                limit: 3,
                hideOnBlur: false,
            } );

            suggestions.getItemValue = function( item ) {
                return item.place_name;
            };

            address_input.on( 'change', function () {
                if ( suggestions.selected ) {
                    const location = suggestions.selected;

                    self.latitude = location.geometry.coordinates[1];
                    self.longitude = location.geometry.coordinates[0];

                    self.setAddress( location.place_name );
                }
            } );

            const address_search = _.debounce( function ( search, text ) {
                if ( search.cancel ) {
                    search.cancel();
                }

                self.mapboxGetPlaces( text, function ( features ) {
                    suggestions.update( features );
                } );
            }, 250 );

            address_input.on( 'input', function () {
                const input_text = $( this ).val();
                address_search( address_search, input_text );
            } );

            self.navigatorGetCurrentPosition( function () {
                self.mapboxGetPlaces( {
                    lng: self.longitude,
                    lat: self.latitude,
                }, function ( features ) {
                    if ( features && features.length ) {
                        let address = features[0].place_name;

                        self.setAddress( address );
                        address_input.val( address );
                    }
                } );
            } );
        },

        navigatorGetCurrentPosition: function( callback ) {
            const self = geoLocationStoreLists;

            const locationAddress = $( '.location-address' ),
                locate_btn = locationAddress.find( '.locate-icon' ),
                loader = locate_btn.next();

            if ( navigator.geolocation ) {
                locate_btn.removeClass( 'dokan-hide' ).on( 'click', function () {
                    locate_btn.addClass( 'dokan-hide' );
                    loader.removeClass( 'dokan-hide' );

                    navigator.geolocation.getCurrentPosition( function( position ) {
                        locate_btn.removeClass( 'dokan-hide' );
                        loader.addClass( 'dokan-hide' );

                        self.latitude = position.coords.latitude,
                        self.longitude = position.coords.longitude,

                        callback();
                    });
                });
            }
        },

        mapboxGetPlaces: function( search, callback ) {
            if ( ! search ) {
                return;
            }

            const url_origin = 'https://api.mapbox.com';
            const access_token = $( '[name="dokan_mapbox_access_token"]' ).val();

            if ( search.lng && search.lat ) {
                search = search.lng + '%2C' + search.lat;
            }

            const url = url_origin + '/geocoding/v5/mapbox.places/' + search + '.json?access_token=' + access_token + '&cachebuster=' + +new Date() + '&autocomplete=true';

            $.ajax( {
                url: url,
                method: 'get',
            } ).done( function ( response ) {
                if ( response.features && typeof callback === 'function' ) {
                    callback( response.features );
                }
            } );
        },

        setAddress: function( address ) {
            const self = geoLocationStoreLists;

            self.setParam( 'address', address );

            if ( ! self.distance ) {
                let distance = 0,
                    slider_val = self.slider.val();

                if ( slider_val ) {
                    distance = slider_val;
                } else {
                    const min = parseInt( self.slider.attr( 'min' ), 10 ),
                        max = parseInt( self.slider.attr( 'max' ), 10 );

                    distance = Math.ceil( ( min + max ) / 2 );
                }

                self.setParam( 'distance', distance );
            }

            self.setParam( 'latitude', self.latitude );
            self.setParam( 'longitude', self.longitude );
        },

        setParam: function ( param, val ) {
            const self = geoLocationStoreLists;

            if ( val ) {
                dokan.storeLists.query[param] = val;
            } else {
                delete dokan.storeLists.query[param];
            }
        },

        setSliderValue: function( event ) {
            const self = geoLocationStoreLists;

            self.sliderValue.html( event.target.value );
        },

        setDistance: function( event ) {
            const self = geoLocationStoreLists;
            self.setParam( 'distance', event.target.value );
        }
    }

    geoLocationStoreLists.init();
})(jQuery);