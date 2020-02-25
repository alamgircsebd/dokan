<template>
    <div class="dokan-modules-wrap">
        <h1>{{ __( 'Modules', 'dokan' ) }}</h1>

        <div class="wp-filter module-filter">
            <div class="filter-items">
                <ul>
                    <li v-for="(menu, index) in filterMenu" :key="index" :class="[filterMenuClass(menu.route)]">
                        <router-link :to="menu.route">
                            {{ menu.title }}
                        </router-link>
                    </li>
                </ul>
            </div>

            <div class="search-form">
                <div class="view-switch">
                    <a href="#" class="view-grid" :class="{ 'current' : currentView == 'grid' }" id="view-switch-grid" @click.prevent="changeView( 'grid' )"><span class="screen-reader-text">Grid View</span></a>
                    <a href="#" class="view-list" :class="{ 'current' : currentView == 'list' }" id="view-switch-list" @click.prevent="changeView( 'list' )"><span class="screen-reader-text">List View</span></a>
                </div>

                <label for="media-search-input" class="screen-reader-text">Search Media</label>
                <input type="search" placeholder="Search Module..." v-model="search" id="media-search-input" class="search">
            </div>
        </div>

        <div class="module-content">
            <template v-if="isLoaded">
                <list-table
                    v-if="currentView == 'list'"
                    :columns="column"
                    :loading="false"
                    :rows="filteredModules"
                    :actions="[]"
                    :show-cb="true"
                    not-found="No module found."
                    :bulk-actions="[
                        {
                            key: 'activate',
                            label: 'Activate'
                        },
                        {
                            key: 'deactivate',
                            label: 'Deactivate'
                        }
                    ]"
                    :sort-by="sortBy"
                    :sort-order="sortOrder"
                    @sort="sortCallback"

                    action-column="name"
                    @bulk:click="onBulkAction"

                >
                    <template slot="name" slot-scope="data">
                        <img :src="data.row.thumbnail" :alt="data.row.name" width="50">
                        <strong><a href="#">{{ data.row.name }}</a></strong>
                    </template>

                    <template slot="active" slot-scope="data">
                        <switches :enabled="data.row.active" :value="data.row.id" @input="onSwitch"></switches>
                    </template>

                </list-table>

                <div class="wp-list-table widefat dokan-modules" v-if="currentView == 'grid'">
                    <template v-if="filteredModules.length > 0">
                        <div class="plugin-card" v-for="module in filteredModules">
                            <div class="plugin-card-top">
                                <div class="name column-name">
                                    <h3>
                                        <span class="plugin-name">{{ module.name }}</span>
                                        <img class="plugin-icon" :src="module.thumbnail" :alt="module.name" />
                                    </h3>
                                </div>

                                <div class="action-links">
                                    <ul class="plugin-action-buttons">
                                        <li :data-module="module.id">
                                            <switches :enabled="module.active" :value="module.id" @input="onSwitch"></switches>
                                        </li>
                                    </ul>
                                </div>

                                <div class="desc column-description">
                                    <p v-html="module.description"></p>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template v-else>
                        <div id="message" class="notice notice-info">
                            <p><strong>{{ __( 'No modules found.', 'dokan' ) }}</strong></p>
                        </div>
                    </template>
                </div>
            </template>
            <div class="loading" v-else>
                <loading></loading>
            </div>
        </div>
    </div>

</template>

