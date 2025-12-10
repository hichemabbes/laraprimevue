<template>
    <div>
        <!-- Titre de la page -->
        <h1 class="modern-title">Liste des Années de Formation</h1>

        <!-- Tableau des années de formation -->
        <DataTable
            v-model:filters="filters"
            showGridlines
            stripedRows
            v-model:selection="selectedAnnees"
            scrollable
            scrollHeight="700px"
            tableStyle="min-width: 50rem"
            size="small"
            :value="anneesFormation"
            paginator
            :rows="20"
            dataKey="id"
            filterDisplay="menu"
            :globalFilterFields="[
                'intitule',
                'statut',
                'date_debut',
                'date_fin',
            ]"
            :loading="loading"
        >
            <!-- En-tête du tableau -->
            <template #header>
                <div
                    class="flex justify-content-between mb-2 align-items-center"
                >
                    <!-- Section gauche -->
                    <div class="flex align-items-center">
                        <!-- Bouton Ajouter -->
                        <Button
                            icon="pi pi-plus"
                            severity="success"
                            size="small"
                            class="mr-2"
                            @click="openForm"
                        />

                        <!-- Champ de recherche -->
                        <span class="p-input-icon-left mr-2">
                            <i class="pi pi-search" />
                            <InputText
                                v-model="filters['global'].value"
                                size="small"
                                placeholder="Recherche..."
                                style="width: 100%"
                            />
                        </span>

                        <!-- Bouton Effacer les filtres -->
                        <Button
                            type="button"
                            icon="pi pi-filter-slash"
                            size="small"
                            class="mr-2"
                            label="Effacer"
                            outlined
                            @click="clearFilter"
                        />
                    </div>

                    <!-- Section droite -->
                    <div class="flex align-items-center">
                        <!-- Bouton Actions -->
                        <SplitButton
                            label="Actions"
                            icon="pi pi-cog"
                            size="small"
                            class="mr-2"
                            :model="actions"
                        />
                    </div>
                </div>
            </template>

            <!-- Message lorsque le tableau est vide -->
            <template #empty>
                <div v-if="!loading">Aucune année de formation trouvée.</div>
                <div v-else>Chargement en cours...</div>
            </template>

            <!-- Colonnes du tableau -->
            <Column selectionMode="multiple" headerStyle="width: 3rem"></Column>
            <Column
                field="intitule"
                header="Intitulé"
                sortable
                :filterMenuStyle="{ width: '14rem' }"
                style="min-width: 12rem"
            >
                <template #body="{ data }">
                    <span :class="{ 'new-row': data.isNew }">{{
                        data.intitule
                    }}</span>
                </template>
                <template #filter="{ filterModel, filterCallback }">
                    <InputText
                        v-model="filterModel.value"
                        type="text"
                        class="p-column-filter"
                        placeholder="Rechercher par intitulé"
                        @input="filterCallback"
                    />
                </template>
            </Column>

            <Column
                field="date_debut"
                header="Date de début"
                sortable
                filterField="date_debut"
                dataType="date"
                style="min-width: 10rem"
            >
                <template #body="{ data }">
                    {{ formatDate(data.date_debut) }}
                </template>
                <template #filter="{ filterModel, filterCallback }">
                    <Calendar
                        v-model="filterModel.value"
                        dateFormat="dd/mm/yy"
                        placeholder="dd/mm/yyyy"
                        mask="99/99/9999"
                        @date-select="filterCallback"
                    />
                </template>
            </Column>

            <Column
                field="date_fin"
                header="Date de fin"
                sortable
                filterField="date_fin"
                dataType="date"
                style="min-width: 10rem"
            >
                <template #body="{ data }">
                    {{ formatDate(data.date_fin) }}
                </template>
                <template #filter="{ filterModel, filterCallback }">
                    <Calendar
                        v-model="filterModel.value"
                        dateFormat="dd/mm/yy"
                        placeholder="dd/mm/yyyy"
                        mask="99/99/9999"
                        @date-select="filterCallback"
                    />
                </template>
            </Column>

            <Column
                header="Statut"
                field="statut"
                sortable
                :filterMenuStyle="{ width: '14rem' }"
                style="min-width: 12rem"
            >
                <!-- Template pour afficher les données dans la colonne -->
                <template #body="{ data }">
                    <div class="centered-content">
                        <Tag
                            :value="data.statut"
                            :severity="getSeverity(data.statut)"
                            class="arabic-text fixed-width-tag"
                        />
                    </div>
                </template>

                <!-- Template pour le filtre -->
                <template #filter="{ filterModel, filterCallback }">
                    <Dropdown
                        v-model="filterModel.value"
                        :options="statuts"
                        placeholder="Sélectionner un statut"
                        class="arabic-text"
                        showClear
                        @change="filterCallback"
                    >
                        <!-- Template pour afficher les options du filtre avec la sévérité -->
                        <template #option="slotProps">
                            <Tag
                                :value="slotProps.option"
                                :severity="getSeverity(slotProps.option)"
                                class="arabic-text fixed-width-tag"
                            />
                        </template>
                    </Dropdown>
                </template>
            </Column>

            <!-- Colonne Actions -->
            <Column header="Actions" style="min-width: 10rem">
                <template #body="{ data }">
                    <Button
                        icon="pi pi-pencil"
                        class="p-button-rounded p-button-text p-button-success mr-2 action-icon"
                        @click="editAnnee(data)"
                    />
                    <Button
                        icon="pi pi-trash"
                        class="p-button-rounded p-button-text p-button-danger action-icon"
                        @click="confirmDeleteAnnee(data)"
                    />
                </template>
            </Column>
        </DataTable>

        <!-- Form d'ajout/modification -->
        <Form
            :visible="formVisible"
            :anneeToEdit="anneeToEdit"
            @update:visible="formVisible = $event"
            @save="handleSaveAnnee"
            @update="handleUpdateAnnee"
            @close="closeForm"
        />

        <!-- Form de confirmation de suppression -->
        <Dialog
            v-model:visible="deleteFormVisible"
            :style="{ width: '450px' }"
            header="Confirmation"
            :modal="true"
        >
            <div class="confirmation-content">
                <i
                    class="pi pi-exclamation-triangle mr-3"
                    style="font-size: 2rem"
                />
                <span
                    >Êtes-vous sûr de vouloir supprimer cette année de formation
                    ?</span
                >
            </div>
            <template #footer>
                <Button
                    label="Non"
                    icon="pi pi-times"
                    class="p-button-text"
                    @click="deleteFormVisible = false"
                />
                <Button
                    label="Oui"
                    icon="pi pi-check"
                    class="p-button-text"
                    @click="deleteAnnee"
                />
            </template>
        </Dialog>
    </div>
