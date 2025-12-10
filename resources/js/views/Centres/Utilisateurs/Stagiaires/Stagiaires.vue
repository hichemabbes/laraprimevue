<template>
    <main-layout>
        <Stagiaires/>
        <router-view />
    </main-layout>
</template>

<script setup>
import MainLayout from '@/layout/MainLayout.vue';
import Stagiaires from '@/Components/Centres/Utilisateurs/Stagiaires/StagiairesList.vue';
</script>

<style scoped lang="scss"></style>
