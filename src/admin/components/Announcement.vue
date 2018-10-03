<template>
    <div class="dokan-announcement-wrapper">

        <h1 class="wp-heading-inline">{{ __( 'Announcement', 'dokan' ) }}</h1>
        <router-link :to="{ name: 'NewAnnouncement' }" class="page-title-action">{{ __( 'Add Announcement', 'dokan' ) }}</router-link>

        <div class="help-block">
            <span class='help-text'><a href="https://wedevs.com/docs/dokan/announcements/" target="_blank">{{ __( 'Need Any Help ?', 'dokan' ) }}</a></span>
            <span class="dashicons dashicons-smiley"></span>
        </div>

        <hr class="wp-header-end">

        <ul class="subsubsub">
            <li><router-link :to="{ name: 'Announcement' }" active-class="current" exact v-html="sprintf( __( 'All <span class=\'count\'>(%s)</span>', 'dokan' ), counts.all )"></router-link> | </li>
            <li><router-link :to="{ name: 'Announcement', query: { status: 'publish' }}" active-class="current" exact v-html="sprintf( __( 'Published <span class=\'count\'>(%s)</span>', 'dokan-lite' ), counts.publish )"></router-link> | </li>
            <li><router-link :to="{ name: 'Announcement', query: { status: 'pending' }}" active-class="current" exact v-html="sprintf( __( 'Pending <span class=\'count\'>(%s)</span>', 'dokan-lite' ), counts.pending )"></router-link> | </li>
            <li><router-link :to="{ name: 'Announcement', query: { status: 'draft' }}" active-class="current" exact v-html="sprintf( __( 'Draft <span class=\'count\'>(%s)</span>', 'dokan-lite' ), counts.draft )"></router-link> | </li>
            <li><router-link :to="{ name: 'Announcement', query: { status: 'trash' }}" active-class="current" exact v-html="sprintf( __( 'Trash <span class=\'count\'>(%s)</span>', 'dokan-lite' ), counts.trash )"></router-link></li>
        </ul>

        <list-table
            :columns="columns"
            :rows="requests"
            :loading="loading"
            :action-column="actionColumn"
            :actions="actions"
            :show-cb="showCb"
            :bulk-actions="bulkActions"
            :not-found="notFound"
            :total-pages="totalPages"
            :total-items="totalItems"
            :per-page="perPage"
            :current-page="currentPage"
            @pagination="goToPage"
            @action:click="onActionClick"
            @bulk:click="onBulkAction"
        >

            <template slot="title" slot-scope="data">
                <strong v-if="'publish' == data.row.status">{{ data.row.title }}</strong>
                <strong v-else><a :href="editUrl(data.row.id)">{{ data.row.title }}</a></strong>
            </template>

            <template slot="status" slot-scope="data">
                <span :class="data.row.status">{{ status[data.row.status] }}</span>
            </template>

            <template slot="content" slot-scope="data">
                <span :class="data.row.status"><a href="#" @click.prevent="showContent( data.row )"><span class="dashicons dashicons-visibility"></span></a></span>
            </template>

            <template slot="created_at" slot-scope="data">
                {{ moment(data.row.created_at).format('MMM D, YYYY') }}
            </template>

            <template slot="send_to" slot-scope="data">
                <span v-if="'all_seller' === data.row.sender_type">{{ __( 'All Vendor', 'dokan' ) }}</span>
                <span v-if="'selected_seller' === data.row.sender_type">{{ __( 'Selected Vendor', 'dokan' ) }}</span>
            </template>

            <template slot="row-actions" slot-scope="data">
                <template v-for="(action, index) in actions">
                    <span :class="action.key" v-if="action.key == 'edit' && 'publish' != data.row.status">
                        <a :href="editUrl(data.row.id)">{{ action.label }}</a>
                        <template v-if="index !== ( actions.length - 1)"> | </template>
                    </span>
                    <span :class="action.key" v-if="action.key == 'trash' && currentStatus !='trash'">
                        <a href="#" @click.prevent="rowAction( action.key, data )">{{ action.label }}</a>
                    </span>
                    <span :class="action.key" v-if="action.key == 'delete' && currentStatus == 'trash'">
                        <a href="#" @click.prevent="rowAction( action.key, data )">{{ action.label }}</a>
                        <template v-if="index !== ( actions.length - 1)"> | </template>
                    </span>
                    <span :class="action.key" v-if="action.key == 'restore' && currentStatus == 'trash'">
                        <a href="#" @click.prevent="rowAction( action.key, data )">{{ action.label }}</a>
                        <template v-if="index !== ( actions.length - 1)"> | </template>
                    </span>
                </template>
            </template>

        </list-table>

        <modal
            :title="modalTitle"
            v-if="showDialog"
            @close="showDialog = false"
            :footer="false"
        >
            <template slot="body">
                <div v-html="modalContent"></div>
            </template>
        </modal>

    </div>
