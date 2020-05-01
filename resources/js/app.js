
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');
import "leaflet/dist/leaflet.css"
import 'es6-promise/auto'

import Vue from 'vue';
import * as Sentry from '@sentry/browser';
import { Vue as VueIntegration } from '@sentry/integrations';
Sentry.init({
  dsn: 'https://dff629914bf3457289cd1aaee81810ee@o385969.ingest.sentry.io/5219592',
  integrations: [new VueIntegration({Vue, attachProps: true})],
});

import Vuetify from 'vuetify';
import fr from '../lang/fr/vuetify';
import VModal from 'vue-js-modal'
import { L, LMap, LTileLayer, LMarker } from 'vue2-leaflet'
import VueRouter from 'vue-router';
import VueCountdown from '@chenfengyuan/vue-countdown'
import Vuex from 'vuex'
import appStore from './store/store';
import vSelect from 'vue-select'
import 'vue-select/dist/vue-select.css';
import 'vuetify/dist/vuetify.min.css'
import DatetimePicker from 'vuetify-datetime-picker'

Vue.use(Vuex)
Vue.use(Vuetify, {
  lang: {
    locales: { fr },
    current: 'fr',
  },
  theme: {
    primary: '#5a6cae', // #C2185B
  },
});



Vue.use(VModal)
Vue.use(DatetimePicker)
Vue.component('l-map', LMap)
Vue.component('l-tile-layer', LTileLayer)
Vue.component('l-marker', LMarker)
Vue.component(VueCountdown.name, VueCountdown)
Vue.component('v-select', vSelect)
/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */
 Vue.component( 'gym-modal', require('./components/GymModal.vue').default );
 Vue.component( 'gym-edit', require('./components/admin/Gym.vue').default );
 Vue.component( 'button-actions', require('./components/ButtonActions.vue').default );
 Vue.component( 'snackbar', require('./components/parts/Snackbar.vue').default );
 Vue.component( 'permission', require('./components/admin/parts/Permission.vue').default );
 Vue.component( 'navigation', require('./components/Navigation.vue').default );
 Vue.component( 'rocket-map', require('./components/parts/RocketMap.vue').default );

import routes from './routes';
import Container from './components/Container.vue';
Vue.use(VueRouter);
const router = new VueRouter({
    routes
});

/*const app = new Vue({
    el: '#app',
    render: h => h(Container),
    router,
});*/
const app = new Vue({
    render: h => h(Container),
    store: appStore,
    router
}).$mount('#app')
