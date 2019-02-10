<template>
    <div ref="buttonactions" :class="'map__actions '+menuClass">
        <div class="map__overlay"></div>
        <div class="actions">
            <button v-on:click="clickRefresh()" class="action" id="refresh">
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
        clickRefresh() {
            console.log('test');
            this.$emit('refreshdata');
            this.toggleMenu();
        },
        toggleMenu() {
             this.menuClass = ( this.menuClass == 'open' ) ? '' : 'open' ;
        }
    }
}
</script>