<script>
    let ListTable = dokan_get_lib('ListTable');
    let Loading = dokan_get_lib('Loading');
    let Switches  = dokan_get_lib('Switches');

    export default {

        name: 'Modules',

        data() {
            return {
                search: '',
                isLoaded: false,
                currentView: '',
                modules : [],
                count: {},
                column: {
                    'name': {
                        label: 'Module Name',
                        sortable: true
                    },
                    'description': {
                        label: 'Description'
                    },
                    'active': {
                        label: 'Status'
                    }
                },

                filterMenu: [
                    {
                        title: 'All',
                        route: {
                            name: 'Modules',
                            params: {}
                        }
                    },
                    {
                        title: 'Active',
                        route: {
                            name: 'ModulesStatus',
                            params: {
                                status: 'active'
                            }
                        }
                    },
                    {
                        title: 'Inactive',
                        route: {
                            name: 'ModulesStatus',
                            params: {
                                status: 'inactive'
                            }
                        }
                    }
                ]
            }
        },

        components: {
            Loading,
            Switches,
            ListTable
        },

        computed: {

            currentStatus() {
                return this.$route.params.status || 'all';
            },

            filteredModules() {
                var self = this;

                var data =  this.modules.filter( ( module ) => {
                    // return module.name.toLowerCase().indexOf( self.search.toLowerCase() ) >= 0;
                    return module.available && module.name.toLowerCase().indexOf( self.search.toLowerCase() ) >= 0;
                } ).sort( ( a, b ) => {
                    return a.name.localeCompare( b.name );
                } ).sort( ( a, b ) => {
                    return ( a.available === b.available ) ? 1 : b.available ? 1 : -1;
                } );

                return data;
            },

            sortBy() {
                return this.$route.query.orderby || 'name';
            },

            sortOrder() {
                return this.$route.query.order || 'desc';
            },
        },

        watch: {
            '$route.query.order'() {
                this.fetchModuels();
            },

            '$route.params.status'() {
                this.fetchModuels();
            }
        },

        methods: {
            changeView( view ) {
                var activetab = '';
                this.currentView = view;

                if ( typeof( localStorage ) != 'undefined' ) {
                    localStorage.setItem( "activeview", this.currentView );
                }
            },

            fetchModuels() {
                this.isLoaded = false;

                dokan.api.get('/admin/modules?status=' + this.currentStatus + '&orderby=' + this.sortBy + '&order=' + this.sortOrder  )
                .done((response, status, xhr) => {
                    this.modules = response;
                    this.isLoaded = true;
                });
            },

            sortCallback(column, order) {
                var currentRoute = this.$router.currentRoute;

                var route = {
                    name: currentRoute.name,
                    params: {},
                    query: {
                        orderby: column,
                        order: order
                    }
                };

                if (currentRoute.params.status) {
                    route.params.status = currentRoute.params.status;
                }

                this.$router.push(route);
            },

            onSwitch( status, moduleSlug ) {
                var moduleData = _.findWhere( this.modules, { id: moduleSlug } );

                if ( status ) {
                    // Need to activate
                    var message = moduleData.name + this.__( 'is successfully activated', 'dokan' );

                    dokan.api.put('/admin/modules/activate', {
                        module: [ moduleSlug ]
                    })
                    .done(response => {
                        this.$notify({
                            title: 'Success!',
                            type: 'success',
                            text: message,
                        });

                        this.toggleActivation = false;
                        location.reload();
                    });

                } else {
                    // Need to deactivate
                    var message = moduleData.name + this.__( 'is successfully deactivated', 'dokan' );

                    dokan.api.put('/admin/modules/deactivate', {
                        module: [ moduleSlug ]
                    })
                    .done(response => {
                        this.$notify({
                            title: 'Success!',
                            type: 'success',
                            text: message,
                        });

                        location.reload();
                    });
                }
            },


            onBulkAction( action, items ) {
                var message = ( 'activate' == action ) ? this.__( 'All selected modules are successfully activated', 'dokan' ) : this.__( 'All selected modules are successfully deactivated', 'dokan' )

                dokan.api.put('/admin/modules/' + action, {
                    module: items
                })
                .done(response => {
                    this.fetchModuels();
                    this.$notify({
                        title: 'Success!',
                        type: 'success',
                        text: message,
                    });
                });
            },

            filterMenuClass(route) {
               let className = '';
               let currentRoute = this.$router.currentRoute;

               const routeParams = jQuery.extend(true, {}, route.params);
               const currentRouteParams = jQuery.extend(true, {}, currentRoute.params);

               if (route.name === currentRoute.name && _.isEqual(routeParams, currentRouteParams)) {
                   className = 'active';
               }

               return className;
           },
        },

        created() {
            if ( typeof(localStorage) != 'undefined' ) {
                this.currentView = localStorage.getItem("activeview") ? localStorage.getItem("activeview") : 'grid';
            } else {
                this.currentView = 'grid';
            }

            this.fetchModuels();
        }
    };

</script>

<style lang="less">
.dokan-modules-wrap {

    .module-content {
        position: relative;

        .loading {
            position: absolute;
            width: 100%;
            width: 100%;
            height: 100%;
            top: 0px;
            left: 0px;
            background: rgba( 255,255,255, 0.5);

            .dokan-loader {
                top: 30%;
                left: 47%;
            }
        }

        .dokan-modules {
            .plugin-card {
                .plugin-action-buttons {
                    .switch {
                        input:checked + .slider {
                            background-color: #0068A0;
                        }
                    }
                }
            }
        }

        table.wp-list-table {
            thead {
                tr {
                    th.active {
                        width: 10%;
                    }

                    th.description {
                        width: 55%;
                    }
                }
            }

            tbody {
                tr {
                    td.name {
                        img {
                            float: left;
                            margin-right: 10px;
                        }
                    }

                    td.active {
                        .switch {
                            input:checked + .slider {
                                background-color: #0068A0;
                            }
                        }
                    }
                }
            }
        }
    }

    .module-filter {
        margin-bottom: 10px;
        padding-left:0px;

        .filter-items {
            ul {
                margin: 0;
                overflow: hidden;
                margin-right: 30px;

                li {
                    display: inline-block;
                    line-height: 53px;
                    margin: 0px;

                    a{
                        font-size: 14px;
                        border-right: 1px solid #eee;
                        width: 80px;
                        display: block;
                        text-align: center;
                        font-weight: 500;
                        box-shadow: none;

                        &:hover {
                            background: #fafafa;
                        }

                    }

                    &.active {
                        a {
                            background: #fafafa;
                        }
                    }

                    &:last-child {
                        a {
                            border-right: none;
                        }
                    }
                }
            }
        }

        .search-form {
            .view-switch {
                padding: 0px;
                margin: -2px 8px 1px 2px;
            }
        }
    }
}

@media only screen and (max-width: 600px) {
    .dokan-modules-wrap {
        input#media-search-input {
            width: 74%;
        }

        .module-content {
            .tablenav.top {
                margin-top: -16px;
            }
        }

        table {
            td.name, td.active {
                display: table-cell !important;
            }

            th:not(.check-column):not(.name):not(.active) {
                display: none;
            }

            td:not(.check-column):not(.name):not(.active) {
                display: none;
            }

            th.column, td.column {
                width: auto;
            }

            th.column.name {
                width: 50% !important;
            }

            th.column.active {
                width: 20% !important;
            }

            td.column.order_id {
                .row-actions {
                    font-size: 11px;
                }
            }
        }
    }
}
</style>
