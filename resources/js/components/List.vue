<template>
    <div>
        <div v-if="getActiveRaids().length > 0" class="raids__active">
            <div class="section__title">Raids en cours</div>
            <div class="raids__wrapper">
                <div v-for="raid in getActiveRaids()" class="raid__wrapper">
                    <div class="raid__img">
                        <img :src="getRaidImgUrl(raid)">
                    </div>
                    <div class="raid__content">
                        <h3>{{raid.egg_level}}T de {{getRaidStartTime(raid)}} à {{getRaidEndTime(raid)}}<span class="raid__timer active" data-start="2019-02-03 17:03:14" data-end="2019-02-03 17:48:14">Reste 21 min</span></h3>
                        <div class="raid__gym">
                            <img src="https://d30y9cdsu7xlg0.cloudfront.net/png/4096-200.png">{{raid.gym.zone.name}} - {{raid.gym.name}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="getFutureRaids().length > 0" class="raids__active">
            <div class="section__title">Raids à venir</div>
            <div class="raids__wrapper">
                <div v-for="raid in getFutureRaids()" class="raid__wrapper">
                    <div class="raid__img">
                        <img :src="getRaidImgUrl(raid)">
                    </div>
                    <div class="raid__content">
                        <h3>{{raid.egg_level}}T de {{getRaidStartTime(raid)}} à {{getRaidEndTime(raid)}}<span class="raid__timer active" data-start="2019-02-03 17:03:14" data-end="2019-02-03 17:48:14">Reste 21 min</span></h3>
                        <div class="raid__gym">
                            <img src="https://d30y9cdsu7xlg0.cloudfront.net/png/4096-200.png">{{raid.gym.zone.name}} - {{raid.gym.name}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="!raids || raids.length === 0" class="raids__empty hide">
            <img src="https://assets.profchen.fr/img/empty_raids.png" />
            <h3>Aucun raid pour le moment...</h3>
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
            getActiveRaids() {
                var now = moment();
                var activeRaids = [];
                if( this.raids && this.raids.length > 0 ) {
                    this.raids.forEach(function(raid) {
                        if( now.isAfter(raid.start_time) ) {
                            activeRaids.push(raid);
                        }
                    });
                }
                return activeRaids;
            },
            getFutureRaids() {
                var now = moment();
                var futureRaids = [];
                if( this.raids && this.raids.length > 0 ) {
                    this.raids.forEach(function(raid) {
                        if( now.isBefore(raid.start_time) ) {
                            futureRaids.push(raid);
                        }
                    });
                }
                return futureRaids;
            },
            getRaidImgUrl( raid ) {
                var now = moment();
                var raidStatus = 'future';
                if( now.isAfter(raid.start_time) ) {
                    raidStatus = 'active';
                }
                if( raidStatus == 'future' || !raid.pokemon ) {
                    return 'https://assets.profchen.fr/img/eggs/egg_'+raid.egg_level+'.png';
                } else {
                    return raid.pokemon.thumbnail_url;
                }
            },
            getRaidStartTime( raid ) {
                var startTime = moment(raid.start_time);
                return startTime.format('HH[h]mm');
            },
            getRaidEndTime( raid ) {
                var endTime = moment(raid.end_time);
                return endTime.format('HH[h]mm');
            }
        }
    }
</script>
