<template>
    <div class="surface-ground pt-2 px-1.5 pb-1.5" style="margin-top: 0">
        <!-- Header Section with Navbar -->
        <div
            class="surface-card p-2 border-round border-1 surface-border"
            style="position: relative; top: -37px; box-shadow: none; margin-bottom: -33px;"
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
                        label="Personnels"
                        icon="pi pi-users"
                        class="p-button-text p-button-plain"
                        disabled
                    />
                </div>
                <div class="flex gap-2">
                    <Button
                        icon="pi pi-refresh"
                        class="p-button-rounded p-button-text"
                        @click="fetchUsers"
                        v-tooltip="'Rafraîchir'"
                    />
                    <div class="flex align-items-center gap-2">
                        <Button
                            icon="pi pi-file-import"
                            class="p-button-text p-button-sm p-button-rounded p-button-success import-icon"
                            @click="showImportModal = true"
                            v-tooltip="'Importer XLS'"
                        />
                        <Button
                            v-if="errors.import.length"
                            icon="pi pi-exclamation-triangle"
                            class="p-button-text p-button-sm p-button-rounded p-button-warning"
                            @click="showImportError = true"
                            v-tooltip="'Voir les erreurs d\'importation'"
                        />
                    </div>
                    <Button
                        icon="pi pi-file-export"
                        class="p-button-rounded p-button-text export-icon"
                        @click="exportXLS"
                        v-tooltip="'Exporter XLS'"
                    />
                </div>
            </div>
        </div>
        <!-- Main Content Section -->
        <div class="surface-card p-4 pt-2 border-round shadow-2 border-1 surface-border">
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
                        label="Ajouter Personnel"
                        class="p-button-success"
                        @click="openAddForm"
                    />
                </div>
            </div>
            <!-- Main DataTable -->
            <DataTable
                v-model:filters="filters"
                :value="users"
                dataKey="id"
                size="small"
                stripedRows
                paginator
                :rows="25"
                :rowsPerPageOptions="[25, 50, 100]"
                filterDisplay="menu"
                :globalFilterFields="[
                    'nom_fr', 'nom_ar', 'cin', 'matricule', 'email', 'statut',
                    'genre_fr', 'cause_inactivite_fr', 'observation_fr', 'ajouter_par'
                ]"
                :loading="loading"
                scrollable
                scrollHeight="750px"
                removableSort
                class="p-datatable-sm border-round-lg"
                :rowClass="data => pinnedRows.includes(data?.id) ? 'pinned-row' : ''"
            >
                <template #empty>
                    <div class="text-center py-4">
                        <i class="pi pi-inbox text-4xl text-400 mb-2" />
                        <p class="text-600">Aucun personnel trouvé</p>
                    </div>
                </template>
                <Column
                    field="id"
                    header="ID"
                    sortable
                    style="min-width: 5rem"
                >
                    <template #body="{ data }">
                        <div class="flex align-items-center gap-2">
                            <Button
                                icon="pi pi-pencil"
                                class="p-button-rounded p-button-text p-button-sm"
                                @click="openEditForm(data)"
                                v-tooltip="'Modifier'"
                            />
                            <span class="font-medium">{{ data.id }}</span>
                        </div>
                    </template>
                </Column>
                <Column
                    header="Photo"
                    style="min-width: 5rem"
                >
                    <template #body="{ data }">
                        <img
                            v-if="data.photo"
                            :src="getPhotoUrl(data.photo)"
                            alt="Photo"
                            class="w-4rem h-4rem border-circle"
                            style="object-fit: cover;"
                        />
                        <i v-else class="pi pi-user text-2xl text-400" />
                    </template>
                </Column>
                <Column
                    field="matricule"
                    header="Matricule"
                    sortable
                    style="min-width: 10rem"
                >
                    <template #body="{ data }">
                        <span>{{ data.matricule || '-' }}</span>
                    </template>
                </Column>
                <Column
                    field="nom_fr"
                    header="Nom"
                    sortable
                    style="min-width: 15rem"
                    :frozen="true"
                >
                    <template #body="{ data }">
                        <div class="flex flex-column text-center">
                            <span>{{ data.prenom_fr }} {{ data.nom_fr }}</span>
                            <span class="arabic-text text-primary-600">{{ data.prenom_ar || '-' }} {{ data.nom_ar || '-' }}</span>
                        </div>
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
                    field="roles"
                    header="Rôles"
                    style="min-width: 20rem"
                >
                    <template #body="{ data }">
                        <div class="flex flex-wrap gap-1">
                            <Tag
                                v-for="role in data.roles"
                                :key="role.id"
                                :value="role.name || role.name_ar || 'Inconnu'"
                                :severity="getRoleSeverity(role.name)"
                                :icon="getRoleIcon(role.name)"
                            />
                        </div>
                    </template>
                </Column>
                <Column
                    field="cin"
                    header="CIN"
                    sortable
                    style="min-width: 10rem"
                >
                    <template #body="{ data }">
                        <span>{{ data.cin || '-' }}</span>
                    </template>
                </Column>
                <Column
                    field="date_cin"
                    header="Date CIN"
                    sortable
                    style="min-width: 12rem"
                >
                    <template #body="{ data }">
                        <Tag
                            :value="formatDate(data.date_cin)"
                            icon="pi pi-calendar"
                        />
                    </template>
                </Column>
                <Column
                    field="date_recrutement"
                    header="Date Recrutement"
                    sortable
                    style="min-width: 12rem"
                >
                    <template #body="{ data }">
                        <Tag
                            :value="formatDate(data.date_recrutement)"
                            icon="pi pi-calendar"
                        />
                    </template>
                </Column>
                <Column
                    field="date_fin_service"
                    header="Date Fin Service"
                    sortable
                    style="min-width: 12rem"
                >
                    <template #body="{ data }">
                        <Tag
                            :value="formatDate(data.date_fin_service)"
                            icon="pi pi-calendar"
                        />
                    </template>
                </Column>
                <Column
                    field="genre_fr"
                    header="Genre"
                    sortable
                    style="min-width: 10rem"
                >
                    <template #body="{ data }">
                        <Tag
                            :value="data.genre_fr || '-'"
                            :severity="getGenreSeverity(data.genre_fr)"
                            :icon="getGenreIcon(data.genre_fr)"
                        />
                    </template>
                </Column>
                <Column
                    field="email"
                    header="Email"
                    sortable
                    style="min-width: 20rem"
                >
                    <template #body="{ data }">
                        <span>{{ data.email }}</span>
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
                            :severity="getStatutSeverity(data.statut)"
                            :icon="getStatutIcon(data.statut)"
                        />
                    </template>
                </Column>
                <Column
                    field="cause_inactivite_fr"
                    header="Cause Inactivité"
                    sortable
                    style="min-width: 15rem"
                >
                    <template #body="{ data }">
                        <span>{{ data.cause_inactivite_fr || '-' }}</span>
                    </template>
                </Column>
                <Column
                    field="observation_fr"
                    header="Observation"
                    sortable
                    style="min-width: 20rem"
                >
                    <template #body="{ data }">
                        <span>{{ data.observation_fr || '-' }}</span>
                    </template>
                </Column>
                <Column
                    field="ajouter_par"
                    header="Ajouté Par"
                    sortable
                    style="min-width: 15rem"
                >
                    <template #body="{ data }">
                        <span>{{ data.ajouter_par || '-' }}</span>
                    </template>
                </Column>
                <Column
                    field="added_by"
                    header="Ajouter par (Nom)"
                    sortable
                    style="min-width: 15rem"
                >
                    <template #body="{ data }">
                        <span>{{ data.added_by ? `${data.added_by.prenom_fr} ${data.added_by.nom_fr}` : '-' }}</span>
                    </template>
                </Column>
                <Column
                    field="created_at"
                    header="Créé Le"
                    sortable
                    style="min-width: 12rem"
                >
                    <template #body="{ data }">
                        <Tag
                            :value="formatDate(data.created_at)"
                            icon="pi pi-calendar"
                        />
                    </template>
                </Column>
                <Column
                    field="updated_at"
                    header="Mis à Jour Le"
                    sortable
                    style="min-width: 12rem"
                >
                    <template #body="{ data }">
                        <Tag
                            :value="formatDate(data.updated_at)"
                            icon="pi pi-calendar"
                        />
                    </template>
                </Column>
                <Column header="Actions" style="min-width: 12rem">
                    <template #body="{ data }">
                        <div class="flex gap-1">
                            <Button
                                :icon="pinnedRows.includes(data.id) ? 'pi pi-star-fill' : 'pi pi-star'"
                                :class="pinnedRows.includes(data.id) ? 'p-button-rounded p-button-text p-button-sm pinned-icon' : 'p-button-rounded p-button-text p-button-sm'"
                                @click="togglePinRow(data)"
                                v-tooltip="pinnedRows.includes(data.id) ? 'Défixer' : 'Fixer'"
                            />
                            <Button
                                icon="pi pi-eye"
                                class="p-button-rounded p-button-text p-button-sm profile-icon"
                                @click="viewUserProfile(data)"
                                v-tooltip="'Voir Profil'"
                            />
                            <Button
                                icon="pi pi-trash"
                                class="p-button-rounded p-button-text p-button-danger p-button-sm trash-icon"
                                @click="confirmDeleteUser(data)"
                                v-tooltip="'Supprimer'"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>
        <!-- Modal for Add User -->
        <PersonnelAtfpAdd
            :visible="addFormVisible"
            @update:visible="addFormVisible = $event"
            @save="onUserSaved"
            @close="closeAddForm"
        />
        <!-- Modal for Edit User -->
        <PersonnelAtfpEdit
            :visible="editFormVisible"
            :user-to-edit="userToEdit"
            :user-id="userToEdit?.id"
            @update:visible="editFormVisible = $event"
            @update="onUserUpdated"
            @close="closeEditForm"
        />
        <!-- Profile Modal -->
        <Dialog
            v-model:visible="profileVisible"
            header="Profil Utilisateur"
            :modal="true"
            :style="{ width: '1500px' }"
            :breakpoints="{ '960px': '80vw', '640px': '90vw' }"
            class="p-dialog surface-card border-round-lg shadow-4"
            :pt="{
                mask: { style: 'backdrop-filter: blur(4px)' },
                header: { class: 'surface-50 border-bottom-1 surface-border p-3' },
                content: { class: 'surface-ground p-4' },
                footer: { class: 'surface-50 border-top-1 surface-border p-3' },
            }"
        >
            <ProfilPersonnelAtfp
                :user="selectedUser"
                @update="onUserUpdated"
                @close="closeProfile"
            />
            <template #footer>
                <Button
                    label="Fermer"
                    icon="pi pi-times"
                    class="p-button-text"
                    @click="closeProfile"
                />
            </template>
        </Dialog>
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
            <div class="flex flex-column gap-3">
                <div class="flex align-items-center">
                    <i class="pi pi-exclamation-triangle text-4xl text-red-500" />
                    <span>
                        Voulez-vous vraiment supprimer l'utilisateur
                        <b>{{ userToDelete?.prenom_fr }} {{ userToDelete?.nom_fr }}</b> ?
                    </span>
                </div>
                <div class="field">
                    <label for="deleteUserPassword" class="font-semibold">Mot de passe de confirmation</label>
                    <InputText
                        id="deleteUserPassword"
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
                <Button
                    label="Annuler"
                    icon="pi pi-times"
                    class="p-button-text"
                    @click="cancelDeleteUser"
                />
                <Button
                    label="Supprimer"
                    icon="pi pi-check"
                    class="p-button-danger"
                    :disabled="loading || !deletePassword"
                    @click="deleteUser"
                />
            </template>
        </Dialog>
        <!-- Import Errors Modal -->
        <Dialog
            v-model:visible="showImportError"
            :modal="true"
            :style="{ width: '95vw', maxWidth: '1600px' }"
            :breakpoints="{ '960px': '98vw', '640px': '100vw' }"
            class="p-dialog surface-card border-round-lg shadow-4"
            :pt="{
                mask: { style: 'backdrop-filter: blur(4px)' },
                header: { class: 'surface-50 border-bottom-1 surface-border p-3' },
                content: { class: 'surface-ground p-4' },
                footer: { class: 'surface-50 border-top-1 surface-border p-3' },
            }"
        >
            <ImportErrors
                :visible="showImportError"
                :errors="errors.import"
                @update:visible="showImportError = $event"
                @close="showImportError = false"
                @update:errors="updateImportErrors"
            />
        </Dialog>
        <!-- Import Modal -->
        <Dialog
            v-model:visible="showImportModal"
            header="Importer un fichier XLS"
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
                <Button
                    label="Télécharger le modèle"
                    icon="pi pi-download"
                    class="p-button-outlined"
                    @click="downloadTemplate"
                />
                <Button
                    label="Importer les données"
                    icon="pi pi-file-import"
                    class="p-button-primary"
                    @click="$refs.fileInput.click()"
                />
                <input
                    type="file"
                    ref="fileInput"
                    style="display: none"
                    accept=".xls,.xlsx"
                    @change="triggerFileImport"
                />
            </div>
            <template #footer>
                <Button
                    label="Annuler"
                    icon="pi pi-times"
                    class="p-button-text"
                    @click="showImportModal = false"
                />
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
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import PersonnelAtfpAdd from './PersonnelDCAdd.vue';
import PersonnelAtfpEdit from './PersonnelDCEdit.vue';
import ProfilPersonnelAtfp from './ProfilPersonnelDC.vue';
import ImportErrors from '@/Components/Atfp/Utilisateurs/PersonnelsDirectionCentrale/PersonnelsDCImportError.vue';

