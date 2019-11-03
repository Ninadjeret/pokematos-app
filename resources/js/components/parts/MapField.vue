<template>
    <div>
        <vue-google-autocomplete
            :id="id"
            classname="form-control"
            :placeholder="placeholder"
            v-on:placechanged="updateCoordinatesFromSearch"
            country="FR"
            types=""
        >
        </vue-google-autocomplete>
        <l-map style="height: 200px; width: 100%" ref="map" :zoom=15 v-on:click="updateCoordinatesFromClick">
            <l-tile-layer :url="url"></l-tile-layer>
            <l-marker :lat-lng="[coordinates.lat, coordinates.lng]" :draggable="true" @update:latLng="updateCoordinatesFromDrag"></l-marker>
        </l-map>
        <input v-model="coords" type="text">
    </div>
</template>

<script>
    import VueGoogleAutocomplete from 'vue-google-autocomplete'
    export default {
        name: 'MapField',
        components: { VueGoogleAutocomplete },
        props: {
            value: {},
            id: {
                type: String,
                required: true
              },
            placeholder: {
                type: String,
                default: 'Saisissez une adresse'
            },
        },

        data() {
            return {
                coordinates:this.value,
                map: null,
                url: 'https://api.tiles.mapbox.com/v4/mapbox.streets/{z}/{x}/{y}.png?access_token=pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpejY4NXVycTA2emYycXBndHRqcmZ3N3gifQ.rJcFIG214AriISLbB6B5aw',
            }
        },
        computed: {
            coords: {
                get: function () {
                    return this.coordinates.lat+','+this.coordinates.lng;
                },
                set: function (newValue) {
                    if( newValue === undefined || newValue == '' || !newValue ) {
                        return
                    }
                    let coordinates = newValue.split(',')
                    console.log(coordinates.length)
                    if( coordinates.length === 2 && coordinates[0].includes('.') && coordinates[1].includes('.') ) {
                        this.updateCoordinates(coordinates[0], coordinates[1])
                    }
                }
            }
        },
        mounted: function() {
            this.$emit('input', this.value)
            this.$nextTick(() => {
              this.map = this.$refs.map.mapObject // work as expected
              if( this.coordinates.lng && this.coordinates.lat ) {
                  this.$refs.map.mapObject.panTo(new L.LatLng(this.coordinates.lat, this.coordinates.lng));
              }
            })
        },
        methods: {
            updateCoordinatesFromClick(event) {
                this.updateCoordinates(event.latlng.lat, event.latlng.lng)
            },
            updateCoordinatesFromDrag(latlng) {
                this.updateCoordinates(latlng.lat, latlng.lng)
            },
            updateCoordinatesFromSearch: function (addressData, placeResultData, id) {
                this.updateCoordinates(addressData.latitude, addressData.longitude)
                this.$refs.map.mapObject.panTo(new L.LatLng(addressData.latitude, addressData.longitude));
            },
            updateCoordinates( lat, lng ) {
                this.coordinates.lat = lat
                this.coordinates.lng = lng
                if( this.coordinates.lng && this.coordinates.lat ) {
                    this.$refs.map.mapObject.panTo(new L.LatLng(this.coordinates.lat, this.coordinates.lng));
                }
                this.$emit('input', this.coordinates)
            }
        }
    }
</script>
