<template>
    <div class="subscription-list">
        <h1 class="wp-heading-inline">{{ __( 'Subscription User List', 'dokan') }}</h1>
        <hr class="wp-header-end">

        <ul class="subsubsub">
            <li><router-link to="" active-class="current" exact v-html="sprintf( __( 'Total Subscribed Vendors <span class=\'count\'>(%s)</span>', 'dokan' ), counts.all )"></router-link></li>
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

            not-found="No vendors found."
            :sort-order="sortOrder"

            @pagination="goToPage"
            @bulk:click="onBulkAction"
        >
            <template slot="user_name" slot-scope="data">
                <strong><a :href="data.row.user_link">{{ data.row.user_name ? data.row.user_name : __( '(no name)', 'dokan' ) }}</a></strong>
            </template>

            <template slot="subscription_title" slot-scope="data">
                <strong><a :href="subscriptionUrl(data.row.subscription_id)">{{ data.row.subscription_title ? data.row.subscription_title : __( '(no name)', 'dokan' ) }}</a></strong>
            </template>

            <template slot="end_date" slot-scope="data">
                {{ subscriptionEndDate(data.row) }}
            </template>

            <template slot="status" slot-scope="data">
                {{ subscriptionStatus(data.row) }}
            </template>

            <template slot="action" slot-scope="data">
                <span @click="toggleSubscription(data.row)" class="action-btn dashicons dashicons-edit"></span>
            </template>
        </list-table>
    </div>
</template>

<script>
let ListTable = dokan_get_lib('ListTable');

