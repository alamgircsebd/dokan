<template>
    <div class="dokan-shipping-settings" id="shipping-settings">
        <div class="dokan-alert dokan-alert-success" v-if="successMessage">
            <span v-html="successMessage"></span>
            <a class="dokan-close" data-dismiss="alert">&times;</a>
        </div>

        <div class="back-link"><router-link :to="{ name: 'Main' }">{{ __( '&larr; Back to Zone List', 'dokan' ) }}</router-link></div>

        <form method="post" id="shipping-settings" @submit.prevent="saveSettings">
            <div class="dokan-shipping-wrapper">
                <div class="dokan-form-group">
                    <label class="dokan-w3" for="dps_pt">
                        {{ __( 'Processing Time', 'dokan' ) }}
                        <span class="dokan-tooltips-help tips" v-tooltip :title="__( 'Write your terms, conditions and instructions about shipping', 'dokan' )">
                            <i class="fa fa-question-circle"></i>
                        </span>
                    </label>

                    <div class="dokan-w6 dokan-text-left">
                        <select name="dps_pt" id="dps_pt" class="dokan-form-control" v-model="shippingSettings.processing_time">
                            <option v-for="(processingTime, index) in getProcessingTimes()" :value="index">{{ processingTime }}</option>
                        </select>
                    </div>
                </div>

                <div class="dokan-form-group">
                    <label class="dokan-w3" for="_dps_shipping_policy">
                        {{ __( 'Shipping Policy', 'dokan' ) }}
                        <span class="dokan-tooltips-help tips" v-tooltip :title="__( 'Write your terms, conditions and instructions about shipping', 'dokan' )">
                            <i class="fa fa-question-circle"></i>
                        </span>
                    </label>

                    <div class="dokan-w6 dokan-text-left">
                        <textarea class="dokan-form-control" id="_dps_shipping_policy" rows="6" v-model="shippingSettings.shipping_policy"></textarea>
                    </div>
                </div>

                <div class="dokan-form-group">
                    <label class="dokan-w3" for="_dps_refund_policy">
                        {{ __( 'Shipping Policy', 'dokan' ) }}
                        <span class="dokan-tooltips-help tips" v-tooltip :title="__( 'Write your terms, conditions and instructions about refund', 'dokan' )">
                            <i class="fa fa-question-circle"></i>
                        </span>
                    </label>

                    <div class="dokan-w6 dokan-text-left">
                        <textarea class="dokan-form-control" rows="6" v-model="shippingSettings.refund_policy"></textarea>
                    </div>
                </div>

                <div class="dokan-form-group">
                    <div class="dokan-w4" style="margin-left:27%;">
                        <input type="submit" name="update_shipping_settings" class="dokan-btn dokan-btn-danger dokan-btn-theme" :value="__( 'Save Settings', 'dokan' )">
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>

<script>

    export default {

        name: 'Settings',

        data() {
            return {
                successMessage: '',
                shippingSettings: {
                    shipping_policy: '',
                    refund_policy: '',
                    processing_time: ''
                }
            }
        },

        methods: {

            getProcessingTimes() {
                return dokanShipping.processing_time;
            },

            saveSettings() {
                var self = this,
                    data = {
                        action: 'dokan-save-shipping-settings',
                        settings: self.shippingSettings,
                        nonce: dokan.nonce
                    };

                jQuery('#shipping-settings').block({ message: null, overlayCSS: { background: '#fff url(' + dokan.ajax_loader + ') no-repeat center', opacity: 0.6 } });

                jQuery.post( dokan.ajaxurl, data, function(resp) {
                    if ( resp.success ) {
                        self.successMessage = resp.data;
                        jQuery('#shipping-settings').unblock();
                    } else {
                        jQuery('#shipping-settings').unblock();
                        alert( resp.data );
                    }
                });
            },

            getSettings() {
                var self = this,
                    data = {
                        action: 'dokan-get-shipping-settings',
                        nonce: dokan.nonce
                    };

                jQuery('#shipping-settings').block({ message: null, overlayCSS: { background: '#fff url(' + dokan.ajax_loader + ') no-repeat center', opacity: 0.6 } });

                jQuery.post( dokan.ajaxurl, data, function(resp) {
                    if ( resp.success ) {
                        self.shippingSettings = resp.data;
                        jQuery('#shipping-settings').unblock();
                    } else {
                        jQuery('#shipping-settings').unblock();
                        alert( resp.data );
                    }
                });
            }
        },

        created() {
            this.getSettings();
        }

    };

</script>

<style lang="less">
    .dokan-shipping-settings {
        .back-link {
            margin: 15px 0px;
            text-align:right;
        }
        .dokan-shipping-wrapper {
            .dokan-form-group {
                clear: both;
                display: flex;
                content: " ";
                label {
                    text-align: right;
                    margin-right: 20px;
                    clear: both;
                }
            }
        }
    }

</style>
