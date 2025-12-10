<template>
    <main-layout>
        <PvSessionsPrincipales/>
        <router-view />
    </main-layout>
</template>

<script setup>
import MainLayout from '@/layout/MainLayout.vue';
import PvSessionsPrincipales from '@/Components/Centres/Formation/Groupes/Alternances/Evaluations/PvSessionsPrincipalesList.vue';
</script>

<style scoped lang="scss"></style>
