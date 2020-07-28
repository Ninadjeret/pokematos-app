<template>
  <div>
    <div class="settings-section">
      <v-subheader>Raids</v-subheader>
      <div class="setting">
        <label>Durée avant l'éclosion (en minutes)</label>
        <input type="text" v-model="timing_before_eclosion" />
      </div>
      <div class="setting">
        <label>Durée après l'éclosion (en minutes)</label>
        <input type="text" v-model="timing_after_eclosion" />
      </div>
      <v-btn dark fixed bottom right fab @click="submit()">
        <v-progress-circular v-if="loading" indeterminate color="primary"></v-progress-circular>
        <v-icon v-else>save</v-icon>
      </v-btn>
    </div>
  </div>
</template>

<script>
export default {
  name: "adminSettings",
  data() {
    return {
      loading: false,
      timing_before_eclosion: 60,
      timing_after_eclosion: 45,
    };
  },
  created() {
    this.fetch();
  },
  methods: {
    fetch() {
      axios
        .get("/api/settings")
        .then((res) => {
          this.timing_before_eclosion = res.data.timing_before_eclosion;
          this.timing_after_eclosion = res.data.timing_after_eclosion;
        })
        .catch((err) => {
          let message = "Problème lors de la récupération";
          if (err.response.data) {
            message = err.response.data;
          }
          this.$store.commit("setSnackbar", {
            message: message,
            timeout: 1500,
          });
        });
    },
    submit() {
      const args = {
        settings: {
          timing_before_eclosion: this.timing_before_eclosion,
          timing_after_eclosion: this.timing_after_eclosion,
        },
      };
      this.save(args);
    },
    save(args) {
      console.log(args);
      this.$store.commit("setSnackbar", { message: "Enregistrement en cours" });
      this.loading = true;
      axios
        .put("/api/settings", args)
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
  },
};
</script>
