<template>
    <main-layout>
        <PasswordManager />
        <router-view />
    </main-layout>
</template>

<script setup>
import MainLayout from '@/layout/MainLayout.vue';
import PasswordManager from '@/Components/Tools/PasswordManagerForm.vue';
</script>

<style scoped lang="scss"></style>
