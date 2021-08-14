<template>
    <div>
        <div class="settings-section">
            <v-subheader>Description</v-subheader>
            <div class="setting">
                <label>Nom</label>
                <input v-model="name" type="text">
            </div>
            <div class="setting d-flex switch">
                <div>
                    <label>Quête d'évent</label>
                    <p class="description">Cette quête est liée à un événement ?</p>
                </div>
                <v-switch v-model="event"></v-switch>
            </div>
            <div class="settings-section">
                <v-subheader>Récompenses</v-subheader>
                <multiselect
                    :reset-after="true"
                    v-model="value"
                    :options="rewards"
                    track-by="name"
                    label="name"
                    placeholder="Ajouter une récompense"
                    @select="addReward">
                    <template slot="singleLabel" slot-scope="{ option }">
                        <strong>{{ option.name }}</strong>
                    </template>
                </multiselect>
                <div v-for="(reward, index) in rewards_selected" class="setting pokemon">
                    <img :src="reward.thumbnails.base">
                    <p>{{reward.name}}</p>
                    <v-btn flat icon color="deep-orange" @click="removeReward(index)">
                        <v-icon>close</v-icon>
                    </v-btn>
                </div>
            </div>
            <v-divider></v-divider>
            <div v-if="$route.params.id && Number.isInteger($route.params.id)">
                <v-subheader>Autres actions</v-subheader>
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
                value: null,
                name: '',
                event: false,
                objects: [],
                rewards_selected: [],
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
            rewards() {
                return this.objects.concat(this.pokemons);
            }
        },
        methods: {
            fetch() {
                axios.get('/api/user/quests/'+this.$route.params.id).then( res => {
                    console.log(res.data)
                    this.name = res.data.name;
                    this.event = res.data.event;
                    this.rewards_selected = res.data.rewards;
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
            fetchRewards() {
                axios.get('/api/user/quests/rewards').then( res => {
                    this.objects = res.data;
                });
            },
            addReward(selectedOption, id) {
                if( this.rewards_selected.length > 0 && this.rewards_selected.filter( reward => reward.name == selectedOption.name ).length > 0 ) return;
                this.rewards_selected.push(selectedOption);
            },
            removeReward(index) {
                this.rewards_selected.splice(index, 1);
            },
            submit() {
                let reward_ids = [];
                let pokemon_ids = [];
                this.rewards_selected.forEach((item, index) => {
                    if( item.pokedex_id ) {
                        pokemon_ids.push(item.id);
                    } else {
                        reward_ids.push(item.id);
                    }
                })
                const args = {
                    name: this.name,
                    event: this.event,
                    reward_ids: reward_ids,
                    pokemon_ids: pokemon_ids
                };
                console.log(args);
                if( this.$route.params.id && Number.isInteger(this.$route.params.id) ) {
                    this.save(args);
                } else {
                    this.create(args);
                }
            },
            save( args ) {
                this.$store.commit('setSnackbar', {message: 'Enregistrement en cours'})
                this.loading = true;
                axios.put('/api/user/quests/'+this.$route.params.id, args).then( res => {
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
            create( args ) {
                this.$store.commit('setSnackbar', {message: 'Enregistrement en cours'})
                this.loading = true;
                axios.post('/api/user/quests', args).then( res => {
                    this.$store.commit('setSnackbar', {
                        message: 'Enregistrement effectué',
                        timeout: 1500
                    })
                    this.loading = false
                    this.$router.push({ name: this.$route.meta.parent })
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
            destroy() {
                this.dialog = false;
                    this.$store.commit('setSnackbar', {message: 'Suppression en cours'})
                    axios.delete('/api/user/quests/'+this.$route.params.id).then( res => {
                        this.$store.commit('setSnackbar', {
                            message: 'suppression effectuée',
                            timeout: 1500
                        })
                        this.$router.push({ name: this.$route.meta.parent })
                    }).catch( err => {
                        let message = 'Problème lors de la suppression';
                        if( err.response.data ) {
                            message = err.response.data;
                        }
                        this.$store.commit('setSnackbar', {
                            message: message,
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
