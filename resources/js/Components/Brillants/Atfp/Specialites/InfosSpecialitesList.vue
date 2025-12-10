<template>
    <div class="surface-ground pt-2 px-1.5 pb-1.5" style="margin-top: 0">
        <!-- ProgressSpinner pour l'importation -->
        <div v-if="isImporting" class="progress-overlay">
            <ProgressSpinner
                style="width: 50px; height: 50px"
                strokeWidth="5"
            />
            <span class="progress-text">Importation en cours...</span>
        </div>
        <div
            class="surface-card p-2 border-round border-1 surface-border"
            style="
                position: relative;
                top: -34px;
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
                        label="Spécialités"
                        icon="pi pi-list"
                        class="p-button-text p-button-plain"
                        @click="$router.push({ name: 'specialites' })"
                    />
                    <Button
                        label="Infos Spécialités"
                        icon="pi pi-info"
                        class="p-button-text p-button-plain"
                        disabled
                    />
                </div>
                <div class="flex gap-2">
                    <Button
                        icon="pi pi-refresh"
                        class="p-button-text p-button-plain"
                        @click="refreshTable"
                        v-tooltip="'Rafraîchir'"
                    />
                    <Button
                        icon="pi pi-file-import"
                        class="p-button-text p-button-plain"
                        @click="openImportPopup"
                        v-tooltip="'Importer XLS'"
                    />
                    <Button
                        icon="pi pi-file-export"
                        class="p-button-text p-button-plain"
                        @click="exportXLS"
                        v-tooltip="'Exporter XLS'"
                    />
                </div>
            </div>
        </div>
        <div
            class="surface-card p-4 pt-2 border-round shadow-2 border-1 surface-border"
        >
            <div class="flex justify-content-between align-items-center mb-4">
                <div class="flex align-items-center gap-2">
                    <span class="p-input-icon-left search-field">
                        <InputText
                            v-model="filters['global'].value"
                            placeholder="Rechercher..."
                            :autofocus="false"
                        />
                    </span>
                    <Button
                        icon="pi pi-filter-slash"
                        class="p-button-outlined p-button-secondary"
                        size="small"
                        @click="clearFilter"
                        v-tooltip="'Réinitialiser les filtres'"
                    />
                    <div class="annee-filter">
                        <Dropdown
                            v-model="selectedAnnee"
                            :options="annees"
                            optionLabel="intitule"
                            optionValue="id"
                            placeholder="Année"
                            class="button-height"
                            @change="applyAnneeFilter"
                            :autofocus="false"
                        />
                        <Button
                            icon="pi pi-times"
                            class="p-button-outlined p-button-danger"
                            size="small"
                            @click="clearAnneeFilter"
                            v-tooltip="'Effacer le filtre année'"
                        />
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button
                        v-if="errors.length > 0"
                        icon="pi pi-exclamation-triangle"
                        class="p-button-warning p-button-outlined"
                        size="small"
                        @click="showImportError = true"
                        v-tooltip="'Erreurs Import'"
                    />
                    <Button
                        label="Tous"
                        :severity="
                            activeTab === 'tous' ? 'primary' : 'secondary'
                        "
                        class="arabic-normal button-height"
                        @click="setActiveTab('tous')"
                    />
                    <Button
                        label="مقيس"
                        :severity="
                            activeTab === 'مقيس' ? 'success' : 'secondary'
                        "
                        class="arabic-normal button-height"
                        @click="setActiveTab('مقيس')"
                    />
                    <Button
                        label="غير مقيس"
                        :severity="
                            activeTab === 'غير مقيس' ? 'danger' : 'secondary'
                        "
                        class="arabic-normal button-height"
                        @click="setActiveTab('غير مقيس')"
                    />
                    <Button
                        label="جديد"
                        :severity="
                            activeTab === 'جديد' ? 'warning' : 'secondary'
                        "
                        class="arabic-normal button-height"
                        @click="setActiveTab('جديد')"
                    />
                    <SplitButton
                        label="Actions"
                        icon="pi pi-ellipsis-v"
                        :model="actions"
                        class="p-button-text"
                        v-tooltip="'Actions'"
                    />
                </div>
            </div>
            <DataTable
                v-model:filters="filters"
                showGridlines
                stripedRows
                v-model:selection="selectedSpecialites"
                :frozenValue="FixLignes"
                :value="filteredSpecialites"
                dataKey="id"
                size="small"
                paginator
                :rows="800"
                filterDisplay="menu"
                :globalFilterFields="[
                    'id',
                    'code',
                    'nom_ar',
                    'nom_fr',
                    'diplome_fr',
                    'statut',
                    'standardisation_ar',
                ]"
                :loading="loading"
                scrollable
                scrollHeight="750px"
                :pt="{
                    table: { style: 'min-width: 50rem' },
                    bodyrow: ({ props }) => ({
                        class: [{ 'font-bold': props.frozenRow }],
                    }),
                }"
            >
                <template #empty>
                    <div v-if="!loading" class="text-center p-3">
                        Aucune spécialité trouvée.
                    </div>
                    <div v-else class="text-center p-3">
                        Chargement en cours...
                    </div>
                </template>
                <Column
                    selectionMode="multiple"
                    headerStyle="width: 3rem"
                    frozen
                ></Column>
                <Column
                    field="code"
                    header="Code"
                    sortable
                    style="min-width: 6rem"
                    frozen
                >
                    <template #body="{ data }">
                        {{ data.code }}
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            placeholder="Filtrer par code"
                            class="p-column-filter"
                            @input="filterCallback()"
                        />
                    </template>
                </Column>
                <Column
                    field="nom_ar"
                    header="Nom (AR)"
                    sortable
                    style="min-width: 22rem"
                >
                    <template #body="{ data }">
                        <div class="arabic-normal">
                            {{ data.nom_ar }}
                        </div>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            placeholder="Filtrer par nom (AR)"
                            class="p-column-filter arabic-normal"
                            @input="filterCallback()"
                        />
                    </template>
                </Column>
                <Column
                    field="nom_fr"
                    header="Nom (FR)"
                    sortable
                    style="min-width: 22rem"
                >
                    <template #body="{ data }">
                        {{ data.nom_fr }}
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            placeholder="Filtrer par nom (FR)"
                            class="p-column-filter"
                            @input="filterCallback()"
                        />
                    </template>
                </Column>
                <Column
                    field="diplome_fr"
                    header="Diplôme"
                    sortable
                    style="min-width: 12rem"
                >
                    <template #body="{ data }">
                        <div class="centered-content">
                            <Tag
                                :value="data.diplome_fr || '----'"
                                :severity="getSeverity(data.diplome_fr)"
                            />
                        </div>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-model="filterModel.value"
                            :options="uniqueDiplomeFrValues"
                            placeholder="Sélectionner un diplôme"
                            class="p-column-filter"
                            @change="filterCallback()"
                        >
                            <template #option="{ option }">
                                <div class="centered-content">
                                    <Tag
                                        :value="option"
                                        :severity="getSeverity(option)"
                                    />
                                </div>
                            </template>
                        </Dropdown>
                    </template>
                </Column>
                <Column
                    field="homologation_fr"
                    header="Homologation"
                    sortable
                    style="min-width: 8rem"
                >
                    <template #body="{ data, index }">
                        <div
                            v-if="
                                editingCell.rowIndex === index &&
                                editingCell.field === 'homologation_fr'
                            "
                        >
                            <Dropdown
                                v-model="editingCell.value"
                                :options="homologationOptions"
                                placeholder="Sélectionner une homologation"
                                class="p-column-filter"
                                @change="
                                    debouncedSaveCell(data, 'homologation_fr')
                                "
                            />
                        </div>
                        <div
                            v-else
                            @dblclick="
                                startEditing(
                                    data,
                                    index,
                                    'homologation_fr',
                                    getDynamicField(data, 'homologation_fr')
                                )
                            "
                        >
                            <Tag
                                :value="
                                    getDynamicField(data, 'homologation_fr')
                                "
                                :severity="
                                    getSeverity(
                                        getDynamicField(data, 'homologation_fr')
                                    )
                                "
                            />
                        </div>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-model="filterModel.value"
                            :options="homologationOptions"
                            placeholder="Sélectionner une homologation"
                            class="p-column-filter"
                            @change="filterCallback()"
                        >
                            <template #option="{ option }">
                                <Tag
                                    :value="option"
                                    :severity="getSeverity(option)"
                                />
                            </template>
                        </Dropdown>
                    </template>
                </Column>
                <Column
                    field="heures_et"
                    header="Heures Techniques"
                    sortable
                    style="min-width: 8rem"
                >
                    <template #body="{ data, index }">
                        <div
                            v-if="
                                editingCell.rowIndex === index &&
                                editingCell.field === 'heures_et'
                            "
                        >
                            <InputNumber
                                v-model="editingCell.value"
                                :min="0"
                                mode="decimal"
                                :useGrouping="false"
                                :disabled="saving"
                                @blur="debouncedSaveCell(data, 'heures_et')"
                                @keyup.enter="
                                    debouncedSaveCell(data, 'heures_et')
                                "
                            />
                        </div>
                        <div
                            v-else
                            @dblclick="
                                startEditing(
                                    data,
                                    index,
                                    'heures_et',
                                    getDynamicField(data, 'heures_et')
                                )
                            "
                        >
                            {{ getDynamicField(data, 'heures_et') }}
                        </div>
                    </template>
                </Column>
                <Column
                    field="heures_eg"
                    header="Heures Généraux"
                    sortable
                    style="min-width: 8rem"
                >
                    <template #body="{ data, index }">
                        <div
                            v-if="
                                editingCell.rowIndex === index &&
                                editingCell.field === 'heures_eg'
                            "
                        >
                            <InputNumber
                                v-model="editingCell.value"
                                :min="0"
                                mode="decimal"
                                :useGrouping="false"
                                :disabled="saving"
                                @blur="debouncedSaveCell(data, 'heures_eg')"
                                @keyup.enter="
                                    debouncedSaveCell(data, 'heures_eg')
                                "
                            />
                        </div>
                        <div
                            v-else
                            @dblclick="
                                startEditing(
                                    data,
                                    index,
                                    'heures_eg',
                                    getDynamicField(data, 'heures_eg')
                                )
                            "
                        >
                            {{ getDynamicField(data, 'heures_eg') }}
                        </div>
                    </template>
                </Column>
                <Column
                    field="heures_totales"
                    header="Heures Totales"
                    sortable
                    style="min-width: 8rem"
                >
                    <template #body="{ data }">
                        {{ getDynamicField(data, 'heures_et', 'heures_eg') }}
                    </template>
                </Column>
                <Column
                    field="duree_formation"
                    header="Durée de Formation"
                    sortable
                    style="min-width: 8rem"
                >
                    <template #body="{ data, index }">
                        <div
                            v-if="
                                editingCell.rowIndex === index &&
                                editingCell.field === 'duree_formation'
                            "
                        >
                            <InputNumber
                                v-model="editingCell.value"
                                :min="0"
                                :max="9.9"
                                :step="0.1"
                                mode="decimal"
                                :useGrouping="false"
                                :format="false"
                                :disabled="saving"
                                @blur="
                                    debouncedSaveCell(data, 'duree_formation')
                                "
                                @keyup.enter="
                                    debouncedSaveCell(data, 'duree_formation')
                                "
                            />
                        </div>
                        <div
                            v-else
                            @dblclick="
                                startEditing(
                                    data,
                                    index,
                                    'duree_formation',
                                    getDynamicField(data, 'duree_formation')
                                )
                            "
                        >
                            {{ getDynamicField(data, 'duree_formation') }}
                        </div>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            placeholder="Filtrer par durée"
                            class="p-column-filter"
                            @input="filterCallback()"
                        />
                    </template>
                </Column>
                <Column
                    field="statut"
                    header="Statut"
                    sortable
                    style="min-width: 8rem"
                >
                    <template #body="{ data }">
                        <div class="centered-content">
                            <Tag
                                :value="getDynamicField(data, 'statut')"
                                :severity="
                                    getSeverity(getDynamicField(data, 'statut'))
                                "
                            />
                        </div>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-model="filterModel.value"
                            :options="statuts"
                            placeholder="Sélectionner un statut"
                            class="p-column-filter"
                            @change="filterCallback()"
                        >
                            <template #option="{ option }">
                                <Tag
                                    :value="option"
                                    :severity="getSeverity(option)"
                                />
                            </template>
                        </Dropdown>
                    </template>
                </Column>
                <Column
                    field="observation"
                    header="Observation"
                    sortable
                    style="min-width: 22rem"
                >
                    <template #body="{ data, index }">
                        <div
                            v-if="
                                editingCell.rowIndex === index &&
                                editingCell.field === 'observation'
                            "
                        >
                            <InputText
                                v-model="editingCell.value"
                                class="arabic-normal"
                                :disabled="saving"
                                @blur="debouncedSaveCell(data, 'observation')"
                                @keyup.enter="
                                    debouncedSaveCell(data, 'observation')
                                "
                            />
                        </div>
                        <div
                            v-else
                            class="arabic-normal"
                            @dblclick="
                                startEditing(
                                    data,
                                    index,
                                    'observation',
                                    getDynamicField(data, 'observation')
                                )
                            "
                        >
                            {{ getDynamicField(data, 'observation') }}
                        </div>
                    </template>
                </Column>
                <Column header="Actions" style="min-width: 12rem" frozen>
                    <template #body="{ data, frozenRow, index }">
                        <div class="flex gap-2">
                            <Button
                                icon="pi pi-plus"
                                severity="success"
                                text
                                size="small"
                                @click="openForm(data)"
                                v-tooltip="'Ajouter Infos'"
                            />
                            <Button
                                :icon="
                                    frozenRow ? 'pi pi-lock-open' : 'pi pi-lock'
                                "
                                :disabled="
                                    frozenRow ? false : FixLignes.length >= 10
                                "
                                text
                                size="small"
                                @click="toggleLock(data, frozenRow, index)"
                            />
                            <Button
                                icon="pi pi-trash"
                                severity="danger"
                                text
                                @click="confirmDeleteInfoSpecialite(data)"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>
            <Dialog
                v-model:visible="importPopupVisible"
                :style="{ width: '30vw' }"
                header="Importer des données"
                :modal="true"
            >
                <div>
                    <div class="flex flex-column gap-3">
                        <Button
                            label="Importer des informations spécialités"
                            icon="pi pi-file-import"
                            severity="info"
                            @click="importInformationsSpecialites"
                        />
                    </div>
                </div>
            </Dialog>
            <ImportError
                :errors="errors"
                :visible="showImportError"
                @update:visible="showImportError = $event"
                @line-imported="handleLineImported"
                @close="refreshTable"
            />
            <Dialog
                v-model:visible="deleteFormVisible"
                :style="{ width: '450px' }"
                header="Confirmer la suppression"
                :modal="true"
            >
                <div class="confirmation-content">
                    <i
                        class="pi pi-exclamation-triangle mr-3"
                        style="font-size: 2rem"
                    />
                    <span
                        >Êtes-vous sûr de vouloir supprimer ces informations
                        ?</span
                    >
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
                        @click="deleteInfoSpecialite"
                    />
                </template>
            </Dialog>
            <Dialog
                v-model:visible="formVisible"
                :style="{ width: '50vw' }"
                header="Ajouter/Modifier Spécialité"
                :modal="true"
            >
                <InfosSpecialitesForm
                    :visible="formVisible"
                    :specialite="selectedSpecialite"
                    :anneeId="selectedAnnee || currentYearId"
                    @save="saveSpecialite"
                    @cancel="formVisible = false"
                    @update:visible="formVisible = $event"
                />
            </Dialog>
            <Toast />
        </div>
    </div>
