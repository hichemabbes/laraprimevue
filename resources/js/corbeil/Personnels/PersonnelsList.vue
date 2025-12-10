<template>
    <div>
        <DataTable
            v-model:filters="filters"
            showGridlines
            stripedRows
            :value="personnels"
            dataKey="id"
            size="small"
            :rows="20"
            filterDisplay="menu"
            :globalFilterFields="[
                'user.name',
                'prenom_fr',
                'user.email',
                'fonction_principale',
                'matricule',
                'cin',
            ]"
            :loading="loading"
            scrollable
            scrollHeight="700px"
            :pt="{ table: { style: 'min-width: 80rem' } }"
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
                    Aucun personnel trouvé.
                </div>
                <div v-else class="text-center p-3">Chargement en cours...</div>
            </template>

            <Column
                field="user.name"
                header="Nom"
                sortable
                style="width: 10rem"
            >
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
                field="prenom_fr"
                header="Prénom (FR)"
                sortable
                style="width: 10rem"
            >
                <template #filter="{ filterModel, filterCallback }">
                    <InputText
                        v-model="filterModel.value"
                        type="text"
                        class="p-column-filter"
                        placeholder="Rechercher par prénom"
                        @input="filterCallback"
                    />
                </template>
            </Column>
            <Column
                field="matricule"
                header="Matricule"
                sortable
                style="width: 8rem"
            />
            <Column field="cin" header="CIN" sortable style="width: 8rem" />
            <Column
                field="user.email"
                header="Email"
                sortable
                style="width: 15rem"
            >
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
            <Column
                field="fonction_principale"
                header="Fonction"
                sortable
                style="width: 10rem"
            >
                <template #filter="{ filterModel, filterCallback }">
                    <InputText
                        v-model="filterModel.value"
                        type="text"
                        class="p-column-filter"
                        placeholder="Rechercher par fonction"
                        @input="filterCallback"
                    />
                </template>
            </Column>
            <Column header="Centre" style="width: 10rem">
                <template #body="{ data }">
                    <div class="flex justify-content-center align-items-center">
                        <Tag
                            :value="data.centre_nom_fr || 'Non affecté'"
                            :severity="
                                data.centre_nom_fr ? 'success' : 'warning'
                            "
                        />
                    </div>
                </template>
            </Column>
            <Column header="Statut" style="width: 10rem">
                <template #body="{ data }">
                    <Tag
                        :value="data.statut_affectation"
                        :severity="
                            data.statut_affectation === 'Actif'
                                ? 'success'
                                : 'danger'
                        "
                    />
                </template>
            </Column>
            <Column header="Rôle" style="width: 10rem">
                <template #body="{ data }">
                    <Tag
                        :value="data.user?.roles[0]?.name || 'Aucun'"
                        severity="info"
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
                            @click="editPersonnel(data)"
                        />
                        <Button
                            icon="pi pi-trash"
                            severity="danger"
                            text
                            @click="confirmDeletePersonnel(data)"
                        />
                    </div>
                </template>
            </Column>
        </DataTable>

        <!-- Formulaire d'ajout/modification -->
        <PersonnelForm
            :visible="formVisible"
            :personnel-to-edit="personnelToEdit"
            :centres="centres"
            :roles="roles"
            :centres-loading="centresLoading"
            :roles-loading="rolesLoading"
            :is-central="isCentral"
            :user-centre-id="userCentreId"
            @update:visible="formVisible = $event"
            @personnel-created="fetchPersonnels"
            @personnel-updated="fetchPersonnels"
        />

        <Dialog
            v-model:visible="deleteFormVisible"
            header="Confirmation de suppression"
            :modal="true"
            :style="{ width: '450px' }"
        >
            <div class="p-fluid">
                <p v-if="personnelToDelete">
                    Êtes-vous sûr de vouloir supprimer le personnel
                    <strong
                        >{{ personnelToDelete.user.name }}
                        {{ personnelToDelete.prenom_fr }}</strong
                    >
                    ?
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
                    @click="deletePersonnel"
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
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import PersonnelForm from './PersonnelForm.vue';

export default {
    components: {
        Button,
        InputText,
        DataTable,
        Column,
        Tag,
        Dialog,
        Toast,
        PersonnelForm,
    },
    data() {
        return {
            personnels: [],
            filters: null,
            loading: false,
            formVisible: false,
            deleteFormVisible: false,
            personnelToEdit: null,
            personnelToDelete: null,
            centres: [],
            centresLoading: false,
            roles: [],
            rolesLoading: false,
            isCentral: false,
            userCentreId: null,
        };
    },
    setup() {
        const toast = useToast();
        return { toast };
    },
    async created() {
        this.initFilters();
        await this.loadProfile();
        this.fetchPersonnels();
        this.fetchRoles();
        this.fetchCentres();
    },
    methods: {
        initFilters() {
            this.filters = {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                'user.name': {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
                prenom_fr: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
                'user.email': {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
                fonction_principale: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
                matricule: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
                cin: {
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
        async fetchPersonnels() {
            try {
                this.loading = true;
                const userId = localStorage.getItem('user_id');
                const headers = {
                    'X-User-ID': userId,
                };
                if (!this.isCentral && this.userCentreId) {
                    headers['X-Centre-ID'] = this.userCentreId;
                }
                const response = await axios.get('/personnels', { headers });
                this.personnels = response.data;
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        'Erreur lors du chargement des personnels.',
                    life: 3000,
                });
            } finally {
                this.loading = false;
            }
        },
        async loadProfile() {
            try {
                const userId = localStorage.getItem('user_id');
                const token = localStorage.getItem('token');
                if (!userId || !token) {
                    this.toast.add({
                        severity: 'warn',
                        summary: 'Attention',
                        detail: 'Utilisateur non identifié. Connectez-vous pour continuer.',
                        life: 3000,
                    });
                    this.$router.push({ name: 'login' });
                    return;
                }
                const response = await axios.get('/profile/data', {
                    headers: {
                        'X-User-ID': userId,
                    },
                });
                this.isCentral = response.data.is_centre_role === 0;
                this.userCentreId = response.data.centre_id;
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        'Impossible de charger le profil',
                    life: 3000,
                });
            }
        },
        async fetchCentres() {
            try {
                this.centresLoading = true;
                const response = await axios.get('/centres/list');
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
        openForm() {
            this.personnelToEdit = null;
            this.formVisible = true;
        },
        editPersonnel(personnel) {
            this.personnelToEdit = personnel;
            this.formVisible = true;
        },
        confirmDeletePersonnel(personnel) {
            this.personnelToDelete = personnel;
            this.deleteFormVisible = true;
        },
        async deletePersonnel() {
            if (this.personnelToDelete) {
                try {
                    await axios.delete(
                        `/personnels/${this.personnelToDelete.id}`
                    );
                    this.personnels = this.personnels.filter(
                        (p) => p.id !== this.personnelToDelete.id
                    );
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Personnel supprimé.',
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
                    this.personnelToDelete = null;
                }
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
.text-red-500 {
    color: #dc3545;
}
</style>
