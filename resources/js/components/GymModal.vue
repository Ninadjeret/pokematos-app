<template>
        <modal v-if="gym" name="GymModal">
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
                        <li v-if="raidStatus == 'none'"><a class="modal__action create-raid" v-on:click="setScreenTo('createRaid')"><i class="material-icons">add_alert</i><span>Annoncer un raid</span></a></li>
                    </ul>
                </div>
                <div class="footer--actions">
                    <button class="button--close" v-on:click="hideModal()"><i class="material-icons">close</i></button>
                </div>
            </div>



            <div v-if="modalScreen == 'createRaid'" class="modal__screen create-raid">
                <h3 class="">Annoncer un raid</h3>

                <hr>
                <div class="step" data-step-num="1" data-step-name="timer">
                    <p class="step__title">A quelle heure commence-t-il ?</p>
                    <button v-on:click="createRaidData.delai-1" class="range_button" id="range__minus"><i class="material-icons">remove</i></button>
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
                            <li v-for="raidLevel in raidLevels"><button data-level="raidLevel">{{raidLevel}}T</button></li>
                        </ul>
                    </div>
                </div>

                <hr>
                <div v-if="createRaidData.delai >= 0" class="step" data-step-num="3b" data-step-name="boss">
                    <p class="step__title">Quel est le Pokémon ?</p>
                    <div class="step__wrapper">
                    </div>
                </div>

                <div class="footer-action">
                    <a v-on:click="setScreenTo('default')" class="bt modal__action cancel">Annuler</a>
                </div>
            </div>


        </div>
        </modal>
</template>

<script>
import moment from 'moment';
export default {
    data() {
        return {
            modalScreen: 'default',
            gym: '',
            raidStatus: 'none',
            raidUrl: '',
            raidAnnonce: '',
            timeLeft: false,
            createRaidData: {
                delai: -60,
                startTime: false,
                eggLevel: false,
                pokemon: false,
            },
            createRaidDelai: '',
            createRaidHoraires: '',
            raidLevels: [1,2,3,4,5],
        }
    },
    mounted() {
        console.log('Component mounted.')
    },
    created() {
        this.updateTimeRange();
    },
    methods: {
        showModal( gym ) {
            console.log('show');
            this.gym = gym;
            this.getRaidData();
            this.$modal.show('GymModal');
        },
        hideModal() {
            this.$modal.hide('GymModal');
        },
        setScreenTo( value ) {
            console.log(value);
            this.modalScreen = value;
        },
        addToTimeRange() {
            this.createRaidData.delai += 1;
            this.updateTimeRange();
        },
        updateTimeRange() {
            var raidStartTime = moment();
            var raidEndTime = moment();
            if( this.createRaidData.delai >= 0 ) {
            } else {
                console.log(this.createRaidData.delai);
                var timeLeft = Math.abs(this.createRaidData.delai);
                raidStartTime.add(timeLeft, 'minutes').minutes();
                raidEndTime.add( parseInt(timeLeft) + parseInt(45), 'minutes').minutes();
                this.createRaidData.startTime = raidStartTime.format('YYYY-MM-DD HH:mm:ss');
                this.createRaidDelai = 'Le raid débute dans ' + timeLeft + ' min';
                this.createRaidHoraires = 'De ' + raidStartTime.format('HH[h]mm') + ' à ' + raidEndTime.format('HH[h]mm');
            }
        },
        getRaidData() {
            var now = moment();

            console.log(this.gym.raid);
            //Statut
            if( this.gym.raid ) {
                var startTime = moment(this.gym.raid.start_time, '"YYYY-MM-DD HH:mm:ss"');
                var endTime = moment(this.gym.raid.end_time, '"YYYY-MM-DD HH:mm:ss"');
                console.log(endTime.format('YYYY-MM-DD HH:mm:ss'));
                if( startTime.isAfter(now) ) {
                    this.raidStatus = 'future';
                } else if( endTime.isAfter(now) ) {
                    this.raidStatus = 'active';
                }
            }

            //Url
            if( this.raidStatus == 'none' ) {
                this.timeLeft = false;
                this.raidAnnonce = 'Rien pour le moment...';
                this.raidUrl = 'https://assets.profchen.fr/img/eggs/egg_0.png';
            } else if( this.raidStatus == 'future' ) {
                this.timeLeft = parseInt(startTime.diff(now, 'milliseconds'));
                this.raidAnnonce = 'Un oeuf '+this.gym.raid.egg_level+' têtes va bientot éclore...';
                this.raidUrl = 'https://assets.profchen.fr/img/eggs/egg_'+this.gym.raid.egg_level+'.png';
            } else if( !this.gym.raid.pokemon ) {
                this.timeLeft = parseInt(endTime.diff(now, 'milliseconds'));
                this.raidAnnonce = 'Un raid '+this.gym.raid.egg_level+' têtes est en cours...';
                this.raidUrl = 'https://assets.profchen.fr/img/eggs/egg_'+this.gym.raid.egg_level+'.png';
            } else {
                this.timeLeft = parseInt(endTime.diff(now, 'milliseconds'));
                this.raidAnnonce = 'Un raid '+this.gym.raid.pokemon.niantic_id+' têtes est en cours...';
                this.raidUrl =  this.gym.raid.pokemon.thumbnail_url;
            }
        }
    }
}
</script>
