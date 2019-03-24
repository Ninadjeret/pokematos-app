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
        name: 'AdminRolesHome',
        data() {
            return {
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
            }
        },
        computed: mapState([
                'currentCity'
        ]),
    }
</script>
