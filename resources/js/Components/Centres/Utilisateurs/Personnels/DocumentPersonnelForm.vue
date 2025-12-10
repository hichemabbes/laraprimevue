```vue
<template>
    <div>
        <!-- Formulaire pour Ajouter un Document -->
        <Dialog
            :visible="visible"
            @update:visible="handleVisibleUpdate"
            :modal="true"
            :focusOnShow="false"
            :style="{ width: '90vw', maxWidth: '1200px' }"
            :breakpoints="{ '960px': '98vw', '640px': '100vw' }"
            class="p-fluid"
            :pt="{
                root: 'border-round-xl shadow-5',
                mask: { style: 'backdrop-filter: blur(4px)' },
                header: { class: 'surface-50 border-bottom-1 surface-border p-4' },
                content: { class: 'surface-ground p-4' },
                footer: { class: 'surface-50 border-top-1 surface-border p-4' }
            }"
        >
            <template #header>
                <div class="flex align-items-center gap-2">
                    <i class="pi pi-file text-primary text-2xl"></i>
                    <span class="font-bold text-2xl">
                        {{ isEditMode ? 'Modifier le Document' : 'Ajouter un Document' }}
                    </span>
                </div>
            </template>
            <div v-if="loading" class="flex flex-column align-items-center justify-content-center gap-3 p-6">
                <ProgressSpinner style="width: 50px; height: 50px" strokeWidth="4" />
                <span class="text-color-secondary">Chargement des données...</span>
            </div>
            <div v-else class="surface-card p-4 shadow-2 border-round">
                <TabView>
                    <TabPanel header="Détails du Document">
                        <form @submit.prevent="submitForm">
                            <div class="grid p-fluid">
                                <div class="col-12 field">
                                    <span class="font-bold text-lg text-blue-700">
                                        {{ isEditMode ? `Modification du document de ${personnelFullName}` : '' }}
                                    </span>
                                </div>
                                <div class="col-12 md:col-6 field">
                                    <label for="type_document_personnel_fr" class="block font-medium mb-2" style="font-size: 0.875rem">
                                        Type de Document <span class="text-red-500">*</span>
                                    </label>
                                    <Dropdown
                                        id="type_document_personnel_fr"
                                        v-model="form.type_document_personnel_fr"
                                        :options="typeDocuments"
                                        optionLabel="nom_fr"
                                        optionValue="valeur"
                                        placeholder="Sélectionner un type de document"
                                        :class="{ 'p-invalid': errors.type_document_personnel_fr }"
                                        :loading="loadingLists"
                                        style="font-size: 0.875rem; border: 2px solid blue;"
                                        :key="dropdownKey"
                                    />
                                    <small v-if="errors.type_document_personnel_fr" class="p-error" style="font-size: 0.75rem">{{ errors.type_document_personnel_fr }}</small>
                                </div>
                                <div class="col-12 md:col-6 field">
                                    <label for="validite_fr" class="block font-medium mb-2" style="font-size: 0.875rem">
                                        Validité
                                    </label>
                                    <Dropdown
                                        id="validite_fr"
                                        v-model="form.validite_fr"
                                        :options="validiteOptions"
                                        optionLabel="label"
                                        optionValue="value"
                                        placeholder="Sélectionner une validité"
                                        :class="{ 'p-invalid': errors.validite_fr }"
                                        style="font-size: 0.875rem"
                                    />
                                    <small v-if="errors.validite_fr" class="p-error" style="font-size: 0.75rem">{{ errors.validite_fr }}</small>
                                </div>
                                <div class="col-12 md:col-6 field">
                                    <label for="depot_fr" class="block font-medium mb-2" style="font-size: 0.875rem">
                                        Mode de Dépôt
                                    </label>
                                    <Dropdown
                                        id="depot_fr"
                                        v-model="form.depot_fr"
                                        :options="depotOptions"
                                        optionLabel="label"
                                        optionValue="value"
                                        placeholder="Sélectionner un mode de dépôt"
                                        :class="{ 'p-invalid': errors.depot_fr }"
                                        style="font-size: 0.875rem"
                                    />
                                    <small v-if="errors.depot_fr" class="p-error" style="font-size: 0.75rem">{{ errors.depot_fr }}</small>
                                </div>
                                <div class="col-12 md:col-6 field">
                                    <label for="date_depot" class="block font-medium mb-2" style="font-size: 0.875rem">
                                        Date de Dépôt
                                    </label>
                                    <Calendar
                                        id="date_depot"
                                        v-model="form.date_depot"
                                        :class="{ 'p-invalid': errors.date_depot }"
                                        dateFormat="yy-mm-dd"
                                        showIcon
                                        placeholder="Sélectionner une date"
                                        style="font-size: 0.875rem"
                                    />
                                    <small v-if="errors.date_depot" class="p-error" style="font-size: 0.75rem">{{ errors.date_depot }}</small>
                                </div>
                                <div class="col-12 md:col-6 field">
                                    <label for="statut" class="block font-medium mb-2" style="font-size: 0.875rem">
                                        Statut
                                    </label>
                                    <Dropdown
                                        id="statut"
                                        v-model="form.statut"
                                        :options="statutOptions"
                                        optionLabel="label"
                                        optionValue="value"
                                        placeholder="Sélectionner un statut"
                                        :class="{ 'p-invalid': errors.statut }"
                                        style="font-size: 0.875rem"
                                    />
                                    <small v-if="errors.statut" class="p-error" style="font-size: 0.75rem">{{ errors.statut }}</small>
                                </div>
                                <div class="col-12 md:col-6 field">
                                    <label for="chemin_fichier" class="block font-medium mb-2" style="font-size: 0.875rem">
                                        Fichier
                                    </label>
                                    <FileUpload
                                        ref="fileUploadRef"
                                        name="chemin_fichier"
                                        accept="application/pdf,image/png,image/jpeg"
                                        :maxFileSize="5000000"
                                        :customUpload="true"
                                        chooseLabel="Choisir"
                                        :showUploadButton="false"
                                        cancelLabel="Annuler"
                                        @select="handleFileSelect"
                                        @clear="handleClear"
                                        :class="{ 'p-invalid': errors.chemin_fichier }"
                                        style="font-size: 0.875rem"
                                    >
                                        <template #empty>
                                            <p>
                                                Glissez-déposez un fichier ici ou cliquez pour sélectionner (PDF, PNG, JPEG, max 5Mo).
                                            </p>
                                        </template>
                                    </FileUpload>
                                    <small v-if="errors.chemin_fichier" class="p-error" style="font-size: 0.75rem">{{ errors.chemin_fichier }}</small>
                                </div>
                            </div>
                        </form>
                    </TabPanel>
                    <TabPanel header="Description et Observation">
                        <div class="grid p-fluid">
                            <div class="col-12 field">
                                <label for="description" class="block font-medium mb-2" style="font-size: 0.875rem">
                                    Description
                                </label>
                                <Textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="4"
                                    :class="{ 'p-invalid': errors.description }"
                                    placeholder="Entrez une description"
                                    style="width: 100%; font-size: 0.875rem"
                                />
                                <small v-if="errors.description" class="p-error" style="font-size: 0.75rem">{{ errors.description }}</small>
                            </div>
                            <div class="col-12 field">
                                <label for="observation" class="block font-medium mb-2" style="font-size: 0.875rem">
                                    Observation
                                </label>
                                <Textarea
                                    id="observation"
                                    v-model="form.observation"
                                    rows="4"
                                    :class="{ 'p-invalid': errors.observation }"
                                    placeholder="Entrez vos observations"
                                    style="width: 100%; font-size: 0.875rem"
                                />
                                <small v-if="errors.observation" class="p-error" style="font-size: 0.75rem">{{ errors.observation }}</small>
                            </div>
                        </div>
                    </TabPanel>
                    <TabPanel header="Liste des Documents">
                        <div class="flex justify-content-between align-items-center mb-4">
                            <div class="flex align-items-center gap-2">
                                <span class="font-bold text-lg">
                                    Liste des documents de <span class="text-primary">{{ personnelFullName }}</span>
                                </span>
                            </div>
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
                        </div>
                        <DataTable
                            v-model:filters="filters"
                            :value="documents"
                            :loading="loadingDocuments"
                            dataKey="id"
                            size="small"
                            stripedRows
                            paginator
                            :rows="10"
                            :rowsPerPageOptions="[10, 20, 50]"
                            filterDisplay="menu"
                            :globalFilterFields="[
                                'type_document_personnel_fr',
                                'type_document_personnel_ar',
                                'date_depot',
                                'validite_fr',
                                'statut'
                            ]"
                            scrollable
                            scrollHeight="600px"
                            removableSort
                            class="p-datatable-sm border-round-lg"
                        >
                            <template #empty>
                                <div class="text-center py-4">
                                    <i class="pi pi-inbox text-4xl text-400 mb-2" />
                                    <p class="text-600">Aucun document trouvé</p>
                                </div>
                            </template>
                            <Column
                                field="type_document_personnel_fr"
                                header="Type (Français)"
                                sortable
                                style="min-width: 12rem"
                            >
                                <template #body="{ data }">
                                    <span class="font-medium">{{ data.type_document_personnel_fr }}</span>
                                </template>
                                <template #filter="{ filterModel, filterCallback }">
                                    <InputText
                                        v-model="filterModel.value"
                                        type="text"
                                        class="p-column-filter"
                                        placeholder="Rechercher par type (Français)"
                                        @input="filterCallback"
                                    />
                                </template>
                            </Column>
                            <Column
                                field="type_document_personnel_ar"
                                header="Type (Arabe)"
                                sortable
                                style="min-width: 12rem"
                                class="arabic-text"
                            >
                                <template #body="{ data }">
                                    <div class="flex align-items-center gap-2">
                                        <i class="pi pi-tag text-primary-500" />
                                        {{ data.type_document_personnel_ar || 'غير محدد' }}
                                    </div>
                                </template>
                                <template #filter="{ filterModel, filterCallback }">
                                    <InputText
                                        v-model="filterModel.value"
                                        type="text"
                                        class="p-column-filter arabic-text"
                                        placeholder="البحث حسب النوع (العربية)"
                                        @input="filterCallback"
                                    />
                                </template>
                            </Column>
                            <Column
                                field="date_depot"
                                header="Date de Dépôt"
                                sortable
                                style="min-width: 10rem"
                            >
                                <template #body="{ data }">
                                    <Tag
                                        :value="formatDate(data.date_depot)"
                                        icon="pi pi-calendar"
                                    />
                                </template>
                            </Column>
                            <Column
                                field="validite_fr"
                                header="Validité"
                                sortable
                                style="min-width: 10rem"
                            >
                                <template #body="{ data }">
                                    <Tag
                                        :value="data.validite_fr"
                                        :severity="getSeverity(data.validite_fr)"
                                        :icon="getStatutIcon(data.validite_fr)"
                                    />
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
                                        :value="data.statut"
                                        :severity="getSeverity(data.statut)"
                                        :icon="getStatutIcon(data.statut)"
                                    />
                                </template>
                            </Column>
                            <Column header="Actions" style="min-width: 10rem">
                                <template #body="slotProps">
                                    <Button
                                        icon="pi pi-pencil"
                                        class="p-button-rounded p-button-success p-button-text"
                                        @click="openEditDialog(slotProps.data)"
                                        v-tooltip="'Modifier'"
                                    />
                                    <Button
                                        icon="pi pi-trash"
                                        class="p-button-rounded p-button-danger p-button-text"
                                        @click="deleteDocument(slotProps.data)"
                                        v-tooltip="'Supprimer'"
                                    />
                                    <Button
                                        icon="pi pi-download"
                                        class="p-button-rounded p-button-info p-button-text"
                                        @click="downloadDocument(slotProps.data.id)"
                                        v-tooltip="'Télécharger'"
                                    />
                                </template>
                            </Column>
                        </DataTable>
                    </TabPanel>
                </TabView>
            </div>
            <template #footer>
                <div class="flex justify-content-end gap-3">
                    <Button
                        label="Annuler"
                        icon="pi pi-times-circle"
                        class="p-button-outlined p-button-secondary p-button-sm"
                        @click="handleCancel"
                        style="font-size: 0.875rem"
                    />
                    <Button
                        :label="isEditMode ? 'Modifier' : 'Enregistrer'"
                        icon="pi pi-check-circle"
                        class="p-button-primary p-button-sm"
                        @click="submitForm"
                        :loading="submitting"
                        style="font-size: 0.875rem"
                    />
                </div>
            </template>
        </Dialog>

        <!-- Formulaire pour Modifier un Document -->
        <Dialog
            :visible="editDialogVisible"
            @update:visible="closeEditDialog"
            :modal="true"
            :focusOnShow="false"
            :style="{ width: '90vw', maxWidth: '1200px' }"
            :breakpoints="{ '960px': '98vw', '640px': '100vw' }"
            class="p-fluid"
            :pt="{
                root: 'border-round-xl shadow-5',
                mask: { style: 'backdrop-filter: blur(4px)' },
                header: { class: 'surface-50 border-bottom-1 surface-border p-4' },
                content: { class: 'surface-ground p-4' },
                footer: { class: 'surface-50 border-top-1 surface-border p-4' }
            }"
        >
            <template #header>
                <div class="flex align-items-center gap-2">
                    <i class="pi pi-file-edit text-primary text-2xl"></i>
                    <span class="font-bold text-2xl">Modifier le Document</span>
                </div>
            </template>
            <div v-if="loading" class="flex flex-column align-items-center justify-content-center gap-3 p-6">
                <ProgressSpinner style="width: 50px; height: 50px" strokeWidth="4" />
                <span class="text-color-secondary">Chargement des données...</span>
            </div>
            <div v-else class="surface-card p-4 shadow-2 border-round">
                <TabView>
                    <TabPanel header="Détails du Document">
                        <form @submit.prevent="submitEditForm">
                            <div class="grid p-fluid">
                                <div class="col-12 field">
                                    <span class="font-bold text-lg text-blue-700">
                                        Modification du document de {{ personnelFullName }}
                                    </span>
                                </div>
                                <div class="col-12 md:col-6 field">
                                    <label for="edit_type_document_personnel_fr" class="block font-medium mb-2" style="font-size: 0.875rem">
                                        Type de Document <span class="text-red-500">*</span>
                                    </label>
                                    <Dropdown
                                        id="edit_type_document_personnel_fr"
                                        v-model="editForm.type_document_personnel_fr"
                                        :options="typeDocuments"
                                        optionLabel="nom_fr"
                                        optionValue="valeur"
                                        placeholder="Sélectionner un type de document"
                                        :class="{ 'p-invalid': editErrors.type_document_personnel_fr }"
                                        :loading="loadingLists"
                                        style="font-size: 0.875rem; border: 2px solid blue;"
                                        :key="dropdownKey"
                                    />
                                    <small v-if="editErrors.type_document_personnel_fr" class="p-error" style="font-size: 0.75rem">{{ editErrors.type_document_personnel_fr }}</small>
                                </div>
                                <div class="col-12 md:col-6 field">
                                    <label for="edit_validite_fr" class="block font-medium mb-2" style="font-size: 0.875rem">
                                        Validité
                                    </label>
                                    <Dropdown
                                        id="edit_validite_fr"
                                        v-model="editForm.validite_fr"
                                        :options="validiteOptions"
                                        optionLabel="label"
                                        optionValue="value"
                                        placeholder="Sélectionner une validité"
                                        :class="{ 'p-invalid': editErrors.validite_fr }"
                                        style="font-size: 0.875rem"
                                    />
                                    <small v-if="editErrors.validite_fr" class="p-error" style="font-size: 0.75rem">{{ editErrors.validite_fr }}</small>
                                </div>
                                <div class="col-12 md:col-6 field">
                                    <label for="edit_depot_fr" class="block font-medium mb-2" style="font-size: 0.875rem">
                                        Mode de Dépôt
                                    </label>
                                    <Dropdown
                                        id="edit_depot_fr"
                                        v-model="editForm.depot_fr"
                                        :options="depotOptions"
                                        optionLabel="label"
                                        optionValue="value"
                                        placeholder="Sélectionner un mode de dépôt"
                                        :class="{ 'p-invalid': editErrors.depot_fr }"
                                        style="font-size: 0.875rem"
                                    />
                                    <small v-if="editErrors.depot_fr" class="p-error" style="font-size: 0.75rem">{{ editErrors.depot_fr }}</small>
                                </div>
                                <div class="col-12 md:col-6 field">
                                    <label for="edit_date_depot" class="block font-medium mb-2" style="font-size: 0.875rem">
                                        Date de Dépôt
                                    </label>
                                    <Calendar
                                        id="edit_date_depot"
                                        v-model="editForm.date_depot"
                                        :class="{ 'p-invalid': editErrors.date_depot }"
                                        dateFormat="yy-mm-dd"
                                        showIcon
                                        placeholder="Sélectionner une date"
                                        style="font-size: 0.875rem"
                                    />
                                    <small v-if="editErrors.date_depot" class="p-error" style="font-size: 0.75rem">{{ editErrors.date_depot }}</small>
                                </div>
                                <div class="col-12 md:col-6 field">
                                    <label for="edit_statut" class="block font-medium mb-2" style="font-size: 0.875rem">
                                        Statut
                                    </label>
                                    <Dropdown
                                        id="edit_statut"
                                        v-model="editForm.statut"
                                        :options="statutOptions"
                                        optionLabel="label"
                                        optionValue="value"
                                        placeholder="Sélectionner un statut"
                                        :class="{ 'p-invalid': editErrors.statut }"
                                        style="font-size: 0.875rem"
                                    />
                                    <small v-if="editErrors.statut" class="p-error" style="font-size: 0.75rem">{{ editErrors.statut }}</small>
                                </div>
                                <div class="col-12 md:col-6 field">
                                    <label for="edit_chemin_fichier" class="block font-medium mb-2" style="font-size: 0.875rem">
                                        Fichier
                                    </label>
                                    <FileUpload
                                        ref="editFileUploadRef"
                                        name="edit_chemin_fichier"
                                        accept="application/pdf,image/png,image/jpeg"
                                        :maxFileSize="5000000"
                                        :customUpload="true"
                                        chooseLabel="Choisir"
                                        :showUploadButton="false"
                                        cancelLabel="Annuler"
                                        @select="handleEditFileSelect"
                                        @clear="handleEditClear"
                                        :class="{ 'p-invalid': editErrors.chemin_fichier }"
                                        style="font-size: 0.875rem"
                                    >
                                        <template #empty>
                                            <p>
                                                Glissez-déposez un fichier ici ou cliquez pour sélectionner (PDF, PNG, JPEG, max 5Mo).
                                            </p>
                                        </template>
                                    </FileUpload>
                                    <small v-if="editErrors.chemin_fichier" class="p-error" style="font-size: 0.75rem">{{ editErrors.chemin_fichier }}</small>
                                </div>
                            </div>
                        </form>
                    </TabPanel>
                    <TabPanel header="Description et Observation">
                        <div class="grid p-fluid">
                            <div class="col-12 field">
                                <label for="edit_description" class="block font-medium mb-2" style="font-size: 0.875rem">
                                    Description
                                </label>
                                <Textarea
                                    id="edit_description"
                                    v-model="editForm.description"
                                    rows="4"
                                    :class="{ 'p-invalid': editErrors.description }"
                                    placeholder="Entrez une description"
                                    style="width: 100%; font-size: 0.875rem"
                                />
                                <small v-if="editErrors.description" class="p-error" style="font-size: 0.75rem">{{ editErrors.description }}</small>
                            </div>
                            <div class="col-12 field">
                                <label for="edit_observation" class="block font-medium mb-2" style="font-size: 0.875rem">
                                    Observation
                                </label>
                                <Textarea
                                    id="edit_observation"
                                    v-model="editForm.observation"
                                    rows="4"
                                    :class="{ 'p-invalid': editErrors.observation }"
                                    placeholder="Entrez vos observations"
                                    style="width: 100%; font-size: 0.875rem"
                                />
                                <small v-if="editErrors.observation" class="p-error" style="font-size: 0.75rem">{{ editErrors.observation }}</small>
                            </div>
                        </div>
                    </TabPanel>
                </TabView>
            </div>
            <template #footer>
                <div class="flex justify-content-end gap-3">
                    <Button
                        label="Annuler"
                        icon="pi pi-times-circle"
                        class="p-button-outlined p-button-secondary p-button-sm"
                        @click="closeEditDialog"
                        style="font-size: 0.875rem"
                    />
                    <Button
                        label="Modifier"
                        icon="pi pi-check-circle"
                        class="p-button-primary p-button-sm"
                        @click="submitEditForm"
                        :loading="submitting"
                        style="font-size: 0.875rem"
                    />
                </div>
            </template>
        </Dialog>
        <!-- Toast Notification -->
        <Toast position="top-right" />
    </div>
</template>

<script>
import axios from '@/axios.js';
import Button from 'primevue/button';
import Calendar from 'primevue/calendar';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import InputText from 'primevue/inputtext';
import ProgressSpinner from 'primevue/progressspinner';
import Textarea from 'primevue/textarea';
import FileUpload from 'primevue/fileupload';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Toast from 'primevue/toast';
import { FilterMatchMode } from 'primevue/api';
import { useToast } from 'primevue/usetoast';

export default {
    name: 'DocumentPersonnelForm',
    components: {
        Button,
        Calendar,
        Dialog,
        Dropdown,
        InputText,
        ProgressSpinner,
        Textarea,
        FileUpload,
        TabView,
        TabPanel,
        DataTable,
        Column,
        Tag,
        Toast
    },
    props: {
        visible: { type: Boolean, default: false },
        documentToEdit: { type: Object, default: null },
        personnelId: { type: Number, required: true },
        isCentreRole: { type: Boolean, default: false }
    },
    data() {
        return {
            form: {
                id: null,
                personnel_id: this.personnelId,
                type_document_personnel_fr: null,
                type_document_personnel_ar: '',
                chemin_fichier: null,
                date_depot: null,
                validite_fr: 'Valide',
                validite_ar: 'صالح',
                depot_fr: null,
                depot_ar: '',
                description: '',
                observation: '',
                statut: 'Actif'
            },
            editForm: {
                id: null,
                personnel_id: this.personnelId,
                type_document_personnel_fr: null,
                type_document_personnel_ar: '',
                chemin_fichier: null,
                date_depot: null,
                validite_fr: 'Valide',
                validite_ar: 'صالح',
                depot_fr: null,
                depot_ar: '',
                description: '',
                observation: '',
                statut: 'Actif'
            },
            errors: {},
            editErrors: {},
            filters: null,
            loading: false,
            loadingLists: false,
            loadingDocuments: false,
            submitting: false,
            editDialogVisible: false,
            documents: [],
            typeDocuments: [
                { nom_fr: 'Copie CIN', nom_ar: 'نسخة بطاقة التعريف', valeur: 'cin' },
                { nom_fr: 'CV', nom_ar: 'السيرة الذاتية', valeur: 'cv' },
                { nom_fr: 'Copie diplôme', nom_ar: 'نسخة الشهادة', valeur: 'diplome' },
                { nom_fr: 'Attestation de travail', nom_ar: 'شهادة عمل', valeur: 'attestation_travail' },
                { nom_fr: 'Fiche de paie', nom_ar: 'إيصال الأجر', valeur: 'fiche_paie' },
                { nom_fr: 'Contrat de travail', nom_ar: 'عقد العمل', valeur: 'contrat_travail' }
            ],
            dropdownKey: 0,
            validiteOptions: [
                { label: 'Valide', value: 'Valide', ar: 'صالح' },
                { label: 'Non Valide', value: 'Non Valide', ar: 'غير صالح' }
            ],
            depotOptions: [
                { label: "Bureau d'ordre", value: "Bureau d'ordre", ar: 'مكتب الضبط' },
                { label: 'Par Poste', value: 'Par Poste', ar: 'عبر البريد' },
                { label: 'Par mail', value: 'Par mail', ar: 'عبر البريد الإلكتروني' },
                { label: 'Par fax', value: 'Par fax', ar: 'عبر الفاكس' }
            ],
            statutOptions: [
                { label: 'Actif', value: 'Actif' },
                { label: 'Inactif', value: 'Inactif' }
            ],
            fileUploadRef: null,
            editFileUploadRef: null,
            isClearing: false,
            isEditClearing: false,
            personnelFullName: 'Nom non disponible'
        };
    },
    computed: {
        isEditMode() {
            return !!this.form.id;
        }
    },
    watch: {
        visible(newVisible) {
            console.log('Visible changed:', newVisible);
            if (newVisible) {
                this.initializeForm();
                this.fetchDocuments();
                this.fetchPersonnelName();
            }
        },
        documentToEdit: {
            handler() {
                console.log('documentToEdit changed:', this.documentToEdit);
                if (this.visible) {
                    this.initializeForm();
                }
            },
            deep: true
        },
        'form.type_document_personnel_fr'(newTypeFr) {
            console.log('type_document_personnel_fr changed:', newTypeFr);
            if (newTypeFr) {
                const selectedType = this.typeDocuments.find(opt => opt.valeur === newTypeFr);
                this.form.type_document_personnel_ar = selectedType ? selectedType.nom_ar : '';
            } else {
                this.form.type_document_personnel_ar = '';
            }
        },
        'form.validite_fr'(newValiditeFr) {
            console.log('validite_fr changed:', newValiditeFr);
            const selectedValidite = this.validiteOptions.find(opt => opt.value === newValiditeFr);
            this.form.validite_ar = selectedValidite ? selectedValidite.ar : '';
        },
        'form.depot_fr'(newDepotFr) {
            console.log('depot_fr changed:', newDepotFr);
            const selectedDepot = this.depotOptions.find(opt => opt.value === newDepotFr);
            this.form.depot_ar = selectedDepot ? selectedDepot.ar : '';
        },
        'editForm.type_document_personnel_fr'(newTypeFr) {
            console.log('edit_type_document_personnel_fr changed:', newTypeFr);
            if (newTypeFr) {
                const selectedType = this.typeDocuments.find(opt => opt.valeur === newTypeFr);
                this.editForm.type_document_personnel_ar = selectedType ? selectedType.nom_ar : '';
            } else {
                this.editForm.type_document_personnel_ar = '';
            }
        },
        'editForm.validite_fr'(newValiditeFr) {
            console.log('edit_validite_fr changed:', newValiditeFr);
            const selectedValidite = this.validiteOptions.find(opt => opt.value === newValiditeFr);
            this.editForm.validite_ar = selectedValidite ? selectedValidite.ar : '';
        },
        'editForm.depot_fr'(newDepotFr) {
            console.log('edit_depot_fr changed:', newDepotFr);
            const selectedDepot = this.depotOptions.find(opt => opt.value === newDepotFr);
            this.editForm.depot_ar = selectedDepot ? selectedDepot.ar : '';
        },
        typeDocuments(newValue) {
            console.log('typeDocuments updated:', newValue);
            this.dropdownKey += 1;
            console.log('New dropdownKey:', this.dropdownKey);
        }
    },
    methods: {
        initFilters() {
            this.filters = {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                type_document_personnel_fr: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                type_document_personnel_ar: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                date_depot: { value: null, matchMode: FilterMatchMode.CONTAINS },
                validite_fr: { value: null, matchMode: FilterMatchMode.EQUALS },
                statut: { value: null, matchMode: FilterMatchMode.EQUALS }
            };
        },
        clearFilter() {
            this.initFilters();
        },
        getSeverity(statut) {
            const statusSeverity = {
                'Valide': 'success',
                'Non Valide': 'danger',
                'Actif': 'success',
                'Inactif': 'danger'
            };
            return statusSeverity[statut] || 'info';
        },
        getStatutIcon(statut) {
            const statusIcons = {
                'Valide': 'pi pi-check',
                'Non Valide': 'pi pi-times',
                'Actif': 'pi pi-check',
                'Inactif': 'pi pi-times'
            };
            return statusIcons[statut] || 'pi pi-info';
        },
        async fetchDocumentTypes() {
            console.log('Entering fetchDocumentTypes');
            this.loadingLists = true;
            try {
                const token = localStorage.getItem('token');
                const centreId = localStorage.getItem('centreId');
                console.log('Token and CentreId:', { token, centreId });
                if (!token || !centreId) {
                    console.error('Missing token or centreId:', { token, centreId });
                    this.$toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'Token ou Centre ID manquant. Veuillez vous reconnecter.',
                        life: 5000
                    });
                    return;
                }
                const headers = {
                    Authorization: `Bearer ${token}`,
                    'X-Centre-Id': centreId,
                    'X-Is-Centre-Role': this.isCentreRole ? 1 : 0
                };
                console.log('Fetching document types with headers:', headers);
                const response = await axios.get('/api/documents-personnels/document-types', { headers });
                console.log('Raw API Response:', response);
                console.log('Document Types:', response.data.document_types);
                if (response.data.document_types && response.data.document_types.length > 0) {
                    this.typeDocuments = response.data.document_types;
                } else {
                    console.warn('No document types returned from API, using fallback data');
                }
                console.log('typeDocuments assigned:', this.typeDocuments);
            } catch (error) {
                console.error('Error fetching document types:', {
                    message: error.message,
                    status: error.response?.status,
                    data: error.response?.data,
                    headers: error.config?.headers
                });
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: error.response?.data?.message || 'Impossible de charger les types de documents.',
                    life: 5000
                });
            } finally {
                this.loadingLists = false;
            }
        },
        async fetchPersonnelName() {
            console.log('Fetching personnel name for personnelId:', this.personnelId);
            this.loading = true;
            try {
                const token = localStorage.getItem('token');
                const centreId = localStorage.getItem('centreId');
                if (!token || !centreId) {
                    console.error('Missing token or centreId:', { token, centreId });
                    this.$toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'Token ou Centre ID manquant. Veuillez vous reconnecter.',
                        life: 5000
                    });
                    return;
                }
                const headers = {
                    Authorization: `Bearer ${token}`,
                    'X-Centre-Id': centreId,
                    'X-Is-Centre-Role': this.isCentreRole ? 1 : 0
                };
                const response = await axios.get(`/api/personnels/${this.personnelId}/user`, { headers });
                console.log('Personnel user data:', response.data);
                const { nom_fr, prenom_fr } = response.data;
                this.personnelFullName = `${nom_fr || ''} ${prenom_fr || ''}`.trim() || 'Nom non disponible';
            } catch (error) {
                console.error('Error fetching personnel name:', {
                    message: error.message,
                    status: error.response?.status,
                    data: error.response?.data
                });
                this.personnelFullName = 'Nom non disponible';
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: error.response?.data?.message || 'Impossible de charger le nom du personnel.',
                    life: 5000
                });
            } finally {
                this.loading = false;
            }
        },
        async fetchDocuments() {
            console.log('Fetching documents for personnelId:', this.personnelId);
            this.loadingDocuments = true;
            try {
                const token = localStorage.getItem('token');
                const centreId = localStorage.getItem('centreId');
                if (!token || !centreId) {
                    console.error('Missing token or centreId:', { token, centreId });
                    this.$toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'Token ou Centre ID manquant. Veuillez vous reconnecter.',
                        life: 5000
                    });
                    return;
                }
                const headers = {
                    Authorization: `Bearer ${token}`,
                    'X-Centre-Id': centreId,
                    'X-Is-Centre-Role': this.isCentreRole ? 1 : 0
                };
                const response = await axios.get(`/api/documents-personnels?personnel_id=${this.personnelId}`, { headers });
                console.log('Documents fetched:', response.data);
                this.documents = response.data;
            } catch (error) {
                console.error('Error fetching documents:', {
                    message: error.message,
                    status: error.response?.status,
                    data: error.response?.data
                });
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: error.response?.data?.message || 'Impossible de charger les documents.',
                    life: 5000
                });
            } finally {
                this.loadingDocuments = false;
            }
        },
        async initializeForm() {
            console.log('Entering initializeForm with personnelId:', this.personnelId);
            this.loading = true;
            console.log('Calling fetchDocumentTypes');
            await this.fetchDocumentTypes();
            console.log('fetchDocumentTypes completed, typeDocuments:', this.typeDocuments);
            if (this.documentToEdit && this.documentToEdit.id) {
                this.form = {
                    id: this.documentToEdit.id,
                    personnel_id: this.personnelId,
                    type_document_personnel_fr: this.documentToEdit.type_document_personnel_fr || null,
                    type_document_personnel_ar: this.documentToEdit.type_document_personnel_ar || '',
                    chemin_fichier: this.documentToEdit.chemin_fichier || null,
                    date_depot: this.documentToEdit.date_depot ? new Date(this.documentToEdit.date_depot) : null,
                    validite_fr: this.documentToEdit.validite_fr || 'Valide',
                    validite_ar: this.documentToEdit.validite_ar || 'صالح',
                    depot_fr: this.documentToEdit.depot_fr || null,
                    depot_ar: this.documentToEdit.depot_ar || '',
                    description: this.documentToEdit.description || '',
                    observation: this.documentToEdit.observation || '',
                    statut: this.documentToEdit.statut || 'Actif'
                };
            } else {
                this.resetForm();
            }
            this.loading = false;
        },
        async initializeEditForm(document) {
            console.log('Initializing edit form for document:', document);
            this.loading = true;
            try {
                const response = await axios.get(`/api/documents-personnels/${document.id}`, {
                    headers: {
                        Authorization: `Bearer ${localStorage.getItem('token')}`,
                        'X-Centre-Id': localStorage.getItem('centreId'),
                        'X-Is-Centre-Role': this.isCentreRole ? 1 : 0
                    }
                });
                console.log('Document data loaded:', response.data);
                const data = response.data;
                this.editForm = {
                    id: data.id,
                    personnel_id: this.personnelId,
                    type_document_personnel_fr: data.type_document_personnel_fr || null,
                    type_document_personnel_ar: data.type_document_personnel_ar || '',
                    chemin_fichier: data.chemin_fichier || null,
                    date_depot: data.date_depot ? new Date(data.date_depot) : null,
                    validite_fr: data.validite_fr || 'Valide',
                    validite_ar: data.validite_ar || 'صالح',
                    depot_fr: data.depot_fr || null,
                    depot_ar: data.depot_ar || '',
                    description: data.description || '',
                    observation: data.observation || '',
                    statut: data.statut || 'Actif'
                };
            } catch (error) {
                console.error('Erreur lors du chargement des données du document :', {
                    message: error.message,
                    status: error.response?.status,
                    data: error.response?.data
                });
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: error.response?.data?.message || 'Impossible de charger les données du document.',
                    life: 5000
                });
            } finally {
                this.loading = false;
            }
        },
        resetForm() {
            console.log('Resetting form');
            this.form = {
                id: null,
                personnel_id: this.personnelId,
                type_document_personnel_fr: null,
                type_document_personnel_ar: '',
                chemin_fichier: null,
                date_depot: null,
                validite_fr: 'Valide',
                validite_ar: 'صالح',
                depot_fr: null,
                depot_ar: '',
                description: '',
                observation: '',
                statut: 'Actif'
            };
            this.errors = {};
            if (this.$refs.fileUploadRef && !this.isClearing) {
                this.isClearing = true;
                this.$refs.fileUploadRef.clear();
                this.isClearing = false;
            }
        },
        resetEditForm() {
            console.log('Resetting edit form');
            this.editForm = {
                id: null,
                personnel_id: this.personnelId,
                type_document_personnel_fr: null,
                type_document_personnel_ar: '',
                chemin_fichier: null,
                date_depot: null,
                validite_fr: 'Valide',
                validite_ar: 'صالح',
                depot_fr: null,
                depot_ar: '',
                description: '',
                observation: '',
                statut: 'Actif'
            };
            this.editErrors = {};
            if (this.$refs.editFileUploadRef && !this.isEditClearing) {
                this.isEditClearing = true;
                this.$refs.editFileUploadRef.clear();
                this.isEditClearing = false;
            }
        },
        handleFileSelect(event) {
            console.log('File selected:', event.files);
            if (!event.files || !event.files[0]) return;
            const file = event.files[0];
            if (!['application/pdf', 'image/png', 'image/jpeg'].includes(file.type)) {
                this.errors.chemin_fichier = 'Seuls les formats PDF, PNG et JPEG sont acceptés';
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Format de fichier non supporté.',
                    life: 5000
                });
                return;
            }
            if (file.size > 5000000) {
                this.errors.chemin_fichier = 'Le fichier doit être inférieur à 5 Mo';
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'La taille du fichier dépasse 5 Mo.',
                    life: 5000
                });
                return;
            }
            const reader = new FileReader();
            reader.onload = () => {
                const base64Size = (reader.result.length * 3 / 4) / 1024 / 1024;
                if (base64Size > 5) {
                    this.errors.chemin_fichier = 'Le fichier encodé dépasse 5 Mo';
                    this.$toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'La taille du fichier encodé dépasse 5 Mo.',
                        life: 5000
                    });
                    return;
                }
                this.form.chemin_fichier = reader.result;
                this.errors.chemin_fichier = null;
            };
            reader.readAsDataURL(file);
        },
        handleEditFileSelect(event) {
            console.log('Edit file selected:', event.files);
            if (!event.files || !event.files[0]) return;
            const file = event.files[0];
            if (!['application/pdf', 'image/png', 'image/jpeg'].includes(file.type)) {
                this.editErrors.chemin_fichier = 'Seuls les formats PDF, PNG et JPEG sont acceptés';
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Format de fichier non supporté.',
                    life: 5000
                });
                return;
            }
            if (file.size > 5000000) {
                this.editErrors.chemin_fichier = 'Le fichier doit être inférieur à 5 Mo';
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'La taille du fichier dépasse 5 Mo.',
                    life: 5000
                });
                return;
            }
            const reader = new FileReader();
            reader.onload = () => {
                const base64Size = (reader.result.length * 3 / 4) / 1024 / 1024;
                if (base64Size > 5) {
                    this.editErrors.chemin_fichier = 'Le fichier encodé dépasse 5 Mo';
                    this.$toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'La taille du fichier encodé dépasse 5 Mo.',
                        life: 5000
                    });
                    return;
                }
                this.editForm.chemin_fichier = reader.result;
                this.editErrors.chemin_fichier = null;
            };
            reader.readAsDataURL(file);
        },
        handleClear() {
            console.log('Clearing file upload');
            if (this.isClearing) return;
            this.isClearing = true;
            this.form.chemin_fichier = null;
            this.errors.chemin_fichier = null;
            if (this.$refs.fileUploadRef) {
                this.$refs.fileUploadRef.clear();
            }
            this.isClearing = false;
        },
        handleEditClear() {
            console.log('Clearing edit file upload');
            if (this.isEditClearing) return;
            this.isEditClearing = true;
            this.editForm.chemin_fichier = null;
            this.editErrors.chemin_fichier = null;
            if (this.$refs.editFileUploadRef) {
                this.$refs.editFileUploadRef.clear();
            }
            this.isEditClearing = false;
        },
        validateForm() {
            console.log('Validating form:', this.form);
            this.errors = {};
            let isValid = true;
            if (!this.form.type_document_personnel_fr) {
                this.errors.type_document_personnel_fr = 'Le type de document est requis';
                isValid = false;
            }
            if (this.form.date_depot && isNaN(new Date(this.form.date_depot).getTime())) {
                this.errors.date_depot = "La date de dépôt n'est pas valide";
                isValid = false;
            }
            return isValid;
        },
        validateEditForm() {
            console.log('Validating edit form:', this.editForm);
            this.editErrors = {};
            let isValid = true;
            if (!this.editForm.type_document_personnel_fr) {
                this.editErrors.type_document_personnel_fr = 'Le type de document est requis';
                isValid = false;
            }
            if (this.editForm.date_depot && isNaN(new Date(this.editForm.date_depot).getTime())) {
                this.editErrors.date_depot = "La date de dépôt n'est pas valide";
                isValid = false;
            }
            return isValid;
        },
        async submitForm() {
            console.log('Submitting form:', this.form);
            if (!this.validateForm()) {
                this.$toast.add({
                    severity: 'warn',
                    summary: 'Erreur de validation',
                    detail: 'Veuillez corriger les erreurs dans le formulaire.',
                    life: 5000
                });
                return;
            }
            this.submitting = true;
            this.errors = {};
            try {
                const formData = { ...this.form };
                if (formData.date_depot) {
                    formData.date_depot = new Date(formData.date_depot).toISOString().split('T')[0];
                }
                const headers = {
                    Authorization: `Bearer ${localStorage.getItem('token')}`,
                    'X-Centre-Id': localStorage.getItem('centreId'),
                    'X-Is-Centre-Role': this.isCentreRole ? 1 : 0
                };
                let response;
                if (this.isEditMode) {
                    response = await axios.patch(`/api/documents-personnels/${this.form.id}`, formData, { headers });
                    this.$toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Document mis à jour avec succès.',
                        life: 3000
                    });
                } else {
                    response = await axios.post(`/api/documents-personnels`, formData, { headers });
                    this.$toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Document créé avec succès.',
                        life: 3000
                    });
                }
                this.$emit('save', response.data);
                this.resetForm();
                this.fetchDocuments();
                this.$emit('update:visible', false);
                this.$emit('close');
            } catch (error) {
                console.error('Erreur lors de la soumission du formulaire :', {
                    message: error.message,
                    status: error.response?.status,
                    data: error.response?.data
                });
                if (error.response?.status === 422) {
                    this.errors = error.response.data.errors || {};
                    this.$toast.add({
                        severity: 'warn',
                        summary: 'Erreur de validation',
                        detail: 'Veuillez corriger les erreurs dans le formulaire.',
                        life: 5000
                    });
                } else {
                    this.$toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: error.response?.data?.message || "Une erreur est survenue lors de l'enregistrement.",
                        life: 5000
                    });
                }
            } finally {
                this.submitting = false;
            }
        },
        async submitEditForm() {
            console.log('Submitting edit form:', this.editForm);
            if (!this.validateEditForm()) {
                this.$toast.add({
                    severity: 'warn',
                    summary: 'Erreur de validation',
                    detail: 'Veuillez corriger les erreurs dans le formulaire.',
                    life: 5000
                });
                return;
            }
            this.submitting = true;
            this.editErrors = {};
            try {
                const formData = { ...this.editForm };
                if (formData.date_depot) {
                    formData.date_depot = new Date(formData.date_depot).toISOString().split('T')[0];
                }
                const headers = {
                    Authorization: `Bearer ${localStorage.getItem('token')}`,
                    'X-Centre-Id': localStorage.getItem('centreId'),
                    'X-Is-Centre-Role': this.isCentreRole ? 1 : 0
                };
                const response = await axios.patch(`/api/documents-personnels/${this.editForm.id}`, formData, { headers });
                this.$toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Document mis à jour avec succès.',
                    life: 3000
                });
                this.$emit('save', response.data);
                this.resetEditForm();
                this.fetchDocuments();
                this.editDialogVisible = false;
            } catch (error) {
                console.error('Erreur lors de la soumission du formulaire d\'édition :', {
                    message: error.message,
                    status: error.response?.status,
                    data: error.response?.data
                });
                if (error.response?.status === 422) {
                    this.editErrors = error.response.data.errors || {};
                    this.$toast.add({
                        severity: 'warn',
                        summary: 'Erreur de validation',
                        detail: 'Veuillez corriger les erreurs dans le formulaire.',
                        life: 5000
                    });
                } else {
                    this.$toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: error.response?.data?.message || "Une erreur est survenue lors de l'enregistrement.",
                        life: 5000
                    });
                }
            } finally {
                this.submitting = false;
            }
        },
        async openEditDialog(document) {
            console.log('Opening edit dialog for document:', document);
            await this.initializeEditForm(document);
            this.editDialogVisible = true;
        },
        closeEditDialog() {
            console.log('Closing edit dialog');
            this.editDialogVisible = false;
            this.resetEditForm();
        },
        async deleteDocument(document) {
            console.log('Deleting document:', document.id);
            if (!confirm('Voulez-vous vraiment supprimer ce document ?')) return;
            try {
                const headers = {
                    Authorization: `Bearer ${localStorage.getItem('token')}`,
                    'X-Centre-Id': localStorage.getItem('centreId'),
                    'X-Is-Centre-Role': this.isCentreRole ? 1 : 0
                };
                await axios.delete(`/api/documents-personnels/${document.id}`, { headers });
                this.$toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Document supprimé avec succès.',
                    life: 3000
                });
                this.fetchDocuments();
            } catch (error) {
                console.error('Erreur lors de la suppression du document :', {
                    message: error.message,
                    status: error.response?.status,
                    data: error.response?.data
                });
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: error.response?.data?.message || 'Impossible de supprimer le document.',
                    life: 5000
                });
            }
        },
        async downloadDocument(id) {
            console.log('Downloading document:', id);
            try {
                const headers = {
                    Authorization: `Bearer ${localStorage.getItem('token')}`,
                    'X-Centre-Id': localStorage.getItem('centreId'),
                    'X-Is-Centre-Role': this.isCentreRole ? 1 : 0
                };
                const response = await axios.get(`/api/documents-personnels/${id}/download`, {
                    headers,
                    responseType: 'blob'
                });
                const contentType = response.headers['content-type'];
                const contentDisposition = response.headers['content-disposition'];
                let fileName = `document_${id}`;
                if (contentDisposition) {
                    const fileNameMatch = contentDisposition.match(/filename="(.+)"/);
                    if (fileNameMatch) {
                        fileName = fileNameMatch[1];
                    }
                }
                const url = window.URL.createObjectURL(new Blob([response.data], { type: contentType }));
                const link = document.createElement('a');
                link.href = url;
                link.download = fileName;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                window.URL.revokeObjectURL(url);
                this.$toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Document téléchargé avec succès.',
                    life: 3000
                });
            } catch (error) {
                console.error('Erreur lors du téléchargement du document :', {
                    message: error.message,
                    status: error.response?.status,
                    data: error.response?.data
                });
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: error.response?.data?.message || 'Impossible de télécharger le document.',
                    life: 5000
                });
            }
        },
        printDocument() {
            if (!this.personnelFullName || this.personnelFullName === 'Nom non disponible') {
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Aucune donnée disponible pour l\'impression.',
                    life: 3000
                });
                return;
            }
            const element = document.createElement('div');
            element.innerHTML = `
                <div class="print-container">
                    <div class="print-header">
                        <h1>Documents de ${this.personnelFullName}</h1>
                    </div>
                    <div class="print-section documents-section">
                        <h2>Documents Administratifs</h2>
                        <table class="print-table">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Date Dépôt</th>
                                    <th>Validité</th>
                                    <th>Mode Dépôt</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${this.documents.length ? this.documents.map(doc => `
                                    <tr>
                                        <td>${doc.type_document_personnel_fr || '-'}</td>
                                        <td>${this.formatDate(doc.date_depot) || '-'}</td>
                                        <td>${doc.validite_fr || '-'}</td>
                                        <td>${doc.depot_fr || '-'}</td>
                                        <td>${doc.statut || '-'}</td>
                                    </tr>
                                `).join('') : `
                                    <tr>
                                        <td colspan="5" class="text-center">
                                            Aucun document trouvé.
                                        </td>
                                    </tr>
                                `}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
            const styles = `
                <style>
                    body {
                        font-family: 'Arial', sans-serif;
                        margin: 20px;
                        color: #333;
                    }
                    .print-container {
                        max-width: 1000px;
                        margin: 0 auto;
                    }
                    .print-header {
                        text-align: center;
                        margin-bottom: 20px;
                    }
                    .print-header h1 {
                        font-size: 24px;
                        color: #005b96;
                    }
                    .print-section {
                        margin-bottom: 30px;
                    }
                    .print-section h2 {
                        font-size: 16px;
                        color: #005b96;
                        border-bottom: 2px solid #007ad9;
                        padding-bottom: 5px;
                        margin-bottom: 10px;
                    }
                    .print-section.documents-section h2 {
                        background-color: var(--surface-0, #ffffff);
                        height: 36px;
                        line-height: 36px;
                        padding: 0 12px;
                        border: 1px solid var(--surface-300, #dee2e6);
                        border-bottom: 2px solid #007ad9;
                        margin-bottom: 10px;
                        font-size: 16px;
                        color: #005b96;
                    }
                    .print-table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-top: 10px;
                    }
                    .print-table th,
                    .print-table td {
                        border: 1px solid #dee2e6;
                        padding: 8px;
                        text-align: left;
                    }
                    .print-table th {
                        background-color: #f8f9fa;
                        font-weight: 600;
                    }
                    .print-table td.text-center {
                        text-align: center;
                    }
                </style>
            `;
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                    <head>
                        <title>Impression Documents de ${this.personnelFullName}</title>
                        <link href="https://fonts.googleapis.com/css2?family=Amiri&display=swap" rel="stylesheet">
                        ${styles}
                    </head>
                    <body>
                        ${element.innerHTML}
                    </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
            printWindow.close();
        },
        formatDate(date) {
            if (!date) return '-';
            return new Date(date).toLocaleDateString('fr-FR', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit'
            });
        },
        handleVisibleUpdate(value) {
            console.log('handleVisibleUpdate:', value);
            this.$emit('update:visible', value);
            if (!value) {
                this.resetForm();
                this.personnelFullName = 'Nom non disponible';
                this.$emit('close');
            }
        },
        handleCancel() {
            console.log('Cancelling form');
            this.resetForm();
            this.personnelFullName = 'Nom non disponible';
            this.$emit('update:visible', false);
            this.$emit('close');
        }
    },
    created() {
        console.log('DocumentPersonnelForm created with props:', {
            visible: this.visible,
            personnelId: this.personnelId,
            isCentreRole: this.isCentreRole
        });
        this.$toast = useToast();
        this.initFilters();
        if (this.visible) {
            this.initializeForm();
            this.fetchDocuments();
            this.fetchPersonnelName();
        }
    }
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

