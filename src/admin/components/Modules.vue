<template>
    <div class="dokan-modules-wrap">
        <h1>{{ __( 'Modules', 'dokan' ) }}</h1>

        <div class="wp-filter module-filter">
            <div class="filter-items">
                <ul>
                    <li><router-link :to="{ name: 'Modules' }" active-class="current" exact v-html="__( 'All', 'dokan-lite' )"></router-link></li>
                    <li><router-link :to="{ name: 'Modules', query: { status: 'active' }}" active-class="current" exact v-html="__( 'Active', 'dokan-lite' )"></router-link></li>
                    <li><router-link :to="{ name: 'Modules', query: { status: 'inactive' }}" active-class="current" exact v-html="__( 'Inactive', 'dokan-lite' )"></router-link></li>
                </ul>
            </div>

            <div class="search-form">
                <div class="view-switch">
                    <a href="#" class="view-grid" id="view-switch-grid"><span class="screen-reader-text">Grid View</span></a>
                    <a href="#" class="view-list current" id="view-switch-list"><span class="screen-reader-text">List View</span></a>
                </div>

                <label for="media-search-input" class="screen-reader-text">Search Media</label>
                <input type="search" placeholder="Search Module..." v-model="search" id="media-search-input" class="search">
            </div>
        </div>

        <div class="module-content">
            <template v-if="isLoaded">
                <list-table
                  :columns="column"
                  :loading="false"
                  :rows="modules"
                  :actions="[]"
                  :show-cb="true"
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
                  action-column="name"
                  @bulk:click="onBulkAction"

                >
                    <template slot="name" slot-scope="data">
                        <img :src="data.row.thumbnail" :alt="data.row.name" width="50">
                        <strong><a href="#">{{ data.row.name }}</a></strong>
                    </template>

                    <template slot="active" slot-scope="data">
                        <switches :enabled="data.row.active" :value="data.row.slug" @input="onSwitch"></switches>
                    </template>

                </list-table>

                <div class="wp-list-table widefat dokan-modules">
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
                                        <li :data-module="module.slug">
                                            <switches :enabled="module.active" :value="module.slug" @input="onSwitch"></switches>
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
                toggleActivation: false,
                modules : [],
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
                }
            }
        },

        components: {
            Loading,
            Switches,
            ListTable
        },

        computed: {

            currentStatus() {
                return this.$route.query.status || 'all';
            },

            filteredModules() {
                var self=this;
                return this.modules.filter( function( module ){
                    return module.name.toLowerCase().indexOf( self.search.toLowerCase() ) >= 0;
                } );
            }
        },

        watch: {
            '$route.query.status'() {
                this.fetchModuels();
            }
        },

        methods: {
            fetchModuels() {
                this.isLoaded = false;

                dokan.api.get('/admin/modules?status=' + this.currentStatus )
                .done((response, status, xhr) => {
                    this.modules = response;
                    this.isLoaded = true;
                });
            },

            onSwitch( status, moduleSlug ) {
                var moduleData = _.findWhere( this.modules, { slug: moduleSlug } );

                this.toggleActivation = true;

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
                        this.toggleActivation = false;
                    });
                }
            },


            onBulkAction( action, items ) {
                console.log( action, items );
            }
        },

        created() {
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
                        margin: 0px 0px 0px -4px;

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

                            &.current {
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
</style>