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

            <div class="settings-section">
                <v-subheader>Réglages généraux</v-subheader>
                <div class="setting d-flex switch">
                    <div>
                        <label>Créer des salons temporaires pour les évents ?</label>
                        <p class="description">Cela permettra à Pokématos d'organiser les évents et publiant messages et réactions. La fonctionnalité doit être activée pour certains événements (comme les Pokéquiz).</p>
                    </div>
                    <v-switch v-model="events_create_channels"></v-switch>
                </div>
                <div v-if="events_create_channels" class="setting">
                    <label>Catégorie de salon</label>
                    <p class="description">Le salon temporaire sera créé dans la catégorie choisie. (les droits appliqués au salon seront les mêmes que ceux de la catégorie)</p>
                    <select v-if="channels_categories" v-model="events_channel_discord_id">
                        <option v-for="channel in channels_categories" :value="channel.id.toString()">{{channel.name}}</option>
                    </select>
                </div>
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
                            <li>{next_etape_nom} : Nom de l'étape</li>
                            <li>{next_etape_heure} : Heure de l'étape</li>
                            <li>{next_etape_description} : Description de l'étape</li>
                        </ul>
                    </p>
                    <input v-model="events_trains_message_check" type="text">
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
                ],
                channels_categories: [],
                events_create_channels: false,
                events_channel_discord_id: false,
                events_trains_add_messages: false,
                events_trains_message_check: '@here nous passons à la prochaine étape : {etape_nom}. RDV à {etape_heure}',

            }
        },
        computed: {
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
