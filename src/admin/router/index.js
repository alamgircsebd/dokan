import Vue from 'vue'
import Router from 'vue-router'
import Vendors from 'admin/components/Vendors.vue'
import VendorSingle from 'admin/components/VendorSingle.vue'
import Settings from 'admin/components/Settings.vue'

Vue.use(Router)

export default new Router({
    routes: [
        {
            path: '/',
            name: 'Vendors',
            component: Vendors
        },
        {
            path: '/vendors/:id',
            name: 'VendorSingle',
            component: VendorSingle
        },
        {
            path: '/settings',
            name: 'Settings',
            component: Settings
        },
    ]
})
