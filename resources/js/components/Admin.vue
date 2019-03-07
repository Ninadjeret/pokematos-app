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
                        label: 'Gérer les arènes',
                        route: 'admin.gyms',
                        icon: 'place'
                    },
                    {
                        label: 'Gérer les zones géographiques',
                        route: 'admin.zones',
                        icon: 'map'
                    }
                ],
                discordItems: [
                    {
                        label: 'Gérer les droits d\'accès',
                        route: 'admin.access',
                        icon: 'lock_open'
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
