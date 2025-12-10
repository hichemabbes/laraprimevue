<template>
    <div
        class="profile-container surface-card p-4 border-round shadow-2 border-1 surface-border"
    >
        <!-- DataTable -->
        <div class="mt-5">
            <div class="flex justify-content-between align-items-center mb-3">
                <div class="flex gap-2 align-items-center">
                    <Button
                        label="Ajouter Utilisateur"
                        icon="pi pi-plus"
                        severity="primary"
                        @click="openForm"
                    />
                    <Button
                        label="Utilisateurs Supprimés"
                        icon="pi pi-trash"
                        severity="secondary"
                        @click="openTrashedDialog"
                    />
                </div>
                <div class="flex gap-2 align-items-center">
                    <Dropdown
                        v-model="activeQualiteFilter"
                        :options="qualiteFilters"
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Filtrer par qualité"
                        class="w-15rem"
                        @change="setQualiteFilter"
                    />
                    <Button
                        icon="pi pi-filter-slash"
                        severity="secondary"
                        text
                        v-tooltip="'Réinitialiser les filtres'"
                        @click="clearFilter"
                    />
                    <span class="p-input-icon-left search-field">
                        <i class="pi pi-search" />
                        <InputText
                            v-model="filters.global.value"
                            placeholder="Rechercher..."
                            @input="debouncedFilter"
                        />
                    </span>
                </div>
            </div>
            <div class="flex justify-content-end gap-2 mb-3">
                <Button
                    label="Importer"
                    icon="pi pi-upload"
                    severity="info"
                    text
                    @click="importXLS"
                />
                <Button
                    label="Exporter"
                    icon="pi pi-download"
                    severity="info"
                    text
                    @click="exportXLS"
                />
            </div>
            <DataTable
                :value="filteredUsers"
                dataKey="id"
                :filters="filters"
                filterDisplay="row"
                paginator
                :rows="10"
                :rowsPerPageOptions="[10, 20, 50]"
                :loading="loading"
                scrollable
                scrollHeight="400px"
                :pt="{ table: { style: 'min-width: 60rem' } }"
            >
                <template #empty>
                    <div class="text-center py-4">
                        <i class="pi pi-inbox text-4xl text-400 mb-2" />
                        <p class="text-600">Aucun utilisateur trouvé.</p>
                    </div>
                </template>
                <Column
                    field="name_fr"
                    header="Nom"
                    filterField="name_fr"
                    :showFilterMenu="false"
                    style="min-width: 15rem"
                >
                    <template #body="{ data }">
                        <span>{{ data.name_fr || 'N/A' }}</span>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter"
                            placeholder="Filtrer par nom"
                            @input="filterCallback()"
                        />
                    </template>
                </Column>
                <Column
                    field="name_ar"
                    header="Nom (AR)"
                    filterField="name_ar"
                    :showFilterMenu="false"
                    style="min-width: 15rem"
                >
                    <template #body="{ data }">
                        <span class="font-arabic">{{
                            data.name_ar || 'N/A'
                        }}</span>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter font-arabic"
                            placeholder="البحث بالاسم"
                            @input="filterCallback()"
                        />
                    </template>
                </Column>
                <Column
                    field="email"
                    header="Email"
                    filterField="email"
                    :showFilterMenu="false"
                    style="min-width: 15rem"
                >
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter"
                            placeholder="Filtrer par email"
                            @input="filterCallback()"
                        />
                    </template>
                </Column>
                <Column
                    field="qualite_fr"
                    header="Qualité"
                    filterField="qualite_fr"
                    :showFilterMenu="false"
                    style="min-width: 15rem"
                >
                    <template #body="{ data }">
                        <span>{{ data.qualite_fr || 'N/A' }}</span>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter"
                            placeholder="Filtrer par qualité"
                            @input="filterCallback()"
                        />
                    </template>
                </Column>
                <Column
                    header="Centres"
                    filterField="centres.nom_fr"
                    :showFilterMenu="false"
                    style="min-width: 15rem"
                >
                    <template #body="{ data }">
                        <Tag
                            :value="formatCentres(data.centres)"
                            :severity="getCentreSeverity(data.centres)"
                            :icon="getCentreIcon(data.centres)"
                        />
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter"
                            placeholder="Filtrer par centre"
                            @input="filterCallback()"
                        />
                    </template>
                </Column>
                <Column
                    field="etablissement_origine_fr"
                    header="Établissement"
                    filterField="etablissement_origine_fr"
                    :showFilterMenu="false"
                    style="min-width: 15rem"
                >
                    <template #body="{ data }">
                        <span>{{
                            data.etablissement_origine_fr || 'N/A'
                        }}</span>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter"
                            placeholder="Filtrer par établissement"
                            @input="filterCallback()"
                        />
                    </template>
                </Column>
                <Column
                    field="matricule"
                    header="Matricule"
                    filterField="matricule"
                    :showFilterMenu="false"
                    style="min-width: 12rem"
                >
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter"
                            placeholder="Filtrer par matricule"
                            @input="filterCallback()"
                        />
                    </template>
                </Column>
                <Column header="Actions" style="min-width: 10rem">
                    <template #body="{ data }">
                        <div class="flex gap-1">
                            <Button
                                icon="pi pi-eye"
                                class="p-button-rounded p-button-text p-button-sm"
                                @click="viewProfile(data)"
                                v-tooltip="'Voir Profil'"
                            />
                            <Button
                                icon="pi pi-pencil"
                                class="p-button-rounded p-button-text p-button-sm"
                                @click="editUser(data)"
                                v-tooltip="'Modifier'"
                            />
                            <Button
                                icon="pi pi-trash"
                                class="p-button-rounded p-button-text p-button-danger p-button-sm"
                                @click="confirmDeleteUser(data)"
                                v-tooltip="'Supprimer'"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- User Form Dialog -->
        <UserForm
            v-if="formVisible"
            :visible="formVisible"
            :user="newUser"
            :roles-loading="rolesLoading"
            :centres-loading="centresLoading"
            :errors="formErrors"
            :is-centre-role="isCentreRole"
            @update:visible="formVisible = $event"
            @update:user="newUser = $event"
            @update:errors="formErrors = $event"
            @update:is-centre-role="isCentreRole = $event"
            @save="saveUser"
            @close="closeForm"
        />

        <!-- Profile Dialog -->
        <Dialog
            v-model:visible="profileVisible"
            header="Profil de l'utilisateur"
            :modal="true"
            :style="{ width: '90vw', maxWidth: '1200px' }"
            :breakpoints="{ '960px': '95vw', '640px': '98vw' }"
            :pt="{
                root: 'border-none',
                mask: { style: 'backdrop-filter: blur(2px)' },
                header: {
                    class: 'surface-card border-bottom-1 surface-border p-3',
                },
                content: {
                    class: 'surface-card p-0',
                    style: 'overflow-x: hidden',
                },
                footer: {
                    class: 'surface-card border-top-1 surface-border p-3',
                },
            }"
        >
            <UserProfil :user="selectedUser || {}" />
            <template #footer>
                <Button
                    label="Fermer"
                    icon="pi pi-times"
                    class="p-button-text"
                    @click="profileVisible = false"
                />
                <Button
                    label="Modifier"
                    icon="pi pi-pencil"
                    class="p-button-primary"
                    @click="editUser(selectedUser)"
                    :disabled="!selectedUser"
                />
            </template>
        </Dialog>

        <!-- Photo Upload Dialog -->
        <Dialog
            v-model:visible="showPhotoDialog"
            header="Changer la photo de l'utilisateur"
            :modal="true"
            :style="{ width: '30rem' }"
            :pt="{
                root: 'border-round-xl shadow-5',
                mask: { style: 'backdrop-filter: blur(4px)' },
                header: {
                    class: 'surface-50 border-bottom-1 surface-border p-4',
                },
                content: { class: 'surface-ground p-4' },
                footer: { class: 'surface-50 border-top-1 surface-border p-4' },
            }"
        >
            <div class="flex flex-column align-items-center gap-3">
                <div
                    v-if="
                        previewImage ||
                        (selectedUser?.photo &&
                            isValidPhoto(selectedUser.photo))
                    "
                    class="cropper-container"
                >
                    <Cropper
                        :src="previewImage || getPhotoUrl(selectedUser?.photo)"
                        :stencil-props="{
                            aspectRatio: null,
                            movable: true,
                            resizable: true,
                            resizeHandlers: {
                                enabled: true,
                                positions: ['bottom-right'],
                            },
                            style: {
                                border: '2px solid #3b82f6',
                                borderRadius: '0',
                                background: 'transparent',
                            },
                            initialSize: { width: 200, height: 200 },
                        }"
                        :canvas="{ minWidth: 100, minHeight: 100 }"
                        @change="onCropChange"
                        @ready="onCropperReady"
                    />
                </div>
                <div v-else class="flex flex-column align-items-center gap-2">
                    <i class="pi pi-image text-5xl text-surface-300"></i>
                    <small class="text-error" v-if="imageError">{{
                        imageError
                    }}</small>
                </div>
                <FileUpload
                    mode="basic"
                    accept="image/*"
                    :maxFileSize="2000000"
                    chooseLabel="Choisir une Image"
                    @select="onPhotoSelect"
                    class="w-full"
                />
                <small class="text-500">Taille max: 2MB (JPG, PNG)</small>
            </div>
            <template #footer>
                <Button
                    label="Annuler"
                    icon="pi pi-times"
                    severity="secondary"
                    raised
                    @click="cancelPhotoUpload"
                />
                <Button
                    label="Enregistrer"
                    icon="pi pi-check"
                    severity="primary"
                    raised
                    :disabled="
                        !croppedImage && !selectedFile && !selectedUser?.photo
                    "
                    :loading="uploadingPhoto"
                    @click="uploadPhoto"
                />
            </template>
        </Dialog>

        <!-- Delete Confirmation Dialog -->
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
                        Voulez-vous vraiment supprimer l'utilisateur
                        <b>{{ userToDelete?.name_fr || 'N/A' }}</b> ?
                    </span>
                </div>
                <div class="field">
                    <label for="deleteUserPassword" class="font-semibold"
                        >Mot de passe de confirmation</label
                    >
                    <Password
                        id="deleteUserPassword"
                        v-model="deletePassword"
                        class="w-full"
                        :class="{ 'p-invalid': passwordError }"
                        placeholder="Entrez le mot de passe"
                        :feedback="false"
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

        <!-- Import Errors Dialog -->
        <Dialog
            v-model:visible="showImportError"
            header="Erreurs d'importation"
            :modal="true"
            :style="{ width: '600px' }"
            :breakpoints="{ '960px': '75vw', '640px': '90vw' }"
            :pt="{
                root: 'border-none',
                mask: { style: 'backdrop-filter: blur(2px)' },
                header: { class: 'surface-50 border-bottom-1 surface-border' },
                content: { class: 'surface-50 py-0' },
                footer: { class: 'surface-50 border-top-1 surface-border' },
            }"
        >
            <DataTable :value="errors" size="small" class="p-datatable-sm">
                <Column field="line" header="Ligne" />
                <Column field="error" header="Erreur" />
            </DataTable>
            <template #footer>
                <Button
                    label="Fermer"
                    icon="pi pi-times"
                    class="p-button-text"
                    @click="showImportError = false"
                />
            </template>
        </Dialog>

        <!-- Trashed Users Dialog -->
        <Dialog
            v-model:visible="trashedVisible"
            header="Utilisateurs supprimés"
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
                :value="trashedUsers"
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
                            Aucun utilisateur supprimé trouvé.
                        </p>
                    </div>
                </template>
                <Column field="name_fr" header="Nom" style="min-width: 15rem">
                    <template #body="{ data }">
                        <span>{{ data.name_fr || 'N/A' }}</span>
                    </template>
                </Column>
                <Column field="email" header="Email" style="min-width: 20rem" />
                <Column header="Rôles" style="min-width: 15rem">
                    <template #body="{ data }">
                        <Tag
                            :value="formatRoles(data.roles)"
                            :severity="
                                data.roles.length > 0 ? 'info' : 'secondary'
                            "
                            :icon="
                                data.roles.length > 0
                                    ? 'pi pi-users'
                                    : 'pi pi-times'
                            "
                        />
                    </template>
                </Column>
                <Column header="Centres" style="min-width: 15rem">
                    <template #body="{ data }">
                        <Tag
                            :value="formatCentres(data.centres)"
                            :severity="getCentreSeverity(data.centres)"
                            :icon="getCentreIcon(data.centres)"
                        />
                    </template>
                </Column>
                <Column header="Actions" style="min-width: 8rem">
                    <template #body="{ data }">
                        <Button
                            icon="pi pi-undo"
                            severity="success"
                            text
                            @click="restoreUser(data)"
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

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import { useRouter } from 'vue-router';
import { useToast } from 'primevue/usetoast';
import { FilterMatchMode, FilterOperator } from 'primevue/api';
import { debounce } from 'lodash';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import Dropdown from 'primevue/dropdown';
import FileUpload from 'primevue/fileupload';
import { Cropper } from 'vue-advanced-cropper';
import 'vue-advanced-cropper/dist/style.css';
import ExcelJS from 'exceljs';
import UserForm from '@/Components/Atfp/Utilisateurs/PersonnelsCentres/PersonnelCentreAdd.vue';
import UserProfil from '@/Components/Atfp/Utilisateurs/PersonnelsCentres/ProfilPersonnelCentre.vue';
import axios from '@/axios.js';

