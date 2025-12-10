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
                        label="Groupes"
                        icon="pi pi-users"
                        class="p-button-text"
                        disabled
                    />
                    <Button
                        label="Programmes"
                        icon="pi pi-book"
                        class="p-button-text"
                        @click="$router.push({ name: 'programmes' })"
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
                    v-model:selection="selectedGroupes"
                    :value="groupes"
                    dataKey="id"
                    size="small"
                    paginator
                    :rows="100"
                    filterDisplay="menu"
                    :globalFilterFields="[
                        'code_groupe',
                        'nom_fr',
                        'nom_ar',
                        'programme.nom_fr',
                        'tuteur.nom_fr',
                    ]"
                    :loading="loading"
                    scrollable
                    scrollHeight="650px"
                    :pt="{ table: { style: 'min-width: 50rem' } }"
                >
                    <template #empty>
                        <div v-if="!loading" class="text-center p-3">
                            Aucun groupe trouvé.
                        </div>
                        <div v-else class="text-center p-3">
                            Chargement en cours...
                        </div>
                    </template>

                    <Column
                        selectionMode="multiple"
                        headerStyle="width: 3rem"
                        frozen
                    ></Column>
                    <Column
                        field="code_groupe"
                        header="Code"
                        sortable
                        style="width: 10rem"
                    >
                        <template #body="{ data }">
                            <span>{{ data.code_groupe }}</span>
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
                        style="width: 15rem"
                    >
                        <template #body="{ data }">
                            <span>{{ data.nom_fr }}</span>
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
                        style="width: 15rem"
                    >
                        <template #body="{ data }">
                            <span>{{ data.nom_ar }}</span>
                        </template>
                    </Column>
                    <Column
                        field="programme.nom_fr"
                        header="Programme"
                        sortable
                        style="width: 15rem"
                    >
                        <template #body="{ data }">
                            <span>{{ data.programme?.nom_fr || 'N/A' }}</span>
                        </template>
                    </Column>
                    <Column
                        field="tuteur.nom_fr"
                        header="Tuteur"
                        sortable
                        style="width: 15rem"
                    >
                        <template #body="{ data }">
                            <span>{{
                                data.tuteur
                                    ? `${data.tuteur.nom_fr} ${data.tuteur.prenom_fr}`
                                    : 'N/A'
                            }}</span>
                        </template>
                    </Column>
                    <Column header="Actions" style="width: 10rem" frozen>
                        <template #body="{ data }">
                            <div class="flex gap-2">
                                <Button
                                    icon="pi pi-pencil"
                                    severity="info"
                                    text
                                    @click="editGroupe(data)"
                                />
                                <Button
                                    icon="pi pi-trash"
                                    severity="danger"
                                    text
                                    @click="confirmDeleteGroupe(data)"
                                />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>

        <GroupeForm
            :visible="formVisible"
            :groupeToEdit="groupeToEdit"
            @update:visible="formVisible = $event"
            @save="handleSaveGroupe"
            @update="handleUpdateGroupe"
            @close="closeForm"
        />

        <Dialog
            v-model:visible="deleteFormVisible"
            header="Confirmation de suppression"
            :modal="true"
            :style="{ width: '450px' }"
        >
            <div class="p-fluid">
                <p v-if="groupeToDelete">
                    Êtes-vous sûr de vouloir supprimer le groupe
                    <strong>{{ groupeToDelete.nom_fr }}</strong> ?
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
                    @click="deleteGroupe"
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
import Dialog from 'primevue/dialog';
import SplitButton from 'primevue/splitbutton';
import Toast from 'primevue/toast';
import GroupeForm from '@/Components/Atfp/Groupes/GroupeForm.vue';
//import ImportError from '@/Components/Atfp/ImportError/GroupesImportError.vue'; // À adapter si nécessaire
import ExcelJS from 'exceljs';

