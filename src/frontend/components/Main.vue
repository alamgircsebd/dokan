<template>
    <div class="shipping-zone">
        <table class="dokan-table shipping-zone-table">
            <thead>
                <tr>
                    <th>{{ i18n.zone_name }}</th>
                    <th>{{ i18n.regions }}</th>
                    <th>{{ i18n.shipping_method }}</th>
                </tr>
            </thead>

            <tbody>
                <template v-if="Object.keys( shippingZone ).length > 0">
                    <tr v-for="zone in shippingZone" >
                        <td>
                            <router-link :to="{ name: 'Zone', params: { zoneID: zone.zone_id }}">{{ zone.zone_name }}</router-link>
                            <div class="row-actions">
                                <router-link :to="{ name: 'Zone', params: { zoneID: zone.zone_id }}">{{ i18n.edit }}</router-link>
                            </div>
                        </td>
                        <td>
                            {{ zone.formatted_zone_location}}
                        </td>
                        <td>
                            <p v-if="getShippingMethod( zone.shipping_methods )" v-html="getShippingMethod( zone.shipping_methods )"></p>
                            <p v-else>
                                <span>{{ i18n.no_method_found }}&nbsp;</span>
                                <router-link :to="{ name: 'Zone', params: { zoneID: zone.zone_id }}"> {{ i18n.add_method }}</router-link>
                            </p>
                        </td>
                    </tr>
                </template>

                <template v-else>
                    <tr>
                        <td colspan="3">{{ i18n.no_shipping_zone_found }}</td>
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
            i18n: {},
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
        this.i18n = dokanShipping.i18n;
        this.fetchShippingZone();
    }
}
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