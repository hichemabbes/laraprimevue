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
                        label="Entreprises"
                        icon="pi pi-building"
                        class="p-button-text"
                        disabled
                    />
                    <Button
                        label="Secteurs"
                        icon="pi pi-sitemap"
                        class="p-button-text"
                        @click="$router.push({ name: 'secteurs' })"
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
                    <Button
                        text
                        icon="pi pi-minus"
                        size="small"
                        label="Tout Réduire"
                        @click="collapseAll"
                        v-tooltip="'Réduire tout'"
                    />
                </div>
            </div>
        </div>

        <!-- Tableau -->
        <div class="col-12">
            <div class="card">
                <DataTable
                    v-model:expandedRows="expandedRows"
                    v-model:filters="filters"
                    showGridlines
                    stripedRows
                    v-model:selection="selectedEntreprises"
                    :value="entreprises"
                    dataKey="id"
                    size="small"
                    paginator
                    :rows="100"
                    filterDisplay="menu"
                    :globalFilterFields="[
                        'nom_fr',
                        'nom_ar',
                        'code_entre',
                        'email_entr',
                        'secteur.nom_fr',
                    ]"
                    :loading="loading"
                    scrollable
                    scrollHeight="650px"
                    :pt="{ table: { style: 'min-width: 50rem' } }"
                >
                    <template #empty>
                        <div v-if="!loading" class="text-center p-3">
                            Aucune entreprise trouvée.
                        </div>
                        <div v-else class="text-center p-3">
                            Chargement en cours...
                        </div>
                    </template>

                    <Column expander style="width: 5rem" />
                    <Column
                        selectionMode="multiple"
                        headerStyle="width: 3rem"
                        frozen
                    ></Column>
                    <Column
                        field="code_entre"
                        header="Code"
                        sortable
                        style="width: 10rem"
                    >
                        <template #body="{ data }">
                            <span>{{ data.code_entre }}</span>
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
                        field="email_entr"
                        header="Email"
                        sortable
                        style="width: 15rem"
                    >
                        <template #body="{ data }">
                            <span>{{ data.email_entr }}</span>
                        </template>
                    </Column>
                    <Column
                        field="secteur.nom_fr"
                        header="Secteur"
                        sortable
                        style="width: 15rem"
                    >
                        <template #body="{ data }">
                            <span>{{ data.secteur?.nom_fr || 'N/A' }}</span>
                        </template>
                    </Column>
                    <Column header="Actions" style="width: 10rem" frozen>
                        <template #body="{ data }">
                            <div class="flex gap-2">
                                <Button
                                    icon="pi pi-pencil"
                                    severity="info"
                                    text
                                    @click="editEntreprise(data)"
                                />
                                <Button
                                    icon="pi pi-trash"
                                    severity="danger"
                                    text
                                    @click="confirmDeleteEntreprise(data)"
                                />
                            </div>
                        </template>
                    </Column>

                    <!-- Sous-table des tuteurs -->
                    <template #expansion="{ data }">
                        <div class="p-3 surface-100">
                            <div
                                class="flex justify-content-between align-items-center mb-2"
                            >
                                <h5 style="font-weight: bold">
                                    <span style="color: #93c5fd"
                                        >Tuteurs pour l'entreprise
                                    </span>
                                    <span style="color: #ef4444">{{
                                        data.nom_fr
                                    }}</span>
                                </h5>
                                <Button
                                    icon="pi pi-plus"
                                    severity="success"
                                    size="small"
                                    @click="openTuteurForm(data)"
                                />
                            </div>
                            <DataTable :value="data.tuteurs" size="small">
                                <Column
                                    field="nom_fr"
                                    header="Nom (FR)"
                                    style="width: 15rem"
                                ></Column>
                                <Column
                                    field="prenom_fr"
                                    header="Prénom (FR)"
                                    style="width: 15rem"
                                ></Column>
                                <Column
                                    field="cin"
                                    header="CIN"
                                    style="width: 10rem"
                                ></Column>
                                <Column
                                    field="telephone"
                                    header="Téléphone"
                                    style="width: 15rem"
                                ></Column>
                                <Column header="Actions" style="width: 10rem">
                                    <template #body="{ data }">
                                        <div class="flex gap-2">
                                            <Button
                                                icon="pi pi-pencil"
                                                severity="info"
                                                text
                                                @click="editTuteur(data)"
                                            />
                                            <Button
                                                icon="pi pi-trash"
                                                severity="danger"
                                                text
                                                @click="
                                                    confirmDeleteTuteur(data)
                                                "
                                            />
                                        </div>
                                    </template>
                                </Column>
                            </DataTable>
                        </div>
                    </template>
                </DataTable>
            </div>
        </div>

        <EntrepriseForm
            :visible="formVisible"
            :entrepriseToEdit="entrepriseToEdit"
            @update:visible="formVisible = $event"
            @save="handleSaveEntreprise"
            @update="handleUpdateEntreprise"
            @close="closeForm"
        />

        <TuteurForm
            :visible="tuteurFormVisible"
            :entreprise="selectedEntreprise"
            :tuteurToEdit="tuteurToEdit"
            @update:visible="tuteurFormVisible = $event"
            @save="handleSaveTuteur"
            @update="handleUpdateTuteur"
            @close="closeTuteurForm"
        />

        <Dialog
            v-model:visible="deleteFormVisible"
            header="Confirmation de suppression"
            :modal="true"
            :style="{ width: '450px' }"
        >
            <div class="p-fluid">
                <p v-if="entrepriseToDelete">
                    Êtes-vous sûr de vouloir supprimer l’entreprise
                    <strong>{{ entrepriseToDelete.nom_fr }}</strong> ?
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
                    @click="deleteEntreprise"
                />
            </template>
        </Dialog>

        <Dialog
            v-model:visible="deleteTuteurFormVisible"
            header="Confirmation de suppression"
            :modal="true"
            :style="{ width: '450px' }"
        >
            <div class="p-fluid">
                <p v-if="tuteurToDelete">
                    Êtes-vous sûr de vouloir supprimer le tuteur
                    <strong
                        >{{ tuteurToDelete.nom_fr }}
                        {{ tuteurToDelete.prenom_fr }}</strong
                    >
                    ?
                </p>
            </div>
            <template #footer>
                <Button
                    label="Non"
                    icon="pi pi-times"
                    text
                    @click="deleteTuteurFormVisible = false"
                />
                <Button
                    label="Oui"
                    icon="pi pi-check"
                    text
                    @click="deleteTuteur"
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
import EntrepriseForm from '@/Components/Atfp/Entreprises/EntrepriseForm.vue';
import TuteurForm from '@/Components/Atfp/Entreprises/TuteurForm.vue'; // À créer séparément
//import ImportError from '@/Components/Atfp/ImportError/EntreprisesImportError.vue'; // À adapter si nécessaire
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
        EntrepriseForm,
        TuteurForm,
        //  ImportError
    },
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            entreprises: [],
            selectedEntreprises: null,
            filters: null,
            actions: [
                {
                    label: 'Modifier',
                    icon: 'pi pi-pencil',
                    command: () => this.editSelectedEntreprise(),
                },
                {
                    label: 'Supprimer',
                    icon: 'pi pi-trash',
                    command: () => this.deleteSelectedEntreprises(),
                },
            ],
            formVisible: false,
            tuteurFormVisible: false,
            deleteFormVisible: false,
            deleteTuteurFormVisible: false,
            entrepriseToEdit: null,
            entrepriseToDelete: null,
            selectedEntreprise: null,
            tuteurToEdit: null,
            tuteurToDelete: null,
            loading: true,
            expandedRows: [],
            errors: [],
            showImportError: false,
        };
    },
    created() {
        this.initFilters();
        this.fetchEntreprises();
    },
    methods: {
        clearFilter() {
            this.initFilters();
        },
        initFilters() {
            this.filters = {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                code_entre: {
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
                email_entr: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.CONTAINS },
                    ],
                },
                'secteur.nom_fr': {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
            };
        },
        openForm() {
            this.entrepriseToEdit = null;
            this.formVisible = true;
        },
        closeForm() {
            this.formVisible = false;
            this.entrepriseToEdit = null;
            this.fetchEntreprises();
        },
        openTuteurForm(entreprise) {
            this.selectedEntreprise = entreprise;
            this.tuteurToEdit = null;
            this.tuteurFormVisible = true;
        },
        closeTuteurForm() {
            this.tuteurFormVisible = false;
            this.selectedEntreprise = null;
            this.tuteurToEdit = null;
            this.fetchEntreprises();
        },
        collapseAll() {
            this.expandedRows = [];
        },
        async fetchEntreprises() {
            this.loading = true;
            try {
                const response = await axios.get('/api/entreprises');
                this.entreprises = response.data;
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Erreur lors du chargement des entreprises.',
                    life: 3000,
                });
            } finally {
                this.loading = false;
            }
        },
        editEntreprise(entreprise) {
            this.entrepriseToEdit = { ...entreprise };
            this.formVisible = true;
        },
        editSelectedEntreprise() {
            if (this.selectedEntreprises?.length === 1) {
                this.editEntreprise(this.selectedEntreprises[0]);
            } else {
                this.toast.add({
                    severity: 'warn',
                    summary: 'Attention',
                    detail: 'Sélectionnez une seule entreprise.',
                    life: 3000,
                });
            }
        },
        async handleSaveEntreprise(newEntreprise) {
            try {
                const response = await axios.post(
                    '/api/entreprises',
                    newEntreprise
                );
                this.entreprises.unshift(response.data);
                this.formVisible = false;
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Entreprise ajoutée.',
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
        async handleUpdateEntreprise(updatedEntreprise) {
            try {
                const response = await axios.put(
                    `/api/entreprises/${updatedEntreprise.id}`,
                    updatedEntreprise
                );
                const index = this.entreprises.findIndex(
                    (e) => e.id === updatedEntreprise.id
                );
                if (index !== -1)
                    this.entreprises.splice(index, 1, response.data);
                this.formVisible = false;
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Entreprise mise à jour.',
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
        confirmDeleteEntreprise(entreprise) {
            this.entrepriseToDelete = entreprise;
            this.deleteFormVisible = true;
        },
        async deleteEntreprise() {
            if (this.entrepriseToDelete) {
                try {
                    await axios.delete(
                        `/api/entreprises/${this.entrepriseToDelete.id}`
                    );
                    this.entreprises = this.entreprises.filter(
                        (e) => e.id !== this.entrepriseToDelete.id
                    );
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Entreprise supprimée.',
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
                    this.entrepriseToDelete = null;
                }
            }
        },
        async deleteSelectedEntreprises() {
            if (this.selectedEntreprises?.length > 0) {
                try {
                    await Promise.all(
                        this.selectedEntreprises.map((e) =>
                            axios.delete(`/api/entreprises/${e.id}`)
                        )
                    );
                    this.entreprises = this.entreprises.filter(
                        (e) =>
                            !this.selectedEntreprises.some(
                                (sel) => sel.id === e.id
                            )
                    );
                    this.selectedEntreprises = null;
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Entreprises supprimées.',
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
                    detail: 'Sélectionnez au moins une entreprise.',
                    life: 3000,
                });
            }
        },
        editTuteur(tuteur) {
            this.tuteurToEdit = { ...tuteur };
            this.selectedEntreprise = this.entreprises.find(
                (e) => e.id === tuteur.entreprise_id
            );
            this.tuteurFormVisible = true;
        },
        confirmDeleteTuteur(tuteur) {
            this.tuteurToDelete = tuteur;
            this.deleteTuteurFormVisible = true;
        },
        async deleteTuteur() {
            if (this.tuteurToDelete) {
                try {
                    await axios.delete(
                        `/api/tuteurs/${this.tuteurToDelete.id}`
                    );
                    const entreprise = this.entreprises.find(
                        (e) => e.id === this.tuteurToDelete.entreprise_id
                    );
                    if (entreprise) {
                        entreprise.tuteurs = entreprise.tuteurs.filter(
                            (t) => t.id !== this.tuteurToDelete.id
                        );
                    }
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Tuteur supprimé.',
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
                    this.deleteTuteurFormVisible = false;
                    this.tuteurToDelete = null;
                }
            }
        },
        async handleSaveTuteur(newTuteur) {
            try {
                const response = await axios.post('/api/tuteurs', newTuteur);
                const entreprise = this.entreprises.find(
                    (e) => e.id === newTuteur.entreprise_id
                );
                if (entreprise) {
                    entreprise.tuteurs.push(response.data);
                }
                this.tuteurFormVisible = false;
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Tuteur ajouté.',
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
        async handleUpdateTuteur(updatedTuteur) {
            try {
                const response = await axios.put(
                    `/api/tuteurs/${updatedTuteur.id}`,
                    updatedTuteur
                );
                const entreprise = this.entreprises.find(
                    (e) => e.id === updatedTuteur.entreprise_id
                );
                if (entreprise) {
                    const index = entreprise.tuteurs.findIndex(
                        (t) => t.id === updatedTuteur.id
                    );
                    if (index !== -1)
                        entreprise.tuteurs.splice(index, 1, response.data);
                }
                this.tuteurFormVisible = false;
                this.tuteurToEdit = null;
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Tuteur mis à jour.',
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
                        '/api/entreprises/importxls',
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
                    await this.fetchEntreprises();
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
                const worksheet = workbook.addWorksheet('Entreprises');
                worksheet.columns = [
                    { header: 'Code', key: 'code_entre', width: 15 },
                    { header: 'Nom (FR)', key: 'nom_fr', width: 30 },
                    { header: 'Nom (AR)', key: 'nom_ar', width: 30 },
                    { header: 'Email', key: 'email_entr', width: 25 },
                    { header: 'Secteur', key: 'secteur_nom_fr', width: 20 },
                ];
                this.entreprises.forEach((e) => {
                    worksheet.addRow({
                        code_entre: e.code_entre,
                        nom_fr: e.nom_fr,
                        nom_ar: e.nom_ar,
                        email_entr: e.email_entr,
                        secteur_nom_fr: e.secteur?.nom_fr || 'N/A',
                    });
                });
                const buffer = await workbook.xlsx.writeBuffer();
                const blob = new Blob([buffer], {
                    type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'entreprises.xlsx';
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
            this.fetchEntreprises();
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
