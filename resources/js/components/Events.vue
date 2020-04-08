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
                    <h3>{{event.name}}</h3>
                    <p>Débute le {{getDate(event)}}</p>
                    <p>Proposé par <strong>{{event.guild.name}}</strong></p>
                    <img src="https://assets.profchen.fr/img/app/event_train.png">
                </router-link>
            </div>
        </div>

    </div>
</template>

<script>
    import moment from 'moment';
    export default {
        name: 'Events',
        data() {
            return {
                events: [],
                loading: true,
            }
        },
        created() {
            this.fetchEvents();
        },
        methods: {
            fetchEvents() {
                axios.get('/api/user/cities/'+this.$store.state.currentCity.id+'/events').then( res => {
                    this.loading = false;
                    this.events = res.data;
                });
            },
            getDate(event) {
                return moment(event.start_time).format('DD/MM à HH[h]mm[m]');
            },
        }
    }
</script>
