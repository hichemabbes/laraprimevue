<template>
    <DataTable
        v-model:expandedRows="expandedRows"
        v-model:filters="filters"
        @rowExpand="onRowExpand"
        @rowCollapse="onRowCollapse"
        stripedRows
        v-model:selection="selectedSpecialites"
        :frozenValue="FixLignes"
        size=""
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
                class: [{ 'font-bold': props.frozenRow }],
            }),
        }"
    >
        <!-- Header -->
        <template #header>
            <div class="flex flex-wrap justify-content-between gap-2">
                <div class="flex mb-3">
                    <Button
                        label="مقيس"
                        :severity="activeTab === 'مقيس' ? 'success' : 'success'"
                        class="mr-2 arabic-normal"
                        @click="setActiveTab('مقيس')"
                    />
                    <Button
                        label="غير مقيس"
                        :severity="
                            activeTab === 'غير مقيس' ? 'danger' : 'danger'
                        "
                        class="mr-2 arabic-normal"
                        @click="setActiveTab('غير مقيس')"
                    />
                    <Button
                        label="جديد"
                        :severity="activeTab === 'جديد' ? 'warning' : 'warning'"
                        class="mr-2 arabic-normal"
                        @click="setActiveTab('جديد')"
                    />
                    <Dropdown
                        v-model="selectedAnnee"
                        :options="annees"
                        optionLabel="intitule"
                        optionValue="intitule"
                        placeholder="Sélectionner une année"
                        class="mr-2"
                        @change="filterByAnnee"
                    />
                </div>
                <div class="flex mb-3">
                    <Button
                        text
                        icon="pi pi-plus"
                        label="Expand All"
                        @click="expandAll"
                    />
                    <Button
                        text
                        icon="pi pi-minus"
                        label="Collapse All"
                        @click="collapseAll"
                    />
                </div>
            </div>
        </template>

        <!-- Colonne d'expansion -->
        <Column expander style="width: 5rem" />

        <!-- Colonnes principales -->
        <Column field="id" header="ID" style="min-width: 8rem">
            <template #body="{ data }">
                {{ data.id }}
            </template>
        </Column>
        <Column field="code" header="Code"></Column>
        <Column field="nom_fr" header="Nom (Fr)"></Column>
        <!-- Colonne Nom (AR) -->
        <Column
            field="nom_ar"
            header="Nom (AR)"
            sortable
            style="min-width: 14rem"
        >
            <template #body="{ data }">
                <div class="arabic-normal">
                    {{ data.nom_ar }}
                </div>
            </template>
        </Column>

        <Column
            header="Diplôme"
            field="diplome"
            sortable
            :filterMenuStyle="{ width: '14rem' }"
            style="min-width: 12rem"
        >
            <template #body="{ data }">
                <div class="centered-content arabic-normal">
                    <Tag
                        :value="data.diplome"
                        :severity="getSeverity(data.diplome)"
                        class="arabic-normal"
                    />
                </div>
            </template>
            <template #filter="{ filterModel, filterCallback }">
                <Dropdown
                    v-model="filterModel.value"
                    :options="diplomes"
                    placeholder="Sélectionner un niveau"
                    class="p-column-filter arabic-normal"
                    @change="filterCallback"
                >
                    <template #option="{ option }">
                        <div class="centered-content arabic-normal">
                            <Tag
                                :value="option"
                                :severity="getSeverity(option)"
                                class="arabic-normal"
                            />
                        </div>
                    </template>
                </Dropdown>
            </template>
        </Column>
        <!-- Colonne Import/Export -->
        <Column header="Import/Export">
            <template #body="slotProps">
                <Button
                    icon="pi pi-upload"
                    class="p-button-rounded p-button-text p-button-info mr-2"
                    @click="importModules(slotProps.data)"
                />
                <Button
                    icon="pi pi-download"
                    class="p-button-rounded p-button-text p-button-success"
                    @click="exportModules(slotProps.data)"
                />
            </template>
        </Column>

        <!-- Sous-table pour les modules -->
        <template #expansion="slotProps">
            <div class="p-3">
                <h5 class="module-title">
                    Modules pour {{ slotProps.data.nom_fr }}
                </h5>
                <DataTable
                    :value="slotProps.data.modulesVisible"
                    class="p-datatable-sm p-datatable-striped"
                >
                    <!-- Colonnes pour les modules -->
                    <Column field="id" header="Id" sortable></Column>
                    <Column field="code" header="Code" sortable>
                        <template #body="slotProps">
                            <span
                                @dblclick="editModuleField(slotProps, 'code')"
                            >
                                {{ slotProps.data.code }}
                            </span>
                            <InputText
                                v-if="
                                    slotProps.data.editing &&
                                    slotProps.data.editingField === 'code'
                                "
                                v-model="slotProps.data.code"
                                @blur="saveModuleEdit(slotProps.data)"
                            />
                        </template>
                    </Column>
                    <Column field="titre" header="Titre" sortable>
                        <template #body="slotProps">
                            <span
                                @dblclick="editModuleField(slotProps, 'titre')"
                            >
                                {{ slotProps.data.titre }}
                            </span>
                            <InputText
                                v-if="
                                    slotProps.data.editing &&
                                    slotProps.data.editingField === 'titre'
                                "
                                v-model="slotProps.data.titre"
                                @blur="saveModuleEdit(slotProps.data)"
                            />
                        </template>
                    </Column>
                    <Column field="type" header="Type" sortable>
                        <template #body="slotProps">
                            <span
                                @dblclick="editModuleField(slotProps, 'type')"
                            >
                                {{ slotProps.data.type }}
                            </span>
                            <Dropdown
                                v-if="
                                    slotProps.data.editing &&
                                    slotProps.data.editingField === 'type'
                                "
                                v-model="slotProps.data.type"
                                :options="[
                                    'Enseignement spécifique',
                                    'Enseignement général',
                                ]"
                                @change="saveModuleEdit(slotProps.data)"
                            />
                        </template>
                    </Column>
                    <Column
                        field="heures_theorique"
                        header="Heures Théoriques"
                        sortable
                    >
                        <template #body="slotProps">
                            <span
                                @dblclick="
                                    editModuleField(
                                        slotProps,
                                        'heures_theorique'
                                    )
                                "
                            >
                                {{ slotProps.data.heures_theorique }}
                            </span>
                            <InputText
                                v-if="
                                    slotProps.data.editing &&
                                    slotProps.data.editingField ===
                                        'heures_theorique'
                                "
                                v-model="slotProps.data.heures_theorique"
                                @blur="saveModuleEdit(slotProps.data)"
                            />
                        </template>
                    </Column>
                    <Column
                        field="heures_pratiques"
                        header="Heures Pratiques"
                        sortable
                    >
                        <template #body="slotProps">
                            <span
                                @dblclick="
                                    editModuleField(
                                        slotProps,
                                        'heures_pratiques'
                                    )
                                "
                            >
                                {{ slotProps.data.heures_pratiques }}
                            </span>
                            <InputText
                                v-if="
                                    slotProps.data.editing &&
                                    slotProps.data.editingField ===
                                        'heures_pratiques'
                                "
                                v-model="slotProps.data.heures_pratiques"
                                @blur="saveModuleEdit(slotProps.data)"
                            />
                        </template>
                    </Column>
                    <Column
                        field="heures_evaluation"
                        header="Heures Evaluation"
                        sortable
                    >
                        <template #body="slotProps">
                            <span
                                @dblclick="
                                    editModuleField(
                                        slotProps,
                                        'heures_evaluation'
                                    )
                                "
                            >
                                {{ slotProps.data.heures_evaluation }}
                            </span>
                            <InputText
                                v-if="
                                    slotProps.data.editing &&
                                    slotProps.data.editingField ===
                                        'heures_evaluation'
                                "
                                v-model="slotProps.data.heures_evaluation"
                                @blur="saveModuleEdit(slotProps.data)"
                            />
                        </template>
                    </Column>
                    <Column
                        field="heures_totales"
                        header="Heures Totales"
                        sortable
                    >
                        <template #body="slotProps">
                            <span
                                @dblclick="
                                    editModuleField(slotProps, 'heures_totales')
                                "
                            >
                                {{ slotProps.data.heures_totales }}
                            </span>
                            <InputText
                                v-if="
                                    slotProps.data.editing &&
                                    slotProps.data.editingField ===
                                        'heures_totales'
                                "
                                v-model="slotProps.data.heures_totales"
                                @blur="saveModuleEdit(slotProps.data)"
                            />
                        </template>
                    </Column>
                    <Column field="contenu" header="Contenu" sortable>
                        <template #body="slotProps">
                            <span
                                @dblclick="
                                    editModuleField(slotProps, 'contenu')
                                "
                            >
                                {{ slotProps.data.contenu }}
                            </span>
                            <InputText
                                v-if="
                                    slotProps.data.editing &&
                                    slotProps.data.editingField === 'contenu'
                                "
                                v-model="slotProps.data.contenu"
                                @blur="saveModuleEdit(slotProps.data)"
                            />
                        </template>
                    </Column>
                    <Column field="annees" header="Années" sortable>
                        <template #body="slotProps">
                            <span
                                @dblclick="editModuleField(slotProps, 'annees')"
                            >
                                {{ formatAnnees(slotProps.data.annees) }}
                            </span>
                            <InputText
                                v-if="
                                    slotProps.data.editing &&
                                    slotProps.data.editingField === 'annees'
                                "
                                v-model="slotProps.data.annees"
                                @blur="saveModuleEdit(slotProps.data)"
                            />
                        </template>
                    </Column>
                    <Column field="observation" header="Observation" sortable>
                        <template #body="slotProps">
                            <span
                                @dblclick="
                                    editModuleField(slotProps, 'observation')
                                "
                            >
                                {{ slotProps.data.observation }}
                            </span>
                            <InputText
                                v-if="
                                    slotProps.data.editing &&
                                    slotProps.data.editingField ===
                                        'observation'
                                "
                                v-model="slotProps.data.observation"
                                @blur="saveModuleEdit(slotProps.data)"
                            />
                        </template>
                    </Column>
                    <Column header="Actions">
                        <template #body="slotProps">
                            <Button
                                icon="pi pi-trash"
                                class="p-button-rounded p-button-text p-button-danger"
                                @click="confirmDeleteModule(slotProps.data)"
                            />
                        </template>
                    </Column>
                </DataTable>
            </div>
        </template>
    </DataTable>

    <!-- Popup pour les erreurs d'importation -->
    <ImportError
        :errors="errors"
        :visible="showImportError"
        @update:visible="showImportError = $event"
        @line-imported="handleLineImported"
        @close="refreshTable"
    />

    <Toast />
    <ConfirmDialog />
