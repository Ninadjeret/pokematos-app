<template>
<header class="header__wrapper--app">
    <div v-if="getCurrentLink().url == '/'" class="header-title home">
        <img src="https://assets.profchen.fr/img/logo_main_400.png"> POKEMATOS <small v-if="currentCity">{{ currentCity.name }}</small>
        <button v-if="cities && cities.length > 1" v-on:click="showModal()"><i class="material-icons">location_city</i></button>
        <modal v-if="cities" name="cityChoice">
            <h3>Choisis ta zone</h3>
            <ul id="cityChoice">
                <li v-for="city in cities" v-on:click="setCurrentCity(city)">
                    {{ city.name }}
                </li>
            </ul>
            <div class="footer--actions">
                <button class="button--close" v-on:click="hideModal()"><i class="material-icons">close</i></button>
            </div>
        </modal>
    </div>
    <div v-else class="header-title">
        {{getCurrentLink().text}}
    </div>
</header>
</template>

<script>
export default {
    props: ['pageTitle', 'currentCity', 'links'],
    data() {
        return {
            cities: JSON.parse(localStorage.getItem('pokematos_cities')),
        }
    },
    mounted() {
        console.log('Component mounted.')
    },
    created() {
    },
    methods: {
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
        showModal() {
            this.$modal.show('cityChoice');
        },
        hideModal() {
            this.$modal.hide('cityChoice');
        }
    }
}
</script>
