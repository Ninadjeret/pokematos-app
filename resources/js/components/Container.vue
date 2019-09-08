<template>
<div id="app__container" :class="'template-'+$route.name" data-app v-if="currentCity">
        <v-toolbar fixed app color="primary" dark>
            <v-btn v-if="$route.meta.parent" :to="{ name: $route.meta.parent}" icon><v-icon>arrow_back</v-icon></v-btn>
            <v-spacer v-if="$route.name == 'map'"></v-spacer>
            <v-toolbar-title v-if="$route.name == 'map'">
                <img src="https://assets.profchen.fr/img/logo_pokematos.png"> POKEMATOS <small v-if="this.$store.state.currentCity">{{ this.$store.state.currentCity.name }}</small>
            </v-toolbar-title>
            <v-toolbar-title v-if="$route.name != 'map'">{{$route.meta.title}}</v-toolbar-title>
            <v-btn v-if="cities &&  cities.length > 1 && $route.name == 'map'" icon @click.stop="dialogCities = true">
                <v-icon>location_city</v-icon>
            </v-btn>
            <v-spacer></v-spacer>
        </v-toolbar>


        <v-content>
            <v-container>
                <transition :name="transitionName">
                    <router-view></router-view>
                </transition>
                <snackbar></snackbar>
            </v-container>
          </v-content>


          <v-footer app v-if="!$route.meta.parent">
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
                    v-if="parseInt(currentCity.permissions) >= 10 && user.permissions[currentCity.guilds[0].id].find(val => val != 'raid_delete' && val != 'raidex_add' )"
                    to="/admin"
                    color="primary"
                    flat value="recent"
                >
                  <span>Admin</span>
                  <v-icon>build</v-icon>
                </v-btn>
              </v-bottom-nav>
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
          </v-footer>

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
                transitionName: 'fade_old',
            }
        },
        mounted() {
            this.$store.commit('fetchCities');
            this.$store.commit('fetchUser');
            if( this.currentCity && this.currentCity !== undefined ) {
                this.$store.commit('fetchGyms');
                this.fetch();
            }
            setInterval( this.fetch, 60000, 'auto' );
        },
        computed: mapState([
                'cities', 'currentCity', 'user'
        ]),
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
            changeCity( city ) {
                this.dialogCities = false;
                this.$store.commit('fetchZones')
            }
            /*test() {
                console.log('refresh-data')
            },
            syncData() {
                this.loadData();
                setInterval( this.loadData, 60000, 'auto' );
            },

            getUser() {
                axios.get('/api/user').then( res => {
                    this.user = res.data
                    localStorage.setItem('pokematos_user', JSON.stringify(res.data));
                }).catch( err => {
                    //No error
                });
            },
            */
        }
    }
</script>
