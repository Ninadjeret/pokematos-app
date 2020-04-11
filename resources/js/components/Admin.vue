<template>
    <div>
        <div class="parent_view" v-if="$route.name == 'admin'">
            <div class="settings-section">

                <div v-if="user.permissions[guild.id].find(val => val === 'guild_manage')" v-for="guild in currentCity.guilds">
                    <v-subheader>Discord {{guild.name}}</v-subheader>
                    <v-list>
                    <template v-if="user.permissions[guild.id].find(val => val === item.permission)" v-for="(item, index) in discordItems">
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

              <div v-if="canAccessCityParam('zone_edit') || canAccessCityParam('poi_edit')">
                    <v-subheader v-if="currentCity">{{currentCity.name}}</v-subheader>
                    <v-list>
                    <template v-for="(item, index) in generalItems">
                        <v-list-tile v-if="canAccessCityParam(item.permission)" :key="item.route" :to="{ name: item.route}">
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


              <div v-if="canAccessCityParam('boss_edit') || canAccessCityParam('quest_edit')">
                    <v-subheader>Global</v-subheader>
                    <v-list>
                    <template v-for="(item, index) in commonItems">
                        <v-list-tile v-if="canAccessCityParam(item.permission)" :key="item.route" :to="{ name: item.route}">
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
                        label: 'Carte',
                        route: 'admin.map',
                        icon: 'map',
                        permission: 'guild_manage'
                    },
                    {
                        label: 'POI',
                        route: 'admin.gyms',
                        icon: 'place',
                        permission: 'poi_edit'
                    },
                    {
                        label: 'Zones géographiques',
                        route: 'admin.zones',
                        icon: 'map',
                        permission: 'zone_edit'
                    },
                    {
                        label: 'Logs',
                        route: 'admin.logs',
                        icon: 'receipt',
                        permission: 'logs_manage'
                    }
                ],
                discordItems: [
                    {
                        label: 'Signalements de raids',
                        route: 'admin.raids',
                        icon: 'add_alert',
                        permission: 'guild_manage'
                    },
                    {
                        label: 'Signalements de raids EX',
                        route: 'admin.raidsex',
                        icon: 'star',
                        permission: 'guild_manage'
                    },
                    {
                        label: 'Signalements de quêtes',
                        route: 'admin.quests.home',
                        icon: 'explore',
                        permission: 'guild_manage',
                    },
                    {
                        label: 'Signalements de boss Rocket',
                        route: 'admin.rocket.home',
                        icon: 'people_alt',
                        permission: 'guild_manage',
                    },
                    {
                        label: 'Roles personnalisés',
                        route: 'admin.roles',
                        icon: 'alternate_email',
                        permission: 'guild_manage'
                    },
                    {
                        label: 'Évents',
                        route: 'admin.events.home',
                        icon: 'event',
                        permission: 'guild_manage'
                    },
                    {
                        label: 'Message de bienvenue',
                        route: 'admin.welcome',
                        icon: 'insert_comment',
                        permission: 'guild_manage'
                    },
                    {
                        label: 'Droits d\'accès',
                        route: 'admin.access',
                        icon: 'lock_open',
                        permission: 'guild_manage'
                    },
                ],
                commonItems: [
                    {
                        label: 'Gérer les boss',
                        route: 'admin.bosses',
                        icon: 'fingerprint',
                        permission: 'boss_edit'
                    },
                    {
                        label: 'Gérer les quêtes',
                        route: 'admin.quests',
                        icon: 'explore',
                        permission: 'quest_edit'
                    },
                    {
                        label: 'Gérer les Boss Rocket',
                        route: 'admin.rocket.bosses',
                        icon: 'people_alt',
                        permission: 'rocket_bosses_edit'
                    },
                ]
            }
        },
        computed: mapState([
                'currentCity', 'user'
        ]),
        watch: {
        },
        mounted() {
        },
        methods: {
            canAccessCityParam( param ) {
                let auth = false;
                let that = this;
                this.currentCity.guilds.forEach((guild, index) => {
                    if( that.user.permissions[guild.id].find(val => val === param) ) {
                        auth = true;
                    }
                })
                return auth;
            },
        }
    }
</script>
