<template>
    <div>
        <vue-google-autocomplete
            :id="id"
            classname="form-control"
            :placeholder="placeholder"
            v-on:placechanged="updateCoordinatesFromSearch"
        >
        </vue-google-autocomplete>
        <l-map style="height: 200px; width: 100%" ref="map" :zoom=15 :center="[coordinates.lat, coordinates.lng]" v-on:click="updateCoordinatesFromClick">
            <l-tile-layer :url="url"></l-tile-layer>
            <l-marker :lat-lng="[coordinates.lat, coordinates.lng]" :draggable="true" @update:latLng="updateCoordinatesFromDrag"></l-marker>
        </l-map>
        <input v-model="coordinates.lat" type="text" disabled>
        <input v-model="coordinates.lng" type="text" disabled>
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
        mounted: function() {
            this.$emit('input', this.value)
            this.$nextTick(() => {
              this.map = this.$refs.map.mapObject // work as expected
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
                this.$emit('input', this.coordinates)
            }
        }
    }
</script>
