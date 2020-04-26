<template>
    <div>
        <div class="settings-section">
            <v-subheader>Informations Pokématos</v-subheader>
            <div class="setting d-flex switch">
                <div>
                    <label>Autoriser Pokématos à vous transmettre des informations ?</label>
                    <p class="description">Vous pourrez ensuite définir quels types d'informations vous souhaitez recevoir</p>
                </div>
                <v-switch v-model="comadmin_active"></v-switch>
            </div>
            <div v-if="comadmin_active" class="setting">
                <label>Salon</label>
                <select v-if="channels" v-model="comadmin_channel_discord_id">
                    <option v-for="channel in channels" :value="channel.id">{{channel.name}}</option>
                </select>
            </div>
            <div v-if="comadmin_active" class="setting checkbox">
                <label>Filtrer la source</label>
                <v-checkbox
                    v-for="(type, index) in types"
                    v-model="comadmin_types"
                    :key="type.value"
                    :label="type.label"
                    :value="type.value">
                </v-checkbox>
            </div>
        </div>
        <v-btn dark fixed bottom right fab @click="submit()">
            <v-progress-circular v-if="loading" indeterminate color="primary"></v-progress-circular>
            <v-icon v-else>save</v-icon>
        </v-btn>
    </div>
</template>

<script>
    export default {
        name: 'adminSettings',
        data() {
            return {
                loading: false,
                channels: [],
                comadmin_active: false,
                comadmin_channel_discord_id: false,
                comadmin_types: [],
                types: [
                    {value: 'news', label: 'Nouveautés de Pokématos'},
                    {value: 'event_invit_sended', label: 'Invitation à des événments'},
                    {value: 'event_invit_accepted', label: 'Validation d\'invitations envoyées'},
                    {value: 'event_invit_refused', label: 'Refus d\'invitations envoyées'},
                    {value: 'event_invit_canceled', label: 'Annulation d\'invitation par l\'hote de l\'évenement'},
                ],
            }
        },
        created() {
            this.fetchChannels();
            this.fetch();
        },
        methods: {
            fetch() {
                axios.get('/api/user/cities/'+this.$store.state.currentCity.id+'/guilds/'+this.$route.params.id+'/settings').then( res => {
                    this.comadmin_active = res.data.comadmin_active;
                    this.comadmin_channel_discord_id = res.data.comadmin_channel_discord_id;
                    this.comadmin_types = res.data.comadmin_types;
                }).catch( err => {
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
            fetchChannels() {
                axios.get('/api/user/cities/'+this.$store.state.currentCity.id+'/guilds/'+this.$route.params.id+'/channels').then( res => {
                    this.channels = res.data;
                })
            },
            submit() {
                const args = {
                    settings: {
                        comadmin_active: this.comadmin_active,
                        comadmin_channel_discord_id: this.comadmin_channel_discord_id,
                        comadmin_types: this.comadmin_types,
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
