<template>
    <form class="account-info">
        <div class="content-header">
            {{__( 'Account Info', 'dokan' )}}
        </div>

        <div class="content-body">
            <div class="vendor-image" v-if="! getId()">
                <div class="picture">
                    <p class="picture-header">{{ __( 'Vendor Picture', 'dokan' ) }}</p>

                    <div class="profile-image">
                        <upload-image @uploadedImage="uploadGravatar" />
                    </div>

                    <p class="picture-footer"
                        v-html="sprintf( __( 'You can change your profile picutre on %s', 'dokan' ), '<a href=\'https://gravatar.com/\' target=\'_blank\'>Gravatar</a>' )"
                    />
                </div>

                <div class="picture banner" :style="banner ? 'padding: 0' : ''">
                    <div class="banner-image">

                        <upload-image @uploadedImage="uploadBanner" :showButton="showButton" :buttonLabel="__( 'Upload Banner', 'dokan' )" />

                        <!-- <img v-if="banner" :src="banner" :alt="banner ? 'banner-image' : ''"> -->
                        <!-- <button @click="uploadBanner">{{__( 'Upload Banner', 'dokan' ) }}</button> -->
                    </div>

                    <p class="picture-footer">{{ __( 'Upload banner for your store. Banner size is (825x300) pixels', 'dokan' ) }}</p>
                </div>
            </div>

            <div class="dokan-form-group">

                <div class="column">
                    <label for="store-email">{{ __( 'First Name', 'dokan') }}</label>
                    <input type="email" class="dokan-form-input" v-model="vendorInfo.first_name" :placeholder="__( 'First Name', 'dokan')">
                </div>

                <div class="column">
                    <label for="store-email">{{ __( 'Last Name', 'dokan') }}</label>
                    <input type="email" class="dokan-form-input" v-model="vendorInfo.last_name" :placeholder="__( 'Last Name', 'dokan')">
                </div>

                <div class="column">
                    <label for="store-name">{{ __( 'Store Name', 'dokan') }}</label>
                    <span v-if="! getId()" class="required-field">*</span>

                    <input type="text"
                        v-model="vendorInfo.store_name"
                        :class="{'dokan-form-input': true, 'has-error': getError('store_name')}"
                        :placeholder="getError( 'store_name' ) ? __( 'Store Name is required', 'dokan' ) : __( 'Store Name', 'dokan' )">
                </div>

                <div class="column" v-if="! getId()">
                    <label for="store-url">{{ __( 'Store URL', 'dokan') }}</label>
                    <input type="text" class="dokan-form-input" v-model="vendorInfo.user_nicename" :placeholder="__( 'Store Url', 'dokan')">

                    <div class="store-avaibility-info">
                        <p class="store-url" v-if="showStoreUrl">{{ storeUrl }}</p>
                        <p class="store-url" v-else>{{ otherStoreUrl }}</p>
                        <span :class="{'is-available': storeAvailable, 'not-available': !storeAvailable}">{{ storeAvailabilityText }}</span>
                    </div>
                </div>

                <div class="column">
                    <label for="store-phone">{{ __( 'Phone Number', 'dokan') }}</label>
                    <input type="number" class="dokan-form-input" v-model="vendorInfo.phone" :placeholder="__( '123456789', 'dokan')">
                </div>

                <div class="column">
                    <label for="store-email">{{ __( 'Email', 'dokan') }}</label>
                    <span v-if="! getId()" class="required-field">*</span>

                    <input type="email"
                        v-model="vendorInfo.user_email"
                        :class="{'dokan-form-input': true, 'has-error': getError('user_email')}"
                        :placeholder="getError( 'user_email' ) ? __( 'Email is required', 'dokan' ) : __( 'store@email.com', 'dokan' )"
                    >
                </div>

                <template v-if="! getId()">
                    <div class="column">
                        <label for="store-username">{{ __( 'Username', 'dokan') }}</label><span class="required-field">*</span>
                        <input type="text" class="dokan-form-input"
                            v-model="vendorInfo.user_login"
                            :class="{'dokan-form-input': true, 'has-error': getError('user_login')}"
                            :placeholder="getError( 'user_login' ) ? __( 'Username is required', 'dokan' ) : __( 'Username', 'dokan' )">

                            <div class="store-avaibility-info">
                                <span :class="{'is-available': userNameAvailable, 'not-available': !userNameAvailable}">{{ userNameAvailabilityText }}</span>
                            </div>
                    </div>

                    <div class="column">
                        <label for="store-password">{{ __( 'Passwrod', 'dokan') }}</label>
                        <input
                            v-if="showPassword"
                            type="text"
                            v-model="vendorInfo.user_pass"
                            class="dokan-form-input"
                            placeholder="********"
                        >

                        <password-generator
                            @passwordGenerated="setPassword"
                            :title="__('Generate Password', 'dokan')"
                        />

                    </div>
                </template>

                <!-- Add other account fields here -->
                <component v-for="(component, index) in getAccountFields"
                    :key="index"
                    :is="component"
                    :vendorInfo="vendorInfo"
                />
            </div>

        </div>
    </form>

