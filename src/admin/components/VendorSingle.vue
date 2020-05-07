<template>
    <div class="dokan-vendor-single">
        <div style="margin-bottom: 10px">
            <a class="button" href="javascript:history.go(-1)">&larr; {{ __( 'Go Back', 'dokan' ) }}</a>
        </div>

        <div class="dokan-hide">
            {{store}}
        </div>

        <modal
            :title="__( 'Send Email', 'dokan' )"
            v-if="showDialog"
            @close="showDialog = false"
        >
            <template slot="body">
                <div class="form-row">
                    <label for="mailto">{{ __( 'To', 'dokan' ) }}</label>
                    <input type="text" id="mailto" disabled="disabled" :value="mailTo">
                </div>

                <div class="form-row">
                    <label for="subject">{{ __( 'Subject', 'dokan' ) }}</label>
                    <input type="text" id="subject" v-model="mail.subject">
                </div>

                <div class="form-row">
                    <label for="message">{{ __( 'Message', 'dokan' ) }}</label>
                    <textarea id="message" rows="5" cols="60" v-model="mail.body"></textarea>
                </div>
            </template>

            <template slot="footer">
                <button class="button button-primary button-large" @click="sendEmail()">{{ __( 'Send Email', 'dokan' ) }}</button>
            </template>
        </modal>

        <div class="vendor-profile" v-if="store.id">
            <section class="vendor-header">
                <div class="profile-info">

                    <div class="featured-vendor" v-if="store.featured">
                        <span title="Featured Vendor" class="dashicons dashicons-star-filled"></span>
                    </div>

                    <div :class="{'profile-icon': true, 'edit-mode': editMode}">
                        <template v-if="editMode">
                            <upload-image @uploadedImage="uploadGravatar" :croppingWidth="150" :croppingHeight="150" :src="store.gravatar_id && store.gravatar ? store.gravatar : getDefaultPic()" />
                        </template>
                        <template v-else>
                            <img :src="store.gravatar ? store.gravatar : getDefaultPic()" :alt="store.store_name">
                        </template>
                        <span class="edit-photo" v-if="editMode" :style="{color: ! store.gravatar_id ? 'black' : '' }">
                            {{ __( 'Change Store Photo', 'dokan' ) }}
                        </span>
                    </div>

                    <div :class="{'store-info': true, 'edit-mode': editMode}">
                        <template v-if="! editMode">
                            <h2 class="store-name">{{ store.store_name ? store.store_name : __( '(No Name)', 'dokan' ) }}</h2>
                        </template>

                        <div class="star-rating" v-if="! editMode">
                            <span v-for="i in 5" :class="['dashicons', i <= store.rating.rating ? 'active' : '' ]"></span>
                        </div>

                        <template v-if="editMode">
                            <VendorAccountFields :vendorInfo="store" />
                        </template>


                        <template v-if="categories.length && ! editMode">
                            <template v-if="! editingCategories">
                                <template v-if="! store.categories.length">
                                    <a
                                        class="store-categoy-names"
                                        href="#edit-categories"
                                        v-html="isCategoryMultiple ? __( 'Add Categories', 'dokan' ) : __( 'Add Category', 'dokan' )"
                                        @click.prevent="editCategory"
                                    />
                                </template>
                                <template v-else>
                                    <a
                                        class="store-categoy-names"
                                        href="#edit-categories"
                                        v-html="store.categories.map( category => category.name ).join( ', ' )"
                                        @click.prevent="editCategory"
                                    />
                                </template>
                            </template>
                            <template v-else>
                                <div class="store-categories-editing">
                                    <h4>{{ isCategoryMultiple ? __( 'Set Store Categories', 'dokan' ) : __( 'Set Store Category', 'dokan' ) }}</h4>
                                    <fieldset :disabled="isUpdating">
                                        <select multiple="multiple"
                                            id="store-categories"
                                            style="width: 100%"
                                            :data-placeholder="__( 'Select Category', 'dokan' )">
                                        </select>
                                        <p>
                                            <button
                                                class="button button-primary button-small"
                                                v-text="__( 'Done', 'dokan' )"
                                                @click="updateStore"
                                            />
                                            <button
                                                class="button button-link button-small"
                                                v-text="__( 'Cancel', 'dokan' )"
                                                @click="editingCategories = false"
                                            />
                                        </p>
                                    </fieldset>
                                </div>
                            </template>
                        </template>

                        <ul :class="{'store-details': true, 'edit-mode': editMode}" v-if="! editMode">
                            <li class="address">
                                <span class="street_1" v-if="store.address.street_1">{{ store.address.street_1 }}, </span>
                                <span class="street_2" v-if="store.address.street_2">{{ store.address.street_2 }}, </span>
                                <span class="city" v-if="store.address.city">{{ store.address.city }}, </span>
                                <span class="state-zip" v-if="store.address.state">{{ store.address.state }} {{ store.address.zip }}</span>
                                <span class="country" v-if="store.address.country">{{ store.address.country }}</span>
                            </li>
                            <li class="phone">
                                {{ store.phone ? store.phone : '—' }}
                            </li>
                        </ul>

                        <div class="actions" v-if="! editMode">
                            <button class="button message" @click="messageDialog()"><span class="dashicons dashicons-email"></span> {{ __( 'Send Email', 'dokan' ) }}</button>
                            <button :class="['button', 'status', store.enabled ? 'enabled' : 'disabled']"><span class="dashicons"></span> {{ store.enabled ? __( 'Enabled', 'dokan' ) : __( 'Disabled', 'dokan' ) }}</button>
                        </div>
                    </div>
                </div>

                <div :class="{'profile-banner': true, 'edit-mode': editMode}">
                    <div class="banner-wrap">
                        <template v-if="editMode">
                                <upload-image @uploadedImage="uploadBanner" :src="store.banner" />
                        </template>

                        <template v-else>
                            <img v-if="store.banner" :src="store.banner" :alt="store.store_name">
                        </template>
                        <span class="edit-banner" v-if="editMode">
                            <i class="change-banner dashicons dashicons-format-image"></i>
                            {{ __( 'Change Store Banner', 'dokan' ) }}
                        </span>
                    </div>
                    <div :class="{'action-links': true, 'edit-mode': editMode}">
                        <template v-if="editMode">
                            <button @click="editMode = false" class="button">{{ __( 'Cancel', 'dokan' ) }}</button>
                            <button @click="updateStore" class="button button-primary">{{ saveBtn }}</button>
                        </template>

                        <template v-else>
                            <a :href="store.shop_url" target="_blank" class="button visit-store">{{ __( 'Visit Store', 'dokan' ) }} <span class="dashicons dashicons-arrow-right-alt"></span></a>
                            <router-link :to="id" class="button" @click.native="editMode = true">
                                   <span class="dashicons dashicons-edit"></span>
                            </router-link>
                        </template>
                    </div>
                </div>
            </section>

            <section class="vendor-summary" v-if="stats !== null && !editMode">
                <div class="summary-wrap products-revenue">
                    <div class="stat-summary products">
                        <h3>{{ __( 'Products', 'dokan' ) }}</h3>

                        <ul class="counts">
                            <li class="products">
                                <span class="count"><a :href="productUrl()">{{ stats.products.total }}</a></span>
                                <span class="subhead">{{ __( 'Total Products', 'dokan' ) }}</span>
                            </li>
                            <li class="items">
                                <span class="count">{{ stats.products.sold }}</span>
                                <span class="subhead">{{ __( 'Items Sold', 'dokan' ) }}</span>
                            </li>
                            <li class="visitors">
                                <span class="count">{{ stats.products.visitor }}</span>
                                <span class="subhead">{{ __( 'Store Visitors', 'dokan' ) }}</span>
                            </li>
                        </ul>
                    </div>

                    <div class="stat-summary revenue">
                        <h3>{{ __( 'Revenue', 'dokan' ) }}</h3>

                        <ul class="counts">
                            <li class="orders">
                                <span class="count"><a :href="ordersUrl()">{{ stats.revenue.orders }}</a></span>
                                <span class="subhead">{{ __( 'Orders Processed', 'dokan' ) }}</span>
                            </li>
                            <li class="gross">
                                <span class="count">
                                    <currency :amount="stats.revenue.sales"></currency>
                                </span>
                                <span class="subhead">{{ __( 'Gross Sales', 'dokan' ) }}</span>
                            </li>
                            <li class="earning">
                                <span class="count">
                                    <currency :amount="stats.revenue.earning"></currency>
                                </span>
                                <span class="subhead">{{ __( 'Total Earning', 'dokan' ) }}</span>
                            </li>
                        </ul>
                    </div>

                    <div class="stat-summary others">
                        <h3>{{ __( 'Others', 'dokan' ) }}</h3>

                        <ul class="counts">
                            <li class="commision">
                                <span class="count" v-html="getEearningRate"></span>
                                <span class="subhead">{{ __( 'Admin Commission', 'dokan' ) }}</span>
                            </li>
                            <li class="balance">
                                <span class="count">
                                    <currency :amount="stats.others.balance"></currency>
                                </span>
                                <span class="subhead">{{ __( 'Current Balance', 'dokan' ) }}</span>
                            </li>
                            <li class="reviews">
                                <span class="count">{{ stats.others.reviews }}</span>
                                <span class="subhead">{{ __( 'Reviews', 'dokan' ) }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="vendor-info">
                    <ul>
                        <li class="registered">
                            <div class="subhead">{{ __( 'Registered Since', 'dokan' ) }}</div>
                            <span class="date">
                                {{ moment(store.registered).format('MMM D, YYYY') }}
                                ({{ moment(store.registered).toNow(true) }})
                            </span>
                        </li>
                        <li class="social-profiles">
                            <div class="subhead">{{ __( 'Social Profiles', 'dokan' ) }}</div>

                            <div class="profiles">
                                <a :class="{ active: isSocialActive('fb') }" :href="store.social.fb" target="_blank"><span class="flaticon-facebook-logo"></span></a>
                                <a :class="{ active: isSocialActive('flickr') }" :href="store.social.flickr" target="_blank"><span class="flaticon-flickr-website-logo-silhouette"></span></a>
                                <a :class="{ active: isSocialActive('twitter') }" :href="store.social.twitter" target="_blank"><span class="flaticon-twitter-logo-silhouette"></span></a>
                                <a :class="{ active: isSocialActive('gplus') }" :href="store.social.gplus" target="_blank"><span class="flaticon-google-plus"></span></a>
                                <a :class="{ active: isSocialActive('instagram') }" :href="store.social.instagram" target="_blank"><span class="flaticon-instagram"></span></a>
                                <a :class="{ active: isSocialActive('youtube') }" :href="store.social.youtube" target="_blank"><span class="flaticon-youtube"></span></a>
                                <a :class="{ active: isSocialActive('linkedin') }" :href="store.social.linkedin" target="_blank"><span class="flaticon-linkedin-logo"></span></a>
                                <a :class="{ active: isSocialActive('pinterest') }" :href="store.social.pinterest" target="_blank"><span class="flaticon-pinterest-logo"></span></a>
                            </div>
                        </li>
                        <li class="payments">
                            <div class="subhead">{{ __( 'Payment Methods', 'dokan' ) }}</div>

                            <div class="payment-methods">
                                <span :title="__( 'PayPal Payment', 'dokan' )" :class="['flaticon-money', hasPaymentEmail('paypal') ? 'active' : '']"></span>
                                <span :title="__( 'Stripe Connect', 'dokan' )" :class="['flaticon-stripe-logo', hasStripe ? 'active': '']"></span>
                                <span :title="__( 'Bank Payment', 'dokan' )" :class="['flaticon-bank-building', hasBank ? 'active': '' ]"></span>
                                <span :title="__( 'Skrill', 'dokan' )" :class="['flaticon-skrill-pay-logo', hasPaymentEmail('skrill') ? 'active' : '']"></span>
                            </div>
                        </li>
                        <li class="publishing">
                            <div class="subhead">{{ __( 'Product Publishing', 'dokan' ) }}</div>

                            <span v-if="store.trusted"><span class="dashicons dashicons-shield"></span> {{ __( 'Direct', 'dokan' ) }}</span>
                            <span v-else><span class="dashicons dashicons-backup"></span> {{ __( 'Requires Review', 'dokan' ) }}</span>
                        </li>
                    </ul>
                </div>
            </section>

            <section class="vendor-other-info" v-if="editMode">
                <div class="address-social-info">
                    <VendorAddressFields :vendorInfo="store" />
                    <VendorSocialFields :vendorInfo="store" />
                </div>
                <div class="payment-info">
                    <VendorPaymentFields :vendorInfo="store" />
                </div>
            </section>

            <div :class="{'action-links': true, 'footer': true, 'edit-mode': editMode}">
                <template v-if="editMode">
                    <button @click="editMode = false" class="button">{{ __( 'Cancel', 'dokan' ) }}</button>
                    <button @click="updateStore" class="button button-primary">{{ saveBtn }}</button>
                </template>
            </div>

        </div>
        <vcl-twitch v-else height="300" primary="#ffffff"></vcl-twitch>
    </div>
</template>

<script>
import $ from 'jquery';

let ContentLoading      = dokan_get_lib('ContentLoading');
let Modal               = dokan_get_lib('Modal');
let Currency            = dokan_get_lib('Currency');
let UploadImage         = dokan_get_lib('UploadImage');
let VendorAccountFields = dokan_get_lib('VendorAccountFields');
let VendorPaymentFields = dokan_get_lib('VendorPaymentFields');
let VendorSocialFields  = dokan_get_lib('VendorSocialFields');
let VendorAddressFields = dokan_get_lib('VendorAddressFields');

let VclTwitch = ContentLoading.VclTwitch;

export default {
    name: 'VendorSingle',

    components: {
        Modal,
        Currency,
        VclTwitch,
        UploadImage,
        VendorPaymentFields,
        VendorSocialFields,
        VendorAccountFields,
        VendorAddressFields,
    },

    data () {
        return {
            showDialog: false,
            stats: null,
            mail: {
                subject: '',
                body: ''
            },
            editMode: false,
            isUpdating: false,
            categories: [],
            isCategoryMultiple: false,
            editingCategories: false,
            store: {
                store_name: '',
                user_pass: '',
                store_url: '',
                email: '',
                user_nicename: '',
                phone: '',
                banner: '',
                banner_id: '',
                gravatar: '',
                gravatar_id: '',
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
                    },
                    paypal: {
                        email: ''
                    }
                },
                address: {
                    street_1: '',
                    street_2: '',
                    city: '',
                    zip: '',
                    state: '',
                    country: ''
                }
            },
            fakeStore: {},
            showStoreUrl: true,
            otherStoreUrl: null,
        };
    },

    computed: {
        id() {
            return this.$route.params.id;
        },

        mailTo() {
            return this.store.store_name + ' <' + this.store.email + '>';
        },

        hasBank() {
            if ( this.store.payment.hasOwnProperty('bank') && ! _.isEmpty(this.store.payment.bank) ) {
                return true;
            }

            return false;
        },

        hasStripe() {
            return this.store.payment && this.store.payment.stripe;
        },

        categoriesFlattened() {
            const categories = {};
            let i = 0;

            for ( i = 0; i < this.categories.length; i++ ) {
                const category = this.categories[ i ];

                categories[ category.id ] = {
                    id: category.id,
                    name: category.name,
                    slug: category.slug
                };
            }

            return categories;
        },

        getEearningRate() {
            let commissionRate = this.stats.others.commission_rate ? this.stats.others.commission_rate : 0;
            let additionalFee  = this.stats.others.additional_fee ? this.stats.others.additional_fee : 0;
            let commissionType = this.stats.others.commission_type;

            if ( '' === this.store.admin_commission ) {
                return this.__( 'Not Set', 'dokan' );
            }

            if ( commissionType === 'flat' ) {
                return accounting.formatMoney( commissionRate );
            } else if ( commissionType === 'percentage' ) {
                return `${commissionRate}%`;
            } else {
                return `${(commissionRate)}% &nbsp; + ${accounting.formatMoney( additionalFee )}`;
            }
        },

        saveBtn() {
            return this.isUpdating ? this.__( 'Saving...', 'dokan' ) : this.__( 'Save Changes' )
        },
    },

    watch: {
        '$route.params.id'() {
            this.fetch();
            this.fetchStats();
        },
    },

    created() {
        this.fetch();
        this.fetchStats();

        if ( this.$route.query.edit && this.$route.query.edit === 'true' ) {
            this.editMode = true;
        }
    },

    methods: {

        fetch() {
            const self = this;
            dokan.api.get('/stores/' + self.id )
            .done( ( response ) => {
                Object.assign( self.fakeStore, self.store );
                Object.assign( self.store, response );
                self.transformer(response);
            } );

            dokan.api.get( '/store-categories?per_page=50' )
                .done( ( response, status, xhr ) => {
                    self.categories = response;
                    self.isCategoryMultiple = ( 'multiple' === xhr.getResponseHeader( 'X-WP-Store-Category-Type' ) );
                } );
        },

        // map response props to store props
        transformer(response) {
            for ( let res in response ) {

                if ( Array.isArray(response[res]) && 0 === response[res].length ) {
                    this.store[res] = this.fakeStore[res];
                }
            }

            // set default bank paymet object if it's not found in the API response
            if ( response.payment && typeof response.payment.bank === 'undefined' || typeof response.payment.bank.ac_number === 'undefined' ) {
                this.store.payment.bank = this.fakeStore.payment.bank;
            }

            // set default paypal paymet object if it's not found in the API response
            if ( response.payment && typeof response.payment.paypal === 'undefined' || typeof response.payment.paypal.email === 'undefined' ) {
                this.store.payment.paypal = this.fakeStore.payment.paypal;
            }

            if ( 'shop_url' in response ) {
                this.store.user_nicename = this.getStoreName(response.shop_url);
            }

            if ( ! response.admin_commission_type ) {
                this.store.admin_commission_type = 'flat';
            }
        },

        // get sotre name from url
        getStoreName(url) {
            let storeName = url.split('/').filter((value) => {
                return value !== '';
            });

            return storeName[storeName.length - 1];
        },

        fetchStats() {
            dokan.api.get('/stores/' + this.id + '/stats' )
            .done(response => this.stats = response);
        },

        isSocialActive(profile) {
            if ( this.store.social.hasOwnProperty(profile) && this.store.social[profile] !== '' ) {
                return true;
            }

            return false;
        },

        hasPaymentEmail(method) {
            if ( this.store.payment.hasOwnProperty(method) && this.store.payment[method].email !== '' ) {
                return true;
            }

            return false;
        },

        messageDialog() {
            this.showDialog = true;
        },

        sendEmail() {
            this.showDialog = false;

            dokan.api.post('/stores/' + this.id + '/email', {
                subject: this.mail.subject,
                body: this.mail.body
            } ).done(response => {
                this.$notify({
                    title: this.__( 'Success!', 'dokan' ),
                    type: 'success',
                    text: this.__( 'Email has been sent successfully.', 'dokan' )
                });
            });

            this.mail = {
                subject: '',
                body: ''
            };
        },

        moment(date) {
            return moment(date);
        },

        productUrl() {
            return dokan.urls.adminRoot + 'edit.php?post_type=product&author=' + this.store.id;
        },

        ordersUrl() {
            return dokan.urls.adminRoot + 'edit.php?post_type=shop_order&vendor_id=' + this.store.id;
        },

        editUrl() {
            return dokan.urls.adminRoot + 'user-edit.php?user_id=' + this.store.id;
        },

        updateStore() {
            const self = this;

            self.isUpdating = true;

            dokan.api.put( `/stores/${self.store.id}`, self.store )
                .done( ( response ) => {
                    self.editMode = false;
                    self.store = response;
                    self.isUpdating = false;
                    self.editingCategories = false;

                    this.updateCommissonRate();

                    this.showAlert(
                        this.__( 'Vendor Updated', 'dokan' ),
                        this.__( 'Vendor Updated Successfully!', 'dokan' ),
                        'success'
                    );

                } )
                .fail((response) => {
                    this.showAlert( this.__( response.responseJSON.message, 'dokan' ), '', 'error' );
                } )
                .always( () => {
                    this.$router.push( {
                        query: {edit: false}
                    } );
                } );
        },

        uploadGravatar( image ) {
            this.store.gravatar_id = image.id;
        },

        uploadBanner( image ) {
            this.store.banner_id = image.id
        },

        showAlert( $title, $des, $status ) {
            this.$swal( $title, $des, $status );
        },

        getDefaultPic() {
            return dokan.urls.assetsUrl + '/images/store-pic.png';
        },

        updateCommissonRate() {
            this.stats.others.commission_rate = this.store.admin_commission;
            this.stats.others.additional_fee = this.store.admin_additional_fee;
            this.stats.others.commission_type = this.store.admin_commission_type;
        },

        setStoreCategories() {
            let self = this;
            let storeCategories = $( '#store-categories' );

            storeCategories.selectWoo( {
                multiple: self.isCategoryMultiple ? true : false,
                ajax: {
                    delay: 800,
                    url: `${dokan.rest.root}dokan/v1/store-categories?per_page=50`,
                    dataType: 'json',
                    headers: {
                        "X-WP-Nonce" : dokan.rest.nonce
                    },
                    data(params) {
                        return {
                            search: params.term
                        };
                    },
                    processResults(data) {
                        return {
                            results: data.map( (cat) => {
                                return {
                                    id: cat.id,
                                    text: cat.name,
                                    slug: cat.slug
                                };
                            })
                        }
                        cache: true
                    }
                }
            } );

            self.store.categories.forEach( ( category ) => {
                let option = new Option( category.name, category.id, true, true );
                storeCategories.append( option ).trigger( 'change' );
            });

            $( '#store-categories' ).on( 'select2:select', (e) => {
                if ( self.isCategoryMultiple ) {
                    self.store.categories.push( {
                        id: e.params.data.id,
                        name: e.params.data.text,
                        slug: e.params.data.slug
                    } );
                } else {
                    self.store.categories[0] = {
                        id: e.params.data.id,
                        name: e.params.data.text,
                        slug: e.params.data.slug
                    }
                }
            } );

            $( '#store-categories' ).on( 'select2:unselect', (e) => {
                let catId = e.params.data.id;
                self.store.categories.forEach( (cat, index) => {
                    if ( parseInt( cat.id ) === parseInt( catId ) ) {
                        $( `#store-categories option[value=${catId}]` ).remove();
                        self.store.categories.splice( index, 1 );
                    }
                });
            });
        },

        async editCategory() {
            this.editingCategories = true;
            await this.$nextTick();
            this.setStoreCategories();
        }
    }
};
</script>

