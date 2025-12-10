<template>
    <div class="grid">
        <!-- Barre de navigation -->
        <div class="col-12">
            <div class="navbar">
                <div class="navbar-menu-left">
                    <Button
                        label="Accueil"
                        icon="pi pi-home"
                        class="p-button-text"
                        @click="$router.push({ name: 'dashboard' })"
                    />
                    <Button
                        label="Secteurs"
                        icon="pi pi-building"
                        class="p-button-text"
                        @click="$router.push({ name: 'secteurs' })"
                    />
                    <Button
                        label="Années de Formation"
                        icon="pi pi-sitemap"
                        class="p-button-text"
                        @click="$router.push({ name: 'annees-formation' })"
                    />
                    <Button
                        label="Années Administratives"
                        icon="pi pi-calendar"
                        class="p-button-text"
                        @click="$router.push({ name: 'annees-administrative' })"
                    />
                </div>
                <div class="navbar-menu-right">
                    <span class="p-input-icon-right search-field">
                        <i class="pi pi-search" />
                        <InputText
                            v-model="filters['global'].value"
                            size="small"
                            placeholder="Recherche..."
                            class="input-search"
                        />
                    </span>
                    <Button
                        type="button"
                        icon="pi pi-times"
                        size="small"
                        severity="danger"
                        outlined
                        @click="clearFilter"
                        v-tooltip="'Effacer'"
                    />
                    <Button
                        icon="pi pi-plus"
                        severity="success"
                        size="small"
                        @click="openForm"
                        v-tooltip="'Ajouter'"
                    />
                    <Button
                        icon="pi pi-file-import"
                        severity="success"
                        size="small"
                        @click="importXLS"
                        v-tooltip="'Importer XLS'"
                    />
                    <Button
                        v-if="errors.length > 0"
                        icon="pi pi-exclamation-triangle"
                        severity="warning"
                        size="small"
                        @click="showImportError = true"
                        v-tooltip="'Erreurs Import'"
                    />
                    <Button
                        icon="pi pi-file-export"
                        severity="info"
                        size="small"
                        @click="exportXLS"
                        v-tooltip="'Exporter XLS'"
                    />
                    <SplitButton
                        icon="pi pi-cog"
                        size="small"
                        :model="actions"
                        v-tooltip="'Actions'"
                    />
                </div>
            </div>
        </div>

        <!-- Tableau -->
        <div class="col-12">
            <div class="card">
                <DataTable
                    v-model:filters="filters"
                    showGridlines
                    stripedRows
                    :value="periodesRepos"
                    dataKey="id"
                    size="small"
                    paginator
                    :rows="20"
                    filterDisplay="menu"
                    :globalFilterFields="[
                        'annee_administrative.nom',
                        'type_repos_administratif_fr.nom_fr',
                        'option_repos_administratif_fr.nom_fr',
                        'type_repos_administratif_ar.nom_ar',
                        'option_repos_administratif_ar.nom_ar',
                        'statut',
                        'description_fr',
                        'description_ar',
                    ]"
                    :loading="loading"
                    scrollable
                    scrollHeight="700px"
                    :pt="{ table: { style: 'min-width: 50rem' } }"
                >
                    <template #empty>
                        <div v-if="!loading" class="text-center p-3">
                            Aucune période de repos trouvée.
                        </div>
                        <div v-else class="text-center p-3">
                            Chargement en cours...
                        </div>
                    </template>

                    <Column
                        field="annee_administrative.nom"
                        header="Année Administrative"
                        sortable
                        style="width: 15rem"
                    >
                        <template #body="{ data }">
                            <span>{{
                                data.annee_administrative?.nom || '-'
                            }}</span>
                        </template>
                    </Column>
                    <Column
                        field="type_repos_administratif_fr.nom_fr"
                        header="Type (FR)"
                        sortable
                        style="width: 15rem"
                    >
                        <template #body="{ data }">
                            <span>{{
                                data.type_repos_administratif_fr?.nom_fr || '-'
                            }}</span>
                        </template>
                    </Column>
                    <Column
                        field="option_repos_administratif_fr.nom_fr"
                        header="Option (FR)"
                        sortable
                        style="width: 15rem"
                    >
                        <template #body="{ data }">
                            <span>{{
                                data.option_repos_administratif_fr?.nom_fr ||
                                '-'
                            }}</span>
                        </template>
                    </Column>
                    <Column
                        field="type_repos_administratif_ar.nom_ar"
                        header="Type (AR)"
                        sortable
                        style="width: 15rem"
                    >
                        <template #body="{ data }">
                            <span>{{
                                data.type_repos_administratif_ar?.nom_ar || '-'
                            }}</span>
                        </template>
                    </Column>
                    <Column
                        field="option_repos_administratif_ar.nom_ar"
                        header="Option (AR)"
                        sortable
                        style="width: 15rem"
                    >
                        <template #body="{ data }">
                            <span>{{
                                data.option_repos_administratif_ar?.nom_ar ||
                                '-'
                            }}</span>
                        </template>
                    </Column>
                    <Column header="Période" style="width: 15rem">
                        <template #body="{ data }">
                            {{ formatDate(data.date_debut) }} -
                            {{ formatDate(data.date_fin) || '-' }}
                        </template>
                    </Column>
                    <Column
                        field="nombre_jour"
                        header="Nb Jours"
                        sortable
                        style="width: 10rem"
                    >
                        <template #body="{ data }">
                            {{ data.nombre_jour || '-' }}
                        </template>
                    </Column>
                    <Column
                        field="description_fr"
                        header="Description (FR)"
                        style="width: 20rem"
                    >
                        <template #body="{ data }">
                            {{ data.description_fr || '-' }}
                        </template>
                    </Column>
                    <Column
                        field="description_ar"
                        header="Description (AR)"
                        style="width: 20rem"
                    >
                        <template #body="{ data }">
                            {{ data.description_ar || '-' }}
                        </template>
                    </Column>
                    <Column
                        field="statut"
                        header="Statut"
                        sortable
                        style="width: 10rem"
                    >
                        <template #body="{ data }">
                            <Tag
                                :value="data.statut"
                                :severity="
                                    data.statut === 'Actif'
                                        ? 'success'
                                        : 'danger'
                                "
                            />
                        </template>
                    </Column>
                    <Column header="Actions" style="width: 15rem">
                        <template #body="{ data }">
                            <div class="flex gap-2">
                                <Button
                                    icon="pi pi-pencil"
                                    severity="info"
                                    text
                                    @click="editPeriode(data)"
                                />
                                <Button
                                    icon="pi pi-trash"
                                    severity="danger"
                                    text
                                    @click="confirmDeletePeriode(data)"
                                />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>

        <PeriodeReposAdministrativeForm
            :visible="formVisible"
            :annee="selectedAnnee"
            :periodeToEdit="periodeToEdit"
            @update:visible="formVisible = $event"
            @save="handleSavePeriode"
            @update="handleUpdatePeriode"
            @close="closeForm"
        />

        <Dialog
            v-model:visible="deleteFormVisible"
            header="Confirmation de suppression"
            :modal="true"
            :style="{ width: '450px' }"
        >
            <div class="p-fluid">
                <p v-if="periodeToDelete">
                    Êtes-vous sûr de vouloir supprimer la période de repos
                    <strong>{{
                        periodeToDelete.option_repos_administratif_fr?.nom_fr ||
                        periodeToDelete.option_repos_administratif_ar?.nom_ar ||
                        'N/A'
                    }}</strong>
                    ?
                </p>
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
                    @click="deletePeriode"
                />
            </template>
        </Dialog>

        <ImportError
            :errors="errors"
            :visible="showImportError"
            @update:visible="showImportError = $event"
            @line-imported="handleLineImported"
            @close="refreshTable"
        />

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
import Dialog from 'primevue/dialog';
import SplitButton from 'primevue/splitbutton';
import Toast from 'primevue/toast';
import PeriodeReposAdministrativeForm from '@/Components/Atfp/Annees/PeriodeReposAdministrativeForm.vue';

