<template>
  <div>
    <div class="settings-section">
      <div class="search__wrapper">
        <v-text-field
          single-line
          hide-details
          outline
          v-model="search"
          label="Recherche"
        ></v-text-field>
      </div>
      <v-list>
        <template v-for="item in paginateItems">
          <v-list-tile
            :key="item.id"
            :to="{
              name: 'admin.quests.rewards.edit',
              params: { reward_id: item.id },
            }"
          >
            <v-list-tile-avatar>
              <img :src="item.thumbnail_url" />
            </v-list-tile-avatar>
            <v-list-tile-content>
              <v-list-tile-title>
                {{ item.name }}
              </v-list-tile-title>
            </v-list-tile-content>
          </v-list-tile>
          <v-divider :key="`d${item.id}`"></v-divider>
        </template>
      </v-list>
      <div class="text-xs-center">
        <v-pagination
          v-model="page"
          :length="totalPages"
          circle
          total-visible="7"
        ></v-pagination>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "AdminQuestRewards",
  data() {
    return {
      search: null,
      items: [],
      page: 1,
      perPage:20,
    };
  },
  computed: {
    totalPages() {
      return Math.ceil( this.filteredItems.length / this.perPage );
    },
    filteredItems() {
      return this.items.filter((item) => {
        let matchingTitle = 1;
        if (this.search != null && item.name != undefined) {
          matchingTitle =
            item.name.toLowerCase().indexOf(this.search.toLowerCase()) > -1;
        }
        return matchingTitle;
      });
    },
    paginateItems() {
      let items = this.filteredItems;
      if( this.search != '' && this.page > ( items.length / this.perPage ) ) this.page = 1;
      let start = (this.page === 1) ? 0 : (this.page - 1) * this.perPage;
      let end = start + this.perPage;
      return this.filteredItems.slice(start, end);
    },
  },
  created() {
    this.fetch();
  },
  methods: {
    fetch() {
      axios
        .get("/api/user/quests/rewards")
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
