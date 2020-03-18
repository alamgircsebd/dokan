<template>
    <div class="reports-page dokan-dashboard">
        <h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
            <router-link :to="{ name: 'Reports', query: { tab: 'report', type: 'by-day'}}" :class="['nav-tab', {'nav-tab-active': $route.path === '/reports' && $route.query.tab !== 'logs' }]">
                {{ __( 'Reports', 'dokan' ) }}
            </router-link>
            <router-link :to="{ name: 'Reports', query: { tab: 'logs' }}" :class="['nav-tab', {'nav-tab-active': $route.query.tab === 'logs'} ]">
                {{ __( 'All Logs', 'dokan' ) }}
            </router-link>
        </h2>

        <div class="export-area" v-if="showLogsAarea">
            <button @click="exportLogs()" id="export-logs" class="button">{{ __('Export Logs', 'dokan') }}</button>
        </div>

        <div class="report-area" v-if="showReportArea">
            <ul class="subsubsub dokan-report-sub" style="float: none;">
                <li>
                    <router-link :to="{ name: 'Reports', query: { tab: 'report', type: 'by-day' }}" active-class="current" exact>
                        {{ __( 'By Day', 'dokan' ) }} |
                    </router-link>
                <li>
                    <router-link :to="{ name: 'Reports', query: { tab: 'report', type: 'by-year' }}" active-class="current" exact>
                        {{ __( 'By Year', 'dokan' ) }} |
                    </router-link>
                </li>
                <li>
                    <router-link :to="{ name: 'Reports', query: { tab: 'report', type: 'by-vendor' }}" active-class="current" exact>
                        {{ __( 'By Vendor', 'dokan' ) }}
                    </router-link>
                </li>
            </ul>

            <form v-if="showDatePicker" @submit.prevent="showReport" class="form-inline report-filter">
                <span v-if="showStorePicker">
                    <span class="form-group">
                        <label for="vendor">{{ __( 'Store Name', 'dokan' ) }} :</label>
                    </span>

                    <multiselect class="vendor-picker" @search-change="searchStore" :loading="isLoading" :options="getStoreList" v-model="getStore" :placeholder="__( 'Search Store...', 'dokan' )" :showLabels="false" label="name" track-by="name"></multiselect>
                </span>

                <span class="form-group">
                    <label for="to">{{ __( 'From', 'dokan' ) }} :</label>
                    <datepicker class="dokan-input" :value="from_date" format="yy-mm-d" v-model="from_date"></datepicker>
                </span>

                <span class="form-group">
                    <label for="to">{{ __( 'To', 'dokan' ) }} :</label>
                    <datepicker class="dokan-input" :value="from_date" format="yy-mm-d" v-model="to_date"></datepicker>

                    <button type="submit" class="button">{{ __( 'Show', 'dokan' ) }}</button>
                </span>
            </form>

            <form @submit.prevent="showByYear" v-if="showYearPicker" class="form-inline report-filter">
                <span class="form-group">
                    <label for="from">{{ __( 'Year', 'dokan' ) }}:</label>
                </span>

                <select v-model="selectedYear" class="dokan-input">
                    <option v-for="(year, key) in yearRange" :key="key" :value="year">{{ year }}</option>
                </select>

                <button type="submit" class="button">{{ __( 'Show', 'dokan' ) }}</button>
            </form>


            <div class="widgets-wrapper">
                <div class="left-side">
                    <postbox :title="__( 'At a Glance', 'dokan' )" extraClass="dokan-status">
                        <div class="dokan-status" v-if="overview !== null">
                            <ul>
                                <li class="sale">
                                    <div class="dashicons dashicons-chart-bar"></div>
                                    <a href="#">
                                        <strong>
                                            <currency :amount="getSalesCount()"></currency>
                                        </strong>
                                        <div class="details">
                                            {{ getSalesDetails() }} <span :class="overview.sales.class">{{ overview.sales.parcent }}</span>
                                        </div>
                                    </a>
                                </li>
                                <li class="commission">
                                    <div class="dashicons dashicons-chart-pie"></div>
                                    <a href="#">
                                        <strong>
                                            <currency :amount="getEarningCount()"></currency>
                                        </strong>
                                        <div class="details">
                                            {{ getEarningDetails() }} <span :class="overview.earning.class">{{ overview.earning.parcent }}</span>
                                        </div>
                                    </a>
                                </li>
                                <li class="vendor" v-if="! showStorePicker">
                                    <div class="dashicons dashicons-id"></div>
                                    <a href="#">
                                        <strong>{{ sprintf( __( '%s Vendor', 'dokan' ), getVendorCount() ) }}</strong>
                                        <div class="details">
                                            {{ getVendorDetails() }} <span :class="overview.vendors.class">{{ overview.vendors.parcent }}</span>
                                        </div>
                                    </a>
                                </li>
                                <li class="order" v-if="showStorePicker">
                                    <div class="dashicons dashicons-id"></div>
                                    <a href="#">
                                        <strong>{{ sprintf( __( '%s Orders', 'dokan' ), getOrderCount() ) }}</strong>
                                        <div class="details">
                                            {{ __( 'Orders placed in this period', 'dokan' ) }} <span :class="overview.orders.class">{{ overview.orders.parcent }}</span>
                                        </div>
                                    </a>
                                </li>
                                <li class="approval" v-if="! showStorePicker">
                                    <div class="dashicons dashicons-businessman"></div>
                                    <a href="#">
                                        <strong>{{ sprintf( __( '%s Vendor', 'dokan' ), overview.vendors.inactive ) }}</strong>
                                        <div class="details">{{ __( 'awaiting approval', 'dokan' ) }}</div>
                                    </a>
                                </li>
                                <li class="product">
                                    <div class="dashicons dashicons-cart"></div>
                                    <a href="#">
                                        <strong>{{ sprintf( __( '%s Products', 'dokan' ), getProductCount() ) }}</strong>
                                        <div class="details">
                                            {{ getDetails() }} <span :class="overview.products.class">{{ overview.products.parcent }}</span>
                                        </div>
                                    </a>
                                </li>
                                <li class="withdraw" v-if="! showStorePicker">
                                    <div class="dashicons dashicons-money"></div>
                                    <a href="#">
                                        <strong>{{ sprintf( __( '%s Withdrawals', 'dokan' ), overview.withdraw.pending ) }}</strong>
                                        <div class="details">{{ __( 'awaiting approval', 'dokan' ) }}</div>
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="loading" v-else>
                            <loading></loading>
                        </div>
                    </postbox>
                </div>

                <div class="right-side">
                    <postbox :title="__( 'Overview', 'dokan' )" class="overview-chart">
                        <chart :data="report" v-if="report !== null"></chart>
                        <div class="loading" v-else>
                            <loading></loading>
                        </div>
                    </postbox>
                </div>
            </div>
        </div>

        <div class="logs-area" v-if="showLogsAarea">
            <list-table
                :columns="columns"
                :loading="loading"
                :rows="logs"
                :actions="actions"
                :bulk-actions="bulkActions"
                :show-cb="showCb"
                :total-items="totalItems"
                :total-pages="totalPages"
                :per-page="perPage"
                :current-page="currentPage"
                :not-found="noLogFound"
                @pagination="goToPage"
            >
            <template slot="filters">

                <select
                    id="filter-vendors"
                    style="width: 190px;"
                    :data-placeholder="__('Filter by vendor', 'dokan')">
                </select>

                <select
                    id="filter-status"
                    style="width: 190px;">
                </select>

                <div class="search-by-order">
                    <search @searched="searchByOrder" :title="__( 'Search by order', 'dokan' )" />
                </div>

            </template>

                <template slot="order_id" slot-scope="data">
                    <a target="_blank" :href="editOrderUrl(data.row.order_id)">#{{ data.row.order_id }}</a>
                </template>

                <template slot="vendor_id" slot-scope="data">
                    <a target="_blank" :href="editUserUrl(data.row.vendor_id)">{{ data.row.vendor_name ? data.row.vendor_name : __( '(no name)', 'dokan' ) }}</a>
                </template>

                <template slot="order_total" slot-scope="data">
                    <del v-if="data.row.has_refund">
                        <currency :amount="data.row.previous_order_total"></currency>
                    </del>
                    <currency :amount="data.row.order_total"></currency>
                </template>

                <template slot="vendor_earning" slot-scope="data">
                    <currency :amount="data.row.vendor_earning"></currency>
                </template>

                <template slot="commission" slot-scope="data">
                    <currency :amount="data.row.commission"></currency>
                </template>

                <template slot="date" slot-scope="data">
                    {{ moment(data.row.date).format('MMM D, YYYY') }}
                </template>

            </list-table>
        </div>

    </div>
