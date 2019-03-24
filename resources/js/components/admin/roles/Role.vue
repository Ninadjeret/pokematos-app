<template>
    <div>
        <div class="settings-section">
            <v-subheader>Description</v-subheader>
            <div class="setting">
                <label>Nom</label>
                <p class="description">@{{name}}</p>
                <input v-model="name" type="text">
            </div>
            <div class="setting">
                <label>Catégorie</label>
                <select v-if="categories" v-model="category_id">
                    <option v-for="categorie in categories" :value="categorie.id">{{categorie.name}}</option>
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
                category_id: '',
                categories: []
            }
        },
        created() {
            this.fetchCategories();
            if( this.$route.params.role_id ) {
                this.fetch();
            }
        },
        methods: {
            fetch() {
                axios.get('/api/user/guilds/'+this.$route.params.id+'/roles/'+this.$route.params.role_id).then( res => {
                    this.name = res.data.name;
                    this.category_id = res.data.category_id;
                    console.log(this.channel_id);
                }).catch( err => {
                    //No error
                });
            },
            fetchCategories() {
                axios.get('/api/user/guilds/'+this.$route.params.id+'/rolecategories').then( res => {
                    this.categories = res.data;
                    console.log(this.categories);
                }).catch( err => {
                    //
                });
            },
            submit() {
                const args = {
                    name: this.name,
                    category_id: this.category_id
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
