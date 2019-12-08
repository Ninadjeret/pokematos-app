<template>
    <div>
        <div class="settings-section">
            <v-list>
            <template v-for="(log, index) in logs">

              <div class="log" :key="log.id" v-if="log.type == 'raid-create'">
                  <h2>Annonce de raid</h2>
                  <p>{{log.created_at}}, <i>par {{log.user.name}}</i></p>
              </div>

              <div class="log" :key="log.id" v-if="log.type == 'analysis-img'">
                  <div v-if="hasImage(log)" class="log__img">
                      <v-btn @click="showModal(log.source)"><img :src="log.source"></v-btn>
                  </div>
                  <div class="log__content">
                      <h2>
                          Analyse d'image
                      </h2>
                      <p class="meta">
                          {{getLogDate(log)}}<i v-if="log.user">, posté par {{log.user.name}} {{log.channel_discord_id}}</i>
                      </p>
                      <p v-if="!log.success" class="error">
                          {{log.error}}
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
                                              {{log.result.gym.name}} (Probabilité 100%)
                                          </template>
                                      </span>
                                  </li>
                                  <li v-if="log.result.pokemon">
                                      <span class="label">Boss identifié</span>
                                      <span class="value">{{log.result.pokemon.name}} (Probabilité 100%)</span>
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
              </div>

              <v-divider></v-divider>
            </template>
          </v-list>
        </div>

        <v-dialog v-model="logImgDialog" max-width="90%">
        <v-card>
            <img v-if="logImg" :src="logImg">
            <v-btn absolute fab top right small @click="logImgDialog = false"><v-icon>close</v-icon></v-btn>
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
            }
        },
        created() {
            this.fetchLogs();
        },
        methods: {
            fetchLogs() {
                axios.get('/api/user/cities/'+this.$store.state.currentCity.id+'/logs').then( res => {
                    console.log(res.data)
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
                    return log.result.type;
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
