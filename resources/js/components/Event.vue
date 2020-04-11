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
            <v-card-title>
                <h3 class="title">{{event.name}}</h3>
            </v-card-title>
            <v-img src="https://assets.profchen.fr/img/app/event_train_plain.jpg" gradient="to top, rgba(0,0,0,.44), rgba(0,0,0,.44)">
            <v-container fill-height>
                <v-layout align-center>
                    <strong class="display-4 font-weight-regular mr-4">8</strong>
                    <v-layout column justify-end>
                        <div class="headline font-weight-light">Monday</div>
                        <div class="text-uppercase font-weight-light">February 2015</div>
                    </v-layout>
                </v-layout>
            </v-container>
        </v-img>
        </v-card>

        <div class="py-0">
            <v-timeline align-top dense>
                <v-timeline-item v-for="(step, index) in event.relation.steps" :key="step.id" :color="getStepColor(step)" small :class="'step step--'+getStepColor(step)">
                    <v-layout pt-3>
                    <v-flex xs3>
                        <strong>{{getStepTime(step)}}</strong>
                    </v-flex>
                    <v-flex>
                        <strong v-if="step.stop" @click="showModal(step.stop)">
                            <span v-if="step.stop && step.stop.zone">{{step.stop.zone.name}} - </span>
                            {{step.stop.name}}
                        </strong>
                        <div v-if="step.description" class="caption">{{step.description}}</div>
                        <v-btn v-if="userCan('guild_manage') && displayCheck(step, index)" round large @click="checkstep(step, 'check')">Check</v-btn>
                        <v-btn v-if="userCan('guild_manage') && displayUncheck(step, index)" round large @click="checkstep(step, 'uncheck')">Annuler le check</v-btn>
                    </v-flex>
                    </v-layout>
                </v-timeline-item>                
            </v-timeline>
        </div>

        <v-btn v-if="event && event.channel_discord_id" round large :href="'https://discordapp.com/channels/'+event.guild.discord_id+'/'+event.channel_discord_id">Rejoindre la conversation</v-btn>

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
        computed: mapState([
                'currentCity', 'user'
        ]),
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
            getStepColor(step) {
                if( step.type != 'stop' ) return 'grey';
                return ( step.checked ) ? 'grey' : 'green' ;
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
                this.$refs.gymModal.showModal( gym );
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
