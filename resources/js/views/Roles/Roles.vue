<template>
    <main-layout>
        <Roles />
        <router-view />
    </main-layout>
</template>

<script setup>
import MainLayout from '@/layout/MainLayout.vue';
import Roles from '@/Components/Roles/RolesList.vue';
</script>

<style scoped lang="scss"></style>
