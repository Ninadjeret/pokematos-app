<template>
    <div>
        <div class="parent_view" v-if="$route.name == 'admin.events.home'">

            <div class="settings-section">
                <v-subheader>Général</v-subheader>
                <v-list>
                <template v-for="(item, index) in items">
                    <v-list-tile :key="item.route" :to="{ name: item.route}">
                        <v-list-tile-action>
                            <v-icon>{{item.icon}}</v-icon>
                        </v-list-tile-action>
                        <v-list-tile-content>
                            <v-list-tile-title>{{item.label}}</v-list-tile-title>
                        </v-list-tile-content>
                        <v-list-tile-action>
                            <v-btn icon ripple>
                                <v-icon color="grey lighten-1">arrow_forward_ios</v-icon>
                            </v-btn>
                        </v-list-tile-action>
                  </v-list-tile>
                  <v-divider></v-divider>
                </template>
              </v-list>
            </div>

            <div v-if="features.events_multi" class="settings-section">
                <v-subheader>Guild VS Guild</v-subheader>
                <div class="setting d-flex switch">
                    <div>
                        <label>Accepter les invitations</label>
                        <p class="description">Autoriser les autres communautés à vos défier/inviter à des événements multi-guilds</p>
                    </div>
                    <v-switch v-model="events_accept_invits"></v-switch>
                </div>
                <div v-if="events_accept_invits" class="setting">
                    <label>Catégorie de salon</label>
                    <p class="description">Pour chaque invitation acceptée, un salon temporaire sera créé. Ce salon sera créé dans la catégorie choisie. (les droits appliqués au salon seront les mêmes que ceux de la catégorie)</p>
                    <select v-if="channels_categories" v-model="events_channel_discord_id">
                        <option v-for="channel in channels_categories" :value="channel.id.toString()">{{channel.name}}</option>
                    </select>
                </div>
            </div>
            <div class="settings-section">
                <v-subheader>Réglages des Pokétrains</v-subheader>
                <div v-if="events_create_channels" class="setting d-flex switch">
                    <div>
                        <label>Publier un message à chaque validation d'étape ?</label>
                        <p class="description">Cela créera un nouveau message dans le salon de l'évent pour annoncer la prochaine étape du parcours</p>
                    </div>
                    <v-switch v-model="events_trains_add_messages"></v-switch>
                </div>
                <div v-if="events_trains_add_messages && events_create_channels" class="setting">
                    <label>Message à publier</label>
                    <p class="description">Vous pouvez en personnaliser le contenu avec différents tags :<br>
                        <ul>
                            <li>{etape_nom}</li>
                            <li>{etape_heure}</li>
                            <li>{etape_description}</li>
                            <li>{next_etape_nom}</li>
                            <li>{next_etape_heure}</li>
                            <li>{next_etape_description}</li>
                        </ul>
                    </p>
                    <textarea v-model="events_trains_message_check"></textarea>
                </div>
                <v-btn dark fixed bottom right fab @click="submit()">
                    <v-progress-circular v-if="loading" indeterminate color="primary"></v-progress-circular>
                    <v-icon v-else>save</v-icon>
                </v-btn>
            </div>

        </div>

        <transition name="fade">
            <router-view></router-view>
        </transition>

    </div>
</template>

<script>
    import { mapState } from 'vuex'

    export default {
        name: 'AdminEventHome',
        data() {
            return {
                loading: false,
                items: [
                    {
                        label: 'Gérer les événements',
                        route: 'admin.events',
                        icon: 'event'
                    },
                    {
                        label: 'Invitations reçues',
                        route: 'admin.events.invits',
                        icon: 'event_available'
                    },
                ],
                channels_categories: [],
                events_create_channels: false,
                events_channel_discord_id: false,
                events_trains_add_messages: false,
                events_trains_message_check: '@here nous passons à la prochaine étape : {etape_nom}. RDV à {etape_heure}',
                events_accept_invits: true,

            }
        },
        computed: {
            features() {
                return window.pokematos.features;
            },
            currentCity() {
                return this.$store.state.currentCity;
            },
        },
        created() {
            this.fetch();
            this.fetchDiscordChannelCategories();
        },
        methods: {
            fetch() {
                axios.get('/api/user/cities/'+this.$store.state.currentCity.id+'/guilds/'+this.$route.params.id+'/settings').then( res => {
                    this.events_create_channels = res.data.events_create_channels;
                    this.events_channel_discord_id = res.data.events_channel_discord_id;
                    this.events_trains_add_messages = res.data.events_trains_add_messages;
                    this.events_trains_message_check = res.data.events_trains_message_check;
                    this.events_accept_invits = res.data.events_accept_invits;
                }).catch( err => {
                    //No error
                });
            },
            fetchDiscordChannelCategories() {
                axios.get('/api/user/cities/'+this.$store.state.currentCity.id+'/guilds/'+this.$route.params.id+'/channelcategories').then( res => {
                    this.channels_categories = res.data;
                }).catch( err => {
                    //No error
                });
            },
            submit() {
                const args = {
                    settings: {
                        events_create_channels: this.events_create_channels,
                        events_channel_discord_id: this.events_channel_discord_id,
                        events_trains_add_messages: this.events_trains_add_messages,
                        events_trains_message_check: this.events_trains_message_check,
                        events_accept_invits: this.events_accept_invits,
                    }
                };
                this.save(args);
            },
            save( args ) {
                this.$store.commit('setSnackbar', {message: 'Enregistrement en cours'})
                this.loading = true;
                axios.put('/api/user/cities/'+this.$store.state.currentCity.id+'/guilds/'+this.$route.params.id+'/settings', args).then( res => {
                    this.$store.commit('setSnackbar', {
                        message: 'Enregistrement effectué',
                        timeout: 1500
                    })
                    this.loading = false
                }).catch( err => {
                    this.$store.commit('setSnackbar', {
                        message: 'Problème lors de l\'enregistrement',
                        timeout: 1500
                    })
                    this.loading = false
                });
            },
        }
    }
</script>
