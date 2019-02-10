
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

import VModal from 'vue-js-modal'
import { L, LMap, LTileLayer, LMarker } from 'vue2-leaflet'
import VueCountdown from '@chenfengyuan/vue-countdown'

Vue.use(VModal)
Vue.component('l-map', LMap)
Vue.component('l-tile-layer', LTileLayer)
Vue.component('l-marker', LMarker)
Vue.component(VueCountdown.name, VueCountdown)
/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

Vue.component('example-component', require('./components/ExampleComponent.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
 Vue.component(
     'app-container',
     require('./components/Container.vue').default
 );
 Vue.component(
     'app-header',
     require('./components/Header.vue').default
 );
 Vue.component(
     'app-nav',
     require('./components/Nav.vue').default
 );
Vue.component(
    'raidsmap',
    require('./components/Map.vue').default
);
Vue.component(
    'raidslist',
    require('./components/List.vue').default
);
Vue.component(
    'settings',
    require('./components/Settings.vue').default
);
Vue.component(
    'gym-modal',
    require('./components/GymModal.vue').default
);
Vue.component(
    'button-actions',
    require('./components/ButtonActions.vue').default
);

const app = new Vue({
    el: '#app',
});
