<script setup>
import { ref, defineEmits, watch } from 'vue';
import axios from '@/axios.js';
import DataTable from '@/Components/Shared/DataTable.vue';
import { useToast } from 'primevue/usetoast';

defineProps({
    filters: Object, // Filtres optionnels passés depuis la vue parent
});
const emit = defineEmits(['edit']);

const emplois = ref([]);
const loading = ref(false);
const totalRecords = ref(0);
const rowsPerPage = ref(10);
const currentPage = ref(1);

const toast = useToast();

const columns = [
    { field: 'formateur_nom', header: 'Formateur', sortable: true },
    { field: 'jour', header: 'Jour', sortable: true },
    { field: 'heure_debut', header: 'Heure Début', sortable: true },
    { field: 'heure_fin', header: 'Heure Fin', sortable: true },
    { field: 'groupe_nom', header: 'Groupe', sortable: true },
    { field: 'module_nom', header: 'Module', sortable: true },
];

const fetchEmplois = async () => {
    loading.value = true;
    try {
        const response = await axios.get(
            '/api/centres/formation/emplois-temps-formateurs',
            {
                params: {
                    page: currentPage.value,
                    per_page: rowsPerPage.value,
                    formateur: filters?.formateur || '',
                    jour: filters?.jour || '',
                    groupe: filters?.groupe || '',
                },
            }
        );
        emplois.value = response.data.data;
        totalRecords.value = response.data.total;
    } catch (error) {
        console.error('Erreur lors de la récupération des emplois:', error);
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: 'Impossible de charger les emplois.',
            life: 3000,
        });
    } finally {
        loading.value = false;
    }
};

const onPage = (event) => {
    currentPage.value = event.page + 1;
    rowsPerPage.value = event.rows;
    fetchEmplois();
};

watch(
    () => filters,
    () => {
        currentPage.value = 1;
        fetchEmplois();
    },
    { deep: true }
);

fetchEmplois();
</script>

<template>
    <div class="emplois-temps-formateurs-list">
        <DataTable
            :value="emplois"
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
.emplois-temps-formateurs-list {
    padding: 1rem;
}
</style>
