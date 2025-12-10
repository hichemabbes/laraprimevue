<template>
    <main-layout>
        <Formateurs />
        <router-view />
    </main-layout>
</template>

<script setup>
import MainLayout from '@/layout/MainLayout.vue';
import Formateurs from '@/Components/Centres/Utilisateurs/Formateurs/FormateursList.vue';
</script>

<style scoped lang="scss"></style>
