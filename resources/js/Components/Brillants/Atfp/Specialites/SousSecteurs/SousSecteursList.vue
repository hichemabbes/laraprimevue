<template>
    <div class="surface-ground pt-2 px-1.5 pb-1.5" style="margin-top: 0">
        <!-- Header Section with Navbar -->
        <div
            class="surface-card p-2 border-round border-1 surface-border"
            style="
                position: relative;
                top: -34px;
                box-shadow: none;
                margin-bottom: -32px;
            "
        >
            <div class="flex justify-content-between align-items-center">
                <div class="flex gap-3">
                    <Button
                        label="Accueil"
                        icon="pi pi-home"
                        class="p-button-text p-button-plain"
                        @click="$router.push({ name: 'dashboard' })"
                    />
                    <Button
                        label="Sous-Secteurs"
                        icon="pi pi-building"
                        class="p-button-text p-button-plain"
                        disabled
                    />
                </div>
                <div class="flex gap-2">
                    <Button
                        icon="pi pi-refresh"
                        class="p-button-text p-button-plain"
                        @click="fetchSousSecteurs"
                        v-tooltip="'Rafraîchir'"
                    />
                    <Button
                        icon="pi pi-file-import"
                        class="p-button-text p-button-plain import-icon"
                        @click="$refs.fileInput.click()"
                        v-tooltip="'Importer XLS'"
                    />
                    <input
                        type="file"
                        ref="fileInput"
                        style="display: none"
                        accept=".xls,.xlsx"
                        @change="importXls"
                    />
                    <Button
                        icon="pi pi-file-export"
                        class="p-button-text p-button-plain export-icon"
                        @click="exportXls"
                        v-tooltip="'Exporter XLS'"
                    />
                    <Button
                        icon="pi pi-trash"
                        class="p-button-text p-button-plain trash-icon"
                        @click="openTrashedDialog"
                        v-tooltip="'Sous-secteurs supprimés'"
                    />
                </div>
            </div>
        </div>

        <!-- Main Content Section -->
        <div
            class="surface-card p-4 pt-2 border-round shadow-2 border-1 surface-border"
        >
            <!-- Table Header with Actions -->
            <div class="flex justify-content-between align-items-center mb-4">
                <div class="flex align-items-center gap-2">
                    <span class="p-input-icon-left search-field">
                        <InputText
                            v-model="filters['global'].value"
                            placeholder="Rechercher..."
                        />
                    </span>
                    <Button
                        icon="pi pi-filter-slash"
                        class="p-button-outlined p-button-secondary"
                        size="small"
                        @click="clearFilter"
                        v-tooltip="'Réinitialiser les filtres'"
                    />
                </div>
                <div class="flex gap-2">
                    <Button
                        icon="pi pi-plus"
                        label="Ajouter"
                        class="p-button-success"
                        @click="openForm"
                    />
                    <Button
                        v-if="errors.length > 0"
                        icon="pi pi-exclamation-triangle"
                        class="p-button-warning p-button-outlined"
                        size="small"
                        @click="showImportError = true"
                        v-tooltip="'Erreurs d\'importation'"
                    />
                    <SplitButton
                        label="Actions"
                        icon="pi pi-ellipsis-v"
                        :model="actions"
                        class="p-button-text"
                    />
                </div>
            </div>

            <!-- Tableau -->
            <DataTable
                v-model:filters="filters"
                showGridlines
                stripedRows
                v-model:selection="selected"
                :frozenValue="FixLignes"
                :value="table"
                dataKey="id"
                size="small"
                paginator
                :rows="20"
                filterDisplay="menu"
                :globalFilterFields="[
                    'code',
                    'nom_fr',
                    'nom_ar',
                    'secteur.nom_fr',
                    'statut',
                    'observation',
                ]"
                :loading="loading"
                scrollable
                scrollHeight="750px"
                :pt="{
                    table: { style: 'min-width: 50rem' },
                    bodyrow: ({ props }) => ({
                        class: [{ 'font-bold': props.frozenRow }],
                    }),
                }"
            >
                <template #empty>
                    <div class="text-center py-4">
                        <i class="pi pi-inbox text-4xl text-400 mb-2" />
                        <p class="text-600">Aucun sous-secteur trouvé.</p>
                    </div>
                </template>

                <Column selectionMode="multiple" headerStyle="width: 3rem" />
                <Column
                    field="code"
                    header="Code"
                    sortable
                    style="min-width: 6rem"
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
                    header="Sous-secteur (FR)"
                    sortable
                    style="min-width: 20rem"
                >
                    <template #body="{ data }">
                        {{ data.nom_fr || '' }}
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
                    header="Sous-secteur (AR)"
                    sortable
                    style="min-width: 20rem"
                >
                    <template #body="{ data }">
                        <span class="arabic-normal">{{ data.nom_ar }}</span>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter arabic-gras"
                            placeholder="Rechercher par nom (AR)"
                            @input="filterCallback"
                        />
                    </template>
                </Column>
                <Column
                    field="secteur.nom_fr"
                    header="Secteur"
                    sortable
                    style="min-width: 15rem"
                >
                    <template #body="{ data }">
                        {{ data.secteur?.nom_fr || '' }}
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter"
                            placeholder="Rechercher par secteur"
                            @input="filterCallback"
                        />
                    </template>
                </Column>
                <Column
                    field="statut"
                    header="Statut"
                    sortable
                    style="min-width: 8rem"
                >
                    <template #body="{ data }">
                        <Tag
                            :value="data.statut"
                            :severity="getSeverity(data.statut)"
                            :icon="getStatutIcon(data.statut)"
                        />
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-model="filterModel.value"
                            :options="statuts"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Tous les statuts"
                            class="p-column-filter"
                            :showClear="true"
                            @change="filterCallback()"
                        >
                            <template #option="slotProps">
                                <Tag
                                    :value="slotProps.option.label"
                                    :severity="
                                        getSeverity(slotProps.option.value)
                                    "
                                    :icon="
                                        getStatutIcon(slotProps.option.value)
                                    "
                                />
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
                        <span class="arabic-normal">{{
                            data.observation || ''
                        }}</span>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter arabic-gras"
                            placeholder="Rechercher par observation"
                            @input="filterCallback"
                        />
                    </template>
                </Column>
                <Column header="Actions" style="min-width: 12rem" frozen>
                    <template #body="{ data, frozenRow, index }">
                        <div class="flex gap-2">
                            <Button
                                :icon="
                                    frozenRow ? 'pi pi-lock-open' : 'pi pi-lock'
                                "
                                :disabled="
                                    frozenRow ? false : FixLignes.length >= 10
                                "
                                text
                                size="small"
                                @click="toggleLock(data, frozenRow, index)"
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
                @update:visible="formVisible = $event"
                @save="handleSave"
                @update="handleUpdate"
                @close="closeForm"
            />

            <Dialog
                v-model:visible="deleteFormVisible"
                header="Confirmer la suppression"
                :modal="true"
                :style="{ width: '450px' }"
                :breakpoints="{ '960px': '75vw', '640px': '90vw' }"
                :pt="{
                    root: { class: 'border-none' },
                    mask: { style: 'backdrop-filter: blur(2px)' },
                    header: {
                        class: 'surface-50 border-bottom-1 surface-border',
                    },
                    content: { class: 'surface-50 py-0' },
                    footer: { class: 'surface-50 border-top-1 surface-border' },
                }"
            >
                <div class="confirmation-content flex flex-column gap-3 p-3">
                    <div class="flex align-items-center gap-3">
                        <i
                            class="pi pi-exclamation-triangle text-4xl text-red-500"
                        />
                        <span v-if="ToDelete">
                            Voulez-vous vraiment supprimer le sous-secteur
                            <b>{{ ToDelete.nom_fr || ToDelete.nom_ar }}</b> ?
                        </span>
                        <span v-else-if="selected?.length > 0">
                            Voulez-vous vraiment supprimer les
                            <b>{{ selected.length }}</b> sous-secteurs
                            sélectionnés ?
                        </span>
                    </div>
                    <div class="field">
                        <label for="deletePassword" class="font-semibold"
                            >Mot de passe de confirmation</label
                        >
                        <InputText
                            id="deletePassword"
                            v-model="deletePassword"
                            type="password"
                            class="w-full"
                            :class="{ 'p-invalid': passwordError }"
                            placeholder="Entrez le mot de passe"
                            @input="passwordError = ''"
                        />
                        <small v-if="passwordError" class="p-error">{{
                            passwordError
                        }}</small>
                    </div>
                </div>
                <template #footer>
                    <Button
                        label="Annuler"
                        icon="pi pi-times"
                        class="p-button-text"
                        @click="cancelDelete"
                    />
                    <Button
                        label="Supprimer"
                        icon="pi pi-check"
                        class="p-button-danger"
                        :disabled="loading || !deletePassword"
                        @click="confirmDeleteAction"
                    />
                </template>
            </Dialog>

            <Dialog
                v-model:visible="trashedVisible"
                header="Sous-secteurs supprimés"
                :modal="true"
                :style="{ width: '800px' }"
                :breakpoints="{ '960px': '80vw', '640px': '95vw' }"
                :pt="{
                    root: { class: 'border-none' },
                    mask: { style: 'backdrop-filter: blur(2px)' },
                    header: {
                        class: 'surface-50 border-bottom-1 surface-border',
                    },
                    content: { class: 'surface-50 p-4' },
                    footer: { class: 'surface-50 border-top-1 surface-border' },
                }"
            >
                <DataTable
                    :value="trashedSousSecteurs"
                    dataKey="id"
                    size="small"
                    paginator
                    :rows="10"
                    :loading="trashedLoading"
                    scrollable
                    scrollHeight="400px"
                    :pt="{ table: { style: 'min-width: 40rem' } }"
                >
                    <template #empty>
                        <div class="text-center py-4">
                            <i class="pi pi-inbox text-4xl text-400 mb-2" />
                            <p class="text-600">
                                Aucun sous-secteur supprimé trouvé.
                            </p>
                        </div>
                    </template>

                    <Column field="code" header="Code" style="min-width: 6rem">
                        <template #body="{ data }">
                            {{ data.code }}
                        </template>
                    </Column>
                    <Column
                        field="nom_fr"
                        header="Sous-secteur (FR)"
                        style="min-width: 15rem"
                    >
                        <template #body="{ data }">
                            {{ data.nom_fr || '' }}
                        </template>
                    </Column>
                    <Column
                        field="nom_ar"
                        header="Sous-secteur (AR)"
                        style="min-width: 15rem"
                    >
                        <template #body="{ data }">
                            <span class="arabic-normal">{{ data.nom_ar }}</span>
                        </template>
                    </Column>
                    <Column
                        field="secteur.nom_fr"
                        header="Secteur"
                        style="min-width: 15rem"
                    >
                        <template #body="{ data }">
                            {{ data.secteur?.nom_fr || '' }}
                        </template>
                    </Column>
                    <Column header="Actions" style="min-width: 8rem">
                        <template #body="{ data }">
                            <Button
                                icon="pi pi-undo"
                                severity="success"
                                text
                                @click="restoreSousSecteur(data)"
                                v-tooltip="'Restaurer'"
                            />
                        </template>
                    </Column>
                </DataTable>
                <template #footer>
                    <Button
                        label="Fermer"
                        icon="pi pi-times"
                        class="p-button-text"
                        @click="trashedVisible = false"
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
    </div>
