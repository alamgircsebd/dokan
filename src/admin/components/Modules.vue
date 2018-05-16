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
                                            <label class="dokan-toggle-switch">
                                                <input type="checkbox" name="module_toggle" class="dokan-toggle-module" :checked="module.active">
                                                <span class="slider round"></span>
                                            </label>
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
    let Loading = dokan_get_lib('Loading');

    export default {

        name: 'Modules',

        data() {
            return {
                search: '',
                isLoaded: false,
                modules : []
            }
        },

        components: {
            Loading,
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