<template>
    <div style="height: 100%;">
        <l-map style="height: 100%; width: 100%" ref="map" :zoom=13 :center="[47.413220, -1.219482]">
            <l-tile-layer :url="url"></l-tile-layer>
            <l-marker v-for="gym in gyms"
                :lat-lng="[47.413220, -1.219482]"
                :key="gym.name"
                v-on:click="showModal(gym)">
                <l-icon class="someCustomClasses" :icon-anchor="[0, 134]">
                    <h1>Headline</h1>
                    <p>And this is some text</p>
                </l-icon>
            </l-marker>
        </l-map>
        <gym-modal ref="gymModal"></gym-modal>
    </div>
</template>

<script>
    export default {
        props: ['gyms'],
        data() {
            return {
              map: null,
              url: 'https://api.tiles.mapbox.com/v4/mapbox.streets/{z}/{x}/{y}.png?access_token=pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpejY4NXVycTA2emYycXBndHRqcmZ3N3gifQ.rJcFIG214AriISLbB6B5aw',
              zoom: 3,
              center: [47.413220, -1.219482],
              bounds: null
            }
        },
        mounted() {
            console.log('Component mounted.'),
            this.$nextTick(() => {
              this.map = this.$refs.map.mapObject // work as expected
            })
        },
        methods: {
            showModal( gym ) {
                this.$refs.gymModal.showModal( gym );
            },
            getMarker( gym ) {

                var html = '<img class="map-marker__wrapper" src="https://assets.profchen.fr/img/map/map_marker_default.png"/>' + '<span class="map-marker__label">Toto</span>';

                return L.icon({
                    iconUrl: 'https://assets.profchen.fr/img/map/map_marker_default.png',
                    className: 'map-marker__wrapper',
                    html: html,
                    iconAnchor: [17, 35],
                })
            }
        }
    }
</script>
