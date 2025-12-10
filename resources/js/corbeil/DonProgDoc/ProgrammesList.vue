<template>
    <DataTable
        v-model:expandedRows="expandedRows"
        v-model:filters="filters"
        stripedRows
        :value="filteredSpecialites"
        :rows="10"
        dataKey="id"
        filterDisplay="menu"
        :globalFilterFields="[
            'id',
            'code',
            'nom_ar',
            'nom_fr',
            'type',
            'diplome',
            'statut',
        ]"
        :loading="loading"
        scrollable
        scrollHeight="650px"
        :pt="{
            table: { style: 'min-width: 50rem' },
            bodyrow: ({ props }) => ({
                style: props.frozenRow ? 'font-weight: bold' : '',
            }),
        }"
        @rowExpand="onRowExpand"
        @rowCollapse="onRowCollapse"
    >
        <template #header>
            <div class="flex flex-column gap-2">
                <!-- Première ligne : Titre, filtre année et boutons -->
                <div
                    class="flex justify-content-between align-items-center mb-2"
                >
                    <div class="text-2xl font-bold text-primary">
                        Liste des programmes
                        <span :style="{ color: '#EF4444' }">actifs</span>
                        <span class="font-bold text-primary"> de l'année </span>
                        <span :style="{ color: '#EF4444' }">{{
                            selectedAnnee
                                ? getAnneeIntitule(selectedAnnee)
                                : 'en cours'
                        }}</span>
                    </div>
                    <div class="flex gap-2 align-items-center">
                        <Dropdown
                            v-model="selectedAnnee"
                            :options="annees"
                            optionLabel="intitule"
                            optionValue="id"
                            placeholder="Sélectionner une année"
                            @change="filterByAnnee"
                        />
                        <Button
                            label="مقيس"
                            :severity="getSeverity('مقيس')"
                            @click="setActiveTab('مقيس')"
                        />
                        <Button
                            label="غير مقيس"
                            :severity="getSeverity('غير مقيس')"
                            @click="setActiveTab('غير مقيس')"
                        />
                        <Button
                            label="جديد"
                            :severity="getSeverity('جديد')"
                            @click="setActiveTab('جديد')"
                        />
                    </div>
                </div>
                <!-- Deuxième ligne : Recherche et boutons dans un cadre fin -->
                <div
                    class="flex justify-content-between align-items-center mb-2 p-2 border-round surface-border border-1"
                >
                    <div class="flex gap-2 align-items-center">
                        <span class="p-input-icon-right">
                            <InputText
                                v-model="globalFilter"
                                placeholder="Rechercher dans les spécialités"
                                class="modern-input compact-filter wider-search"
                                @input="onGlobalFilter"
                            />
                            <i class="pi pi-search" />
                        </span>
                        <Button
                            icon="pi pi-times"
                            size="small"
                            severity="danger"
                            outlined
                            @click="clearSearch"
                        />
                    </div>
                    <div class="flex gap-2 align-items-center">
                        <Button
                            text
                            icon="pi pi-minus"
                            label="Tout Réduire"
                            @click="collapseAll"
                        />
                    </div>
                </div>
            </div>
        </template>

        <template #empty>
            <div v-if="!loading" class="text-center p-3">
                Aucune spécialité trouvée.
            </div>
            <div v-else class="text-center p-3">Chargement en cours...</div>
        </template>

        <Column expander style="width: 5rem" />
        <Column field="id" header="ID" :showFilterMenu="false">
            <template #filter="{ filterModel, filterCallback }">
                <InputText
                    v-model="filterModel.value"
                    @input="filterCallback()"
                    placeholder="Rechercher par ID"
                />
            </template>
        </Column>
        <Column field="code" header="Code" sortable style="min-width: 10rem">
            <template #body="{ data }">
                {{ data.code }}
            </template>
            <template #filter="{ filterModel, filterCallback }">
                <InputText
                    v-model="filterModel.value"
                    type="text"
                    placeholder="Rechercher par code"
                    @input="filterCallback"
                />
            </template>
        </Column>
        <Column field="nom_fr" header="Nom (Fr)" :showFilterMenu="false">
            <template #filter="{ filterModel, filterCallback }">
                <InputText
                    v-model="filterModel.value"
                    @input="filterCallback()"
                    placeholder="Rechercher par Nom (Fr)"
                />
            </template>
        </Column>
        <Column field="nom_ar" header="Nom (AR)" :showFilterMenu="false">
            <template #body="{ data }">
                {{ data.nom_ar }}
            </template>
            <template #filter="{ filterModel, filterCallback }">
                <InputText
                    v-model="filterModel.value"
                    @input="filterCallback()"
                    placeholder="Rechercher par Nom (AR)"
                />
            </template>
        </Column>
        <Column field="diplome" header="Diplôme">
            <template #body="{ data }">
                <Tag
                    :value="data.diplome"
                    :severity="getSeverity(data.diplome)"
                />
            </template>
            <template #filter="{ filterModel, filterCallback }">
                <Dropdown
                    v-model="filterModel.value"
                    :options="['BTS', 'BTP', 'CAP', 'CC', 'CFA']"
                    placeholder="Sélectionner un diplôme"
                    @change="filterCallback()"
                />
            </template>
        </Column>
        <Column field="programmesCount" header="Programmes">
            <template #body="{ data }">
                {{ data.programmes ? data.programmes.length : 0 }}
            </template>
        </Column>

        <template #expansion="slotProps">
            <div class="p-3 surface-100">
                <div
                    class="flex justify-content-between align-items-center mb-2"
                >
                    <h5 class="text-primary" style="font-weight: bold">
                        Programmes de formation pour la spécialité
                        <span style="color: #ef4444">{{
                            slotProps.data.nom_fr
                        }}</span>
                    </h5>
                    <div class="flex gap-2 align-items-center">
                        <span class="p-input-icon-right">
                            <InputText
                                v-model="programmeFilter[slotProps.data.id]"
                                placeholder="Rechercher dans les programmes"
                                size="small"
                                @input="onProgrammeFilter(slotProps.data.id)"
                            />
                            <i class="pi pi-search" />
                        </span>
                        <Button
                            icon="pi pi-plus"
                            severity="success"
                            size="small"
                            @click="openProgrammeForm(slotProps.data)"
                        />
                    </div>
                </div>
                <DataTable
                    v-model:expandedRows="expandedProgrammes[slotProps.data.id]"
                    :value="
                        filteredProgrammes(
                            slotProps.data.programmes,
                            slotProps.data.id
                        )
                    "
                    dataKey="id"
                    @rowExpand="onProgrammeExpand"
                    @rowCollapse="onProgrammeCollapse"
                >
                    <Column expander style="width: 5rem" />
                    <Column field="id" header="Id"></Column>
                    <Column field="version" header="Version"></Column>
                    <Column field="date_debut" header="Date Début"></Column>
                    <Column field="date_fin" header="Date Fin"></Column>
                    <Column field="actif" header="Actif">
                        <template #body="{ data }">
                            <i
                                :class="[
                                    'pi',
                                    data.actif
                                        ? 'pi-check-circle text-green-500'
                                        : 'pi-times-circle text-red-500',
                                ]"
                            ></i>
                        </template>
                    </Column>
                    <Column field="specificCount" header="Modules ET">
                        <template #body="{ data }">
                            {{ getModuleCounts(data.modules).specificCount }}
                        </template>
                    </Column>
                    <Column field="specificHours" header="Heures ET">
                        <template #body="{ data }">
                            {{ getModuleCounts(data.modules).specificHours }}
                        </template>
                    </Column>
                    <Column field="generalCount" header="Modules EG">
                        <template #body="{ data }">
                            {{ getModuleCounts(data.modules).generalCount }}
                        </template>
                    </Column>
                    <Column field="generalHours" header="Heures EG">
                        <template #body="{ data }">
                            {{ getModuleCounts(data.modules).generalHours }}
                        </template>
                    </Column>
                    <Column field="totalHours" header="Total">
                        <template #body="{ data }">
                            {{
                                getModuleCounts(data.modules).specificHours +
                                getModuleCounts(data.modules).generalHours
                            }}
                        </template>
                    </Column>
                    <Column
                        header="Actions"
                        :exportable="false"
                        style="min-width: 8rem"
                    >
                        <template #body="slotProps">
                            <div class="flex gap-2">
                                <Button
                                    icon="pi pi-pencil"
                                    severity="info"
                                    text
                                    @click="
                                        promptPassword(
                                            'editProgramme',
                                            slotProps.data
                                        )
                                    "
                                />
                                <Button
                                    icon="pi pi-trash"
                                    severity="danger"
                                    text
                                    @click="
                                        promptPassword(
                                            'deleteProgramme',
                                            slotProps.data
                                        )
                                    "
                                />
                            </div>
                        </template>
                    </Column>
                    <template #expansion="slotProps">
                        <div class="p-3 surface-100">
                            <div
                                class="flex justify-content-between align-items-center mb-2"
                            >
                                <h5 style="font-weight: bold">
                                    <span style="color: #93c5fd"
                                        >Modules du programme d'études de la
                                        version
                                    </span>
                                    <span style="color: #ef4444">{{
                                        slotProps.data.version
                                    }}</span>
                                </h5>
                                <div class="flex gap-2 align-items-center">
                                    <span class="p-input-icon-right">
                                        <InputText
                                            v-model="
                                                moduleFilter[slotProps.data.id]
                                            "
                                            placeholder="Rechercher dans les modules"
                                            size="small"
                                            @input="
                                                onModuleFilter(
                                                    slotProps.data.id
                                                )
                                            "
                                        />
                                        <i class="pi pi-search" />
                                    </span>
                                    <Button
                                        icon="pi pi-plus"
                                        severity="success"
                                        size="small"
                                        @click="openModuleForm(slotProps.data)"
                                    />
                                    <Button
                                        icon="pi pi-upload"
                                        severity="warning"
                                        size="small"
                                        @click="importModules(slotProps.data)"
                                    />
                                    <Button
                                        icon="pi pi-download"
                                        severity="info"
                                        size="small"
                                        @click="
                                            handleExportClick(
                                                slotProps.data.specialite_id,
                                                slotProps.data.id
                                            )
                                        "
                                    />
                                </div>
                            </div>
                            <DataTable
                                :value="
                                    filteredModules(
                                        slotProps.data.modules,
                                        slotProps.data.id
                                    )
                                "
                                dataKey="id"
                            >
                                <Column field="id" header="Id"></Column>
                                <Column field="code" header="Code"></Column>
                                <Column field="titre" header="Titre"></Column>
                                <Column field="type" header="Type"></Column>
                                <Column
                                    field="heures_theoriques"
                                    header="Heures Théoriques"
                                ></Column>
                                <Column
                                    field="heures_pratiques"
                                    header="Heures Pratiques"
                                ></Column>
                                <Column
                                    field="heures_evaluation"
                                    header="Heures Évaluation"
                                ></Column>
                                <Column
                                    field="total_heures"
                                    header="Total Heures"
                                >
                                    <template #body="{ data }">
                                        {{ calculateTotalHours(data) }}
                                    </template>
                                </Column>
                                <Column
                                    field="contenu_pdf"
                                    header="Contenu PDF"
                                >
                                    <template #body="{ data }">
                                        <span
                                            v-if="data.id && data.contenu_pdf"
                                        >
                                            <a
                                                :href="`/storage/modules/${data.contenu_pdf}`"
                                                target="_blank"
                                                @click.prevent="
                                                    downloadPdf(
                                                        data.id,
                                                        data.contenu_pdf
                                                    )
                                                "
                                                class="text-blue-600 hover:underline"
                                                >Télécharger</a
                                            >
                                        </span>
                                        <span v-else>N/A</span>
                                    </template>
                                </Column>
                                <Column
                                    header="Actions"
                                    :exportable="false"
                                    style="min-width: 8rem"
                                >
                                    <template #body="slotProps">
                                        <div class="flex gap-2">
                                            <Button
                                                icon="pi pi-pencil"
                                                severity="info"
                                                text
                                                @click="
                                                    promptPassword(
                                                        'editModule',
                                                        slotProps.data
                                                    )
                                                "
                                            />
                                            <Button
                                                icon="pi pi-trash"
                                                severity="danger"
                                                text
                                                @click="
                                                    promptPassword(
                                                        'deleteModule',
                                                        slotProps.data
                                                    )
                                                "
                                            />
                                        </div>
                                    </template>
                                </Column>
                            </DataTable>
                        </div>
                    </template>
                </DataTable>
            </div>
        </template>
    </DataTable>

    <ProgrammesForm
        :visible="programmeFormVisible"
        :initialData="selectedProgramme"
        :specialiteId="
            selectedSpecialiteForProgramme
                ? selectedSpecialiteForProgramme.id
                : null
        "
        @update:visible="programmeFormVisible = $event"
        @save="handleProgrammeSaved"
        @update="handleProgrammeUpdated"
    />
    <ModulesForm
        :visible="moduleFormVisible"
        :initialData="selectedModule"
        :programmeId="
            selectedProgrammeForModule ? selectedProgrammeForModule.id : null
        "
        @update:visible="moduleFormVisible = $event"
        @save="handleModuleSaved"
        @update="handleModuleUpdated"
    />
    <ConfirmDialog group="dialog"></ConfirmDialog>
    <ModulesImportError
        :visible="showImportErrors"
        :errors="importErrors"
        @update:visible="showImportErrors = $event"
        @line-imported="handleLineImported"
        @close="showImportErrors = false"
    />
    <Toast />
