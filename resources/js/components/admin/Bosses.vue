<template>
    <div>
        <div class="settings-section">
            <div class="section__title">Pouet</div>
        </div>
    </div>
</template>

<script>
    import { mapState } from 'vuex'
    export default {
        name: 'AdminBosses',
        data() {
            return {
            }
        },
        computed: mapState([
                'currentCity'
        ]),
        created() {
            console.log('toto');
            console.log(this.$route );
        },
        methods: {
        }
    }
</script>
