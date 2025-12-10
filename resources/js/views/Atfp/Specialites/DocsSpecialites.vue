<template>
    <main-layout>
        <DocsSpecialites />
        <router-view />
    </main-layout>
</template>

<script setup>
import MainLayout from '@/layout/MainLayout.vue';
import DocsSpecialites from '@/Components/Atfp/Specialites/DocsSpecialitesList.vue';
</script>

<style scoped lang="scss"></style>
