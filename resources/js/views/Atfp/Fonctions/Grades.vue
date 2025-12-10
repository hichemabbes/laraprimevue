<template>
    <main-layout>
        <Grades/>
        <router-view />
    </main-layout>
</template>

<script setup>
import MainLayout from '@/layout/MainLayout.vue';
import Grades from '@/Components/Atfp/Fonctions/GradesList.vue';
</script>

<style scoped lang="scss"></style>
