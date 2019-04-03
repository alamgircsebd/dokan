<template>
    <div>
        <h1 class="wp-heading-inline">{{ __( 'Abuse Reports', 'dokan' ) }}</h1>
        <hr class="wp-header-end">

        <list-table
            :columns="columns"
            :loading="loading"
            :rows="reports"
            :actions="actions"
            :bulk-actions="bulkActions"
            :show-cb="false"
            :total-items="totalItems"
            :total-pages="totalPages"
            :per-page="perPage"
            :current-page="currentPage"
            @pagination="goToPage"
        >
            <template slot="reason" slot-scope="{ row }">
                <strong>
                    <a href="#view-report" @click.prevent="showReport(row)">{{ row.reason }}</a>
                </strong>
            </template>

            <template slot="product" slot-scope="{ row }">
                <a :href="row.product.admin_url">{{ row.product.title }}</a>
            </template>

            <template slot="vendor" slot-scope="{ row }">
                <router-link :to="'/vendors/' + row.vendor.id">
                    {{ row.vendor.name ? row.vendor.name : __('(no name)', 'dokan') }}
                </router-link>
            </template>

            <template slot="reported_by" slot-scope="{ row }">
                <a
                    v-if="row.reported_by.admin_url"
                    :href="row.reported_by.admin_url"
                    v-text="row.reported_by.name"
                    target="_blank"
                />
                <template v-else>
                    {{ row.reported_by.name }} &lt;{{ row.reported_by.email }}&gt;
                </template>
            </template>

            <template slot="reported_at" slot-scope="{ row }">
                {{ moment(row.reported_at).format('MMM D, YYYY h:mm:ss a') }}
            </template>

            <template slot="filters">
                <abuse-reasons-dropdown v-model="filter.reason" :placeholder="__('Filter by abuse reason', 'dokan')" />
                <button
                    v-if="filter.reason"
                    type="button"
                    class="button"
                    @click="filter.reason = ''"
                >&times;</button>
            </template>
        </list-table>

        <modal
            :title="__('Product Abuse Report', 'dokan')"
            v-if="showModal"
            :footer="false"
            @close="hideReport"
        >
            <template slot="body">
                <p style="margin-top: 0;"><strong>{{ __('Reported Product', 'dokan') }}:</strong> <a :href="report.product.admin_url">{{ report.product.title }}</a></p>
                <p><strong>{{ __('Reason', 'dokan') }}:</strong> {{ report.reason }}</p>
                <p><strong>{{ __('Description', 'dokan') }}:</strong> {{ report.description || '―' }}</p>
                <p>
                    <strong>{{ __('Reported by', 'dokan') }}:</strong>
                    <a
                        v-if="report.reported_by.admin_url"
                        :href="report.reported_by.admin_url"
                        v-text="report.reported_by.name"
                        target="_blank"
                    />
                    <template v-else>
                        {{ report.reported_by.name }} &lt;{{ report.reported_by.email }}&gt;
                    </template>
                </p>
                <p><strong>{{ __('Reported At', 'dokan') }}:</strong> {{ moment(report.reported_at).format('MMM D, YYYY h:mm:ss a') }}</p>
                <p>
                    <strong>{{ __('Product Vendor', 'dokan') }}:</strong>
                    <a
                        v-if="report.reported_by.admin_url"
                        :href="report.reported_by.admin_url"
                        v-text="report.reported_by.name"
                    />
                    <template v-else>
                        {{ report.reported_by.name }} &lt;{{ report.reported_by.email }}&gt;
                    </template>
                </p>
            </template>
        </modal>
    </div>
</template>

<script>
    import AbuseReasonsDropdown from '../../components/AbuseReasonsDropdown.vue';
    const ListTable = dokan_get_lib('ListTable');
    const Modal = dokan_get_lib('Modal');

    export default {
        name: 'AbuseReports',

        components: {
            AbuseReasonsDropdown,
            ListTable,
            Modal
        },

        data() {
            return {
                columns: {
                    reason: {
                        label: this.__('Reason', 'dokan')
                    },

                    product: {
                        label: this.__('Product', 'dokan'),
                    },

                    vendor: {
                        label: this.__('Vendor', 'dokan')
                    },

                    reported_by: {
                        label: this.__('Reported by', 'dokan')
                    },

                    reported_at: {
                        label: this.__('Reported at', 'dokan')
                    }
                },
                loading: false,
                reports: [],
                actions: [],
                bulkActions: [],
                totalItems: 0,
                totalPages: 1,
                perPage: 10,
                showModal: false,
                report: {},
                query: {},
                filter: {
                    reason: ''
                },
            };
        },

        computed: {
            currentPage() {
                const page = this.$route.query.page || 1;
                return parseInt(page);
            },

            queryFilterReason() {
                return this.$route.query.reason || '';
            }
        },

        created() {
            if (this.queryFilterReason) {
                this.filter.reason = this.queryFilterReason;
                this.query.reason = this.queryFilterReason;
            }

            console.log('before');
            this.fetchReports();
        },

        watch: {
            '$route.query.page'() {
                this.fetchReports();
            },

            '$route.query.reason'() {
                this.fetchReports();
            },

            'filter.reason'(reason) {
                this.query = {};

                if (reason) {
                    this.query = {
                        reason
                    };
                }

                this.goTo(this.query);
            }
        },

        methods: {
            fetchReports() {
                const self = this;

                self.loading = true;

                if (self.currentPage > 1) {
                    self.query.page = self.currentPage;
                }

                dokan.api.get('/abuse-reports', self.query).done((response, status, xhr) => {
                    self.reports = response;
                    self.loading = false;

                    self.updatePagination(xhr);
                })
            },

            updatePagination(xhr) {
                this.totalPages = parseInt( xhr.getResponseHeader('X-Dokan-AbuseReports-TotalPages') );
                this.totalItems = parseInt(xhr.getResponseHeader('X-Dokan-AbuseReports-Total'));
            },

            moment(date) {
                return moment(date);
            },

            goToPage(page) {
                this.query.page = page;
                this.goTo(this.query);
            },

            goTo(query) {
                this.$router.push({
                    name: 'AbuseReports',
                    query
                });
            },

            showReport(report) {
                this.report = report;
                this.showModal = true;
            },

            hideReport() {
                this.report = {};
                this.showModal = false;
            }
        }
    };
</script>
