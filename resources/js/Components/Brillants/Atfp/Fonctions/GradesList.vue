<template>
    <div class="surface-ground pt-2 px-1.5 pb-1.5" style="margin-top: 0">
        <!-- Header Section with Navbar -->
        <div class="surface-card p-2 border-round border-1 surface-border" style="position: relative; top: -37px; box-shadow: none; margin-bottom: -33px;">
            <div class="flex justify-content-between align-items-center">
                <div class="flex gap-3">
                    <Button label="Accueil" icon="pi pi-home" class="p-button-text p-button-plain" @click="$router.push({ name: 'dashboard' })" />
                    <Button label="Grades" icon="pi pi-star" class="p-button-text p-button-plain" disabled />
                </div>
                <div class="flex gap-2">
                    <Button icon="pi pi-refresh" class="p-button-rounded p-button-text" @click="fetchGrades" v-tooltip="'Rafraîchir'" />
                    <Button icon="pi pi-file-import" class="p-button-rounded p-button-text import-icon" @click="importXLS" v-tooltip="'Importer XLS'" />
                    <Button v-if="errors.import.length" icon="pi pi-exclamation-triangle" class="p-button-rounded p-button-text p-button-warning" @click="showImportError = true" v-tooltip="'Erreurs Import'" />
                    <Button icon="pi pi-file-export" class="p-button-rounded p-button-text export-icon" @click="exportXLS" v-tooltip="'Exporter XLS'" />
                </div>
            </div>
        </div>

        <!-- Main Content Section -->
        <div class="surface-card p-4 pt-2 border-round-lg shadow-2 border-1 surface-border">
            <!-- Table Header with Actions -->
            <div class="flex justify-content-between align-items-center mb-4">
                <div class="flex align-items-center gap-2">
                    <span class="p-input-icon-left search-field">
                        <i class="pi pi-search" />
                        <InputText v-model="filters.global.value" placeholder="Rechercher..." />
                    </span>
                    <Button icon="pi pi-filter-slash" class="p-button-outlined p-button-secondary" size="small" @click="clearFilter" v-tooltip="'Réinitialiser les filtres'" />
                </div>
                <div class="flex gap-2">
                    <Button icon="pi pi-plus" label="Ajouter Grade" class="p-button-success" @click="openAddForm" />
                    <SplitButton label="Actions" icon="pi pi-ellipsis-v" :model="actions" class="p-button-secondary" />
                </div>
            </div>

            <!-- Main DataTable -->
            <DataTable
                v-model:filters="filters"
                v-model:selection="selectedGrades"
                :value="grades"
                dataKey="id"
                size="small"
                stripedRows
                paginator
                :rows="10"
                :rowsPerPageOptions="[10, 25, 50]"
                filterDisplay="menu"
                :globalFilterFields="['filiere_fr', 'filiere_ar', 'grade_fr', 'grade_ar', 'poste_fr', 'poste_ar', 'created_at', 'updated_at']"
                :loading="loading"
                scrollable
                scrollHeight="600px"
                removableSort
                class="p-datatable-sm border-round-lg"
            >
                <template #empty>
                    <div class="text-center py-4">
                        <i class="pi pi-inbox text-4xl text-400 mb-2" />
                        <p class="text-600">Aucun grade trouvé</p>
                    </div>
                </template>
                <Column selectionMode="multiple" headerStyle="width: 3rem" />
                <Column field="filiere_fr" header="Filière (FR)" sortable style="min-width: 15rem">
                    <template #body="{ data }">
                        <span class="font-medium">{{ data.filiere_fr }}</span>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" type="text" class="p-column-filter" placeholder="Rechercher par filière" @input="filterCallback" />
                    </template>
                </Column>
                <Column field="filiere_ar" header="Filière (AR)" sortable style="min-width: 15rem">
                    <template #body="{ data }">
                        <div class="flex align-items-center gap-2">
                            <i class="pi pi-tag text-primary-500" />
                            <span class="arabic-text">{{ data.filiere_ar || 'غير محدد' }}</span>
                        </div>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" type="text" class="p-column-filter" placeholder="Rechercher par filière arabe" @input="filterCallback" />
                    </template>
                </Column>
                <Column field="grade_fr" header="Grade (FR)" sortable style="min-width: 20rem">
                    <template #body="{ data }">
                        <span>{{ data.grade_fr }}</span>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" type="text" class="p-column-filter" placeholder="Rechercher par grade" @input="filterCallback" />
                    </template>
                </Column>
                <Column field="grade_ar" header="Grade (AR)" sortable style="min-width: 20rem">
                    <template #body="{ data }">
                        <span class="arabic-text">{{ data.grade_ar || 'غير محدد' }}</span>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" type="text" class="p-column-filter" placeholder="Rechercher par grade arabe" @input="filterCallback" />
                    </template>
                </Column>
                <Column field="poste_fr" header="Poste (FR)" sortable style="min-width: 20rem">
                    <template #body="{ data }">
                        <span>{{ data.poste_fr }}</span>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" type="text" class="p-column-filter" placeholder="Rechercher par poste" @input="filterCallback" />
                    </template>
                </Column>
                <Column field="poste_ar" header="Poste (AR)" sortable style="min-width: 20rem">
                    <template #body="{ data }">
                        <span class="arabic-text">{{ data.poste_ar || 'غير محدد' }}</span>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" type="text" class="p-column-filter" placeholder="Rechercher par poste arabe" @input="filterCallback" />
                    </template>
                </Column>
                <Column field="created_at" header="Date d'ajout" sortable style="min-width: 15rem">
                    <template #body="{ data }">
                        <div class="flex align-items-center gap-2">
                            <i class="pi pi-calendar text-blue-500" />
                            <span>{{ formatDate(data.created_at) }}</span>
                        </div>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" type="text" class="p-column-filter" placeholder="Rechercher par date" @input="filterCallback" />
                    </template>
                </Column>
                <Column field="updated_at" header="Date de modification" sortable style="min-width: 15rem">
                    <template #body="{ data }">
                        <div class="flex align-items-center gap-2">
                            <i class="pi pi-calendar text-blue-500" />
                            <span>{{ formatDate(data.updated_at) }}</span>
                        </div>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" type="text" class="p-column-filter" placeholder="Rechercher par date" @input="filterCallback" />
                    </template>
                </Column>
                <Column header="Actions" style="min-width: 10rem">
                    <template #body="{ data }">
                        <div class="flex gap-1">
                            <Button icon="pi pi-pencil" class="p-button-rounded p-button-text p-button-sm" @click="editGrade(data)" v-tooltip="'Modifier'" />
                            <Button
                                icon="pi pi-trash"
                                class="p-button-rounded p-button-text p-button-danger p-button-sm trash-icon"
                                @click="confirmDeleteGrade(data)"
                                v-tooltip="'Supprimer'"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- Modal for Add/Edit Grade -->
        <GradeForm
            :visible="formVisible"
            :grade-to-edit="gradeToEdit"
            @update:visible="formVisible = $event"
            @save="onGradeSaved"
            @update="onGradeUpdated"
            @close="closeForm"
        />

        <!-- Delete Confirmation Modal -->
        <Dialog
            v-model:visible="deleteFormVisible"
            header="Confirmer la suppression"
            :modal="true"
            :style="{ width: '450px' }"
            :breakpoints="{ '960px': '75vw', '640px': '90vw' }"
            class="p-dialog surface-card border-round-lg shadow-4"
            :pt="{
                mask: { style: 'backdrop-filter: blur(4px)' },
                header: { class: 'surface-50 border-bottom-1 surface-border p-3' },
                content: { class: 'surface-ground p-4' },
                footer: { class: 'surface-50 border-top-1 surface-border p-3' },
            }"
        >
            <div class="confirmation-content flex flex-column gap-3">
                <div class="flex align-items-center gap-3">
                    <i class="pi pi-exclamation-triangle text-4xl text-red-500" />
                    <span>Voulez-vous vraiment supprimer le grade <b>{{ gradeToDelete?.poste_fr }}</b> ?</span>
                </div>
                <div class="field">
                    <label for="deleteGradePassword" class="font-semibold">Mot de passe de confirmation</label>
                    <InputText
                        id="deleteGradePassword"
                        v-model="deletePassword"
                        type="password"
                        class="w-full"
                        :class="{ 'p-invalid': errors.passwordError }"
                        placeholder="Entrez le mot de passe"
                        @input="errors.passwordError = ''"
                    />
                    <small v-if="errors.passwordError" class="p-error">{{ errors.passwordError }}</small>
                </div>
            </div>
            <template #footer>
                <Button label="Annuler" icon="pi pi-times" class="p-button-text" @click="cancelDeleteGrade" />
                <Button
                    label="Supprimer"
                    icon="pi pi-check"
                    class="p-button-danger"
                    :disabled="loading || !deletePassword"
                    @click="deleteGrade"
                />
            </template>
        </Dialog>

        <!-- Import Errors Modal -->
        <Dialog
            v-model:visible="showImportError"
            header="Erreurs d'importation"
            :modal="true"
            :style="{ width: '600px' }"
            :breakpoints="{ '960px': '75vw', '640px': '90vw' }"
            class="p-dialog surface-card border-round-lg shadow-4"
            :pt="{
                mask: { style: 'backdrop-filter: blur(4px)' },
                header: { class: 'surface-50 border-bottom-1 surface-border p-3' },
                content: { class: 'surface-ground p-4' },
                footer: { class: 'surface-50 border-top-1 surface-border p-3' },
            }"
        >
            <DataTable :value="errors.import" size="small" class="p-datatable-sm border-round-lg">
                <Column field="line" header="Ligne" />
                <Column field="error" header="Erreur" />
            </DataTable>
            <template #footer>
                <Button label="Fermer" icon="pi pi-times" class="p-button-text" @click="showImportError = false" />
            </template>
        </Dialog>

        <Toast position="top-right" />
    </div>
