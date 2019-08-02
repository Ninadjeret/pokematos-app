<template>
    <div>
        <div class="settings-section">
            <div class="setting d-flex switch">
                <div>
                    <label>Actier le signalement des raids EX</label>
                    <p class="description">Les joueurs de votre communauté pourront ainsi annoncer des raids EX</p>
                </div>
                <v-switch v-model="raidsex_active"></v-switch>
            </div>
        </div>
        <div v-if="raidsex_active" class="settings-section">
            <v-subheader>Droits d'accès</v-subheader>
            <div class="setting d-flex switch">
                <div>
                    <label>Autoriser tous les joueurs à annoncer des Raids EX depuis la map ?</label>
                    <p class="description">Sinon seuls les modérateurs et/ou admins pourront l'effectuer</p>
                </div>
                <v-switch v-model="raidsex_access_everyone"></v-switch>
            </div>
            <v-subheader>Salons temporaires</v-subheader>
            <div class="setting d-flex switch">
                <div>
                    <label>Créer des salons temporaires</label>
                    <p class="description">Ces salons seront créés lors de l'annonce et supprimés automatiquement à la fin du raid EX</p>
                </div>
                <v-switch v-model="raidsex_channels"></v-switch>
            </div>
            <div v-if="raidsex_channels" class="setting">
                <label>Catégorie de salon</label>
                <p class="description">Le salon temporaire sera créé dans la catégorie choisie. (les droits appliqués au salon seront les mêmes que ceux de la caégorie)</p>
                <select v-if="channels_categories" v-model="raidsex_channel_category_id">
                    <option v-for="channel in channels_categories" :value="channel.id.toString()">{{channel.name}}</option>
                </select>
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
        name: 'AdminRaidsEx',
        data() {
            return {
                loading: false,
                raidsex_active: false,
                raidsex_channels: false,
                raidsex_channel_category_id: '',
                raidsex_access_everyone: true,
                channels_categories: [],

            }
        },
        created() {
            this.fetch();
            this.fetchDiscordChannelCategories();
        },
        methods: {
            fetch() {
                axios.get('/api/user/cities/'+this.$store.state.currentCity.id+'/guilds/'+this.$route.params.id+'/settings').then( res => {
                    console.log(res.data);
                    this.raidsex_active = parseInt(res.data.raidsex_active);
                    this.raidsex_channels = parseInt(res.data.raidsex_channels);
                    this.raidsex_access_everyone = parseInt(res.data.raidsex_access_everyone);
                    this.raidsex_channel_category_id = res.data.raidsex_channel_category_id.toString();                
                }).catch( err => {
                    //No error
                });
            },
            fetchDiscordChannelCategories() {
                axios.get('/api/user/cities/'+this.$store.state.currentCity.id+'/guilds/'+this.$route.params.id+'/channelcategories').then( res => {
                    this.channels_categories = res.data;
                }).catch( err => {
                    //No error
                });
            },
            submit() {
                const args = {
                    settings: {
                        raidsex_active: this.raidsex_active,
                        raidsex_channels: this.raidsex_channels,
                        raidsex_channel_category_id: this.raidsex_channel_category_id,
                        raidsex_access_everyone: this.raidsex_access_everyone,
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
