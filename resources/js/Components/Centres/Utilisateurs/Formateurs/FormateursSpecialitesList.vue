<script setup>
import { ref, defineProps, defineEmits, watch } from 'vue';
import axios from '@/axios.js';
import DataTable from '@/Components/Shared/DataTable.vue';
import { useToast } from 'primevue/usetoast';

defineProps({
    filters: Object,
});
const emit = defineEmits(['edit']);

const specialites = ref([]);
const loading = ref(false);
const totalRecords = ref(0);
const rowsPerPage = ref(10);
const currentPage = ref(1);

const toast = useToast();

const columns = [
    { field: 'formateur_nom', header: 'Formateur', sortable: true },
    { field: 'specialite_nom', header: 'Spécialité', sortable: true },
    { field: 'module_nom', header: 'Module', sortable: true },
];

const fetchSpecialites = async () => {
    loading.value = true;
    try {
        const response = await axios.get(
            '/api/centres/personnels/formateurs-specialites',
            {
                params: {
                    page: currentPage.value,
                    per_page: rowsPerPage.value,
                    formateur: filters.formateur,
                    specialite: filters.specialite,
                },
            }
        );
        specialites.value = response.data.data;
        totalRecords.value = response.data.total;
    } catch (error) {
        console.error('Erreur lors de la récupération des spécialités:', error);
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: 'Impossible de charger les spécialités.',
            life: 3000,
        });
    } finally {
        loading.value = false;
    }
};

const onPage = (event) => {
    currentPage.value = event.page + 1;
    rowsPerPage.value = event.rows;
    fetchSpecialites();
};

watch(
    filters,
    () => {
        currentPage.value = 1;
        fetchSpecialites();
    },
    { deep: true }
);

fetchSpecialites();
</script>

<template>
    <div>
        <DataTable
            :value="specialites"
            :columns="columns"
            :loading="loading"
            :paginator="true"
            :rows="rowsPerPage"
            :totalRecords="totalRecords"
            @page="onPage"
            lazy
        >
            <template #actions="{ data }">
                <Button
                    label="Modifier"
                    icon="pi pi-pencil"
                    class="p-button-text p-button-sm"
                    @click="emit('edit', data)"
                />
            </template>
        </DataTable>
    </div>
</template>

<style scoped>
/* Style hérité du parent ou global */
</style>
