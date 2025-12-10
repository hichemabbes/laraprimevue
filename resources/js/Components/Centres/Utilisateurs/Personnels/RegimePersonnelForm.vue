<template>
    <div>
        <!-- Formulaire pour Ajouter un Régime -->
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
                    <i class="pi pi-clock text-primary text-2xl"></i>
                    <span class="font-bold text-2xl">
                        {{ isEditMode ? 'Modifier le Régime' : 'Ajouter un Régime' }}
                    </span>
                </div>
            </template>
            <div v-if="loading" class="flex flex-column align-items-center justify-content-center gap-3 p-6">
                <ProgressSpinner style="width: 50px; height: 50px" strokeWidth="4" />
                <span class="text-color-secondary">Chargement des données...</span>
            </div>
            <div v-else class="surface-card p-4 shadow-2 border-round">
                <TabView>
                    <TabPanel header="Détails du Régime">
                        <form @submit.prevent="submitForm">
                            <div class="grid p-fluid">
                                <div class="col-12 field">
                                    <span class="font-bold text-lg text-blue-700">
                                        {{ isEditMode ? `Modification du régime de ${personnelFullName}` : `Ajout d'un régime pour ${personnelFullName}` }}
                                    </span>
                                </div>
                                <div class="col-12 md:col-6 field">
                                    <label for="annee_formation_id" class="block font-medium mb-2" style="font-size: 0.875rem">
                                        Année de Formation <span class="text-red-500">*</span>
                                    </label>
                                    <Dropdown
                                        id="annee_formation_id"
                                        v-model="form.annee_formation_id"
                                        :options="anneesFormation"
                                        optionLabel="intitule"
                                        optionValue="id"
                                        placeholder="Sélectionner une année de formation"
                                        :class="{ 'p-invalid': errors.annee_formation_id }"
                                        :loading="loadingLists"
                                        style="font-size: 0.875rem"
                                        :key="dropdownKey"
                                    />
                                    <small v-if="errors.annee_formation_id" class="p-error" style="font-size: 0.75rem">{{ errors.annee_formation_id }}</small>
                                </div>
                                <div class="col-12 md:col-6 field">
                                    <label for="regime_horaire" class="block font-medium mb-2" style="font-size: 0.875rem">
                                        Régime Horaire (heures) <span class="text-red-500">*</span>
                                    </label>
                                    <InputNumber
                                        id="regime_horaire"
                                        v-model="form.regime_horaire"
                                        :min="0"
                                        :max="168"
                                        :class="{ 'p-invalid': errors.regime_horaire }"
                                        placeholder="Entrez le régime horaire"
                                        style="font-size: 0.875rem"
                                    />
                                    <small v-if="errors.regime_horaire" class="p-error" style="font-size: 0.75rem">{{ errors.regime_horaire }}</small>
                                </div>
                                <div class="col-12 md:col-6 field">
                                    <label for="rabattement" class="block font-medium mb-2" style="font-size: 0.875rem">
                                        Rabattement (heures)
                                    </label>
                                    <InputNumber
                                        id="rabattement"
                                        v-model="form.rabattement"
                                        :min="0"
                                        :max="168"
                                        :class="{ 'p-invalid': errors.rabattement }"
                                        placeholder="Entrez le rabattement"
                                        style="font-size: 0.875rem"
                                    />
                                    <small v-if="errors.rabattement" class="p-error" style="font-size: 0.75rem">{{ errors.rabattement }}</small>
                                </div>
                                <div class="col-12 md:col-6 field">
                                    <label for="statut" class="block font-medium mb-2" style="font-size: 0.875rem">
                                        Statut <span class="text-red-500">*</span>
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
                        </form>
                    </TabPanel>
                    <TabPanel header="Liste des Régimes">
                        <div class="flex justify-content-between align-items-center mb-4">
                            <div class="flex align-items-center gap-2">
                                <span class="font-bold text-lg">
                                    Liste des régimes de <span class="text-primary">{{ personnelFullName }}</span>
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
                            :value="regimes"
                            :loading="loadingRegimes"
                            dataKey="id"
                            size="small"
                            stripedRows
                            paginator
                            :rows="10"
                            :rowsPerPageOptions="[10, 20, 50]"
                            filterDisplay="menu"
                            :globalFilterFields="['annee_formation_intitule', 'regime_horaire', 'rabattement', 'regime_reel', 'statut']"
                            scrollable
                            scrollHeight="600px"
                            removableSort
                            class="p-datatable-sm border-round-lg"
                        >
                            <template #empty>
                                <div class="text-center py-4">
                                    <i class="pi pi-inbox text-4xl text-400 mb-2" />
                                    <p class="text-600">Aucun régime trouvé</p>
                                </div>
                            </template>
                            <Column
                                field="annee_formation_intitule"
                                header="Année de Formation"
                                sortable
                                style="min-width: 12rem"
                            >
                                <template #body="{ data }">
                                    <span class="font-medium">{{ data.annee_formation_intitule }}</span>
                                </template>
                                <template #filter="{ filterModel, filterCallback }">
                                    <InputText
                                        v-model="filterModel.value"
                                        type="text"
                                        class="p-column-filter"
                                        placeholder="Rechercher par année"
                                        @input="filterCallback"
                                    />
                                </template>
                            </Column>
                            <Column
                                field="regime_horaire"
                                header="Régime Horaire (heures)"
                                sortable
                                style="min-width: 10rem"
                            >
                                <template #body="{ data }">
                                    <Tag :value="data.regime_horaire" icon="pi pi-clock" />
                                </template>
                            </Column>
                            <Column
                                field="rabattement"
                                header="Rabattement (heures)"
                                sortable
                                style="min-width: 10rem"
                            >
                                <template #body="{ data }">
                                    <Tag :value="data.rabattement || '0'" icon="pi pi-minus" />
                                </template>
                            </Column>
                            <Column
                                field="regime_reel"
                                header="Régime Réel (heures)"
                                sortable
                                style="min-width: 10rem"
                            >
                                <template #body="{ data }">
                                    <Tag :value="data.regime_reel" icon="pi pi-clock" />
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
                                        @click="deleteRegime(slotProps.data)"
                                        v-tooltip="'Supprimer'"
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

        <!-- Formulaire pour Modifier un Régime -->
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
                    <i class="pi pi-clock text-primary text-2xl"></i>
                    <span class="font-bold text-2xl">Modifier le Régime</span>
                </div>
            </template>
            <div v-if="loading" class="flex flex-column align-items-center justify-content-center gap-3 p-6">
                <ProgressSpinner style="width: 50px; height: 50px" strokeWidth="4" />
                <span class="text-color-secondary">Chargement des données...</span>
            </div>
            <div v-else class="surface-card p-4 shadow-2 border-round">
                <form @submit.prevent="submitEditForm">
                    <div class="grid p-fluid">
                        <div class="col-12 field">
                            <span class="font-bold text-lg text-blue-700">
                                Modification du régime de {{ personnelFullName }}
                            </span>
                        </div>
                        <div class="col-12 md:col-6 field">
                            <label for="edit_annee_formation_id" class="block font-medium mb-2" style="font-size: 0.875rem">
                                Année de Formation <span class="text-red-500">*</span>
                            </label>
                            <Dropdown
                                id="edit_annee_formation_id"
                                v-model="editForm.annee_formation_id"
                                :options="anneesFormation"
                                optionLabel="intitule"
                                optionValue="id"
                                placeholder="Sélectionner une année de formation"
                                :class="{ 'p-invalid': editErrors.annee_formation_id }"
                                :loading="loadingLists"
                                style="font-size: 0.875rem"
                                :key="dropdownKey"
                            />
                            <small v-if="editErrors.annee_formation_id" class="p-error" style="font-size: 0.75rem">{{ editErrors.annee_formation_id }}</small>
                        </div>
                        <div class="col-12 md:col-6 field">
                            <label for="edit_regime_horaire" class="block font-medium mb-2" style="font-size: 0.875rem">
                                Régime Horaire (heures) <span class="text-red-500">*</span>
                            </label>
                            <InputNumber
                                id="edit_regime_horaire"
                                v-model="editForm.regime_horaire"
                                :min="0"
                                :max="168"
                                :class="{ 'p-invalid': editErrors.regime_horaire }"
                                placeholder="Entrez le régime horaire"
                                style="font-size: 0.875rem"
                            />
                            <small v-if="editErrors.regime_horaire" class="p-error" style="font-size: 0.75rem">{{ editErrors.regime_horaire }}</small>
                        </div>
                        <div class="col-12 md:col-6 field">
                            <label for="edit_rabattement" class="block font-medium mb-2" style="font-size: 0.875rem">
                                Rabattement (heures)
                            </label>
                            <InputNumber
                                id="edit_rabattement"
                                v-model="editForm.rabattement"
                                :min="0"
                                :max="168"
                                :class="{ 'p-invalid': editErrors.rabattement }"
                                placeholder="Entrez le rabattement"
                                style="font-size: 0.875rem"
                            />
                            <small v-if="editErrors.rabattement" class="p-error" style="font-size: 0.75rem">{{ editErrors.rabattement }}</small>
                        </div>
                        <div class="col-12 md:col-6 field">
                            <label for="edit_statut" class="block font-medium mb-2" style="font-size: 0.875rem">
                                Statut <span class="text-red-500">*</span>
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
                </form>
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
        <Toast position="top-right" />
    </div>