</template>

<script>
let Chart       = dokan_get_lib('Chart');
let Postbox     = dokan_get_lib('Postbox');
let Loading     = dokan_get_lib('Loading');
let Currency    = dokan_get_lib('Currency');
let Datepicker  = dokan_get_lib('Datepicker');
let Multiselect = dokan_get_lib('Multiselect');
let ListTable   = dokan_get_lib('ListTable');
let debounce    = dokan_get_lib('debounce');
let Search      = dokan_get_lib('Search');

import $ from 'jquery';

export default {
    name: 'Reports',

    components: {
        Chart,
        Postbox,
        Loading,
        Currency,
        Datepicker,
        Multiselect,
        ListTable,
        Search
    },

    data() {
        return {
            from_date: this.getFromDate(),
            to_date: this.getToDate(),
            overview: null,
            report: null,
            showDatePicker: true,
            showYearPicker: false,
            showStorePicker: false,
            yearRange: this.getYearRange(),
            selectedYear: moment().format( 'Y' ),
            getStore: '',
            getStoreList: [],
            isLoading: false,
            showReportArea: true,
            showLogsAarea: false,

            // logs data
            showCb: false,
            counts: {
                all: 0
            },

            totalItems: 0,
            perPage: 20,
            totalPages: 1,
            loading: false,
            actions: [],
            bulkActions: [],
            noLogFound: this.__( 'No logs found.', 'dokan' ),
            order_statuses: [
                {
                    id: 0,
                    text: this.__( 'Filter by status', 'dokan' )
                },
                {
                    id: 1,
                    text: this.__( 'Processing', 'dokan' )
                },
                {
                    id: 2,
                    text: this.__( 'Completed', 'dokan' )
                },
                {
                    id: 3,
                    text: this.__( 'On-hold', 'dokan' )
                },
                {
                    id: 4,
                    text: this.__( 'Cancelled', 'dokan' )
                },
                {
                    id: 5,
                    text: this.__( 'Refunded', 'dokan' )
                },
                {
                    id: 6,
                    text: this.__( 'Failed', 'dokan' )
                },
                {
                    id: 7,
                    text: this.__( 'Pending Payment', 'dokan' )
                }
            ],
            filter: {
                query: {
                    tab: 'logs'
                }
            },
            columns: {
                'order_id': {
                    label: this.__( 'Order ID', 'dokan' ),
                },
                'vendor_id': {
                    label: this.__( 'Vendor', 'dokan' )
                },
                'order_total': {
                    label: this.__( 'Order Total', 'dokan' )
                },
                'vendor_earning': {
                    label: this.__( 'Vendor Earning', 'dokan' ),
                },
                'commission': {
                    label: this.__( 'Commission', 'dokan' )
                },
                'status': {
                    label: this.__( 'Status', 'dokan' )
                },
                'date' : {
                    label: this.__( 'Date', 'dokan' )
                }
            },
            logs: []
        }
    },

    created() {
        if ( this.$route.query.tab === 'logs' ) {
            this.fetchLogs();
            this.prepareLogArea()
        } else {
            this.fetchOverview();
            this.fetchReport();
        }

        if ( this.$route.query.type === 'by-year' ) {
            this.prepareYearView();
        } else if ( this.$route.query.type === 'by-vendor' ) {
            this.prepareVendorView();
        }
    },

    computed: {
        currentPage() {
            let page = this.$route.query.page || 1;

            return parseInt( page );
        },
    },

    watch: {
        '$route.query.type'() {
            this.report   = null;
            this.overview = null;

            if ( this.$route.query.type === 'by-year' ) {
                this.prepareYearView();
                this.showByYear();
            }

            if ( this.$route.query.type === 'by-vendor' ) {
                this.prepareVendorView();
            }

            if ( this.$route.query.type === 'by-day' ) {
                this.prepareDayView();
            }
        },

        '$route.query.page'() {
            this.fetchLogs();
        },

        '$route.query.tab'() {
            if ( this.$route.query.tab === 'logs' ) {
                this.prepareLogArea();
                this.fetchLogs();
                this.prepareLogsFilter();
            } else {
                this.prepareReportArea();
                this.fetchReport();
                this.fetchOverview();
            }
        },

        '$route.query'() {
            if ( this.$route.query.tab !== 'logs' ) {
                return;
            }

            if ( ! this.$route.query.order_status ) {
                delete this.filter.query.order_status;
                this.clearSelection( '#filter-status');
            }

            if ( ! this.$route.query.vendor_id ) {
                delete this.filter.query.vendor_id;
                this.clearSelection( '#filter-vendors')
            }

            this.fetchLogs();
        }
    },

    mounted() {
        this.prepareLogsFilter();
    },

    methods: {
        fetchOverview( from = null, to = null, seller_id = null ) {

            let url = '/admin/report/summary';

            if ( from !== null && to !== null && seller_id !== null ) {
                url = `/admin/report/summary?from=${from}&to=${to}&seller_id=${seller_id}`;
            } else if ( from !== null && to !== null ) {
                url = `/admin/report/summary?from=${from}&to=${to}`;
            }

            dokan.api.get(url)
            .done(response => {
                this.overview = response;
            });
        },

        fetchReport( from = null, to = null, seller_id = null ) {

            let url = '/admin/report/overview';

            if ( from !== null && to !== null && seller_id !== null ) {
                url = `/admin/report/overview?from=${from}&to=${to}&seller_id=${seller_id}`;
            } else if ( from !== null && to !== null ) {
                url = `/admin/report/overview?from=${from}&to=${to}`;
            }

            dokan.api.get(url)
            .done(response => {
                this.report = response;
            });
        },

        showReport() {
            this.report   = null;
            this.overview = null;

            this.fetchReport( this.from_date, this.to_date, this.getStore.id );
            this.fetchOverview( this.from_date, this.to_date, this.getStore.id );
        },

        showByYear() {
            this.report   = null;
            this.overview = null;

            const fromDate = moment( String( this.selectedYear ), 'Y' ).startOf( 'year' ).format( 'Y-M-D' );
            const toDate   = moment( String( this.selectedYear ), 'Y' ).endOf('year' ).format( 'Y-M-D' );

            this.fetchReport( fromDate, toDate );
            this.fetchOverview( fromDate, toDate );
        },

        getSalesCount() {
            return this.overview.sales.this_period !== null ? this.overview.sales.this_period : this.overview.sales.this_month;
        },

        getEarningCount() {
            return this.overview.earning.this_period !== null ? this.overview.earning.this_period : this.overview.earning.this_month;
        },

        getProductCount() {
            return this.overview.products.this_period !== null ? this.overview.products.this_period : this.overview.products.this_month;
        },

        getVendorCount() {
            return this.overview.vendors.this_period !== null ? this.overview.vendors.this_period : this.overview.vendors.this_month;
        },

        getOrderCount() {
            return this.overview.orders.this_period !== null ? this.overview.orders.this_period : this.overview.orders.this_month;
        },

        getDetails() {
            return this.overview.products.this_period !== null ? this.__( 'Created this period', 'dokan' ) : this.__( 'created this month', 'dokan' );
        },

        getVendorDetails() {
            return this.overview.vendors.this_period !== null ? this.__( 'Signup this period', 'dokan' ) : this.__( 'signup this month', 'dokan' );
        },

        getSalesDetails() {
            return this.overview.sales.this_period !== null ? this.__( 'Net sales this period', 'dokan' ) : this.__( 'Net sales this month', 'dokan' );
        },

        getEarningDetails() {
            return this.overview.earning.this_period !== null ? this.__( 'Commission earned this period', 'dokan' ) : this.__( 'Commission earned this month', 'dokan' );
        },

        getFromDate() {
            return moment().startOf( 'month' ).format( 'Y-M-D' );
        },

        getToDate() {
            return moment().endOf( 'month' ).format( 'Y-M-D' );
        },

        getYearRange() {
            const endYear   = Number( moment().add( 5, 'years' ).format( 'Y' ) );
            const startYear = Number( moment().subtract( 5, 'years' ).format( 'Y' ) );

            let yearRange = [];

            for ( let i = startYear; i <= endYear; i++ ) {
                yearRange.push(i);
            }

            return yearRange;
        },

        searchStore: debounce( function( payload ) {
            this.isLoading = true;

            if ( ! payload ) {
                return this.isLoading = false;
            }

            dokan.api.get(`/stores?search=${payload}`)
            .done( response => {
                this.getStoreList = response.map((store) => {
                    return { id: store.id, name: store.store_name };
                } )

                this.isLoading = false;
            } );

        }, 300 ),

        prepareVendorView() {
            this.showDatePicker  = true;
            this.showYearPicker  = false;
            this.showStorePicker = true;
        },

        prepareYearView() {
            this.showDatePicker  = false;
            this.showYearPicker  = true;
            this.showStorePicker = false;
        },

        prepareDayView() {
            this.showDatePicker  = true;
            this.showYearPicker  = false;
            this.showStorePicker = false;
        },

        prepareLogArea() {
            this.showLogsAarea  = true;
            this.showReportArea = false;
        },

        prepareReportArea() {
            this.showLogsAarea  = false;
            this.showReportArea = true;
        },

        // all logs methods
        fetchLogs() {

            this.loading = true;

            dokan.api.get('/admin/logs',{
                per_page: this.perPage,
                page: this.currentPage,
                vendor_id: this.$route.query.vendor_id || 0,
                order_status: this.$route.query.order_status || '',
                order_id: this.$route.query.order_id || 0,
            })
            .done((response, status, xhr) => {
                if ( 'success' in response && response.success === false ) {
                    this.logs = [];
                    this.loading = false;
                    this.updatePagination(xhr);
                    return;
                }

                this.logs = response;
                this.loading = false;

                this.updatePagination(xhr);
            })
        },

        updatePagination(xhr) {
            this.totalPages = parseInt( xhr.getResponseHeader('X-WP-TotalPages') );
            this.totalItems = parseInt( xhr.getResponseHeader('X-WP-Total') );
        },

        goToPage(page) {
            this.filter.query.page = page;
            this.setRoute( this.filter.query );
        },

        editOrderUrl(id) {
            return `${dokan.urls.adminRoot}post.php?action=edit&post=${id}`;
        },

        editUserUrl(id) {
            return `${dokan.urls.adminRoot}user-edit.php?user_id=${id}`;
        },

        exportLogs() {
            let csv = this.convertToCSV( this.logs );

            this.exportCSVFile(csv);
        },

        convertToCSV(data) {
            let array = typeof data != 'object' ? JSON.parse(data) : data;
            let str = '';

            str += '"Order ID", "Vendor ID", "Vendor Name", "Order Total", "Refund Total", "Vendor Earning", "Commission", "Status", "Date"';
            str += '\r\n';

            for (let i = 0; i < array.length; i++) {
                let line = '';

                for (let index in array[i]) {
                    if (line != '') line += ',';

                    if ( 'commission' == index || 'previous_order_total' == index || 'vendor_earning' == index ) {
                        line += '"' + accounting.formatMoney(
                            array[i][index],
                            '',
                            dokan.precision,
                            dokan.currency.thousand,
                            dokan.currency.decimal,
                            dokan.currency.format
                        ) + '"';
                    } else if ( 'has_refund' == index ) {
                        line += '';
                    } else if ( 'order_total' == index ) {

                        // if there is refund for an order, calculate refund total
                        let total_refund = 0;

                        if ( array[i]['has_refund'] ) {
                            total_refund = array[i]['previous_order_total'] - array[i]['order_total'];
                        }

                        line += '"' + accounting.formatMoney(
                            total_refund,
                            '',
                            dokan.precision,
                            dokan.currency.thousand,
                            dokan.currency.decimal,
                            dokan.currency.format
                        ) + '"';
                    } else {
                        line += '"' + array[i][index] +'"';
                    }
                }

                str += line + '\r\n';
            }

            return str;
        },

        exportCSVFile(csv) {
            let exportedFilenmae = 'logs-' + moment().format('Y-MM-DD') + '.csv';
            let blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });

            if (navigator.msSaveBlob) { // IE 10+
                navigator.msSaveBlob(blob, exportedFilenmae);
            } else {
                var link = document.createElement("a");
                if (link.download !== undefined) { // feature detection
                    // Browsers that support HTML5 download attribute
                    var url = URL.createObjectURL(blob);
                    link.setAttribute("href", url);
                    link.setAttribute("download", exportedFilenmae);
                    link.style.visibility = 'hidden';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }
            }
        },

        moment(date) {
            return moment(date);
        },

        setRoute( query ) {
            this.$router.push( {
                name: 'Reports',
                query: query
            } );
        },

        searchByOrder( payload ) {
            if ( ! payload ) {
                delete this.filter.query.order_id;
                this.setRoute( this.filter.query );
            }

            let order_id = Number.parseInt( payload );

            if ( Number.isNaN( order_id ) ) {
                return;
            }

            if ( typeof order_id !== 'number' ) {
                return;
            }

            // on search by order id, reset the entire query
            this.filter.query = {};
            this.filter.query.tab = 'logs';
            this.filter.query.order_id = order_id

            this.setRoute( this.filter.query );
        },

        clearSelection( element ) {
            $( element ).val( null ).trigger( 'change' );
        },

        async prepareLogsFilter() {
            let self = this;
            await this.$nextTick();

            $( '#filter-vendors' ).selectWoo( {
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
                                    text: store.store_name ? store.store_name : sprintf( '(%1$s) #%2$d', self.__( 'no name', 'dokan' ), store.id )
                                };
                            })
                        };
                    }
                }
            } );

            $( '#filter-vendors').on( 'select2:select', (e) => {
                // on search by vendor, reset the page query
                if ( this.filter.query.page ) {
                    delete this.filter.query.page;
                }

                // on search by vendor, reset the order_id query
                if ( this.filter.query.order_id ) {
                    delete this.filter.query.order_id;
                }

                this.filter.query.vendor_id = e.params.data.id;
                this.setRoute( this.filter.query );
            } );

            $( '#filter-status' ).selectWoo( {
                data: this.order_statuses,
            } );

            $( '#filter-status' ).on('select2:select', (e) => {
                let status = e.params.data.text.toLowerCase();

                // on order status change, reset the page query
                if ( this.filter.query.page ) {
                    delete this.filter.query.page;
                }

                if ( e.params.data.id == 0 ) {
                    delete this.filter.query.order_status;
                    return this.setRoute( this.filter.query );
                }

                this.filter.query.order_status = 'wc-' + status;
                this.setRoute( this.filter.query );
            });
        }
    }
};
</script>

