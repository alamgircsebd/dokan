<template>
    <img @click="uploadImage" :src="image.url" :alt="image.alt" :style="image.id ? {padding: '5px'} : ''">
</template>

<script>
export default {
    name: 'UploadImage',

    props: {
        image: {
            type: Object,
            default: () => {
                return {
                    url: dokan.urls.proAssetsUrl + '/images/store-pic.png',
                    id: '',
                    alt: ''
                }
            }
        }
    },

    data() {
        return {
        }
    },

    methods: {
        uploadImage() {
            this.openMediaManager( this.onSelectImage );
        },

        onSelectImage( image ) {
            this.image.url = image.url;
            this.image.id = image.id;

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