<style lang="less">
.dokan-vendor-single {
    .dokan-hide {
        display: none;
    }

    .vendor-profile {
        .action-links.edit-mode {
            .button span {
                line-height: 27px
            }
        }
        .action-links.footer.edit-mode {
            float: right;
            margin-top: 20px;
        }
    }

    .dokan-form-input {
        width: 100%;
        padding: 6px;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
        margin-top: 6px;
        margin-bottom: 16px;
        resize: vertical;
        height: auto;
    }

    .dokan-form-input::placeholder {
        color: #bcbcbc;
    }


    * {
        box-sizing: border-box;
    }

    .modal-body {
        min-height: 150px;
        max-height: 350px;

        .form-row {
            padding-bottom: 10px;

            input {
                width: 90%;
            }
        }

        label {
            display: block;
            padding-bottom: 3px;
        }
    }

    .vendor-header {
        display: flex;

        .profile-info {
            background: #fff;
            border: 1px solid #D9E4E7;
            padding: 20px;
            width: 285px;
            margin-right: 30px;
            border-radius: 3px;
            position: relative;

            .featured-vendor {
                position: absolute;
                top: 10px;
                right: 15px;
                color: #FF9800;
            }
        }

        .profile-banner {
            position: relative;
            width: ~"calc(100% - 285px + 30px)";
            // max-width: 850px;
            height: 350px;
            border: 1px solid #dfdfdf;
            background: #496a94;
            overflow: hidden;

            img {
                height: 350px;
                width: 100%;
            }

            .action-links {
                position: absolute;
                right: 20px;
                top: 20px;

                .button {
                    box-shadow: 1px 1px 5px rgba(0, 0, 0, 0.2);
                }

                .button.visit-store {
                    background: #0085ba;
                    border-color: #0085ba;
                    color: #fff;

                    &:hover {
                        background: #008ec2;
                        border-color: #006799;
                    }

                    .dashicons {
                        font-size: 17px;
                        margin-top: 5px;
                    }
                }

                .button.edit-store {
                    color: #B8BAC2;
                    background: #fff;
                    border-color: #fff;
                    margin-left: 5px;

                    &:hover {
                        background: #eee;
                        border-color: #eee;
                    }

                    .dashicons {
                        margin-top: 3px;
                    }
                }
            }
        }

        .profile-icon {
            position: relative;
            text-align: center;
            margin: 0 auto;

            .edit-photo {
                position: absolute;
                left: 33%;
                top: 46px;
                color: white;
                width: 80px;
                // color: black;
            }

            img {
                height: 120px;
                width: 120px;
                border-radius: 50%
            }
        }

        .profile-icon.edit-mode {
            .dokan-upload-image {
                max-width: 120px;
                margin: 0 auto;
            }

            img {
                border: 5px solid #1a9ed4;

                cursor: pointer;
                opacity: .8;

                &:hover {
                    padding: 5px;
                    background-color: #f1f1f1;
                    transition: padding .2s;
                }
            }
        }

        .profile-banner.edit-mode {
            cursor: pointer;

            .banner-wrap {
                display: flex;
                justify-content: center;

                img {
                    border: 5px solid #5ca9d3;
                    opacity: .5;

                    &:hover {
                        padding: 5px;
                        background-color: #f1f1f1;
                        transition: padding .2s;
                    }
                }

                .edit-banner {
                    position: absolute;
                    top: 75%;
                    font-size: 30px;
                    font-weight: 400;
                    color: white;

                    i.change-banner {
                        font-size: 50px;
                        margin-top: -70px;
                        position: relative;
                        left: 140px;
                    }
                }

            }
        }

        .store-info {

            .star-rating {
                text-align: center;

                span {
                    &:before {
                        content: "\f154";
                        color: #999;
                    }

                    &.active:before {
                        content: "\f155";
                        color: #FF9800;
                    }
                }
            }

            h2 {
                text-align: center;
                font-size: 2em;
                margin-bottom: .5em;
            }

            .store-details {
                color: #AEB0B3;

                .dashicons {
                    color: #BABCC3;
                }

                li {
                    margin-bottom: 8px;
                    padding-left: 30px;

                    &:before {
                        display: inline-block;
                        width: 20px;
                        height: 20px;
                        font-size: 20px;
                        line-height: 1;
                        font-family: dashicons;
                        text-decoration: inherit;
                        font-weight: 400;
                        font-style: normal;
                        vertical-align: top;
                        text-align: center;
                        transition: color .1s ease-in 0;
                        -webkit-font-smoothing: antialiased;
                        -moz-osx-font-smoothing: grayscale;
                        margin-left: -30px;
                        width: 30px;
                    }
                }

                li.address:before {
                    content: "\f230";
                }

                li.phone {

                    &:before {
                        content: "\f525";
                        transform: scale(-1, 1);
                    }
                }
            }

            .store-details.edit-mode {
                .content-header {
                    display: none;
                }

                li {
                    padding-left: 0;
                }
            }

            .actions {
                margin-top: 25px;
                text-align: center;

                .dashicons {
                    color: #fff;
                    border-radius: 50%;
                    font-size: 16px;
                    width: 16px;
                    height: 16px;
                    vertical-align: middle;
                    margin-top: -2px;
                }

                .message {
                    background: #1FB18A;
                    border-color: #1FB18A;
                    color: #fff;
                    box-shadow: none;
                    font-size: 0.9em;
                    margin-right: 7px;

                    &:hover {
                        background: darken(#1FB18A, 5%);
                        border-color: darken(#1FB18A, 5%);
                    }
                }

                .status {
                    background-color: #fff;
                    box-shadow: none;
                    font-size: 0.9em;
                    border-color: #ddd;

                    &:hover {
                        background-color: #eee;
                    }

                    &.enabled .dashicons {
                        background-color: #19c11f;

                        &:before {
                            content: "\f147";
                            margin-left: -2px;
                        }
                    }

                    &.disabled .dashicons {
                        background-color: #f44336;

                        &:before {
                            content: "\f158";
                        }
                    }
                }
            }

            a.store-categoy-names {
                text-align: center;
                font-weight: 500;
                font-size: 14px;
                margin: 8px 0 14px;
                color: #444;
                text-decoration: none;
                display: block;
                line-height: 1.6;

                &:hover {
                    color: #0073aa;
                }
            }

            .store-categories-editing {

                h4 {
                    font-size: 15px;
                    font-weight: 700;
                    margin-bottom: 5px;
                }

                .button-link {
                    text-decoration: none;
                }
            }
        }

        .store-info.edit-mode {
            .account-info {
                .content-header {
                    display: none;
                }

                .column {
                    // display: flex;
                    label {
                        float: left;
                        clear: both;
                        margin-top: 10px;
                        margin-left: -4px;
                    }
                    .dokan-form-input {
                        width: 60%;
                        padding: 5px;
                        float: right;
                        margin-right: -4px;
                    }
                    .store-url {
                        margin: 0;
                        padding: 0;
                        bottom: 10px;
                        font-style: italic;
                        color: #a09f9f;
                        font-size: 12px;
                    }
                }
            }
        }
    }

    .vendor-summary {
        display: flex;
        justify-content: space-between;
        margin-top: 20px;

        .summary-wrap {
            width: 72%;
            background: #fff;
            border: 1px solid #D9E4E7;
            border-radius: 3px;
            padding: 20px;
            display: flex;
            justify-content: space-between;
        }

        .stat-summary {
            width: 32%;

            h3 {
                margin: 0 0 1em 0;
            }

            ul.counts {
                border: 1px solid #dfdfdf;
                margin-bottom: 0;

                li {
                    margin-bottom: 10px;
                    border-top: 1px solid #dfdfdf;
                    position: relative;
                    padding: 15px 10px 5px 75px;

                    &:first-child {
                        border-top: none;
                    }

                    .count {
                        font-size: 1.5em;
                        line-height: 130%;

                        a {
                            text-decoration: none;
                        }
                    }

                    .subhead {
                        color: #999;
                        display: block;
                        margin-top: 3px;
                    }

                    &:after {
                        display: inline-block;
                        width: 22px;
                        height: 22px;
                        font-size: 22px;
                        line-height: 1;
                        font-family: dashicons;
                        text-decoration: inherit;
                        font-weight: 400;
                        font-style: normal;
                        vertical-align: top;
                        text-align: center;
                        transition: color .1s ease-in 0;
                        -webkit-font-smoothing: antialiased;
                        -moz-osx-font-smoothing: grayscale;
                        left: 31px;
                        top: 26px;
                        color: #fff;
                        position: absolute;
                    }

                    &:before {
                        position: absolute;
                        width: 41px;
                        height: 41px;
                        border-radius: 50%;
                        left: 20px;
                        top: 18px;
                        content: " ";
                    }

                    &.products {
                        color: #FB094C;

                        a { color: #FB094C; }
                        &:before { background-color: #FB094C; }
                        &:after {
                            font-family: WooCommerce!important;
                            content: '\e006';
                        }
                    }

                    &.items {
                        color: #2CC55E;

                        &:before { background-color: #2CC55E; }
                        &:after { content: "\f233"; }
                    }

                    &.visitors{
                        color: #0F72F9;

                        &:before { background-color: #0F72F9; }
                        &:after { content: "\f307"; }
                    }

                    &.orders {
                        color: #323ABF;

                        a { color: #323ABF; }
                        &:before { background-color: #323ABF; }
                        &:after { content: "\f174"; }
                    }

                    &.gross {
                        color: darken(#99E412, 8%);

                        &:before { background-color: #99E412; }
                        &:after { content: "\f239"; }
                    }

                    &.earning {
                        color: #8740A7;

                        &:before { background-color: #8740A7; }
                        &:after { content: "\f524"; }
                    }

                    &.commision {
                        color: #FB0A4C;

                        &:before { background-color: #FB0A4C; }
                        &:after { content: "\f524"; }
                    }

                    &.balance {
                        color: #FD553B;

                        &:before { background-color: #FD553B; }
                        &:after { content: "\f184"; }
                    }

                    &.reviews {
                        color: #EE8A12;

                        &:before { background-color: #EE8A12; }
                        &:after { content: "\f125"; }
                    }
                }
            }
        }

        .vendor-info {
            background: #fff;
            border: 1px solid #D9E4E7;
            border-radius: 3px;
            // padding: 20px;
            width: 27%;

            .subhead {
                color: #999;
                display: block;
                margin-bottom: 10px;
            }

            ul {
                margin: 0;
            }

            li {
                border-top: 1px solid #dfdfdf;
                padding: 10px 15px;

                &:first-child {
                    border-top: none;
                }
            }

            li.registered {
                padding-top: 15px;
            }

            .social-profiles {

                a {
                    text-decoration: none;
                    color: #ddd;
                    margin-right: 5px;
                }

                [class^="flaticon-"]:before,
                [class*=" flaticon-"]:before,
                [class^="flaticon-"]:after,
                [class*=" flaticon-"]:after {
                    font-size: 19px;
                }

                a.active {
                    .flaticon-facebook-logo { color: #3C5998; }
                    .flaticon-twitter-logo-silhouette { color: #1496F1; }
                    .flaticon-google-plus { color: #EC322B; }
                    .flaticon-youtube { color: #CD2120; }
                    .flaticon-instagram { color: #B6224A; }
                    .flaticon-linkedin-logo { color: #0C61A8; }
                    .flaticon-pinterest-logo { color: #BD091E; }
                    .flaticon-flickr-website-logo-silhouette { color: #FB0072; }
                }
            }

            li.payments {
                // text-align: center;

                [class^="flaticon-"]:before,
                [class*=" flaticon-"]:before,
                [class^="flaticon-"]:after,
                [class*=" flaticon-"]:after {
                    font-size: 35px;
                    vertical-align: top;
                }

                [class^="flaticon-"] {
                    margin-right: 8px;
                    color: #ddd;
                }

                .flaticon-bank-building:before {
                    font-size: 26px;
                }

                .flaticon-money.active { color: #011B78; }
                .flaticon-stripe-logo.active { color: #5458DF; }
                .flaticon-bank-building.active { color: #444; }
                .flaticon-skrill-pay-logo.active { color: #721052; }
            }
        }
    }

    .vendor-other-info {
        .content-header {
            font-size: 14px !important;
            font-weight: 600 !important;
            padding-left: 12px !important;
        }

        .address-social-info {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;

            .content-header {
                font-size: 18px;
                margin: 0;
                padding: 10px;
                border-bottom: 1px solid #f1f1f1;
            }

            .social-info, .account-info {
                width: 48%;
                background-color: white;

                .content-body {
                    padding: 10px 20px;
                }
            }

            .account-info {
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
        }

        .payment-info {
            background-color: white;
            margin-top: 30px;

            .content-header {
                font-size: 18px;
                margin: 0;
                padding: 10px;
                border-bottom: 1px solid #f1f1f1;
            }

            .content-body {
                display: flex;
                justify-content: space-between;

                .dokan-form-group {
                    width: 48%;
                    padding: 10px 20px;
                }
            }
        }

        .multiselect {
            margin-top: 5px;
        }

        .multiselect__select {
            &:before {
                top: 55%;
            }
        }

        .multiselect__tags {
            min-height: 34px;
        }

        .multiselect__single {
            font-size: 14px;
            padding-left: 0px;
            margin-bottom: 4px;
            margin-top: -2px;
        }

        .multiselect__input {
            &:focus {
                box-shadow: none;
                border: none;
                outline: none;
            }
        }

    }
}

@media only screen and (max-width: 600px) {
    .dokan-vendor-single {
        .vendor-header {
            display: block;

            .profile-info {
                width: 100% !important;
                margin-bottom: 10px;
            }

            .profile-banner {
                width: 100% !important;
            }
        }

        .vendor-summary {
            display: block;
            .summary-wrap {
                display: block;
                width: 100% !important;

                .stat-summary {
                    width: 100% !important;
                    padding-bottom: 20px;
                }
            }
            .vendor-info {
                width: 100% !important;
                margin-top: 20px;
            }
        }
    }
}
</style>
