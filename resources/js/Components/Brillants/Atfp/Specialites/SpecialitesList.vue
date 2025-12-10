<template>
    <div class="surface-ground pt-20">
        <!-- ProgressSpinner -->
        <div v-if="isImporting" class="progress-overlay">
            <ProgressSpinner
                :style="{ width: '50px', height: '50px' }"
                strokeWidth="5"
            />
            <span class="progress-text">Importation en cours...</span>
        </div>
        <!-- Navbar -->
        <div
            class="surface-card p-2 border-round border-1 surface-border layout-nav-content-container"
            :style="{
                position: 'relative',
                top: '-34px',
                boxShadow: 'none',
                marginBottom: '-30px',
            }"
        >
            <div class="flex justify-content-between align-items-center">
                <div class="flex gap-3">
                    <Button
                        label="Accueil"
                        icon="pi pi-home"
                        class="p-button-text p-button-plain nav-item"
                        @click="$router.push({ name: 'home' })"
                    />
                    <Button
                        label="Spécialités"
                        icon="pi pi-list"
                        class="p-button-text p-button-plain nav-item"
                        disabled
                    />
                </div>
                <div class="flex gap-2">
                    <Button
                        icon="pi pi-refresh"
                        class="p-button-text p-button-plain nav-item"
                        @click="refreshTable"
                        v-tooltip="'Rafraîchir'"
                    />
                    <Button
                        icon="pi pi-file-import"
                        class="p-button-text p-button-plain nav-item"
                        @click="$refs.fileInput.click()"
                        v-tooltip="'Importer XLS'"
                    />
                    <input
                        ref="fileInput"
                        type="file"
                        style="display: none"
                        accept=".xls,.xlsx"
                        @change="importSpecialites"
                    />
                    <Button
                        icon="pi pi-file-export"
                        class="p-button-text p-button-plain nav-item"
                        @click="exportSpecialties"
                        v-tooltip="'Exporter XLS'"
                    />
                </div>
            </div>
        </div>
        <!-- Main Content -->
        <div class="surface-card p-4 pt-2 border-round shadow-md border-1 surface-border layout-content-container">
            <div class="flex justify-content-between align-items-center mb-4">
                <div class="flex align-items-center gap-2">
                    <span class="p-input-icon-left search-field">
                        <InputText
                            v-model="filters['global'].value"
                            placeholder="Rechercher..."
                            class="search-input"
                        />
                    </span>
                    <Button
                        icon="pi pi-filter-slash"
                        class="p-button-outlined p-button-secondary"
                        size="small"
                        @click="clearFilter"
                        v-tooltip="'Réinitialiser les filtres'"
                    />
                    <Button
                        icon="pi pi-plus"
                        label="Ajouter"
                        class="p-button-success"
                        @click="openForm"
                    />
                </div>
                <div class="flex gap-2">
                    <Button
                        v-if="errors.length > 0"
                        icon="pi pi-exclamation-triangle"
                        class="p-button-warning p-button-outlined"
                        size="small"
                        @click="showImportError = true"
                        v-tooltip="'Erreurs d’importation'"
                    />
                    <Button
                        label="مقيس"
                        :severity="activeTab === 'مقيس' ? 'success' : 'secondary'"
                        class="font-arabic button-height"
                        @click="setActiveTab('مقيس')"
                        v-tooltip="'Standardisé'"
                    />
                    <Button
                        label="غير مقيس"
                        :severity="activeTab === 'غير مقيس' ? 'danger' : 'secondary'"
                        class="font-arabic button-height"
                        @click="setActiveTab('غير مقيس')"
                        v-tooltip="'Non Standardisé'"
                    />
                    <Button
                        label="جديد"
                        :severity="activeTab === 'جديد' ? 'warning' : 'secondary'"
                        class="font-arabic button-height"
                        @click="setActiveTab('جديد')"
                        v-tooltip="'Nouveau'"
                    />
                    <Button
                        label="Tous"
                        :severity="activeTab === null ? 'info' : 'secondary'"
                        class="button-height"
                        @click="setActiveTab(null)"
                    />
                    <SplitButton
                        label="Actions"
                        icon="pi pi-ellipsis-v"
                        :model="actions"
                        class="p-button-text"
                    />
                </div>
            </div>
            <!-- DataTable -->
            <DataTable
                v-model:filters="filters"
                showGridlines
                stripedRows
                v-model:selection="selectedSpecialties"
                :frozenValue="fixedRows"
                :value="filteredSpecialties"
                dataKey="id"
                size="small"
                paginator
                :rows="rows"
                :rowsPerPageOptions="[10, 20, 100]"
                :totalRecords="totalRecords"
                filterDisplay="menu"
                :globalFilterFields="[
                    'id',
                    'code',
                    'nom_ar',
                    'nom_fr',
                    'standardisation_ar',
                    'diplome_fr',
                    'statut',
                    'observation',
                ]"
                :loading="loading"
                scrollable
                scrollHeight="750px"
                :lazy="true"
                @page="onPage"
                @update:rows="onRowsUpdate"
                :pt="{
                    table: { style: 'min-width: 50rem' },
                    bodyrow: ({ props }) => ({
                        class: [{ 'font-bold': props.frozenRow }],
                    }),
                }"
            >
                <template #empty>
                    <div v-if="!loading" class="text-center p-3">
                        <span class="single-line-text">Aucune spécialité trouvée.</span>
                    </div>
                    <div v-else class="text-center p-3">
                        <span class="single-line-text">Chargement...</span>
                    </div>
                </template>
                <Column
                    selectionMode="multiple"
                    headerStyle="width: 3rem"
                    frozen
                ></Column>
                <Column
                    field="code"
                    header="Code"
                    sortable
                    style="min-width: 10rem"
                    frozen
                >
                    <template #body="{ data }">
                        <span class="single-line-text">{{ data.code }}</span>
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
                    header="Spécialité"
                    sortable
                    style="min-width: 20rem"
                >
                    <template #body="{ data }">
                        <div class="flex flex-column">
                            <span class="single-line-text">{{ data.nom_fr || '' }}</span>
                            <span class="single-line-text font-arabic text-secondary">{{ data.nom_ar || '' }}</span>
                        </div>
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
                    field="secteur"
                    header="Secteur"
                    sortable
                    style="min-width: 16rem"
                >
                    <template #body="{ data }">
                        <div class="flex flex-column">
                            <span class="single-line-text">{{ data.secteur?.nom_fr || '-' }}</span>
                            <span class="single-line-text font-arabic text-secondary">{{ data.secteur?.nom_ar || '-' }}</span>
                        </div>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-model="filterModel.value"
                            :options="sectors.map((s) => s.nom_fr)"
                            placeholder="Sélectionner un secteur"
                            class="p-column-filter"
                            @change="filterCallback"
                            showClear
                        />
                    </template>
                </Column>
                <Column
                    field="sousSecteur"
                    header="Sous-secteur"
                    sortable
                    style="min-width: 16rem"
                >
                    <template #body="{ data }">
                        <div class="flex flex-column">
                            <span class="single-line-text">{{ getSubSectorName(data.sous_secteur_id, 'fr') }}</span>
                            <span class="single-line-text font-arabic text-secondary">{{ getSubSectorName(data.sous_secteur_id, 'ar') }}</span>
                        </div>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-model="filterModel.value"
                            :options="subSectors.map((s) => s.nom_fr)"
                            placeholder="Sélectionner un sous-secteur"
                            class="p-column-filter"
                            @change="filterCallback"
                            showClear
                        />
                    </template>
                </Column>
                <Column
                    field="diplome_fr"
                    header="Diplôme"
                    sortable
                    style="min-width: 12rem"
                >
                    <template #body="{ data }">
                        <div class="centered-content">
                            <Tag
                                :value="data.diplome_fr || ''"
                                :severity="getSeverity(data.diplome_fr)"
                            />
                        </div>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-model="filterModel.value"
                            :options="diplomas.map((d) => d.nom_fr)"
                            placeholder="Sélectionner un diplôme"
                            class="p-column-filter"
                            @change="filterCallback"
                            showClear
                        >
                            <template #option="{ option }">
                                <div class="centered-content">
                                    <Tag
                                        :value="option"
                                        :severity="getSeverity(option)"
                                    />
                                </div>
                            </template>
                        </Dropdown>
                    </template>
                </Column>
                <Column
                    field="standardisation_ar"
                    header="Type"
                    sortable
                    style="min-width: 8rem"
                >
                    <template #body="{ data }">
                        <div class="centered-content">
                            <Tag
                                :value="data.standardisation_ar || ''"
                                :severity="getSeverity(data.standardisation_ar)"
                                class="font-arabic"
                            />
                        </div>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-model="filterModel.value"
                            :options="standardisations_ar"
                            placeholder="Sélectionner un type"
                            class="p-column-filter"
                            @change="filterCallback"
                            showClear
                        >
                            <template #option="{ option }">
                                <div class="centered-content">
                                    <Tag
                                        :value="option"
                                        :severity="getSeverity(option)"
                                        class="font-arabic"
                                    />
                                </div>
                            </template>
                        </Dropdown>
                    </template>
                </Column>
                <Column
                    field="statut"
                    header="Statut"
                    sortable
                    style="min-width: 12rem"
                >
                    <template #body="{ data }">
                        <div class="centered-content">
                            <Tag
                                :value="data.statut || ''"
                                :severity="getSeverity(data.statut)"
                            />
                        </div>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-model="filterModel.value"
                            :options="['Active', 'Annulée', 'Remplacée']"
                            placeholder="Sélectionner un statut"
                            class="p-column-filter"
                            @change="filterCallback"
                            showClear
                        >
                            <template #option="{ option }">
                                <div class="centered-content">
                                    <Tag
                                        :value="option"
                                        :severity="getSeverity(option)"
                                    />
                                </div>
                            </template>
                        </Dropdown>
                    </template>
                </Column>
                <Column
                    field="observation"
                    header="Observation"
                    sortable
                    style="min-width: 20rem"
                >
                    <template #body="{ data }">
                        <span class="single-line-text">{{ data.observation || '' }}</span>
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
                                :disabled="frozenRow ? false : fixedRows.length >= 10"
                                text
                                size="small"
                                @click="toggleLock(data, frozenRow, index)"
                            />
                            <Button
                                icon="pi pi-pencil"
                                severity="info"
                                text
                                @click="editSpecialty(data)"
                            />
                            <Button
                                icon="pi pi-trash"
                                severity="danger"
                                text
                                @click="confirmDeleteSpecialty(data)"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>
            <!-- Specialty Form -->
            <SpecialtyForm
                :visible="formVisible"
                :specialtyToEdit="specialtyToEdit"
                @update:visible="formVisible = $event"
                @save="handleSaveSpecialty"
                @update="handleUpdateSpecialty"
                @close="formClose"
            />
            <!-- Delete Confirmation Dialog -->
            <Dialog
                v-model:visible="deleteFormVisible"
                header="Confirmation de suppression"
                :modal="true"
                :style="{ width: '450px' }"
            >
                <div class="p-fluid">
                    <p v-if="specialtyToDelete">
                        Êtes-vous sûr de vouloir supprimer la spécialité
                        <strong>{{ specialtyToDelete.nom_fr || specialtyToDelete.nom_ar }}</strong> ?
                    </p>
                    <p v-else-if="selectedSpecialties?.length > 0">
                        Êtes-vous sûr de vouloir supprimer les {{ selectedSpecialties.length }} spécialités sélectionnées ?
                    </p>
                    <div class="mt-4">
                        <label for="password" class="block text-sm font-medium">Mot de passe</label>
                        <InputText
                            id="password"
                            type="password"
                            v-model="password"
                            class="w-full mt-1"
                            placeholder="Entrez le mot de passe"
                        />
                        <small v-if="passwordError" class="p-error">{{ error }}</small>
                    </div>
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
                        @click="confirmDelete"
                    />
                </template>
            </Dialog>
            <!-- Import Errors -->
            <ImportErrors
                :errors="errors"
                :visible="showImportError"
                @update:visible="showImportError = $event"
                @line-imported="handleLineImported"
                @close="refreshTable"
            />
            <Toast />
        </div>
    </div>