</template>

<script>
import { FilterMatchMode, FilterOperator } from 'primevue/api';
import { useToast } from 'primevue/usetoast';
import axios from '@/axios.js';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Dropdown from 'primevue/dropdown';
import SplitButton from 'primevue/splitbutton';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import Form from '@/Components/Atfp/Specialites/SousSecteurs/SousSecteurForm.vue';
import ImportError from '@/Components/Atfp/ImportError/SousSecteursImportError.vue';
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
            statuts: [
                { label: 'Actif', value: 'Actif' },
                { label: 'Inactif', value: 'Inactif' },
            ],
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
            trashedVisible: false,
            trashedSousSecteurs: [],
            trashedLoading: false,
            ToEdit: null,
            ToDelete: null,
            loading: true,
            errors: [],
            showImportError: false,
            deletePassword: '',
            passwordError: '',
        };
    },
    created() {
        this.initFilters();
        this.fetchSousSecteurs();
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
                if (selectedItem) {
                    this.edit(selectedItem);
                } else {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'Sous-secteur non trouvé.',
                        life: 3000,
                    });
                }
            } else {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
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
                'secteur.nom_fr': {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.CONTAINS },
                    ],
                },
                statut: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.EQUALS },
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
        async fetchSousSecteurs() {
            this.loading = true;
            try {
                const response = await axios.get('api/sous-secteurs');
                this.table = response.data || [];
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Liste des sous-secteurs chargée.',
                    life: 3000,
                });
            } catch (error) {
                console.error(
                    'Erreur lors du chargement des sous-secteurs:',
                    error
                );
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
        async fetchTrashedSousSecteurs() {
            this.trashedLoading = true;
            try {
                const response = await axios.get('api/sous-secteurs/trashed');
                this.trashedSousSecteurs = Array.isArray(response.data)
                    ? response.data
                    : [];
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Liste des sous-secteurs supprimés chargée.',
                    life: 3000,
                });
            } catch (error) {
                console.error(
                    'Erreur lors du chargement des sous-secteurs supprimés:',
                    error
                );
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Impossible de charger les sous-secteurs supprimés.',
                    life: 3000,
                });
            } finally {
                this.trashedLoading = false;
            }
        },
        async restoreSousSecteur(sousSecteur) {
            if (!sousSecteur || !Number.isInteger(sousSecteur.id)) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'ID de sous-secteur invalide.',
                    life: 3000,
                });
                return;
            }
            try {
                await axios.post(`api/sous-secteurs/restore/${sousSecteur.id}`);
                this.trashedSousSecteurs = this.trashedSousSecteurs.filter(
                    (s) => s.id !== sousSecteur.id
                );
                await this.fetchSousSecteurs();
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Sous-secteur restauré.',
                    life: 3000,
                });
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        'Impossible de restaurer le sous-secteur.',
                    life: 3000,
                });
            }
        },
        openTrashedDialog() {
            this.trashedVisible = true;
            this.fetchTrashedSousSecteurs();
        },
        async handleSave(newElement) {
            try {
                await axios.post('api/sous-secteurs', newElement);
                await this.fetchSousSecteurs();
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
                    detail:
                        error.response?.data?.message ||
                        "Impossible d'ajouter le sous-secteur.",
                    life: 3000,
                });
            }
        },
        async handleUpdate(updated) {
            try {
                await axios.put(`api/sous-secteurs/${updated.id}`, updated);
                await this.fetchSousSecteurs();
                this.formVisible = false;
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Sous-secteur modifié.',
                    life: 3000,
                });
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        'Impossible de modifier le sous-secteur.',
                    life: 3000,
                });
            }
        },
        confirmDelete(element) {
            this.ToDelete = element;
            this.deletePassword = '';
            this.passwordError = '';
            this.deleteFormVisible = true;
        },
        confirmDeleteSelected() {
            if (this.selected?.length > 0) {
                this.ToDelete = null;
                this.deletePassword = '';
                this.passwordError = '';
                this.deleteFormVisible = true;
            } else {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Sélectionnez au moins un sous-secteur.',
                    life: 3000,
                });
            }
        },
        cancelDelete() {
            this.deleteFormVisible = false;
            this.ToDelete = null;
            this.deletePassword = '';
            this.passwordError = '';
        },
        async confirmDeleteAction() {
            try {
                this.loading = true;
                const cleanedPassword = this.deletePassword.trim();
                if (this.ToDelete) {
                    await axios.delete(
                        `api/sous-secteurs/${this.ToDelete.id}`,
                        {
                            data: { password: cleanedPassword },
                        }
                    );
                    await this.fetchSousSecteurs();
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Sous-secteur supprimé.',
                        life: 3000,
                    });
                } else if (this.selected?.length > 0) {
                    await axios.post('api/sous-secteurs/delete-selected', {
                        ids: this.selected.map((s) => s.id),
                        password: cleanedPassword,
                    });
                    await this.fetchSousSecteurs();
                    this.selected = null;
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Sous-secteurs supprimés.',
                        life: 3000,
                    });
                }
                this.deleteFormVisible = false;
                this.ToDelete = null;
                this.deletePassword = '';
                this.passwordError = '';
            } catch (error) {
                this.passwordError =
                    error.response?.data?.error ||
                    'Mot de passe incorrect ou erreur lors de la suppression.';
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: this.passwordError,
                    life: 3000,
                });
            } finally {
                this.loading = false;
            }
        },
        toggleLock(data, frozen, index) {
            if (frozen) {
                this.FixLignes = this.FixLignes.filter((_, i) => i !== index);
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Ligne libérée.',
                    life: 3000,
                });
            } else if (this.FixLignes.length < 10) {
                this.FixLignes.push(data);
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Ligne fixée.',
                    life: 3000,
                });
            }
        },
        async importXls(event) {
            const file = event.target.files?.[0];
            if (!file) return;
            const formData = new FormData();
            formData.append('file', file);
            try {
                const response = await axios.post(
                    'api/sous-secteurs/importxls',
                    formData,
                    {
                        headers: { 'Content-Type': 'multipart/form-data' },
                    }
                );
                this.errors = response.data.error_lines || [];
                this.toast.add({
                    severity: this.errors.length > 0 ? 'warn' : 'success',
                    summary: this.errors.length > 0 ? 'Attention' : 'Succès',
                    detail:
                        this.errors.length > 0
                            ? `${this.errors.length} erreurs lors de l'importation.`
                            : 'Importation réussie.',
                    life: 5000,
                });
                if (this.errors.length > 0) this.showImportError = true;
                await this.fetchSousSecteurs();
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        "Impossible d'importer le fichier.",
                    life: 5000,
                });
            } finally {
                event.target.value = '';
            }
        },
        handleLineImported(importedLine) {
            this.errors = this.errors.filter(
                (err) => err.line !== importedLine.line
            );
            this.toast.add({
                severity: 'success',
                summary: 'Succès',
                detail: 'Ligne importée.',
                life: 3000,
            });
        },
        async exportXls() {
            try {
                const workbook = new ExcelJS.Workbook();
                const worksheet = workbook.addWorksheet('Sous-secteurs');
                worksheet.columns = [
                    { header: 'Code', key: 'code', width: 15 },
                    { header: 'Nom (FR)', key: 'nom_fr', width: 30 },
                    { header: 'Nom (AR)', key: 'nom_ar', width: 30 },
                    {
                        header: 'Secteur (FR)',
                        key: 'secteur_nom_fr',
                        width: 30,
                    },
                    { header: 'Statut', key: 'statut', width: 20 },
                    {
                        header: 'Date Création',
                        key: 'date_creation',
                        width: 15,
                    },
                    {
                        header: 'Date Annulation',
                        key: 'date_annulation',
                        width: 15,
                    },
                    { header: 'Observation', key: 'observation', width: 30 },
                ];
                this.table.forEach((s) => {
                    worksheet.addRow({
                        code: s.code,
                        nom_fr: s.nom_fr,
                        nom_ar: s.nom_ar,
                        secteur_nom_fr: s.secteur?.nom_fr,
                        statut: s.statut,
                        date_creation: s.date_creation
                            ? this.formatDate(s.date_creation)
                            : null,
                        date_annulation: s.date_annulation
                            ? this.formatDate(s.date_annulation)
                            : null,
                        observation: s.observation,
                    });
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
                    summary: 'Succès',
                    detail: 'Exportation réussie.',
                    life: 3000,
                });
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: "Impossible d'exporter le fichier.",
                    life: 3000,
                });
            }
        },
        refreshTable() {
            this.fetchSousSecteurs();
        },
        normalizeStatut(statut) {
            return statut ? statut.toLowerCase() : '';
        },
        getSeverity(statut) {
            const normalizedStatut = this.normalizeStatut(statut);
            const statusSeverity = {
                actif: 'success',
                inactif: 'danger',
            };
            return statusSeverity[normalizedStatut] || 'secondary';
        },
        getStatutIcon(statut) {
            const normalizedStatut = this.normalizeStatut(statut);
            const statusIcons = {
                actif: 'pi pi-check',
                inactif: 'pi pi-times',
            };
            return statusIcons[normalizedStatut] || 'pi pi-info';
        },
        formatDate(date) {
            if (!date) return null;
            if (typeof date === 'string') date = new Date(date);
            if (!(date instanceof Date) || isNaN(date.getTime())) return null;
            return date.toISOString().split('T')[0];
        },
    },
};
</script>

<style scoped>
.confirmation-content {
    padding: 1rem;
}
.p-datatable {
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}
.p-datatable-sm .p-datatable-tbody > tr > td {
    padding: 0.5rem 1rem;
}
.p-datatable .p-column-header-content {
    justify-content: center;
}
.arabic-normal {
    font-family: 'Amiri', Arial, sans-serif;
    font-weight: normal;
    direction: rtl;
}
.arabic-gras {
    font-family: 'Amiri', Arial, sans-serif;
    font-weight: bold;
    direction: rtl;
}
.button-height {
    height: 3rem;
}
.field {
    margin-bottom: 1rem;
}
.p-error {
    color: #dc3545;
    font-size: 0.875rem;
}
label {
    margin-bottom: 0.5rem;
    display: block;
    color: #495057;
}
@media (prefers-color-scheme: dark) {
    label {
        color: #1f2a44;
    }
}
.import-icon i {
    color: #28a745; /* Green for import icon */
}
.export-icon i {
    color: #007bff; /* Blue for export icon */
}
.trash-icon i {
    color: #dc3545; /* Red for trash icon */
}
.p-tooltip-text {
    white-space: nowrap; /* Prevent tooltip text from wrapping */
}
</style>