</template>

<script>
import Tag from 'primevue/tag';
import SplitButton from 'primevue/splitbutton';
import Dialog from 'primevue/dialog';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Dropdown from 'primevue/dropdown';
import Toast from 'primevue/toast';
import InputText from 'primevue/inputtext';
import ConfirmDialog from 'primevue/confirmdialog';
import { FilterMatchMode, FilterOperator } from 'primevue/api';
import ImportError from '@/corbeil/ProgramesSpecialites/ModulesImportError.vue';

import ExcelJS from 'exceljs';
import Calendar from 'primevue/calendar';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';

export default {
    components: {
        Button,
        DataTable,
        Column,
        Tag,
        Dropdown,
        Calendar,
        SplitButton,
        Dialog,
        Toast,
        InputText,
        ConfirmDialog,
        ImportError,
    },
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            specialites: [],
            types: ['جديد', 'مقيس', 'غير مقيس'],
            FixLignes: [],
            filters: null,
            showFreezeDropdown: false,
            selectedSpecialites: [],
            frozenColumns: [],
            expandedRows: [],
            loading: true,
            activeTab: 'مقيس',
            selectedAnnee: null,
            annees: [], // Liste des années
            diplomes: [
                'شهادة مؤهل تقني سامي',
                'شهادة مؤهل تقني مهني',
                'شهادة الكفاءة المهنية',
                'شهادة مهارة',
                'شهادة إنهاء التدريب',
            ],
            showImportError: false,
            errors: [],
        };
    },
    computed: {
        filteredSpecialites() {
            if (this.activeTab === 'مقيس') {
                return this.specialites.filter((s) => s.type === 'مقيس');
            } else if (this.activeTab === 'غير مقيس') {
                return this.specialites.filter((s) => s.type === 'غير مقيس');
            } else if (this.activeTab === 'جديد') {
                return this.specialites.filter((s) => s.type === 'جديد');
            }
            return this.specialites;
        },
    },
    methods: {
        async fetchData() {
            try {
                const response = await fetch('/api/modules');
                const data = await response.json();
                console.log('Données reçues:', data);

                this.specialites = data.map((specialite) => {
                    specialite.modules = specialite.modules.map((module) => {
                        if (typeof module.annees === 'string') {
                            try {
                                module.annees = JSON.parse(module.annees); // Convertir en tableau JSON
                            } catch (error) {
                                console.error(
                                    'Erreur lors du parsing des années:',
                                    error
                                );
                                module.annees = [];
                            }
                        } else if (!Array.isArray(module.annees)) {
                            module.annees = [];
                        }
                        module.editing = false;
                        module.editingField = null;
                        return module;
                    });

                    // Ajouter une propriété modulesVisible pour le filtrage
                    specialite.modulesVisible = specialite.modules; // Initialiser modulesVisible
                    return specialite;
                });
            } catch (error) {
                console.error(
                    'Erreur lors de la récupération des données:',
                    error
                );
            } finally {
                this.loading = false;
            }
        },
        async fetchAnnees() {
            try {
                const response = await axios.get('/api/annees-formation');
                this.annees = response.data;
                console.log('Années récupérées:', this.annees); // Ajoutez un log pour vérifier
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Impossible de charger les années.',
                    life: 3000,
                });
            }
        },
        formatAnnees(annees) {
            return Array.isArray(annees) ? annees.join(', ') : annees || '';
        },
        filterByAnnee() {
            if (!this.selectedAnnee) {
                // Si aucune année n'est sélectionnée, afficher tous les modules
                this.specialites.forEach((specialite) => {
                    specialite.modulesVisible = specialite.modules;
                });
                return;
            }

            // Filtrer les modules pour chaque spécialité
            this.specialites.forEach((specialite) => {
                specialite.modulesVisible = specialite.modules.filter(
                    (module) => {
                        // Vérifier si l'année sélectionnée existe dans le tableau des années
                        return (
                            module.annees &&
                            module.annees.includes(
                                this.selectedAnnee.toString()
                            )
                        );
                    }
                );
            });

            console.log('ProgramesSpecialites filtrés:', this.specialites); // Ajoutez un log pour vérifier
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
                nom_ar: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.CONTAINS },
                    ],
                },
                nom_fr: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.CONTAINS },
                    ],
                },
                type: {
                    operator: FilterOperator.OR,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.EQUALS },
                    ],
                },
                diplome: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.CONTAINS },
                    ],
                },
            };
        },
        onRowExpand(event) {
            console.log('Ligne étendue:', event.data);
        },
        onRowCollapse(event) {
            console.log('Ligne réduite:', event.data);
        },
        expandAll() {
            this.expandedRows = this.specialites.map((s) => s.specialite_id);
        },
        collapseAll() {
            this.expandedRows = [];
        },
        setActiveTab(tab) {
            this.activeTab = tab;
        },
        getSeverity(type) {
            switch (type) {
                case 'شهادة مؤهل تقني سامي':
                    return 'info';
                case 'شهادة مؤهل تقني مهني':
                    return 'secondary';
                case 'شهادة الكفاءة المهنية':
                    return 'warning';
                case 'شهادة مهارة':
                    return 'success';
                case 'شهادة إنهاء التدريب':
                    return 'danger';
                default:
                    return null;
            }
        },
        async importModules(specialite) {
            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.accept = '.xls,.xlsx';
            fileInput.style.display = 'none';

            fileInput.onchange = async (event) => {
                const file = event.target.files[0];
                if (!file) return;

                const formData = new FormData();
                formData.append('file', file);
                formData.append('specialite_id', specialite.id);

                try {
                    const response = await axios.post(
                        '/api/modules/importxls',
                        formData,
                        {
                            headers: {
                                'Content-Type': 'multipart/form-data',
                            },
                        }
                    );

                    this.errors = response.data.error_lines || [];
                    let message = 'Importation réussie.';
                    let severity = 'success';

                    if (this.errors.length > 0) {
                        message = `Importation terminée avec ${this.errors.length} erreurs. Veuillez vérifier les lignes concernées.`;
                        severity = 'warn';
                        this.showImportError = true;
                    }

                    // Rafraîchir la table après une importation réussie
                    await this.refreshTable();

                    this.toast.add({
                        severity: severity,
                        summary: 'Import XLS',
                        detail: message,
                        life: 10000,
                    });
                } catch (error) {
                    console.error("Erreur lors de l'import XLS :", error);
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: "Une erreur s'est produite lors de l'import XLS.",
                        life: 3000,
                    });
                } finally {
                    fileInput.remove();
                }
            };

            document.body.appendChild(fileInput);
            fileInput.click();
        },
        handleLineImported(importedLine) {
            this.errors = this.errors.filter(
                (err) => err.line !== importedLine.line
            );
        },
        async refreshTable() {
            this.loading = true;
            try {
                const response = await axios.get('/api/modules');
                this.table = response.data;
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Échec du chargement des modules.',
                    life: 3000,
                });
            } finally {
                this.loading = false;
            }
        },
        async exportModules(specialite) {
            if (!specialite.modules || specialite.modules.length === 0) {
                this.$toast.add({
                    severity: 'warn',
                    summary: 'Aucun module à exporter',
                    detail: `La spécialité ${specialite.nom_ar} ne contient aucun module.`,
                    life: 3000,
                });
                return;
            }

            const workbook = new ExcelJS.Workbook();
            const worksheet = workbook.addWorksheet(
                `Modules_${specialite.nom_ar}`
            );

            worksheet.columns = [
                { header: 'ID', key: 'id', width: 10 },
                { header: 'Code', key: 'code', width: 20 },
                { header: 'Titre', key: 'titre', width: 30 },
                { header: 'Type', key: 'type', width: 25 },
                {
                    header: 'Heures Théoriques',
                    key: 'heures_theorique',
                    width: 20,
                },
                {
                    header: 'Heures Pratiques',
                    key: 'heures_pratiques',
                    width: 20,
                },
                {
                    header: 'Heures Evaluation',
                    key: 'heures_evaluation',
                    width: 20,
                },
                { header: 'Heures Totales', key: 'heures_totales', width: 20 },
                { header: 'Contenu', key: 'contenu', width: 40 },
                { header: 'Années', key: 'annees', width: 40 },
                { header: 'Observation', key: 'observation', width: 40 },
            ];

            specialite.modules.forEach((module) => {
                worksheet.addRow({
                    id: module.id,
                    code: module.code,
                    titre: module.titre,
                    type: module.type,
                    heures_theorique: module.heures_theorique,
                    heures_pratiques: module.heures_pratiques,
                    heures_evaluation: module.heures_evaluation,
                    heures_totales: module.heures_totales,
                    contenu: module.contenu,
                    annees: Array.isArray(module.annees)
                        ? module.annees.join(', ')
                        : module.annees || '',
                    observation: module.observation,
                });
            });

            worksheet.getRow(1).eachCell((cell) => {
                cell.font = { bold: true };
                cell.alignment = { horizontal: 'center' };
            });

            const buffer = await workbook.xlsx.writeBuffer();
            const blob = new Blob([buffer], {
                type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            });
            const url = window.URL.createObjectURL(blob);

            const a = document.createElement('a');
            a.href = url;
            a.download = `Modules_${specialite.nom_ar}_${new Date().toISOString().slice(0, 10)}.xlsx`;
            a.click();

            window.URL.revokeObjectURL(url);

            this.$toast.add({
                severity: 'success',
                summary: 'Export réussi',
                detail: `Les modules de la spécialité ${specialite.nom_ar} ont été exportés avec succès.`,
                life: 3000,
            });
        },
        editModuleField(slotProps, field) {
            slotProps.data.editing = true;
            slotProps.data.editingField = field;
        },
        async saveModuleEdit(module) {
            this.$confirm.require({
                message:
                    'Êtes-vous sûr de vouloir enregistrer les modifications ?',
                header: 'Confirmation',
                icon: 'pi pi-exclamation-triangle',
                autofocus: false,
                accept: async () => {
                    try {
                        const response = await fetch(
                            `/api/modules/${module.id}`,
                            {
                                method: 'PUT',
                                headers: {
                                    'Content-Type': 'application/json',
                                    Accept: 'application/json',
                                    'X-CSRF-TOKEN': document
                                        .querySelector(
                                            'meta[name="csrf-token"]'
                                        )
                                        .getAttribute('content'),
                                },
                                body: JSON.stringify(module),
                            }
                        );

                        if (!response.ok) {
                            const errorData = await response.json();
                            throw new Error(
                                errorData.message ||
                                    'Erreur lors de la mise à jour du module'
                            );
                        }

                        const data = await response.json();
                        this.$toast.add({
                            severity: 'success',
                            summary: 'Succès',
                            detail: 'ModuleGeneral mis à jour avec succès',
                            life: 3000,
                        });
                    } catch (error) {
                        console.error('Erreur:', error);
                        this.$toast.add({
                            severity: 'error',
                            summary: 'Erreur',
                            detail:
                                error.message ||
                                'Échec de la mise à jour du module',
                            life: 3000,
                        });
                    } finally {
                        module.editing = false;
                        module.editingField = null;
                    }
                },
                reject: () => {
                    module.editing = false;
                    module.editingField = null;
                },
            });
        },
        confirmDeleteModule(module) {
            this.$confirm.require({
                message: 'Êtes-vous sûr de vouloir supprimer ce module ?',
                header: 'Confirmation',
                icon: 'pi pi-exclamation-triangle',
                accept: async () => {
                    try {
                        const response = await fetch(
                            `/api/modules/${module.id}`,
                            {
                                method: 'DELETE',
                            }
                        );
                        if (!response.ok) {
                            throw new Error(
                                'Erreur lors de la suppression du module'
                            );
                        }
                        this.$toast.add({
                            severity: 'success',
                            summary: 'Succès',
                            detail: 'ModuleGeneral supprimé avec succès',
                            life: 3000,
                        });
                        this.fetchData();
                    } catch (error) {
                        console.error('Erreur:', error);
                        this.$toast.add({
                            severity: 'error',
                            summary: 'Erreur',
                            detail: 'Échec de la suppression du module',
                            life: 3000,
                        });
                    }
                },
            });
        },
    },
    mounted() {
        this.fetchData();
        this.fetchAnnees(); // Appeler la méthode pour récupérer les années
    },
};
</script>

<style scoped>
.p-datatable-sm {
    font-size: 0.875rem;
}
.p-datatable-striped .p-datatable-tbody > tr:nth-child(odd) {
    background-color: #f9f9f9;
}
.arabic-normal {
    font-family: 'Noto Naskh Arabic', sans-serif;
    font-size: 1em !important;
    font-weight: normal !important;
    direction: rtl;
}
.module-title {
    color: #fca5a5; /* Couleur du texte */
    font-weight: bold; /* Optionnel : rendre le texte plus gras */
}
</style>
