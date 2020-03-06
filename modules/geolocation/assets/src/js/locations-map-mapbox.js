( function( $ ) {
    DokanGeo.LocationsMaps = {
        map: null,
        mapboxId: 'dokan-geolocation-locations-map',
        items: [],
        data: {
            type: 'FeatureCollection',
            features: [],
        },
        marker: {
            image: null,
            clusterer: null,
        },

        init: function () {
            var self = this;
            var locations = {
                longitude: 0,
                latitude: 0,
            };

            mapboxgl.accessToken = DokanGeo.mapbox_access_token;

            self.map = new mapboxgl.Map( {
                container: self.mapboxId,
                style: 'mapbox://styles/mapbox/streets-v10',
                center: [ DokanGeo.default_geolocation.longitude, DokanGeo.default_geolocation.latitude ],
                zoom: DokanGeo.map_zoom,
            } );

            self.map.addControl( new mapboxgl.NavigationControl() );

            self.items = $( '[name="dokan_geolocation[]"]' );

            self.items.each( function ( i ) {
                var id = $( this ).val(),
                    latitude = $( this ).data( 'latitude' ),
                    longitude = $( this ).data( 'longitude' ),
                    info = $( this ).data( 'info' );

                var dataItem = {
                    type: 'Feature',
                    properties: {
                        id: 'dokan-geolocation-item-' + i,
                        info: info,
                    },
                    geometry: {
                        type: 'Point',
                        coordinates: [
                            longitude,
                            latitude,
                            0
                        ],
                    },
                };

                self.data.features.push( dataItem );
                locations.longitude += longitude;
                locations.latitude += latitude;
            } );

            if ( locations.longitude && locations.latitude ) {
                self.map.setCenter( [ locations.longitude / self.items.length, locations.latitude / self.items.length ] );
            }

            self.map.on( 'load', function () {
                self.loadImages( 'image', DokanGeo.marker.image );
                self.loadImages( 'clusterer', DokanGeo.marker.clusterer );
            } );
        },

        loadImages: function ( id, imageURL ) {
            var self = this;

            self.map.loadImage( imageURL, function ( error, image ) {
                if ( error ) {
                    return;
                }

                self.marker[ id ] = image;
                self.map.addImage( 'dokan-marker-' + id, image );
                self.addMapLayers();
            } );
        },

        addMapLayers: function () {
            var self = this;

            if ( ! self.marker.image || ! self.marker.clusterer ) {
                return;
            }

            self.map.addSource( 'dokan_geolocation_map_main_data', {
                type: 'geojson',
                data: self.data,
                cluster: true,
                clusterMaxZoom: 14, // Max zoom to cluster points on
                clusterRadius: 50 // Radius of each cluster when clustering points (defaults to 50)
            });

            self.map.addLayer( {
                id: 'clusters',
                type: 'symbol',
                source: 'dokan_geolocation_map_main_data',
                filter: [ 'has', 'point_count' ],
                layout: {
                    'icon-image': 'dokan-marker-clusterer',
                    "icon-allow-overlap" : true,
                     "text-allow-overlap": true
                }
            } );

            self.map.addLayer( {
                id: 'cluster-count',
                type: 'symbol',
                source: 'dokan_geolocation_map_main_data',
                filter: [ 'has', 'point_count' ],
                layout: {
                    'text-field': '{point_count_abbreviated}',
                    'text-font': [ 'DIN Offc Pro Medium', 'Arial Unicode MS Bold' ],
                    'text-size': 12,
                },
                paint: {
                    'text-color': 'rgb(253, 218, 206)',
                }
            } );

            self.map.addLayer( {
                id: 'unclustered-point',
                type: 'symbol',
                source: 'dokan_geolocation_map_main_data',
                filter: [ '!', [ 'has', 'point_count' ] ],
                layout: {
                    'icon-image': 'dokan-marker-image',
                    "icon-allow-overlap" : true,
                     "text-allow-overlap": true
                }
            } );

            self.map.on( 'click', 'clusters', function( e ) {
                var renderedFeatures = self.map.queryRenderedFeatures( e.point, { layers: [ 'clusters' ] } );
                var clusterId = renderedFeatures[0].properties.cluster_id;
                var maxZoom = 9;

                self.map.getSource( 'dokan_geolocation_map_main_data' )
                    .getClusterLeaves( clusterId, 255, 0, function ( error, features ) {
                        if ( ( self.map.getZoom() > maxZoom ) && ( features.length > 1 ) ) {
                            var html = '<div class="white-popup dokan-geo-map-info-windows-in-popup">',
                                i = 0;

                            for ( i = 0; i < features.length; i++ ) {
                                html += self.getInfoWindowContent( features[i].properties.info );
                            }

                            html += '</div>';

                            $.magnificPopup.open({
                                items: {
                                    type: 'inline',
                                    src: html
                                }
                            });

                        } else {
                            self.map.getSource( 'dokan_geolocation_map_main_data' )
                                .getClusterExpansionZoom( clusterId, function( err, zoom ) {
                                    if ( ! err ) {
                                        self.map.easeTo( {
                                            center: features[0].geometry.coordinates,
                                            zoom: zoom
                                        } );
                                    };
                                });
                        }
                    } );
            });

            self.map.on( 'mouseenter', 'clusters', function() {
                self.map.getCanvas().style.cursor = 'pointer';
            });

            self.map.on( 'mouseleave', 'clusters', function() {
                self.map.getCanvas().style.cursor = '';
            } );

            self.map.on( 'click', 'unclustered-point', function( e ) {
                var features = self.map.queryRenderedFeatures( e.point, { layers: [ 'unclustered-point' ] } );
                var feature = features[0];
                var info = feature.properties.info;

                if ( info ) {
                    self.map.easeTo( {
                        center: e.lngLat,
                    } );

                    new mapboxgl.Popup( { closeOnClick: true } )
                        .setLngLat( e.lngLat )
                        .setHTML( self.getInfoWindowContent( info ) )
                        .setMaxWidth( '654px' )
                        .addTo( self.map );
                }
            } );
        },

        getInfoWindowContent: function ( info ) {
            if ( typeof info === 'string' ) {
                info = JSON.parse( info );
            }

            var content = DokanGeo.info_window_template,
                infoProp;

            for ( infoProp in info ) {
                content = content.replace( '{' + infoProp + '}', info[infoProp] );
            }

            return content;
        }
    };

    if ( $( '#dokan-geolocation-locations-map' ).length && DokanGeo.mapbox_access_token ) {
        DokanGeo.LocationsMaps.init();
    }
} )( jQuery );
