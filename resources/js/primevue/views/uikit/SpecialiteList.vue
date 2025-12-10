<template>
    <div>
        <!-- Start Table -->
        <div class="card" style="overflow-x: auto">
            <!-- Tabs pour filtrer les spécialités -->
            <div class="flex mb-3">
                <Button
                    label="مقيس"
                    :severity="activeTab === 'مقيس' ? 'primary' : 'secondary'"
                    class="mr-2 arabic-text"
                    @click="setActiveTab('مقيس')"
                />
                <Button
                    label="غير مقيس"
                    :severity="activeTab === 'autres' ? 'primary' : 'secondary'"
                    class="arabic-text"
                    @click="setActiveTab('autres')"
                />
            </div>
            <DataTable
                v-model:filters="filters"
                showGridlines
                stripedRows
                v-model:selection="selectedSpecialites"
                :frozenValue="FixLignes"
                size="small"
                :value="filteredSpecialites"
                paginator
                :rows="10"
                dataKey="id"
                filterDisplay="menu"
                :globalFilterFields="[
                    'id',
                    'code',
                    'nom_ar',
                    'nom_fr',
                    'type',
                    'homologation',
                    'duree_formation',
                    'diplôme',
                    'statut',
                ]"
                :loading="loading"
                scrollable
                scrollHeight="flex"
                :pt="{
                    table: { style: 'min-width: 50rem' },
                    bodyrow: ({ props }) => ({
                        class: [{ 'font-bold': props.frozenRow }],
                    }),
                }"
            >
                <!-- Header Section -->
                <template #header>
                    <div
                        class="flex justify-content-between mb-2 align-items-center"
                    >
                        <!-- Section gauche -->
                        <div class="flex align-items-center">
                            <!-- Bouton pour ouvrir la sidebar -->
                            <Button
                                icon="pi pi-plus"
                                severity="success"
                                size="small"
                                class="mr-2"
                                @click="openAddSpecialitePopup"
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
                            <!-- Bouton Effacer avec icône "X" -->
                            <Button
                                type="button"
                                icon="pi pi-times"
                                size="small"
                                class="mr-2"
                                severity="danger"
                                outlined
                                @click="clearFilter"
                            />
                            <!-- Bouton Figer avec Dropdown -->
                            <Button
                                type="button"
                                icon="pi pi-lock"
                                size="small"
                                class="mr-2"
                                label="Figer"
                                @click="toggleFreezeDropdown"
                            />
                            <Dropdown
                                v-model="selectedColumnToFreeze"
                                :options="columnOptions"
                                optionLabel="header"
                                placeholder="Sélectionner une colonne"
                                class="mr-2"
                                v-if="showFreezeDropdown"
                                @change="freezeColumn"
                            >
                                <template #option="slotProps">
                                    <div>
                                        <span
                                            v-if="slotProps.option.isFrozen"
                                            class="frozen-indicator"
                                            >✔️</span
                                        >
                                        {{ slotProps.option.header }}
                                    </div>
                                </template>
                            </Dropdown>
                        </div>
                        <!-- Section droite -->
                        <div class="flex align-items-center">
                            <!-- Bouton Associer -->
                            <Button
                                icon="pi pi-link"
                                severity="info"
                                size="small"
                                class="mr-2"
                                @click="openAssociateSecteurPopup"
                                :disabled="
                                    !selectedSpecialites ||
                                    selectedSpecialites.length === 0
                                "
                            />
                            <!-- Bouton pour accéder à la page des erreurs d'importation -->
                            <Button
                                icon="pi pi-file-import"
                                severity="success"
                                size="small"
                                class="mr-2"
                                label="Import XLS"
                                @click="importXLS"
                            />
                            <!-- Bouton pour afficher les erreurs d'importation -->
                            <Button
                                v-if="errors.length > 0"
                                icon="pi pi-exclamation-triangle"
                                severity="warning"
                                size="small"
                                class="mr-2"
                                label="Erreur Import"
                                @click="showImportErrorPopup = true"
                            />
                            <!-- Bouton Export XLS -->
                            <Button
                                icon="pi pi-file-export"
                                severity="info"
                                size="small"
                                class="mr-2"
                                label="Export XLS"
                                @click="exportXLS"
                            />

                            <!-- Bouton Actions -->
                            <SplitButton
                                label="Actions"
                                icon="pi pi-cog"
                                size="small"
                                :model="actions"
                            />
                        </div>
                    </div>
                </template>
                <!-- Empty Template -->
                <template #empty>
                    <div v-if="!loading">Aucune spécialité trouvée.</div>
                    <div v-else>Chargement en cours...</div>
                </template>
                <!-- Columns -->
                <Column
                    selectionMode="multiple"
                    headerStyle="width: 3rem"
                    frozen
                    class="font-bold"
                ></Column>
                <!-- Colonne id -->
                <Column
                    field="id"
                    header="ID"
                    sortable
                    style="min-width: 5rem"
                    :frozen="frozenColumns.includes('id')"
                >
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter"
                            placeholder="Rechercher par id"
                            @input="filterCallback"
                        />
                    </template>
                </Column>
                <!-- Colonne Code -->
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
                <!-- Colonne Nom Secteur (AR) -->
                <Column
                    field="nom_secteur_ar"
                    header="Secteur"
                    sortable
                    style="min-width: 12rem"
                >
                    <template #body="{ data }">
                        {{ getSecteurNomAr(data.secteur_id) }}
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
                <!-- Colonne Nom Sous-Secteur (AR) -->
                <Column
                    field="nom_sous_secteur_ar"
                    header="Sous Secteur"
                    sortable
                    style="min-width: 12rem"
                >
                    <template #body="{ data }">
                        {{ getSousSecteurNomAr(data.sous_secteur_id) }}
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
                    header="Type"
                    field="type"
                    sortable
                    :filterMenuStyle="{ width: '10rem' }"
                    style="min-width: 12rem"
                >
                    <template #body="{ data }">
                        <div class="centered-content">
                            <Tag
                                :value="data.type"
                                :class="getSeverity(data.type)"
                                class="fixed-width-tag arabic-text"
                                style="color: #ffffff !important"
                            />
                        </div>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-model="filterModel.value"
                            :options="types"
                            placeholder="Sélectionner un type"
                            class="p-column-filter arabic-text"
                            showClear
                            @change="filterCallback"
                        >
                            <template #option="slotProps">
                                <Tag
                                    :value="slotProps.option"
                                    :severity="getSeverity(slotProps.option)"
                                    class="arabic-text fixed-width-tag"
                                    style="color: #ffffff !important"
                                />
                            </template>
                        </Dropdown>
                    </template>
                </Column>

                <Column
                    header="statut"
                    field="Statut"
                    sortable
                    :filterMenuStyle="{ width: '10rem' }"
                    style="min-width: 12rem"
                >
                    <template #body="{ data }">
                        <div class="centered-content">
                            <Tag
                                :value="data.statut"
                                :class="getSeverity(data.statut)"
                                class="fixed-width-tag arabic-text"
                                style="color: #ffffff !important"
                            />
                        </div>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-model="filterModel.value"
                            :options="statuts"
                            placeholder="Sélectionner un niveau"
                            class="p-column-filter arabic-text"
                            showClear
                            @change="filterCallback"
                        >
                            <template #option="slotProps">
                                <Tag
                                    :value="slotProps.option"
                                    :severity="getSeverity(slotProps.option)"
                                    class="arabic-text fixed-width-tag"
                                    style="color: #ffffff !important"
                                />
                            </template>
                        </Dropdown>
                    </template>
                </Column>

                <Column
                    field="observation"
                    header="Observation"
                    sortable
                    style="min-width: 14rem"
                >
                    <template #body="{ data }">
                        {{ data.observation }}
                    </template>
                </Column>

                <!-- Colonne Actions -->
                <Column header="Actions" style="min-width: 14rem">
                    <template #body="{ data, frozenRow, index }">
                        <Button
                            type="button"
                            :icon="frozenRow ? 'pi pi-lock-open' : 'pi pi-lock'"
                            :disabled="
                                frozenRow ? false : FixLignes.length >= 10
                            "
                            text
                            size="small"
                            @click="toggleLock(data, frozenRow, index)"
                        />
                        <Button
                            icon="pi pi-pencil"
                            class="p-button-rounded p-button-text p-button-success mr-2"
                            @click="editSpecialite(data)"
                        />
                        <Button
                            icon="pi pi-trash"
                            class="p-button-rounded p-button-text p-button-danger"
                            @click="confirmDeleteSpecialite(data)"
                        />
                    </template>
                </Column>
            </DataTable>
        </div>
        <!-- End Table -->
        <!-- Sidebar pour ajouter/modifier une spécialité -->
        <AddSpecialite
            :visible="addSpecialitePopupVisible"
            :specialiteToEdit="specialiteToEdit"
            @update:visible="addSpecialitePopupVisible = $event"
            @save="handleSaveSpecialite"
            @update="handleUpdateSpecialite"
        />
        <!-- Popup de confirmation de suppression -->
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
                    >Êtes-vous sûr de vouloir supprimer cette spécialité ?</span
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
                    @click="deleteSpecialite"
                />
            </template>
        </Dialog>

        <!-- Popup pour associer les spécialités à un secteur -->
        <Dialog
            v-model:visible="associateSecteurPopupVisible"
            :style="{ width: '30vw' }"
            header="Associer à un secteur"
            :modal="true"
        >
            <div>
                <Dropdown
                    v-model="selectedSecteur"
                    :options="secteurs"
                    optionLabel="nom_fr"
                    optionValue="id"
                    placeholder="Sélectionner un secteur"
                    class="w-full mb-3"
                />
                <div class="flex justify-content-end">
                    <Button
                        label="Annuler"
                        icon="pi pi-times"
                        class="p-button-text"
                        @click="associateSecteurPopupVisible = false"
                    />
                    <Button
                        label="Associer"
                        icon="pi pi-check"
                        class="p-button-success"
                        @click="associateSpecialitesToSecteur"
                    />
                </div>
                <!-- Popup pour afficher les erreurs d'importation -->
                <!-- Popup d'erreurs -->
                <ImportError
                    :errors="errors"
                    :visible="showImportErrorPopup"
                    @update:visible="showImportErrorPopup = $event"
                    @line-imported="handleLineImported"
                    @close="refreshTable"
                />
            </div>
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
import Dropdown from 'primevue/dropdown';
import Calendar from 'primevue/calendar';
import SplitButton from 'primevue/splitbutton';
import Dialog from 'primevue/dialog';
import AddSpecialite from '@/Popup/AddSpecialite.vue';
import { utils, writeFile } from 'xlsx';
import ImportError from '@/Popup/ImportError.vue';
import Tag from 'primevue/tag';
import Toast from 'primevue/toast';
import * as XLSX from 'xlsx';
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
        AddSpecialite,
        ImportError,
    },
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            FixLignes: [],
            specialites: [],
            selectedSpecialites: null,
            filters: null,
            types: ['مقيس', 'غير مقيس'],
            homologations: ['منظر', 'غير منظر'],
            diplômes: [
                'شهادة مؤهل تقني سامي',
                'شهادة مؤهل تقني مهني',
                'شهادة الكفاءة المهنية',
                'شهادة مهارة',
                'شهادة إنهاء التدريب',
            ],
            statuts: ['نشط', 'غير نشط', 'ملغى'],
            actions: [
                {
                    label: 'Modifier',
                    icon: 'pi pi-pencil',
                    command: () => this.editSelectedSpecialite(),
                },
                {
                    label: 'Supprimer',
                    icon: 'pi pi-trash',
                    command: () => this.deleteSelectedSpecialites(),
                },
            ],
            addSpecialitePopupVisible: false,
            deletePopupVisible: false,
            associateSecteurPopupVisible: false,
            specialiteToEdit: null,
            specialiteToDelete: null,
            loading: true,
            showImportErrorPopup: false,
            frozenColumns: [],
            activeTab: 'مقيس', // Onglet actif par défaut
            secteurs: [], // Liste des secteurs
            sousSecteurs: [], // Liste des sous-secteurs
            selectedSecteur: null, // Secteur sélectionné
            errors: [],
        };
    },
    created() {
        this.initFilters();
        this.fetchSpecialites();
        this.fetchSecteurs();
        this.fetchSousSecteurs();
    },
    computed: {
        columnOptions() {
            // Ajouter les colonnes Code, Secteur et Sous Secteur à la liste des options
            return [
                {
                    field: 'code',
                    header: 'Code',
                    isFrozen: this.frozenColumns.includes('code'),
                },
                {
                    field: 'nom_secteur_ar',
                    header: 'Secteur',
                    isFrozen: this.frozenColumns.includes('nom_secteur_ar'),
                },
                {
                    field: 'nom_sous_secteur_ar',
                    header: 'Sous Secteur',
                    isFrozen: this.frozenColumns.includes(
                        'nom_sous_secteur_ar'
                    ),
                },
                ...this.columns.map((col) => ({
                    field: col.field,
                    header: col.header,
                    isFrozen: this.frozenColumns.includes(col.field),
                })),
            ];
        },
        // Filtre les spécialités en fonction de l'onglet actif
        filteredSpecialites() {
            if (this.activeTab === 'مقيس') {
                return this.specialites.filter((s) => s.type === 'مقيس');
            } else {
                return this.specialites.filter((s) => s.type === 'غير مقيس');
            }
        },
    },

    methods: {
        // Définir l'onglet actif
        setActiveTab(tab) {
            this.activeTab = tab;
        },
        clearFilter() {
            this.initFilters();
        },
        initFilters() {
            this.filters = {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                id: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
                code: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
                nom_secteur_ar: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
                nom_ar: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.CONTAINS },
                    ],
                },
                nom_fr: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.CONTAINS },
                    ],
                },
                type: {
                    operator: FilterOperator.OR,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.EQUALS },
                    ],
                },
                homologation: {
                    operator: FilterOperator.OR,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.EQUALS },
                    ],
                },
                duree_formation: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.EQUALS },
                    ],
                },
                diplôme: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.CONTAINS },
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

        handleLineImported(importedLine) {
            // Supprimer la ligne importée de la liste des erreurs
            this.errors = this.errors.filter(
                (err) => err.line !== importedLine.line
            );
        },

        async refreshTable() {
            this.loading = true;
            try {
                const response = await axios.get('/api/specialites');
                this.specialites = response.data;
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Échec du chargement des secteurs.',
                    life: 3000,
                });
            } finally {
                this.loading = false;
            }
        },

        getSeverity(type) {
            switch (type) {
                // Liste "types"
                case 'مقيس':
                    return 'status-success'; // Vert
                case 'غير مقيس':
                    return 'status-danger'; // Rouge

                // Liste "homologations"
                case 'منظر':
                    return 'status-info'; // Bleu
                case 'غير منظر':
                    return 'status-warning'; // Jaune

                // Liste "diplômes"
                case 'شهادة مؤهل تقني سامي':
                    return 'status-primary'; // Bleu primaire
                case 'شهادة مؤهل تقني مهني':
                    return 'status-secondary'; // Gris
                case 'شهادة الكفاءة المهنية':
                    return 'status-light'; // Bleu clair
                case 'شهادة مهارة':
                    return 'status-warning'; // Jaune
                case 'شهادة إنهاء التدريب':
                    return 'status-danger'; // Rouge

                // Liste "statuts"
                case 'نشط':
                    return 'status-success'; // Vert
                case 'غير نشط':
                    return 'status-warning'; // Jaune
                case 'ملغى':
                    return 'status-danger'; // Rouge

                // Valeur par défaut si aucune correspondance
                default:
                    return null;
            }
        },
        openAddSpecialitePopup() {
            this.specialiteToEdit = null;
            this.addSpecialitePopupVisible = true;
        },
        closeAddSpecialitePopup() {
            this.addSpecialitePopupVisible = false;
            this.specialiteToEdit = null;
        },
        async fetchSpecialites() {
            this.loading = true;
            try {
                const response = await axios.get('/api/specialites');
                console.log("Données reçues de l'API :", response.data); // Ajoutez cette ligne
                this.specialites = response.data.map((specialite) => ({
                    ...specialite,
                    date_creation: new Date(specialite.date_creation), // Transformation de la date de création
                    date_modification: new Date(specialite.date_modification), // Transformation de la date de modification, si elle existe
                }));
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Impossible de charger les spécialités.',
                    life: 3000,
                });
            } finally {
                this.loading = false;
            }
        },
        async fetchSecteurs() {
            try {
                const response = await axios.get('/api/secteurs');
                console.log("Données reçues de l'API :", response.data); // Ajoutez cette ligne
                this.secteurs = response.data;
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Impossible de charger les secteurs.',
                    life: 3000,
                });
            }
        },
        async fetchSousSecteurs() {
            try {
                const response = await axios.get('/api/sous-secteurs');
                console.log("Données reçues de l'API :", response.data); // Ajoutez cette ligne
                this.sousSecteurs = response.data;
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Impossible de charger les sous-secteurs.',
                    life: 3000,
                });
            }
        },
        getSecteurNomAr(secteurId) {
            if (!this.secteurs || !Array.isArray(this.secteurs)) {
                return 'Non défini';
            }
            const secteur = this.secteurs.find((s) => s.id === secteurId);
            return secteur ? secteur.nom_ar : 'Non défini';
        },

        getSousSecteurNomAr(SousSecteurId) {
            if (!this.sousSecteurs || !Array.isArray(this.sousSecteurs)) {
                return 'Non défini';
            }
            const sousSecteur = this.sousSecteurs.find(
                (s) => s.id === SousSecteurId
            );
            return sousSecteur ? sousSecteur.nom_ar : 'Non défini';
        },

        editSpecialite(specialite) {
            this.specialiteToEdit = specialite;
            this.addSpecialitePopupVisible = true;
        },
        editSelectedSpecialite() {
            if (
                this.selectedSpecialites &&
                this.selectedSpecialites.length === 1
            ) {
                this.editSpecialite(this.selectedSpecialites[0]);
            } else {
                this.toast.add({
                    severity: 'warn',
                    summary: 'Aucune sélection',
                    detail: 'Veuillez sélectionner une spécialité à modifier.',
                    life: 3000,
                });
            }
        },

        async handleUpdateSpecialite(updatedSpecialite) {
            try {
                const response = await axios.put(
                    `/api/specialites/${updatedSpecialite.id}`,
                    updatedSpecialite
                );
                const index = this.specialites.findIndex(
                    (s) => s.id === updatedSpecialite.id
                );
                if (index !== -1) {
                    this.specialites.splice(index, 1, response.data);
                }
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'La spécialité à été modifié avec succès.',
                    life: 3000,
                });
            } catch (error) {
                console.error(
                    'Erreur lors de la modification du specialité :',
                    error
                );
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Échec de la modification du spécialité.',
                    life: 3000,
                });
            } finally {
                this.closeAddSpecialitePopup();
            }
        },
        async deleteSpecialite() {
            if (this.specialiteToDelete) {
                try {
                    await axios.delete(
                        `/api/specialites/${this.specialiteToDelete.id}`
                    );
                    this.specialites = this.specialites.filter(
                        (s) => s.id !== this.specialiteToDelete.id
                    );
                    this.toast.add({
                        severity: 'success',
                        summary: 'Supprimé',
                        detail: 'Spécialité supprimée avec succès.',
                        life: 3000,
                    });
                } catch (error) {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'Impossible de supprimer la spécialité.',
                        life: 3000,
                    });
                } finally {
                    this.deletePopupVisible = false;
                    this.specialiteToDelete = null;
                }
            }
        },
        async deleteSelectedSecteurs() {
            if (
                this.selectedSpecialites &&
                this.selectedSpecialites.length > 0
            ) {
                try {
                    await axios.post('/api/specialites/delete-selected', {
                        ids: this.selectedSpecialites.map((s) => s.id),
                    });
                    this.specialites = this.specialites.filter(
                        (s) => !this.selectedSpecialites.includes(s)
                    );
                    this.toast.add({
                        severity: 'success',
                        summary: 'Supprimé',
                        detail: 'Les specialités sélectionnées ont été supprimées avec succès.',
                        life: 3000,
                    });
                    this.selectedSpecialites = null;
                } catch (error) {
                    console.error(
                        'Erreur lors de la suppression des specialités :',
                        error
                    );
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'Échec de la suppression des specialités sélectionnés.',
                        life: 3000,
                    });
                }
            } else {
                this.toast.add({
                    severity: 'warn',
                    summary: 'Aucune sélection',
                    detail: 'Veuillez sélectionner un ou plusieurs specialités à supprimer.',
                    life: 3000,
                });
            }
        },
        confirmDeleteSpecialite(specialite) {
            this.specialiteToDelete = specialite;
            this.deletePopupVisible = true;
        },

        async handleSaveSpecialite(newSpecialite) {
            try {
                const response = await axios.post(
                    '/api/specialites',
                    newSpecialite
                );
                this.specialites.push(response.data);
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Spécialité ajoutée avec succès.',
                    life: 3000,
                });
            } catch (error) {
                console.error(
                    'Erreur lors de la création de spécialité :',
                    error
                );
                if (error.response && error.response.status === 422) {
                    const validationErrors = error.response.data.errors;
                    for (const [key, value] of Object.entries(
                        validationErrors
                    )) {
                        this.toast.add({
                            severity: 'error',
                            summary: 'Erreur',
                            detail: `${key}: ${value[0]}`,
                            life: 5000,
                        });
                    }
                } else {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: "Erreur lors de l'ajout de la spécialité.",
                        life: 3000,
                    });
                }
            } finally {
                this.closeAddSpecialitePopup();
            }
        },

        toggleLock(data, frozen, index) {
            if (frozen) {
                this.FixLignes = this.FixLignes.filter((c, i) => i !== index);
                this.secteurs.push(data);
            } else {
                this.secteurs = this.secteurs.filter((c, i) => i !== index);
                this.FixLignes.push(data);
            }

            this.secteurs.sort((val1, val2) => {
                return val1.id < val2.id ? -1 : 1;
            });
        },
        bodyrow: ({ props }) => ({
            class: [{ 'font-bold': props.frozenRow }],
        }),

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

        openAssociateSecteurPopup() {
            if (
                this.selectedSpecialites &&
                this.selectedSpecialites.length > 0
            ) {
                this.associateSecteurPopupVisible = true;
            } else {
                this.toast.add({
                    severity: 'warn',
                    summary: 'Aucune sélection',
                    detail: 'Veuillez sélectionner au moins une spécialité.',
                    life: 3000,
                });
            }
        },
        async associateSpecialitesToSecteur() {
            if (!this.selectedSecteur) {
                this.toast.add({
                    severity: 'warn',
                    summary: 'Aucun secteur sélectionné',
                    detail: 'Veuillez sélectionner un secteur.',
                    life: 3000,
                });
                return;
            }

            try {
                const specialiteIds = this.selectedSpecialites.map((s) => s.id);
                const response = await axios.post(
                    '/api/associate-specialites',
                    {
                        specialite_ids: specialiteIds,
                        secteur_id: this.selectedSecteur,
                    }
                );

                if (response.data.error) {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: response.data.error,
                        life: 3000,
                    });
                } else {
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Spécialités associées avec succès.',
                        life: 3000,
                    });
                    this.associateSecteurPopupVisible = false;
                    this.selectedSpecialites = null;
                    this.fetchSpecialites(); // Rafraîchir la liste des spécialités
                }
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: "Erreur lors de l'association des spécialités.",
                    life: 3000,
                });
            }
        },

        async importXLS() {
            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.accept = '.xls,.xlsx';
            fileInput.style.display = 'none';

            fileInput.onchange = async (event) => {
                const file = event.target.files[0];
                if (!file) return;

                const formData = new FormData();
                formData.append('file', file);

                try {
                    const response = await axios.post(
                        '/api/specialites/importxls',
                        formData,
                        {
                            headers: {
                                'Content-Type': 'multipart/form-data',
                            },
                        }
                    );

                    console.log('Réponse du backend :', response.data); // Ajoutez ce log pour vérifier

                    if (response.data.success) {
                        this.toast.add({
                            severity: 'success',
                            summary: 'Import XLS',
                            detail:
                                response.data.message || 'Importation réussie.',
                            life: 10000,
                        });

                        if (
                            response.data.error_lines &&
                            response.data.error_lines.length > 0
                        ) {
                            this.errors = response.data.error_lines;
                            this.showImportErrorPopup = true;
                        }
                    } else {
                        this.toast.add({
                            severity: 'error',
                            summary: 'Erreur',
                            detail:
                                response.data.message ||
                                "Une erreur s'est produite lors de l'importation.",
                            life: 10000,
                        });

                        if (
                            response.data.error_lines &&
                            response.data.error_lines.length > 0
                        ) {
                            this.errors = response.data.error_lines;
                            this.showImportErrorPopup = true;
                        }
                    }
                } catch (error) {
                    console.error("Erreur lors de l'importation :", error);

                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: "Une erreur s'est produite lors de l'importation. Veuillez réessayer.",
                        life: 10000,
                    });
                } finally {
                    fileInput.remove();
                }
            };

            document.body.appendChild(fileInput);
            fileInput.click();
        },

        exportXLS() {
            try {
                const data = this.specialites.map((specialite) => ({
                    id: specialite.id,
                    code: specialite.code,
                    nom_secteur_ar: this.getSecteurNomAr(specialite.secteur_id),
                    nom_sous_secteur_ar: this.getSousSecteurNomAr(
                        specialite.sous_secteur_id
                    ),
                    nom_ar: specialite.nom_ar,
                    nom_fr: specialite.nom_fr,
                    type: specialite.type,
                    homologation: specialite.homologation,
                    date_arrete: this.formatDate(specialite.date_arrete),
                    num_journal_officiel: specialite.num_journal_officiel,
                    date_journal_officiel: this.formatDate(
                        specialite.date_journal_officiel
                    ),
                    duree_formation: specialite.duree_formation,
                    diplôme: specialite.diplôme,
                    heures_technique: specialite.heures_technique,
                    heures_generaux: specialite.heures_generaux,
                    heures_total: specialite.heures_total,
                    statut: specialite.statut,
                    observation: specialite.observation,
                }));

                const worksheet = XLSX.utils.json_to_sheet(data);
                const workbook = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(
                    workbook,
                    worksheet,
                    'Specialites'
                );
                XLSX.writeFile(workbook, 'specialites.xlsx');

                this.toast.add({
                    severity: 'success',
                    summary: 'Export XLS',
                    detail: "L'exportation des specialites a été réalisée avec succès.",
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

<style scoped>
.card {
    overflow-x: auto;
}
.frozen-indicator {
    color: green;
    margin-right: 5px;
}
.status-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 3px;
    font-weight: bold;
}
.status-success {
    background-color: rgba(25, 135, 84, 0.1);
    color: #198754;
}
.status-danger {
    background-color: rgba(220, 53, 69, 0.1);
    color: #dc3545;
}
.status-info {
    background-color: rgba(13, 202, 240, 0.1);
    color: #0dcaf0;
}
.arabic-text {
    font-family: 'Lateef', serif !important;
    font-size: 1.4em !important; /* Taille de police augmentée */
}

/* Success - Vert */
.status-success {
    background-color: rgba(25, 135, 84, 0.5);
    color: #198754;
}

/* Danger - Rouge */
.status-danger {
    background-color: rgba(220, 53, 69, 0.5); /* transparent */
    color: #dc3545; /* rouge */
}

/* Info - Bleu */
.status-info {
    background-color: rgba(13, 202, 240, 0.5); /* transparent */
    color: #0dcaf0; /* bleu */
}

/* Warning - Jaune */
.status-warning {
    background-color: rgba(255, 193, 7, 0.5); /* transparent */
    color: #ffc107; /* jaune */
}

/* Primary - Bleu primaire */
.status-primary {
    background-color: rgba(0, 123, 255, 0.5); /* transparent */
    color: #007bff; /* bleu */
}

/* Secondary - Gris */
.status-secondary {
    background-color: rgba(2, 117, 125, 0.5); /* transparent */
    color: #76b765; /* gris */
}

/* Light - Bleu clair */
.status-light {
    background-color: rgba(173, 216, 230, 0.5); /* transparent */
    color: #add8e6; /* bleu clair */
}

/* Dark - Noir */
.status-dark {
    background-color: rgba(0, 58, 64, 0.7); /* transparent */
    color: #add8a6; /* noir/gris foncé */
}

/* Texte arabe */
.arabic-text {
    font-family: 'Lateef', serif !important;
    font-size: 1.4em !important; /* Taille de police augmentée */
}
</style>
