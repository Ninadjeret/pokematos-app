<template>
    <div>
        <div class="settings-section">
            <div class="search__wrapper">
                <v-text-field single-line hide-details outline v-model="search" label="Recherche"></v-text-field>
            </div>
            <v-list>
            <template v-for="(zone, index) in filteredZones">
              <v-list-tile :key="zone.id" :to="{ name: 'admin.zones.edit', params: { id: zone.id } }">
                <v-list-tile-content>
                  <v-list-tile-title>
                      {{zone.name}}
                  </v-list-tile-title>
                </v-list-tile-content>
              </v-list-tile>
              <v-divider></v-divider>
            </template>
          </v-list>
            <v-btn dark fixed bottom right fab :to="{ name: 'admin.zones.add' }"><v-icon>add</v-icon></v-btn>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'AdminZones',
        data() {
            return {
                search: null,
                zones: [],
            }
        },
        computed: {
            filteredZones() {
                return this.zones.filter((zone) => {
                    let matchingTitle = 1;
                    if (this.search != null) {
                        matchingTitle = zone.name.toLowerCase().indexOf(this.search.toLowerCase()) > -1;
                    }
                    return (matchingTitle);
                });
            },
        },
        created() {
            this.fetchZones();
        },
        methods: {
            fetchZones() {
                axios.get('/api/user/cities/'+this.$store.state.currentCity.id+'/zones').then( res => {
                    this.zones = res.data;
                    let message = 'Problème lors de la récupération';
                    if( err.response.data ) {
                        message = err.response.data;
                    }
                    this.$store.commit('setSnackbar', {
                        message: message,
                        timeout: 1500
                    })
                });
            },
        }
    }
</script>
