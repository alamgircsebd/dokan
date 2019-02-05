<template>
    <div class="dokan-vendor-edit">
        <div class="loading" v-if="isLoading">
            <loading></loading>
        </div>

        <modal :title="title" width="800px" @close="closeModal">
            <div slot="body">
                <div class="tab-header">
                    <ul class="tab-list">
                        <li v-for="(tab, index) in tabs" :key="index" class="tab-title">
                            <div class="tab-link" :style="currentTab === tab.name ? styleObject : ''">
                                <i class="icon">
                                    <img :src="getIcon(tab.icon)">
                                </i>
                                <a href="#" @click.prevent="currentTab = tab.name">{{tab.label}}</a>
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="tab-contents" v-if="currentTab">
                    <transition name="component-fade" mode="out-in">
                        <component :vendorInfo="store" :is="currentTab" />
                    </transition>
                </div>
            </div>

            <div slot="footer">
                <button class="dokan-btn" @click="createVendor">{{ 'vendorPaymentOptions' === currentTab ? __( 'Create Vendor', 'dokan' ) : this.nextBtn }}</button>
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

    name: 'AddVendor',

    components: {
        Modal,
        Loading,
        vendorAccountInfo,
        vendorOptions,
        vendorAddress,
        vendorSocial,
        vendorPaymentOptions,
    },

    data() {
        return {
            isLoading: false,
            styleObject: {
                '--width': '100%'
            },
            nextBtn: this.__( 'Next', 'dokan' ),
            title: this.__( 'Add New Vendor', 'dokan' ),
            tabs: {
                vendorAccountInfo: {
                    label: this.__( 'Account Info', 'dokan' ),
                    name: 'vendorAccountInfo',
                    icon: 'account',
                },
                vendorOptions: {
                    label: this.__( 'Dokan Options', 'dokan' ),
                    name: 'vendorOptions',
                    icon: 'options',
                },
                vendorAddress: {
                    label: this.__( 'Address', 'dokan' ),
                    name: 'vendorAddress',
                    icon: 'address',
                },
                vendorSocial: {
                    label: this.__( 'Social', 'dokan' ),
                    name: 'vendorSocial',
                    icon: 'social',
                },
                vendorPaymentOptions: {
                    label: this.__( 'Payment Options', 'dokan' ),
                    name: 'vendorPaymentOptions',
                    icon: 'payment',
                }
            },
            currentTab: 'vendorAccountInfo',
            store: {
                store_name: '',
                store_url: '',
                user_email: '',
                user_name: '',
                phone: '',
                website: '',
                banner: '',
                gravatar: '',
                social: {
                    fb: '',
                    gplus: '',
                    youtube: '',
                    twitter: '',
                    linkedin: '',
                    pinterest: '',
                    instagram: '',
                },
                payment: {
                    bank: {
                        ac_name: '',
                        ac_number: '',
                        bank_name: '',
                        bank_addr: '',
                        routing_number: '',
                        iban: '',
                        swift: ''
                    }
                },
                address: {
                    street_1: '',
                    street_2: '',
                    city: '',
                    zip: '',
                    country: ''
                }
            },
        };
    },

    created() {
        // this.fetch();

        this.$root.$on('uploadedImage', (images) => {
            this.store.banner   = images.bannerId;
            this.store.gravatar = images.gravatarId;
        } );
    },

    // watch: {
    //     currentTab: (tab) => {
    //         if ( 'vendorPaymentOptions' === tab ) {
    //             console.log('yes');
    //             this.nextBtn = 'Test';
    //             console.log( this.nextBtn );
    //         }
    //     }
    // },


    methods: {
        getId() {
            return this.$route.params.id;
        },

        getIcon(name = '') {
            return dokan.urls.assetsUrl + '/images/' + name + '.png';
        },

        showAlert( $title, $des, $status ) {
            this.$swal( $title, $des, $status );
        },

        // fetch() {
        //     dokan.api.get('/stores/' + this.getId() )
        //     .done(response => {
        //         this.store.vendorPaymentOptions.payment = response.payment;
        //         this.store.vendorAddress.address = response.address;
        //         this.store.vendorSocial.social  = response.social;

        //         // setup vendorAccountInfo & vendorOptons tab
        //         let accountKeys = ['store_name', 'shop_url', 'phone', 'email'];
        //         let optionKeys  = ['banner', 'gravatar'];

        //         for ( let key in response ) {
        //             // setup vendorAccountInfo tab
        //             if ( accountKeys.includes(key) ) {
        //                 this.store.vendorAccountInfo[key] = response[key];
        //             }

        //             // setup vendorOptions tab
        //             if ( optionKeys.includes(key) ) {
        //                 this.store.vendorOptions[key] = response[key];
        //             }
        //         }

        //         this.isLoading = false;
        //     });
        // },

        createVendor() {
            // only for validation|if success create the vendor
            if ( 'vendorAccountInfo' === this.currentTab ) {
                dokan.api.post('/stores/', this.store)
                .done((response) => {
                    this.store.id = response.id;
                })
                .fail((response) => {
                    this.showAlert( this.__( response.responseJSON.message, 'dokan' ), '', 'error' );
                    this.currentTab = 'vendorAccountInfo';
                })
            }

            if ( 'vendorPaymentOptions' === this.currentTab ) {
                // close the modal on vendor creation
                this.$root.$emit('modalClosed');

                // edit the vendor
                dokan.api.put('/stores/' + this.store.id, this.store)
                .done((response) => {
                    this.showAlert(
                        this.__( 'Vendor Created', 'dokan' ),
                        this.__( 'A vendor has been created successfully!', 'dokan' ),
                        'success'
                    );
                })
                .fail((response) => {
                    this.showAlert( this.__( response.responseJSON.message, 'dokan' ), '', 'error' );
                })
            }

            // move next tab
            this.currentTab = this.nextTab(this.tabs, this.currentTab);
        },

        nextTab(tabs, currentTab) {
            let keys      = Object.keys(tabs);
            let nextIndex = keys.indexOf(currentTab) +1;
            let nextTab  =  keys[nextIndex];

            return nextTab;
        },

        // createVendor() {
        //     this.isLoading = true;

        //     let vendorData = this.

        //     dokan.api.post('/stores/', this.store)
        //     .then((response) => {
        //         console.log(response)
        //     })
        // },

        closeModal() {
            this.$root.$emit('modalClosed');
        }

    }
};
</script>

