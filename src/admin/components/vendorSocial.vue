<template>
    <div class="account-info">
        <div class="content-header">
            {{__( 'Social Options', 'dokan' )}}
        </div>

        <div class="content-body">

            <div class="dokan-form-group">
                <div class="column">
                    <label for="">{{ __( 'Facebook', 'dokan' ) }}</label>
                    <input type="text" class="dokan-form-input" v-model="vendorInfo.social.fb" :placeholder="__( 'https://exmaple.com' )">
                </div>

                <div class="column">
                    <label for="">{{ __( 'Google Plus', 'dokan' ) }}</label>
                    <input type="text" class="dokan-form-input" v-model="vendorInfo.social.gplus" :placeholder="__( 'https://exmaple.com' )">
                </div>

                <div class="column">
                    <label for="">{{ __( 'Twitter', 'dokan' ) }}</label>
                    <input type="text" class="dokan-form-input" v-model="vendorInfo.social.twitter" :placeholder="__( 'https://exmaple.com' )">
                </div>

                <div class="column">
                    <label for="">{{ __( 'Instagram', 'dokan' ) }}</label>
                    <input type="text" class="dokan-form-input" v-model="vendorInfo.social.youtube" :placeholder="__( 'https://exmaple.com' )">
                </div>

                <div class="column">
                    <label for="">{{ __( 'Instagram', 'dokan' ) }}</label>
                    <input type="text" class="dokan-form-input" v-model="vendorInfo.social.linkedin" :placeholder="__( 'https://exmaple.com' )">
                </div>

                <div class="column">
                    <label for="">{{ __( 'Instagram', 'dokan' ) }}</label>
                    <input type="text" class="dokan-form-input" v-model="vendorInfo.social.pinterest" :placeholder="__( 'https://exmaple.com' )">
                </div>

                <div class="column">
                    <label for="">{{ __( 'Instagram', 'dokan' ) }}</label>
                    <input type="text" class="dokan-form-input" v-model="vendorInfo.social.instagram" :placeholder="__( 'https://exmaple.com' )">
                </div>
            </div>

<!--             <div class="dokan-form-group">
                <div class="column" v-for="(value, key) in data.social" :key="key">
                    <label for="value">{{key.replace('_', ' ') | capitalize }}</label>
                    <input type="text" class="dokan-form-input" v-model="data.social[key]">
                </div>
            </div> -->

        </div>
    </div>
</template>

<script>
export default {
    name: 'vendorSocial',

    props: {
        vendorInfo: {
            type: Object
        }
    }
};
</script>