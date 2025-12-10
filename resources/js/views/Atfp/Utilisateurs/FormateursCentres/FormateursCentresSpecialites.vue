<template>
    <main-layout>
        <FormateursCentresSpecialites/>
        <router-view />
    </main-layout>
</template>

<script setup>
import MainLayout from '@/layout/MainLayout.vue';
import FormateursCentresSpecialites from '@/Components/Atfp/Utilisateurs/Formateurscentres/FormateursCentresSpecialitesList.vue';
</script>

<style scoped lang="scss"></style>
