<template>
    <div class="zone-component">
        <div class="dokan-alert dokan-alert-success" v-if="successMessage">
            <span v-html="successMessage"></span>
            <a class="dokan-close" data-dismiss="alert">&times;</a>
        </div>

        <form action="" method="post">
            <div class="dokan-form-group dokan-clearfix">
                <label class="dokan-w4 dokan-control-label dokan-text-right" for="">
                    {{ __( 'Zone Name', 'dokan' ) }} :
                </label>
                <div class="dokan-w5 dokan-text-left">
                    {{ zone.data.zone_name }}
                </div>
            </div>

            <div class="dokan-form-group dokan-clearfix">
                <label class="dokan-w4 dokan-control-label dokan-text-right" for="">
                    {{ __( 'Zone Location', 'dokan' ) }} :
                </label>
                <div class="dokan-w5 dokan-text-left">
                    <p>{{ zone.formatted_zone_location }}</p>

                    <a href="#" v-if="showLimitLocationLink && this.$route.params.zoneID != 0" class="limit-location-link" @click.prevent="wantToSetLocation">
                        <switches :enabled="wantToLimitLocation" @input="wantToSetLocation"></switches>
                        <span>{{ __( 'Limit your zone location', 'dokan' ) }}</span>
                    </a>
                </div>
            </div>

            <div class="dokan-form-group dokan-clearfix" v-if="wantToLimitLocation && showCountryList">
                <label class="dokan-w4 dokan-control-label dokan-text-right">
                    {{ __( 'Select Country', 'dokan' ) }}
                </label>
                <div class="dokan-w5 dokan-text-left">
                    <multiselect v-model="country" :options="countryList" @input="setStateForChosenCountry" :placeholder="__( 'Select Country', 'dokan' )" :multiple="true" label="name" track-by="code">
                        <template slot="option" slot-scope="props">
                            <span v-html="props.option.name"></span>
                        </template>
                        <template slot="tag" slot-scope="props">
                            <span class="multiselect__tag">
                                <span v-html="props.option.name"></span>
                                <i aria-hidden="true" tabindex="1" @keydown.enter.prevent="props.remove(props.option)"  @mousedown.prevent="props.remove(props.option)" class="multiselect__tag-icon"></i>
                            </span>
                        </template>
                    </multiselect>
                </div>
            </div>

            <div class="dokan-form-group dokan-clearfix" v-if="wantToLimitLocation && showStateList && stateList.length">
                <label class="dokan-w4 dokan-control-label dokan-text-right" for="">
                    {{ __( 'Select States', 'dokan' ) }}
                </label>
                <div class="dokan-w5 dokan-text-left">
                    <multiselect v-model="state" :options="stateList" :placeholder="__( 'Select States', 'dokan' )" :multiple="true" label="name" track-by="code">
                        <template slot="option" slot-scope="props">
                            <span v-html="props.option.name"></span>
                        </template>
                        <template slot="tag" slot-scope="props">
                            <span class="multiselect__tag">
                                <span v-html="props.option.name"></span>
                                <i aria-hidden="true" tabindex="1" @keydown.enter.prevent="props.remove(props.option)"  @mousedown.prevent="props.remove(props.option)" class="multiselect__tag-icon"></i>
                            </span>
                        </template>
                    </multiselect>
                </div>
            </div>

            <div class="dokan-form-group dokan-clearfix" v-if="wantToLimitLocation && showPostCodeList">
                <label class="dokan-w4 dokan-control-label dokan-text-right" for="">
                    {{ __( 'Set your postcode', 'dokan' ) }} <i class="fa fa-question-circle" v-tooltip :title="postCodeTitle"></i>
                </label>
                <div class="dokan-w5 dokan-text-left">
                    <input name="zone_postcode" id="zone_postcode" class="dokan-form-control" v-model="postcode">
                </div>
            </div>

            <div class="dokan-edit-row zone-method-wrapper">
                <div class="dokan-section-heading" data-togglehandler="dokan_product_inventory">
                    <h2><i class="fa fa-truck" aria-hidden="true"></i> {{ __( 'Shipping Method', 'dokan' ) }}</h2>
                    <p>{{ __( 'Add your shipping method for appropiate zone', 'dokan' ) }}</p>
                    <div class="dokan-clearfix"></div>
                </div>

                <div class="dokan-section-content">
                    <table class="dokan-table zone-method-table">
                        <thead>
                            <tr>
                                <th class="title">{{ __( 'Method Title', 'dokan' ) }}</th>
                                <th class="enabled">{{ __( 'Status', 'dokan' ) }}</th>
                                <th class="description">{{ __( 'Description', 'dokan' ) }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-if="Object.keys( zoneShippingMethod ).length">
                                <tr v-for="method in zoneShippingMethod">
                                    <td>
                                        {{ method.title }}
                                        <div class="row-actions">
                                            <span class="edit"><a href="#" @click.prevent="editShippingMethod( method )">{{ __( 'Edit', 'dokan' ) }}</a> | </span>
                                            <span class="delete"><a href="#" @click.prevent="deleteShippingMethod( method )">{{ __( 'Delete', 'dokan' ) }}</a></span>
                                        </div>
                                    </td>
                                    <td>
                                        <switches :enabled="method.enabled == 'yes'" :value="method.instance_id" @input="onSwitch"></switches>
                                    </td>
                                    <td>
                                        {{ method.settings.description }}
                                    </td>
                                </tr>
                            </template>
                            <template v-else>
                                <tr>
                                    <td colspan="3">
                                        {{ __( 'No method found', 'dokan' ) }}
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div><!-- .dokan-side-right -->

                <div class="dokan-section-footer">
                    <a href="#" class="dokan-btn dokan-btn-theme" @click.prevent="showAddShippingMethodModal=true"><i class="fa fa-plus"></i> {{ __( 'Add Shipping Method', 'dokan' ) }}</a>
                </div>
            </div><!-- .dokan-product-inventory -->

            <div class="dokan-form-group">
                <router-link :to="{ name: 'Main' }">{{ __( '&larr; Back to Zone List', 'dokan' ) }}</router-link>
                <input type="submit" class="dokan-btn dokan-btn-theme dokan-right" @click.prevent="saveZoneSettings" :value="__( 'Save Changes', 'dokan' )">
            </div>

            <div class="dokan-clearfix"></div>
        </form>

        <modal
            :title="__( 'Add Shipping Method', 'dokan' )"
            v-if="showAddShippingMethodModal"
            @close="showAddShippingMethodModal = false"
        >
            <template slot="body">
                <p>{{ __( 'Choose the shipping method you wish to add. Only shipping methods which support zones are listed.', 'dokan' ) }}</p>
                <select class="dokan-form-control" v-model="shipping_method" model="shipping_method" id="shipping_method">
                    <option value="">&dash; {{ __( 'Select a Method', 'dokan' ) }} &dash;</option>
                    <option v-for="(availableMethod, key) in availableMethods" :value="key">{{ availableMethod }}</option>
                    <!-- <option value="custom">Custom Shipping</option> -->
                </select>
            </template>

            <template slot="footer">
                <button class="button button-primary button-large" @click.prevent="addNewMethod">{{ __( 'Add Shipping Method', 'dokan' ) }}</button>
            </template>
        </modal>

        <modal
            :title="editShippingMethodTitle"
            v-if="editShippingMethodModal"
            @close="editShippingMethodModal = false"
        >
            <template slot="body" v-if="editShippingMethodData.method_id != 'free_shipping'">
                <div class="dokan-form-group">
                    <label for="method_title">{{ __( 'Title', 'dokan' ) }}</label>
                    <input type="text" id="method_title" class="dokan-form-control" v-model="editShippingMethodData.settings.title" :placeholder="__( 'Enter method title', 'dokan' )">
                </div>

                <div class="dokan-form-group">
                    <label for="method_cost">{{ __( 'Cost', 'dokan' ) }}</label>
                    <input type="text" id="method_cost" class="dokan-form-control" v-model="editShippingMethodData.settings.cost" placeholder="0.00">
                    <span class="description" v-if="editShippingMethodData.method_id == 'flat_rate'" v-html="cost_description">
                    </span>
                </div>

                <div class="dokan-form-group">
                    <label for="method_tax_status">{{ __( 'Tax Status', 'dokan' ) }}</label>
                    <select v-model="editShippingMethodData.settings.tax_status" id="method_tax_status" class="dokan-form-control">
                        <option value="none">{{ __( 'None', 'dokan' ) }}</option>
                        <option value="taxable">{{ __( 'Taxable', 'dokan' ) }}</option>
                    </select>
                </div>

                <div class="dokan-form-group">
                    <label for="method_description">{{ __( 'Description', 'dokan' ) }}</label>
                    <textarea v-model="editShippingMethodData.settings.description" id="method_description" class="dokan-form-control">{{ editShippingMethodData.settings.description }}</textarea>
                </div>

                <template v-if="'flat_rate' == editShippingMethodData.method_id">
                    <hr>
                    <div class="dokan-form-group">
                        <h3>{{ __( 'Shipping Class Cost', 'dokan' ) }}</h3>
                        <span class="description">{{ __( 'These costs can optionally be added based on the product shipping class', 'dokan' ) }}</span>
                    </div>
                    <template v-for="shippingClass in shippingClasses">
                        <div class="dokan-form-group">
                            <label :for="shippingClass.slug">"{{ shippingClass.name }}" {{ __( 'shipping class cost', 'dokan' ) }}</label>
                            <input type="text" :id="shippingClass.slug" class="dokan-form-control" v-model="editShippingMethodData.settings['class_cost_' + shippingClass.term_id]" placeholder="N\A">
                            <span class="description" v-if="editShippingMethodData.method_id == 'flat_rate'" v-html="cost_description"></span>
                        </div>
                    </template>

                    <div class="dokan-form-group">
                        <label for="no_class_cost">{{ __( 'No shipping class cost', 'dokan' ) }}</label>
                        <input type="text" id="no_class_cost" class="dokan-form-control" v-model="editShippingMethodData.settings.no_class_cost" placeholder="N\A">
                        <span class="description" v-if="editShippingMethodData.method_id == 'flat_rate'" v-html="cost_description"></span>
                    </div>

                    <div class="dokan-form-group">
                        <label for="calculation_type">{{ __( 'Calculation type', 'dokan' ) }}</label>
                        <select v-model="editShippingMethodData.settings.calculation_type" id="calculation_type" class="dokan-form-control">
                            <option value="class">{{ __( 'Per class: Charge shipping for each shipping class individually', 'dokan' ) }}</option>
                            <option value="order" :selected="true">{{ __( 'Per order: Charge shipping for the most expensive shipping class', 'dokan' ) }}</option>
                        </select>
                    </div>

                </template>
            </template>

            <template slot="body" v-else>
                <div class="dokan-form-group">
                    <label for="method_title">{{ __( 'Method Title', 'dokan' ) }}</label>
                    <input type="text" id="method_title" class="dokan-form-control" v-model="editShippingMethodData.settings.title" :placeholder="__( 'Enter method title', 'dokan' )">
                </div>

                <div class="dokan-form-group">
                    <label for="minimum_order_amount">{{ __( 'Minimum order amount for free shipping', 'dokan' ) }}</label>
                    <input type="text" id="minimum_order_amount" class="dokan-form-control" v-model="editShippingMethodData.settings.min_amount" placeholder="0.00">
                    <span class="description" v-if="editShippingMethodData.method_id == 'flat_rate'" v-html="cost_description"></span>
                </div>
            </template>

            <template slot="footer">
                <button class="button button-primary button-large" @click.prevent="updateShippingMethodSettings">{{ __( 'Save Settings', 'dokan' ) }}</button>
            </template>
        </modal>
    </div>
</template>

<script>
let Switches    = dokan_get_lib('Switches');
let Modal       = dokan_get_lib('Modal');
let Multiselect = dokan_get_lib('Multiselect');

export default {

    name: 'Zone',

    components: {
        Switches,
        Modal,
        Multiselect
    },

    data () {
        return {
            successMessage: '',
            showAddShippingMethodModal: false,
            editShippingMethodModal: false,
            wantToLimitLocation: false,

            zone: {
                data : {
                    zone_name : ''
                },
                formatted_zone_location: ''
            },

            state: [],
            country: [],
            postcode: [],

            showCountryList: false,
            showStateList: false,
            showPostCodeList: false,

            stateList: [],
            countryList: [],

            zoneShippingMethod: {},
            shipping_method: '',
            editShippingMethodData: {
                method_id: '',
                instance_id: '0',
                settings: {
                    title: '',
                    cost: '0',
                    description: this.__( 'Lets you charge a rate for shipping', 'dokan' ),
                    tax_status: 'none'
                }
            },
            cost_description: this.__( 'Enter a cost (excl. tax) or sum, e.g. <code>10.00 * [qty]</code>. Use <code>[qty]</code> for the number of items, <code>[cost]</code> for the total cost of items, and <code>[fee percent=\'10\' min_fee=\'20\' max_fee=\'\']</code> for percentage based fees.', 'dokan' ),
            editShippingMethodTitle: this.__( 'Edit Shipping Method', 'dokan' ),
            postCodeTitle: this.__( 'Postcodes need to be comma separated', 'dokan' ),
            availableMethods: {
                flat_rate: this.__( 'Flat Rate', 'dokan' ),
                local_pickup: this.__( 'Local Pickup', 'dokan' ),
                free_shipping: this.__( 'Free Shipping', 'dokan' )
            }
        }
    },

    computed: {
        zoneLocation() {
            return _.groupBy( this.zone.data.zone_locations, 'type' );
        },

        showLimitLocationLink() {
            return this.zoneLocation.postcode === undefined;
        },

        shippingClasses() {
            return dokanShipping.shipping_class;
        }
    },

    methods: {
        wantToSetLocation() {
            this.wantToLimitLocation = !this.wantToLimitLocation;
        },

        onSwitch( checked, value ) {
            var self = this,
                data = {
                    action: 'dokan-toggle-shipping-method-enabled',
                    zoneID: self.$route.params.zoneID,
                    instance_id: value,
                    checked: checked,
                    nonce: dokan.nonce
                };

            jQuery('.zone-method-wrapper').block({ message: null, overlayCSS: { background: '#fff url(' + dokan.ajax_loader + ') no-repeat center', opacity: 0.6 } });

            jQuery.post( dokan.ajaxurl, data, function(resp) {
                if ( resp.success ) {
                    self.successMessage = resp.data;
                    jQuery('.zone-method-wrapper').unblock();
                    setTimeout( function() {
                        self.successMessage = '';
                    }, 2000)
                } else {
                    jQuery('.zone-method-wrapper').unblock();
                    alert( resp.data );
                }
            });

        },

        saveZoneSettings() {
            var self = this,
                data = {
                    action: 'dokan-save-zone-settings',
                    country: self.country,
                    state: self.wantToLimitLocation ? self.state: [],
                    postcode: self.wantToLimitLocation ? self.postcode : '',
                    zoneID: self.$route.params.zoneID,
                    nonce: dokan.nonce
                };

            jQuery('#dokan-shipping-zone').block({ message: null, overlayCSS: { background: '#fff url(' + dokan.ajax_loader + ') no-repeat center', opacity: 0.6 } });

            jQuery.post( dokan.ajaxurl, data, function(resp) {
                if ( resp.success ) {
                    self.successMessage = resp.data;
                    jQuery('#dokan-shipping-zone').unblock();
                } else {
                    jQuery('#dokan-shipping-zone').unblock();
                    alert( resp.data );
                }
            });

        },

        editShippingMethod( method ) {
            this.editShippingMethodData = {
                instance_id : method.instance_id,
                method_id   : method.id,
                settings    : method.settings
            };

            this.editShippingMethodModal = true;
        },

        deleteShippingMethod( method ) {
            var self = this,
                data = {
                    action: 'dokan-delete-shipping-method',
                    zoneID: self.$route.params.zoneID,
                    instance_id : method.instance_id,
                    nonce: dokan.nonce
                };

            jQuery('.zone-method-wrapper').block({ message: null, overlayCSS: { background: '#fff url(' + dokan.ajax_loader + ') no-repeat center', opacity: 0.6 } });

            jQuery.post( dokan.ajaxurl, data, function(resp) {
                if ( resp.success ) {
                    self.fetchZone();
                    self.successMessage = resp.data;
                    jQuery('.zone-method-wrapper').unblock();
                } else {
                    jQuery('.zone-method-wrapper').unblock();
                    alert( resp.data );
                }
            });

        },

        updateShippingMethodSettings() {
            var self = this,
                data = {
                    action: 'dokan-update-shipping-method-settings',
                    data: self.editShippingMethodData,
                    zoneID: self.$route.params.zoneID,
                    nonce: dokan.nonce
                };

            jQuery('.zone-method-wrapper').block({ message: null, overlayCSS: { background: '#fff url(' + dokan.ajax_loader + ') no-repeat center', opacity: 0.6 } });

            jQuery.post( dokan.ajaxurl, data, function(resp) {
                if ( resp.success ) {
                    self.fetchZone();
                    self.editShippingMethodModal = false;
                    jQuery('.zone-method-wrapper').unblock();
                } else {
                    jQuery('.zone-method-wrapper').unblock();
                    alert( resp.data );
                }
            });
        },

        getStatesFromCountry( country ) {
            var states = [];

            _.each( country, function( code ) {

                if ( dokanShipping.states[code] === undefined ) {
                    return;
                }

                var stateArray =  Object.keys( dokanShipping.states[code] ).map( statecode => ( {
                    code: code + ':' + statecode,
                    name: dokanShipping.states[code][statecode]
                } ) );

                states = states.concat( states, stateArray);
            });

            return states;
        },

        getCountryFromContinent( continent ) {
            var country = [];

            _.each( continent, function( code ) {
                country = country.concat( dokanShipping.continents[code].countries );
            });

            return country.map( ( val ) => {
                return {
                    code : val,
                    name: dokanShipping.allowed_countries[val]
                };
            } );
        },

        setStateForChosenCountry( value, id ) {
            var country = _.pluck( value, 'code' );
            this.stateList = this.getStatesFromCountry( country );
        },

        fetchZone() {
            var self = this,
                data = {
                    action: 'dokan-get-shipping-zone',
                    zoneID: self.$route.params.zoneID,
                    nonce: dokan.nonce
                };

            jQuery('#dokan-shipping-zone').block({ message: null, overlayCSS: { background: '#fff url(' + dokan.ajax_loader + ') no-repeat center', opacity: 0.6 } });

            jQuery.post( dokan.ajaxurl, data, function(resp) {
                if ( resp.success ) {
                    self.zone = resp.data;
                    self.zoneShippingMethod = resp.data.shipping_methods;

                    if ( self.zone.locations.length < 1 ) {
                        self.zone.locations = resp.data.data.zone_locations;
                    }

                    var zoneLocationTypes = Object.keys(self.zoneLocation);
                    if (zoneLocationTypes.indexOf('postcode') < 0) {
                        if (zoneLocationTypes.indexOf('state') >= 0) {
                            self.showPostCodeList = true;
                        } else if (zoneLocationTypes.indexOf('country') >= 0) {
                            self.showStateList = true;
                            self.showPostCodeList = true;
                            var country = _.pluck( self.zoneLocation['country'], 'code' );
                            self.stateList = self.getStatesFromCountry( country );
                        } else if (zoneLocationTypes.indexOf('continent') >= 0) {
                            self.showCountryList = true;
                            self.showStateList = true;
                            self.showPostCodeList = true;
                            var continent = _.pluck( self.zoneLocation['continent'], 'code' );
                            self.countryList = self.getCountryFromContinent( continent );
                        }
                    }

                    if ( resp.data.locations.length > 0 ) {
                        var locationResp = _.groupBy( resp.data.locations, 'type' );

                        if ( Object.keys( locationResp ).includes( 'state' ) || Object.keys( locationResp ).includes( 'postcode' ) ) {
                            self.wantToLimitLocation = true;
                        }

                        Object.keys( locationResp ).forEach(function(key) {
                            if ( 'country' == key ) {
                                self.country = locationResp[key].map( countrydata => {
                                    return {
                                        code: countrydata.code,
                                        name: dokanShipping.allowed_countries[countrydata.code]
                                    }
                                });
                            } else if ( 'state' == key ) {
                                self.state = locationResp[key].map( statedata => {
                                    var stateCode = statedata.code.split(':');
                                    return {
                                        code: statedata.code,
                                        name: dokanShipping.states[stateCode[0]][stateCode[1]]
                                    }
                                });
                            } else if ( 'postcode' == key ) {
                                // Render comma string from postcode location array
                                self.postcode = _.pluck( locationResp[key], 'code').join(',');

                                if ( self.zoneLocation.postcode !== undefined ) {
                                    self.postcode = _.pluck( self.zoneLocation.postcode, 'code' ).join( ',' );
                                }
                            }
                        });
                    }

                    jQuery('#dokan-shipping-zone').unblock();

                } else {
                    jQuery('#dokan-shipping-zone').unblock();
                    alert( resp.data );
                }
            });
        },

        addNewMethod() {
            var self = this,
                data = {
                    action: 'dokan-add-shipping-method',
                    zoneID: self.$route.params.zoneID,
                    nonce: dokan.nonce,
                    method: self.shipping_method
                };

            data.settings = {
                title: self.shipping_method != '' ? this.availableMethods[self.shipping_method] : '',
                cost: '0',
                description: this.__( 'Lets you charge a rate for shipping', 'dokan' ),
                tax_status: 'none'
            };

            jQuery.post( dokan.ajaxurl, data, function(resp) {
                if ( resp.success ) {
                    self.fetchZone();
                    self.showAddShippingMethodModal = false;
                    self.successMessage = resp.data;
                } else {
                    alert( resp.data )
                }
            });
        }
    },

    created() {
        this.fetchZone();
    }
};
</script>

<style lang="less">
    .zone-component {

        a.limit-location-link {
            label.switch {
                top: 9px;
                margin-right: 5px;
            }
        }

        .dokan-text-right {
            text-align: right;
        }

        .dokan-control-label {
            padding-right: 25px;
        }

        .dokan-form-group{
            span.description {
                font-size: 12px;
                font-style: italic;
                color: #7b7b7b;
                margin-top: 6px;
                display: block;
            }
        }

        .dokan-edit-row {
            .dokan-section-footer {
                border-top: 1px solid #eee;
                padding: 15px;
            }
        }

        .zone-method-wrapper {
            margin: 30px 0px;


            table.zone-method-table {
                thead {
                    th {
                        border-bottom: none;
                        height: 45px;
                        vertical-align: middle;

                        &.title {
                            width: 30%;
                            padding-left: 15px;
                        }
                        &.enabled {
                            width: 10%;
                        }
                    }
                }

                tbody {
                    tr {
                        td {
                            padding: 15px;

                            &:first-child {
                                padding-left: 15px;
                            }

                            .row-actions {
                                visibility: hidden;
                                font-size: 12px;
                                color: #ccc;
                                clear: both;
                                padding-top: 8px;

                                .delete a {
                                    color: #A05;

                                    &:hover {
                                        color: red;
                                    }
                                }
                            }
                        }


                        &:hover .row-actions {
                            visibility: visible;
                        }
                    }
                }
            }
        }

        .dokan-modal {
            .dokan-modal-content {
                width: 700px;

                .modal-body {
                    max-height: 300px;
                }
            }
        }

        @media only screen and ( max-width: 750px ) {
            .dokan-modal {
                .dokan-modal-content {
                    width: 600px;
                }
            }
        }

        @media only screen and ( max-width: 667px ) {
            .dokan-modal {
                .dokan-modal-content {
                    width: 500px;
                }
            }
        }

        @media only screen and ( max-width: 568px ) {
            .dokan-modal {
                .dokan-modal-content {
                    width: 420px;
                }
            }
        }

        @media only screen and ( max-width: 480px ) {
            .dokan-modal {
                .dokan-modal-content {
                    width: 350px;
                }
            }
        }

        @media only screen and ( max-width: 360px ) {
            .dokan-modal {
                .dokan-modal-content {
                    width: 300px;
                }
            }
        }
    }

</style>
