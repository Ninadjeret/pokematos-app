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
                    //this.roles_gym_color = res.data.roles_gym_color;;
                }).catch( err => {
                    let message = 'Problème lors de la récupération';
                    if( err.response.data ) {
                        message = err.response.data;
                    }
                    this.$store.commit('setSnackbar', {
                        message: message,
                        timeout: 1500
                    })
                });
            },
            submit() {
                const args = {
                    settings: {
                        //roles_gym_color: this.roles_gym_color,
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
                    let message = 'Problème lors de l\'enregistrement';
                    if( err.response.data ) {
                        message = err.response.data;
                    }
                    this.$store.commit('setSnackbar', {
                        message: message,
                        timeout: 1500
                    })
                    this.loading = false
                });
            },
        }
    }
</script>
