<template>
    <div style="height: 100%;">
        <l-map style="height: 100%; width: 100%" ref="map" :zoom=13 :center="[currentCity.lat, currentCity.lng]">
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
              dialog:false,
              mapFilters: [],
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

                let auth = true;
                if( this.mapFilters.length > 0 ) {
                    auth = false;
                    if( this.mapFilters.includes( 'empty_gyms' ) && gym.gym && !gym.raid ) {
                        auth = true;
                    }
                    if( this.mapFilters.includes( 'active_gyms' ) && gym.gym && gym.raid ) {
                        auth = true;
                    }
                    if( this.mapFilters.includes( 'empty_stops' ) && !gym.gym && !gym.quest ) {
                        auth = true;
                    }
                    if( this.mapFilters.includes( 'active_stops' ) && !gym.gym && gym.quest ) {
                        auth = true;
                    }
                }
                if( !auth ) {
                    return false;
                }

                const that2 = this;
                var zindex = 1;
                var label = false;
                var url = (gym.gym) ? 'https://assets.profchen.fr/img/map/map_marker_default_01.png' : 'https://assets.profchen.fr/img/map/map_marker_stop.png' ;
                var imgclassname = 'map-marker__img';
                if(gym.ex) {
                    url = 'https://assets.profchen.fr/img/map/map_marker_default_ex_03.png';
                }

                if( gym.quest ) {
                    if( gym.quest.quest.pokemon ) {
                        var imgclassname = 'map-marker__img quest';
                        url = 'https://assets.profchen.fr/img/map/map_marker_quest_pokemon_'+gym.quest.quest.pokemon.pokedex_id+'_'+gym.quest.quest.pokemon.form_id+'.png';
                    }
                    if( gym.quest.quest.reward ) {
                        var imgclassname = 'map-marker__img quest';
                        url = 'https://assets.profchen.fr/img/map/map_marker_quest_reward_'+gym.quest.quest.type+'.png';
                    }
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
                            if( gym.raid.pokemon.form_id == '00' ) {
                                url = 'https://assets.profchen.fr/img/map/map_marker_pokemon_'+gym.raid.pokemon.pokedex_id+'.png';
                            } else {
                                url = 'https://assets.profchen.fr/img/map/map_marker_pokemon_'+gym.raid.pokemon.pokedex_id+'_'+gym.raid.pokemon.form_id+'.png';
                            }
                        }

                    //Raids à venir
                    } else {
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
                    }
                    var html = '<img class="'+imgclassname+'" src="'+url+'"/>' + '<span class="map-marker__label">'+label+'</span>'
                    zindex = gym.raid.egg_level * 100;
                }



                if( gym.lat == null || gym.lng == null ) {
                    return false;
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
