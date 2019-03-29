<template>
    <div :class="{'payment-info': true, 'edit-mode': getId()}">
        <div class="content-header">
            {{__( 'Payment Options', 'dokan' )}}
        </div>

        <div class="content-body">
            <div class="dokan-form-group">
                <div class="column">
                    <label for="account-name">{{ __( 'Account Name', 'dokan') }}</label>
                    <input type="text" class="dokan-form-input" v-model="vendorInfo.payment.bank.ac_name" :placeholder="__( 'Type here', 'dokan')">
                </div>

                <div class="column">
                    <label for="account-number">{{ __( 'Account Number', 'dokan') }}</label>
                    <input type="text" class="dokan-form-input" v-model="vendorInfo.payment.bank.ac_number" :placeholder="__( 'Type here', 'dokan')">
                </div>

                <div class="column">
                    <label for="bank-name">{{ __( 'Bank Name', 'dokan') }}</label>
                    <input type="text" class="dokan-form-input" v-model="vendorInfo.payment.bank.bank_name" :placeholder="__( 'Type here', 'dokan')">
                </div>

                <div class="column">
                    <label for="bank-address">{{ __( 'Bank Address', 'dokan') }}</label>
                    <input type="text" class="dokan-form-input" v-model="vendorInfo.payment.bank.bank_addr" :placeholder="__( 'Type here', 'dokan')">
                </div>

                <div class="column">
                    <label for="routing-number">{{ __( 'Routing Number', 'dokan') }}</label>
                    <input type="text" class="dokan-form-input" v-model="vendorInfo.payment.bank.routing_number" :placehoder="__( 'Type here', 'dokan')">
                </div>

                <div class="column">
                    <label for="iban">{{ __( 'IBAN', 'dokan') }}</label>
                    <input type="text" class="dokan-form-input" v-model="vendorInfo.payment.bank.iban" :placehoder="__( 'Type here', 'dokan')">
                </div>

                <div class="column">
                    <label for="swift">{{ __( 'Swift', 'dokan') }}</label>
                    <input type="text" class="dokan-form-input" v-model="vendorInfo.payment.bank.swift" :placehoder="__( 'Type here', 'dokan')">
                </div>
            </div>

            <div class="dokan-form-group">

                <div :class="{'column': getId(), 'checkbox-group': ! getId()}">
                    <label for="account-name">{{ __( 'PayPal Email', 'dokan') }}</label>
                    <input type="email" class="dokan-form-input" v-model="vendorInfo.payment.paypal.email" :placeholder="__( 'Type here', 'dokan')">
                </div>

                <div class="checkbox-group">
                    <div class="checkbox-left">
                        <switches @input="setValue" :enabled="enabled" value="enabled"></switches>
                        <span class="desc">{{ __( 'Enable Selling', 'dokan' ) }}</span>
                    </div>
                </div>

                <div class="checkbox-group">
                    <div class="checkbox-left">
                        <switches @input="setValue" :enabled="trusted" value="trusted"></switches>
                        <span class="desc">{{ __( 'Publish Product Directly', 'dokan' ) }}</span>
                    </div>
                </div>

                <div class="checkbox-group">
                    <div class="checkbox-left">
                        <switches @input="setValue" :enabled="featured" value="featured"></switches>
                        <span class="desc">{{ __( 'Make Vendor Featured', 'dokan' ) }}</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<script>
let Switches = dokan_get_lib('Switches');

export default {
    name: 'VendorPaymentFields',

    components: {
        Switches,
    },

    props: {
        vendorInfo: {
            type: Object
        },
    },

    data() {
        return {
            enabled: false,
            trusted: false,
            featured: false
        }
    },

    created() {
        if ( this.vendorInfo.enabled ) {
            this.enabled = true;
            this.vendorInfo.enabled = true;
        }

        if ( this.vendorInfo.trusted ) {
            this.trusted = true;
            this.vendorInfo.trusted = true;
        }

        if ( this.vendorInfo.featured ) {
            this.featured = true;
            this.vendorInfo.featured = true
        }
    },

    methods: {
        setValue( status, key ) {
            if ( 'enabled' === key ) {
                if ( status ) {
                    this.vendorInfo.enabled = true;
                } else {
                    this.vendorInfo.enabled = false;
                }
            }

            if ( 'trusted' === key ) {
                if ( status ) {
                    this.vendorInfo.trusted = true;
                } else {
                    this.vendorInfo.trusted = false;
                }
            }

            if ( 'featured' === key ) {
                if ( status ) {
                    this.vendorInfo.featured = true;
                } else {
                    this.vendorInfo.featured = false;
                }
            }
        },

        getId() {
            return this.$route.params.id;
        },

    }

};
</script>

<style lang="less">
.checkbox-group {
    margin-top: 20px;
    padding: 0 10px;

    .checkbox-left {
        display: inline-block;
    }

    .checkbox-left {
        .switch {
            margin-right: 10px;
            display: inline-block;
        }
    }
}
.payment-info.edit-mode {
    .checkbox-group {
        padding: 0;
    }
}
</style>