</template>

<script>
import { ref } from 'vue';
import { FilterMatchMode } from 'primevue/api';
import { useToast } from 'primevue/usetoast';
import axios from '@/axios.js';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import SplitButton from 'primevue/splitbutton';
import Toast from 'primevue/toast';
import ExcelJS from 'exceljs';
import GradeForm from './GradeForm.vue';

export default {
    components: { Button, InputText, DataTable, Column, Dialog, SplitButton, Toast, GradeForm },
    setup() {
        const toast = useToast();
        const deleteFormVisible = ref(false);
        const gradeToDelete = ref(null);
        const deletePassword = ref('');
        const errors = ref({ passwordError: '', import: [] });

        return { toast, deleteFormVisible, gradeToDelete, deletePassword, errors };
    },
    data() {
        return {
            grades: [],
            selectedGrades: [],
            filters: {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                filiere_fr: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                filiere_ar: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                grade_fr: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                grade_ar: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                poste_fr: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                poste_ar: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                created_at: { value: null, matchMode: FilterMatchMode.CONTAINS },
                updated_at: { value: null, matchMode: FilterMatchMode.CONTAINS },
            },
            actions: [
                { label: 'Exporter en Excel', icon: 'pi pi-file-excel', command: () => this.exportXLS() },
                { label: 'Modifier', icon: 'pi pi-pencil', command: () => this.editGrade(this.selectedGrades[0]), disabled: () => this.selectedGrades.length !== 1 },
                { label: 'Supprimer', icon: 'pi pi-trash', command: () => this.confirmDeleteGrade(this.selectedGrades[0]), disabled: () => this.selectedGrades.length === 0 },
            ],
            loading: false,
            formVisible: false,
            gradeToEdit: null,
            showImportError: false,
        };
    },
    created() {
        this.fetchGrades();
    },
    methods: {
        async fetchGrades() {
            this.loading = true;
            try {
                const { data } = await axios.get('api/grades');
                if (!Array.isArray(data.data)) {
                    throw new Error('Réponse des grades invalide');
                }
                this.grades = data.data;
            } catch (error) {
                console.error('Fetch grades error:', error.response || error);
                this.showError(error.response?.data?.message || 'Erreur lors du chargement des grades.');
                this.grades = [];
                if (error.response?.status === 401) {
                    this.$router.push({ name: 'login' });
                }
            } finally {
                this.loading = false;
            }
        },

        openAddForm() {
            this.gradeToEdit = null;
            this.formVisible = true;
        },

        openForm(grade) {
            this.gradeToEdit = grade ? { ...grade } : null;
            this.formVisible = true;
        },

        closeForm() {
            this.formVisible = false;
            this.gradeToEdit = null;
        },

        onGradeSaved(grade) {
            this.grades.unshift(grade);
            this.showSuccess('Grade créé avec succès.');
            this.closeForm();
        },

        onGradeUpdated(grade) {
            const index = this.grades.findIndex(g => g.id === grade.id);
            if (index !== -1) {
                this.grades.splice(index, 1, grade);
            }
            this.showSuccess('Grade mis à jour avec succès.');
            this.closeForm();
        },

        editGrade(grade) {
            if (!grade || (this.selectedGrades.length === 1 && this.selectedGrades[0].id !== grade.id)) {
                this.showWarn('Sélectionnez un seul grade.');
                return;
            }
            this.openForm(grade);
        },

        confirmDeleteGrade(grade) {
            if (!grade || !grade.id) {
                this.showWarn('Sélectionnez un grade valide.');
                return;
            }
            this.gradeToDelete = { ...grade };
            this.deletePassword = '';
            this.errors.passwordError = '';
            this.deleteFormVisible = true;
        },

        cancelDeleteGrade() {
            this.deleteFormVisible = false;
            this.gradeToDelete = null;
            this.deletePassword = '';
            this.errors.passwordError = '';
        },

        async deleteGrade() {
            if (!this.gradeToDelete) {
                this.showError('Aucun grade sélectionné.');
                return;
            }
            this.loading = true;
            try {
                const cleanedPassword = this.deletePassword.trim();
                const response = await axios.delete(`api/grades/${this.gradeToDelete.id}`, {
                    data: { password: cleanedPassword },
                });
                this.grades = this.grades.filter(g => g.id !== this.gradeToDelete.id);
                this.showSuccess(response.data.message || 'Grade supprimé avec succès.');
                this.cancelDeleteGrade();
            } catch (error) {
                console.error('Delete error:', error.response || error);
                const errorMessage = error.response?.data?.message || error.response?.data?.error || 'Erreur lors de la suppression. Vérifiez le mot de passe.';
                this.errors.passwordError = errorMessage;
                this.showError(errorMessage);
            } finally {
                this.loading = false;
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
                    const { data } = await axios.post('api/grades/importxls', formData, {
                        headers: { 'Content-Type': 'multipart/form-data' },
                    });
                    this.errors.import = Array.isArray(data.data.error_lines) ? data.data.error_lines : [];
                    this.showSuccess(this.errors.import.length ? `Import terminé avec ${this.errors.import.length} erreurs.` : 'Importation réussie.');
                    if (this.errors.import.length) this.showImportError = true;
                    await this.fetchGrades();
                } catch (error) {
                    console.error('Import XLS error:', error.response || error);
                    this.showError(error.response?.data?.message || "Erreur lors de l'import XLS.");
                } finally {
                    fileInput.remove();
                }
            };
            document.body.appendChild(fileInput);
            fileInput.click();
        },

        async exportXLS() {
            try {
                const workbook = new ExcelJS.Workbook();
                const worksheet = workbook.addWorksheet('Grades');
                worksheet.columns = [
                    { header: 'Filière (FR)', key: 'filiere_fr', width: 20 },
                    { header: 'Filière (AR)', key: 'filiere_ar', width: 20 },
                    { header: 'Grade (FR)', key: 'grade_fr', width: 20 },
                    { header: 'Grade (AR)', key: 'grade_ar', width: 20 },
                    { header: 'Poste (FR)', key: 'poste_fr', width: 20 },
                    { header: 'Poste (AR)', key: 'poste_ar', width: 20 },
                    { header: 'Date d\'ajout', key: 'created_at', width: 15 },
                    { header: 'Date de modification', key: 'updated_at', width: 15 },
                ];
                this.grades.forEach(g => worksheet.addRow({
                    filiere_fr: g.filiere_fr,
                    filiere_ar: g.filiere_ar || 'غير محدد',
                    grade_fr: g.grade_fr,
                    grade_ar: g.grade_ar || 'غير محدد',
                    poste_fr: g.poste_fr,
                    poste_ar: g.poste_ar || 'غير محدد',
                    created_at: this.formatDate(g.created_at),
                    updated_at: this.formatDate(g.updated_at),
                }));
                const buffer = await workbook.xlsx.writeBuffer();
                const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'grades.xlsx';
                link.click();
                URL.revokeObjectURL(link.href);
                this.showSuccess('Exportation réussie.');
            } catch (error) {
                console.error('Export XLS error:', error);
                this.showError("Erreur lors de l'export XLS.");
            }
        },

        formatDate(date) {
            if (!date) return '-';
            return new Date(date).toLocaleDateString('fr-FR');
        },

        clearFilter() {
            this.filters = {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                filiere_fr: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                filiere_ar: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                grade_fr: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                grade_ar: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                poste_fr: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                poste_ar: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                created_at: { value: null, matchMode: FilterMatchMode.CONTAINS },
                updated_at: { value: null, matchMode: FilterMatchMode.CONTAINS },
            };
        },

        showSuccess(message) {
            this.toast.add({ severity: 'success', summary: 'Succès', detail: message, life: 3000 });
        },

        showError(message) {
            this.toast.add({ severity: 'error', summary: 'Erreur', detail: message, life: 5000 });
        },

        showWarn(message) {
            this.toast.add({ severity: 'warn', summary: 'Attention', detail: message, life: 3000 });
        },
    },
};
</script>

<style scoped>
@font-face {
    font-family: 'NotoNaskhArabic';
    src: url('/fonts/NotoNaskhArabic-VariableFont_wght.ttf') format('opentype');
    font-weight: normal;
    font-style: normal;
    font-display: swap;
}

.arabic-text {
    font-family: 'NotoNaskhArabic', sans-serif;
    direction: rtl;
    text-align: right;
}

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
    color: #28a745;
}
.export-icon i {
    color: #007bff;
}
.trash-icon i {
    color: #dc3545;
}
.p-tooltip-text {
    white-space: nowrap;
}
</style>
