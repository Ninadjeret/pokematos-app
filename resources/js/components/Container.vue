 <template>
<div id="app__container" :class="'template-'+$route.name.split('.').join('-')" data-app v-if="currentCity && currentCity !== 'undefined'">
        <v-toolbar fixed app color="primary" dark>
            <v-btn v-if="$route.meta.parent" :to="{ name: $route.meta.parent}" icon><v-icon>arrow_back</v-icon></v-btn>
            <v-spacer class="hidden-md-and-up" v-if="$route.name == 'map'"></v-spacer>
            <img v-if="$route.name == 'map'" :src="baseUrl+'/storage/img/static/logo_pokematos_256.png'">
            <v-toolbar-title v-if="$route.name == 'map'">
                POKEMATOS
            </v-toolbar-title>
            <v-toolbar-title v-if="$route.name != 'map'">{{$route.meta.title}}</v-toolbar-title>
            <div class="current-city" v-if="$route.name == 'map' && this.$store.state.currentCity && cities &&  cities.length === 1">{{ this.$store.state.currentCity.name }}</div>
            <div class="current-city multiple" v-if="$route.name == 'map' && this.$store.state.currentCity && cities &&  cities.length > 1" @click.stop="dialogCities = true">{{ this.$store.state.currentCity.name }}</div>
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

                <v-btn v-if="features.events" to="/events" color="primary" flat value="recent" >
                  <span>Évents</span>
                  <v-icon>event</v-icon>
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
            </v-toolbar-items>
        </v-toolbar>


        <v-content>
            <v-container fluid>
                <div id="bg1"></div>
                <transition name="fade">
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
                    <span v-if="lastChanges && lastChanges.lists.server > lastChanges.lists.local" class="unread"></span>
                  </v-btn>

                  <v-btn v-if="features.events" to="/events" color="primary" flat value="recent" >
                    <span>Évents</span>
                    <v-icon>event</v-icon>
                    <span v-if="lastChanges && lastChanges.events.server > lastChanges.events.local" class="unread"></span>
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
                            <li v-for="city in cities" @click="changeCity( city )" :key="city.id">
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
                mode: 'base',
            }
        },
        async mounted() {
            console.log(window.pokematos);
            try {
                await this.$store.dispatch('fetchGyms');
                this.$store.commit('fetchPokemon');
                this.fetchLastChanges();
            } finally {
                this.dialogUpdate = false;
                setInterval( this.fetch, 60000, 'auto' );
            }
        },
        computed: {
            features() {
                return window.pokematos.features;
            },
            cities() {
                return this.$store.state.cities;
            },
            currentCity() {
                return this.$store.state.currentCity;
            },
            user() {
                return this.$store.state.user;
            },
            baseUrl() {
                return window.pokematos.baseUrl;
            },
            lastChanges() {
                if( this.$store.state.settings === null ) return false;
                return this.$store.state.settings.lastChanges;
            },
            isAdmin() {
                let isAdmin = parseInt(this.currentCity.permissions) >= 30;
                let isModo =  this.canAccessCityParam('poi_edit') || this.canAccessCityParam('zone_edit') || this.canAccessCityParam('events_manage') || this.canAccessCityParam('boss_edit') || this.canAccessCityParam('quest_edit') || this.canAccessCityParam('rocket_bosses_edit')
                return isAdmin || isModo;
            }
        },
        watch: {
            /*currentCity: function( val ) {
                if( val && val !== undefined ) this.fetch();
            },*/
        },
        methods: {
            changeMode(mode) {
                this.mode = mode;
            },
            fetch() {
                console.log('Synchronisation auto');
                this.$store.dispatch('fetchGyms');
            },
            fetchLastChanges() {
                this.$store.commit('initSetting', {
                    setting: 'lastChanges',
                    value: {
                        'lists':{
                            local: Date.now() / 1000,
                            server: Date.now() / 1000,
                        },
                        'events':{
                            local: Date.now() / 1000,
                            server: Date.now() / 1000,
                        },
                        'admin':{
                            local: Date.now() / 1000,
                            server: Date.now() / 1000,
                        },
                    }
                });
                axios.get('/api/user/cities/'+this.$store.state.currentCity.id+'/last-changes').then( res => {
                    if( this.$store.state.settings === null ) return;
                    let lastChanges = this.$store.state.settings.lastChanges;
                    if( this.$store.state.settings.events === null ) return;
                    lastChanges.events.server = res.data.events;
                    lastChanges.lists.server = res.data.lists;
                    lastChanges.admin.server = res.data.admin;
                    this.$store.commit('setSetting', {
                        setting: 'lastChanges',
                        value: lastChanges
                    });
                });
            },
            async changeCity( city ) {
                this.dialogCities = false;
                this.dialogUpdate = true;
                try {
                    await this.$store.dispatch('changeCity', city)
                } finally {
                    this.dialogUpdate = false;
                }
            },
            canAccessCityParam( param ) {
                let auth = false;
                let that = this;
                this.currentCity.guilds.forEach( (guild, index) => {
                    if( that.user.permissions[guild.id].find(val => val === param ) ) {
                        auth = true;
                    }
                })
                return auth;
            }
        }
    }
</script>
