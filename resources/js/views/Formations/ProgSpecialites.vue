<template>
    <main-layout>
        <ProgSpecialites />
        <router-view />
    </main-layout>
</template>

<script setup>
import MainLayout from '@/layout/MainLayout.vue';
import ProgSpecialites from '@/Components/Formations/Specialites/ProgSpecialitesList.vue';
</script>


<style scoped lang="scss"></style>
