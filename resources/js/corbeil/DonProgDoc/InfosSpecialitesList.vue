<template>
    <DataTable
        v-model:filters="filters"
        stripedRows
        v-model:selection="selectedSpecialites"
        :frozenValue="FixLignes"
        size="small"
        :value="filteredSpecialites"
        paginator
        :rows="20"
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
            'homologation',
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
    >
        <template #header>
            <div class="flex flex-column gap-2">
                <!-- Première ligne : Titre, filtre année et boutons -->
                <div
                    class="flex justify-content-between align-items-center mb-2"
                >
                    <div class="text-2xl font-bold text-primary">
                        Informations sur les Spécialités
                        <span :style="{ color: '#EF4444' }">{{
                            dynamicSubtitle
                        }}</span>
                        <span class="font-bold text-primary">
                            pour l'année de formation
                        </span>
                        <span :style="{ color: '#EF4444' }">{{
                            anneeText
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
                                v-model="filters['global'].value"
                                size="small"
                                placeholder="Recherche..."
                            />
                            <i class="pi pi-search" />
                        </span>
                        <Button
                            icon="pi pi-times"
                            size="small"
                            severity="danger"
                            outlined
                            @click="clearFilter"
                        />
                        <Button
                            icon="pi pi-lock"
                            size="small"
                            label="Figer"
                            @click="toggleFreezeDropdown"
                        />
                        <Dropdown
                            v-model="selectedColumnToFreeze"
                            :options="columnOptions"
                            optionLabel="header"
                            placeholder="Sélectionner une colonne"
                            v-if="showFreezeDropdown"
                            @change="freezeColumn"
                        >
                            <template #option="slotProps">
                                <div>
                                    <span
                                        v-if="slotProps.option.isFrozen"
                                        :style="{
                                            color: 'green',
                                            marginRight: '5px',
                                        }"
                                        >✔️</span
                                    >
                                    {{ slotProps.option.header }}
                                </div>
                            </template>
                        </Dropdown>
                    </div>
                    <div class="flex align-items-center gap-2">
                        <Button
                            icon="pi pi-file-import"
                            severity="success"
                            size="small"
                            label="Import XLS"
                            @click="Importxls"
                        />
                        <Button
                            v-if="errors.length > 0"
                            icon="pi pi-exclamation-triangle"
                            severity="warning"
                            size="small"
                            label="Erreur Import"
                            @click="showImportError = true"
                        />
                        <Button
                            icon="pi pi-file-export"
                            severity="info"
                            size="small"
                            label="Export XLS"
                            @click="exportXLS"
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

        <Column
            field="code"
            header="Code"
            sortable
            style="min-width: 10rem"
            :frozen="frozenColumns.includes('code')"
        >
            <template #filter="{ filterModel, filterCallback }">
                <InputText
                    v-model="filterModel.value"
                    type="text"
                    placeholder="Rechercher par code"
                    @input="filterCallback"
                />
            </template>
        </Column>
        <Column
            field="nom_fr"
            header="Nom (Fr)"
            style="min-width: 14rem"
            :showFilterMenu="false"
        >
            <template #filter="{ filterModel, filterCallback }">
                <InputText
                    v-model="filterModel.value"
                    @input="filterCallback()"
                    placeholder="Rechercher par Nom (Fr)"
                />
            </template>
        </Column>
        <Column
            field="nom_ar"
            header="Nom (AR)"
            style="min-width: 14rem"
            :showFilterMenu="false"
            :frozen="frozenColumns.includes('nom_ar')"
        >
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
        <Column field="diplome" header="Diplôme" style="min-width: 10rem">
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
        <Column
            field="homologation"
            header="Homologation"
            style="min-width: 12rem"
        >
            <template #body="{ data }">
                <span v-if="!data.editing">
                    <Tag
                        :value="getDynamicField(data, 'homologation')"
                        :severity="
                            getSeverity(getDynamicField(data, 'homologation'))
                        "
                    />
                </span>
                <Dropdown
                    v-else
                    v-model="data.editedFields.homologation"
                    :options="['Homologuée', 'Non Homologuée']"
                    placeholder="Sélectionner un type"
                />
            </template>
            <template #filter="{ filterModel, filterCallback }">
                <Dropdown
                    v-model="filterModel.value"
                    :options="['Homologuée', 'Non Homologuée']"
                    placeholder="Sélectionner un type"
                    @change="filterCallback()"
                />
            </template>
        </Column>
        <Column
            field="heures_et"
            header="Heures Techniques"
            sortable
            style="min-width: 12rem"
        >
            <template #body="{ data }">
                <span v-if="!data.editing">{{
                    getDynamicField(data, 'heures_et')
                }}</span>
                <InputText v-else v-model="data.editedFields.heures_et" />
            </template>
        </Column>
        <Column
            field="heures_eg"
            header="Heures Généraux"
            sortable
            style="min-width: 12rem"
        >
            <template #body="{ data }">
                <span v-if="!data.editing">{{
                    getDynamicField(data, 'heures_eg')
                }}</span>
                <InputText v-else v-model="data.editedFields.heures_eg" />
            </template>
        </Column>
        <Column
            field="heures_totales"
            header="Heures Totales"
            sortable
            style="min-width: 12rem"
        >
            <template #body="{ data }">
                {{ getDynamicField(data, 'heures_totales') }}
            </template>
        </Column>
        <Column
            field="duree_formation"
            header="Durée de Formation"
            sortable
            style="min-width: 12rem"
        >
            <template #body="{ data }">
                <span v-if="!data.editing">{{
                    getDynamicField(data, 'duree_formation')
                }}</span>
                <InputText v-else v-model="data.editedFields.duree_formation" />
            </template>
        </Column>
        <Column
            field="observation"
            header="Observation"
            sortable
            style="min-width: 14rem"
        >
            <template #body="{ data }">
                <span v-if="!data.editing">{{
                    getDynamicField(data, 'observation')
                }}</span>
                <InputText v-else v-model="data.editedFields.observation" />
            </template>
        </Column>
        <Column header="Actions" style="min-width: 14rem" frozen>
            <template #body="{ data }">
                <div class="flex gap-2">
                    <Button
                        :icon="isFixed(data) ? 'pi pi-lock-open' : 'pi pi-lock'"
                        severity="secondary"
                        text
                        @click="toggleFixLine(data)"
                        v-tooltip="'Fixer/Défixer la ligne'"
                    />
                    <Button
                        v-if="!data.editing"
                        icon="pi pi-pencil"
                        severity="info"
                        text
                        @click="startEditing(data)"
                    />
                    <Button
                        v-if="!data.editing"
                        icon="pi pi-trash"
                        severity="danger"
                        text
                        @click="confirmDeleteInfoSpecialite(data)"
                    />
                    <Button
                        v-if="data.editing"
                        icon="pi pi-check"
                        severity="success"
                        text
                        @click="saveEditing(data)"
                    />
                    <Button
                        v-if="data.editing"
                        icon="pi pi-times"
                        severity="danger"
                        text
                        @click="cancelEditing(data)"
                    />
                </div>
            </template>
        </Column>
    </DataTable>

    <Dialog
        v-model:visible="deleteDialogVisible"
        header="Confirmation de suppression"
        :modal="true"
        :style="{ width: '450px' }"
    >
        <div class="p-fluid">
            <p>Êtes-vous sûr de vouloir supprimer cette information ?</p>
        </div>
        <template #footer>
            <Button
                label="Non"
                icon="pi pi-times"
                text
                @click="deleteDialogVisible = false"
            />
            <Button
                label="Oui"
                icon="pi pi-check"
                text
                @click="confirmDelete"
            />
        </template>
    </Dialog>
    <Dialog
        v-model:visible="editDialogVisible"
        header="Confirmation de modification"
        :modal="true"
        :style="{ width: '450px' }"
    >
        <div class="p-fluid">
            <p>Êtes-vous sûr de vouloir modifier cette information ?</p>
        </div>
        <template #footer>
            <Button
                label="Non"
                icon="pi pi-times"
                text
                @click="editDialogVisible = false"
            />
            <Button label="Oui" icon="pi pi-check" text @click="confirmEdit" />
        </template>
    </Dialog>
    <ImportError
        :errors="errors"
        :visible="showImportError"
        @update:visible="showImportError = $event"
        @line-imported="handleLineImported"
        @close="refreshTable"
    />
    <Toast />
</template>

<script>
import { FilterMatchMode, FilterOperator } from 'primevue/api';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dropdown from 'primevue/dropdown';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import Tag from 'primevue/tag';
import ImportError from '@/corbeil/DonProgDoc/InfosSpecialitesImportError.vue';
import ExcelJS from 'exceljs';

export default {
    components: {
        Button,
        InputText,
        DataTable,
        Column,
        Dropdown,
        Dialog,
        Toast,
        Tag,
        ImportError,
    },
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            activeTab: 'مقيس',
            FixLignes: [],
            formVisible: false,
            deleteDialogVisible: false,
            editDialogVisible: false,
            itemToDelete: null,
            itemToEdit: null,
            ToEdit: null,
            specialites: [],
            selectedSpecialites: [],
            filters: null,
            showFreezeDropdown: false,
            selectedColumnToFreeze: null,
            frozenColumns: ['code'],
            selectedAnnee: null,
            loading: true,
            errors: [],
            showImportError: false,
            annees: [],
        };
    },
    computed: {
        dynamicSubtitle() {
            return this.activeTab === 'جديد'
                ? 'Nouvelles'
                : this.activeTab === 'غير مقيس'
                  ? 'Non Normalisée'
                  : 'Normalisée';
        },
        anneeText() {
            const selected = this.annees.find(
                (annee) => annee.id === this.selectedAnnee
            );
            return selected
                ? selected.intitule
                : this.annees.find((annee) => annee.statut === 'En cours')
                      ?.intitule || 'en cours';
        },
        columnOptions() {
            return [
                {
                    field: 'code',
                    header: 'Code',
                    isFrozen: this.frozenColumns.includes('code'),
                },
                {
                    field: 'nom_ar',
                    header: 'Nom (AR)',
                    isFrozen: this.frozenColumns.includes('nom_ar'),
                },
            ];
        },
        filteredSpecialites() {
            return this.specialites.filter((s) => s.type === this.activeTab);
        },
    },
    created() {
        this.initFilters();
        this.fetchSpecialites();
        this.fetchAnnees();
    },
    methods: {
        openForm() {
            this.ToEdit = null;
            this.formVisible = true;
        },
        handleSaved() {
            this.fetchSpecialites();
        },
        setActiveTab(tab) {
            this.activeTab = tab;
        },
        clearFilter() {
            this.initFilters();
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
                        { value: null, matchMode: FilterMatchMode.CONTAINS },
                    ],
                },
                nom_ar: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.CONTAINS },
                    ],
                },
                diplome: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.EQUALS },
                    ],
                },
                homologation: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.EQUALS },
                    ],
                },
            };
        },
        async fetchSpecialites() {
            this.loading = true;
            try {
                const response = await axios.get('/api/specialites', {
                    params: { include_infos: true },
                });
                this.specialites = response.data;
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Impossible de charger les spécialités.',
                    life: 3000,
                });
            } finally {
                this.loading = false;
            }
        },
        async fetchAnnees() {
            try {
                const response = await axios.get('/api/annees-formation');
                this.annees = response.data;
                const activeAnnee = this.annees.find(
                    (annee) => annee.statut === 'En cours'
                );
                if (activeAnnee) {
                    this.selectedAnnee = activeAnnee.id;
                }
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Impossible de charger les années.',
                    life: 3000,
                });
            }
        },
        getDynamicField(data, field) {
            return (
                (this.selectedAnnee &&
                    data.informations_specialites?.find(
                        (info) => info.annee_id === this.selectedAnnee
                    )?.[field]) ||
                ''
            );
        },
        filterByAnnee() {
            this.$forceUpdate();
        },
        isFixed(data) {
            return this.FixLignes.some((item) => item.id === data.id);
        },
        toggleFixLine(data) {
            const index = this.FixLignes.findIndex(
                (item) => item.id === data.id
            );
            if (index === -1) {
                this.FixLignes.push({ ...data });
                this.toast.add({
                    severity: 'success',
                    summary: 'Ligne fixée',
                    detail: 'La ligne a été fixée en haut du tableau.',
                    life: 3000,
                });
            } else {
                this.FixLignes.splice(index, 1);
                this.toast.add({
                    severity: 'info',
                    summary: 'Ligne libérée',
                    detail: "La ligne n'est plus fixée.",
                    life: 3000,
                });
            }
        },
        startEditing(data) {
            const originalData =
                this.specialites.find((s) => s.id === data.id) || data;
            originalData.editing = true;
            originalData.editedFields = {
                homologation: this.getDynamicField(
                    originalData,
                    'homologation'
                ),
                heures_et: this.getDynamicField(originalData, 'heures_et'),
                heures_eg: this.getDynamicField(originalData, 'heures_eg'),
                heures_totales: this.getDynamicField(
                    originalData,
                    'heures_totales'
                ),
                duree_formation: this.getDynamicField(
                    originalData,
                    'duree_formation'
                ),
                observation: this.getDynamicField(originalData, 'observation'),
                specialite_id: originalData.id,
                annee_id: this.selectedAnnee,
            };
            this.itemToEdit = originalData;
            const fixIndex = this.FixLignes.findIndex((f) => f.id === data.id);
            if (fixIndex !== -1) {
                this.FixLignes[fixIndex] = { ...originalData };
            }
        },
        async saveEditing(data) {
            this.editDialogVisible = true;
            this.itemToEdit = data;
        },
        async confirmEdit() {
            if (!this.itemToEdit) return;
            try {
                const info = this.itemToEdit.informations_specialites?.find(
                    (info) => info.annee_id === this.selectedAnnee
                );
                const payload = {
                    ...this.itemToEdit.editedFields,
                    heures_et:
                        parseInt(this.itemToEdit.editedFields.heures_et) ||
                        null,
                    heures_eg:
                        parseInt(this.itemToEdit.editedFields.heures_eg) ||
                        null,
                    specialite_id: this.itemToEdit.id,
                    annee_id: this.selectedAnnee,
                };
                if (info) {
                    await axios.put(
                        `/api/informations-specialites/${info.id}`,
                        payload
                    );
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Informations mises à jour.',
                        life: 3000,
                    });
                } else {
                    await axios.post('/api/informations-specialites', payload);
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Informations ajoutées.',
                        life: 3000,
                    });
                }
                this.itemToEdit.editing = false;
                delete this.itemToEdit.editedFields;
                await this.fetchSpecialites();
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: error.response?.data?.errors
                        ? Object.values(error.response.data.errors).join(' ')
                        : 'Erreur lors de la mise à jour.',
                    life: 5000,
                });
            }
            this.editDialogVisible = false;
            this.itemToEdit = null;
        },
        cancelEditing(data) {
            data.editing = false;
            delete data.editedFields;
        },
        confirmDeleteInfoSpecialite(data) {
            this.itemToDelete =
                this.specialites.find((s) => s.id === data.id) || data;
            this.deleteDialogVisible = true;
        },
        async confirmDelete() {
            if (!this.itemToDelete) return;
            const info = this.itemToDelete.informations_specialites?.find(
                (info) => info.annee_id === this.selectedAnnee
            );
            if (info) {
                try {
                    await axios.delete(
                        `/api/informations-specialites/${info.id}`
                    );
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Information supprimée.',
                        life: 3000,
                    });
                    const fixIndex = this.FixLignes.findIndex(
                        (f) => f.id === this.itemToDelete.id
                    );
                    if (fixIndex !== -1) {
                        this.FixLignes.splice(fixIndex, 1);
                    }
                    await this.fetchSpecialites();
                } catch (error) {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'Impossible de supprimer.',
                        life: 3000,
                    });
                }
            } else {
                this.toast.add({
                    severity: 'warn',
                    summary: 'Avertissement',
                    detail: 'Aucune donnée pour cette année.',
                    life: 3000,
                });
            }
            this.deleteDialogVisible = false;
            this.itemToDelete = null;
        },
        getSeverity(value) {
            const severityMap = {
                مقيس: 'success',
                'غير مقيس': 'danger',
                جديد: 'warning',
                BTS: 'info',
                BTP: 'secondary',
                CAP: 'warning',
                CC: 'success',
                CFA: 'danger',
                Homologuée: 'success',
                'Non Homologuée': 'danger',
            };
            return severityMap[value] || 'secondary';
        },
        toggleFreezeDropdown() {
            this.showFreezeDropdown = !this.showFreezeDropdown;
        },
        freezeColumn() {
            if (this.selectedColumnToFreeze) {
                const field = this.selectedColumnToFreeze.field;
                if (this.frozenColumns.includes(field)) {
                    this.frozenColumns = this.frozenColumns.filter(
                        (col) => col !== field
                    );
                    this.toast.add({
                        severity: 'info',
                        summary: 'Colonne libérée',
                        detail: `${this.selectedColumnToFreeze.header} n'est plus figée.`,
                        life: 3000,
                    });
                } else {
                    this.frozenColumns.push(field);
                    this.toast.add({
                        severity: 'success',
                        summary: 'Colonne figée',
                        detail: `${this.selectedColumnToFreeze.header} est maintenant figée.`,
                        life: 3000,
                    });
                }
                this.showFreezeDropdown = false;
                this.selectedColumnToFreeze = null;
            }
        },
        async Importxls() {
            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.accept = '.xls,.xlsx';
            fileInput.style.display = 'none';
            fileInput.onchange = async (event) => {
                const file = event.target.files[0];
                if (!file) return;
                const formData = new FormData();
                formData.append('file', file);
                try {
                    const response = await axios.post(
                        '/api/informations-specialites/importxls',
                        formData,
                        { headers: { 'Content-Type': 'multipart/form-data' } }
                    );
                    this.errors = response.data.error_lines || [];
                    this.toast.add({
                        severity: this.errors.length > 0 ? 'warn' : 'success',
                        summary: 'Import XLS',
                        detail:
                            this.errors.length > 0
                                ? `Import terminé avec ${this.errors.length} erreurs.`
                                : 'Importation réussie.',
                        life: 10000,
                    });
                    if (this.errors.length > 0) this.showImportError = true;
                    await this.refreshTable();
                } catch (error) {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: "Erreur lors de l'import XLS.",
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
        async exportXLS() {
            try {
                const workbook = new ExcelJS.Workbook();
                const worksheet = workbook.addWorksheet('Spécialités');

                worksheet.columns = [
                    { header: 'Code', key: 'code', width: 15 },
                    { header: 'Nom (FR)', key: 'nom_fr', width: 30 },
                    { header: 'Nom (AR)', key: 'nom_ar', width: 30 },
                    { header: 'Type', key: 'type', width: 15 },
                    { header: 'Diplôme', key: 'diplome', width: 30 },
                    { header: 'Statut', key: 'statut', width: 15 },
                    { header: 'Homologation', key: 'homologation', width: 20 },
                    {
                        header: 'Heures Techniques',
                        key: 'heures_et',
                        width: 20,
                    },
                    { header: 'Heures Généraux', key: 'heures_eg', width: 20 },
                    {
                        header: 'Heures Totales',
                        key: 'heures_totales',
                        width: 20,
                    },
                    {
                        header: 'Durée de Formation',
                        key: 'duree_formation',
                        width: 20,
                    },
                    { header: 'Observation', key: 'observation', width: 30 },
                ];

                this.specialites.forEach((s) => {
                    worksheet.addRow({
                        code: s.code,
                        nom_fr: s.nom_fr,
                        nom_ar: s.nom_ar,
                        type: s.type,
                        diplome: s.diplome,
                        statut: s.statut,
                        homologation: this.getDynamicField(s, 'homologation'),
                        heures_et: this.getDynamicField(s, 'heures_et'),
                        heures_eg: this.getDynamicField(s, 'heures_eg'),
                        heures_totales: this.getDynamicField(
                            s,
                            'heures_totales'
                        ),
                        duree_formation: this.getDynamicField(
                            s,
                            'duree_formation'
                        ),
                        observation: this.getDynamicField(s, 'observation'),
                    });
                });

                worksheet.getRow(1).eachCell((cell) => {
                    cell.font = { bold: true };
                    cell.fill = {
                        type: 'pattern',
                        pattern: 'solid',
                        fgColor: { argb: 'FF1976D2' },
                    };
                    cell.color = { argb: 'FFFFFFFF' };
                });

                const buffer = await workbook.xlsx.writeBuffer();
                const blob = new Blob([buffer], {
                    type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = `specialites_${this.anneeText}.xlsx`;
                link.click();
                URL.revokeObjectURL(link.href);

                this.toast.add({
                    severity: 'success',
                    summary: 'Export XLS',
                    detail: 'Exportation réussie.',
                    life: 3000,
                });
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: "Erreur lors de l'export XLS.",
                    life: 3000,
                });
                console.error(error);
            }
        },
        refreshTable() {
            this.fetchSpecialites();
        },
    },
};
</script>
