<template>
    <div>
        <div class="raids__active">
            <div class="section__title">Raids en cours</div>
            <div class="raids__wrapper">
                <div v-for="raid in raids" class="raid__wrapper">
                    <div class="raid__img">
                        <img src="https://assets.profchen.fr/img/pokemon/pokemon_icon_068_00.png">
                    </div>
                    <div class="raid__content">
                        <h3>{{raid.egg_level}}T de 17h03 à 17h48<span class="raid__timer active" data-start="2019-02-03 17:03:14" data-end="2019-02-03 17:48:14">Reste 21 min</span></h3>
                        <div class="raid__gym">
                            <img src="https://d30y9cdsu7xlg0.cloudfront.net/png/4096-200.png">Noyal - {{raid.gym.name}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
              raids: JSON.parse( localStorage.getItem('pokematos_raids')),
              currentCity: JSON.parse( localStorage.getItem('pokematos_currentCity'))
            }
        },
        mounted() {
            console.log('Component mounted.'),
            this.getRaids()
        },
        methods: {
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
