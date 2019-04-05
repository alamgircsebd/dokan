<template>
    <div class="dokan-upload-image">
        <img v-if="! showButton" @click="uploadImage" :src="image.src ? image.src : src" :style="image.id ? {padding: '5px'} : ''">

        <button v-if="showButton" @click.prevent="uploadImage">
            {{ buttonLabel }}
        </button>
    </div>
</template>

<script>
export default {
    name: 'UploadImage',

    inheritAttrs: false,

    props: {
        src: {
            type: String,
            default: dokan.urls.proAssetsUrl + '/images/store-pic.png',
        },
        showButton: {
            type: Boolean,
            default: false,
        },
        buttonLabel: {
            type: String,
            default: 'Upload Image'
        }
    },

    data() {
        return {
            image: {
                src: '',
                id: '',
            }
        }
    },

    methods: {
        uploadImage() {
            this.openMediaManager( this.onSelectImage );
        },

        onSelectImage( image ) {
            this.image.src = image.url;
            this.image.id = image.id;
            // this.showButton = false;
            this.$emit( 'uploadedImage', this.image );
        },

        openMediaManager( callback ) {
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
<style lang="less">
    .dokan-upload-image {
        img {
            cursor: pointer;
        }
    }
</style>