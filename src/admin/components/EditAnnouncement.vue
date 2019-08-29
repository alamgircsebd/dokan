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

                            <div class="sub-action">
                                <span id="timestamp">
                                    <span class="dashicons dashicons dashicons-calendar"></span>
                                    <template v-if="onSchedule">
                                        Schedule for: <strong>{{ scheduleTime.humanTime }}</strong>
                                    </template>

                                    <template v-else>
                                        Publish: <strong>immediately</strong>
                                    </template>
                                    <a v-show="!onScheduleEdit" href="#" @click.prevent="onScheduleEdit=true">Edit</a>
                                </span>
                                <fieldset v-show="onScheduleEdit" id="timestampdiv">
                                    <label>
                                        <select v-model="scheduleTime.month">
                                            <option v-for="month in months">
                                                {{ month }}
                                            </option>
                                        </select>
                                    </label>

                                    <label>
                                        <input v-model="scheduleTime.day" id="jj" type="text" size="2" maxlength="2" autocomplete="off">,
                                    </label>

                                    <label>
                                        <input v-model="scheduleTime.year" id="aa" type="text" size="4" maxlength="4" autocomplete="off"> @
                                    </label>

                                    <label>
                                        <input v-model="scheduleTime.hour" id="hh" type="text" size="2" maxlength="2" autocomplete="off"> :
                                    </label>

                                    <label>
                                        <input v-model="scheduleTime.min" id="mm" type="text" size="2" maxlength="2" autocomplete="off">
                                    </label>
                                </fieldset>
                                <p v-show="onScheduleEdit">
                                    <button @click.prevent="saveSchedule">{{ __( 'Ok', 'dokan' ) }}</button>
                                    <button @click.prevent="cancelSchedule">{{ __( 'Cancel', 'dokan' ) }}</button>
                                </p>
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
    let TextEditor  = dokan_get_lib('TextEditor');
    let Postbox     = dokan_get_lib('Postbox');
    let Multiselect = dokan_get_lib('Multiselect');
    let moment      = dokan_get_lib('moment');

    export default {
        name: 'EditAnnouncement',

        components: {
            Postbox,
            TextEditor,
            Multiselect
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
                onSchedule: false,
                onScheduleEdit: false,
                months: [
                    '01-Jan',
                    '02-Feb',
                    '03-Mar',
                    '04-Apr',
                    '05-May',
                    '06-Jun',
                    '07-Jul',
                    '08-Aug',
                    '09-Sep',
                    '10-Oct',
                    '11-Nov',
                    '12-Dec',
                ],
                scheduleTime: {
                    year: moment(dokan.current_time).format('YYYY'),
                    month: `${moment(dokan.current_time).format('MM')}-${moment(dokan.current_time).format('MMM')}`,
                    day: moment(dokan.current_time).format('DD'),
                    hour: moment(dokan.current_time).format('HH'),
                    min: moment(dokan.current_time).format('mm'),
                    postDate: '',
                    humanTime: ''
                }
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
                    if ( 'future' === response.status ) {
                        this.onSchedule = true;
                        this.publishBtnLabel = this.__( 'Schedule', 'dokan' );

                        let date = moment(response.created_at);
                        this.scheduleTime.year = date.format('YYYY');
                        this.scheduleTime.month = `${date.format('MM')}-${date.format('MMM')}`;
                        this.scheduleTime.day = moment(dokan.current_time).format('DD'),
                        this.scheduleTime.hour = date.format('HH');
                        this.scheduleTime.min = date.format('mm');

                        let {year, month, day, hour, min} = {...this.scheduleTime };

                        this.scheduleTime.humanTime = `${date.format('MMM')} ${day}, ${year} @ ${hour}:${min}`;
                        this.scheduleTime.postDate = `${year}-${month.substr(0,2)}-${day} ${hour}:${min}`;
                    }

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
                jsonData.post_date = this.scheduleTime.postDate;

                // if announcement is 'schedueld', but want to publish it now
                // change post status to `future` so that wp_insert_post can make it `publish`
                // and set post_date_gmt to `0000-00-00 00:00:00`
                if ( ! jsonData.post_date ) {
                    jsonData.status = 'future';
                    jsonData.post_date_gmt = '0000-00-00 00:00:00';
                }

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
            },

            saveSchedule() {
                this.onSchedule = true;
                this.onScheduleEdit = false;
                this.publishBtnLabel = this.__( 'Schedule', 'dokan' );

                let {year, month, day, hour, min} = {...this.scheduleTime};

                this.scheduleTime.postDate = `${year}-${month.substr(0,2)}-${day} ${hour}:${min}`;
                this.scheduleTime.humanTime = `${day} ${month.substr(3)}, ${year} @ ${hour}:${min}`;
            },

            cancelSchedule() {
                this.onSchedule = false;
                this.onScheduleEdit = false;
                this.publishBtnLabel = this.__( 'Send', 'dokan' );
                this.scheduleTime.postDate = '';
            }
        },

        created() {
            this.fetchAnnouncement();
        },
    };
</script>

<style lang="less">

</style>