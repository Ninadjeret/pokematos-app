<template>
  <div>
    <div class="settings-section">
      <v-subheader>Description</v-subheader>
      <div class="setting">
        <label>Nom</label>
        <input v-model="name" type="text" />
      </div>
      <div class="setting">
        <label>Type</label>
        <select v-if="types" v-model="type">
          <option v-for="item in types" :value="item.id">
            {{ item.name }}
          </option>
        </select>
      </div>
      <div v-if="type == 'megaenergy'" class="setting">
        <label>Pokémon</label>
        <select v-if="pokemons" v-model="sstype">
          <option v-for="pokemon in pokemons" :value="String(pokemon.pokedex_id)">
            {{ pokemon.name_fr.replace('Méga-', '') }}
          </option>
        </select>
      </div>
    </div>

    <div v-if="this.$route.params.reward_id" class="settings-section">
        <v-subheader>Autres actions</v-subheader>
        <v-list-tile color="pink" @click="dialog = true"
          >Supprimer cet objet</v-list-tile
        >
      </div>
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
</template>

<script>
export default {
  name: "AdminQuestReward",
  data() {
    return {
      loading: false,
      dialog: false,
      name: "",
      type: "",
      sstype: null,
      qty: "",
      types: [
        {
          id: 'megaenergy',
          name: 'Méga énergie'
        },
        {
          id: 'potion',
          name: 'Potion'
        },
        {
          id: 'superpotion',
          name: 'Super Potion'
        },
        {
          id: 'hyperpotion',
          name: 'Hyper Potion'
        },
        {
          id: 'potionmax',
          name: 'Potion Max'
        },
        {
          id: 'rappel',
          name: 'Rappel'
        },
        {
          id: 'egg',
          name: 'Oeuf chance'
        },
        {
          id: 'encens',
          name: 'Encens'
        },
        {
          id: 'ct_immediate',
          name: 'CT Attaque Immédiate'
        },
        {
          id: 'ct_chargee',
          name: 'CT Attaque Chargée'
        },
        {
          id: 'ct_immediate_elite',
          name: 'CT Attaque Immédiade d\'élite'
        },
        {
          id: 'ct_charge_eelite',
          name: 'CT Chargée d\'élite'
        },
        {
          id: 'candy',
          name: 'Super Bonbon'
        },
        {
          id: 'xlcandy',
          name: 'Super Bonbon L'
        },
        {
          id: 'raidpass',
          name: 'Passe de raid'
        },
        {
          id: 'remote_raidpass',
          name: 'Passe de Raid à distance'
        },
        {
          id: 'premium_raidpass',
          name: 'Passe de Raid premium'
        },
        {
          id: 'starpiece',
          name: 'Morceau d\'Étoile'
        },
        {
          id: 'pokeball',
          name: 'Poké Ball'
        },
        {
          id: 'greatball',
          name: 'Super Ball'
        },
        {
          id: 'ultraball',
          name: 'Hyper Ball'
        },
        {
          id: 'troykey',
          name: 'Module Leurre'
        },

        {
          id: 'troykey_glacial',
          name: 'Leurre Glacial'
        },
        {
          id: 'troykey_magnetic',
          name: 'Leurre Magnétique'
        },
        {
          id: 'troykey_moss',
          name: 'Leurre Moussu'
        },
        {
          id: 'troykey_rainy',
          name: 'Leurre Pluvieux'
        },


      ],
      pokemons: [],
    };
  },
  created() {
    if (this.$route.params.reward_id) {
      this.fetch();
      this.fetchMega();
    }
  },
  computed: {
    baseUrl() {
      return window.pokematos.baseUrl;
    },
  },
  methods: {
    fetch() {
      axios
        .get("/api/user/quests/rewards/" + this.$route.params.reward_id)
        .then((res) => {
          this.name = res.data.name;
          this.type = res.data.type;
          this.sstype = res.data.sstype;
          this.qty = res.data.qty;
        })
    },
    fetchMega() {
      axios
        .get("/api/user/quests/rewards/mega")
        .then((res) => {
          this.pokemons = res.data;
        })
    },
    submit() {
      const args = {
        name: this.name,
        type: this.type,
        sstype: this.sstype,
        qty: this.qty,
      };
      if (this.$route.params.reward_id) {
        this.save(args);
      } else {
        this.create(args);
      }
    },
    save(args) {
      this.$store.commit("setSnackbar", { message: "Enregistrement en cours" });
      this.loading = true;
      axios
        .put("/api/user/quests/rewards/" + this.$route.params.reward_id, args)
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
    destroy() {
      this.dialog = false;
      this.$store.commit("setSnackbar", { message: "Suppression en cours" });
      axios
        .delete("/api/user/quests/rewards/" + this.$route.params.reward_id)
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