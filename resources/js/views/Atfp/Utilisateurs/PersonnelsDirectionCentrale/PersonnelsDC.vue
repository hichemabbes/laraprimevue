<template>
    <main-layout>
        <PersonnelsDC />
        <router-view />
    </main-layout>
</template>

<script setup>
import MainLayout from '@/layout/MainLayout.vue';
import PersonnelsDC from '@/Components/Atfp/Utilisateurs/PersonnelsDirectionCentrale/PersonnelsDCList.vue';
</script>

<style scoped lang="scss"></style>
