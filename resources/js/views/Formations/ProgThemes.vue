<template>
    <main-layout>
        <ProgThemes />
        <router-view />
    </main-layout>
</template>

<script setup>
import MainLayout from '@/layout/MainLayout.vue';
import ProgThemes from '@/Components/Formations/Themes/ProgThemesList.vue';
</script>


<style scoped lang="scss"></style>
