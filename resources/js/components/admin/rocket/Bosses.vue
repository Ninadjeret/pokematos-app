<template>
  <div>
    <div class="settings-section">
      <v-tabs
        v-model="active"
        dark
        icons-and-text
        @change="boss = bosses[active]"
      >
        <v-tab v-for="(boss, index) in bosses" :key="boss.id" ripple>
          {{ boss.name }}
          <img :src="boss.thumbnail" />
        </v-tab>
        <v-tab-item v-for="(boss, index) in bosses" :key="boss.id">
          <v-card flat>
            <v-card-text>
              <div v-for="step in steps" class="settings-section">
                <v-subheader>{{ step.name }}</v-subheader>
                <multiselect
                  :reset-after="true"
                  v-model="value"
                  :options="pokemons"
                  track-by="name_fr"
                  label="name_fr"
                  placeholder="Ajouter un Pokémon"
                  @select="addPokemonToStep($event, step.id)"
                >
                  <template slot="singleLabel" slot-scope="{ option }"
                    ><strong>{{ option.name_fr }}</strong></template
                  >
                </multiselect>
                <div
                  v-for="(pokemon, index) in boss.pokemon['step' + step.id]"
                  class="setting pokemon"
                >
                  <img :src="pokemon.thumbnail_url" />
                  <p>{{ pokemon.name_fr }}</p>
                  <v-btn
                    flat
                    icon
                    color="deep-orange"
                    @click="removePokemonFromStep(index, step.id)"
                  >
                    <v-icon>close</v-icon>
                  </v-btn>
                </div>
              </div>
              <v-btn dark fixed bottom right fab @click="submit()">
                <v-progress-circular
                  v-if="loading"
                  indeterminate
                  color="primary"
                ></v-progress-circular>
                <v-icon v-else>save</v-icon>
              </v-btn>
            </v-card-text>
          </v-card>
        </v-tab-item>
      </v-tabs>
    </div>
  </div>
</template>

<script>
import Multiselect from "vue-multiselect";
export default {
  name: "AdminRocketBosses",
  components: { Multiselect },
  data() {
    return {
      loading: false,
      active: null,
      pokemons: [],
      bosses: [],
      value: null,
      boss: false,
      steps: [
        {
          id: 1,
          name: "Premier Pokemon",
        },
        {
          id: 2,
          name: "Deuxième Pokemon",
        },
        {
          id: 3,
          name: "Troisième Pokemon",
        },
      ],
    };
  },
  created() {
    this.fetchPokemons();
    this.fetchBosses();
  },
  methods: {
    toto(tab) {
      console.log(this.active);
    },
    fetchBosses() {
      axios.get("/api/rocket/bosses").then((res) => {
        this.bosses = res.data;
        this.boss = this.bosses[0];
        let message = "Problème lors de la récupération";
        if (err.response.data) {
          message = err.response.data;
        }
        this.$store.commit("setSnackbar", {
          message: message,
          timeout: 1500,
        });
      });
    },
    fetchPokemons() {
      axios
        .get("/api/user/pokemon")
        .then((res) => {
          this.pokemons = res.data;
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
        });
    },
    addPokemonToStep(selectedOption, stepId) {
      if (
        this.boss.pokemon["step" + stepId].filter(
          (pokemon) => pokemon.id == selectedOption.id
        ).length > 0
      )
        return;
      this.boss.pokemon["step" + stepId].push(selectedOption);
    },
    removePokemonFromStep(index, stepId) {
      this.boss.pokemon["step" + stepId].splice(index, 1);
    },
    submit() {
      const args = {
        pokemon: this.boss.pokemon,
      };
      this.save(args);
    },
    save(args) {
      this.$store.commit("setSnackbar", { message: "Enregistrement en cours" });
      this.loading = true;
      axios
        .put("/api/rocket/bosses/" + this.boss.id, args)
        .then((res) => {
          this.$store.commit("fetchPokemon");
          this.$store.commit("setSnackbar", {
            message: "Pokémon de " + this.boss.name + " mis à jour",
            timeout: 1500,
          });
          this.loading = false;
        })
        .catch((err) => {
          let message = "Problème lors de la mise à jour";
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
  },
};
</script>
