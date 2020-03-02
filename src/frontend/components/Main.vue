<template>
    <div class="shipping-zone">
        <table class="dokan-table shipping-zone-table">
            <thead>
                <tr>
                    <th>{{ __( 'Zone Name', 'dokan' ) }}</th>
                    <th>{{ __( 'Region(s)', 'dokan') }}</th>
                    <th>{{ __( 'Shipping Method', 'dokan' ) }}</th>
                </tr>
            </thead>

            <tbody>
                <template v-if="Object.keys( shippingZone ).length > 0">
                    <tr v-for="zone in shippingZone" >
                        <td>
                            <router-link :to="{ name: 'Zone', params: { zoneID: zone.zone_id }}">{{ zone.zone_name }}</router-link>
                            <div class="row-actions">
                                <router-link :to="{ name: 'Zone', params: { zoneID: zone.zone_id }}">{{ __( 'Edit', 'dokan' ) }}</router-link>
                            </div>
                        </td>
                        <td>
                            {{ zone.formatted_zone_location}}
                        </td>
                        <td>
                            <p v-if="getShippingMethod( zone.shipping_methods )" v-html="getShippingMethod( zone.shipping_methods )"></p>
                            <p v-else>
                                <span>{{ __( 'No method found', 'dokan' ) }}&nbsp;</span>
                                <router-link :to="{ name: 'Zone', params: { zoneID: zone.zone_id }}"> {{ __( 'Add Shipping Method', 'dokan' ) }}</router-link>
                            </p>
                        </td>
                    </tr>
                </template>

                <template v-else>
                    <tr>
                        <td colspan="3">{{ __( 'No shipping zone found for configuration. Please contact the admin to manage your store\'s shipping', 'dokan' ) }}</td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</template>

<script>
export default {
    name: 'Main',

    data() {
        return {
            shippingZone : {}
        }
    },

    methods: {
        getShippingMethod( methods ) {
            var shippingMethods = [];

            Object.keys( methods ).forEach( method => {
                var className = methods[method].enabled == 'yes' ? 'is-enabled' : 'is-disabled';
                var formattedMethod = '<span class="' + className + '">' + methods[method].title + '</span>'

                shippingMethods.push( formattedMethod );
            });

            return shippingMethods.join(', ');
        },

        fetchShippingZone() {
            var self = this,
                data = {
                action: 'dokan-get-shipping-zone',
                nonce: dokan.nonce
            }
            jQuery('#dokan-shipping-zone').block({ message: null, overlayCSS: { background: '#fff url(' + dokan.ajax_loader + ') no-repeat center', opacity: 0.6 } });

            jQuery.post( dokan.ajaxurl, data, function(resp) {
                if ( resp.success ) {
                    self.shippingZone = resp.data;
                    jQuery('#dokan-shipping-zone').unblock();
                } else {
                    jQuery('#dokan-shipping-zone').unblock();
                    alert( resp.data );
                }
            });
        }
    },

    created() {
        this.fetchShippingZone();
    }
};
</script>

<style lang="less">
    table.shipping-zone-table {
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

                    span.is-disabled {
                        text-decoration: line-through;
                    }
                }


                &:hover .row-actions {
                    visibility: visible;
                }
            }
        }
    }

</style>
