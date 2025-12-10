<template>
    <div>
        <DataTable
            v-model:filters="filters"
            showGridlines
            stripedRows
            :value="roles"
            dataKey="id"
            size="small"
            :rows="20"
            filterDisplay="menu"
            :globalFilterFields="['name', 'guard_name']"
            :loading="loading"
            scrollable
            scrollHeight="700px"
            :pt="{ table: { style: 'min-width: 50rem' } }"
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
                </div>
            </template>

            <template #empty>
                <div v-if="!loading" class="text-center p-3">
                    Aucun rôle trouvé.
                </div>
                <div v-else class="text-center p-3">Chargement en cours...</div>
            </template>

            <Column field="name" header="Nom" sortable style="width: 20rem">
                <template #filter="{ filterModel, filterCallback }">
                    <InputText
                        v-model="filterModel.value"
                        type="text"
                        class="p-column-filter"
                        placeholder="Rechercher par nom"
                        @input="filterCallback"
                    />
                </template>
            </Column>
            <Column
                field="guard_name"
                header="Guard"
                sortable
                style="width: 20rem"
            >
                <template #body="{ data }">
                    <span>{{ data.guard_name || 'Non défini' }}</span>
                </template>
                <template #filter="{ filterModel, filterCallback }">
                    <InputText
                        v-model="filterModel.value"
                        type="text"
                        class="p-column-filter"
                        placeholder="Rechercher par guard"
                        @input="filterCallback"
                    />
                </template>
            </Column>
            <Column
                field="is_centre_role"
                header="Rôle de centre"
                sortable
                style="width: 15rem"
            >
                <template #body="{ data }">
                    <span>{{ data.is_centre_role ? 'Oui' : 'Non' }}</span>
                </template>
                <template #filter="{ filterModel, filterCallback }">
                    <Dropdown
                        v-model="filterModel.value"
                        :options="[
                            { label: 'Oui', value: true },
                            { label: 'Non', value: false },
                        ]"
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Filtrer par type"
                        class="p-column-filter"
                        @change="filterCallback"
                    />
                </template>
            </Column>
            <Column header="Actions" style="width: 10rem" frozen>
                <template #body="{ data }">
                    <div class="flex gap-2">
                        <Button
                            icon="pi pi-pencil"
                            severity="info"
                            text
                            @click="editRole(data)"
                        />
                        <Button
                            icon="pi pi-trash"
                            severity="danger"
                            text
                            @click="confirmDeleteRole(data)"
                        />
                    </div>
                </template>
            </Column>
        </DataTable>

        <Dialog
            v-model:visible="formVisible"
            :header="roleToEdit ? 'Modifier un rôle' : 'Ajouter un rôle'"
            :modal="true"
            :style="{ width: '450px' }"
        >
            <div class="p-fluid">
                <div class="field">
                    <label for="name">Nom</label>
                    <InputText
                        id="name"
                        v-model="newRole.name"
                        :class="{ 'p-invalid': errors.name }"
                    />
                    <small v-if="errors.name" class="text-red-500">{{
                        errors.name
                    }}</small>
                </div>
                <div class="field">
                    <label for="guard_name">Guard (optionnel)</label>
                    <Dropdown
                        id="guard_name"
                        v-model="newRole.guard_name"
                        :options="guardOptions"
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Sélectionner un guard (facultatif)"
                        :showClear="true"
                    />
                    <small v-if="errors.guard_name" class="text-red-500">{{
                        errors.guard_name
                    }}</small>
                </div>
                <div class="field">
                    <label for="is_centre_role">Rôle lié à un centre</label>
                    <Checkbox
                        id="is_centre_role"
                        v-model="newRole.is_centre_role"
                        :binary="true"
                    />
                    <span class="ml-2">{{
                        newRole.is_centre_role ? 'Oui' : 'Non'
                    }}</span>
                    <small v-if="errors.is_centre_role" class="text-red-500">{{
                        errors.is_centre_role
                    }}</small>
                </div>
            </div>
            <template #footer>
                <Button
                    label="Annuler"
                    icon="pi pi-times"
                    text
                    @click="closeForm"
                />
                <Button
                    :label="roleToEdit ? 'Mettre à jour' : 'Créer'"
                    icon="pi pi-check"
                    :loading="saving"
                    @click="roleToEdit ? updateRole() : createRole()"
                />
            </template>
        </Dialog>

        <Dialog
            v-model:visible="deleteFormVisible"
            header="Confirmation de suppression"
            :modal="true"
            :style="{ width: '450px' }"
        >
            <div class="p-fluid">
                <p v-if="roleToDelete">
                    Êtes-vous sûr de vouloir supprimer le rôle
                    <strong>{{ roleToDelete.name }}</strong> ?
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
                    @click="deleteRole"
                />
            </template>
        </Dialog>

        <Toast />
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
import Dropdown from 'primevue/dropdown';
import Dialog from 'primevue/dialog';
import Checkbox from 'primevue/checkbox';
import Toast from 'primevue/toast';

