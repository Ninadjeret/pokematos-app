<template>
  <div>
    <div v-if="!isLoaded" class="loading">
      <div class="loading__content">
        <i class="friendball"></i>
        <p>Chargement...</p>
      </div>
    </div>

    <div v-if="isLoaded" class="event">
      <v-tabs v-model="tabs" color="transparent" slider-color="#8e56d9" class>
        <v-tab href="#description" class="primary--text">Présentation</v-tab>
        <v-tab href="#settings" class="primary--text">Réglages</v-tab>
        <v-tab v-if="features.events_multi" href="#guests" class="primary--text"
          >Guilds</v-tab
        >
      </v-tabs>

      <v-tabs-items v-model="tabs">
        <v-tab-item value="description">
          <div class="settings-section">
            <v-subheader>Description</v-subheader>
            <div class="setting">
              <label>Nom</label>
              <input v-model="name" type="text" />
            </div>
            <div class="setting datetime">
              <label>Début de l'évent</label>
              <v-datetime-picker
                label="Début de l'évent"
                v-model="start_time"
                date-format="dd/MM/yyyy"
                time-format="HH:mm"
                :datePickerProps="{ locale: 'fr' }"
                :timePickerProps="{ format: '24hr' }"
              ></v-datetime-picker>
            </div>
            <div class="setting image">
              <label>Image</label>
              <span v-if="image !== null" class="preview">
                <v-btn
                  fab
                  dark
                  small
                  color="rgba(0,0,0,0.5)"
                  @click="image = null"
                >
                  <v-icon>close</v-icon>
                </v-btn>
                <img :src="image" />
              </span>
              <input
                type="file"
                class="form-control"
                v-on:change="onImageChange"
              />
            </div>
            <div class="setting">
              <label>Type d'évent</label>
              <select :disabled="disabled" v-model="type">
                <option
                  v-for="typesingle in types"
                  :value="typesingle.id"
                  :key="typesingle.id"
                >
                  {{ typesingle.name }}
                </option>
              </select>
              <p class="commentaire">{{ types[type].description }}</p>
            </div>
          </div>

          <div class="settings-section">
            <v-subheader>Lien avec Discord</v-subheader>
            <div class="setting">
              <label>Lier l'évent avec Discord ?</label>
              <v-btn-toggle v-model="channel_discord_type" mandatory>
                <v-btn :disabled="disabled" v-if="type == 'train'" value="none"
                  >Aucun lien</v-btn
                >
                <v-btn :disabled="disabled" value="existing"
                  >Salon existant</v-btn
                >
                <v-btn :disabled="disabled" value="temp"
                  >Salon temporaire</v-btn
                >
              </v-btn-toggle>
            </div>
            <div v-if="channel_discord_type == 'temp'" class="setting">
              <label>Catégorie de salon</label>
              <p class="description">
                Le salon temporaire sera créé dans la catégorie choisie. (les
                droits appliqués au salon seront les mêmes que ceux de la
                catégorie)
              </p>
              <select
                v-if="channels_categories"
                v-model="category_discord_id"
                :disabled="disabled"
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
            <div v-if="channel_discord_type == 'existing'" class="setting">
              <label>Salon</label>
              <select
                v-if="channels"
                v-model="channel_discord_id"
                :disabled="disabled"
              >
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
            <v-divider></v-divider>
            <div v-if="getId">
              <v-subheader>Autres actions</v-subheader>
              <v-list-tile color="pink" @click="dialog = true"
                >Supprimer l'évent</v-list-tile
              >
            </div>
          </div>
        </v-tab-item>

        <v-tab-item value="settings">
          <div class="settings-section">
            <v-subheader v-if="type == 'quiz'">PokéQuiz</v-subheader>
            <div v-if="type == 'quiz'">
              <v-alert :value="isQuizStarted" type="warning"
                >Le quiz a commencé. Les questions et délais ne peuvent plus
                être modifiés</v-alert
              >
              <div class="setting">
                <label>Nombre de questions</label>
                <select :disabled="isQuizStarted" v-model="quiz.nb_questions">
                  <option
                    v-for="choicesNbQuestion in choicesNbQuestions"
                    :value="choicesNbQuestion"
                    :key="choicesNbQuestion"
                  >
                    {{ choicesNbQuestion }}
                  </option>
                </select>
              </div>
              <div class="setting">
                <label>Délai max pour fournir une bonne réponse</label>
                <p class="description">
                  En minutes. Au dela de ce délai, si personne n'a la bonne
                  réponse, Pokématos passera à la question suivante.
                </p>
                <select :disabled="isQuizStarted" v-model="quiz.delay">
                  <option
                    v-for="choicesDelay in choicesDelays"
                    :value="choicesDelay"
                    :key="choicesDelay"
                  >
                    {{ choicesDelay }}min
                  </option>
                </select>
              </div>
              <div class="setting checkbox">
                <label>Difficulté des questions</label>
                <v-checkbox
                  @change="fetchQuizAvailableQuestions()"
                  v-for="(level, index) in levels"
                  v-model="quiz.difficulties"
                  :key="level.id"
                  :label="level.label"
                  :value="level.id"
                ></v-checkbox>
              </div>
              <!--<div class="setting checkbox">
                                <label>Thèmes des questions</label>
                                <v-checkbox
                                    v-for="(theme, index) in themes"
                                    v-model="quiz.themes"
                                    :key="theme.id"
                                    :label="theme.name"
                                    :value="theme.id">
                                </v-checkbox>
              </div>-->
              <div class="setting d-flex switch">
                <div>
                  <label
                    >Proposer des questions en lien uniquement avec PokémonGO
                    ?</label
                  >
                </div>
                <v-switch
                  @change="fetchQuizAvailableQuestions()"
                  :disabled="isQuizStarted"
                  v-model="quiz.only_pogo"
                ></v-switch>
              </div>
              <v-alert :value="true" type="info"
                >{{ availableQuestions }} questions répondent aux
                critères</v-alert
              >
            </div>

            <v-subheader v-if="type == 'train'">Pokétrain</v-subheader>
            <div v-if="type == 'train'">
              <event-train ref="editableTrain" :steps="steps"></event-train>
            </div>
          </div>
        </v-tab-item>

        <v-tab-item v-if="features.events_multi" value="guests">
          <div v-if="type == 'quiz'" class="settings-section invit-guets">
            <v-subheader>Événement multi-guilds</v-subheader>
            <div class="setting d-flex switch">
              <div>
                <label>Événement multi-guilds</label>
                <p class="description">
                  Inviter d'autres guilds à vous défier et participer à votre
                  événement
                </p>
              </div>
              <v-switch v-model="multi_guilds"></v-switch>
            </div>
            <div v-if="multi_guilds" class="setting">
              <v-subheader>Guilds invitées</v-subheader>
              <multiselect
                :reset-after="true"
                v-model="temp"
                :options="availableGuilds"
                track-by="id"
                label="name"
                placeholder="Inviter une Guild"
                @select="addGuest"
              ></multiselect>
              <div
                v-for="(guest, index) in guests"
                :class="'guest guest-' + getGuestStatusIcon(guest)"
                :key="guest.guild_id"
              >
                <v-list-tile avatar :key="guest.guild_id">
                  <v-list-tile-avatar>
                    <v-icon>{{ getGuestStatusIcon(guest) }}</v-icon>
                  </v-list-tile-avatar>
                  <v-list-tile-content>
                    <v-list-tile-title>{{
                      guest.guild.name
                    }}</v-list-tile-title>
                    <v-list-tile-sub-title
                      >{{ getGuestStatusLabel(guest) }}
                      {{ getGuestStatusDate(guest) }}</v-list-tile-sub-title
                    >
                  </v-list-tile-content>
                  <v-btn
                    v-if="guest.status == 'pending'"
                    flat
                    icon
                    color="deep-orange"
                    @click="removeGuest(index)"
                  >
                    <v-icon>close</v-icon>
                  </v-btn>
                </v-list-tile>
              </div>
            </div>
          </div>
          <div v-else class="settings-section invit-guets">
            <p>L'événement actuel n'est pas disponible en mode multi-guilds</p>
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
  </div>
