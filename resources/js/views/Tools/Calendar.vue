<template>
    <main-layout>
        <Calendar />
        <router-view />
    </main-layout>
</template>

<script setup>
import MainLayout from '@/layout/MainLayout.vue';
import Calendar from '@/Components/Tools/CalendarComponent.vue';
</script>

<style scoped lang="scss"></style>
