<template>
    <main-layout>
        <Entreprises />
        <router-view />
    </main-layout>
</template>

<script setup>
import MainLayout from '@/layout/MainLayout.vue';
import Entreprises from '@/Components/Entreprises/EntreprisesList.vue';
</script>

<style scoped lang="scss"></style>
