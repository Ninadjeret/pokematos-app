<template>
  <div>
    <div class="parent_view" v-if="$route.name == 'admin.quests.home'">
      <div class="settings-section">
        <v-subheader>Général</v-subheader>
        <v-list>
          <template v-for="(item, index) in items">
            <v-list-tile :key="item.route" :to="{ name: item.route }">
              <v-list-tile-action>
                <v-icon>{{ item.icon }}</v-icon>
              </v-list-tile-action>
              <v-list-tile-content>
                <v-list-tile-title>{{ item.label }}</v-list-tile-title>
              </v-list-tile-content>
              <v-list-tile-action>
                <v-btn icon ripple>
                  <v-icon color="grey lighten-1">arrow_forward_ios</v-icon>
                </v-btn>
              </v-list-tile-action>
            </v-list-tile>
            <v-divider></v-divider>
          </template>
        </v-list>
      </div>

      <div class="settings-section">
        <v-subheader>Réglages</v-subheader>
        <div class="setting d-flex switch">
          <div>
            <label>Analyser les captures d'écran publiées</label>
          </div>
          <v-switch v-model="questreporting_images_active"></v-switch>
        </div>
        <div v-if="questreporting_images_active" class="setting d-flex switch">
          <div>
            <label>Supprimer les captures après analyse ?</label>
          </div>
          <v-switch v-model="questreporting_images_delete"></v-switch>
        </div>
        <div class="setting">
          <label>Salons à analyser</label>
          <p class="description">
            Spécifiez les salons dans lesquels Pokématos doit analyser les
            messages d'annonce de quête.
          </p>
          <select
            multiple="true"
            v-if="channels"
            v-model="questreporting_allowed_channels"
          >
            <option
              :key="channel.id"
              v-for="channel in channels"
              :value="channel.id"
            >
              {{ channel.name }}
            </option>
          </select>
        </div>
      </div>
    </div>

    <v-btn dark fixed bottom right fab @click="submit()">
      <v-progress-circular
        v-if="loading"
        indeterminate
        color="primary"
      ></v-progress-circular>
      <v-icon v-else>save</v-icon>
    </v-btn>

    <transition name="fade">
      <router-view></router-view>
    </transition>
  </div>
</template>

<script>
import { mapState } from "vuex";

export default {
  name: "AdminQuestReportingHome",
  data() {
    return {
      loading: false,
      items: [
        {
          label: "Gérer les annonces",
          route: "admin.quests.annonces",
          icon: "settings_input_component",
        },
      ],
      roles_gym_color: "",
      questreporting_images_active: false,
      questreporting_images_delete: false,
      questreporting_allowed_channels: [],
      channels: [],
    };
  },
  computed: mapState(["currentCity"]),
  created() {
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
        .then((res) => {
          this.questreporting_images_active = parseInt(
            res.data.questreporting_images_active
          );
          this.questreporting_images_delete = parseInt(
            res.data.questreporting_images_delete
          );
          this.questreporting_allowed_channels =
            res.data.questreporting_allowed_channels;
        })
        .catch((err) => {
          //No error
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
        .then((res) => {
          this.channels = res.data;
        });
    },
    submit() {
      const args = {
        settings: {
          questreporting_images_active: this.questreporting_images_active,
          questreporting_images_delete: this.questreporting_images_delete,
          questreporting_allowed_channels: this.questreporting_allowed_channels,
        },
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
        .then((res) => {
          this.$store.commit("setSnackbar", {
            message: "Enregistrement effectué",
            timeout: 1500,
          });
          this.loading = false;
        })
        .catch((err) => {
          this.$store.commit("setSnackbar", {
            message: "Problème lors de l'enregistrement",
            timeout: 1500,
          });
          this.loading = false;
        });
    },
  },
};
</script>
