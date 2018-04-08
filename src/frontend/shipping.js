import App from './components/shipping.vue'
import router from './router'


let Vue = dokan_get_lib('Vue');

new Vue({
    el: '#dokan-vue-shipping',
    router,
    render: h => h(App)
});