export default {

    name: 'Subscriptions',

    components: {
        ListTable
    },

    data () {
        return {
            showCb: true,

            counts: {
                all: 0
            },

            totalItems: 0,
            perPage: 10,
            totalPages: 1,
            loading: false,
            columns: {
                'user_name': {
                    label: this.__( 'User Name', 'dokan' ),
                },
                'subscription_title': {
                    label: this.__( 'Subscription Pack', 'dokan' )
                },
                'start_date': {
                    label: this.__( 'Start Date', 'dokan' )
                },
                'end_date': {
                    label: this.__( 'End Date', 'dokan' ),
                },
                'status': {
                    label: this.__( 'Status', 'dokan' )
                },
                'action': {
                    label: this.__( 'Action', 'dokan' )
                }
            },
            actions: [],
            bulkActions: [
                {
                    key: 'cancel',
                    label: this.__( 'Cancel Subscription', 'dokan' )
                },
                {
                    key: 'activate',
                    label: this.__( 'Activate Subscription', 'dokan' )
                },
            ],
            vendors: []
        }
    },

    watch: {
        '$route.query.status'() {
            this.fetchSubscription();
        },

        '$route.query.page'() {
            this.fetchSubscription();
        },

        '$route.query.orderby'() {
            this.fetchSubscription();
        },

        '$route.query.order'() {
            this.fetchSubscription();
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
        this.fetchSubscription();
    },

    methods: {
        toggleSubscription(subscription) {
            const hasActiveCancelledSubscription = subscription.status && subscription.has_active_cancelled_sub;

            if ( hasActiveCancelledSubscription ) {
                this.$swal({
                    title: this.__( 'Cancel Subscription', 'dokan' ),
                    type: 'warning',
                    html: `Subscriptoin will be cancelled at ${subscription.end_date} automatically`,
                    showCancelButton: subscription.is_recurring,
                    confirmButtonText: this.__( 'Cancel now', 'dokan' ),
                    cancelButtonText: this.__( 'Don\'t cancel subscription', 'dokan' ),
                })
                .then( async (response) => {
                    if ( response.dismiss && 'overlay' === response.dismiss ) {
                        return;
                    }

                    this.loading = true;
                    let action = '';
                    let cancelImmediately = false;

                    if ( response.value ) {
                        action = 'cancel'
                        cancelImmediately = true;
                    }

                    if ( response.dismiss && 'cancel' === response.dismiss ) {
                        action = 'activate'
                    }

                    const updated = await this.updateSubscriptonStatus( subscription.id, action, cancelImmediately );

                    if ( updated ) {
                        this.loading = false;
                        const message = 'cancel' === action ? this.__( 'cancelled', 'dokan' ) : this.__( 're-activated', 'dokan' );
                        this.$swal.fire({
                            title: this.__( `Subscription has been ${message}`, 'dokan' ),
                            type: 'success',
                            showConfirmButton: false,
                            timer: 2000
                        });
                    }

                    location.reload()
                })
            } else {
                this.$swal({
                    title: this.__( 'Cancel Subscription', 'dokan' ),
                    type: 'warning',
                    showCancelButton: true,
                    cancelButtonText: this.__( 'Don\'t cancel', 'dokan' ),
                    confirmButtonText: this.__( 'Cancel subscription', 'dokan' ),
                    input: 'radio',
                    inputOptions: {
                        immediately: this.__( `Immediately <span class="date">${subscription.current_date}</span>`, 'dokan' ),
                        end_of_current_period: this.__( `End of the current period <span class="date">${subscription.end_date}</span>`, 'dokan' ),
                    }
                })
                .then( async (response) => {
                    if ( response.dismiss || ! response.value ) {
                        return;
                    }

                    this.loading = true;
                    let action = 'cancel'
                    let cancelImmediately = 'immediately' === response.value;

                    const updated = await this.updateSubscriptonStatus( subscription.id, action, cancelImmediately );

                    if ( updated ) {
                        this.loading = false;
                        this.$swal.fire({
                            title: this.__( 'Subscription has been cancelled', 'dokan' ),
                            type: 'success',
                            showConfirmButton: false,
                            timer: 2000
                        });
                    }

                    location.reload()
                })
            }
        },

        buttonTitle(subscription) {
            return subscription.status && subscription.has_active_cancelled_sub ? this.__( 'Reactivate', 'dokan') : this.__( 'Cancel', 'dokan' )
        },

        updatedCounts(xhr) {
            this.counts.all = parseInt( xhr.getResponseHeader('X-WP-Total') );
        },

        updatePagination(xhr) {
            this.totalPages = parseInt( xhr.getResponseHeader('X-WP-TotalPages') );
            this.totalItems = parseInt( xhr.getResponseHeader('X-WP-Total') );
        },

        updateSubscriptonStatus(id, status, immediately) {
            let self = this;
            let data = {
                action: status,
                immediately: immediately
            }

            return new Promise((resolve, reject) => {
                dokan.api.put('/subscription/' + id, data )
                    .done((response, status, xhr) => {
                        if ( 'success' === status ) {
                            resolve(true);
                        } else {
                            reject( new Error( 'Something went wrong' ) );
                        }
                    });
            });
        },

        fetchSubscription() {
            let self = this;

            self.loading = true;

            // dokan.api.get('/subscription?number=' + this.perPage + '&paged=' + this.currentPage)
            dokan.api.get('/subscription', {
                per_page: self.perPage,
                paged: self.currentPage,
                order: this.sortOrder
            })
            .done((response, status, xhr) => {
                if ( response.code == 'no_subscription' ) {
                    return self.loading = false;
                }

                self.vendors = response;
                self.loading = false;

                this.updatedCounts(xhr);
                this.updatePagination(xhr);
            });
        },

        goToPage(page) {
            this.$router.push({
                name: 'Subscriptions',
                query: {
                    page: page
                }
            });
        },

        onBulkAction(action, items) {
            const message = 'activate' === action
                ? this.__( 'Want to activate the subscription again?', 'dokan' )
                : this.__( 'Are you sure to cancel the subscription?', 'dokan' );

            if ( ! confirm( message ) ) {
                return;
            }

            let data = {
                action: action,
                user_ids: items
            }

            this.loading = true;

            dokan.api.put('/subscription/batch', data)
            .done(response => {
                location.reload();
            });
        },

        subscriptionUrl(id) {
            return dokan.urls.adminRoot + 'post.php?post=' + id + '&action=edit';
        },

        subscriptionStatus(subscription) {
            if ( subscription.status && subscription.has_active_cancelled_sub ) {
                return this.sprintf( this.__( 'Active (Cancels %s)', 'dokan' ), subscription.end_date );
            }

            if ( subscription.status ) {
                return this.__( 'Active', 'dokan' );
            }

            return this.__( 'Inactive', 'dokan' );
        },

        subscriptionEndDate(subscription) {
            return subscription.is_recurring ? this.__( 'Recurring', 'dokan' ) : subscription.end_date;
        },
    }
};
</script>

<style lang="less">
    .subscription-list {
        table {
            th.column.status {
                width: 20% !important;
            }

            .action-btn {
                cursor: pointer;
            }
        }
    }

    .swal2-radio {
        flex-direction: column;

        label {
            padding-bottom: 8px;
        }
    }
    .swal2-actions button:focus {
        display: none;
        outline: none;
    }
    .swal2-content {
        .swal2-label {
            font-weight: normal;
            span.date {
                color: #919191;
                font-weight: lighter;
            }
        }
    }
</style>
