<script setup>
import { ref, onMounted } from 'vue';
import axios from '@/axios.js';
import DataTable from '@/Components/Shared/DataTable.vue';
import GroupeFusionForm from '@/Components/Centres/Formation/Groupes/Alternances/Groupes/GroupeFusionForm.vue';
import GroupeDivisionForm from '@/Components/Centres/Formation/Groupes/Alternances/Groupes/GroupeDivisionForm.vue';
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const groupes = ref([]);
const loading = ref(false);
const totalRecords = ref(0);
const rowsPerPage = ref(10);
const currentPage = ref(1);
const showFusionForm = ref(false);
const showDivisionForm = ref(false);
const selectedGroupe = ref(null);

// Colonnes du tableau
const columns = [
    { field: 'nom', header: 'Nom du Groupe', sortable: true },
    { field: 'specialite', header: 'Spécialité', sortable: true },
    {
        field: 'nombre_stagiaires',
        header: 'Nombre de StagiairesCentres',
        sortable: true,
    },
    { field: 'date_creation', header: 'Date de Création', sortable: true },
];

// Récupérer les groupes depuis le backend
const fetchGroupes = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/centres/groupes', {
            params: {
                page: currentPage.value,
                per_page: rowsPerPage.value,
            },
        });
        groupes.value = response.data.data;
        totalRecords.value = response.data.total;
    } catch (error) {
        console.error('Erreur lors de la récupération des groupes:', error);
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: 'Impossible de charger les groupes.',
            life: 3000,
        });
    } finally {
        loading.value = false;
    }
};

// Actions sur les lignes
const openFusionForm = () => {
    showFusionForm.value = true;
};

const openDivisionForm = (groupe) => {
    selectedGroupe.value = groupe;
    showDivisionForm.value = true;
};

// Gestion de la pagination
const onPage = (event) => {
    currentPage.value = event.page + 1;
    rowsPerPage.value = event.rows;
    fetchGroupes();
};

// Fermer les formulaires
const closeForms = () => {
    showFusionForm.value = false;
    showDivisionForm.value = false;
    selectedGroupe.value = null;
    fetchGroupes(); // Rafraîchir la liste après une opération
};

// Initialisation
onMounted(() => {
    fetchGroupes();
});
</script>

<template>
    <div class="gestion-groupes-container">
        <h2>Gestion des Groupes</h2>

        <!-- Tableau des groupes -->
        <DataTable
            :value="groupes"
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
                    label="Diviser"
                    icon="pi pi-split"
                    class="p-button-text p-button-sm"
                    @click="openDivisionForm(data)"
                />
            </template>
            <template #header>
                <Button
                    label="Fusionner des Groupes"
                    icon="pi pi-sync"
                    @click="openFusionForm"
                />
            </template>
        </DataTable>

        <!-- Formulaire de fusion -->
        <Dialog
            v-model:visible="showFusionForm"
            header="Fusionner des Groupes"
            :style="{ width: '50vw' }"
            :modal="true"
            @update:visible="closeForms"
        >
            <GroupeFusionForm @close="closeForms" />
        </Dialog>

        <!-- Formulaire de division -->
        <Dialog
            v-model:visible="showDivisionForm"
            header="Diviser un Groupe"
            :style="{ width: '50vw' }"
            :modal="true"
            @update:visible="closeForms"
        >
            <GroupeDivisionForm :groupe="selectedGroupe" @close="closeForms" />
        </Dialog>
    </div>
</template>

<style scoped>
.gestion-groupes-container {
    padding: 1rem;
}

h2 {
    margin-bottom: 1.5rem;
    color: var(--primary-color);
}
</style>
