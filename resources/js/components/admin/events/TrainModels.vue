<template>
    <div>

        <div v-if="loading" class="loading">
            <div class="loading__content">
                <i class="friendball"></i>
                <p>Chargement...</p>
            </div>
        </div>

        <div v-if="!loading" class="settings-section">
            <v-list>
                <v-subheader>Évents en cours/à venir</v-subheader>
                <template v-for="(train, index) in trains">
                    <v-list-tile :key="event.id" :to="{ name: 'admin.events.trainmodel.edit', params: { trainmodel_id: train.id } }">
                        <v-list-tile-avatar>
                            <img src="https://assets.profchen.fr/img/app/event_train.png">
                        </v-list-tile-avatar>
                        <v-list-tile-content>
                            <v-list-tile-title>{{event.name}}</v-list-tile-title>
                        </v-list-tile-content>
                    </v-list-tile>
                    <v-divider></v-divider>
                </template>
          </v-list>
          <v-btn dark fixed bottom right fab :to="{ name: 'admin.events.trainmodel.add' }"><v-icon>add</v-icon></v-btn>
      </div>
    </div>
</template>

<script>
    import { mapState } from 'vuex'
    import moment from 'moment';
    export default {
        name: 'AdminTrainModels',
        data() {
            return {
                trains: [],
                loading: true
            }
        },
        created() {
            this.fetchEvents();
        },
        methods: {
            fetchEvents() {
                axios.get('/api/user/guilds/'+this.$route.params.id+'/events/trainmodels').then( res => {
                    this.loading = false;
                    this.trains = res.data;
                });
            },
        }
    }
</script>
