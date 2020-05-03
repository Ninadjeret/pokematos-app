<template>
    <div>

        <div v-if="loading" class="loading">
            <div class="loading__content">
                <i class="friendball"></i>
                <p>Chargement...</p>
            </div>
        </div>

        <div v-if="!loading">
            <div class="invitation" v-for="(invit, index) in invits" :key="invit.id">
                <v-card dark flat class="event__single">
                    <v-img :src="invit.event.image" gradient="to top, rgba(0,0,0,.44), rgba(0,0,0,.44)">
                    <v-container >
                        <v-card-title>
                            <h3 class="title">{{invit.event.name}}</h3>
                            <h4 class="subtitle"><span v-if="invit.event.type == 'train'">Pokétrain en {{invit.event.relation.steps.length}} étapes / </span> Par {{invit.event.guild.name}}</h4>
                        </v-card-title>
                        <v-layout align-center>
                            <strong class="display-4 font-weight-regular mr-4">{{getDate(invit.event).day}}</strong>
                            <v-layout column justify-end>
                                <div class="headline font-weight-light">{{getDate(invit.event).weekDay}}</div>
                                <div class="text-uppercase font-weight-light">{{getDate(invit.event).month}} {{getDate(invit.event).year}}</div>
                            </v-layout>
                        </v-layout>
                    </v-container>
                </v-img>
                </v-card>
                <div class="invitation__action ">
                    <v-layout wrap align-content-center align-center justify-center>
                        <v-flex v-if="invit.status == 'pending'" xs12>
                            <p>{{getInvitStatusLabel(invit)}} le {{getInvitStatusDate(invit)}}</p>
                        </v-flex>
                        <v-flex xs6>
                            <p v-if="invit.status == 'accepted'" style="color:darkgreen">Invitation acceptée le {{getInvitStatusDate(invit)}}</p>
                            <v-btn v-if="invit.status != 'accepted'" round large class="accept" @click="acceptInvit(invit, 'accept')"><v-icon>event_available</v-icon>&nbsp;Accepter</v-btn>
                        </v-flex>
                        <v-flex xs6>
                            <p v-if="invit.status == 'refused'" style="color:darkred">Invitation refusée le {{getInvitStatusDate(invit)}}</p>
                            <v-btn v-if="invit.status != 'refused'" round large class="refuse"@click="acceptInvit(invit, 'refuse')"><v-icon>event_busy</v-icon>&nbsp;Refuser</v-btn>
                        </v-flex>
                    </v-layout>
                </div>
            </div>
      </div>

      <div v-if="!loading && invits.length === 0" class="event__empty">
          <img src="https://assets.profchen.fr/img/app/empty_2.png">
          <h3>Aucune invitation pour l'instant :/</h3>
      </div>

    </div>
</template>

<script>
    import { mapState } from 'vuex'
    import moment from 'moment';
    export default {
        name: 'AdminEventsInvits',
        data() {
            return {
                invits: [],
                loading: true
            }
        },
        computed: {
        },
        created() {
            this.fetchInvits();
        },
        methods: {
            fetchInvits() {
                axios.get('/api/user/guilds/'+this.$route.params.id+'/events/invits').then( res => {
                    this.loading = false;
                    this.invits = res.data;
                });
            },
            acceptInvit( invit, action ) {
                axios.post('/api/user/guilds/'+invit.guild.id+'/events/invits/'+invit.id+'/'+action).then( res => {
                    this.loading = false;
                    this.invits = res.data;
                });
            },
            getEventDate(invit) {
                moment.locale('fr');
                return moment(invit.event.start_time).format('DD/MM [à] HH[h]mm');
            },
            getInvitStatusLabel(invit) {
                if( invit.status == 'accepted' ) return 'Acceptée';
                if( invit.status == 'refused' ) return 'Refusée';
                return 'Reçue';
            },
            getInvitStatusDate(invit) {
                let date = moment(invit.status.time);
                return date.format('DD/MM [à] HH[h]mm');
            },
            getDate(event) {
                moment.locale('fr');
                return {
                    day: moment(event.start_time).format('D'),
                    weekDay: moment(event.start_time).format('dddd'),
                    month: moment(event.start_time).format('MMMM'),
                    year: moment(event.start_time).format('YYYY'),
                }
            }
        }
    }
</script>
