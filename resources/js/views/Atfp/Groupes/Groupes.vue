<template>
    <main-layout>
        <Groupes />
        <router-view />
    </main-layout>
</template>

<script setup>
import MainLayout from '@/layout/MainLayout.vue';
import Groupes from '@/Components/Atfp/Groupes/GroupesList.vue';
</script>

<style scoped lang="scss"></style>
