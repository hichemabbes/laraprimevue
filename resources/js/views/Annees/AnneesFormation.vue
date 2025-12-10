<template>
    <main-layout>
        <AnneesFormation />
        <router-view />
    </main-layout>
</template>

<script setup>
import MainLayout from '@/layout/MainLayout.vue';
import AnneesFormation from '@/Components/Annees/AnneesFormationList.vue';
</script>

<style scoped lang="scss"></style>
