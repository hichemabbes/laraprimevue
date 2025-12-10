<template>
    <main-layout>
        <EvaluationsGroupes/>
        <router-view />
    </main-layout>
</template>

<script setup>
import MainLayout from '@/layout/MainLayout.vue';
import EvaluationsGroupes from '@/Components/Centres/Formation/Groupes/Alternances/Evaluations/EvaluationsGroupesList.vue';
</script>

<style scoped lang="scss"></style>
