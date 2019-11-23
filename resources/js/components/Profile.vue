<template>
<div>
    <div v-if="user" class="user__card">
        <div class="user__summary">
            <div class="user__img">
                <img v-if="user.discord_avatar_id" :src="'https://cdn.discordapp.com/avatars/'+user.discord_id+'/'+user.discord_avatar_id+'.png'">
                <img v-else src="https://assets.profchen.fr/img/avatar_default.png">
            </div>
            <div class="user__info">
                <h3>{{user.name}} <small>//via Discord</small></h3>
                <a href="/logout" class="logout">SE DECONNECTER</a>
            </div>
        </div>
        <div class="user__stats">
            <h3>Performances</h3>
            <div class="stats">
                <div class="stat">
                    <span>{{user.stats.total.raidCreate}}</span> Raids annoncés
                </div>
                <div class="stat">
                    <span>{{user.stats.total.raidUpdate}}</span> Raids mis à jour
                </div>
                <div class="stat">
                    <span>{{user.stats.total.questCreate}}</span> quêtes annoncées
                </div>
            </div>
        </div>
    </div>
    <div class="settings-section help">
        <div class="section__title">Assistance</div>
        <div class="settings-section__wrapper">
            <div class="setting__wrapper">
                <a href="https://www.profchen.fr/settings/policy/" class="setting-link">Politique de confidentialité</a>
            </div>
            <div class="setting__wrapper">
                <a href="https://www.pokematos.fr/documentation" class="setting-link">Documentation</a>
            </div>
            <div class="setting__wrapper">
                <span @click="updateData" class="setting-link">Retélécharger les données</span>
            </div>
        </div>
    </div>
    <div class="settings-section about">
        <div class="section__title">Merci pour votre soutien</div>
        <p class="donation">
            Les frais de fonctionnement de Pokématos représentent <strong>{{totalCouts}}€</strong> depuis le 01/01/2019. Grace à vous, nous avons déja récupéré <strong>{{totalDons}}€</strong> !
            <v-progress-linear
                color="#5a6cae"
                height="20"
                :value="pourcentageDons"
            ></v-progress-linear>
            <span class="text-center">
                <v-btn href="https://www.pokematos.fr/don">Faire un don</v-btn>
            </span>
        </p>
    </div>
    <div class="settings-section about">
        <div class="section__title">A propos</div>

        <p class="credit">
            Pokématos, créé pour vous avec <i class="material-icons">favorite</i><br>
            Version <span id="version">{{appVersion}}</span>
        </p>
    </div>

    <v-dialog
        content-class="dialog-update"
        v-model="dialogUpdate"
        persistent
        width="300"
      >
        <v-card color="primary">
          <v-card-text>
            <p>Mise à jour des données<br><small><i>cela peut prendre 1 à 2 min...</i></small></p>
            <v-progress-linear
              indeterminate
              color="#5a6cae"
              class="mb-0"
            ></v-progress-linear>
          </v-card-text>
        </v-card>
      </v-dialog>

</div>
</template>

<script>
    import axios2 from 'axios';
    export default {
        name: 'Profile',
        data() {
            return {
                totalCouts: 317,
                totalDons: 105,
                dialogUpdate: false,
            }
        },
        computed: {
            pourcentageDons() {
                return ( this.totalDons > this.totalCouts ) ? 100 : this.totalDons/this.totalCouts*100 ;
            },
            user() {
                return this.$store.state.user;
            },
            appVersion() {
                return this.$store.getters.getSetting('appVersion');
            }
        },
        created() {
            this.$store.commit('fetchUser');
        },
        methods: {
            async updateData() {
                this.$store.commit('setSetting', {
                    setting: 'lastUpdate',
                    value: '2000-01-01 00:00:00'
                });
                this.dialogUpdate= true;
                try {
                    await this.$store.dispatch('fetchGyms')
                } finally {
                    this.dialogUpdate = false;
                }
            },
        }
    }
</script>
