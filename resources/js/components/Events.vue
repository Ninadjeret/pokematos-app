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
                    <img :src="'https://assets.profchen.fr/img/app/event_'+event.type+'.png'">
                </router-link>
            </div>

            <v-btn v-if="userCan('guild_manage')" dark fixed bottom right fab @click="dialog = true">
                <v-icon>add</v-icon>
            </v-btn>
        </div>

        <v-dialog v-model="dialog" max-width="90%">
            <v-card>
                <div class="dialog__wrap">
                    <h3 class="dialog__title">Créer un événement</h3>
                    <hr>
                    <h3>Pour quelle communauté</h3>
                    <select v-model="guild">
                        <option v-for="guild in getGuildsWithCapability()" :key="guild.id">{{guild.name}}</option>
                    </select>
                    <div class="event__card">
                        <router-link :to="{ name: 'events.event', params: { event_id: 1 }}">
                            <h3>Pokétrain</h3>
                            <p>Permet de générer un parcours d'arène</p>
                            <img src="https://assets.profchen.fr/img/app/event_train.png">
                        </router-link>
                    </div>
                    <div class="footer--actions">
                        <button class="button--close" @click="dialog = false"><i class="material-icons">close</i></button>
                    </div>
                </div>
            </v-card>
        </v-dialog>

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
                return moment(event.start_time).format('DD/MM à HH[h]mm');
            },
            userCan( param ) {
                let auth = false;
                let that = this;
                this.currentCity.guilds.forEach((guild, index) => {
                    if( that.user.permissions[guild.id].find(val => val === param) ) {
                        auth = true;
                    }
                })
                return auth;
            },
            getGuildsWithCapability( cap = 'guild_manage' ) {
                let that = this;
                return this.currentCity.guilds.filter(function(guild) {
                    let auth = false;
                    if( that.user.permissions[guild.id].find(val => val === cap) ) {
                        auth = true;
                    }
                    return auth;
                });   
            }
        }
    }
</script>
