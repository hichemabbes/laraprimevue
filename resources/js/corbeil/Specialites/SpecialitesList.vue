<template>
    <div>
        <!-- Tabs pour filtrer les spécialités -->
        <div class="flex justify-content-between align-items-center mb-3">
            <!-- Titre -->
            <div class="text-2xl font-bold text-primary">
                Liste des Spécialités
                <span class="text-danger">{{ dynamicSubtitle }}</span>
            </div>
            <!-- Boutons de filtre -->
            <div class="flex align-items-center gap-2">
                <Button
                    label="جديد"
                    :severity="getSeverity('جديد')"
                    @click="setActiveTab('جديد')"
                />
                <Button
                    label="غير مقيس"
                    :severity="getSeverity('غير مقيس')"
                    @click="setActiveTab('غير مقيس')"
                />
                <Button
                    label="مقيس"
                    :severity="getSeverity('مقيس')"
                    @click="setActiveTab('مقيس')"
                />
            </div>
        </div>

        <!-- DataTable -->
        <DataTable
            v-model:filters="filters"
            showGridlines
            stripedRows
            v-model:selection="selectedSpecialites"
            :frozenValue="FixLignes"
            :value="filteredSpecialites"
            dataKey="id"
            size="small"
            :rows="20"
            paginator
            filterDisplay="menu"
            :globalFilterFields="[
                'id',
                'code',
                'nom_ar',
                'nom_fr',
                'type',
                'diplome',
                'statut',
            ]"
            :loading="loading"
            scrollable
            scrollHeight="650px"
            :pt="{
                table: { style: 'min-width: 50rem' },
                bodyrow: ({ props }) => ({
                    class: [{ 'font-bold': props.frozenRow }],
                }),
            }"
        >
            <!-- Header -->
            <template #header>
                <div
                    class="flex justify-content-between mb-2 align-items-center"
                >
                    <!-- Section gauche -->
                    <div class="flex align-items-center gap-2">
                        <Button
                            icon="pi pi-plus"
                            severity="success"
                            size="small"
                            @click="openForm"
                        />
                        <span class="p-input-icon-right mr-2">
                            <InputText
                                v-model="filters['global'].value"
                                size="small"
                                placeholder="Recherche..."
                            />
                            <i class="pi pi-search" />
                        </span>
                        <Button
                            icon="pi pi-times"
                            size="small"
                            severity="danger"
                            outlined
                            @click="clearFilter"
                        />
                        <Button
                            icon="pi pi-lock"
                            size="small"
                            label="Figer"
                            @click="toggleFreezeDropdown"
                        />
                        <Dropdown
                            v-if="showFreezeDropdown"
                            v-model="selectedColumnToFreeze"
                            :options="columnOptions"
                            optionLabel="header"
                            placeholder="Sélectionner une colonne"
                            @change="freezeColumn"
                        >
                            <template #option="slotProps">
                                <div>
                                    <span v-if="slotProps.option.isFrozen"
                                        >✔️</span
                                    >
                                    {{ slotProps.option.header }}
                                </div>
                            </template>
                        </Dropdown>
                    </div>
                    <!-- Section droite -->
                    <div class="flex align-items-center gap-3">
                        <Button
                            icon="pi pi-file-import"
                            severity="success"
                            size="small"
                            label="Import XLS"
                            @click="importXLS"
                        />
                        <Button
                            v-if="errors.length > 0"
                            icon="pi pi-exclamation-triangle"
                            severity="warning"
                            size="small"
                            label="Erreur Import"
                            @click="showImportError = true"
                        />
                        <Button
                            icon="pi pi-file-export"
                            severity="info"
                            size="small"
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

            <!-- Message vide -->
            <template #empty>
                <div v-if="!loading" class="text-center p-3">
                    Aucune spécialité trouvée.
                </div>
                <div v-else class="text-center p-3">Chargement en cours...</div>
            </template>

            <!-- Colonnes -->
            <Column
                selectionMode="multiple"
                headerStyle="width: 3rem"
                frozen
                class="font-bold"
            ></Column>

            <Column
                v-if="activeTab !== 'جديد'"
                field="secteur.code"
                header="Code Secteur"
                sortable
                style="min-width: 10rem"
                :frozen="frozenColumns.includes('secteur.code')"
            >
                <template #body="{ data }">
                    {{ data.secteur?.code || '----' }}
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
                v-if="activeTab !== 'جديد'"
                field="secteur.nom_ar"
                header="Nom Secteur"
                sortable
                style="min-width: 14rem"
                :frozen="frozenColumns.includes('secteur.nom_ar')"
            >
                <template #body="{ data }">
                    {{ data.secteur?.nom_ar || '----' }}
                </template>
                <template #filter="{ filterModel, filterCallback }">
                    <InputText
                        v-model="filterModel.value"
                        type="text"
                        class="p-column-filter"
                        placeholder="Rechercher par nom"
                        @input="filterCallback"
                    />
                </template>
            </Column>

            <Column
                v-if="activeTab === 'مقيس'"
                field="sous_secteur.code"
                header="Code Sous Secteur"
                sortable
                style="min-width: 10rem"
                :frozen="frozenColumns.includes('sous_secteur.code')"
            >
                <template #body="{ data }">
                    {{ data.sous_secteur?.code || '----' }}
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
                v-if="activeTab === 'مقيس'"
                field="sous_secteur.nom_ar"
                header="Nom Sous Secteur"
                sortable
                style="min-width: 14rem"
                :frozen="frozenColumns.includes('sous_secteur.nom_ar')"
            >
                <template #body="{ data }">
                    {{ data.sous_secteur?.nom_ar || '----' }}
                </template>
                <template #filter="{ filterModel, filterCallback }">
                    <InputText
                        v-model="filterModel.value"
                        type="text"
                        class="p-column-filter"
                        placeholder="Rechercher par nom"
                        @input="filterCallback"
                    />
                </template>
            </Column>

            <Column
                field="code"
                header="Code Spécialité"
                sortable
                style="min-width: 10rem"
                :frozen="frozenColumns.includes('code')"
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
                        placeholder="Rechercher par nom"
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
                        placeholder="Rechercher par nom"
                        @input="filterCallback"
                    />
                </template>
            </Column>

            <Column
                field="diplome"
                header="Diplôme"
                sortable
                style="min-width: 10rem"
            >
                <template #body="{ data }">
                    <Tag
                        :value="data.diplome"
                        :severity="getSeverity(data.diplome)"
                    />
                </template>
                <template #filter="{ filterModel, filterCallback }">
                    <Dropdown
                        v-model="filterModel.value"
                        :options="diplomes"
                        placeholder="Sélectionner un diplôme"
                        @change="filterCallback"
                    />
                </template>
            </Column>

            <Column
                field="statut"
                header="Statut"
                sortable
                style="min-width: 10rem"
            >
                <template #body="{ data }">
                    <Tag
                        :value="data.statut"
                        :severity="getSeverity(data.statut)"
                    />
                </template>
                <template #filter="{ filterModel, filterCallback }">
                    <Dropdown
                        v-model="filterModel.value"
                        :options="statuts"
                        placeholder="Sélectionner un statut"
                        showClear
                        @change="filterCallback"
                    />
                </template>
            </Column>

            <Column
                field="date_creation"
                header="Date de création"
                sortable
                dataType="date"
                style="min-width: 12rem"
            >
                <template #body="{ data }">
                    {{ formatDate(data.date_creation) }}
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
                field="date_annulation"
                header="Date d'annulation"
                sortable
                dataType="date"
                style="min-width: 12rem"
            >
                <template #body="{ data }">
                    {{ formatDate(data.date_annulation) }}
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
                field="observation"
                header="Observation"
                sortable
                style="min-width: 14rem"
            >
                <template #body="{ data }">
                    {{ data.observation }}
                </template>
                <template #filter="{ filterModel, filterCallback }">
                    <InputText
                        v-model="filterModel.value"
                        type="text"
                        class="p-column-filter"
                        placeholder="Rechercher par observation"
                        @input="filterCallback"
                    />
                </template>
            </Column>

            <Column header="Actions" style="min-width: 14rem" frozen>
                <template #body="{ data, frozenRow, index }">
                    <div class="flex gap-2">
                        <Button
                            :icon="frozenRow ? 'pi pi-lock-open' : 'pi pi-lock'"
                            severity="secondary"
                            text
                            :disabled="
                                frozenRow ? false : FixLignes.length >= 10
                            "
                            @click="toggleLock(data, frozenRow, index)"
                            v-tooltip="'Fixer/Défixer la ligne'"
                        />
                        <Button
                            icon="pi pi-pencil"
                            severity="info"
                            text
                            @click="editSpecialite(data)"
                        />
                        <Button
                            icon="pi pi-trash"
                            severity="danger"
                            text
                            @click="confirmDeleteSpecialite(data)"
                        />
                    </div>
                </template>
            </Column>
        </DataTable>

        <!-- Formulaire -->
        <Form
            :visible="formVisible"
            :specialiteToEdit="specialiteToEdit"
            :existingSpecialites="specialites"
            @update:visible="formVisible = $event"
            @save="handleSaveSpecialite"
            @update="handleUpdateSpecialite"
        />

        <!-- Confirmation de suppression -->
        <Dialog
            v-model:visible="deleteFormVisible"
            header="Confirmation de suppression"
            :modal="true"
            :style="{ width: '450px' }"
        >
            <div class="p-fluid">
                <p>Êtes-vous sûr de vouloir supprimer cette spécialité ?</p>
            </div>
            <template #footer>
                <Button
                    label="Non"
                    icon="pi pi-times"
                    text
                    @click="deleteFormVisible = false"
                />
                <Button
                    label="Oui"
                    icon="pi pi-check"
                    text
                    @click="deleteSpecialite"
                />
            </template>
        </Dialog>

        <!-- Erreurs d'importation -->
        <ImportError
            :errors="errors"
            :visible="showImportError"
            @update:visible="showImportError = $event"
            @line-imported="handleLineImported"
            @close="refreshTable"
        />

        <!-- Notifications -->
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
import Calendar from 'primevue/calendar';
import Form from '@/corbeil/Specialites/SpecialitesForm.vue';
import ImportError from '@/corbeil/Specialites/SpecialitesImportError.vue';
import ExcelJS from 'exceljs';

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
        ImportError,
    },
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            activeTab: 'مقيس',
            FixLignes: [],
            specialites: [],
            selectedSpecialites: [],
            filters: null,
            showFreezeDropdown: false,
            selectedColumnToFreeze: null,
            frozenColumns: ['code'],
            loading: true,
            formVisible: false,
            deleteFormVisible: false,

            specialiteToEdit: null,
            specialiteToDelete: null,
            errors: [],
            showImportError: false,
            secteurs: [],
            sousSecteurs: [],
            selectedSecteur: null,
            diplomes: ['BTS', 'BTP', 'CAP', 'CC', 'CFA'],
            statuts: ['En vigueur', 'Non validée', 'Annulée'],
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
        };
    },
    created() {
        this.initFilters();
        this.fetchSpecialites();
        this.fetchSecteurs();
        this.fetchSousSecteurs();
    },
    computed: {
        dynamicSubtitle() {
            return this.activeTab === 'جديد'
                ? 'Nouvelles'
                : this.activeTab === 'غير مقيس'
                  ? 'Non Normalisée'
                  : 'Normalisée';
        },
        columnOptions() {
            return [
                {
                    field: 'secteur.code',
                    header: 'Code Secteur',
                    isFrozen: this.frozenColumns.includes('secteur.code'),
                },
                {
                    field: 'secteur.nom_ar',
                    header: 'Nom Secteur',
                    isFrozen: this.frozenColumns.includes('secteur.nom_ar'),
                },
                {
                    field: 'sous_secteur.code',
                    header: 'Code Sous Secteur',
                    isFrozen: this.frozenColumns.includes('sous_secteur.code'),
                },
                {
                    field: 'sous_secteur.nom_ar',
                    header: 'Nom Sous Secteur',
                    isFrozen: this.frozenColumns.includes(
                        'sous_secteur.nom_ar'
                    ),
                },
                {
                    field: 'code',
                    header: 'Code Spécialité',
                    isFrozen: this.frozenColumns.includes('code'),
                },
            ];
        },
        filteredSpecialites() {
            return this.specialites.filter((s) => s.type === this.activeTab);
        },
    },
    methods: {
        setActiveTab(tab) {
            this.activeTab = tab;
        },
        clearFilter() {
            this.initFilters();
        },
        openForm() {
            this.specialiteToEdit = null;
            this.formVisible = true;
        },
        initFilters() {
            this.filters = {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                'secteur.code': {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
                'secteur.nom_ar': {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.CONTAINS },
                    ],
                },
                'sous_secteur.code': {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
                'sous_secteur.nom_ar': {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.CONTAINS },
                    ],
                },
                code: {
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
                diplome: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.EQUALS },
                    ],
                },
                statut: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.EQUALS },
                    ],
                },
                date_creation: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.DATE_IS },
                    ],
                },
                date_annulation: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.DATE_IS },
                    ],
                },
                observation: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.CONTAINS },
                    ],
                },
            };
        },
        async fetchSpecialites() {
            this.loading = true;
            try {
                const response = await axios.get('/api/specialites');
                this.specialites = response.data;
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
        async handleSaveSpecialite(newSpecialite) {
            try {
                const response = await axios.post(
                    '/api/specialites',
                    newSpecialite
                );
                this.specialites.push(response.data);
                this.formVisible = false;
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Spécialité ajoutée.',
                    life: 3000,
                });
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: "Erreur lors de l'ajout.",
                    life: 3000,
                });
            }
        },
        editSpecialite(specialite) {
            this.specialiteToEdit = { ...specialite };
            this.formVisible = true;
        },
        editSelectedSpecialite() {
            if (this.selectedSpecialites?.length === 1) {
                this.editSpecialite(this.selectedSpecialites[0]);
            } else {
                this.toast.add({
                    severity: 'warn',
                    summary: 'Attention',
                    detail: 'Sélectionnez une seule spécialité.',
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
                if (index !== -1) this.specialites[index] = response.data;
                this.formVisible = false;
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Spécialité mise à jour.',
                    life: 3000,
                });
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Erreur lors de la mise à jour.',
                    life: 3000,
                });
            }
        },
        confirmDeleteSpecialite(specialite) {
            this.specialiteToDelete = specialite;
            this.deleteFormVisible = true;
        },
        async deleteSpecialite() {
            try {
                await axios.delete(
                    `/api/specialites/${this.specialiteToDelete.id}`
                );
                this.specialites = this.specialites.filter(
                    (s) => s.id !== this.specialiteToDelete.id
                );
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Spécialité supprimée.',
                    life: 3000,
                });
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Erreur lors de la suppression.',
                    life: 3000,
                });
            } finally {
                this.deleteFormVisible = false;
                this.specialiteToDelete = null;
            }
        },
        async deleteSelectedSpecialites() {
            if (this.selectedSpecialites?.length > 0) {
                try {
                    await axios.post('/api/specialites/delete-selected', {
                        ids: this.selectedSpecialites.map((s) => s.id),
                    });
                    this.specialites = this.specialites.filter(
                        (s) =>
                            !this.selectedSpecialites.some(
                                (sel) => sel.id === s.id
                            )
                    );
                    this.selectedSpecialites = [];
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Spécialités supprimées.',
                        life: 3000,
                    });
                } catch (error) {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'Erreur lors de la suppression.',
                        life: 3000,
                    });
                }
            } else {
                this.toast.add({
                    severity: 'warn',
                    summary: 'Attention',
                    detail: 'Aucune spécialité sélectionnée.',
                    life: 3000,
                });
            }
        },
        toggleLock(data, frozen, index) {
            if (frozen) {
                this.FixLignes = this.FixLignes.filter((_, i) => i !== index);
            } else if (this.FixLignes.length < 10) {
                this.FixLignes.push(data);
            }
            this.toast.add({
                severity: frozen ? 'info' : 'success',
                summary: frozen ? 'Ligne libérée' : 'Ligne fixée',
                detail: frozen
                    ? "La ligne n'est plus fixée."
                    : 'La ligne a été fixée.',
                life: 3000,
            });
        },
        toggleFreezeDropdown() {
            this.showFreezeDropdown = !this.showFreezeDropdown;
        },
        freezeColumn() {
            if (this.selectedColumnToFreeze) {
                const field = this.selectedColumnToFreeze.field;
                if (this.frozenColumns.includes(field)) {
                    this.frozenColumns = this.frozenColumns.filter(
                        (col) => col !== field
                    );
                    this.toast.add({
                        severity: 'info',
                        summary: 'Colonne libérée',
                        detail: `${this.selectedColumnToFreeze.header} n\'est plus figée.`,
                        life: 3000,
                    });
                } else {
                    this.frozenColumns.push(field);
                    this.toast.add({
                        severity: 'success',
                        summary: 'Colonne figée',
                        detail: `${this.selectedColumnToFreeze.header} est maintenant figée.`,
                        life: 3000,
                    });
                }
                this.showFreezeDropdown = false;
                this.selectedColumnToFreeze = null;
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
                            headers: { 'Content-Type': 'multipart/form-data' },
                        }
                    );
                    this.errors = response.data.error_lines || [];
                    this.toast.add({
                        severity: this.errors.length > 0 ? 'warn' : 'success',
                        summary: 'Import XLS',
                        detail:
                            this.errors.length > 0
                                ? `Import terminé avec ${this.errors.length} erreurs.`
                                : 'Importation réussie.',
                        life: 10000,
                    });
                    if (this.errors.length > 0) this.showImportError = true;
                    await this.fetchSpecialites();
                } catch (error) {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: "Erreur lors de l'import XLS.",
                        life: 3000,
                    });
                } finally {
                    fileInput.remove();
                }
            };
            document.body.appendChild(fileInput);
            fileInput.click();
        },
        handleLineImported(importedLine) {
            this.errors = this.errors.filter(
                (err) => err.line !== importedLine.line
            );
        },
        async exportXLS() {
            try {
                const workbook = new ExcelJS.Workbook();
                const worksheet = workbook.addWorksheet('Spécialités');
                worksheet.columns = [
                    { header: 'Code Secteur', key: 'secteur_code', width: 15 },
                    {
                        header: 'Nom Secteur (AR)',
                        key: 'secteur_nom_ar',
                        width: 30,
                    },
                    {
                        header: 'Code Sous-Secteur',
                        key: 'sous_secteur_code',
                        width: 15,
                    },
                    {
                        header: 'Nom Sous-Secteur (AR)',
                        key: 'sous_secteur_nom_ar',
                        width: 30,
                    },
                    { header: 'Code Spécialité', key: 'code', width: 15 },
                    { header: 'Nom (FR)', key: 'nom_fr', width: 30 },
                    { header: 'Nom (AR)', key: 'nom_ar', width: 30 },
                    { header: 'Type', key: 'type', width: 15 },
                    { header: 'Diplôme', key: 'diplome', width: 15 },
                    { header: 'Statut', key: 'statut', width: 15 },
                    {
                        header: 'Date Création',
                        key: 'date_creation',
                        width: 20,
                    },
                    {
                        header: 'Date Annulation',
                        key: 'date_annulation',
                        width: 20,
                    },
                    { header: 'Observation', key: 'observation', width: 30 },
                ];
                this.filteredSpecialites.forEach((s) => {
                    worksheet.addRow({
                        secteur_code: s.secteur?.code || '----',
                        secteur_nom_ar: s.secteur?.nom_ar || '----',
                        sous_secteur_code: s.sous_secteur?.code || '----',
                        sous_secteur_nom_ar: s.sous_secteur?.nom_ar || '----',
                        code: s.code,
                        nom_fr: s.nom_fr,
                        nom_ar: s.nom_ar,
                        type: s.type,
                        diplome: s.diplome,
                        statut: s.statut,
                        date_creation: this.formatDate(s.date_creation),
                        date_annulation: this.formatDate(s.date_annulation),
                        observation: s.observation,
                    });
                });
                worksheet.getRow(1).eachCell((cell) => {
                    cell.font = { bold: true };
                    cell.fill = {
                        type: 'pattern',
                        pattern: 'solid',
                        fgColor: { argb: 'FF1976D2' },
                    };
                    cell.color = { argb: 'FFFFFFFF' };
                });
                const buffer = await workbook.xlsx.writeBuffer();
                const blob = new Blob([buffer], {
                    type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = `specialites_${this.activeTab}.xlsx`;
                link.click();
                URL.revokeObjectURL(link.href);
                this.toast.add({
                    severity: 'success',
                    summary: 'Export XLS',
                    detail: 'Exportation réussie.',
                    life: 3000,
                });
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: "Erreur lors de l'export XLS.",
                    life: 3000,
                });
            }
        },
        formatDate(date) {
            if (!date) return '';
            const d = new Date(date);
            return `${d.getDate().toString().padStart(2, '0')}/${(d.getMonth() + 1).toString().padStart(2, '0')}/${d.getFullYear()}`;
        },

        refreshTable() {
            this.fetchSpecialites();
        },
        getSeverity(value) {
            const severityMap = {
                مقيس: 'success',
                'غير مقيس': 'danger',
                جديد: 'warning',
                BTS: 'info',
                BTP: 'secondary',
                CAP: 'warning',
                CC: 'success',
                CFA: 'danger',
                'En vigueur': 'success',
                'Non validée': 'warning',
                Annulée: 'danger',
            };
            return severityMap[value] || 'secondary';
        },
    },
};
</script>
