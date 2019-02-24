import Vue from 'vue';
import Vuex from 'vuex';
import axios from 'axios';
import moment from 'moment';
import VuexPersistence from 'vuex-persist';

Vue.use(Vuex);

const store = new Vuex.Store({
    state: {
        currentCity: JSON.parse(localStorage.getItem('pokematos_currentCity') ),
        cities: JSON.parse(localStorage.getItem('pokematos_cities') ),
        gyms: JSON.parse(localStorage.getItem('pokematos_gyms') ),
        pokemons: JSON.parse(localStorage.getItem('pokematos_pokemons') ),
        settings: JSON.parse(localStorage.getItem('pokematos_settings') ),
        user: JSON.parse(localStorage.getItem('pokematos_user') ),
    },
    mutations: {
        fetchGyms( state ) {
            console.log('coucou');
            axios.get('/api/user/cities/'+state.currentCity.id+'/gyms').then( res => {
                state.gyms = res.data;
                localStorage.setItem('pokematos_gyms', JSON.stringify(state.gyms));
            }).catch( err => {
                //No error
            });
        },
        fetchPokemon( state ) {
            axios.get('/api/pokemons/raidbosses').then( res => {
                state.pokemons = res.data;
                localStorage.setItem('pokematos_pokemons', JSON.stringify(state.pokemons));
            }).catch( err => {
                //No error
            });
        },
        fetchCities( state ) {
            axios.get('/api/user/cities/').then( res => {
                state.cities = res.data;
                localStorage.setItem('pokematos_cities', JSON.stringify(state.cities));
                if (!state.currentCity || state.currentCity == undefined) {
                    state.currentCity = state.cities[0];
                    localStorage.setItem('pokematos_currentCity', JSON.stringify(state.cities[0]));
                }
            }).catch( err => {
                //No error
            });
        },
        fetchUser( state ) {
            axios.get('/api/user').then( res => {
                state.user = res.data
                localStorage.setItem('pokematos_user', JSON.stringify(res.data));
            }).catch( err => {
                //No error
            });
        },
        setCity( state, payload ) {
            state.currentCity = payload.city;
        },
        setSetting( state, payload ) {
            if( state.settings === undefined || !state.settings || state.settings === null ) state.settings = {};
            state.settings[payload.setting] = payload.value;
            localStorage.setItem('pokematos_settings', JSON.stringify(state.settings));
        }
    },
    getters: {
        activeRaids: state => {
            if( !state.gyms || state.gyms.length === 0 ) return [];
            return state.gyms.filter((gym) => {
                var now = moment();
                return now.isAfter(gym.raid.start_time) && now.isBefore(gym.raid.end_time);
            });
        },
        futureRaids: state => {
            if( !state.gyms || state.gyms.length === 0 ) return [];
            return state.gyms.filter((gym) => {
                var now = moment();
                if (gym.raid) {
                    var startTime = moment(gym.raid.start_time, '"YYYY-MM-DD HH:mm:ss"');
                }
                return gym.raid && startTime.isAfter(now);
            });
        },
        getSetting: state => (setting) => {
            if( state.settings && state.settings[setting] ) {
                return state.settings[setting];
            } else {
                return false;
            }
        },
    },
    actions: {
        fetchData ({ commit }) {
            commit('fetchGyms')
            commit('fetchPokemon')
            commit('fetchUser')
        },
        changeCity ({ dispatch, commit }, payload) {
            console.log(payload);
            commit('setCity', payload)
            dispatch('fetchData');
        },
    },
});

export default store;
