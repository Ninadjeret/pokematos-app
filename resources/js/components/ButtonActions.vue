<template>
    <div ref="buttonactions" :class="'map__actions '+menuClass">
        <div class="map__overlay"></div>
        <div class="actions">
            <button v-on:click="localise()" class="action" id="findme">
                <span>Localiser</span><i class="material-icons">gps_fixed</i>
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
            console.log('test');
            this.$emit('refresh-data');
            this.toggleMenu();
        },
        localise() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (position) {
                    this.$refs.map.mapObject.panTo(new L.LatLng(position.coords.latitude, position.coords.longitude));
                });
            }
        },
        toggleMenu() {
             this.menuClass = ( this.menuClass == 'open' ) ? '' : 'open' ;
        }
    }
}
</script>
