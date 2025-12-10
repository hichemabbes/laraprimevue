<template>
    <main-layout>
        <Centres />
        <router-view />
    </main-layout>
</template>

<script setup>
import MainLayout from '@/layout/MainLayout.vue';
import Centres from '@/Components/Atfp/Centres/CentresList.vue';
</script>

<style scoped lang="scss"></style>
