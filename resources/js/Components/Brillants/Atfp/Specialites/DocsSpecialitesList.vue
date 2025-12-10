<template>
    <div class="surface-ground pt-2 px-1.5 pb-1.5" style="margin-top: 0">
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
                        @click="$router.push({ name: 'infos-specialites' })"
                    />
                    <Button
                        label="Docs Spécialités"
                        icon="pi pi-file"
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
                :rows="20"
                filterDisplay="menu"
                :globalFilterFields="[
                    'code',
                    'nom_ar',
                    'nom_fr',
                    'homologation_fr',
                    'programme_etude',
                    'dossier_homologation',
                    'guide_pedagogique',
                    'guide_evaluation',
                    'guide_organisation',
                    'guide_materiels',
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
                        {{ specialites.length }} spécialités chargées.
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
                        <div class="flex align-items-center gap-2">
                            <i
                                class="pi pi-circle-fill"
                                :style="{ color: getDocumentStatusColor(data) }"
                                style="font-size: 0.75rem"
                            ></i>
                            {{ data.code }}
                        </div>
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
                            class="p-column-filter arabic-gras"
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
                    field="homologation_fr"
                    header="Homologation"
                    sortable
                    style="min-width: 8rem"
                >
                    <template #body="{ data }">
                        <div class="centered-content">
                            <Tag
                                :value="data.homologation_fr || '----'"
                                :severity="getSeverity(data.homologation_fr)"
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
                    field="programme_etude"
                    header="Programme d'étude"
                    style="min-width: 10rem"
                >
                    <template #body="{ data }">
                        <div
                            v-if="
                                getDocumentIndicator('Programme d\'étude', data)
                            "
                            class="centered-content"
                        >
                            <Button
                                icon="pi pi-eye"
                                class="p-button-text p-button-plain"
                                @click="
                                    viewDocument(
                                        getDocumentIndicator(
                                            'Programme d\'étude',
                                            data
                                        ).documentId
                                    )
                                "
                                v-tooltip="'Visionner'"
                            />
                            <span class="indicator-label">{{
                                getDocumentIndicator("Programme d'étude", data)
                                    .label
                            }}</span>
                            <i
                                :class="
                                    getDocumentIndicator(
                                        'Programme d\'étude',
                                        data
                                    ).icon
                                "
                                :style="{
                                    color: getDocumentIndicator(
                                        'Programme d\'étude',
                                        data
                                    ).color,
                                }"
                            />
                        </div>
                        <div v-else class="centered-content">
                            Aucun document
                        </div>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-model="filterModel.value"
                            :options="documentStatusOptions"
                            optionValue="value"
                            placeholder="Statut document"
                            class="p-column-filter"
                            @change="filterCallback()"
                        >
                            <template #option="{ option }">
                                <div class="flex align-items-center gap-2">
                                    <i
                                        :class="option.icon"
                                        :style="{ color: option.color }"
                                    ></i>
                                    <span>{{ option.label }}</span>
                                </div>
                            </template>
                        </Dropdown>
                    </template>
                </Column>
                <Column
                    field="dossier_homologation"
                    header="Dossier d'homologation"
                    style="min-width: 10rem"
                >
                    <template #body="{ data }">
                        <div
                            v-if="
                                getDocumentIndicator(
                                    'Dossier d\'homologation',
                                    data
                                )
                            "
                            class="centered-content"
                        >
                            <Button
                                icon="pi pi-eye"
                                class="p-button-text p-button-plain"
                                @click="
                                    viewDocument(
                                        getDocumentIndicator(
                                            'Dossier d\'homologation',
                                            data
                                        ).documentId
                                    )
                                "
                                v-tooltip="'Visionner'"
                            />
                            <span class="indicator-label">{{
                                getDocumentIndicator(
                                    "Dossier d'homologation",
                                    data
                                ).label
                            }}</span>
                            <i
                                :class="
                                    getDocumentIndicator(
                                        'Dossier d\'homologation',
                                        data
                                    ).icon
                                "
                                :style="{
                                    color: getDocumentIndicator(
                                        'Dossier d\'homologation',
                                        data
                                    ).color,
                                }"
                            />
                        </div>
                        <div v-else class="centered-content">
                            Aucun document
                        </div>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-model="filterModel.value"
                            :options="documentStatusOptions"
                            optionValue="value"
                            placeholder="Statut document"
                            class="p-column-filter"
                            @change="filterCallback()"
                        >
                            <template #option="{ option }">
                                <div class="flex align-items-center gap-2">
                                    <i
                                        :class="option.icon"
                                        :style="{ color: option.color }"
                                    ></i>
                                    <span>{{ option.label }}</span>
                                </div>
                            </template>
                        </Dropdown>
                    </template>
                </Column>
                <Column
                    field="guide_pedagogique"
                    header="Guide pédagogique"
                    style="min-width: 10rem"
                >
                    <template #body="{ data }">
                        <div
                            v-if="
                                getDocumentIndicator('Guide pédagogique', data)
                            "
                            class="centered-content"
                        >
                            <Button
                                icon="pi pi-eye"
                                class="p-button-text p-button-plain"
                                @click="
                                    viewDocument(
                                        getDocumentIndicator(
                                            'Guide pédagogique',
                                            data
                                        ).documentId
                                    )
                                "
                                v-tooltip="'Visionner'"
                            />
                            <span class="indicator-label">{{
                                getDocumentIndicator('Guide pédagogique', data)
                                    .label
                            }}</span>
                            <i
                                :class="
                                    getDocumentIndicator(
                                        'Guide pédagogique',
                                        data
                                    ).icon
                                "
                                :style="{
                                    color: getDocumentIndicator(
                                        'Guide pédagogique',
                                        data
                                    ).color,
                                }"
                            />
                        </div>
                        <div v-else class="centered-content">
                            Aucun document
                        </div>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-model="filterModel.value"
                            :options="documentStatusOptions"
                            optionValue="value"
                            placeholder="Statut document"
                            class="p-column-filter"
                            @change="filterCallback()"
                        >
                            <template #option="{ option }">
                                <div class="flex align-items-center gap-2">
                                    <i
                                        :class="option.icon"
                                        :style="{ color: option.color }"
                                    ></i>
                                    <span>{{ option.label }}</span>
                                </div>
                            </template>
                        </Dropdown>
                    </template>
                </Column>
                <Column
                    field="guide_evaluation"
                    header="Guide d'évaluation"
                    style="min-width: 10rem"
                >
                    <template #body="{ data }">
                        <div
                            v-if="
                                getDocumentIndicator(
                                    'Guide d\'évaluation',
                                    data
                                )
                            "
                            class="centered-content"
                        >
                            <Button
                                icon="pi pi-eye"
                                class="p-button-text p-button-plain"
                                @click="
                                    viewDocument(
                                        getDocumentIndicator(
                                            'Guide d\'évaluation',
                                            data
                                        ).documentId
                                    )
                                "
                                v-tooltip="'Visionner'"
                            />
                            <span class="indicator-label">{{
                                getDocumentIndicator("Guide d'évaluation", data)
                                    .label
                            }}</span>
                            <i
                                :class="
                                    getDocumentIndicator(
                                        'Guide d\'évaluation',
                                        data
                                    ).icon
                                "
                                :style="{
                                    color: getDocumentIndicator(
                                        'Guide d\'évaluation',
                                        data
                                    ).color,
                                }"
                            />
                        </div>
                        <div v-else class="centered-content">
                            Aucun document
                        </div>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-model="filterModel.value"
                            :options="documentStatusOptions"
                            optionValue="value"
                            placeholder="Statut document"
                            class="p-column-filter"
                            @change="filterCallback()"
                        >
                            <template #option="{ option }">
                                <div class="flex align-items-center gap-2">
                                    <i
                                        :class="option.icon"
                                        :style="{ color: option.color }"
                                    ></i>
                                    <span>{{ option.label }}</span>
                                </div>
                            </template>
                        </Dropdown>
                    </template>
                </Column>
                <Column
                    field="guide_organisation"
                    header="Guide d'organisation"
                    style="min-width: 10rem"
                >
                    <template #body="{ data }">
                        <div
                            v-if="
                                getDocumentIndicator(
                                    'Guide d\'organisation',
                                    data
                                )
                            "
                            class="centered-content"
                        >
                            <Button
                                icon="pi pi-eye"
                                class="p-button-text p-button-plain"
                                @click="
                                    viewDocument(
                                        getDocumentIndicator(
                                            'Guide d\'organisation',
                                            data
                                        ).documentId
                                    )
                                "
                                v-tooltip="'Visionner'"
                            />
                            <span class="indicator-label">{{
                                getDocumentIndicator(
                                    "Guide d'organisation",
                                    data
                                ).label
                            }}</span>
                            <i
                                :class="
                                    getDocumentIndicator(
                                        'Guide d\'organisation',
                                        data
                                    ).icon
                                "
                                :style="{
                                    color: getDocumentIndicator(
                                        'Guide d\'organisation',
                                        data
                                    ).color,
                                }"
                            />
                        </div>
                        <div v-else class="centered-content">
                            Aucun document
                        </div>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-model="filterModel.value"
                            :options="documentStatusOptions"
                            optionValue="value"
                            placeholder="Statut document"
                            class="p-column-filter"
                            @change="filterCallback()"
                        >
                            <template #option="{ option }">
                                <div class="flex align-items-center gap-2">
                                    <i
                                        :class="option.icon"
                                        :style="{ color: option.color }"
                                    ></i>
                                    <span>{{ option.label }}</span>
                                </div>
                            </template>
                        </Dropdown>
                    </template>
                </Column>
                <Column
                    field="guide_materiels"
                    header="Guide matériels"
                    style="min-width: 10rem"
                >
                    <template #body="{ data }">
                        <div
                            v-if="getDocumentIndicator('Guide matériels', data)"
                            class="centered-content"
                        >
                            <Button
                                icon="pi pi-eye"
                                class="p-button-text p-button-plain"
                                @click="
                                    viewDocument(
                                        getDocumentIndicator(
                                            'Guide matériels',
                                            data
                                        ).documentId
                                    )
                                "
                                v-tooltip="'Visionner'"
                            />
                            <span class="indicator-label">{{
                                getDocumentIndicator('Guide matériels', data)
                                    .label
                            }}</span>
                            <i
                                :class="
                                    getDocumentIndicator(
                                        'Guide matériels',
                                        data
                                    ).icon
                                "
                                :style="{
                                    color: getDocumentIndicator(
                                        'Guide matériels',
                                        data
                                    ).color,
                                }"
                            />
                        </div>
                        <div v-else class="centered-content">
                            Aucun document
                        </div>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-model="filterModel.value"
                            :options="documentStatusOptions"
                            optionValue="value"
                            placeholder="Statut document"
                            class="p-column-filter"
                            @change="filterCallback()"
                        >
                            <template #option="{ option }">
                                <div class="flex align-items-center gap-2">
                                    <i
                                        :class="option.icon"
                                        :style="{ color: option.color }"
                                    ></i>
                                    <span>{{ option.label }}</span>
                                </div>
                            </template>
                        </Dropdown>
                    </template>
                </Column>
                <Column header="Actions" style="min-width: 12rem" frozen>
                    <template #body="{ data, frozenRow, index }">
                        <div class="flex gap-2">
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
                                v-tooltip="'Fixer/Défixer'"
                            />
                            <Button
                                icon="pi pi-file"
                                severity="info"
                                text
                                @click="openDocumentManager(data)"
                                v-tooltip="'Gérer les documents'"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>
            <Dialog
                v-model:visible="documentManagerVisible"
                :style="{ width: '80vw', maxWidth: '1200px' }"
                :modal="true"
                :closable="true"
            >
                <DocumentManager
                    v-if="documentManagerVisible && selectedSpecialite"
                    :specialite="selectedSpecialite"
                    @close="documentManagerVisible = false"
                    @refresh="fetchSpecialites"
                />
            </Dialog>
            <Toast />
        </div>
    </div>
