<template>
  <div>
    <div v-if="!isLoaded" class="loading">
      <div class="loading__content">
        <i class="friendball"></i>
        <p>Chargement...</p>
      </div>
    </div>

    <div v-if="isLoaded"></div>
    <div class="settings-section">
      <v-subheader>Général</v-subheader>
      <div class="setting">
        <label>Question</label>
        <input v-model="question" type="text" />
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
      themes: [],
      fetchLoaded: false,
      fetchedThemes: false
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
    }
  },
  methods: {
    fetch() {
      axios
        .get("/api/quiz/questions/" + this.getId)
        .then(res => {
          this.fetchLoaded = true;
          this.question = res.data.question;
        })
        .catch(err => {
          let message = "Problème lors de la récupération";
          if (err.response.data) {
            message = err.response.data;
          }
          this.$store.commit("setSnackbar", {
            message: message,
            timeout: 1500
          });
        });
    },
    fetchThemes() {
      axios.get("/api/quiz/themes").then(res => {
        this.fetchedThemes = true;
        this.themes = res.data;
      });
    },
    submit() {
      const args = {
        name: this.name,
        reward_ids: reward_ids,
        pokemon_ids: pokemon_ids
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
        .put("/api/quests/" + this.$route.params.id, args)
        .then(res => {
          this.$store.commit("setSnackbar", {
            message: "Enregistrement effectué",
            timeout: 1500
          });
          this.loading = false;
        })
        .catch(err => {
          let message = "Problème lors de l'enregistrement";
          if (err.response.data) {
            message = err.response.data;
          }
          this.$store.commit("setSnackbar", {
            message: message,
            timeout: 1500
          });
          this.loading = false;
        });
    },
    create(args) {
      this.$store.commit("setSnackbar", { message: "Enregistrement en cours" });
      this.loading = true;
      axios
        .post("/api/quests", args)
        .then(res => {
          this.$store.commit("setSnackbar", {
            message: "Enregistrement effectué",
            timeout: 1500
          });
          this.loading = false;
          this.$router.push({ name: this.$route.meta.parent });
        })
        .catch(err => {
          let message = "Problème lors de l'enregistrement";
          if (err.response.data) {
            message = err.response.data;
          }
          this.$store.commit("setSnackbar", {
            message: message,
            timeout: 1500
          });
          this.loading = false;
        });
    },
    destroy() {
      this.dialog = false;
      this.$store.commit("setSnackbar", { message: "Suppression en cours" });
      axios
        .delete("/api/quiz/questions/" + this.getId)
        .then(res => {
          this.$store.commit("setSnackbar", {
            message: "suppression effectuée",
            timeout: 1500
          });
          this.$router.push({ name: this.$route.meta.parent });
        })
        .catch(err => {
          let message = "Problème lors de la suppression";
          if (err.response.data) {
            message = err.response.data;
          }
          this.$store.commit("setSnackbar", {
            message: message,
            timeout: 1500
          });
        });
    }
  }
};
</script>
