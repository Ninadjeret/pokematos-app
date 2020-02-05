<template>
    <div>
        <div class="settings-section">
            <div class="search__wrapper">
                <v-text-field single-line hide-details outline v-model="search" label="Recherche"></v-text-field>
            </div>
            <v-list class="quests">
            <template v-for="(quest, index) in filteredQuests">
              <v-list-tile :key="quest.id" :to="{ name: 'admin.quests.edit', params: { id: quest.id } }">
                <v-list-tile-content>
                  <v-list-tile-title>
                      {{quest.name}}
                  </v-list-tile-title>
                </v-list-tile-content>
                <v-avatar v-if="quest.rewards.length >= 1">
                    <img :src="quest.rewards[0].thumbnail_url">
                    <span v-if="quest.rewards.length > 1" class="rewards_badge">
                        +{{quest.rewards.length - 1}}
                    </span>
                </v-avatar>
              </v-list-tile>
              <v-divider></v-divider>
            </template>
          </v-list>
            <v-btn dark fixed bottom right fab :to="{ name: 'admin.quests.add' }"><v-icon>add</v-icon></v-btn>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'AdminQuests',
        data() {
            return {
                search: null,
                quests: [],
            }
        },
        computed: {
            filteredQuests() {
                return this.quests.filter((quest) => {
                    let matchingTitle = 1;
                    if (this.search != null) {
                        matchingTitle = quest.name.toLowerCase().indexOf(this.search.toLowerCase()) > -1;
                    }
                    return (matchingTitle);
                });
            },
        },
        created() {
            this.fetchQuests();
        },
        methods: {
            fetchQuests() {
                axios.get('/api/quests').then( res => {
                    this.quests = res.data;
                }).catch( err => {
                    //No error
                });
            },
        }
    }
</script>
