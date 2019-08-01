<template>
    <div>
        <div class="settings-section">
            <v-subheader>Description</v-subheader>
            <div class="setting">
                <label>Nom</label>
                <input v-model="name" type="text">
            </div>
            <div class="setting">
                <label>Type de récompense</label>
                <v-btn-toggle v-model="reward_type" mandatory>
                    <v-btn value="object">Objet</v-btn>
                    <v-btn value="pokemon">Pokémon</v-btn>
                </v-btn-toggle>
            </div>
            <div v-if="reward_type == 'object'" class="setting">
                <label>Pokémon</label>
                <multiselect
                    v-model="reward"
                    :options="rewards"
                    track-by="id"
                    label="name"
                    :multiple="false"
                    placeholder="Choisir un objet">
                </multiselect>
            </div>
            <div v-if="reward_type == 'pokemon'" class="setting">
                <label>Pokémon</label>
                <multiselect
                    v-model="pokemon"
                    :options="pokemons"
                    track-by="id"
                    label="name_fr"
                    :multiple="false"
                    placeholder="Choisir un Pokémon">
                </multiselect>
            </div>
            <v-divider></v-divider>
            <div v-if="$route.params.id && Number.isInteger($route.params.id)">
                <v-subheader v-if="">Autres actions</v-subheader>
                <v-list-tile color="pink" @click="dialog = true">Supprimer la quête</v-list-tile>
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
    import Multiselect from 'vue-multiselect'
    export default {
        name: 'AdminQuest',
        components: { Multiselect },
        data() {
            return {
                loading: false,
                dialog: false,
                name: '',
                reward_type: 'object',
                reward: false,
                pokemon: false,
                rewards: [],
            }
        },
        created() {
            this.fetchRewards();
            if( this.$route.params.id && Number.isInteger(this.$route.params.id) ) {
                this.fetch();
            }
        },
        computed: {
            pokemons() {
                return this.$store.state.pokemons;
            },
        },
        methods: {
            fetch() {
                axios.get('/api/quests/'+this.$route.params.id).then( res => {
                    this.name = res.data.name;
                    this.reward_type = res.data.reward_type;
                    if( this.reward_type == 'object' ) {
                        this.reward = this.convertIdtoObject(res.data.reward_id, this.rewards);
                    } else {
                        this.pokemon = this.convertIdtoObject(res.data.pokemon_id, this.pokemons);
                    }
                }).catch( err => {
                    //No error
                });
            },
            fetchRewards() {
                axios.get('/api/quests/rewards').then( res => {
                    this.rewards = res.data;
                });
            },
            submit() {
                const args = {
                    name: this.name,
                    reward_type: this.reward_type,
                    pokemon_id: ( this.pokemon ) ? this.pokemon.id : null ,
                    reward_id: ( this.reward ) ? this.reward.id : null ,
                };
                if( this.$route.params.id && Number.isInteger(this.$route.params.id) ) {
                    this.save(args);
                } else {
                    this.create(args);
                }
            },
            save( args ) {
                this.$store.commit('setSnackbar', {message: 'Enregistrement en cours'})
                this.loading = true;
                axios.put('/api/quests/'+this.$route.params.id, args).then( res => {
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
                axios.post('/api/quests', args).then( res => {
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
                    axios.delete('/api/quests/'+this.$route.params.id).then( res => {
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
            convertIdtoObject( id, ObjectsReference ) {
                if( id.length === 0 ) return false;
                let item = ObjectsReference.find( el => el.id == id );
                if( item ) return item;
                return false;
            },
        }
    }
</script>
