<template>
    <div>
        <div v-if="loading" class="loading">
            <div class="loading__content">
                <i class="friendball"></i>
                <p>Chargement...</p>
            </div>
        </div>

        <div v-if="!loading" class="" >
            <div class="settings-section">     
                <v-subheader>Apparence</v-subheader>
                <div class="setting">
                    <label>Mode clair/sombre</label>
                    <select v-model="appearandeMod">
                        <option v-for="choice in pref_appearance_mod_choices" :value="choice.id">{{choice.name}}</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import moment from 'moment';
    import { mapState } from 'vuex'
    export default {
        name: 'ProfilePreferences',
        data() {
            return {
                loading: true,
                pref_appearance_mod_choices: [
                    {
                        id: 'system',
                        name: 'Reprendre le mode du téléphone'
                    },
                    {
                        id: 'light',
                        name: 'Mode clair'
                    },
                    {
                        id: 'dark',
                        name: 'Mode sombre'
                    }
                ]
            }
        },
        created() {
            this.loading = false;
        },
        computed: {
            appearandeMod: {
                get: function () {
                    return this.$store.getters.getSetting("appaeranceMod");
                },
                set: function (newValue) {
                    this.$store.commit("setSetting", {
                        setting: "appaeranceMod",
                        value: newValue,
                    });
                    this.$store.commit("setSnackbar", {
                        message: "Préférences mises à jour",
                        timeout: 1500
                    });
                    this.updateMetaColor();
                },
            },
        },
        methods: {
            updateMetaColor() {
                let appearanceMod = this.$store.getters.getSetting("appaeranceMod")
                let darkMod =  window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches
                let themeColor = appearanceMod == 'dark' || darkMod ? '#333333' : '#ffffff' 
                document.getElementById('theme-color').setAttribute("content", themeColor)
            }
        }
    }
</script>