</template>

<script>
import moment from "moment";
import Multiselect from "vue-multiselect";
import draggable from "vuedraggable";
export default {
  name: "AdminEvent",
  components: { Multiselect, draggable },
  data() {
    return {
      loading: false,
      dialog: false,
      tabs: "description",
      name: "",
      start_time: null,
      type: "quiz",
      steps: [],
      image: null,
      multi_guilds: false,
      quiz: {
        nb_questions: 10,
        delay: 5,
        themes: [],
        difficulties: [],
        only_pogo: false,
      },
      levels: [
        { id: 1, label: "Facile" },
        { id: 2, label: "Moyen" },
        { id: 3, label: "Difficile" },
        { id: 5, label: "Expert" },
      ],
      choicesNbQuestions: [5, 10, 15, 20, 25, 30],
      choicesDelays: [1, 2, 3, 4, 5],
      fetchLoaded: false,
      fetchGuildsLoaded: false,
      fetchQuizThemesLoaded: false,
      fetchDiscordChannelCategoriesLoaded: false,
      fetchDiscordChannelsLoaded: false,
      availableGuilds: [],
      guests: [],
      themes: [],
      temp: null,
      availableQuestions: 0,
      channel_discord_type: "temp",
      channel_discord_id: null,
      channels_categories: [],
      channels: [],
      category_discord_id: null,
    };
  },
  computed: {
    features() {
      return window.pokematos.features;
    },
    isLoaded() {
      return (
        this.fetchLoaded &&
        this.fetchGuildsLoaded &&
        this.fetchQuizThemesLoaded &&
        this.fetchDiscordChannelCategoriesLoaded &&
        this.fetchDiscordChannelsLoaded
      );
    },
    types() {
      let types = {};
      if (this.features.events_train) {
        types["train"] = {
          id: "train",
          name: "Pokétrain",
          description:
            "Un pokétrain permet d'orgniser un parcours d'arènes, par exemple lors des journées à 5 pass gratuits.",
        };
      }
      if (this.features.events_quiz) {
        types["quiz"] = {
          id: "quiz",
          name: "Pokéquiz",
          description: "Organisez des quiz drôles et challengeants",
        };
      }
      return types;
    },
    getId() {
      return this.$route.params.event_id &&
        Number.isInteger(parseInt(this.$route.params.event_id))
        ? parseInt(this.$route.params.event_id)
        : false;
    },
    disabled() {
      return this.getId !== false;
    },
    gyms() {
      return this.$store.state.gyms.filter((gym) => gym.gym);
    },
    user() {
      return this.$store.state.user;
    },
    isQuizStarted() {
      if (this.start_time === null) return false;
      let startTime = moment(this.start_time);
      return startTime.isBefore();
    },
  },
  created() {
    this.fetchGuilds();
    this.fetchQuizThemes();
    this.fetchDiscordChannelCategories();
    this.fetchDiscordChannels();
    this.fetchQuizAvailableQuestions();
    if (this.getId) {
      this.fetch();
    } else {
      this.fetchLoaded = true;
    }
  },
  methods: {
    fetch() {
      axios
        .get(
          "/api/user/cities/" +
            this.$store.state.currentCity.id +
            "/events/" +
            this.getId
        )
        .then((res) => {
          this.name = res.data.name;
          this.start_time = new Date(res.data.start_time);
          this.type = res.data.type;
          this.steps = res.data.relation.steps;
          this.image = res.data.image;
          this.guests = res.data.guests;
          this.multi_guilds = res.data.multi_guilds;
          if (this.type == "quiz") this.quiz = res.data.relation;
          this.channel_discord_id = res.data.channel_discord_id;
          this.channel_discord_type = res.data.channel_discord_type;
          this.fetchLoaded = true;
          this.fetchQuizAvailableQuestions();
        })
        .catch((err) => {
          let message = "Problème lors de la récupération";
          if (err.response && err.response.data) {
            message = err.response.data;
          }
          this.$store.commit("setSnackbar", {
            message: message,
            timeout: 1500,
          });
        });
    },
    fetchGuilds() {
      axios
        .get("/api/user/guilds/" + this.$route.params.id + "/events/guilds")
        .then((res) => {
          this.availableGuilds = res.data;
          this.fetchGuildsLoaded = true;
        });
    },
    fetchQuizThemes() {
      axios.get("/api/events/quiz/themes").then((res) => {
        this.themes = res.data;
        this.fetchQuizThemesLoaded = true;
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
          this.fetchDiscordChannelCategoriesLoaded = true;
          this.channels_categories = res.data;
        })
        .catch((err) => {
          //No error
        });
    },
    fetchDiscordChannels() {
      axios
        .get(
          "/api/user/cities/" +
            this.$store.state.currentCity.id +
            "/guilds/" +
            this.$route.params.id +
            "/channels"
        )
        .then((res) => {
          this.fetchDiscordChannelsLoaded = true;
          this.channels = res.data;
        });
    },
    fetchQuizAvailableQuestions() {
      const args = {
        nb_questions: this.quiz.nb_questions,
        themes: this.quiz.themes,
        difficulties: this.quiz.difficulties,
        only_pogo: this.quiz.only_pogo,
      };
      axios
        .get("/api/events/quiz/available-questions", { params: args })
        .then((res) => {
          this.availableQuestions = res.data;
        });
    },
    onImageChange(e) {
      let toUpload = e.target.files[0];
      let formData = new FormData();
      let that = this;
      formData.append("image", toUpload);
      axios
        .post("api/user/upload", formData, {
          headers: { "Content-Type": "multipart/form-data" },
        })
        .then(function (res) {
          that.image = "/storage/user/" + that.user.id + "-" + res.data;
        });
    },
    addGuest(selectedOption, id) {
      if (
        this.guests.filter((guest) => guest.guild_id == selectedOption.id)
          .length > 0
      )
        return;
      this.guests.push({
        event_id: this.getId,
        guild_id: selectedOption.id,
        status: "pending",
        guild: {
          name: selectedOption.name,
        },
      });
      this.temp = null;
    },
    removeGuest(index) {
      this.guests.splice(index, 1);
    },
    getGuestStatusIcon(guest) {
      if (guest.status == "accepted") return "event_available";
      if (guest.status == "refused") return "event_busy";
      return "hourglass_empty";
    },
    getGuestStatusLabel(guest) {
      if (!guest.id) return "Prête à être envoyée";
      if (guest.status == "accepted") return "Acceptée";
      if (guest.status == "refused") return "Refusée";
      return "Envoyée";
    },
    getGuestStatusDate(guest) {
      if (!guest.id) return "";
      let date = moment(guest.status.time);
      return "le " + date.format("DD/MM [à] HH[h]mm");
    },
    submit() {
      let start_time = moment(this.start_time.toString());
      const args = {
        name: this.name,
        type: this.type,
        start_time: start_time.format("YYYY-MM-DD HH:mm:SS"),
        steps: this.$refs.editableTrain
          ? this.$refs.editableTrain.editableSteps
          : [],
        quiz: this.quiz,
        image: this.image,
        multi_guilds: this.multi_guilds,
        guests: this.guests,
        channel_discord_type: this.channel_discord_type,
        channel_discord_id: this.channel_discord_id,
        category_discord_id: this.category_discord_id,
      };
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
        .put(
          "/api/user/guilds/" + this.$route.params.id + "/events/" + this.getId,
          args
        )
        .then((res) => {
          this.fetch();
          this.$store.commit("setSnackbar", {
            message: "Enregistrement effectué",
            timeout: 1500,
          });
          this.loading = false;
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
          this.loading = false;
        });
    },
    create(args) {
      this.$store.commit("setSnackbar", { message: "Enregistrement en cours" });
      this.loading = true;
      axios
        .post("/api/user/guilds/" + this.$route.params.id + "/events", args)
        .then((res) => {
          this.$store.commit("setSnackbar", {
            message: "Enregistrement effectué",
            timeout: 1500,
          });
          this.loading = false;
          this.$router.push({ name: this.$route.meta.parent });
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
          this.loading = false;
        });
    },
    destroy() {
      this.dialog = false;
      this.$store.commit("setSnackbar", { message: "Suppression en cours" });
      axios
        .delete(
          "/api/user/guilds/" + this.$route.params.id + "/events/" + this.getId
        )
        .then((res) => {
          this.$store.commit("setSnackbar", {
            message: "suppression effectuée",
            timeout: 1500,
          });
          this.$router.push({ name: this.$route.meta.parent });
        })
        .catch((err) => {
          this.$store.commit("setSnackbar", {
            message: "Problème lors de la suppression",
            timeout: 1500,
          });
        });
    },
  },
};
</script>

<style>
.button {
  margin-top: 35px;
}
.flip-list-move {
  transition: transform 0.5s;
}
.no-move {
  transition: transform 0s;
}
.ghost {
  opacity: 0.5;
  background: #c8ebfb;
}
.list-group {
  min-height: 20px;
}
.list-group-item {
  cursor: move;
}
.list-group-item i {
  cursor: pointer;
}
</style>