</template>

<script>
import { FilterMatchMode } from 'primevue/api';
import { useToast } from 'primevue/usetoast';
import axios from '@/axios';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Dropdown from 'primevue/dropdown';
import SplitButton from 'primevue/splitbutton';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import ProgressSpinner from 'primevue/progressspinner';
import SpecialtyForm from '@/Components/Atfp/Specialites/ThemeAdd.vue';
import ImportErrors from '@/Components/Atfp/ImportError/SpecialitesImportError.vue';
import ExcelJS from 'exceljs';

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
        ProgressSpinner,
        SpecialtyForm,
        ImportErrors,
    },
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            activeTab: null,
            fixedRows: [],
            specialties: [],
            selectedSpecialties: [],
            filters: null,
            standardisations_ar: ['جديد', 'مقيس', 'غير مقيس'],
            diplomas: [],
            actions: [
                {
                    label: 'Modifier',
                    icon: 'pi pi-pencil',
                    command: () => this.editSelectedSpecialty(),
                },
                {
                    label: 'Supprimer',
                    icon: 'pi pi-trash',
                    command: () => this.deleteSelectedSpecialties(),
                },
            ],
            formVisible: false,
            deleteFormVisible: false,
            specialtyToEdit: null,
            specialtyToDelete: null,
            password: '',
            passwordError: '',
            loading: true,
            sectors: [],
            subSectors: [],
            errors: [],
            showImportError: false,
            isImporting: false,
            rows: 20,
            currentPage: 1,
            totalRecords: 0,
        };
    },
    computed: {
        filteredSpecialties() {
            if (this.activeTab === null) return this.specialties;
            return this.specialties.filter(s => s.standardisation_ar === this.activeTab);
        },
    },
    async created() {
        this.initFilters();
        await Promise.all([
            this.fetchSpecialties(),
            this.fetchSectors(),
            this.fetchSubSectors(),
            this.fetchDiplomas(),
        ]);
        this.loading = false;
    },
    methods: {
        setActiveTab(tab) {
            this.activeTab = tab;
            this.currentPage = 1;
            this.fetchSpecialties();
        },
        clearFilter() {
            this.initFilters();
        },
        openForm() {
            this.specialtyToEdit = null;
            this.formVisible = true;
        },
        initFilters() {
            this.filters = {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                id: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                code: { value: null, matchMode: FilterMatchMode.CONTAINS },
                nom_fr: { value: null, matchMode: FilterMatchMode.CONTAINS },
                nom_ar: { value: null, matchMode: FilterMatchMode.CONTAINS },
                standardisation_ar: { value: null, matchMode: FilterMatchMode.EQUALS },
                diplome_fr: { value: null, matchMode: FilterMatchMode.EQUALS },
                statut: { value: null, matchMode: FilterMatchMode.EQUALS },
                'secteur.nom_fr': { value: null, matchMode: FilterMatchMode.EQUALS },
                'sousSecteur.nom_fr': { value: null, matchMode: FilterMatchMode.EQUALS },
                observation: { value: null, matchMode: FilterMatchMode.CONTAINS },
            };
        },
        async fetchDiplomas() {
            try {
                const response = await axios.get('/api/listes');
                const listes = response.data?.data || [];
                const diplomasList = listes.find(l => l.nom_fr === 'Diplomes');
                this.diplomas = diplomasList ? diplomasList.options : [];
            } catch (error) {
                console.error('Erreur lors du chargement des diplômes:', error);
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Échec du chargement des diplômes.',
                    life: 3000,
                });
            }
        },
        async refreshTable() {
            this.loading = true;
            try {
                await this.fetchSpecialties();
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Tableau mis à jour.',
                    life: 3000,
                });
            } catch (error) {
                console.error('Erreur lors de la mise à jour des spécialités:', error);
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Échec de la mise à jour des spécialités.',
                    life: 3000,
                });
            } finally {
                this.loading = false;
            }
        },
        async fetchSpecialties() {
            this.loading = true;
            try {
                const response = await axios.get('/api/specialites', {
                    params: {
                        page: this.currentPage,
                        per_page: this.rows,
                        with_infos: true,
                        standardisation_ar: this.activeTab,
                    },
                });
                this.specialties = Array.isArray(response.data.data) ? response.data.data : [];
                this.totalRecords = response.data.total || 0;
                console.log('Réponse API spécialités:', response.data);
                console.log('Spécialités chargées:', this.specialties.map(s => ({
                    id: s.id,
                    sous_secteur_id: s.sous_secteur_id,
                    sousSecteur: s.sousSecteur,
                })));
            } catch (error) {
                console.error('Erreur lors du chargement des spécialités:', error);
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Échec du chargement des spécialités.',
                    life: 3000,
                });
            } finally {
                this.loading = false;
            }
        },
        async fetchSectors() {
            try {
                const response = await axios.get('/api/secteurs');
                this.sectors = Array.isArray(response.data) ? response.data : [];
                console.log('Secteurs chargés:', this.sectors);
            } catch (error) {
                console.error('Erreur lors du chargement des secteurs:', error);
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Échec du chargement des secteurs.',
                    life: 3000,
                });
            }
        },
        async fetchSubSectors() {
            try {
                const response = await axios.get('/api/sous-secteurs');
                this.subSectors = Array.isArray(response.data) ? response.data : [];
                console.log('Sous-secteurs chargés:', this.subSectors);
            } catch (error) {
                console.error('Erreur lors du chargement des sous-secteurs:', error);
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Échec du chargement des sous-secteurs.',
                    life: 3000,
                });
            }
        },
        getSubSectorName(sousSecteurId, lang = 'fr') {
            const subSector = this.subSectors.find(s => s.id === sousSecteurId);
            return subSector ? subSector[`nom_${lang}`] || '-' : '-';
        },
        async handleSaveSpecialty(newSpecialty) {
            this.specialties.unshift(newSpecialty);
            await this.fetchSpecialties();
            this.formVisible = false;
            this.toast.add({
                severity: 'success',
                summary: 'Succès',
                detail: 'Spécialité ajoutée.',
                life: 3000,
            });
        },
        editSpecialty(specialty) {
            this.specialtyToEdit = {
                ...specialty,
                statut: specialty.statut || 'Active',
            };
            this.formVisible = true;
        },
        editSelectedSpecialty() {
            if (this.selectedSpecialties.length === 1) {
                const selectedItem = this.fixedRows.find(s => s.id === this.selectedSpecialties[0].id) ||
                    this.specialties.find(s => s.id === this.selectedSpecialties[0].id);
                if (selectedItem) {
                    this.editSpecialty(selectedItem);
                } else {
                    this.toast.add({
                        severity: 'warn',
                        summary: 'Attention',
                        detail: 'Élément non trouvé.',
                        life: 3000,
                    });
                }
            } else {
                this.toast.add({
                    severity: 'warn',
                    summary: 'Attention',
                    detail: 'Sélectionnez une seule spécialité.',
                    life: 3000,
                });
            }
        },
        async handleUpdateSpecialty(updatedSpecialty) {
            const indexTable = this.specialties.findIndex(s => s.id === updatedSpecialty.id);
            if (indexTable !== -1) {
                this.specialties[indexTable] = updatedSpecialty;
            }
            const indexFixedRows = this.fixedRows.findIndex(s => s.id === updatedSpecialty.id);
            if (indexFixedRows !== -1) {
                this.fixedRows[indexFixedRows] = updatedSpecialty;
            }
            this.formVisible = false;
            this.toast.add({
                severity: 'success',
                summary: 'Succès',
                detail: 'Spécialité mise à jour.',
                life: 3000,
            });
        },
        confirmDeleteSpecialty(specialty) {
            this.specialtyToDelete = specialty;
            this.deleteFormVisible = true;
        },
        async confirmDelete() {
            if (this.password !== 'ha') {
                this.passwordError = 'Mot de passe incorrect.';
                return;
            }
            this.passwordError = '';
            if (this.specialtyToDelete) {
                await this.deleteSpecialty();
            } else if (this.selectedSpecialties.length > 0) {
                await this.deleteSelectedSpecialties();
            }
            this.deleteFormVisible = false;
            this.password = '';
        },
        async deleteSpecialty() {
            try {
                await axios.delete(`/api/specialites/${this.specialtyToDelete.id}`);
                this.specialties = this.specialties.filter(s => s.id !== this.specialtyToDelete.id);
                this.fixedRows = this.fixedRows.filter(s => s.id !== this.specialtyToDelete.id);
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Spécialité supprimée.',
                    life: 3000,
                });
            } catch (error) {
                console.error('Erreur lors de la suppression:', error);
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: error.response?.data?.message || 'Échec de la suppression de la spécialité.',
                    life: 3000,
                });
            } finally {
                this.specialtyToDelete = null;
            }
        },
        async deleteSelectedSpecialties() {
            if (this.selectedSpecialties.length > 0) {
                try {
                    const ids = this.selectedSpecialties.map(s => s.id);
                    await axios.post('/api/specialites/delete-selected', { ids });
                    this.specialties = this.specialties.filter(s => !ids.includes(s.id));
                    this.fixedRows = this.fixedRows.filter(s => !ids.includes(s.id));
                    this.selectedSpecialties = [];
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: `${ids.length} spécialité(s) supprimée(s).`,
                        life: 3000,
                    });
                } catch (error) {
                    console.error('Erreur lors de la suppression multiple:', error);
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: error.response?.data?.message || 'Échec de la suppression des spécialités.',
                        life: 3000,
                    });
                }
            } else {
                this.toast.add({
                    severity: 'warn',
                    summary: 'Attention',
                    detail: 'Sélectionnez au moins une spécialité.',
                    life: 3000,
                });
            }
        },
        getSectorName(sectorId, lang = 'fr') {
            const sector = this.sectors.find(s => s.id === sectorId);
            return sector ? sector[`nom_${lang}`] || '-' : '-';
        },
        formatDate(date) {
            if (!date) return null;
            if (typeof date === 'string') date = new Date(date);
            if (!(date instanceof Date) || isNaN(date.getTime())) return null;
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        },
        toggleLock(data, frozenRow, index) {
            if (frozenRow) {
                this.fixedRows = this.fixedRows.filter((_, i) => i !== index);
                this.specialties.push(data);
                this.toast.add({
                    severity: 'info',
                    summary: 'Ligne libérée',
                    detail: 'La ligne n’est plus fixée.',
                    life: 3000,
                });
            } else if (this.fixedRows.length < 10) {
                this.specialties = this.specialties.filter(s => s.id !== data.id);
                this.fixedRows.push(data);
                this.toast.add({
                    severity: 'success',
                    summary: 'Ligne fixée',
                    detail: 'La ligne a été fixée.',
                    life: 3000,
                });
            } else {
                this.toast.add({
                    severity: 'warn',
                    summary: 'Limite atteinte',
                    detail: 'Maximum 10 lignes fixées.',
                    life: 3000,
                });
            }
        },
        getSeverity(value) {
            switch (value) {
                case 'Standardisé':
                case 'Active':
                case 'Certificat de Compétence':
                case 'مقيس':
                case 'معتمدة':
                    return 'success';
                case 'Non Standardisé':
                case 'Annulée':
                case "Certificat de Fin d'Apprentissage":
                case 'غير مقيس':
                case 'ملغية':
                    return 'danger';
                case 'Nouveau':
                case 'Remplacée':
                case "Certificat d'Aptitude Professionnelle":
                case 'جديد':
                case 'تم تعويضها':
                    return 'warning';
                case 'Brevet de Technicien Supérieur':
                    return 'info';
                case 'Brevet de Technicien Professionnel':
                case 'Certificat de Formation Professionnelle':
                case 'Formation Technique Professionnelle':
                    return 'secondary';
                default:
                    return 'secondary';
            }
        },
        async importSpecialites(event) {
            const file = event.target.files?.[0];
            if (!file) return;
            this.isImporting = true;
            const formData = new FormData();
            formData.append('file', file);
            try {
                const response = await axios.post('/api/specialites/import', formData, {
                    headers: { 'Content-Type': 'multipart/form-data' },
                });
                this.errors = response.data?.data?.error_lines || [];
                this.toast.add({
                    severity: this.errors.length > 0 ? 'warn' : 'success',
                    summary: 'Importation',
                    detail: this.errors.length > 0
                        ? `Importation terminée avec ${this.errors.length} erreur(s).`
                        : 'Importation réussie.',
                    life: 10000,
                });
                if (this.errors.length > 0) this.showImportError = true;
                await this.refreshTable();
            } catch (error) {
                console.error('Erreur lors de l’importation:', error);
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: error.response?.data?.message || 'Échec de l’importation.',
                    life: 3000,
                });
            } finally {
                event.target.value = '';
                this.isImporting = false;
            }
        },
        handleLineImported(errorLine) {
            this.errors = this.errors.filter(err => err.line !== errorLine.line);
        },
        async exportSpecialties() {
            try {
                const workbook = new ExcelJS.Workbook();
                const worksheet = workbook.addWorksheet('Spécialités');
                worksheet.columns = [
                    { header: 'Code', key: 'code', width: 15 },
                    { header: 'Nom (FR)', key: 'nom_fr', width: 30 },
                    { header: 'Nom (AR)', key: 'nom_ar', width: 30 },
                    { header: 'Secteur (FR)', key: 'secteur_nom_fr', width: 20 },
                    { header: 'Secteur (AR)', key: 'secteur_nom_ar', width: 20 },
                    { header: 'Sous-secteur (FR)', key: 'sous_secteur_nom_fr', width: 20 },
                    { header: 'Sous-secteur (AR)', key: 'sous_secteur_nom_ar', width: 20 },
                    { header: 'Type', key: 'standardisation_ar', width: 15 },
                    { header: 'Diplôme (FR)', key: 'diplome_fr', width: 20 },
                    { header: 'Diplôme (AR)', key: 'diplome_ar', width: 20 },
                    { header: 'Date de création', key: 'date_creation', width: 15 },
                    { header: 'Statut', key: 'statut', width: 15 },
                    { header: 'Observation', key: 'observation', width: 30 },
                    { header: 'Date d’annulation', key: 'date_annulation', width: 15 },
                ];
                this.specialties.forEach(specialty => {
                    worksheet.addRow({
                        code: specialty.code,
                        nom_fr: specialty.nom_fr || '',
                        nom_ar: specialty.nom_ar || '',
                        secteur_nom_fr: this.getSectorName(specialty.secteur_id, 'fr'),
                        secteur_nom_ar: this.getSectorName(specialty.secteur_id, 'ar'),
                        sous_secteur_nom_fr: this.getSubSectorName(specialty.sous_secteur_id, 'fr'),
                        sous_secteur_nom_ar: this.getSubSectorName(specialty.sous_secteur_id, 'ar'),
                        standardisation_ar: specialty.standardisation_ar || '',
                        diplome_fr: specialty.diplome_fr || '',
                        diplome_ar: specialty.diplome_ar || '',
                        date_creation: this.formatDate(specialty.date_creation),
                        statut: specialty.statut || '',
                        observation: specialty.observation || '',
                        date_annulation: this.formatDate(specialty.date_annulation),
                    });
                });
                worksheet.getRow(1).eachCell(cell => {
                    cell.font = { bold: true };
                    cell.fill = {
                        type: 'pattern',
                        pattern: 'solid',
                        fgColor: { argb: 'FFCCCCCC' },
                    };
                    cell.alignment = { vertical: 'middle', horizontal: 'center' };
                });
                const buffer = await workbook.xlsx.writeBuffer();
                const blob = new Blob([buffer], {
                    type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = `specialites_${new Date().toISOString().split('T')[0]}.xlsx`;
                link.click();
                URL.revokeObjectURL(link.href);
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Exportation réussie.',
                    life: 3000,
                });
            } catch (error) {
                console.error('Erreur lors de l’exportation:', error);
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Échec de l’exportation.',
                    life: 3000,
                });
            }
        },
        onPage(event) {
            this.currentPage = event.page + 1;
            this.rows = event.rows;
            this.fetchSpecialties();
        },
        onRowsUpdate(event) {
            this.rows = event;
            this.currentPage = 1;
            this.fetchSpecialties();
        },
        formClose() {
            this.formVisible = false;
            this.specialtyToEdit = null;
        },
    },
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Amiri:wght@400&display=swap');

