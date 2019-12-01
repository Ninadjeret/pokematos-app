<template>
    <div>
        <div class="settings-section">
            <v-list>
            <template v-for="(log, index) in logs">
              <div class="log" :key="log.id">

                  <div class="log__content" v-if="log.type == 'raid'">
                      <p>{{log.created_at}}</p>
                  </div>

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
                axios.get('/api/user/guilds/'+this.$route.params.id+'/logs').then( res => {
                    console.log('tutu')
                    this.logs = res.data;
                });
            },
        }
    }
</script>
