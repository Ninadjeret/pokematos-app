<template>
    <v-dialog v-model="dialog" max-width="90%" content-class="gym-modal">
        <v-card v-if="gym">
            <div class="dialog__wrap">


            <div v-if="modalScreen == 'default'" class="modal__screen default">
                <div v-if="gym.ex" class="gym__ex">Arêne EX</div>
                <h3 class="dialog__title">{{gym.name}}</h3>
                <p v-if="gym.zone" class="dialog__city">{{gym.zone.name}}</p>
                <hr>
                <div :class="'dialog__egg '+raidStatus">
                    <p>
                        <span class="annonce">{{raidAnnonce}}</span>
                        <span class="source"></span>
                    </p>
                    <img :src="raidUrl">
                    <span v-if="timeLeft && timeLeft > 0" class="dialog__counter">
                        <countdown :time="timeLeft"  v-on:end="getRaidData()">
                            <template slot-scope="props">{{ props.totalMinutes }}:{{ props.seconds }}</template>
                        </countdown>
                    </span>
                </div>
                <hr>
                <div class="dialog__content">
                    <ul>
                        <li v-if="gym.google_maps_url"><a class="modal__action" :href="gym.google_maps_url"><i class="material-icons">navigation</i><span>Itinéraire vers l'arène</span></a></li>
                        <li v-if="raidStatus == 'active' && gym.raid.pokemon == false"><a class="modal__action create-raid" v-on:click="setScreenTo('updateRaid')"><i class="material-icons">fingerprint</i><span>Préciser le Pokémon</span></a></li>
                        <li v-if="raidStatus == 'none'"><a class="modal__action create-raid" v-on:click="setScreenTo('createRaid')"><i class="material-icons">add_alert</i><span>Annoncer un raid</span></a></li>
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
                    <input v-model="createRaidData.delai" v-on:change="updateTimeRange()" type="range" class="range" min="-60" max="45" step="1" data-orientation="horizontal">
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
            createRaidDelai: '',
            createRaidHoraires: '',
            raidLevels: [1,2,3,4,5],
            startTime: false,
            endTime: false,
        }
    },
    computed: {
        pokemons() {
            return this.$store.state.pokemons;
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
        }
    },
    created() {
        this.updateTimeRange();
    },
    methods: {
        showModal( gym ) {
            this.gym = gym;
            this.dialog = true;
            this.getRaidData();
        },
        hideModal() {
            this.dialog = false;
        },
        setScreenTo( value ) {
            console.log(value);
            this.modalScreen = value;
        },
        addToTimeRange() {
            this.createRaidData.delai += 1;
            this.updateTimeRange();
        },
        substractToTimeRange() {
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
                this.raidUrl = 'https://assets.profchen.fr/img/eggs/egg_0.png';
            } else if( this.raidStatus == 'future' && this.startTime ) {
                this.timeLeft = parseInt(this.startTime.diff(now, 'milliseconds'));
                this.raidAnnonce = 'Un oeuf '+this.gym.raid.egg_level+' têtes va bientot éclore...';
                this.raidUrl = 'https://assets.profchen.fr/img/eggs/egg_'+this.gym.raid.egg_level+'.png';
            } else if( !this.gym.raid.pokemon && this.endTime ) {
                this.timeLeft = parseInt(this.endTime.diff(now, 'milliseconds'));
                this.raidAnnonce = 'Un raid '+this.gym.raid.egg_level+' têtes est en cours...';
                this.raidUrl = 'https://assets.profchen.fr/img/eggs/egg_'+this.gym.raid.egg_level+'.png';
            } else if( this.endTime ) {
                this.timeLeft = parseInt(this.endTime.diff(now, 'milliseconds'));
                this.raidAnnonce = 'Un raid '+this.gym.raid.pokemon.niantic_id+' têtes est en cours...';
                this.raidUrl =  this.gym.raid.pokemon.thumbnail_url;
            }
        },
        postNewRaid() {
            this.setScreenTo('default');
            this.hideModal();
            axios.post('/api/user/cities/1/raids', {
                 params: {
                     gym_id: this.gym.id,
                     pokemon_id: this.createRaidData.pokemon.id,
                     egg_level: this.createRaidData.eggLevel,
                     start_time: this.createRaidData.startTime
                 },
            }).then(res => {
                this.$store.dispatch('fetchData');
            }).catch(err => {
                console.log(err)
            });
        },
        postUpdateRaid() {
            this.setScreenTo('default');
            this.hideModal();
            axios.put('/api/user/cities/1/raids/'+this.gym.raid.id, {
                 params: {
                     pokemon_id: this.createRaidData.pokemon.id,
                 },
            }).then(res => {
                this.$store.dispatch('fetchData');
            }).catch(err => {
                console.log(err)
            });
        }
    }
}
</script>
