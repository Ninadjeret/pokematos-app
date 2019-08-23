<template>
    <div>Toto</div>
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
                console.log(res.data.current)
                let serverVersion = res.data
                if( typeof appVersion === "undefined" || !appVersion || appVersion == '' ) {
                    this.$store.commit('initSetting', {
                        setting: 'appVersion',
                        value: serverVersion.current
                    });
                } else {

                }
            }).catch();
        }
    },
}
</script>
