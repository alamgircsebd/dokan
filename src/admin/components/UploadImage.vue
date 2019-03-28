<template>
    <img @click="uploadGravatar" :src="images.url" :alt="images.alt" :style="images.id ? {padding: '5px'} : ''">
</template>

<script>
export default {
    name: 'UploadImage',

    props: {
        images: {
            type: Object,
            default: () => {
                return {
                    url: '',
                    id: '',
                    alt: ''
                }
            }
        }
    },

    data() {
        return {
            // images: {
                // banner: '',
                // bannerId: '',
                // gravatar: '',
                // gravatarId: ''
            // },
        }
    },

    methods: {
        // uploadBanner() {
        //     this.openMediaManager(this.onSelectBanner);
        // },

        uploadGravatar() {
            this.openMediaManager(this.onSelectGravatar);
        },

        onSelectGravatar(image) {
            this.images.url = image.url;
            this.images.id = image.id;

            this.$emit('uploadedImage', this.images);
        },

        // onSelectBanner(image) {
        //     this.images.banner   = image.url;
        //     this.images.bannerId = image.id;

        //     this.$root.$emit('uploadedImage', this.images);
        // },

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