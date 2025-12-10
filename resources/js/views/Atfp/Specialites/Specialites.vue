<template>
    <main-layout>
        <Specialites />
        <router-view />
    </main-layout>
</template>

<script setup>
import MainLayout from '@/layout/MainLayout.vue';
import Specialites from '@/Components/Atfp/Specialites/ThemesList.vue';
</script>

<style scoped lang="scss"></style>
