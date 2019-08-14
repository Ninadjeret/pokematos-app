<template>
    <div>
        <div class="settings-section">
            <v-subheader>Position</v-subheader>

            <div class="setting">
                <label>Position par défaut</label>
                <map-field id="toto" v-model="coordinates"></map-field>
            </div>

            <v-btn dark fixed bottom right fab @click="submit()">
                <v-progress-circular v-if="loading" indeterminate color="primary"></v-progress-circular>
                <v-icon v-else>save</v-icon>
            </v-btn>
        </div>

    </div>
</template>

<script>
    import MapField from '../../components/parts/MapField.vue'
    export default {
        name: 'adminMap',
        components: { MapField },
        data() {
            return {
                loading: false,
                coordinates: {
                    lat: this.$store.state.currentCity.lat,
                    lng: this.$store.state.currentCity.lng
                }
            }
        },
        computed: {
            currentCity() {
                return this.$store.state.currentCity
            }
        },
        created() {
            this.$store.commit('fetchCities');
        },
        methods: {
            submit() {
                const args = {
                    lat: this.coordinates.lat,
                    lng: this.coordinates.lng,
                };
                this.save(args);
            },
            save( args ) {
                this.$store.commit('setSnackbar', {message: 'Enregistrement en cours'})
                this.loading = true;
                axios.put('/api/user/cities/'+this.$store.state.currentCity.id, args).then( res => {
                    this.$store.commit('fetchCities');
                    this.$store.commit('setSnackbar', {
                        message: 'Enregistrement effectué',
                        timeout: 1500
                    })
                    this.loading = false
                }).catch( err => {
                    let message = 'Problème lors de l\'enregistrement';
                    if( err.response.data ) {
                        message = err.response.data;
                    }
                    this.$store.commit('setSnackbar', {
                        message: message,
                        timeout: 1500
                    })
                    this.loading = false
                });
            },
        }
    }
</script>
