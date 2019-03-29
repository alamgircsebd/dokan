<template>
    <div class="account-info">
        <div class="content-header">
            {{__( 'Address', 'dokan' )}}
        </div>

        <div class="content-body">
            <div class="dokan-form-group">

                <div class="column">
                    <label for="street-1">{{ __( 'Stree 1', 'dokan') }}</label>
                    <input type="text" class="dokan-form-input" v-model="vendorInfo.address.street_1" :placeholder="__( 'Type here', 'dokan')">
                </div>

                <div class="column">
                    <label for="street-2">{{ __( 'Street 2', 'dokan') }}</label>
                    <input type="text" class="dokan-form-input" v-model="vendorInfo.address.street_2" :placeholder="__( 'Type here', 'dokan')">
                </div>

                <div class="column">
                    <label for="city">{{ __( 'City', 'dokan') }}</label>
                    <input type="text" class="dokan-form-input" v-model="vendorInfo.address.city" :placeholder="__( 'Type here', 'dokan')">
                </div>

                <div class="column">
                    <label for="zip">{{ __( 'Zip', 'dokan') }}</label>
                    <input type="text" class="dokan-form-input" v-model="vendorInfo.address.zip" :placeholder="__( 'Type here', 'dokan')">
                </div>

                <div class="column">
                    <label for="country">{{ __( 'Country', 'dokan') }}</label>
                    <Multiselect @input="saveCountry" v-model="selectedCountry" :options="countries" :multiselect="false" label="name" track-by="name" :placeholder="__( 'Select Country', 'dokan' )" />
                </div>

                <div class="column">
                    <label for="state">{{ __( 'State', 'dokan') }}</label>
                    <Multiselect @input="saveState" v-model="selectedState" :options="getStatesFromCountryCode( selectedCode )" :multiselect="false" label="name" track-by="name" :placeholder="__( 'Select State', 'dokan' )" />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
let Multiselect = dokan_get_lib('Multiselect');

export default {
    name: 'VendorAddressFields',

    components: {
        Multiselect
    },

    props: {
        vendorInfo: {
            type: Object
        },
    },

    data() {
        return {
            countries: [],
            states: [],
            selectedCountry: {},
            selectedState: {},
        }
    },

    computed: {
        selectedCode() {
            // let selected = this.selectedCountry;
            let selected = this.vendorInfo.address.country;

            if ( '' !== selected ) {
                return selected;
            }

            return [];
        },
    },

    created() {
        this.countries = this.transformCountries( dokan.countries )
        this.states = dokan.states;

        let savedCountry = this.vendorInfo.address.country;
        let savedState = this.vendorInfo.address.state;

        if ( '' !== savedCountry ) {
            this.selectedCountry = {
                name: this.getCountryFromCountryCode( savedCountry ),
                code: savedCountry
            }

            this.selectedState = {
                name: this.getStateFromStateCode( savedState, savedCountry ),
                code: savedState
            }
        }
    },

    methods: {
        transformCountries( countryObject ) {
            let countries = [];

            for ( let key in countryObject ) {
                countries.push( {
                    name: countryObject[key],
                    code: key
                } );
            }

            return countries;
        },

        getCountryFromCountryCode( countryCode ) {
            if ( '' === countryCode ) {
                return;
            }

            return dokan.countries[countryCode];
        },

        getStateFromStateCode( stateCode, countryCode ) {
            if ( '' === stateCode ) {
                return;
            }

            let states = dokan.states[countryCode];
            let state  = states && states[stateCode];

            return typeof state !== 'undefined' ? state : [];
        },

        getStatesFromCountryCode( countryCode ) {
            if ( '' === countryCode ) {
                return;
            }

            let states       = [];
            let statesObject = this.states;

            for ( let state in statesObject ) {
                if ( state !== countryCode ) {
                    continue;
                }

                if ( statesObject[state] && statesObject[state].length < 1 ) {
                    continue;
                }

                for ( let name in statesObject[state] ) {
                    states.push( {
                        name: statesObject[state][name],
                        code: name
                    } );
                }
            }

            return states;
        },

        saveCountry( value ) {
            if ( ! value ) return;

            // if reset default state values
            this.vendorInfo.address.state = null;
            this.selectedState = {};

            this.vendorInfo.address.country = value.code;
        },

        saveState( value ) {
            if ( ! value ) return;

            this.vendorInfo.address.state = value.code;
        }
    }
};
</script>