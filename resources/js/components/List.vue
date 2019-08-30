<template>
    <div>

        <v-tabs v-model="tabs" fixed-tabs grow color="transparent" slider-color="white" class="">
            <v-tab href="#raids" class="primary--text">Raids</v-tab>
            <v-tab href="#quetes" class="primary--text">Quêtes</v-tab>
        </v-tabs>

        <v-tabs-items v-model="tabs">
            <v-tab-item value="raids">
                <div v-if="activeRaids.length > 0" class="raids__active">
                    <div class="section__title">Raids en cours</div>
                    <div class="raids__wrapper">
                        <div v-on:click="showModal(gym)" v-for="gym in activeRaids" class="raid__wrapper">
                            <div class="raid__img">
                                <img :src="getRaidImgUrl(gym.raid)">
                            </div>
                            <div class="raid__content">
                                <h3>
                                    <span v-if="gym.raid.ex">Raid EX de {{getRaidStartTime(gym.raid)}} à {{getRaidEndTime(gym.raid)}}</span>
                                    <span v-else>{{gym.raid.egg_level}}T de {{getRaidStartTime(gym.raid)}} à {{getRaidEndTime(gym.raid)}}</span>
                                    <span class="raid__timer active">
                                        <countdown :time="getRaidTimeLeft(gym.raid)" @end="$store.dispatch('fetchData')">
                                            <template slot-scope="props">Reste {{ props.totalMinutes }} min</template>
                                        </countdown>
                                    </span>
                                </h3>
                                <div class="raid__gym">
                                    <img src="https://d30y9cdsu7xlg0.cloudfront.net/png/4096-200.png">{{gym.zone.name}} - {{gym.name}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="futureRaids.length > 0" class="raids__future">
                    <div class="section__title">Raids à venir</div>
                    <div class="raids__wrapper">
                        <div v-on:click="showModal(gym)" v-for="gym in futureRaids" class="raid__wrapper">
                            <div class="raid__img">
                                <img :src="getRaidImgUrl(gym.raid)">
                            </div>
                            <div class="raid__content">
                                <h3>
                                    <span v-if="gym.raid.ex">Raid EX de {{getRaidStartTime(gym.raid)}} à {{getRaidEndTime(gym.raid)}}</span>
                                    <span v-else>{{gym.raid.egg_level}}T de {{getRaidStartTime(gym.raid)}} à {{getRaidEndTime(gym.raid)}}</span>
                                    <span class="raid__timer future">
                                        <countdown v-if="gym.raid.ex" :time="getRaidTimeLeft(gym.raid)" @end="$store.dispatch('fetchData')">
                                            <template slot-scope="props">{{ props.days }}j et {{ props.hours }}h</template>
                                        </countdown>
                                        <countdown v-else :time="getRaidTimeLeft(gym.raid)" @end="$store.dispatch('fetchData')">
                                            <template slot-scope="props">Dans {{ props.totalMinutes }} min</template>
                                        </countdown>
                                    </span>
                                </h3>
                                <div class="raid__gym">
                                    <img src="https://d30y9cdsu7xlg0.cloudfront.net/png/4096-200.png">{{gym.zone.name}} - {{gym.name}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="futureRaids.length === 0 &  activeRaids.length === 0" class="raids__empty hide">
                    <h3>Aucun raid pour le moment...</h3>
                    <div class="wrapper" v-if="raidsListFilters.length < 5">
                        <p>Elargissez vos critères pour voir s'il y a d'autres raids dans les environs</p>
                        <v-btn depressed @click="dialog = true">Modifier mes filtres</v-btn>
                    </div>
                    <img src="https://assets.profchen.fr/img/empty.png" />
                </div>

            </v-tab-item>
            <v-tab-item value="quetes">
                <div v-if="activeQuests.length > 0" class="raids__active">
                    <div class="section__title">Quêtes en cours</div>
                    <div class="raids__wrapper">
                        <div v-on:click="showModal(gym)" v-for="gym in activeQuests" class="raid__wrapper">
                            <div class="raid__img">
                                <img v-if="gym.quest.reward" :src="gym.quest.reward.thumbnail_url">
                                <img v-if="!gym.quest.reward" src="https://assets.profchen.fr/img/app/unknown.png">
                            </div>
                            <div class="raid__content">
                                <h3>
                                    <span>{{gym.quest.quest.name}}</span>
                                </h3>
                                <div class="raid__gym">
                                    <img src="https://d30y9cdsu7xlg0.cloudfront.net/png/4096-200.png">{{gym.zone.name}} - {{gym.name}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="activeQuests.length === 0" class="raids__empty hide">
                    <h3>Aucune quête pour le moment...</h3>
                    <div class="wrapper" v-if="raidsListFilters.length > 1">
                        <p>Elargissez vos critères pour voir s'il y a d'autres quêtes dans les environs</p>
                        <v-btn depressed @click="dialog = true">Modifier mes filtres</v-btn>
                    </div>
                    <img src="https://assets.profchen.fr/img/empty.png" />
                </div>
            </v-tab-item>
        </v-tabs-items>


        <v-dialog v-model="dialog" max-width="290" content-class="list-filters">
            <v-card v-if="tabs == 'raids'">
                <v-subheader>Ordre d'affichage</v-subheader>
                <v-card-text>
                    <select v-model="raidsListOrder">
                        <option v-for="orderOption in orderOptions" :value="orderOption.id">{{orderOption.name}}</option>
                    </select>
                </v-card-text>
                <v-subheader>Quels raids voir ?</v-subheader>
                <v-card-text>
                    <v-checkbox v-model="raidsListFilters" label="Raids ex" value="6"></v-checkbox>
                    <v-checkbox v-model="raidsListFilters" label="Raids 5 têtes" value="5"></v-checkbox>
                    <v-checkbox v-model="raidsListFilters" label="Raids 4 têtes" value="4"></v-checkbox>
                    <v-checkbox v-model="raidsListFilters" label="Raids 3 têtes" value="3"></v-checkbox>
                    <v-checkbox v-model="raidsListFilters" label="Raids 2 têtes" value="2"></v-checkbox>
                    <v-checkbox v-model="raidsListFilters" label="Raids 1 tête" value="1"></v-checkbox>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="primary" flat @click="dialog = false">Fermer</v-btn>
                </v-card-actions>
            </v-card>
            <v-card v-if="tabs == 'quetes'" max-width="290" content-class="list-filters">
                <v-subheader>Quels Pokémon voir ?</v-subheader>
                <v-card-text>
                    <v-checkbox v-model="questsListFilters" v-for="quest in pokemonQuests" :key="quest.id" :label="quest.pokemon.name_fr" :value="quest.id"></v-checkbox>
                </v-card-text>
                <v-subheader>Quels objets voir ?</v-subheader>
                <v-card-text>
                    <v-checkbox v-model="questsListFilters" v-for="quest in rewardQuests" :key="quest.id" :label="quest.reward.name" :value="quest.id"></v-checkbox>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="primary" flat @click="dialog = false">Fermer</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <button-actions @showfilters="dialog = true"></button-actions>
        <gym-modal ref="gymModal"></gym-modal>
    </div>
</template>

<script>
    import moment from 'moment';
    export default {
        name: 'List',
        props: ['gyms'],
        data() {
            return {
                toto: [],
                dialog:false,
                tabs: 'raids',
                orderOptions: [{id:'date', name:'Date'}, {id:'level', name:'Niveau de Boss'}]
            }
        },
        computed: {
            pokemonQuests() {
                return this.$store.getters.pokemonQuests;
            },
            rewardQuests() {
                return this.$store.getters.rewardQuests;
            },
            activeRaids() {
                const that = this;
                return this.$store.getters.activeRaids.sort(this.compare).filter(function(gym) {
                    let isEmpty = ( that.raidsListFilters.length == 0 ) ? true : false;
                    let inArray = ( that.raidsListFilters.includes( gym.raid.egg_level.toString()) ) ? true : false;
                    return isEmpty || inArray;
                });;
            },
            futureRaids() {
                const that = this;
                return this.$store.getters.futureRaids.sort(this.compare).filter(function(gym) {
                    let isEmpty = ( that.raidsListFilters.length == 0 ) ? true : false;
                    let inArray = ( that.raidsListFilters.includes( gym.raid.egg_level.toString()) ) ? true : false;
                    return isEmpty || inArray;
                });
            },
            activeQuests() {
                const that = this;
                return this.$store.getters.activeQuests.filter(function(gym) {
                    let isEmpty = ( that.questsListFilters.length == 0 ) ? true : false;
                    let inArray = ( that.questsListFilters.includes(gym.quest.quest.id) ) ? true : false;
                    return isEmpty || inArray;
                });;
            },
            raidsListOrder: {
                get: function () {
                    return this.$store.getters.getSetting('raidsListOrder');
                },
                set: function (newValue) {
                    this.$store.commit('setSetting', {
                        setting: 'raidsListOrder',
                        value: newValue
                    });
                }
            },
            raidsListFilters: {
                get: function () {
                    return this.$store.getters.getSetting('raidsListFilters');
                },
                set: function (newValue) {
                    this.$store.commit('setSetting', {
                        setting: 'raidsListFilters',
                        value: newValue
                    });
                }
            },
            questsListFilters: {
                get: function () {
                    return this.$store.getters.getSetting('questsListFilters');
                },
                set: function (newValue) {
                    this.$store.commit('setSetting', {
                        setting: 'questsListFilters',
                        value: newValue
                    });
                }
            }
        },
        created() {
            this.$store.commit('initSetting', {
                setting: 'raidsListFilters',
                value: ["1","2","3","4","5","6"]
            });
            this.$store.commit('initSetting', {
                setting: 'questsListFilters',
                value: []
            });
            this.$store.commit('initSetting', {
                setting: 'raidsListOrder',
                value: 'date'
            });
        },
        methods: {
            compare(a, b) {
                console.log(this.raidsListOrder);
                if( this.raidsListOrder == 'date' ) {
                    return (a.raid.start_time > b.raid.start_time) ? 1 : -1;
                } else {
                    return (a.raid.egg_level > b.raid.egg_level) ? -1 : 1;
                }
            },
            showModal( gym ) {
                this.$refs.gymModal.showModal( gym );
            },
            getRaidImgUrl( raid ) {
                var now = moment();
                var raidStatus = 'future';
                if( now.isAfter(raid.start_time) ) {
                    raidStatus = 'active';
                }
                if( raidStatus == 'future' || !raid.pokemon ) {
                    return 'https://assets.profchen.fr/img/eggs/egg_'+raid.egg_level+'.png';
                } else {
                    return raid.pokemon.thumbnail_url;
                }
            },
            getRaidStartTime( raid ) {
                var startTime = moment(raid.start_time);
                return startTime.format('HH[h]mm');
            },
            getRaidEndTime( raid ) {
                var endTime = moment(raid.end_time);
                return endTime.format('HH[h]mm');
            },
            getRaidTimeLeft( raid ) {
                var now = moment();
                var start_time = moment(raid.start_time, '"YYYY-MM-DD HH:mm:ss"');
                var end_time = moment(raid.end_time, '"YYYY-MM-DD HH:mm:ss"');
                var raidStatus = 'future';
                if( now.isAfter(raid.start_time) ) {
                    raidStatus = 'active';
                }

                if( raidStatus == 'future' ) {
                    return parseInt(start_time.diff(now, 'milliseconds'));
                } else {
                    return parseInt(end_time.diff(now, 'milliseconds'));
                }
            }
        }
    }
</script>
