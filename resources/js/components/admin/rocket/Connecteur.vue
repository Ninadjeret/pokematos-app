<template>
  <div>
    <v-tabs v-model="tabs" color="transparent" slider-color="#8e56d9" class="">
      <v-tab href="#general" class="primary--text">Général</v-tab>
      <v-tab href="#annonces" class="primary--text">Annonces</v-tab>
    </v-tabs>

    <v-tabs-items v-model="tabs">
      <v-tab-item value="general">
        <div class="settings-section">
          <v-subheader>Général</v-subheader>
          <div class="setting">
            <label>Nom</label>
            <input v-model="name" type="text" />
          </div>
          <div class="setting">
            <label>Channel</label>
            <select v-if="channels" v-model="channel_discord_id">
              <option
                v-for="channel in channels"
                v-bind:key="channel.id"
                :value="channel.id"
              >
                {{ channel.name }}
              </option>
            </select>
          </div>
        </div>
        <div class="settings-section">
          <div v-if="this.$route.params.quest_connector_id">
            <v-subheader v-if="">Autres actions</v-subheader>
            <v-list-tile color="pink" @click="dialog = true"
              >Supprimer le connecteur</v-list-tile
            >
          </div>
        </div>
      </v-tab-item>
      <v-tab-item value="annonces">
        <div class="settings-section">
          <v-subheader>Pokéstops</v-subheader>
          <div class="setting">
            <label>Filtrer les pokéstops</label>
            <v-btn-toggle v-model="filter_stop_type" mandatory>
              <v-btn value="none">Aucun filtre</v-btn>
              <v-btn value="zone">Par zone(s)</v-btn>
              <v-btn value="stop">Par pokéstop(s)</v-btn>
            </v-btn-toggle>
          </div>
          <div v-if="filter_stop_type == 'zone'" class="setting">
            <label>Zone(s)</label>
            <multiselect
              v-model="filter_stop_zone"
              :options="zones"
              track-by="id"
              label="name"
              :multiple="true"
              placeholder="Ajouter une zone"
            >
            </multiselect>
          </div>
          <div v-if="filter_stop_type == 'stop'" class="setting">
            <label>Arêne(s)</label>
            <multiselect
              v-model="filter_stop_stop"
              :options="gyms"
              track-by="id"
              label="name"
              :multiple="true"
              placeholder="Ajouter un Pokéstop"
            >
            </multiselect>
          </div>
        </div>
        <div class="settings-section">          
          <v-subheader>Boss</v-subheader>
          <div class="setting">
            <label>Filtrer les récompenses</label>
            <v-btn-toggle v-model="filter_boss_type" mandatory>
              <v-btn value="none">Aucun filtre</v-btn>
              <v-btn value="boss">Par boss</v-btn>
            </v-btn-toggle>
          </div>
          <div v-if="filter_boss_type == 'boss'" class="setting">
            <label>Objet(s)</label>
            <multiselect
              v-model="filter_boss_bosses"
              :options="bosses"
              track-by="id"
              label="name"
              :multiple="true"
              placeholder="Ajouter un boss"
            >
            </multiselect>
          </div>
        </div>
        <div class="settings-section">          
          <v-subheader>Format de l'annonce</v-subheader>
          <div class="setting">
            <label>Format</label>
            <v-btn-toggle v-model="format" mandatory>
              <v-btn value="auto">Automatique</v-btn>
              <v-btn value="custom">Personnalisé</v-btn>
              <v-btn value="both">Les deux</v-btn>
            </v-btn-toggle>
          </div>
          <div class="setting" v-if="format != 'auto'">
            <label>Message personnalisé</label>
            <p class="description">
              Utilisez les tags suivants pour afficher des propriétés du raid
              :<br />
              {rocketboss_name}<br />
              {rocketboss_pokemon_1}<br />
              {rocketboss_pokemon_2}<br />
              {rocketboss_pokemon_3}<br />
              {pokestop_nom}<br />
              {pokestop_nom_custom}<br />
              {pokestop_description}<br />
              {pokestop_zone}<br />
              {pokestop_gmaps}<br />
              {utilisateur}<br />
              {role_poi_lie}<br />
              {role_zone_liee}<br />
            </p>
            <input v-model="custom_message" type="text" />
          </div>
          <div class="setting d-flex switch">
            <div>
              <label>Supprimer les messages à la fin de la journée</label>
              <p class="description">
                Tous les messages d'annonces de quête liés à ce connecteurs
                seront supprimés dans la nuit
              </p>
            </div>
            <v-switch v-model="delete_after_end"></v-switch>
          </div>
          <div v-if="$route.params.invasion_connector_id">
            <v-subheader v-if="">Autres actions</v-subheader>
            <v-list-tile color="pink" @click="dialog = true"
              >Supprimer le connecteur</v-list-tile
            >
          </div>
        </div>
      </v-tab-item>
    </v-tabs-items>

    <v-btn dark fixed bottom right fab @click="submit()">
      <v-progress-circular
        v-if="loading"
        indeterminate
        color="primary"
      ></v-progress-circular>
      <v-icon v-else>save</v-icon>
    </v-btn>

    <v-dialog v-model="dialog" persistent max-width="290">
      <v-card>
        <v-card-title class="headline">Supprimer {{ name }} ?</v-card-title>
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
import Multiselect from "vue-multiselect";