import ExcelJS from 'exceljs';

export default {
    components: {
        Button,
        InputText,
        DataTable,
        Column,
        Tag,
        Dialog,
        SplitButton,
        Toast,
        PeriodeReposAdministrativeForm,
    },
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            periodesRepos: [],
            filters: null,
            actions: [
                {
                    label: 'Modifier',
                    icon: 'pi pi-pencil',
                    command: () => this.editSelectedPeriode(),
                },
                {
                    label: 'Supprimer',
                    icon: 'pi pi-trash',
                    command: () => this.deleteSelectedPeriodes(),
                },
            ],
            formVisible: false,
            deleteFormVisible: false,
            selectedAnnee: null,
            periodeToEdit: null,
            periodeToDelete: null,
            loading: true,
            errors: [],
            showImportError: false,
        };
    },
    created() {
        this.initFilters();
        this.fetchPeriodesRepos();
    },
    methods: {
        formatDate(date) {
            if (!date) return null;
            if (typeof date === 'string') date = new Date(date);
            if (!(date instanceof Date) || isNaN(date.getTime())) return null;
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            return `${day}/${month}/${year}`;
        },
        clearFilter() {
            this.initFilters();
        },
        initFilters() {
            this.filters = {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                'annee_administrative.nom': {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
                'type_repos_administratif_fr.nom_fr': {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
                'option_repos_administratif_fr.nom_fr': {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
                'type_repos_administratif_ar.nom_ar': {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
                'option_repos_administratif_ar.nom_ar': {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
                statut: {
                    operator: FilterOperator.OR,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.EQUALS },
                    ],
                },
                description_fr: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.CONTAINS },
                    ],
                },
                description_ar: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.CONTAINS },
                    ],
                },
            };
        },
        openForm() {
            this.periodeToEdit = null;
            this.selectedAnnee = null;
            this.formVisible = true;
        },
        closeForm() {
            this.formVisible = false;
            this.selectedAnnee = null;
            this.periodeToEdit = null;
            this.fetchPeriodesRepos();
        },
        async fetchPeriodesRepos() {
            this.loading = true;
            try {
                const response = await axios.get(
                    '/api/periodes-repos-administrative'
                );
                this.periodesRepos = response.data.map((periode) => ({
                    ...periode,
                    date_debut: new Date(periode.date_debut),
                    date_fin: periode.date_fin
                        ? new Date(periode.date_fin)
                        : null,
                }));
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Erreur lors du chargement des périodes.',
                    life: 3000,
                });
            } finally {
                this.loading = false;
            }
        },
        editPeriode(periode) {
            this.periodeToEdit = { ...periode };
            this.selectedAnnee = periode.annee_administrative;
            this.formVisible = true;
        },
        editSelectedPeriode() {
            if (this.selectedPeriodes?.length === 1) {
                this.editPeriode(this.selectedPeriodes[0]);
            } else {
                this.toast.add({
                    severity: 'warn',
                    summary: 'Attention',
                    detail: 'Sélectionnez une seule période.',
                    life: 3000,
                });
            }
        },
        async handleSavePeriode(newPeriode) {
            try {
                const response = await axios.post(
                    '/api/periodes-repos-administrative',
                    newPeriode
                );
                this.periodesRepos.unshift(response.data);
                this.closeForm();
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Période ajoutée.',
                    life: 3000,
                });
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        'Erreur lors de l’ajout.',
                    life: 3000,
                });
            }
        },
        async handleUpdatePeriode(updatedPeriode) {
            try {
                const response = await axios.put(
                    `/api/periodes-repos-administrative/${updatedPeriode.id}`,
                    updatedPeriode
                );
                const index = this.periodesRepos.findIndex(
                    (p) => p.id === updatedPeriode.id
                );
                if (index !== -1)
                    this.periodesRepos.splice(index, 1, response.data);
                this.closeForm();
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Période mise à jour.',
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
        confirmDeletePeriode(periode) {
            this.periodeToDelete = periode;
            this.deleteFormVisible = true;
        },
        async deletePeriode() {
            if (this.periodeToDelete) {
                try {
                    await axios.delete(
                        `/api/periodes-repos-administrative/${this.periodeToDelete.id}`
                    );
                    this.periodesRepos = this.periodesRepos.filter(
                        (p) => p.id !== this.periodeToDelete.id
                    );
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Période supprimée.',
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
                    this.periodeToDelete = null;
                }
            }
        },
        async deleteSelectedPeriodes() {
            if (this.selectedPeriodes?.length > 0) {
                try {
                    await Promise.all(
                        this.selectedPeriodes.map((p) =>
                            axios.delete(
                                `/api/periodes-repos-administrative/${p.id}`
                            )
                        )
                    );
                    this.periodesRepos = this.periodesRepos.filter(
                        (p) =>
                            !this.selectedPeriodes.some(
                                (sel) => sel.id === p.id
                            )
                    );
                    this.selectedPeriodes = null;
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Périodes supprimées.',
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
                    detail: 'Sélectionnez au moins une période.',
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
                const file = event.target.files?.[0];
                if (!file) return;
                const formData = new FormData();
                formData.append('file', file);
                try {
                    const response = await axios.post(
                        '/api/periodes-repos-administrative/importxls',
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
                    await this.fetchPeriodesRepos();
                } catch (error) {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail:
                            error.response?.data?.message ||
                            "Erreur lors de l'import XLS.",
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
                const worksheet = workbook.addWorksheet(
                    'PeriodesReposAdministratives'
                );
                worksheet.columns = [
                    { header: 'Année Administrative', key: 'annee', width: 30 },
                    { header: 'Type (FR)', key: 'type_fr', width: 20 },
                    { header: 'Option (FR)', key: 'option_fr', width: 20 },
                    { header: 'Type (AR)', key: 'type_ar', width: 20 },
                    { header: 'Option (AR)', key: 'option_ar', width: 20 },
                    { header: 'Date Début', key: 'date_debut', width: 15 },
                    { header: 'Date Fin', key: 'date_fin', width: 15 },
                    { header: 'Nb Jours', key: 'nombre_jour', width: 10 },
                    {
                        header: 'Description (FR)',
                        key: 'description_fr',
                        width: 30,
                    },
                    {
                        header: 'Description (AR)',
                        key: 'description_ar',
                        width: 30,
                    },
                    { header: 'Statut', key: 'statut', width: 15 },
                ];
                this.periodesRepos.forEach((p) => {
                    worksheet.addRow({
                        annee: p.annee_administrative?.nom || '-',
                        type_fr: p.type_repos_administratif_fr?.nom_fr || '-',
                        option_fr:
                            p.option_repos_administratif_fr?.nom_fr || '-',
                        type_ar: p.type_repos_administratif_ar?.nom_ar || '-',
                        option_ar:
                            p.option_repos_administratif_ar?.nom_ar || '-',
                        date_debut: this.formatDate(p.date_debut),
                        date_fin: this.formatDate(p.date_fin) || '-',
                        nombre_jour: p.nombre_jour || '-',
                        description_fr: p.description_fr || '-',
                        description_ar: p.description_ar || '-',
                        statut: p.statut,
                    });
                });
                const buffer = await workbook.xlsx.writeBuffer();
                const blob = new Blob([buffer], {
                    type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'periodes_repos_administratives.xlsx';
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
        refreshTable() {
            this.fetchPeriodesRepos();
        },
    },
};
</script>

<style scoped>
.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #1f2937;
    padding: 0.8rem 1rem;
    border: none;
    border-radius: 0;
    margin-top: -2.2rem;
    margin-bottom: -2em;
}

.navbar-menu-left {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.navbar-menu-right {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.search-field {
    display: flex;
    align-items: center;
    height: 2.2rem;
    position: relative;
    margin-right: 1rem;
}

.input-search {
    height: 2.4rem;
    width: 20rem;
    padding-right: 2.5rem;
    padding-left: 2.5rem;
    box-sizing: border-box;
}

:deep(.p-input-icon-right) .pi {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
    pointer-events: none;
}

.navbar :deep(.p-button) {
    height: 2.2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0;
}

.navbar :deep(.p-button.p-button-text) {
    color: #ffffff;
    padding: 0.5rem;
}

.navbar :deep(.p-button.p-button-text:hover) {
    background-color: rgba(255, 255, 255, 0.1);
}

:deep(.p-datatable) {
    border-top: none;
    border-radius: 0;
}

:deep(.p-datatable-wrapper) {
    border-top: none;
}

.grid {
    margin-top: 0;
    padding-top: 0;
}
</style>
