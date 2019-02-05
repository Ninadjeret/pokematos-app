<template>
<div id="toto">
    <app-header v-bind:page-title="pageTitle" v-bind:current-city="currentCity" v-bind:cities="cities"></app-header>
    <app-nav></app-nav>
    <raidslist v-bind:raids="raids"></raidslist>
</div>
</template>

<script>
    export default {
        props: ['pageTitle'],
        data() {
            return {
                raids: JSON.parse( localStorage.getItem('pokematos_raids')),
                currentCity: JSON.parse( localStorage.getItem('pokematos_currentCity')),
                cities: JSON.parse(localStorage.getItem('pokematos_cities')),
            }
        },
        mounted() {
            console.log(this.pageTitle),
            this.getCities();
            this.getRaids();
        },
        methods: {
            getCities() {
                axios.get('/api/user/cities').then(res => {
                    this.cities = res.data
                    //console.log(res.data)
                    localStorage.setItem('pokematos_cities', JSON.stringify(res.data));
                    this.setDefaultCity();
                }).catch(err => {
                    //No error
                });
            },
            setDefaultCity() {
                var city = JSON.parse(localStorage.getItem('pokematos_currentCity'))
                if (!city) {
                    this.currentCity = this.cities[0];
                    localStorage.setItem('pokematos_currentCity', JSON.stringify(this.currentCity));
                }
            },
            getRaids() {
                axios.get('/api/user/cities/'+this.currentCity.id+'/raids').then( res => {
                    this.raids = res.data
                    console.log(res.data)
                    localStorage.setItem('pokematos_raids', JSON.stringify(res.data));
                }).catch( err => {
                    //No error
                });
            }
        }
    }
</script>
