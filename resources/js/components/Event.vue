<template>
    <div>
        <div v-if="loading" class="loading">
            <div class="loading__content">
                <i class="friendball"></i>
                <p>Chargement...</p>
            </div>
        </div>

        <div v-if="!loading">
        <v-card dark flat class="event__single">
            <v-img :src="event.image" gradient="to top, rgba(0,0,0,.44), rgba(0,0,0,.44)">
            <v-container >
                <v-card-title>
                    <h3 class="title">{{event.name}}</h3>
                </v-card-title>
                <v-layout align-center>
                    <strong class="display-4 font-weight-regular mr-4">{{date.day}}</strong>
                    <v-layout column justify-end>
                        <div class="headline font-weight-light">{{date.weekDay}}</div>
                        <div class="text-uppercase font-weight-light">{{date.month}} {{date.year}}</div>
                    </v-layout>
                </v-layout>
            </v-container>
        </v-img>
        </v-card>

        <div class="py-0">
            <v-timeline align-top dense>
                <v-timeline-item v-for="(step, index) in event.relation.steps" :key="step.id" :color="getStepColor(step, index)" small :class="'step step--'+getStepColor(step, index)">
                    <v-layout pt-3>
                    <v-flex xs3>
                        <strong>{{getStepTime(step)}}</strong>
                    </v-flex>
                    <v-flex>
                        <strong v-if="step.type == 'stop'" @click="showModal(step.stop)">
                            <span class="stop__marker">
                                <img v-if="step.stop.ex" src="https://assets.profchen.fr/img/app/connector_gym_ex.png">
                                <img v-if="!step.stop.ex" src="https://assets.profchen.fr/img/app/connector_gym.png">
                            </span>
                            <span v-if="step.stop && step.stop.zone">{{step.stop.zone.name}} - </span>
                            {{step.stop.name}}
                        </strong>
                        <strong v-if="step.type == 'transport'"><v-icon>directions_car</v-icon>&nbsp;Trajet en voiture/bus</strong>
                        <div v-if="step.description" class="caption">{{step.description}}</div>
                        <v-btn v-if="userCan('guild_manage') && displayCheck(step, index)" round large @click="checkstep(step, 'check')"><v-icon>done</v-icon>Check</v-btn>
                        <v-btn class="secondary" v-if="userCan('guild_manage') && displayUncheck(step, index)" round large @click="checkstep(step, 'uncheck')"><v-icon>close</v-icon>Uncheck</v-btn>
                    </v-flex>
                    </v-layout>
                </v-timeline-item>
            </v-timeline>
        </div>

        <v-btn v-if="event && event.channel_discord_id" fixed bottom round large :href="'https://discordapp.com/channels/'+event.guild.discord_id+'/'+event.channel_discord_id">Rejoindre la conversation</v-btn>

        </div>

        <gym-modal ref="gymModal"></gym-modal>
    </div>
</template>

<script>
    import moment from 'moment';
    import { mapState } from 'vuex'
    export default {
        name: 'Event',
        data() {
            return {
                loading: true,
                event: false,
            }
        },
        created() {
            if( this.$route.params.event_id ) {
                this.fetch();
            }
        },
        computed:{
            user() {
                return this.$store.state.user;
            },
            currentCity() {
                return this.$store.state.currentCity;
            },
            date() {
                moment.locale('fr');
                return {
                    day: moment(this.event.start_time).format('D'),
                    weekDay: moment(this.event.start_time).format('dddd'),
                    month: moment(this.event.start_time).format('MMMM'),
                    year: moment(this.event.start_time).format('YYYY'),
                }
            },
        },
        methods: {
            fetch() {
                axios.get('/api/user/cities/'+this.$store.state.currentCity.id+'/events/'+this.$route.params.event_id).then( res => {
                    this.loading = false;
                    this.event = res.data;
                }).catch( err => {
                    //No error
                });
            },
            checkstep( step, action ) {
                axios.post('/api/user/guilds/'+this.event.guild.id+'/events/'+this.$route.params.event_id+'/steps/'+step.id+'/'+action).then( res => {
                    this.loading = false;
                    this.event = res.data;
                }).catch( err => {
                    //No error
                });
            },
            getStepTime(step) {
                return moment(step.start_time).format('HH[h]mm');
            },
            getStepColor(step, index) {
                if( this.displayCheck( step, index ) ) return 'green';
                return ( step.checked ) ? 'grey' : '#5a6cae' ;
            },
            userCan( param ) {
                let auth = false;
                let that = this;
                this.currentCity.guilds.forEach((guild, index) => {
                    if( guild.id === that.event.guild.id && that.user.permissions[guild.id].find(val => val === param) ) {
                        auth = true;
                    }
                })
                return auth;
            },
            showModal( gym ) {
                let gymToDisplay = this.$store.state.gyms.find(stop => stop.id === gym.id);
                this.$refs.gymModal.showModal( gymToDisplay );
            },
            displayCheck(step, index) {
                if( step.checked ) return false;
                if( index === 0 ) return true;
                if( this.event.relation.steps[index-1].checked ) return true;
                return false;
            },
            displayUncheck(step, index) {
                if( !step.checked ) return false;
                if( index === this.event.relation.steps.length - 1 ) return true;
                if( this.event.relation.steps[index+1].checked == false ) return true;
                return false;
            }
        }
    }
</script>
