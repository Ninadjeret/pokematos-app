<template>
    <v-dialog v-model="dialog" max-width="90%" :content-class="contentClass">
        <v-card v-if="gym">
            <div class="dialog__wrap">


            <div v-if="modalScreen == 'default'" class="modal__screen default">
                <div v-if="gym.ex" class="gym__ex">Arêne EX</div>
                <h3 class="dialog__title">{{gym.name}}</h3>
                <p v-if="gym.zone" class="dialog__city">{{gym.zone.name}}</p>
                <hr>
                <div v-if="!gym.gym" class="dialog__egg quest">
                    <p>
                        <span v-if="!gym.quest" class="annonce">Aucune quête signalée</span>
                        <span v-if="gym.quest && gym.quest.name" class="annonce">Quête <strong>{{gym.quest.name}}</strong> en cours</span>
                    </p>
                    <img v-if="!gym.quest" src="https://assets.profchen.fr/img/app/egg_0.png">
                    <img v-if="gym.quest && gym.quest.reward" :src="gym.quest.reward.thumbnail_url">
                    <img v-if="gym.quest && !gym.quest.reward" src="https://assets.profchen.fr/img/app/unknown.png">
                </div>
                <div v-if="gym.gym" :class="'dialog__egg '+raidStatus">
                    <p>
                        <span class="annonce">{{raidAnnonce}}</span>
                        <span class="source"></span>
                    </p>
                    <img :src="raidUrl">
                    <span v-if="timeLeft && timeLeft > 0" class="dialog__counter">
                        <div v-if="gym.raid.ex == 1">
                            <countdown :time="timeLeft"  v-on:end="getRaidData()">
                                <template slot-scope="props">{{ props.days }}j {{ props.hours }}h et {{ props.minutes }}min</template>
                            </countdown>
                        </div>
                        <div v-if="gym.raid.ex == 0">
                            <countdown :time="timeLeft"  v-on:end="getRaidData()">
                                <template slot-scope="props">{{ props.totalMinutes }}:{{ props.seconds }}</template>
                            </countdown>
                        </div>
                    </span>
                </div>
                <hr>
                <div class="dialog__content">
                    <ul>
                        <li v-if="raidStatus == 'active' && gym.raid.pokemon == false && !gym.raid.ex"><a class="modal__action create-raid" v-on:click="setScreenTo('updateRaid')"><i class="material-icons">fingerprint</i><span>Préciser le Pokémon</span></a></li>
                        <li v-if="raidStatus == 'none' && gym.gym"><a class="modal__action create-raid" v-on:click="setScreenTo('createRaid')"><i class="material-icons">add_alert</i><span>Annoncer un raid</span></a></li>
                        <li v-if="!gym.quest && !gym.gym"><a class="modal__action create-quest" v-on:click="setScreenTo('createQuest')"><i class="material-icons">explore</i><span>Annoncer une quête</span></a></li>
                        <li v-if="gym.quest && ( !gym.quest.quest_id || !gym.quest.reward_type )"><a class="modal__action update-quest" v-on:click="setScreenTo('updateQuest')"><i class="material-icons">fingerprint</i><span>Préciser la quête</span></a></li>
                        <li v-if="gym.quest && !gym.gym"><a class="modal__action create-quest" v-on:click="deleteQuestConfirm()"><i class="material-icons">delete</i><span>Supprimer la quête</span></a></li>
                        <li v-if="raidStatus == 'none' && gym.ex === true"><a class="modal__action create-raid-ex" v-on:click="setScreenTo('createRaidEx')"><i class="material-icons">star</i><span>Annoncer un raid EX</span></a></li>
                        <li v-if="gym.raid && canDeleteRaid()"><a class="modal__action delete-raid" v-on:click="deleteRaidConfirm()"><i class="material-icons">delete</i><span>Supprimer le raid</span></a></li>
                        <li v-if="gym.google_maps_url && gym.gym"><a class="modal__action" :href="gym.google_maps_url"><i class="material-icons">navigation</i><span>Itinéraire vers l'arène</span></a></li>
                        <li v-if="gym.google_maps_url && !gym.gym"><a class="modal__action" :href="gym.google_maps_url"><i class="material-icons">navigation</i><span>Itinéraire vers le Pokéstop</span></a></li>
                    </ul>
                </div>
                <div class="footer--actions">
                    <button class="button--close" v-on:click="hideModal()"><i class="material-icons">close</i></button>
                </div>
            </div>


            <div v-if="modalScreen == 'updateRaid'" class="modal__screen update-raid">
                <h3 class="">Préciser le Pokémon</h3>
                <hr>
                <div class="update-raid__wrapper">
                    <ul v-if="pokemons">
                        <li v-for="pokemon in pokemons" :key="pokemon.id" v-if="gym.raid.egg_level == pokemon.boss_level">
                            <a v-on:click="updateRaidBoss(pokemon)">
                                <img :src="pokemon.thumbnail_url">
                            </a>
                        </li>
                    </ul>
                </div>
                <hr>
                <div class="footer-action">
                    <a v-on:click="setScreenTo('default')" class="bt modal__action cancel">Annuler</a>
                </div>
            </div>

            <div v-if="modalScreen == 'createRaidEx'" class="modal__screen create-raid-ex">
                <h3 class="">Annoncer un raid EX</h3>
                <hr>
                <div class="update-raid__wrapper">
                    <p class="step__title">Quel jour ?</p>
                    <v-date-picker v-model="exDate" locale="fr-fr" :first-day-of-week="1" full-width :min="exBeginDate" :max="exEndDate"></v-date-picker>
                </div>
                <hr>
                    <p class="step__title">A quelle heure ?</p>
                    <v-layout>
                        <v-flex xs6>
                            <select dir="rtl" class="hour" v-if="exAllowedHours" v-model="exHour">
                                <option v-for="hour in exAllowedHours" :value="hour">{{hour}}h</option>
                            </select>
                        </v-flex>
                        <v-flex xs6>
                            <select class="minutes" v-if="exAllowedMinutes" v-model="exMinutes">
                                <option v-for="minutes in exAllowedMinutes" :value="minutes">{{minutes}}</option>
                            </select>
                        </v-flex>
                    </v-layout>
                <hr>
                <div class="footer-action">
                    <a v-on:click="postNewRaidEx()" class="bt modal__action cancel">Confirmer</a>
                    <a v-on:click="setScreenTo('default')" class="bt modal__action cancel">Annuler</a>
                </div>
            </div>

            <div v-if="modalScreen == 'createQuest'" class="modal__screen create-quest">
                <h3 class="">Annoncer une quête</h3>
                <div v-if="!questToSubmit" class="search__wrapper">
                    <v-text-field single-line hide-details outline v-model="questSearch" label="Recherche"></v-text-field>
                </div>
                <p v-if="questToSubmit" class="step__title">Quelle est la quête ?</p>
                <v-list>
                <template v-for="(quest, index) in filteredQuests">
                  <v-list-tile :key="quest.id" @click="clickQuest(quest)">
                    <v-list-tile-content>
                      <v-list-tile-title>
                          {{quest.name}}
                      </v-list-tile-title>
                    </v-list-tile-content>
                    <v-avatar v-if="!questToSubmit">
                        <img :src="quest.rewards[0].thumbnail_url">
                        <span v-if="quest.rewards.length > 1" class="rewards_badge">
                            +{{quest.rewards.length - 1}}
                        </span>
                    </v-avatar>
                    <v-avatar v-if="questToSubmit">
                        <v-btn flat icon>
                            <v-icon>close</v-icon>
                        </v-btn>
                    </v-avatar>
                  </v-list-tile>
                  <v-divider></v-divider>
                </template>
              </v-list>
              <hr>
              <div v-if="questToSubmit" class="step quest_rewards" data-step-name="boss">
                  <p class="step__title">Quelle est la récompense ?</p>
                  <div class="step__wrapper">
                      <ul>
                          <li v-for="reward in questToSubmit.rewards" :key="reward.id">
                              <a @click="postNewQuest(questToSubmit.id, reward)">
                                  <img :src="reward.thumbnail_url">
                              </a>
                          </li>
                      </ul>
                      <a class="bt" @click="postNewQuest(questToSubmit.id, false)">Je ne sais pas encore</a>
                  </div>
              </div>

                <div class="footer-action">
                    <a v-on:click="setScreenTo('default')" class="bt modal__action cancel">Annuler</a>
                </div>
            </div>

            <div v-if="modalScreen == 'updateQuest'" class="modal__screen update-quest">
                <div v-if="!gym.quest.reward_id">
                    <h3 class="">Préciser la récompense</h3>
                    <p class="dialog__city">{{gym.quest.name}}</p>
                    <hr>
                    <div class="update-raid__wrapper">
                        <ul v-if="pokemons">
                            <li v-for="reward in gym.quest.quest.rewards" :key="reward.name">
                                <a v-on:click="updateQuest(gym.quest.id, reward, false)">
                                    <img :src="reward.thumbnail_url">
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <hr>
                <div class="footer-action">
                    <a v-on:click="setScreenTo('default')" class="bt modal__action cancel">Annuler</a>
                </div>
            </div>

            <div v-if="modalScreen == 'createRaid'" class="modal__screen create-raid">
                <h3 class="">Annoncer un raid</h3>

                <hr>
                <div class="step" data-step-num="1" data-step-name="timer">
                    <p class="step__title">A quelle heure commence-t-il ?</p>
                    <button v-on:click="substractToTimeRange()" class="range_button" id="range__minus"><i class="material-icons">remove</i></button>
                    <p class="step__timer" data-starttime="">
                        <span v-if="createRaidDelai" class="step__timer--delai">{{createRaidDelai}}</span><br>
                        <span v-if="createRaidHoraires" class="step__timer--horaires">{{createRaidHoraires}}</span>
                    </p>
                    <button v-on:click="addToTimeRange()" class="range_button" id="range__plus"><i class="material-icons">add</i></button>
                    <input v-model="createRaidData.delai" v-on:change="updateTimeRange()" @input="updateTimeRange()" type="range" class="range" min="-60" max="45" step="1" data-orientation="horizontal">
                </div>

                <hr>
                <div class="step" data-step-num="2" data-step-name="level" data-validate="oui">
                    <p class="step__title">Quel est son niveau ?</p>
                    <div class="step__wrapper">
                        <ul>
                            <li v-for="raidLevel in raidLevels" v-on:click="updateRaidLevel(raidLevel)"><button data-level="raidLevel">{{raidLevel}}T</button></li>
                        </ul>
                    </div>
                </div>

                <hr>
                <div v-if="createRaidData.delai >= 0" class="step" data-step-num="3b" data-step-name="boss">
                    <p class="step__title">Quel est le Pokémon ?</p>
                    <div class="step__wrapper">
                        <ul v-if="pokemons">
                            <li v-for="pokemon in pokemons" :key="pokemon.id" v-if="createRaidData.eggLevel === 0 || createRaidData.eggLevel == pokemon.boss_level">
                                <a v-on:click="updateRaidBoss(pokemon)">
                                    <img :src="pokemon.thumbnail_url">
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="footer-action">
                    <a v-on:click="setScreenTo('default')" class="bt modal__action cancel">Annuler</a>
                </div>
            </div>


        </div>
    </v-card>
