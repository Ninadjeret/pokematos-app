<template>
<div id="toto">
    <app-header v-bind:page-title="pageTitle"></app-header>
    <app-nav></app-nav>
</div>
</template>

<script>
    export default {
        props: ['pageTitle'],
        data() {
            return {
            }
        },
        mounted() {
            console.log(this.pageTitle)
        }
    }
</script>
