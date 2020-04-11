<template>
    <div>

        <div v-if="loading" class="loading">
            <div class="loading__content">
                <i class="friendball"></i>
                <p>Chargement...</p>
            </div>
        </div>

        <div v-if="!loading" class="settings-section">
            <v-list>
                <v-subheader>Évents en cours/à venir</v-subheader>
                <template v-for="(event, index) in activeEvents">
                    <v-list-tile :key="event.id" :to="{ name: 'admin.events.edit', params: { event_id: event.id } }">
                        <v-list-tile-avatar>
                            <img src="https://assets.profchen.fr/img/app/event_train.png">
                        </v-list-tile-avatar>
                        <v-list-tile-content>
                            <v-list-tile-title>
                                {{event.name}} <span>Débute le {{getEventStartTime(event)}}</span>
                            </v-list-tile-title>
                        </v-list-tile-content>
                    </v-list-tile>
                    <v-divider></v-divider>
                </template>
          </v-list>
          <v-list>
                <v-subheader>Évents passés</v-subheader>
                <template v-for="(event, index) in passedEvents">
                    <v-list-tile :key="event.id" :to="{ name: 'admin.events.edit', params: { event_id: event.id } }">
                        <v-list-tile-avatar>
                            <img src="https://assets.profchen.fr/img/app/event_train.png">
                        </v-list-tile-avatar>
                        <v-list-tile-content>
                            <v-list-tile-title>
                                {{event.name}} <span>Débute le {{getEventStartTime(event)}}</span>
                            </v-list-tile-title>
                        </v-list-tile-content>
                    </v-list-tile>
                    <v-divider></v-divider>
                </template>
          </v-list>
          <v-btn dark fixed bottom right fab :to="{ name: 'admin.events.add' }"><v-icon>add</v-icon></v-btn>
        </div>
    </div>
</template>

<script>
    import { mapState } from 'vuex'
    import moment from 'moment';
    export default {
        name: 'AdminEvents',
        data() {
            return {
                events: [],
                loading: true
            }
        },
        computed: {
            activeEvents() {
                return this.events.filter((event) => {
                    let endTime = moment(event.end_time);
                    let now = moment();
                    return now.isBefore(endTime);
                });
            },
            passedEvents() {
                return this.events.filter((event) => {
                    let endTime = moment(event.end_time);
                    let now = moment();
                    return now.isAfter(endTime);
                });                
            }
        },
        created() {
            this.fetchEvents();
        },
        methods: {
            fetchEvents() {
                axios.get('/api/user/guilds/'+this.$route.params.id+'/events').then( res => {
                    this.loading = false;
                    this.events = res.data;
                });
            },
            getEventStartTime(event) {
                return moment(event.start_time).format('DD/MM à HH[h]mm');
            }
        }
    }
</script>
