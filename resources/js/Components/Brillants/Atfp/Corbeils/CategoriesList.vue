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
                        label="Tableau Types de Catégories"
                        icon="pi pi-list"
                        class="p-button-text p-button-plain"
                        disabled
                    />
                    <Button
                        label="Catégories & Listes"
                        icon="pi pi-home"
                        class="p-button-text p-button-plain"
                        @click="$router.push({ name: 'options-listes' })"
                    />
                </div>
                <div class="flex gap-2">
                    <Button
                        icon="pi pi-refresh"
                        class="p-button-text p-button-plain"
                        @click="fetchTypesCategories"
                        v-tooltip="'Rafraîchir'"
                    />
                    <Button
                        icon="pi pi-file-export"
                        class="p-button-text p-button-plain"
                        @click="exportXLS"
                        v-tooltip="'Exporter XLS'"
                    />
                    <Button
                        icon="pi pi-trash"
                        class="p-button-text p-button-plain trash-icon"
                        @click="openTrashedDialog"
                        v-tooltip="'Catégories supprimées'"
                    />
                    <Button
                        v-if="isSuperAdmin"
                        icon="pi pi-key"
                        label="Gérer mot de passe"
                        class="p-button-text p-button-plain"
                        @click="$router.push({ name: 'password-manager' })"
                        v-tooltip="'Gérer le mot de passe de suppression'"
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
                            class="w-full"
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
                    <SplitButton
                        label="Actions"
                        icon="pi pi-ellipsis-v"
                        :model="actions"
                        class="p-button-text"
                    />
                </div>
            </div>

            <!-- Main DataTable -->
            <DataTable
                v-model:filters="filters"
                v-model:selection="selectedTypes"
                :value="typesCategories"
                dataKey="id"
                size="small"
                stripedRows
                paginator
                :rows="20"
                :rowsPerPageOptions="[10, 20, 50]"
                filterDisplay="menu"
                :globalFilterFields="['nom_fr', 'nom_ar']"
                :loading="loading"
                scrollable
                scrollHeight="700px"
                removableSort
                class="p-datatable-sm border-1 surface-border"
            >
                <template #empty>
                    <div class="text-center py-4">
                        <i class="pi pi-inbox text-4xl text-400 mb-2" />
                        <p class="text-600">Aucun type de catégorie trouvé</p>
                    </div>
                </template>

                <Column
                    selectionMode="multiple"
                    headerStyle="width: 3rem"
                    frozen
                />

                <Column
                    field="nom_fr"
                    header="Nom (Français)"
                    sortable
                    style="min-width: 20rem"
                >
                    <template #body="{ data }">
                        <span class="font-medium">{{ data.nom_fr }}</span>
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
                    header="Nom (Arabe)"
                    sortable
                    style="min-width: 20rem"
                >
                    <template #body="{ data }">
                        <span>{{ data.nom_ar }}</span>
                    </template>
                </Column>

                <Column
                    field="description_fr"
                    header="Description (Français)"
                    sortable
                    style="min-width: 25rem"
                >
                    <template #body="{ data }">
                        <span>{{ data.description_fr || 'Non définie' }}</span>
                    </template>
                </Column>

                <Column
                    field="est_predefini"
                    header="Prédéfini"
                    sortable
                    style="min-width: 10rem"
                >
                    <template #body="{ data }">
                        <Tag
                            :value="data.est_predefini ? 'Oui' : 'Non'"
                            :severity="
                                data.est_predefini ? 'success' : 'warning'
                            "
                        />
                    </template>
                </Column>

                <Column header="Listes" style="min-width: 30rem">
                    <template #body="{ data }">
                        <Dropdown
                            v-model="selectedListes[data.id]"
                            :options="data.listes || []"
                            optionLabel="nom_fr"
                            placeholder="Sélectionner une liste"
                            class="w-full"
                            :emptyMessage="'Aucune liste disponible'"
                            :disabled="!data.listes || data.listes.length === 0"
                        />
                    </template>
                </Column>

                <Column header="Actions" style="min-width: 12rem" frozen>
                    <template #body="{ data }">
                        <div class="flex gap-1">
                            <Button
                                icon="pi pi-pencil"
                                class="p-button-rounded p-button-text p-button-sm"
                                @click="editType(data)"
                                v-tooltip="'Modifier'"
                            />
                            <Button
                                icon="pi pi-trash"
                                class="p-button-rounded p-button-text p-button-danger p-button-sm"
                                @click="confirmDeleteType(data)"
                                v-tooltip="'Supprimer'"
                            />
                            <Button
                                icon="pi pi-plus"
                                class="p-button-rounded p-button-text p-button-success p-button-sm"
                                @click="openOptionForm(data)"
                                v-tooltip="'Ajouter une liste'"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- Formulaire Type de Catégorie -->
        <CategorieForm
            :visible="typeFormVisible"
            :typeToEdit="typeToEdit"
            @update:visible="typeFormVisible = $event"
            @save="handleSaveType"
            @update="handleUpdateType"
            @close="closeTypeForm"
        />

        <!-- Formulaire Option -->
        <OptionForm
            :visible="optionFormVisible"
            :typeId="selectedTypeId"
            :optionToEdit="optionToEdit"
            @update:visible="optionFormVisible = $event"
            @save="handleSaveOption"
            @update="handleUpdateOption"
            @close="closeOptionForm"
        />

        <!-- Confirmation de suppression -->
        <Dialog
            v-model:visible="deleteFormVisible"
            header="Confirmer la suppression"
            :modal="true"
            :style="{ width: '450px' }"
            :breakpoints="{ '960px': '75vw', '640px': '90vw' }"
            :pt="{
                root: 'border-none',
                mask: { style: 'backdrop-filter: blur(2px)' },
                header: { class: 'surface-50 border-bottom-1 surface-border' },
                content: { class: 'surface-50 py-0' },
                footer: { class: 'surface-50 border-top-1 surface-border' },
            }"
        >
            <div class="confirmation-content flex flex-column gap-3 p-3">
                <div class="flex align-items-center gap-3">
                    <i
                        class="pi pi-exclamation-triangle text-4xl text-red-500"
                    />
                    <span>
                        Voulez-vous vraiment supprimer le type
                        <b>{{ typeToDelete?.nom_fr }}</b> ?<br />
                        <small class="text-red-500"
                            >Attention : Cela supprimera également toutes les
                            listes associées !</small
                        >
                    </span>
                </div>
                <div class="field">
                    <label for="deleteTypePassword" class="font-semibold"
                        >Mot de passe de confirmation</label
                    >
                    <InputText
                        id="deleteTypePassword"
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
                    @click="cancelDeleteType"
                />
                <Button
                    label="Supprimer"
                    icon="pi pi-check"
                    class="p-button-danger"
                    :disabled="loading || !deletePassword"
                    @click="deleteType"
                />
            </template>
        </Dialog>

        <!-- Dialog for Trashed Listes -->
        <Dialog
            v-model:visible="trashedVisible"
            header="Catégories supprimées"
            :modal="true"
            :style="{ width: '800px' }"
            :breakpoints="{ '960px': '80vw', '640px': '95vw' }"
            :pt="{
                root: { class: 'border-none' },
                mask: { style: 'backdrop-filter: blur(2px)' },
                header: { class: 'surface-50 border-bottom-1 surface-border' },
                content: { class: 'surface-50 p-4' },
                footer: { class: 'surface-50 border-top-1 surface-border' },
            }"
        >
            <DataTable
                :value="trashedCategories"
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
                            Aucune catégorie supprimée trouvée.
                        </p>
                    </div>
                </template>

                <Column
                    field="nom_fr"
                    header="Nom (FR)"
                    style="min-width: 15rem"
                >
                    <template #body="{ data }">
                        {{ data.nom_fr || '' }}
                    </template>
                </Column>
                <Column
                    field="nom_ar"
                    header="Nom (AR)"
                    style="min-width: 15rem"
                >
                    <template #body="{ data }">
                        <span class="arabic-normal">{{ data.nom_ar }}</span>
                    </template>
                </Column>
                <Column header="Actions" style="min-width: 8rem">
                    <template #body="{ data }">
                        <Button
                            icon="pi pi-undo"
                            severity="success"
                            text
                            @click="restoreCategory(data)"
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

        <Toast position="top-right" />
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
import Dialog from 'primevue/dialog';
import SplitButton from 'primevue/splitbutton';
import Toast from 'primevue/toast';
import CategorieForm from '@/Components/Atfp/Corbeils/CategorieForm.vue';
import OptionForm from '@/Components/Atfp/Listes/OptionForm.vue';
import ExcelJS from 'exceljs';

