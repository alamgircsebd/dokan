<template>
    <div id="dokan-store-categories">
        <h1 class="wp-heading-inline">{{ __( 'Store Categories', 'dokan' ) }}</h1>
        <form class="search-form wp-clearfix" @submit.prevent>
            <p class="search-box">
                <lazy-input
                    v-model="search"
                    name='s'
                    type="search"
                    :placeholder="__('Search Categories')"
                ></lazy-input>
            </p>
        </form>
        <div id="col-container" class="wp-clearfix">
            <div id="col-left">
                <div class="col-wrap">
                    <div class="form-wrap">
                        <h2>{{ __( 'Add New Category', 'dokan' ) }}</h2>

                        <form id="addtag" @submit.prevent="addCategory">
                            <fieldset :disabled="isCreating">
                                <div class="form-field form-required term-name-wrap">
                                    <label for="tag-name">{{ __( 'Name', 'dokan' ) }}</label>
                                    <input v-model="category.name" id="tag-name" type="text" size="40" aria-required="true">
                                    <p>{{ __( 'The name of the category.', 'dokan' ) }}</p>
                                </div>

                                <div class="form-field term-slug-wrap">
                                    <label for="tag-slug">{{ __( 'Slug', 'dokan' ) }}</label>
                                    <input v-model="category.slug" id="tag-slug" type="text" size="40">
                                    <p>{{ __( 'The “slug” is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.', 'dokan' ) }}</p>
                                </div>

                                <div class="form-field term-description-wrap">
                                    <label for="tag-description">{{ __( 'Description', 'dokan' ) }}</label>
                                    <textarea v-model="category.description" id="tag-description" rows="5" cols="40" />
                                    <p>{{ __( 'The description is not prominent by default; however, some themes may show it.', 'dokan' ) }}</p>
                                </div>

                                <p class="submit">
                                    <input type="submit" name="submit" id="submit" class="button button-primary" :value="__( 'Add New Category', 'dokan' )">
                                </p>
                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>
            <div id="col-right">
                <div class="col-wrap">
                    <form id="post-filter">
                        <list-table
                            :columns="columns"
                            :loading="loading"
                            :rows="categories"
                            :actions="actions"
                            :action-column="actionColumn"
                            :show-cb="showCb"
                            :total-items="totalItems"
                            :bulk-actions="bulkActions"
                            :total-pages="totalPages"
                            :per-page="perPage"
                            :current-page="currentPage"
                            :not-found="notFound"
                            :sort-by="sortBy"
                            :sort-order="sortOrder"
                            @pagination="goToPage"
                        >
                            <template slot="name" slot-scope="{ row }">
                                <strong>
                                    <router-link :to="{ name: 'StoreCategoriesShow', params: { id: row.id } }" v-html="columnName( row )" />
                                </strong>
                            </template>

                            <template slot="row-actions" slot-scope="{ row }">
                                <span v-for="( action, index ) in actions" :class="action.key">
                                    <router-link
                                        v-if="action.key === 'edit'"
                                        :to="{ name: 'StoreCategoriesShow', params: { id: row.id } }"
                                        v-text="action.label"
                                    />

                                    <template v-if="row.id !== defaultCategory">
                                        <a
                                            v-if="action.key === 'delete'"
                                            href="#delete"
                                            @click.prevent="deleteCategory( row )"
                                            v-text="action.label"
                                        />

                                        <a
                                            v-if="action.key === 'set_as_default'"
                                            v-text="action.label"
                                            href="#make-default"
                                            @click.prevent="makeDefaultCategory( row )"
                                        />

                                        <template v-if="index !== ( actions.length - 1 )"> | </template>
                                    </template>
                                </span>
                            </template>

                            <template slot="count" slot-scope="{ row }">
                                <router-link :to="{ name: 'Vendors',query: { store_category: row.slug} }">{{ row.count }}</router-link>
                            </template>
                        </list-table>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
const LazyInput = dokan_get_lib('LazyInput');
const ListTable = dokan_get_lib('ListTable');

