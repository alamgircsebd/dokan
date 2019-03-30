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
                        <img v-if="banner" :src="banner" :alt="banner ? 'banner-image' : ''">
                        <button @click="uploadBanner">{{__( 'Upload Banner', 'dokan' ) }}</button>
                    </div>

                    <p v-if="! banner" class="picture-footer">{{ __( 'Upload banner for your store. Banner size is (825x300) pixels', 'dokan' ) }}</p>
                </div>
            </div>

            <div class="dokan-form-group">
                <div class="column">
                    <label for="store-name">{{ __( 'Store Name', 'dokan') }}</label>
                    <input type="text" required class="dokan-form-input" v-model="vendorInfo.store_name" :placeholder="__( 'Type here', 'dokan')">
                </div>

                <div class="column">
                    <label for="store-url">{{ __( 'Store URL', 'dokan') }}</label>
                    <input type="text" class="dokan-form-input" v-model="vendorInfo.user_nicename" :placeholder="__( 'Type here', 'dokan')">
                    <p class="store-url" v-if="showStoreUrl">{{storeUrl}}</p>
                    <p class="store-url" v-else>{{otherStoreUrl}}</p>
                </div>

                <div class="column">
                    <label for="store-phone">{{ __( 'Phone Number', 'dokan') }}</label>
                    <input type="number" class="dokan-form-input" v-model="vendorInfo.phone" :placeholder="__( 'Type here', 'dokan')">
                </div>

                <template v-if="! getId()">
                    <div class="column">
                        <label for="store-email">{{ __( 'Email', 'dokan') }}</label>
                        <input type="email" class="dokan-form-input" v-model="vendorInfo.user_email" :placeholder="__( 'Type here', 'dokan')">
                    </div>

                    <div class="column">
                        <label for="store-username">{{ __( 'User Name', 'dokan') }}</label>
                        <input type="text" class="dokan-form-input" v-model="vendorInfo.user_login" :placehoder="__( 'Type here', 'dokan')">
                    </div>

                    <div class="column">
                        <label for="store-password">{{ __( 'Passwrod', 'dokan') }}</label>
                        <input type="password" class="dokan-form-input" v-model="vendorInfo.user_pass" placehoder="******">
                    </div>
                </template>

            </div>

        </div>
    </form>

</template>

<script>
import UploadImage from 'admin/components/UploadImage.vue';

export default {
    name: 'VendorAccountFields',

    components: {
        UploadImage
    },

    props: {
        vendorInfo: {
            type: Object
        }
    },

    data() {
        return {
            showStoreUrl: true,
            otherStoreUrl: null,
            banner: '',
            defaultUrl: dokan.urls.siteUrl + dokan.urls.storePrefix + '/'
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
            }
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
        // if ( this.vendorInfo.gravatar ) {
        //     this.images.gravatar = this.vendorInfo.gravatar;
        // }

        // if ( this.vendorInfo.banner ) {
        //     this.images.banner = this.vendorInfo.banner;
        // }
    },

    methods: {
        uploadBanner() {
            this.openMediaManager( this.onSelectBanner );
        },

        uploadGravatar( image ) {
            this.vendorInfo.gravatar_id = image.id;
        },

        getId() {
            return this.$route.params.id;
        },

        onSelectBanner( image ) {
            this.banner = image.url;
            this.vendorInfo.banner_id = image.id;
        },

        openMediaManager(callback) {
            const self = this;

            if (self.fileFrame) {
                self.fileFrame.open();
                return;
            }

            const fileStatesOptions = {
                library: wp.media.query(),
                multiple: false, // set it true for multiple image
                title: this.__('Select Image', 'dokan'),
                priority: 20,
                filterable: 'uploaded'
            };

            const fileStates = [
                new wp.media.controller.Library(fileStatesOptions)
            ];

            const mediaOptions = {
                title: this.__('Select Image', 'dokan'),
                button: {
                    text: this.__('Select Image', 'dokan')
                },
                multiple: false
            };

            mediaOptions.states = fileStates;

            self.fileFrame = wp.media(mediaOptions);

            self.fileFrame.on('select', () => {
                const selection = self.fileFrame.state().get('selection');

                const files = selection.map((attachment) => {
                    return attachment.toJSON();
                });

                const file = files.pop();

                callback(file);

                self.fileFrame = null;
            });

            self.fileFrame.on('ready', () => {
                self.fileFrame.uploader.options.uploader.params = {
                    type: 'dokan-vendor-option-media'
                };
            });

            self.fileFrame.open();
        },
    }
};
</script>