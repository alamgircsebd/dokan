<template>
    <div class="dokan-vendor-single">
        <div style="margin-bottom: 10px">
            <a class="button" href="javascript:history.go(-1)">&larr; Go Back</a>
        </div>

        <modal
            title="Send Email"
            v-if="showDialog"
            @close="showDialog = false"
        >
            <template slot="body">
                <div class="form-row">
                    <label for="mailto">To</label>
                    <input type="text" id="mailto" disabled="disabled" :value="mailTo">
                </div>

                <div class="form-row">
                    <label for="subject">Subject</label>
                    <input type="text" id="subject" v-model="mail.subject">
                </div>

                <div class="form-row">
                    <label for="message">Message</label>
                    <textarea id="message" rows="5" cols="60" v-model="mail.body"></textarea>
                </div>
            </template>

            <template slot="footer">
                <button class="button button-primary button-large" @click="sendEmail()">Send Email</button>
            </template>
        </modal>

        <div class="vendor-profile" v-if="store.id">
            <section class="vendor-header">
                <div class="profile-info">

                    <div class="featured-vendor" v-if="store.featured">
                        <span title="Featured Vendor" class="dashicons dashicons-star-filled"></span>
                    </div>

                    <div class="profile-icon">
                        <img :src="store.gravatar" :alt="store.store_name">
                    </div>

                    <div class="store-info">
                        <h2 class="store-name">{{ store.store_name ? store.store_name : '(No Name)' }}</h2>

                        <div class="star-rating">
                            <span v-for="i in 5" :class="['dashicons', i <= store.rating.rating ? 'active' : '' ]"></span>
                        </div>

                        <ul class="store-details">
                            <li class="address">
                                <span class="street_1">{{ store.address.street_1 }}, </span>
                                <span class="city">{{ store.address.city }}, </span>
                                <span class="state-zip">{{ store.address.state }} {{ store.address.zip }}</span>
                            </li>
                            <li class="phone">
                                {{ store.phone ? store.phone : '—' }}
                            </li>
                        </ul>

                        <div class="actions">
                            <button class="button message" @click="messageDialog()"><span class="dashicons dashicons-email"></span> Send Email</button>
                            <button :class="['button', 'status', store.enabled ? 'enabled' : 'disabled']"><span class="dashicons"></span> {{ store.enabled ? 'Enabled' : 'Disabled' }}</button>
                        </div>
                    </div>
                </div>

                <div class="profile-banner">
                    <div class="banner-wrap">
                        <img v-if="store.banner" :src="store.banner" :alt="store.store_name">
                    </div>
                    <div class="action-links">
                        <a :href="store.shop_url" target="_blank" class="button visit-store">Visit Store <span class="dashicons dashicons-arrow-right-alt"></span></a>
                        <a :href="editUrl()" class="button edit-store"><span class="dashicons dashicons-edit"></span></a>
                    </div>
                </div>
            </section>

            <section class="vendor-summary" v-if="stats !== null">
                <div class="summary-wrap products-revenue">
                    <div class="stat-summary products">
                        <h3>Products</h3>

                        <ul class="counts">
                            <li class="products">
                                <span class="count"><a :href="productUrl()">{{ stats.products.total }}</a></span>
                                <span class="subhead">Total Products</span>
                            </li>
                            <li class="items">
                                <span class="count">{{ stats.products.sold }}</span>
                                <span class="subhead">Items Sold</span>
                            </li>
                            <li class="visitors">
                                <span class="count">{{ stats.products.visitor }}</span>
                                <span class="subhead">Store Visitors</span>
                            </li>
                        </ul>
                    </div>

                    <div class="stat-summary revenue">
                        <h3>Revenue</h3>

                        <ul class="counts">
                            <li class="orders">
                                <span class="count"><a :href="ordersUrl()">{{ stats.revenue.orders }}</a></span>
                                <span class="subhead">Orders Processed</span>
                            </li>
                            <li class="gross">
                                <span class="count">{{ stats.revenue.sales | currency }}</span>
                                <span class="subhead">Gross Sales</span>
                            </li>
                            <li class="earning">
                                <span class="count">{{ stats.revenue.earning | currency }}</span>
                                <span class="subhead">Total Earning</span>
                            </li>
                        </ul>
                    </div>

                    <div class="stat-summary others">
                        <h3>Others</h3>

                        <ul class="counts">
                            <li class="commision">
                                <span class="count">{{ stats.others.commision_rate }}%</span>
                                <span class="subhead">Earning Rate</span>
                            </li>
                            <li class="balance">
                                <span class="count">{{ stats.others.balance | currency }}</span>
                                <span class="subhead">Current Balance</span>
                            </li>
                            <li class="reviews">
                                <span class="count">{{ stats.others.reviews }}</span>
                                <span class="subhead">Reviews</span>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="vendor-info">
                    <ul>
                        <li class="registered">
                            <div class="subhead">Registered Since</div>
                            <span class="date">
                                {{ moment(store.registered).format('MMM D, YYYY') }}
                                ({{ moment(store.registered).toNow(true) }})
                            </span>
                        </li>
                        <li class="social-profiles">
                            <div class="subhead">Social Profiles</div>

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
                            <div class="subhead">Payment Methods</div>

                            <div class="payment-methods">
                                <span title="PayPal Payment" :class="['flaticon-money', hasPaymentEmail('paypal') ? 'active' : '']"></span>
                                <span title="Stripe Connect" class="flaticon-stripe-logo"></span>
                                <span title="Bank Payment" :class="['flaticon-bank-building', hasBank ? 'active': '' ]"></span>
                                <span title="Skrill" :class="['flaticon-skrill-pay-logo', hasPaymentEmail('skrill') ? 'active' : '']"></span>
                            </div>
                        </li>
                        <li class="publishing">
                            <div class="subhead">Product Publishing</div>

                            <span v-if="store.trusted"><span class="dashicons dashicons-shield"></span> Direct</span>
                            <span v-else><span class="dashicons dashicons-backup"></span> Requires Review</span>
                        </li>
                    </ul>
                </div>
            </section>
        </div>
        <vcl-twitch v-else height="300" primary="#ffffff"></vcl-twitch>
    </div>