const router = useRouter();
const toast = useToast();

// State
const users = ref([]);
const trashedUsers = ref([]);
const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    name_fr: {
        operator: FilterOperator.AND,
        constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }],
    },
    name_ar: {
        operator: FilterOperator.AND,
        constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }],
    },
    email: {
        operator: FilterOperator.AND,
        constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }],
    },
    qualite_fr: {
        operator: FilterOperator.AND,
        constraints: [{ value: null, matchMode: FilterMatchMode.EQUALS }],
    },
    'centres.nom_fr': {
        operator: FilterOperator.AND,
        constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }],
    },
    etablissement_origine_fr: {
        operator: FilterOperator.AND,
        constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }],
    },
    matricule: {
        operator: FilterOperator.AND,
        constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }],
    },
});
const loading = ref(false);
const trashedLoading = ref(false);
const formVisible = ref(false);
const deleteFormVisible = ref(false);
const trashedVisible = ref(false);
const showImportError = ref(false);
const centresLoading = ref(false);
const rolesLoading = ref(false);
const errors = ref([]);
const formErrors = ref({});
const isCentreRole = ref(false);
const userToDelete = ref(null);
const deletePassword = ref('');
const passwordError = ref('');
const activeQualiteFilter = ref('Tous');
const qualiteFilters = ref([
    { label: 'Tous', value: 'Tous' },
    {
        label: 'Personnel (ATFP) Direction Centrale',
        value: 'Personnel (ATFP) Direction Centrale',
    },
    { label: 'Personnel (ATFP) Centres', value: 'Personnel (ATFP) Centres' },
    {
        label: 'Personnel (Externe) Direction Centrale',
        value: 'Personnel (Externe) Direction Centrale',
    },
    {
        label: 'Personnel (Externe) Centres',
        value: 'Personnel (Externe) Centres',
    },
]);
const profileVisible = ref(false);
const selectedUser = ref(null);
const showPhotoDialog = ref(false);
const selectedFile = ref(null);
const previewImage = ref(null);
const croppedImage = ref(null);
const imageError = ref(null);
const uploadingPhoto = ref(false);
const newUser = ref({
    id: null,
    nom_fr: '',
    prenom_fr: '',
    nom_ar: '',
    prenom_ar: '',
    matricule: '',
    num_extrait_naissance: '',
    cin: '',
    date_cin: null,
    lieu_cin_fr: '',
    lieu_cin_ar: '',
    date_naissance: null,
    lieu_naissance_fr: '',
    lieu_naissance_ar: '',
    nationalite_fr: '',
    nationalite_ar: '',
    genre_fr: 'Homme',
    genre_ar: '',
    qualite_fr: 'Personnel (ATFP) Direction Centrale',
    qualite_ar: '',
    etablissement_origine_fr: '',
    etablissement_origine_ar: '',
    mission_fr: '',
    mission_ar: '',
    observation_fr: '',
    observation_ar: '',
    email: '',
    password: '',
    password_confirmation: '',
    statut: 'Actif',
    roles: [],
    centres: [],
    photo: null,
});

