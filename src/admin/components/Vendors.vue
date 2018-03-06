<template>
    <div class="home">
        <h1 class="wp-heading-inline">Vendors</h1>
        <a href="#" class="page-title-action">Add New</a>

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

            <template slot="enabled" slot-scope="data">
                <switches :enabled="data.row.enabled" :value="data.row.id" @input="onSwitch"></switches>
            </template>
        </list-table>
    </div>
</template>

<script>
import ListTable from 'vue-wp-list-table';
import Switches from '@/components/Switches.vue';
import API from '@/utils/Api';
import 'vue-wp-list-table/dist/vue-wp-list-table.css';

export default {

    name: 'Home',

    components: {
        ListTable,
        Switches
    },

    data () {
        return {
            showCb: true,

            sortBy: 'title',
            sortOrder: 'asc',

            totalItems: 0,
            perPage: 10,
            totalPages: 1,
            currentPage: 1,
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
                    key: 'trash',
                    label: 'Move to Trash'
                }
            ],
            vendors: []
        }
    },

    created() {

        this.fetchVendors();
    },

    methods: {

        fetchVendors(time = 100) {

            let self = this;

            self.loading = true;

            dokan.api.get('/stores?status=all')
            .done((response, status, xhr) => {
                console.log(response, status, xhr);
                self.vendors = response;
                self.totalItems = parseInt( xhr.getResponseHeader('X-WP-Total') );
                self.totalPages = parseInt( xhr.getResponseHeader('X-WP-TotalPages') );
                self.loading = false;
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
            console.log(status, vendor_id);

            let message = ( status === false ) ? 'The vendor has been disabled.' : 'Selling has been enabled';

            this.$notify({
                title: 'Success!',
                type: 'success',
                text: message,
            });
        },

        goToPage(page) {
            console.log('Going to page: ' + page);
            this.currentPage = page;
            this.fetchVendors(1000);
        },

        onBulkAction(action, items) {
            console.log(action, items);
            alert(action + ': ' + items.join(', ') );
        },

        sortCallback(column, order) {
            this.sortBy = column;
            this.sortOrder = order;

            this.fetchVendors(1000);
        }
    }
}
</script>

<style lang="less">
.home {
    h1 {
        margin-bottom: 15px;
    }

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