</template>

<script>
    let ListTable = dokan_get_lib('ListTable');
    let Modal = dokan_get_lib('Modal');

    export default {
        name: 'Announcement',

        components: {
            ListTable,
            Modal
        },

        data() {
            return {
                requests: [],
                loading: false,

                status: {
                    'publish' : this.__( 'Published', 'dokan' ),
                    'pending' : this.__( 'Pending', 'dokan' ),
                    'draft' : this.__( 'Draft', 'dokan' ),
                    'trash' : this.__( 'Trash', 'dokan' )
                },

                counts: {
                    all: 0,
                    publish: 0,
                    draft: 0,
                    pending: 0,
                    trash: 0
                },
                notFound: this.__( 'No announcement found.', 'dokan' ),
                totalPages: 1,
                perPage: 10,
                totalItems: 0,

                showCb: true,

                columns: {
                    'title': { label: this.__( 'Title', 'dokan' ) },
                    'content': { label: this.__( 'Content', 'dokan' ) },
                    'send_to': { label: this.__( 'Sent To', 'dokan' ) },
                    'status': { label: this.__( 'Status', 'dokan' ) },
                    'created_at': { label: this.__( 'Created Date', 'dokan' ) },
                },

                actionColumn: 'title',
                actions: [
                    {
                        key: 'edit',
                        label: this.__( 'Edit', 'dokan' )
                    },
                    {
                        key: 'trash',
                        label: this.__( 'Trash', 'dokan' )
                    },
                    {
                        key: 'delete',
                        label: this.__( 'Permanent Delete', 'dokan' )
                    },
                    {
                        key: 'restore',
                        label: this.__( 'Restore', 'dokan' )
                    }
                ],
                showDialog: false,
                modalContent: '',
                modalTitle: ''
            }
        },

        watch: {
            '$route.query.status'() {
                this.fetchAll();
            },

            '$route.query.page'() {
                this.fetchAll();
            }
        },

        computed: {

            currentStatus() {
                return this.$route.query.status || 'all';
            },

            currentPage() {
                let page = this.$route.query.page || 1;
                return parseInt( page );
            },

            bulkActions() {
                if ( 'trash' == this.$route.query.status ) {
                    return [
                        {
                            key: 'delete',
                            label: this.__( 'Permanent Delete', 'dokan' )
                        },
                        {
                            key: 'restore',
                            label: this.__( 'Restore', 'dokan' )
                        }
                    ];
                } else {
                    return [
                        {
                            key: 'trash',
                            label: this.__( 'Move in Trash', 'dokan' )
                        }
                    ];
                }
            }
        },

        methods: {

            updatedCounts( xhr ) {
                this.counts.all     = parseInt( xhr.getResponseHeader('X-Status-All') );
                this.counts.publish = parseInt( xhr.getResponseHeader('X-Status-Publish') );
                this.counts.pending = parseInt( xhr.getResponseHeader('X-Status-Pending') );
                this.counts.draft   = parseInt( xhr.getResponseHeader('X-Status-Draft') );
                this.counts.trash   = parseInt( xhr.getResponseHeader('X-Status-Trash') );
            },

            updatePagination(xhr) {
                this.totalPages = parseInt( xhr.getResponseHeader('X-WP-TotalPages') );
                this.totalItems = parseInt( xhr.getResponseHeader('X-WP-Total') );
            },

            fetchAll() {
                this.loading = true;

                dokan.api.get('/announcement?per_page=' + this.perPage + '&page=' + this.currentPage + '&status=' + this.currentStatus)
                .done((response, status, xhr) => {
                    this.requests = response;
                    this.loading = false;

                    this.updatedCounts( xhr );
                    this.updatePagination( xhr );
                });
            },

            showContent( row ) {
                this.modalTitle = row.title;
                this.modalContent = row.content;
                this.showDialog = true;
            },

            moment(date) {
                return moment(date);
            },

            editUrl(id) {
                return dokan.urls.adminRoot + 'admin.php?page=dokan#/announcement/' + id + '/edit';
            },

            goToPage(page) {
                this.$router.push({
                    name: 'Announcement',
                    query: {
                        status: this.currentStatus,
                        page: page
                    }
                });
            },

            onActionClick(action, row) {
            },

            rowAction( action, data ) {
                if ( ! data.row.id ) {
                    alert( this.__( 'No data found', 'dokan' ) );
                    return;
                }

                if ( 'trash' === action || 'delete' === action ) {
                    this.loading = true;

                    var isPermanentDelete = ( 'delete' === action ) ? '?force=true' : '';

                    dokan.api.delete('/announcement/' + data.row.id + isPermanentDelete )
                    .done( ( response, status, xhr ) => {
                        this.fetchAll();
                        this.loading = false;
                    });
                }

                if ( 'restore' === action ) {
                    this.loading = true;
                    let jsonData = {};

                    dokan.api.put('/announcement/' + data.row.id + '/restore' )
                    .done( ( response, status, xhr ) => {
                        this.fetchAll();
                        this.loading = false;
                    })
                    .error( ( response, status, xhr ) => {
                        console.log( response );
                    });
                }
            },

            onBulkAction( action, items ) {
                if ( 'trash' === action ) {
                    this.loading = true;

                    let jsonData = {};
                    jsonData.trash = items;

                    dokan.api.put('/announcement/batch', jsonData )
                    .done( ( response, status, xhr ) => {
                        this.fetchAll();
                        this.loading = false;
                    });
                }

                if ( 'delete' === action ) {
                    this.loading = true;

                    let jsonData = {};
                    jsonData.delete = items;

                    dokan.api.put('/announcement/batch', jsonData )
                    .done( ( response, status, xhr ) => {
                        this.fetchAll();
                        this.loading = false;
                    });

                }

                if ( 'restore' === action ) {
                    this.loading = true;
                    let jsonData = {};
                    jsonData.restore = items;

                    dokan.api.put('/announcement/batch', jsonData )
                    .done( ( response, status, xhr ) => {
                        this.fetchAll();
                        this.loading = false;
                    });
                }

            }

        },

        created() {
            this.fetchAll();
        }

    };

</script>

<style lang="less">
    .dokan-announcement-wrapper {
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

        th.title {
            width: 35%;
        }

        td.status {
            span {
                line-height: 2.5em;
                padding: 5px 8px;
                border-radius: 4px;
            }

            .publish {
                background: #c6e1c6;
                color: #5b841b;
            }

            .pending {
                background: #f8dda7;
                color: #94660c
            }

            .trash {
                background: #eba3a3;
                color: #761919
            }

            .draft {
                background: #e5e5e5;
                color: #761919
            }
        }
    }

@media only screen and (max-width: 600px) {
    .dokan-announcement-wrapper {
        .help-block {
            top: 45px !important;
            left: 0 !important;
        }

        .subsubsub {
            margin-top: 20px;
        }

        table {
            td.title, td.content {
                display: table-cell !important;
            }

            th:not(.check-column):not(.title):not(.content):not(.send_to) {
                display: none;
            }

            td:not(.check-column):not(.title):not(.content):not(.send_to) {
                display: none;
            }

            th.column, td.column {
                width: auto;
            }

            td.manage-column.column-cb.check-column {
                padding-right: 15px;
            }
        }
    }
}
</style>