// Computed
const filteredUsers = computed(() => {
    if (!users.value) return [];
    return users.value.filter((user) => {
        if (activeQualiteFilter.value === 'Tous') return true;
        return user.qualite_fr === activeQualiteFilter.value;
    });
});

const availableCentres = computed(() => {
    const allCentres = users.value.flatMap((user) => user.centres);
    return [
        ...new Map(allCentres.map((c) => [c.centre_id, c.nom_fr])).values(),
    ];
});

// Watch
watch(activeQualiteFilter, () => {
    fetchUsers();
});

// Lifecycle
onMounted(() => {
    fetchUsers().catch((error) => {
        console.error('Erreur lors du chargement initial:', error);
        router.push('/login');
    });
});

// Methods
const debouncedFilter = debounce(() => {
    fetchUsers();
}, 300);

function setQualiteFilter() {
    fetchUsers();
}

function clearFilter() {
    filters.value = {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        name_fr: {
            operator: FilterOperator.AND,
            constraints: [
                { value: null, matchMode: FilterMatchMode.STARTS_WITH },
            ],
        },
        name_ar: {
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
        qualite_fr: {
            operator: FilterOperator.AND,
            constraints: [{ value: null, matchMode: FilterMatchMode.EQUALS }],
        },
        'centres.nom_fr': {
            operator: FilterOperator.AND,
            constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }],
        },
        etablissement_origine_fr: {
            operator: FilterOperator.AND,
            constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }],
        },
        matricule: {
            operator: FilterOperator.AND,
            constraints: [
                { value: null, matchMode: FilterMatchMode.STARTS_WITH },
            ],
        },
    };
    activeQualiteFilter.value = 'Tous';
    fetchUsers();
}

