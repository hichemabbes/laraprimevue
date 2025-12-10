<template>
    <main-layout>
        <HistoriqueEmploisGroupes/>
        <router-view />
    </main-layout>
</template>

<script setup>
import MainLayout from '@/layout/MainLayout.vue';
import HistoriqueEmploisGroupes from '@/Components/Centres/Formation/Groupes/Alternances/Emplois/HistoriqueEmploisGroupesList.vue';
</script>

<style scoped lang="scss"></style>
