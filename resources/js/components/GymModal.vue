<template>
        <modal name="GymModal">
            <div v-if="gym">
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
                        <li v-if="gym.google_maps_url"><a :href="gym.google_maps_url"><i class="material-icons">navigation</i><span>Itinéraire vers l'arène</span></a></li>
                    </ul>
                </div>
                <div class="footer--actions">
                    <button class="button--close" v-on:click="hideModal()"><i class="material-icons">close</i></button>
                </div>
            </div>
        </modal>
</template>

<script>
import moment from 'moment';
export default {
    data() {
        return {
            gym: '',
            raidStatus: 'none',
            raidUrl: '',
            raidAnnonce: '',
            timeLeft: false,
        }
    },
    mounted() {
        console.log('Component mounted.')
    },
    created() {
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
