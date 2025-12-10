<template>
    <div class="surface-ground pt-2 px-1.5 pb-1.5" style="margin-top: 0">
        <!-- Header Section with Navbar -->
        <div
            class="surface-card p-2 border-round border-1 surface-border"
            style="
                position: relative;
                top: -37px;
                box-shadow: none;
                margin-bottom: -33px;
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
                        label="Utilisateurs & Centres"
                        icon="pi pi-users"
                        class="p-button-text p-button-plain"
                    />
                </div>
                <div class="flex gap-2">
                    <Button
                        icon="pi pi-refresh"
                        class="p-button-rounded p-button-text"
                        @click="fetchUserCentres"
                        v-tooltip="'Rafraîchir'"
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
                        label="Ajouter Affectation"
                        class="p-button-success"
                        @click="openForm"
                    />
                </div>
            </div>

            <!-- Main DataTable -->
            <DataTable
                v-model:filters="filters"
                v-model:selection="selectedUserCentres"
                :value="userCentres"
                dataKey="id"
                size="small"
                stripedRows
                paginator
                :rows="10"
                :rowsPerPageOptions="[10, 25, 50]"
                filterDisplay="menu"
                :globalFilterFields="[
                    'user.nom_fr',
                    'user.prenom_fr',
                    'centre.nom_fr',
                    'centre.nom_ar',
                    'role.name',
                    'type_affectation_fr',
                    'statut'
                ]"
                :loading="loading"
                scrollable
                scrollHeight="600px"
                removableSort
                class="p-datatable-sm border-round-lg"
            >
                <template #empty>
                    <div class="text-center py-4">
                        <i class="pi pi-inbox text-4xl text-400 mb-2" />
                        <p class="text-600">
                            Aucune affectation utilisateur-centre trouvée
                        </p>
                    </div>
                </template>

                <Column selectionMode="multiple" headerStyle="width: 3rem" />

                <Column
                    field="user.nom_fr"
                    header="Nom (FR)"
                    sortable
                    style="min-width: 12rem"
                >
                    <template #body="{ data }">
                        <span class="font-medium">{{ data.user?.nom_fr }} {{ data.user?.prenom_fr }}</span>
                    </template>
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
                    field="centre.nom_fr"
                    header="Centre (FR)"
                    sortable
                    style="min-width: 12rem"
                >
                    <template #body="{ data }">
                        <span>{{ data.centre?.nom_fr }}</span>
                    </template>
                </Column>

                <Column
                    field="centre.nom_ar"
                    header="Centre (AR)"
                    sortable
                    style="min-width: 12rem"
                >
                    <template #body="{ data }">
                        <span class="arabic-text">{{ data.centre?.nom_ar }}</span>
                    </template>
                </Column>

                <Column
                    field="role.name"
                    header="Rôle"
                    sortable
                    style="min-width: 10rem"
                >
                    <template #body="{ data }">
                        <Tag :value="data.role ? data.role.name : 'Non défini'" />
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-model="filterModel.value"
                            :options="roles"
                            optionLabel="name"
                            optionValue="name"
                            placeholder="Sélectionner un rôle"
                            class="p-column-filter"
                            @change="filterCallback"
                            :showClear="true"
                            :loading="loadingRoles"
                            :disabled="loadingRoles && roles.length === 0"
                        />
                    </template>
                </Column>

                <Column
                    field="type_affectation_fr"
                    header="Type Affectation"
                    sortable
                    style="min-width: 12rem"
                >
                    <template #body="{ data }">
                        <Tag :value="data.type_affectation_fr || 'Non défini'" />
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
                            :value="formatStatut(data.statut)"
                            :severity="getSeverity(data.statut)"
                            :icon="getStatutIcon(data.statut)"
                        />
                    </template>
                </Column>

                <Column
                    header="Actions"
                    :exportable="false"
                    style="min-width: 10rem"
                >
                    <template #body="{ data }">
                        <div class="flex gap-1">
                            <Button
                                icon="pi pi-pencil"
                                class="p-button-rounded p-button-text p-button-sm"
                                @click="editUserCentre(data)"
                                v-tooltip="'Modifier'"
                            />
                            <Button
                                icon="pi pi-trash"
                                class="p-button-rounded p-button-text p-button-danger p-button-sm"
                                @click="confirmDeleteUserCentre(data)"
                                v-tooltip="'Supprimer'"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>

            <!-- UserCentre Form Modal -->
            <UserCentreForm
                :visible="formVisible"
                :userCentreToEdit="userCentreToEdit"
                @update:visible="formVisible = $event"
                @save="handleSaveUserCentre"
                @update="handleUpdateUserCentre"
                @close="closeForm"
            />

            <!-- Delete Confirmation Dialog -->
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
                <div class="flex flex-column gap-3">
                    <div class="flex align-items-center gap-3">
                        <i class="pi pi-exclamation-triangle text-4xl text-red-500" />
                        <span>
                            Voulez-vous vraiment supprimer l'affectation de
                            <b>{{ userCentreToDelete?.user?.nom_fr }} {{ userCentreToDelete?.user?.prenom_fr }}</b>
                            au centre <b>{{ userCentreToDelete?.centre?.nom_fr }}</b> ?
                        </span>
                    </div>
                    <div class="field">
                        <label for="deletePassword" class="font-semibold">Mot de passe de confirmation</label>
                        <InputText
                            id="deletePassword"
                            v-model="deletePassword"
                            type="password"
                            class="w-full"
                            :class="{ 'p-invalid': passwordError }"
                            placeholder="Entrez le mot de passe"
                            @input="passwordError = ''"
                        />
                        <small v-if="passwordError" class="p-error">{{ passwordError }}</small>
                    </div>
                </div>
                <template #footer>
                    <Button
                        label="Annuler"
                        icon="pi pi-times"
                        class="p-button-text"
                        @click="cancelDeleteUserCentre"
                    />
                    <Button
                        label="Supprimer"
                        icon="pi pi-check"
                        class="p-button-danger"
                        :disabled="loading || !deletePassword"
                        @click="deleteUserCentre"
                    />
                </template>
            </Dialog>

            <!-- Toast Notification -->
            <Toast position="top-right" />
        </div>
    </div>
