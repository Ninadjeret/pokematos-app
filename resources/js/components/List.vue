<template>
    <div>
        <div v-if="activeRaids.length > 0" class="raids__active">
            <div class="section__title">Raids en cours</div>
            <div class="raids__wrapper">
                <div v-on:click="showModal(gym)" v-for="gym in activeRaids" class="raid__wrapper">
                    <div class="raid__img">
                        <img :src="getRaidImgUrl(gym.raid)">
                    </div>
                    <div class="raid__content">
                        <h3>
                            <span v-if="gym.raid.ex">Raid EX de {{getRaidStartTime(gym.raid)}} à {{getRaidEndTime(gym.raid)}}</span>
                            <span v-else>{{gym.raid.egg_level}}T de {{getRaidStartTime(gym.raid)}} à {{getRaidEndTime(gym.raid)}}</span>
                            <span class="raid__timer active">
                                <countdown :time="getRaidTimeLeft(gym.raid)" @end="$store.dispatch('fetchData')">
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

        <div v-if="futureRaids.length > 0" class="raids__future">
            <div class="section__title">Raids à venir</div>
            <div class="raids__wrapper">
                <div v-on:click="showModal(gym)" v-for="gym in futureRaids" class="raid__wrapper">
                    <div class="raid__img">
                        <img :src="getRaidImgUrl(gym.raid)">
                    </div>
                    <div class="raid__content">
                        <h3>
                            <span v-if="gym.raid.ex">Raid EX de {{getRaidStartTime(gym.raid)}} à {{getRaidEndTime(gym.raid)}}</span>
                            <span v-else>{{gym.raid.egg_level}}T de {{getRaidStartTime(gym.raid)}} à {{getRaidEndTime(gym.raid)}}</span>
                            <span class="raid__timer future">
                                <countdown v-if="gym.raid.ex" :time="getRaidTimeLeft(gym.raid)" @end="$store.dispatch('fetchData')">
                                    <template slot-scope="props">Dans {{ props.days }}j et {{ props.hours }}h</template>
                                </countdown>
                                <countdown v-else :time="getRaidTimeLeft(gym.raid)" @end="$store.dispatch('fetchData')">
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
        <div v-if="futureRaids.length === 0 &  activeRaids.length === 0" class="raids__empty hide">
            <img src="https://assets.profchen.fr/img/empty_raids.png" />
            <h3>Aucun raid pour le moment...</h3>
        </div>
        <button-actions></button-actions>
        <gym-modal ref="gymModal"></gym-modal>
    </div>
</template>

<script>
    import moment from 'moment';
    export default {
        name: 'List',
        props: ['gyms'],
        data() {
            return {
            }
        },
        computed: {
            activeRaids() {
                return this.$store.getters.activeRaids;
            },
            futureRaids() {
                return this.$store.getters.futureRaids;
            }
        },
        mounted() {
            console.log('Component mounted.')
        },
        methods: {
            showModal( gym ) {
                this.$refs.gymModal.showModal( gym );
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
