<template>
    <main-layout>
        <Sessions />
        <router-view />
    </main-layout>
</template>

<script setup>
import MainLayout from '@/layout/MainLayout.vue';
import Sessions from '@/Components/Formations/Sessions/SessionsList.vue';
</script>

<style scoped lang="scss"></style>
