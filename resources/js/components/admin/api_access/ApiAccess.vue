<template>
  <div>
    <div class="settings-section">
      <v-list>
        <template v-for="item in items">
          <v-list-tile
            :key="item.id"
            :to="{
              name: 'admin.api_access.edit',
              params: { id: $route.params.id, api_access_id: item.id },
            }"
          >
            <v-list-tile-content>
              <v-list-tile-title>
                {{ item.name }}
              </v-list-tile-title>
            </v-list-tile-content>
          </v-list-tile>
          <v-divider :key="`d${item.id}`"></v-divider>
        </template>
      </v-list>
    </div>
    <v-btn dark fixed bottom right fab :to="{ name: 'admin.api_access.add' }"
      ><v-icon>add</v-icon></v-btn
    >
  </div>
</template>

<script>
export default {
  name: "AdminApiAccess",
  data() {
    return {
      items: [],
    };
  },
  created() {
    this.fetch();
  },
  methods: {
    fetch() {
      axios
        .get("/api/user/guilds/" + this.$route.params.id + "/api_access")
        .then((res) => {
          this.items = res.data;
        })
        .catch((err) => {
          //No error
        });
    },
  },
};
</script>
