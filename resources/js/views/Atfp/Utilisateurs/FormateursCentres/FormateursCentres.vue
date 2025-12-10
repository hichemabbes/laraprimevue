<template>
    <main-layout>
        <FormateursCentres />
        <router-view />
    </main-layout>
</template>

<script setup>
import MainLayout from '@/layout/MainLayout.vue';
import FormateursCentres from '@/Components/Atfp/Utilisateurs/FormateursCentres/FormateursCentresList.vue';
</script>

<style scoped lang="scss"></style>
