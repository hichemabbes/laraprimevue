<template>
    <main-layout>
        <OptionsListes />
        <router-view />
    </main-layout>
</template>

<script setup>
import MainLayout from '@/layout/MainLayout.vue';
import OptionsListes from '@/Components/Listes/ListesList.vue';
</script>

<style scoped lang="scss"></style>
