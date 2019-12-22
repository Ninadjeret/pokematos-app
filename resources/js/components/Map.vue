<template>
    <div style="height: 100%;">
        <l-map
            style="height: 100%; width: 100%"
            ref="map"
            @update:bounds="addMarkers"
            :zoom=13>
            <l-tile-layer :url="url"></l-tile-layer>
        </l-map>
        <button-actions @localize="localize()" @showfilters="dialog = true"></button-actions>
        <gym-modal ref="gymModal"></gym-modal>

        <v-dialog v-model="dialog" max-width="290" content-class="list-filters">
            <v-card>
                <v-subheader>Afficher seulement</v-subheader>
                <v-card-text>
                    <v-checkbox v-model="mapFilters" label="Arènes vierges" value="empty_gyms" @change="addMarkers()"></v-checkbox>
                    <v-checkbox v-model="mapFilters" label="Raids en cours/à venir" value="active_gyms" @change="addMarkers()"></v-checkbox>
                    <v-checkbox v-model="mapFilters" label="Pokéstop vierges" value="empty_stops" @change="addMarkers()"></v-checkbox>
                    <v-checkbox v-model="mapFilters" label="Pokéstops avec quête" value="active_stops" @change="addMarkers()"></v-checkbox>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="primary" flat @click="dialog = false">Fermer</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

    </div>
</template>

