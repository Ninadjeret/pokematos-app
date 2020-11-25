<template>
  <div>
    <div class="settings-section">
      <v-btn-toggle @change="fetchRanking" v-model="ranking_period" mandatory>
        <v-btn value="total">Total</v-btn>
        <v-btn value="month">Ce mois</v-btn>
        <v-btn value="custom">Personnalisé</v-btn>
      </v-btn-toggle>
      <v-layout v-if="ranking_period == 'custom'" row wrap>
        <v-flex xs6 sm6 md6>
          <v-menu
            v-model="period_start_menu"
            :close-on-content-click="false"
            :nudge-right="40"
            lazy
            transition="scale-transition"
            offset-y
            full-width
          >
            <template v-slot:activator="{ on }">
              <v-text-field
                v-model="period_start"
                label="Date de début"
                prepend-icon="event"
                readonly
                v-on="on"
              ></v-text-field>
            </template>
            <v-date-picker
              v-model="period_start"
              @input="period_start_menu = false"
              @change="fetchRanking"
              locale="fr-fr"
              :first-day-of-week="1"
            ></v-date-picker>
          </v-menu>
        </v-flex>
        <v-flex xs6 sm6 md6>
          <v-menu
            v-model="period_end_menu"
            :close-on-content-click="false"
            :nudge-right="40"
            lazy
            transition="scale-transition"
            offset-y
            full-width
          >
            <template v-slot:activator="{ on }">
              <v-text-field
                v-model="period_end"
                label="Date de fin"
                prepend-icon="event"
                readonly
                v-on="on"
              ></v-text-field>
            </template>
            <v-date-picker
              v-model="period_end"
              @input="period_end_menu = false"
              @change="fetchRanking"
              locale="fr-fr"
              :first-day-of-week="1"
            ></v-date-picker>
          </v-menu>
        </v-flex>
      </v-layout>
    </div>
    <div v-if="!ranking" class="loading">
      <div class="loading__content">
        <i class="friendball"></i>
        <p>Chargement...</p>
      </div>
    </div>
    <div v-if="ranking && ranking.length > 0">
      <div v-if="user" class="ranking__top">
        <h4>Top 3</h4>
        <div class="top3">
          <div
            v-for="position in top3"
            :key="position"
            :class="'top top-' + position"
          >
            <div class="thumbnail">
              <img
                v-if="ranking[position - 1].user.discord_avatar_id"
                :src="
                  'https://cdn.discordapp.com/avatars/' +
                  ranking[position - 1].user.discord_id +
                  '/' +
                  ranking[position - 1].user.discord_avatar_id +
                  '.png'
                "
              />
              <img
                v-else
                src="https://assets.profchen.fr/img/avatar_default.png"
              />
              <div class="position">{{ position }}</div>
            </div>
            <div class="name">{{ ranking[position - 1].user.name }}</div>
            <div v-if="type == 'top100'" class="city">
              {{ ranking[position - 1].city }}
            </div>
            <div class="score">{{ ranking[position - 1].total }}</div>
          </div>
        </div>
      </div>
      <div
        class="ranking__item"
        v-for="item in ranking"
        :key="item.rank"
        v-if="item.rank > 3"
        :class="
          item.user.id == user.id
            ? 'current rank-' + item.rank
            : 'rank-' + item.rank
        "
      >
        <template>
          <div class="position_pos">{{ item.rank }}.</div>
          <div class="position_name">
            <div>
              <img
                v-if="item.user.discord_avatar_id"
                :src="
                  'https://cdn.discordapp.com/avatars/' +
                  item.user.discord_id +
                  '/' +
                  item.user.discord_avatar_id +
                  '.png'
                "
              />
              <img
                v-else
                src="https://assets.profchen.fr/img/avatar_default.png"
              />
            </div>
            <div class="namecity">
              <span class="name">{{ item.user.name }}</span>
              <span class="city" v-if="type == 'top100'">{{ item.city }}</span>
            </div>
          </div>
          <div class="position_score">{{ item.total }}</div>
        </template>
      </div>
    </div>
  </div>
</template>

<script>
import moment from "moment";
export default {
  name: "UserRanking",
  props: {
    type: String,
  },
  data() {
    return {
      ranking: false,
      ranking_period: "total",
      period_start: moment().format("YYYY-MM") + "-01",
      period_start_menu: false,
      period_end: moment().format("YYYY-MM-DD"),
      period_end_menu: false,
    };
  },
  created() {
    this.fetchRanking();
  },
  computed: {
    user() {
      return this.$store.state.user;
    },
    currentCity() {
      return this.$store.state.currentCity;
    },
    top3() {
      if (this.ranking.length >= 3) {
        return [2, 1, 3];
      } else if (this.ranking.length >= 2) {
        return [2, 1];
      } else if (this.ranking.length >= 1) {
        return [1];
      }
    },
  },
  methods: {
    fetchRanking() {
      this.ranking = false;
      let args = {
        type: this.$props.type,
        period_type: this.ranking_period,
        period_start: this.period_start,
        period_end: this.period_end,
      };
      console.log(args);
      axios
        .get("/api/user/cities/" + this.currentCity.id + "/ranking", {
          params: args,
        })
        .then((res) => {
          this.ranking = res.data;
        });
    },
  },
};
</script>
