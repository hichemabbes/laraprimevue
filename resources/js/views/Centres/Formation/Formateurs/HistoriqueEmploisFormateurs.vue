<template>
    <main-layout>
        <HistoriqueEmploisFormateurs/>
        <router-view />
    </main-layout>
</template>

<script setup>
import MainLayout from '@/layout/MainLayout.vue';
import HistoriqueEmploisFormateurs from '@/Components/Centres/Formation/Formateurs/Emplois/HistoriqueEmploisFormateursList.vue';
</script>

<style scoped lang="scss"></style>
