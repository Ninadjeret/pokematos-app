<template>
    <div>
        <div class="settings-section">
            <div class="search__wrapper">
                <v-text-field single-line hide-details outline v-model="search" label="Recherche"></v-text-field>
            </div>
            <v-list>
            <template v-for="(item, index) in filteredItems">
              <v-list-tile :key="item.id" :to="{ name: 'admin.roles.categories.edit', params: { id: $route.params.id, category_id:item.id } }">
                <v-list-tile-content>
                  <v-list-tile-title :style="'color:'+item.color">
                      {{item.name}}
                  </v-list-tile-title>
                </v-list-tile-content>
              </v-list-tile>
              <v-divider></v-divider>
            </template>
          </v-list>
            <v-btn dark fixed bottom right fab :to="{ name: 'admin.roles.categories.add', params: { id: $route.params.id } }"><v-icon>add</v-icon></v-btn>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'AdminRolesCategories',
        data() {
            return {
                search: null,
                items: [],
            }
        },
        computed: {
            filteredItems() {
                return this.items.filter((item) => {
                    let matchingTitle = 1;
                    if (this.search != null) {
                        matchingTitle = item.name.toLowerCase().indexOf(this.search.toLowerCase()) > -1;
                    }
                    return (matchingTitle);
                });
            },
        },
        created() {
            this.fetch();
        },
        methods: {
            fetch() {
                axios.get('/api/user/guilds/'+this.$route.params.id+'/rolecategories').then( res => {
                    this.items = res.data;
                }).catch( err => {
                    //No error
                });
            },
        }
    }
</script>
