<script setup>
import { ref, watch, defineProps } from 'vue';
import axios from '@/axios.js';
import DataTable from '@/Components/Shared/DataTable.vue';
import ConfirmationDialog from '@/Components/Shared/ConfirmationDialog.vue';
import { useToast } from 'primevue/usetoast';

defineProps({
    filters: Object,
});

const cartes = ref([]);
const loading = ref(false);
const totalRecords = ref(0);
const rowsPerPage = ref(10);
const currentPage = ref(1);
const dialogVisible = ref(false);
const selectedCarte = ref(null);

const toast = useToast();

// Colonnes du tableau
const columns = [
    { field: 'stagiaire_nom', header: 'Stagiaire', sortable: true },
    { field: 'groupe_nom', header: 'Groupe', sortable: true },
    { field: 'numero_carte', header: 'Numéro de Carte', sortable: true },
    { field: 'date_generation', header: 'Date de Génération', sortable: true },
];

// Récupérer les données depuis le backend
const fetchCartes = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/centres/stagiaires/cartes', {
            params: {
                page: currentPage.value,
                per_page: rowsPerPage.value,
                groupe: filters.groupe,
                stagiaire: filters.stagiaire,
                date_generation: filters.dateGeneration,
            },
        });
        cartes.value = response.data.data;
        totalRecords.value = response.data.total;
    } catch (error) {
        console.error('Erreur lors de la récupération des cartes:', error);
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: 'Impossible de charger les cartes.',
            life: 3000,
        });
    } finally {
        loading.value = false;
    }
};

// Actions sur les lignes
const viewDetails = (carte) => {
    selectedCarte.value = carte;
    dialogVisible.value = true;
};

const downloadCarte = async (carte) => {
    try {
        const response = await axios.get(
            `/api/centres/stagiaires/cartes/${carte.id}/download`,
            {
                responseType: 'blob',
            }
        );
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute(
            'download',
            `carte_stagiaire_${carte.numero_carte}.pdf`
        );
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    } catch (error) {
        console.error('Erreur lors du téléchargement:', error);
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: 'Impossible de télécharger la carte.',
            life: 3000,
        });
    }
};

// Gestion des filtres et pagination
const onPage = (event) => {
    currentPage.value = event.page + 1;
    rowsPerPage.value = event.rows;
    fetchCartes();
};

// Recharger les données quand les filtres changent
watch(
    filters,
    () => {
        currentPage.value = 1;
        fetchCartes();
    },
    { deep: true }
);

// Initialisation
fetchCartes();
</script>

<template>
    <div>
        <!-- Tableau des cartes -->
        <DataTable
            :value="cartes"
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
                    label="Détails"
                    icon="pi pi-eye"
                    class="p-button-text p-button-sm"
                    @click="viewDetails(data)"
                />
                <Button
                    label="Télécharger"
                    icon="pi pi-download"
                    class="p-button-text p-button-sm"
                    @click="downloadCarte(data)"
                />
            </template>
        </DataTable>

        <!-- Dialogue pour les détails -->
        <ConfirmationDialog
            v-model:visible="dialogVisible"
            header="Détails de la Carte"
            :closable="true"
        >
            <div v-if="selectedCarte">
                <p>
                    <strong>Stagiaire:</strong>
                    {{ selectedCarte.stagiaire_nom }}
                </p>
                <p><strong>Groupe:</strong> {{ selectedCarte.groupe_nom }}</p>
                <p>
                    <strong>Numéro de Carte:</strong>
                    {{ selectedCarte.numero_carte }}
                </p>
                <p>
                    <strong>Date de Génération:</strong>
                    {{ selectedCarte.date_generation }}
                </p>
            </div>
        </ConfirmationDialog>
    </div>
</template>

<style scoped>
/* Style hérité du parent ou global */
</style>
