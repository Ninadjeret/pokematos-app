<template>
    <div ref="buttonactions" :class="'map__actions '+menuClass">
        <div class="map__overlay"></div>
        <div class="actions">
            <button v-if="this.$route.name == 'map'" v-on:click="localise()" class="action" id="findme">
                <span>Localiser</span><i class="material-icons">gps_fixed</i>
            </button>
            <button v-if="this.$route.name == 'list' || this.$route.name == 'map'" v-on:click="showFilters()" class="action" id="showfilters">
                <span>Filtrer</span><i class="material-icons">filter_list</i>
            </button>
            <button v-on:click="refresh()" class="action" id="refresh">
                <span>Actualiser</span>
                <div class="mdl-spinner mdl-spinner--single-color mdl-js-spinner is-active"></div>
                <i class="material-icons">refresh</i>
            </button>
        </div>
        <div class="launcher">
            <button id="launcher" v-on:click="toggleMenu()">
                <i class="material-icons menu-on">menu</i>
                <i class="material-icons menu-off">close</i>
            </button>
        </div>
    </div>
</template>

<script>
import moment from 'moment';
export default {
    data() {
        return {
            'menuClass': ''
        }
    },
    mounted() {
        console.log('Component mounted.')
    },
    created() {
    },
    methods: {
        refresh() {
            this.$store.dispatch('fetchData');
            this.toggleMenu();
        },
        localise() {
            this.$emit('localize');
            this.toggleMenu();
        },
        showFilters() {
            this.$emit('showfilters');
            this.toggleMenu();
        },
        toggleMenu() {
             this.menuClass = ( this.menuClass == 'open' ) ? '' : 'open' ;
        }
    }
}
</script>
