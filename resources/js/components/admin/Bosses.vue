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
                pokemons: [],
                bosses1t: [],
                bosses2t: [],
                bosses3t: [],
                bosses4t: [],
                bosses5t: [],
            }
        },
        created() {
            this.initBosses();
            this.fetch();
        },
        methods: {
            initBosses() {
                this.bosses1t = this.$store.state.pokemons.filter(boss => boss.boss_level == '1');
                this.bosses2t = this.$store.state.pokemons.filter(boss => boss.boss_level == '2');
                this.bosses3t = this.$store.state.pokemons.filter(boss => boss.boss_level == '3');
                this.bosses4t = this.$store.state.pokemons.filter(boss => boss.boss_level == '4');
                this.bosses5t = this.$store.state.pokemons.filter(boss => boss.boss_level == '5');
            },
            fetch() {
                axios.get('/api/pokemons').then( res => {
                    this.pokemons = res.data;
                }).catch( err => {
                    //No error
                });
            },
            addBossTo1t(selectedOption, id) {
                console.log(selectedOption);
                if( this.bosses1t.filter( boss => boss.id == selectedOption.id ).length > 0 ) return;
                this.bosses1t.push(selectedOption);
            },
            remove1t(index) {
                console.log('toto');
                console.log(index);
                this.bosses1t.splice(index, 1);
            },
            addBossTo2t(selectedOption, id) {
                console.log(selectedOption);
                this.bosses2t.push(selectedOption);
            },
            remove2t(index) {
                console.log('toto');
                console.log(index);
                this.bosses2t.splice(index, 1);
            },
            addBossTo3t(selectedOption, id) {
                console.log(selectedOption);
                this.bosses3t.push(selectedOption);
            },
            remove3t(index) {
                console.log('toto');
                console.log(index);
                this.bosses3t.splice(index, 1);
            },
            addBossTo4t(selectedOption, id) {
                console.log(selectedOption);
                this.bosses4t.push(selectedOption);
            },
            remove4t(index) {
                console.log('toto');
                console.log(index);
                this.bosses4t.splice(index, 1);
            },
            addBossTo5t(selectedOption, id) {
                console.log(selectedOption);
                this.bosses5t.push(selectedOption);
            },
            remove5t(index) {
                console.log('toto');
                console.log(index);
                this.bosses5t.splice(index, 1);
            },
            submit() {
                const args = {
                    bosses1t: this.bosses1t,
                    bosses2t: this.bosses2t,
                    bosses3t: this.bosses3t,
                    bosses4t: this.bosses4t,
                    bosses5t: this.bosses5t,
                };
                this.save(args);
            },
            save( args ) {
                this.$store.commit('setSnackbar', {message: 'Enregistrement en cours'})
                this.loading = true;
                axios.put('/api/pokemons/raidbosses', args).then( res => {
                    console.log(res.data);
                    this.$store.commit('setSnackbar', {
                        message: 'Boss mis à jour',
                        timeout: 1500
                    })
                    this.$store.commit('setPokemons', res.data)
                    this.loading = false
                }).catch( err => {
                    this.$store.commit('setSnackbar', {
                        message: 'Problème lors de l\'enregistrement',
                        timeout: 1500
                    })
                    this.loading = false
                });
            },
        }
    }
</script>

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>
