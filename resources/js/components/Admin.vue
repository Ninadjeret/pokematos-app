<template>
    <div>
        <div class="parent_view" v-if="$route.name == 'admin'">
            <div class="settings-section">
                <div class="section__title">Général</div>
            </div>
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
