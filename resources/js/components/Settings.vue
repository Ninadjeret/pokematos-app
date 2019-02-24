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
    <div class="settings-section map">
        <div class="section__title">Map</div>
            <div class="settings-section__wrapper">
                        <div class="setting__wrapper toggle">
                <div class="setting_label">
                    <p class="setting__titre">Masquer les arênes vides</p>
                    <p class="setting__desc">N'afficher sur la carte que les arênes avec un raid en cours ou à venir</p>
                </div>
                <div class="setting__value">
                    <v-switch v-model="settingsHideGyms"></v-switch>
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
                <a href="https://www.profchen.fr/loading/" class="setting-link">Re-télécharger les données</a>
            </div>
        </div>
    </div>
    <div class="settings-section about">
        <div class="section__title">A propos</div>
        <p class="credit">
            Prof Chen map, créé pour vous avec <i class="material-icons">favorite</i><br>
            Version <span id="version">1.4.12</span>
        </p>
    </div>
</div>
</template>

<script>
    export default {
        name: 'Settings',
        data() {
            return {
            }
        },
        computed: {
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
