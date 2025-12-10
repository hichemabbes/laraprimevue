<template>
    <div class="surface-ground pt-2 px-1.5 pb-1.5" style="margin-top: 0">
        <!-- Header Section with Navbar -->
        <div class="surface-card p-2 border-round border-1 surface-border" style="position: relative; top: -37px; box-shadow: none; margin-bottom: -33px;">
            <div class="flex justify-content-between align-items-center">
                <div class="flex gap-3">
                    <Button label="Accueil" icon="pi pi-home" class="p-button-text p-button-plain" @click="$router.push({ name: 'dashboard' })" />
                    <Button label="Rôles" icon="pi pi-key" class="p-button-text p-button-plain" disabled />
                </div>
                <div class="flex gap-2">
                    <Button icon="pi pi-refresh" class="p-button-rounded p-button-text" @click="fetchRoles" v-tooltip="'Rafraîchir'" />
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
                    <Button icon="pi pi-plus" label="Ajouter Rôle" class="p-button-success" @click="openAddForm" />
                    <SplitButton label="Actions" icon="pi pi-ellipsis-v" :model="actions" class="p-button-secondary" />
                </div>
            </div>

            <!-- Main DataTable -->
            <DataTable
                v-model:filters="filters"
                v-model:selection="selectedRoles"
                :value="roles"
                dataKey="id"
                size="small"
                stripedRows
                paginator
                :rows="10"
                :rowsPerPageOptions="[10, 25, 50]"
                filterDisplay="menu"
                :globalFilterFields="['name', 'name_ar', 'guard_name', 'statut', 'creer_par', 'desactive_par', 'created_at', 'updated_at']"
                :loading="loading"
                scrollable
                scrollHeight="600px"
                removableSort
                class="p-datatable-sm border-round-lg"
            >
                <template #empty>
                    <div class="text-center py-4">
                        <i class="pi pi-inbox text-4xl text-400 mb-2" />
                        <p class="text-600">Aucun rôle trouvé</p>
                    </div>
                </template>
                <Column selectionMode="multiple" headerStyle="width: 3rem" />
                <Column field="name" header="Nom" sortable style="min-width: 20rem">
                    <template #body="{ data }">
                        <span class="font-medium">{{ data.name }}</span>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" type="text" class="p-column-filter" placeholder="Rechercher par nom" @input="filterCallback" />
                    </template>
                </Column>
                <Column field="name_ar" header="Nom (Arabe)" sortable style="min-width: 20rem">
                    <template #body="{ data }">
                        <div class="flex align-items-center gap-2">
                            <i class="pi pi-tag text-primary-500" />
                            <span class="arabic-text">{{ data.name_ar || 'غير محدد' }}</span>
                        </div>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" type="text" class="p-column-filter" placeholder="Rechercher par nom arabe" @input="filterCallback" />
                    </template>
                </Column>
                <Column field="guard_name" header="Guard" sortable style="min-width: 15rem">
                    <template #body="{ data }">
                        <Tag :value="data.guard_name || 'Non disponible'" severity="info" icon="pi pi-shield" />
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" type="text" class="p-column-filter" placeholder="Rechercher par guard" @input="filterCallback" />
                    </template>
                </Column>
                <Column field="is_centre_role" header="Rôle de centre" sortable style="min-width: 15rem">
                    <template #body="{ data }">
                        <Tag :value="data.is_centre_role ? 'Oui' : 'Non'" :severity="data.is_centre_role ? 'success' : 'warning'" :icon="data.is_centre_role ? 'pi pi-check' : 'pi pi-times'" />
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-model="filterModel.value"
                            :options="[{ label: 'Oui', value: true }, { label: 'Non', value: false }]"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Filtrer par type"
                            class="p-column-filter"
                            @change="filterCallback"
                        />
                    </template>
                </Column>
                <Column field="statut" header="Statut" sortable style="min-width: 15rem">
                    <template #body="{ data }">
                        <Tag :value="data.statut || 'actif'" :severity="data.statut === 'actif' ? 'success' : 'danger'" :icon="data.statut === 'actif' ? 'pi pi-check' : 'pi pi-times'" />
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-model="filterModel.value"
                            :options="[{ label: 'Actif', value: 'actif' }, { label: 'Inactif', value: 'inactif' }]"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Filtrer par statut"
                            class="p-column-filter"
                            @change="filterCallback"
                        />
                    </template>
                </Column>
                <Column field="creer_par" header="Créé par" sortable style="min-width: 15rem">
                    <template #body="{ data }">
                        <span>{{ data.creer_par || 'Non défini' }}</span>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" type="text" class="p-column-filter" placeholder="Rechercher par créateur" @input="filterCallback" />
                    </template>
                </Column>
                <Column field="desactive_par" header="Désactivé par" sortable style="min-width: 15rem">
                    <template #body="{ data }">
                        <span>{{ data.desactive_par || 'Non défini' }}</span>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" type="text" class="p-column-filter" placeholder="Rechercher par désactivateur" @input="filterCallback" />
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
                            <Button icon="pi pi-pencil" class="p-button-rounded p-button-text p-button-sm" @click="editRole(data)" v-tooltip="'Modifier'" />
                            <Button
                                icon="pi pi-trash"
                                class="p-button-rounded p-button-text p-button-danger p-button-sm trash-icon"
                                @click="confirmDeleteRole(data)"
                                v-tooltip="'Supprimer'"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- Modal for Add/Edit Role -->
        <RoleForm
            :visible="formVisible"
            :role-to-edit="roleToEdit"
            @update:visible="formVisible = $event"
            @save="onRoleSaved"
            @update="onRoleUpdated"
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
                    <span>Voulez-vous vraiment supprimer le rôle <b>{{ roleToDelete?.name }}</b> ?</span>
                </div>
                <div class="field">
                    <label for="deleteRolePassword" class="font-semibold">Mot de passe de confirmation</label>
                    <InputText
                        id="deleteRolePassword"
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
                <Button label="Annuler" icon="pi pi-times" class="p-button-text" @click="cancelDeleteRole" />
                <Button
                    label="Supprimer"
                    icon="pi pi-check"
                    class="p-button-danger"
                    :disabled="loading || !deletePassword"
                    @click="deleteRole"
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
import Tag from 'primevue/tag';
import Dropdown from 'primevue/dropdown';
import Dialog from 'primevue/dialog';
import SplitButton from 'primevue/splitbutton';
import Toast from 'primevue/toast';
import ExcelJS from 'exceljs';
import RoleForm from './RoleForm.vue';

