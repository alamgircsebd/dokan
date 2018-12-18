<template>
    <div class="tools-page">
        <h1 class="wp-heading-inline">{{ __( 'Tools Page', 'dokan' ) }}</h1>

        <postbox v-for="(type, key) in types" :key="key" :title="type.name">
            <p v-text="type.desc"></p>

            <div v-if="showBar == key && key != '' ">
                <progressbar :value="progressValue"></progressbar>
            </div>

            <a class="button button-primary" v-text="type.button" @click="doAction(key)"></a>
        </postbox>
    </div>
</template>

<script>
let Postbox     = dokan_get_lib('Postbox');
let Progressbar = dokan_get_lib('Progressbar');

export default {
    name: 'Tools',

    components: {
        Postbox,
        Progressbar
    },

    data() {
        return {
            progressValue: 0,
            showBar: '',
            types: [
                {
                    name: this.__( 'Page Installation', 'dokan' ),
                    desc: this.__( 'Clicking this button will create required pages for the plugin.', 'dokan' ),
                    button: this.__( 'Install Dokan Pages', 'dokan' ),
                    action: 'create_pages'
                },
                {
                    name: this.__( 'Regenerate Order Sync Table', 'dokan' ),
                    desc: this.__( 'This tool will delete all orders from the Dokan\'s sync table and re-build it.', 'dokan' ),
                    button: this.__( 'Re-build', 'dokan' ),
                    total_orders: 0,
                    offset: 0,
                    limit: 100,
                    action: 'regen_sync_table'
                },
                {
                    name: this.__( 'Check for Duplicate Orders', 'dokan' ),
                    desc: this.__( 'This tool will check for duplicate orders from the Dokan\'s sync table.', 'dokan' ),
                    button: this.__( 'Check Orders', 'dokan' ),
                    total_orders: 0,
                    offset: 0,
                    limit: 100,
                    action: 'check_duplicate_suborders'
                }
            ],

        }
    },

    methods: {
        doAction(key) {
            switch (key) {
                case 0:
                    this.createPages();
                    break;
                case 1:
                    this.rebuildOrderTable();
                    this.showProgressBar(key);
                    break;
                case 2:
                    this.checkDuplicateOrder();
                    this.showProgressBar(key);
                    break;
            }
        },

        createPages() {
            let self = this;
            let data = this.types[0];

            jQuery.post( dokan.ajaxurl, data, function(res) {
                if ( res.success ) {
                    self.$notify({
                        title: self.__( 'Success!', 'dokan' ),
                        text: res.data.message,
                        type: 'success'
                    })
                } else {
                    self.$notify({
                        title: self.__( 'Failure!', 'dokan' ),
                        text: self.__( 'Something went wrong.' ),
                        type: 'warn'
                    })
                }
            } );
        },

        rebuildOrderTable() {
            let self = this;
            let data = this.types[1];

            jQuery.post( dokan.ajaxurl, data, function(res) {
                  if ( res.success ) {
                    let completed = ( res.data.done * 100 ) / res.data.total_orders;

                    if ( ! isNaN( completed ) ) {
                        self.progressValue = Math.round( completed );
                    }

                    if ( res.data.done != 'All' ) {
                        self.types[1].offset       = res.data.offset;
                        self.types[1].total_orders = res.data.total_orders;

                        self.$notify({
                            title: self.__( 'Order re-build in progress...', 'dokan' ),
                            text: res.data.message,
                            type: 'success'
                        })

                        return self.rebuildOrderTable();
                    }

                    self.$notify({
                        title: self.__( 'Success!', 'dokan' ),
                        text: res.data.message,
                        type: 'success'
                    })
                } else {
                    self.$notify({
                        title: self.__( 'Failure!', 'dokan' ),
                        text: self.__( 'Something went wrong.' ),
                        type: 'warn'
                    })
                }
            } );
        },

        checkDuplicateOrder() {
            let self = this;
            let data = this.types[2];

            jQuery.post( dokan.ajaxurl, data, function(res) {
                  if ( res.success ) {
                    let completed = ( res.data.done * 100 ) / res.data.total_orders;

                    if ( ! isNaN( completed ) ) {
                        self.progressValue = Math.round( completed );
                    }

                    if ( res.data.done != 'All' ) {
                        self.types[2].offset       = res.data.offset;
                        self.types[2].total_orders = res.data.total_orders;
                        self.types[2].done         = res.data.done

                        self.$notify({
                            title: self.__( 'Checking Duplication in Progress...', 'dokan' ),
                            text: res.data.message,
                            type: 'success'
                        })

                        return self.checkDuplicateOrder();
                    }

                    self.$notify({
                        title: self.__( 'Success!', 'dokan' ),
                        text: res.data.message,
                        type: 'success'
                    })
                } else {
                    self.$notify({
                        title: self.__( 'Failure!', 'dokan' ),
                        text: self.__( 'Something went wrong.' ),
                        type: 'warn'
                    })
                }
            } );
        },

        showProgressBar(key) {
            return this.showBar = key
        }
    }
}
</script>