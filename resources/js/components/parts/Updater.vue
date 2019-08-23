<template>

</template>

<script>
import { mapState } from 'vuex'
export default {
    name: 'Updater',
    data() {
        return {
        }
    },
    computed: {
        appVersion: {
            get: function () {
                return this.$store.getters.getSetting('appVersion');
            },
            set: function (newValue) {
                this.$store.commit('setSetting', {
                    setting: 'appVersion',
                    value: newValue
                });
            }
        }
    },
    created() {
        this.fetchVersion();
    },
    methods: {
        fetchVersion() {
            axios.get('/api/version').then( res => {
                this.$store.commit('setSetting', {
                    setting: 'appVersion',
                    value: res.data.current
                });
            }).catch();
        }
    },
}
</script>
