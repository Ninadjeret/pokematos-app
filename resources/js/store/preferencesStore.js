import Vue from 'vue';
import Vuex from 'vuex';
import axios from 'axios';

Vue.use(Vuex);

const defaultSettings = {
    raidsListFilters: ["1", "2", "3", "4", "5", "6"],
    raidsListOrder: 'date',
    hideGyms: false
}

const preferencesStore = new Vuex.Store({
    state: {
        appaerance_mod: 'system'
    },
    mutations: {
        UpdateAppearanceMod(state) {
            state.appaerance_mod = res.data;
            localStorage.setItem('pokematos_preferences_appaerance_mod', state.appaerance_mod);
        },
    },
    getters: {
        getAppearanceMod: state => {
            return state.appaerance_mod;
        },
    },
});

export default preferencesStore;