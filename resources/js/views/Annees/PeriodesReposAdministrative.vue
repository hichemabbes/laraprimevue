<template>
    <main-layout>
        <PeriodesReposAdministrative />
        <router-view />
    </main-layout>
</template>

<script setup>
import MainLayout from '@/layout/MainLayout.vue';
import PeriodesReposAdministrative from '@/Components/Corbeils/PeriodeReposAdministrativeListe.vue';
</script>

<style scoped lang="scss"></style>
