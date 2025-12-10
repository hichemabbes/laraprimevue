<template>
    <main-layout>
        <Personnels/>
        <router-view />
    </main-layout>
</template>

<script setup>
import MainLayout from '@/layout/MainLayout.vue';
import Personnels from '@/Components/Centres/Utilisateurs/Personnels/PersonnelsList.vue';
</script>

<style scoped lang="scss"></style>
