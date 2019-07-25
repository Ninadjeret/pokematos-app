<template>
    <div>
        <div class="settings-section">

            <v-tabs v-model="tabs" fixed-tabs grow color="transparent" slider-color="white">
                <v-tab href="#general" class="primary--text">Général</v-tab>
                <v-tab href="#annonces" class="primary--text">Annonces</v-tab>
            </v-tabs>

            <v-tabs-items v-model="tabs">
                <v-tab-item value="general">
                    <v-subheader>Général</v-subheader>
                    <div class="setting">
                        <label>Nom</label>
                        <input v-model="name" type="text">
                    </div>
                    <div class="setting">
                        <label>Channel</label>
                        <select v-if="channels" v-model="channel_discord_id">
                            <option v-for="channel in channels" :value="channel.id">{{channel.name}}</option>
                        </select>
                    </div>
                    <div v-if="this.$route.params.connector_id">
                        <v-subheader v-if="">Autres actions</v-subheader>
                        <v-list-tile color="pink" @click="dialog = true">Supprimer le connecteur</v-list-tile>
                    </div>
                </v-tab-item>
                <v-tab-item value="annonces">
                        <v-subheader>Arênes</v-subheader>
                        <div class="setting">
                            <label>Filtrer les arènes</label>
                            <v-btn-toggle v-model="filter_gym_type" mandatory>
                                <v-btn value="none">Aucun filtre</v-btn>
                                <v-btn value="zone">Par zone(s)</v-btn>
                                <v-btn value="gym">Par arêne(s)</v-btn>
                            </v-btn-toggle>
                        </div>
                        <div v-if="filter_gym_type == 'zone'" class="setting">
                            <label>Zone(s)</label>
                            <multiselect
                                v-model="filter_gym_zone"
                                :options="zones"
                                track-by="id"
                                label="name"
                                :multiple="true"
                                placeholder="Ajouter une zone">
                            </multiselect>
                        </div>
                        <div v-if="filter_gym_type == 'gym'" class="setting">
                            <label>Arêne(s)</label>
                            <multiselect
                                v-model="filter_gym_gym"
                                :options="gyms"
                                track-by="id"
                                label="name"
                                :multiple="true"
                                placeholder="Ajouter une arêne">
                            </multiselect>
                        </div>
                        <v-subheader>Boss</v-subheader>
                        <div class="setting">
                            <label>Filtrer les Pokémon</label>
                            <v-btn-toggle v-model="filter_pokemon_type" mandatory>
                                <v-btn value="none">Aucun filtre</v-btn>
                                <v-btn value="level">Par niveau(x)</v-btn>
                                <v-btn value="pokemon">Par Pokémon(s)</v-btn>
                            </v-btn-toggle>
                        </div>
                        <div v-if="filter_pokemon_type == 'level'" class="setting">
                            <label>Niveau(x) de boss</label>
                            <multiselect
                                v-model="filter_pokemon_level"
                                :options="levels"
                                track-by="id"
                                label="name"
                                :multiple="true"
                                placeholder="Ajouter un niveau de boss">
                            </multiselect>
                        </div>
                        <div v-if="filter_pokemon_type == 'pokemon'" class="setting">
                            <label>Pokémon</label>
                            <multiselect
                                v-model="filter_pokemon_pokemon"
                                :options="pokemons"
                                track-by="id"
                                label="name_fr"
                                :multiple="true"
                                placeholder="Ajouter un Pokémon">
                            </multiselect>
                        </div>
                        <v-subheader>Format de l'annonce</v-subheader>
                        <div class="setting">
                            <label>Format</label>
                            <v-btn-toggle v-model="format" mandatory>
                                <v-btn value="auto">Automatique</v-btn>
                                <v-btn value="custom">Personnalisé</v-btn>
                            </v-btn-toggle>
                        </div>
                        <div class="setting" v-if="format == 'custom'">
                            <label>Message personnalisé avant pop</label>
                            <p class="description">
                                Utilisez les tags suivants pour afficher des propriétés du raid :<br>
                                {arene_nom}<br>
                                {arene_nom_custom}<br>
                                {arene_zone}<br>
                                {arene_description}<br>
                                {arene_gmaps}<br>
                                {raid_niveau}<br>
                                {raid_debut}<br>
                                {raid_fin}<br>
                                {utilisateur}<br>
                            </p>
                            <input v-model="custom_message_before" type="text">
                        </div>
                        <div class="setting" v-if="format == 'custom'">
                            <label>Message personalisé après pop</label>
                            <p class="description">
                                Utilisez les tags suivants pour afficher des propriétés du raid :<br>
                                {arene_nom}<br>
                                {arene_nom_custom}<br>
                                {arene_zone}<br>
                                {arene_description}<br>
                                {arene_gmaps}<br>
                                {raid_niveau}<br>
                                {raid_pokemon}<br>
                                {raid_debut}<br>
                                {raid_fin}<br>
                                {utilisateur}<br>
                            </p>
                            <input v-model="custom_message_after" type="text">
                        </div>
                </v-tab-item>
            </v-tabs-items>

            <v-btn dark fixed bottom right fab @click="submit()">
                <v-progress-circular v-if="loading" indeterminate color="primary"></v-progress-circular>
                <v-icon v-else>save</v-icon>
            </v-btn>
        </div>
        <v-dialog v-model="dialog" persistent max-width="290">
        <v-card>
          <v-card-title class="headline">Supprimer {{name}} ?</v-card-title>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn flat @click="dialog = false">Annuler</v-btn>
            <v-btn flat @click="destroy()">Confirmer</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </div>