</v-dialog>
</template>

<script>
import moment from 'moment';
export default {
    data() {
        return {
            dialog: false,
            modalScreen: 'default',
            gym: '',
            raidUrl: '',
            raidAnnonce: '',
            timeLeft: false,
            createRaidData: {
                delai: -60,
                startTime: false,
                eggLevel: 0,
                pokemon: false,
            },
            createRaidDelai: 0,
            createRaidHoraires: '',
            raidLevels: [1,2,3,4,5],
            startTime: false,
            endTime: false,
            exAllowedHours: [9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20],
            exAllowedMinutes: [0, 15, 30, 45],
            exDate: new Date().toISOString().substr(0, 10),
            exHour: 13,
            exMinutes: 0,
            questSearch: null,
            questToSubmit: false,
        }
    },
    computed: {
        pokemons() {
            return this.$store.getters.getRaidBosses;
        },
        user() {
            return this.$store.state.user;
        },
        currentCity() {
            return this.$store.state.currentCity;
        },
        filteredQuests() {
            return this.$store.state.quests.filter((quest) => {
                if( quest.rewards.length == 0 ) return false;
                if( this.questToSubmit && this.questToSubmit.id != quest.id ) return false;
                let matchingTitle = 1;
                let matchingPokemon = 1;
                if (this.questSearch != null) {
                    matchingTitle = quest.name.toLowerCase().indexOf(this.questSearch.toLowerCase()) > -1;
                    matchingPokemon = false;
                    quest.rewards.forEach((reward, index) => {
                        if( reward.name.toLowerCase().indexOf(this.questSearch.toLowerCase()) > -1 ) {
                            matchingPokemon = true;
                        }
                    })
                }
                return (matchingTitle || matchingPokemon);
            });
        },
        raidStatus() {
            if( this.gym.raid ) {
                var now = moment();
                this.startTime = moment(this.gym.raid.start_time, '"YYYY-MM-DD HH:mm:ss"');
                this.endTime = moment(this.gym.raid.end_time, '"YYYY-MM-DD HH:mm:ss"');
                if( this.startTime.isAfter(now) ) {
                    return 'future';
                } else if( this.endTime.isAfter(now) ) {
                    return 'active';
                }
            } else {
                return 'none';
            }
        },
        exBeginDate () {
            return moment().format('YYYY-MM-DD')
        },
        exEndDate () {
            return moment().add(14, 'days').format('YYYY-MM-DD')
        },
        contentClass() {
            let isGym = (this.gym.gym) ? 'gym' : 'stop' ;
            let isEx = (this.gym.ex) ? 'ex' : '' ;
            return 'gym-modal '+isGym+' '+isEx;
        }
    },
    created() {
        this.updateTimeRange();
    },
    methods: {
        showModal( gym ) {
            this.gym = gym;
            this.dialog = true;
            this.createRaidData.pokemon = false;
            this.getRaidData();
        },
        hideModal() {
            this.dialog = false;
        },
        canDeleteRaid() {
            let permissions = this.user.permissions;
            return ( permissions[this.currentCity.guilds[0].id].find(val => val === 'raid_delete' ) );
        },
        setScreenTo( value ) {
            console.log(value);
            this.modalScreen = value;
        },
        addToTimeRange() {
            this.createRaidData.delai = parseInt(this.createRaidData.delai);
            this.createRaidData.delai += 1;
            this.updateTimeRange();
        },
        substractToTimeRange() {
            this.createRaidData.delai = parseInt(this.createRaidData.delai);
            this.createRaidData.delai -= 1;
            this.updateTimeRange();
        },
        updateTimeRange() {
            var raidStartTime = moment();
            var raidEndTime = moment();
            if( this.createRaidData.delai >= 0 ) {
                var timeLeft = 45 - this.createRaidData.delai;
                raidStartTime.subtract(this.createRaidData.delai, 'minutes').minutes();
                raidEndTime.add( parseInt(timeLeft), 'minutes').minutes();
                this.createRaidData.startTime = raidStartTime.format('YYYY-MM-DD HH:mm:ss');
                this.createRaidDelai = 'Raid en cours. Reste ' + timeLeft + ' min';
                this.createRaidHoraires = 'De ' + raidStartTime.format('HH[h]mm') + ' à ' + raidEndTime.format('HH[h]mm');
            } else {
                var timeLeft = Math.abs(this.createRaidData.delai);
                raidStartTime.add(timeLeft, 'minutes').minutes();
                raidEndTime.add( parseInt(timeLeft) + parseInt(45), 'minutes').minutes();
                this.createRaidData.startTime = raidStartTime.format('YYYY-MM-DD HH:mm:ss');
                this.createRaidDelai = 'Le raid débute dans ' + timeLeft + ' min';
                this.createRaidHoraires = 'De ' + raidStartTime.format('HH[h]mm') + ' à ' + raidEndTime.format('HH[h]mm');
            }
        },
        updateRaidLevel( raidLevel ) {
            this.createRaidData.eggLevel = raidLevel;
            if( this.createRaidData.delai < 0 ) {
                var result = confirm('Confirmer un raid '+this.createRaidData.eggLevel+'T à l\'arène '+this.gym.name);
                if( result ) {
                    this.postNewRaid();
                }
            }
        },
        updateRaidBoss( pokemon ) {
            this.createRaidData.pokemon = pokemon;
            if( this.gym.raid ) {
                var result = confirm('Annoncer '+this.createRaidData.pokemon.name_fr+' comme Boss pour le raid à l\'arène '+this.gym.name);
                if( result ) {
                    this.postUpdateRaid();
                }
            } else {
                var result = confirm('Confirmer un raid '+this.createRaidData.pokemon.name_fr+' à l\'arène '+this.gym.name);
                if( result ) {
                    this.postNewRaid();
                }
            }
        },
        getRaidData() {
            var now = moment();

            //Url
            if( this.raidStatus == 'none' ) {
                this.timeLeft = false;
                this.raidAnnonce = 'Rien pour le moment...';
                this.raidUrl = 'https://assets.profchen.fr/img/app/egg_0.png';
            } else if( this.raidStatus == 'future' && this.startTime ) {
                this.timeLeft = parseInt(this.startTime.diff(now, 'milliseconds'));
                if( this.gym.raid.ex ) {
                    this.raidAnnonce = 'Un raid EX va avoir lieu ici prochainement';
                    this.raidUrl = 'https://assets.profchen.fr/img/eggs/egg_'+this.gym.raid.egg_level+'.png';
                } else {
                    this.raidAnnonce = 'Un oeuf '+this.gym.raid.egg_level+' têtes va bientot éclore...';
                    this.raidUrl = 'https://assets.profchen.fr/img/eggs/egg_'+this.gym.raid.egg_level+'.png';
                }

            } else if( !this.gym.raid.pokemon && this.endTime ) {
                this.timeLeft = parseInt(this.endTime.diff(now, 'milliseconds'));
                this.raidAnnonce = 'Un raid '+this.gym.raid.egg_level+' têtes est en cours...';
                this.raidUrl = 'https://assets.profchen.fr/img/eggs/egg_'+this.gym.raid.egg_level+'.png';
                if( this.gym.raid.ex ) this.raidAnnonce = 'Un raid EX est en cours...';
            } else if( this.endTime ) {
                this.timeLeft = parseInt(this.endTime.diff(now, 'milliseconds'));
                this.raidAnnonce = 'Un raid '+this.gym.raid.pokemon.name_fr+' est en cours...';
                this.raidUrl =  this.gym.raid.pokemon.thumbnail_url;
            }
        },
        deleteRaidConfirm() {
            var result = confirm('Supprimer le raid '+this.gym.raid.egg_level+'T à l\'arène '+this.gym.name);
            if( result ) {
                this.deleteRaid();
            }

        },
        deleteQuestConfirm() {
            var result = confirm('Supprimer la quête actuelle au Pokéstop '+this.gym.name);
            if( result ) {
                this.deleteQuest();
            }

        },
        postNewRaid() {
            this.setScreenTo('default');
            this.hideModal();
            axios.post('/api/user/cities/'+this.currentCity.id+'/raids', {
                 params: {
                     gym_id: this.gym.id,
                     pokemon_id: this.createRaidData.pokemon.id,
                     egg_level: this.createRaidData.eggLevel,
                     start_time: this.createRaidData.startTime
                 },
            }).then(res => {
                console.log(res.data);
                this.$store.dispatch('fetchData');
            }).catch(err => {
                console.log(err)
            });
        },
        postNewRaidEx() {
            this.setScreenTo('default');
            this.hideModal();
            axios.post('/api/user/cities/'+this.currentCity.id+'/raids', {
                 params: {
                     gym_id: this.gym.id,
                     pokemon_id: false,
                     egg_level: 6,
                     start_time: this.exDate+' '+this.exHour+':'+this.exMinutes+':00',
                     ex: true,
                 },
            }).then(res => {
                console.log(res.data);
                this.$store.dispatch('fetchData');
            }).catch(err => {
                console.log(err)
            });
        },
        postNewQuest(questId, reward) {
            var result = confirm('Confirmer le signalement de quete pour le pokéstop '+this.gym.name);
            if( result ) {
                this.setScreenTo('default');
                this.hideModal();
                let reward_type = false;
                if( reward.pokedex_id ) {
                    reward_type = 'pokemon';
                } else if( reward.name ) {
                    reward_type = 'reward';
                }
                let reward_id = ( reward ) ? reward.id : false;
                this.$store.commit('setSnackbar', {
                    message: 'Création de la quête',
                    timeout: 1500
                });
                axios.post('/api/user/cities/'+this.currentCity.id+'/quests', {
                     params: {
                         gym_id: this.gym.id,
                         quest_id: questId,
                         reward_type: reward_type,
                         reward_id: reward_id,
                     },
                }).then(res => {
                    console.log(res.data);
                    this.$store.dispatch('fetchData');
                }).catch(err => {
                    console.log(err)
                });
            }
        },
        postUpdateRaid() {
            this.setScreenTo('default');
            this.hideModal();
            axios.put('/api/user/cities/'+this.currentCity.id+'/raids/'+this.gym.raid.id, {
                 params: {
                     gym_id: this.gym.id,
                     pokemon_id: this.createRaidData.pokemon.id,
                 },
            }).then(res => {
                this.$store.dispatch('fetchData');
            }).catch(err => {
                console.log(err)
            });
        },

        deleteRaid() {
            this.setScreenTo('default');
            this.hideModal();
            axios.delete('/api/user/cities/'+this.currentCity.id+'/raids/'+this.gym.raid.id).then(res => {
                this.$store.dispatch('fetchData');
                this.$store.commit('setSnackbar', {
                    message: 'Raid supprimé',
                    timeout: 1500
                });
            }).catch(err => {
                console.log(err)
            });
        },
        deleteQuest() {
            this.setScreenTo('default');
            this.hideModal();
            axios.delete('/api/user/cities/'+this.currentCity.id+'/quests/'+this.gym.quest.id).then(res => {
                this.$store.dispatch('fetchData');
                this.$store.commit('setSnackbar', {
                    message: 'Quête supprimée',
                    timeout: 1500
                });
            }).catch(err => {
                console.log(err)
            });
        },
        clickQuest(quest) {
            if( !this.questToSubmit  ) {
                if( quest.rewards.length === 1 ) {
                    this.postNewQuest(quest.id, quest.rewards[0])
                } else {
                    this.questToSubmit = quest;
                }
            } else {
                this.questToSubmit = false;
            }
        },
        updateQuest( instanceId, reward, quest ) {
            this.setScreenTo('default');
            this.hideModal();
            this.$store.commit('setSnackbar', {
                message: 'Mise à jour de la quête',
                timeout: 1500
            });
            if( reward ) {
                axios.put('/api/user/cities/'+this.currentCity.id+'/quests/'+instanceId, {
                    params: {
                        reward_type: (reward.pokedex_id) ? 'pokemon' : 'reward' ,
                        reward_id: reward.id,
                    }
                }).then(res => {
                    this.$store.dispatch('fetchData');

                }).catch(err => {
                    console.log(err)
                });
            }
        }
    }
}
</script>
