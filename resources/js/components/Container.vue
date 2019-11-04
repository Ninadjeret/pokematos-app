<template>
<div id="app__container" :class="'template-'+$route.name" data-app v-if="currentCity && currentCity !== 'undefined'">
        <v-toolbar fixed app color="primary" dark>
            <v-btn v-if="$route.meta.parent" :to="{ name: $route.meta.parent}" icon><v-icon>arrow_back</v-icon></v-btn>
            <v-spacer class="hidden-md-and-up" v-if="$route.name == 'map'"></v-spacer>
            <v-toolbar-title v-if="$route.name == 'map'">
                <img src="https://assets.profchen.fr/img/logo_pokematos.png"> POKEMATOS <small v-if="this.$store.state.currentCity">{{ this.$store.state.currentCity.name }}</small>
            </v-toolbar-title>
            <v-toolbar-title v-if="$route.name != 'map'">{{$route.meta.title}}</v-toolbar-title>
            <v-btn v-if="cities &&  cities.length > 1 && $route.name == 'map'" icon @click.stop="dialogCities = true">
                <v-icon>location_city</v-icon>
            </v-btn>
            <v-spacer></v-spacer>
            <v-toolbar-items class="hidden-sm-and-down">
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
            </v-toolbar-items>
        </v-toolbar>


        <v-content>
            <v-container>
                <transition :name="transitionName">
                    <router-view></router-view>
                </transition>
                <snackbar></snackbar>
            </v-container>
          </v-content>


          <v-footer app v-if="!$route.meta.parent" class="hidden-md-and-up">
              <v-bottom-nav v-if="!$route.meta.parent" :value="true" absolute color="white" :mandatory="false">
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
                      v-if="currentCity && currentCity !== undefined && isAdmin"
                      to="/admin"
                      color="primary"
                      flat value="recent"
                  >
                    <span>Admin</span>
                    <v-icon>build</v-icon>
                  </v-btn>
              </v-bottom-nav>
          </v-footer>
          <v-dialog v-model="dialogCities" max-width="90%" content-class="city-modal">
              <v-card>
                    <v-card-title class="headline">Choisis ta zone</v-card-title>
                    <v-card-text>
                        <ul id="cityChoice">
                            <li v-for="city in cities" @click="changeCity( city )">
                                {{ city.name }}
                            </li>
                        </ul>
                    </v-card-text>
              </v-card>
          </v-dialog>
          <v-dialog
              content-class="dialog-update"
              v-model="dialogUpdate"
              persistent
              width="300"
            >
              <v-card color="primary">
                <v-card-text>
                  <p>Initialisation<br><small><i>Le premier chargement peut prendre 1 à 2 min...</i></small></p>
                  <v-progress-linear
                    indeterminate
                    color="#5a6cae"
                    class="mb-0"
                  ></v-progress-linear>
                </v-card-text>
              </v-card>
            </v-dialog>
            <updater></updater>

</div>
</template>

<script>
    import { mapState } from 'vuex'
    import VueRouter from 'vue-router'
    import Updater from './parts/Updater.vue'
    export default {
        name: 'Container',
        components: { Updater },
        data() {
            return {
                dialogCities: false,
                dialogUpdate: true,
                transitionName: 'fade_old',
            }
        },
        async mounted() {
            try {
                await this.$store.dispatch('fetchGyms')
            } finally {
                this.dialogUpdate = false;
                setInterval( this.fetch, 60000, 'auto' );
            }
        },
        computed: {
            cities() {
                return this.$store.state.cities;
            },
            currentCity() {
                return this.$store.state.currentCity;
            },
            user() {
                return this.$store.state.user;
            },
            isAdmin() {
                let isAdmin = parseInt(this.currentCity.permissions) >= 30;
                let isModo = parseInt(this.currentCity.permissions) >= 20 && this.user.permissions[this.currentCity.guilds[0].id].find(val => val != 'raid_delete' && val != 'raidex_add' )
                return isAdmin || isModo;
            }
        },
        watch: {
            currentCity: function( val ) {
                if( val && val !== undefined ) this.fetch();
            },
            '$route' (to, from) {
                const toDepth = to.path.split('/').length
                const fromDepth = from.path.split('/').length
                console.log(toDepth);
                //console.log(fromDepth);
                //this.transitionName = toDepth < fromDepth ? 'slide-right' : 'slide-left'
             }
        },
        methods: {
            fetch() {
                this.$store.dispatch('autoFetchData');
            },
            async changeCity( city ) {
                this.dialogCities = false;
                this.dialogUpdate = true;
                try {
                    await this.$store.dispatch('changeCity', city)
                } finally {
                    this.dialogUpdate = false;
                }
            }
        }
    }
</script>
