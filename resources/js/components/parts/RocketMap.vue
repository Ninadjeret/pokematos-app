<template>
    <div>
        <l-map
            style="height: 100%; width: 100%; z-index: 1000; position: absolute; left:0; top: 0;"
            ref="rocketmap"
            :zoom=13>
            <l-tile-layer :url="urlRocket"></l-tile-layer>
        </l-map>
        <gym-modal ref="gymModal"></gym-modal>
    </div>
</template>

<script>
import { mapState } from 'vuex'
export default {
    name: 'RocketMap',
    data() {
        return {
            map: null,
            urlRocket: 'https://api.mapbox.com/styles/v1/mapbox/dark-v10/tiles/256/{z}/{x}/{y}?access_token=pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpejY4NXVycTA2emYycXBndHRqcmZ3N3gifQ.rJcFIG214AriISLbB6B5aw',
            mode: 'base',
            center: [47.413220, -1.219482],
            bounds: null,
            markers: [],
            dialog:false,
            markersLayer: [],
        }
    },
    computed: {
        gyms() {
            return this.$store.state.gyms
        },
        currentCity() {
            return this.$store.state.currentCity
        },
        baseUrl() {
            return window.pokematos.baseUrl;
        },
    },
    watch: {
        gyms: function () {
            this.addMarkers()
        }
    },
    mounted() {
        this.$nextTick(() => {
          this.map = this.$refs.rocketmap.mapObject // work as expected
          this.addMarkers();

      });
      this.localize();
    },
    methods: {
        addMarkers() {
            const that = this;
            this.deleteMarkers();
            let zoom = this.map.getZoom();
            let mapBounds = this.map.getBounds();
            let limit = 100;
            let count = 0;
            this.gyms.forEach(function(gym) {
                if( gym.invasion ) {
                    that.addMarker(gym);
                }
            });
            //this.displayPlayerOnMap();
        },
        deleteMarkers() {
            const that = this;
            this.markers.forEach(function(marker) {
                that.$refs.rocketmap.mapObject.removeLayer(marker);
            });
        },
        addMarker(stop) {
            let url = baseUrl+'/storage/img/static/map/map_marker_rocket_'+stop.invasion.boss.name+'.png';
            var mapMarker = L.marker([gym.lat, gym.lng], {
                icon: new L.DivIcon({
                    className: 'map-marker__wrapper',
                    html: '<img class="'+imgclassname+'" src="'+url+'"/>',
                    iconAnchor: [17, 35],
                }),
                zIndexOffset: 10,
            }).addTo(this.$refs.map.mapObject).on('click', function(e){
                that2.showModal( e.target.gym );
            });
            mapMarker.gym = gym;
            this.markers.push(mapMarker);
        }
    }
}
</script>
