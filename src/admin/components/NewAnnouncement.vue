<template>
    <div class="dokan-announcement-form-wrapper">
        <h1 class="wp-heading-inline">{{ __( 'Add New Announcement', 'dokan' ) }}</h1>

        <div class="help-block">
            <span class='help-text'><a href="https://wedevs.com/docs/dokan/announcements/" target="_blank">{{ __( 'Need Any Help ?', 'dokan' ) }}</a></span>
            <span class="dashicons dashicons-smiley"></span>
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
                                <input type="submit" class="button button-default draft-btn" :value="draftBtnLabel" @click.prevent="createAnnouncement('draft')" :disabled="loadSpinner">
                                <span class="spinner" v-if="loadSpinner"></span>
                                <input type="submit" class="button button-primary publish-btn" :value="publishBtnLabel" @click.prevent="createAnnouncement('publish')" :disabled="loadSpinner">
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
                                            <multiselect v-model="announcement.sender_ids" id="ajax" label="name" track-by="id" placeholder="Type to search" open-direction="bottom" :options="vendors" :multiple="true" :searchable="true" :loading="isLoading" :internal-search="false" :clear-on-select="true" :close-on-select="false" :options-limit="300" :limit="3" :limit-text="limitText" :max-height="700" :show-no-results="false" :hide-selected="true" @search-change="asyncFind">
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
        name: 'NewAnnouncement',

        components: {
            Postbox,
            TextEditor
        },

        data() {
            return {
                announcement: {
                    title: '',
                    content: '',
                    status: 'publish',
                    sender_type: 'all_seller',
                    sender_ids: []
                },
                message: '',
                isSaved: false,
                loadSpinner: false,
                isLoading: false,
                draftBtnLabel : this.__( 'Save as Draft', 'dokan' ),
                publishBtnLabel : this. __( 'Send', 'dokan' ),
                vendors: [],
            }
        },

        computed: {
            submitBtnLabel() {
                return this.statusesLabel[this.announcement.status];
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

            createAnnouncement( status ) {
                var self = this;
                this.loadSpinner = true;
                let jsonData = {};
                jsonData = jQuery.extend( {}, this.announcement );

                jsonData.sender_ids = _.pluck( jsonData.sender_ids, 'id' );
                jsonData.status = status;

                dokan.api.post('/announcement', jsonData )
                .done( response => {
                    this.isSaved = false;
                    this.loadSpinner = false;

                    if ( 'draft' == status ) {
                        this.$router.push({
                            name: 'EditAnnouncement',
                            params: { id: response.id }
                        });
                    } else {
                        this.$router.push({
                            name: 'Announcement',
                        });
                    }
                })
                .error( response => {
                    this.isSaved = false;
                    alert( response.responseJSON.message );
                });
            }
        }

    };
</script>

<style lang="less">

    .dokan-announcement-form-wrapper {
        position: relative;

        .help-block {
            position: absolute;
            top: 10px;
            right: 10px;

            span.help-text {
                display: inline-block;
                margin-top: 4px;
                margin-right: 6px;
                a {
                    text-decoration: none;
                }
            }

            span.dashicons {
                font-size: 25px;
            }
        }

        #post-body {
            .post-body-content {
                position: relative;
                width: 100%;
                min-width: 463px;
                float: left;
                margin-bottom: 20px;

                #postdivrich {
                    margin-top: 20px;
                }
            }

            .announcement-actions {
                background: #fafafa;
                .action {
                    padding: 15px 0px;
                    .spinner {
                        visibility: visible;
                        float:none;
                    }

                    .draft-btn {
                        float:left;
                    }

                    .publish-btn {
                        float:right;
                    }
                }
            }
        }

        .multiselect {
            input#ajax {
                background: none !important;
                box-shadow: none !important;
                outline: 0 !important;
                border:none !important;
            }
        }

    }

</style>