</template>

<script>
let ContentLoading = dokan_get_lib('ContentLoading');
let Modal = dokan_get_lib('Modal');

let VclFacebook = ContentLoading.VclFacebook;
let VclTwitch = ContentLoading.VclTwitch;

export default {

    name: 'VendorSingle',

    components: {
        VclFacebook,
        VclTwitch,
        Modal,
    },

    data () {
        return {
            showDialog: false,
            store: {},
            stats: null,
            mail: {
                subject: '',
                body: ''
            }
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
        }
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
    },

    methods: {

        fetch() {
            dokan.api.get('/stores/' + this.id )
            .done(response => this.store = response);
        },

        fetchStats() {
            dokan.api.get('/stores/' + this.id + '/stats' )
            .done(response => this.stats = response);
        },

        isSocialActive(profile) {
            if (this.store.social.hasOwnProperty(profile) && this.store.social[profile] !== false ) {
                return true;
            }

            return false;
        },

        hasPaymentEmail(method) {
            if ( this.store.payment.hasOwnProperty(method) && this.store.payment[method].email !== false ) {
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
                    title: 'Success!',
                    type: 'success',
                    text: 'Email has been sent successfully.'
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
            return dokan.urls.adminRoot + 'edit.php?post_type=shop_order&author=' + this.store.id;
        },

        editUrl() {
            return dokan.urls.adminRoot + 'user-edit.php?user_id=' + this.store.id;
        },
    }
};
</script>

<style lang="less">
.dokan-vendor-single {

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
            max-width: 850px;
            height: 315px;
            border: 1px solid #dfdfdf;
            background: #fff;
            overflow: hidden;

            img {
                height: 315px;
                width: auto;
            }

            .action-links {
                position: absolute;
                right: 20px;
                top: 20px;

                .button {
                    box-shadow: 1px 1px 5px rgba(0, 0, 0, 0.2);
                }

                .button.visit-store {
                    background: #FD563A;
                    border-color: #FD563A;
                    color: #fff;

                    &:hover {
                        background: darken(#FD563A, 5%);
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

            img {
                height: auto;
                width: 64px;
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
                        &:after { content: "\f513"; }
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
}
</style>
