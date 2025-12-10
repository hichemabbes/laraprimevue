<template>
    <main-layout>
        <DecoupagesProgrammes />
        <router-view />
    </main-layout>
</template>

<script setup>
import MainLayout from '@/layout/MainLayout.vue';
import DecoupagesProgrammes from '@/Components/Atfp/Specialites/Programmes/Decoupages/DecoupageGeneralList.vue';
</script>

<style scoped lang="scss"></style>
