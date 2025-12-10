<template>
    <div id="app">
        <AppConfig />
        <router-view></router-view>
    </div>
</template>

<script setup>
import AppConfig from '@/primevue/layout/AppConfig.vue';
</script>

<style>
#app {
    font-family: Avenir, Helvetica, Arial, sans-serif;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    text-align: center;
    color: #2c3e50;
}
</style>
