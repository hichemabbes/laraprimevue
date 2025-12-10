<template>
    <main-layout>
        <PersonnelsCentres />
        <router-view />
    </main-layout>
</template>

<script setup>
import MainLayout from '@/layout/MainLayout.vue';
import PersonnelsCentres from '@/Components/Atfp/Utilisateurs/PersonnelsCentres/PersonnelsCentresList.vue';
</script>

<style scoped lang="scss"></style>