function formatCentres(centres) {
    return centres.length > 0
        ? centres.map((c) => c.nom_fr || 'N/A').join(', ')
        : 'Non affecté';
}

function getCentreSeverity(centres) {
    return centres.length > 0 ? 'success' : 'warning';
}

function getCentreIcon(centres) {
    return centres.length > 0 ? 'pi pi-map-marker' : 'pi pi-building';
}

function formatRoles(roles) {
    return roles.length > 0
        ? roles.map((r) => r.name || 'N/A').join(', ')
        : 'Aucun rôle';
}

function isValidPhoto(photo) {
    if (!photo) return false;
    if (photo.startsWith('data:image/')) return true;
    const validFormats = ['.jpg', '.jpeg', '.png', '.gif', '.svg'];
    return validFormats.some((format) => photo.toLowerCase().endsWith(format));
}

function getPhotoUrl(photo) {
    if (!photo) return '/default-avatar.png';
    if (photo.startsWith('data:image/')) return photo;
    if (photo.startsWith('/storage/')) {
        return `${window.location.origin}${photo}`;
    }
    return `${window.location.origin}/storage/photos/${photo}`;
}

function handlePhotoError() {
    toast.add({
        severity: 'warn',
        summary: 'Avertissement',
        detail: "La photo n'a pas pu être chargée. Une image par défaut est affichée.",
        life: 3000,
    });
}

function onPhotoSelect(event) {
    try {
        imageError.value = null;
        const file = event.files[0];
        if (!file) {
            throw new Error('Aucun fichier sélectionné');
        }
        if (file.size > 2000000) {
            imageError.value = "La taille de l'image ne doit pas dépasser 2 Mo";
            toast.add({
                severity: 'error',
                summary: 'Erreur',
                detail: imageError.value,
                life: 3000,
            });
            return;
        }
        if (!file.type.match('image/(jpg|jpeg|png)')) {
            imageError.value =
                'Veuillez sélectionner une image valide (JPG, PNG)';
            toast.add({
                severity: 'error',
                summary: 'Erreur',
                detail: imageError.value,
                life: 3000,
            });
            return;
        }
        selectedFile.value = file;
        previewImage.value = URL.createObjectURL(file);
    } catch (err) {
        imageError.value =
            err.message || "Erreur lors de la sélection de l'image";
        previewImage.value = null;
        selectedFile.value = null;
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: imageError.value,
            life: 3000,
        });
    }
}

function onCropperReady() {
    console.log('Cropper prêt : le composant est chargé et rendu');
}

function onCropChange({ coordinates, canvas }) {
    try {
        croppedImage.value = canvas.toDataURL('image/png');
    } catch (err) {
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: "Erreur lors du recadrage de l'image",
            life: 3000,
        });
    }
}

