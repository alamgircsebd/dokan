<template>
    <div class="dokan-vendor-single">
        <div style="margin-bottom: 10px">
            <router-link to="/" class="button">&larr; Back to listing</router-link>
        </div>

        <div class="vendor-header" v-if="store.id">
            <div class="profile-banner">
                <div class="banner-wrap">
                    <img v-if="store.banner" :src="store.banner" :alt="store.store_name">
                </div>
            </div>
            <div class="profile-info">
                <div class="profile-icon">
                    <div class="profile-icon-wrap">
                        <img :src="store.gravatar" :alt="store.store_name">
                    </div>
                </div>

                <div class="store-info">
                    <h2 class="store-name">{{ store.store_name }}</h2>

                    <ul class="store-info">
                        <li class="address">
                            <span class="dashicons dashicons-location"></span>
                            <span class="street_1">{{ store.address.street_1 }}, </span>
                            <span class="city">{{ store.address.city }}, </span>
                            <span class="state-zip">{{ store.address.state }} {{ store.address.zip }}</span>
                        </li>
                        <li class="phone">
                            <span class="dashicons dashicons-phone"></span>
                            {{ store.phone }}
                        </li>
                        <li class="rating">
                            <span class="dashicons dashicons-star-filled"></span>
                            {{ store.rating.rating }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <vcl-twitch v-else height="300" primary="#ffffff"></vcl-twitch>
    </div>
</template>

<script>
import { VclFacebook, VclTwitch } from 'vue-content-loading';

export default {

    name: 'VendorSingle',

    components: {
        VclFacebook,
        VclTwitch
    },

    data () {
        return {
            store: {}
        };
    },

    computed: {

        id() {
            return this.$route.params.id;
        }
    },

    created() {
        this.fetch();
    },

    methods: {

        fetch() {
            dokan.api.get('/stores/' + this.id )
            .done(response => this.store = response);
        }
    }
};
</script>

<style lang="less">
.dokan-vendor-single {
    max-width: 850px;

    .vendor-header {

        .profile-banner {
            position: relative;
            height: 315px;
            border: 1px solid #dfdfdf;
            background: #fff;
            overflow: hidden;

            img {
                height: 315px;
                width: auto;
            }
        }

        .profile-info {
            display: flex;
        }

        .profile-icon {
            width: 200px;
            margin-right: 20px;
            position: relative;

            .profile-icon-wrap {
                background: #fff;
                border: 5px solid #fff;
                height: 170px;
                position: absolute;
                width: 170px;
                box-shadow: 1px 1px 8px rgba(0, 0, 0, .1);
                padding: 3px;
                box-sizing: border-box;
                top: -70px;
                left: 30px;

                img {
                    height: auto;
                    width: 100%;
                }
            }
        }

        .store-info {
            position: relative;
            width: ~"calc(100% - 220px)";

            h2 {
                position: absolute;
                top: -65px;
                width: 100%;
                font-size: 2em;
                color: #fff;
                text-shadow: 0 0 3px rgba(0, 0, 0, 0.8);
            }
        }
    }
}
</style>
