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
                    <input type="text" class="dokan-form-input" v-model="vendorInfo.user_nicename" :placeholder="__( 'Type here', 'dokan')">
                    <p class="store-url">{{storeUrl}}</p>
                </div>

                <div class="column">
                    <label for="store-phone">{{ __( 'Phone Number', 'dokan') }}</label>
                    <input type="number" class="dokan-form-input" v-model="vendorInfo.phone" :placeholder="__( 'Type here', 'dokan')">
                </div>

                <div class="column">
                    <label for="store-email">{{ __( 'Email', 'dokan') }}</label>
                    <input type="email" class="dokan-form-input" v-model="vendorInfo.user_email" :placeholder="__( 'Type here', 'dokan')">
                </div>

                <!--Show this feild when creating a new vendor  -->
                <div class="column" v-if="! getId()">
                    <label for="store-username">{{ __( 'User Name', 'dokan') }}</label>
                    <input type="text" class="dokan-form-input" v-model="vendorInfo.user_login" :placehoder="__( 'Type here', 'dokan')">
                </div>

                <div class="column">
                    <label for="store-password">{{ __( 'Passwrod', 'dokan') }}</label>
                    <input type="password" class="dokan-form-input" v-model="vendorInfo.user_pass" placehoder="******">
                </div>

            </div>

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
    },

    data() {
        return {
            storeUrl: dokan.urls.siteUrl + dokan.urls.storePrefix + '/'
        }
    },

    methods: {
        getId() {
            return this.vendorInfo.id ? this.vendorInfo.id : this.$route.params.id;
        },
    }
};
</script>