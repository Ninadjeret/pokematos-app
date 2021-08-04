<template>
  <div>
    <v-tabs v-model="tabs" color="transparent" slider-color="#8e56d9" class>
      <v-tab href="#general" class="primary--text">Général</v-tab>
      <v-tab href="#annonces" class="primary--text">Paramètres</v-tab>
      <v-tab href="#canaux" class="primary--text">Affichage</v-tab>
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
                :value="channel.id"
                :key="channel.id"
              >
                {{ channel.name }}
              </option>
            </select>
          </div>
        </div>
        <div class="settings-section">
          <div v-if="this.$route.params.connector_id">
            <v-subheader>Autres actions</v-subheader>
            <v-list-tile color="pink" @click="dialog = true"
              >Supprimer le connecteur</v-list-tile
            >
          </div>
        </div>
      </v-tab-item>
      <v-tab-item value="annonces">
        <div class="settings-section">
          <v-subheader>Arênes</v-subheader>
          <div class="setting">
            <label>Filtrer les arènes</label>
            <v-btn-toggle v-model="filter_gym_type" mandatory>
              <v-btn value="none">Aucun filtre</v-btn>
              <v-btn value="zone">Par zone(s)</v-btn>
              <v-btn value="gym">Par arêne(s)</v-btn>
            </v-btn-toggle>
          </div>
          <div v-if="filter_gym_type == 'zone'" class="setting">
            <label>Zone(s)</label>
            <multiselect
              v-model="filter_gym_zone"
              :options="zones"
              track-by="id"
              label="name"
              :multiple="true"
              placeholder="Ajouter une zone"
            ></multiselect>
          </div>
          <div v-if="filter_gym_type == 'gym'" class="setting">
            <label>Arêne(s)</label>
            <multiselect
              v-model="filter_gym_gym"
              :options="gyms"
              track-by="id"
              label="name"
              :multiple="true"
              placeholder="Ajouter une arêne"
            >
              <template slot="option" slot-scope="props">
                <div class="option__desc">
                  <span class="option__title">{{ props.option.name }}</span>
                  <span v-if="props.option.ex" class="option__small">[EX]</span>
                </div>
              </template>
            </multiselect>
          </div>
          <div class="setting">
            <label>Statut Ex des arènes</label>
            <v-btn-toggle v-model="filter_ex_type" mandatory>
              <v-btn value="none">Peu importe</v-btn>
              <v-btn value="ex">EX</v-btn>
              <v-btn value="nonex">Non-ex</v-btn>
            </v-btn-toggle>
          </div>
        </div>
        <div class="settings-section">          
          <v-subheader>Boss</v-subheader>
          <div class="setting">
            <label>Filtrer les Pokémon</label>
            <v-btn-toggle v-model="filter_pokemon_type" mandatory>
              <v-btn value="none">Aucun filtre</v-btn>
              <v-btn value="level">Par niveau(x)</v-btn>
              <v-btn value="pokemon">Par Pokémon(s)</v-btn>
            </v-btn-toggle>
          </div>
          <div v-if="filter_pokemon_type == 'level'" class="setting">
            <label>Niveau(x) de boss</label>
            <multiselect
              v-model="filter_pokemon_level"
              :options="levels"
              track-by="id"
              label="name"
              :multiple="true"
              placeholder="Ajouter un niveau de boss"
            ></multiselect>
          </div>
          <div v-if="filter_pokemon_type == 'pokemon'" class="setting">
            <label>Pokémon</label>
            <multiselect
              v-model="filter_pokemon_pokemon"
              :options="pokemons"
              track-by="id"
              label="name_fr"
              :multiple="true"
              placeholder="Ajouter un Pokémon"
            ></multiselect>
          </div>
          <div class="setting checkbox">
            <label>Filtrer la source</label>
            <v-checkbox
              v-for="source in sources"
              v-model="filter_source_type"
              :key="source.value"
              :label="source.label"
              :value="source.value"
            ></v-checkbox>
          </div>
          <div class="setting d-flex switch">
            <div>
              <label>Supprimer les messages à la fin du raid</label>
              <p class="description">
                Tous les messages d'annonces seront supprimés à la fin des raids
                concernés
              </p>
            </div>
            <v-switch v-model="delete_after_end"></v-switch>
          </div>
        </div>
        <div class="settings-section">
          <v-subheader>Canaux temporaires</v-subheader>
          <div class="setting d-flex switch">
            <div>
              <label>Créer des salons temporaires</label>
              <p class="description">
                Une réaction permettra de créer un salon temporaire
              </p>
            </div>
            <v-switch v-model="add_channel"></v-switch>
          </div>

          <div v-if="add_channel" class="setting">
            <label>Catégorie de salon</label>
            <p class="description">
              Le salon temporaire sera créé dans la catégorie choisie.
            </p>
            <select
              v-if="channels_categories"
              v-model="channel_category_discord_id"
            >
              <option
                v-for="channel in channels_categories"
                :value="channel.id.toString()"
                :key="channel.id"
              >
                {{ channel.name }}
              </option>
            </select>
          </div>
          <div v-if="add_channel" class="setting">
            <label>Quand supprimer le salon ?</label>
            <p class="description">
              Définissez quand le salon du raid doit être supprimé. Par défaut,
              réservéz "En fin de journée" aux annonces de Raid EX
            </p>
            <select v-model="channel_duration">
              <option value="raidend">A la fin du raid</option>
              <option value="15min">15 min après la fin du raid</option>
              <option value="1h">1h après la fin du raid</option>
              <option value="2h">2h après la fin du raid</option>
              <option value="dayend">A la fin de journée</option>
            </select>
          </div>
        </div>
        <div class="settings-section">
          <v-subheader>Suivi des participants</v-subheader>
          <div class="setting d-flex switch">
            <div>
              <label>Activer le suivi des participants</label>
              <p class="description">
                Des réactions sous les annonces de raids permettront aux joueurs
                de définir leur présence, leur mode (sur place ou à distance) et
                le nombre de comptes
              </p>
            </div>
            <v-switch v-model="add_participants"></v-switch>
          </div>
        </div>
      </v-tab-item>
      <v-tab-item value="canaux">
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
          <div class="setting checkbox" v-if="format != 'custom'">
            <label>Options de l'annonce auto</label>
            <v-checkbox
              v-for="choice in autoSettingsChoices"
              v-model="auto_settings"
              :key="choice.value"
              :label="choice.label"
              :value="choice.value"
            ></v-checkbox>
          </div>
          <div class="setting" v-if="format != 'auto'">
            <label>Message personnalisé avant pop</label>
            <p class="description">
              Utilisez les tags suivants pour afficher des propriétés du raid :
              <br />
              {arene_nom} ou {arene_nom_nettoye}
              <br />
              {arene_nom_custom} ou {arene_nom_custom_nettoye}
              <br />
              {arene_zone} ou {arene_zone_nettoye}
              <br />
              {arene_description}
              <br />
              {arene_gmaps}
              <br />
              {raid_niveau}
              <br />
              {raid_debut}
              <br />
              {raid_fin}
              <br />
              {utilisateur}
              <br />
              {role_poi_lie}
              <br />
              {role_zone_liee}
              <br />
            </p>
            <textarea v-model="custom_message_before"></textarea>
          </div>
          <div class="setting" v-if="format != 'auto'">
            <label>Message personalisé après pop</label>
            <p class="description">
              Utilisez les tags suivants pour afficher des propriétés du raid :
              <br />
              {arene_nom} ou {arene_nom_nettoye}
              <br />
              {arene_nom_custom} ou {arene_nom_custom_nettoye}
              <br />
              {arene_zone} ou {arene_zone_nettoye}
              <br />
              {arene_description}
              <br />
              {arene_gmaps}
              <br />
              {raid_niveau}
              <br />
              {raid_pokemon} ou {raid_pokemon_nettoye}
              <br />
              {raid_debut}
              <br />
              {raid_fin}
              <br />
              {utilisateur}
              <br />
              {role_poi_lie}
              <br />
              {role_zone_liee}
              <br />
              {role_pokemon_lie}
              <br />
            </p>
            <textarea v-model="custom_message_after"></textarea>
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
  name: "AdminRolesCategoriesEdit",
  components: { Multiselect },
  data() {
    return {
      loading: false,
      dialog: false,
      tabs: null,
      channels: [],
      pokemons: [],
      zones: [],
      levels: [
        { id: 1, name: "1 tête" },
        { id: 2, name: "2 têtes" },
        { id: 3, name: "3 têtes" },
        { id: 4, name: "4 têtes" },
        { id: 5, name: "5 têtes" },
        { id: 6, name: "EX" },
        { id: 7, name: "Méga" },
      ],
      name: "",
      channel_discord_id: false,
      filter_gym_type: "none",
      filter_gym_zone: [],
      filter_gym_gym: [],
      filter_pokemon_type: "none",
      filter_pokemon_level: [],
      filter_pokemon_pokemon: [],
      filter_ex_type: "none",
      filter_source_type: ["auto", "map", "image", "text"],
      sources: [
        { value: "auto", label: "Auto" },
        { value: "map", label: "Map" },
        { value: "image", label: "Image" },
        { value: "text", label: "Texte" },
      ],
      format: "auto",
      custom_message_before: "",
      custom_message_after: "",
      auto_settings: [],
      delete_after_end: true,
      add_channel: false,
      channel_category_discord_id: "",
      channel_duration: "raidend",
      channels_categories: [],
      add_participants: false,
    };
  },
  created() {
    this.fetchChannels();
    this.fetchPokemons();
    //this.fetchGyms();
    this.fetchZones();
    this.fetchDiscordChannelCategories();
    if (this.$route.params.connector_id) {
      this.fetch();
    }
  },
  computed: {
    channelName() {
      return "Toto";
    },
    gyms() {
      return this.$store.state.gyms.filter((element) => element.gym);
    },
    autoSettingsChoices() {
        let choices = [
        { value: "cp", label: "Afficher les CP min et max" },
        { value: "arene_desc", label: "Afficher la description de l'arene" },
      ];
      if( this.add_participants ) {
        choices.push({ value: "participants_nb", label: "Afficher le nombre de participants" });
        choices.push({ value: "participants_list", label: "Afficher la liste des participants" });
      }
      return choices;
    }
  },
  methods: {
    fetch() {
      axios
        .get(
          "/api/user/guilds/" +
            this.$route.params.id +
            "/connectors/" +
            this.$route.params.connector_id
        )
        .then((res) => {
        console.log(res.data);
          this.name = res.data.name;
          this.channel_discord_id = res.data.channel_discord_id;
          this.filter_gym_type = res.data.filter_gym_type;
          this.filter_gym_zone = res.data.filtered_zones;
          this.filter_gym_gym = res.data.filtered_gyms;
          this.filter_pokemon_type = res.data.filter_pokemon_type;
          this.filter_pokemon_level = res.data.filtered_levels;
          this.filter_pokemon_pokemon = res.data.filtered_pokemons;
          this.filter_ex_type = res.data.filter_ex_type;
          this.format = res.data.format;
          this.filter_source_type = res.data.filter_source_type;
          this.custom_message_before = res.data.custom_message_before;
          this.custom_message_after = res.data.custom_message_after;
          this.auto_settings = res.data.auto_settings;
          this.delete_after_end = res.data.delete_after_end;
          this.add_channel = res.data.add_channel;
          this.channel_category_discord_id = res.data.channel_category_discord_id ? res.data.channel_category_discord_id.toString() : false;
          this.channel_duration = res.data.channel_duration;
          this.add_participants = res.data.add_participants;

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
    fetchDiscordChannelCategories() {
      axios
        .get(
          "/api/user/cities/" +
            this.$store.state.currentCity.id +
            "/guilds/" +
            this.$route.params.id +
            "/channelcategories"
        )
        .then((res) => {
          this.channels_categories = res.data;
        })
        .catch((err) => {
          //No error
        });
    },
    fetchPokemons() {
      axios.get("/api/user/pokemon").then((res) => {
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
    submit() {
      const args = {
        name: this.name,
        channel_discord_id: this.channel_discord_id,
        filter_gym_type: this.filter_gym_type,
        filter_gym_zone: this.filter_gym_zone,
        filter_gym_gym: this.filter_gym_gym,
        filter_pokemon_type: this.filter_pokemon_type,
        filter_pokemon_level: this.filter_pokemon_level,
        filter_pokemon_pokemon: this.filter_pokemon_pokemon,
        filter_ex_type: this.filter_ex_type,
        filter_source_type: this.filter_source_type,
        format: this.format,
        custom_message_before: this.custom_message_before,
        custom_message_after: this.custom_message_after,
        auto_settings: this.auto_settings,
        delete_after_end: this.delete_after_end,
        add_channel: this.add_channel,
        channel_category_discord_id: this.channel_category_discord_id,
        channel_duration: this.channel_duration,
        add_participants: this.add_participants,
      };
      if (this.$route.params.connector_id) {
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
            "/connectors/" +
            this.$route.params.connector_id,
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
        .post("/api/user/guilds/" + this.$route.params.id + "/connectors", args)
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
            "/connectors/" +
            this.$route.params.connector_id
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