<style lang="less">
.component-fade-enter-active, .component-fade-leave-active {
  transition: opacity .3s ease;
}
.component-fade-enter, .component-fade-leave-to
/* .component-fade-leave-active below version 2.1.8 */ {
  opacity: 0;
}
.swal2-container {
    z-index: 999999 !important;
}

.dokan-vendor-edit {
    .tab-header {

        .tab-list {
            display: flex;
            justify-content: space-around;
            border: 2px solid #e0dede96;
            padding: 10px 0 10px 0;
            border-radius: 5px;

            .tab-title {
                color: #000;
                font-size: 15px;
                font-weight: 500;
                margin: 0;
                position: relative;

                .tab-link {
                    display: flex;

                    &:after {
                        content: "";
                        position: absolute;
                        bottom: -11px;
                        left: 0;
                        width: var(--width);
                        height: 1px;
                        background: #3b80f4;
                    }

                    .icon {
                        padding-right: 8px;
                        position: relative;
                        top: 1px;
                    }

                    a {
                        text-decoration: none;
                        &:active {
                            display: contents;
                        }
                    }
                }
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

                .profile-image {
                    max-width: 100px;
                    margin: 0 auto;
                }

                .profile-image img {
                    border: 1px solid #E5E5E5;
                    padding: 15px 10px 0;
                    cursor: pointer;
                    width: 100%;
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

    // .loading {
    //     position: absolute;
    //     left: 40%;
    //     top: 25%;
    //     transform: translate(-50%, -50%);
    //     z-index: 9999999;
    // }
}
</style>