</template>

<script>
    import Multiselect from 'vue-multiselect'

    export default {
        name: 'AdminRolesCategoriesEdit',
        components: { Multiselect },
        data() {
            return {
                loading: false,
                dialog: false,
                tabs: null,
                channels: [],
                pokemons: [],
                gyms: [],
                zones: [],
                levels: [
                    {id:1,name: '1 tête'},
                    {id:2,name: '2 têtes'},
                    {id:3,name: '3 têtes'},
                    {id:4,name: '4 têtes'},
                    {id:5,name: '5 têtes'},
                    {id:6,name: 'EX'},
                ],
                name: '',
                channel_discord_id: false,
                filter_gym_type: 'none',
                filter_gym_zone: [],
                filter_gym_gym: [],
                filter_pokemon_type: 'none',
                filter_pokemon_level: [],
                filter_pokemon_pokemon: [],
                format: 'auto',
                custom_message_before: '',
                custom_message_after: '',
            }
        },
        created() {
            this.fetchChannels();
            this.fetchPokemons();
            this.fetchGyms();
            this.fetchZones();
            if( this.$route.params.connector_id ) {
                this.fetch();
            }
        },
        computed: {
            channelName() {
                return 'Toto';
            },
        },
        methods: {
            fetch() {
                axios.get('/api/user/guilds/'+this.$route.params.id+'/connectors/'+this.$route.params.connector_id).then( res => {
                    this.name = res.data.name;
                    this.channel_discord_id = res.data.channel_discord_id;
                    this.filter_gym_type = res.data.filter_gym_type;
                    this.filter_gym_zone = this.convertIdstoObjects(res.data.filter_gym_zone, this.zones);
                    this.filter_gym_gym = this.convertIdstoObjects(res.data.filter_gym_gym, this.gyms);
                    this.filter_pokemon_type = res.data.filter_pokemon_type;
                    this.filter_pokemon_level = this.convertIdstoObjects(res.data.filter_pokemon_level, this.levels);
                    this.filter_pokemon_pokemon = this.convertIdstoObjects(res.data.filter_pokemon_pokemon, this.pokemons);
                    this.format = res.data.format;
                    this.custom_message_before = res.data.custom_message_before;
                    this.custom_message_after = res.data.custom_message_after;
                }).catch( err => {
                    //No error
                });
            },
            fetchChannels() {
                axios.get('/api/user/cities/'+this.$store.state.currentCity.id+'/guilds/'+this.$route.params.id+'/channels').then( res => {
                    this.channels = res.data;
                })
            },
            fetchPokemons() {
                axios.get('/api/pokemons').then( res => {
                    this.pokemons = res.data;
                });
            },
            fetchGyms() {
                axios.get('/api/user/cities/'+this.$store.state.currentCity.id+'/gyms').then( res => {
                    this.gyms = res.data;
                });
            },
            fetchZones() {
                axios.get('/api/user/cities/'+this.$store.state.currentCity.id+'/zones').then( res => {
                    this.zones = res.data;
                });
            },
            submit() {
                const args = {
                    name: this.name,
                    channel_discord_id: this.channel_discord_id,
                    filter_gym_type: this.filter_gym_type,
                    filter_gym_zone: this.convertObjectsToIds(this.filter_gym_zone),
                    filter_gym_gym: this.convertObjectsToIds(this.filter_gym_gym),
                    filter_pokemon_type: this.filter_pokemon_type,
                    filter_pokemon_level: this.convertObjectsToIds(this.filter_pokemon_level),
                    filter_pokemon_pokemon: this.convertObjectsToIds(this.filter_pokemon_pokemon),
                    format: this.format,
                    custom_message_before: this.custom_message_before,
                    custom_message_after: this.custom_message_after,
                };
                if( this.$route.params.connector_id ) {
                    this.save(args);
                } else {
                    this.create(args);
                }
            },
            save( args ) {
                this.$store.commit('setSnackbar', {message: 'Enregistrement en cours'})
                this.loading = true;
                axios.put('/api/user/guilds/'+this.$route.params.id+'/connectors/'+this.$route.params.connector_id, args).then( res => {
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
            create( args ) {
                this.$store.commit('setSnackbar', {message: 'Enregistrement en cours'})
                this.loading = true;
                axios.post('/api/user/guilds/'+this.$route.params.id+'/connectors', args).then( res => {
                    this.$store.commit('setSnackbar', {
                        message: 'Enregistrement effectué',
                        timeout: 1500
                    })
                    this.loading = false
                    this.$router.push({ name: this.$route.meta.parent })
                }).catch( err => {
                    this.$store.commit('setSnackbar', {
                        message: 'Problème lors de l\'enregistrement',
                        timeout: 1500
                    })
                    this.loading = false
                });
            },
            destroy() {
                this.dialog = false;
                    this.$store.commit('setSnackbar', {message: 'Suppression en cours'})
                    axios.delete('/api/user/guilds/'+this.$route.params.id+'/connectors/'+this.$route.params.connector_id).then( res => {
                        this.$store.commit('setSnackbar', {
                            message: 'suppression effectuée',
                            timeout: 1500
                        })
                        this.$router.push({ name: this.$route.meta.parent })
                    }).catch( err => {
                        this.$store.commit('setSnackbar', {
                            message: 'Problème lors de la suppression',
                            timeout: 1500
                        })
                    });
            },
            convertObjectsToIds( array ) {
                let arrayIds = [];
                if( array.length === 0 ) return arrayIds;
                array.forEach(function(item){
                  arrayIds.push(item.id);
                });
                return arrayIds;
            },
            convertIdstoObjects( arrayIds, ObjectsReference ) {
                let arrayObjects = [];
                console.log(arrayIds);
                console.log(ObjectsReference);
                console.log( Array.isArray(arrayIds) );

                if( arrayIds.length === 0 ) return arrayObjects;
                arrayIds.forEach(function(id){
                    console.log('tyty');
                    let objectToAdd = ObjectsReference.find( el => el.id == id );
                    if( objectToAdd ) arrayObjects.push(objectToAdd);
                });
                return arrayObjects;
            },
    }
}
</script>
