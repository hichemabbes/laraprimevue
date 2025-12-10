<template>
    <main-layout>
        <InfosSpecialites />
        <router-view />
    </main-layout>
</template>

<script setup>
import MainLayout from '@/layout/MainLayout.vue';
import InfosSpecialites from '@/Components/Atfp/Specialites/InfosSpecialitesList.vue';
</script>

<style scoped lang="scss"></style>
