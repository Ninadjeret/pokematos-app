<template>
        <modal name="raid-modal">
            <h3>Choisis ta zone</h3>
            <div class="footer--actions">
                <button class="button--close" v-on:click="hideModal()"><i class="material-icons">close</i></button>
            </div>
        </modal>
</template>

<script>
export default {
    data() {
        return {
        }
    },
    mounted() {
        console.log('Component mounted.')
    },
    created() {
    },
    methods: {
        hideModal() {
            this.$modal.hide('cityChoice');
        }
    }
}
</script>
