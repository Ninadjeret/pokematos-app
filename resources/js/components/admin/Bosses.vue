<template>
    <div class="mb-60">
        <div class="settings-section">
            <v-subheader>1 Tête</v-subheader>
            <multiselect
                :reset-after="true"
                v-model="value"
                :options="pokemons"
                track-by="name_fr"
                label="name_fr"
                placeholder="Ajouter un Pokémon"
                @select="addBossTo1t">
                <template slot="singleLabel" slot-scope="{ option }"><strong>{{ option.name_fr }}</strong></template>
            </multiselect>
            <div v-for="(boss, index) in bosses1t" class="setting pokemon">
                <img :src="boss.thumbnail_url">
                <p>{{boss.name_fr}}</p>
                <v-btn flat icon color="deep-orange" @click="remove1t(index)">
                    <v-icon>close</v-icon>
                </v-btn>
            </div>
        </div>

        <div class="settings-section">
            <v-subheader>2 Têtes</v-subheader>
            <multiselect
                :reset-after="true"
                v-model="value"
                :options="pokemons"
                track-by="name_fr"
                label="name_fr"
                placeholder="Ajouter un Pokémon"
                @select="addBossTo2t">
                <template slot="singleLabel" slot-scope="{ option }"><strong>{{ option.name_fr }}</strong></template>
            </multiselect>
            <div v-for="(boss, index) in bosses2t" class="setting pokemon">
                <img :src="boss.thumbnail_url">
                <p>{{boss.name_fr}}</p>
                <v-btn flat icon color="deep-orange" @click="remove2t(index)">
                    <v-icon>close</v-icon>
                </v-btn>
            </div>
        </div>

        <div class="settings-section">
            <v-subheader>3 Têtes</v-subheader>
            <multiselect
                :reset-after="true"
                v-model="value"
                :options="pokemons"
                track-by="name_fr"
                label="name_fr"
                placeholder="Ajouter un Pokémon"
                @select="addBossTo3t">
                <template slot="singleLabel" slot-scope="{ option }"><strong>{{ option.name_fr }}</strong></template>
            </multiselect>
            <div v-for="(boss, index) in bosses3t" class="setting pokemon">
                <img :src="boss.thumbnail_url">
                <p>{{boss.name_fr}}</p>
                <v-btn flat icon color="deep-orange" @click="remove3t(index)">
                    <v-icon>close</v-icon>
                </v-btn>
            </div>
        </div>

        <div class="settings-section">
            <v-subheader>4 Têtes</v-subheader>
            <multiselect
                :reset-after="true"
                v-model="value"
                :options="pokemons"
                track-by="name_fr"
                label="name_fr"
                placeholder="Ajouter un Pokémon"
                @select="addBossTo4t">
                <template slot="singleLabel" slot-scope="{ option }"><strong>{{ option.name_fr }}</strong></template>
            </multiselect>
            <div v-for="(boss, index) in bosses4t" class="setting pokemon">
                <img :src="boss.thumbnail_url">
                <p>{{boss.name_fr}}</p>
                <v-btn flat icon color="deep-orange" @click="remove4t(index)">
                    <v-icon>close</v-icon>
                </v-btn>
            </div>
        </div>

        <div class="settings-section">
            <v-subheader>5 Têtes</v-subheader>
            <multiselect
                :reset-after="true"
                v-model="value"
                :options="pokemons"
                track-by="name_fr"
                label="name_fr"
                placeholder="Ajouter un Pokémon"
                @select="addBossTo5t">
                <template slot="singleLabel" slot-scope="{ option }"><strong>{{ option.name_fr }}</strong></template>
            </multiselect>
            <div v-for="(boss, index) in bosses5t" class="setting pokemon">
                <img :src="boss.thumbnail_url">
                <p>{{boss.name_fr}}</p>
                <v-btn flat icon color="deep-orange" @click="remove5t(index)">
                    <v-icon>close</v-icon>
                </v-btn>
            </div>
        </div>

        <div class="settings-section">
            <v-subheader>Boss de raid EX</v-subheader>
            <multiselect
                :reset-after="true"
                v-model="value"
                :options="pokemons"
                track-by="name_fr"
                label="name_fr"
                placeholder="Ajouter un Pokémon"
                @select="addBossTo6t">
                <template slot="singleLabel" slot-scope="{ option }"><strong>{{ option.name_fr }}</strong></template>
            </multiselect>
            <div v-for="(boss, index) in bosses6t" class="setting pokemon">
                <img :src="boss.thumbnail_url">
                <p>{{boss.name_fr}}</p>
                <v-btn flat icon color="deep-orange" @click="remove6t(index)">
                    <v-icon>close</v-icon>
                </v-btn>
            </div>
        </div>

        <v-btn dark fixed bottom right fab @click="submit()">
            <v-progress-circular v-if="loading" indeterminate color="primary"></v-progress-circular>
            <v-icon v-else>save</v-icon>
        </v-btn>

    </div>