.arabic-text,
:deep(.p-inputtext[dir='rtl']),
:deep(.p-textarea[dir='rtl']) {
    font-family: 'NotoNaskhArabic', sans-serif;
    direction: rtl;
    text-align: right;
}

.field {
    margin-bottom: 1.25rem;
}

label.font-medium {
    color: #6c757d;
}

:deep(.p-button) {
    max-width: 200px;
    padding: 0.8rem 1rem;
    font-size: 0.875rem;
    border-radius: 0.25rem;
}

:deep(.p-tabview .p-tabview-nav) {
    background: var(--surface-ground);
    border-bottom: 1px solid var(--surface-border);
    margin-bottom: 1.5rem;
}

:deep(.p-tabview .p-tabview-nav li .p-tabview-nav-link) {
    background: transparent;
    border-color: transparent;
    color: var(--text-color-secondary);
    font-weight: 500;
    padding: 1rem 1.25rem;
    transition: all 0.2s ease;
}

:deep(.p-tabview .p-tabview-nav li .p-tabview-nav-link:not(.p-disabled):focus) {
    box-shadow: none;
}

:deep(.p-tabview .p-tabview-nav li.p-highlight .p-tabview-nav-link) {
    color: var(--primary-color);
    border-bottom: 2px solid var(--primary-color);
}

:deep(.p-tabview .p-tabview-panels) {
    background: transparent;
    padding: 0;
}

:deep(.p-inputtext),
:deep(.p-dropdown),
:deep(.p-textarea),
:deep(.p-calendar) {
    border-radius: 0.25rem;
}

:deep(.p-datatable) {
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

:deep(.p-datatable-sm .p-datatable-tbody > tr > td) {
    padding: 0.5rem 1rem;
}

:deep(.p-datatable .p-column-header-content) {
    justify-content: center;
}

:deep(.p-datatable .p-datatable-thead > tr > th) {
    background-color: transparent;
    font-weight: 600;
    padding: 0.5rem;
}

:deep(.p-datatable-header) {
    padding: 0 !important;
    margin: 0 !important;
    border: none !important;
}

:deep(.p-button-text) {
    margin: 0 0.5rem;
}

:deep(.surface-card) {
    margin-top: 1.5rem;
}
</style>
```
