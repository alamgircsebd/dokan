<template>
    <router-link v-if="categories.length" class="page-title-action" :to="{name: 'StoreCategoriesIndex'}">{{ __( 'Store Categories', 'dokan' ) }}</router-link>
</template>

<script>
export default {
    name: 'VendorPro',

    data() {
        return {
            categories: [],
            isCategoryMultiple: false,
            storeCategoryType: dokan.store_category_type,
        }
    },

    created() {
        if (this.storeCategoryType !== 'none') {
            this.fetchCategories();
        }
    },

    methods: {
        fetchCategories() {
            const self = this;

            dokan.api.get( '/store-categories' )
                .done( ( response, status, xhr ) => {
                    self.categories = response;
                    self.isCategoryMultiple = ( 'multiple' === xhr.getResponseHeader( 'X-WP-Store-Category-Type' ) );

                    self.columns = {
                        'store_name': {
                            label: this.__( 'Store', 'dokan' ),
                            sortable: true
                        },
                        'email': {
                            label: this.__( 'E-mail', 'dokan' )
                        },
                        'categories': {
                            label: self.isCategoryMultiple ? this.__( 'Categories', 'dokan' ) : this.__( 'Category', 'dokan' )
                        },
                        'phone': {
                            label: this.__( 'Phone', 'dokan' )
                        },
                        'registered': {
                            label: this.__( 'Registered', 'dokan' ),
                            sortable: true
                        },
                        'enabled': {
                            label: this.__( 'Status', 'dokan' )
                        }
                    };

                    this.$root.$emit( 'categoryFetched', self );
                }
            );
        },
    }
};
</script>