<style lang="less">
.reports-page {
    .logs-area {
        .order_total {
            display: flex;

            del {
                padding-right: 5px;
            }
        }

        .tablenav {
            .actions {
                overflow: visible;
            }
        }

        .search-by-order {
            display: inline;
            margin-left: 5px;

            .search-box #post-search-input {
                border-radius: 3px;
                border: 1px solid #aaaaaa;
                padding-top: 0 !important;
                padding-bottom: 0 !important;
                padding-left: 8px !important;
            }

            .search-box #post-search-input::placeholder {
                color: #999 !important;
            }
        }

    }

    .export-area {
        position: absolute;
        right: 19px;
        top: 19px;

        #export-logs {
            padding: 0px 10px !important;
            height: 30px !important;
        }
    }

    .widgets-wrapper {
        display: block;
        overflow: hidden;
        margin-top: 15px;
        width: 100%;

        .left-side {
            width: 30%;
        }

        .right-side {
            width: 67%;
        }

        .left-side,
        .right-side {
            float: left;
        }

        .left-side {
            margin-right: 3%;
        }

        .dokan-status ul li {
            min-height: 80px;
        }
    }

    .vendor-picker {
        width: 200px;
        display: inline-block;
        top: -2px;
    }

    .dokan-input {
        height: 38px;
    }
    .dokan-postbox {
        .loading {
            display: block;
            width: 100%;
            margin: 15px auto;
            text-align: center;
        }
    }
    button.button {
        height: 38px !important;
        padding: 5px 20px 5px 20px !important;
    }
    .subscribe-box {
        margin: 20px -12px -11px -12px;
        padding: 0 15px 15px;
        background: #fafafa;
        border-top: 1px solid #efefef;
        position: relative;

        h3 {
            margin: 10px 0;
        }

        p {
            margin-bottom: 10px !important;
        }

        .thank-you {
            background: #4fa72b;
            margin-top: 10px;
            padding: 15px;
            border-radius: 3px;
            color: #fff;
        }

        .form-wrap {
            display: flex;

            input[type="email"] {
                width: 100%;
                padding: 3px 0 3px 6px;
                margin: 0px -1px 0 0;
            }

            button.button {
                box-shadow: none;
                background: #FF5722;
                color: #fff;
                border-color: #FF5722;
                border-top-left-radius: 0;
                border-bottom-left-radius: 0;

                &:hover {
                    background: lighten(#FF5722, 5%);
                }
            }
        }

        .loading {
            position: absolute;
            height: 100%;
            margin: 0 0 0 -15px;
            background: rgba(0,0,0, 0.2);

            .dokan-loader {
                margin-top: 30px;
            }
        }
    }

    .report-area {
        .report-filter {
            .multiselect__input {
                border: none;
                box-shadow: none;
                &:focus {
                    border: none;
                    box-shadow: none;
                    outline: none;
                }
            }

        }
    }
}

</style>
