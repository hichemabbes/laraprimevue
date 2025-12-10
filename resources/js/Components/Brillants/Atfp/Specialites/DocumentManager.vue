<template>
    <div class="document-manager-container">
        <!-- Toolbar avec bouton aligné à droite -->
        <Toolbar class="mb-4 surface-0 border-bottom-1 surface-border">
            <template #start>
                <div class="flex align-items-center gap-3">
                    <i class="pi pi-file-pdf text-primary text-2xl"></i>
                    <div>
                        <h2 class="m-0 text-900 font-bold">
                            Gestion des Documents
                        </h2>
                        <div class="flex align-items-center gap-2 mt-1">
                            <Tag
                                :value="specialite.code"
                                severity="info"
                                class="font-bold"
                            />
                            <span class="text-700">{{
                                specialite.nom_fr
                            }}</span>
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
                />
            </template>
        </Toolbar>

        <!-- Panel Détails Spécialité avec compteur de versions -->
        <Panel
            toggleable
            collapsed
            class="mb-4 surface-ground border-1 surface-border"
        >
            <template #header>
                <div class="flex align-items-center gap-2">
                    <i class="pi pi-info-circle text-primary"></i>
                    <span class="font-semibold">Détails de la spécialité</span>
                    <Badge
                        :value="documentCount"
                        severity="info"
                        class="ml-2"
                    ></Badge>
                </div>
            </template>
            <div class="grid p-3">
                <div class="col-12 md:col-3">
                    <div class="detail-item">
                        <label class="text-600 text-sm font-medium">Code</label>
                        <p class="detail-value font-bold text-900">
                            {{ specialite.code || 'Non défini' }}
                        </p>
                    </div>
                </div>
                <div class="col-12 md:col-3">
                    <div class="detail-item">
                        <label class="text-600 text-sm font-medium"
                            >Nom (FR)</label
                        >
                        <p class="detail-value text-900">
                            {{ specialite.nom_fr || 'Non défini' }}
                        </p>
                    </div>
                </div>
                <div class="col-12 md:col-3">
                    <div class="detail-item">
                        <label class="text-600 text-sm font-medium"
                            >Versions</label
                        >
                        <div class="flex align-items-center gap-2">
                            <Tag
                                :value="documentCount"
                                severity="info"
                                rounded
                            />
                            <span class="text-600 text-sm">document(s)</span>
                        </div>
                    </div>
                </div>
                <div class="col-12 md:col-3">
                    <div class="detail-item">
                        <label class="text-600 text-sm font-medium"
                            >Standardisation</label
                        >
                        <Tag
                            :value="specialite.standardisation_ar || 'غير معرف'"
                            :severity="
                                getStandardizationSeverity(
                                    specialite.standardisation_ar
                                )
                            "
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
                    :globalFilterFields="[
                        'version',
                        'type_doc_fr',
                        'computedStatus',
                        'date_debut',
                        'observation_fr',
                    ]"
                    currentPageReportTemplate="Affichage de {first} à {last} sur {totalRecords} entrées"
                    paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                >
                    <template #header>
                        <div
                            class="flex flex-column md:flex-row md:justify-content-between md:align-items-center gap-3"
                        >
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

                    <!-- Colonne Version avec filtre -->
                    <Column
                        field="version"
                        header="Version"
                        sortable
                        style="min-width: 8rem"
                    >
                        <template #body="{ data }">
                            <Tag
                                :value="data.version"
                                :severity="
                                    getStatusSeverity(data.computedStatus)
                                "
                                rounded
                            />
                        </template>
                        <template #filter="{ filterModel, filterCallback }">
                            <InputText
                                v-model="filterModel.value"
                                type="text"
                                class="p-column-filter"
                                placeholder="Rechercher par version"
                                @input="filterCallback()"
                            />
                        </template>
                    </Column>

                    <!-- Colonne Type avec filtre -->
                    <Column
                        field="type_doc_fr"
                        header="Type"
                        sortable
                        style="min-width: 10rem"
                    >
                        <template #body="{ data }">
                            <div class="flex align-items-center gap-2">
                                <i class="pi pi-file-pdf text-red-500"></i>
                                <span>{{
                                    data.type_doc_fr || 'Non spécifié'
                                }}</span>
                            </div>
                        </template>
                        <template #filter="{ filterModel, filterCallback }">
                            <Dropdown
                                v-model="filterModel.value"
                                :options="
                                    documentTypes.map((type) => type.nom_fr)
                                "
                                placeholder="Tous les types"
                                class="p-column-filter"
                                :showClear="true"
                                @change="filterCallback()"
                            />
                        </template>
                    </Column>

                    <!-- Colonne Période avec filtre -->
                    <Column
                        header="Période"
                        sortable
                        :sortField="'date_debut'"
                        style="min-width: 12rem"
                    >
                        <template #body="{ data }">
                            <div class="flex flex-column">
                                <span class="font-medium">{{
                                    formatDate(data.date_debut) || 'Non définie'
                                }}</span>
                                <span class="text-sm text-color-secondary"
                                    >au
                                    {{
                                        formatDate(data.date_fin) ||
                                        'Non définie'
                                    }}</span
                                >
                            </div>
                        </template>
                        <template #filter="{ filterModel, filterCallback }">
                            <Calendar
                                v-model="filterModel.value"
                                dateFormat="dd/mm/yy"
                                placeholder="Filtrer par date"
                                class="p-column-filter"
                                @date-select="filterCallback()"
                                showIcon
                                :showClear="true"
                            />
                        </template>
                    </Column>

                    <!-- Colonne Statut avec filtre -->
                    <Column
                        field="computedStatus"
                        header="Statut"
                        sortable
                        style="min-width: 8rem"
                    >
                        <template #body="{ data }">
                            <Tag
                                :value="data.computedStatus"
                                :severity="
                                    getStatusSeverity(data.computedStatus)
                                "
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
                                        :severity="
                                            getStatusSeverity(
                                                slotProps.option.value
                                            )
                                        "
                                        :icon="
                                            getStatusIcon(
                                                slotProps.option.value
                                            )
                                        "
                                    />
                                </template>
                            </Dropdown>
                        </template>
                    </Column>

                    <!-- Colonne Observation avec filtre -->
                    <Column
                        field="observation_fr"
                        header="Observation"
                        sortable
                        style="min-width: 12rem"
                    >
                        <template #body="{ data }">
                            <span>{{ data.observation_fr || 'Aucune' }}</span>
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
                    <Column
                        header="Actions"
                        :exportable="false"
                        style="min-width: 120px"
                    >
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
                        <div
                            class="flex flex-column align-items-center justify-content-center p-4 gap-3"
                        >
                            <i class="pi pi-file-pdf text-5xl text-400"></i>
                            <span class="text-600 font-medium"
                                >Aucun document disponible</span
                            >
                            <Button
                                label="Ajouter un document"
                                icon="pi pi-plus"
                                class="p-button-text"
                                @click="showAddDialog = true"
                            />
                        </div>
                    </template>

                    <template #loading>
                        <div
                            class="flex flex-column align-items-center justify-content-center p-4 gap-3"
                        >
                            <ProgressSpinner
                                style="width: 40px; height: 40px"
                            />
                            <span class="text-600 font-medium"
                                >Chargement des données...</span
                            >
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
                header: {
                    class: 'surface-50 border-bottom-1 surface-border compact-header',
                },
                content: { class: 'surface-50 py-0' },
                footer: { class: 'surface-50 border-top-1 surface-border' },
            }"
        >
            <template #header>
                <div
                    class="flex align-items-center justify-content-between w-full"
                >
                    <div class="flex align-items-center gap-2">
                        <i
                            :class="editMode ? 'pi pi-pencil' : 'pi pi-plus'"
                            class="text-primary"
                        ></i>
                        <span class="font-bold text-lg">{{
                            editMode
                                ? 'Modifier un document'
                                : 'Ajouter un document'
                        }}</span>
                    </div>
                    <Button
                        icon="pi pi-times"
                        class="p-button-text p-button-rounded p-button-plain absolute right-0 top-0 mr-2 mt-2"
                        @click="closeDialog"
                    />
                </div>
            </template>

            <div class="p-fluid formgrid grid p-5 gap-3">
                <!-- Version et Type sur la même ligne, même largeur -->
                <div class="flex flex-wrap gap-3 w-full">
                    <div class="field flex-1">
                        <label for="version" class="block font-medium mb-2">
                            Version <span class="text-red-500">*</span>
                        </label>
                        <InputText
                            id="version"
                            v-model="documentForm.version"
                            placeholder="Ex: 1.0"
                            class="w-full"
                            :class="{
                                'p-invalid': submitted && !documentForm.version,
                            }"
                        />
                        <small
                            v-if="submitted && !documentForm.version"
                            class="p-error"
                            >Version requise</small
                        >
                    </div>
                    <div class="field flex-1">
                        <label for="type_doc_fr" class="block font-medium mb-2">
                            Type <span class="text-red-500">*</span>
                        </label>
                        <Dropdown
                            id="type_doc_fr"
                            v-model="documentForm.type_doc_fr"
                            :options="documentTypes"
                            optionLabel="nom_fr"
                            optionValue="nom_fr"
                            placeholder="Sélectionner un type"
                            class="w-full"
                            :class="{
                                'p-invalid':
                                    submitted && !documentForm.type_doc_fr,
                            }"
                        />
                        <small
                            v-if="submitted && !documentForm.type_doc_fr"
                            class="p-error"
                            >Type requis</small
                        >
                    </div>
                </div>

                <!-- Champ Fichier amélioré -->
                <div class="field col-12">
                    <label for="fichier" class="block font-medium mb-2">
                        Fichier PDF <span class="text-red-500">*</span>
                    </label>
                    <div class="p-inputgroup">
                        <InputText
                            :value="currentFileName"
                            placeholder="Aucun fichier sélectionné"
                            class="w-full"
                            readonly
                            :class="{
                                'p-invalid':
                                    fileError ||
                                    (submitted &&
                                        !fileToUpload &&
                                        !currentDocument?.fichier),
                            }"
                        />
                        <Button
                            icon="pi pi-upload"
                            class="p-button-primary"
                            @click="$refs.fileUpload.click()"
                        />
                        <Button
                            v-if="currentDocument?.fichier"
                            icon="pi pi-download"
                            class="p-button-success"
                            @click="downloadDocument(currentDocument)"
                            v-tooltip="'Télécharger le fichier actuel'"
                        />
                        <input
                            ref="fileUpload"
                            type="file"
                            accept=".pdf"
                            style="display: none"
                            @change="onFileSelect($event)"
                        />
                    </div>
                    <small v-if="fileError" class="p-error">{{
                        fileError
                    }}</small>
                    <small
                        v-if="
                            submitted &&
                            !fileToUpload &&
                            !currentDocument?.fichier
                        "
                        class="p-error"
                        >Fichier PDF requis</small
                    >
                </div>

                <!-- Date début et Date fin sur la même ligne, même largeur -->
                <div class="flex flex-wrap gap-3 w-full">
                    <div class="field flex-1">
                        <label for="date_debut" class="block font-medium mb-2"
                            >Date début</label
                        >
                        <Calendar
                            id="date_debut"
                            v-model="documentForm.date_debut"
                            dateFormat="dd/mm/yy"
                            showIcon
                            showButtonBar
                            placeholder="Sélectionner une date"
                            class="w-full"
                        />
                    </div>
                    <div class="field flex-1">
                        <label for="date_fin" class="block font-medium mb-2"
                            >Date fin</label
                        >
                        <Calendar
                            id="date_fin"
                            v-model="documentForm.date_fin"
                            dateFormat="dd/mm/yy"
                            showIcon
                            showButtonBar
                            placeholder="Sélectionner une date"
                            class="w-full"
                            :minDate="documentForm.date_debut"
                        />
                    </div>
                </div>

                <!-- Statut avec RadioButton pour sélection unique -->
                <div class="field col-12">
                    <label class="block font-medium mb-2"
                        >Statut <span class="text-red-500">*</span></label
                    >
                    <div class="flex flex-wrap gap-3">
                        <div
                            v-for="option in statutOptions"
                            :key="option.value"
                            class="flex align-items-center"
                        >
                            <RadioButton
                                v-model="documentForm.statut_fr"
                                :inputId="
                                    'statut_' + option.value.toLowerCase()
                                "
                                name="statut"
                                :value="option.value"
                                :class="{
                                    'p-invalid':
                                        submitted && !documentForm.statut_fr,
                                }"
                            />
                            <label
                                :for="'statut_' + option.value.toLowerCase()"
                                class="ml-2"
                                >{{ option.label }}</label
                            >
                        </div>
                    </div>
                    <small
                        v-if="submitted && !documentForm.statut_fr"
                        class="p-error"
                        >Statut requis</small
                    >
                </div>

                <!-- Observation -->
                <div class="field col-12">
                    <label for="observation_fr" class="block font-medium mb-2"
                        >Observation</label
                    >
                    <Textarea
                        id="observation_fr"
                        v-model="documentForm.observation_fr"
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
                header: {
                    class: 'surface-100 border-bottom-1 surface-border p-4',
                },
                content: { class: 'surface-100 p-5' },
                footer: {
                    class: 'surface-100 border-top-1 surface-border p-4',
                },
            }"
        >
            <template #header>
                <div class="flex align-items-center gap-2">
                    <i class="pi pi-exclamation-triangle text-red-500"></i>
                    <span class="font-bold text-lg"
                        >Confirmer la suppression</span
                    >
                </div>
            </template>
            <div class="flex align-items-center gap-3">
                <i class="pi pi-exclamation-circle text-red-500 text-2xl"></i>
                <span
                    >Voulez-vous vraiment supprimer ce document ? Cette action
                    est irréversible.</span
                >
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
                header: {
                    class: 'surface-50 border-bottom-1 surface-border p-3',
                },
                content: { class: 'p-0 h-full' },
                footer: { class: 'surface-50 border-top-1 surface-border p-3' },
            }"
        >
            <template #header>
                <div class="flex align-items-center gap-2">
                    <i class="pi pi-file-pdf text-red-500 text-xl"></i>
                    <span class="font-bold text-lg">{{
                        previewDocumentName || 'Prévisualisation du document'
                    }}</span>
                </div>
            </template>
            <div
                v-if="previewLoading"
                class="flex justify-content-center align-items-center h-full"
            >
                <ProgressSpinner style="width: 50px; height: 50px" />
                <span class="ml-3 text-600">Chargement du PDF...</span>
            </div>
            <div
                v-else-if="previewError"
                class="flex justify-content-center align-items-center h-full flex-column gap-3"
            >
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
                    @click="showPreviewDialog = false"
                />
            </template>
        </Dialog>

        <Toast position="top-right" />
    </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
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
import Calendar from 'primevue/calendar';
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
        Calendar,
        Textarea,
        Toast,
        ProgressSpinner,
        Toolbar,
        Panel,
        Badge,
        RadioButton,
    },
    props: {
        specialite: {
            type: Object,
            required: true,
        },
    },
    emits: ['close', 'refresh'],
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
        const previewDocumentUrl = ref(null);
        const previewDocumentName = ref('');
        const previewLoading = ref(false);
        const previewError = ref(null);

        const documentTypes = ref([
            { nom_fr: "Programme d'étude", nom_ar: 'برنامج التكوين' },
            { nom_fr: "Dossier d'homologation", nom_ar: 'ملف التنظير' },
            { nom_fr: 'Guide pédagogique', nom_ar: 'الدليل البيداغوجي' },
            { nom_fr: "Guide d'évaluation", nom_ar: 'دليل التقييم' },
            { nom_fr: "Guide d'organisation", nom_ar: 'دليل التنظيم' },
            { nom_fr: 'Guide matériels', nom_ar: 'دليل التجهيزات' },
        ]);

        const statusMap = ref({
            Actif: { fr: 'Actif', ar: 'نشط' },
            Inactif: { fr: 'Inactif', ar: 'غير نشط' },
            Annulé: { fr: 'Annulé', ar: 'ملغى' },
        });

        const statutOptions = ref([
            { label: 'Actif', value: 'Actif' },
            { label: 'Inactif', value: 'Inactif' },
            { label: 'Annulé', value: 'Annulé' },
        ]);

        const documentForm = ref({
            version: '',
            type_doc_fr: '',
            fichier: null,
            date_debut: null,
            date_fin: null,
            statut_fr: 'Actif',
            observation_fr: '',
            observation_ar: '',
        });

        const filters = ref({
            global: { value: null, matchMode: FilterMatchMode.CONTAINS },
            version: {
                operator: FilterOperator.AND,
                constraints: [
                    { value: null, matchMode: FilterMatchMode.CONTAINS },
                ],
            },
            type_doc_fr: {
                operator: FilterOperator.AND,
                constraints: [
                    { value: null, matchMode: FilterMatchMode.EQUALS },
                ],
            },
            date_debut: {
                operator: FilterOperator.AND,
                constraints: [
                    { value: null, matchMode: FilterMatchMode.DATE_IS },
                ],
            },
            computedStatus: {
                operator: FilterOperator.AND,
                constraints: [
                    { value: null, matchMode: FilterMatchMode.EQUALS },
                ],
            },
            observation_fr: {
                operator: FilterOperator.AND,
                constraints: [
                    { value: null, matchMode: FilterMatchMode.CONTAINS },
                ],
            },
        });

        const documentCount = computed(() => documents.value.length);

        const currentFileName = computed(() => {
            if (fileToUpload.value) return fileToUpload.value.name;
            if (currentDocument.value?.fichier) {
                return currentDocument.value.fichier.split('/').pop();
            }
            return '';
        });

        const processedDocuments = computed(() => {
            return documents.value.map((doc) => {
                const today = new Date();
                const dateFin = doc.date_fin ? new Date(doc.date_fin) : null;
                let computedStatus = doc.statut_fr;

                if (doc.statut_fr === 'Actif' && dateFin && dateFin < today) {
                    computedStatus = 'Inactif';
                } else if (doc.statut_fr === 'Annulé') {
                    computedStatus = 'Annulé';
                }

                return {
                    ...doc,
                    computedStatus,
                };
            });
        });

        const clearFilters = () => {
            initFilters();
        };

        const initFilters = () => {
            filters.value = {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                version: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.CONTAINS },
                    ],
                },
                type_doc_fr: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.EQUALS },
                    ],
                },
                date_debut: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.DATE_IS },
                    ],
                },
                computedStatus: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.EQUALS },
                    ],
                },
                observation_fr: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.CONTAINS },
                    ],
                },
            };
        };

        const fetchDocuments = async () => {
            if (!props.specialite?.id) {
                toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'ID de spécialité invalide.',
                    life: 3000,
                });
                return;
            }
            loading.value = true;
            try {
                const response = await axios.get(
                    `/api/documents?specialite_id=${props.specialite.id}`
                );
                documents.value = response.data.data || [];
            } catch (error) {
                console.error(
                    'Erreur lors du chargement des documents:',
                    error
                );
                toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Impossible de charger les documents.',
                    life: 3000,
                });
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
            try {
                previewLoading.value = true;
                previewError.value = null;
                previewDocumentName.value = `${doc.type_doc_fr || 'Document'} - v${doc.version}`;
                const response = await axios.get(
                    `/api/documents/${doc.id}/download`,
                    {
                        responseType: 'blob',
                    }
                );
                const blob = new Blob([response.data], {
                    type: 'application/pdf',
                });
                previewDocumentUrl.value = URL.createObjectURL(blob);
                showPreviewDialog.value = true;
            } catch (error) {
                console.error(
                    'Erreur lors de la prévisualisation du document:',
                    error
                );
                previewError.value = 'Impossible de charger le document PDF.';
                toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Impossible de prévisualiser le document',
                    life: 3000,
                });
            } finally {
                previewLoading.value = false;
            }
        };

        const downloadDocument = async (doc) => {
            try {
                const response = await axios.get(
                    `/api/documents/${doc.id}/download`,
                    {
                        responseType: 'blob',
                    }
                );
                const url = window.URL.createObjectURL(
                    new Blob([response.data])
                );
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute(
                    'download',
                    doc.fichier.split('/').pop() || 'document.pdf'
                );
                document.body.appendChild(link);
                link.click();
                link.remove();
                window.URL.revokeObjectURL(url);
            } catch (error) {
                toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Impossible de télécharger le document',
                    life: 3000,
                });
            }
        };

        const saveDocument = async () => {
            submitted.value = true;

            // Validate required fields
            if (
                !documentForm.value.version ||
                !documentForm.value.type_doc_fr ||
                !documentForm.value.statut_fr ||
                (!fileToUpload.value && !currentDocument.value?.fichier)
            ) {
                toast.add({
                    severity: 'warn',
                    summary: 'Validation',
                    detail: 'Veuillez remplir tous les champs obligatoires.',
                    life: 3000,
                });
                return;
            }

            const formData = new FormData();
            formData.append('version', documentForm.value.version);
            formData.append('type_doc_fr', documentForm.value.type_doc_fr);
            formData.append('statut_fr', documentForm.value.statut_fr);

            // Map type_doc_ar and statut_ar
            const typeDocAr =
                documentTypes.value.find(
                    (type) => type.nom_fr === documentForm.value.type_doc_fr
                )?.nom_ar || '';
            const statutAr =
                statusMap.value[documentForm.value.statut_fr]?.ar || '';

            formData.append('type_doc_ar', typeDocAr);
            formData.append('statut_ar', statutAr);

            if (documentForm.value.date_debut) {
                formData.append(
                    'date_debut',
                    formatDateForApi(documentForm.value.date_debut)
                );
            }
            if (documentForm.value.date_fin) {
                formData.append(
                    'date_fin',
                    formatDateForApi(documentForm.value.date_fin)
                );
            }
            if (documentForm.value.observation_fr) {
                formData.append(
                    'observation_fr',
                    documentForm.value.observation_fr
                );
            }
            if (documentForm.value.observation_ar) {
                formData.append(
                    'observation_ar',
                    documentForm.value.observation_ar
                );
            }
            if (fileToUpload.value) {
                formData.append('fichier', fileToUpload.value);
            }
            formData.append('specialite_id', props.specialite.id);

            try {
                if (editMode.value) {
                    await axios.post(
                        `/api/documents/${currentDocument.value.id}?_method=PUT`,
                        formData,
                        {
                            headers: { 'Content-Type': 'multipart/form-data' },
                        }
                    );
                    toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Document mis à jour avec succès',
                        life: 3000,
                    });
                } else {
                    await axios.post('/api/documents', formData, {
                        headers: { 'Content-Type': 'multipart/form-data' },
                    });
                    toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Document ajouté avec succès',
                        life: 3000,
                    });
                }
                closeDialog();
                await fetchDocuments();
                emit('refresh');
            } catch (error) {
                console.error(
                    'Validation error details:',
                    error.response?.data
                );
                const errorMessage =
                    error.response?.data?.message ||
                    'Erreur lors de la sauvegarde du document';
                toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: errorMessage,
                    life: 3000,
                });
            }
        };

        const editDocument = (doc) => {
            currentDocument.value = doc;
            editMode.value = true;
            documentForm.value = {
                version: doc.version,
                type_doc_fr: doc.type_doc_fr,
                fichier: doc.fichier,
                date_debut: doc.date_debut ? new Date(doc.date_debut) : null,
                date_fin: doc.date_fin ? new Date(doc.date_fin) : null,
                statut_fr: doc.statut_fr || 'Actif',
                observation_fr: doc.observation_fr || '',
                observation_ar: doc.observation_ar || '',
            };
            showAddDialog.value = true;
            submitted.value = false;
        };

        const confirmDeleteDocument = (doc) => {
            currentDocument.value = doc;
            showDeleteDialog.value = true;
        };

        const deleteDocument = async () => {
            try {
                await axios.delete(
                    `/api/documents/${currentDocument.value.id}`
                );
                toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Document supprimé avec succès',
                    life: 3000,
                });
                showDeleteDialog.value = false;
                await fetchDocuments();
                emit('refresh');
            } catch (error) {
                toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Impossible de supprimer le document',
                    life: 3000,
                });
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
                version: '',
                type_doc_fr: '',
                fichier: null,
                date_debut: null,
                date_fin: null,
                statut_fr: 'Actif',
                observation_fr: '',
                observation_ar: '',
            };
        };

        const formatDate = (dateString) => {
            if (!dateString) return null;
            const date = new Date(dateString);
            return date.toLocaleDateString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
            });
        };

        const formatDateForApi = (date) => {
            if (!date) return null;
            if (typeof date === 'string') date = new Date(date);
            if (!(date instanceof Date)) return null;
            return date.toISOString().split('T')[0];
        };

        const getStatusSeverity = (status) => {
            switch (status) {
                case 'Actif':
                    return 'success';
                case 'Inactif':
                    return 'warning';
                case 'Annulé':
                    return 'danger';
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
                case 'Annulé':
                    return 'pi pi-times-circle';
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

        onMounted(() => {
            fetchDocuments();
            initFilters();
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
            fileError,
            submitted,
            filters,
            documentCount,
            currentFileName,
            previewDocumentUrl,
            previewDocumentName,
            previewLoading,
            previewError,
            onFileSelect,
            saveDocument,
            downloadDocument,
            previewDocument,
            editDocument,
            confirmDeleteDocument,
            deleteDocument,
            closeDialog,
            formatDate,
            getStatusSeverity,
            getStatusIcon,
            getStandardizationSeverity,
            clearFilters,
        };
    },
};
</script>

<style scoped lang="scss">
.document-manager-container {
    display: flex;
    flex-direction: column;
    height: 100%;
    padding: 1.5rem;
    gap: 1.5rem;
    background-color: var(--surface-0);

    /* Styles pour la barre d'outils */
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

    /* Styles pour le panneau de détails */
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

    /* Styles pour la carte contenant la table */
    .p-card {
        border-radius: 6px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        overflow: hidden;

        .p-card-body {
            padding: 1.5rem;
        }
    }

    /* Styles pour la table */
    .modern-table {
        :deep(.p-datatable) {
            border-radius: 6px;
            overflow: hidden;

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

            .p-datatable-footer {
                background-color: var(--surface-50);
                border-top: 1px solid var(--surface-border);
                padding: 1rem;
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

    /* Styles pour les dialogues */
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
                .p-dropdown,
                .p-calendar {
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

                .arabic-text {
                    direction: rtl;
                    text-align: right;
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

        .file-icon {
            font-size: 1rem;
            color: var(--text-color-secondary);
            cursor: pointer;
            &:hover {
                color: var(--primary-color);
            }
        }
    }

    /* Styles pour le champ de fichier */
    .file-upload-container {
        .p-inputtext[readonly] {
            background-color: var(--surface-100);
            cursor: default;
        }

        .p-button {
            padding: 0.5rem 1rem;
        }
    }

    /* Styles pour la prévisualisation */
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

    /* Styles pour les toasts */
    .p-toast {
        .p-toast-message {
            border-radius: 4px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }
    }

    /* Styles pour les états vides/chargement */
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

    /* Responsive adjustments */
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

    /* Animations */
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
