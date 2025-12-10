<template>
    <main-layout>
        <FormateursSpecialites/>
        <router-view />
    </main-layout>
</template>

<script setup>
import MainLayout from '@/layout/MainLayout.vue';
import FormateursSpecialites from '@/Components/Centres/Utilisateurs/Formateurs/FormateursSpecialitesList.vue';
</script>

<style scoped lang="scss"></style>
