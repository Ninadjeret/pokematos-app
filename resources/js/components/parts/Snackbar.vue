<template>
    <transition name="fade">
        <div class="snackbar" v-if="snackbar.message">
            {{snackbar.message}}
        </div>
    </transition>
</template>

<script>
import { mapState } from 'vuex'
export default {
    name: 'Snackbar',
    data() {
        return {
        }
    },
    computed: mapState([
            'snackbar'
    ]),
    watch: {
        snackbar: function( snackbar ) {
            const that = this;
            if( snackbar.timeout && snackbar.timeout > 0 ) {
                setTimeout(function(){
                    that.$store.commit('setSnackbar', false);
                }, snackbar.timeout);
            }
        },
    }
}
</script>
