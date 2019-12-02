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
                  <h2>Analyse d'image</h2>
                  <p>{{log.created_at}}, <i>posté par {{log.user.name}} dans le salon {{log.channel_discord_id}}</i></p>
              </div>

              <v-divider></v-divider>
            </template>
          </v-list>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'AdminLogs',
        data() {
            return {
                logs: [],
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
        }
    }
</script>
