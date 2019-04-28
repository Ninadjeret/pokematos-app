<template>
    <div>
        <div class="settings-section">
            <v-subheader>Description</v-subheader>
            <div class="setting">
                <label>Nom</label>
                <input v-model="name" type="text">
            </div>
            <v-subheader>Notifications</v-subheader>
            <div class="setting d-flex switch">
                <div>
                    <label>Utiliser les roles de la catégorie {{name}} comme des notifications</label>
                    <p class="description">Les joueurs pourront s'attribuer les roles selon leurs besoins et ainsi être notifiés lorsqu'ils seront utilisés.</p>
                </div>
                <v-switch v-model="notifications"></v-switch>
            </div>
            <div v-if="notifications" class="setting">
                <label>Salon d'inscription</label>
                <p class="description">C'est dans ce salon que le bot affichera les messages permettant aux joueurs de s'inscrire à un role</p>
                <select v-if="channels" v-model="channel_id">
                    <option v-for="channel in channels" :value="channel.id">{{channel.name}}</option>
                </select>
            </div>
            <v-subheader>Restrictions</v-subheader>
            <div class="setting d-flex switch">
                <div>
                    <label>Controler l'utilisation</label>
                    <p class="description">Limiter les mentions de cette catégorie à certains roles/salons ?</p>
                </div>
                <v-switch v-model="restricted"></v-switch>
            </div>
            <div v-if="restricted" class="setting">
                <div>
                    <label>Règles d'utilisation</label>
                </div>
                <div class="permissions">
                    <v-layout row wrap>
                        <v-flex xs12>
                              <permission
                                v-for="(permission, index) in permissions"
                                :key="index"
                                :permission="permission"
                                :channels="channels"
                                :roles="roles"
                                @delete-permission="deletePermission(index, permission)"
                              />
                      </v-flex>
                      </v-layout>
                      <div class="text-xs-center">
                            <v-btn class="bt--small bt--secondary" @click="addPermission">Ajouter une règle</v-btn>
                      </div>
                </div>
            </div>
            <div v-if="this.$route.params.category_id">
                <v-subheader v-if="">Autres actions</v-subheader>
                <v-list-tile color="pink" @click="dialog = true">Supprimer la catégorie</v-list-tile>
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
        name: 'AdminRolesCategoriesEdit',
        data() {
            return {
                loading: false,
                dialog: false,
                name: '',
                channel_id: '',
                channels: [],
                roles: [],
                notifications: false,
                restricted: false,
                permissions: [],
                permissions_to_delete: [],
            }
        },
        created() {
            this.fetchChannels();
            this.fetchRoles();
            if( this.$route.params.category_id ) {
                this.fetch();
            }
        },
        methods: {
            fetch() {
                axios.get('/api/user/guilds/'+this.$route.params.id+'/rolecategories/'+this.$route.params.category_id).then( res => {
                    this.name = res.data.name;
                    this.notifications = res.data.notifications;
                    this.channel_id = res.data.channel_discord_id;
                    this.restricted = res.data.restricted;
                    this.permissions = res.data.permissions;
                    console.log(res.data);
                }).catch( err => {
                    //No error
                });
            },
            fetchChannels() {
                axios.get('/api/user/cities/'+this.$store.state.currentCity.id+'/guilds/'+this.$route.params.id+'/channels').then( res => {
                    this.channels = res.data;
                })
            },
            fetchRoles() {
                axios.get('/api/user/cities/'+this.$store.state.currentCity.id+'/guilds/'+this.$route.params.id+'/roles').then( res => {
                    this.roles = res.data;
                })
            },
            submit() {
                const args = {
                    name: this.name,
                    notifications: this.notifications,
                    channel_discord_id: this.channel_id,
                    restricted: this.restricted,
                    permissions: this.permissions,
                    permissions_to_delete: this.permissions_to_delete,
                };
                if( this.$route.params.category_id ) {
                    this.save(args);
                } else {
                    this.create(args);
                }
            },
            save( args ) {
                this.$store.commit('setSnackbar', {message: 'Enregistrement en cours'})
                this.loading = true;
                axios.put('/api/user/guilds/'+this.$route.params.id+'/rolecategories/'+this.$route.params.category_id, args).then( res => {
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
                axios.post('/api/user/guilds/'+this.$route.params.id+'/rolecategories', args).then( res => {
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
                    axios.delete('/api/user/guilds/'+this.$route.params.id+'/rolecategories/'+this.$route.params.category_id).then( res => {
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
            addPermission() {
                this.permissions.push({
                    id: false,
                    channels: [],
                    roles: [],
                    type: 'auth',
                    open: true,
                });
          },
          deletePermission(index, permission) {
             if (permission.id) {
                 this.permissions_to_delete.push(permission.id);
             }
             this.permissions.splice(index, 1);
         },
    }
}
</script>
