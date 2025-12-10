<template>
    <main-layout>
        <Annees />
        <router-view />
    </main-layout>
</template>

<script setup>
import MainLayout from '@/primevue/layout/MainLayout.vue';
import Annees from '@/primevue/views/brillant/AnneesList.vue';
</script>

<style scoped lang="scss"></style>
