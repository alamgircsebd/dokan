<template>
    <div class="dokan-store-reviews">
        <h1 class="wp-heading-inline">{{ __( 'Store reviews', 'dokan' ) }}</h1>

        <div class="help-block">
            <span class='help-text'><a href="https://wedevs.com/docs/dokan/modules/vendor-review/" target="_blank">{{ __( 'Need Any Help ?', 'dokan' ) }}</a></span>
            <span class="dashicons dashicons-smiley"></span>
        </div>

        <hr class="wp-header-end">

        <ul class="subsubsub">
            <li><router-link :to="{ name: 'Store Reviews' }" active-class="current" exact v-html="sprintf( __( 'All <span class=\'count\'>(%s)</span>', 'dokan' ), counts.all )"></router-link> | </li>
            <li><router-link :to="{ name: 'Store Reviews', query: { status: 'trash' }}" active-class="current" exact v-html="sprintf( __( 'Trash <span class=\'count\'>(%s)</span>', 'dokan' ), counts.trash )"></router-link></li>
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
                <strong><a href="#" @click.prevent="showEditForm( data.row )">{{ data.row.title }}</a></strong>
            </template>

            <template slot="content" slot-scope="data">
                {{ data.row.content }}
            </template>

            <template slot="created_at" slot-scope="data">
                {{ moment(data.row.created_at).format('MMM D, YYYY') }}
            </template>

            <template slot="customer" slot-scope="data">
                <a :href="editUserUrl(data.row.customer.id)">{{ data.row.customer.display_name }}</a>
            </template>

            <template slot="vendor" slot-scope="data">
                <a :href="editUserUrl(data.row.vendor.id)">{{ data.row.vendor.shop_name }}</a>
            </template>

            <template slot="rating" slot-scope="data">
                <star-rating :star-size="20" :read-only="true" :show-rating="false" :rating="data.row.rating"></star-rating>
            </template>

            <template slot="row-actions" slot-scope="data">
                <template v-for="(action, index) in actions">
                    <span :class="action.key" v-if="action.key == 'edit'">
                        <a href="#" @click.prevent="showEditForm( data.row )">{{ action.label }}</a>
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
            <template slot="filters">
                <select
                    id="filter-vendors"
                    style="width: 190px;"
                    :data-placeholder="__( 'Filter by vendor', 'dokan' )">
                    <option v-if="selectedVendor" :value="selectedVendor.id" selected="selected">{{ selectedVendor.store_name }}</option>
                </select>
            </template>
        </list-table>

        <modal
            :title="__( 'Edit Review', 'dokan' )"
            v-if="showDialog"
            @close="closeEditForm"
            :footer="false"
            width="600px"
        >
            <template slot="body">
                <form @submit.prevent="updateReview" class="dokan-edit-store-review-form">
                    <div class="input-form-group">
                        <label for="store-review-rating">{{ __( 'Rating', 'dokan' ) }}</label>
                        <star-rating :star-size="25" v-model="editReviewData.rating" :increment="1" :show-rating="false" :rating="editReviewData.rating"></star-rating>
                    </div>

                    <div class="input-form-group">
                        <label for="store-review-title">{{ __( 'Title', 'dokan' ) }}</label>
                        <input class="regular-text" type="text" id="store-review-title" v-model="editReviewData.title">
                    </div>

                    <div class="input-form-group">
                        <label for="store-review-content">{{ __( 'Content', 'dokan' ) }}</label>
                        <textarea id="store-review-content" class="regular-textarea" v-model="editReviewData.content"></textarea>
                    </div>

                    <div class="input-form-group">
                        <input type="submit" :value="__( 'Update Review', 'dokan' )" class="button button-primary">
                    </div>
                </form>
            </template>
        </modal>
    </div>
</template>

