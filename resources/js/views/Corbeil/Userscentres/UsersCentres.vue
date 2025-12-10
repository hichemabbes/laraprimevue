<template>
    <main-layout>
        <UserCentre />
        <router-view />
    </main-layout>
</template>

<script setup>
import MainLayout from '@/layout/MainLayout.vue';
import UserCentre from '@/Components/Atfp/Corbeils/PersonnelsCentres1List.vue';
</script>

<style scoped lang="scss"></style>
