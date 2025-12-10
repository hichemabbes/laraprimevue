<template>
    <main-layout>
        <Formations />
        <router-view />
    </main-layout>
</template>

<script setup>
import MainLayout from '@/layout/MainLayout.vue';
import Formations from '@/Components/Formations/FormationsList.vue';
</script>

<style scoped lang="scss"></style>
