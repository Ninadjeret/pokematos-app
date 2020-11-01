<template>
  <div>
    <div class="settings-section">
      <v-subheader>Description</v-subheader>
      <div class="setting">
        <label>Nom</label>
        <input v-model="name_fr" type="text" />
      </div>
      <div class="setting">
        <label>Nom pour l'OCR</label>
        <input v-model="name_ocr" type="text" />
      </div>
      <div class="setting">
        <label>Num du Pokédex</label>
        <input v-model="pokedex_id" disabled type="text" />
      </div>
      <div class="setting">
        <label>ID de forme</label>
        <input v-model="form_id" type="text" />
      </div>
      <div class="setting">
        <label>ID Niantic</label>
        <input v-model="niantic_id" disabled type="text" />
      </div>

      <div v-if="this.$route.params.pokemon_id">
        <v-subheader>Autres actions</v-subheader>
        <v-list-tile color="pink" @click="dialog = true"
          >Supprimer ce Pokémon</v-list-tile
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
    </div>
    <v-dialog v-model="dialog" persistent max-width="290">
      <v-card>
        <v-card-title class="headline">Supprimer {{ name_fr }} ?</v-card-title>
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
  name: "AdminApiAcces",
  data() {
    return {
      loading: false,
      dialog: false,
      name_fr: "",
      name_ocr: "",
      pokedex_id: "",
      form_id: "",
      niantic_id: "",
    };
  },
  created() {
    if (this.$route.params.pokemon_id) {
      this.fetch();
    }
  },
  methods: {
    fetch() {
      axios
        .get("/api/user/pokemon/" + this.$route.params.pokemon_id)
        .then((res) => {
          this.name_fr = res.data.name_fr;
          this.name_ocr = res.data.name_ocr;
          this.pokedex_id = res.data.pokedex_id;
          this.form_id = res.data.form_id;
          this.niantic_id = res.data.niantic_id;
        })
        .catch((err) => {
          //No error
        });
    },
    submit() {
      const args = {
        name_fr: this.name_fr,
        name_ocr: this.name_ocr,
        pokedex_id: this.pokedex_id,
        form_id: this.form_id,
        niantic_id: this.niantic_id,
      };
      if (this.$route.params.pokemon_id) {
        this.save(args);
      } else {
        this.create(args);
      }
    },
    save(args) {
      this.$store.commit("setSnackbar", { message: "Enregistrement en cours" });
      this.loading = true;
      axios
        .put("/api/user/pokemon/" + this.$route.params.pokemon_id, args)
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
        .delete("/api/user/pokemon/" + this.$route.params.pokemon_id)
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