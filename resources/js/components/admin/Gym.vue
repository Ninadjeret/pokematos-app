<template>
    <div>
        <div class="settings-section">
            <v-subheader>Description</v-subheader>

            <div class="setting">
                <label>Nom personnalisé</label>
                <p class="description">Nom de l'arène, tel que vous souhaitez le voir apparaître sur Discord et le map</p>
                <input v-model="name" type="text">
            </div>

            <div class="setting">
                <label>Nom officiel</label>
                <p class="description">Nom de l’arène, tel qu'il est présent dans le jeu</p>
                <input v-model="niantic_name" type="text">
            </div>

            <div class="setting">
                <label>Description</label>
                <textarea v-model="description"></textarea>
            </div>

            <div class="setting">
                <label>Ville / Zone géographique</label>
                <select v-if="zones" v-model="zone_id">
                    <option v-for="zone in zones" :value="zone.id">{{zone.name}}</option>
                </select>
            </div>
            <div class="setting map">
                <label>Position</label>
                <map-field id="gym-coordinates" v-model="coordinates"></map-field>
            </div>

            <v-subheader>Arène</v-subheader>

            <div class="setting d-flex switch">
                <div>
                    <label>Arène</label>
                    <p class="description">Ce POI est-il une arêne ?</p>
                </div>
                <v-switch v-model="gym"></v-switch>
            </div>

            <div v-if="gym" class="setting d-flex switch">
                <div>
                    <label>Arêne Ex</label>
                    <p class="description">Nom de l’arène, tel qu'il est présent dans le jeu</p>
                </div>
                <v-switch v-model="ex"></v-switch>
            </div>

            <div v-if="gym" class="setting">
                <label>Alias de nom</label>
                <p class="description">Les alias de nom sont utilisés lors de la détection des raids pour identifier une arène dont le nom serait mal lu depuis l'image. <a href="https://www.pokematos.fr/documentation/alias-de-pois/">En savoir plus</a></p>
                <div class="alias" v-for="(alias, index) in aliases">
                    <input v-model="alias.name" type="text">
                    <v-btn small flat fab @click="removeAlias(index)"><v-icon>delete</v-icon></v-btn>
                </div>
                <div class="alias__add">
                    <v-btn small fab @click="addAlias"><v-icon>add</v-icon></v-btn>
                </div>
            </div>

            <div v-if="inAdmin && getId">
                <v-subheader v-if="">Autres actions</v-subheader>
                <v-list-tile color="pink" @click="dialog = true">Supprimer le POI</v-list-tile>
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
    import VueGoogleAutocomplete from 'vue-google-autocomplete'
    import MapField from '../../components/parts/MapField.vue'
    export default {
        name: 'AdminGym',
        components: { VueGoogleAutocomplete, MapField },
        props: {
            poiId: {
                type: Number,
                required: false,
                default: 0,
            },
        },
        data() {
            return {
                loading: false,
                dialog: false,
                zones: [],
                name: '',
                niantic_name: '',
                description: '',
                zone_id: '',
                ex: false,
                gym: false,
                aliases: [],
                coordinates:{lat: 1, lng: 1},
            }
        },
        computed: {
            inAdmin() {
                return this.poiId === 0;
            },
            getId() {
                let routeId = ( this.$route.params.poi_id && Number.isInteger(parseInt(this.$route.params.poi_id)) ) ? parseInt(this.$route.params.poi_id) : false ;
                let paramId = ( this.poiId > 0 ) ? parseInt(this.poiId) : false;
                if( routeId ) {
                    return routeId;
                } else if( paramId ) {
                    return paramId;
                } else {
                    return false;
                }
            }
        },
        created() {
            console.log(this.$route.params);
            this.fetchZones();
            if( this.getId ) {
                this.fetch();
            }
        },
        methods: {
            fetch() {
                axios.get('/api/user/cities/'+this.$store.state.currentCity.id+'/gyms/'+this.getId).then( res => {
                    this.name = res.data.name;
                    this.niantic_name = res.data.niantic_name;
                    this.description = res.data.description;
                    this.zone_id = (res.data.zone) ? res.data.zone.id : false ;
                    this.ex = res.data.ex;
                    this.gym = res.data.gym;
                    this.coordinates.lat = res.data.lat;
                    this.coordinates.lng = res.data.lng;
                    this.aliases = res.data.aliases;
                }).catch( err => {
                    let message = 'Problème lors de la récupération';
                    if( err.response && err.response.data ) {
                        message = err.response.data;
                    }
                    this.$store.commit('setSnackbar', {
                        message: message,
                        timeout: 1500
                    })
                });
            },
            fetchZones() {
                axios.get('/api/user/cities/'+this.$store.state.currentCity.id+'/zones').then( res => {
                    this.zones = res.data;
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
            addAlias() {
                this.aliases.push({id:null,name:''});
            },
            removeAlias(index) {
                console.log(index);
                this.aliases.splice(index, 1);
            },
            submit() {
                const args = {
                    name: this.name,
                    niantic_name: this.niantic_name,
                    description: this.description,
                    zone_id: this.zone_id,
                    ex: this.ex,
                    gym: this.gym,
                    lat: this.coordinates.lat,
                    lng: this.coordinates.lng,
                    aliases: this.aliases,
                };
                if( this.getId ) {
                    this.save(args);
                    this.$emit('poi-create')
                } else {
                    this.create(args);
                    this.$emit('poi-create')
                }
            },
            save( args ) {
                this.$store.commit('setSnackbar', {message: 'Enregistrement en cours'})
                this.loading = true;
                axios.put('/api/user/cities/'+this.$store.state.currentCity.id+'/gyms/'+this.getId, args).then( res => {
                    this.$store.dispatch('fetchGyms');
                    this.$store.commit('setSnackbar', {
                        message: 'Enregistrement effectué',
                        timeout: 1500
                    })
                    this.loading = false
                }).catch( err => {
                    let message = 'Problème lors de la récupération';
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
                axios.post('/api/user/cities/'+this.$store.state.currentCity.id+'/gyms', args).then( res => {
                    this.$store.dispatch('fetchGyms');
                    this.$store.commit('setSnackbar', {
                        message: 'Enregistrement effectué',
                        timeout: 1500
                    })
                    this.loading = false
                    this.$router.push({ name: this.$route.meta.parent })
                }).catch( err => {
                    let message = 'Problème lors de la récupération';
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
                    axios.delete('/api/user/cities/'+this.$store.state.currentCity.id+'/gyms/'+this.getId).then( res => {
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
