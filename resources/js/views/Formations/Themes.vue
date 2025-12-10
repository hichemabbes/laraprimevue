<template>
    <main-layout>
        <Themes />
        <router-view />
    </main-layout>
</template>

<script setup>
import MainLayout from '@/layout/MainLayout.vue';
import Themes from '@/Components/Formations/Themes/ThemesList.vue';
</script>

<style scoped lang="scss"></style>
