<template>
    <div>
        <div v-if="HasActiveRaids()" class="raids__active">
            <div class="section__title">Raids en cours</div>
            <div class="raids__wrapper">
                <div v-for="raid in raids" class="raid__wrapper">
                    <div class="raid__img">
                        <img :src="getRaidImgUrl(raid)">
                    </div>
                    <div class="raid__content">
                        <h3>{{raid.egg_level}}T de 17h03 à 17h48<span class="raid__timer active" data-start="2019-02-03 17:03:14" data-end="2019-02-03 17:48:14">Reste 21 min</span></h3>
                        <div class="raid__gym">
                            <img src="https://d30y9cdsu7xlg0.cloudfront.net/png/4096-200.png">{{raid.gym.zone.name}} - {{raid.gym.name}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="HasFutureRaids()" class="raids__active">
            <div class="section__title">Raids à venir</div>
            <div class="raids__wrapper">
                <div v-for="raid in raids" class="raid__wrapper">
                    <div class="raid__img">
                        <img :src="getRaidImgUrl(raid)">
                    </div>
                    <div class="raid__content">
                        <h3>{{raid.egg_level}}T de 17h03 à 17h48<span class="raid__timer active" data-start="2019-02-03 17:03:14" data-end="2019-02-03 17:48:14">Reste 21 min</span></h3>
                        <div class="raid__gym">
                            <img src="https://d30y9cdsu7xlg0.cloudfront.net/png/4096-200.png">{{raid.gym.zone.name}} - {{raid.gym.name}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import moment from 'moment';
    export default {
        props: ['raids'],
        data() {
            return {
            }
        },
        mounted() {
            console.log('Component mounted.')
        },
        methods: {
            HasActiveRaids() {
                var now = moment();
                var hasActiveRaids = false;
                this.raids.forEach(function(raid) {
                    console.log(raid);
                    if( now.isAfter(raid.startTime) ) {
                        hasActiveRaids = true;
                    }
                });
                return hasActiveRaids;
            },
            HasFutureRaids() {
                var now = moment();
                var HasFutureRaids = false;
                this.raids.forEach(function(raid) {
                    console.log(raid);
                    if( now.isBefore(raid.startTime) ) {
                        HasFutureRaids = true;
                    }
                });
                return HasFutureRaids;
            },
            getRaidImgUrl( raid ) {
                var now = moment();
                var raidStatus = 'future';
                if( now.isAfter(raid.startTime) ) {
                    raidStatus = 'active';
                }
                if( raidStatus == 'active' ) {
                    return 'https://assets.profchen.fr/img/pokemon/pokemon_icon_068_00.png';
                } else {
                    return 'https://assets.profchen.fr/img/eggs/egg_'+raid.egg_level+'.png';
                }
            }
        }
    }
</script>
