<template>
    <div class="account-info">
        <div class="content-header">
            {{__( 'Account Info', 'dokan' )}}
        </div>

        <div class="content-body">
            <div class="dokan-form-group">
                <div class="column">
                    <label for="store-name">{{ __( 'Store Name', 'dokan') }}</label>
                    <input type="text" class="dokan-form-input" v-model="vendorInfo.store_name" :placeholder="__( 'Type here', 'dokan')">
                </div>

                <div class="column">
                    <label for="store-url">{{ __( 'Store URL', 'dokan') }}</label>
                    <input type="text" class="dokan-form-input" v-model="vendorInfo.store_url" :placeholder="__( 'Type here', 'dokan')">
                </div>

                <div class="column">
                    <label for="store-phone">{{ __( 'Phone Number', 'dokan') }}</label>
                    <input type="text" class="dokan-form-input" v-model="vendorInfo.phone" :placeholder="__( 'Type here', 'dokan')">
                </div>

                <div class="column">
                    <label for="store-email">{{ __( 'Email', 'dokan') }}</label>
                    <input type="email" class="dokan-form-input" v-model="vendorInfo.user_email" :placeholder="__( 'Type here', 'dokan')">
                </div>

                <div class="column">
                    <label for="store-nicename">{{ __( 'Nickname', 'dokan') }}</label>
                    <input type="url" class="dokan-form-input" v-model="vendorInfo.user_login" :placehoder="__( 'Type here', 'dokan')">
                </div>

                <div class="column">
                    <label for="store-website">{{ __( 'Website', 'dokan') }}</label>
                    <input type="url" class="dokan-form-input" v-model="vendorInfo.website" :placehoder="__( 'Type here', 'dokan')">
                </div>
            </div>


<!--             <div class="dokan-form-group">
                <div class="column" v-for="(value, key) in data" :key="key">
                    <label for="value">{{key.replace('_', ' ') | capitalize }}</label>
                    <input type="text" class="dokan-form-input" v-model="data[key]">
                </div>
            </div>
 -->
        </div>
    </div>
</template>

<script>
export default {
    name: 'vendorAccountInfo',

    props: {
        vendorInfo: {
            type: Object
        }
    }

};
</script>