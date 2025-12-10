<template>
    <div>
        <!-- Start Table -->
        <div class="card">
            <DataTable
                v-model:filters="filters"
                showGridlines
                stripedRows
                v-model:selection="selectedSousSecteurs"
                :value="sousSecteurs"
                dataKey="id"
                size="small"
                paginator
                :rows="10"
                filterDisplay="menu"
                :globalFilterFields="['code', 'nom_fr', 'nom_ar', 'secteur_id']"
                :loading="loading"
            >
                <template #header>
                    <div
                        class="flex justify-content-between mb-2 align-items-center"
                    >
                        <!-- Section gauche -->
                        <div class="flex align-items-center">
                            <Button
                                icon="pi pi-plus"
                                severity="success"
                                size="small"
                                class="mr-2"
                                @click="openAddSousSecteurPopup"
                            />
                            <span class="p-input-icon-left mr-2">
                                <i class="pi pi-search" />
                                <InputText
                                    v-model="filters['global'].value"
                                    size="small"
                                    placeholder="Recherche..."
                                    style="width: 100%"
                                />
                            </span>
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
                            <Button
                                icon="pi pi-file-import"
                                severity="info"
                                size="small"
                                class="mr-2"
                                label="Import XLS"
                                @click="importXLS"
                            />
                            <Button
                                icon="pi pi-file-export"
                                severity="info"
                                size="small"
                                class="mr-2"
                                label="Export XLS"
                                @click="exportXLS"
                            />
                            <SplitButton
                                label="Actions"
                                icon="pi pi-cog"
                                size="small"
                                :model="actions"
                            />
                        </div>
                    </div>
                </template>
                <template #empty>
                    <div v-if="!loading">Aucun sous-secteur trouvé.</div>
                    <div v-else>Chargement en cours...</div>
                </template>
                <Column
                    selectionMode="multiple"
                    headerStyle="width: 3rem"
                ></Column>
                <Column
                    field="code"
                    header="Code"
                    sortable
                    style="min-width: 10rem"
                >
                    <template #body="{ data }">
                        {{ data.code }}
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter"
                            placeholder="Rechercher par code"
                            @input="filterCallback"
                        />
                    </template>
                </Column>
                <Column
                    field="nom_fr"
                    header="Nom (FR)"
                    sortable
                    style="min-width: 14rem"
                >
                    <template #body="{ data }">
                        {{ data.nom_fr }}
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter"
                            placeholder="Rechercher par nom (FR)"
                            @input="filterCallback"
                        />
                    </template>
                </Column>
                <Column
                    field="nom_ar"
                    header="Nom (AR)"
                    sortable
                    style="min-width: 14rem"
                >
                    <template #body="{ data }">
                        {{ data.nom_ar }}
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter"
                            placeholder="Rechercher par nom (AR)"
                            @input="filterCallback"
                        />
                    </template>
                </Column>
                <Column
                    field="secteur_id"
                    header="Secteur"
                    sortable
                    style="min-width: 12rem"
                >
                    <template #body="{ data }">
                        {{ data.secteur ? data.secteur.nom_fr : 'N/A' }}
                    </template>
                </Column>
                <Column header="Actions" style="min-width: 10rem">
                    <template #body="{ data }">
                        <Button
                            icon="pi pi-pencil"
                            class="p-button-rounded p-button-text p-button-success mr-2"
                            @click="editSousSecteur(data)"
                        />
                        <Button
                            icon="pi pi-trash"
                            class="p-button-rounded p-button-text p-button-danger"
                            @click="confirmDeleteSousSecteur(data)"
                        />
                    </template>
                </Column>
            </DataTable>
        </div>
        <!-- End Table -->

        <!-- Add/Edit SousSecteur Popup -->
        <AddSousSecteur
            :visible="addSousSecteurPopupVisible"
            :sousSecteurToEdit="sousSecteurToEdit"
            @update:visible="addSousSecteurPopupVisible = $event"
            @save="handleSaveSousSecteur"
            @update="handleUpdateSousSecteur"
            @close="closeAddSousSecteurPopup"
        />

        <!-- Delete Confirmation Popup -->
        <Dialog
            v-model:visible="deletePopupVisible"
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
                    >Êtes-vous sûr de vouloir supprimer ce sous-secteur ?</span
                >
            </div>
            <template #footer>
                <Button
                    label="Non"
                    icon="pi pi-times"
                    class="p-button-text"
                    @click="deletePopupVisible = false"
                />
                <Button
                    label="Oui"
                    icon="pi pi-check"
                    class="p-button-text"
                    @click="deleteSousSecteur"
                />
            </template>
        </Dialog>

        <!-- Toast Component -->
        <Toast />
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
import SplitButton from 'primevue/splitbutton';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import AddSousSecteur from '@/Popup/AddSousSecteur.vue';
import * as XLSX from 'xlsx';

