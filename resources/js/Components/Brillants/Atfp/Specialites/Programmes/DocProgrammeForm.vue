<template>
    <div class="document-manager-container">
        <!-- Toolbar avec bouton aligné à droite -->
        <Toolbar class="mb-4 surface-0 border-bottom-1 surface-border">
            <template #start>
                <div class="flex align-items-center gap-3">
                    <i class="pi pi-file-pdf text-primary text-2xl"></i>
                    <div>
                        <h2 class="m-0 text-900 font-bold">
                            Gestion des documents
                        </h2>
                        <div class="flex align-items-center gap-2 mt-1">
                            <Tag
                                :value="specialite?.code || 'N/A'"
                                severity="info"
                                class="font-bold"
                            />
                            <span class="text-700">{{ specialite?.nom_fr || 'Spécialité non définie' }}</span>
                        </div>
                    </div>
                </div>
            </template>
            <template #end>
                <Button
                    label="Ajouter un document"
                    icon="pi pi-plus"
                    severity="success"
                    class="font-medium"
                    @click="showAddDialog = true"
                    :disabled="!programme?.id"
                />
            </template>
        </Toolbar>

        <!-- Panel Détails Spécialité avec compteur de documents -->
        <Panel toggleable collapsed class="mb-4 surface-ground border-1 surface-border">
            <template #header>
                <div class="flex align-items-center gap-2">
                    <i class="pi pi-info-circle text-primary"></i>
                    <span class="font-semibold">Détails de la spécialité</span>
                    <Badge :value="documentCount" severity="info" class="ml-2"></Badge>
                </div>
            </template>
            <div class="grid p-3">
                <div class="col-12 md:col-3">
                    <div class="detail-item">
                        <label class="text-600 text-sm font-medium">Code</label>
                        <p class="detail-value font-bold text-900">
                            {{ specialite?.code || 'Non défini' }}
                        </p>
                    </div>
                </div>
                <div class="col-12 md:col-3">
                    <div class="detail-item">
                        <label class="text-600 text-sm font-medium">Nom (FR)</label>
                        <p class="detail-value text-900">
                            {{ specialite?.nom_fr || 'Non défini' }}
                        </p>
                    </div>
                </div>
                <div class="col-12 md:col-3">
                    <div class="detail-item">
                        <label class="text-600 text-sm font-medium">Documents</label>
                        <div class="flex align-items-center gap-2">
                            <Tag :value="documentCount" severity="info" rounded />
                            <span class="text-600 text-sm">document(s)</span>
                        </div>
                    </div>
                </div>
                <div class="col-12 md:col-3">
                    <div class="detail-item">
                        <label class="text-600 text-sm font-medium">Standardisation</label>
                        <Tag
                            :value="specialite?.standardisation_ar || 'غير معرف'"
                            :severity="getStandardizationSeverity(specialite?.standardisation_ar)"
                            class="font-bold"
                        />
                    </div>
                </div>
            </div>
        </Panel>

        <!-- Tableau avec filtres complets -->
        <Card class="shadow-1 border-1 surface-border">
            <template #content>
                <DataTable
                    :value="processedDocuments"
                    :loading="loading"
                    :paginator="true"
                    :rows="10"
                    :rowsPerPageOptions="[5, 10, 20]"
                    v-model:filters="filters"
                    showGridlines
                    stripedRows
                    scrollable
                    scrollHeight="750px"
                    responsiveLayout="scroll"
                    dataKey="id"
                    filterDisplay="menu"
                    class="p-datatable-sm modern-table"
                    :globalFilterFields="['titre', 'type_document_fr', 'computedStatus', 'description', 'observation']"
                    currentPageReportTemplate="Affichage de {first} à {last} sur {totalRecords} entrées"
                    paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                >
                    <template #header>
                        <div class="flex flex-column md:flex-row md:justify-content-between md:align-items-center gap-3">
                            <span class="p-input-icon-left w-full md:w-auto">
                                <i class="pi pi-search" />
                                <InputText
                                    v-model="filters['global'].value"
                                    placeholder="Recherche globale..."
                                    class="w-full"
                                />
                            </span>
                            <div class="flex gap-2">
                                <Button
                                    icon="pi pi-filter-slash"
                                    label="Effacer"
                                    class="p-button-outlined"
                                    @click="clearFilters"
                                />
                            </div>
                        </div>
                    </template>

                    <!-- Colonne Titre avec filtre -->
                    <Column field="titre" header="Titre" sortable style="min-width: 12rem">
                        <template #body="{ data }">
                            <span>{{ data.titre || 'Non spécifié' }}</span>
                        </template>
                        <template #filter="{ filterModel, filterCallback }">
                            <InputText
                                v-model="filterModel.value"
                                type="text"
                                class="p-column-filter"
                                placeholder="Rechercher par titre"
                                @input="filterCallback()"
                            />
                        </template>
                    </Column>

                    <!-- Colonne Type avec filtre -->
                    <Column field="type_document_fr" header="Type" sortable style="min-width: 10rem">
                        <template #body="{ data }">
                            <div class="flex align-items-center gap-2">
                                <i class="pi pi-file-pdf text-red-500"></i>
                                <span>{{ data.type_document_fr || 'Non spécifié' }}</span>
                            </div>
                        </template>
                        <template #filter="{ filterModel, filterCallback }">
                            <Dropdown
                                v-model="filterModel.value"
                                :options="documentTypes.map(type => type.nom_fr)"
                                placeholder="Tous les types"
                                class="p-column-filter"
                                :showClear="true"
                                @change="filterCallback()"
                            />
                        </template>
                    </Column>

                    <!-- Colonne Statut avec filtre -->
                    <Column field="computedStatus" header="Statut" sortable style="min-width: 8rem">
                        <template #body="{ data }">
                            <Tag
                                :value="data.computedStatus"
                                :severity="getStatusSeverity(data.computedStatus)"
                                :icon="getStatusIcon(data.computedStatus)"
                            />
                        </template>
                        <template #filter="{ filterModel, filterCallback }">
                            <Dropdown
                                v-model="filterModel.value"
                                :options="statutOptions"
                                optionLabel="label"
                                optionValue="value"
                                placeholder="Tous les statuts"
                                class="p-column-filter"
                                :showClear="true"
                                @change="filterCallback()"
                            >
                                <template #option="slotProps">
                                    <Tag
                                        :value="slotProps.option.label"
                                        :severity="getStatusSeverity(slotProps.option.value)"
                                        :icon="getStatusIcon(slotProps.option.value)"
                                    />
                                </template>
                            </Dropdown>
                        </template>
                    </Column>

                    <!-- Colonne Description avec filtre -->
                    <Column field="description" header="Description" sortable style="min-width: 12rem">
                        <template #body="{ data }">
                            <span>{{ data.description || 'Aucune' }}</span>
                        </template>
                        <template #filter="{ filterModel, filterCallback }">
                            <InputText
                                v-model="filterModel.value"
                                type="text"
                                class="p-column-filter"
                                placeholder="Rechercher par description"
                                @input="filterCallback()"
                            />
                        </template>
                    </Column>

                    <!-- Colonne Observation avec filtre -->
                    <Column field="observation" header="Observation" sortable style="min-width: 12rem">
                        <template #body="{ data }">
                            <span>{{ data.observation || 'Aucune' }}</span>
                        </template>
                        <template #filter="{ filterModel, filterCallback }">
                            <InputText
                                v-model="filterModel.value"
                                type="text"
                                class="p-column-filter"
                                placeholder="Rechercher par observation"
                                @input="filterCallback()"
                            />
                        </template>
                    </Column>

                    <!-- Actions Column -->
                    <Column header="Actions" :exportable="false" style="min-width: 120px">
                        <template #body="{ data }">
                            <div class="flex gap-1 justify-content-center">
                                <Button
                                    icon="pi pi-eye"
                                    class="p-button-rounded p-button-text p-button-plain"
                                    @click="previewDocument(data)"
                                    v-tooltip.top="'Prévisualiser'"
                                />
                                <Button
                                    icon="pi pi-download"
                                    class="p-button-rounded p-button-text p-button-plain"
                                    @click="downloadDocument(data)"
                                    v-tooltip.top="'Télécharger'"
                                />
                                <Button
                                    icon="pi pi-pencil"
                                    class="p-button-rounded p-button-text p-button-plain"
                                    @click="editDocument(data)"
                                    v-tooltip.top="'Modifier'"
                                />
                                <Button
                                    icon="pi pi-trash"
                                    class="p-button-rounded p-button-text p-button-danger"
                                    @click="confirmDeleteDocument(data)"
                                    v-tooltip.top="'Supprimer'"
                                />
                            </div>
                        </template>
                    </Column>

                    <template #empty>
                        <div class="flex flex-column align-items-center justify-content-center p-4 gap-3">
                            <i class="pi pi-file-pdf text-5xl text-400"></i>
                            <span class="text-600 font-medium">Aucun document disponible</span>
                            <Button
                                label="Ajouter un document"
                                icon="pi pi-plus"
                                class="p-button-text"
                                @click="showAddDialog = true"
                                :disabled="!programme?.id"
                            />
                        </div>
                    </template>

                    <template #loading>
                        <div class="flex flex-column align-items-center justify-content-center p-4 gap-3">
                            <ProgressSpinner style="width: 40px; height: 40px" />
                            <span class="text-600 font-medium">Chargement des données...</span>
                        </div>
                    </template>
                </DataTable>
            </template>
        </Card>

        <!-- Dialog Add/Edit avec champ fichier amélioré -->
        <Dialog
            v-model:visible="showAddDialog"
            :modal="true"
            :style="{ width: '700px' }"
            :breakpoints="{ '960px': '75vw', '640px': '90vw' }"
            :closable="false"
            :pt="{
                root: 'border-none',
                mask: { style: 'backdrop-filter: blur(2px)' },
                header: { class: 'surface-50 border-bottom-1 surface-border compact-header' },
                content: { class: 'surface-50 py-0' },
                footer: { class: 'surface-50 border-top-1 surface-border' }
            }"
        >
            <template #header>
                <div class="flex align-items-center justify-content-between w-full">
                    <div class="flex align-items-center gap-2">
                        <i :class="editMode ? 'pi pi-pencil' : 'pi pi-plus'" class="text-primary"></i>
                        <span class="font-bold text-lg">{{ editMode ? 'Modifier un document' : 'Ajouter un document' }}</span>
                    </div>
                    <Button
                        icon="pi pi-times"
                        class="p-button-text p-button-rounded p-button-plain absolute right-0 top-0 mr-2 mt-2"
                        @click="closeDialog"
                    />
                </div>
            </template>

            <div class="p-fluid formgrid grid p-5 gap-3">
                <!-- Titre et Type sur la même ligne, même largeur -->
                <div class="flex flex-wrap gap-3 w-full">
                    <div class="field flex-1">
                        <label for="titre" class="block font-medium mb-2">
                            Titre <span class="text-red-500">*</span>
                        </label>
                        <InputText
                            id="titre"
                            v-model="documentForm.titre"
                            placeholder="Ex: Document principal"
                            class="w-full"
                            :class="{ 'p-invalid': submitted && !documentForm.titre }"
                            :autoFocus="false"
                        />
                        <small v-if="submitted && !documentForm.titre" class="p-error">Titre requis</small>
                    </div>
                    <div class="field flex-1">
                        <label for="type" class="block font-medium mb-2">
                            Type <span class="text-red-500">*</span>
                        </label>
                        <Dropdown
                            id="type"
                            v-model="documentForm.type"
                            :options="documentTypes"
                            optionLabel="nom_fr"
                            placeholder="Sélectionner un type"
                            class="w-full"
                            :class="{ 'p-invalid': submitted && !documentForm.type }"
                            :autoFocus="false"
                            :loading="loadingTypes"
                        />
                        <small v-if="submitted && !documentForm.type" class="p-error">Type requis</small>
                        <small v-if="typeLoadError" class="p-error">{{ typeLoadError }}</small>
                    </div>
                </div>

                <!-- Champ Fichier amélioré -->
                <div class="field col-12">
                    <label for="fichier" class="block font-medium mb-2">
                        Fichier PDF <span v-if="!editMode" class="text-red-500">*</span>
                    </label>
                    <div class="p-inputgroup">
                        <InputText
                            :value="currentFileName"
                            placeholder="Aucun fichier sélectionné"
                            class="w-full"
                            readonly
                            :class="{ 'p-invalid': fileError || (submitted && !fileToUpload && !editMode) }"
                        />
                        <Button icon="pi pi-upload" class="p-button-primary" @click="$refs.fileUpload.click()" />
                        <input
                            ref="fileUpload"
                            type="file"
                            accept=".pdf"
                            style="display: none"
                            @change="onFileSelect($event)"
                        />
                    </div>
                    <small v-if="fileError" class="p-error">{{ fileError }}</small>
                    <small v-if="submitted && !fileToUpload && !editMode" class="p-error">Fichier PDF requis</small>
                </div>

                <!-- Statut avec RadioButton pour sélection unique -->
                <div class="field col-12">
                    <label class="block font-medium mb-2">Statut <span class="text-red-500">*</span></label>
                    <div class="flex flex-wrap gap-3">
                        <div v-for="option in statutOptions" :key="option.value" class="flex align-items-center">
                            <RadioButton
                                v-model="documentForm.statut"
                                :inputId="'statut_' + option.value.toLowerCase()"
                                name="statut"
                                :value="option.value"
                                :class="{ 'p-invalid': submitted && !documentForm.statut }"
                            />
                            <label :for="'statut_' + option.value.toLowerCase()" class="ml-2">{{ option.label }}</label>
                        </div>
                    </div>
                    <small v-if="submitted && !documentForm.statut" class="p-error">Statut requis</small>
                </div>

                <!-- Description -->
                <div class="field col-12">
                    <label for="description" class="block font-medium mb-2">Description</label>
                    <Textarea
                        id="description"
                        v-model="documentForm.description"
                        rows="3"
                        placeholder="Saisissez la description..."
                        class="w-full"
                        autoResize
                    />
                </div>

                <!-- Observation -->
                <div class="field col-12">
                    <label for="observation" class="block font-medium mb-2">Observation</label>
                    <Textarea
                        id="observation"
                        v-model="documentForm.observation"
                        rows="3"
                        placeholder="Saisissez vos observations..."
                        class="w-full"
                        autoResize
                    />
                </div>
            </div>

            <template #footer>
                <div class="flex justify-content-end gap-2">
                    <Button
                        label="Annuler"
                        icon="pi pi-times"
                        class="p-button-text"
                        @click="closeDialog"
                    />
                    <Button
                        :label="editMode ? 'Mettre à jour' : 'Enregistrer'"
                        :icon="editMode ? 'pi pi-check' : 'pi pi-save'"
                        class="p-button-success"
                        @click="saveDocument"
                        :loading="saving"
                    />
                </div>
            </template>
        </Dialog>

        <!-- Delete Confirmation Dialog -->
        <Dialog
            v-model:visible="showDeleteDialog"
            :style="{ width: '450px' }"
            :modal="true"
            :pt="{
                root: { class: 'border-none' },
                header: { class: 'surface-100 border-bottom-1 surface-border p-4' },
                content: { class: 'surface-100 p-5' },
                footer: { class: 'surface-100 border-top-1 surface-border p-4' }
            }"
        >
            <template #header>
                <div class="flex align-items-center gap-2">
                    <i class="pi pi-exclamation-triangle text-red-500"></i>
                    <span class="font-bold text-lg">Confirmer la suppression</span>
                </div>
            </template>
            <div class="flex align-items-center gap-3">
                <i class="pi pi-exclamation-circle text-red-500 text-2xl"></i>
                <span>Voulez-vous vraiment supprimer ce document ? Cette action est irréversible.</span>
            </div>
            <template #footer>
                <div class="flex justify-content-end gap-2">
                    <Button
                        label="Non"
                        icon="pi pi-times"
                        class="p-button-text"
                        @click="showDeleteDialog = false"
                    />
                    <Button
                        label="Oui, supprimer"
                        icon="pi pi-check"
                        class="p-button-danger"
                        @click="deleteDocument"
                    />
                </div>
            </template>
        </Dialog>

        <!-- Document Preview Dialog -->
        <Dialog
            v-model:visible="showPreviewDialog"
            :modal="true"
            :style="{ width: '80vw', height: '90vh' }"
            :breakpoints="{ '960px': '95vw', '640px': '100vw' }"
            :pt="{
                root: { class: 'border-round shadow-4' },
                header: { class: 'surface-50 border-bottom-1 surface-border p-3' },
                content: { class: 'p-0 h-full' },
                footer: { class: 'surface-50 border-top-1 surface-border p-3' }
            }"
        >
            <template #header>
                <div class="flex align-items-center gap-2">
                    <i class="pi pi-file-pdf text-red-500 text-xl"></i>
                    <span class="font-bold text-lg">{{ previewDocumentName || 'Prévisualisation du document' }}</span>
                </div>
            </template>
            <div v-if="previewLoading" class="flex justify-content-center align-items-center h-full">
                <ProgressSpinner style="width: 50px; height: 50px" />
                <span class="ml-3 text-600">Chargement du PDF...</span>
            </div>
            <div v-else-if="previewError" class="flex justify-content-center align-items-center h-full flex-column gap-3">
                <i class="pi pi-exclamation-circle text-red-500 text-4xl"></i>
                <span class="text-600">{{ previewError }}</span>
            </div>
            <iframe
                v-else
                :src="previewDocumentUrl"
                class="w-full h-full border-none"
                title="Prévisualisation du document PDF"
            ></iframe>
            <template #footer>
                <Button
                    label="Fermer"
                    icon="pi pi-times"
                    class="p-button-text"
                    @click="closeDialogPreview"
                />
            </template>
        </Dialog>

        <Toast position="top-right" />
    </div>
