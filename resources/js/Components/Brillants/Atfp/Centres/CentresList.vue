```vue
<template>
    <div class="surface-ground pt-2 px-1.5 pb-1.5" style="margin-top: 0">
        <!-- ProgressSpinner ajouté ici -->
        <div v-if="isImporting" class="progress-overlay">
            <ProgressSpinner
                style="width: 50px; height: 50px"
                strokeWidth="5"
            />

            <span class="progress-text">Importation en cours...</span>
        </div>
        <!-- Header Section with Navbar -->
        <div
            class="surface-card p-2 border-round border-1 surface-border"
            style="
                position: relative;
                top: -36px;
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
                        label="Centres"
                        icon="pi pi-building"
                        class="p-button-text p-button-plain p-button-sm"
                        disabled
                    />
                </div>
                <div class="flex gap-2">
                    <Button
                        icon="pi pi-refresh"
                        class="p-button-text p-button-sm p-button-rounded p-button-secondary"
                        @click="fetchCentres"
                        v-tooltip="'Rafraîchir'"
                    />
                    <div class="flex align-items-center gap-2">
                        <Button
                            icon="pi pi-file-import"
                            class="p-button-text p-button-sm p-button-rounded p-button-success"
                            @click="$refs.fileInput.click()"
                            v-tooltip="'Importer XLS'"
                        />
                        <Button
                            v-if="importErrors.length > 0"
                            icon="pi pi-exclamation-triangle"
                            class="p-button-text p-button-sm p-button-rounded p-button-danger"
                            @click="showImportErrors = true"
                            v-tooltip="'Voir les erreurs d\'importation'"
                        />
                        <input
                            type="file"
                            ref="fileInput"
                            style="display: none"
                            accept=".xls,.xlsx"
                            @change="importCentres"
                        />
                    </div>
                    <Button
                        icon="pi pi-file-export"
                        class="p-button-text p-button-sm p-button-rounded p-button-help"
                        @click="exportCentres"
                        v-tooltip="'Exporter XLS'"
                    />
                    <Button
                        icon="pi pi-trash"
                        class="p-button-text p-button-sm p-button-rounded p-button-danger trash-icon"
                        @click="showTrashedDialog = true"
                        v-tooltip="'Centres supprimés'"
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
                <Button
                    icon="pi pi-plus"
                    label=""
                    class="p-button-success p-button-sm p-button-rounded"
                    @click="openForm"
                />
            </div>

            <!-- Main DataTable -->
            <DataTable
                v-model:filters="filters"
                :value="centres"
                :frozenValue="FixLignes"
                dataKey="id"
                size="small"
                stripedRows
                paginator
                :rows="rows"
                :rowsPerPageOptions="[20, 50, 100]"
                :totalRecords="totalRecords"
                filterDisplay="menu"
                :globalFilterFields="[
                    'code',
                    'nom_fr',
                    'nom_ar',
                    'gouvernorat_fr',
                    'etat_fr',
                    'statut_fr',
                    'telephone_1',
                    'email',
                    'economat_fr',
                    'type_centre_fr',
                    'classe_fr',
                ]"
                :loading="loading"
                scrollable
                scrollHeight="700px"
                removableSort
                class="p-datatable-sm border-1 surface-border"
                :lazy="true"
                @page="onPage($event)"
                @update:rows="onRowsUpdate($event)"
                :pt="{
                    table: { style: 'min-width: 120rem' },
                    bodyrow: ({ props }) => ({
                        class: [{ 'font-bold': props.frozenRow }],
                    }),
                }"
            >
                <template #empty>
                    <div class="text-center py-4">
                        <i class="pi pi-inbox text-4xl text-400 mb-2" />
                        <p class="text-600">Aucun centre trouvé</p>
                    </div>
                </template>

                <!-- Logo Column -->
                <Column header="Logo" style="min-width: 10rem">
                    <template #body="{ data }">
                        <img
                            v-if="data.logo"
                            :src="getLogoSrc(data.logo)"
                            alt="Logo du centre"
                            class="logo-image"
                            @error="onImageError"
                        />
                        <span v-else>-</span>
                    </template>
                </Column>

                <Column
                    field="code"
                    header="Code"
                    sortable
                    style="min-width: 12rem"
                    frozen
                >
                    <template #body="{ data }">
                        <span class="flex align-items-center gap-2">
                            <i
                                class="pi pi-circle-fill"
                                :class="
                                    data.statut_fr === 'Fonctionnel'
                                        ? 'text-green-500'
                                        : 'text-red-500'
                                "
                                style="font-size: 0.5rem"
                            />
                            <span class="font-medium">{{ data.code }}</span>
                        </span>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter"
                            placeholder="Rechercher par code"
                            @input="filterCallback()"
                        />
                    </template>
                </Column>

                <Column
                    field="nom_fr"
                    header="Nom (FR)"
                    sortable
                    style="min-width: 30rem"
                >
                    <template #body="{ data }">
                        <span>{{ data.nom_fr }}</span>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter"
                            placeholder="Rechercher par nom (FR)"
                            @input="filterCallback()"
                        />
                    </template>
                </Column>

                <Column
                    field="nom_ar"
                    header="Nom (AR)"
                    sortable
                    style="min-width: 30rem"
                >
                    <template #body="{ data }">
                        <span class="font-arabic">{{ data.nom_ar }}</span>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter font-arabic"
                            dir="rtl"
                            placeholder="البحث بالاسم (العربية)"
                            @input="filterCallback()"
                        />
                    </template>
                </Column>

                <Column
                    field="gouvernorat_fr"
                    header="Gouvernorat"
                    sortable
                    style="min-width: 14rem"
                >
                    <template #body="{ data }">
                        <Tag
                            :value="data.gouvernorat_fr || '-'"
                            severity="info"
                        />
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-if="gouvernoratOptions.length > 0"
                            v-model="filterModel.value"
                            :options="gouvernoratOptions"
                            optionLabel="nom_fr"
                            optionValue="nom_fr"
                            placeholder="Sélectionner un gouvernorat"
                            class="p-column-filter"
                            :showClear="true"
                            @change="filterCallback()"
                        />
                        <InputText
                            v-else
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter"
                            placeholder="Filtre indisponible"
                            disabled
                        />
                    </template>
                </Column>

                <Column
                    field="telephone_1"
                    header="Téléphone"
                    sortable
                    style="min-width: 12rem"
                >
                    <template #body="{ data }">
                        <span>{{ data.telephone_1 }}</span>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter"
                            placeholder="Rechercher par téléphone"
                            @input="filterCallback()"
                        />
                    </template>
                </Column>

                <Column
                    field="email"
                    header="Email"
                    sortable
                    style="min-width: 14rem"
                >
                    <template #body="{ data }">
                        <span>{{ data.email || '-' }}</span>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter"
                            placeholder="Rechercher par email"
                            @input="filterCallback()"
                        />
                    </template>
                </Column>

                <Column
                    field="economat_fr"
                    header="Économat (FR)"
                    sortable
                    style="min-width: 14rem"
                >
                    <template #body="{ data }">
                        <Tag
                            :value="data.economat_fr || '-'"
                            severity="warning"
                        />
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-if="economatOptions.length > 0"
                            v-model="filterModel.value"
                            :options="economatOptions"
                            optionLabel="nom_fr"
                            optionValue="nom_fr"
                            placeholder="Sélectionner un économat"
                            class="p-column-filter"
                            :showClear="true"
                            @change="filterCallback()"
                        />
                        <InputText
                            v-else
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter"
                            placeholder="Filtre indisponible"
                            disabled
                        />
                    </template>
                </Column>

                <Column
                    field="type_centre_fr"
                    header="Type"
                    sortable
                    style="min-width: 14rem"
                >
                    <template #body="{ data }">
                        <Tag
                            :value="getTypeLabel(data.type_centre_fr)"
                            :severity="getTypeSeverity(data.type_centre_fr)"
                        />
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-if="typeCentreOptions.length > 0"
                            v-model="filterModel.value"
                            :options="typeCentreOptions"
                            optionLabel="nom_fr"
                            optionValue="nom_fr"
                            placeholder="Sélectionner un type"
                            class="p-column-filter"
                            :showClear="true"
                            @change="filterCallback()"
                        />
                        <InputText
                            v-else
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter"
                            placeholder="Filtre indisponible"
                            disabled
                        />
                    </template>
                </Column>

                <Column
                    field="classe_fr"
                    header="Classe"
                    sortable
                    style="min-width: 12rem"
                >
                    <template #body="{ data }">
                        <Tag
                            :value="data.classe_fr || '-'"
                            :severity="getClasseSeverity(data.classe_fr)"
                        />
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-if="classeOptions.length > 0"
                            v-model="filterModel.value"
                            :options="classeOptions"
                            optionLabel="nom_fr"
                            optionValue="nom_fr"
                            placeholder="Sélectionner une classe"
                            class="p-column-filter"
                            :showClear="true"
                            @change="filterCallback()"
                        />
                        <InputText
                            v-else
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter"
                            placeholder="Filtre indisponible"
                            disabled
                        />
                    </template>
                </Column>

                <Column
                    field="etat_fr"
                    header="État"
                    sortable
                    style="min-width: 12rem"
                >
                    <template #body="{ data }">
                        <Tag
                            :value="data.etat_fr || '-'"
                            :severity="
                                data.etat_fr === 'Ouvert' ? 'success' : 'danger'
                            "
                        />
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-if="etatOptions.length > 0"
                            v-model="filterModel.value"
                            :options="etatOptions"
                            optionLabel="nom_fr"
                            optionValue="nom_fr"
                            placeholder="Sélectionner un état"
                            class="p-column-filter"
                            :showClear="true"
                            @change="filterCallback()"
                        />
                        <InputText
                            v-else
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter"
                            placeholder="Filtre indisponible"
                            disabled
                        />
                    </template>
                </Column>

                <Column
                    field="statut_fr"
                    header="Statut"
                    sortable
                    style="min-width: 12rem"
                >
                    <template #body="{ data }">
                        <Tag
                            :value="data.statut_fr || '-'"
                            :severity="
                                data.statut_fr === 'Fonctionnel'
                                    ? 'success'
                                    : 'danger'
                            "
                        />
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-if="statutOptions.length > 0"
                            v-model="filterModel.value"
                            :options="statutOptions"
                            optionLabel="nom_fr"
                            optionValue="nom_fr"
                            placeholder="Sélectionner un statut"
                            class="p-column-filter"
                            :showClear="true"
                            @change="filterCallback()"
                        />
                        <InputText
                            v-else
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter"
                            placeholder="Filtre indisponible"
                            disabled
                        />
                    </template>
                </Column>

                <Column header="Actions" style="min-width: 14rem" frozen>
                    <template #body="{ data, frozenRow, index }">
                        <div class="flex gap-2">
                            <Button
                                :icon="
                                    frozenRow ? 'pi pi-lock-open' : 'pi pi-lock'
                                "
                                :disabled="
                                    frozenRow ? false : FixLignes.length >= 10
                                "
                                class="p-button-text p-button-rounded p-button-secondary"
                                @click="toggleLock(data, frozenRow, index)"
                                v-tooltip="
                                    frozenRow ? 'Déverrouiller' : 'Verrouiller'
                                "
                            />
                            <Button
                                icon="pi pi-eye"
                                class="p-button-text p-button-rounded p-button-primary"
                                v-tooltip="'Voir les détails'"
                                @click="viewCentre(data)"
                            />
                            <Button
                                icon="pi pi-pencil"
                                class="p-button-text p-button-rounded p-button-warning"
                                v-tooltip="'Modifier'"
                                @click="editCentre(data)"
                            />
                            <Button
                                icon="pi pi-trash"
                                class="p-button-text p-button-rounded p-button-danger"
                                v-tooltip="'Supprimer'"
                                @click="confirmDelete(data)"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>

            <!-- Dialogs -->
            <CentreForm
                :visible="showForm"
                :centreId="centreToEdit ? centreToEdit.id : null"
                :centreData="centreToEdit"
                :style="{ width: '900px' }"
                :breakpoints="{ '960px': '80vw', '640px': '95vw' }"
                @update:visible="showForm = $event"
                @close="closeForm"
                @save="onCentreSaved"
                @update="onCentreUpdated"
            />
            <Dialog
                v-model:visible="showDeleteDialog"
                header="Confirmation de suppression"
                :modal="true"
                :style="{ width: '700px' }"
                :pt="{
                    root: { class: 'border-none' },
                    mask: { style: 'backdrop-filter: blur(2px)' },
                    header: {
                        class: 'surface-50 border-bottom-1 surface-border',
                    },
                    content: { class: 'surface-50 py-0' },
                    footer: { class: 'surface-50 border-top-1 surface-border' },
                }"
            >
                <div class="confirmation-content flex flex-column gap-3 p-3">
                    <div class="flex align-items-center gap-3">
                        <i
                            class="pi pi-exclamation-triangle text-4xl text-red-500"
                        />
                        <span v-if="centreToDelete">
                            Voulez-vous vraiment supprimer le centre
                            <b>{{
                                centreToDelete.nom_fr || centreToDelete.nom_ar
                            }}</b>
                            ?
                        </span>
                    </div>
                    <div class="field">
                        <label for="deletePassword" class="font-semibold"
                            >Mot de passe de confirmation</label
                        >
                        <span class="p-inputgroup">
                            <InputText
                                id="deletePassword"
                                v-model="deletePassword"
                                :type="showPassword ? 'text' : 'password'"
                                class="w-full"
                                :class="{ 'p-invalid': passwordError }"
                                placeholder="Entrez le mot de passe"
                                @input="passwordError = ''"
                            />
                            <Button
                                :icon="
                                    showPassword
                                        ? 'pi pi-eye-slash'
                                        : 'pi pi-eye'
                                "
                                class="p-button-text"
                                @click="toggleShowPassword"
                                v-tooltip="
                                    showPassword
                                        ? 'Masquer le mot de passe'
                                        : 'Afficher le mot de passe'
                                "
                            />
                        </span>
                        <small v-if="passwordError" class="p-error">{{
                            passwordError
                        }}</small>
                    </div>
                </div>
                <template #footer>
                    <div class="mt-4 flex justify-end gap-2">
                        <Button
                            label="Annuler"
                            icon="pi pi-times"
                            class="p-button-text"
                            @click="showDeleteDialog = false"
                        />
                        <Button
                            label="Supprimer"
                            icon="pi pi-check"
                            class="p-button-danger"
                            :disabled="deleting || !deletePassword"
                            @click="deleteCentre"
                            :loading="deleting"
                        />
                    </div>
                </template>
            </Dialog>

            <Dialog
                v-model:visible="showTrashedDialog"
                header="Centres supprimés"
                :modal="true"
                :style="{ width: '90vw', maxWidth: '1600px' }"
                :breakpoints="{ '960px': '95vw', '640px': '100vw' }"
                :pt="{
                    root: { class: 'border-none' },
                    mask: { style: 'backdrop-filter: blur(2px)' },
                    header: {
                        class: 'surface-50 border-bottom-1 surface-border',
                    },
                    content: { class: 'surface-50 p-4' },
                    footer: { class: 'surface-50 border-top-1 surface-border' },
                }"
            >
                <RecupererCentres @restored="fetchCentres" />
                <template #footer>
                    <Button
                        label="Fermer"
                        icon="pi pi-times"
                        class="p-button-text"
                        @click="showTrashedDialog = false"
                    />
                </template>
            </Dialog>

            <CentreDetails
                :showViewDialog="showViewDialog"
                :selectedCentre="selectedCentre"
                @update:showViewDialog="showViewDialog = $event"
                @edit="editCentre"
                @refresh="fetchCentres"
            />
            <ImportErrors
                :visible="showImportErrors"
                :errors="importErrors"
                @update:visible="showImportErrors = $event"
                @close="showImportErrors = false"
                @line-imported="handleLineImported"
                @update:errors="importErrors = $event"
            />
        </div>
        <Toast position="top-right" />
    </div>
</template>

<script>
import { FilterMatchMode } from 'primevue/api';
import axios from 'axios';
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import Dropdown from 'primevue/dropdown';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import FileUpload from 'primevue/fileupload';
import ProgressBar from 'primevue/progressbar';
import ProgressSpinner from 'primevue/progressspinner'; // Ajout du composant ProgressSpinner
import CentreForm from '@/Components/Atfp/Centres/CentresForm.vue';
import CentreDetails from '@/Components/Atfp/Centres/CentreDetails.vue';
import ImportErrors from '@/Components/Atfp/ImportError/CentresImportError.vue';
import RecupererCentres from '@/Components/Atfp/Recuperations/RecupererCentres.vue';
import { useToast } from 'primevue/usetoast';
import { useRouter } from 'vue-router';

export default {
    components: {
        Button,
        Column,
        DataTable,
        InputText,
        Tag,
        Dropdown,
        Dialog,
        Toast,
        FileUpload,
        ProgressBar,
        ProgressSpinner, // Ajout dans les composants
        CentreForm,
        CentreDetails,
        ImportErrors,
        RecupererCentres,
    },
    setup() {
        const toast = useToast();
        const router = useRouter();
        return { toast, router };
    },
    data() {
        return {
            centres: [],
            FixLignes: [],
            loading: false,
            filters: {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                code: { value: null, matchMode: FilterMatchMode.CONTAINS },
                nom_fr: { value: null, matchMode: FilterMatchMode.CONTAINS },
                nom_ar: { value: null, matchMode: FilterMatchMode.CONTAINS },
                gouvernorat_fr: {
                    value: null,
                    matchMode: FilterMatchMode.EQUALS,
                },
                etat_fr: { value: null, matchMode: FilterMatchMode.EQUALS },
                statut_fr: { value: null, matchMode: FilterMatchMode.EQUALS },
                telephone_1: {
                    value: null,
                    matchMode: FilterMatchMode.CONTAINS,
                },
                email: { value: null, matchMode: FilterMatchMode.CONTAINS },
                economat_fr: { value: null, matchMode: FilterMatchMode.EQUALS },
                type_centre_fr: {
                    value: null,
                    matchMode: FilterMatchMode.EQUALS,
                },
                classe_fr: { value: null, matchMode: FilterMatchMode.EQUALS },
            },
            showForm: false,
            centreToEdit: null,
            showDeleteDialog: false,
            centreToDelete: null,
            deletePassword: '',
            passwordError: '',
            deleting: false,
            showViewDialog: false,
            selectedCentre: null,
            showImportErrors: false,
            importErrors: [],
            gouvernoratOptions: [],
            typeCentreOptions: [],
            classeOptions: [],
            economatOptions: [],
            etatOptions: [],
            statutOptions: [],
            lists: {},
            rows: 20,
            currentPage: 1,
            totalRecords: 0,
            showTrashedDialog: false,
            showPassword: false,
            isImporting: false, // Nouvelle variable pour gérer l'état d'importation
        };
    },
    created() {
        this.fetchCentres();
    },
    methods: {
        async fetchCentres() {
            this.loading = true;
            try {
                const response = await axios.get('/api/centres-with-options', {
                    params: {
                        page: this.currentPage,
                        per_page: this.rows,
                    },
                });

                if (!response.data?.centres?.data || !response.data?.lists) {
                    throw new Error('Structure de réponse invalide');
                }

                this.centres = response.data.centres.data;
                this.totalRecords = response.data.centres.total || 0;
                this.lists = response.data.lists || {};

                this.gouvernoratOptions = this.lists['Gouvernorats'] || [];
                this.typeCentreOptions = this.lists['Types Centres'] || [];
                this.classeOptions = this.lists['Classes Centres'] || [];
                this.economatOptions = this.lists['Economats'] || [];
                this.etatOptions = this.lists['Etats Centres'] || [];
                this.statutOptions = this.lists['Statuts Centres'] || [];

                [
                    'Gouvernorats',
                    'Types Centres',
                    'Classes Centres',
                    'Economats',
                    'Etats Centres',
                    'Statuts Centres',
                ].forEach((list) => {
                    if (!this.lists[list] || this.lists[list].length === 0) {
                        this.toast.add({
                            severity: 'warn',
                            summary: 'Attention',
                            detail: `La liste "${list}" est indisponible.`,
                            life: 5000,
                        });
                    }
                });
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        'Impossible de charger les centres.',
                    life: 3000,
                });
            } finally {
                this.loading = false;
            }
        },
        toggleShowPassword() {
            this.showPassword = !this.showPassword;
        },
        async importCentres(event) {
            const file = event.target.files[0];
            if (!file) return;
            this.isImporting = true; // Activer le spinner
            const formData = new FormData();
            formData.append('action', 'import');
            formData.append('file', file);

            try {
                const response = await axios.post(
                    '/api/centres/bulk',
                    formData,
                    {
                        headers: { 'Content-Type': 'multipart/form-data' },
                    }
                );

                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: `Importation terminée. ${response.data.success_count || 0} lignes importées, ${response.data.error_count || 0} lignes en échec.`,
                    life: 5000,
                });

                this.importErrors = response.data.error_lines || [];
                this.showImportErrors = this.importErrors.length > 0;

                await this.fetchCentres();
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        "Erreur lors de l'importation.",
                    life: 5000,
                });
            } finally {
                event.target.value = '';
                this.isImporting = false; // Désactiver le spinner
            }
        },
        async exportCentres() {
            try {
                const response = await axios.post(
                    '/api/centres/bulk',
                    { action: 'export' },
                    { responseType: 'blob' }
                );
                const url = window.URL.createObjectURL(
                    new Blob([response.data])
                );
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'centres_export.xlsx');
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                window.URL.revokeObjectURL(url);
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        "Erreur lors de l'exportation.",
                    life: 3000,
                });
            }
        },
        openForm() {
            this.centreToEdit = null;
            this.showForm = true;
        },
        editCentre(centre) {
            this.centreToEdit = { ...centre };
            this.showForm = true;
        },
        viewCentre(centre) {
            this.selectedCentre = { ...centre };
            this.showViewDialog = true;
        },
        confirmDelete(centre) {
            this.centreToDelete = centre;
            this.deletePassword = '';
            this.passwordError = '';
            this.showDeleteDialog = true;
        },
        async deleteCentre() {
            this.deleting = true;
            try {
                const response = await axios.delete(
                    `/api/centres/${this.centreToDelete.id}`,
                    {
                        data: { password: this.deletePassword },
                        headers: {
                            Authorization: `Bearer ${localStorage.getItem('token')}`,
                        },
                    }
                );
                this.centres = this.centres.filter(
                    (c) => c.id !== this.centreToDelete.id
                );
                this.FixLignes = this.FixLignes.filter(
                    (c) => c.id !== this.centreToDelete.id
                );
                this.showDeleteDialog = false;
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Centre supprimé avec succès.',
                    life: 3000,
                });
            } catch (error) {
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
                this.deleting = false;
            }
        },
        onCentreSaved(centre) {
            this.centres.push(centre);
            this.showForm = false;
            this.toast.add({
                severity: 'success',
                summary: 'Succès',
                detail: 'Centre ajouté avec succès.',
                life: 3000,
            });
        },
        onCentreUpdated(centre) {
            const index = this.centres.findIndex((c) => c.id === centre.id);
            if (index !== -1) {
                this.centres.splice(index, 1, centre);
            }
            const fixIndex = this.FixLignes.findIndex(
                (c) => c.id === centre.id
            );
            if (fixIndex !== -1) {
                this.FixLignes.splice(fixIndex, 1, centre);
            }
            this.showForm = false;
            this.toast.add({
                severity: 'success',
                summary: 'Succès',
                detail: 'Centre mis à jour avec succès.',
                life: 3000,
            });
        },
        closeForm() {
            this.showForm = false;
            this.centreToEdit = null;
        },
        clearFilter() {
            this.filters = {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                code: { value: null, matchMode: FilterMatchMode.CONTAINS },
                nom_fr: { value: null, matchMode: FilterMatchMode.CONTAINS },
                nom_ar: { value: null, matchMode: FilterMatchMode.CONTAINS },
                gouvernorat_fr: {
                    value: null,
                    matchMode: FilterMatchMode.EQUALS,
                },
                etat_fr: { value: null, matchMode: FilterMatchMode.EQUALS },
                statut_fr: { value: null, matchMode: FilterMatchMode.EQUALS },
                telephone_1: {
                    value: null,
                    matchMode: FilterMatchMode.CONTAINS,
                },
                email: { value: null, matchMode: FilterMatchMode.CONTAINS },
                economat_fr: { value: null, matchMode: FilterMatchMode.EQUALS },
                type_centre_fr: {
                    value: null,
                    matchMode: FilterMatchMode.EQUALS,
                },
                classe_fr: { value: null, matchMode: FilterMatchMode.EQUALS },
            };
        },
        getTypeLabel(type) {
            const typeObj = this.typeCentreOptions.find(
                (t) => t.nom_fr === type
            );
            return typeObj ? typeObj.nom_fr : type || '-';
        },
        getTypeSeverity(type) {
            if (!type) return 'secondary';
            switch (type) {
                case 'Centre Sectoriel de Formation':
                    return 'success';
                case "Centre de Formation et d'Apprentissage Professionnel":
                    return 'info';
                case 'Centre de Formation et de Promotion du Travail Indépendant':
                    return 'warning';
                case 'Centre de la Jeune Fille Rurale':
                    return 'danger';
                default:
                    return 'secondary';
            }
        },
        getClasseSeverity(classe) {
            if (!classe) return 'secondary';
            switch (classe) {
                case 'Classe A':
                    return 'success';
                case 'Classe B':
                    return 'info';
                case 'Classe C':
                    return 'secondary';
                default:
                    return 'secondary';
            }
        },
        getLogoSrc(logo) {
            if (logo && logo.startsWith('data:image/')) {
                return logo;
            }
            return logo || '';
        },
        onImageError(event) {
            this.toast.add({
                severity: 'warn',
                summary: 'Erreur',
                detail: "Impossible de charger l'image du logo.",
                life: 3000,
            });
        },
        async handleLineImported(lineData) {
            try {
                const response = await axios.post('/api/centres/bulk', {
                    action: 'import-line',
                    lineData,
                });
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Ligne importée avec succès.',
                    life: 3000,
                });
                this.importErrors = this.importErrors.filter(
                    (e) => e.line !== lineData.line
                );
                if (this.importErrors.length === 0) {
                    this.showImportErrors = false;
                }
                await this.fetchCentres();
            } catch (error) {
                let errorMessage =
                    error.response?.data?.message ||
                    "Erreur lors de l'importation de la ligne.";
                if (error.response?.status === 422) {
                    errorMessage =
                        error.response?.data?.errors?.lineData || errorMessage;
                } else if (error.response?.status === 500) {
                    errorMessage =
                        error.response?.data?.error ||
                        'Erreur serveur interne. Veuillez vérifier les logs serveur.';
                }
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: errorMessage,
                    life: 5000,
                });
            }
        },
        onPage(event) {
            this.currentPage = event.page + 1;
            this.rows = event.rows;
            this.fetchCentres();
        },
        onRowsUpdate(rows) {
            this.rows = rows;
            this.currentPage = 1;
            this.fetchCentres();
        },
        toggleLock(data, frozen, index) {
            if (frozen) {
                this.FixLignes = this.FixLignes.filter((_, i) => i !== index);
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Ligne libérée.',
                    life: 3000,
                });
            } else if (this.FixLignes.length < 10) {
                this.FixLignes.push(data);
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Ligne fixée.',
                    life: 3000,
                });
            } else {
                this.toast.add({
                    severity: 'warn',
                    summary: 'Limite atteinte',
                    detail: 'Vous ne pouvez pas fixer plus de 10 lignes.',
                    life: 3000,
                });
            }
        },
    },
};
</script>

<style scoped>
@font-face {
    font-family: 'Montserrat-Arabic-Regular';
    src: url('/fonts/Montserrat-Arabic-Regular.otf') format('opentype');
    font-weight: normal;
    font-style: normal;
}

.logo-image {
    width: 50px;
    height: 50px;
    object-fit: contain;
}

.search-field {
    min-width: 250px;
}

.font-arabic,
:deep(.p-inputtext[dir='rtl']) {
    font-family: 'Montserrat-Arabic-Regular', sans-serif;
}

.trash-icon i {
    color: #dc3545; /* Red for trash icon */
}

.import-icon i {
    color: #28a745; /* Green for import icon */
}
.export-icon i {
    color: #007bff; /* Blue for export icon */
}
.trash-icon i {
    color: #dc3545; /* Red for trash icon */
}
.p-tooltip-text {
    white-space: nowrap; /* Prevent tooltip text from wrapping */
}

/* Styles pour le spinner de progression */
.progress-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5); /* Fond semi-transparent */
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    z-index: 1000; /* Assure que le spinner est au-dessus des autres éléments */
}

.progress-text {
    margin-top: 10px;
    color: #ffffff;
    font-size: 1.2rem;
    font-family: 'Montserrat-Arabic-Regular', sans-serif;
}
</style>
```
