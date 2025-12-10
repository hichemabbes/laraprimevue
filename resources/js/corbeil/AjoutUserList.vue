<template>
    <div>
        <DataTable
            v-model:filters="filters"
            showGridlines
            stripedRows
            :value="users"
            dataKey="id"
            size="small"
            :rows="20"
            filterDisplay="menu"
            :globalFilterFields="['name', 'email', 'roles.name']"
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
                    Aucun utilisateur trouvé.
                </div>
                <div v-else class="text-center p-3">Chargement en cours...</div>
            </template>

            <Column field="name" header="Nom" sortable style="width: 15rem">
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
            <Column field="email" header="Email" sortable style="width: 20rem">
                <template #filter="{ filterModel, filterCallback }">
                    <InputText
                        v-model="filterModel.value"
                        type="text"
                        class="p-column-filter"
                        placeholder="Rechercher par email"
                        @input="filterCallback"
                    />
                </template>
            </Column>
            <Column header="Rôles" style="width: 15rem">
                <template #body="{ data }">
                    <div class="flex justify-content-center align-items-center">
                        <Tag
                            :value="
                                data.roles.map((role) => role.name).join(', ')
                            "
                            severity="info"
                        />
                    </div>
                </template>
            </Column>
            <Column header="Centre" style="width: 15rem">
                <template #body="{ data }">
                    <div class="flex justify-content-center align-items-center">
                        <Tag
                            :value="
                                data.centres?.[0]?.nom_fr ||
                                'Direction Centrale'
                            "
                            :severity="
                                data.centres.length ? 'success' : 'warning'
                            "
                        />
                    </div>
                </template>
            </Column>
            <Column header="Actions" style="width: 10rem" frozen>
                <template #body="{ data }">
                    <div class="flex gap-2">
                        <Button
                            icon="pi pi-pencil"
                            severity="info"
                            text
                            @click="editUser(data)"
                        />
                        <Button
                            icon="pi pi-trash"
                            severity="danger"
                            text
                            @click="confirmDeleteUser(data)"
                        />
                    </div>
                </template>
            </Column>
        </DataTable>

        <!-- Formulaire d'ajout/édition -->
        <Dialog
            v-model:visible="formVisible"
            :header="
                userToEdit
                    ? 'Modifier un utilisateur'
                    : 'Ajouter un utilisateur'
            "
            :modal="true"
            :style="{ width: '450px' }"
        >
            <div class="p-fluid">
                <div class="field">
                    <label for="name">Nom</label>
                    <InputText
                        id="name"
                        v-model="newUser.name"
                        :class="{ 'p-invalid': errors.name }"
                    />
                    <small v-if="errors.name" class="p-error">{{
                        errors.name
                    }}</small>
                </div>
                <div class="field">
                    <label for="email">Email</label>
                    <InputText
                        id="email"
                        v-model="newUser.email"
                        :class="{ 'p-invalid': errors.email }"
                    />
                    <small v-if="errors.email" class="p-error">{{
                        errors.email
                    }}</small>
                </div>
                <div class="field">
                    <label for="role">Rôle</label>
                    <Dropdown
                        id="role"
                        v-model="newUser.role"
                        :options="roles"
                        optionLabel="name"
                        optionValue="name"
                        placeholder="Sélectionner un rôle"
                        :loading="rolesLoading"
                        :class="{ 'p-invalid': errors.role }"
                        @change="onRoleChange"
                    />
                    <small v-if="errors.role" class="p-error">{{
                        errors.role
                    }}</small>
                </div>
                <div class="field" v-if="isCentreRole">
                    <label for="centre_id">Centre</label>
                    <Dropdown
                        id="centre_id"
                        v-model="newUser.centre_id"
                        :options="centres"
                        optionLabel="nom_fr"
                        optionValue="id"
                        placeholder="Sélectionner un centre"
                        :loading="centresLoading"
                        :class="{ 'p-invalid': errors.centre_id }"
                    />
                    <small v-if="errors.centre_id" class="p-error">{{
                        errors.centre_id
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
                    :label="userToEdit ? 'Mettre à jour' : 'Créer'"
                    icon="pi pi-check"
                    :loading="saving"
                    @click="userToEdit ? updateUser() : createUser()"
                />
            </template>
        </Dialog>

        <!-- Confirmation de suppression -->
        <Dialog
            v-model:visible="deleteFormVisible"
            header="Confirmation de suppression"
            :modal="true"
            :style="{ width: '450px' }"
        >
            <div class="p-fluid">
                <p v-if="userToDelete">
                    Êtes-vous sûr de vouloir supprimer l'utilisateur
                    <strong>{{ userToDelete.name }}</strong> ?
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
                    @click="deleteUser"
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
import Tag from 'primevue/tag';
import Dropdown from 'primevue/dropdown';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';

export default {
    components: {
        Button,
        InputText,
        DataTable,
        Column,
        Tag,
        Dropdown,
        Dialog,
        Toast,
    },
    data() {
        return {
            users: [],
            filters: null,
            loading: false,
            formVisible: false,
            saving: false,
            roles: [],
            rolesLoading: false,
            centres: [],
            centresLoading: false,
            deleteFormVisible: false,
            userToEdit: null,
            userToDelete: null,
            newUser: {
                name: '',
                email: '',
                role: '',
                centre_id: null,
                password: '', // Ajouté pour stocker le mot de passe généré
                password_confirmation: '', // Ajouté pour la confirmation
            },
            errors: {},
            isCentreRole: false,
        };
    },
    setup() {
        const toast = useToast();
        return { toast };
    },
    created() {
        this.initFilters();
        this.checkAuthAndFetchData();
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
                email: {
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
        async checkAuthAndFetchData() {
            try {
                await this.fetchUsers();
                await this.fetchRoles();
                await this.fetchCentres();
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Vous devez vous connecter.',
                    life: 3000,
                });
                this.$router.push('/login');
            }
        },
        async fetchUsers() {
            try {
                this.loading = true;
                const response = await axios.get('/users');
                this.users = response.data;
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        'Erreur lors du chargement des utilisateurs.',
                    life: 3000,
                });
            } finally {
                this.loading = false;
            }
        },
        async fetchRoles() {
            try {
                this.rolesLoading = true;
                const response = await axios.get('/roles');
                this.roles = response.data;
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
                this.rolesLoading = false;
            }
        },
        async fetchCentres() {
            try {
                this.centresLoading = true;
                const response = await axios.get('/centres');
                this.centres = response.data;
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        'Erreur lors du chargement des centres.',
                    life: 3000,
                });
            } finally {
                this.centresLoading = false;
            }
        },
        openForm() {
            this.userToEdit = null;
            this.resetForm();
            this.errors = {};
            this.isCentreRole = false;
            this.formVisible = true;
        },
        closeForm() {
            this.formVisible = false;
            this.userToEdit = null;
            this.resetForm();
            this.errors = {};
            this.isCentreRole = false;
        },
        generatePassword(name) {
            // Génère un mot de passe au format nom + "123***"
            return `${name}123***`;
        },
        async createUser() {
            this.errors = {};
            try {
                this.saving = true;
                // Générer le mot de passe automatiquement avant l'envoi
                const generatedPassword = this.generatePassword(
                    this.newUser.name
                );
                this.newUser.password = generatedPassword;
                this.newUser.password_confirmation = generatedPassword; // Confirmation identique

                const response = await axios.post('/users', this.newUser);
                this.users.unshift(response.data.user);
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: `Utilisateur créé ! Mot de passe : ${generatedPassword}`,
                    life: 5000,
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
        editUser(user) {
            this.userToEdit = { ...user };
            this.newUser = {
                name: user.name,
                email: user.email,
                role: user.roles[0]?.name || '',
                centre_id: user.centres?.[0]?.id || null,
            };
            this.checkIfCentreRole();
            this.errors = {};
            this.formVisible = true;
        },
        async updateUser() {
            this.errors = {};
            try {
                this.saving = true;
                const response = await axios.put(
                    `/users/${this.userToEdit.id}`,
                    this.newUser
                );
                const index = this.users.findIndex(
                    (u) => u.id === this.userToEdit.id
                );
                if (index !== -1)
                    this.users.splice(index, 1, response.data.user);
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Utilisateur mis à jour.',
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
        confirmDeleteUser(user) {
            this.userToDelete = user;
            this.deleteFormVisible = true;
        },
        async deleteUser() {
            if (this.userToDelete) {
                try {
                    await axios.delete(`/users/${this.userToDelete.id}`);
                    this.users = this.users.filter(
                        (u) => u.id !== this.userToDelete.id
                    );
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Utilisateur supprimé.',
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
                    this.userToDelete = null;
                }
            }
        },
        resetForm() {
            this.newUser = {
                name: '',
                email: '',
                role: '',
                centre_id: null,
                password: '',
                password_confirmation: '',
            };
            this.errors = {};
            this.isCentreRole = false;
        },
        onRoleChange() {
            this.checkIfCentreRole();
        },
        checkIfCentreRole() {
            const selectedRole = this.roles.find(
                (role) => role.name === this.newUser.role
            );
            this.isCentreRole = selectedRole?.is_centre_role || false;
            if (!this.isCentreRole) {
                this.newUser.centre_id = null;
            }
        },
    },
};
</script>

<style scoped>
.field {
    margin-bottom: 1.5rem;
}
.p-error {
    color: #dc3545;
}
</style>
