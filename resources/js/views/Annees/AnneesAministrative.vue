<template>
    <main-layout>
        <AnneesAdministrative />
        <router-view />
    </main-layout>
</template>

<script setup>
import MainLayout from '@/layout/MainLayout.vue';
import AnneesAdministrative from '@/Components/Annees/AnneeAdministrativeListe.vue';
</script>

<style scoped lang="scss"></style>
