<template>
    <div>
        <div class="parent_view" v-if="$route.name == 'admin.raids'">
            <div class="settings-section">
                <v-subheader>Général</v-subheader>
                <v-list>
                <template v-for="(item, index) in menuItems">
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
        name: 'AdminRaids',
        data() {
            return {
                menuItems: [
                    {
                        label: 'Détecter les annonces',
                        route: 'admin.gyms',
                        icon: 'place'
                    },
                    {
                        label: 'Recevoir les annonces',
                        route: 'admin.zones',
                        icon: 'map'
                    },
                    {
                        label: 'Roles personnalisés',
                        route: 'admin.zones',
                        icon: 'map'
                    }
                ],
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
