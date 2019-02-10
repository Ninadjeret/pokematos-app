<template>
<div id="app__container">
    <app-header
        v-bind:page-title="pageTitle"
        v-bind:current-city="currentCity"
        v-bind:cities="cities"
        v-bind:links="links">
    </app-header>
    <app-nav></app-nav>
    <raidsmap
        v-if="getCurrentLink().id == 'map'"
        v-bind:gyms="gyms">
    </raidsmap>
    <raidslist
        v-if="getCurrentLink().id == 'list'"
        v-bind:gyms="gyms">
    </raidslist>
    <settings
        v-if="getCurrentLink().id == 'settings'"
        v-bind:user="user">
    </settings>
</div>
</template>

<script>
    export default {
        props: ['pageTitle'],
        data() {
            return {
                links: [{
                        id: 'map',
                        text: 'Map',
                        url: '/',
                        icon: 'map'
                    },
                    {
                        id: 'list',
                        text: 'Liste',
                        url: '/list',
                        icon: 'notifications_active'
                    },
                    {
                        id: 'settings',
                        text: 'Réglages',
                        url: '/settings',
                        icon: 'settings'
                    },
                ],
                gyms: JSON.parse( localStorage.getItem('pokematos_gyms')),
                currentCity: JSON.parse( localStorage.getItem('pokematos_currentCity')),
                cities: JSON.parse(localStorage.getItem('pokematos_cities')),
                user: JSON.parse(localStorage.getItem('pokematos_user')),
            }
        },
        mounted() {
            console.log(this.pageTitle),
            this.loadData(),
            this.$on('refreshdata', function() {
                console.log('refresh-data')
            })
        },
        methods: {
            test() {
                console.log('refresh-data')
            },
            loadData() {
                axios.get('/api/user/cities').then(res => {
                    this.cities = res.data
                    //console.log(res.data)
                    localStorage.setItem('pokematos_cities', JSON.stringify(res.data));
                    this.setDefaultCity();
                    this.getRaids();
                    this.getUser();
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
                axios.get('/api/user/cities/'+this.currentCity.id+'/gyms').then( res => {
                    this.gyms = res.data
                    console.log(res.data)
                    localStorage.setItem('pokematos_gyms', JSON.stringify(res.data));
                }).catch( err => {
                    //No error
                });
            },
            getUser() {
                axios.get('/api/user').then( res => {
                    this.user = res.data
                    localStorage.setItem('pokematos_user', JSON.stringify(res.data));
                }).catch( err => {
                    //No error
                });
            },
            getCurrentLink() {
                var currentLocation = window.location.pathname;
                var current = false;
                this.links.forEach(function(link) {
                    if( link.url == currentLocation ) {
                        current = link;
                    }
                });
                console.log(current);
                return current;
            },
        }
    }
</script>
