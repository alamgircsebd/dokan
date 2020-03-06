(function($) {
    if ( ! $( '#dokan-geolocation-product-location' ).length ) {
        return;
    }

    function SearchButtonControl ( mapId ) {
        this._mapId = mapId;
    }

    SearchButtonControl.prototype.onAdd = function ( map ) {
        var self = this;

        this._map = map;

        var icon = document.createElement( 'i' );
        icon.className = 'fa fa-search';

        var label = document.createTextNode( 'Search Map' );

        var button = document.createElement( 'button' );
        button.type = 'button';
        // button.className = 'button';
        button.appendChild( icon );
        button.appendChild( label );
        button.addEventListener( 'click', function ( e ) {
            e.preventDefault();
            var control = document.getElementById( self._mapId ).getElementsByClassName( 'mapboxgl-ctrl-top-left' )[0];
            control.className = control.className + ' ' + 'show-geocoder';
        } );

        var container = document.createElement( 'div' );
        container.className = 'mapboxgl-ctrl mapboxgl-ctrl-group dokan-mapboxgl-ctrl';
        container.appendChild( button );

        this._container = container;

        return this._container;
    };

    SearchButtonControl.prototype.onRemove = function () {
        this._container.parentNode.removeChild( this._container );
        this._map = undefined;
    };

    var useStoreSettings = $( '#_dokan_geolocation_use_store_settings' );

    var dokanMapbox, accessToken, mapboxId, latInput, lngInput, addressInput, dokanGeocoder, dokanMarker;
    var location = {};

    useStoreSettings.on( 'change', function () {
        $( '#dokan-geolocation-product-location-no-store-settings' ).toggleClass( 'dokan-hide' );
        $( '#dokan-geolocation-product-location' ).toggleClass( 'dokan-hide' );

        if ( ! useStoreSettings.is( ':checked' ) && ! dokanMapbox ) {
            initMap();
        }
    } );

    var locateBtn = $( '#dokan-geolocation-product-location' ).find( '.locate-icon' );

    if ( ! navigator.geolocation ) {
        locateBtn.addClass( 'dokan-hide' );
    } else {
        locateBtn.on( 'click', function () {
            navigator.geolocation.getCurrentPosition( function( position ) {
                var lat = position.coords.latitude;
                var lng = position.coords.longitude;

                dokanMarker.setLngLat( [ lng, lat ] );
                dokanMapbox.setCenter( [ lng, lat ] );

                setLocation( {
                    latitude: lat,
                    longitude: lng,
                } );
            });
        });
    }

    if ( ! useStoreSettings.is( ':checked' ) ) {
        initMap();
    }

    function initMap() {
        mapboxId     = 'dokan-geolocation-product-location-map';
        accessToken  = $( '[name="_dokan_geolocation_mapbox_access_token"]' ).val();
        latInput     = $( '[name="_dokan_geolocation_product_dokan_geo_latitude"]' );
        lngInput     = $( '[name="_dokan_geolocation_product_dokan_geo_longitude"]' );
        addressInput = $( '#_dokan_geolocation_product_location' );

        location = {
            latitude: latInput.val(),
            longitude: lngInput.val(),
            address: addressInput.val(),
            zoom: 12,
        };

        mapboxgl.accessToken = accessToken;

        dokanMapbox = new mapboxgl.Map( {
            container: mapboxId,
            style: 'mapbox://styles/mapbox/streets-v10',
            center: [ location.longitude, location.latitude ],
            zoom: location.zoom,
        } );

        dokanMapbox.addControl( new mapboxgl.NavigationControl() );
        dokanMapbox.addControl( new SearchButtonControl( mapboxId ), 'top-left' );

        dokanMapbox.on( 'load', function () {
            dokanGeocoder = new MapboxGeocoder( {
                accessToken: mapboxgl.accessToken,
                mapboxgl: mapboxgl,
                zoom: dokanMapbox.getZoom(),
                placeholder: 'Search Address',
                marker: false,
                reverseGeocode: true,
            });

            dokanMapbox.addControl( dokanGeocoder, 'top-left' );
            dokanGeocoder.setInput( location.address );

            dokanGeocoder.on( 'result', function ( resultData ) {
                var result = resultData.result;
                var lngLat = result.center;
                var address = result.place_name;

                dokanMarker.setLngLat( lngLat );
                dokanMapbox.setCenter( [ lngLat[0], lngLat[1] ] );

                setLocation( {
                    address: address,
                    latitude: lngLat[1],
                    longitude: lngLat[0],
                } );
            } );
        } );

        dokanMarker = new mapboxgl.Marker( {
            draggable: true
        } )
            .setLngLat( [ location.longitude, location.latitude ] )
            .addTo( dokanMapbox )
            .on( 'dragend', onMarkerDragEnd );
    }

    function onMarkerDragEnd () {
        var urlOrigin = dokanGeocoder.geocoderService.client.origin;
        var accessToken = dokanGeocoder.geocoderService.client.accessToken;
        var lng = dokanMarker.getLngLat().wrap().lng;
        var lat = dokanMarker.getLngLat().wrap().lat;

        dokanMapbox.setCenter( [ lng, lat ] );

        setLocation( {
            latitude: lat,
            longitude: lng,
        } );

        var url = urlOrigin + '/geocoding/v5/mapbox.places/' + lng + '%2C' + lat + '.json?access_token=' + accessToken + '&cachebuster=' + +new Date() + '&autocomplete=true';

        dokanGeocoder._inputEl.disabled = true;
        dokanGeocoder._loadingEl.style.display = 'block';

        jQuery.ajax( {
            url: url,
            method: 'get',
        } ).done( function ( response ) {
            if ( response.features ) {
                dokanGeocoder._typeahead.update( response.features );
                $( dokanMapbox._controlContainer ).find( '.mapboxgl-ctrl-top-left' ).addClass( 'show-geocoder' );
            }
        } ).always( function () {
            dokanGeocoder._inputEl.disabled = false;
            dokanGeocoder._loadingEl.style.display = '';
        } );
    }

    function setLocation( newLocation ) {
        location = Object.assign( location, newLocation );

        latInput.val( location.latitude );
        lngInput.val( location.longitude );
        addressInput.val( location.address );
    }

    $( '#dokan-map-add' ).on( 'input', function ( e ) {
        setLocation( {
            address: e.target.value
        } );
    } );
})(jQuery);
