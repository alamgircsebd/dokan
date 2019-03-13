<template>
    <div class="dokan-refund-wrapper">
        <h1 class="wp-heading-inline">{{ __( 'Refund Requests', 'dokan' ) }}</h1>

        <div class="help-block">
            <span class='help-text'><a href="https://wedevs.com/docs/dokan/refund/" target="_blank">{{ __( 'Need Any Help ?', 'dokan' ) }}</a></span>
            <span class="dashicons dashicons-smiley"></span>
        </div>

        <hr class="wp-header-end">

        <ul class="subsubsub">
            <li><router-link :to="{ name: 'Refund', query: { status: 'pending' }}" active-class="current" exact v-html="sprintf( __( 'Pending <span class=\'count\'>(%s)</span>', 'dokan-lite' ), counts.pending )"></router-link> | </li>
            <li><router-link :to="{ name: 'Refund', query: { status: 'approved' }}" active-class="current" exact v-html="sprintf( __( 'Approved <span class=\'count\'>(%s)</span>', 'dokan-lite' ), counts.approved )"></router-link> | </li>
            <li><router-link :to="{ name: 'Refund', query: { status: 'cancelled' }}" active-class="current" exact v-html="sprintf( __( 'Cancelled <span class=\'count\'>(%s)</span>', 'dokan-lite' ), counts.cancelled )"></router-link></li>
        </ul>

        <search title="Search Refund" @searched="doSearch"></search>

        <list-table
            :columns="columns"
            :rows="requests"
            :loading="loading"
            :action-column="actionColumn"
            :actions="actions"
            :show-cb="true"
            :bulk-actions="bulkActions"
            :not-found="notFound"
            :total-pages="totalPages"
            :total-items="totalItems"
            :per-page="perPage"
            :current-page="currentPage"
            @pagination="goToPage"
            @action:click="onActionClick"
            @bulk:click="onBulkAction"
            @searched="doSearch"
        >

            <template slot="order_id" slot-scope="data">
                <a :href="orderUrl(data.row.order_id)"><strong>#{{ data.row.order_id }}</strong></a>
            </template>

            <template slot="amount" slot-scope="data">
                <currency :amount="data.row.amount"></currency>
            </template>

            <template slot="vendor" slot-scope="data">
                <a :href="vendorUrl(data.row.vendor.id)" :title="data.row.vendor.email">{{ data.row.vendor.store_name }}</a>
            </template>

            <template slot="date" slot-scope="data">
                {{ moment(data.row.created).format('MMM D, YYYY') }}
            </template>

            <template slot="row-actions" slot-scope="data">
                <template v-for="(action, index) in actions">
                    <span :class="action.key" v-if="action.key == 'approved' && currentStatus == 'pending'">
                        <a href="#" @click.prevent="rowAction( action.key, data )">{{ action.label }}</a>
                        <template v-if="index !== ( actions.length - 1)"> | </template>
                    </span>

                    <span :class="action.key" v-if="action.key == 'cancelled' && currentStatus == 'pending'">
                        <a href="#" @click.prevent="rowAction( action.key, data )">{{ action.label }}</a>
                    </span>

                     <span :class="action.key" v-if="action.key == 'delete' && currentStatus == 'cancelled'">
                        <a href="#" @click.prevent="rowAction( action.key, data )">{{ action.label }}</a>
                    </span>
                </template>
            </template>
        </list-table>
    </div>
</template>

<script>
let ListTable = dokan_get_lib('ListTable');
let Currency  = dokan_get_lib('Currency');
let Search    = dokan_get_lib('Search');

