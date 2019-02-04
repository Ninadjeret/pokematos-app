<template>
    <div>
        <h2>Testtttttttttt</h2>
        <l-map style="height: 50%; width: 50%" ref="map" :zoom=13 :center="[47.413220, -1.219482]">
            <l-tile-layer :url="url"></l-tile-layer>
        </l-map>
    </div>
</template>

<script>
    export default {
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
        }
    }
</script>