async function uploadPhoto() {
    try {
        if (!selectedUser.value) {
            throw new Error('Aucun utilisateur sélectionné');
        }
        uploadingPhoto.value = true;
        const base64Image =
            croppedImage.value ||
            (selectedFile.value
                ? await toBase64(selectedFile.value)
                : selectedUser.value.photo);

        if (!base64Image) {
            throw new Error('Aucune image sélectionnée ou existante');
        }

        const response = await axios.post(
            `/users/${selectedUser.value.id}/photo`,
            {
                photo: base64Image,
            }
        );

        selectedUser.value.photo =
            response.data.photo || selectedUser.value.photo;
        const userIndex = users.value.findIndex(
            (u) => u.id === selectedUser.value.id
        );
        if (userIndex !== -1) {
            users.value[userIndex].photo = response.data.photo;
        }
        toast.add({
            severity: 'success',
            summary: 'Succès',
            detail: 'Photo mise à jour',
            life: 3000,
        });
        showPhotoDialog.value = false;
    } catch (err) {
        console.error('Erreur lors de uploadPhoto:', err);
        let errorMessage = "Erreur lors de l'upload de la photo";
        if (err.response?.status === 422) {
            const errors = err.response.data.errors?.photo || [
                'Erreur de validation',
            ];
            errorMessage = errors.join(', ');
        } else if (err.response?.status === 404) {
            errorMessage = 'Utilisateur non trouvé';
        } else if (err.response?.status === 500) {
            errorMessage = err.response.data.error || 'Erreur serveur interne';
        } else if (err.message) {
            errorMessage = err.message;
        }
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: errorMessage,
            life: 3000,
        });
    } finally {
        uploadingPhoto.value = false;
        selectedFile.value = null;
        previewImage.value = null;
        croppedImage.value = null;
        imageError.value = null;
    }
}

function toBase64(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => resolve(reader.result);
        reader.onerror = (error) => reject(error);
        reader.readAsDataURL(file);
    });
}

function cancelPhotoUpload() {
    showPhotoDialog.value = false;
    selectedFile.value = null;
    previewImage.value = null;
    croppedImage.value = null;
    imageError.value = null;
}

async function fetchUsers() {
    loading.value = true;
    try {
        const response = await axios.get('/users', {
            params: {
                search: filters.value.global.value || undefined,
                qualite_fr:
                    activeQualiteFilter.value !== 'Tous'
                        ? activeQualiteFilter.value
                        : undefined,
            },
        });
        const data = response.data.data || [];
        if (data.length === 0) {
            toast.add({
                severity: 'warn',
                summary: 'Aucun utilisateur',
                detail: 'Aucun utilisateur trouvé.',
                life: 3000,
            });
        }
        users.value = data.map((user) => ({
            ...user,
            user_id: user.id,
            name_fr: trim(`${user.nom_fr || ''} ${user.prenom_fr || ''}`),
            name_ar: trim(`${user.nom_ar || ''} ${user.prenom_ar || ''}`),
            qualite_fr:
                user.qualite_fr || 'Personnel (ATFP) Direction Centrale',
            centres: Array.isArray(user.centres) ? user.centres : [],
            roles: Array.isArray(user.roles) ? user.roles : [],
            photo: user.photo || null,
            etablissement_origine_fr: user.etablissement_origine_fr || '',
        }));
    } catch (error) {
        console.error(
            'fetchUsers error:',
            error.response ? error.response.data : error
        );
        let message = 'Erreur lors du chargement des utilisateurs.';
        if (error.response?.status === 401) {
            message = 'Session expirée. Veuillez vous reconnecter.';
            router.push('/login');
        }
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: message,
            life: 5000,
        });
        users.value = [];
    } finally {
        loading.value = false;
    }
}

async function fetchTrashedUsers() {
    trashedLoading.value = true;
    try {
        const response = await axios.get('/users/trashed');
        trashedUsers.value = response.data.data || [];
        trashedUsers.value = trashedUsers.value.map((user) => ({
            ...user,
            user_id: user.id,
            name_fr: trim(`${user.nom_fr || ''} ${user.prenom_fr || ''}`),
            roles: Array.isArray(user.roles) ? user.roles : [],
            centres: Array.isArray(user.centres) ? user.centres : [],
        }));
    } catch (error) {
        console.error(
            'Erreur lors du chargement des utilisateurs supprimés:',
            error
        );
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail:
                error.response?.data?.message ||
                'Erreur lors du chargement des utilisateurs supprimés.',
            life: 5000,
        });
        trashedUsers.value = [];
    } finally {
        trashedLoading.value = false;
    }
}

async function restoreUser(user) {
    try {
        await axios.post(`/users/restore/${user.id}`);
        trashedUsers.value = trashedUsers.value.filter((u) => u.id !== user.id);
        await fetchUsers();
        toast.add({
            severity: 'success',
            summary: 'Succès',
            detail: 'Utilisateur restauré.',
            life: 3000,
        });
    } catch (error) {
        console.error(
            "Erreur lors de la restauration de l'utilisateur:",
            error
        );
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail:
                error.response?.data?.message ||
                "Impossible de restaurer l'utilisateur.",
            life: 5000,
        });
    }
}

function openTrashedDialog() {
    trashedVisible.value = true;
    fetchTrashedUsers();
}

