<template>
    <div>
        <v-btn to="/" color="primary" flat value="recent" >
          <span>Map</span>
          <v-icon>map</v-icon>
        </v-btn>

        <v-btn to="/list" color="primary" flat value="recent" >
          <span>Listes</span>
          <v-icon>notifications_active</v-icon>
        </v-btn>

        <v-btn to="/profile" color="primary" flat value="recent" >
          <span>Profil</span>
          <v-icon>person</v-icon>
        </v-btn>

        <v-btn
            v-if="currentCity && currentCity !== undefined && parseInt(currentCity.permissions) >= 10 && user.permissions[currentCity.guilds[0].id].find(val => val != 'raid_delete' && val != 'raidex_add' )"
            to="/admin"
            color="primary"
            flat value="recent"
        >
          <span>Admin</span>
          <v-icon>build</v-icon>
        </v-btn>
    </div>
</template>

<script>
import { mapState } from 'vuex'
export default {
    name: 'Navigation',
    data() {
        return {
        }
    },
    computed: mapState([
        'currentCity', 'user'
    ]),
}
</script>
