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
                        label="Listes & Options"
                        icon="pi pi-list"
                        class="p-button-text p-button-plain"
                        disabled
                    />
                </div>
                <div class="flex gap-2">
                    <Button
                        icon="pi pi-refresh"
                        class="p-button-text p-button-plain"
                        @click="fetchListes"
                        v-tooltip="'Rafraîchir'"
                    />
                    <Button
                        icon="pi pi-file-export"
                        class="p-button-text p-button-plain"
                        @click="exportXLS"
                        v-tooltip="'Exporter XLS'"
                    />
                    <Button
                        icon="pi pi-minus"
                        class="p-button-text p-button-plain"
                        @click="collapseAll"
                        v-tooltip="'Tout Réduire'"
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
                        <i class="pi pi-search" />
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
                        label="Ajouter Liste"
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
                v-model:expandedRows="expandedRows"
                v-model:filters="filters"
                v-model:selection="selectedListes"
                :value="listes"
                dataKey="id"
                size="small"
                stripedRows
                paginator
                :rows="20"
                :rowsPerPageOptions="[10, 20, 50]"
                filterDisplay="menu"
                :globalFilterFields="['nom_fr', 'nom_ar', 'description']"
                :loading="loading"
                scrollable
                scrollHeight="700px"
                removableSort
                class="p-datatable-sm border-1 surface-border"
            >
                <template #empty>
                    <div class="text-center py-4">
                        <i class="pi pi-inbox text-4xl text-400 mb-2" />
                        <p class="text-600">Aucune liste trouvée</p>
                    </div>
                </template>

                <Column expander style="width: 3rem" />
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
                        <span class="arabic-text">{{ data.nom_ar }}</span>
                    </template>
                </Column>

                <Column
                    field="description"
                    header="Description"
                    sortable
                    style="min-width: 25rem"
                >
                    <template #body="{ data }">
                        <span>{{ data.description || 'Non définie' }}</span>
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
                            :severity="
                                data.statut === 'Actif' ? 'success' : 'warning'
                            "
                        />
                    </template>
                </Column>

                <Column header="Actions" style="min-width: 12rem" frozen>
                    <template #body="{ data }">
                        <div class="flex gap-1">
                            <Button
                                icon="pi pi-pencil"
                                class="p-button-rounded p-button-text p-button-sm"
                                @click="editListe(data)"
                                v-tooltip="'Modifier'"
                            />
                            <Button
                                icon="pi pi-trash"
                                class="p-button-rounded p-button-text p-button-danger p-button-sm"
                                @click="confirmDeleteListe(data)"
                                v-tooltip="'Supprimer'"
                            />
                            <Button
                                icon="pi pi-plus"
                                class="p-button-rounded p-button-text p-button-success p-button-sm"
                                @click="openOptionForm(data)"
                                v-tooltip="'Ajouter une option'"
                            />
                        </div>
                    </template>
                </Column>

                <!-- Expanded Row Template -->
                <template #expansion="{ data }">
                    <div class="p-3 surface-50 border-round">
                        <div
                            class="flex justify-content-between align-items-center mb-3"
                        >
                            <div class="flex align-items-center gap-2">
                                <i class="pi pi-list text-primary-500" />
                                <span class="font-semibold"
                                    >Options pour la liste
                                    {{ data.nom_fr }}</span
                                >
                            </div>
                            <Button
                                icon="pi pi-plus"
                                label="Ajouter Option"
                                class="p-button-sm p-button-success"
                                @click="openOptionForm(data)"
                            />
                        </div>

                        <DataTable
                            :value="data.options"
                            size="small"
                            :loading="loadingOptions"
                            dataKey="valeur"
                            class="p-datatable-sm"
                        >
                            <Column
                                field="nom_fr"
                                header="Nom (Français)"
                                style="min-width: 15rem"
                            >
                                <template #body="{ data }">
                                    <div class="flex align-items-center gap-2">
                                        <i
                                            class="pi pi-tag"
                                            :class="`text-${getRandomColor()}-500`"
                                        />
                                        {{ data.nom_fr || '-' }}
                                    </div>
                                </template>
                            </Column>

                            <Column
                                field="nom_ar"
                                header="Nom (Arabe)"
                                style="min-width: 15rem"
                            >
                                <template #body="{ data }">
                                    <div class="flex align-items-center gap-2">
                                        <i
                                            class="pi pi-tag"
                                            :class="`text-${getRandomColor()}-500`"
                                        />
                                        <span class="arabic-text">{{
                                            data.nom_ar || '-'
                                        }}</span>
                                    </div>
                                </template>
                            </Column>

                            <Column
                                field="valeur"
                                header="Valeur"
                                style="min-width: 15rem"
                            >
                                <template #body="{ data }">
                                    {{ data.valeur || '-' }}
                                </template>
                            </Column>

                            <Column header="Actions" style="min-width: 10rem">
                                <template #body="{ data }">
                                    <div class="flex gap-1">
                                        <Button
                                            icon="pi pi-pencil"
                                            class="p-button-rounded p-button-text p-button-sm"
                                            @click="editOption(data)"
                                            v-tooltip="'Modifier'"
                                        />
                                        <Button
                                            icon="pi pi-trash"
                                            class="p-button-rounded p-button-text p-button-danger p-button-sm"
                                            @click="confirmDeleteOption(data)"
                                            v-tooltip="'Supprimer'"
                                        />
                                    </div>
                                </template>
                            </Column>
                        </DataTable>
                    </div>
                </template>
            </DataTable>
        </div>

        <!-- Formulaire pour Liste -->
        <ListeForm
            :visible="showListeForm"
            :listeToEdit="listeToEdit"
            @update:visible="showListeForm = $event"
            @save="handleSaveListe"
            @update="handleUpdateListe"
            @close="closeForm"
        />

        <!-- Formulaire pour Option -->
        <OptionForm
            :visible="showOptionForm"
            :optionToEdit="optionToEdit"
            :selectedListe="selectedListe"
            @update:visible="showOptionForm = $event"
            @save="handleSaveOption"
            @update="handleUpdateOption"
            @close="closeForm"
        />

        <!-- Confirmation de suppression pour liste -->
        <Dialog
            v-model:visible="deleteListeVisible"
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
                        Voulez-vous vraiment supprimer la liste
                        <b>{{ listeToDelete?.nom_fr }}</b> ?
                    </span>
                </div>
                <div class="field">
                    <label for="deleteListePassword" class="font-semibold"
                        >Mot de passe de confirmation</label
                    >
                    <InputText
                        id="deleteListePassword"
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
                    @click="cancelDeleteListe"
                />
                <Button
                    label="Supprimer"
                    icon="pi pi-check"
                    class="p-button-danger"
                    :disabled="loading || !deletePassword"
                    @click="deleteListe"
                />
            </template>
        </Dialog>

        <!-- Confirmation de suppression pour option -->
        <Dialog
            v-model:visible="deleteOptionVisible"
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
                        Voulez-vous vraiment supprimer l'option
                        <b>{{ optionToDelete?.nom_fr }}</b> ?
                    </span>
                </div>
                <div class="field">
                    <label for="deleteOptionPassword" class="font-semibold"
                        >Mot de passe de confirmation</label
                    >
                    <InputText
                        id="deleteOptionPassword"
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
                    @click="cancelDeleteOption"
                />
                <Button
                    label="Supprimer"
                    icon="pi pi-check"
                    class="p-button-danger"
                    :disabled="loading || !deletePassword"
                    @click="deleteOption"
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
import Dialog from 'primevue/dialog';
import SplitButton from 'primevue/splitbutton';
import Toast from 'primevue/toast';
import ListeForm from '@/Components/Listes/ListeForm.vue';
import OptionForm from '@/Components/Listes/OptionForm.vue';
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
        ListeForm,
        OptionForm,
    },
    data() {
        return {
            listes: [],
            selectedListes: null,
            filters: null,
            actions: [
                {
                    label: 'Modifier',
                    icon: 'pi pi-pencil',
                    command: () => this.editSelectedListe(),
                },
                {
                    label: 'Supprimer',
                    icon: 'pi pi-trash',
                    command: () => this.deleteSelectedListes(),
                },
            ],
            loading: false,
            loadingOptions: false,
            showListeForm: false,
            showOptionForm: false,
            listeToEdit: null,
            optionToEdit: null,
            selectedListe: null,
            expandedRows: [],
            deleteListeVisible: false,
            deleteOptionVisible: false,
            deletePassword: '',
            passwordError: '',
            listeToDelete: null,
            optionToDelete: null,
        };
    },
    setup() {
        const toast = useToast();
        return { toast };
    },
    created() {
        this.initFilters();
        this.fetchListes();
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
                description: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.CONTAINS },
                    ],
                },
            };
        },
        clearFilter() {
            this.initFilters();
        },
        getRandomColor() {
            const colors = [
                'blue',
                'green',
                'yellow',
                'cyan',
                'pink',
                'indigo',
                'teal',
            ];
            return colors[Math.floor(Math.random() * colors.length)];
        },
        async fetchListes() {
            try {
                this.loading = true;
                const response = await axios.get('/api/listes');
                if (!Array.isArray(response.data)) {
                    throw new Error('Response data is not an array');
                }
                this.listes = response.data.map((liste) => ({
                    ...liste,
                    options: liste.options || [],
                }));
            } catch (error) {
                console.error('Error fetching listes:', error);
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.error ||
                        'Erreur lors du chargement des listes.',
                    life: 3000,
                });
                this.listes = [];
            } finally {
                this.loading = false;
            }
        },
        openForm() {
            this.listeToEdit = null;
            this.optionToEdit = null;
            this.selectedListe = null;
            this.showListeForm = true;
        },
        openOptionForm(liste) {
            this.selectedListe = liste;
            this.listeToEdit = null;
            this.optionToEdit = null;
            this.showOptionForm = true;
        },
        closeForm() {
            this.showListeForm = false;
            this.showOptionForm = false;
            this.listeToEdit = null;
            this.optionToEdit = null;
            this.selectedListe = null;
        },
        collapseAll() {
            this.expandedRows = [];
        },
        async handleSaveListe(newListe) {
            try {
                const response = await axios.post('/api/listes/create', {
                    ...newListe,
                    options: newListe.options || [],
                });
                this.listes.unshift({
                    ...response.data,
                    options: response.data.options || [],
                });
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Liste créée avec succès',
                    life: 3000,
                });
                this.closeForm();
            } catch (error) {
                console.error('Error saving liste:', error);
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.error ||
                        'Erreur lors de la création.',
                    life: 3000,
                });
            }
        },
        async handleUpdateListe(updatedListe) {
            try {
                const response = await axios.put(
                    `/api/listes/${updatedListe.id}`,
                    {
                        ...updatedListe,
                        options: updatedListe.options || [],
                    }
                );
                const index = this.listes.findIndex(
                    (l) => l.id === updatedListe.id
                );
                if (index !== -1) {
                    this.listes.splice(index, 1, {
                        ...response.data,
                        options: response.data.options || [],
                    });
                }
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Liste mise à jour.',
                    life: 3000,
                });
                this.closeForm();
            } catch (error) {
                console.error('Error updating liste:', error);
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.error ||
                        'Erreur lors de la mise à jour.',
                    life: 3000,
                });
            }
        },
        async handleSaveOption(newOption) {
            try {
                const liste = this.listes.find(
                    (l) => l.id === this.selectedListe.id
                );
                const options = liste.options
                    ? [...liste.options, newOption]
                    : [newOption];
                const response = await axios.put(
                    `/api/listes/${this.selectedListe.id}`,
                    {
                        ...this.selectedListe,
                        options,
                    }
                );
                const index = this.listes.findIndex(
                    (l) => l.id === this.selectedListe.id
                );
                if (index !== -1) {
                    this.listes.splice(index, 1, {
                        ...response.data,
                        options: response.data.options || [],
                    });
                }
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Option créée avec succès',
                    life: 3000,
                });
                this.closeForm();
            } catch (error) {
                console.error('Error saving option:', error);
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.error ||
                        "Erreur lors de la création de l'option.",
                    life: 3000,
                });
            }
        },
        async handleUpdateOption(updatedOption) {
            try {
                const liste = this.listes.find(
                    (l) => l.id === this.selectedListe.id
                );
                const options = liste.options.map((opt) =>
                    opt.valeur === updatedOption.valeur ? updatedOption : opt
                );
                const response = await axios.put(
                    `/api/listes/${this.selectedListe.id}`,
                    {
                        ...this.selectedListe,
                        options,
                    }
                );
                const index = this.listes.findIndex(
                    (l) => l.id === this.selectedListe.id
                );
                if (index !== -1) {
                    this.listes.splice(index, 1, {
                        ...response.data,
                        options: response.data.options || [],
                    });
                }
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Option mise à jour.',
                    life: 3000,
                });
                this.closeForm();
            } catch (error) {
                console.error('Error updating option:', error);
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.error ||
                        "Erreur lors de la mise à jour de l'option.",
                    life: 3000,
                });
            }
        },
        editListe(liste) {
            this.listeToEdit = {
                ...liste,
                options: liste.options ? [...liste.options] : [],
            };
            this.optionToEdit = null;
            this.selectedListe = null;
            this.showListeForm = true;
        },
        editSelectedListe() {
            if (this.selectedListes?.length === 1) {
                this.editListe(this.selectedListes[0]);
            } else {
                this.toast.add({
                    severity: 'warn',
                    summary: 'Attention',
                    detail: 'Sélectionnez une seule liste.',
                    life: 3000,
                });
            }
        },
        confirmDeleteListe(liste) {
            this.listeToDelete = liste;
            this.deletePassword = '';
            this.passwordError = '';
            this.deleteListeVisible = true;
        },
        cancelDeleteListe() {
            this.deleteListeVisible = false;
            this.listeToDelete = null;
            this.deletePassword = '';
            this.passwordError = '';
        },
        async deleteListe() {
            if (this.listeToDelete) {
                try {
                    this.loading = true;
                    const response = await axios.delete(
                        `/api/listes/${this.listeToDelete.id}`,
                        {
                            data: { password: this.deletePassword },
                        }
                    );
                    this.listes = this.listes.filter(
                        (l) => l.id !== this.listeToDelete.id
                    );
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: response.data.message || 'Liste supprimée.',
                        life: 3000,
                    });
                    this.deleteListeVisible = false;
                    this.listeToDelete = null;
                    this.deletePassword = '';
                    this.passwordError = '';
                } catch (error) {
                    console.error('Error deleting liste:', error);
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
        async deleteSelectedListes() {
            if (this.selectedListes?.length > 0) {
                this.confirmDeleteListe(this.selectedListes[0]);
            } else {
                this.toast.add({
                    severity: 'warn',
                    summary: 'Attention',
                    detail: 'Sélectionnez au moins une liste.',
                    life: 3000,
                });
            }
        },
        editOption(option) {
            this.optionToEdit = { ...option };
            this.selectedListe = this.listes.find((l) =>
                l.options.some((opt) => opt.valeur === option.valeur)
            );
            this.listeToEdit = null;
            this.showOptionForm = true;
        },
        confirmDeleteOption(option) {
            this.optionToDelete = option;
            this.deletePassword = '';
            this.passwordError = '';
            this.deleteOptionVisible = true;
        },
        cancelDeleteOption() {
            this.deleteOptionVisible = false;
            this.optionToDelete = null;
            this.deletePassword = '';
            this.passwordError = '';
        },
        async deleteOption() {
            if (this.optionToDelete) {
                try {
                    this.loading = true;
                    const liste = this.listes.find((l) =>
                        l.options.some(
                            (opt) => opt.valeur === this.optionToDelete.valeur
                        )
                    );
                    const options = liste.options.filter(
                        (opt) => opt.valeur !== this.optionToDelete.valeur
                    );
                    const response = await axios.put(
                        `/api/listes/${liste.id}`,
                        {
                            ...liste,
                            options,
                        }
                    );
                    const index = this.listes.findIndex(
                        (l) => l.id === liste.id
                    );
                    if (index !== -1) {
                        this.listes.splice(index, 1, {
                            ...response.data,
                            options: response.data.options || [],
                        });
                    }
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: response.data.message || 'Option supprimée.',
                        life: 3000,
                    });
                    this.deleteOptionVisible = false;
                    this.optionToDelete = null;
                    this.deletePassword = '';
                    this.passwordError = '';
                } catch (error) {
                    console.error('Error deleting option:', error);
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
        async exportXLS() {
            try {
                const workbook = new ExcelJS.Workbook();
                const worksheet = workbook.addWorksheet('Listes');
                worksheet.columns = [
                    { header: 'Nom (FR)', key: 'nom_fr', width: 20 },
                    { header: 'Nom (AR)', key: 'nom_ar', width: 20 },
                    { header: 'Description', key: 'description', width: 30 },
                    { header: 'Statut', key: 'statut', width: 10 },
                    { header: 'Couleur', key: 'couleur', width: 10 },
                    { header: 'Fond', key: 'fond', width: 10 },
                    { header: 'Ordre', key: 'ordre', width: 10 },
                    {
                        header: 'Option Nom (FR)',
                        key: 'option_nom_fr',
                        width: 20,
                    },
                    {
                        header: 'Option Nom (AR)',
                        key: 'option_nom_ar',
                        width: 20,
                    },
                    {
                        header: 'Option Valeur',
                        key: 'option_valeur',
                        width: 20,
                    },
                ];

                const response = await axios.get('/api/listes');
                if (!Array.isArray(response.data)) {
                    throw new Error('Response data is not an array');
                }
                const listes = response.data;

                for (const liste of listes) {
                    const options = liste.options || [];
                    if (options.length === 0) {
                        worksheet.addRow({
                            nom_fr: liste.nom_fr,
                            nom_ar: liste.nom_ar,
                            description: liste.description,
                            statut: liste.statut,
                            couleur: liste.couleur,
                            fond: liste.fond,
                            ordre: liste.ordre,
                        });
                    } else {
                        options.forEach((option) => {
                            worksheet.addRow({
                                nom_fr: liste.nom_fr,
                                nom_ar: liste.nom_ar,
                                description: liste.description,
                                statut: liste.statut,
                                couleur: liste.couleur,
                                fond: liste.fond,
                                ordre: liste.ordre,
                                option_nom_fr: option.nom_fr,
                                option_nom_ar: option.nom_ar,
                                option_valeur: option.valeur,
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
                link.download = 'listes.xlsx';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(link.href);

                this.toast.add({
                    severity: 'success',
                    summary: 'Export XLS',
                    detail: 'Exportation réussie.',
                    life: 3000,
                });
            } catch (error) {
                console.error('Error exporting XLS:', error);
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.error ||
                        "Erreur lors de l'export XLS.",
                    life: 3000,
                });
            }
        },
    },
};
</script>

<style scoped>
@font-face {
    font-family: 'Montserrat-Arabic';
    src: url('/fonts/Montserrat-Arabic-Regular.otf') format('opentype');
    font-weight: normal;
    font-style: normal;
    font-display: swap;
}

.arabic-text {
    font-family: 'Montserrat-Arabic', sans-serif;
    direction: rtl;
    text-align: right;
}

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
</style>
