<template>
    <div>
        <div class="settings-section">
            <v-subheader>Description</v-subheader>

            <div class="setting">
                <label>Nom</label>
                <input v-model="name" type="text">
            </div>

            <div class="setting">
                <v-datetime-picker
                    label="Début de l'évent"
                    v-model="start_time"
                    date-format="dd/MM/yyyy"
                    time-format="HH:mm"
                ></v-datetime-picker>
            </div>

            <div class="setting">
                <label>Type d'évent</label>
                <select v-model="type">
                    <option v-for="typesingle in types" :value="typesingle.id" :key="typesingle.id">{{typesingle.name}}</option>
                </select>
                <p class="commentaire">{{types[type].description}}</p>
            </div>

            <v-subheader v-if="type == 'train'">Pokétrain</v-subheader>
            <div v-if="type == 'train'" class="setting">
                <label>Étapes du Pokétrain</label>
                <div class="step" v-for="(step, index) in steps" :key="index">
                    <div class="step__num">{{index+1}}</div>
                    <div class="step__content">
                        <div class="setting">
                            <label>Heure</label>
                            <v-layout>
                                <v-flex xs6>
                                    <select dir="rtl" class="hour" v-if="exAllowedHours" v-model="step.hour">
                                        <option v-for="hour in exAllowedHours" :value="hour" :key="hour">{{hour}}h</option>
                                    </select>
                                </v-flex>
                                <v-flex xs6>
                                    <select class="minutes" v-if="exAllowedMinutes" v-model="step.minutes">
                                        <option v-for="minutes in exAllowedMinutes" :value="minutes" :key="minutes">{{minutes}}</option>
                                    </select>
                                </v-flex>
                            </v-layout>
                        </div>
                        <div class="setting">
                            <label>Type d'étape</label>
                            <select v-model="step.type">
                                <option v-for="stepType in stepTypes" :value="stepType.id" :key="stepType.id">{{stepType.name}}</option>
                            </select>
                        </div>
                        <div class="setting" v-if="step.type == 'stop' && gyms">
                            <label>Arène liée</label>
                            <select v-model="step.stop_id">
                                <option v-for="gym in gyms" :value="gym.id" :key="gym.id">{{gym.name}}</option>
                            </select>
                        </div>
                        <div class="setting">
                            <label>Description</label>
                            <input v-model="step.description" type="text">
                        </div>
                        <v-btn small flat fab @click="removeStep(index)"><v-icon>delete</v-icon></v-btn>                        
                    </div>
                </div>
                <div class="alias__add">
                    <v-btn small fab @click="addStep"><v-icon>add</v-icon></v-btn>
                </div>
            </div>

            <v-divider></v-divider>
            <div v-if="getId">
                <v-subheader v-if="">Autres actions</v-subheader>
                <v-list-tile color="pink" @click="dialog = true">Supprimer l'évent</v-list-tile>
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
    import moment from 'moment';
    export default {
        name: 'AdminEvent',
        data() {
            return {
                loading: false,
                dialog: false,
                name: '',
                start_time: null,
                type: 'train',
                steps: [],
                exAllowedHours: [5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22],
                exAllowedMinutes: [0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55],
                types: {
                    'train': {
                        id: 'train',
                        name: 'Pokétrain',
                        description: 'Un pokétrain permet d\'orgniser un parcours d\'arènes, par exemple lors des journées à 5 pass gratuits.'
                    }
                },
                stepTypes: {
                    'stop': {
                        id: 'stop',
                        name: 'Arène'
                    },
                    'transport': {
                        id: 'transport',
                        name: 'Trajet en voiture/bus'
                    }
                }
            }
        },
        computed: {
            getId() {
                return( this.$route.params.event_id && Number.isInteger(parseInt(this.$route.params.event_id)) ) ? parseInt(this.$route.params.event_id) : false ;
            },
            gyms() {
                return this.$store.state.gyms;
            }
        },
        created() {
            if( this.getId ) {
                this.fetch();
            }
        },
        methods: {
            fetch() {
                axios.get('/api/user/cities/'+this.$store.state.currentCity.id+'/events/'+this.getId).then( res => {
                    this.name = res.data.name;
                    this.start_time = new Date(res.data.start_time);
                    this.type = res.data.type;
                    this.steps = res.data.relation.steps;
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
            addStep() {
                this.steps.push({id:null,name:'', type:'stop'});
            },
            removeStep(index) {
                this.steps.splice(index, 1);
            },
            submit() {
                let start_time = moment(this.start_time.toString())
                const args = {
                    name: this.name,
                    type: this.type,
                    start_time: start_time.format('YYYY-MM-DD HH:mm:SS'),
                    steps: this.steps,
                };
                if( this.getId ) {
                    this.save(args);
                } else {
                    this.create(args);
                }
            },
            save( args ) {
                this.$store.commit('setSnackbar', {message: 'Enregistrement en cours'})
                this.loading = true;
                axios.put('/api/user/guilds/'+this.$route.params.id+'/events/'+this.getId, args).then( res => {
                    this.fetch();
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
                axios.post('/api/user/guilds/'+this.$route.params.id+'/events', args).then( res => {
                    this.fetch();
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
                    axios.delete('/api/user/guilds/'+this.$route.params.id+'/events/'+this.getId).then( res => {
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
