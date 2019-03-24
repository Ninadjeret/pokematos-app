<template>
    <div>
        <div class="parent_view" v-if="$route.name == 'admin'">
            <div class="settings-section">
                <v-subheader>Général</v-subheader>
                <v-list>
                <template v-for="(item, index) in generalItems">
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

              <div v-for="guild in currentCity.guilds">
                  <v-subheader>Discord {{guild.name}}</v-subheader>
                  <v-list>
                  <template v-for="(item, index) in discordItems">
                      <v-list-tile :key="item.route" :to="{ name: item.route, params: { id: guild.id }}">
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

            <v-subheader>Commun</v-subheader>
            <v-list>
            <template v-for="(item, index) in commonItems">
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
        </div>
        <transition name="fade">
            <router-view></router-view>
        </transition>
    </div>
</template>

<script>
    import { mapState } from 'vuex'
    export default {
        name: 'Admin',
        data() {
            return {
                generalItems: [
                    {
                        label: 'Arènes',
                        route: 'admin.gyms',
                        icon: 'place'
                    },
                    {
                        label: 'Aones géographiques',
                        route: 'admin.zones',
                        icon: 'map'
                    }
                ],
                discordItems: [
                    {
                        label: 'Signalements de raids',
                        route: 'admin.raids',
                        icon: 'add_alert'
                    },
                    {
                        label: 'Signalements de pops sauvages',
<<<<<<< HEAD
                        route: 'admin.pops',
=======
                        route: 'admin.access',
>>>>>>> 7825fa41493a2ae4c4324d72e593c91c40f72664
                        icon: 'add_a_photo'
                    },
                    {
                        label: 'Signalement de quètes',
<<<<<<< HEAD
                        route: 'admin.quests',
                        icon: 'add_location'
                    },
                    {
                        label: 'Roles personnalisés',
                        route: 'admin.roles',
                        icon: 'alternate_email'
                    },
                    {
=======
                        route: 'admin.access',
                        icon: 'add_location'
                    },
                    {
>>>>>>> 7825fa41493a2ae4c4324d72e593c91c40f72664
                        label: 'Droits d\'accès',
                        route: 'admin.access',
                        icon: 'lock_open'
                    },
                ],
                commonItems: [
                    {
                        label: 'Gérer les boss',
                        route: 'admin.bosses',
                        icon: 'fingerprint'
                    },
                ]
            }
        },
        computed: mapState([
                'currentCity'
        ]),
        watch: {
        },
        mounted() {
        },
        methods: {
        }
    }
</script>
