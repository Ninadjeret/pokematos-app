<template>
  <div>
    <div v-if="user" class="user__card">
      <div class="user__summary">
        <div class="user__img">
          <img
            v-if="user.discord_avatar_id"
            :src="
              'https://cdn.discordapp.com/avatars/' +
              user.discord_id +
              '/' +
              user.discord_avatar_id +
              '.png'
            "
          />
          <img v-else src="https://assets.profchen.fr/img/avatar_default.png" />
        </div>
        <div class="user__info">
          <h3>{{ user.name }} <small>//via Discord</small></h3>
          <a href="/logout" class="logout">SE DECONNECTER</a>
        </div>
      </div>
      <div class="user__stats">
        <h3>Performances</h3>
        <div class="stats">
          <div class="stat">
            <span>{{ user.stats.total.raidCreate }}</span> Raids annoncés
          </div>
          <div class="stat">
            <span>{{ user.stats.total.raidUpdate }}</span> Raids mis à jour
          </div>
          <div class="stat">
            <span>{{ user.stats.total.questCreate }}</span> quêtes annoncées
          </div>
        </div>
      </div>
    </div>

    <div class="settings-section" id="user__ranking">
      <h2>Classements</h2>
      <div v-if="user" class="ranking__card">
        <h4>Raids annoncés</h4>
        <div class="ranking__position">
          <div><img src="https://assets.profchen.fr/img/test/test4.png" /></div>
          <table v-if="ranking" class="position__table">
            <thead>
              <td class="position_pos">Pos.</td>
              <td>Nom</td>
              <td class="position_score">Score</td>
            </thead>
            <tr
              v-for="item in ranking"
              :key="item.rank"
              :class="item.user.id == user.id ? 'current' : ''"
            >
              <td class="position_pos">{{ item.rank }}.</td>
              <td class="position_name">{{ item.user.name }}</td>
              <td class="position_score">{{ item.total }}</td>
            </tr>
          </table>
        </div>
        <v-btn class="ranking__more" round large to="/profile/ranking"
          >Voir tous les classements</v-btn
        >
      </div>
    </div>

    <div class="settings-section help">
      <div class="section__title">Assistance</div>
      <div class="settings-section__wrapper">
        <div class="setting__wrapper">
          <a
            href="https://www.profchen.fr/settings/policy/"
            class="setting-link"
            >Politique de confidentialité</a
          >
        </div>
        <div class="setting__wrapper">
          <a href="https://www.pokematos.fr/documentation" class="setting-link"
            >Documentation</a
          >
        </div>
        <div class="setting__wrapper">
          <span @click="updateData" class="setting-link"
            >Retélécharger les données</span
          >
        </div>
      </div>
    </div>
    <div class="settings-section">
      <div class="section__title">Merci pour votre soutien</div>
      <p class="donation">
        Les frais de fonctionnement de Pokématos représentent
        <strong>{{ totalCouts }}€</strong> depuis le 01/01/2019. Grace à vous,
        nous avons déja récupéré <strong>{{ totalDons }}€</strong> !
        <v-progress-linear
          color="#5a6cae"
          height="20"
          :value="pourcentageDons"
        ></v-progress-linear>
        <span class="text-center">
          <v-btn round large href="https://www.pokematos.fr/don"
            >Faire un don</v-btn
          >
        </span>
      </p>
    </div>
    <div class="settings-section about">
      <div class="section__title">A propos</div>

      <p class="credit">
        Pokématos, créé pour vous avec <i class="material-icons">favorite</i
        ><br />
        Version <span id="version">{{ appVersion }}</span>
      </p>
    </div>

    <v-dialog
      content-class="dialog-update"
      v-model="dialogUpdate"
      persistent
      width="300"
    >
      <v-card color="primary">
        <v-card-text>
          <p>
            Mise à jour des données pour la ville {{ currentCity.name
            }}<br /><small><i>cela peut prendre quelques minutes</i></small>
          </p>
          <v-progress-linear
            indeterminate
            color="#5a6cae"
            class="mb-0"
          ></v-progress-linear>
        </v-card-text>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import axios2 from "axios";
export default {
  name: "Profile",
  data() {
    return {
      dialogUpdate: false,
      ranking: false,
    };
  },
  computed: {
    totalCouts() {
      return window.pokematos.donation.goal;
    },
    totalDons() {
      return window.pokematos.donation.current;
    },
    pourcentageDons() {
      return this.totalDons > this.totalCouts
        ? 100
        : (this.totalDons / this.totalCouts) * 100;
    },
    user() {
      return this.$store.state.user;
    },
    appVersion() {
      return window.pokematos.version;
    },
    currentCity() {
      return this.$store.state.currentCity;
    },
  },
  created() {
    this.$store.commit("fetchUser");
    this.fetchRanking();
  },
  methods: {
    async updateData() {
      this.dialogUpdate = true;
      try {
        await this.$store.dispatch("changeCity", this.currentCity);
      } finally {
        this.dialogUpdate = false;
      }
    },
    fetchRanking() {
      axios
        .get("/api/user/cities/" + this.currentCity.id + "/ranking/short")
        .then((res) => {
          this.ranking = res.data;
        });
    },
  },
};
</script>
