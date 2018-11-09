import Main from './components/Main.vue'
import Zone from './components/Zone.vue'
import Settings from './components/Settings.vue'

let Vue    = dokan_get_lib('Vue')
let Router = dokan_get_lib('Router')

Vue.use(Router)

var routes = [
    { path: '/', component: Main, name: 'Main' },
    { path: '/settings', component: Settings, name: 'Settings' },
    { path: '/zone/:zoneID', component: Zone, name: 'Zone' },
]


export default new Router({
    routes: routes
})