async function openForm() {
    newUser.value = {
        id: null,
        nom_fr: '',
        prenom_fr: '',
        nom_ar: '',
        prenom_ar: '',
        matricule: '',
        num_extrait_naissance: '',
        cin: '',
        date_cin: null,
        lieu_cin_fr: '',
        lieu_cin_ar: '',
        date_naissance: null,
        lieu_naissance_fr: '',
        lieu_naissance_ar: '',
        nationalite_fr: '',
        nationalite_ar: '',
        genre_fr: 'Homme',
        genre_ar: '',
        qualite_fr: 'Personnel (ATFP) Direction Centrale',
        qualite_ar: '',
        etablissement_origine_fr: '',
        etablissement_origine_ar: '',
        mission_fr: '',
        mission_ar: '',
        observation_fr: '',
        observation_ar: '',
        email: '',
        password: '',
        password_confirmation: '',
        statut: 'Actif',
        roles: [],
        centres: [],
        photo: null,
    };
    formErrors.value = {};
    isCentreRole.value = false;
    formVisible.value = true;
    await nextTick();
}

function closeForm() {
    formVisible.value = false;
    newUser.value = {
        id: null,
        nom_fr: '',
        prenom_fr: '',
        nom_ar: '',
        prenom_ar: '',
        matricule: '',
        num_extrait_naissance: '',
        cin: '',
        date_cin: null,
        lieu_cin_fr: '',
        lieu_cin_ar: '',
        date_naissance: null,
        lieu_naissance_fr: '',
        lieu_naissance_ar: '',
        nationalite_fr: '',
        nationalite_ar: '',
        genre_fr: 'Homme',
        genre_ar: '',
        qualite_fr: 'Personnel (ATFP) Direction Centrale',
        qualite_ar: '',
        etablissement_origine_fr: '',
        etablissement_origine_ar: '',
        mission_fr: '',
        mission_ar: '',
        observation_fr: '',
        observation_ar: '',
        email: '',
        password: '',
        password_confirmation: '',
        statut: 'Actif',
        roles: [],
        centres: [],
        photo: null,
    };
    formErrors.value = {};
}

async function saveUser(user) {
    try {
        const payload = {
            id: user.id || null,
            nom_fr: user.nom_fr || null,
            prenom_fr: user.prenom_fr || null,
            nom_ar: user.nom_ar || null,
            prenom_ar: user.prenom_ar || null,
            matricule: user.matricule || null,
            num_extrait_naissance: user.num_extrait_naissance || null,
            cin: user.cin || null,
            date_cin: user.date_cin || null,
            lieu_cin_fr: user.lieu_cin_fr || null,
            lieu_cin_ar: user.lieu_cin_ar || null,
            date_naissance: user.date_naissance || null,
            lieu_naissance_fr: user.lieu_naissance_fr || null,
            lieu_naissance_ar: user.lieu_naissance_ar || null,
            nationalite_fr: user.nationalite_fr || null,
            nationalite_ar: user.nationalite_ar || null,
            genre_fr: user.genre_fr || null,
            genre_ar: user.genre_ar || null,
            qualite_fr: user.qualite_fr || null,
            qualite_ar: user.qualite_ar || null,
            etablissement_origine_fr: user.etablissement_origine_fr || null,
            etablissement_origine_ar: user.etablissement_origine_ar || null,
            mission_fr: user.mission_fr || null,
            mission_ar: user.mission_ar || null,
            observation_fr: user.observation_fr || null,
            observation_ar: user.observation_ar || null,
            email: user.email ? user.email.toLowerCase() : null,
            password: user.password || undefined,
            password_confirmation: user.password_confirmation || undefined,
            statut: user.statut || 'Actif',
            centres: Array.isArray(user.centres)
                ? user.centres.map((c) => c.centre_id)
                : [],
            roles: Array.isArray(user.roles)
                ? user.roles.map((r) => r.role_id)
                : [],
            photo: user.photo || null,
        };

        let response;
        if (!payload.id) {
            response = await axios.post('/users', payload);
            users.value.unshift({
                ...response.data.data,
                user_id: response.data.data.id,
                name_fr: trim(
                    `${response.data.data.nom_fr || ''} ${response.data.data.prenom_fr || ''}`
                ),
                name_ar: trim(
                    `${response.data.data.nom_ar || ''} ${response.data.data.prenom_ar || ''}`
                ),
                centres: response.data.data.centres || [],
                roles: response.data.data.roles || [],
                photo: response.data.data.photo || null,
                etablissement_origine_fr:
                    response.data.data.etablissement_origine_fr || '',
            });
            toast.add({
                severity: 'success',
                summary: 'Succès',
                detail: 'Utilisateur créé !',
                life: 3000,
            });
        } else {
            response = await axios.put(`/users/${payload.id}`, payload);
            const index = users.value.findIndex((u) => u.id === payload.id);
            if (index !== -1) {
                users.value[index] = {
                    ...response.data.data,
                    user_id: response.data.data.id,
                    name_fr: trim(
                        `${response.data.data.nom_fr || ''} ${response.data.data.prenom_fr || ''}`
                    ),
                    name_ar: trim(
                        `${response.data.data.nom_ar || ''} ${response.data.data.prenom_ar || ''}`
                    ),
                    centres: response.data.data.centres || [],
                    roles: response.data.data.roles || [],
                    photo: response.data.data.photo || null,
                    etablissement_origine_fr:
                        response.data.data.etablissement_origine_fr || '',
                };
            }
            toast.add({
                severity: 'success',
                summary: 'Succès',
                detail: 'Utilisateur mis à jour.',
                life: 3000,
            });
        }
        closeForm();
    } catch (error) {
        console.error(
            "Erreur lors de l'enregistrement de l'utilisateur:",
            error
        );
        if (error.response?.status === 422) {
            formErrors.value = error.response.data.errors || {};
            let errorMessage =
                'Veuillez corriger les erreurs dans le formulaire.';
            if (formErrors.value.nom_fr)
                errorMessage = formErrors.value.nom_fr[0];
            else if (formErrors.value.prenom_fr)
                errorMessage = formErrors.value.prenom_fr[0];
            else if (formErrors.value.email)
                errorMessage = formErrors.value.email[0];
            else if (formErrors.value.statut)
                errorMessage = formErrors.value.statut[0];
            else if (formErrors.value.password)
                errorMessage = formErrors.value.password[0];
            else if (formErrors.value.etablissement_origine_fr)
                errorMessage = formErrors.value.etablissement_origine_fr[0];
            else if (formErrors.value.qualite_fr)
                errorMessage = formErrors.value.qualite_fr[0];
            else if (formErrors.value.matricule)
                errorMessage = formErrors.value.matricule[0];
            else if (formErrors.value.num_extrait_naissance)
                errorMessage = formErrors.value.num_extrait_naissance[0];
            toast.add({
                severity: 'error',
                summary: 'Erreur de validation',
                detail: errorMessage,
                life: 5000,
            });
        } else {
            toast.add({
                severity: 'error',
                summary: 'Erreur',
                detail:
                    error.response?.data?.message ||
                    "Erreur lors de l'enregistrement.",
                life: 5000,
            });
        }
    }
}