export default {
  name: "AdminQuestsConnector",
  components: { Multiselect },
  data() {
    return {
      loading: false,
      dialog: false,
      tabs: null,
      zones: [],
      bosses: [],
      channels: [],
      name: "",
      channel_discord_id: false,
      filter_boss_type: "none",
      filter_boss_bosses: [],
      filter_stop_type: "none",
      filter_stop_zone: [],
      filter_stop_stop: [],
      format: "auto",
      custom_message: "",
      delete_after_end: true,
    };
  },
  created() {
    this.fetchChannels();
    this.fetchPokemons();
    this.fetchZones();
    this.fetchBosses();
    if (this.$route.params.invasion_connector_id) {
      this.fetch();
    }
  },
  computed: {
    channelName() {
      return "Toto";
    },
    gyms() {
      return this.$store.state.gyms;
    },
  },
  methods: {
    fetch() {
      axios
        .get(
          "/api/user/guilds/" +
            this.$route.params.id +
            "/invasionconnectors/" +
            this.$route.params.invasion_connector_id
        )
        .then((res) => {
          console.log(res.data);
          this.name = res.data.name;
          this.channel_discord_id = res.data.channel_discord_id;
          this.filter_stop_type = res.data.filter_stop_type;
          this.filter_stop_zone = res.data.filtered_zones;
          this.filter_stop_stop = res.data.filtered_stops;
          this.filter_boss_type = res.data.filter_boss_type;
          this.filter_boss_bosses = res.data.filtered_bosses;
          this.format = res.data.format;
          this.custom_message = res.data.custom_message;
          this.delete_after_end = res.data.delete_after_end;
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
    fetchPokemons() {
      axios.get("/api/suer/pokemon").then((res) => {
        this.pokemons = res.data;
      });
    },
    fetchZones() {
      axios
        .get("/api/user/cities/" + this.$store.state.currentCity.id + "/zones")
        .then((res) => {
          this.zones = res.data;
        });
    },
    fetchBosses() {
      axios.get("/api/rocket/bosses").then((res) => {
        this.bosses = res.data;
      });
    },
    submit() {
      const args = {
        name: this.name,
        channel_discord_id: this.channel_discord_id,
        filter_stop_type: this.filter_stop_type,
        filter_stop_zone: this.filter_stop_zone,
        filter_stop_stop: this.filter_stop_stop,
        filter_boss_type: this.filter_boss_type,
        filter_boss_bosses: this.filter_boss_bosses,
        format: this.format,
        custom_message: this.custom_message,
        delete_after_end: this.delete_after_end,
      };
      if (this.$route.params.invasion_connector_id) {
        this.save(args);
      } else {
        this.create(args);
      }
    },
    save(args) {
      this.$store.commit("setSnackbar", { message: "Enregistrement en cours" });
      this.loading = true;
      axios
        .put(
          "/api/user/guilds/" +
            this.$route.params.id +
            "/invasionconnectors/" +
            this.$route.params.invasion_connector_id,
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
        .post(
          "/api/user/guilds/" + this.$route.params.id + "/invasionconnectors",
          args
        )
        .then((res) => {
          this.$store.commit("setSnackbar", {
            message: "Enregistrement effectué",
            timeout: 1500,
          });
          this.loading = false;
          this.$router.push({ name: this.$route.meta.parent });
        })
        .catch((err) => {
          let message = "Problème lors de la création";
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
        .delete(
          "/api/user/guilds/" +
            this.$route.params.id +
            "/invasionconnectors/" +
            this.$route.params.invasion_connector_id
        )
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
