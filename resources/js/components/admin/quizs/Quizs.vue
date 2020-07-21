<template>
  <div>
    <div class="settings-section">
      <div class="search__wrapper">
        <v-text-field single-line hide-details outline v-model="search" label="Recherche"></v-text-field>
      </div>
      <v-list class="quests hidden-md-and-up">
        <template v-for="(item) in filteredItems">
          <v-list-tile
            :key="item.id"
            :to="{ name: 'admin.quiz.questions.edit', params: { id: item.id } }"
          >
            <v-list-tile-content>
              <v-list-tile-title>
                {{item.question}}
                <span v-if="item.theme" class>// {{item.theme.name}}</span>
              </v-list-tile-title>
            </v-list-tile-content>
            <v-divider></v-divider>
          </v-list-tile>
        </template>
      </v-list>

      <v-data-table :headers="headers" :items="filteredItems" class="hidden-md-and-down">
        <template v-slot:items="props">
          <td>{{ props.item.question }}</td>
          <td class="text-xs-right">{{ props.item.answer }}</td>
          <td class="text-xs-right">{{ props.item.difficulty }}</td>
          <td class="text-xs-right">{{ props.item.answer }}</td>
        </template>
      </v-data-table>

      <v-btn dark fixed bottom right fab :to="{ name: 'admin.quiz.questions.add' }">
        <v-icon>add</v-icon>
      </v-btn>
    </div>
  </div>
</template>

<script>
export default {
  name: "AdminQuizs",
  data() {
    return {
      search: null,
      headers: [
        { text: "Question", value: "question" },
        { text: "Réponse", value: "reponse" },
        { text: "Difficulté", value: "difficulte" },
        { text: "Réponse", value: "reponse2" }
      ],
      items: []
    };
  },
  computed: {
    filteredItems() {
      return this.items.filter(item => {
        let matchingTitle = 1;
        if (this.search != null) {
          matchingTitle =
            item.question.toLowerCase().indexOf(this.search.toLowerCase()) > -1;
        }
        return matchingTitle;
      });
    }
  },
  created() {
    this.fetchQuizQuestions();
  },
  methods: {
    fetchQuizQuestions() {
      axios
        .get("/api/quiz/questions")
        .then(res => {
          this.items = res.data;
        })
        .catch(err => {
          //No error
        });
    }
  }
};
</script>
