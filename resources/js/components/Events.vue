<template>
    <div>

        <div v-if="loading" class="loading">
            <div class="loading__content">
                <i class="friendball"></i>
                <p>Chargement...</p>
            </div>
        </div>

        <div v-if="!loading">
            <div class="event__card" v-for="event in events" :key="event.id">
                <router-link :to="{ name: 'events.event', params: { event_id: event.id }}">
                    <div class="event__img" :style="'background-image: url('+event.image+')'">
                    </div>
                    <div class="event__content">
                        <h3>{{event.name}}</h3>
                        <p>Débute {{getDate(event)}}</p>
                        <p>Proposé par <strong>{{event.guild.name}}</strong></p>
                    </div>
                </router-link>
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
                return moment(event.start_time).format('dddd DD/MM à HH[h]mm');
            }
        }
    }
</script>
