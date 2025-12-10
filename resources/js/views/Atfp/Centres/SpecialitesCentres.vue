<template>
    <main-layout>
        <SpecialitesCentres />
        <router-view />
    </main-layout>
</template>

<script setup>
import MainLayout from '@/layout/MainLayout.vue';
import SpecialitesCentres from '@/Components/Atfp/Centres/SpeCentresList.vue';
</script>

<style scoped lang="scss"></style>
