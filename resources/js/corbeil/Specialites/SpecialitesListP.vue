<template>
    <div>
        <!-- Header avec filtres -->
        <div class="flex justify-content-between align-items-center mb-3">
            <div class="text-2xl font-bold text-primary">
                Gestion des Secteurs, Sous-Secteurs et Spécialités
            </div>
            <div class="flex gap-2 align-items-center">
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

        <!-- Cards pour "مقيس" -->
        <div
            v-if="activeTab === 'مقيس'"
            class="flex flex-wrap gap-3 mb-4"
            style="max-height: 150px; overflow-y: auto"
        >
            <div
                v-for="secteur in filteredSecteurs"
                :key="secteur.id"
                class="flex-grow-1"
                style="min-width: 250px"
            >
                <Panel>
                    <template #header>
                        <div class="flex align-items-center gap-2">
                            <i class="pi pi-building text-blue-500"></i>
                            <span class="font-bold">{{ secteur.code }}</span>
                        </div>
                    </template>
                    <div class="flex flex-column gap-1">
                        <span>{{ secteur.nom_fr }}</span>
                        <span>{{ secteur.nom_ar }}</span>
                        <div class="flex gap-2">
                            <span class="text-success"
                                >{{
                                    secteur.sous_secteurs.length
                                }}
                                Sous-Secteurs</span
                            >
                            <span class="text-success"
                                >{{
                                    countSpecialitesForSecteur(secteur)
                                }}
                                Spécialités</span
                            >
                        </div>
                    </div>
                </Panel>
            </div>
        </div>

        <!-- Cards pour "غير مقيس" -->
        <div
            v-if="activeTab === 'غير مقيس'"
            class="flex flex-wrap gap-3 mb-4"
            style="max-height: 150px; overflow-y: auto"
        >
            <div
                v-for="secteur in filteredSecteurs"
                :key="secteur.id"
                class="flex-grow-1"
                style="min-width: 250px"
            >
                <Panel>
                    <template #header>
                        <div class="flex align-items-center gap-2">
                            <i class="pi pi-building text-orange-500"></i>
                            <span class="font-bold">{{ secteur.code }}</span>
                        </div>
                    </template>
                    <div class="flex flex-column gap-1">
                        <span>{{ secteur.nom_fr }}</span>
                        <span>{{ secteur.nom_ar }}</span>
                        <span class="text-success"
                            >{{ secteur.specialites.length }} Spécialités</span
                        >
                    </div>
                </Panel>
            </div>
        </div>

        <!-- Table principale (conditionnée par activeTab) -->
        <div v-if="activeTab === 'مقيس' || activeTab === 'غير مقيس'">
            <DataTable
                v-model:expandedRows="expandedRows"
                v-model:filters="filters"
                stripedRows
                :frozenValue="fixedSecteurs"
                :value="filteredSecteurs"
                :rows="10"
                dataKey="id"
                filterDisplay="menu"
                :globalFilterFields="['code', 'nom_fr', 'nom_ar']"
                :loading="loading"
                scrollable
                scrollHeight="850px"
                :pt="{ table: { style: 'min-width: 50rem' } }"
            >
                <template #header>
                    <div class="flex justify-between gap-2">
                        <div class="flex gap-2 align-items-center">
                            <InputText
                                v-model="filters['global'].value"
                                placeholder="Rechercher dans les secteurs"
                                style="width: 250px"
                                @input="onGlobalFilter"
                            />
                            <Button
                                icon="pi pi-times"
                                severity="secondary"
                                @click="
                                    filters['global'].value = null;
                                    onGlobalFilter();
                                "
                            />
                        </div>
                        <Button
                            text
                            icon="pi pi-minus"
                            label="Tout Réduire"
                            @click="collapseAll"
                            class="ml-auto"
                        />
                    </div>
                </template>

                <Column expander style="width: 5rem" />
                <Column
                    field="code"
                    header="Code"
                    sortable
                    style="min-width: 10rem"
                >
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            placeholder="Rechercher par code"
                            @input="filterCallback()"
                        />
                    </template>
                </Column>
                <Column
                    field="nom_fr"
                    header="Nom (FR)"
                    sortable
                    style="min-width: 14rem"
                >
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            placeholder="Rechercher par nom (FR)"
                            @input="filterCallback()"
                        />
                    </template>
                </Column>
                <Column
                    field="nom_ar"
                    header="Nom (AR)"
                    sortable
                    style="min-width: 14rem"
                >
                    <template #body="{ data }">
                        {{ data.nom_ar }}
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            placeholder="Rechercher par nom (AR)"
                            @input="filterCallback()"
                        />
                    </template>
                </Column>
                <Column header="Fixer" style="width: 6rem">
                    <template #body="{ data, frozenRow }">
                        <Button
                            :icon="frozenRow ? 'pi pi-lock-open' : 'pi pi-lock'"
                            severity="info"
                            text
                            @click="toggleFixSecteur(data, frozenRow)"
                        />
                    </template>
                </Column>

                <!-- Expansion conditionnée par activeTab -->
                <template #expansion="slotProps">
                    <!-- Sous-table pour "مقيس" (Sous-secteurs) -->
                    <div v-if="activeTab === 'مقيس'" class="p-3">
                        <div class="p-3 surface-100">
                            <div
                                class="flex justify-content-between align-items-center mb-2"
                            >
                                <h5 style="font-weight: bold">
                                    <span
                                        style="color: rgba(78, 120, 185, 0.99)"
                                        >Sous-Secteurs du Secteur
                                    </span>
                                    <span style="color: #ef4444">{{
                                        slotProps.data.nom_fr
                                    }}</span>
                                </h5>
                                <InputText
                                    v-model="
                                        sousSecteurFilter[slotProps.data.id]
                                    "
                                    placeholder="Rechercher dans les sous-secteurs"
                                    style="width: 250px"
                                    @input="
                                        onSousSecteurFilter(slotProps.data.id)
                                    "
                                />
                            </div>
                            <DataTable
                                v-model:expandedRows="
                                    expandedSousSecteurs[slotProps.data.id]
                                "
                                :value="filteredSousSecteurs(slotProps.data.id)"
                                dataKey="id"
                                @rowExpand="onSousSecteurExpand"
                                @rowCollapse="onSousSecteurCollapse"
                            >
                                <Column expander style="width: 5rem" />
                                <Column
                                    field="code"
                                    header="Code"
                                    sortable
                                    style="min-width: 10rem"
                                />
                                <Column
                                    field="nom_fr"
                                    header="Nom (FR)"
                                    sortable
                                    style="min-width: 14rem"
                                />
                                <Column
                                    field="nom_ar"
                                    header="Nom (AR)"
                                    sortable
                                    style="min-width: 14rem"
                                >
                                    <template #body="{ data }">
                                        {{ data.nom_ar }}
                                    </template>
                                </Column>

                                <!-- Sous-sous-table pour Spécialités (مقيس) -->
                                <template #expansion="slotPropsSub">
                                    <div class="p-3 surface-100">
                                        <div
                                            class="flex justify-content-between align-items-center mb-2"
                                        >
                                            <h5 style="font-weight: bold">
                                                <span style="color: #93c5fd"
                                                    >Spécialités du Sous-Secteur
                                                </span>
                                                <span style="color: #ef4444">{{
                                                    slotPropsSub.data.nom_fr
                                                }}</span>
                                            </h5>
                                            <InputText
                                                v-model="
                                                    specialiteFilter[
                                                        slotPropsSub.data.id
                                                    ]
                                                "
                                                placeholder="Rechercher dans les spécialités"
                                                style="width: 220px"
                                                @input="
                                                    onSpecialiteFilter(
                                                        slotPropsSub.data.id
                                                    )
                                                "
                                            />
                                        </div>
                                        <DataTable
                                            :value="
                                                filteredSpecialites(
                                                    slotPropsSub.data.id
                                                )
                                            "
                                            dataKey="id"
                                        >
                                            <Column
                                                field="code"
                                                header="Code"
                                                sortable
                                                style="min-width: 10rem"
                                            />
                                            <Column
                                                field="nom_fr"
                                                header="Nom (FR)"
                                                sortable
                                                style="min-width: 14rem"
                                            />
                                            <Column
                                                field="nom_ar"
                                                header="Nom (AR)"
                                                sortable
                                                style="min-width: 14rem"
                                            >
                                                <template #body="{ data }">
                                                    {{ data.nom_ar }}
                                                </template>
                                            </Column>
                                            <Column
                                                field="diplome"
                                                header="Diplôme"
                                                sortable
                                                style="min-width: 10rem"
                                            >
                                                <template #body="{ data }">
                                                    <Tag
                                                        :value="data.diplome"
                                                        :severity="
                                                            getSeverity(
                                                                data.diplome
                                                            )
                                                        "
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
                                                    <Tag
                                                        :value="data.statut"
                                                        :severity="
                                                            getSeverity(
                                                                data.statut
                                                            )
                                                        "
                                                    />
                                                </template>
                                            </Column>
                                            <Column
                                                field="date_creation"
                                                header="Date Création"
                                                sortable
                                                style="min-width: 12rem"
                                            >
                                                <template #body="{ data }">
                                                    {{
                                                        formatDate(
                                                            data.date_creation
                                                        )
                                                    }}
                                                </template>
                                            </Column>
                                            <Column
                                                field="date_annulation"
                                                header="Date Annulation"
                                                sortable
                                                style="min-width: 12rem"
                                            >
                                                <template #body="{ data }">
                                                    {{
                                                        formatDate(
                                                            data.date_annulation
                                                        )
                                                    }}
                                                </template>
                                            </Column>
                                            <Column
                                                field="observation"
                                                header="Observation"
                                                style="min-width: 15rem"
                                            />
                                        </DataTable>
                                    </div>
                                </template>
                            </DataTable>
                        </div>
                    </div>
                    <!-- Sous-table pour "غير مقيس" (Spécialités directement) -->
                    <div v-if="activeTab === 'غير مقيس'" class="p-3">
                        <div class="p-3 surface-100">
                            <div
                                class="flex justify-content-between align-items-center mb-2"
                            >
                                <h5>
                                    Spécialités pour {{ slotProps.data.nom_fr }}
                                </h5>
                                <InputText
                                    v-model="
                                        specialiteFilter[slotProps.data.id]
                                    "
                                    placeholder="Rechercher dans les spécialités"
                                    style="width: 250px"
                                    @input="
                                        onSpecialiteFilter(slotProps.data.id)
                                    "
                                />
                            </div>
                            <DataTable
                                :value="filteredSpecialites(slotProps.data.id)"
                                dataKey="id"
                            >
                                <Column
                                    field="code"
                                    header="Code"
                                    sortable
                                    style="min-width: 10rem"
                                />
                                <Column
                                    field="nom_fr"
                                    header="Nom (FR)"
                                    sortable
                                    style="min-width: 14rem"
                                />
                                <Column
                                    field="nom_ar"
                                    header="Nom (AR)"
                                    sortable
                                    style="min-width: 14rem"
                                >
                                    <template #body="{ data }">
                                        {{ data.nom_ar }}
                                    </template>
                                </Column>
                                <Column
                                    field="diplome"
                                    header="Diplôme"
                                    sortable
                                    style="min-width: 10rem"
                                >
                                    <template #body="{ data }">
                                        <Tag
                                            :value="data.diplome"
                                            :severity="
                                                getSeverity(data.diplome)
                                            "
                                        />
                                    </template>
                                </Column>
                                <Column
                                    field="statut"
                                    header="Statut"
                                    sortable
                                    style="min-width: 12rem"
                                />
                                <Column
                                    field="date_creation"
                                    header="Date Création"
                                    sortable
                                    style="min-width: 12rem"
                                >
                                    <template #body="{ data }">
                                        {{ formatDate(data.date_creation) }}
                                    </template>
                                </Column>
                                <Column
                                    field="date_annulation"
                                    header="Date Annulation"
                                    sortable
                                    style="min-width: 12rem"
                                >
                                    <template #body="{ data }">
                                        {{ formatDate(data.date_annulation) }}
                                    </template>
                                </Column>
                                <Column
                                    field="observation"
                                    header="Observation"
                                    style="min-width: 15rem"
                                />
                            </DataTable>
                        </div>
                    </div>
                </template>
            </DataTable>
        </div>

        <!-- Table directe pour "جديد" (Spécialités uniquement) -->
        <div v-if="activeTab === 'جديد'">
            <DataTable
                v-model:filters="filters"
                stripedRows
                :frozenValue="fixedSpecialitesDirect"
                :value="filteredSpecialitesDirect"
                :rows="10"
                dataKey="id"
                filterDisplay="menu"
                :globalFilterFields="[
                    'code',
                    'nom_fr',
                    'nom_ar',
                    'diplome',
                    'statut',
                    'date_creation',
                    'date_annulation',
                    'observation',
                ]"
                :loading="loading"
                scrollable
                scrollHeight="650px"
                :pt="{ table: { style: 'min-width: 50rem' } }"
            >
                <template #header>
                    <div class="flex justify-content-between gap-2">
                        <div class="flex gap-2 align-items-center">
                            <InputText
                                v-model="filters['global'].value"
                                placeholder="Rechercher dans les spécialités"
                                style="width: 250px"
                                @input="onGlobalFilter"
                            />
                            <Button
                                icon="pi pi-times"
                                severity="secondary"
                                @click="
                                    filters['global'].value = null;
                                    onGlobalFilter();
                                "
                            />
                        </div>
                    </div>
                </template>

                <Column
                    field="code"
                    header="Code"
                    sortable
                    style="min-width: 10rem"
                >
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            placeholder="Rechercher par code"
                            @input="filterCallback()"
                        />
                    </template>
                </Column>
                <Column
                    field="nom_fr"
                    header="Nom (FR)"
                    sortable
                    style="min-width: 14rem"
                >
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            placeholder="Rechercher par nom (FR)"
                            @input="filterCallback()"
                        />
                    </template>
                </Column>
                <Column
                    field="nom_ar"
                    header="Nom (AR)"
                    sortable
                    style="min-width: 14rem"
                >
                    <template #body="{ data }">
                        {{ data.nom_ar }}
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            placeholder="Rechercher par nom (AR)"
                            @input="filterCallback()"
                        />
                    </template>
                </Column>
                <Column
                    field="diplome"
                    header="Diplôme"
                    sortable
                    style="min-width: 10rem"
                >
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
                    field="statut"
                    header="Statut"
                    sortable
                    style="min-width: 12rem"
                >
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-model="filterModel.value"
                            :options="['En vigueur', 'Non validée', 'Annulée']"
                            placeholder="Sélectionner un statut"
                            @change="filterCallback()"
                        />
                    </template>
                </Column>
                <Column
                    field="date_creation"
                    header="Date Création"
                    sortable
                    style="min-width: 12rem"
                >
                    <template #body="{ data }">
                        {{ formatDate(data.date_creation) }}
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            placeholder="Rechercher par date création"
                            @input="filterCallback()"
                        />
                    </template>
                </Column>
                <Column
                    field="date_annulation"
                    header="Date Annulation"
                    sortable
                    style="min-width: 12rem"
                >
                    <template #body="{ data }">
                        {{ formatDate(data.date_annulation) }}
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            placeholder="Rechercher par date annulation"
                            @input="filterCallback()"
                        />
                    </template>
                </Column>
                <Column
                    field="observation"
                    header="Observation"
                    style="min-width: 15rem"
                >
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            placeholder="Rechercher par observation"
                            @input="filterCallback()"
                        />
                    </template>
                </Column>
                <Column header="Fixer" style="width: 6rem">
                    <template #body="{ data, frozenRow }">
                        <Button
                            :icon="frozenRow ? 'pi pi-lock-open' : 'pi pi-lock'"
                            severity="info"
                            text
                            @click="toggleFixSpecialiteDirect(data, frozenRow)"
                        />
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- Toast -->
        <Toast />
    </div>
