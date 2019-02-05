<template>
    <div class="dokan-vendor-edit">
        <div class="loading" v-if="isLoading">
            <loading></loading>
        </div>

        <modal @close="store = null" :title="title" width="800px" v-if="store">
            <div slot="body">
                <div class="tab-header">
                    <ul class="tab-list">
                        <li v-for="(tab, index) in tabs" :key="index" class="tab-title">
                            <a href="#" @click.prevent="currentTab = tab.name">{{tab.label}}</a>
                        </li>
                    </ul>
                </div>

                <div class="tab-contents" v-if="currentTab && Object.keys(store[currentTab]).length > 0">
                    <component :data="store[currentTab]" :is="currentTab" />
                </div>
            </div>

            <div slot="footer">
                <button class="dokan-btn" @click="updateProfile">
                    {{ __( 'Update Profile', 'dokan' ) }}
                </button>
            </div>
        </modal>

    </div>
</template>

<script>

const Modal   = dokan_get_lib('Modal');
const Loading = dokan_get_lib('Loading');

import vendorAccountInfo from './vendorAccountInfo.vue';
import vendorOptions from './vendorOptions.vue';
import vendorSocial from './vendorSocial.vue';
import vendorAddress from './vendorAddress.vue';
import vendorPaymentOptions from './vendorPaymentOptions.vue';

export default {

    name: 'VendorEdit',

    components: {
        Modal,
        Loading,
        vendorAccountInfo,
        vendorOptions,
        vendorAddress,
        vendorSocial,
        vendorPaymentOptions
    },

    data() {
        return {
            isLoading: true,
            title: this.__( 'Edit Vendor', 'dokan' ),
            tabs: [
                {
                    label: this.__( 'Account Info', 'dokan' ),
                    name: 'vendorAccountInfo',
                    icon: '',
                },
                {
                    label: this.__( 'Dokan Options', 'dokan' ),
                    name: 'vendorOptions',
                    icon: '',
                },
                {
                    label: this.__( 'Address', 'dokan' ),
                    name: 'vendorAddress',
                    icon: '',
                },
                {
                    label: this.__( 'Social', 'dokan' ),
                    name: 'vendorSocial',
                    icon: '',
                },
                {
                    label: this.__( 'Payment Options', 'dokan' ),
                    name: 'vendorPaymentOptions',
                    icon: '',
                }
            ],
            currentTab: 'vendorAccountInfo',
            store: {
                vendorAccountInfo: {},
                vendorOptions: {
                    banner: null,
                    gravatar: null,
                },
                vendorAddress: {
                    address: {}
                },
                vendorSocial: {
                    social: {}
                },
                vendorPaymentOptions: {
                    payment: {}
                }
            },
        };
    },

    created() {
        this.fetch();

        // console.log( 'gravatar', this.store.vendorOptions.hasOwnProperty('gravatar') )
        // console.log( 'banner', this.store.vendorOptions.hasOwnProperty('banner') )

        this.$root.$on('uploadImages', (images) => {

            console.table(images);

            // this.store.vendorOptions.banner = images.;
        } );
    },

    methods: {
        getId() {
            return this.$route.params.id;
        },

        fetch() {
            dokan.api.get('/stores/' + this.getId() )
            .done(response => {
                this.store.vendorPaymentOptions.payment = response.payment;
                this.store.vendorAddress.address = response.address;
                this.store.vendorSocial.social  = response.social;

                // setup vendorAccountInfo & vendorOptons tab
                let accountKeys = ['store_name', 'shop_url', 'phone', 'email'];
                let optionKeys  = ['banner', 'gravatar'];

                for ( let key in response ) {
                    // setup vendorAccountInfo tab
                    if ( accountKeys.includes(key) ) {
                        this.store.vendorAccountInfo[key] = response[key];
                    }

                    // setup vendorOptions tab
                    if ( optionKeys.includes(key) ) {
                        this.store.vendorOptions[key] = response[key];
                    }
                }

                this.isLoading = false;
            });
        },

        updateProfile() {
            this.isLoading = true;
            let currentTab = '';
            console.table(this.store[this.currentTab]);
            dokan.api.put('/stores/' + this.getId(), this.store[this.currentTab] )
            .done((response) => {
                if ( response ) {
                    this.isLoading = false;
                }
            });

            this.tabs.forEach((value, key) => {

                if ( this.currentTab !== value.name ) {
                    return;
                }

                if ( typeof this.tabs[key+1] !== 'undefined' ) {
                    currentTab = this.tabs[key+1].name;
                }
            });

            this.currentTab = currentTab;
        }
    }
};
</script>

<style lang="less">
.fade-enter-active, .fade-leave-active {
  transition: opacity .5s;
}
.fade-enter, .fade-leave-to /* .fade-leave-active below version 2.1.8 */ {
  opacity: 0;
}
.dokan-vendor-edit {

    .tab-header {

        .tab-list {
            display: flex;
            justify-content: space-around;
            border: 2px solid #e0dede96;
            padding: 10px 0 10px 0;
            border-radius: 3px;

            .tab-title {
                color: #000;
                font-size: 15px;
                font-weight: 500;
                margin: 0;
            }
        }
    }

    .tab-contents {
        border: 1px solid #e5e5e5;
        border-radius: 3px;
        max-height: 500px;
        overflow: scroll;

        .content-header {
            background: #F9F9F9;
            margin: 0;
            padding: 10px;
        }

        .content-body {
            padding-top: 20px;
            padding-bottom: 20px;
            // display: flex;

            .dokan-form-group {
                margin: 0 10px;
                overflow: hidden;

                &:after,
                &:before {
                    display: table;
                    content: " ";
                }

                .column {
                    float: left;
                    width: 50%;
                    padding: 0 10px;
                }

                .bank-info {
                    padding-left: 10px;
                }
            }

            .dokan-form-input {
                width: 100%; /* Full width */
                padding: 12px; /* Some padding */
                border: 1px solid #ccc; /* Gray border */
                border-radius: 4px; /* Rounded borders */
                box-sizing: border-box; /* Make sure that padding and width stays in place */
                margin-top: 6px; /* Add a top margin */
                margin-bottom: 16px; /* Bottom margin */
                resize: vertical /* Allow the user to vertically resize the textarea (not horizontally) */
            }

            .picture {
                background: #fcfcfc;
                margin: 0 20px;
                border-radius: 3px;
                padding: 10px 20px;
                border: 2px dashed #d2d2d2;
                text-align: center;

                .profile-image img {
                    border: 1px solid #E5E5E5;
                    padding: 15px 10px 0;
                    cursor: pointer;
                }
            }
            .picture.banner {
                margin-top: 40px !important;
                padding: 70px 0 !important;

                .banner-image {
                    img {
                        width: 100%;
                    }

                    button {
                        background: #1A9ED4;
                        color: white;
                        padding: 10px 15px;
                        border-radius: 3px;
                    }
                }
            }
        }
    }

    .dokan-btn {
        background: #1a9ed4;
        padding: 10px 20px;
        color: white;
        border-radius: 3px;
    }

    .dokan-modal .modal-footer {
        padding: 15px;
        bottom: 0;
        border-top: none;
        box-shadow: none;
    }

    .loading {
        position: absolute;
        left: 40%;
        top: 25%;
        transform: translate(-50%, -50%);
        z-index: 9999999;
    }
}
</style>
