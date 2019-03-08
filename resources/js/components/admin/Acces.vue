<template>
    <div>
        <div class="settings-section">
            <v-subheader>Accès</v-subheader>
            <div class="setting">
                <label>Qui peut accéder à la Map</label>
                <v-btn-toggle v-model="map_access_rule" mandatory>
                    <v-btn value="everyone">Tous les utilisateurs</v-btn>
                    <v-btn value="specific_roles">Seulement certains roles</v-btn>
                </v-btn-toggle>
            </div>
            <div v-if="map_access_rule == 'specific_roles'" class="setting">
                <label>Roles autorisés</label>
                <select multiple="true" v-if="roles" v-model="map_access_roles">
                    <option v-for="role in roles" :value="role.id">{{role.name}}</option>
                </select>
            </div>
            <v-subheader>Administration</v-subheader>
            <div class="setting">
                <label>Roles des administrateurs</label>
                <p class="description">Si les utilisateurs disposent d'un des roles sélectionnés, il seront automatiquement définis comme administrateurs de la map.</p>
                <select multiple="true" v-if="roles" v-model="map_access_admin_roles">
                    <option v-for="role in roles" :value="role.id">{{role.name}}</option>
                </select>
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
                map_access_rule: 'everyone',
                map_access_roles: [],
                map_access_admin_roles: [],
                roles: [],
            }
        },
        created() {
            this.fetch();
            this.fetchDiscordRoles();
        },
        methods: {
            fetch() {
                axios.get('/api/user/cities/'+this.$store.state.currentCity.id+'/guilds/'+this.$route.params.id+'/settings').then( res => {
                    if( res.data.map_access_rule ) this.map_access_rule = res.data.map_access_rule;
                    if( res.data.map_access_roles ) this.map_access_roles = res.data.map_access_roles;
                    if( res.data.map_access_admin_roles ) this.map_access_admin_roles = res.data.map_access_admin_roles;
                }).catch( err => {
                    //No error
                });
            },
            fetchDiscordRoles() {
                axios.get('/api/user/cities/'+this.$store.state.currentCity.id+'/guilds/'+this.$route.params.id+'/roles').then( res => {
                    console.log(res.data);
                    this.roles = res.data;
                }).catch( err => {
                    //No error
                });
            },
            submit() {
                const args = {
                    settings: {
                        map_access_rule: this.map_access_rule,
                        map_access_roles: this.map_access_roles,
                        map_access_admin_roles: this.map_access_admin_roles
                    }
                };
                this.save(args);
            },
            save( args ) {
                this.$store.commit('setSnackbar', {message: 'Enregistrement en cours'})
                this.loading = true;
                axios.put('/api/user/cities/'+this.$store.state.currentCity.id+'/guilds/'+this.$route.params.id+'/settings', args).then( res => {
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
