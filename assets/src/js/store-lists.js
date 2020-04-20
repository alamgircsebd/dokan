;(function($) {
    var storeListsPro = {
        /**
         * Item array holder
         *
         * @type array
         */
        itemArray: [],

        /**
         * Item ID holder
         *
         * @type array
         */
        itemSlugs: [],

        /**
         * Selected category text holder
         *
         * @type string
         */
        itemString: '',

        /**
         * Init all the methods
         *
         * @return void
         */
        init: function() {
            $( '#dokan-store-listing-filter-form-wrap .store-lists-category .category-input' ).on( 'click', this.toggleCategory );
            $( '#dokan-store-listing-filter-form-wrap .store-lists-category .category-box ul li' ).on( 'click', this.selectCategory );
            $( '#dokan-store-listing-filter-form-wrap .featured.item #featured' ).on( 'change', this.toggleFeatured );
            $( '#dokan-store-listing-filter-form-wrap .open-now.item #open-now' ).on( 'change', this.toggleIsOpen );
            $( '#dokan-store-listing-filter-form-wrap .store-ratings.item i' ).on( 'click', this.setRatings );

            const params = dokan.storeLists.getParams();

            if ( params.length ) {
                params.forEach( function( param ) {
                    storeListsPro.setParams( Object.keys( param ), Object.values( param ) );
                });
            }

            this.makeStyleAdjustment();
        },

        /**
         * Toggle category
         *
         * @return void
         */
        toggleCategory: function() {
            $( '.store-lists-category .category-box' ).slideToggle();
            $( '.store-lists-category .category-input .dokan-icon' ).toggleClass( 'dashicons-arrow-down-alt2 dashicons-arrow-up-alt2' );
        },

        /**
         * Select Category
         *
         * @param  string event
         *
         * @return void
         */
        selectCategory: function( event ) {
            const item = $( event.target );
            const currentItem = item.text().trim();
            const currentItemSlug = item.data( 'slug' );
            const self = storeListsPro;

            item.toggleClass( 'dokan-btn-theme' );

            if ( ! self.itemSlugs.includes( currentItemSlug ) ) {
                self.itemArray.push( currentItem );
                self.itemSlugs.push( currentItemSlug );
            } else {
                let itemToRemove = self.itemSlugs.indexOf( currentItemSlug );
                self.itemArray.splice( itemToRemove, 1 );
                self.itemSlugs.splice( itemToRemove, 1 );
            }

            dokan.storeLists.query.store_categories = self.itemSlugs;
            const itemString = self.itemArray.join( ', ' );

            self.setCategoryHolder( itemString );
        },

        /**
         * Toggle Featured
         *
         * @param  string
         *
         * @return void
         */
        toggleFeatured: function( event ) {
            delete dokan.storeLists.query.featured;

            if ( event.target.checked ) {
                dokan.storeLists.query.featured = 'yes';
            }
        },

        /**
         * Toogle is Open
         *
         * @param  string
         *
         * @return void
         */
        toggleIsOpen: function( event ) {
            delete dokan.storeLists.query.open_now;

            if ( event.target.checked ) {
                dokan.storeLists.query.open_now = 'yes';
            }
        },

        /**
         * Set Ratings
         *
         * @param string
         *
         * @return void
         */
        setRatings: function( event ) {
            event.preventDefault();

            const currentItem = $( event.target );
            const parent = currentItem.parent();

            parent.addClass( 'selected' );

            [ ...parent.find( 'i' ) ].forEach( function( item ) {
                if ( currentItem.is( item ) ) {

                    if ( $( item ).hasClass( 'active' ) ) {
                        parent.removeClass( 'selected' );
                        $( item ).removeClass( 'active' );
                    } else {
                        $( item ).addClass( 'active' );
                    }

                } else {
                    $( item ).removeClass( 'active' );
                }
            });

            const rating = dokan.storeLists.query.rating;

            if ( rating != currentItem.data( 'rating' ) ) {
                dokan.storeLists.query.rating = currentItem.data( 'rating' );
            } else {
                delete dokan.storeLists.query.rating;
            }
        },

        /**
         * Set Parrams
         *
         * @param string key
         * @param mix value
         */
        setParams: function( key, value ) {
            const self = storeListsPro;
            const dokan = window.dokan.storeLists;

            key.forEach( function( param, index ) {
                const charIndex = param.indexOf( '[' );

                // If charIndex is greater than 0, then it's an array; ei: category[]=cat_1
                if ( charIndex > 0 ) {
                    self.itemSlugs.push( value[ index ] );
                    dokan.query[ param.substr( 0, charIndex ) ] = self.itemSlugs
                }
            });

            const categoryHolder = $( '.category-items' );

            if ( dokan.cateItemStringArray.length ) {
                self.itemArray = dokan.cateItemStringArray;
                const strings = self.itemArray.join( ', ' );

                self.setCategoryHolder( strings );
            }
        },

        /**
         * Set Category Holder
         *
         * @param string
         *
         * @return void
         */
        setCategoryHolder: function( string ) {
            const categoryHolder = $( '.category-items' );

            if ( ! string ) {
                return categoryHolder.text( dokan.all_categories );
            }

            if ( string.length > 15 ) {
                categoryHolder.text( ' ' ).append( string.substr( 0, 15 ) + '...' );
            } else {
                categoryHolder.text( ' ' ).append( string );
            }
        },

        /**
         * If only 2 elments found, make them stay closer ( open now and featured )
         *
         * Push apply button a little down
         *
         * @return void
         */
        makeStyleAdjustment() {
            const element = $('.store-lists-other-filter-wrap');

            if ( element && element.children() && element.children().length === 2 ) {
                if ( element.children().first() && element.children().first().hasClass( 'featured' ) ) {
                    $( '#dokan-store-listing-filter-form-wrap .apply-filter' ).css( 'margin-top', '15px' );
                }
            }
        }
    };

    if ( window.dokan && window.dokan.storeLists ) {
        storeListsPro.init();
    }

})(jQuery);