<template>
    <div>
        <!-- Start Table -->
        <div class="card">
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
                :rows="100"
                filterDisplay="menu"
                :globalFilterFields="['code', 'nom_fr', 'nom_ar', 'type']"
                :loading="loading"
                scrollable
                scrollHeight="650px"
                :pt="{
                    table: { style: 'min-width: 50rem' },
                    bodyrow: ({ props }) => ({
                        class: [
                            {
                                'font-bold': props.frozenRow,
                                'text-blue-400': props.frozenRow,
                            },
                        ],
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
                            <!-- Bouton pour ouvrir la Form d'ajout -->
                            <Button
                                icon="pi pi-plus"
                                severity="success"
                                size="small"
                                class="mr-2"
                                @click="openForm"
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
                        </div>
                        <!-- Section droite -->
                        <div class="flex align-items-center">
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
                                @click="showImportError = true"
                            />

                            <Button
                                icon="pi pi-file-export"
                                severity="info"
                                size="small"
                                class="mr-2"
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

                <!-- Empty Template -->
                <template #empty>
                    <div v-if="!loading">Aucun secteur trouvé.</div>
                    <div v-else>Chargement en cours...</div>
                </template>

                <!-- Columns -->
                <Column
                    selectionMode="multiple"
                    headerStyle="width: 3rem"
                ></Column>
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
                        <div class="arabic-normal">
                            {{ data.nom_ar }}
                        </div>
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
                    header="Type"
                    field="type"
                    sortable
                    :filterMenuStyle="{ width: '14rem' }"
                    style="min-width: 12rem"
                >
                    <template #body="{ data }">
                        <div class="centered-content arabic-normal">
                            <!-- Ajout de la classe arabic-text -->
                            <!-- Utilisez la prop severity si disponible -->
                            <Tag
                                :value="data.type"
                                :severity="getSeverity(data.type)"
                                class="arabic-normal"
                            />
                        </div>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-model="filterModel.value"
                            :options="types"
                            placeholder="Sélectionner un type"
                            class="p-column-filter arabic-normal"
                            @change="filterCallback"
                        >
                            <template #option="{ option }">
                                <div class="centered-content arabic-normal">
                                    <Tag
                                        :value="option"
                                        :severity="getSeverity(option)"
                                        class="arabic-normal"
                                    />
                                </div>
                            </template>
                        </Dropdown>
                    </template>
                </Column>

                <!-- Colonne Actions -->
                <Column header="Actions" style="min-width: 10rem">
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
                            @click="edit(data)"
                        />
                        <Button
                            icon="pi pi-trash"
                            class="p-button-rounded p-button-text p-button-danger"
                            @click="confirmDelete(data)"
                        />
                    </template>
                </Column>
            </DataTable>
        </div>
        <!-- End Table -->

        <!-- Form Secteur Form -->
        <Form
            :visible="formVisible"
            :initialData="ToEdit"
            @update:visible="formVisible = $event"
            @save="handleSave"
            @update="handleUpdate"
            @close="closeForm"
        />

        <!-- Form de confirmation de suppression -->
        <Dialog
            v-model:visible="deleteFormVisible"
            :style="{ width: '450px' }"
            header="Confirmation"
            :modal="true"
        >
            <div class="confirmation-content">
                <i
                    class="pi pi-exclamation-triangle mr-3"
                    style="font-size: 2rem"
                />
                <span v-if="ToDelete">
                    Êtes-vous sûr de vouloir supprimer le secteur
                    <strong>{{}}</strong> ?
                </span>
                <span v-else>
                    Êtes-vous sûr de vouloir supprimer les {{}} secteurs
                    sélectionnés ?
                </span>

                <!-- Champ de saisie du mot de passe -->
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
                    severity="success"
                    outlined
                    icon="pi pi-times"
                    @click="deleteFormVisible = false"
                    class="p-2.3 border-1 border-surface-200"
                ></Button>
                <Button
                    label="Oui"
                    severity="danger"
                    outlined
                    icon="pi pi-check"
                    @click="confirmDeleteAction"
                    class="p-2.3 border-1 border-surface-100"
                ></Button>
            </template>
        </Dialog>

        <!-- Popup pour les erreurs d'importation -->
        <ImportError
            :errors="errors"
            :visible="showImportError"
            @update:visible="showImportError = $event"
            @line-imported="handleLineImported"
            @close="refreshTable"
        />

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
import Tag from 'primevue/tag';
import Dropdown from 'primevue/dropdown';
import SplitButton from 'primevue/splitbutton';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import Form from '@/corbeil/Secteurs/SecteursForm.vue';
import ImportError from '@/corbeil/Specialites/SecteursImportError.vue';
import ExcelJS from 'exceljs'; // Importation d'ExcelJS
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
            types: ['مقيس', 'غير مقيس'],
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
            password: '', // Champ pour stocker le mot de passe saisi
            passwordError: '', // Message d'erreur pour le mot de passe
        };
    },

    created() {
        this.initFilters();
        this.fetch();
    },

    methods: {
        openForm() {
            this.ToEdit = null; // Réinitialiser ToEdit
            this.formVisible = true;
        },
        closeForm() {
            this.formVisible = false;
            this.ToEdit = null; // Réinitialiser ToEdit
        },
        edit(element) {
            this.ToEdit = { ...element }; // Copier les données de la ligne
            this.formVisible = true;
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
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
                nom_ar: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
                type: {
                    operator: FilterOperator.OR,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.EQUALS },
                    ],
                },
            };
        },

        handleLineImported(importedLine) {
            this.errors = this.errors.filter(
                (err) => err.line !== importedLine.line
            );
        },

        async refreshTable() {
            this.loading = true;
            try {
                const response = await axios.get('/api/secteurs');
                this.table = response.data;
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
                case 'غير مقيس':
                    return 'danger'; // Rouge pour les types urgents
                case 'مقيس':
                    return 'success'; // Vert pour les types standard
                default:
                    return null; // Aucune classe severity pour les autres types
            }
        },

        async fetch() {
            this.loading = true;
            try {
                const response = await axios.get('/api/secteurs');
                this.table = response.data;
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

        editSelected() {
            if (this.selected && this.selected.length === 1) {
                const selected = this.selected[0];

                // Vérifier si la ligne sélectionnée est dans FixLignes ou secteurs
                const ToEdit =
                    this.FixLignes.find((s) => s.id === selected.id) ||
                    this.table.find((s) => s.id === selected.id);

                if (ToEdit) {
                    this.edit(ToEdit);
                }
            } else {
                this.toast.add({
                    severity: 'warn',
                    summary: 'Aucune sélection',
                    detail: 'Veuillez sélectionner un seul secteur à modifier.',
                    life: 3000,
                });
            }
        },

        async handleSave(newelement) {
            try {
                const response = await axios.post('/api/secteurs', newelement);
                this.table.unshift(response.data);
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Le secteur a été ajouté avec succès.',
                    life: 3000,
                });
            } catch (error) {
                console.error('Erreur lors de la création du secteur :', error);
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
                        detail: "Échec de l'ajout du secteur.",
                        life: 3000,
                    });
                }
            } finally {
                this.closeForm();
            }
        },

        async handleUpdate(updated) {
            try {
                const response = await axios.put(
                    `/api/secteurs/${updated.id}`,
                    updated
                );

                // Mettre à jour la ligne dans les secteurs normaux
                const indexTable = this.table.findIndex(
                    (s) => s.id === updated.id
                );
                if (indexTable !== -1) {
                    this.table.splice(indexTable, 1, response.data);
                }

                // Mettre à jour la ligne dans les lignes gelées
                const indexFixLignes = this.FixLignes.findIndex(
                    (s) => s.id === updated.id
                );
                if (indexFixLignes !== -1) {
                    this.FixLignes.splice(indexFixLignes, 1, response.data);
                }

                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Le secteur a été modifié avec succès.',
                    life: 3000,
                });
            } catch (error) {
                console.error(
                    'Erreur lors de la modification du secteur :',
                    error
                );
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Échec de la modification du secteur.',
                    life: 3000,
                });
            } finally {
                this.closeForm();
            }
        },
        confirmDeleteAction() {
            // Vérifier le mot de passe avant de procéder à la suppression
            if (this.password !== 'ha') {
                this.passwordError = 'Mot de passe incorrect.';
                return;
            }

            this.passwordError = ''; // Réinitialiser l'erreur si le mot de passe est correct

            if (this.ToDelete) {
                this.delete(); // Supprimer un seul secteur
            } else if (this.selected && this.selected.length > 0) {
                this.deleteSelected(); // Supprimer les secteurs sélectionnés
            }
            this.deleteFormVisible = false; // Fermer le formulaire de confirmation
            this.password = ''; // Réinitialiser le champ de mot de passe
        },

        async delete() {
            if (this.ToDelete) {
                try {
                    await axios.delete(`/api/secteurs/${this.ToDelete.id}`);

                    // Supprimer la ligne des secteurs normaux
                    this.table = this.table.filter(
                        (s) => s.id !== this.ToDelete.id
                    );

                    // Supprimer la ligne des lignes gelées
                    this.FixLignes = this.FixLignes.filter(
                        (s) => s.id !== this.ToDelete.id
                    );

                    this.toast.add({
                        severity: 'success',
                        summary: 'Supprimé',
                        detail: 'Le secteur a été supprimé avec succès.',
                        life: 3000,
                    });
                } catch (error) {
                    console.error(
                        'Erreur lors de la suppression du secteur :',
                        error
                    );
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'Échec de la suppression du secteur.',
                        life: 3000,
                    });
                } finally {
                    this.ToDelete = null;
                }
            }
        },

        async deleteSelected() {
            if (this.selected && this.selected.length > 0) {
                try {
                    await axios.post('/api/secteurs/delete-selected', {
                        ids: this.selected.map((s) => s.id),
                    });

                    // Supprimer les lignes sélectionnées des secteurs normaux
                    this.table = this.table.filter(
                        (s) => !this.selected.includes(s)
                    );

                    // Supprimer les lignes sélectionnées des lignes gelées
                    this.FixLignes = this.FixLignes.filter(
                        (s) => !this.selected.includes(s)
                    );

                    this.toast.add({
                        severity: 'success',
                        summary: 'Supprimé',
                        detail: 'Les secteurs sélectionnés ont été supprimés avec succès.',
                        life: 3000,
                    });
                    this.selected = null;
                } catch (error) {
                    console.error(
                        'Erreur lors de la suppression des secteurs :',
                        error
                    );
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'Échec de la suppression des secteurs sélectionnés.',
                        life: 3000,
                    });
                } finally {
                    this.TodeleteSelected = null;
                }
            }
        },

        confirmDelete(element) {
            this.ToDelete = element;
            this.deleteFormVisible = true;
        },

        confirmDeleteSelected() {
            if (this.selected && this.selected.length > 0) {
                this.ToDelete = null;
                this.deleteFormVisible = true;
            } else {
                this.toast.add({
                    severity: 'warn',
                    summary: 'Aucune sélection',
                    detail: 'Veuillez sélectionner un ou plusieurs secteurs à supprimer.',
                    life: 3000,
                });
            }
        },

        toggleLock(data, frozen, index) {
            if (frozen) {
                // Retirer la ligne gelée et la remettre dans les secteurs normaux
                this.FixLignes = this.FixLignes.filter((c, i) => i !== index);
                this.table.push(data);
            } else {
                // Retirer la ligne normale et la mettre dans les lignes gelées
                this.table = this.table.filter((c, i) => i !== index);
                this.FixLignes.push(data);
            }

            // Trier les secteurs pour maintenir l'ordre
            this.table.sort((val1, val2) => {
                return val1.id < val2.id ? -1 : 1;
            });
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
                        '/api/secteurs/importxls',
                        formData,
                        {
                            headers: {
                                'Content-Type': 'multipart/form-data',
                            },
                        }
                    );

                    this.errors = response.data.error_lines || [];
                    let message = 'Importation réussie.';
                    let severity = 'success';

                    if (this.errors.length > 0) {
                        message = `Importation terminée avec ${this.errors.length} erreurs. Veuillez vérifier les lignes concernées.`;
                        severity = 'warn';
                        this.showImportError = true;
                    }

                    // Rafraîchir la table après une importation réussie
                    await this.refreshTable();

                    this.toast.add({
                        severity: severity,
                        summary: 'Import XLS',
                        detail: message,
                        life: 10000,
                    });
                } catch (error) {
                    console.error("Erreur lors de l'import XLS :", error);
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: "Une erreur s'est produite lors de l'import XLS.",
                        life: 3000,
                    });
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: "Une erreur s'est produite lors de l'importation.",
                        life: 10000,
                    });
                } finally {
                    fileInput.remove();
                }
            };

            document.body.appendChild(fileInput);
            fileInput.click();
        },

        async exportXLS() {
            try {
                const data = this.table.map((secteur) => ({
                    Code: secteur.code,
                    'Nom (FR)': secteur.nom_fr,
                    'Nom (AR)': secteur.nom_ar,
                    Type: secteur.type,
                }));

                const workbook = new ExcelJS.Workbook();
                const worksheet = workbook.addWorksheet('Table');
                worksheet.columns = [
                    { header: 'Code', key: 'Code', width: 15 },
                    { header: 'Nom (FR)', key: 'Nom (FR)', width: 30 },
                    { header: 'Nom (AR)', key: 'Nom (AR)', width: 30 },
                    { header: 'Type', key: 'Type', width: 20 },
                ];

                data.forEach((row) => worksheet.addRow(row));

                const buffer = await workbook.xlsx.writeBuffer();
                const blob = new Blob([buffer], {
                    type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'secteurs.xlsx';
                link.click();
                URL.revokeObjectURL(link.href);

                this.toast.add({
                    severity: 'success',
                    summary: 'Export XLS',
                    detail: "L'exportation des secteurs a été réalisée avec succès.",
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
.arabic-normal {
    font-family: 'Noto Naskh Arabic', sans-serif; /* Police utilisée */
    font-size: 1em !important; /* Réduction de la taille de police */
    font-weight: normal !important; /* Suppression du gras */
}
.arabic-gras {
    font-family: 'Noto Naskh Arabic', sans-serif; /* Police utilisée */
    font-size: 1em !important; /* Réduction de la taille de police */
}
</style>
