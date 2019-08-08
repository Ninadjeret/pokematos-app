<template>
<div>
    <div class="section__title">Connecté en tant que</div>
    <div class="settings-section__wrapper">
            <div v-if="user" class="setting__wrapper user">
                <div class="user__img">
                    <img v-if="user.discord_avatar_id" :src="'https://cdn.discordapp.com/avatars/'+user.discord_id+'/'+user.discord_avatar_id+'.png'">
                    <img v-else src="https://assets.profchen.fr/img/avatar_default.png">
                </div>
                <div class="user__info">
                    <h3>{{user.name}} <small>//via Discord</small></h3>
                    <a href="/logout" class="logout">SE DECONNECTER</a>
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
        </div>
    </div>
    <div class="settings-section about">
        <div class="section__title">Merci pour votre soutien</div>
        <p class="donation">
            Les frais de fonctionnement de Pokématos représentent pour l'instant <strong>{{totalCouts}}€</strong>. Grace à vous, nous avons déja récupéré <strong>{{totalDons}}€</strong> !
            <v-progress-linear
                color="#5a6cae"
                height="20"
                :value="pourcentageDons"
            ></v-progress-linear>
            <span class="text-center">
                <v-btn href="#">Faire un don</v-btn>
            </span>
        </p>
    </div>
    <div class="settings-section about">
        <div class="section__title">A propos</div>

        <p class="credit">
            Pokématos, créé pour vous avec <i class="material-icons">favorite</i><br>
            Version <span id="version">2.0.0</span>
        </p>
    </div>
</div>
</template>

<script>
    export default {
        name: 'Profile',
        data() {
            return {
                totalCouts: 33,
                totalDons: 5,
            }
        },
        computed: {
            pourcentageDons() {
                return ( this.totalDons > this.totalCouts ) ? 100 : this.totalDons/this.totalCouts*100 ;
            },
            user() {
                return this.$store.state.user;
            },
            settingsHideGyms: {
                get: function () {
                    return this.$store.getters.getSetting('hideGyms')
                },
                set: function (newValue) {
                    this.$store.commit('setSetting', {
                        setting: 'hideGyms',
                        value: newValue
                    });
                }
            }
        }
    }
</script>