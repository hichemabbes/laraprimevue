<template>
    <main-layout>
        <EmploisTempsFormateurs/>
        <router-view />
    </main-layout>
</template>

<script setup>
import MainLayout from '@/layout/MainLayout.vue';
import EmploisTempsFormateurs from '@/Components/Centres/Formation/Formateurs/Emplois/EmploisTempsFormateursList.vue';
</script>

<style scoped lang="scss"></style>