</template>

<script>
import { ref, computed, watch, onMounted } from 'vue';
import { useToast } from 'primevue/usetoast';
import { FilterMatchMode, FilterOperator } from 'primevue/api';
import axios from 'axios';

// PrimeVue Components
import Button from 'primevue/button';
import Card from 'primevue/card';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Textarea from 'primevue/textarea';
import Toast from 'primevue/toast';
import ProgressSpinner from 'primevue/progressspinner';
import Toolbar from 'primevue/toolbar';
import Panel from 'primevue/panel';
import Badge from 'primevue/badge';
import RadioButton from 'primevue/radiobutton';

export default {
    components: {
        Button,
        Card,
        DataTable,
        Column,
        Tag,
        Dialog,
        InputText,
        Dropdown,
        Textarea,
        Toast,
        ProgressSpinner,
        Toolbar,
        Panel,
        Badge,
        RadioButton
    },
    props: {
        specialite: { type: Object, default: () => ({}) },
        programme: { type: Object, default: () => ({}) },
        visible: { type: Boolean, default: false },
        documentToEdit: { type: Object, default: null }
    },
    emits: ['update:visible', 'save', 'update', 'close', 'refresh'],
    setup(props, { emit }) {
        const toast = useToast();
        const documents = ref([]);
        const loading = ref(false);
        const showAddDialog = ref(false);
        const showDeleteDialog = ref(false);
        const showPreviewDialog = ref(false);
        const editMode = ref(false);
        const currentDocument = ref(null);
        const fileToUpload = ref(null);
        const fileError = ref(null);
        const submitted = ref(false);
        const saving = ref(false);
        const previewDocumentUrl = ref(null);
        const previewDocumentName = ref('');
        const previewLoading = ref(false);
        const previewError = ref(null);
        const documentTypes = ref([]);
        const loadingTypes = ref(false);
        const typeLoadError = ref(null);

        const statutOptions = ref([
            { label: 'Actif', value: 'Actif' },
            { label: 'Inactif', value: 'Inactif' }
        ]);

        const documentForm = ref({
            programme_id: null,
            titre: '',
            type: null, // Stocke l'objet { nom_fr, nom_ar }
            statut: 'Actif',
            description: '',
            observation: ''
        });

        const filters = ref({
            global: { value: null, matchMode: FilterMatchMode.CONTAINS },
            titre: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
            type_document_fr: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.EQUALS }] },
            computedStatus: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.EQUALS }] },
            description: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
            observation: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] }
        });

        const documentCount = computed(() => documents.value.length);

        const currentFileName = computed(() => {
            if (fileToUpload.value) return fileToUpload.value.name;
            return editMode.value ? 'Fichier existant' : '';
        });

        const processedDocuments = computed(() => {
            return documents.value.map(doc => ({
                ...doc,
                computedStatus: doc.statut
            }));
        });

        // API Configuration
        const api = {
            async initializeCsrf() {
                try {
                    await axios.get('/sanctum/csrf-cookie');
                } catch (error) {
                    console.error('Erreur lors de l\'initialisation du CSRF:', error);
                    throw new Error('Échec de l\'initialisation CSRF');
                }
            },
            async fetchDocumentTypes() {
                const response = await axios.get('/api/document-programme');
                return Array.isArray(response.data.data) ? response.data.data : [];
            },
            async fetchDocuments(programmeId) {
                const response = await axios.get(`/api/documents-programmes?programme_id=${programmeId}`);
                return Array.isArray(response.data.data) ? response.data.data : [];
            },
            async saveDocument(formData, isEdit, documentId) {
                return axios({
                    method: 'post',
                    url: isEdit ? `/api/documents-programmes/${documentId}` : '/api/documents-programmes',
                    data: formData,
                    headers: { 'Content-Type': 'multipart/form-data' }
                });
            },
            async deleteDocument(documentId) {
                return axios.delete(`/api/documents-programmes/${documentId}`);
            },
            async downloadDocument(documentId) {
                return axios.get(`/api/documents-programmes/${documentId}/download`, {
                    responseType: 'blob'
                });
            }
        };

        // Gestion standardisée des erreurs API
        const handleApiError = (error, defaultMessage) => {
            console.error('Erreur API:', error);
            const errorMessage =
                error.response?.data?.message ||
                (error.response?.data?.details
                    ? Object.values(error.response.data.details).flat().join(', ')
                    : defaultMessage);
            toast.add({
                severity: 'error',
                summary: 'Erreur',
                detail: errorMessage,
                life: 5000
            });
            return errorMessage;
        };

        const clearFilters = () => {
            initFilters();
        };

        const initFilters = () => {
            filters.value = {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                titre: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
                type_document_fr: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.EQUALS }] },
                computedStatus: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.EQUALS }] },
                description: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
                observation: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] }
            };
        };

        const fetchDocumentTypes = async () => {
            loadingTypes.value = true;
            typeLoadError.value = null;
            try {
                documentTypes.value = await api.fetchDocumentTypes();
                if (documentTypes.value.length === 0) {
                    typeLoadError.value = 'Aucun type de document disponible.';
                    toast.add({
                        severity: 'warn',
                        summary: 'Avertissement',
                        detail: 'Aucun type de document n\'est configuré dans le système.',
                        life: 5000
                    });
                }
                console.log('Types de documents chargés:', documentTypes.value);
            } catch (error) {
                typeLoadError.value = handleApiError(error, 'Impossible de charger les types de documents.');
                documentTypes.value = [];
            } finally {
                loadingTypes.value = false;
            }
        };

        const fetchDocuments = async () => {
            if (!props.programme?.id) {
                console.warn('Aucun programme_id fourni:', props.programme);
                toast.add({
                    severity: 'warn',
                    summary: 'Avertissement',
                    detail: 'Aucun programme sélectionné pour charger les documents.',
                    life: 3000
                });
                documents.value = [];
                return;
            }
            loading.value = true;
            try {
                documents.value = await api.fetchDocuments(props.programme.id);
                console.log('Documents chargés:', documents.value);
            } catch (error) {
                handleApiError(error, 'Impossible de charger les documents.');
                documents.value = [];
            } finally {
                loading.value = false;
            }
        };

        const onFileSelect = (event) => {
            const file = event.target.files[0];
            if (!file) return;

            if (file.type !== 'application/pdf') {
                fileError.value = 'Seuls les fichiers PDF sont acceptés';
                fileToUpload.value = null;
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                fileError.value = 'Le fichier ne doit pas dépasser 2MB';
                fileToUpload.value = null;
                return;
            }

            fileError.value = null;
            fileToUpload.value = file;
        };

        const previewDocument = async (doc) => {
            if (!doc?.id) {
                toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Document invalide.',
                    life: 3000
                });
                return;
            }
            try {
                previewLoading.value = true;
                previewError.value = null;
                previewDocumentName.value = `${doc.titre || 'Document'} - ${doc.type_document_fr || 'Non spécifié'}`;
                const response = await api.downloadDocument(doc.id);
                const blob = new Blob([response.data], { type: 'application/pdf' });
                if (previewDocumentUrl.value) {
                    URL.revokeObjectURL(previewDocumentUrl.value);
                }
                previewDocumentUrl.value = URL.createObjectURL(blob);
                showPreviewDialog.value = true;
            } catch (error) {
                previewError.value =
                    error.response?.status === 404
                        ? 'Document non trouvé.'
                        : handleApiError(error, 'Impossible de charger le document PDF.');
                showPreviewDialog.value = true;
            } finally {
                previewLoading.value = false;
            }
        };

        const downloadDocument = async (doc) => {
            if (!doc?.id) {
                toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Document invalide.',
                    life: 3000
                });
                return;
            }
            try {
                const response = await api.downloadDocument(doc.id);
                const blob = new Blob([response.data], { type: 'application/pdf' });
                const url = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', doc.titre ? `${doc.titre}.pdf` : 'document.pdf');
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                window.URL.revokeObjectURL(url);
                toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Document téléchargé avec succès.',
                    life: 3000
                });
            } catch (error) {
                handleApiError(error, 'Impossible de télécharger le document.');
            }
        };

        const saveDocument = async () => {
            submitted.value = true;
            saving.value = true;

            if (!props.programme?.id) {
                console.error('Erreur: programme_id est indéfini', { programme: props.programme });
                toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'ID de programme manquant.',
                    life: 3000
                });
                saving.value = false;
                return;
            }

            if (!documentForm.value.titre || !documentForm.value.type || !documentForm.value.statut) {
                console.warn('Validation échouée', { documentForm: documentForm.value });
                toast.add({
                    severity: 'warn',
                    summary: 'Validation',
                    detail: 'Veuillez remplir tous les champs obligatoires (titre, type, statut).',
                    life: 3000
                });
                saving.value = false;
                return;
            }

            if (!editMode.value && !fileToUpload.value) {
                console.warn('Fichier requis pour un nouveau document');
                toast.add({
                    severity: 'warn',
                    summary: 'Validation',
                    detail: 'Un fichier PDF est requis pour un nouveau document.',
                    life: 3000
                });
                saving.value = false;
                return;
            }

            const formData = new FormData();
            formData.append('programme_id', props.programme.id.toString());
            formData.append('titre', documentForm.value.titre.trim());
            formData.append('type_document_fr', documentForm.value.type.nom_fr);
            formData.append('type_document_ar', documentForm.value.type.nom_ar);
            formData.append('statut', documentForm.value.statut);
            if (fileToUpload.value) {
                formData.append('fichier', fileToUpload.value);
            }
            if (documentForm.value.description) {
                formData.append('description', documentForm.value.description.trim());
            }
            if (documentForm.value.observation) {
                formData.append('observation', documentForm.value.observation.trim());
            }
            if (editMode.value) {
                formData.append('_method', 'PUT');
            }

            const formDataEntries = {};
            for (let [key, value] of formData.entries()) {
                formDataEntries[key] = value instanceof File ? value.name : value;
            }
            console.log('FormData envoyé:', formDataEntries);

            try {
                const response = await api.saveDocument(formData, editMode.value, currentDocument.value?.id);
                toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: editMode.value ? 'Document mis à jour avec succès' : 'Document ajouté avec succès',
                    life: 3000
                });
                closeDialog();
                await fetchDocuments();
                emit(editMode.value ? 'update' : 'save', response.data.data);
            } catch (error) {
                handleApiError(error, 'Erreur lors de la sauvegarde du document');
            } finally {
                saving.value = false;
            }
        };

        const editDocument = (doc) => {
            if (!doc?.id || !props.programme?.id) {
                toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Document ou programme invalide.',
                    life: 3000
                });
                return;
            }
            currentDocument.value = { ...doc };
            editMode.value = true;
            const selectedType = documentTypes.value.find(
                (type) => type.nom_fr === doc.type_document_fr
            ) || null;
            documentForm.value = {
                programme_id: props.programme.id,
                titre: doc.titre || '',
                type: selectedType,
                statut: doc.statut || 'Actif',
                description: doc.description || '',
                observation: doc.observation || ''
            };
            fileToUpload.value = null;
            fileError.value = null;
            showAddDialog.value = true;
            submitted.value = false;
            console.log('Initialisation de documentForm pour édition:', documentForm.value);
        };

        const confirmDeleteDocument = (doc) => {
            if (!doc?.id) {
                toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Document invalide.',
                    life: 3000
                });
                return;
            }
            currentDocument.value = doc;
            showDeleteDialog.value = true;
        };

        const deleteDocument = async () => {
            if (!currentDocument.value?.id) {
                toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Document invalide.',
                    life: 3000
                });
                return;
            }
            try {
                await api.deleteDocument(currentDocument.value.id);
                toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Document supprimé avec succès',
                    life: 3000
                });
                showDeleteDialog.value = false;
                await fetchDocuments();
                emit('refresh');
            } catch (error) {
                handleApiError(error, 'Impossible de supprimer le document.');
            }
        };

        const closeDialog = () => {
            showAddDialog.value = false;
            editMode.value = false;
            currentDocument.value = null;
            fileToUpload.value = null;
            fileError.value = null;
            submitted.value = false;
            documentForm.value = {
                programme_id: props.programme?.id || null,
                titre: '',
                type: null,
                statut: 'Actif',
                description: '',
                observation: ''
            };
            emit('close');
        };

        const closeDialogPreview = () => {
            showPreviewDialog.value = false;
            if (previewDocumentUrl.value) {
                URL.revokeObjectURL(previewDocumentUrl.value);
            }
            previewDocumentUrl.value = null;
            previewDocumentName.value = '';
            previewError.value = null;
        };

        const getStatusSeverity = (status) => {
            switch (status) {
                case 'Actif':
                    return 'success';
                case 'Inactif':
                    return 'warning';
                default:
                    return 'info';
            }
        };

        const getStatusIcon = (status) => {
            switch (status) {
                case 'Actif':
                    return 'pi pi-check-circle';
                case 'Inactif':
                    return 'pi pi-clock';
                default:
                    return null;
            }
        };

        const getStandardizationSeverity = (standard) => {
            switch (standard) {
                case 'مقيس':
                    return 'success';
                case 'غير مقيس':
                    return 'danger';
                case 'جديد':
                    return 'warning';
                default:
                    return 'info';
            }
        };

        watch(
            () => props.programme?.id,
            (newId) => {
                if (newId) {
                    documentForm.value.programme_id = newId;
                    fetchDocuments();
                } else {
                    documents.value = [];
                    documentForm.value.programme_id = null;
                }
            },
            { immediate: true }
        );

        watch(
            () => props.visible,
            (newVisible) => {
                if (newVisible && props.documentToEdit) {
                    editDocument(props.documentToEdit);
                }
            }
        );

        onMounted(async () => {
            initFilters();
            console.log('Composant monté, programme:', props.programme);
            try {
                await api.initializeCsrf();
                await fetchDocumentTypes();
                if (props.programme?.id) {
                    documentForm.value.programme_id = props.programme.id;
                    await fetchDocuments();
                }
            } catch (error) {
                console.error('Erreur lors de l\'initialisation:', error);
                toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Erreur lors du chargement initial des données.',
                    life: 3000
                });
            }
        });

        return {
            documents,
            processedDocuments,
            loading,
            documentTypes,
            showAddDialog,
            showDeleteDialog,
            showPreviewDialog,
            editMode,
            currentDocument,
            documentForm,
            statutOptions,
            fileToUpload,
            fileError,
            submitted,
            saving,
            filters,
            documentCount,
            currentFileName,
            previewDocumentUrl,
            previewDocumentName,
            previewLoading,
            previewError,
            loadingTypes,
            typeLoadError,
            fetchDocumentTypes,
            fetchDocuments,
            onFileSelect,
            previewDocument,
            downloadDocument,
            saveDocument,
            editDocument,
            confirmDeleteDocument,
            deleteDocument,
            closeDialog,
            closeDialogPreview,
            clearFilters,
            getStatusSeverity,
            getStatusIcon,
            getStandardizationSeverity
        };
    }
};
</script>

