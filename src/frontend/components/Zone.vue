<template>
    <div class="zone-component">
        <div class="dokan-alert dokan-alert-success" v-if="successMessage">
            <span v-html="successMessage"></span>
            <a class="dokan-close" data-dismiss="alert">&times;</a>
        </div>

        <form action="" method="post">
            <div class="dokan-form-group dokan-clearfix">
                <label class="dokan-w4 dokan-control-label dokan-text-right" for="">
                    {{ i18n.zone_name }} :
                </label>
                <div class="dokan-w5 dokan-text-left">
                    {{ zone.data.zone_name }}
                </div>
            </div>

            <div class="dokan-form-group dokan-clearfix">
                <label class="dokan-w4 dokan-control-label dokan-text-right" for="">
                    {{ i18n.zone_location }} :
                </label>
                <div class="dokan-w5 dokan-text-left">
                    <p>{{ zone.formatted_zone_location }}</p>

                    <a href="#" v-if="showLimitLocationLink && this.$route.params.zoneID != 0" class="limit-location-link" @click.preventx="wantToSetLocation">
                        <switches :enabled="wantToLimitLocation" @input="wantToSetLocation"></switches>
                        <span>{{ i18n.limit_zone_location }}</span>
                    </a>
                </div>
            </div>

            <div class="dokan-form-group dokan-clearfix" v-if="wantToLimitLocation && showCountryList">
                <label class="dokan-w4 dokan-control-label dokan-text-right">
                    {{ i18n.select_country }}
                </label>
                <div class="dokan-w5 dokan-text-left">
                    <multiselect v-model="country" :options="countryList" @input="setStateForChosenCountry" :placeholder="i18n.select_country" :multiple="true" label="name" track-by="code">
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
                    {{ i18n.select_state }}
                </label>
                <div class="dokan-w5 dokan-text-left">
                    <multiselect v-model="state" :options="stateList" :placeholder="i18n.select_state" :multiple="true" label="name" track-by="code">
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
                    {{ i18n.select_postcode }} <i class="fa fa-question-circle" v-tooltip :title="i18n.postcode_help_text"></i>
                </label>
                <div class="dokan-w5 dokan-text-left">
                    <input name="zone_postcode" id="zone_postcode" class="dokan-form-control" v-model="postcode">
                </div>
            </div>

            <div class="dokan-edit-row zone-method-wrapper">
                <div class="dokan-section-heading" data-togglehandler="dokan_product_inventory">
                    <h2><i class="fa fa-truck" aria-hidden="true"></i> {{ i18n.shipping_method }}</h2>
                    <p>{{ i18n.shipping_method_help }}</p>
                    <div class="dokan-clearfix"></div>
                </div>

                <div class="dokan-section-content">
                    <table class="dokan-table zone-method-table">
                        <thead>
                            <tr>
                                <th class="title">{{ i18n.method_title }}</th>
                                <th class="enabled">{{ i18n.status }}</th>
                                <th class="description">{{ i18n.description }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-if="Object.keys( zoneShippingMethod ).length">
                                <tr v-for="method in zoneShippingMethod">
                                    <td>
                                        {{ method.title }}
                                        <div class="row-actions">
                                            <span class="edit"><a href="#" @click.prevent="editShippingMethod( method )">{{ i18n.edit }}</a> | </span>
                                            <span class="delete"><a href="#" @click.prevent="deleteShippingMethod( method )">{{ i18n.delete }}</a></span>
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
                                        {{ i18n.no_method_found }}
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div><!-- .dokan-side-right -->

                <div class="dokan-section-footer">
                    <a href="#" class="dokan-btn dokan-btn-theme" @click.prevent="showAddShippingMethodModal=true"><i class="fa fa-plus"></i> {{ i18n.add_method }}</a>
                </div>
            </div><!-- .dokan-product-inventory -->

            <div class="dokan-form-group">
                <input type="submit" class="dokan-btn dokan-btn-theme dokan-right" @click.prevent="saveZoneSettings" value="Save Changes">
            </div>

            <div class="dokan-clearfix"></div>
        </form>

        <modal
            title="Add Shipping Methods"
            v-if="showAddShippingMethodModal"
            @close="showAddShippingMethodModal = false"
        >
            <template slot="body">
                <p>{{ i18n.choose_shipping_help_text }}</p>
                <select class="dokan-form-control" v-model="shipping_method" model="shipping_method" id="shipping_method">
                    <option value="">&dash; {{ i18n.select_method }} &dash;</option>
                    <option value="flat_rate">{{ i18n.flat_rate }}</option>
                    <option value="local_pickup">{{ i18n.local_pickup }}</option>
                    <option value="free_shipping">{{ i18n.free_pickup }}</option>
                    <!-- <option value="custom">Custom Shipping</option> -->
                </select>
            </template>

            <template slot="footer">
                <button class="button button-primary button-large" @click.prevent="addNewMethod">{{ i18n.add_method }}</button>
            </template>
        </modal>

        <modal
            title="Edit Shipping Methods"
            v-if="editShippingMethodModal"
            @close="editShippingMethodModal = false"
        >
            <template slot="body" v-if="editShippingMethodData.method_id != 'free_shipping'">
                <div class="dokan-form-group">
                    <label for="method_title">{{ i18n.title }}</label>
                    <input type="text" id="method_title" class="dokan-form-control" v-model="editShippingMethodData.settings.title" placeholder="Enter method title">
                </div>

                <div class="dokan-form-group">
                    <label for="method_cost">{{ i18n.cost }}</label>
                    <input type="text" id="method_cost" class="dokan-form-control" v-model="editShippingMethodData.settings.cost" placeholder="0.00">
                    <span class="description" v-if="editShippingMethodData.method_id == 'flat_rate'" v-html="i18n.cost_desc"></span>
                </div>

                <div class="dokan-form-group">
                    <label for="method_tax_status">{{ i18n.tax_status }}</label>
                    <select v-model="editShippingMethodData.settings.tax_status" id="method_tax_status" class="dokan-form-control">
                        <option value="none">{{ i18n.none }}</option>
                        <option value="taxable">{{ i18n.taxable }}</option>
                    </select>
                </div>

                <div class="dokan-form-group">
                    <label for="method_description">{{ i18n.description }}</label>
                    <textarea v-model="editShippingMethodData.settings.description" id="method_description" class="dokan-form-control">{{ editShippingMethodData.settings.description }}</textarea>
                </div>

                <template v-if="'flat_rate' == editShippingMethodData.method_id">
                    <hr>
                    <div class="dokan-form-group">
                        <h3>{{ i18n.shipping_class_cost }}</h3>
                        <span class="description">{{ i18n.shipping_class_cost_help_text }}</span>
                    </div>
                    <template v-for="shippingClass in shippingClasses">
                        <div class="dokan-form-group">
                            <label :for="shippingClass.slug">{{ shippingClass.name }} {{ i18n.shipping_class_cost }}</label>
                            <input type="text" :id="shippingClass.slug" class="dokan-form-control" v-model="editShippingMethodData.settings['class_cost_' + shippingClass.term_id]" placeholder="N\A">
                            <span class="description" v-if="editShippingMethodData.method_id == 'flat_rate'" v-html="i18n.cost_desc"></span>
                        </div>
                    </template>

                    <div class="dokan-form-group">
                        <label for="no_class_cost">{{ i18n.no_shipping_class_cost }}</label>
                        <input type="text" id="no_class_cost" class="dokan-form-control" v-model="editShippingMethodData.settings.no_class_cost" placeholder="N\A">
                        <span class="description" v-if="editShippingMethodData.method_id == 'flat_rate'" v-html="i18n.cost_desc"></span>
                    </div>

                    <div class="dokan-form-group">
                        <label for="calculation_type">{{ i18n.calculation_type }}</label>
                        <select v-model="editShippingMethodData.settings.calculation_type" id="calculation_type" class="dokan-form-control">
                            <option value="class">{{ i18n.per_class }}</option>
                            <option value="order" :selected="true">{{ i18n.per_order }}</option>
                        </select>
                    </div>

                </template>
            </template>

            <template slot="body" v-else>
                <div class="dokan-form-group">
                    <label for="method_title">{{ i18n.title }}</label>
                    <input type="text" id="method_title" class="dokan-form-control" v-model="editShippingMethodData.settings.title" placeholder="Enter method title">
                </div>

                <div class="dokan-form-group">
                    <label for="minimum_order_amount">{{ i18n.minimum_order_amount }}</label>
                    <input type="text" id="minimum_order_amount" class="dokan-form-control" v-model="editShippingMethodData.settings.min_amount" placeholder="0.00">
                    <span class="description" v-if="editShippingMethodData.method_id == 'flat_rate'" v-html="i18n.cost_desc"></span>
                </div>
            </template>

            <template slot="footer">
                <button class="button button-primary button-large" @click.prevent="updateShippingMethodSettings">{{ i18n.save_settings }}</button>
            </template>
        </modal>
    </div>
</template>

<script>
let Switches = dokan_get_lib('Switches');
let Modal = dokan_get_lib('Modal');

export default {

    name: 'Zone',

    components: {
        Switches,
        Modal
    },

    data () {
        return {
            i18n: {},
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
                settings: {}
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
                    country: self.wantToLimitLocation ? self.country : [],
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
                        self.wantToLimitLocation = true;
                        var locationResp = _.groupBy( resp.data.locations, 'type' );

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
        this.i18n = dokanShipping.i18n;
        this.fetchZone();
    }
}
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

    }

</style>