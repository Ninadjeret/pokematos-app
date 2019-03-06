<template>
    <div>
        <div class="settings-section">
            <v-subheader>Accès</v-subheader>
            <div class="setting">
                <label>Qui peut accéder à la Map</label>
                <v-btn-toggle v-model="mapAccessRule" mandatory>
                    <v-btn value="left">Tous les utilisateurs</v-btn>
                    <v-btn value="center">Seulement certains roles</v-btn>
                </v-btn-toggle>
            </div>
            <v-subheader>Administration</v-subheader>
            <div class="setting">
                <label>Nom</label>
                <input v-model="name" type="text">
            </div>


            <v-btn dark fixed bottom right fab @click="submit()">
                <v-progress-circular v-if="loading" indeterminate color="primary"></v-progress-circular>
                <v-icon v-else>save</v-icon>
            </v-btn>
        </div>

    </div>
</template>

<script>
    export default {
        name: 'AdminAccess',
        data() {
            return {
                loading: false,
                name: '',
                mapAccessRule: 'left'
            }
        },
        created() {
            this.fetch();
        },
        methods: {
            fetch() {
                axios.get('/api/user/cities/'+this.$store.state.currentCity.id+'/zones/'+this.$route.params.id).then( res => {
                    this.name = res.data.name;
                }).catch( err => {
                    //No error
                });
            },
            fetchDiscordRoles() {
                axios.get('/api/user/cities/'+this.$store.state.currentCity.id+'/zones/'+this.$route.params.id).then( res => {
                    this.name = res.data.name;
                }).catch( err => {
                    //No error
                });
            },
            submit() {
                const args = {
                    name: this.name
                };
                this.save(args);
            },
            save( args ) {
                this.$store.commit('setSnackbar', {message: 'Enregistrement en cours'})
                this.loading = true;
                axios.put('/api/user/cities/'+this.$store.state.currentCity.id+'/zones/'+this.$route.params.id, args).then( res => {
                    this.$store.commit('setSnackbar', {
                        message: 'Enregistrement effectué',
                        timeout: 1500
                    })
                    this.loading = false
                }).catch( err => {
                    this.$store.commit('setSnackbar', {
                        message: 'Problème lors de l\'enregistrement',
                        timeout: 1500
                    })
                    this.loading = false
                });
            },
        }
    }
</script>