export default {
    components: { Button, InputText, DataTable, Column, Tag, Dropdown, Dialog, SplitButton, Toast, RoleForm },
    setup() {
        const toast = useToast();
        const deleteFormVisible = ref(false);
        const roleToDelete = ref(null);
        const deletePassword = ref('');
        const errors = ref({ passwordError: '', import: [] });

        return { toast, deleteFormVisible, roleToDelete, deletePassword, errors };
    },
    data() {
        return {
            roles: [],
            selectedRoles: [],
            filters: {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                name: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                name_ar: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                guard_name: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                is_centre_role: { value: null, matchMode: FilterMatchMode.EQUALS },
                statut: { value: null, matchMode: FilterMatchMode.EQUALS },
                creer_par: { value: null, matchMode: FilterMatchMode.CONTAINS },
                desactive_par: { value: null, matchMode: FilterMatchMode.CONTAINS },
                created_at: { value: null, matchMode: FilterMatchMode.CONTAINS },
                updated_at: { value: null, matchMode: FilterMatchMode.CONTAINS },
            },
            actions: [
                { label: 'Exporter en Excel', icon: 'pi pi-file-excel', command: () => this.exportXLS() },
                { label: 'Modifier', icon: 'pi pi-pencil', command: () => this.editRole(this.selectedRoles[0]), disabled: () => this.selectedRoles.length !== 1 },
                { label: 'Supprimer', icon: 'pi pi-trash', command: () => this.confirmDeleteRole(this.selectedRoles[0]), disabled: () => this.selectedRoles.length === 0 },
            ],
            loading: false,
            formVisible: false,
            roleToEdit: null,
            showImportError: false,
        };
    },
    created() {
        this.fetchRoles();
    },
    methods: {
        async fetchRoles() {
            this.loading = true;
            try {
                const { data } = await axios.get('api/roles');
                if (!Array.isArray(data.data)) {
                    throw new Error('Réponse des rôles invalide');
                }
                console.log('Fetched roles:', data.data);
                this.roles = data.data;
            } catch (error) {
                console.error('Fetch roles error:', error.response || error);
                this.showError(error.response?.data?.message || 'Erreur lors du chargement des rôles.');
                this.roles = [];
                if (error.response?.status === 401) {
                    this.$router.push({ name: 'login' });
                }
            } finally {
                this.loading = false;
            }
        },

        openAddForm() {
            console.log('Opening form for adding new role');
            this.roleToEdit = null; // Explicitly reset roleToEdit
            this.formVisible = true;
        },

        openForm(role) {
            console.log('Opening form for editing, role:', role);
            this.roleToEdit = role ? { ...role } : null;
            this.formVisible = true;
        },

        closeForm() {
            console.log('Closing form, resetting roleToEdit');
            this.formVisible = false;
            this.roleToEdit = null;
        },

        onRoleSaved(role) {
            console.log('Role saved:', role);
            this.roles.unshift(role);
            this.showSuccess('Rôle créé avec succès.');
            this.closeForm();
        },

        onRoleUpdated(role) {
            console.log('Role updated:', role);
            const index = this.roles.findIndex(r => r.id === role.id);
            if (index !== -1) {
                this.roles.splice(index, 1, role);
            }
            this.showSuccess('Rôle mis à jour avec succès.');
            this.closeForm();
        },

        editRole(role) {
            console.log('editRole called with role:', role);
            if (!role || (this.selectedRoles.length === 1 && this.selectedRoles[0].id !== role.id)) {
                this.showWarn('Sélectionnez un seul rôle.');
                return;
            }
            this.openForm(role);
        },

        confirmDeleteRole(role) {
            console.log('confirmDeleteRole called with role:', role);
            if (!role || !role.id) {
                console.warn('No valid role provided for deletion');
                this.showWarn('Sélectionnez un rôle valide.');
                return;
            }
            this.roleToDelete = { ...role };
            this.deletePassword = '';
            this.errors.passwordError = '';
            console.log('Setting deleteFormVisible to true');
            this.deleteFormVisible = true;
        },

        cancelDeleteRole() {
            console.log('cancelDeleteRole called');
            this.deleteFormVisible = false;
            this.roleToDelete = null;
            this.deletePassword = '';
            this.errors.passwordError = '';
        },

        async deleteRole() {
            if (!this.roleToDelete) {
                this.showError('Aucun rôle sélectionné.');
                return;
            }
            this.loading = true;
            try {
                const cleanedPassword = this.deletePassword.trim();
                console.log('Attempting to delete role:', this.roleToDelete.id);
                const response = await axios.delete(`api/roles/${this.roleToDelete.id}`, {
                    data: { password: cleanedPassword },
                });
                this.roles = this.roles.filter(r => r.id !== this.roleToDelete.id);
                this.showSuccess(response.data.message || 'Rôle supprimé avec succès.');
                this.cancelDeleteRole();
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
                    const { data } = await axios.post('api/roles/importxls', formData, {
                        headers: { 'Content-Type': 'multipart/form-data' },
                    });
                    this.errors.import = Array.isArray(data.data.error_lines) ? data.data.error_lines : [];
                    this.showSuccess(this.errors.import.length ? `Import terminé avec ${this.errors.import.length} erreurs.` : 'Importation réussie.');
                    if (this.errors.import.length) this.showImportError = true;
                    await this.fetchRoles();
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
                const worksheet = workbook.addWorksheet('Rôles');
                worksheet.columns = [
                    { header: 'Nom', key: 'name', width: 20 },
                    { header: 'Nom (Arabe)', key: 'name_ar', width: 20 },
                    { header: 'Guard', key: 'guard_name', width: 15 },
                    { header: 'Rôle de centre', key: 'is_centre_role', width: 15 },
                    { header: 'Statut', key: 'statut', width: 15 },
                    { header: 'Créé par', key: 'creer_par', width: 20 },
                    { header: 'Désactivé par', key: 'desactive_par', width: 20 },
                    { header: 'Date d\'ajout', key: 'created_at', width: 15 },
                    { header: 'Date de modification', key: 'updated_at', width: 15 },
                ];
                this.roles.forEach(r => worksheet.addRow({
                    name: r.name,
                    name_ar: r.name_ar || 'غير محدد',
                    guard_name: r.guard_name || 'Non disponible',
                    is_centre_role: r.is_centre_role ? 'Oui' : 'Non',
                    statut: r.statut || 'actif',
                    creer_par: r.creer_par || 'Non défini',
                    desactive_par: r.desactive_par || 'Non défini',
                    created_at: this.formatDate(r.created_at),
                    updated_at: this.formatDate(r.updated_at),
                }));
                const buffer = await workbook.xlsx.writeBuffer();
                const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'roles.xlsx';
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
                name: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                name_ar: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                guard_name: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                is_centre_role: { value: null, matchMode: FilterMatchMode.EQUALS },
                statut: { value: null, matchMode: FilterMatchMode.EQUALS },
                creer_par: { value: null, matchMode: FilterMatchMode.CONTAINS },
                desactive_par: { value: null, matchMode: FilterMatchMode.CONTAINS },
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
