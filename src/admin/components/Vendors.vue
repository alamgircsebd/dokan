<template>
    <div class="vendor-list">
        <h1 class="wp-heading-inline">Vendors</h1>
        <!-- <a href="#" class="page-title-action">Add New</a> -->
        <hr class="wp-header-end">

        <ul class="subsubsub">
            <li><router-link :to="{ name: 'Vendors', query: { status: 'all' }}" active-class="current" exact>All <span class="count">({{ counts.all }})</span></router-link> | </li>
            <li><router-link :to="{ name: 'Vendors', query: { status: 'approved' }}" active-class="current" exact>Approved <span class="count">({{ counts.approved }})</span></router-link> | </li>
            <li><router-link :to="{ name: 'Vendors', query: { status: 'pending' }}" active-class="current" exact>Pending <span class="count">({{ counts.pending }})</span></router-link></li>
        </ul>

        <list-table
            :columns="columns"
            :loading="loading"
            :rows="vendors"
            :actions="actions"
            :show-cb="showCb"
            :total-items="totalItems"
            :bulk-actions="bulkActions"
            :total-pages="totalPages"
            :per-page="perPage"
            :current-page="currentPage"
            :action-column="actionColumn"

            not-found="No vendors found."

            :sort-by="sortBy"
            :sort-order="sortOrder"
            @sort="sortCallback"

            @pagination="goToPage"
            @action:click="onActionClick"
            @bulk:click="onBulkAction"
        >
            <template slot="store_name" slot-scope="data">
                <img :src="data.row.gravatar" :alt="data.row.store_name" width="50">
                <strong><router-link :to="'/vendors/' + data.row.id">{{ data.row.store_name }}</router-link></strong>
            </template>

            <template slot="email" slot-scope="data">
                <a :href="'mailto:' + data.row.email">{{ data.row.email }}</a>
            </template>

            <template slot="registered" slot-scope="data">
                {{ moment(data.row.registered).format('MMM D, YYYY') }}
            </template>

            <template slot="enabled" slot-scope="data">
                <switches :enabled="data.row.enabled" :value="data.row.id" @input="onSwitch"></switches>
            </template>
        </list-table>
    </div>
</template>

<script>
let ListTable = dokan_get_lib('ListTable');
let Switches  = dokan_get_lib('Switches');

export default {

    name: 'Vendors',

    components: {
        ListTable,
        Switches
    },

    data () {
        return {
            showCb: true,

            counts: {
                pending: 0,
                approved: 0,
                all: 0
            },

            totalItems: 0,
            perPage: 20,
            totalPages: 1,
            loading: false,

            columns: {
                'store_name': {
                    label: 'Store',
                    sortable: true
                },
                'email': {
                    label: 'E-mail'
                },
                'phone': {
                    label: 'Phone'
                },
                'registered': {
                    label: 'Registered',
                    sortable: true
                },
                'enabled': {
                    label: 'Status'
                }
            },
            actionColumn: 'title',
            actions: [
                {
                    key: 'edit',
                    label: 'Edit'
                },
                {
                    key: 'trash',
                    label: 'Delete'
                }
            ],
            bulkActions: [
                {
                    key: 'approved',
                    label: 'Approve Vendors'
                },
                {
                    key: 'pending',
                    label: 'Disable Selling'
                }
            ],
            vendors: []
        }
    },

    watch: {
        '$route.query.status'() {
            this.fetchVendors();
        },

        '$route.query.page'() {
            this.fetchVendors();
        },

        '$route.query.orderby'() {
            this.fetchVendors();
        },

        '$route.query.order'() {
            this.fetchVendors();
        },
    },

    computed: {

        currentStatus() {
            return this.$route.query.status || 'all';
        },

        currentPage() {
            let page = this.$route.query.page || 1;

            return parseInt( page );
        },

        sortBy() {
            return this.$route.query.orderby || 'registered';
        },

        sortOrder() {
            return this.$route.query.order || 'desc';
        }
    },

    created() {

        this.fetchVendors();
    },

    methods: {

        updatedCounts(xhr) {
            this.counts.pending  = parseInt( xhr.getResponseHeader('X-Status-Pending') );
            this.counts.approved = parseInt( xhr.getResponseHeader('X-Status-Approved') );
            this.counts.all      = parseInt( xhr.getResponseHeader('X-Status-All') );
        },

        updatePagination(xhr) {
            this.totalPages = parseInt( xhr.getResponseHeader('X-WP-TotalPages') );
            this.totalItems = parseInt( xhr.getResponseHeader('X-WP-Total') );
        },

        fetchVendors() {

            let self = this;

            self.loading = true;

            // dokan.api.get('/stores?per_page=' + this.perPage + '&page=' + this.currentPage + '&status=' + this.currentStatus)
            dokan.api.get('/stores', {
                per_page: this.perPage,
                page: this.currentPage,
                status: this.currentStatus,
                orderby: this.sortBy,
                order: this.sortOrder
            })
            .done((response, status, xhr) => {
                // console.log(response, status, xhr);
                self.vendors = response;
                self.loading = false;

                this.updatedCounts(xhr);
                this.updatePagination(xhr);
            });
        },

        onActionClick(action, row) {
            if ( 'trash' === action ) {
                if ( confirm('Are you sure to delete?') ) {
                    alert('deleted: ' + row.title);
                }
            }
        },

        onSwitch(status, vendor_id) {

            let message = ( status === false ) ? 'The vendor has been disabled.' : 'Selling has been enabled';

            dokan.api.put('/stores/' + vendor_id + '/status', {
                status: ( status === false ) ? 'inactive' : 'active'
            })
            .done(response => {
                this.$notify({
                    title: 'Success!',
                    type: 'success',
                    text: message,
                });

                if (this.currentStatus !== 'all' ) {
                    this.fetchVendors();
                }
            });
        },

        moment(date) {
            return moment(date);
        },

        goToPage(page) {
            this.$router.push({
                name: 'Vendors',
                query: {
                    status: this.currentStatus,
                    page: page
                }
            });
        },

        onBulkAction(action, items) {
            let jsonData = {};
            jsonData[action] = items;

            this.loading = true;

            dokan.api.put('/stores/batch', jsonData)
            .done(response => {
                this.loading = false;
                this.fetchVendors();
            });
        },

        sortCallback(column, order) {
            this.$router.push({
                name: 'Vendors',
                query: {
                    status: this.currentStatus,
                    page: 1,
                    orderby: column,
                    order: order
                }
            });
        }
    }
}
</script>

<style lang="less">
.vendor-list {

    .image {
        width: 10%;
    }

    .store_name {
        width: 30%;
    }

    td.store_name img {
        float: left;
        margin-right: 10px;
        margin-top: 1px;
        width: 24px;
        height: auto;
    }

    td.store_name strong {
        display: block;
        margin-bottom: .2em;
        font-size: 14px;
    }

    .vue-notification {
        // &.success {
        //     background: #68CD86;
        //     border-left-color: #42A85F;
        // }
    }
}
</style>
