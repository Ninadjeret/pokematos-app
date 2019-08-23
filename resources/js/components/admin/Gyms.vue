<template>
    <div>
        <div class="settings-section">
            <div class="search__wrapper">
                <v-text-field single-line hide-details outline v-model="search" label="Recherche"></v-text-field>
            </div>
            <v-list>
            <template v-for="(gym, index) in filteredGyms">
              <v-list-tile :key="gym.id" :to="{ name: 'admin.gyms.edit', params: { id: gym.id } }">
                  <v-list-tile-avatar>
                      <img :src="getPoiIcon(gym)">
                  </v-list-tile-avatar>
                  <v-list-tile-content>
                      <v-list-tile-title>
                          {{gym.name}} <span v-if="gym.zone" class=""> // {{gym.zone.name}}</span>
                      </v-list-tile-title>
                  </v-list-tile-content>
              </v-list-tile>
              <v-divider></v-divider>
            </template>
          </v-list>
            <v-btn dark fixed bottom right fab :to="{ name: 'admin.gyms.add' }"><v-icon>add</v-icon></v-btn>
        </div>
    </div>
</template>

<script>
    import { mapState } from 'vuex'
    export default {
        name: 'AdminGyms',
        data() {
            return {
                search: null,
            }
        },
        computed: {
            filteredGyms() {
                return this.gyms.filter((gym) => {
                    let matchingTitle = 1;
                    let matchingZone = 1;
                    let matchingEx = 1;
                    let matchingGym = 1;
                    let matchingStop = 1;
                    if (this.search != null) {
                        matchingTitle = gym.name.toLowerCase().indexOf(this.search.toLowerCase()) > -1;
                        matchingZone = gym.zone.name.toLowerCase().indexOf(this.search.toLowerCase()) > -1;
                        matchingEx = gym.ex && this.search.toLowerCase() == ':ex';
                        matchingGym = gym.gym && this.search.toLowerCase() == ':arene';
                        matchingStop = !gym.gym && this.search.toLowerCase() == ':pokestop';
                    }
                    return (matchingTitle || matchingZone || matchingEx || matchingGym || matchingStop);
                });
            },
            gyms() {
                return this.$store.state.gyms;
            }
        },
        created() {
            this.$store.commit('fetchGyms');
        },
        methods: {
            getPoiIcon( gym ) {
                let isGym = ( gym.gym ) ? '1' : '0' ;
                let isEx = ( gym.ex ) ? '1' : '0' ;
                return 'https://assets.profchen.fr/img/app/icon_poi_'+isGym+'_'+isEx+'_96.png'
            }
        }
    }
</script>