export default {
    components: {
        Button,
        InputText,
        DataTable,
        Column,
        Dropdown,
        Dialog,
        Checkbox,
        Toast,
    },
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            roles: [],
            filters: null,
            loading: false,
            formVisible: false,
            saving: false,
            deleteFormVisible: false,
            roleToEdit: null,
            roleToDelete: null,
            newRole: {
                name: '',
                guard_name: null, // Nullable par défaut
                is_centre_role: false,
            },
            guardOptions: [
                { label: 'Aucun', value: null }, // Option vide pour rendre guard optionnel
                { label: 'Web', value: 'web' },
                { label: 'Sanctum', value: 'sanctum' },
            ],
            errors: {},
        };
    },
    created() {
        this.initFilters();
        this.fetchRoles();
    },
    methods: {
        initFilters() {
            this.filters = {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                name: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
                guard_name: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
                is_centre_role: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.EQUALS },
                    ],
                },
            };
        },
        clearFilter() {
            this.initFilters();
        },
        async fetchRoles() {
            try {
                this.loading = true;
                const response = await axios.get('/roles');
                this.roles = response.data; // Ajusté pour correspondre à la réponse directe de getRoles()
                console.log('Rôles chargés :', this.roles); // Pour débogage
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        'Erreur lors du chargement des rôles.',
                    life: 3000,
                });
            } finally {
                this.loading = false;
            }
        },
        openForm() {
            this.roleToEdit = null;
            this.resetForm();
            this.formVisible = true;
        },
        closeForm() {
            this.formVisible = false;
            this.roleToEdit = null;
            this.resetForm();
        },
        async createRole() {
            try {
                this.saving = true;
                this.errors = {};
                const response = await axios.post('/roles', this.newRole);
                this.roles.unshift(response.data.role);
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Rôle créé avec succès.',
                    life: 3000,
                });
                this.formVisible = false;
            } catch (error) {
                if (error.response?.status === 422) {
                    this.errors = error.response.data.errors || {};
                } else {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail:
                            error.response?.data?.message ||
                            'Erreur lors de la création.',
                        life: 3000,
                    });
                }
            } finally {
                this.saving = false;
            }
        },
        editRole(role) {
            this.roleToEdit = { ...role };
            this.newRole = {
                name: role.name,
                guard_name: role.guard_name || null,
                is_centre_role: role.is_centre_role,
            };
            this.formVisible = true;
        },
        async updateRole() {
            try {
                this.saving = true;
                this.errors = {};
                const response = await axios.put(
                    `/roles/${this.roleToEdit.id}`,
                    this.newRole
                );
                const index = this.roles.findIndex(
                    (r) => r.id === this.roleToEdit.id
                );
                if (index !== -1)
                    this.roles.splice(index, 1, response.data.role);
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Rôle mis à jour.',
                    life: 3000,
                });
                this.formVisible = false;
            } catch (error) {
                if (error.response?.status === 422) {
                    this.errors = error.response.data.errors || {};
                } else {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail:
                            error.response?.data?.message ||
                            'Erreur lors de la mise à jour.',
                        life: 3000,
                    });
                }
            } finally {
                this.saving = false;
            }
        },
        confirmDeleteRole(role) {
            this.roleToDelete = role;
            this.deleteFormVisible = true;
        },
        async deleteRole() {
            if (this.roleToDelete) {
                try {
                    await axios.delete(`/roles/${this.roleToDelete.id}`);
                    this.roles = this.roles.filter(
                        (r) => r.id !== this.roleToDelete.id
                    );
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Rôle supprimé.',
                        life: 3000,
                    });
                } catch (error) {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail:
                            error.response?.data?.message ||
                            'Erreur lors de la suppression.',
                        life: 3000,
                    });
                } finally {
                    this.deleteFormVisible = false;
                    this.roleToDelete = null;
                }
            }
        },
        resetForm() {
            this.newRole = {
                name: '',
                guard_name: null,
                is_centre_role: false,
            };
            this.errors = {};
        },
    },
};
</script>

<style scoped>
.field {
    margin-bottom: 1.5rem;
}
</style>
