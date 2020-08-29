<template>
  <div>
    <div v-if="!isLoaded" class="loading">
      <div class="loading__content">
        <i class="friendball"></i>
        <p>Chargement...</p>
      </div>
    </div>

    <div v-if="isLoaded">
      <div class="settings-section">
        <v-subheader>Général</v-subheader>
        <div class="setting">
          <label>Question</label>
          <input v-model="question" type="text" />
        </div>
        <div class="setting">
          <label>Réponse</label>
          <textarea v-model="answer"></textarea>
        </div>
        <div class="setting">
          <label>Réponses alternatives</label>
          <p
            class="description"
          >Séparées par des virgules. Les réponses alternatives sont les autres réponses considérées comme correcte par Pokématos. Cela peut correspondre à d'autres ortographe ou manière d'écrire.</p>
          <textarea v-model="alt_answers"></textarea>
        </div>
        <div class="setting">
          <label>Explication (facultatif)</label>
          <p
            class="description"
          >L'explication sera transmise par le bot si la bonne réponse est trouvée. L'explication peut expliquer la bonne réponse ou donner des informations de contexte</p>
          <textarea v-model="explanation"></textarea>
        </div>
        <div class="setting">
          <label>Thème</label>
          <select v-model="theme_id">
            <option :key="theme.id" v-for="theme in themes" :value="theme.id">{{theme.name}}</option>
          </select>
        </div>
        <div class="setting d-flex switch">
          <div>
            <label>A propos de Pokémon GO</label>
            <p
              class="description"
            >Est-ce que la question concerne directement Pokémon Go, ou est-elle en lien avec l'univers Pokémon ?</p>
          </div>
          <v-switch v-model="about_pogo"></v-switch>
        </div>
        <div class="setting">
          <label>Difficulté</label>
          <select v-model="difficulty">
            <option :key="lvl" v-for="lvl in [1,2,3,5]" :value="lvl">{{lvl}}</option>
          </select>
        </div>
        <v-divider></v-divider>
        <div v-if="getId">
          <v-subheader>Autres actions</v-subheader>
          <v-list-tile color="pink" @click="dialog = true">Supprimer la question</v-list-tile>
        </div>

        <v-btn dark fixed bottom right fab @click="submit()">
          <v-progress-circular v-if="loading" indeterminate color="primary"></v-progress-circular>
          <v-icon v-else>save</v-icon>
        </v-btn>
      </div>
    </div>
    <v-dialog v-model="dialog" persistent max-width="290">
      <v-card>
        <v-card-title class="headline">Supprimer {{question}} ?</v-card-title>
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
  name: "AdminQuizQuestion",
  data() {
    return {
      loading: false,
      dialog: false,
      question: "",
      answer: "",
      alt_answers: "",
      explanation: "",
      difficulty: 1,
      theme_id: 1,
      themes: [],
      about_pogo: 0,
      fetchLoaded: false,
      fetchedThemes: false,
    };
  },
  created() {
    this.fetchThemes();
    console.log(this.getId);
    if (this.getId) {
      this.fetch();
    }
  },
  computed: {
    isLoaded() {
      return (
        ((this.getId && this.fetchLoaded) || !this.getId) && this.fetchThemes
      );
    },
    getId() {
      return Number.isInteger(parseInt(this.$route.params.question_id))
        ? parseInt(this.$route.params.question_id)
        : false;
    },
  },
  methods: {
    fetch() {
      axios
        .get("/api/quiz/questions/" + this.getId)
        .then((res) => {
          console.log(res.data);
          this.fetchLoaded = true;
          this.about_pogo = res.data.about_pogo;
          this.question = res.data.question;
          this.answer = res.data.answer;
          this.alt_answers = res.data.alt_answers.join(", ");
          this.explanation = res.data.explanation;
          this.difficulty = res.data.difficulty;
          this.theme_id = resa.data.theme_id;
        })
        .catch((err) => {
          let message = "Problème lors de la récupération";
          message = err.response;
          if (err.response && err.response.data) {
            message = err.response.data;
          }
          this.$store.commit("setSnackbar", {
            message: message,
            timeout: 1500,
          });
        });
    },
    fetchThemes() {
      axios.get("/api/quiz/themes").then((res) => {
        this.fetchedThemes = true;
        this.themes = res.data;
      });
    },
    submit() {
      const args = {
        question: this.question,
        answer: this.answer,
        alt_answers: this.alt_answers.split(", "),
        explanation: this.explanation,
        difficulty: this.difficulty,
        theme_id: this.theme_id,
        about_pogo: this.about_pogo,
      };
      console.log(args);
      if (this.getId) {
        this.save(args);
      } else {
        this.create(args);
      }
    },
    save(args) {
      this.$store.commit("setSnackbar", { message: "Enregistrement en cours" });
      this.loading = true;
      axios
        .put("/api/quiz/questions/" + this.getId, args)
        .then((res) => {
          this.$store.commit("setSnackbar", {
            message: "Enregistrement effectué",
            timeout: 1500,
          });
          this.loading = false;
        })
        .catch((err) => {
          let message = "Problème lors de l'enregistrement";
          if (err.response.data) {
            message = err.response.data;
          }
          this.$store.commit("setSnackbar", {
            message: message,
            timeout: 1500,
          });
          this.loading = false;
        });
    },
    create(args) {
      this.$store.commit("setSnackbar", { message: "Enregistrement en cours" });
      this.loading = true;
      axios
        .post("/api/quiz/questions", args)
        .then((res) => {
          this.$store.commit("setSnackbar", {
            message: "Enregistrement effectué",
            timeout: 1500,
          });
          this.loading = false;
          this.$router.push({ name: this.$route.meta.parent });
        })
        .catch((err) => {
          let message = "Problème lors de l'enregistrement";
          if (err.response.data) {
            message = err.response.data;
          }
          this.$store.commit("setSnackbar", {
            message: message,
            timeout: 1500,
          });
          this.loading = false;
        });
    },
    destroy() {
      this.dialog = false;
      this.$store.commit("setSnackbar", { message: "Suppression en cours" });
      axios
        .delete("/api/quiz/questions/" + this.getId)
        .then((res) => {
          this.$store.commit("setSnackbar", {
            message: "suppression effectuée",
            timeout: 1500,
          });
          this.$router.push({ name: this.$route.meta.parent });
        })
        .catch((err) => {
          let message = "Problème lors de la suppression";
          if (err.response.data) {
            message = err.response.data;
          }
          this.$store.commit("setSnackbar", {
            message: message,
            timeout: 1500,
          });
        });
    },
  },
};
</script>
