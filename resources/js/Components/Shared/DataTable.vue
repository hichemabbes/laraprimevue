<script setup>
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';

defineProps({
    data: {
        type: Array,
        required: true,
    },
    columns: {
        type: Array,
        required: true,
        default: () => [],
    },
});
</script>

<template>
    <DataTable :value="data" responsiveLayout="scroll" class="mt-3">
        <Column
            v-for="col in columns"
            :key="col.field"
            :field="col.field"
            :header="col.header"
        />
    </DataTable>
</template>

<style scoped>
/* Ajouter des styles si nécessaire */
</style>