</template>

<script>
import { ref, computed } from 'vue';
import { FilterMatchMode } from 'primevue/api';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Dropdown from 'primevue/dropdown';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import DocumentManager from '@/Components/Atfp/Specialites/DocumentManager.vue';
import ExcelJS from 'exceljs';

export default {
    components: {
        Button,
        InputText,
        DataTable,
        Column,
        Tag,
        Dropdown,
        Dialog,
        Toast,
        DocumentManager,
    },
    setup() {
        const toast = useToast();
        const specialites = ref([]);
        return { toast, specialites };
    },
    data() {
        return {
            activeTab: 'tous',
            FixLignes: [],
            selectedSpecialites: [],
            filters: null,
            selectedAnnee: null,
            standardisations: ['جديد', 'مقيس', 'غير مقيس'],
            homologationOptions: ['Homologuée', 'Non Homologuée'],
            loading: true,
            documentManagerVisible: false,
            selectedSpecialite: null,
            secteurs: [],
            sousSecteurs: [],
            annees: [],
            currentYearId: null,
            documentTypes: [
                { nom_fr: "Programme d'étude", nom_ar: 'برنامج التكوين' },
                { nom_fr: "Dossier d'homologation", nom_ar: 'ملف التنظير' },
                { nom_fr: 'Guide pédagogique', nom_ar: 'الدليل البيداغوجي' },
                { nom_fr: "Guide d'évaluation", nom_ar: 'دليل التقييم' },
                { nom_fr: "Guide d'organisation", nom_ar: 'دليل التنظيم' },
                { nom_fr: 'Guide matériels', nom_ar: 'دليل التجهيزات' },
            ],
            documentStatusOptions: [
                {
                    label: 'Actif',
                    value: 'active',
                    icon: 'pi pi-check-circle',
                    color: '#28a745',
                },
                {
                    label: 'Annulé',
                    value: 'cancelled',
                    icon: 'pi pi-ban',
                    color: '#ffc107',
                },
                { label: 'Aucun', value: 'none', icon: '', color: '' },
            ],
        };
    },
    computed: {
        filteredSpecialites() {
            let filtered = this.specialites;
            console.log('Filtered Specialites:', filtered);
            if (this.activeTab === 'مقيس') {
                filtered = filtered.filter(
                    (s) => s.standardisation_ar === 'مقيس'
                );
            } else if (this.activeTab === 'غير مقيس') {
                filtered = filtered.filter(
                    (s) => s.standardisation_ar === 'غير مقيس'
                );
            } else if (this.activeTab === 'جديد') {
                filtered = filtered.filter(
                    (s) => s.standardisation_ar === 'جديد'
                );
            }
            return filtered;
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
    },
    methods: {
        setActiveTab(tab) {
            this.activeTab = tab;
        },
        clearFilter() {
            this.initFilters();
        },
        clearAnneeFilter() {
            this.selectedAnnee = this.currentYearId;
            this.applyAnneeFilter();
        },
        initFilters() {
            this.filters = {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                code: { value: null, matchMode: FilterMatchMode.CONTAINS },
                nom_ar: { value: null, matchMode: FilterMatchMode.CONTAINS },
                nom_fr: { value: null, matchMode: FilterMatchMode.CONTAINS },
                homologation_fr: {
                    value: null,
                    matchMode: FilterMatchMode.EQUALS,
                },
                programme_etude: {
                    value: null,
                    matchMode: FilterMatchMode.EQUALS,
                },
                dossier_homologation: {
                    value: null,
                    matchMode: FilterMatchMode.EQUALS,
                },
                guide_pedagogique: {
                    value: null,
                    matchMode: FilterMatchMode.EQUALS,
                },
                guide_evaluation: {
                    value: null,
                    matchMode: FilterMatchMode.EQUALS,
                },
                guide_organisation: {
                    value: null,
                    matchMode: FilterMatchMode.EQUALS,
                },
                guide_materiels: {
                    value: null,
                    matchMode: FilterMatchMode.EQUALS,
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
        async fetchSpecialites() {
            try {
                console.log('Fetching specialites with params:', {
                    with: 'documents,infos_specialites',
                    annee_formation_id: this.selectedAnnee,
                });
                const response = await axios.get('/api/specialites', {
                    params: {
                        with: 'documents,infos_specialites',
                        annee_formation_id: this.selectedAnnee,
                    },
                });
                console.log('API Response:', response.data);
                this.specialites = response.data.map((specialite) => {
                    console.log(
                        `Specialite ID ${specialite.id} Documents:`,
                        specialite.documents
                    );
                    return {
                        ...specialite,
                        homologation_fr: this.getDynamicField(
                            specialite,
                            'homologation_fr'
                        ),
                        programme_etude: this.getDocumentStatus(
                            "Programme d'étude",
                            specialite
                        ),
                        dossier_homologation: this.getDocumentStatus(
                            "Dossier d'homologation",
                            specialite
                        ),
                        guide_pedagogique: this.getDocumentStatus(
                            'Guide pédagogique',
                            specialite
                        ),
                        guide_evaluation: this.getDocumentStatus(
                            "Guide d'évaluation",
                            specialite
                        ),
                        guide_organisation: this.getDocumentStatus(
                            "Guide d'organisation",
                            specialite
                        ),
                        guide_materiels: this.getDocumentStatus(
                            'Guide matériels',
                            specialite
                        ),
                    };
                });
                console.log('Spécialités chargées:', this.specialites);
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Impossible de charger les spécialités.',
                    life: 3000,
                });
                console.error(
                    'Erreur lors du chargement des spécialités:',
                    error
                );
            }
        },
        async fetchSecteurs() {
            try {
                const response = await axios.get('/api/secteurs');
                this.secteurs = response.data;
                console.log('Secteurs:', this.secteurs);
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
                console.log('Sous-secteurs:', this.sousSecteurs);
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
                console.log('Années:', this.annees);
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
            console.log('Current Year ID:', this.currentYearId);
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
                this.specialites.push(data);
            } else if (this.FixLignes.length < 10) {
                this.specialites = this.specialites.filter(
                    (c, i) => i !== index
                );
                this.FixLignes.push(data);
            }
            this.specialites.sort((val1, val2) => (val1.id < val2.id ? -1 : 1));
        },
        getSeverity(value) {
            switch (value) {
                case 'Homologuée':
                    return 'success';
                case 'Non Homologuée':
                    return 'danger';
                default:
                    return 'secondary';
            }
        },
        getDynamicField(data, field) {
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
            return info[field] !== null && info[field] !== undefined
                ? info[field]
                : '----';
        },
        getDocumentStatusColor(data) {
            if (
                !data.documents ||
                !Array.isArray(data.documents) ||
                data.documents.length === 0
            ) {
                console.log(`No documents for specialite ${data.id}`);
                return '#dc3545'; // Rouge si aucun document
            }

            const hasActiveDocument = data.documents.some(
                (doc) => doc.statut_fr === 'Actif'
            );
            if (hasActiveDocument) {
                console.log(`Active document found for specialite ${data.id}`);
                return '#28a745'; // Vert si au moins un document actif
            }

            const hasCancelledDocument = data.documents.some(
                (doc) => doc.statut_fr === 'Annulé'
            );
            if (hasCancelledDocument) {
                console.log(
                    `Cancelled document found for specialite ${data.id}`
                );
                return '#ffc107'; // Jaune si annulé
            }

            console.log(
                `No active or cancelled documents for specialite ${data.id}`
            );
            return '#dc3545'; // Rouge sinon
        },
        getLatestDocument(type, documents) {
            if (
                !documents ||
                !Array.isArray(documents) ||
                documents.length === 0
            ) {
                console.log(`No documents for type ${type}`);
                return null;
            }

            const typeDocuments = documents.filter((doc) => {
                const isTypeMatch = doc.type_doc_fr === type;
                const isValidStatus =
                    doc.statut_fr === 'Actif' || doc.statut_fr === 'Annulé';
                console.log(
                    `Document ID ${doc.id}: Type=${doc.type_doc_fr}, Status=${doc.statut_fr}, Match=${isTypeMatch && isValidStatus}`
                );
                return isTypeMatch && isValidStatus;
            });

            if (typeDocuments.length === 0) {
                console.log(`No matching documents for type ${type}`);
                return null;
            }

            return typeDocuments.sort((a, b) => {
                if (a.statut_fr === 'Actif' && b.statut_fr !== 'Actif')
                    return -1;
                if (a.statut_fr !== 'Actif' && b.statut_fr === 'Actif')
                    return 1;
                return new Date(b.updated_at) - new Date(a.updated_at);
            })[0];
        },
        getDocumentIndicator(type, data) {
            console.log(
                `Checking indicator for type ${type}, specialite ${data.id}, documents:`,
                data.documents
            );
            if (
                !data.documents ||
                !Array.isArray(data.documents) ||
                data.documents.length === 0
            ) {
                console.log(`No documents available for specialite ${data.id}`);
                return null;
            }

            const latestDocument = this.getLatestDocument(type, data.documents);
            if (!latestDocument) {
                console.log(`No latest document for type ${type}`);
                return null;
            }

            console.log(
                `Latest document for ${type}: ID=${latestDocument.id}, Status=${latestDocument.statut_fr}`
            );
            if (latestDocument.statut_fr === 'Actif') {
                return {
                    label: 'Actif',
                    icon: 'pi pi-check-circle',
                    color: '#28a745',
                    documentId: latestDocument.id,
                };
            }

            if (latestDocument.statut_fr === 'Annulé') {
                return {
                    label: 'Annulé',
                    icon: 'pi pi-ban',
                    color: '#ffc107',
                    documentId: latestDocument.id,
                };
            }

            return null;
        },
        getDocumentStatus(type, data) {
            const indicator = this.getDocumentIndicator(type, data);
            if (!indicator) return 'none';
            if (indicator.color === '#28a745') return 'active';
            if (indicator.color === '#ffc107') return 'cancelled';
            return 'none';
        },
        async viewDocument(documentId) {
            try {
                console.log(`Downloading document ID ${documentId}`);
                const response = await axios.get(
                    `/api/documents/${documentId}/download`,
                    {
                        responseType: 'blob',
                    }
                );
                const url = window.URL.createObjectURL(
                    new Blob([response.data])
                );
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', `document_${documentId}.pdf`);
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                window.URL.revokeObjectURL(url);
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Document téléchargé.',
                    life: 3000,
                });
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Échec du téléchargement du document.',
                    life: 3000,
                });
                console.error('Erreur lors du téléchargement:', error);
            }
        },
        applyAnneeFilter() {
            this.refreshTable();
        },
        openDocumentManager(specialite) {
            if (!specialite || !specialite.id) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Spécialité invalide.',
                    life: 3000,
                });
                return;
            }
            this.selectedSpecialite = specialite;
            this.documentManagerVisible = true;
        },
        async exportXLS() {
            try {
                const data = this.specialites.map((specialite) => {
                    const yearId = this.selectedAnnee || this.currentYearId;
                    const info =
                        specialite.infos_specialites?.find(
                            (info) => info.annee_formation_id === yearId
                        ) || {};
                    return {
                        Code: specialite.code,
                        'Nom (FR)': specialite.nom_fr,
                        'Nom (AR)': specialite.nom_ar,
                        'Secteur (AR)': this.getSecteurNomAr(
                            specialite.secteur_id
                        ),
                        'Sous-Secteur (AR)': this.getSousSecteurNomAr(
                            specialite.sous_secteur_id
                        ),
                        Standardisation: specialite.standardisation_ar,
                        Statut: specialite.statut_ar,
                        Homologation: info.homologation_fr || '----',
                        "Programme d'étude":
                            this.getDocumentIndicator(
                                "Programme d'étude",
                                specialite
                            )?.label || '',
                        "Dossier d'homologation":
                            this.getDocumentIndicator(
                                "Dossier d'homologation",
                                specialite
                            )?.label || '',
                        'Guide pédagogique':
                            this.getDocumentIndicator(
                                'Guide pédagogique',
                                specialite
                            )?.label || '',
                        "Guide d'évaluation":
                            this.getDocumentIndicator(
                                "Guide d'évaluation",
                                specialite
                            )?.label || '',
                        "Guide d'organisation":
                            this.getDocumentIndicator(
                                "Guide d'organisation",
                                specialite
                            )?.label || '',
                        'Guide matériels':
                            this.getDocumentIndicator(
                                'Guide matériels',
                                specialite
                            )?.label || '',
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
                    { header: 'Secteur (AR)', key: 'Secteur (AR)', width: 30 },
                    {
                        header: 'Sous-Secteur (AR)',
                        key: 'Sous-Secteur (AR)',
                        width: 30,
                    },
                    {
                        header: 'Standardisation',
                        key: 'Standardisation',
                        width: 15,
                    },
                    { header: 'Statut', key: 'Statut', width: 15 },
                    { header: 'Homologation', key: 'Homologation', width: 15 },
                    {
                        header: "Programme d'étude",
                        key: "Programme d'étude",
                        width: 15,
                    },
                    {
                        header: "Dossier d'homologation",
                        key: "Dossier d'homologation",
                        width: 15,
                    },
                    {
                        header: 'Guide pédagogique',
                        key: 'Guide pédagogique',
                        width: 15,
                    },
                    {
                        header: "Guide d'évaluation",
                        key: "Guide d'évaluation",
                        width: 15,
                    },
                    {
                        header: "Guide d'organisation",
                        key: "Guide d'organisation",
                        width: 15,
                    },
                    {
                        header: 'Guide matériels',
                        key: 'Guide matériels',
                        width: 15,
                    },
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
                data.forEach((row) => worksheet.addRow(row));
                worksheet.getRow(1).eachCell((cell) => {
                    cell.font = { bold: true };
                    cell.fill = {
                        type: 'pattern',
                        pattern: 'solid',
                        fgColor: { argb: 'FFCCCCCC' },
                    };
                    cell.alignment = {
                        vertical: 'middle',
                        horizontal: 'center',
                    };
                });
                const buffer = await workbook.xlsx.writeBuffer();
                const blob = new Blob([buffer], {
                    type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = `specialites_documents_${new Date().toISOString().split('T')[0]}.xlsx`;
                link.click();
                URL.revokeObjectURL(link.href);
                this.toast.add({
                    severity: 'success',
                    summary: 'Export XLS',
                    detail: "L'exportation des spécialités a été réalisée avec succès.",
                    life: 3000,
                });
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: "Une erreur s'est produite lors de l'export XLS.",
                    life: 3000,
                });
                console.error("Erreur lors de l'export XLS:", error);
            }
        },
    },
};
</script>

<style scoped>
.arabic-normal {
    font-family: 'Arial', sans-serif;
    direction: rtl;
}

.arabic-gras {
    font-family: 'Arial', sans-serif;
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
    gap: 0.5rem;
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

.indicator-label {
    font-size: 0.9rem;
    color: #333;
}
</style>