<script>
    import StarRating from 'vue-star-rating'
    import $ from 'jquery';

    let ListTable = dokan_get_lib('ListTable');
    let Modal = dokan_get_lib('Modal');

    export default {
        name: 'StoreReviews',

        components: {
            ListTable,
            Modal,
            StarRating
        },

        data() {
            return {
                requests: [],
                loading: false,
                editReviewData: {},
                status: {
                    'trash' : this.__( 'Trash', 'dokan' ),
                },
                counts: {
                    all: 0,
                    trash: 0,
                },
                notFound: this.__( 'No reviews found.', 'dokan' ),
                totalPages: 1,
                perPage: 10,
                totalItems: 0,
                showCb: true,
                columns: {
                    'title': { label: this.__( 'Title', 'dokan' ) },
                    'content': { label: this.__( 'Content', 'dokan' ) },
                    'customer': { label: this.__( 'Customer', 'dokan' ) },
                    'vendor': { label: this.__( 'Vendor', 'dokan' ) },
                    'rating': { label: this.__( 'Rating', 'dokan' ) },
                    'created_at': { label: this.__( 'Date', 'dokan' ) },
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
                modalTitle: '',
                filter: {
                    query: {}
                },
                selectedVendor: {}
            }
        },

        watch: {
            '$route.query.status'() {
                this.fetchAll();
            },

            '$route.query.page'() {
                this.fetchAll();
            },
            '$route.query.vendor_id'() {
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
                this.counts.trash   = parseInt( xhr.getResponseHeader('X-Status-Trash') );
            },

            updatePagination(xhr) {
                this.totalPages = parseInt( xhr.getResponseHeader('X-WP-TotalPages') );
                this.totalItems = parseInt( xhr.getResponseHeader('X-WP-Total') );
            },

            fetchAll() {
                this.loading = true;

                var vendorQuery = this.$route.query.vendor_id !== undefined ? '&vendor_id=' + this.$route.query.vendor_id : '';

                dokan.api.get('/store-reviews?per_page=' + this.perPage + '&page=' + this.currentPage + '&status=' + this.currentStatus + vendorQuery )
                .done((response, status, xhr) => {
                    this.requests = response;
                    this.loading = false;

                    this.updatedCounts( xhr );
                    this.updatePagination( xhr );
                });
            },

            editUserUrl( userID ) {
                return dokan.urls.adminRoot + 'user-edit.php?user_id=' + userID;
            },

            moment(date) {
                return moment(date);
            },

            goToPage(page) {
                this.$router.push({
                    name: 'StoreReviews',
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

                    dokan.api.delete('/store-reviews/' + data.row.id + isPermanentDelete )
                    .done( ( response, status, xhr ) => {
                        this.fetchAll();
                    });
                }

                if ( 'restore' === action ) {
                    this.loading = true;
                    let jsonData = {};

                    dokan.api.put('/store-reviews/' + data.row.id + '/restore' )
                    .done( ( response, status, xhr ) => {
                        this.fetchAll();
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

                    dokan.api.put('/store-reviews/batch', jsonData )
                    .done( ( response, status, xhr ) => {
                        this.fetchAll();
                    });
                }

                if ( 'delete' === action ) {
                    this.loading = true;

                    let jsonData = {};
                    jsonData.delete = items;

                    dokan.api.put('/store-reviews/batch', jsonData )
                    .done( ( response, status, xhr ) => {
                        this.fetchAll();
                    });

                }

                if ( 'restore' === action ) {
                    this.loading = true;
                    let jsonData = {};
                    jsonData.restore = items;

                    dokan.api.put('/store-reviews/batch', jsonData )
                    .done( ( response, status, xhr ) => {
                        this.fetchAll();
                    });
                }
            },

            showEditForm( data ) {
                this.editReviewData = jQuery.extend( {}, data );
                this.showDialog = true;
            },

            closeEditForm() {
                this.editReviewData = {};
                this.showDialog = false;
            },

            updateReview() {
                var formElm = jQuery('form.dokan-edit-store-review-form');

                formElm.block({ message: null, overlayCSS: { background: '#fff url(' + dokan.ajax_loader + ') no-repeat center', opacity: 0.6 } });
                dokan.api.put('/store-reviews/' + this.editReviewData.id, this.editReviewData )
                    .done( ( response, status, xhr ) => {
                        this.loading = true;
                        this.fetchAll();
                        formElm.unblock();
                        this.closeEditForm();
                    });
            },

            clearSelection( element ) {
                $( element ).val( null ).trigger( 'change' );
            },

            async prepareLogsFilter() {
                await this.$nextTick();

                $( '#filter-vendors' ).selectWoo( {
                    allowClear: true,
                    ajax: {
                        url: `${dokan.rest.root}dokan/v1/stores`,
                        delay: 500,
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
                                results: data.map((store) => {
                                    return {
                                        id: store.id,
                                        text: store.store_name != '' ? store.store_name : '(No name)'
                                    };
                                })
                            };
                        }
                    }
                } );

                $( '#filter-vendors').on( 'select2:select', (e) => {
                    this.filter.query.vendor_id = e.params.data.id;
                    this.setRoute( this.filter.query );
                } );

                $( '#filter-vendors').on( 'select2:unselect', (e) => {
                    delete this.filter.query.vendor_id;
                    this.clearSelection( '#filter-vendors');
                    this.setRoute(this.filter.query);
                } );
            },
            setRoute( query ) {
                this.$router.push( {
                    name: 'Store Reviews',
                    query: query
                } );
            },

            fetchVendorByUrlQuery() {
                var vendorID = this.$route.query.vendor_id !== undefined ? this.$route.query.vendor_id : '';

                if ( '' !== vendorID ) {
                    dokan.api.get('/stores/' + vendorID )
                        .done((response, status, xhr) => {
                            this.selectedVendor = response;
                            this.prepareLogsFilter();
                        });

                }
            }

        },


        created() {
            this.fetchAll();
            this.fetchVendorByUrlQuery();
        },

        mounted() {
            this.prepareLogsFilter();
        },
    };
</script>

<style lang="less">
    .dokan-store-reviews {
        position: relative;

        .select2-container--default {
            height: 30px;
        }

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
            width: 18%;
        }

        th.content {
            width: 29%;
        }

        th.customer {
            width: 14%;
        }

        th.vendor {
            width: 14%;
        }

        th.rating {
            width: 12%;
        }

        th.created_at {
            width: 12%;
        }
    }

    form.dokan-edit-store-review-form {
        div.input-form-group {
            margin-bottom: 10px;

            label {
                display: block;
                margin-bottom: 5px;
                font-weight: bold;
            }

            input,textarea {
                width: 100%;
            }

            textarea {
                height: 150px;
                padding:5px;
            }
        }
    }

    @media only screen and (max-width: 600px) {
        .dokan-store-reviews {
            .select2-container--default {
                height: 30px;
            }
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

                th:not(.check-column):not(.title):not(.content):not(.customer) {
                    display: none;
                }

                td:not(.check-column):not(.title):not(.content):not(.customer) {
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
