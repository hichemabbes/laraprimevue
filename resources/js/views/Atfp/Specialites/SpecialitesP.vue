<template>
    <main-layout>
        <SpecialitesP />
        <router-view />
    </main-layout>
</template>

<script setup>
import MainLayout from '@/layout/MainLayout.vue';
import SpecialitesP from '@/corbeil/Specialites/SpecialitesListP.vue';
</script>

<style scoped lang="scss"></style>
