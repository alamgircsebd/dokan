<template>
    <div class="dokan-vendor-edit">
        <div class="loading" v-if="isLoading">
            <loading></loading>
        </div>

        <modal :title="title" width="800px" @close="closeModal">
            <div slot="body">
                <div class="tab-header">
                    <ul class="tab-list">
                        <li v-for="(tab, index) in tabs" :key="index" :class="{'tab-title': true, 'active': currentTab === tab.name}">
                            <div class="tab-link" :style="currentTab === tab.name ? styleObject : ''">
                                <i :class="{'icon': true, 'first': tab.name === 'vendorAccountInfo'}">
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

            <!-- Change the button name if it's vendor edit page -->
            <div slot="footer" v-if="storeId">
                <button class="dokan-btn" @click="updateVendor">{{ __( 'Update Vendor', 'dokan' ) }}</button>
            </div>

            <div slot="footer" v-else>
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

    props: ['vendorId'],

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
            storeId: '',
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
                user_nicename: '',
                phone: '',
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
        this.$root.$on('uploadedImage', (images) => {
            this.store.banner   = images.bannerId;
            this.store.gravatar = images.gravatarId;
        } );

        // edit vendor if it's vendor single page or pass vendorId to edit a vendor
        if ( this.getId() || this.vendorId ) {
            this.storeId = this.getId() ? this.getId() : this.vendorId;

            this.title = this.__( 'Edit Vendor', 'dokan' );
            this.fetch();
        }
    },

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

        fetch() {
            dokan.api.get('/stores/' + this.storeId )
            .done((response) => {
                this.store = response;
                this.transformer(response);
            })
        },

        // map response props to store props
        transformer(response) {
            if ( 'email' in response ) {
                this.store.user_email = response.email;
            }

            if ( 'shop_url' in response ) {
                this.store.user_nicename = this.getStoreName(response.shop_url);
            }
        },

        // get sotre name from url
        getStoreName(url) {
            let storeName = url.split('/').filter((value) => {
                return value !== '';
            });

            return storeName[storeName.length - 1];
        },

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
                });
            }

            // move next tab
            this.currentTab = this.nextTab(this.tabs, this.currentTab);
        },

        updateVendor() {
            if ( 'vendorPaymentOptions' === this.currentTab ) {
                // close the modal on vendor update
                this.$root.$emit('modalClosed');

                dokan.api.put('/stores/' + this.storeId, this.store )
                .done((response) => {
                    this.showAlert(
                        this.__( 'Vendor Updated', 'dokan' ),
                        this.__( 'The vendor has been updated!', 'dokan' ),
                        'success'
                    );
                })
                .fail((response) => {
                    this.showAlert( this.__( response.responseJSON.message, 'dokan' ), '', 'error' );
                });
            }

            dokan.api.put('/stores/' + this.storeId, this.store )
            .fail((response) => {
                this.showAlert( this.__( response.responseJSON.message, 'dokan' ), '', 'error' );
            });

            // move next tab
            this.currentTab = this.nextTab(this.tabs, this.currentTab);
        },

        nextTab(tabs, currentTab) {
            let keys      = Object.keys(tabs);
            let nextIndex = keys.indexOf(currentTab) +1;
            let nextTab  =  keys[nextIndex];

            return nextTab;
        },

        closeModal() {
            this.$root.$emit('modalClosed');
        }
    }
};
</script>

<style lang="less">

.dokan-vendor-edit {
    .tab-header {

        .tab-list {
            // display: flex;
            // justify-content: space-around;
            // border: 2px solid #e0dede96;
            // padding: 10px 0 10px 0;
            // border-radius: 5px;

            // .tab-title {
            //     color: #000;
            //     font-size: 15px;
            //     font-weight: 500;
            //     margin: 0;
            //     position: relative;

            //     .tab-link {
            //         display: flex;

            //         &:after {
            //             content: "";
            //             position: absolute;
            //             bottom: -11px;
            //             left: 0;
            //             width: var(--width);
            //             height: 1px;
            //             background: #3b80f4;
            //         }

            //         .icon {
            //             padding-right: 8px;
            //             position: relative;
            //             top: 1px;
            //         }

            //         a {
            //             text-decoration: none;
            //             &:active {
            //                 display: contents;
            //             }
            //         }
            //     }
            // }

            // margin: 40px;
            // padding: 0;
            overflow: hidden;

            .tab-title {
                float: left;
                margin-left: 0;
                width: auto;
                height: 50px;
                list-style-type: none;
                padding: 5px 20px 5px 38px;; /* padding around text, last should include arrow width */
                border-right: 10px solid white; /* width: gap between arrows, color: background of document */
                position: relative;
                background-color: #f5f5f5;
                display: flex;
                justify-content: center;
                align-items: center;

                .icon {
                    position: relative;
                    top: 1px;
                }

                a {
                    color: #000;
                    text-decoration: none;

                    // &:active {
                    //     display: contents;
                    // }
                }

                &:first-child {
                    padding-left: 5px;
                }

                &:nth-child(n+2)::before {
                    position: absolute;
                    top:0;
                    left:0;
                    display: block;
                    border-left: 25px solid white; /* width: arrow width, color: background of document */
                    border-top: 25px solid transparent; /* width: half height */
                    border-bottom: 25px solid transparent; /* width: half height */
                    width: 0;
                    height: 0;
                    content: " ";
                }

                &:after {
                    z-index: 1; /* need to bring this above the next item */
                    position: absolute;
                    top: 0;
                    right: -25px; /* arrow width (negated) */
                    display: block;
                    border-left: 25px solid #f5f5f5; /* width: arrow width */
                    border-top: 25px solid transparent; /* width: half height */
                    border-bottom: 25px solid transparent; /* width: half height */
                    width:0;
                    height:0;
                    content: " ";
                    border-left-color: #f5f5f5;
                }
            }
            .tab-title.active {
                background-color: #2C70A3;

                a {
                    color: #fff;
                }

                &:after {
                    border-left-color: #2C70A3;
                }
            }

            .tab-title.active ~.tab-title {
                // background-color: #EBEBEB;

                &:after ~.tab-title {
                    border-left-color: #EBEBEB;
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

                    .store-url {
                        margin: 0;
                        padding: 0;
                        position: relative;
                        bottom: 10px;
                        font-style: italic;
                        color: #a09f9f;
                        font-size: 12px;
                    }
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
}
</style>