</template>

<script>
import { ref, computed } from 'vue';
import { FilterMatchMode, FilterOperator } from 'primevue/api';
import { useToast } from 'primevue/usetoast';
import axios from '@/axios';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Dropdown from 'primevue/dropdown';
import SplitButton from 'primevue/splitbutton';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import ProgressSpinner from 'primevue/progressspinner';
import ImportError from '@/Components/Atfp/ImportError/InfoSpecialitesImportError.vue';
import InfosSpecialitesForm from '@/Components/Atfp/Specialites/InfoSpecialiteForm.vue';
import ExcelJS from 'exceljs';
import { debounce } from 'lodash';

export default {
    components: {
        Button,
        InputText,
        InputNumber,
        DataTable,
        Column,
        Tag,
        Dropdown,
        SplitButton,
        Dialog,
        Toast,
        ProgressSpinner,
        ImportError,
        InfosSpecialitesForm,
    },
    setup() {
        const toast = useToast();
        const specialites = ref({ data: [] });
        return { toast, specialites };
    },
    data() {
        return {
            activeTab: 'tous',
            FixLignes: [],
            selectedSpecialites: [],
            filters: null,
            selectedAnnee: null,
            homologationOptions: ['Homologuée', 'Non Homologuée'],
            statuts: ['Active', 'Non Active', 'Annulée', 'Remplacée'],
            standardisations_ar: ['جديد', 'مقيس', 'غير مقيس'],
            actions: [
                {
                    label: 'Supprimer',
                    icon: 'pi pi-trash',
                    command: () => this.deleteSelectedInfoSpecialites(),
                },
            ],
            deleteFormVisible: false,
            formVisible: false,
            infoSpecialiteToDelete: null,
            selectedSpecialite: null,
            loading: true,
            secteurs: [],
            sousSecteurs: [],
            selectedSecteur: null,
            errors: [],
            showImportError: false,
            importPopupVisible: false,
            isImporting: false,
            annees: [],
            currentYearId: null,
            editingCell: { rowIndex: null, field: null, value: null },
            saving: false,
            homologationMapping: {
                Homologuée: 'منظر',
                'Non Homologuée': 'غير منظر',
            },
        };
    },
    computed: {
        filteredSpecialites() {
            let filtered = this.specialites.data || [];
            if (this.activeTab !== 'tous') {
                filtered = filtered.filter(
                    (s) => s.standardisation_ar === this.activeTab
                );
            }
            return filtered;
        },
        uniqueDiplomeFrValues() {
            const diplomes = this.specialites.data
                .map((s) => s.diplome_fr)
                .filter(
                    (value, index, self) =>
                        value && self.indexOf(value) === index
                );
            return ['', ...diplomes.sort()];
        },
    },
    async created() {
        this.initFilters();
        await Promise.all([
            this.fetchAnnees(),
            this.fetchSecteurs(),
            this.fetchSousSecteurs(),
        ]);
        this.setCurrentYear();
        this.selectedAnnee = this.currentYearId;
        await this.fetchSpecialites();
        this.loading = false;
        this.debouncedSaveCell = debounce(this.saveCell, 500);
    },
    methods: {
        setActiveTab(tab) {
            this.activeTab = tab;
            this.refreshTable();
        },
        clearFilter() {
            this.initFilters();
        },
        clearAnneeFilter() {
            this.selectedAnnee = this.currentYearId;
            this.applyAnneeFilter();
        },
        openForm(specialite = null) {
            this.selectedSpecialite = specialite;
            this.formVisible = true;
        },
        async saveSpecialite(specialiteData) {
            const yearId = this.selectedAnnee || this.currentYearId;
            const payload = {
                ...specialiteData,
                annee_formation_id: yearId,
            };
            try {
                if (
                    this.selectedSpecialite?.infos_specialites?.find(
                        (info) => info.annee_formation_id === yearId
                    )?.id
                ) {
                    const infoId =
                        this.selectedSpecialite.infos_specialites.find(
                            (info) => info.annee_formation_id === yearId
                        ).id;
                    await axios.put(
                        `/api/infos-specialites/${infoId}`,
                        payload
                    );
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Spécialité mise à jour.',
                        life: 3000,
                    });
                } else {
                    await axios.post(`/api/infos-specialites`, payload);
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Spécialité ajoutée.',
                        life: 3000,
                    });
                }
                await this.refreshTable();
                this.formVisible = false;
                this.selectedSpecialite = null;
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        "Échec de l'enregistrement.",
                    life: 3000,
                });
            }
        },
        initFilters() {
            this.filters = {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                code: {
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
                nom_fr: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.CONTAINS },
                    ],
                },
                diplome_fr: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.EQUALS },
                    ],
                },
                standardisation_ar: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.EQUALS },
                    ],
                },
                homologation_fr: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.EQUALS },
                    ],
                },
                statut: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.EQUALS },
                    ],
                },
                duree_formation: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.CONTAINS },
                    ],
                },
            };
        },
        async refreshTable() {
            this.loading = true;
            try {
                await this.fetchSpecialites();
                this.toast.add({
                    severity: 'success',
                    summary: 'Rafraîchi',
                    detail: 'Tableau mis à jour.',
                    life: 3000,
                });
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Échec du chargement des spécialités.',
                    life: 3000,
                });
            } finally {
                this.loading = false;
            }
        },
        startEditing(data, index, field, value) {
            this.editingCell = {
                rowIndex: index,
                field,
                value: value === '----' ? null : value,
            };
        },
        async saveCell(data, field) {
            if (this.saving) return;
            if (
                this.editingCell.value === undefined ||
                this.editingCell.value === '----'
            ) {
                this.editingCell = { rowIndex: null, field: null, value: null };
                return;
            }
            const yearId = this.selectedAnnee || this.currentYearId;
            if (!yearId) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Aucune année sélectionnée.',
                    life: 3000,
                });
                this.editingCell = { rowIndex: null, field: null, value: null };
                return;
            }
            this.saving = true;
            try {
                let infoSpecialite = data.infos_specialites?.find(
                    (info) => info.annee_formation_id === yearId
                );
                let value = this.editingCell.value;
                let payload = {
                    specialite_id: data.id,
                    annee_formation_id: yearId,
                    [field]: value,
                };
                if (field === 'heures_et' || field === 'heures_eg') {
                    value =
                        value !== null && value !== undefined
                            ? parseInt(value)
                            : null;
                    if (isNaN(value)) value = null;
                    payload[field] = value;
                } else if (field === 'duree_formation') {
                    value =
                        value !== null && value !== undefined
                            ? parseFloat(value.toFixed(1))
                            : null;
                    if (isNaN(value)) value = null;
                    payload[field] = value;
                } else if (field === 'homologation_fr') {
                    payload.homologation_ar =
                        this.homologationMapping[value] || null;
                }
                let response;
                if (infoSpecialite) {
                    response = await axios.put(
                        `/api/infos-specialites/${infoSpecialite.id}`,
                        payload
                    );
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Information mise à jour.',
                        life: 3000,
                    });
                } else {
                    response = await axios.post(
                        `/api/infos-specialites`,
                        payload
                    );
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Information ajoutée.',
                        life: 3000,
                    });
                }
                await this.fetchSpecialites();
            } catch (error) {
                const errorMessage =
                    error.response?.data?.message ||
                    'Erreur lors de la mise à jour.';
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: errorMessage,
                    life: 3000,
                });
            } finally {
                this.saving = false;
                this.editingCell = { rowIndex: null, field: null, value: null };
            }
        },
        async confirmDeleteInfoSpecialite(data) {
            const yearId = this.selectedAnnee || this.currentYearId;
            const infoSpecialite = data.infos_specialites?.find(
                (info) => info.annee_formation_id === yearId
            );
            if (!infoSpecialite) {
                this.toast.add({
                    severity: 'warn',
                    summary: 'Aucune information',
                    detail: 'Aucune information à supprimer pour cette année.',
                    life: 3000,
                });
                return;
            }
            this.infoSpecialiteToDelete = infoSpecialite;
            this.deleteFormVisible = true;
        },
        async deleteInfoSpecialite() {
            if (this.infoSpecialiteToDelete) {
                try {
                    await axios.delete(
                        `/api/infos-specialites/${this.infoSpecialiteToDelete.id}`
                    );
                    this.toast.add({
                        severity: 'success',
                        summary: 'Supprimé',
                        detail: 'Information supprimée avec succès.',
                        life: 3000,
                    });
                    await this.refreshTable();
                } catch (error) {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: "Impossible de supprimer l'information.",
                        life: 3000,
                    });
                } finally {
                    this.deleteFormVisible = false;
                    this.infoSpecialiteToDelete = null;
                }
            }
        },
        async deleteSelectedInfoSpecialites() {
            if (this.selectedSpecialites?.length > 0) {
                const yearId = this.selectedAnnee || this.currentYearId;
                const infoSpecialiteIds = this.selectedSpecialites
                    .map(
                        (s) =>
                            s.infos_specialites?.find(
                                (info) => info.annee_formation_id === yearId
                            )?.id
                    )
                    .filter((id) => id);
                if (infoSpecialiteIds.length === 0) {
                    this.toast.add({
                        severity: 'warn',
                        summary: 'Aucune information',
                        detail: 'Aucune information à supprimer pour les spécialités sélectionnées.',
                        life: 3000,
                    });
                    return;
                }
                try {
                    await axios.post('/api/infos-specialites/delete-selected', {
                        ids: infoSpecialiteIds,
                    });
                    this.toast.add({
                        severity: 'success',
                        summary: 'Supprimé',
                        detail: 'Informations supprimées avec succès.',
                        life: 3000,
                    });
                    this.selectedSpecialites = null;
                    await this.refreshTable();
                } catch (error) {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'Échec de la suppression des informations.',
                        life: 3000,
                    });
                }
            } else {
                this.toast.add({
                    severity: 'warn',
                    summary: 'Aucune sélection',
                    detail: 'Veuillez sélectionner une ou plusieurs spécialités.',
                    life: 3000,
                });
            }
        },
        async fetchSpecialites() {
            try {
                const response = await axios.get('/api/specialites', {
                    params: {
                        with_infos: true,
                        annee_formation_id: this.selectedAnnee,
                        standardisation_ar:
                            this.activeTab !== 'tous' ? this.activeTab : null,
                    },
                });
                this.specialites = response.data;
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Impossible de charger les spécialités.',
                    life: 3000,
                });
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
                    detail: 'Impossible de charger les secteurs.',
                    life: 3000,
                });
            }
        },
        async fetchSousSecteurs() {
            try {
                const response = await axios.get('/api/sous-secteurs');
                this.sousSecteurs = response.data;
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Impossible de charger les sous-secteurs.',
                    life: 3000,
                });
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
            }
        },
        setCurrentYear() {
            const now = new Date();
            let currentYear = this.annees.find((annee) => {
                const startDate = new Date(annee.date_debut);
                const endDate = new Date(annee.date_fin);
                return now >= startDate && now <= endDate;
            });

            if (!currentYear && this.annees.length > 0) {
                currentYear = this.annees.reduce((latest, current) => {
                    return new Date(current.date_fin) >
                        new Date(latest.date_fin)
                        ? current
                        : latest;
                });
            }

            this.currentYearId = currentYear ? currentYear.id : null;
            this.selectedAnnee = this.currentYearId;
        },
        getSecteurNomAr(secteurId) {
            if (!this.secteurs || !Array.isArray(this.secteurs)) return '----';
            const secteur = this.secteurs.find((s) => s.id === secteurId);
            return secteur ? secteur.nom_ar : '----';
        },
        getSousSecteurNomAr(sousSecteurId) {
            if (!this.sousSecteurs || !Array.isArray(this.sousSecteurs))
                return '----';
            const sousSecteur = this.sousSecteurs.find(
                (s) => s.id === sousSecteurId
            );
            return sousSecteur ? sousSecteur.nom_ar : '----';
        },
        formatDate(date) {
            if (!date) return null;
            if (typeof date === 'string') date = new Date(date);
            if (!(date instanceof Date) || isNaN(date.getTime())) return null;
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        },
        toggleLock(data, frozen, index) {
            if (frozen) {
                this.FixLignes = this.FixLignes.filter((c, i) => i !== index);
                this.specialites.data.push(data);
            } else if (this.FixLignes.length < 10) {
                this.specialites.data = this.specialites.data.filter(
                    (c, i) => i !== index
                );
                this.FixLignes.push(data);
            }
            this.specialites.data.sort((val1, val2) =>
                val1.id < val2.id ? -1 : 1
            );
        },
        getSeverity(value) {
            switch (value) {
                case 'Homologuée':
                case 'Active':
                case 'Certificat de Compétence':
                case 'مقيس':
                    return 'success';
                case 'Non Homologuée':
                case 'Annulée':
                case "Certificat de Fin d'Apprentissage":
                case 'غير مقيس':
                    return 'danger';
                case 'Non Active':
                case 'Remplacée':
                case "Certificat d'Aptitude Professionnelle":
                case 'جديد':
                    return 'warning';
                case 'Brevet de Technicien Supérieur':
                    return 'info';
                case 'Brevet de Technicien Professionnel':
                case 'Certificat de Formation Professionnelle':
                case 'Formation Technique Professionnelle':
                    return 'secondary';
                default:
                    return 'secondary';
            }
        },
        openImportPopup() {
            this.importPopupVisible = true;
        },
        getDynamicField(data, field, secondField = null) {
            const yearId = this.selectedAnnee || this.currentYearId;
            if (!yearId || !data.infos_specialites) {
                return '----';
            }
            const infos = data.infos_specialites.filter(
                (info) => info.annee_formation_id === yearId
            );
            if (infos.length === 0) {
                return '----';
            }
            const info = infos.sort(
                (a, b) => new Date(b.updated_at) - new Date(a.updated_at)
            )[0];
            if (!info) {
                return '----';
            }
            if (field === 'heures_et' && secondField === 'heures_eg') {
                const heuresEt = info.heures_et || 0;
                const heuresEg = info.heures_eg || 0;
                return heuresEt + heuresEg;
            }
            return info[field] !== null && info[field] !== undefined
                ? info[field]
                : '----';
        },
        applyAnneeFilter() {
            this.refreshTable();
        },
        async importInformationsSpecialites() {
            this.importPopupVisible = false;
            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.accept = '.xls,.xlsx';
            fileInput.style.display = 'none';
            fileInput.onchange = async (event) => {
                const file = event.target.files[0];
                if (!file) return;
                this.isImporting = true;
                const formData = new FormData();
                formData.append('file', file);
                try {
                    const response = await axios.post(
                        '/api/infos-specialites/importxls',
                        formData,
                        {
                            headers: { 'Content-Type': 'multipart/form-data' },
                        }
                    );
                    this.errors = response.data.error_lines || [];
                    let message = 'Importation réussie.';
                    let severity = 'success';
                    if (this.errors.length > 0) {
                        message = `Importation terminée avec ${this.errors.length} erreurs.`;
                        severity = 'warn';
                        this.showImportError = true;
                    }
                    await this.refreshTable();
                    this.toast.add({
                        severity,
                        summary: 'Import XLS',
                        detail: message,
                        life: 10000,
                    });
                } catch (error) {
                    const errorMessage =
                        error.response?.data?.message ||
                        "Erreur lors de l'import XLS.";
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: errorMessage,
                        life: 3000,
                    });
                } finally {
                    this.isImporting = false;
                    fileInput.remove();
                }
            };
            document.body.appendChild(fileInput);
            fileInput.click();
        },
        async exportXLS() {
            try {
                const data = this.filteredSpecialites.map((specialite) => {
                    const yearId = this.selectedAnnee || this.currentYearId;
                    const info =
                        specialite.infos_specialites?.find(
                            (info) => info.annee_formation_id === yearId
                        ) || {};
                    return {
                        Code: specialite.code,
                        'Nom (FR)': specialite.nom_fr,
                        'Nom (AR)': specialite.nom_ar,
                        Diplôme: specialite.diplome_fr || '----',
                        'Secteur (AR)': this.getSecteurNomAr(
                            specialite.secteur_id
                        ),
                        'Sous-Secteur (AR)': this.getSousSecteurNomAr(
                            specialite.sous_secteur_id
                        ),
                        Homologation: info.homologation_fr || '----',
                        'Heures Techniques': info.heures_et || '----',
                        'Heures Généraux': info.heures_eg || '----',
                        'Heures Totales':
                            (info.heures_et || 0) + (info.heures_eg || 0) ||
                            '----',
                        'Durée de Formation': info.duree_formation || '----',
                        Statut: info.statut || '----',
                        Observation: info.observation || '----',
                        'Date création': this.formatDate(
                            specialite.date_creation
                        ),
                        'Date annulation': this.formatDate(
                            specialite.date_annulation
                        ),
                    };
                });
                const workbook = new ExcelJS.Workbook();
                const worksheet = workbook.addWorksheet('Spécialités');
                worksheet.columns = [
                    { header: 'Code', key: 'Code', width: 15 },
                    { header: 'Nom (FR)', key: 'Nom (FR)', width: 30 },
                    { header: 'Nom (AR)', key: 'Nom (AR)', width: 30 },
                    { header: 'Diplôme', key: 'Diplôme', width: 15 },
                    { header: 'Secteur (AR)', key: 'Secteur (AR)', width: 30 },
                    {
                        header: 'Sous-Secteur (AR)',
                        key: 'Sous-Secteur (AR)',
                        width: 30,
                    },
                    { header: 'Homologation', key: 'Homologation', width: 15 },
                    {
                        header: 'Heures Techniques',
                        key: 'Heures Techniques',
                        width: 15,
                    },
                    {
                        header: 'Heures Généraux',
                        key: 'Heures Généraux',
                        width: 15,
                    },
                    {
                        header: 'Heures Totales',
                        key: 'Heures Totales',
                        width: 15,
                    },
                    {
                        header: 'Durée de Formation',
                        key: 'Durée de Formation',
                        width: 15,
                    },
                    { header: 'Statut', key: 'Statut', width: 15 },
                    { header: 'Observation', key: 'Observation', width: 30 },
                    {
                        header: 'Date création',
                        key: 'Date création',
                        width: 15,
                    },
                    {
                        header: 'Date annulation',
                        key: 'Date annulation',
                        width: 15,
                    },
                ];
                worksheet.addRows(data);
                const buffer = await workbook.xlsx.writeBuffer();
                const blob = new Blob([buffer], {
                    type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                });
                const link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = `specialites_${new Date().toISOString().split('T')[0]}.xlsx`;
                link.click();
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: "Échec de l'export XLS.",
                    life: 3000,
                });
            }
        },
        handleLineImported(importedLine) {
            this.errors = this.errors.filter(
                (err) => err.line !== importedLine.line
            );
        },
    },
};
</script>

<style scoped>
@font-face {
    font-family: 'Montserrat-Arabic';
    src: url('/fonts/Montserrat-Arabic-Regular.otf') format('opentype');
    font-weight: normal;
    font-style: normal;
}

.arabic-normal {
    font-family: 'Montserrat-Arabic', sans-serif;
    direction: rtl;
}

.arabic-gras {
    font-family: 'Montserrat-Arabic', sans-serif;
    font-weight: bold;
    direction: rtl;
}

.button-height {
    height: 40px;
}

.centered-content {
    display: flex;
    justify-content: center;
    align-items: center;
}

.annee-filter {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.search-field {
    display: flex;
    align-items: center;
}

.progress-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.progress-text {
    margin-top: 10px;
    color: #ffffff;
    font-size: 1.2rem;
    font-family: 'Montserrat-Arabic', sans-serif;
}
</style>
