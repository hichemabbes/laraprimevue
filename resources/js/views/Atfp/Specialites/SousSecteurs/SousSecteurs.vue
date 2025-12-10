<template>
    <main-layout>
        <SousSecteurs />
        <router-view />
    </main-layout>
</template>

<script setup>
import MainLayout from '@/layout/MainLayout.vue';
import SousSecteurs from '@/Components/Atfp/Specialites/SousSecteurs/SousSecteursList.vue';
</script>

<style scoped lang="scss"></style>
