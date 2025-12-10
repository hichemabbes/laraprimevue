<template>
    <main-layout>
        <DirecteursCentres />
        <router-view />
    </main-layout>
</template>

<script setup>
import MainLayout from '@/layout/MainLayout.vue';
import DirecteursCentres from '@/Components/Atfp/Utilisateurs/DirecteursCentres/DirecteursCentresList.vue';
</script>

<style scoped lang="scss"></style>
