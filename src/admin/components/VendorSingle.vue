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
                    <div class="profile-icon">
                        <img :src="store.gravatar" :alt="store.store_name">
                    </div>

                    <div class="store-info">
                        <h2 class="store-name">{{ store.store_name }}</h2>

                        <div class="star-rating">
                            <span v-for="i in 5" :class="['dashicons', i <= store.rating.count ? 'active' : '' ]"></span>
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
                </div>
            </section>

            <section class="vendor-summary">
                <div class="half-box products-revenue">
                    <div class="stat-summary products">
                        <h3>Products</h3>

                        <ul class="counts">
                            <li class="products">
                                <span class="count">27</span>
                                <span class="subhead">Total Products</span>
                            </li>
                            <li class="items">
                                <span class="count">155</span>
                                <span class="subhead">Items Sold</span>
                            </li>
                            <li class="visitors">
                                <span class="count">7650</span>
                                <span class="subhead">Store Visitors</span>
                            </li>
                        </ul>
                    </div>

                    <div class="stat-summary revenue">
                        <h3>Revenue</h3>

                        <ul class="counts">
                            <li class="orders">
                                <span class="count">140</span>
                                <span class="subhead">Orders Processed</span>
                            </li>
                            <li class="gross">
                                <span class="count">{{ 16125 | currency }}</span>
                                <span class="subhead">Gross Sales</span>
                            </li>
                            <li class="earning">
                                <span class="count">{{ 12240 | currency }}</span>
                                <span class="subhead">Total Earning</span>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="half-box">
                    <div class="stat-summary others">
                        <h3>Others</h3>

                        <ul class="counts">
                            <li class="commision">
                                <span class="count">10%</span>
                                <span class="subhead">Commision Rate</span>
                            </li>
                            <li class="balance">
                                <span class="count">{{ 190 | currency }}</span>
                                <span class="subhead">Current Balance</span>
                            </li>
                            <li class="reviews">
                                <span class="count">123</span>
                                <span class="subhead">Reviews</span>
                            </li>
                        </ul>
                    </div>

                    <div class="stat-summary others">
                        <h3>Others</h3>

                    </div>
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
        }
    },

    created() {
        this.fetch();
    },

    methods: {

        fetch() {
            dokan.api.get('/stores/' + this.id )
            .done(response => this.store = response);
        },

        messageDialog() {
            this.showDialog = true;
        },

        sendEmail() {
            this.showDialog = false;

            this.mail = {
                subject: '',
                body: ''
            };
        }
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

        .half-box {
            width: 49%;
            background: #fff;
            border: 1px solid #D9E4E7;
            border-radius: 3px;
            padding: 20px;
            display: flex;
            justify-content: space-between;
        }

        .stat-summary {
            width: 48%;

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
                        left: 29px;
                        top: 26px;
                        color: #fff;
                        position: absolute;
                    }

                    &:before {
                        position: absolute;
                        width: 41px;
                        height: 41px;
                        border-radius: 50%;
                        left: 18px;
                        top: 18px;
                        content: " ";
                    }

                    &.products {
                        color: #FB094C;

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
    }
}
</style>