</template>

<script>
import UploadImage from 'admin/components/UploadImage.vue';
import PasswordGenerator from 'admin/components/passwordGenerator.vue';

let debounce = dokan_get_lib( 'Debounce' );

export default {
    name: 'VendorAccountFields',

    components: {
        UploadImage,
        PasswordGenerator
    },

    props: {
        vendorInfo: {
            type: Object
        },
        errors: {
            type: Array,
            required: false
        }
    },

    data() {
        return {
            showStoreUrl: true,
            showPassword: false,
            otherStoreUrl: null,
            banner: '',
            defaultUrl: dokan.urls.siteUrl + dokan.urls.storePrefix + '/',
            showButton: true,
            placeholderData: '',
            delay: 500,
            storeAvailable: null,
            userNameAvailable: null,
            storeAvailabilityText: '',
            userNameAvailabilityText: '',
            getAccountFields: dokan.hooks.applyFilters( 'getVendorAccountFields', [] ),
        }
    },

    watch: {
        'vendorInfo.store_name'( value ) {
            this.showStoreUrl = true;
        },

        'vendorInfo.user_nicename'( newValue ) {
            if ( typeof newValue !== 'undefined' ) {
                this.showStoreUrl = false;
                this.otherStoreUrl = this.defaultUrl + newValue.trim().split(' ').join('-');
                this.vendorInfo.user_nicename = newValue.split(' ').join('-');

                // check if the typed url is available
                this.checkStoreName();
            }
        },

        'vendorInfo.user_login'( value ) {
            this.checkUsername();
        }
    },

    computed: {
        storeUrl() {
            let storeUrl = this.vendorInfo.store_name.trim().split(' ').join('-');
            this.vendorInfo.user_nicename = storeUrl;
            this.otherStoreUrl = this.defaultUrl + storeUrl;

            return this.defaultUrl + storeUrl;
        }
    },

    created() {
        this.checkStoreName = debounce( this.checkStore, this.delay );
        this.checkUsername = debounce( this.searchUsername, this.delay );
        this.$root.$on( 'passwordCancelled', () => {
            this.showPassword = false;
        } );
    },

    methods: {
        uploadBanner( image ) {
            this.vendorInfo.banner_id = image.id;

            // hide button and footer text after uploading banner
            this.showButton = false;
        },

        uploadGravatar( image ) {
            this.vendorInfo.gravatar_id = image.id;
        },

        // getId function has been used to identify whether is it vendor edit page or not
        getId() {
            return this.$route.params.id;
        },

        onSelectBanner( image ) {
            this.banner = image.url;
            this.vendorInfo.banner_id = image.id;
        },

        getError( key ) {
            let errors = this.errors;

            if ( ! errors || typeof errors === 'undefined' ) {
                return false;
            }

            if ( errors.length < 1 ) {
                return false;
            }

            if ( errors.includes( key ) ) {
                return key;
            }
        },

        checkStore() {
            const storeName = this.vendorInfo.user_nicename;

            if ( ! storeName ) {
                return;
            }

            this.storeAvailabilityText = this.__( 'Searching...', 'dokan' );

            dokan.api.get( `/stores/check`, {
                store_slug: storeName
            } ).then( ( response ) => {
                if ( response.available ) {
                    this.storeAvailable = true;
                    this.$root.$emit( 'usernameChecked', {
                        userNameAvailable: this.userNameAvailable,
                        storeAvailable: this.storeAvailable
                    } );
                    this.storeAvailabilityText = this.__( 'Available', 'dokan' )
                } else {
                    this.storeAvailable = false;
                    this.$root.$emit( 'usernameChecked', {
                        userNameAvailable: this.userNameAvailable,
                        storeAvailable: this.storeAvailable
                    } );
                    this.storeAvailabilityText = this.__( 'Not Available', 'dokan' );
                }
            } );
        },

        searchUsername() {
            const userName = this.vendorInfo.user_login;

            if ( ! userName ) {
                return;
            }

            this.userNameAvailabilityText = this.__( 'Searching...', 'dokan' );

            dokan.api.get( `/stores/check`, {
                store_slug: userName
            } ).then( ( response ) => {
                if ( response.available ) {
                    this.userNameAvailable = true;
                    this.$root.$emit( 'usernameChecked', {
                        userNameAvailable: this.userNameAvailable,
                        storeAvailable: this.storeAvailable
                    } );
                    this.userNameAvailabilityText = this.__( 'Available', 'dokan' )
                } else {
                    this.userNameAvailable = false;
                    this.$root.$emit( 'usernameChecked', {
                        userNameAvailable: this.userNameAvailable,
                        storeAvailable: this.storeAvailable
                    } );
                    this.userNameAvailabilityText = this.__( 'Not Available', 'dokan' );
                }
            } );
        },

        setPassword( password ) {
            this.showPassword = true;
            this.vendorInfo.user_pass = password;
        }

    }
};
</script>