export default {
    components: {
        Button,
        InputText,
        DataTable,
        Column,
        Tag,
        Dropdown,
        Dialog,
        SplitButton,
        Toast,
        CategorieForm,
        OptionForm,
    },
    data() {
        return {
            typesCategories: [],
            selectedTypes: null,
            selectedListes: {},
            filters: null,
            actions: [
                {
                    label: 'Modifier',
                    icon: 'pi pi-pencil',
                    command: () => this.editSelectedType(),
                },
                {
                    label: 'Supprimer',
                    icon: 'pi pi-trash',
                    command: () => this.deleteSelectedTypes(),
                },
            ],
            loading: false,
            typeFormVisible: false,
            optionFormVisible: false,
            deleteFormVisible: false,
            trashedVisible: false,
            trashedCategories: [],
            trashedLoading: false,
            typeToEdit: null,
            typeToDelete: null,
            selectedTypeId: null,
            optionToEdit: null,
            deletePassword: '',
            passwordError: '',
            isSuperAdmin: false,
        };
    },
    setup() {
        const toast = useToast();
        return { toast };
    },
    created() {
        this.initFilters();
        this.fetchTypesCategories();
        this.checkUserRole();
    },
    methods: {
        initFilters() {
            this.filters = {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
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
        clearFilter() {
            this.initFilters();
        },
        async checkUserRole() {
            const token = localStorage.getItem('token');
            if (!token) {
                this.$router.push({ name: 'login' });
                this.isSuperAdmin = false;
                return;
            }
            try {
                const response = await axios.get('/api/user', {
                    headers: {
                        Authorization: `Bearer ${token}`,
                    },
                });
                const roles = response.data.user?.roles || [];
                this.isSuperAdmin = roles.some(
                    (role) => role.name === 'SuperAdmin'
                );
            } catch (error) {
                this.isSuperAdmin = false;
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        'Erreur lors de la vérification du rôle : ' +
                        (error.response?.data?.message || error.message),
                    life: 3000,
                });
                if (error.response?.status === 401) {
                    localStorage.removeItem('token');
                    this.$router.push({ name: 'login' });
                }
            }
        },
        async fetchTypesCategories() {
            try {
                this.loading = true;
                const token = localStorage.getItem('token');
                const response = await axios.get('/api/categories', {
                    headers: {
                        Authorization: `Bearer ${token}`,
                    },
                });
                this.typesCategories = response.data;
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        'Erreur lors du chargement des types de catégories.',
                    life: 3000,
                });
            } finally {
                this.loading = false;
            }
        },
        async fetchTrashedCategories() {
            this.trashedLoading = true;
            try {
                const token = localStorage.getItem('token');
                const response = await axios.get('/api/categories/trashed', {
                    headers: {
                        Authorization: `Bearer ${token}`,
                    },
                });
                this.trashedCategories = Array.isArray(response.data)
                    ? response.data
                    : [];
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Liste des catégories supprimées chargée.',
                    life: 3000,
                });
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        'Impossible de charger les catégories supprimées.',
                    life: 3000,
                });
            } finally {
                this.trashedLoading = false;
            }
        },
        async restoreCategory(category) {
            try {
                const token = localStorage.getItem('token');
                await axios.post(
                    `/api/categories/restore/${category.id}`,
                    {},
                    {
                        headers: {
                            Authorization: `Bearer ${token}`,
                        },
                    }
                );
                this.trashedCategories = this.trashedCategories.filter(
                    (c) => c.id !== category.id
                );
                await this.fetchTypesCategories();
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Catégorie restaurée.',
                    life: 3000,
                });
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        'Impossible de restaurer la catégorie.',
                    life: 3000,
                });
            }
        },
        openTrashedDialog() {
            this.trashedVisible = true;
            this.fetchTrashedCategories();
        },
        openForm() {
            this.typeToEdit = null;
            this.typeFormVisible = true;
        },
        closeTypeForm() {
            this.typeFormVisible = false;
            this.typeToEdit = null;
        },
        openOptionForm(type) {
            this.selectedTypeId = type.id;
            this.optionToEdit = null;
            this.optionFormVisible = true;
        },
        closeOptionForm() {
            this.optionFormVisible = false;
            this.optionToEdit = null;
            this.selectedTypeId = null;
        },
        async handleSaveType(newType) {
            try {
                const token = localStorage.getItem('token');
                const response = await axios.post('/api/categories', newType, {
                    headers: {
                        Authorization: `Bearer ${token}`,
                    },
                });
                this.typesCategories.unshift(response.data);
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Type de catégorie créé avec succès',
                    life: 3000,
                });
                this.typeFormVisible = false;
            } catch (error) {
                const errorMessage = error.response?.data?.errors
                    ? Object.values(error.response.data.errors).join(', ')
                    : error.response?.data?.message ||
                      'Erreur lors de la création.';
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: errorMessage,
                    life: 3000,
                });
            }
        },
        async handleUpdateType(updatedType) {
            try {
                const token = localStorage.getItem('token');
                const response = await axios.put(
                    `/api/categories/${updatedType.id}`,
                    updatedType,
                    {
                        headers: {
                            Authorization: `Bearer ${token}`,
                        },
                    }
                );
                const index = this.typesCategories.findIndex(
                    (t) => t.id === updatedType.id
                );
                if (index !== -1) {
                    this.typesCategories.splice(index, 1, response.data);
                }
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Type de catégorie mis à jour.',
                    life: 3000,
                });
                this.typeFormVisible = false;
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        'Erreur lors de la mise à jour.',
                    life: 3000,
                });
            }
        },
        async handleSaveOption(newOption) {
            try {
                const token = localStorage.getItem('token');
                const response = await axios.post('/api/listes', newOption, {
                    headers: {
                        Authorization: `Bearer ${token}`,
                    },
                });
                const index = this.typesCategories.findIndex(
                    (t) => t.id === newOption.categorie_id
                );
                if (index !== -1) {
                    if (!this.typesCategories[index].listes) {
                        this.typesCategories[index].listes = [];
                    }
                    this.typesCategories[index].listes.push(response.data);
                }
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Liste créée avec succès',
                    life: 3000,
                });
                this.optionFormVisible = false;
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        'Erreur lors de la création de la liste.',
                    life: 3000,
                });
            }
        },
        async handleUpdateOption(updatedOption) {
            try {
                const token = localStorage.getItem('token');
                const response = await axios.put(
                    `/api/listes/${updatedOption.id}`,
                    updatedOption,
                    {
                        headers: {
                            Authorization: `Bearer ${token}`,
                        },
                    }
                );
                const typeIndex = this.typesCategories.findIndex(
                    (t) => t.id === updatedOption.categorie_id
                );
                if (
                    typeIndex !== -1 &&
                    this.typesCategories[typeIndex].listes
                ) {
                    const optionIndex = this.typesCategories[
                        typeIndex
                    ].listes.findIndex((o) => o.id === updatedOption.id);
                    if (optionIndex !== -1) {
                        this.typesCategories[typeIndex].listes.splice(
                            optionIndex,
                            1,
                            response.data
                        );
                    }
                }
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Liste mise à jour.',
                    life: 3000,
                });
                this.optionFormVisible = false;
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        'Erreur lors de la mise à jour de la liste.',
                    life: 3000,
                });
            }
        },
        editType(type) {
            this.typeToEdit = { ...type };
            this.typeFormVisible = true;
        },
        editSelectedType() {
            if (this.selectedTypes?.length === 1) {
                this.editType(this.selectedTypes[0]);
            } else {
                this.toast.add({
                    severity: 'warn',
                    summary: 'Attention',
                    detail: 'Sélectionnez un seul type.',
                    life: 3000,
                });
            }
        },
        confirmDeleteType(type) {
            this.typeToDelete = type;
            this.deletePassword = '';
            this.passwordError = '';
            this.deleteFormVisible = true;
        },
        cancelDeleteType() {
            this.deleteFormVisible = false;
            this.typeToDelete = null;
            this.deletePassword = '';
            this.passwordError = '';
        },
        async deleteType() {
            if (this.typeToDelete) {
                try {
                    this.loading = true;
                    const token = localStorage.getItem('token');
                    const response = await axios.delete(
                        `/api/categories/${this.typeToDelete.id}`,
                        {
                            data: { password: this.deletePassword },
                            headers: {
                                Authorization: `Bearer ${token}`,
                            },
                        }
                    );
                    this.typesCategories = this.typesCategories.filter(
                        (t) => t.id !== this.typeToDelete.id
                    );
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail:
                            response.data.message ||
                            'Type de catégorie supprimé.',
                        life: 3000,
                    });
                    this.deleteFormVisible = false;
                    this.typeToDelete = null;
                    this.deletePassword = '';
                    this.passwordError = '';
                } catch (error) {
                    this.passwordError =
                        error.response?.data?.error ||
                        'Erreur lors de la suppression.';
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: this.passwordError,
                        life: 3000,
                    });
                } finally {
                    this.loading = false;
                }
            }
        },
        async deleteSelectedTypes() {
            if (this.selectedTypes?.length > 0) {
                this.confirmDeleteType(this.selectedTypes[0]);
            } else {
                this.toast.add({
                    severity: 'warn',
                    summary: 'Attention',
                    detail: 'Sélectionnez au moins un type.',
                    life: 3000,
                });
            }
        },
        async exportXLS() {
            try {
                const token = localStorage.getItem('token');
                const workbook = new ExcelJS.Workbook();
                const worksheet = workbook.addWorksheet('TypesCategories');
                worksheet.columns = [
                    { header: 'Nom (FR)', key: 'nom_fr', width: 20 },
                    { header: 'Nom (AR)', key: 'nom_ar', width: 20 },
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
                    { header: 'Prédefini', key: 'est_predefini', width: 10 },
                    { header: 'Ordre', key: 'ordre', width: 10 },
                    {
                        header: 'Liste Nom (FR)',
                        key: 'liste_nom_fr',
                        width: 20,
                    },
                    {
                        header: 'Liste Nom (AR)',
                        key: 'liste_nom_ar',
                        width: 20,
                    },
                    { header: 'Icône', key: 'icone', width: 15 },
                    { header: 'Couleur', key: 'couleur', width: 10 },
                    { header: 'Fond', key: 'fond', width: 10 },
                    { header: 'Statut', key: 'statut', width: 10 },
                ];

                const response = await axios.get('/api/categories', {
                    headers: {
                        Authorization: `Bearer ${token}`,
                    },
                });
                const categories = response.data;

                for (const category of categories) {
                    const optionsResponse = await axios.get(
                        `/api/options/${category.id}/0`,
                        {
                            headers: {
                                Authorization: `Bearer ${token}`,
                            },
                        }
                    );
                    const listes = optionsResponse.data;

                    if (listes.length === 0) {
                        worksheet.addRow({
                            nom_fr: category.nom_fr,
                            nom_ar: category.nom_ar,
                            description_fr: category.description_fr,
                            description_ar: category.description_ar,
                            est_predefini: category.est_predefini
                                ? 'Oui'
                                : 'Non',
                            ordre: category.ordre,
                        });
                    } else {
                        listes.forEach((liste) => {
                            worksheet.addRow({
                                nom_fr: category.nom_fr,
                                nom_ar: category.nom_ar,
                                description_fr: category.description_fr,
                                description_ar: category.description_ar,
                                est_predefini: category.est_predefini
                                    ? 'Oui'
                                    : 'Non',
                                ordre: category.ordre,
                                liste_nom_fr: liste.nom_fr,
                                liste_nom_ar: liste.nom_ar,
                                icone: liste.icone,
                                couleur: liste.couleur,
                                fond: liste.fond,
                                statut: liste.statut,
                            });
                        });
                    }
                }

                const buffer = await workbook.xlsx.writeBuffer();
                const blob = new Blob([buffer], {
                    type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'categories.xlsx';
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
                    detail:
                        error.response?.data?.message ||
                        "Erreur lors de l'export XLS.",
                    life: 3000,
                });
            }
        },
    },
};
</script>

<style scoped>
.confirmation-content {
    display: flex;
    align-items: center;
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

.search-field {
    width: 20rem;
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

:deep(.p-inputtext) {
    border-radius: 4px;
    border: 1px solid #ced4da;
    padding: 0.5rem;
}

:deep(.p-inputtext:focus) {
    border-color: #6366f1;
    box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
}

.trash-icon i {
    color: #dc3545; /* Red for trash icon */
}
</style>