export default {
    components: {
        Button,
        InputText,
        DataTable,
        Column,
        Dialog,
        SplitButton,
        Toast,
        GroupeForm,
        // ImportError
    },
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            groupes: [],
            selectedGroupes: null,
            filters: null,
            actions: [
                {
                    label: 'Modifier',
                    icon: 'pi pi-pencil',
                    command: () => this.editSelectedGroupe(),
                },
                {
                    label: 'Supprimer',
                    icon: 'pi pi-trash',
                    command: () => this.deleteSelectedGroupes(),
                },
            ],
            formVisible: false,
            deleteFormVisible: false,
            groupeToEdit: null,
            groupeToDelete: null,
            loading: true,
            errors: [],
            showImportError: false,
        };
    },
    created() {
        this.initFilters();
        this.fetchGroupes();
    },
    methods: {
        clearFilter() {
            this.initFilters();
        },
        initFilters() {
            this.filters = {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                code_groupe: {
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
                'programme.nom_fr': {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
                'tuteur.nom_fr': {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
            };
        },
        openForm() {
            this.groupeToEdit = null;
            this.formVisible = true;
        },
        closeForm() {
            this.formVisible = false;
            this.groupeToEdit = null;
            this.fetchGroupes();
        },
        async fetchGroupes() {
            this.loading = true;
            try {
                const response = await axios.get('/api/groupes');
                this.groupes = response.data;
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Erreur lors du chargement des groupes.',
                    life: 3000,
                });
            } finally {
                this.loading = false;
            }
        },
        editGroupe(groupe) {
            this.groupeToEdit = { ...groupe };
            this.formVisible = true;
        },
        editSelectedGroupe() {
            if (this.selectedGroupes?.length === 1) {
                this.editGroupe(this.selectedGroupes[0]);
            } else {
                this.toast.add({
                    severity: 'warn',
                    summary: 'Attention',
                    detail: 'Sélectionnez un seul groupe.',
                    life: 3000,
                });
            }
        },
        async handleSaveGroupe(newGroupe) {
            try {
                const response = await axios.post('/api/groupes', newGroupe);
                this.groupes.unshift(response.data);
                this.formVisible = false;
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Groupe ajouté.',
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
        async handleUpdateGroupe(updatedGroupe) {
            try {
                const response = await axios.put(
                    `/api/groupes/${updatedGroupe.id}`,
                    updatedGroupe
                );
                const index = this.groupes.findIndex(
                    (g) => g.id === updatedGroupe.id
                );
                if (index !== -1) this.groupes.splice(index, 1, response.data);
                this.formVisible = false;
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Groupe mis à jour.',
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
        confirmDeleteGroupe(groupe) {
            this.groupeToDelete = groupe;
            this.deleteFormVisible = true;
        },
        async deleteGroupe() {
            if (this.groupeToDelete) {
                try {
                    await axios.delete(
                        `/api/groupes/${this.groupeToDelete.id}`
                    );
                    this.groupes = this.groupes.filter(
                        (g) => g.id !== this.groupeToDelete.id
                    );
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Groupe supprimé.',
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
                    this.groupeToDelete = null;
                }
            }
        },
        async deleteSelectedGroupes() {
            if (this.selectedGroupes?.length > 0) {
                try {
                    await Promise.all(
                        this.selectedGroupes.map((g) =>
                            axios.delete(`/api/groupes/${g.id}`)
                        )
                    );
                    this.groupes = this.groupes.filter(
                        (g) =>
                            !this.selectedGroupes.some((sel) => sel.id === g.id)
                    );
                    this.selectedGroupes = null;
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Groupes supprimés.',
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
                    detail: 'Sélectionnez au moins un groupe.',
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
                        '/api/groupes/importxls',
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
                    await this.fetchGroupes();
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
                const worksheet = workbook.addWorksheet('Groupes');
                worksheet.columns = [
                    { header: 'Code', key: 'code_groupe', width: 15 },
                    { header: 'Nom (FR)', key: 'nom_fr', width: 30 },
                    { header: 'Nom (AR)', key: 'nom_ar', width: 30 },
                    { header: 'Programme', key: 'programme_nom_fr', width: 20 },
                    { header: 'Tuteur', key: 'tuteur_nom', width: 20 },
                ];
                this.groupes.forEach((g) => {
                    worksheet.addRow({
                        code_groupe: g.code_groupe,
                        nom_fr: g.nom_fr,
                        nom_ar: g.nom_ar,
                        programme_nom_fr: g.programme?.nom_fr || 'N/A',
                        tuteur_nom: g.tuteur
                            ? `${g.tuteur.nom_fr} ${g.tuteur.prenom_fr}`
                            : 'N/A',
                    });
                });
                const buffer = await workbook.xlsx.writeBuffer();
                const blob = new Blob([buffer], {
                    type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'groupes.xlsx';
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
            this.fetchGroupes();
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
