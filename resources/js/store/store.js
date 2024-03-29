import Vue from 'vue';
import Vuex from 'vuex';
import axios from 'axios';
import moment from 'moment';
import VuexPersistence from 'vuex-persist';

Vue.use(Vuex);

const defaultSettings = {
    mapFilters: ["empty_gyms", "active_gyms", "empty_stops", "active_stops"],
    raidsListFilters: ["1", "2", "3", "4", "5", "6", "7"],
    questsListFilters: [],
    raidsZoneFilters: [],
    questsZoneFilters: [],
    raidsListOrder: 'date',
    hideGyms: false,
    appaeranceMod: 'system'
}

const store = new Vuex.Store({
    state: {
        currentCity: [],
        cities: [],
        gyms: [],
        pokemons: [],
        quests: [],
        settings: {},
        user: {},
        zones: [],
        snackbar: false,
    },
    mutations: {
        fetchFeatures(state) {
            axios.get('/api/features').then(res => {
                state.features = res.data;
                localStorage.setItem('pokematos_features', JSON.stringify(state.features));
            });
        },
        fetchPokemon(state) {
            axios.get('/api/user/pokemon').then(res => {
                state.pokemons = res.data;
                //console.log(res.data);
                localStorage.setItem('pokematos_pokemons', JSON.stringify(state.pokemons));
            }).catch(err => {
                //No error
            });
        },
        fetchQuests(state) {
            axios.get('/api/user/quests').then(res => {
                state.quests = res.data;
                localStorage.setItem('pokematos_quests', JSON.stringify(state.quests));
            }).catch(err => {
                //No error
            });
        },
        fetchZones(state) {
            axios.get('/api/user/cities/' + state.currentCity.id + '/zones').then(res => {
                state.zones = res.data;
                localStorage.setItem('pokematos_zones', JSON.stringify(state.zones));
            }).catch(err => {
                //No error
            });
        },
        setCities(state, cities) {
            state.cities = cities;
            localStorage.setItem('pokematos_cities', JSON.stringify(state.cities));
            if (!state.currentCity || state.currentCity == undefined) {
                state.currentCity = state.cities[0];
                localStorage.setItem('pokematos_currentCity', JSON.stringify(state.cities[0]));
            } else {
                var newCurrentCity = cities.find(function (city) {
                    return city.id == state.currentCity.id;
                });
                if (newCurrentCity) {
                    state.currentCity = newCurrentCity;
                    localStorage.setItem('pokematos_currentCity', JSON.stringify(newCurrentCity));
                } else {
                    state.currentCity = state.cities[0];
                    localStorage.setItem('pokematos_currentCity', JSON.stringify(state.cities[0]));
                }

            }
        },
        setCity(state, currentCity) {
            var newCurrentCity = state.cities.find(function (city) {
                return city.id == currentCity.id;
            });
            if (newCurrentCity) {
                state.currentCity = newCurrentCity;
                localStorage.setItem('pokematos_currentCity', JSON.stringify(newCurrentCity));
            } else {
                state.currentCity = state.cities[0];
                localStorage.setItem('pokematos_currentCity', JSON.stringify(state.cities[0]));
            }
        },
        fetchUser(state) {
            axios.get('/api/user').then(res => {
                state.user = res.data
                localStorage.setItem('pokematos_user', JSON.stringify(res.data));
            }).catch(err => {
                //No error
            });
        },
        setPokemons(state, payload) {
            state.pokemons = payload;
            localStorage.setItem('pokematos_pokemons', JSON.stringify(payload));
        },
        setSetting(state, payload) {
            if (state.settings === undefined || !state.settings || state.settings === null || Array.isArray(state.settings)) {
                state.settings = {};
            }
            state.settings[payload.setting] = payload.value;
            localStorage.setItem('pokematos_settings', JSON.stringify(state.settings));
        },
        initSetting(state, payload) {
            if (state.settings === undefined || !state.settings || state.settings === null || Array.isArray(state.settings)) {
                state.settings = {};
            }
            if (state.settings[payload.setting]) {
                return;
            } else {
                state.settings[payload.setting] = payload.value;
                localStorage.setItem('pokematos_settings', JSON.stringify(state.settings));
            }
        },
        setSnackbar(state, payload) {
            state.snackbar = payload;
        },
        deletePOIActivity(state, payload) {
            let objIndex = state.gyms.findIndex((obj => obj.id == payload.id));
            let gym = state.gyms[objIndex];
            gym.raid = false;
            gym.quest = false;
            state.gyms = [
                ...state.gyms.filter(element => element.id !== gym.id),
                gym
            ];
            localStorage.setItem('pokematos_gyms', JSON.stringify(state.gyms));
        },
        setGyms(state, gyms) {
            console.log(gyms)
            if (!state.gyms || !Array.isArray(state.gyms)) {
                state.gyms = [];
            }
            gyms.forEach(function (gym) {
                state.gyms = [
                    ...state.gyms.filter(element => element.id !== gym.id),
                    gym
                ];
            });
            localStorage.setItem('pokematos_gyms', JSON.stringify(state.gyms));
        }
    },
    getters: {
        activeRaids: state => {
            if (!state.gyms || state.gyms.length === 0) return [];
            return state.gyms.filter((gym) => {
                var now = moment();
                return gym.raid && now.isAfter(gym.raid.start_time) && now.isBefore(gym.raid.end_time);
            });
        },
        futureRaids: state => {
            if (!state.gyms || state.gyms.length === 0) return [];
            return state.gyms.filter((gym) => {
                var now = moment();
                if (gym.raid) {
                    var startTime = moment(gym.raid.start_time, '"YYYY-MM-DD HH:mm:ss"');
                }
                return gym.raid && startTime.isAfter(now);
            });
        },
        activeQuests: state => {
            if (!state.gyms || state.gyms.length === 0) return [];
            return state.gyms.filter((gym) => {
                var now = moment();
                return (gym.quest && gym.quest.date == moment().format('YYYY-MM-DD') + ' 00:00:00');
            });
        },
        activeRocketInvasions: state => {
            if (!state.gyms || state.gyms.length === 0) return [];
            return state.gyms.filter((gym) => {
                var now = moment();
                return (gym.invasion && gym.invasion.date == moment().format('YYYY-MM-DD'));
            });
        },
        rewardQuests: state => {
            if (!state.quests || state.quests.length === 0) return [];
            let rewardQuests = [];
            state.quests.forEach(function (quest) {
                if (quest.rewards) {
                    quest.rewards.forEach(function (reward) {
                        if (!rewardQuests.includes(reward) && !reward.pokedex_id) {
                            rewardQuests.push(reward);
                        }
                    });
                }
            });
            return rewardQuests;
        },
        pokemonQuests: state => {
            if (!state.quests || state.quests.length === 0) return [];
            let pokemonQuests = [];
            state.quests.forEach(function (quest) {
                if (quest.rewards) {
                    quest.rewards.forEach(function (reward) {
                        if (pokemonQuests.filter(item => (item.id == reward.id)).length === 0 && reward.pokedex_id) {
                            pokemonQuests.push(reward);
                        }
                    });
                }
            });
            return pokemonQuests.sort(function (a, b) {
                if (a.name < b.name) {
                    return -1;
                }
                if (a.name > b.name) {
                    return 1;
                }
                return 0;
            })
        },
        getRaidBosses: state => {
            if (!state.pokemons || state.pokemons.length === 0) return [];
            return state.pokemons.filter((pokemon) => {
                return pokemon.boss == true && pokemon.boss_level >= 0 && (pokemon.boss_level <= 5 || pokemon.boss_level >= 7);
            });
        },
        getSetting: state => (setting) => {
            if (state.settings && state.settings[setting]) {
                return state.settings[setting];
            } else if( defaultSettings[setting] ) {
                console.log(defaultSettings[setting])
                return defaultSettings[setting];
            } else {
                return false;
            }
        },
        getGyms: state => {
            if (!state.gyms || state.gyms.length === 0) return [];
            return state.gyms.filter((gym) => {
                return (gym.gym);
            });
        },
    },
    actions: {
        initStore({
            commit,
            state,
            getters
        }) {
            console.log('initStore...');
            let items = ['settings', 'cities', 'currentCity', 'user', , 'quests', 'gyms', 'pokemons', 'zones'];
            items.forEach(function (item) {
                if (localStorage.getItem('pokematos_' + item)) {
                    try {
                        state[item] = JSON.parse(localStorage.getItem('pokematos_' + item));
                    } catch (e) {
                        localStorage.setItem('pokematos_' + item, '');
                    }
                } else {
                    state[item] = {};
                    localStorage.setItem('pokematos_' + item, '');
                }
            })
            //On met à jour toutes les arènes si elles sont KO
            if (localStorage.getItem('pokematos_gyms') == '') {
                commit('setSetting', {
                    setting: 'lastUpdate',
                    value: 'initial'
                });
            }
        },
        async fetchGyms({
            commit,
            state,
            getters
        }) {
            try {
                var user = await axios.get('/api/user');
                state.user = user.data;
                localStorage.setItem('pokematos_user', JSON.stringify(state.user));
            } catch (error) {
                console.log('tutututut')
                console.log(error)
                console.log(error.response)
                if ( error.response  && error.response.status == '401') {
                    document.location.reload(true);
                }
            }

            var cities = await axios.get('/api/user/cities/');
            commit('setCities', cities.data);
            commit('fetchZones');
            commit('fetchQuests');
            commit('fetchPokemon');

            var lastUpdate = getters.getSetting('lastUpdate');
            var result = await axios.get('/api/user/cities/' + state.currentCity.id + '/gyms?last_update=' + lastUpdate);
            commit('setGyms', result.data)
            if (lastUpdate == 'initial') {
                let updateDate = require('moment')().format('YYYY-MM-DD') + '00:00:00';
                var result = await axios.get('/api/user/cities/' + state.currentCity.id + '/gyms?last_update=' + updateDate);
                commit('setGyms', result.data)
            }

            commit('setSetting', {
                setting: 'lastUpdate',
                value: require('moment')().format('YYYY-MM-DD HH:mm:ss')
            });

        },
        async fetchData(context) {
            context.commit('setSnackbar', {
                message: 'Synchronisation en cours',
                timeout: 20000
            })
            await context.dispatch('fetchGyms')
            context.commit('setSnackbar', {
                message: 'Synchronisation terminée',
                timeout: 1500
            })
        },
        async changeCity({
            dispatch,
            commit,
            state
        }, city) {
            commit('setCity', city);
            state.gyms = [];
            localStorage.setItem('pokematos_gyms', []);
            let lastUpdate = require('moment')();
            var result = await axios.get('/api/user/cities/' + state.currentCity.id + '/gyms');
            commit('setGyms', result.data)
            commit('fetchZones')
            commit('fetchPokemon')

            let updateDate = require('moment')().format('YYYY-MM-DD') + '00:00:00';
            var result = await axios.get('/api/user/cities/' + state.currentCity.id + '/gyms?last_update=' + updateDate);
            commit('setGyms', result.data)

            commit('setSetting', {
                setting: 'lastUpdate',
                value: require('moment')().format('YYYY-MM-DD HH:mm:ss')
            });
        },
    },
});

export default store;
