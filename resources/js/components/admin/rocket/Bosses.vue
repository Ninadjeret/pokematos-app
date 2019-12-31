<template>
  <div>
    <div class="settings-section">
      <v-list>
        <template v-for="(boss) in bosses">
          <v-list-tile
            :key="boss.id"
            :to="{ name: 'admin.rocket.boss', params: { rocket_boss_id: boss.id } }"
          >
            <v-list-tile-content>
              <v-list-tile-title>{{boss.name}}</v-list-tile-title>
            </v-list-tile-content>
          </v-list-tile>
          <v-divider :key="boss.id"></v-divider>
        </template>
      </v-list>
    </div>
  </div>
</template>

<script>
export default {
  name: "AdminRocketBosses",
  data() {
    return {
      bosses: []
    };
  },
  created() {
    this.fetchBosses();
  },
  methods: {
    fetchBosses() {
      axios.get("/api/rocket/bosses").then(res => {
        this.bosses = res.data;
        let message = "Problème lors de la récupération";
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