</template>

<script>
import { FilterMatchMode } from 'primevue/api';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';

// Components
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import Dropdown from 'primevue/dropdown';
import UserCentreForm from '@/Components/Atfp/Corbeils/PersonnelCentreForm.vue';

export default {
    components: {
        Button,
        DataTable,
        Column,
        InputText,
        Tag,
        Dialog,
        Toast,
        Dropdown,
        UserCentreForm,
    },
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            userCentres: [],
            selectedUserCentres: [],
            filters: null,
            formVisible: false,
            deleteFormVisible: false,
            userCentreToEdit: null,
            userCentreToDelete: null,
            deletePassword: '',
            passwordError: '',
            loading: false,
            roles: [],
            loadingRoles: false,
        };
    },
    created() {
        this.initFilters();
        this.fetchUserCentres();
        this.fetchFormData();
    },
    methods: {
        initFilters() {
            this.filters = {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                'user.nom_fr': { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                'centre.nom_fr': { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                'role.name': { value: null, matchMode: FilterMatchMode.EQUALS },
                statut: { value: null, matchMode: FilterMatchMode.EQUALS },
            };
        },
        clearFilter() {
            this.initFilters();
        },
        formatStatut(statut) {
            const statusLabels = {
                actif: 'Actif',
                inactif: 'Inactif',
                suspendu: 'Suspendu',
            };
            return statusLabels[statut] || statut || 'Inactif';
        },
        getSeverity(statut) {
            const statusSeverity = {
                actif: 'success',
                inactif: 'secondary',
                suspendu: 'warning',
            };
            return statusSeverity[statut] || 'secondary';
        },
        getStatutIcon(statut) {
            const statusIcons = {
                actif: 'pi pi-check',
                inactif: 'pi pi-times',
                suspendu: 'pi pi-exclamation-triangle',
            };
            return statusIcons[statut] || 'pi pi-info';
        },
        async fetchFormData() {
            this.loadingRoles = true;
            try {
                const response = await axios.get('/api/form-data');
                this.roles = Array.isArray(response.data.roles) ? response.data.roles : [];
                if (this.roles.length === 0) {
                    this.toast.add({
                        severity: 'warn',
                        summary: 'Avertissement',
                        detail: 'Aucun rôle disponible pour le filtre.',
                        life: 5000,
                    });
                }
            } catch (error) {
                console.error('Error fetching form data:', error);
                this.roles = [];
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Erreur lors du chargement des rôles pour le filtre.',
                    life: 3000,
                });
            } finally {
                this.loadingRoles = false;
            }
        },
        async fetchUserCentres() {
            this.loading = true;
            try {
                const response = await axios.get('/api/users-centres', {
                    params: {
                        with: ['user', 'centre', 'role']
                    }
                });
                if (!Array.isArray(response.data)) {
                    throw new Error('Réponse des affectations invalide');
                }
                this.userCentres = response.data;
                if (this.userCentres.length === 0) {
                    this.toast.add({
                        severity: 'info',
                        summary: 'Information',
                        detail: 'Aucune affectation utilisateur-centre trouvée.',
                        life: 3000,
                    });
                }
            } catch (error) {
                console.error('Erreur lors du chargement des affectations:', {
                    status: error.response?.status,
                    message: error.response?.data?.message || error.message,
                });
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Erreur lors du chargement des affectations.',
                    life: 3000,
                });
                if (error.response?.status === 401) {
                    this.$router.push({ name: 'login' });
                }
            } finally {
                this.loading = false;
            }
        },
        openForm() {
            this.userCentreToEdit = null;
            this.formVisible = true;
        },
        closeForm() {
            this.formVisible = false;
            this.userCentreToEdit = null;
        },
        editUserCentre(userCentre) {
            this.userCentreToEdit = { ...userCentre };
            this.formVisible = true;
        },
        confirmDeleteUserCentre(userCentre) {
            this.userCentreToDelete = userCentre;
            this.deletePassword = '';
            this.passwordError = '';
            this.deleteFormVisible = true;
        },
        cancelDeleteUserCentre() {
            this.deleteFormVisible = false;
            this.userCentreToDelete = null;
            this.deletePassword = '';
            this.passwordError = '';
        },
        async deleteUserCentre() {
            if (!this.userCentreToDelete) return;
            try {
                this.loading = true;
                const cleanedPassword = this.deletePassword.trim();
                const response = await axios.delete(
                    `/api/users-centres/${this.userCentreToDelete.id}`,
                    {
                        data: { password: cleanedPassword },
                    }
                );
                this.userCentres = this.userCentres.filter(
                    (uc) => uc.id !== this.userCentreToDelete.id
                );
                this.showSuccess(
                    response.data.message || 'Affectation supprimée avec succès.'
                );
                this.deleteFormVisible = false;
                this.userCentreToDelete = null;
                this.deletePassword = '';
                this.passwordError = '';
            } catch (error) {
                this.passwordError =
                    error.response?.data?.error ||
                    'Erreur lors de la suppression. Vérifiez le mot de passe.';
                this.showError(this.passwordError);
            } finally {
                this.loading = false;
            }
        },
        handleSaveUserCentre(userCentre) {
            this.userCentres.push({
                ...userCentre,
                user: { nom_fr: userCentre.user_nom_fr, prenom_fr: userCentre.user_prenom_fr },
                centre: { nom_fr: userCentre.centre_nom_fr, nom_ar: userCentre.centre_nom_ar },
                role: userCentre.role_id ? { id: userCentre.role_id, name: userCentre.role_name } : null,
            });
            this.showSuccess('Affectation créée avec succès.');
            this.closeForm();
        },
        handleUpdateUserCentre(updatedUserCentre) {
            const index = this.userCentres.findIndex(
                (uc) => uc.id === updatedUserCentre.id
            );
            if (index !== -1) {
                this.userCentres[index] = {
                    ...updatedUserCentre,
                    user: { nom_fr: updatedUserCentre.user_nom_fr, prenom_fr: updatedUserCentre.user_prenom_fr },
                    centre: { nom_fr: updatedUserCentre.centre_nom_fr, nom_ar: updatedUserCentre.centre_nom_ar },
                    role: updatedUserCentre.role_id ? { id: updatedUserCentre.role_id, name: updatedUserCentre.role_name } : null,
                };
                this.showSuccess('Affectation mise à jour avec succès.');
            }
            this.closeForm();
        },
        showSuccess(message) {
            this.toast.add({
                severity: 'success',
                summary: 'Succès',
                detail: message,
                life: 3000,
            });
        },
        showError(message) {
            this.toast.add({
                severity: 'error',
                summary: 'Erreur',
                detail: message,
                life: 5000,
            });
        },
    },
};
</script>

<style scoped>
@font-face {
    font-family: 'NotoNaskhArabic';
    src: url('/fonts/NotoNaskhArabic-VariableFont_wght.ttf') format('truetype');
    font-weight: normal;
    font-style: normal;
    font-display: swap;
}

.arabic-text {
    font-family: 'NotoNaskhArabic', sans-serif;
    direction: rtl;
    text-align: right;
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
        color: #e0e0e0;
    }
}
</style>
