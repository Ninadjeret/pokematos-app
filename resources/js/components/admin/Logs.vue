<template>
    <div>
        <div class="settings-section">

            <div class="search__wrapper setting text-xs-center">
                <v-btn-toggle v-model="filter_logs" mandatory>
                    <v-btn value="all">Tous les logs</v-btn>
                    <v-btn value="error">Erreurs</v-btn>
                    <v-btn value="success">Succes</v-btn>
                </v-btn-toggle>
            </div>

            <div v-if="loading" class="loading">
                <div class="loading__content">
                    <i class="friendball"></i>
                    <p>Chargement...</p>
                </div>
            </div>

            <v-list v-if="!loading">
            <template v-for="(log, index) in filteredLogs">

              <div class="log" :key="log.id" v-if="log.type == 'analysis-img'">
                  <div v-if="hasImage(log)" class="log__img">
                      <v-btn @click="showModal(log.source)"><img :src="log.source"></v-btn>
                  </div>
                  <div class="log__content">
                      <h2>
                          Analyse d'image
                      </h2>
                      <p class="meta">
                          {{getLogDate(log)}}<i v-if="log.user">, posté par {{log.user.name}}</i>
                      </p>

                      <p v-if="!log.success" class="error">
                          {{log.error}}
                      </p>
                      <p v-if="log.success" class="success">
                          Capture correctement analysée
                      </p>

                      <v-expansion-panel>
                        <v-expansion-panel-content>
                          <template v-slot:header>
                            <div>Résultat détaillé</div>
                          </template>
                          <v-card>
                              <ul class="result">
                                  <li>
                                      <span class="label">Type d'image</span>
                                      <span class="value">{{getLogImgType(log)}}</span>
                                  </li>
                                  <li>
                                      <span class="label">Texte décodé</span>
                                      <span class="value">{{log.result.ocr}}</span>
                                  </li>
                                  <li>
                                      <span class="label">Début du raid</span>
                                      <span class="value">{{getLogImgRaidStart(log)}}</span>
                                  </li>
                                  <li>
                                      <span class="label">Arène identifiée</span>
                                      <span class="value">
                                          <template v-if="log.result.gym">
                                              {{log.result.gym.name}} (Probabilité {{log.result.gym_probability}}%)
                                          </template>
                                      </span>
                                  </li>
                                  <li v-if="log.result.pokemon">
                                      <span class="label">Boss identifié</span>
                                      <span class="value">{{log.result.pokemon.name}} (Probabilité {{log.result.pokemon_probability}}%)</span>
                                  </li>
                                  <li v-if="!log.result.pokemon">
                                      <span class="label">Niveau de boss</span>
                                      <span class="value">{{log.result.egg_level}}</span>
                                  </li>
                              </ul>
                          </v-card>
                        </v-expansion-panel-content>
                      </v-expansion-panel>
                  </div>
                  <v-divider></v-divider>
              </div>


            </template>
          </v-list>
        </div>

        <v-dialog v-model="logImgDialog" max-width="90%">
        <v-card>
            <img v-if="logImg" :src="logImg">
            <div class="footer--actions">
                <button class="button--close" @click="logImgDialog = false">
                    <i class="material-icons">close</i>
                </button>
            </div>
        </v-card>
      </v-dialog>


    </div>
</template>

<script>
    import moment from 'moment';
    export default {
        name: 'AdminLogs',
        data() {
            return {
                logs: [],
                logImgDialog: false,
                logImg: false,
                loading: true,
                filter_logs: 'all',
            }
        },
        computed: {
            filteredLogs() {
                let that = this;
                return this.logs.filter((log) => {
                    return ( that.filter_logs == 'all'
                        || ( that.filter_logs == 'success' && log.success )
                        || ( that.filter_logs == 'error' && !log.success )
                    );
                });
            }
        },
        created() {
            this.fetchLogs();
        },
        methods: {
            fetchLogs() {
                axios.get('/api/user/cities/'+this.$store.state.currentCity.id+'/logs').then( res => {
                    this.loading = false;
                    this.logs = res.data;
                });
            },
            showModal(img) {
                this.logImg = img;
                this.logImgDialog = true;
            },
            getLogDate(log) {
                return moment(log.created_at).format('DD/MM/YYYY à HH[h]mm[m]ss[s]');
            },
            getLogImgType(log) {
                if(log.result.type == 'pokemon') {
                    return 'Capture de raid en cours'
                } else if(log.result.type == 'egg') {
                    return 'Capture de raid à venir';
                } else if(log.result.type == 'ex') {
                    return 'Capture d\'invitation de Raid EX';
                } else {
                    return 'Type d\'image non reconnu';
                }
            },
            getLogImgRaidStart(log) {
                if( log.result.date ) {
                    return moment(log.result.date).format('DD/MM à HH[h]mm[m]');
                }
                return '';
            },
            hasImage(log) {
                if( log.source.search("cdn.discordapp.com/attachments") > -1 ) {
                    return true;
                }
                return false;
            }
        }
    }
</script>