</template>

<script>
import axios from 'axios';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Button from 'primevue/button';
import ConfirmDialog from 'primevue/confirmdialog';
import Toast from 'primevue/toast';
import { FilterMatchMode } from 'primevue/api';
import { useConfirm } from 'primevue/useconfirm';
import { useToast } from 'primevue/usetoast';
import ProgrammesForm from '@/corbeil/DonProgDoc/ProgrammesForm.vue';
import ModulesForm from '@/corbeil/DonProgDoc/ModulesForm.vue';
import ModulesImportError from '@/corbeil/DonProgDoc/ModulesImportError.vue';
import ExcelJS from 'exceljs';
import { saveAs } from 'file-saver';

axios.defaults.baseURL = 'http://127.0.0.1:8000';

export default {
    components: {
        DataTable,
        Column,
        Tag,
        InputText,
        Dropdown,
        Button,
        ConfirmDialog,
        Toast,
        ProgrammesForm,
        ModulesForm,
        ModulesImportError,
    },
    setup() {
        const confirm = useConfirm();
        const toast = useToast();
        return { confirm, toast };
    },
    data() {
        return {
            specialites: [],
            expandedRows: [],
            expandedProgrammes: {},
            loading: true,
            activeTab: 'مقيس',
            filters: {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                id: { value: null, matchMode: FilterMatchMode.EQUALS },
                code: { value: null, matchMode: FilterMatchMode.CONTAINS },
                nom_fr: { value: null, matchMode: FilterMatchMode.CONTAINS },
                nom_ar: { value: null, matchMode: FilterMatchMode.CONTAINS },
                diplome: { value: null, matchMode: FilterMatchMode.EQUALS },
            },
            programmeFormVisible: false,
            moduleFormVisible: false,
            selectedProgramme: {},
            selectedModule: {},
            selectedSpecialiteForProgramme: null,
            selectedProgrammeForModule: null,
            annees: [],
            selectedAnnee: null,
            globalFilter: '',
            programmeFilter: {},
            moduleFilter: {},
            showImportErrors: false,
            importErrors: [],
        };
    },
    computed: {
        filteredSpecialites() {
            let result = this.specialites.filter(
                (s) => s.type === this.activeTab
            );
            if (this.globalFilter) {
                const filterLower = this.globalFilter.toLowerCase();
                result = result.filter(
                    (s) =>
                        s.id.toString().includes(filterLower) ||
                        s.code?.toLowerCase().includes(filterLower) ||
                        s.nom_fr?.toLowerCase().includes(filterLower) ||
                        s.nom_ar?.toLowerCase().includes(filterLower) ||
                        s.diplome?.toLowerCase().includes(filterLower)
                );
            }
            return result;
        },
    },
    methods: {
        async fetchData() {
            this.loading = true;
            try {
                const response = await axios.get('/api/specialites');
                this.specialites = response.data.map((s) => {
                    s.programmes = s.programmes || [];
                    s.programmes.forEach((p) => (p.modules = p.modules || []));
                    this.expandedProgrammes[s.id] =
                        this.expandedProgrammes[s.id] || [];
                    return s;
                });
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Échec de la récupération des données',
                    life: 3000,
                });
                console.error('Fetch data error:', error);
            } finally {
                this.loading = false;
            }
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
                console.error('Fetch annees error:', error);
            }
        },
        filterByAnnee() {
            this.fetchData();
        },
        getAnneeIntitule(anneeId) {
            const annee = this.annees.find((a) => a.id === anneeId);
            return annee ? annee.intitule : '';
        },
        filteredProgrammes(programmes, specialiteId) {
            let result = programmes;
            if (this.selectedAnnee) {
                const selectedAnnee = this.annees.find(
                    (a) => a.id === this.selectedAnnee
                );
                if (selectedAnnee) {
                    const dateDebutAnnee = new Date(selectedAnnee.date_debut);
                    result = result.filter((p) => {
                        const dateDebutProgramme = new Date(p.date_debut);
                        const dateFinProgramme = p.date_fin
                            ? new Date(p.date_fin)
                            : null;
                        return (
                            (!dateFinProgramme ||
                                dateFinProgramme >= dateDebutAnnee) &&
                            dateDebutProgramme <= dateDebutAnnee
                        );
                    });
                }
            }
            if (this.programmeFilter[specialiteId]) {
                const filterLower =
                    this.programmeFilter[specialiteId].toLowerCase();
                result = result.filter(
                    (p) =>
                        p.id.toString().includes(filterLower) ||
                        p.version?.toLowerCase().includes(filterLower) ||
                        p.date_debut?.toLowerCase().includes(filterLower) ||
                        p.date_fin?.toLowerCase().includes(filterLower)
                );
            }
            return result;
        },
        filteredModules(modules, programmeId) {
            let result = modules;
            if (this.moduleFilter[programmeId]) {
                const filterLower =
                    this.moduleFilter[programmeId].toLowerCase();
                result = result.filter(
                    (m) =>
                        m.id.toString().includes(filterLower) ||
                        m.code?.toLowerCase().includes(filterLower) ||
                        m.titre?.toLowerCase().includes(filterLower) ||
                        m.type?.toLowerCase().includes(filterLower) ||
                        m.heures_theoriques?.toString().includes(filterLower) ||
                        m.heures_pratiques?.toString().includes(filterLower) ||
                        m.heures_evaluation?.toString().includes(filterLower)
                );
            }
            return result;
        },
        getSeverity(value) {
            const severityMap = {
                مقيس: this.activeTab === 'مقيس' ? 'success' : 'secondary',
                'غير مقيس':
                    this.activeTab === 'غير مقيس' ? 'danger' : 'secondary',
                جديد: this.activeTab === 'جديد' ? 'warning' : 'secondary',
                BTS: 'info',
                BTP: 'secondary',
                CAP: 'warning',
                CC: 'success',
                CFA: 'danger',
            };
            return severityMap[value] || 'secondary';
        },
        collapseAll() {
            this.expandedRows = [];
            this.specialites.forEach(
                (s) => (this.expandedProgrammes[s.id] = [])
            );
        },
        setActiveTab(tab) {
            this.activeTab = tab;
        },
        clearSearch() {
            this.globalFilter = '';
            this.$forceUpdate();
        },
        openProgrammeForm(specialite) {
            this.selectedSpecialiteForProgramme = specialite;
            this.selectedProgramme = {};
            this.programmeFormVisible = true;
        },
        promptPassword(action, data) {
            const password = prompt(
                'Veuillez entrer le mot de passe pour continuer :'
            );
            if (password === 'ha') {
                if (action === 'editProgramme') {
                    this.editProgramme(data);
                } else if (action === 'deleteProgramme') {
                    this.confirmDeleteProgramme(data);
                } else if (action === 'editModule') {
                    this.editModule(data);
                } else if (action === 'deleteModule') {
                    this.confirmDeleteModule(data);
                }
            } else {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Mot de passe incorrect.',
                    life: 3000,
                });
            }
        },
        editProgramme(programme) {
            this.selectedProgramme = { ...programme };
            this.selectedSpecialiteForProgramme = this.specialites.find(
                (s) => s.id === programme.specialite_id
            );
            this.programmeFormVisible = true;
        },
        openModuleForm(programme) {
            this.selectedProgrammeForModule = programme;
            this.selectedModule = {};
            this.moduleFormVisible = true;
        },
        editModule(module) {
            this.selectedModule = { ...module };
            this.selectedProgrammeForModule = this.specialites
                .flatMap((s) => s.programmes)
                .find((p) => p.id === module.programme_id);
            this.moduleFormVisible = true;
        },
        confirmDeleteProgramme(programme) {
            this.confirm.require({
                group: 'dialog',
                message: 'Êtes-vous sûr de vouloir supprimer ce programme ?',
                header: 'Confirmation de Suppression',
                icon: 'pi pi-exclamation-triangle',
                acceptLabel: 'Oui',
                rejectLabel: 'Non',
                accept: async () => {
                    try {
                        const response = await axios.delete(
                            `/api/programmes/${programme.id}`
                        );
                        if (
                            response.status === 200 ||
                            response.status === 204
                        ) {
                            this.toast.add({
                                severity: 'success',
                                summary: 'Succès',
                                detail: 'Programme supprimé.',
                                life: 3000,
                            });
                            await this.fetchData();
                        }
                    } catch (error) {
                        this.toast.add({
                            severity: 'error',
                            summary: 'Erreur',
                            detail: 'Échec de la suppression du programme.',
                            life: 3000,
                        });
                        console.error(
                            'Delete programme error:',
                            error.response || error
                        );
                    }
                },
            });
        },
        confirmDeleteModule(module) {
            this.confirm.require({
                group: 'dialog',
                message: 'Êtes-vous sûr de vouloir supprimer ce module ?',
                header: 'Confirmation de Suppression',
                icon: 'pi pi-exclamation-triangle',
                acceptLabel: 'Oui',
                rejectLabel: 'Non',
                accept: async () => {
                    try {
                        const response = await axios.delete(
                            `/api/modules/${module.id}`
                        );
                        if (
                            response.status === 200 ||
                            response.status === 204
                        ) {
                            this.toast.add({
                                severity: 'success',
                                summary: 'Succès',
                                detail: 'ModuleGeneral supprimé.',
                                life: 3000,
                            });
                            await this.fetchData();
                        }
                    } catch (error) {
                        this.toast.add({
                            severity: 'error',
                            summary: 'Erreur',
                            detail: 'Échec de la suppression du module.',
                            life: 3000,
                        });
                        console.error(
                            'Delete module error:',
                            error.response || error
                        );
                    }
                },
            });
        },
        handleProgrammeSaved() {
            this.programmeFormVisible = false;
            this.fetchData();
        },
        handleProgrammeUpdated() {
            this.programmeFormVisible = false;
            this.fetchData();
        },
        handleModuleSaved() {
            this.moduleFormVisible = false;
            this.fetchData();
        },
        handleModuleUpdated() {
            this.moduleFormVisible = false;
            this.fetchData();
        },
        async importModules(programme) {
            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.accept = '.xls,.xlsx';
            fileInput.style.display = 'none';
            fileInput.onchange = async (event) => {
                const file = event.target.files[0];
                if (!file) return;
                const formData = new FormData();
                formData.append('file', file);
                formData.append('programme_id', programme.id);
                try {
                    const response = await axios.post(
                        '/api/modules/importxls',
                        formData,
                        { headers: { 'Content-Type': 'multipart/form-data' } }
                    );
                    this.importErrors = (response.data.error_lines || []).map(
                        (error) => ({
                            ...error,
                            programme_id: programme.id,
                        })
                    );
                    this.showImportErrors = this.importErrors.length > 0;
                    this.toast.add({
                        severity:
                            this.importErrors.length > 0 ? 'warn' : 'success',
                        summary: 'Import XLS',
                        detail:
                            this.importErrors.length > 0
                                ? `Import terminé avec ${this.importErrors.length} erreurs.`
                                : 'Importation réussie.',
                        life: 10000,
                    });
                    if (!this.importErrors.length) {
                        await this.fetchData();
                    }
                } catch (error) {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: "Erreur lors de l'import XLS.",
                        life: 3000,
                    });
                    console.error('Import modules error:', error);
                } finally {
                    fileInput.remove();
                }
            };
            document.body.appendChild(fileInput);
            fileInput.click();
        },
        handleLineImported(error) {
            this.importErrors = this.importErrors.filter(
                (e) => e.line !== error.line
            );
            if (this.importErrors.length === 0) {
                this.showImportErrors = false;
                this.fetchData();
            }
        },
        handleExportClick(specialiteId, programmeId) {
            if (!specialiteId || !programmeId)
                return this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Données manquantes.',
                    life: 3000,
                });
            this.fetchAndExportModules(specialiteId, programmeId);
        },
        async fetchAndExportModules(specialiteId, programmeId) {
            try {
                const response = await axios.get(
                    `/api/modules/export-modules/${specialiteId}/${programmeId}`
                );
                const programme = response.data.programme;
                if (!programme) {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'Programme non trouvé.',
                        life: 3000,
                    });
                    return;
                }
                const modules = programme.modules || [];
                if (!modules.length) {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'Aucun module trouvé pour ce programme.',
                        life: 3000,
                    });
                    return;
                }
                this.exportModules(programme, specialiteId);
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Erreur lors de l’exportation des modules.',
                    life: 3000,
                });
                console.error('Export modules error:', error.response || error);
            }
        },
        exportModules(programme, specialiteId) {
            const specialite = this.specialites.find(
                (s) => s.id === specialiteId
            );
            const specialiteName = specialite
                ? specialite.nom_fr ||
                  specialite.nom_ar ||
                  'Spécialité inconnue'
                : 'Spécialité inconnue';
            const today = new Date();
            const day = String(today.getDate()).padStart(2, '0');
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const year = today.getFullYear();
            const exportDate = `${day}/${month}/${year}`;
            const fileName = `Modules pour spécialité ${specialiteName} version programme ${programme.version} le ${exportDate}`;

            const workbook = new ExcelJS.Workbook();
            const worksheet = workbook.addWorksheet(
                `Modules_${programme.version}`
            );
            worksheet.columns = [
                { header: 'ID ModuleGeneral', key: 'id', width: 10 },
                { header: 'Code', key: 'code', width: 20 },
                { header: 'Titre', key: 'titre', width: 30 },
                { header: 'Type', key: 'type', width: 25 },
                {
                    header: 'Heures Théoriques',
                    key: 'heures_theoriques',
                    width: 20,
                },
                {
                    header: 'Heures Pratiques',
                    key: 'heures_pratiques',
                    width: 20,
                },
                {
                    header: 'Heures Évaluation',
                    key: 'heures_evaluation',
                    width: 20,
                },
                { header: 'Contenu PDF', key: 'contenu_pdf', width: 40 },
                { header: 'Observation', key: 'observation', width: 40 },
            ];
            programme.modules.forEach((m) =>
                worksheet.addRow({
                    id: m.id,
                    code: m.code || '',
                    titre: m.titre || '',
                    type: m.type || 'Enseignement spécifique',
                    heures_theoriques: m.heures_theoriques || 0,
                    heures_pratiques: m.heures_pratiques || 0,
                    heures_evaluation: m.heures_evaluation || 0,
                    contenu_pdf: m.contenu_pdf || '',
                    observation: m.observation || '',
                })
            );
            worksheet.getRow(1).eachCell((cell) => {
                cell.font = { bold: true };
                cell.alignment = { horizontal: 'center' };
            });
            workbook.xlsx.writeBuffer().then((buffer) => {
                saveAs(
                    new Blob([buffer], {
                        type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    }),
                    `${fileName}.xlsx`
                );
                this.toast.add({
                    severity: 'success',
                    summary: 'Export réussi',
                    detail: 'Modules exportés avec succès.',
                    life: 3000,
                });
            });
        },
        async downloadPdf(moduleId, fileName) {
            try {
                const response = await axios({
                    url: `/api/modules/${moduleId}/download`,
                    method: 'GET',
                    responseType: 'blob',
                });
                if (response.status === 200) {
                    const url = window.URL.createObjectURL(
                        new Blob([response.data])
                    );
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', fileName);
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    window.URL.revokeObjectURL(url);
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Fichier téléchargé.',
                        life: 3000,
                    });
                }
            } catch (error) {
                if (error.response && error.response.status === 404) {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'Fichier non trouvé.',
                        life: 3000,
                    });
                } else {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'Échec du téléchargement.',
                        life: 3000,
                    });
                }
                console.error('Download PDF error:', error);
            }
        },
        calculateTotalHours(module) {
            const theo = module.heures_theoriques || 0;
            const prac = module.heures_pratiques || 0;
            const evalHours = module.heures_evaluation || 0;
            return theo + prac + evalHours;
        },
        getModuleCounts(modules) {
            const specificModules = (modules || []).filter(
                (m) => m.type === 'Enseignement spécifique'
            );
            const generalModules = (modules || []).filter(
                (m) => m.type === 'Enseignement général'
            );
            return {
                specificCount: specificModules.length,
                specificHours: specificModules.reduce(
                    (sum, m) => sum + this.calculateTotalHours(m),
                    0
                ),
                generalCount: generalModules.length,
                generalHours: generalModules.reduce(
                    (sum, m) => sum + this.calculateTotalHours(m),
                    0
                ),
            };
        },
        onGlobalFilter() {
            this.$forceUpdate();
        },
        onProgrammeFilter() {
            this.$forceUpdate();
        },
        onModuleFilter() {
            this.$forceUpdate();
        },
        onRowExpand() {},
        onRowCollapse() {},
        onProgrammeExpand() {},
        onProgrammeCollapse() {},
    },
    mounted() {
        this.fetchData();
        this.fetchAnnees();
    },
};
</script>