</template>

<script>
    import Multiselect from 'vue-multiselect'

    export default {
        name: 'AdminAccess',
        components: { Multiselect },
        data() {
            return {
                loading: false,
                value: null,
                bosses1t: [],
                bosses2t: [],
                bosses3t: [],
                bosses4t: [],
                bosses5t: [],
                bosses6t: [],
            }
        },
        created() {
            this.$store.commit('fetchPokemon');
            this.initBosses();
        },
        computed: {
            pokemons() {
                return this.$store.state.pokemons;
            }
        },
        methods: {
            initBosses() {
                this.bosses1t = this.$store.state.pokemons.filter(boss => boss.boss_level == '1');
                this.bosses2t = this.$store.state.pokemons.filter(boss => boss.boss_level == '2');
                this.bosses3t = this.$store.state.pokemons.filter(boss => boss.boss_level == '3');
                this.bosses4t = this.$store.state.pokemons.filter(boss => boss.boss_level == '4');
                this.bosses5t = this.$store.state.pokemons.filter(boss => boss.boss_level == '5');
                this.bosses6t = this.$store.state.pokemons.filter(boss => boss.boss_level == '6');
            },
            addBossTo1t(selectedOption, id) {
                if( this.bosses1t.filter( boss => boss.id == selectedOption.id ).length > 0 ) return;
                this.bosses1t.push(selectedOption);
            },
            remove1t(index) {
                this.bosses1t.splice(index, 1);
            },
            addBossTo2t(selectedOption, id) {
                if( this.bosses2t.filter( boss => boss.id == selectedOption.id ).length > 0 ) return;
                this.bosses2t.push(selectedOption);
            },
            remove2t(index) {
                this.bosses2t.splice(index, 1);
            },
            addBossTo3t(selectedOption, id) {
                if( this.bosses3t.filter( boss => boss.id == selectedOption.id ).length > 0 ) return;
                this.bosses3t.push(selectedOption);
            },
            remove3t(index) {
                this.bosses3t.splice(index, 1);
            },
            addBossTo4t(selectedOption, id) {
                if( this.bosses4t.filter( boss => boss.id == selectedOption.id ).length > 0 ) return;
                this.bosses4t.push(selectedOption);
            },
            remove4t(index) {
                this.bosses4t.splice(index, 1);
            },
            addBossTo5t(selectedOption, id) {
                if( this.bosses5t.filter( boss => boss.id == selectedOption.id ).length > 0 ) return;
                this.bosses5t.push(selectedOption);
            },
            remove5t(index) {
                this.bosses5t.splice(index, 1);
            },
            addBossTo6t(selectedOption, id) {
                if( this.bosses6t.filter( boss => boss.id == selectedOption.id ).length > 0 ) return;
                this.bosses6t.push(selectedOption);
            },
            remove6t(index) {
                this.bosses6t.splice(index, 1);
            },
            submit() {
                const args = {
                    bosses1t: this.bosses1t,
                    bosses2t: this.bosses2t,
                    bosses3t: this.bosses3t,
                    bosses4t: this.bosses4t,
                    bosses5t: this.bosses5t,
                    bosses6t: this.bosses6t,
                };
                this.save(args);
            },
            save( args ) {
                this.$store.commit('setSnackbar', {message: 'Enregistrement en cours'})
                this.loading = true;
                axios.put('/api/pokemons/raidbosses', args).then( res => {
                    this.$store.commit('fetchPokemon')
                    this.$store.commit('setSnackbar', {
                        message: 'Boss mis à jour',
                        timeout: 1500
                    })
                    this.loading = false
                }).catch( err => {
                    let message = 'Problème lors de la mise à jour';
                    if( err.response.data ) {
                        message = err.response.data;
                    }
                    this.$store.commit('setSnackbar', {
                        message: message,
                        timeout: 1500
                    })
                    this.loading = false
                });
            },
        }
    }
</script>

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>
