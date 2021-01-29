<template>
  <div>
    <div class="rankings">
      <v-tabs v-model="tabs" color="transparent" slider-color="#8e56d9" class>
        <v-tab href="#raids" class="primary--text">Raids</v-tab>
        <v-tab href="#quetes" class="primary--text">Quêtes</v-tab>
        <v-tab href="#rocket" class="primary--text">Rocket</v-tab>
        <v-tab href="#global" class="primary--text">Top 100</v-tab>
      </v-tabs>
      <v-tabs-items v-model="tabs">
        <v-tab-item value="raids">
          <user-ranking type="raids"></user-ranking>
        </v-tab-item>
        <v-tab-item value="quetes">
          <user-ranking type="quests"></user-ranking>
        </v-tab-item>
        <v-tab-item value="rocket">
          <user-ranking type="rocket"></user-ranking>
        </v-tab-item>
        <v-tab-item value="global">
          <user-ranking type="top100"></user-ranking>
        </v-tab-item>
      </v-tabs-items>
    </div>
  </div>
</template>

<script>
import moment from "moment";
export default {
  name: "Ranking",
  data() {
    return {
      tabs: null,
    };
  },
};
</script>
