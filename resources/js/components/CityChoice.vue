<template>
    <div>
        <!--<img src="https://assets.profchen.fr/v2/logo_pokematos.png">-->
        POKEMATOS <small>{{ currentCity.name }}</small>
        <button v-on:click="showModal()"><i class="material-icons">location_city</i></button>
        <modal name="cityChoice">
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
</template>

<script>
    export default {
        data() {
            return {
              cities: JSON.parse( localStorage.getItem('pokematos_cities')),
              currentCity: JSON.parse( localStorage.getItem('pokematos_currentCity'))
            }
        },
        mounted() {
            console.log('Component mounted.')
        },
        created() {
            this.getCities();
            this.setDefaultCity();
        },
        methods: {
            getCities() {
                axios.get('/api/user/cities').then( res => {
                    this.cities = res.data
                    //console.log(res.data)
                    localStorage.setItem('pokematos_cities', JSON.stringify(res.data));
                }).catch( err => {
                    //No error
                });
            },
            setDefaultCity() {
                var city = JSON.parse( localStorage.getItem('pokematos_currentCity'))
                if( !city ) {
                    this.currentCity = this.cities[0];
                    console.log('Ajout de '.this.currentCity );
                    localStorage.setItem('pokematos_currentCity', JSON.stringify(this.currentCity));
                }
            },
            setCurrentCity( city ) {
                console.log(city);
                this.currentCity = city;
                localStorage.setItem('pokematos_currentCity', JSON.stringify(this.currentCity));
                this.hideModal();

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
