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
        snackbar: false,
    },
    mutations: {
        fetchGyms( state, payload ) {
            if( payload ) {
                state.snackbar = {
                    message: 'Synchronisation en cours',
                    timeout: 10000
                }
            }
            axios.get('/api/user/cities/'+state.currentCity.id+'/gyms').then( res => {
                state.gyms = res.data;
                localStorage.setItem('pokematos_gyms', JSON.stringify(state.gyms));
                if( payload ) {
                    state.snackbar = {
                        message: 'Synchronisation terminée',
                        timeout: 1000
                    }
                }
            }).catch( err => {
                if( payload ) {
                    state.snackbar = {
                        message: 'Erreur de synchronisation',
                        timeout: 1000
                    }
                }
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
                } else {
                    var newCurrentCity = res.data.find(function(city) {
                      return city.id == state.currentCity.id;
                    });
                    if( newCurrentCity ) {
                        state.currentCity = newCurrentCity;
                        localStorage.setItem('pokematos_currentCity', JSON.stringify(newCurrentCity));
                    } else {
                        state.currentCity = state.cities[0];
                        localStorage.setItem('pokematos_currentCity', JSON.stringify(state.cities[0]));
                    }

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
            localStorage.setItem('pokematos_currentCity', JSON.stringify(payload.city));
        },
        setPokemons( state, payload ) {
            state.pokemons = payload;
            localStorage.setItem('pokematos_pokemons', JSON.stringify(payload));
        },
        setSetting( state, payload ) {
            if( state.settings === undefined || !state.settings || state.settings === null ) state.settings = {};
            state.settings[payload.setting] = payload.value;
            localStorage.setItem('pokematos_settings', JSON.stringify(state.settings));
        },
        setSnackbar( state, payload ) {
            state.snackbar = payload;
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
        autoFetchData ({ commit }) {
            commit('fetchGyms')
            commit('fetchPokemon')
        },
        fetchData ({ commit }) {
            commit('fetchGyms', true)
        },
        changeCity ({ dispatch, commit }, payload) {
            commit('setCity', payload)
            dispatch('fetchData');
        },
    },
});

export default store;
