<template>
    <div>
        <div class="flex justify-content-between align-items-center mb-2">
            <div class="text-2xl font-bold text-primary">
                Liste des centres et spécialités
                <span :style="{ color: '#EF4444' }">actives</span>
                <span class="font-bold text-primary">
                    pour l'année de formation
                </span>
                <span :style="{ color: '#EF4444' }">{{
                    selectedAnnee ? getAnneeIntitule(selectedAnnee) : 'en cours'
                }}</span>
            </div>
            <div class="flex align-items-center gap-2">
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
        <!-- Deuxième ligne : Recherche et boutons dans un cadre fin -->
        <div
            class="flex justify-content-between align-items-center mb-2 p-2 border-round surface-border border-1"
        >
            <div class="flex gap-2 align-items-center">
                <Button
                    icon="pi pi-plus"
                    severity="success"
                    size="small"
                    @click="openForm"
                />
                <Dropdown
                    v-model="selectedAnnee"
                    :options="annees"
                    optionLabel="intitule"
                    optionValue="id"
                    placeholder="Sélectionner une année"
                    @change="filterByAnnee"
                />
                <Button
                    v-if="selectedAnnee"
                    icon="pi pi-times"
                    size="small"
                    severity="secondary"
                    @click="clearAnneeFilter"
                />
                <Button
                    text
                    icon="pi pi-minus"
                    label="Tout Réduire"
                    @click="collapseAll"
                />
            </div>
            <div class="flex align-items-center gap-2">
                <Button
                    icon="pi pi-file-import"
                    severity="success"
                    size="small"
                    label="Import XLS"
                    @click="$refs.fileInput.click()"
                />
                <input
                    type="file"
                    ref="fileInput"
                    style="display: none"
                    accept=".xls,.xlsx"
                    @change="importXls"
                />
                <Button
                    v-if="importErrors.length > 0"
                    icon="pi pi-exclamation-triangle"
                    severity="warning"
                    size="small"
                    label="Erreur Import"
                    @click="importErrorsVisible = true"
                />
                <Button
                    icon="pi pi-file-export"
                    severity="info"
                    size="small"
                    label="Export XLS"
                    @click="exportXls"
                />
            </div>
        </div>
        <DataTable
            v-model:expandedRows="expandedRows"
            v-model:filters="filters"
            showGridlines
            stripedRows
            v-model:selection="selectedCentres"
            :value="filteredCentres"
            dataKey="id"
            size="small"
            :rows="20"
            filterDisplay="menu"
            :globalFilterFields="[
                'code',
                'nom_fr',
                'nom_ar',
                'statut',
                'gouvernerat_fr',
            ]"
            :loading="loading"
            scrollable
            scrollHeight="700px"
            :pt="{ table: { style: 'min-width: 50rem' } }"
        >
            <template #empty>
                <div v-if="!loading" class="text-center p-3">
                    Aucun centre trouvé.
                </div>
                <div v-else class="text-center p-3">Chargement en cours...</div>
            </template>

            <Column expander style="width: 5rem" />
            <Column
                selectionMode="multiple"
                headerStyle="width: 3rem"
                frozen
                class="font-bold"
            ></Column>
            <Column
                field="code"
                header="Code"
                sortable
                style="min-width: 10rem"
            >
                <template #body="{ data }">
                    <span>{{ data.code }}</span>
                </template>
                <template #filter="{ filterModel, filterCallback }">
                    <InputText
                        v-model="filterModel.value"
                        type="text"
                        class="p-column-filter"
                        placeholder="Rechercher par code"
                        @input="filterCallback"
                    />
                </template>
            </Column>
            <Column
                field="nom_fr"
                header="Nom (FR)"
                sortable
                style="min-width: 12rem"
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
                        @input="filterCallback"
                    />
                </template>
            </Column>
            <Column
                field="nom_ar"
                header="Nom (AR)"
                sortable
                style="min-width: 12rem"
            >
                <template #body="{ data }">
                    <span>{{ data.nom_ar }}</span>
                </template>
                <template #filter="{ filterModel, filterCallback }">
                    <InputText
                        v-model="filterModel.value"
                        type="text"
                        class="p-column-filter"
                        placeholder="Rechercher par nom (AR)"
                        @input="filterCallback"
                    />
                </template>
            </Column>
            <Column
                field="gouvernerat_fr"
                header="Gouvernorat"
                sortable
                style="min-width: 12rem"
            >
                <template #body="{ data }">
                    <span>{{ data.gouvernerat_fr }}</span>
                </template>
                <template #filter="{ filterModel, filterCallback }">
                    <Dropdown
                        v-model="filterModel.value"
                        :options="gouvernorats"
                        placeholder="Sélectionner un gouvernorat"
                        showClear
                        @change="filterCallback"
                    />
                </template>
            </Column>
            <Column
                field="statut"
                header="Statut"
                sortable
                style="min-width: 12rem"
            >
                <template #body="{ data }">
                    <div class="flex justify-content-center">
                        <Tag
                            :value="data.statut"
                            :severity="getSeverity(data.statut)"
                        />
                    </div>
                </template>
                <template #filter="{ filterModel, filterCallback }">
                    <Dropdown
                        v-model="filterModel.value"
                        :options="statuts"
                        placeholder="Sélectionner un statut"
                        showClear
                        @change="filterCallback"
                    >
                        <template #option="slotProps">
                            <Tag
                                :value="slotProps.option"
                                :severity="getSeverity(slotProps.option)"
                            />
                        </template>
                    </Dropdown>
                </template>
            </Column>
            <Column header="Actions" style="min-width: 15rem" frozen>
                <template #body="{ data }">
                    <div class="flex gap-2">
                        <Button
                            icon="pi pi-pencil"
                            severity="info"
                            text
                            @click="editCentre(data)"
                        />
                        <Button
                            icon="pi pi-trash"
                            severity="danger"
                            text
                            @click="confirmDeleteCentre(data)"
                        />
                        <Button
                            icon="pi pi-link"
                            severity="success"
                            text
                            @click="openAssociateAssociations(data)"
                        />
                    </div>
                </template>
            </Column>

            <template #expansion="{ data }">
                <div class="p-3 surface-100">
                    <div
                        class="flex justify-content-between align-items-center mb-2"
                    >
                        <h5 class="text-primary" style="font-weight: bold">
                            Les spécialités ouvertes au
                            <span style="color: #ef4444">{{
                                data.nom_fr
                            }}</span>
                        </h5>
                    </div>
                    <DataTable
                        :value="data.specialites || []"
                        class="p-datatable-sm"
                    >
                        <Column
                            field="code"
                            header="Code"
                            style="min-width: 10rem"
                        ></Column>
                        <Column
                            field="nom_fr"
                            header="Nom (FR)"
                            style="min-width: 12rem"
                        ></Column>
                        <Column
                            field="nom_ar"
                            header="Nom (AR)"
                            style="min-width: 12rem"
                        ></Column>
                        <Column
                            field="pivot.date_debut"
                            header="Date Début"
                            style="min-width: 10rem"
                        >
                            <template #body="{ data }">
                                <span v-if="!data.editing">{{
                                    data.pivot.date_debut
                                }}</span>
                                <Calendar
                                    v-else
                                    v-model="data.editedFields.date_debut"
                                    dateFormat="yy-mm-dd"
                                />
                            </template>
                        </Column>
                        <Column
                            field="pivot.date_fin"
                            header="Date Fin"
                            style="min-width: 10rem"
                        >
                            <template #body="{ data }">
                                <span v-if="!data.editing">{{
                                    data.pivot.date_fin || ''
                                }}</span>
                                <Calendar
                                    v-else
                                    v-model="data.editedFields.date_fin"
                                    dateFormat="yy-mm-dd"
                                />
                            </template>
                        </Column>
                        <Column
                            field="pivot.statut"
                            header="Statut"
                            style="min-width: 10rem"
                        >
                            <template #body="{ data }">
                                <Tag
                                    v-if="!data.editing"
                                    :value="
                                        data.pivot.statut ? 'Actif' : 'Inactif'
                                    "
                                    :severity="
                                        data.pivot.statut ? 'success' : 'danger'
                                    "
                                />
                                <Dropdown
                                    v-else
                                    v-model="data.editedFields.statut"
                                    :options="[
                                        { label: 'Actif', value: 1 },
                                        { label: 'Inactif', value: 0 },
                                    ]"
                                    optionLabel="label"
                                    optionValue="value"
                                />
                            </template>
                        </Column>
                        <Column
                            field="pivot.observation"
                            header="Observation"
                            style="min-width: 15rem"
                        >
                            <template #body="{ data }">
                                <span v-if="!data.editing">{{
                                    data.pivot.observation || ''
                                }}</span>
                                <Textarea
                                    v-else
                                    v-model="data.editedFields.observation"
                                    rows="2"
                                />
                            </template>
                        </Column>
                        <Column header="Actions" style="min-width: 10rem">
                            <template #body="{ data }">
                                <div class="flex gap-2">
                                    <Button
                                        v-if="!data.editing"
                                        icon="pi pi-pencil"
                                        class="p-button-rounded p-button-text p-button-sm"
                                        @click="startEditing(data)"
                                    />
                                    <Button
                                        v-if="data.editing"
                                        icon="pi pi-check"
                                        class="p-button-rounded p-button-success p-button-text p-button-sm"
                                        @click="saveEditing(data)"
                                    />
                                    <Button
                                        v-if="data.editing"
                                        icon="pi pi-times"
                                        class="p-button-rounded p-button-danger p-button-text p-button-sm"
                                        @click="cancelEditing(data)"
                                    />
                                    <Button
                                        v-if="!data.editing"
                                        icon="pi pi-trash"
                                        class="p-button-rounded p-button-danger p-button-text p-button-sm"
                                        @click="deleteAssociation(data)"
                                    />
                                </div>
                            </template>
                        </Column>
                    </DataTable>
                </div>
            </template>
        </DataTable>

        <CentresForm
            :visible="formVisible"
            :centreToEdit="centreToEdit"
            @update:visible="formVisible = $event"
            @save="handleSaveCentre"
            @update="handleUpdateCentre"
            @close="closeForm"
        />

        <Dialog
            v-model:visible="deleteFormVisible"
            header="Confirmation de suppression"
            :modal="true"
            :style="{ width: '450px' }"
        >
            <div class="p-fluid">
                <p v-if="centreToDelete">
                    Êtes-vous sûr de vouloir supprimer le centre
                    <strong>{{ centreToDelete.nom_fr }}</strong> ?<br />
                    Veuillez entrer le mot de passe pour confirmer :
                </p>
                <InputText
                    v-model="deletePassword"
                    type="password"
                    placeholder="Mot de passe"
                    class="mt-2"
                />
                <small v-if="deleteError" class="text-red-500">{{
                    deleteError
                }}</small>
            </div>
            <template #footer>
                <Button
                    label="Non"
                    icon="pi pi-times"
                    class="p-button-text"
                    @click="deleteFormVisible = false"
                />
                <Button
                    label="Oui"
                    icon="pi pi-check"
                    class="p-button-text"
                    @click="deleteCentre"
                />
            </template>
        </Dialog>

        <Dialog
            v-model:visible="associateFormVisible"
            header="Associer des spécialités"
            :modal="true"
            :style="{ width: '600px' }"
        >
            <div class="p-fluid">
                <p>
                    Associer des spécialités au centre
                    <strong>{{ selectedCentre?.nom_fr }}</strong> :
                </p>
                <div class="flex gap-2 mb-3">
                    <Button
                        label="مقيس"
                        :severity="
                            specialiteType === 'مقيس' ? 'success' : 'secondary'
                        "
                        @click="setSpecialiteType('مقيس')"
                    />
                    <Button
                        label="غير مقيس"
                        :severity="
                            specialiteType === 'غير مقيس'
                                ? 'danger'
                                : 'secondary'
                        "
                        @click="setSpecialiteType('غير مقيس')"
                    />
                    <Button
                        label="جديد"
                        :severity="
                            specialiteType === 'جديد' ? 'warning' : 'secondary'
                        "
                        @click="setSpecialiteType('جديد')"
                    />
                </div>
                <Dropdown
                    v-if="specialiteType !== 'جديد'"
                    v-model="selectedSecteur"
                    :options="filteredSecteurs"
                    optionLabel="nom_fr"
                    placeholder="Sélectionner un secteur"
                    class="w-full mb-3"
                    @change="fetchSousSecteurs"
                />
                <Dropdown
                    v-if="specialiteType === 'مقيس'"
                    v-model="selectedSousSecteur"
                    :options="sousSecteurs"
                    optionLabel="nom_fr"
                    placeholder="Sélectionner un sous-secteur"
                    class="w-full mb-3"
                    @change="fetchAssociations"
                    :disabled="!selectedSecteur"
                />
                <MultiSelect
                    v-model="selectedAssociations"
                    :options="filteredAssociations"
                    optionLabel="code_nom_fr"
                    placeholder="Sélectionner des spécialités"
                    class="w-full mb-3"
                    display="chip"
                />
                <Calendar
                    v-model="dateDebut"
                    dateFormat="yy-mm-dd"
                    placeholder="Date de début"
                    class="w-full mb-3"
                    :required="true"
                />
                <Calendar
                    v-model="dateFin"
                    dateFormat="yy-mm-dd"
                    placeholder="Date de fin (facultatif)"
                    class="w-full mb-3"
                />
                <Textarea
                    v-model="observation"
                    rows="3"
                    placeholder="Observation"
                    class="w-full mb-3"
                />
            </div>
            <template #footer>
                <Button
                    label="Annuler"
                    icon="pi pi-times"
                    class="p-button-text"
                    @click="associateFormVisible = false"
                />
                <Button
                    label="Associer"
                    icon="pi pi-check"
                    class="p-button-text"
                    @click="associateAssociations"
                />
            </template>
        </Dialog>

        <CentresImportError
            :visible="importErrorsVisible"
            :errors="importErrors"
            @update:visible="importErrorsVisible = $event"
            @line-imported="handleLineImported"
            @close="closeImportErrors"
        />

        <Toast />
    </div>
