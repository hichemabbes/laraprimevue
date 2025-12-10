<template>
    <main-layout>
        <StagiairesCentres />
        <router-view />
    </main-layout>
</template>

<script setup>
import MainLayout from '@/layout/MainLayout.vue';
import StagiairesCentres from '@/Components/Atfp/Utilisateurs/StagiairesCentres/StagiairesCentresList.vue';
</script>

<style scoped lang="scss"></style>
