<script setup>
import { ref, defineProps, watch } from 'vue';
import axios from '@/axios.js';
import DataTable from '@/Components/Shared/DataTable.vue';
import { useToast } from 'primevue/usetoast';

defineProps({
    filters: Object, // Filtres optionnels
});

const historiques = ref([]);
const loading = ref(false);
const totalRecords = ref(0);
const rowsPerPage = ref(10);
const currentPage = ref(1);

const toast = useToast();

const columns = [
    { field: 'type', header: 'Type', sortable: true }, // 'Groupe' ou 'Formateur'
    { field: 'entite_nom', header: 'Entité', sortable: true }, // Nom du groupe ou formateur
    { field: 'jour', header: 'Jour', sortable: true },
    { field: 'heure_debut', header: 'Heure Début', sortable: true },
    { field: 'heure_fin', header: 'Heure Fin', sortable: true },
    { field: 'module_nom', header: 'Module', sortable: true },
    { field: 'date_modification', header: 'Date Modification', sortable: true },
];

const fetchHistorique = async () => {
    loading.value = true;
    try {
        const response = await axios.get(
            '/api/centres/formation/historique-emplois',
            {
                params: {
                    page: currentPage.value,
                    per_page: rowsPerPage.value,
                    type: filters?.type || '',
                    entite: filters?.entite || '',
                    jour: filters?.jour || '',
                },
            }
        );
        historiques.value = response.data.data;
        totalRecords.value = response.data.total;
    } catch (error) {
        console.error("Erreur lors de la récupération de l'historique:", error);
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: "Impossible de charger l'historique.",
            life: 3000,
        });
    } finally {
        loading.value = false;
    }
};

const onPage = (event) => {
    currentPage.value = event.page + 1;
    rowsPerPage.value = event.rows;
    fetchHistorique();
};

watch(
    () => filters,
    () => {
        currentPage.value = 1;
        fetchHistorique();
    },
    { deep: true }
);

fetchHistorique();
</script>

<template>
    <div class="historique-emplois-list">
        <DataTable
            :value="historiques"
            :columns="columns"
            :loading="loading"
            :paginator="true"
            :rows="rowsPerPage"
            :totalRecords="totalRecords"
            @page="onPage"
            lazy
        />
    </div>
</template>

<style scoped>
.historique-emplois-list {
    padding: 1rem;
}
</style>