export default {
    components: {
        Button,
        InputText,
        DataTable,
        Column,
        Tag,
        Dialog,
        Toast,
        PersonnelAtfpAdd,
        PersonnelAtfpEdit,
        ProfilPersonnelAtfp,
        ImportErrors
    },
    setup() {
        const toast = useToast();
        const deleteFormVisible = ref(false);
        const userToDelete = ref(null);
        const deletePassword = ref('');
        const errors = ref({ passwordError: '', import: [] });
        const profileVisible = ref(false);
        const selectedUser = ref(null);
        const pinnedRows = ref([]);
        const showImportModal = ref(false);
        const addFormVisible = ref(false);
        const editFormVisible = ref(false);
        return {
            toast,
            deleteFormVisible,
            userToDelete,
            deletePassword,
            errors,
            profileVisible,
            selectedUser,
            pinnedRows,
            showImportModal,
            addFormVisible,
            editFormVisible
        };
    },
    data() {
        return {
            users: [],
            filters: {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                nom_fr: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                nom_ar: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                cin: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                matricule: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                email: { value: null, matchMode: FilterMatchMode.CONTAINS },
                statut: { value: null, matchMode: FilterMatchMode.EQUALS },
                genre_fr: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                cause_inactivite_fr: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                observation_fr: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                ajouter_par: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
            },
            loading: false,
            userToEdit: null,
            showImportError: false,
        };
    },
    mounted() {
        // Charger les erreurs d'importation depuis localStorage
        const savedErrors = localStorage.getItem('importErrors');
        if (savedErrors) {
            try {
                this.errors.import = JSON.parse(savedErrors);
            } catch (e) {
                console.error('Erreur lors du parsing des erreurs sauvegardées:', e);
                this.errors.import = [];
            }
        }
        this.fetchUsers();
    },
    methods: {
        updateImportErrors(newErrors) {
            this.errors.import = newErrors;
            localStorage.setItem('importErrors', JSON.stringify(newErrors));
        },
        async fetchUsers() {
            this.loading = true;
            try {
                const { data } = await axios.get('/api/personnels_dc-with-options', {
                    headers: {
                        Authorization: `Bearer ${localStorage.getItem('token')}`
                    }
                });
                if (!data || !data.success || !Array.isArray(data.data?.personnels?.data)) {
                    throw new Error('Réponse des utilisateurs invalide');
                }
                console.log('Réponse API:', data.data.personnels.data);
                this.users = data.data.personnels.data.map(user => ({
                    id: user.id || null,
                    user_id: user.user_id || null,
                    roles: Array.isArray(user.roles) ? user.roles : [],
                    prenom_fr: user.prenom_fr || '',
                    nom_fr: user.nom_fr || '',
                    prenom_ar: user.prenom_ar || '',
                    nom_ar: user.nom_ar || '',
                    cin: user.cin || '',
                    matricule: user.matricule || '',
                    email: user.email || '',
                    statut: user.statut || 'actif',
                    genre_fr: user.genre_fr || '',
                    cause_inactivite_fr: user.cause_inactivite_fr || '',
                    observation_fr: user.observation_fr || '',
                    ajouter_par: user.ajouter_par || '',
                    added_by: user.added_by_prenom_fr && user.added_by_nom_fr ? {
                        prenom_fr: user.added_by_prenom_fr,
                        nom_fr: user.added_by_nom_fr
                    } : null,
                    created_at: user.created_at || null,
                    updated_at: user.updated_at || null,
                    photo: user.photo || '',
                    date_cin: user.date_cin || null,
                    date_recrutement: user.date_recrutement || null,
                    date_fin_service: user.date_fin_service || null,
                    date_naissance: user.date_naissance || null,
                    lieu_naissance_fr: user.lieu_naissance_fr || '',
                    lieu_naissance_ar: user.lieu_naissance_ar || '',
                    nationalite_fr: user.nationalite_fr || '',
                    nationalite_ar: user.nationalite_ar || '',
                    genre_ar: user.genre_ar || '',
                    adresse_fr: user.adresse_fr || '',
                    adresse_ar: user.adresse_ar || '',
                    gouvernorat_fr: user.gouvernorat_fr || '',
                    gouvernorat_ar: user.gouvernorat_ar || '',
                    delegation_fr: user.delegation_fr || '',
                    delegation_ar: user.delegation_ar || '',
                    telephone_1: user.telephone_1 || '',
                    telephone_2: user.telephone_2 || '',
                    observation_ar: user.observation_ar || '',
                    lieu_cin_fr: user.lieu_cin_fr || '',
                    lieu_cin_ar: user.lieu_cin_ar || '',
                    etat_civil_fr: user.etat_civil_fr || '',
                    etat_civil_ar: user.etat_civil_ar || '',
                    qualite_fr: user.qualite_fr || '',
                    qualite_ar: user.qualite_ar || '',
                    etablissement_origine_fr: user.etablissement_origine_fr || '',
                    etablissement_origine_ar: user.etablissement_origine_ar || '',
                    mission_fr: user.mission_fr || '',
                    mission_ar: user.mission_ar || '',
                    cause_inactivite_ar: user.cause_inactivite_ar || '',
                }));
                this.users = this.sortUsersByPinned(this.users);
            } catch (error) {
                console.error('Fetch users error:', error);
                let errorMessage = 'Erreur lors du chargement des utilisateurs.';
                if (error.response) {
                    errorMessage = error.response.data?.message || errorMessage;
                    if (error.response.status === 401) {
                        this.$router.push({ name: 'login' });
                    }
                }
                this.showError(errorMessage);
                this.users = [];
            } finally {
                this.loading = false;
            }
        },
        sortUsersByPinned(users) {
            if (!Array.isArray(users)) return [];
            const pinned = users.filter(user => user && this.pinnedRows.includes(user.id));
            const unpinned = users.filter(user => user && !this.pinnedRows.includes(user.id));
            const sortedPinned = this.pinnedRows
                .map(id => pinned.find(user => user.id === id))
                .filter(user => user);
            return [...sortedPinned, ...unpinned];
        },
        togglePinRow(user) {
            if (!user?.id) {
                this.showWarn('Utilisateur invalide.');
                return;
            }
            if (this.pinnedRows.includes(user.id)) {
                this.pinnedRows = this.pinnedRows.filter(id => id !== user.id);
            } else {
                this.pinnedRows.push(user.id);
            }
            this.users = this.sortUsersByPinned(this.users);
            this.showSuccess(this.pinnedRows.includes(user.id) ? 'Ligne fixée en haut.' : 'Ligne défixée.');
        },
        openAddForm() {
            try {
                this.userToEdit = null;
                this.addFormVisible = true;
            } catch (error) {
                console.error('Error opening add form:', error);
                this.showError('Erreur lors de l\'ouverture du formulaire d\'ajout.');
            }
        },
        openEditForm(user) {
            if (!user?.id) {
                this.showWarn('Utilisateur invalide.');
                return;
            }
            try {
                this.userToEdit = {
                    ...user,
                    roles: Array.isArray(user.roles) ? user.roles.map(role => role.id) : []
                };
                this.editFormVisible = true;
            } catch (error) {
                console.error('Error opening edit form:', error);
                this.showError('Erreur lors de l\'ouverture du formulaire de modification.');
            }
        },
        viewUserProfile(user) {
            if (!user?.id) {
                this.showWarn('Sélectionnez un utilisateur valide.');
                return;
            }
            try {
                this.selectedUser = { ...user };
                this.profileVisible = true;
            } catch (error) {
                console.error('Error viewing user profile:', error);
                this.showError('Erreur lors de l\'affichage du profil.');
            }
        },
        closeProfile() {
            this.profileVisible = false;
            this.selectedUser = null;
        },
        onUserSaved(user) {
            try {
                if (!user?.id) throw new Error('Utilisateur invalide sauvegardé.');
                this.users.unshift(user);
                this.users = this.sortUsersByPinned(this.users);
                this.showSuccess('Utilisateur créé avec succès.');
                this.closeAddForm();
            } catch (error) {
                console.error('Error on user saved:', error);
                this.showError('Erreur lors de la sauvegarde de l\'utilisateur.');
            }
        },
        onUserUpdated(user) {
            try {
                if (!user?.id) throw new Error('Utilisateur invalide mis à jour.');
                const index = this.users.findIndex(u => u.id === user.id);
                if (index !== -1) {
                    this.users.splice(index, 1, {
                        ...user,
                        roles: Array.isArray(user.roles) ? user.roles : []
                    });
                    this.users = this.sortUsersByPinned(this.users);
                }
                this.showSuccess('Utilisateur mis à jour avec succès.');
                this.closeEditForm();
                this.closeProfile();
            } catch (error) {
                console.error('Error on user updated:', error);
                this.showError('Erreur lors de la mise à jour de l\'utilisateur.');
            }
        },
        confirmDeleteUser(user) {
            if (!user?.id) {
                this.showWarn('Sélectionnez un utilisateur valide.');
                return;
            }
            try {
                this.userToDelete = { ...user };
                this.deletePassword = '';
                this.errors.passwordError = '';
                this.deleteFormVisible = true;
            } catch (error) {
                console.error('Error confirming delete user:', error);
                this.showError('Erreur lors de la confirmation de la suppression.');
            }
        },
        cancelDeleteUser() {
            this.deleteFormVisible = false;
            this.userToDelete = null;
            this.deletePassword = '';
            this.errors.passwordError = '';
        },
        async deleteUser() {
            if (!this.userToDelete?.id) {
                this.showError('Aucun utilisateur sélectionné.');
                return;
            }
            this.loading = true;
            try {
                const cleanedPassword = this.deletePassword.trim();
                const response = await axios.delete(`/api/personnels_dc/${this.userToDelete.id}`, {
                    data: { password: cleanedPassword },
                    headers: {
                        Authorization: `Bearer ${localStorage.getItem('token')}`
                    }
                });
                this.users = this.users.filter(u => u.id !== this.userToDelete.id);
                this.pinnedRows = this.pinnedRows.filter(id => id !== this.userToDelete.id);
                this.showSuccess(response.data.message || 'Utilisateur supprimé avec succès.');
                this.cancelDeleteUser();
            } catch (error) {
                console.error('Delete error:', error);
                const errorMessage = error.response?.data?.message || error.response?.data?.error || 'Erreur lors de la suppression. Vérifiez le mot de passe.';
                this.errors.passwordError = errorMessage;
                this.showError(errorMessage);
            } finally {
                this.loading = false;
            }
        },
        closeAddForm() {
            this.addFormVisible = false;
        },
        closeEditForm() {
            this.editFormVisible = false;
            this.userToEdit = null;
        },
        clearFilter() {
            this.filters = {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                nom_fr: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                nom_ar: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                cin: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                matricule: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                email: { value: null, matchMode: FilterMatchMode.CONTAINS },
                statut: { value: null, matchMode: FilterMatchMode.EQUALS },
                genre_fr: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                cause_inactivite_fr: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                observation_fr: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                ajouter_par: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
            };
        },
        async triggerFileImport(event) {
            const file = event.target.files[0];
            if (!file) {
                this.showWarn('Aucun fichier sélectionné.');
                return;
            }
            const formData = new FormData();
            formData.append('file', file);
            try {
                const response = await axios.post('/api/personnels_dc/importxls', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                        Authorization: `Bearer ${localStorage.getItem('token')}`
                    },
                });
                this.errors.import = Array.isArray(response.data.error_lines) ? response.data.error_lines : [];
                localStorage.setItem('importErrors', JSON.stringify(this.errors.import));
                this.showSuccess(`Importation terminée. ${response.data.success_count || 0} lignes importées, ${response.data.error_count || 0} lignes en échec.`);
                this.showImportError = this.errors.import.length > 0;
                await this.fetchUsers();
                this.showImportModal = false;
            } catch (error) {
                console.error('Import XLS error:', error);
                const errorMessage = error.response?.data?.message || "Erreur lors de l'importation.";
                this.showError(errorMessage);
                this.errors.import = [];
                localStorage.setItem('importErrors', JSON.stringify([]));
            } finally {
                event.target.value = '';
            }
        },
        async downloadTemplate() {
            try {
                const response = await axios.get('/api/personnels_dc/download-template', {
                    responseType: 'blob',
                    headers: {
                        Authorization: `Bearer ${localStorage.getItem('token')}`
                    }
                });
                const blob = new Blob([response.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'PersonnelATFP.xlsx';
                link.click();
                URL.revokeObjectURL(link.href);
                this.showSuccess('Modèle téléchargé avec succès.');
                this.showImportModal = false;
            } catch (error) {
                console.error('Erreur lors du téléchargement du modèle:', error);
                let errorMessage = 'Erreur lors du téléchargement du modèle.';
                if (error.response) {
                    if (error.response.status === 404) {
                        errorMessage = 'L\'endpoint de téléchargement du modèle est introuvable.';
                    } else if (error.response.status === 401) {
                        errorMessage = 'Authentification requise. Veuillez vous reconnecter.';
                        this.$router.push({ name: 'login' });
                    } else {
                        errorMessage = error.response.data?.message || errorMessage;
                    }
                }
                this.showError(errorMessage);
            }
        },
        async exportXLS() {
            try {
                const response = await axios.get('/api/personnels_dc/exportxls', {
                    responseType: 'blob',
                    headers: {
                        Authorization: `Bearer ${localStorage.getItem('token')}`
                    }
                });
                const blob = new Blob([response.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'personnels-atfp.xlsx';
                link.click();
                URL.revokeObjectURL(link.href);
                this.showSuccess('Exportation réussie.');
            } catch (error) {
                console.error('Export XLS error:', error);
                let errorMessage = "Erreur lors de l'export XLS.";
                if (error.response) {
                    if (error.response.status === 401) {
                        errorMessage = 'Authentification requise. Veuillez vous reconnecter.';
                        this.$router.push({ name: 'login' });
                    } else {
                        errorMessage = error.response.data?.message || errorMessage;
                    }
                }
                this.showError(errorMessage);
            }
        },
        getPhotoUrl(photo) {
            if (!photo) return '';
            if (photo.startsWith('data:image/') || photo.startsWith('blob:')) {
                return photo;
            }
            return `${window.location.origin}/storage/${photo}`;
        },
        formatDate(date) {
            if (!date) return '-';
            try {
                const d = new Date(date);
                if (isNaN(d.getTime())) return '-';
                return d.toLocaleDateString('fr-FR');
            } catch (error) {
                console.error('Error formatting date:', error);
                return '-';
            }
        },
        normalizeStatut(statut) {
            if (!statut) {
                return 'actif';
            }
            const statutMap = {
                'Actif': 'actif',
                'Inactif': 'inactif',
                'actif': 'actif',
                'inactif': 'inactif',
            };
            return statutMap[statut] || statut.toLowerCase();
        },
        formatStatut(statut) {
            const normalizedStatut = this.normalizeStatut(statut);
            const statusLabels = {
                actif: 'Actif',
                inactif: 'Inactif',
            };
            return statusLabels[normalizedStatut] || statut || 'Actif';
        },
        getStatutSeverity(statut) {
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
        getRoleSeverity(role) {
            const roleSeverity = {
                admin: 'primary',
                manager: 'success',
                user: 'info',
                super_admin: 'warning',
            };
            return roleSeverity[role?.toLowerCase()] || 'secondary';
        },
        getRoleIcon(role) {
            const roleIcons = {
                admin: 'pi pi-user-plus',
                manager: 'pi pi-briefcase',
                user: 'pi pi-user',
                super_admin: 'pi pi-crown',
            };
            return roleIcons[role?.toLowerCase()] || 'pi pi-info';
        },
        getGenreSeverity(genre) {
            const genreSeverity = {
                'Homme': 'info',
                'Femme': 'success'
            };
            return genreSeverity[genre] || 'secondary';
        },
        getGenreIcon(genre) {
            const genreIcons = {
                'Homme': 'pi pi-male',
                'Femme': 'pi pi-female'
            };
            return genreIcons[genre] || 'pi pi-info';
        },
        showSuccess(message) {
            this.toast.add({ severity: 'success', summary: 'Succès', detail: message, life: 3000 });
        },
        showError(message) {
            this.toast.add({ severity: 'error', summary: 'Erreur', detail: message, life: 5000 });
        },
        showWarn(message) {
            this.toast.add({ severity: 'warn', summary: 'Attention', detail: message, life: 3000 });
        }
    },
};
</script>

<style>
@font-face {
    font-family: 'Montserrat-Arabic-Regular';
    src: url('/fonts/Montserrat-Arabic-Regular.otf') format('opentype');
    font-weight: normal;
    font-style: normal;
    font-display: swap;
}
.arabic-text {
    font-family: 'Montserrat-Arabic-Regular', sans-serif;
    direction: rtl;
    text-align: center;
}
.p-dialog .p-dialog-content {
    padding: 0;
}
.p-datatable .p-datatable-thead > tr > th {
    background: var(--surface-100);
    font-weight: 600;
    padding: 0.75rem;
    color: var(--text-color);
    border-bottom: 1px solid var(--surface-border);
}
.p-datatable .p-datatable-tbody > tr {
    transition: background-color 0.2s;
}
.p-datatable .p-datatable-tbody > tr:hover {
    background: var(--surface-50) !important;
}
.field {
    margin-bottom: 1rem;
}
.p-error {
    font-size: 0.75rem;
    color: var(--red-500);
    display: flex;
    align-items: center;
}
@media (max-width: 960px) {
    .p-dialog {
        width: 98vw !important;
    }
}
@media (max-width: 640px) {
    .p-dialog {
        width: 100vw !important;
    }
    .p-dialog .p-dialog-content {
        padding: 0.5rem;
    }
    .field {
        margin-bottom: 0.75rem;
    }
}
</style>
