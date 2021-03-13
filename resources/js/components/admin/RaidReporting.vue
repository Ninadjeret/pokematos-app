<template>
  <div>
    <div class="parent_view" v-if="$route.name == 'admin.raids'">
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
        <v-subheader>Signalement des raids</v-subheader>
        <div class="setting d-flex switch">
          <div>
            <label>Analyser les captures d'écran publiées</label>
          </div>
          <v-switch v-model="raidreporting_images_active"></v-switch>
        </div>
        <div v-if="raidreporting_images_active" class="setting d-flex switch">
          <div>
            <label>Supprimer les captures après analyse ?</label>
          </div>
          <v-switch v-model="raidreporting_images_delete"></v-switch>
        </div>
        <div class="setting d-flex switch">
          <div>
            <label>Analyser les messages texte publiés</label>
          </div>
          <v-switch v-model="raidreporting_text_active"></v-switch>
        </div>
        <div v-if="raidreporting_text_active" class="setting d-flex switch">
          <div>
            <label>Supprimer les messages texte d'annonce de raid ?</label>
          </div>
          <v-switch v-model="raidreporting_text_delete"></v-switch>
        </div>
        <div v-if="raidreporting_text_active" class="setting">
          <label>Préfixes des messages texte</label>
          <p class="description">
            Indiquer par quoi doivent commencer les messages texte pour être
            analysés ?
          </p>
          <input v-model="raidreporting_text_prefixes" type="text" />
        </div>
        <div class="setting">
          <label>Salons à analyser</label>
          <p class="description">
            Vous pouvez spécifier certains salons dans lesquels Pokématos doit
            analyser les messages de raid. Sinon, par défaut, il cherchera sur
            tous les canaux auxquels il a accès
          </p>
          <select
            multiple="true"
            v-if="channels"
            v-model="raidreporting_allowed_channels"
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
        <div class="setting">
          <label>Probabilité minimale de correspondance des arènes</label>
          <p class="description">
            Compris entre 1 et 100, cela définir le niveau de probabilité
            minimale que le bot accepte pour considérer qu'il a trouvé une
            arène. (100 est le max)
          </p>
          <input
            v-model="raidreporting_gym_min_proability"
            type="number"
            min="1"
            max="100"
            step="1"
          />
        </div>
        <v-btn dark fixed bottom right fab @click="submit()">
          <v-progress-circular
            v-if="loading"
            indeterminate
            color="primary"
          ></v-progress-circular>
          <v-icon v-else>save</v-icon>
        </v-btn>
      </div>
        <div class="settings-section">
            <v-subheader>Organisation des raids</v-subheader>
            <div class="setting d-flex switch">
                <div>
                    <label>Gérer le multi-comptes</label>
                    <p class="description">
            Si l'option est activée, les joueurs pourront préciser, pour chaque raid, le nombre de comptes présents.
          </p>
                </div>
                <v-switch v-model="raidorga_nb_players"></v-switch>
            </div>
        </div>

    </div>

    <transition name="fade">
      <router-view></router-view>
    </transition>
  </div>
</template>

<script>
import { mapState } from "vuex";

export default {
  name: "AdminRaidReportingHome",
  data() {
    return {
      loading: false,
      items: [
        {
          label: "Gérer les annonces",
          route: "admin.raids.annonces",
          icon: "settings_input_component",
        },
      ],
      raidreporting_images_active: false,
      raidreporting_images_delete: false,
      raidreporting_text_active: false,
      raidreporting_text_delete: false,
      raidreporting_text_prefixes: "+raid, +Raid",
      raidreporting_gym_min_proability: 70,
      raidreporting_allowed_channels: [],
      raidorga_nb_players: false,
      channels: [],
    };
  },
  computed: mapState(["currentCity"]),
  created() {
    this.fetch();
    this.fetchChannels();
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
          this.raidreporting_images_active = parseInt(
            res.data.raidreporting_images_active
          );
          this.raidreporting_images_delete = parseInt(
            res.data.raidreporting_images_delete
          );
          this.raidreporting_text_active = parseInt(
            res.data.raidreporting_text_active
          );
          this.raidreporting_text_delete = parseInt(
            res.data.raidreporting_text_delete
          );
          this.raidreporting_text_prefixes = res.data.raidreporting_text_prefixes.join(
            ", "
          );
          this.raidreporting_gym_min_proability =
            res.data.raidreporting_gym_min_proability;
          this.raidreporting_allowed_channels =
            res.data.raidreporting_allowed_channels;
          this.raidorga_nb_players = parseInt(
            res.data.raidorga_nb_players
          );
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
          raidreporting_images_active: this.raidreporting_images_active,
          raidreporting_images_delete: this.raidreporting_images_delete,
          raidreporting_text_active: this.raidreporting_text_active,
          raidreporting_text_delete: this.raidreporting_text_delete,
          raidreporting_allowed_channels: this.raidreporting_allowed_channels,
          raidreporting_text_prefixes: this.raidreporting_text_prefixes.split(
            ", "
          ),
          raidreporting_gym_min_proability: this.raidreporting_gym_min_proability,
          raidorga_nb_players: this.raidorga_nb_players,
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