</template>

<script>
import axios from 'axios';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import Dropdown from 'primevue/dropdown';
import Panel from 'primevue/panel';
import Toast from 'primevue/toast';
import { FilterMatchMode } from 'primevue/api';
import { useToast } from 'primevue/usetoast';

export default {
    components: {
        DataTable,
        Column,
        Tag,
        InputText,
        Button,
        Dropdown,
        Panel,
        Toast,
    },
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            secteurs: [],
            sousSecteurs: [],
            specialites: [],
            expandedRows: [],
            expandedSousSecteurs: {},
            filters: {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                code: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                nom_fr: { value: null, matchMode: FilterMatchMode.CONTAINS },
                nom_ar: { value: null, matchMode: FilterMatchMode.CONTAINS },
                diplome: { value: null, matchMode: FilterMatchMode.EQUALS },
                statut: { value: null, matchMode: FilterMatchMode.EQUALS },
                date_creation: {
                    value: null,
                    matchMode: FilterMatchMode.CONTAINS,
                },
                date_annulation: {
                    value: null,
                    matchMode: FilterMatchMode.CONTAINS,
                },
                observation: {
                    value: null,
                    matchMode: FilterMatchMode.CONTAINS,
                },
            },
            sousSecteurFilter: {},
            specialiteFilter: {},
            activeTab: 'مقيس',
            loading: true,
            fixedSecteurs: [],
            fixedSousSecteurs: {},
            fixedSpecialites: {},
            fixedSpecialitesDirect: [],
        };
    },
    computed: {
        filteredSecteurs() {
            return this.secteurs.filter((s) => s.type === this.activeTab);
        },
        filteredSpecialitesDirect() {
            return this.specialites.filter((s) => s.type === 'جديد');
        },
    },
    methods: {
        async fetchData() {
            this.loading = true;
            try {
                const [
                    secteursResponse,
                    sousSecteursResponse,
                    specialitesResponse,
                ] = await Promise.all([
                    axios.get('/api/secteurs'),
                    axios.get('/api/sous-secteurs'),
                    axios.get('/api/specialites'),
                ]);
                this.secteurs = secteursResponse.data;
                this.sousSecteurs = sousSecteursResponse.data;
                this.specialites = specialitesResponse.data;

                this.secteurs.forEach((secteur) => {
                    secteur.sous_secteurs = this.sousSecteurs.filter(
                        (ss) => ss.secteur_id === secteur.id
                    );
                    secteur.specialites = this.specialites.filter(
                        (sp) =>
                            sp.secteur_id === secteur.id &&
                            sp.type === 'غير مقيس'
                    );
                    secteur.sous_secteurs.forEach((ss) => {
                        ss.specialites = this.specialites.filter(
                            (sp) =>
                                sp.sous_secteur_id === ss.id &&
                                sp.type === 'مقيس'
                        );
                    });
                });
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Erreur lors du chargement des données.',
                    life: 3000,
                });
            } finally {
                this.loading = false;
            }
        },

        filteredSousSecteurs(secteurId) {
            let result = this.sousSecteurs.filter(
                (ss) => ss.secteur_id === secteurId
            );
            if (this.sousSecteurFilter[secteurId]) {
                const filterLower =
                    this.sousSecteurFilter[secteurId].toLowerCase();
                result = result.filter(
                    (ss) =>
                        ss.code?.toLowerCase().includes(filterLower) ||
                        ss.nom_fr?.toLowerCase().includes(filterLower) ||
                        ss.nom_ar?.toLowerCase().includes(filterLower)
                );
            }
            return result;
        },

        filteredSpecialites(parentId) {
            let result = [];
            if (this.activeTab === 'مقيس') {
                result = this.specialites.filter(
                    (sp) =>
                        sp.sous_secteur_id === parentId && sp.type === 'مقيس'
                );
            } else if (this.activeTab === 'غير مقيس') {
                result = this.specialites.filter(
                    (sp) => sp.secteur_id === parentId && sp.type === 'غير مقيس'
                );
            }
            if (this.specialiteFilter[parentId]) {
                const filterLower =
                    this.specialiteFilter[parentId].toLowerCase();
                result = result.filter(
                    (sp) =>
                        sp.code?.toLowerCase().includes(filterLower) ||
                        sp.nom_fr?.toLowerCase().includes(filterLower) ||
                        sp.nom_ar?.toLowerCase().includes(filterLower) ||
                        sp.diplome?.toLowerCase().includes(filterLower) ||
                        sp.statut?.toLowerCase().includes(filterLower) ||
                        sp.date_creation
                            ?.toString()
                            .toLowerCase()
                            .includes(filterLower) ||
                        sp.date_annulation
                            ?.toString()
                            .toLowerCase()
                            .includes(filterLower) ||
                        sp.observation?.toLowerCase().includes(filterLower)
                );
            }
            return result;
        },

        countSpecialitesForSecteur(secteur) {
            return secteur.sous_secteurs.reduce(
                (total, ss) =>
                    total + (ss.specialites ? ss.specialites.length : 0),
                0
            );
        },

        setActiveTab(tab) {
            this.activeTab = tab;
            this.expandedRows = [];
            this.expandedSousSecteurs = {};
            this.filters.global.value = null;
            this.sousSecteurFilter = {};
            this.specialiteFilter = {};
        },

        toggleFixSecteur(data, frozen) {
            if (frozen) {
                this.fixedSecteurs = this.fixedSecteurs.filter(
                    (s) => s.id !== data.id
                );
            } else {
                if (this.fixedSecteurs.length < 5) {
                    this.fixedSecteurs.push(data);
                } else {
                    this.toast.add({
                        severity: 'warn',
                        summary: 'Limite atteinte',
                        detail: 'Maximum de lignes fixes atteint.',
                        life: 3000,
                    });
                }
            }
        },

        toggleFixSpecialiteDirect(data, frozen) {
            if (frozen) {
                this.fixedSpecialitesDirect =
                    this.fixedSpecialitesDirect.filter(
                        (sp) => sp.id !== data.id
                    );
            } else {
                if (this.fixedSpecialitesDirect.length < 5) {
                    this.fixedSpecialitesDirect.push(data);
                } else {
                    this.toast.add({
                        severity: 'warn',
                        summary: 'Limite atteinte',
                        detail: 'Maximum de lignes fixes atteint.',
                        life: 3000,
                    });
                }
            }
        },

        collapseAll() {
            this.expandedRows = [];
            this.expandedSousSecteurs = {};
        },
        onRowExpand() {},
        onRowCollapse() {},
        onSousSecteurExpand() {},
        onSousSecteurCollapse() {},
        onGlobalFilter() {
            this.$forceUpdate();
        },
        onSousSecteurFilter() {
            this.$forceUpdate();
        },
        onSpecialiteFilter() {
            this.$forceUpdate();
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
                'En vigueur': 'warning',
                'Non validée': 'success',
                Annulée: 'danger',
            };
            return severityMap[value] || 'secondary';
        },
        formatDate(date) {
            if (!date) return ''; // Retourne une chaîne vide si la date est null
            return date.split('T')[0]; // Extrait la partie avant 'T', soit "2025-03-19"
        },
    },
    mounted() {
        this.fetchData();
    },
};
</script>
