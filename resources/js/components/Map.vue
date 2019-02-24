<template>
    <div style="height: 100%;">
        <l-map style="height: 100%; width: 100%" ref="map" :zoom=13 :center="[currentCity.lat, currentCity.lng]">
            <l-tile-layer :url="url"></l-tile-layer>
        </l-map>
        <button-actions @localize="localize()"></button-actions>
        <gym-modal ref="gymModal"></gym-modal>
    </div>
</template>

<script>
    import { mapState } from 'vuex'
    import moment from 'moment'
    export default {
        name: 'Map',
        data() {
            return {
              map: null,
              url: 'https://api.tiles.mapbox.com/v4/mapbox.streets/{z}/{x}/{y}.png?access_token=pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpejY4NXVycTA2emYycXBndHRqcmZ3N3gifQ.rJcFIG214AriISLbB6B5aw',
              center: [47.413220, -1.219482],
              bounds: null,
              markers: [],
            }
        },
        computed: mapState([
                'gyms', 'currentCity'
        ]),
        watch: {
            gyms: function updateMarkers() {
                this.addMarkers();
            },
        },
        mounted() {
            this.$nextTick(() => {
              this.map = this.$refs.map.mapObject // work as expected
              this.addMarkers();
            })
        },
        methods: {
            displayPlayerOnMap() {
                const that = this;
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function (position) {
                    var mapMarker = L.marker([position.coords.latitude, position.coords.longitude], {
                            icon: L.icon({
                                iconUrl: 'https://assets.profchen.fr/img/map/map_marker_player.png',
                            }),
                        }).addTo(that.$refs.map.mapObject);
                        that.markers.push(mapMarker);
                    });
                }
            },
            showModal( gym ) {
                this.$refs.gymModal.showModal( gym );
            },
            addMarkers() {
                const that = this;
                this.deleteMarkers();
                if( this.gyms && this.gyms.length > 0 ) {
                    this.gyms.forEach(function(gym) {
                        that.addMarker(gym);
                    });
                }
                this.displayPlayerOnMap();
            },
            deleteMarkers() {
                const that = this;
                this.markers.forEach(function(marker) {
                    that.$refs.map.mapObject.removeLayer(marker);
                });
            },
            addMarker( gym ) {
                const that2 = this;
                var zindex = 1;
                var label = false;
                var url = 'https://assets.profchen.fr/img/map/map_marker_default_01.png';
                var imgclassname = 'map-marker__img';
                if(gym.ex) {
                    url = 'https://assets.profchen.fr/img/map/map_marker_default_ex_00.png';
                }
                var html = '<img class="'+imgclassname+'" src="'+url+'"/>';

                if( gym.raid !== false ) {
                    imgclassname = imgclassname + ' raid';
                    var now = moment();
                    var raidStartTime = moment(gym.raid.start_time, '"YYYY-MM-DD HH:mm:ss"');
                    var raidEndTime = moment(gym.raid.end_time, '"YYYY-MM-DD HH:mm:ss"');

                    //raid actifs
                    if( now.isAfter(gym.raid.start_time) && now.isBefore(gym.raid.end_time) ) {
                        label = raidEndTime.diff(now, 'minutes') + ' min';
                        url = 'https://assets.profchen.fr/img/map/map_marker_active_'+gym.raid.egg_level+'.png';
                        if( gym.raid.pokemon != false ) {
                            url = 'https://assets.profchen.fr/img/map/map_marker_pokemon_'+gym.raid.pokemon.pokedex_id+'.png';
                        }

                    //Raids à venir
                    } else {
                        label = raidStartTime.diff(now, 'minutes') + ' min';
                        url = 'https://assets.profchen.fr/img/map/map_marker_future_'+gym.raid.egg_level+'.png';
                    }
                    var html = '<img class="'+imgclassname+'" src="'+url+'"/>' + '<span class="map-marker__label">'+label+'</span>'
                    zindex = gym.raid.egg_level * 100;
                }

                if( this.$store.getters.getSetting('hideGyms') === false || gym.raid !== false ) {
                    var mapMarker = L.marker([gym.lat, gym.lng], {
                        icon: new L.DivIcon({
                            className: 'map-marker__wrapper',
                            html: html,
                            iconAnchor: [17, 35],
                        }),
                        zIndexOffset: zindex,
                    }).addTo(this.$refs.map.mapObject).on('click', function(e){
                        that2.showModal( e.target.gym );
                    });
                    mapMarker.gym = gym;
                    this.markers.push(mapMarker);
                }

            },
            localize() {
                console.log('localize');
                const that = this;
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function (position) {
                        that.$refs.map.mapObject.panTo(new L.LatLng(position.coords.latitude, position.coords.longitude));
                    });
                }
            },
        }
    }
</script>
