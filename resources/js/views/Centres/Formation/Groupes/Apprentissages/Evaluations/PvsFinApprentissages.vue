<template>
    <main-layout>
        <PvsFinApprentissages/>
        <router-view />
    </main-layout>
</template>

<script setup>
import MainLayout from '@/layout/MainLayout.vue';
import PvsFinApprentissages from '@/Components/Centres/Formation/Groupes/Alternances/Evaluations/PvsFinApprentissagesList.vue';
</script>

<style scoped lang="scss"></style>
