<template>
    <div>
        <div class="settings-section">
            <v-subheader>Description</v-subheader>
            <div class="setting">
                <label>Nom</label>
                <p v-if="name == '@everyone'" class="description">Le nom du role @everyone ne peut pas être modifié</p>
                <p v-else class="description">@{{name}}</p>
                <input v-model="name" :disabled="(name == '@everyone')" type="text">
            </div>
            <div class="setting">
                <label>Catégorie</label>
                <select v-if="categories" v-model="category_id">
                    <option v-for="categorie in categories" :value="categorie.id">{{categorie.name}}</option>
                </select>
            </div>
            <div class="setting">
                <label>Ce role fait référence à</label>
                <v-btn-toggle v-model="type" mandatory>
                    <v-btn value="gym">Une arène</v-btn>
                    <v-btn value="zone">Une zone</v-btn>
                    <v-btn value="pokemon">Un Pokémon</v-btn>
                    <v-btn value="other">Autre chose</v-btn>
                </v-btn-toggle>
            </div>
            <div class="setting" v-if="type == 'gym' && gyms">
                <label>Arène liée</label>
                <select v-model="gym_id">
                    <option v-for="gym in gyms" :value="gym.id">{{gym.name}}</option>
                </select>
            </div>
            <div class="setting" v-if="type == 'zone' && zones">
                <label>Zone géographique liée</label>
                <select v-model="zone_id">
                    <option v-for="zone in zones" :value="zone.id">{{zone.name}}</option>
                </select>
            </div>
            <div class="setting" v-if="type == 'pokemon' && pokemons">
                <label>Pokémon lié</label>
                <select v-model="pokemon_id">
                    <option v-for="pokemon in pokemons" :value="pokemon.id">{{pokemon.name_fr}}</option>
                </select>
            </div>
            <v-divider></v-divider>
            <div v-if="this.$route.params.role_id">
                <v-subheader v-if="">Autres actions</v-subheader>
                <v-list-tile color="pink" @click="dialog = true">Supprimer le role</v-list-tile>
            </div>

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
    export default {
        name: 'AdminRolesEdit',
        data() {
            return {
                loading: false,
                dialog: false,
                name: '',
                type: 'other',
                gym_id: '',
                zone_id: '',
                pokemon_id: '',
                category_id: '',
                categories: [],
                gyms: []
            }
        },
        created() {
            this.fetchGyms();
            this.fetchZones();
            this.fetchPokemons();
            this.fetchCategories();
            if( this.$route.params.role_id ) {
                this.fetch();
            }
        },
        methods: {
            fetch() {
                axios.get('/api/user/guilds/'+this.$route.params.id+'/roles/'+this.$route.params.role_id).then( res => {
                    this.name = res.data.name;
                    this.type = res.data.type,
                    this.gym_id = res.data.gym_id;
                    this.zone_id = res.data.zone_id;
                    this.pokemon_id = res.data.pokemon_id;
                    this.category_id = res.data.category_id;
                    console.log(this.channel_id);
                }).catch( err => {
                    //No error
                });
            },
            fetchCategories() {
                axios.get('/api/user/guilds/'+this.$route.params.id+'/rolecategories').then( res => {
                    this.categories = res.data;
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
            fetchPokemons() {
                axios.get('/api/pokemons').then( res => {
                    this.pokemons = res.data;
                });
            },
            submit() {
                const args = {
                    name: this.name,
                    type: this.type,
                    gym_id: this.gym_id,
                    zone_id: this.zone_id,
                    pokemon_id: this.pokemon_id,
                    category_id: this.category_id,
                };
                if( this.$route.params.role_id ) {
                    this.save(args);
                } else {
                    this.create(args);
                }
            },
            save( args ) {
                this.$store.commit('setSnackbar', {message: 'Enregistrement en cours'})
                this.loading = true;
                axios.put('/api/user/guilds/'+this.$route.params.id+'/roles/'+this.$route.params.role_id, args).then( res => {
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
                axios.post('/api/user/guilds/'+this.$route.params.id+'/roles', args).then( res => {
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
                    axios.delete('/api/user/guilds/'+this.$route.params.id+'/roles/'+this.$route.params.role_id).then( res => {
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
            }
        }
    }
</script>
