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
                    <label for="state">{{ __( 'State', 'dokan') }}</label>
                    <input type="text" class="dokan-form-input" v-model="vendorInfo.address.state" :placehoder="__( 'Type here', 'dokan')">
                </div>

                <div class="column">
                    <label for="country">{{ __( 'Country', 'dokan') }}</label>
                    <input type="text" class="dokan-form-input" v-model="vendorInfo.address.country" :placehoder="__( 'Type here', 'dokan')">
                </div>
            </div>

        </div>
    </div>
</template>

<script>
export default {
    name: 'vendorAddress',

    props: {
        vendorInfo: {
            type: Object
        },
    }
};
</script>