</template>

<script>
import { FilterMatchMode, FilterOperator } from 'primevue/api';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Dropdown from 'primevue/dropdown';
import MultiSelect from 'primevue/multiselect';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import Calendar from 'primevue/calendar';
import Textarea from 'primevue/textarea';
import CentresForm from '@/Components/Atfp/Centres/CentresForm.vue';
import CentresImportError from '@/Components/Atfp/ImportError/CentresImportError.vue';

export default {
    components: {
        Button,
        InputText,
        DataTable,
        Column,
        Tag,
        Dropdown,
        MultiSelect,
        Dialog,
        Toast,
        Calendar,
        Textarea,
        CentresForm,
        CentresImportError,
    },
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            centres: [],
            selectedCentres: null,
            filters: null,
            statuts: [
                'Actif',
                'Inactif',
                'En construction',
                'En rénovation',
                'Fermé définitivement',
            ],
            gouvernorats: [
                'ariana',
                'beja',
                'ben_arous',
                'bizerte',
                'gabes',
                'gafsa',
                'jendouba',
                'kairouan',
                'kasserine',
                'kebili',
                'manouba',
                'kef',
                'mahdia',
                'medenine',
                'monastir',
                'nabeul',
                'sfax',
                'sidi_bouzid',
                'siliana',
                'sousse',
                'tataouine',
                'tozeur',
                'tunis',
                'zaghouan',
            ],
            annees: [],
            selectedAnnee: null,
            formVisible: false,
            deleteFormVisible: false,
            associateFormVisible: false,
            importErrorsVisible: false,
            centreToEdit: null,
            centreToDelete: null,
            selectedCentre: null,
            deletePassword: '',
            deleteError: '',
            loading: true,
            expandedRows: [],
            secteurs: [],
            sousSecteurs: [],
            associations: [],
            selectedSecteur: null,
            selectedSousSecteur: null,
            selectedAssociations: [],
            specialiteType: 'مقيس',
            dateDebut: null,
            dateFin: null,
            observation: '',
            importErrors: [],
            itemToEdit: null,
        };
    },
    computed: {
        filteredCentres() {
            if (!this.selectedAnnee) return this.centres;

            const selectedYear = this.annees.find(
                (a) => a.id === this.selectedAnnee
            );
            if (!selectedYear) return this.centres;

            const yearStart = new Date(selectedYear.date_debut);
            const yearEnd = new Date(selectedYear.date_fin);

            return this.centres.map((centre) => {
                const filteredSpecialites = centre.specialites.filter(
                    (specialite) => {
                        const dateDebutAssoc = specialite.pivot.date_debut
                            ? new Date(specialite.pivot.date_debut)
                            : null;
                        const dateFinAssoc = specialite.pivot.date_fin
                            ? new Date(specialite.pivot.date_fin)
                            : null;

                        if (!dateDebutAssoc) return false;

                        if (dateDebutAssoc < yearEnd && !dateFinAssoc)
                            return true;

                        if (
                            dateDebutAssoc < yearEnd &&
                            dateFinAssoc &&
                            dateFinAssoc > yearStart
                        )
                            return true;

                        return false;
                    }
                );

                return {
                    ...centre,
                    specialites: filteredSpecialites,
                };
            });
        },
        filteredSecteurs() {
            if (this.specialiteType === 'مقيس') {
                return this.secteurs.filter((s) => s.type === 'مقيس');
            } else if (this.specialiteType === 'غير مقيس') {
                return this.secteurs.filter((s) => s.type === 'غير مقيس');
            }
            return [];
        },
        filteredAssociations() {
            if (!Array.isArray(this.associations)) return [];
            return this.associations
                .map((a) => ({
                    ...a,
                    code_nom_fr: `${a.code}_${a.nom_fr}`,
                }))
                .filter((a) => {
                    if (this.specialiteType === 'جديد') {
                        return (
                            a.type === 'جديد' &&
                            !a.secteur_id &&
                            !a.sous_secteur_id
                        );
                    } else if (this.specialiteType === 'غير مقيس') {
                        return (
                            a.type === 'غير مقيس' &&
                            a.secteur_id ===
                                (this.selectedSecteur?.id || null) &&
                            !a.sous_secteur_id
                        );
                    } else if (this.specialiteType === 'مقيس') {
                        return (
                            a.type === 'مقيس' &&
                            a.sous_secteur_id ===
                                (this.selectedSousSecteur?.id || null)
                        );
                    }
                    return false;
                });
        },
    },
    created() {
        this.initFilters();
        this.fetchCentres();
        this.fetchSecteurs();
        this.fetchAssociations();
        this.fetchAnnees();
    },
    methods: {
        clearFilter() {
            this.initFilters();
            this.selectedAnnee = null;
        },
        clearAnneeFilter() {
            this.selectedAnnee = null;
            this.filterByAnnee();
        },
        initFilters() {
            this.filters = {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                code: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
                nom_fr: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
                nom_ar: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
                gouvernerat_fr: {
                    operator: FilterOperator.OR,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.EQUALS },
                    ],
                },
                statut: {
                    operator: FilterOperator.OR,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.EQUALS },
                    ],
                },
            };
        },
        getSeverity(statut) {
            switch (statut) {
                case 'Actif':
                    return 'success';
                case 'Inactif':
                    return 'warning';
                case 'En construction':
                    return 'info';
                case 'En rénovation':
                    return 'secondary';
                case 'Fermé définitivement':
                    return 'danger';
                default:
                    return 'secondary';
            }
        },
        filterByAnnee() {
            this.expandedRows = [];
        },
        getAnneeIntitule(anneeId) {
            const annee = this.annees.find((a) => a.id === anneeId);
            return annee ? annee.intitule : '';
        },
        async fetchAnnees() {
            try {
                const response = await axios.get('/api/annees-formation');
                this.annees = response.data;
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Impossible de charger les années.',
                    life: 3000,
                });
            }
        },
        openForm() {
            this.centreToEdit = null;
            this.formVisible = true;
        },
        closeForm() {
            this.formVisible = false;
            this.centreToEdit = null;
            this.fetchCentres();
        },
        expandAll() {
            this.expandedRows = this.filteredCentres.map((centre) => centre.id);
        },
        collapseAll() {
            this.expandedRows = [];
        },
        async fetchCentres() {
            this.loading = true;
            try {
                const response = await axios.get('/api/centres');
                this.centres = response.data.map((centre) => ({
                    ...centre,
                    specialites: centre.specialites.map((spec) => ({
                        ...spec,
                        editing: false,
                    })),
                }));
                console.log(
                    'Données des centres:',
                    JSON.stringify(this.centres, null, 2)
                );
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Erreur lors du chargement des centres.',
                    life: 3000,
                });
                console.error('Erreur fetchCentres:', error);
            } finally {
                this.loading = false;
            }
        },
        async fetchSecteurs() {
            try {
                const response = await axios.get('/api/secteurs');
                this.secteurs = response.data;
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Erreur lors du chargement des secteurs.',
                    life: 3000,
                });
            }
        },
        async fetchSousSecteurs() {
            this.selectedSousSecteur = null;
            this.selectedAssociations = [];
            if (!this.selectedSecteur || this.specialiteType !== 'مقيس') {
                this.sousSecteurs = [];
                return;
            }
            try {
                const response = await axios.get(
                    `/api/sous-secteurs?secteur_id=${this.selectedSecteur.id}`
                );
                this.sousSecteurs = response.data.filter(
                    (ss) => ss.secteur_id === this.selectedSecteur.id
                );
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Erreur lors du chargement des sous-secteurs.',
                    life: 3000,
                });
            }
        },
        async fetchAssociations() {
            try {
                const response = await axios.get('/api/specialites');
                this.associations = response.data;
                console.log('Spécialités chargées:', this.associations);
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Erreur lors du chargement des spécialités.',
                    life: 3000,
                });
                console.error('Erreur fetchAssociations:', error);
            }
        },
        editCentre(centre) {
            this.centreToEdit = { ...centre };
            this.formVisible = true;
        },
        async handleSaveCentre(newCentre) {
            try {
                const response = await axios.post('/api/centres', newCentre);
                this.centres.unshift(response.data);
                this.formVisible = false;
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Centre ajouté.',
                    life: 3000,
                });
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        'Erreur lors de l’ajout.',
                    life: 3000,
                });
            }
        },
        async handleUpdateCentre(updatedCentre) {
            try {
                const response = await axios.put(
                    `/api/centres/${updatedCentre.id}`,
                    updatedCentre
                );
                const index = this.centres.findIndex(
                    (c) => c.id === updatedCentre.id
                );
                if (index !== -1) this.centres.splice(index, 1, response.data);
                this.formVisible = false;
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Centre mis à jour.',
                    life: 3000,
                });
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Erreur lors de la mise à jour.',
                    life: 3000,
                });
            }
        },
        confirmDeleteCentre(centre) {
            this.centreToDelete = centre;
            this.deleteFormVisible = true;
            this.deletePassword = '';
            this.deleteError = '';
        },
        async deleteCentre() {
            if (this.centreToDelete) {
                if (this.deletePassword !== 'ha') {
                    this.deleteError = 'Mot de passe incorrect.';
                    return;
                }
                try {
                    await axios.delete(
                        `/api/centres/${this.centreToDelete.id}`,
                        {
                            data: { password: this.deletePassword },
                        }
                    );
                    this.centres = this.centres.filter(
                        (c) => c.id !== this.centreToDelete.id
                    );
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Centre supprimé.',
                        life: 3000,
                    });
                } catch (error) {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'Erreur lors de la suppression.',
                        life: 3000,
                    });
                } finally {
                    this.deleteFormVisible = false;
                    this.centreToDelete = null;
                    this.deletePassword = '';
                }
            }
        },
        openAssociateAssociations(centre) {
            this.selectedCentre = centre;
            this.selectedSecteur = null;
            this.selectedSousSecteur = null;
            this.selectedAssociations = [];
            this.specialiteType = 'مقيس';
            this.dateDebut = null;
            this.dateFin = null;
            this.observation = '';
            this.sousSecteurs = [];
            this.associateFormVisible = true;
        },
        setSpecialiteType(type) {
            this.specialiteType = type;
            this.selectedSecteur = null;
            this.selectedSousSecteur = null;
            this.selectedAssociations = [];
            this.sousSecteurs = [];
        },
        formatDate(date) {
            if (!date || !(date instanceof Date)) return null;
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        },
        async associateAssociations() {
            if (this.selectedAssociations.length === 0 || !this.dateDebut) {
                this.toast.add({
                    severity: 'warn',
                    summary: 'Attention',
                    detail: 'Sélectionnez au moins une spécialité et une date de début.',
                    life: 3000,
                });
                return;
            }
            try {
                const payload = {
                    centre_id: this.selectedCentre.id,
                    specialite_ids: this.selectedAssociations.map((a) => a.id),
                    date_debut: this.formatDate(this.dateDebut),
                    date_fin: this.formatDate(this.dateFin),
                    statut: this.dateFin ? 0 : 1,
                    observation: this.observation,
                };
                await axios.post('/api/centres/associate-specialites', payload);
                this.fetchCentres();
                this.associateFormVisible = false;
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Spécialités associées.',
                    life: 3000,
                });
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Erreur lors de l’association.',
                    life: 3000,
                });
                console.error('Erreur associateAssociations:', error);
            }
        },
        startEditing(specialite) {
            console.log('Éditer spécialité:', specialite);
            const centre = this.centres.find(
                (c) => c.id === specialite.pivot.centre_id
            );
            const originalSpecialite = centre.specialites.find(
                (s) => s.id === specialite.id
            );
            originalSpecialite.editing = true;
            originalSpecialite.editedFields = {
                date_debut: specialite.pivot.date_debut
                    ? new Date(specialite.pivot.date_debut)
                    : null,
                date_fin: specialite.pivot.date_fin
                    ? new Date(specialite.pivot.date_fin)
                    : null,
                statut: specialite.pivot.statut,
                observation: specialite.pivot.observation || '',
                centre_id: specialite.pivot.centre_id,
                specialite_id: specialite.id,
            };
            this.itemToEdit = originalSpecialite;
        },
        async saveEditing(specialite) {
            try {
                const payload = {
                    date_debut: this.formatDate(
                        specialite.editedFields.date_debut
                    ),
                    date_fin: this.formatDate(specialite.editedFields.date_fin),
                    statut: specialite.editedFields.statut,
                    observation: specialite.editedFields.observation,
                    centre_id: specialite.pivot.centre_id,
                    specialite_id: specialite.id,
                };
                await axios.put(
                    `/api/centres/${specialite.pivot.centre_id}/specialites/${specialite.id}`,
                    payload
                );
                specialite.editing = false;
                delete specialite.editedFields;
                this.fetchCentres();
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Spécialité mise à jour.',
                    life: 3000,
                });
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Erreur lors de la mise à jour.',
                    life: 3000,
                });
            }
            this.itemToEdit = null;
        },
        cancelEditing(specialite) {
            specialite.editing = false;
            delete specialite.editedFields;
            this.itemToEdit = null;
        },
        async deleteAssociation(specialite) {
            try {
                await axios.delete(
                    `/api/centres/${specialite.pivot.centre_id}/specialites/${specialite.id}`
                );
                this.fetchCentres();
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Spécialité supprimée.',
                    life: 3000,
                });
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Erreur lors de la suppression.',
                    life: 3000,
                });
            }
        },
        async exportXls() {
            try {
                const response = await axios.get('/api/centres/export-xls', {
                    responseType: 'blob',
                });
                const url = window.URL.createObjectURL(
                    new Blob([response.data])
                );
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'centres_export.xlsx');
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Exportation réussie.',
                    life: 3000,
                });
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Erreur lors de l’exportation.',
                    life: 3000,
                });
            }
        },
        async importXls(event) {
            const file = event.target.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('file', file);

            try {
                const response = await axios.post(
                    '/api/centres/import-xls',
                    formData,
                    {
                        headers: { 'Content-Type': 'multipart/form-data' },
                    }
                );
                this.fetchCentres();
                if (response.data.error_lines.length > 0) {
                    this.importErrors = response.data.error_lines;
                    this.importErrorsVisible = true;
                } else {
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Importation réussie.',
                        life: 3000,
                    });
                }
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Erreur lors de l’importation.',
                    life: 3000,
                });
            } finally {
                this.$refs.fileInput.value = '';
            }
        },
        handleLineImported(error) {
            this.importErrors = this.importErrors.filter((e) => e !== error);
            this.fetchCentres();
        },
        closeImportErrors() {
            this.importErrorsVisible = false;
            this.importErrors = [];
        },
    },
};
</script>

<style scoped>
/* Pas de styles personnalisés supplémentaires */
</style>