export default {

    name: 'Refund',

    components: {
        ListTable,
        Currency,
        Search
    },

    data() {
        return {
            requests: [],
            loading: false,

            counts: {
                pending: 0,
                approved: 0,
                cancelled: 0
            },
            totalPages: 1,
            perPage: 10,
            totalItems: 0,
            notFound: this.__( 'No request found.', 'dokan' ),
            columns: {
                'order_id': { label: this.__( 'Order ID', 'dokan' ) },
                'vendor': { label: this.__( 'Vendor', 'dokan' ) },
                'amount': { label: this.__( 'Refund Amount', 'dokan' ) },
                'reason': { label: this.__( 'Refund Reason', 'dokan' ) },
                'method': { label: this.__( 'Payment Gateway', 'dokan' ) },
                'date': { label: this.__( 'Date', 'dokan' ) },
            },

            actionColumn: 'order_id',
            actions: [
                {
                    key: 'approved',
                    label: this.__( 'Approve Refund', 'dokan' )
                },
                {
                    key: 'cancelled',
                    label: this.__( 'Cancel', 'dokan' )
                },
                {
                    key: 'delete',
                    label: this.__( 'Delete', 'dokan' )
                }
            ],
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
            if ( 'pending' == this.$route.query.status ) {
                return [
                    {
                        key: 'approved',
                        label: this.__( 'Approve Refund', 'dokan' )
                    },
                    {
                        key: 'cancelled',
                        label: this.__( 'Cancel', 'dokan' )
                    }
                ];
            } else if ( 'cancelled' == this.$route.query.status ) {
                return [
                    {
                        key: 'delete',
                        label: this.__( 'Delete', 'dokan' )
                    }
                ];
            } else {
                return []
            }
        }
    },

    watch: {
        '$route.query.status'() {
            this.fetchRefunds();
        },

        '$route.query.page'() {
            this.fetchRefunds();
        }
    },

    methods: {
        doSearch(payload) {
            this.loading = true;

            dokan.api.get('/refund?per_page=' + this.perPage + '&page=' + this.currentPage + '&status=' + this.currentStatus)
            .done((response, status, xhr) => {
                this.requests = response.filter((refund) => {
                    return refund.order_id.includes(payload) || refund.vendor.store_name.includes(payload);
                });

                this.loading = false;
                this.updatedCounts( xhr );
                this.updatePagination( xhr );
            });
        },

        updatedCounts( xhr ) {
            this.counts.pending = parseInt( xhr.getResponseHeader('X-Status-Pending') );
            this.counts.approved   = parseInt( xhr.getResponseHeader('X-Status-Completed') );
            this.counts.cancelled   = parseInt( xhr.getResponseHeader('X-Status-Cancelled') );
        },

        updatePagination(xhr) {
            this.totalPages = parseInt( xhr.getResponseHeader('X-WP-TotalPages') );
            this.totalItems = parseInt( xhr.getResponseHeader('X-WP-Total') );
        },

        fetchRefunds() {
            this.loading = true;

            dokan.api.get('/refund?per_page=' + this.perPage + '&page=' + this.currentPage + '&status=' + this.currentStatus)
            .done((response, status, xhr) => {
                this.requests = response;
                this.loading = false;

                this.updatedCounts( xhr );
                this.updatePagination( xhr );
            });
        },

        moment(date) {
            return moment(date);
        },

        orderUrl(id) {
            return dokan.urls.adminRoot + 'post.php?post=' + id + '&action=edit';
        },

        vendorUrl(id) {
            return dokan.urls.adminRoot + 'admin.php?page=dokan#/vendors/'+ id;
        },

        goToPage(page) {
            this.$router.push({
                name: 'Refund',
                query: {
                    status: this.currentStatus,
                    page: page
                }
            });
        },

        onActionClick(action, row) {
            console.log( action, row );
        },

        rowAction( action, data ) {
            this.loading      = true;
            let jsonData      = {};
            jsonData.id       = data.row.id;
            jsonData.order_id = data.row.order_id;

            if ( 'approved' === action ) {
                jsonData.status   = 'approved';
                dokan.api.put('/refund/' + data.row.id, jsonData )
                .done( ( response, status, xhr ) => {
                    this.fetchRefunds();
                    this.loading = false;
                });
            } else if( 'cancelled' === action )  {
                jsonData.status   = 'cancelled';
                dokan.api.put('/refund/' + data.row.id, jsonData )
                .done( ( response, status, xhr ) => {
                    this.fetchRefunds();
                    this.loading = false;
                });
            } else if ( 'delete' === action ) {
                dokan.api.delete('/refund/' + data.row.id )
                .done( ( response, status, xhr ) => {
                    this.fetchRefunds();
                    this.loading = false;
                });
            }
        },

        onBulkAction( action, items ) {
            this.loading      = true;
            let jsonData      = {};
            jsonData[action]   = items;

            console.log( jsonData );

            dokan.api.put('/refund/batch', jsonData )
            .done( ( response, status, xhr ) => {
                this.fetchRefunds();
                this.loading = false;
            });
        }
    },

    created() {
        this.fetchRefunds();
    }

};

</script>

<style lang="less">

.dokan-refund-wrapper {
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

    table {
        thead {
            tr {
                th.order_id {
                    width: 17%;
                }
            }
        }
    }
}

@media only screen and (max-width: 600px) {
    .dokan-refund-wrapper {
        .help-block {
            top: 45px !important;
            left: 0 !important;
        }

        .subsubsub {
            margin-top: 20px;
        }

        table {
            td.order_id, td.vendor, td.amount {
                display: table-cell !important;
            }

            th:not(.check-column):not(.order_id):not(.vendor):not(.amount) {
                display: none;
            }

            td:not(.check-column):not(.order_id):not(.vendor):not(.amount) {
                display: none;
            }

            th.column, td.column {
                width: auto;
            }

            th.column.amount {
                width: 18% !important;
            }

            th.column.vendor {
                width: 20% !important;
            }

            th.column.order_id {
                width: 35% !important;
            }

            td.column.order_id {
                .row-actions {
                    font-size: 11px;
                }
            }
        }
    }
}

@media only screen and (max-width: 360px) {
    .dokan-refund-wrapper {
        table {
            td.column.order_id {
                .row-actions {
                    font-size: 10px;
                }
            }
        }
    }
}

@media only screen and (max-width: 320px) {
    .dokan-refund-wrapper {
        table {
            td.column.order_id {
                .row-actions {
                    font-size: 9px;
                }
            }
        }
    }
}
</style>
