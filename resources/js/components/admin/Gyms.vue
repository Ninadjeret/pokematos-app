<template>
    <div>
        <div class="settings-section">
            <div class="search__wrapper">
                <v-text-field single-line hide-details outline v-model="search" label="Recherche"></v-text-field>
            </div>
            <v-list>
            <template v-for="(gym, index) in filteredGyms">
              <v-list-tile :key="gym.id" :to="{ name: 'admin.gyms.edit', params: { id: gym.id } }">
                <v-list-tile-content>
                  <v-list-tile-title>
                      {{gym.name}} <span v-if="gym.zone.name" class=""> // {{gym.zone.name}}</span>
                  </v-list-tile-title>
                </v-list-tile-content>
              </v-list-tile>
              <v-divider></v-divider>
            </template>
          </v-list>
            <v-btn dark fixed bottom right fab><v-icon>add</v-icon></v-btn>
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
                gyms: [],
            }
        },
        computed: {
            filteredGyms() {
                return this.gyms.filter((gym) => {
                    let matchingTitle = 1;
                    let matchingZone = 1;
                    if (this.search != null) {
                        matchingTitle = gym.name.toLowerCase().indexOf(this.search.toLowerCase()) > -1;
                        matchingZone = gym.zone.name.toLowerCase().indexOf(this.search.toLowerCase()) > -1;
                    }
                    return (matchingTitle || matchingZone);
                });
            },
        },
        created() {
            this.fetchGyms();
        },
        methods: {
            fetchGyms() {
                axios.get('/api/user/cities/'+this.$store.state.currentCity.id+'/gyms').then( res => {
                    this.gyms = res.data;
                }).catch( err => {
                    //No error
                });
            },
        }
    }
</script>