<style scoped lang="scss">
.document-manager-container {
    display: flex;
    flex-direction: column;
    padding: 1.5rem;
    gap: 1.5rem;
    background-color: var(--surface-0);

    .p-toolbar {
        background-color: var(--surface-0);
        border: 1px solid var(--surface-border);
        border-radius: 4px;
        padding: 1rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);

        .p-button {
            transition: all 0.2s ease-in-out;

            &:hover {
                transform: translateY(-1px);
            }
        }
    }

    .p-panel {
        border-radius: 6px;
        overflow: hidden;

        .p-panel-header {
            background-color: var(--surface-ground);
            padding: 1rem;
            border-bottom: 1px solid var(--surface-border);
        }

        .detail-item {
            margin-bottom: 1rem;

            label {
                font-size: 0.875rem;
                color: var(--text-color-secondary);
                display: block;
                margin-bottom: 0.25rem;
            }

            .detail-value {
                margin: 0;
                font-size: 1rem;
            }
        }
    }

    .p-card {
        border-radius: 6px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        overflow: hidden;

        .p-card-body {
            padding: 1.5rem;
        }
    }

    .modern-table {
        border-radius: 6px;
        overflow: hidden;

        :deep(.p-datatable) {
            .p-datatable-header {
                background-color: var(--surface-50);
                padding: 1rem;
                border-bottom: 1px solid var(--surface-border);
            }

            .p-datatable-thead {
                tr > th {
                    background-color: var(--surface-50);
                    font-weight: 600;
                    color: var(--text-color-secondary);
                    border-bottom: 1px solid var(--surface-border);
                    padding: 1rem;
                    white-space: nowrap;

                    .p-column-filter {
                        width: 100%;
                    }
                }
            }

            .p-datatable-tbody {
                tr {
                    transition: background-color 0.2s;

                    &:hover {
                        background-color: var(--surface-50) !important;
                    }

                    td {
                        border-bottom: 1px solid var(--surface-border);
                        padding: 1rem;
                        vertical-align: middle;

                        .p-tag {
                            font-size: 0.875rem;
                            padding: 0.25rem 0.75rem;
                        }

                        .p-button {
                            margin: 0 0.25rem;
                        }
                    }
                }
            }

            .p-paginator {
                background-color: var(--surface-50);
                border-top: 1px solid var(--surface-border);
                padding: 1rem;

                .p-paginator-element {
                    margin: 0 0.25rem;
                }

                .p-dropdown {
                    min-width: 80px;
                }
            }
        }
    }

    .p-dialog {
        border-radius: 8px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);

        .p-dialog-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--surface-border);
        }

        .p-dialog-content {
            padding: 1.5rem;
            background-color: var(--surface-50);
        }

        .p-dialog-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--surface-border);
        }

        .formgrid {
            .field {
                label {
                    font-size: 0.875rem;
                    font-weight: 500;
                    color: var(--text-color);
                }

                .p-inputtext,
                .p-dropdown {
                    border-radius: 4px;
                    transition: border-color 0.2s;

                    &:focus {
                        border-color: var(--primary-color);
                        box-shadow: 0 0 0 2px var(--primary-color-light);
                    }
                }

                .p-error {
                    font-size: 0.75rem;
                    margin-top: 0.25rem;
                }
            }

            .flex {
                display: flex;
                flex-wrap: wrap;
                gap: 1rem;
                width: 100%;

                .field {
                    flex: 1;
                    min-width: 0;
                }
            }
        }

        .compact-header {
            padding: 0.5rem !important;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
    }

    .file-upload-container {
        .p-inputtext[readonly] {
            background-color: var(--surface-100);
            cursor: default;
        }

        .p-button {
            padding: 0.5rem 1rem;
        }
    }

    .preview-dialog {
        .p-dialog-content {
            padding: 0;
            overflow: hidden;

            iframe {
                border: none;
                width: 100%;
                height: 100%;
            }
        }
    }

    .p-toast {
        .p-toast-message {
            border-radius: 4px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }
    }

    .p-datatable-empty-message,
    .p-datatable-loading {
        padding: 2rem;
        text-align: center;

        .pi {
            font-size: 3rem;
            color: var(--text-color-secondary);
        }

        .p-progress-spinner {
            width: 40px;
            height: 40px;
        }
    }

    @media (max-width: 768px) {
        padding: 1rem;

        .p-toolbar {
            flex-direction: column;
            gap: 1rem;

            .p-toolbar-group-start,
            .p-toolbar-group-end {
                width: 100%;
                justify-content: center;
            }
        }

        .modern-table {
            :deep(.p-datatable) {
                .p-datatable-thead {
                    tr > th {
                        padding: 0.75rem;
                        font-size: 0.875rem;
                    }
                }

                .p-datatable-tbody {
                    tr > td {
                        padding: 0.75rem;
                        font-size: 0.875rem;
                    }
                }
            }
        }

        .p-dialog {
            width: 95vw !important;

            .formgrid {
                .field {
                    margin-bottom: 1.5rem;
                }

                .flex {
                    flex-direction: column;
                    gap: 1.5rem;
                }
            }
        }
    }

    .fade-enter-active,
    .fade-leave-active {
        transition: opacity 0.3s ease;
    }

    .fade-enter-from,
    .fade-leave-to {
        opacity: 0;
    }
}
</style>