async function editUser(user) {
    newUser.value = {
        id: user.id,
        nom_fr: user.nom_fr || '',
        prenom_fr: user.prenom_fr || '',
        nom_ar: user.nom_ar || '',
        prenom_ar: user.prenom_ar || '',
        matricule: user.matricule || '',
        num_extrait_naissance: user.num_extrait_naissance || '',
        cin: user.cin || '',
        date_cin: user.date_cin || null,
        lieu_cin_fr: user.lieu_cin_fr || '',
        lieu_cin_ar: user.lieu_cin_ar || '',
        date_naissance: user.date_naissance || null,
        lieu_naissance_fr: user.lieu_naissance_fr || '',
        lieu_naissance_ar: user.lieu_naissance_ar || '',
        nationalite_fr: user.nationalite_fr || '',
        nationalite_ar: user.nationalite_ar || '',
        genre_fr: user.genre_fr || 'Homme',
        genre_ar: user.genre_ar || '',
        qualite_fr: user.qualite_fr || 'Personnel (ATFP) Direction Centrale',
        qualite_ar: user.qualite_ar || '',
        etablissement_origine_fr: user.etablissement_origine_fr || '',
        etablissement_origine_ar: user.etablissement_origine_ar || '',
        mission_fr: user.mission_fr || '',
        mission_ar: user.mission_ar || '',
        observation_fr: user.observation_fr || '',
        observation_ar: user.observation_ar || '',
        email: user.email || '',
        password: '',
        password_confirmation: '',
        statut: user.statut || 'Actif',
        roles: Array.isArray(user.roles) ? user.roles : [],
        centres: Array.isArray(user.centres) ? user.centres : [],
        photo: user.photo || null,
    };
    isCentreRole.value = newUser.value.centres.length > 0;
    formErrors.value = {};
    formVisible.value = true;
    await nextTick();
}

function viewProfile(user) {
    if (!user) {
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: 'Aucun utilisateur sélectionné.',
            life: 3000,
        });
        return;
    }
    selectedUser.value = user;
    profileVisible.value = true;
}

function confirmDeleteUser(user) {
    userToDelete.value = user;
    deletePassword.value = '';
    passwordError.value = '';
    deleteFormVisible.value = true;
}

function cancelDeleteUser() {
    deleteFormVisible.value = false;
    userToDelete.value = null;
    deletePassword.value = '';
    passwordError.value = '';
}

async function deleteUser() {
    try {
        await axios.delete(`/users/${userToDelete.value.id}`, {
            data: { password: deletePassword.value },
        });
        users.value = users.value.filter((u) => u.id !== userToDelete.value.id);
        toast.add({
            severity: 'success',
            summary: 'Succès',
            detail: 'Utilisateur supprimé.',
            life: 3000,
        });
        cancelDeleteUser();
    } catch (error) {
        console.error("Erreur lors de la suppression de l'utilisateur:", error);
        if (error.response?.status === 401) {
            passwordError.value = 'Mot de passe incorrect.';
        } else {
            toast.add({
                severity: 'error',
                summary: 'Erreur',
                detail:
                    error.response?.data?.message ||
                    'Erreur lors de la suppression.',
                life: 5000,
            });
        }
    }
}

