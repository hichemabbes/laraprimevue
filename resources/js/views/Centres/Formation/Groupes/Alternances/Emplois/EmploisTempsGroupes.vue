<template>
    <main-layout>
        <EmploisTempsGroupes/>
        <router-view />
    </main-layout>
</template>

<script setup>
import MainLayout from '@/layout/MainLayout.vue';
import EmploisTempsGroupes from '@/Components/Centres/Formation/Groupes/Alternances/Emplois/EmploisTempsGroupesList.vue';
</script>

<style scoped lang="scss"></style>