export default {
    components: {
        Button,
        InputText,
        DataTable,
        Column,
        Tag,
        Dropdown,
        SplitButton,
        Dialog,
        Toast,
        AddSousSecteur,
    },
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            sousSecteurs: [],
            selectedSousSecteurs: null,
            filters: null,
            actions: [
                {
                    label: 'Modifier',
                    icon: 'pi pi-pencil',
                    command: () => this.editSelectedSousSecteur(),
                },
                {
                    label: 'Supprimer',
                    icon: 'pi pi-trash',
                    command: () => this.deleteSelectedSousSecteurs(),
                },
            ],
            addSousSecteurPopupVisible: false,
            deletePopupVisible: false,
            sousSecteurToEdit: null,
            sousSecteurToDelete: null,
            loading: true,
        };
    },
    created() {
        this.initFilters();
        this.fetchSousSecteurs();
    },
    methods: {
        clearFilter() {
            this.initFilters();
        },
        initFilters() {
            this.filters = {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                code: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
                nom_fr: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
                nom_ar: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
            };
        },
        openAddSousSecteurPopup() {
            this.sousSecteurToEdit = null;
            this.addSousSecteurPopupVisible = true;
        },
        closeAddSousSecteurPopup() {
            this.addSousSecteurPopupVisible = false;
            this.sousSecteurToEdit = null;
        },
        async fetchSousSecteurs() {
            this.loading = true;
            try {
                const response = await axios.get('/api/sous-secteurs');
                this.sousSecteurs = response.data;
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Échec du chargement des sous-secteurs.',
                    life: 3000,
                });
            } finally {
                this.loading = false;
            }
        },
        editSousSecteur(sousSecteur) {
            this.sousSecteurToEdit = sousSecteur;
            this.addSousSecteurPopupVisible = true;
        },
        editSelectedSousSecteur() {
            if (
                this.selectedSousSecteurs &&
                this.selectedSousSecteurs.length === 1
            ) {
                this.editSousSecteur(this.selectedSousSecteurs[0]);
            } else {
                this.toast.add({
                    severity: 'warn',
                    summary: 'Aucune sélection',
                    detail: 'Veuillez sélectionner un seul sous-secteur à modifier.',
                    life: 3000,
                });
            }
        },
        async handleSaveSousSecteur(newSousSecteur) {
            try {
                // Envoyer la requête POST pour ajouter un nouveau sous-secteur
                const response = await axios.post(
                    '/api/sous-secteurs',
                    newSousSecteur
                );

                // Ajouter le nouveau sous-secteur au tableau local
                this.sousSecteurs.push(response.data);

                // Afficher un message de succès
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Le sous-secteur a été ajouté avec succès.',
                    life: 3000,
                });

                // Recharger les données depuis l'API pour s'assurer que le tableau est à jour
                await this.fetchSousSecteurs();
            } catch (error) {
                // Afficher un message d'erreur en cas d'échec
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: "Échec de l'ajout du sous-secteur.",
                    life: 3000,
                });

                // Afficher l'erreur dans la console pour le débogage
                console.error(
                    "Erreur lors de l'ajout du sous-secteur :",
                    error
                );
            } finally {
                // Fermer le popup d'ajout
                this.closeAddSousSecteurPopup();
            }
        },
        async handleUpdateSousSecteur(updatedSousSecteur) {
            try {
                const response = await axios.put(
                    `/api/sous-secteurs/${updatedSousSecteur.id}`,
                    updatedSousSecteur
                );
                const index = this.sousSecteurs.findIndex(
                    (s) => s.id === updatedSousSecteur.id
                );
                if (index !== -1) {
                    this.sousSecteurs.splice(index, 1, response.data);
                }
                this.toast.add({
                    severity: 'info',
                    summary: 'Succès',
                    detail: 'Le sous-secteur a été modifié avec succès.',
                    life: 3000,
                });
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Échec de la modification du sous-secteur.',
                    life: 3000,
                });
            } finally {
                this.closeAddSousSecteurPopup();
            }
        },
        confirmDeleteSousSecteur(sousSecteur) {
            this.sousSecteurToDelete = sousSecteur;
            this.deletePopupVisible = true;
        },
        async deleteSousSecteur() {
            if (this.sousSecteurToDelete) {
                try {
                    await axios.delete(
                        `/api/sous-secteurs/${this.sousSecteurToDelete.id}`
                    );
                    this.sousSecteurs = this.sousSecteurs.filter(
                        (s) => s.id !== this.sousSecteurToDelete.id
                    );
                    this.toast.add({
                        severity: 'error',
                        summary: 'Supprimé',
                        detail: 'Le sous-secteur a été supprimé avec succès.',
                        life: 3000,
                    });
                } catch (error) {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'Échec de la suppression du sous-secteur.',
                        life: 3000,
                    });
                } finally {
                    this.deletePopupVisible = false;
                    this.sousSecteurToDelete = null;
                }
            }
        },
        async deleteSelectedSousSecteurs() {
            if (
                !this.selectedSousSecteurs ||
                this.selectedSousSecteurs.length === 0
            ) {
                this.toast.add({
                    severity: 'warn',
                    summary: 'Aucune sélection',
                    detail: 'Veuillez sélectionner au moins un sous-secteur à supprimer.',
                    life: 3000,
                });
                return;
            }

            try {
                await Promise.all(
                    this.selectedSousSecteurs.map((sousSecteur) =>
                        axios.delete(`/api/sous-secteurs/${sousSecteur.id}`)
                    )
                );

                this.sousSecteurs = this.sousSecteurs.filter(
                    (s) => !this.selectedSousSecteurs.includes(s)
                );
                this.toast.add({
                    severity: 'success',
                    summary: 'Supprimé',
                    detail: 'Les sous-secteurs sélectionnés ont été supprimés avec succès.',
                    life: 3000,
                });
                this.selectedSousSecteurs = null;
            } catch (error) {
                console.error(
                    'Erreur lors de la suppression des sous-secteurs:',
                    error
                );
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: "Une erreur s'est produite lors de la suppression des sous-secteurs.",
                    life: 3000,
                });
            }
        },
        importXLS() {
            this.toast.add({
                severity: 'info',
                summary: 'Imports XLS',
                detail: "La fonctionnalité d'import XLS est en cours de développement.",
                life: 3000,
            });
        },
        exportXLS() {
            try {
                const data = this.sousSecteurs.map((sousSecteur) => ({
                    Code: sousSecteur.code,
                    'Nom (FR)': sousSecteur.nom_fr,
                    'Nom (AR)': sousSecteur.nom_ar,
                    Secteur: sousSecteur.secteur
                        ? sousSecteur.secteur.nom_fr
                        : 'N/A',
                }));

                const worksheet = XLSX.utils.json_to_sheet(data);
                const workbook = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(
                    workbook,
                    worksheet,
                    'SousSecteurs'
                );
                XLSX.writeFile(workbook, 'sous-secteurs.xlsx');

                this.toast.add({
                    severity: 'success',
                    summary: 'Export XLS',
                    detail: "L'exportation des sous-secteurs a été réalisée avec succès.",
                    life: 3000,
                });
            } catch (error) {
                console.error("Erreur lors de l'export XLS :", error);
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: "Une erreur s'est produite lors de l'export XLS.",
                    life: 3000,
                });
            }
        },
    },
};
</script>