@font-face {
    font-family: 'Montserrat-Arabic-Regular';
    src: url('/fonts/Montserrat-Arabic-Regular.otf') format('opentype');
    font-weight: normal;
    font-style: normal;
}

.font-arabic {
    font-family: 'Amiri', 'Montserrat-Arabic-Regular', sans-serif;
    direction: rtl;
}

.text-secondary {
    color: var(--text-color-secondary);
}

.layout-nav-content-container {
    background: var(--surface-background);
    transition: all 0.3s ease;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.nav-item {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    border-radius: 6px;
    transition: background-color 0.2s ease, color 0.2s;
    color: var(--text-color);
    text-decoration: none;
}

.nav-item:hover {
    background-color: var(--surface-hover);
}

.layout-content-container {
    animation: contentEntry 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    background: var(--surface-background);
}

@keyframes contentEntry {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.single-line-text {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
}

.data-table {
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
}

.data-table-sm .p-datatable-tbody > tr > td {
    padding: 0.5rem 1rem;
}

.data-table .column-header-content {
    justify-content: center;
}

.search-input {
    border-radius: 6px;
    transition: border-color 0.2s;
    padding: 0.5rem;
    background: var(--surface-background);
    color: var(--text-color);
}

.search-input:hover {
    border-color: var(--primary-color);
}

.p-button {
    transition: background-color 0.2s, color 0.2s;
}

.p-button:hover {
    background-color: var(--surface-hover);
}

.button-height {
    height: 40px;
}

.centered-content {
    display: flex;
    justify-content: center;
    align-items: center;
}

.progress-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.progress-text {
    margin-top: 10px;
    color: #ffffff;
    font-size: 1.2rem;
    font-family: 'Amiri', 'Montserrat-Arabic-Regular', sans-serif;
}

:root {
    --primary-color: #6366f1;
    --primary-color-text: #ffffff;
    --surface-background: #ffffff;
    --surface-hover: #f5f5f5;
    --text-color: #1f2a44;
    --text-color-secondary: #6b7280;
    --surface-border: #e5e7eb;
}
</style>
