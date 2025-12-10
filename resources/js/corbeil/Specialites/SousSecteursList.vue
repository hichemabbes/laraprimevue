<template>
    <div>
        <div class="flex justify-content-between align-items-center mb-3">
            <div class="text-2xl font-bold text-primary">
                Liste des Sous-Secteurs
            </div>
        </div>

        <DataTable
            v-model:filters="filters"
            showGridlines
            stripedRows
            v-model:selection="selected"
            :frozenValue="FixLignes"
            :value="table"
            dataKey="id"
            size="small"
            :rows="100"
            filterDisplay="menu"
            :globalFilterFields="[
                'code',
                'nom_fr',
                'nom_ar',
                'secteur.nom_ar',
                'secteur.code',
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
            <template #header>
                <div
                    class="flex justify-content-between mb-2 align-items-center"
                >
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
                            type="button"
                            icon="pi pi-times"
                            size="small"
                            severity="danger"
                            outlined
                            @click="clearFilter"
                        />
                    </div>
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

            <template #empty>
                <div v-if="!loading" class="text-center p-3">
                    Aucun sous-secteur trouvé.
                </div>
                <div v-else class="text-center p-3">Chargement en cours...</div>
            </template>

            <Column
                selectionMode="multiple"
                headerStyle="width: 3rem"
                frozen
                class="font-bold"
            ></Column>

            <Column
                field="code"
                header="Code"
                sortable
                style="min-width: 10rem"
                frozen
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
                field="secteur.nom_ar"
                header="Secteur"
                sortable
                style="min-width: 14rem"
            >
                <template #body="{ data }">
                    {{ data.secteur?.nom_ar || 'Non défini' }}
                </template>
                <template #filter="{ filterModel, filterCallback }">
                    <InputText
                        v-model="filterModel.value"
                        type="text"
                        class="p-column-filter"
                        placeholder="Rechercher par secteur (AR)"
                        @input="filterCallback"
                    />
                </template>
            </Column>

            <Column
                field="secteur.code"
                header="Code Secteur"
                sortable
                style="min-width: 10rem"
            >
                <template #body="{ data }">
                    {{ data.secteur?.code || 'Non défini' }}
                </template>
                <template #filter="{ filterModel, filterCallback }">
                    <InputText
                        v-model="filterModel.value"
                        type="text"
                        class="p-column-filter"
                        placeholder="Rechercher par code secteur"
                        @input="filterCallback"
                    />
                </template>
            </Column>

            <Column header="Actions" style="min-width: 14rem" frozen>
                <template #body="{ data, frozenRow, index }">
                    <div class="flex gap-2">
                        <Button
                            :icon="frozenRow ? 'pi pi-lock-open' : 'pi pi-lock'"
                            :disabled="
                                frozenRow ? false : FixLignes.length >= 10
                            "
                            severity="secondary"
                            text
                            @click="toggleLock(data, frozenRow, index)"
                            v-tooltip="'Fixer/Défixer la ligne'"
                        />
                        <Button
                            icon="pi pi-pencil"
                            severity="info"
                            text
                            @click="edit(data)"
                        />
                        <Button
                            icon="pi pi-trash"
                            severity="danger"
                            text
                            @click="confirmDelete(data)"
                        />
                    </div>
                </template>
            </Column>
        </DataTable>

        <Form
            :visible="formVisible"
            :initialData="ToEdit"
            :existingSousSecteurs="table"
            @update:visible="formVisible = $event"
            @save="handleSave"
            @update="handleUpdate"
            @close="closeForm"
        />

        <Dialog
            v-model:visible="deleteFormVisible"
            header="Confirmation de suppression"
            :modal="true"
            :style="{ width: '450px' }"
        >
            <div class="p-fluid">
                <p v-if="ToDelete">
                    Êtes-vous sûr de vouloir supprimer le sous-secteur
                    <strong>{{ ToDelete.nom_fr }}</strong> ?
                </p>
                <p v-else>
                    Êtes-vous sûr de vouloir supprimer les
                    {{ selected?.length }} sous-secteurs sélectionnés ?
                </p>
                <div class="mt-4">
                    <label
                        for="password"
                        class="block text-sm font-medium text-gray-700"
                        >Mot de passe</label
                    >
                    <InputText
                        id="password"
                        type="password"
                        v-model="password"
                        class="w-full mt-1"
                        placeholder="Entrez le mot de passe"
                    />
                    <small v-if="passwordError" class="text-red-500">{{
                        passwordError
                    }}</small>
                </div>
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
                    @click="confirmDeleteAction"
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
import SplitButton from 'primevue/splitbutton';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import Form from '@/corbeil/Specialites/SousSecteursForm.vue';
import ImportError from '@/corbeil/Specialites/SousSecteursImportError.vue';
import ExcelJS from 'exceljs';

