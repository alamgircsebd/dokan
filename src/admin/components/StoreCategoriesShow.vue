<template>
    <div id="dokan-store-category-single">
        <h1>
            {{ __( 'Edit Category', 'dokan' ) }}

            <a
                href="#"
                class="alignright button"
                @click.prevent="deleteCategory"
            >{{ __( 'Delete Category', 'dokan' ) }}</a>
        </h1>
        <form @submit.prevent="updateCategory">
            <fieldset :disabled="loading">
                <table class="form-table">
                    <tbody>
                        <tr class="form-field form-required term-name-wrap">
                            <th scope="row">
                                <label for="name">{{ __( 'Name', 'dokan' ) }}</label>
                            </th>
                            <td>
                                <input v-model="category.name" id="name" type="text" size="40" aria-required="true">
                                <p class="description">{{ __( 'Name of the store category', 'dokan' ) }}</p>
                            </td>
                        </tr>

                        <tr class="form-field term-slug-wrap">
                            <th scope="row"><label for="slug">{{ __( 'Slug', 'dokan' ) }}</label></th>
                            <td>
                                <input v-model="category.slug" id="slug" type="text" size="40">
                                <p class="description">{{ __( 'The “slug” is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.', 'dokan' ) }}</p>
                            </td>
                        </tr>

                        <tr class="form-field term-description-wrap">
                            <th scope="row"><label for="description">{{ __( 'Description', 'dokan' ) }}</label></th>
                            <td>
                                <textarea v-model="category.description" id="description" rows="5" cols="50" class="large-text" />
                                <p class="description">{{ __( 'The description is not prominent by default; however, some themes may show it.', 'dokan' ) }}</p>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="edit-tag-actions">
                    <button type="submit" class="button button-primary">{{ __( 'Update', 'dokan' ) }}</button>
                </div>
            </fieldset>
        </form>
    </div>
</template>

<script>
export default {
    name: 'StoreCategoriesShow',

    data() {
        return {
            category: {},
            loading: true
        };
    },

    created() {
        this.fetchCategory();
    },

    methods: {
        fetchCategory() {
            const self = this;

            self.loading = true;

            dokan.api.get( self.$route.path )
                .done( ( response ) => {
                    self.category = response;
                } ).always( () => {
                    self.loading = false;
                } );
        },

        updateCategory() {
            const self = this;

            self.loading = true;

            dokan.api.put( self.$route.path, self.category )
                .done( ( response ) => {
                    self.category = response;
                } ).always( () => {
                    self.loading = false;
                } ).fail( ( jqXHR ) => {
                    const message = jqXHR.responseJSON.message;
                    alert( message );
                } );
        },

        deleteCategory() {
            if ( confirm( this.__( 'Are you sure you want to delete this category?', 'dokan' ) ) ) {
                const self = this;

                self.loading = true;

                dokan.api.delete( `${self.$route.path}?force=true` )
                    .done( ( response ) => {
                        this.$router.push( {
                            name: 'StoreCategoriesIndex'
                        } );
                    } ).always( () => {
                        self.loading = false;
                    } ).fail( ( jqXHR ) => {
                        const message = jqXHR.responseJSON.message;
                        alert( message );
                    } );
            }
        }
    }
};
</script>
