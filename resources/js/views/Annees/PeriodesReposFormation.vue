<template>
    <main-layout>
        <PeriodesReposFormation />
        <router-view />
    </main-layout>
</template>

<script setup>
import MainLayout from '@/layout/MainLayout.vue';
import PeriodesReposFormation from '@/Components/Corbeils/PeriodesReposFormationList.vue';
</script>

<style scoped lang="scss"></style>
