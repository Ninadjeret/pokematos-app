<template>
  <div>
    <div class="settings-section">
      <v-subheader>Description</v-subheader>
      <div class="setting">
        <label>Nom</label>
        <input v-model="name" type="text" />
      </div>
      <div class="setting">
        <label>Clé d'API</label>
        <p class="description" v-if="!this.$route.params.api_access_id">
          La clé d'API sera générée automatiquement à l'enregistrement
        </p>
        <input disabled v-model="key" type="text" />
        <div class="text-xs-center">
          <v-btn
            v-if="token_message.length == 0 && this.$route.params.api_access_id"
            @click="updateToken"
            flat
            center
            color="green"
          >
            GÉnÉRER UNE NOUVELLE CLÉ<v-icon>cached</v-icon>
          </v-btn>
          <v-alert v-if="token_message.length > 0" :value="true" type="warning">
            {{ token_message }}
          </v-alert>
          <p>L'API est disponible à {{ ApiUrl }}</p>
        </div>
      </div>
      <v-subheader>Autorisations</v-subheader>
      <div class="setting checkbox">
        <div>
          <label>Autorisations accordées</label>
          <p class="description">
            Cochez les droits que vous souhaitez donner pour l'utilisation de
            cet accès à l'API
          </p>
        </div>
        <v-checkbox
          v-for="autorisation in autorisations"
          v-model="authorizations"
          :key="autorisation.value"
          :value="autorisation.value"
        >
          <template v-slot:label
            ><span :class="autorisation.type">{{ autorisation.type }}</span
            >{{ autorisation.label }}</template
          >
        </v-checkbox>
      </div>
      <div v-if="this.$route.params.api_access_id">
        <v-subheader>Autres actions</v-subheader>
        <v-list-tile color="pink" @click="dialog = true"
          >Supprimer cet accès API</v-list-tile
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
  name: "AdminApiAcces",
  data() {
    return {
      loading: false,
      dialog: false,
      name: "",
      key: "",
      authorizations: [],
      autorisations: [
        {
          value: "stops.get",
          type: "GET",
          label: "/stops",
        },
        {
          value: "raids.get",
          type: "GET",
          label: "/raids",
        },
        {
          value: "raids.post",
          type: "POST",
          label: "/raids",
        },
        {
          value: "rankings.get",
          type: "GET",
          label: "/rankings",
        },
      ],
      token_message: "",
    };
  },
  computed: {
    baseUrl() {
      return window.pokematos.baseUrl;
    },
    ApiUrl() {
      return this.baseUrl+"/api/ext/v1/";
    },
  },
  created() {
    if (this.$route.params.api_access_id) {
      this.fetch();
    }
  },
  methods: {
    fetch() {
      axios
        .get(
          "/api/user/guilds/" +
            this.$route.params.id +
            "/api_access/" +
            this.$route.params.api_access_id
        )
        .then((res) => {
          this.name = res.data.name;
          this.key = res.data.key;
          this.authorizations = res.data.authorizations;
        })
        .catch((err) => {
          //No error
        });
    },
    submit() {
      const args = {
        name: this.name,
        key: this.key,
        authorizations: this.authorizations,
      };
      if (this.$route.params.api_access_id) {
        this.save(args);
      } else {
        this.create(args);
      }
    },
    save(args) {
      this.$store.commit("setSnackbar", { message: "Enregistrement en cours" });
      this.loading = true;
      axios
        .put(
          "/api/user/guilds/" +
            this.$route.params.id +
            "/api_access/" +
            this.$route.params.api_access_id,
          args
        )
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
    create(args) {
      this.$store.commit("setSnackbar", { message: "Enregistrement en cours" });
      this.loading = true;
      axios
        .post("/api/user/guilds/" + this.$route.params.id + "/api_access", args)
        .then((res) => {
          this.$store.commit("setSnackbar", {
            message: "Enregistrement effectué",
            timeout: 1500,
          });
          this.loading = false;
          this.$router.push({ name: this.$route.meta.parent });
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
        .delete(
          "/api/user/guilds/" +
            this.$route.params.id +
            "/api_access/" +
            this.$route.params.api_access_id
        )
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
    updateToken() {
      var confirm = window.confirm(
        "Voulez-vous mettre à jour la clé d'API ? Une fois modifiée, vous devrez la mettre à jour sur les différentes serices qui l'utilisaient."
      );
      if (confirm) {
        axios
          .put(
            "/api/user/guilds/" +
              this.$route.params.id +
              "/api_access/" +
              this.$route.params.api_access_id +
              "/token"
          )
          .then((res) => {
            this.key = res.data.key;
            this.token_message =
              "La clé d'accès API a été mise à jour. Si des applications ou services utilisent l'ancienne clé, mettez-les à jour avec la nouvelle.";
          });
      }
    },
  },
};
</script>