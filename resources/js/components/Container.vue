<template>
<div id="app__container" :class="'template-'+$route.meta.id" data-app>
        <v-toolbar color="primary" dark>
            <v-spacer v-if="$route.meta.id == 'map'"></v-spacer>
            <v-toolbar-title v-if="$route.meta.id == 'map'">
                <img src="https://assets.profchen.fr/img/logo_pokematos.png"> POKEMATOS <small v-if="this.$store.state.currentCity">{{ this.$store.state.currentCity.name }}</small>
            </v-toolbar-title>
            <v-toolbar-title v-if="$route.meta.id != 'map'">{{$route.name}}</v-toolbar-title>
            <v-spacer></v-spacer>
            <v-btn v-if="cities &&  cities.length > 1 && $route.meta.id == 'map'" icon @click.stop="dialogCities = true">
                <v-icon>location_city</v-icon>
            </v-btn>
        </v-toolbar>

        <v-bottom-nav :value="true" absolute color="white">
          <v-btn to="/" color="primary" flat value="recent" >
            <span>Map</span>
            <v-icon>map</v-icon>
          </v-btn>

          <v-btn to="/list" color="primary" flat value="recent" >
            <span>Liste</span>
            <v-icon>notifications_active</v-icon>
          </v-btn>

          <v-btn to="/settings" color="primary" flat value="recent" >
            <span>Réglages</span>
            <v-icon>settings</v-icon>
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


        <router-view></router-view>
</div>
</template>

<script>
    import { mapState } from 'vuex'
    import VueRouter from 'vue-router'
    export default {
        name: 'Container',
        data() {
            return {
                dialogCities: false,
            }
        },
        mounted() {
            this.$store.commit('fetchCities');
            //this.$store.dispatch('fetchData');
            if( this.currentCity && this.currentCity !== undefined ) this.fetch();
            setInterval( this.fetch, 60000, 'auto' );
        },
        computed: mapState([
                'cities', 'currentCity'
        ]),
        watch: {
            currentCity: function( val ) {
                if( val && val !== undefined ) this.fetch();
            },
        },
        methods: {
            fetch() {
                this.$store.dispatch('fetchData');
            },
            changeCity( city ) {
                this.dialogCities = false;
                this.$store.dispatch('changeCity', {city: city})
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
