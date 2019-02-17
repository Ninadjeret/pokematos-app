<template>
    <div>
        <div v-if="getActiveRaids().length > 0" class="raids__active">
            <div class="section__title">Raids en cours</div>
            <div class="raids__wrapper">
                <div v-on:click="showModal(gym)" v-for="gym in getActiveRaids()" class="raid__wrapper">
                    <div class="raid__img">
                        <img :src="getRaidImgUrl(gym.raid)">
                    </div>
                    <div class="raid__content">
                        <h3>{{gym.raid.egg_level}}T de {{getRaidStartTime(gym.raid)}} à {{getRaidEndTime(gym.raid)}}
                            <span class="raid__timer active">
                                <countdown :time="getRaidTimeLeft(gym.raid)">
                                    <template slot-scope="props">Reste {{ props.totalMinutes }} min</template>
                                </countdown>
                            </span>
                        </h3>
                        <div class="raid__gym">
                            <img src="https://d30y9cdsu7xlg0.cloudfront.net/png/4096-200.png">{{gym.zone.name}} - {{gym.name}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="getFutureRaids().length > 0" class="raids__active">
            <div class="section__title">Raids à venir</div>
            <div class="raids__wrapper">
                <div v-on:click="showModal(gym)" v-for="gym in getFutureRaids()" class="raid__wrapper">
                    <div class="raid__img">
                        <img :src="getRaidImgUrl(gym.raid)">
                    </div>
                    <div class="raid__content">
                        <h3>{{gym.raid.egg_level}}T de {{getRaidStartTime(gym.raid)}} à {{getRaidEndTime(gym.raid)}}
                            <span class="raid__timer future">
                                <countdown :time="getRaidTimeLeft(gym.raid)">
                                    <template slot-scope="props">Dans {{ props.totalMinutes }} min</template>
                                </countdown>
                            </span>
                        </h3>
                        <div class="raid__gym">
                            <img src="https://d30y9cdsu7xlg0.cloudfront.net/png/4096-200.png">{{gym.zone.name}} - {{gym.name}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="!gyms || gyms.length === 0" class="raids__empty hide">
            <img src="https://assets.profchen.fr/img/empty_raids.png" />
            <h3>Aucun raid pour le moment...</h3>
        </div>
        <button-actions @toto="test()"></button-actions>
        <gym-modal ref="gymModal"></gym-modal>
    </div>
</template>

<script>
    import moment from 'moment';
    export default {
        props: ['gyms'],
        data() {
            return {
            }
        },
        mounted() {
            console.log('Component mounted.'),
            this.$on('toto', function() {
                console.log('refresh-data')
            })
        },
        methods: {
            test() {
                console.log('trtete')
            },
            showModal( gym ) {
                this.$refs.gymModal.showModal( gym );
            },
            getActiveRaids() {
                var now = moment();
                var activeRaids = [];
                if( this.gyms && this.gyms.length > 0 ) {
                    this.gyms.forEach(function(gym) {
                        if( gym.raid ) {
                            if( now.isAfter(gym.raid.start_time) ) {
                                activeRaids.push(gym);
                            }
                        }
                    });
                }
                return activeRaids;
            },
            getFutureRaids() {
                var now = moment();
                var futureRaids = [];
                if( this.gyms && this.gyms.length > 0 ) {
                    this.gyms.forEach(function(gym) {
                        if( gym.raid ) {
                            if( now.isBefore(gym.raid.start_time) ) {
                                futureRaids.push(gym);
                            }
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
            },
            getRaidTimeLeft( raid ) {
                var now = moment();
                var start_time = moment(raid.start_time, '"YYYY-MM-DD HH:mm:ss"');
                var end_time = moment(raid.end_time, '"YYYY-MM-DD HH:mm:ss"');
                var raidStatus = 'future';
                if( now.isAfter(raid.start_time) ) {
                    raidStatus = 'active';
                }

                if( raidStatus == 'future' ) {
                    return parseInt(start_time.diff(now, 'milliseconds'));
                } else {
                    return parseInt(end_time.diff(now, 'milliseconds'));
                }
            }
        }
    }
</script>