export default {
    components: {
        Button,
        InputText,
        DataTable,
        Column,
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
            FixLignes: [],
            table: [],
            selected: null,
            filters: null,
            actions: [
                {
                    label: 'Modifier',
                    icon: 'pi pi-pencil',
                    command: () => this.editSelected(),
                },
                {
                    label: 'Supprimer',
                    icon: 'pi pi-trash',
                    command: () => this.confirmDeleteSelected(),
                },
            ],
            formVisible: false,
            deleteFormVisible: false,
            ToEdit: null,
            ToDelete: null,
            loading: true,
            errors: [],
            showImportError: false,
            password: '',
            passwordError: '',
        };
    },
    created() {
        this.initFilters();
        this.fetch();
    },
    methods: {
        openForm() {
            this.ToEdit = null;
            this.formVisible = true;
        },
        closeForm() {
            this.formVisible = false;
            this.ToEdit = null;
        },
        edit(element) {
            this.ToEdit = { ...element };
            this.formVisible = true;
        },
        editSelected() {
            if (this.selected?.length === 1) {
                const selectedItem =
                    this.FixLignes.find((s) => s.id === this.selected[0].id) ||
                    this.table.find((s) => s.id === this.selected[0].id);
                if (selectedItem) this.edit(selectedItem);
            } else {
                this.toast.add({
                    severity: 'warn',
                    summary: 'Attention',
                    detail: 'Sélectionnez un seul sous-secteur.',
                    life: 3000,
                });
            }
        },
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
                        { value: null, matchMode: FilterMatchMode.CONTAINS },
                    ],
                },
                nom_ar: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.CONTAINS },
                    ],
                },
                'secteur.nom_ar': {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.CONTAINS },
                    ],
                },
                'secteur.code': {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
            };
        },
        async fetch() {
            this.loading = true;
            try {
                const response = await axios.get('/api/sous-secteurs');
                this.table = response.data;
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Impossible de charger les sous-secteurs.',
                    life: 3000,
                });
            } finally {
                this.loading = false;
            }
        },
        async handleSave(newElement) {
            try {
                const response = await axios.post(
                    '/api/sous-secteurs',
                    newElement
                );
                this.table.unshift(response.data);
                this.formVisible = false;
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Sous-secteur ajouté.',
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
        async handleUpdate(updated) {
            try {
                const response = await axios.put(
                    `/api/sous-secteurs/${updated.id}`,
                    updated
                );
                const indexTable = this.table.findIndex(
                    (s) => s.id === updated.id
                );
                if (indexTable !== -1)
                    this.table.splice(indexTable, 1, response.data);
                const indexFixLignes = this.FixLignes.findIndex(
                    (s) => s.id === updated.id
                );
                if (indexFixLignes !== -1)
                    this.FixLignes.splice(indexFixLignes, 1, response.data);
                this.formVisible = false;
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Sous-secteur mis à jour.',
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
        confirmDelete(element) {
            this.ToDelete = element;
            this.deleteFormVisible = true;
        },
        confirmDeleteSelected() {
            if (this.selected?.length > 0) {
                this.ToDelete = null;
                this.deleteFormVisible = true;
            } else {
                this.toast.add({
                    severity: 'warn',
                    summary: 'Attention',
                    detail: 'Sélectionnez au moins un sous-secteur.',
                    life: 3000,
                });
            }
        },
        confirmDeleteAction() {
            if (this.password !== 'ha') {
                this.passwordError = 'Mot de passe incorrect.';
                return;
            }
            this.passwordError = '';
            if (this.ToDelete) {
                this.delete();
            } else if (this.selected?.length > 0) {
                this.deleteSelected();
            }
            this.deleteFormVisible = false;
            this.password = '';
        },
        async delete() {
            try {
                await axios.delete(`/api/sous-secteurs/${this.ToDelete.id}`);
                this.table = this.table.filter(
                    (s) => s.id !== this.ToDelete.id
                );
                this.FixLignes = this.FixLignes.filter(
                    (s) => s.id !== this.ToDelete.id
                );
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Sous-secteur supprimé.',
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
                this.ToDelete = null;
            }
        },
        async deleteSelected() {
            try {
                await axios.post('/api/sous-secteurs/delete-selected', {
                    ids: this.selected.map((s) => s.id),
                });
                this.table = this.table.filter(
                    (s) => !this.selected.some((sel) => sel.id === s.id)
                );
                this.FixLignes = this.FixLignes.filter(
                    (s) => !this.selected.some((sel) => sel.id === s.id)
                );
                this.selected = null;
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Sous-secteurs supprimés.',
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
        },
        toggleLock(data, frozen, index) {
            if (frozen) {
                this.FixLignes = this.FixLignes.filter((_, i) => i !== index);
                this.toast.add({
                    severity: 'info',
                    summary: 'Ligne libérée',
                    detail: "La ligne n'est plus fixée.",
                    life: 3000,
                });
            } else if (this.FixLignes.length < 10) {
                this.FixLignes.push(data);
                this.toast.add({
                    severity: 'success',
                    summary: 'Ligne fixée',
                    detail: 'La ligne a été fixée.',
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
                        '/api/sous-secteurs/importxls',
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
                    await this.refreshTable();
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
                const worksheet = workbook.addWorksheet('Sous-Secteurs');
                worksheet.columns = [
                    { header: 'Code', key: 'code', width: 15 },
                    { header: 'Nom (FR)', key: 'nom_fr', width: 30 },
                    { header: 'Nom (AR)', key: 'nom_ar', width: 30 },
                    { header: 'Secteur', key: 'secteur_nom_ar', width: 30 },
                    { header: 'Code Secteur', key: 'secteur_code', width: 15 },
                ];
                this.table.forEach((s) => {
                    worksheet.addRow({
                        code: s.code,
                        nom_fr: s.nom_fr,
                        nom_ar: s.nom_ar,
                        secteur_nom_ar: s.secteur?.nom_ar || 'Non défini',
                        secteur_code: s.secteur?.code || 'Non défini',
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
                link.download = 'sous-secteurs.xlsx';
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
            this.fetch();
        },
    },
};
</script>