export default {
    name: 'StoreCategoriesIndex',

    components: {
        LazyInput,
        ListTable
    },

    data() {
        return {
            apiHandler: {
                abort() {
                    //
                }
            },
            isCreating: false,
            category: {
                name: '',
                slug: '',
                description: ''
            },
            defaultCategory: 0,
            search: '',
            categories: [],
            showCb: false,
            totalItems: 0,
            perPage: 20,
            totalPages: 1,
            loading: false,
            notFound: this.__( 'No category found', 'dokan' ),
            columns: {
                name: {
                    label: this.__( 'Name', 'dokan' ),
                    sortable: true
                },

                description: {
                    label: this.__( 'Description', 'dokan' ),
                    sortable: false
                },

                slug: {
                    label: this.__( 'Slug', 'dokan' ),
                    sortable: true
                },

                count: {
                    label: this.__( 'Count', 'dokan' ),
                    sortable: true
                }
            },
            actionColumn: 'name',
            actions: [
                {
                    key: 'edit',
                    label: this.__( 'Edit', 'dokan' )
                },
                {
                    key: 'delete',
                    label: this.__( 'Delete', 'dokan' )
                },
                {
                    key: 'set_as_default',
                    label: this.__( 'Set as default', 'dokan' )
                }
            ],
            bulkActions: [],
            sortBy: 'name',
            sortOrder: 'asc'
        };
    },

    computed: {
        currentPage() {
            let page = this.$route.query.page || 1;

            return parseInt( page );
        },
    },

    created() {
        if ( this.$router.currentRoute.query.search ) {
            this.search = this.$router.currentRoute.query.search;
        }

        this.fetchCategories();
    },

    watch: {
        '$route.query': 'fetchCategories',
        search: 'onChangeSearch'
    },

    methods: {
        updateHeaderParams( xhr ) {
            this.totalPages = parseInt( xhr.getResponseHeader( 'X-WP-TotalPages' ) );
            this.totalItems = parseInt( xhr.getResponseHeader( 'X-WP-Total' ) );
            this.defaultCategory = parseInt( xhr.getResponseHeader( 'X-WP-Default-Category' ) );
        },

        addCategory() {
            const self = this;

            self.isCreating = true;

            dokan.api.post( '/store-categories', self.category)
                .done( () => {
                    self.category = {
                        name: '',
                        slug: '',
                        description: ''
                    };

                    self.fetchCategories();

                } ).always( () => {
                    self.isCreating = false;
                } ).fail( ( jqXHR ) => {
                    const message = jqXHR.responseJSON.message;
                    alert( message );
                } );
        },

        fetchCategories() {
            const self = this;

            self.apiHandler.abort();

            self.loading = true;

            const query = {
                per_page: self.perPage,
                page: self.currentPage,
                status: self.currentStatus,
                orderby: self.sortBy,
                order: self.sortOrder
            };

            if ( self.search ) {
                query.search = self.search;
            }

            self.apiHandler = dokan.api.get( '/store-categories', query ).done( ( response, status, xhr ) => {
                self.categories = response;
                self.updateHeaderParams( xhr );
            } ).always( () => {
                self.loading = false;
            } );
        },

        deleteCategory( category ) {
            if ( confirm( this.__( 'Are you sure you want to delete this category?', 'dokan' ) ) ) {
                const self = this;

                self.loading = true;

                dokan.api.delete( `${self.$route.path}/${category.id}?force=true` )
                    .done( ( response ) => {
                        self.fetchCategories();
                    } ).fail( ( jqXHR ) => {
                        self.loading = false;
                        const message = jqXHR.responseJSON.message;
                        alert( message );
                    } );
            }
        },

        onChangeSearch( search ) {
            const query = $.extend( true, {}, this.$router.currentRoute.query );

            if ( search ) {
                query.search = search;
            } else {
                delete query.search;
            }

            this.$router.replace( {
                query
            } );
        },

        goToPage(page) {
            this.$router.push({
                name: 'StoreCategoriesIndex',
                query: {
                    status: this.currentStatus,
                    page: page
                }
            });
        },

        makeDefaultCategory( category ) {
            const self = this;

            self.loading = true;

            dokan.api.put( `${self.$route.path}/default-category`, category )
                .done( ( response ) => {
                    self.fetchCategories();
                } ).fail( ( jqXHR ) => {
                    self.loading = false;
                    const message = jqXHR.responseJSON.message;
                    alert( message );
                } );
        },

        columnName( row ) {
            let name = row.name ? row.name : __( '(no name)', 'dokan' );

            if ( row.id === this.defaultCategory ) {
                name += this.sprintf( '<span class="default-category"> - %s</span>', this.__( 'Default', 'dokan' ) );
            }

            return name;
        }
    }
};
</script>

<style lang="less">
    .wp-list-table {

        .default-category {
            color: #666;
        }
    }
</style>