</template>
<script>
import { FilterMatchMode, FilterOperator } from 'primevue/api';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Dropdown from 'primevue/dropdown';
import Calendar from 'primevue/calendar';
import SplitButton from 'primevue/splitbutton';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import Form from '@/primevue/views/brillant/AnneesForm.vue';

import Breadcrumb from 'primevue/breadcrumb';

export default {
    components: {
        Button,
        InputText,
        DataTable,
        Column,
        Tag,
        Dropdown,
        Calendar,
        SplitButton,
        Dialog,
        Toast,
        Form,
        Breadcrumb,
    },
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            anneesFormation: [],
            selectedAnnees: null,
            filters: null,
            statuts: ['قادمة', 'منقضية', 'حالية'],
            actions: [
                {
                    label: 'Modifier',
                    icon: 'pi pi-pencil',
                    command: () => this.editSelectedAnnee(),
                },
                {
                    label: 'Supprimer',
                    icon: 'pi pi-trash',
                    command: () => this.deleteSelectedAnnees(),
                },
            ],
            formVisible: false,
            deleteFormVisible: false,
            anneeToEdit: null,
            anneeToDelete: null,
            loading: true,
            newAnneeFormation: {
                intitule: '',
                date_debut: '',
                date_fin: '',
                statut: '',
            },
            errors: {
                intitule: '',
                date_debut: '',
                date_fin: '',
                statut: '',
            },
            isSaving: false,
        };
    },
    computed: {
        totalAnnees() {
            return this.anneesFormation.length;
        },
        nouvellesAnnees() {
            return this.anneesFormation.filter((a) => a.statut === 'قادمة')
                .length;
        },
    },
    created() {
        this.initFilters();
        this.fetchAnneesFormation();
    },
    methods: {
        formatDate(date) {
            if (!date) return null;

            if (typeof date === 'string') {
                date = new Date(date);
            }

            if (!(date instanceof Date) || isNaN(date.getTime())) {
                return null;
            }

            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        },
        clearFilter() {
            this.initFilters();
        },
        initFilters() {
            this.filters = {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                intitule: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
                date_debut: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.DATE_IS },
                    ],
                },
                date_fin: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.DATE_IS },
                    ],
                },
                statut: {
                    operator: FilterOperator.OR,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.EQUALS },
                    ],
                },
            };
        },
        getSeverity(statut) {
            switch (statut) {
                case 'قادمة':
                    return 'success'; // Vert
                case 'منقضية':
                    return 'danger'; // Rouge
                case 'حالية':
                    return 'warning'; // Orange
                default:
                    return null; // Aucune couleur par défaut
            }
        },
        openForm() {
            // Réinitialiser le formulaire avant d'ouvrir la Form
            this.newAnneeFormation = {
                intitule: '',
                date_debut: '',
                date_fin: '',
                statut: '',
            };
            this.anneeToEdit = null; // S'assurer qu'aucune année n'est en mode édition
            this.formVisible = true;
        },
        closeForm() {
            this.formVisible = false;
            this.anneeToEdit = null;
            this.fetchAnneesFormation();
        },
        async fetchAnneesFormation() {
            this.loading = true;
            try {
                const response = await axios.get('/api/annees-formation');
                this.anneesFormation = response.data.map((annee) => ({
                    ...annee,
                    date_debut: new Date(annee.date_debut),
                    date_fin: new Date(annee.date_fin),
                }));
            } catch (error) {
                console.error(
                    'Erreur lors du chargement des années de formation:',
                    error
                );
            } finally {
                this.loading = false;
            }
        },
        editAnnee(annee) {
            this.anneeToEdit = annee;
            this.newAnneeFormation = { ...annee }; // Remplir le formulaire avec les données de l'année à éditer
            this.formVisible = true;
        },
        editSelectedAnnee() {
            if (this.selectedAnnees && this.selectedAnnees.length === 1) {
                this.editAnnee(this.selectedAnnees[0]);
            } else {
                this.toast.add({
                    severity: 'warn',
                    summary: 'Aucune sélection',
                    detail: 'Veuillez sélectionner une année de formation à modifier.',
                    life: 3000,
                });
            }
        },
        async handleSaveAnnee(newAnnee) {
            try {
                const response = await axios.post(
                    '/api/annees-formation',
                    newAnnee
                );
                this.anneesFormation.unshift(response.data);
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Année ajoutée avec succès.',
                    life: 3000,
                });
            } catch (error) {
                if (error.response && error.response.status === 422) {
                    const errors = error.response.data.errors;
                    for (const key in errors) {
                        this.toast.add({
                            severity: 'error',
                            summary: 'Erreur de validation',
                            detail: errors[key][0],
                            life: 3000,
                        });
                    }
                } else {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: "Erreur lors de l'ajout de l'année.",
                        life: 3000,
                    });
                }
            } finally {
                this.closeForm();
            }
        },
        async handleUpdateAnnee(updatedAnnee) {
            try {
                const formattedAnnee = {
                    ...updatedAnnee,
                    date_debut: this.formatDate(updatedAnnee.date_debut),
                    date_fin: this.formatDate(updatedAnnee.date_fin),
                };

                const response = await axios.put(
                    `/api/annees-formation/${updatedAnnee.id}`,
                    formattedAnnee
                );

                const index = this.anneesFormation.findIndex(
                    (a) => a.id === updatedAnnee.id
                );
                if (index !== -1) {
                    this.anneesFormation.splice(index, 1, response.data);
                }

                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Année de formation modifiée avec succès.',
                    life: 3000,
                });
            } catch (error) {
                if (error.response && error.response.status === 422) {
                    const errors = error.response.data.errors;
                    for (const key in errors) {
                        this.toast.add({
                            severity: 'error',
                            summary: 'Erreur de validation',
                            detail: errors[key][0],
                            life: 3000,
                        });
                    }
                } else {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: "Erreur lors de la modification de l'année de formation.",
                        life: 3000,
                    });
                }
            } finally {
                this.closeForm();
            }
        },
        async deleteAnnee() {
            if (this.anneeToDelete) {
                try {
                    await axios.delete(
                        `/api/annees-formation/${this.anneeToDelete.id}`
                    );
                    this.anneesFormation = this.anneesFormation.filter(
                        (a) => a.id !== this.anneeToDelete.id
                    );
                    this.toast.add({
                        severity: 'success',
                        summary: 'Supprimé',
                        detail: 'Année de formation supprimée avec succès.',
                        life: 3000,
                    });
                } catch (error) {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: "Impossible de supprimer l'année de formation.",
                        life: 3000,
                    });
                } finally {
                    this.deleteFormVisible = false;
                    this.anneeToDelete = null;
                }
            }
        },
        deleteSelectedAnnees() {
            if (this.selectedAnnees && this.selectedAnnees.length > 0) {
                this.anneesFormation = this.anneesFormation.filter(
                    (a) => !this.selectedAnnees.includes(a)
                );
                this.toast.add({
                    severity: 'success',
                    summary: 'Supprimé',
                    detail: 'Années de formation supprimées avec succès.',
                    life: 3000,
                });
                this.selectedAnnees = null;
            } else {
                this.toast.add({
                    severity: 'warn',
                    summary: 'Aucune sélection',
                    detail: 'Veuillez sélectionner une année de formation à supprimer.',
                    life: 3000,
                });
            }
        },
        confirmDeleteAnnee(annee) {
            this.anneeToDelete = annee;
            this.deleteFormVisible = true;
        },
    },
};
</script>

<style scoped>
.arabic-text {
    font-family: 'Noto Naskh Arabic', sans-serif; /* Police utilisée */
    font-size: 1em !important; /* Réduction de la taille de police */
    font-weight: normal !important; /* Suppression du gras */
}
</style>
