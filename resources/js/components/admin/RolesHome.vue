<template>
    <div>
        <div class="parent_view" v-if="$route.name == 'admin.roles'">

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
                  </v-list-tile>
                  <v-divider></v-divider>
                </template>
              </v-list>
            </div>

            <div class="settings-section">
                <v-subheader>Réglages</v-subheader>
                <div class="setting colorpicker">
                    <label>Couleur pour les roles Arène</label>
                    <swatches
                        v-model="roles_gym_color"
                        colors="material-basic"
                        show-fallback
                        shapes="circles"
                        swatch-size="30"
                        popover-to="left"
                        :trigger-style="{ width: '32px', height: '32px' }">
                    </swatches>
                </div>
                <div class="setting colorpicker">
                    <label>Couleur pour les roles Arène EX</label>
                    <swatches
                        v-model="roles_gymex_color"
                        colors="material-basic"
                        show-fallback
                        shapes="circles"
                        swatch-size="30"
                        popover-to="left"
                        :trigger-style="{ width: '32px', height: '32px' }">
                    </swatches>
                </div>
                <div class="setting colorpicker">
                    <label>Couleur pour les roles zone géographique</label>
                    <swatches
                        v-model="roles_zone_color"
                        colors="material-basic"
                        show-fallback
                        shapes="circles"
                        swatch-size="30"
                        popover-to="left"
                        :trigger-style="{ width: '32px', height: '32px' }">
                    </swatches>
                </div>
                <div class="setting colorpicker">
                    <label>Couleur pour les roles Pokémon</label>
                    <swatches
                        v-model="roles_pokemon_color"
                        colors="material-basic"
                        show-fallback
                        shapes="circles"
                        swatch-size="30"
                        popover-to="left"
                        :trigger-style="{ width: '32px', height: '32px' }">
                    </swatches>
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
    import Swatches from 'vue-swatches'
    import "vue-swatches/dist/vue-swatches.min.css"

    export default {
        name: 'AdminRolesHome',
        components: { Swatches },
        data() {
            return {
                loading: false,
                items: [
                    {
                        label: 'Catégories de roles',
                        route: 'admin.roles.categories',
                        icon: 'supervised_user_circle'
                    },
                    {
                        label: 'Roles',
                        route: 'admin.roles.roles',
                        icon: 'alternate_email'
                    }
                ],
                roles_gym_color: '',
                roles_gymex_color: '',
                roles_zone_color: '',
                roles_pokemon_color: '',
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
                    this.roles_gym_color = res.data.roles_gym_color;
                    this.roles_gymex_color = res.data.roles_gymex_color;
                    this.roles_zone_color = res.data.roles_zone_color;
                    this.roles_pokemon_color = res.data.roles_pokemon_color;
                }).catch( err => {
                    //No error
                });
            },
            submit() {
                const args = {
                    settings: {
                        roles_gym_color: this.roles_gym_color,
                        roles_gymex_color: this.roles_gymex_color,
                        roles_zone_color: this.roles_zone_color,
                        roles_pokemon_color: this.roles_pokemon_color
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
