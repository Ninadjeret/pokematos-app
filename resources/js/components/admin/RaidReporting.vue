<template>
    <div>
        <div class="parent_view" v-if="$route.name == 'admin.raids'">

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
                <v-subheader>Réglages</v-subheader>
                <div class="setting d-flex switch">
                    <div>
                        <label>Analyser les captures d'écran publiées</label>
                    </div>
                    <v-switch v-model="raidreporting_images_active"></v-switch>
                </div>
                <div v-if="raidreporting_images_active" class="setting d-flex switch">
                    <div>
                        <label>Supprimer les captures après analyse ?</label>
                    </div>
                    <v-switch v-model="raidreporting_images_delete"></v-switch>
                </div>
                <div class="setting d-flex switch">
                    <div>
                        <label>Analyser les messages texte publiés</label>
                    </div>
                    <v-switch v-model="raidreporting_text_active"></v-switch>
                </div>
                <div v-if="raidreporting_text_active" class="setting d-flex switch">
                    <div>
                        <label>Supprimer les messages texte d'annonce de raid ?</label>
                    </div>
                    <v-switch v-model="raidreporting_text_delete"></v-switch>
                </div>
                <div v-if="raidreporting_text_active" class="setting">
                    <label>Préfixes des messages texte</label>
                    <p class="description">Indiquer par quoi doivent commencer les messages texte pour être analysés ?</p>
                    <input v-model="raidreporting_text_prefixes" type="text">
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
        name: 'AdminRaidReportingHome',
        data() {
            return {
                loading: false,
                items: [
                    {
                        label: 'Gérer les annonces',
                        route: 'admin.raids.annonces',
                        icon: 'settings_input_component'
                    },
                ],
                raidreporting_images_active: false,
                raidreporting_images_delete: false,
                raidreporting_text_active: false,
                raidreporting_text_delete: false,
                raidreporting_text_prefixes: '+raid, +Raid',
            }
        },
        computed: mapState([
                'currentCity'
        ]),
        created() {
            this.fetch();
        },
        methods: {
            fetch() {
                axios.get('/api/user/cities/'+this.$store.state.currentCity.id+'/guilds/'+this.$route.params.id+'/settings').then( res => {
                    this.raidreporting_images_active = parseInt(res.data.raidreporting_images_active);
                    this.raidreporting_images_delete = parseInt(res.data.raidreporting_images_delete);
                    this.raidreporting_text_active = parseInt(res.data.raidreporting_text_active);
                    this.raidreporting_text_delete = parseInt(res.data.raidreporting_text_delete);
                    this.raidreporting_text_prefixes = res.data.raidreporting_text_prefixes.join(', ');
                }).catch( err => {
                    //No error
                });
            },
            submit() {
                const args = {
                    settings: {
                        raidreporting_images_active: this.raidreporting_images_active,
                        raidreporting_images_delete: this.raidreporting_images_delete,
                        raidreporting_text_active: this.raidreporting_text_active,
                        raidreporting_text_delete: this.raidreporting_text_delete,
                        raidreporting_text_prefixes: this.raidreporting_text_prefixes.split(', '),
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
