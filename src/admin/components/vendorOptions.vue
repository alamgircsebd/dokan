<template>
    <div class="account-info">
        <div class="content-header">
            {{__( 'Options', 'dokan' )}}
        </div>

        <div class="content-body">
            <div class="picture">
                <p class="title">{{ __( 'Store Picture', 'dokan' ) }}</p>

                <div class="profile-image">
                    <img @click="uploadGravatar" :src="images.gravatar ? images.gravatar : getDefaultPic()" alt="store picture" :style="images.gravatar ? {padding: '5px'} : ''">
                </div>

                <p class="picture-footer">{{ __( 'You can change your profile picutre on Gravatar', 'dokan' ) }}</p>
            </div>

            <div class="picture banner">
                <div class="banner-image">
                    <img :src="images.banner" :alt="images.banner ? 'banner-image' : ''">
                    <button @click="uploadBanner">{{__( 'Upload Banner', 'dokan' ) }}</button>
                </div>

                <p v-if="! images.banner" class="picture-footer">{{ __( 'Upload banner for your store. Banner size is (825x300) pixels', 'dokan' ) }}</p>
            </div>

        </div>
    </div>
</template>

<script>
export default {
    name: 'vendorOptions',


    props: {
        vendorInfo: {
            type: Object
        },
    },

    data() {
        return {
            images: {
                banner: '',
                bannerId: '',
                gravatar: '',
                gravatarId: ''
            },
        }
    },

    created() {
        if ( this.vendorInfo.gravatar ) {
            this.images.gravatar = this.vendorInfo.gravatar;
        }

        if ( this.vendorInfo.banner ) {
            this.images.banner = this.vendorInfo.banner;
        }
    },

    methods: {
        getDefaultPic() {
            return dokan.urls.assetsUrl + '/images/store-pic.png';
        },

        uploadBanner() {
            this.openMediaManager(this.onSelectBanner);
        },

        uploadGravatar() {
            this.openMediaManager(this.onSelectGravatar);
        },

        getId() {
            return this.$route.params.id;
        },

        onSelectGravatar(image) {
            this.images.gravatar   = image.url;
            this.images.gravatarId = image.id;

            this.$root.$emit('uploadedImage', this.images);
        },

        onSelectBanner(image) {
            this.images.banner   = image.url;
            this.images.bannerId = image.id;

            this.$root.$emit('uploadedImage', this.images);
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