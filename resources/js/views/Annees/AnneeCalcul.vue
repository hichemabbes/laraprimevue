<template>
    <main-layout>
        <AnneeCalcul />
        <router-view />
    </main-layout>
</template>

<script setup>
import MainLayout from '@/layout/MainLayout.vue';
import AnneeCalcul from '@/Components/Annees/AnneeCalculList.vue';
</script>

<style scoped lang="scss"></style>
