<template>
    <main-layout>
        <Programmes />
        <router-view />
    </main-layout>
</template>

<script setup>
import MainLayout from '@/layout/MainLayout.vue';
import Programmes from '@/Components/Atfp/Specialites/Programmes/ProgrammesList.vue';
</script>

<style scoped lang="scss"></style>
