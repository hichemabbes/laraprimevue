<script setup>
import { ref } from 'vue';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Button from 'primevue/button';

defineProps({
    filterOptions: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['filter']);

const searchQuery = ref('');
const selectedFilter = ref(null);

const applyFilters = () => {
    emit('filter', { search: searchQuery.value, filter: selectedFilter.value });
};
</script>

<template>
    <div class="flex gap-3 mb-3">
        <span class="p-float-label">
            <InputText id="search" v-model="searchQuery" class="w-full" />
            <label for="search">Rechercher</label>
        </span>
        <span class="p-float-label" v-if="filterOptions.length">
            <Dropdown
                id="filter"
                v-model="selectedFilter"
                :options="filterOptions"
                optionLabel="label"
                optionValue="value"
                class="w-full"
            />
            <label for="filter">Filtrer par</label>
        </span>
        <Button label="Appliquer" icon="pi pi-filter" @click="applyFilters" />
    </div>
</template>

<style scoped>
/* Ajouter des styles si nécessaire */
</style>
