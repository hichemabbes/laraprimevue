<template>
    <main-layout>
        <Secteurs />
        <router-view />
    </main-layout>
</template>

<script setup>
import MainLayout from '@/layout/MainLayout.vue';
import Secteurs from '@/Components/Atfp/Specialites/Secteurs/SecteursList.vue';
</script>

<style scoped lang="scss"></style>