</template>

<script>
import axios from '@/axios.js';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import ProgressSpinner from 'primevue/progressspinner';
import Textarea from 'primevue/textarea';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Toast from 'primevue/toast';
import { FilterMatchMode } from 'primevue/api';
import { useToast } from 'primevue/usetoast';

export default {
    name: 'RegimePersonnelForm',
    components: {
        Button,
        Dialog,
        Dropdown,
        InputText,
        InputNumber,
        ProgressSpinner,
        Textarea,
        TabView,
        TabPanel,
        DataTable,
        Column,
        Tag,
        Toast
    },
    props: {
        visible: { type: Boolean, default: false },
        regimeToEdit: { type: Object, default: null },
        personnelId: { type: Number, required: true },
        isCentreRole: { type: Boolean, default: false }
    },
    data() {
        return {
            form: {
                id: null,
                personnel_id: this.personnelId,
                annee_formation_id: null,
                regime_horaire: null,
                rabattement: 0,
                observation: '',
                statut: 'Actif'
            },
            editForm: {
                id: null,
                personnel_id: this.personnelId,
                annee_formation_id: null,
                regime_horaire: null,
                rabattement: 0,
                observation: '',
                statut: 'Actif'
            },
            errors: {},
            editErrors: {},
            filters: null,
            loading: false,
            loadingLists: false,
            loadingRegimes: false,
            submitting: false,
            editDialogVisible: false,
            regimes: [],
            anneesFormation: [],
            statutOptions: [
                { label: 'Actif', value: 'Actif' },
                { label: 'Inactif', value: 'Inactif' }
            ],
            personnelFullName: 'Nom non disponible',
            dropdownKey: 0
        };
    },
    computed: {
        isEditMode() {
            return !!this.form.id;
        }
    },
    watch: {
        visible(newVisible) {
            if (newVisible) {
                this.initializeForm();
                this.fetchRegimes();
                this.fetchPersonnelName();
            } else {
                this.resetForm();
                this.regimes = [];
                this.anneesFormation = [];
                this.personnelFullName = 'Nom non disponible';
            }
        },
        regimeToEdit: {
            handler() {
                if (this.visible) {
                    this.initializeForm();
                }
            },
            deep: true
        },
        personnelId(newId) {
            this.form.personnel_id = newId;
            this.editForm.personnel_id = newId;
            if (this.visible) {
                this.fetchRegimes();
                this.fetchPersonnelName();
            }
        }
    },
    methods: {
        initFilters() {
            this.filters = {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                annee_formation_intitule: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                regime_horaire: { value: null, matchMode: FilterMatchMode.EQUALS },
                rabattement: { value: null, matchMode: FilterMatchMode.EQUALS },
                regime_reel: { value: null, matchMode: FilterMatchMode.EQUALS },
                statut: { value: null, matchMode: FilterMatchMode.EQUALS }
            };
        },
        clearFilter() {
            this.initFilters();
        },
        getSeverity(statut) {
            return statut === 'Actif' ? 'success' : 'danger';
        },
        getStatutIcon(statut) {
            return statut === 'Actif' ? 'pi pi-check' : 'pi pi-times';
        },
        async fetchPersonnelName() {
            console.log('Fetching personnel name for personnelId:', this.personnelId);
            this.loading = true;
            try {
                const token = localStorage.getItem('token');
                const centreId = localStorage.getItem('centreId');
                if (!token || !centreId || !this.personnelId) {
                    console.error('Missing token, centreId or personnelId:', { token, centreId, personnelId: this.personnelId });
                    throw new Error('Token, Centre ID ou Personnel ID manquant.');
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
        async fetchAnneesFormation() {
            this.loadingLists = true;
            try {
                const token = localStorage.getItem('token');
                const centreId = localStorage.getItem('centreId');
                if (!token || !centreId) {
                    throw new Error('Token ou Centre ID manquant.');
                }
                const headers = {
                    Authorization: `Bearer ${token}`,
                    'X-Centre-Id': centreId,
                    'X-Is-Centre-Role': this.isCentreRole ? 1 : 0
                };
                const response = await axios.get('/api/annees-formation', { headers });
                this.anneesFormation = response.data;
                if (!this.anneesFormation.length) {
                    this.$toast.add({
                        severity: 'warn',
                        summary: 'Avertissement',
                        detail: 'Aucune année de formation disponible.',
                        life: 5000
                    });
                }
                this.dropdownKey += 1;
            } catch (error) {
                console.error('Erreur lors de la récupération des années de formation:', error);
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: error.response?.data?.message || 'Impossible de charger les années de formation.',
                    life: 5000
                });
                this.anneesFormation = [];
            } finally {
                this.loadingLists = false;
            }
        },
        async fetchRegimes() {
            this.loadingRegimes = true;
            try {
                const token = localStorage.getItem('token');
                const centreId = localStorage.getItem('centreId');
                if (!token || !centreId || !this.personnelId) {
                    throw new Error('Token, Centre ID ou Personnel ID manquant.');
                }
                const headers = {
                    Authorization: `Bearer ${token}`,
                    'X-Centre-Id': centreId,
                    'X-Is-Centre-Role': this.isCentreRole ? 1 : 0
                };
                const response = await axios.get('/api/regimes-personnels', {
                    headers,
                    params: { personnel_id: this.personnelId }
                });
                this.regimes = response.data.map(item => ({
                    ...item,
                    annee_formation_intitule: item.annee_formation?.intitule || 'Non spécifié',
                    regime_reel: item.regime_horaire - (item.rabattement || 0)
                }));
                if (!this.regimes.length) {
                    this.$toast.add({
                        severity: 'info',
                        summary: 'Information',
                        detail: 'Aucun régime trouvé pour ce personnel.',
                        life: 5000
                    });
                }
            } catch (error) {
                console.error('Erreur lors de la récupération des régimes:', error);
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: error.response?.data?.message || 'Impossible de charger les régimes.',
                    life: 5000
                });
                this.regimes = [];
            } finally {
                this.loadingRegimes = false;
            }
        },
        async initializeForm() {
            console.log('Entering initializeForm with personnelId:', this.personnelId);
            this.loading = true;
            await this.fetchAnneesFormation();
            if (this.regimeToEdit && this.regimeToEdit.id) {
                try {
                    const token = localStorage.getItem('token');
                    const centreId = localStorage.getItem('centreId');
                    if (!token || !centreId) {
                        throw new Error('Token ou Centre ID manquant.');
                    }
                    const headers = {
                        Authorization: `Bearer ${token}`,
                        'X-Centre-Id': centreId,
                        'X-Is-Centre-Role': this.isCentreRole ? 1 : 0
                    };
                    const response = await axios.get(`/api/regimes-personnels/${this.regimeToEdit.id}`, { headers });
                    const data = response.data;
                    this.form = {
                        id: data.id,
                        personnel_id: this.personnelId,
                        annee_formation_id: data.annee_formation_id || null,
                        regime_horaire: data.regime_horaire || null,
                        rabattement: data.rabattement || 0,
                        observation: data.observation || '',
                        statut: data.statut || 'Actif'
                    };
                } catch (error) {
                    console.error('Erreur lors du chargement du régime:', error);
                    this.$toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: error.response?.data?.message || 'Impossible de charger les données du régime.',
                        life: 5000
                    });
                    this.resetForm();
                }
            } else {
                this.resetForm();
            }
            this.loading = false;
        },
        async initializeEditForm(regime) {
            console.log('Initializing edit form for regime:', regime);
            this.loading = true;
            try {
                const token = localStorage.getItem('token');
                const centreId = localStorage.getItem('centreId');
                if (!token || !centreId) {
                    throw new Error('Token ou Centre ID manquant.');
                }
                const headers = {
                    Authorization: `Bearer ${token}`,
                    'X-Centre-Id': centreId,
                    'X-Is-Centre-Role': this.isCentreRole ? 1 : 0
                };
                const response = await axios.get(`/api/regimes-personnels/${regime.id}`, { headers });
                const data = response.data;
                this.editForm = {
                    id: data.id,
                    personnel_id: this.personnelId,
                    annee_formation_id: data.annee_formation_id || null,
                    regime_horaire: data.regime_horaire || null,
                    rabattement: data.rabattement || 0,
                    observation: data.observation || '',
                    statut: data.statut || 'Actif'
                };
            } catch (error) {
                console.error('Erreur lors du chargement des données du régime:', error);
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: error.response?.data?.message || 'Impossible de charger les données du régime.',
                    life: 5000
                });
            } finally {
                this.loading = false;
            }
        },
        resetForm() {
            this.form = {
                id: null,
                personnel_id: this.personnelId,
                annee_formation_id: null,
                regime_horaire: null,
                rabattement: 0,
                observation: '',
                statut: 'Actif'
            };
            this.errors = {};
            this.dropdownKey += 1;
        },
        resetEditForm() {
            this.editForm = {
                id: null,
                personnel_id: this.personnelId,
                annee_formation_id: null,
                regime_horaire: null,
                rabattement: 0,
                observation: '',
                statut: 'Actif'
            };
            this.editErrors = {};
            this.dropdownKey += 1;
        },
        validateForm() {
            this.errors = {};
            let isValid = true;
            if (!this.form.annee_formation_id) {
                this.errors.annee_formation_id = "L'année de formation est requise.";
                isValid = false;
            }
            if (!this.form.regime_horaire || this.form.regime_horaire <= 0) {
                this.errors.regime_horaire = 'Le régime horaire doit être un nombre positif.';
                isValid = false;
            }
            if (this.form.rabattement < 0) {
                this.errors.rabattement = 'Le rabattement ne peut pas être négatif.';
                isValid = false;
            }
            if (!this.form.statut) {
                this.errors.statut = 'Le statut est requis.';
                isValid = false;
            }
            return isValid;
        },
        validateEditForm() {
            this.editErrors = {};
            let isValid = true;
            if (!this.editForm.annee_formation_id) {
                this.editErrors.annee_formation_id = "L'année de formation est requise.";
                isValid = false;
            }
            if (!this.editForm.regime_horaire || this.editForm.regime_horaire <= 0) {
                this.editErrors.regime_horaire = 'Le régime horaire doit être un nombre positif.';
                isValid = false;
            }
            if (this.editForm.rabattement < 0) {
                this.editErrors.rabattement = 'Le rabattement ne peut pas être négatif.';
                isValid = false;
            }
            if (!this.editForm.statut) {
                this.editErrors.statut = 'Le statut est requis.';
                isValid = false;
            }
            return isValid;
        },
        async submitForm() {
            if (!this.validateForm()) {
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Veuillez corriger les erreurs dans le formulaire.',
                    life: 5000
                });
                return;
            }
            this.submitting = true;
            try {
                const token = localStorage.getItem('token');
                const centreId = localStorage.getItem('centreId');
                if (!token || !centreId || !this.personnelId) {
                    throw new Error('Token, Centre ID ou Personnel ID manquant.');
                }
                const headers = {
                    Authorization: `Bearer ${token}`,
                    'X-Centre-Id': centreId,
                    'X-Is-Centre-Role': this.isCentreRole ? 1 : 0
                };
                const payload = { ...this.form };
                let response;
                if (this.isEditMode) {
                    response = await axios.patch(`/api/regimes-personnels/${this.form.id}`, payload, { headers });
                    this.$toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Régime modifié avec succès.',
                        life: 3000
                    });
                } else {
                    response = await axios.post('/api/regimes-personnels', payload, { headers });
                    this.$toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Régime créé avec succès.',
                        life: 3000
                    });
                }
                this.resetForm();
                this.fetchRegimes();
                this.$emit('update:visible', false);
                this.$emit('saved', response.data);
            } catch (error) {
                console.error('Erreur lors de la soumission du formulaire:', error);
                let errorMessage = this.isEditMode ? 'Impossible de modifier le régime.' : 'Impossible de créer le régime.';
                if (error.response) {
                    if (error.response.status === 422 && error.response.data?.message === 'Un régime existe déjà pour ce personnel et cette année.') {
                        errorMessage = "Un régime est déjà défini pour l'année sélectionnée.";
                    } else if (error.response.status === 422) {
                        this.errors = error.response.data.errors || {};
                        errorMessage = 'Veuillez corriger les erreurs dans le formulaire.';
                    } else if (error.response.status === 403) {
                        errorMessage = error.response.data?.message || 'Vous n\'êtes pas autorisé à effectuer cette action.';
                    } else if (error.response.status === 401) {
                        errorMessage = 'Non authentifié. Veuillez vous reconnecter.';
                    } else if (error.response.status === 404) {
                        errorMessage = 'Ressource non trouvée.';
                    } else {
                        errorMessage = error.response.data?.message || errorMessage;
                    }
                }
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: errorMessage,
                    life: 5000
                });
            } finally {
                this.submitting = false;
            }
        },
        async submitEditForm() {
            if (!this.validateEditForm()) {
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Veuillez corriger les erreurs dans le formulaire.',
                    life: 5000
                });
                return;
            }
            this.submitting = true;
            try {
                const token = localStorage.getItem('token');
                const centreId = localStorage.getItem('centreId');
                if (!token || !centreId || !this.personnelId) {
                    throw new Error('Token, Centre ID ou Personnel ID manquant.');
                }
                const headers = {
                    Authorization: `Bearer ${token}`,
                    'X-Centre-Id': centreId,
                    'X-Is-Centre-Role': this.isCentreRole ? 1 : 0
                };
                const payload = { ...this.editForm };
                const response = await axios.patch(`/api/regimes-personnels/${this.editForm.id}`, payload, { headers });
                this.$toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Régime modifié avec succès.',
                    life: 3000
                });
                this.resetEditForm();
                this.fetchRegimes();
                this.editDialogVisible = false;
                this.$emit('saved', response.data);
            } catch (error) {
                console.error('Erreur lors de la soumission du formulaire d\'édition:', error);
                let errorMessage = 'Impossible de modifier le régime.';
                if (error.response) {
                    if (error.response.status === 422 && error.response.data?.message === 'Un régime existe déjà pour ce personnel et cette année.') {
                        errorMessage = "Un régime est déjà défini pour l'année sélectionnée.";
                    } else if (error.response.status === 422) {
                        this.editErrors = error.response.data.errors || {};
                        errorMessage = 'Veuillez corriger les erreurs dans le formulaire.';
                    } else if (error.response.status === 403) {
                        errorMessage = error.response.data?.message || 'Vous n\'êtes pas autorisé à effectuer cette action.';
                    } else if (error.response.status === 401) {
                        errorMessage = 'Non authentifié. Veuillez vous reconnecter.';
                    } else if (error.response.status === 404) {
                        errorMessage = 'Ressource non trouvée.';
                    } else {
                        errorMessage = error.response.data?.message || errorMessage;
                    }
                }
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: errorMessage,
                    life: 5000
                });
            } finally {
                this.submitting = false;
            }
        },
        async deleteRegime(regime) {
            this.$confirm.require({
                message: `Êtes-vous sûr de vouloir supprimer le régime pour l'année "${regime.annee_formation?.intitule || 'Non spécifié'}" ?`,
                header: 'Confirmation de suppression',
                icon: 'pi pi-exclamation-triangle',
                acceptLabel: 'Oui, supprimer',
                rejectLabel: 'Annuler',
                accept: async () => {
                    try {
                        const token = localStorage.getItem('token');
                        const centreId = localStorage.getItem('centreId');
                        if (!token || !centreId) {
                            throw new Error('Token ou Centre ID manquant.');
                        }
                        const headers = {
                            Authorization: `Bearer ${token}`,
                            'X-Centre-Id': centreId,
                            'X-Is-Centre-Role': this.isCentreRole ? 1 : 0
                        };
                        await axios.delete(`/api/regimes-personnels/${regime.id}`, { headers });
                        this.$toast.add({
                            severity: 'success',
                            summary: 'Succès',
                            detail: 'Régime supprimé avec succès.',
                            life: 3000
                        });
                        this.fetchRegimes();
                    } catch (error) {
                        console.error('Erreur lors de la suppression du régime:', error);
                        this.$toast.add({
                            severity: 'error',
                            summary: 'Erreur',
                            detail: error.response?.data?.message || 'Impossible de supprimer le régime.',
                            life: 5000
                        });
                    }
                }
            });
        },
        async openEditDialog(regime) {
            await this.initializeEditForm(regime);
            this.editDialogVisible = true;
        },
        closeEditDialog() {
            this.editDialogVisible = false;
            this.resetEditForm();
        },
        handleVisibleUpdate(newVisible) {
            this.$emit('update:visible', newVisible);
            if (!newVisible) {
                this.resetForm();
                this.personnelFullName = 'Nom non disponible';
            }
        },
        handleCancel() {
            this.$emit('update:visible', false);
            this.resetForm();
            this.personnelFullName = 'Nom non disponible';
        }
    },
    created() {
        this.$toast = useToast();
        this.initFilters();
        if (this.visible) {
            this.initializeForm();
            this.fetchRegimes();
            this.fetchPersonnelName();
        }
    },
    beforeUnmount() {
        this.resetForm();
        this.resetEditForm();
        this.anneesFormation = [];
        this.regimes = [];
    }
};
</script>

<style scoped>
.p-datatable-sm .p-datatable-tbody > tr > td {
    padding: 0.5rem;
}
.p-button-sm {
    padding: 0.5rem 1rem;
}
</style>
