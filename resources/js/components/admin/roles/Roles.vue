<template>
<div>
    <div class="settings-section">
        <div class="search__wrapper">
            <v-text-field single-line hide-details outline v-model="search" label="Recherche"></v-text-field>
        </div>
        <v-list>
            <template v-for="(item, index) in filteredItems">
              <v-list-tile :key="item.id" :to="{ name: 'admin.roles.roles.edit', params: { id: $route.params.id, role_id:item.id } }">
                <v-list-tile-content>
                  <v-list-tile-title :style="'color:'+item.color">
                      {{item.name}} <span v-if="item.category" class=""> // {{item.category.name}}</span>
                  </v-list-tile-title>
                </v-list-tile-content>
            </v-list-tile>
              </v-list-tile>
              <v-divider></v-divider>
            </template>
        </v-list>
        <v-btn dark fixed bottom right fab :to="{ name: 'admin.roles.roles.add', params: { id: $route.params.id } }">
            <v-icon>add</v-icon>
        </v-btn>
    </div>
</div>
</template>

<script>
    export default {
        name: 'AdminRolesRoles',
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
                    let matchingCategory = 1;
                    let matchingType = 1;
                    if (this.search != null) {
                        matchingTitle = item.name.toLowerCase().indexOf(this.search.toLowerCase()) > -1;
                        matchingCategory = item.category.name.toLowerCase() == this.search.toLowerCase();
                        matchingType = ':'+item.type == this.search.toLowerCase();
                    }
                    return (matchingTitle || matchingCategory || matchingType);
                });
            },
        },
        created() {
            this.fetch();
        },
        methods: {
            fetch() {
                axios.get('/api/user/guilds/'+this.$route.params.id+'/roles').then( res => {
                    this.items = res.data;
                }).catch( err => {
                    //No error
                });
            },
        }
    }
</script>
