<template>
    <main-layout>
        <Tuteurs/>
        <router-view />
    </main-layout>
</template>

<script setup>
import MainLayout from '@/layout/MainLayout.vue';
import Tuteurs from '@/Components/Entreprises/TuteursList.vue';
</script>

<style scoped lang="scss"></style>
