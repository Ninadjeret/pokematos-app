<template>
  <div>
    <div class="settings-section">
      <v-subheader>Message de bienvenue</v-subheader>
      <div class="setting d-flex switch">
        <div>
          <label>Activer le message de bienvenue ?</label>
          <p
            class="description"
          >Celui-ci apparaitra dans le salon choisi lors de l'arrivée d'un nouvel utilisateur</p>
        </div>
        <v-switch v-model="welcome_active"></v-switch>
      </div>
      <div v-if="welcome_active" class="setting">
        <label>Message de bienvenue</label>
        <p class="description">{utilisateur} permet d'indiquer le nom du nouvel arrivant.</p>
        <textarea v-model="welcome_message"></textarea>
      </div>
      <div v-if="welcome_active" class="setting">
        <label>Salon</label>
        <select v-if="channels" v-model="welcome_channel_discord_id">
          <option :key="channel.id" v-for="channel in channels" :value="channel.id">{{channel.name}}</option>
        </select>
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
  name: "adminWelcome",
  data() {
    return {
      loading: false,
      welcome_active: false,
      welcome_message: "",
      welcome_channel_discord_id: false,
      channels: [],
      coordinates: {}
    };
  },
  created() {
    this.fetchChannels();
    this.fetch();
  },
  methods: {
    fetch() {
      axios
        .get(
          "/api/user/cities/" +
            this.$store.state.currentCity.id +
            "/guilds/" +
            this.$route.params.id +
            "/settings"
        )
        .then(res => {
          this.welcome_active = parseInt(res.data.welcome_active);
          this.welcome_message = res.data.welcome_message;
          this.welcome_channel_discord_id = res.data.welcome_channel_discord_id;
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
    fetchChannels() {
      axios
        .get(
          "/api/user/cities/" +
            this.$store.state.currentCity.id +
            "/guilds/" +
            this.$route.params.id +
            "/channels"
        )
        .then(res => {
          this.channels = res.data;
        });
    },
    submit() {
      const args = {
        settings: {
          welcome_active: this.welcome_active,
          welcome_message: this.welcome_message,
          welcome_channel_discord_id: this.welcome_channel_discord_id
        }
      };
      this.save(args);
    },
    save(args) {
      this.$store.commit("setSnackbar", { message: "Enregistrement en cours" });
      this.loading = true;
      axios
        .put(
          "/api/user/cities/" +
            this.$store.state.currentCity.id +
            "/guilds/" +
            this.$route.params.id +
            "/settings",
          args
        )
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
    }
  }
};
</script>