async function importXLS() {
    errors.value = [];
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = '.xlsx, .xls';
    input.onchange = async (event) => {
        const file = event.target.files[0];
        if (!file) return;
        try {
            const workbook = new ExcelJS.Workbook();
            await workbook.xlsx.load(file);
            const worksheet = workbook.worksheets[0];
            const rows = [];
            worksheet.eachRow((row, rowNumber) => {
                if (rowNumber > 1) {
                    rows.push({
                        nom_fr: row.getCell(1).value?.toString() || null,
                        prenom_fr: row.getCell(2).value?.toString() || null,
                        nom_ar: row.getCell(3).value?.toString() || null,
                        prenom_ar: row.getCell(4).value?.toString() || null,
                        matricule: row.getCell(5).value?.toString() || null,
                        qualite_fr: row.getCell(6).value?.toString() || null,
                        qualite_ar: row.getCell(7).value?.toString() || null,
                        email: row.getCell(8).value?.toString() || null,
                        centres: row.getCell(9).value
                            ? row
                                  .getCell(9)
                                  .value.toString()
                                  .split(',')
                                  .map((c) => c.trim())
                            : [],
                        roles: row.getCell(10).value
                            ? row
                                  .getCell(10)
                                  .value.toString()
                                  .split(',')
                                  .map((r) => r.trim())
                            : [],
                        etablissement_origine_fr:
                            row.getCell(11).value?.toString() || null,
                    });
                }
            });
            const response = await axios.post('/users/import', { users: rows });
            toast.add({
                severity: 'success',
                summary: 'Succès',
                detail:
                    response.data.message ||
                    'Utilisateurss importés avec succès.',
                life: 3000,
            });
            await fetchUsers();
            if (response.data.errors?.length > 0) {
                errors.value = response.data.errors;
                showImportError.value = true;
            }
        } catch (error) {
            console.error("Erreur lors de l'importation XLS:", error);
            toast.add({
                severity: 'error',
                summary: 'Erreur',
                detail:
                    error.response?.data?.message ||
                    "Erreur lors de l'importation du fichier.",
                life: 5000,
            });
        }
    };
    input.click();
}

async function exportXLS() {
    try {
        const workbook = new ExcelJS.Workbook();
        const worksheet = workbook.addWorksheet('Utilisateurss');
        worksheet.columns = [
            { header: 'Nom (FR)', key: 'nom_fr', width: 20 },
            { header: 'Prénom (FR)', key: 'prenom_fr', width: 20 },
            { header: 'Nom (AR)', key: 'nom_ar', width: 20 },
            { header: 'Prénom (AR)', key: 'prenom_ar', width: 20 },
            { header: 'Matricule', key: 'matricule', width: 15 },
            { header: 'Qualité (FR)', key: 'qualite_fr', width: 30 },
            { header: 'Qualité (AR)', key: 'qualite_ar', width: 30 },
            { header: 'Email', key: 'email', width: 30 },
            { header: 'Centres', key: 'centres', width: 40 },
            { header: 'Rôles', key: 'roles', width: 30 },
            {
                header: "Établissement d'origine",
                key: 'etablissement_origine_fr',
                width: 30,
            },
        ];
        filteredUsers.value.forEach((user) => {
            worksheet.addRow({
                nom_fr: user.nom_fr || '',
                prenom_fr: user.prenom_fr || '',
                nom_ar: user.nom_ar || '',
                prenom_ar: user.prenom_ar || '',
                matricule: user.matricule || '',
                qualite_fr: user.qualite_fr || '',
                qualite_ar: user.qualite_ar || '',
                email: user.email || '',
                centres: formatCentres(user.centres),
                roles: formatRoles(user.roles),
                etablissement_origine_fr: user.etablissement_origine_fr || '',
            });
        });
        const buffer = await workbook.xlsx.writeBuffer();
        const blob = new Blob([buffer], {
            type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `utilisateurs_${new Date().toISOString().split('T')[0]}.xlsx`;
        a.click();
        window.URL.revokeObjectURL(url);
        toast.add({
            severity: 'success',
            summary: 'Succès',
            detail: 'Exportation réussie.',
            life: 3000,
        });
    } catch (error) {
        console.error("Erreur lors de l'exportation XLS:", error);
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: "Erreur lors de l'exportation du fichier.",
            life: 5000,
        });
    }
}

// Utility function to trim strings
function trim(str) {
    return str.trim().replace(/\s+/g, ' ');
}
</script>

<style scoped>
.search-field :deep(.p-inputtext) {
    width: 250px;
}
.trash-icon {
    color: #e57373 !important;
}
:deep(.p-datatable .p-datatable-tbody > tr > td) {
    padding: 0.5rem;
}
:deep(.p-paginator) {
    justify-content: flex-end;
}
:deep(.p-dialog) {
    z-index: 1000 !important;
}
@font-face {
    font-family: 'Montserrat-Arabic';
    src: url('/fonts/Montserrat-Arabic-Regular.otf') format('opentype');
    font-weight: normal;
    font-style: normal;
}
.font-arabic {
    font-family: 'Montserrat-Arabic', sans-serif;
}
.custom-avatar {
    width: 100px !important;
    height: 100px !important;
}
.photo-upload-button {
    position: absolute;
    bottom: -10px;
    right: 10px;
}
.cropper-container {
    width: 300px;
    height: 300px;
    position: relative;
    overflow: hidden;
    background: transparent;
}
:deep(.cropper-handler) {
    display: none !important;
}
:deep(.cropper-handler.bottom-right) {
    display: block !important;
    background: #3b82f6 !important;
    border: 2px solid #ffffff !important;
    width: 16px !important;
    height: 16px !important;
    border-radius: 50%;
    cursor: se-resize;
    box-shadow: 0 0 5px rgba(0, 0, 0, 0.5);
}
:deep(.cropper-viewport) {
    background: transparent !important;
    border: none !important;
}
:deep(.cropper-face) {
    background: transparent !important;
}
.text-error {
    color: var(--red-500);
}
</style>
