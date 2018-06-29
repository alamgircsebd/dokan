<template>
    <div class="dokan-announcement-form-wrapper" v-if="announcement.id">
        <h1 class="wp-heading-inline">{{ __( 'Edit Announcement', 'dokan' ) }}</h1>
        <router-link :to="{ name: 'NewAnnouncement' }" class="page-title-action">{{ __( 'Add Announcement', 'dokan' ) }}</router-link>

        <div class="help-block">
            <span class='help-text'><a href="https://wedevs.com/docs/dokan/announcements/" target="_blank">{{ __( 'Need Any Help ?', 'dokan' ) }}</a></span>
            <span class="dashicons dashicons-smiley"></span>
        </div>

        <div id="announcement-message_updated" class="announcement-error notice is-dismissible updated" v-if="isSaved">
            <p><strong v-html="message"></strong></p>
            <button type="button" class="notice-dismiss" @click.prevent="isSaved = false">
                <span class="screen-reader-text">{{ __( 'Dismiss this notice.', 'dokan-lite' ) }}</span>
            </button>
        </div>

        <hr class="wp-header-end">

        <form action="" method="post" id="post">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div class="post-body-content">
                        <div id="titlediv">
                            <div id="titlewrap">
                                <input type="text" v-model="announcement.title" name="post_title"  size="30" id="title" autocomplete="off" placeholder="Enter announcement title">
                            </div>
                            <div class="inside"></div>
                        </div>

                        <div id="postdivrich" class="postarea wp-editor-expand">
                            <text-editor v-model="announcement.content"></text-editor>
                        </div>
                    </div>

                    <div id="postbox-container-1" class="postbox-container">
                        <postbox :title="__( 'Publish', 'dokan' )" extraClass="announcement-actions">
                            <div class="action">
                                <input type="submit" class="button button-default draft-btn" :value="draftBtnLabel" @click.prevent="updateAnnouncement('draft')">
                                <span class="spinner" v-if="loadSpinner"></span>
                                <input type="submit" class="button button-primary publish-btn" :value="publishBtnLabel" @click.prevent="updateAnnouncement( 'publish' )">
                                <div class="clear"></div>
                            </div>
                        </postbox>
                    </div>

                    <div id="postbox-container-2" class="postbox-container">
                        <postbox :title="__( 'Announcement Settings', 'dokan' )" extraClass="announcement-settings">
                            <table class="form-table announcement-meta-options">
                                <tbody>
                                    <tr>
                                        <th>{{ __( 'Send Announcement To', 'dokan' ) }}</th>
                                        <td>
                                            <select name="announcement_sender_type" id="announcement_sender_type" v-model="announcement.sender_type">
                                                <option value="all_seller">{{ __( 'All Vendor', 'dokan' )}}</option>
                                                <option value="selected_seller">{{ __( 'Selected Vendor', 'dokan' )}}</option>
                                            </select>
                                        </td>
                                    </tr>

                                    <tr v-if="'selected_seller' === announcement.sender_type">
                                        <th>{{ __( 'Select Vendors', 'dokan' ) }}</th>
                                        <td>
                                            <multiselect v-model="announcement.sender_ids" id="ajax" label="name" track-by="id" placeholder="Type to search" open-direction="bottom" :options="vendors" :multiple="true" :searchable="true" :loading="isLoading" :internal-search="false" :clear-on-select="false" :close-on-select="false" :options-limit="300" :limit="3" :limit-text="limitText" :max-height="600" :show-no-results="false" :hide-selected="true" @search-change="asyncFind">
                                                <template slot="clear" slot-scope="props">
                                                  <div class="multiselect__clear" v-if="announcement.sender_ids.length" @mousedown.prevent.stop="clearAll(props.search)"></div>
                                                </template><span slot="noResult">Oops! No elements found. Consider changing the search query.</span>
                                            </multiselect>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </postbox>
                    </div>
                </div>
                <br class="clear">
            </div>
        </form>
    </div>
</template>

<script>
    let TextEditor = dokan_get_lib('TextEditor');
    let Postbox = dokan_get_lib('Postbox');

    export default {
        name: 'EditAnnouncement',

        components: {
            Postbox,
            TextEditor
        },

        data() {
            return {
                announcement: {},
                loadSpinner: false,
                isSaved: false,
                isUpdated: false,
                isLoading: false,
                draftBtnLabel : this.__( 'Save as Draft', 'dokan' ),
                publishBtnLabel : this. __( 'Send', 'dokan' ),
                message: '',
                vendors: [],
            }
        },

        methods: {
            limitText (count) {
              return `and ${count} other vendors`
            },
            asyncFind (query) {
                this.isLoading = true
                dokan.api.get('/stores' + '?search=' + query )
                .done( response => {
                    this.isLoading = false;
                    this.vendors = _.map( response, function( item ) {
                        return {
                            id : item.id,
                            name: item.store_name + '( ' + item.email + ' )'
                        };
                    });
                });
            },
            clearAll () {
                this.announcement.sender_ids = []
            },

            fetchAnnouncement() {
                dokan.api.get('/announcement/' + this.$route.params.id  )
                .done( response => {
                    this.announcement = response;
                })
                .error( response => {
                    alert( response.responseJSON.message );
                });
            },

            updateAnnouncement( status ) {
                this.loadSpinner = true;
                let jsonData = {};
                jsonData = jQuery.extend( {}, this.announcement );

                jsonData.sender_ids = _.pluck( jsonData.sender_ids, 'id' );
                jsonData.status = status;

                dokan.api.put('/announcement/' + this.$route.params.id, jsonData )
                .done( response => {
                    this.loadSpinner = false;
                    this.isSaved = true;
                    this.message = this.__( 'Announcement draft successfully', 'dokan' );
                    if ( 'draft' == status ) {
                        this.$router.push({
                            name: 'EditAnnouncement',
                            params: { id: response.id }
                        });
                    } else {
                        this.loadSpinner = false;
                        this.$router.push({
                            name: 'Announcement',
                        });
                    }
                })
                .error( response => {
                    this.loadSpinner = false;
                    this.isSaved = true;
                    this.message = response.responseJSON.message;
                });
            }
        },

        created() {
            this.fetchAnnouncement();
        }

    };
</script>

<style lang="less">

</style>