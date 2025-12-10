<template>
    <main-layout>
        <PvSessionsControles/>
        <router-view />
    </main-layout>
</template>

<script setup>
import MainLayout from '@/layout/MainLayout.vue';
import PvSessionsControles from '@/Components/Centres/Formation/Groupes/Alternances/Evaluations/PvSessionsControlesList.vue';
</script>

<style scoped lang="scss"></style>
