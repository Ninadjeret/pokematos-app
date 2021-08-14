<template>
    <div style="height: 100%;">

        <div v-if="loading" class="loading">
            <div class="loading__content">
                <i class="friendball"></i>
                <p>Chargement...</p>
            </div>
        </div>

        <div v-if="!loading" class="events" >
            <!--<div class="event__card" v-for="event in events" :key="event.id">
                <router-link :to="{ name: 'events.event', params: { event_id: event.id }}">
                    <div class="event__img" :style="'background-image: url('+event.image+')'">
                    </div>
                    <div class="event__content">
                        <h3>{{event.name}}</h3>
                        <p>Débute {{getDate(event)}}</p>
                        <p><span v-if="event.type == 'train'">Pokétrain en {{event.relation.steps.length}} étapes</span></p>
                    </div>
                </router-link>
            </div>-->
            <v-card dark flat class="event__single" v-for="event in events" :key="event.id" :to="{ name: 'events.event', params: { event_id: event.id }}">
                <v-img :src="event.image" gradient="to top, rgba(0,0,0,.44), rgba(0,0,0,.44)">
                <v-container >
                    <v-card-title>
                        <h3 class="title">{{event.name}}</h3>
                        <h4 class="subtitle"><span v-if="event.type == 'train'">Pokétrain en {{event.relation.steps.length}} étapes / </span> Par {{event.guild.name}}</h4>
                    </v-card-title>
                    <v-layout align-center>
                        <strong class="display-4 font-weight-regular mr-4">{{getDate(event).day}}</strong>
                        <v-layout column justify-end>
                            <div class="headline font-weight-light">{{getDate(event).weekDay}}</div>
                            <div class="text-uppercase font-weight-light">{{getDate(event).month}} {{getDate(event).year}}</div>
                        </v-layout>
                    </v-layout>
                </v-container>
            </v-img>
            </v-card>
        </div>

        <div v-if="!loading && events.length === 0" class="container_fullheight">
            <div class="event__empty">
                <img src="/storage/img/static/empty_2.png">
                <h3>Aucun évent n'est actuellement programmé par ta communauté</h3>
            </div>
        </div>

    </div>
</template>

<script>
    import moment from 'moment';
    import { mapState } from 'vuex'
    export default {
        name: 'Events',
        data() {
            return {
                events: [],
                loading: true,
                dialog: false,
                guild: false,
            }
        },
        created() {
            this.fetchEvents();
            let lastChanges = this.$store.state.settings.lastChanges
            lastChanges.events.local = Date.now() / 1000;
            this.$store.commit('setSetting', {setting: 'lastChanges',value: lastChanges});
        },
        computed: mapState([
                'currentCity', 'user'
        ]),
        methods: {
            fetchEvents() {
                axios.get('/api/user/cities/'+this.$store.state.currentCity.id+'/events').then( res => {
                    this.loading = false;
                    this.events = res.data;
                });
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