<script>
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
              dialog:false,
              markersLayer: [],
            }
        },
        computed: {
            mapFilters: {
                get: function () {
                    let filters = this.$store.getters.getSetting('mapFilters');
                    if( !filters || typeof filters == 'string' ) {
                        return [];
                    }
                    return filters;
                },
                set: function (newValue) {
                    this.$store.commit('setSetting', {
                        setting: 'mapFilters',
                        value: newValue
                    });
                }
            },
            gyms() {
                return this.$store.state.gyms
            },
            currentCity() {
                return this.$store.state.currentCity
            }
        },
        watch: {
            gyms: function () {
                this.addMarkers()
            }
        },
        mounted() {
            this.$nextTick(() => {
              this.map = this.$refs.map.mapObject // work as expected
              this.addMarkers();

          });
          this.localize();
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
                let zoom = this.map.getZoom();
                let mapBounds = this.map.getBounds();
                let limit = 100;
                let count = 0;
                if( this.gyms && this.gyms.length > 0 ) {
                    this.gyms.forEach(function(gym) {
                        if( gym.gym ) {
                            that.addMarker(gym);
                        }
                        if( count <= limit && zoom >= 15 && !gym.gym && mapBounds.contains([gym.lat, gym.lng]) ) {
                            count++;
                            that.addMarker(gym);
                        }
                    });
                }
                if( count >= limit ) {
                    this.$store.commit('setSnackbar', {
                        message: 'Zoomez plus pour voir plus de pokéstops',
                        timeout: 1500
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

                let auth = true;
                let filters = this.$store.getters.getSetting('mapFilters');
                if( filters && filters.length > 0 ) {
                    auth = false;
                    if( filters.includes( 'empty_gyms' ) && gym.gym && !gym.raid ) {
                        auth = true;
                    }
                    if( filters.includes( 'active_gyms' ) && gym.gym && gym.raid ) {
                        auth = true;
                    }
                    if( filters.includes( 'empty_stops' ) && !gym.gym && !gym.quest ) {
                        auth = true;
                    }
                    if( filters.includes( 'active_stops' ) && !gym.gym && gym.quest ) {
                        auth = true;
                    }
                }

                if( !auth ) {
                    return false;
                }

                const that2 = this;
                var zindex = (gym.gym) ? 500 : 1;
                var label = false;
                var url = (gym.gym) ? 'https://assets.profchen.fr/img/map/map_marker_default_01.png' : 'https://assets.profchen.fr/img/map/map_marker_stop.png' ;
                var imgclassname = 'map-marker__img';
                if(gym.ex) {
                    zindex = 1000;
                    url = 'https://assets.profchen.fr/img/map/map_marker_default_ex_03.png';
                }

                if( gym.quest ) {
                    zindex = 2000;
                    if( gym.quest.reward_type && gym.quest.reward_type == 'pokemon' ) {
                        var imgclassname = 'map-marker__img quest';
                        url = 'https://assets.profchen.fr/img/map/map_marker_quest_pokemon_'+gym.quest.reward.pokedex_id+'_'+gym.quest.reward.form_id+'.png';
                    }
                    else if( gym.quest.reward_type && gym.quest.reward_type == 'reward' ) {
                        var imgclassname = 'map-marker__img quest';
                        url = 'https://assets.profchen.fr/img/map/map_marker_quest_reward_'+gym.quest.reward.id+'.png';
                    } else {
                        var imgclassname = 'map-marker__img quest';
                        url = 'https://assets.profchen.fr/img/map/map_marker_quest_unknown.png';
                    }
                }

                var html = '<img class="'+imgclassname+'" src="'+url+'"/>';

                if( gym.raid !== false ) {

                    var now = moment();
                    var raidStartTime = moment(gym.raid.start_time, '"YYYY-MM-DD HH:mm:ss"');
                    var raidEndTime = moment(gym.raid.end_time, '"YYYY-MM-DD HH:mm:ss"');

                    //raid actifs
                    if( now.isAfter(gym.raid.start_time) && now.isBefore(gym.raid.end_time) ) {
                        imgclassname = imgclassname + ' raid';
                        label = raidEndTime.diff(now, 'minutes') + ' min';
                        url = 'https://assets.profchen.fr/img/map/map_marker_active_'+gym.raid.egg_level+'.png';
                        if( gym.raid.pokemon != false ) {
                            if( gym.raid.pokemon.form_id == '00' ) {
                                url = 'https://assets.profchen.fr/img/map/map_marker_pokemon_'+gym.raid.pokemon.pokedex_id+'.png';
                            } else {
                                url = 'https://assets.profchen.fr/img/map/map_marker_pokemon_'+gym.raid.pokemon.pokedex_id+'_'+gym.raid.pokemon.form_id+'.png';
                            }
                        }
                        var html = '<img class="'+imgclassname+'" src="'+url+'"/>' + '<span class="map-marker__label">'+label+'</span>'
                        zindex = gym.raid.egg_level * 2000;

                    //Raids à venir
                    } else if( now.isBefore(gym.raid.start_time) ) {
                        imgclassname = imgclassname + ' raid';
                        if( gym.raid.ex ) {
                            if( raidStartTime.diff(now, 'days') >= 1 ) {
                                label = raidStartTime.diff(now, 'days') + ' jours';
                            } else if( raidStartTime.diff(now, 'hours') >= 1 ) {
                                label = raidStartTime.diff(now, 'hours') + 'h';
                            } else {
                                label = raidStartTime.diff(now, 'minutes') + ' min'
                            }
                            url = 'https://assets.profchen.fr/img/map/map_marker_future_'+gym.raid.egg_level+'.png';
                        } else {
                            label = raidStartTime.diff(now, 'minutes') + ' min';
                            url = 'https://assets.profchen.fr/img/map/map_marker_future_'+gym.raid.egg_level+'.png';
                        }
                        var html = '<img class="'+imgclassname+'" src="'+url+'"/>' + '<span class="map-marker__label">'+label+'</span>'
                        zindex = gym.raid.egg_level * 2000;
                    }

                }

                if( gym.lat == null || gym.lng == null ) {
                    return false;
                }


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
            },
            localize() {
                const that = this;
                that.$refs.map.mapObject.panTo(new L.LatLng(this.currentCity.lat, this.currentCity.lng));
                if (navigator.geolocation) {
                    console.log('atata');
                    navigator.geolocation.getCurrentPosition(function (position) {
                        that.$refs.map.mapObject.panTo(new L.LatLng(position.coords.latitude, position.coords.longitude));
                    });
                }
            },
        }
    }
</script>
