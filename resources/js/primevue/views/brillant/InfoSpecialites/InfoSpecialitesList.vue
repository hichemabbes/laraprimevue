<template>
    <div class="card">
        <TabView>
            <TabPanel header="Vue d'ensemble Spesialites">
                <!-- Start Table -->
                <!-- Tabs pour filtrer les spécialités -->
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
                        class="arabic-normal"
                        @click="setActiveTab('جديد')"
                    />

                    <!-- Filtre par année -->
                    <span class="mx-2"></span>
                    <Dropdown
                        v-model="selectedAnnee"
                        :options="annees"
                        optionLabel="intitule"
                        optionValue="id"
                        placeholder="Sélectionner une année"
                        class="mr-2"
                        @change="filterByAnnee"
                    />
                </div>

                <DataTable
                    v-model:filters="filters"
                    showGridlines
                    stripedRows
                    v-model:selection="selectedSpecialites"
                    :frozenValue="FixLignes"
                    size="small"
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
                    <!-- Header Section -->
                    <template #header>
                        <div
                            class="flex justify-content-between mb-2 align-items-center"
                        >
                            <!-- Section gauche -->
                            <div class="flex align-items-center">
                                <!-- Bouton pour ouvrir la sidebar -->
                                <Button
                                    icon="pi pi-plus"
                                    severity="success"
                                    size="small"
                                    class="mr-2"
                                    @click="openForm"
                                />
                                <!-- Champ de recherche -->
                                <span class="p-input-icon-left mr-2">
                                    <i class="pi pi-search" />
                                    <InputText
                                        v-model="filters['global'].value"
                                        size="small"
                                        placeholder="Recherche..."
                                        style="width: 100%"
                                    />
                                </span>
                                <!-- Bouton Effacer avec icône "X" -->
                                <Button
                                    type="button"
                                    icon="pi pi-times"
                                    size="small"
                                    class="mr-2"
                                    severity="danger"
                                    outlined
                                    @click="clearFilter"
                                />
                                <!-- Bouton Figer avec Dropdown -->
                                <Button
                                    type="button"
                                    icon="pi pi-lock"
                                    size="small"
                                    class="mr-2"
                                    label="Figer"
                                    @click="toggleFreezeDropdown"
                                />
                                <Dropdown
                                    v-model="selectedColumnToFreeze"
                                    :options="columnOptions"
                                    optionLabel="header"
                                    placeholder="Sélectionner une colonne"
                                    class="mr-2"
                                    v-if="showFreezeDropdown"
                                    @change="freezeColumn"
                                >
                                    <template #option="slotProps">
                                        <div>
                                            <span
                                                v-if="slotProps.option.isFrozen"
                                                class="frozen-indicator"
                                                >✔️</span
                                            >
                                            {{ slotProps.option.header }}
                                        </div>
                                    </template>
                                </Dropdown>
                            </div>
                            <!-- Section droite -->
                            <div class="flex align-items-center">
                                <!-- Bouton Associer -->
                                <Button
                                    icon="pi pi-link"
                                    severity="info"
                                    size="small"
                                    class="mr-2"
                                    @click="openAssociateSecteur"
                                    :disabled="
                                        !selectedSpecialites ||
                                        selectedSpecialites.length === 0
                                    "
                                />
                                <!-- Bouton pour accéder à la page des erreurs d'importation -->
                                <Button
                                    icon="pi pi-file-import"
                                    severity="success"
                                    size="small"
                                    class="mr-2"
                                    label="Import XLS"
                                    @click="openImportPopup"
                                />
                                <!-- Bouton pour afficher les erreurs d'importation -->
                                <Button
                                    v-if="errors.length > 0"
                                    icon="pi pi-exclamation-triangle"
                                    severity="warning"
                                    size="small"
                                    class="mr-2"
                                    label="Erreur Import"
                                    @click="showImportError = true"
                                />
                                <!-- Bouton Export XLS -->
                                <Button
                                    icon="pi pi-file-export"
                                    severity="info"
                                    size="small"
                                    class="mr-2"
                                    label="Export XLS"
                                    @click="exportXLS"
                                />

                                <!-- Bouton Actions -->
                                <SplitButton
                                    label="Actions"
                                    icon="pi pi-cog"
                                    size="small"
                                    :model="actions"
                                />
                            </div>
                        </div>
                    </template>
                    <!-- Empty Template -->
                    <template #empty>
                        <div v-if="!loading">Aucune spécialité trouvée.</div>
                        <div v-else>Chargement en cours...</div>
                    </template>
                    <!-- Columns -->
                    <Column
                        selectionMode="multiple"
                        headerStyle="width: 3rem"
                        frozen
                        class="font-bold"
                    ></Column>

                    <!-- Colonne Code -->
                    <Column
                        field="code"
                        header="Code"
                        sortable
                        style="min-width: 10rem"
                    >
                        <template #body="{ data }">
                            {{ data.code }}
                        </template>
                    </Column>

                    <!-- Colonne Nom Secteur (AR) -->
                    <Column
                        field="nom_secteur_ar"
                        header="Secteur"
                        sortable
                        style="min-width: 14rem"
                    >
                        <template #body="{ data }">
                            <div class="arabic-normal">
                                {{ getSecteurNomAr(data.secteur_id) }}
                            </div>
                        </template>
                    </Column>

                    <!-- Colonne Nom Sous-Secteur (AR) -->
                    <Column
                        field="nom_sous_secteur_ar"
                        header="Sous Secteur"
                        sortable
                        style="min-width: 12rem"
                    >
                        <template #body="{ data }">
                            <div class="arabic-normal">
                                {{ getSousSecteurNomAr(data.sous_secteur_id) }}
                            </div>
                        </template>
                    </Column>

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

                    <!-- Colonne Nom (FR) -->
                    <Column
                        field="nom_fr"
                        header="Nom (FR)"
                        sortable
                        style="min-width: 14rem"
                    >
                        <template #body="{ data }">
                            <div class="arabic-normal">{{ data.nom_fr }}</div>
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
                                <!-- Ajout de la classe arabic-text -->
                                <!-- Utilisez la prop severity si disponible -->
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

                    <!-- Colonnes dynamiques -->
                    <Column
                        field="homologation"
                        header="Homologation"
                        sortable
                        style="min-width: 12rem"
                    >
                        <template #body="{ data }">
                            {{ getDynamicField(data, 'homologation') }}
                        </template>
                    </Column>
                    <Column
                        field="heures_techniques"
                        header="Heures Techniques"
                        sortable
                        style="min-width: 12rem"
                    >
                        <template #body="{ data }">
                            {{ getDynamicField(data, 'heures_techniques') }}
                        </template>
                    </Column>
                    <Column
                        field="heures_generaux"
                        header="Heures Généraux"
                        sortable
                        style="min-width: 12rem"
                    >
                        <template #body="{ data }">
                            {{ getDynamicField(data, 'heures_generaux') }}
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
                            {{ getDynamicField(data, 'duree_formation') }}
                        </template>
                    </Column>
                    <Column
                        field="observation"
                        header="Observation"
                        sortable
                        style="min-width: 12rem"
                    >
                        <template #body="{ data }">
                            {{ getDynamicField(data, 'observation') }}
                        </template>
                    </Column>

                    <Column header="Documentations" style="min-width: 14rem">
                        <template #body="{ data }">
                            <SplitButton
                                icon="pi pi-file"
                                :model="documentationMenu(data)"
                                severity="secondary"
                                class="p-button-text"
                            />
                        </template>
                    </Column>

                    <!-- Colonne Actions -->
                    <Column header="Actions" style="min-width: 14rem">
                        <template #body="{ data, frozenRow, index }">
                            <Button
                                type="button"
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
                                icon="pi pi-pencil"
                                class="p-button-rounded p-button-text p-button-success mr-2"
                                @click="editSpecialite(data)"
                            />
                            <Button
                                icon="pi pi-trash"
                                class="p-button-rounded p-button-text p-button-danger"
                                @click="confirmDeleteSpecialite(data)"
                            />
                        </template>
                    </Column>
                </DataTable>
                <!-- End Table -->
            </TabPanel>
            <TabPanel header="Spécialités">
                <!-- Start Table -->
                <!-- Tabs pour filtrer les spécialités -->
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
                        class="arabic-normal"
                        @click="setActiveTab('جديد')"
                    />
                </div>

                <DataTable
                    v-model:filters="filters"
                    showGridlines
                    stripedRows
                    v-model:selection="selectedSpecialites"
                    :frozenValue="FixLignes"
                    size="small"
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
                    <!-- Header Section -->
                    <template #header>
                        <div
                            class="flex justify-content-between mb-2 align-items-center"
                        >
                            <!-- Section gauche -->
                            <div class="flex align-items-center">
                                <!-- Bouton pour ouvrir la sidebar -->
                                <Button
                                    icon="pi pi-plus"
                                    severity="success"
                                    size="small"
                                    class="mr-2"
                                    @click="openForm"
                                />
                                <!-- Champ de recherche -->
                                <span class="p-input-icon-left mr-2">
                                    <i class="pi pi-search" />
                                    <InputText
                                        v-model="filters['global'].value"
                                        size="small"
                                        placeholder="Recherche..."
                                        style="width: 100%"
                                    />
                                </span>
                                <!-- Bouton Effacer avec icône "X" -->
                                <Button
                                    type="button"
                                    icon="pi pi-times"
                                    size="small"
                                    class="mr-2"
                                    severity="danger"
                                    outlined
                                    @click="clearFilter"
                                />
                                <!-- Bouton Figer avec Dropdown -->
                                <Button
                                    type="button"
                                    icon="pi pi-lock"
                                    size="small"
                                    class="mr-2"
                                    label="Figer"
                                    @click="toggleFreezeDropdown"
                                />
                                <Dropdown
                                    v-model="selectedColumnToFreeze"
                                    :options="columnOptions"
                                    optionLabel="header"
                                    placeholder="Sélectionner une colonne"
                                    class="mr-2"
                                    v-if="showFreezeDropdown"
                                    @change="freezeColumn"
                                >
                                    <template #option="slotProps">
                                        <div>
                                            <span
                                                v-if="slotProps.option.isFrozen"
                                                class="frozen-indicator"
                                                >✔️</span
                                            >
                                            {{ slotProps.option.header }}
                                        </div>
                                    </template>
                                </Dropdown>
                            </div>
                            <!-- Section droite -->
                            <div class="flex align-items-center">
                                <!-- Bouton Associer -->
                                <Button
                                    icon="pi pi-link"
                                    severity="info"
                                    size="small"
                                    class="mr-2"
                                    @click="openAssociateSecteur"
                                    :disabled="
                                        !selectedSpecialites ||
                                        selectedSpecialites.length === 0
                                    "
                                />
                                <!-- Bouton pour accéder à la page des erreurs d'importation -->
                                <Button
                                    icon="pi pi-file-import"
                                    severity="success"
                                    size="small"
                                    class="mr-2"
                                    label="Import XLS"
                                    @click="openImportPopup"
                                />
                                <!-- Bouton pour afficher les erreurs d'importation -->
                                <Button
                                    v-if="errors.length > 0"
                                    icon="pi pi-exclamation-triangle"
                                    severity="warning"
                                    size="small"
                                    class="mr-2"
                                    label="Erreur Import"
                                    @click="showImportError = true"
                                />
                                <!-- Bouton Export XLS -->
                                <Button
                                    icon="pi pi-file-export"
                                    severity="info"
                                    size="small"
                                    class="mr-2"
                                    label="Export XLS"
                                    @click="exportXLS"
                                />

                                <!-- Bouton Actions -->
                                <SplitButton
                                    label="Actions"
                                    icon="pi pi-cog"
                                    size="small"
                                    :model="actions"
                                />
                            </div>
                        </div>
                    </template>
                    <!-- Empty Template -->
                    <template #empty>
                        <div v-if="!loading">Aucune spécialité trouvée.</div>
                        <div v-else>Chargement en cours...</div>
                    </template>
                    <!-- Columns -->
                    <Column
                        selectionMode="multiple"
                        headerStyle="width: 3rem"
                        frozen
                        class="font-bold"
                    ></Column>

                    <!-- Colonne Code -->
                    <Column
                        field="code"
                        header="Code"
                        sortable
                        style="min-width: 10rem"
                    >
                        <template #body="{ data }">
                            {{ data.code }}
                        </template>
                    </Column>

                    <!-- Colonne Nom Secteur (AR) -->
                    <Column
                        field="nom_secteur_ar"
                        header="Secteur"
                        sortable
                        style="min-width: 14rem"
                    >
                        <template #body="{ data }">
                            <div class="arabic-normal">
                                {{ getSecteurNomAr(data.secteur_id) }}
                            </div>
                        </template>
                    </Column>

                    <!-- Colonne Nom Sous-Secteur (AR) -->
                    <Column
                        field="nom_sous_secteur_ar"
                        header="Sous Secteur"
                        sortable
                        style="min-width: 12rem"
                    >
                        <template #body="{ data }">
                            <div class="arabic-normal">
                                {{ getSousSecteurNomAr(data.sous_secteur_id) }}
                            </div>
                        </template>
                    </Column>

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

                    <!-- Colonne Nom (FR) -->
                    <Column
                        field="nom_fr"
                        header="Nom (FR)"
                        sortable
                        style="min-width: 14rem"
                    >
                        <template #body="{ data }">
                            <div class="arabic-normal">{{ data.nom_fr }}</div>
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
                                <!-- Ajout de la classe arabic-text -->
                                <!-- Utilisez la prop severity si disponible -->
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

                    <!-- Colonne Actions -->
                    <Column header="Actions" style="min-width: 14rem">
                        <template #body="{ data, frozenRow, index }">
                            <Button
                                type="button"
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
                                icon="pi pi-pencil"
                                class="p-button-rounded p-button-text p-button-success mr-2"
                                @click="editSpecialite(data)"
                            />
                            <Button
                                icon="pi pi-trash"
                                class="p-button-rounded p-button-text p-button-danger"
                                @click="confirmDeleteSpecialite(data)"
                            />
                        </template>
                    </Column>
                </DataTable>
                <!-- End Table -->
            </TabPanel>
            <TabPanel header="Informations Spécialités">
                <Infos />
            </TabPanel>
            <TabPanel header="Programmes Spécialités">
                <Prog />
            </TabPanel>
            <TabPanel header="Documentations Spécialités">
                <p class="m-0">
                    At vero eos et accusamus et iusto odio dignissimos ducimus
                    qui blanditiis praesentium voluptatum deleniti atque
                    corrupti quos dolores et quas molestias excepturi sint
                    occaecati cupiditate non provident, similique sunt in culpa
                    qui officia deserunt mollitia animi, id est laborum et
                    dolorum fuga. Et harum quidem rerum facilis est et expedita
                    distinctio. Nam libero tempore, cum soluta nobis est
                    eligendi optio cumque nihil impedit quo minus.
                </p>
            </TabPanel>
        </TabView>
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
import SplitButton from 'primevue/splitbutton';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import Infos from '@/primevue/views/brillant/InfosSpecialites/InfosSpecialitesList.vue';
import Form from '@/corbeil/SpecialitesForm.vue';
import Prog from '@/primevue/views/brillant/ProgramesSpecialites/ProgSpecialitesList.vue';

import DocumentPopup from '@/corbeil/Documentations/DocumentPopup.vue';
import ModulePopup from '@/corbeil/ProgramesSpecialites/ModuleForm.vue';
import ImportError from '@/corbeil/Specialites/SpecialitesImportError.vue';
import ExcelJS from 'exceljs';
import Calendar from 'primevue/calendar';
import Badge from '@/Pages/Badge.vue';

export default {
    components: {
        Badge,
        Button,
        InputText,
        DataTable,
        Column,
        Tag,
        Dropdown,
        Calendar,
        SplitButton,
        Dialog,
        Toast,
        Form,
        ImportError,
        Infos,
        Prog,
        DocumentPopup,
        ModulePopup,
    },
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            activeTab: 'مقيس', // Onglet actif par défaut
            FixLignes: [], // Liste des lignes fixes
            specialites: [], // Liste des spécialités
            selectedSpecialites: [], // Spécialités sélectionnées (tableau)
            filters: null, // Filtres appliqués sur les données
            showFreezeDropdown: false,
            selectedSpecialite: null,
            selectedAnnee: null,
            documentPopupVisible: false,
            modulePopupVisible: false,
            documents: [],
            modules: [],

            types: ['جديد', 'مقيس', 'غير مقيس'], // Types de spécialités
            diplomes: [
                'شهادة مؤهل تقني سامي',
                'شهادة مؤهل تقني مهني',
                'شهادة الكفاءة المهنية',
                'شهادة مهارة',
                'شهادة إنهاء التدريب',
            ], // Diplômes disponibles
            statuts: ['معتمد', 'غير معتمد', 'ملغى'], // Statuts des spécialités
            actions: [
                {
                    label: 'Modifier',
                    icon: 'pi pi-pencil',
                    command: () => this.editSelectedSpecialite(),
                },
                {
                    label: 'Supprimer',
                    icon: 'pi pi-trash',
                    command: () => this.deleteSelectedSpecialites(),
                },
            ],
            formVisible: false, // Visibilité du formulaire principal
            deleteFormVisible: false, // Visibilité du formulaire de suppression
            associateSecteurFormVisible: false, // Visibilité du formulaire d'association de secteur
            specialiteToEdit: null, // Spécialité en cours d'édition
            specialiteToDelete: null, // Spécialité en cours de suppression
            loading: true, // Indicateur de chargement
            frozenColumns: [], // Colonnes gelées dans le tableau
            secteurs: [], // Liste des secteurs
            sousSecteurs: [], // Liste des sous-secteurs
            selectedSecteur: null, // Secteur sélectionné
            errors: [], // Tableau des erreurs générales
            showImportError: false, // Affichage d'une erreur d'importation
            importPopupVisible: false, // Affichage du popup d'importation
            annees: [], // Liste des années
            documentToEdit: null,
        };
    },
    created() {
        this.initFilters();
        this.fetchSpecialites();
        this.fetchSecteurs();
        this.fetchSousSecteurs();
        this.fetchAnnees();
    },
    computed: {
        columnOptions() {
            return [
                {
                    field: 'code',
                    header: 'Code',
                    isFrozen: this.frozenColumns.includes('code'),
                },
                {
                    field: 'nom_secteur_ar',
                    header: 'Secteur',
                    isFrozen: this.frozenColumns.includes('nom_secteur_ar'),
                },
                {
                    field: 'nom_sous_secteur_ar',
                    header: 'Sous Secteur',
                    isFrozen: this.frozenColumns.includes(
                        'nom_sous_secteur_ar'
                    ),
                },
                ...this.columns.map((col) => ({
                    field: col.field,
                    header: col.header,
                    isFrozen: this.frozenColumns.includes(col.field),
                })),
            ];
        },
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
        setActiveTab(tab) {
            this.activeTab = tab;
        },
        clearFilter() {
            this.initFilters();
        },
        openForm() {
            this.specialiteToEdit = null;
            this.formVisible = true;
        },
        closeForm() {
            this.formVisible = false;
            this.specialiteToEdit = null;
        },
        initFilters() {
            this.filters = {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                id: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
                code: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
                nom_secteur_ar: {
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
                statut: {
                    operator: FilterOperator.OR,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.EQUALS },
                    ],
                },
            };
        },
        async refreshTable() {
            this.loading = true;
            try {
                const response = await axios.get('/api/specialites');
                this.specialites = response.data;
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
        async handleSaveSpecialite(newSpecialite) {
            try {
                if (!newSpecialite || Object.keys(newSpecialite).length === 0) {
                    console.error(
                        'Les données de la spécialité sont invalides ou manquantes.'
                    );
                    return;
                }

                const response = await axios.post(
                    '/api/specialites',
                    newSpecialite
                );

                if (response.status === 200 || response.status === 201) {
                    this.specialites.push(response.data); // Ajoute la nouvelle spécialité à la liste
                    this.formVisible = false; // Ferme le formulaire
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'La spécialité a été enregistrée avec succès.',
                        life: 3000,
                    });
                } else {
                    console.error(
                        "Échec de l'enregistrement de la spécialité :",
                        response.statusText
                    );
                }
            } catch (error) {
                if (error.response) {
                    console.error(
                        "Erreur API lors de l'enregistrement :",
                        error.response.data.message || error.response.statusText
                    );
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail:
                            error.response.data.message ||
                            "Erreur lors de l'enregistrement de la spécialité.",
                        life: 3000,
                    });
                } else {
                    console.error(
                        "Une erreur est survenue lors de l'enregistrement de la spécialité :",
                        error.message
                    );
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: "Une erreur est survenue lors de l'enregistrement de la spécialité.",
                        life: 3000,
                    });
                }
            }
        },
        editSpecialite(specialite) {
            this.specialiteToEdit = specialite;
            this.formVisible = true;
        },
        editSelectedSpecialite() {
            if (
                this.selectedSpecialites &&
                this.selectedSpecialites.length === 1
            ) {
                this.editSpecialite(this.selectedSpecialites[0]);
            } else {
                this.toast.add({
                    severity: 'warn',
                    summary: 'Aucune sélection',
                    detail: 'Veuillez sélectionner une spécialité à modifier.',
                    life: 3000,
                });
            }
        },
        async handleUpdateSpecialite(updatedSpecialite) {
            try {
                if (
                    !updatedSpecialite ||
                    Object.keys(updatedSpecialite).length === 0 ||
                    !updatedSpecialite.id
                ) {
                    console.error(
                        'Les données de la spécialité sont invalides ou manquantes.'
                    );
                    return;
                }

                const response = await axios.put(
                    `/api/specialites/${updatedSpecialite.id}`,
                    updatedSpecialite
                );

                if (response.status === 200) {
                    const index = this.specialites.findIndex(
                        (s) => s.id === updatedSpecialite.id
                    );
                    if (index !== -1) {
                        this.specialites[index] = response.data; // Met à jour la spécialité dans la liste
                    }
                    this.formVisible = false; // Ferme le formulaire
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'La spécialité a été mise à jour avec succès.',
                        life: 3000,
                    });
                } else {
                    console.error(
                        'Échec de la mise à jour de la spécialité :',
                        response.statusText
                    );
                }
            } catch (error) {
                if (error.response) {
                    console.error(
                        'Erreur API lors de la mise à jour :',
                        error.response.data.message || error.response.statusText
                    );
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail:
                            error.response.data.message ||
                            'Erreur lors de la mise à jour de la spécialité.',
                        life: 3000,
                    });
                } else {
                    console.error(
                        'Une erreur est survenue lors de la mise à jour de la spécialité :',
                        error.message
                    );
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'Une erreur est survenue lors de la mise à jour de la spécialité.',
                        life: 3000,
                    });
                }
            }
        },
        async deleteSpecialite() {
            if (this.specialiteToDelete) {
                try {
                    await axios.delete(
                        `/api/specialites/${this.specialiteToDelete.id}`
                    );
                    this.specialites = this.specialites.filter(
                        (s) => s.id !== this.specialiteToDelete.id
                    );
                    this.toast.add({
                        severity: 'success',
                        summary: 'Supprimé',
                        detail: 'Spécialité supprimée avec succès.',
                        life: 3000,
                    });
                } catch (error) {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'Impossible de supprimer la spécialité.',
                        life: 3000,
                    });
                } finally {
                    this.deleteFormVisible = false;
                    this.specialiteToDelete = null;
                }
            }
        },
        confirmDeleteSpecialite(specialite) {
            this.specialiteToDelete = specialite;
            this.deleteFormVisible = true;
        },
        async deleteSelectedSpecialites() {
            if (
                this.selectedSpecialites &&
                this.selectedSpecialites.length > 0
            ) {
                try {
                    await axios.post('/api/specialites/delete-selected', {
                        ids: this.selectedSpecialites.map((s) => s.id),
                    });
                    this.specialites = this.specialites.filter(
                        (s) => !this.selectedSpecialites.includes(s)
                    );
                    this.toast.add({
                        severity: 'success',
                        summary: 'Supprimé',
                        detail: 'Les spécialités sélectionnées ont été supprimées avec succès.',
                        life: 3000,
                    });
                    this.selectedSpecialites = null;
                } catch (error) {
                    console.error(
                        'Erreur lors de la suppression des spécialités :',
                        error
                    );
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'Échec de la suppression des spécialités sélectionnées.',
                        life: 3000,
                    });
                }
            } else {
                this.toast.add({
                    severity: 'warn',
                    summary: 'Aucune sélection',
                    detail: 'Veuillez sélectionner une ou plusieurs spécialités à supprimer.',
                    life: 3000,
                });
            }
        },
        async fetchSpecialites() {
            this.loading = true;
            try {
                // Ajouter le paramètre intitule_annee à la requête si nécessaire
                const params = {};
                if (this.selectedAnnee) {
                    params.intitule_annee = this.selectedAnnee;
                }

                const response = await axios.get('/api/specialites', {
                    params,
                });
                this.specialites = response.data.map((specialite) => {
                    // Si des informations spécialités existent, prendre la première (ou gérer selon votre logique)
                    const infoSpecialite =
                        specialite.informations_specialites?.[0] || {};

                    // Ajouter les informations supplémentaires de informations_specialites
                    specialite.homologation =
                        infoSpecialite.homologation || null;
                    specialite.heures_techniques =
                        infoSpecialite.heures_techniques || null;
                    specialite.heures_generaux =
                        infoSpecialite.heures_generaux || null;
                    specialite.heures_totales =
                        infoSpecialite.heures_totales || null;
                    specialite.duree_formation =
                        infoSpecialite.duree_formation || null;

                    return specialite;
                });
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
        getSecteurNomAr(secteurId) {
            if (!this.secteurs || !Array.isArray(this.secteurs)) {
                return '----';
            }
            const secteur = this.secteurs.find((s) => s.id === secteurId);
            return secteur ? secteur.nom_ar : '----';
        },
        getSousSecteurNomAr(SousSecteurId) {
            if (!this.sousSecteurs || !Array.isArray(this.sousSecteurs)) {
                return '----';
            }
            const sousSecteur = this.sousSecteurs.find(
                (s) => s.id === SousSecteurId
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
        async associateSpecialitesToSecteur() {
            if (!this.selectedSecteur) {
                this.toast.add({
                    severity: 'warn',
                    summary: 'Aucun secteur sélectionné',
                    detail: 'Veuillez sélectionner un secteur.',
                    life: 3000,
                });
                return;
            }

            try {
                const specialiteIds = this.selectedSpecialites.map((s) => s.id);
                const response = await axios.post(
                    '/api/associate-specialites',
                    {
                        specialite_ids: specialiteIds,
                        secteur_id: this.selectedSecteur,
                    }
                );

                if (response.data.error) {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: response.data.error,
                        life: 3000,
                    });
                } else {
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Spécialités associées avec succès.',
                        life: 3000,
                    });
                    this.associateSecteurFormVisible = false;
                    this.selectedSpecialites = null;
                    this.fetchSpecialites();
                }
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: "Erreur lors de l'association des spécialités.",
                    life: 3000,
                });
            }
        },
        openAssociateSecteur() {
            if (
                this.selectedSpecialites &&
                this.selectedSpecialites.length > 0
            ) {
                this.associateSecteurFormVisible = true;
            } else {
                this.toast.add({
                    severity: 'warn',
                    summary: 'Aucune sélection',
                    detail: 'Veuillez sélectionner au moins une spécialité.',
                    life: 3000,
                });
            }
        },
        toggleLock(data, frozen, index) {
            if (frozen) {
                this.FixLignes = this.FixLignes.filter((c, i) => i !== index);
                this.secteurs.push(data);
            } else {
                this.secteurs = this.secteurs.filter((c, i) => i !== index);
                this.FixLignes.push(data);
            }
            this.secteurs.sort((val1, val2) => (val1.id < val2.id ? -1 : 1));
        },
        getSeverity(type) {
            switch (type) {
                case 'مقيس':
                    return 'success';
                case 'غير مقيس':
                    return 'danger';
                case 'جديد':
                    return 'info';
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
                case 'معتمد':
                    return 'success';
                case 'غير معتمد':
                    return 'warning';
                case 'ملغى':
                    return 'danger';
                default:
                    return null;
            }
        },
        openImportPopup() {
            this.importPopupVisible = true;
        },

        getDynamicField(data, field) {
            if (!this.selectedAnnee) return '----';
            const info = data.informations_specialites.find(
                (info) => info.annee_id === this.selectedAnnee
            );
            return info ? info[field] : '----';
        },
        async filterByAnnee() {
            this.loading = true;
            try {
                const response = await axios.get('/api/specialites', {
                    params: { annee_id: this.selectedAnnee },
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

        documentationMenu(specialite) {
            return [
                {
                    label: 'Documents',
                    icon: 'pi pi-file',
                    command: () => this.openDocumentPopup(specialite),
                },
                {
                    label: 'Programme',
                    icon: 'pi pi-book',
                    command: () => this.openModulePopup(specialite),
                },
            ];
        },

        addDocument() {
            // Logique pour ajouter un document
            this.documentToEdit = null;
            this.documentPopupVisible = true;
        },
        editDocument(document) {
            this.documentToEdit = document;
            this.documentPopupVisible = true;
        },
        async deleteDocument(document) {
            try {
                await axios.delete(`/api/documents/${document.id}`);
                this.documents = this.documents.filter(
                    (d) => d.id !== document.id
                );
                this.toast.add({
                    severity: 'success',
                    summary: 'Supprimé',
                    detail: 'DocumentSpecialite supprimé avec succès.',
                    life: 3000,
                });
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Impossible de supprimer le document.',
                    life: 3000,
                });
            }
        },

        downloadDocument(document) {
            // Logique pour télécharger un document
            window.open(document.Document, '_blank');
        },

        addModule() {
            // Logique pour ajouter un module
            this.moduleToEdit = null;
            this.modulePopupVisible = true;
        },
        editModule(module) {
            this.moduleToEdit = module;
            this.modulePopupVisible = true;
        },
        async deleteModule(module) {
            try {
                await axios.delete(`/api/modules/${module.id}`);
                this.modules = this.modules.filter((m) => m.id !== module.id);
                this.toast.add({
                    severity: 'success',
                    summary: 'Supprimé',
                    detail: 'ModuleGeneral supprimé avec succès.',
                    life: 3000,
                });
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Impossible de supprimer le module.',
                    life: 3000,
                });
            }
        },

        async fetchModules(specialiteId) {
            try {
                const response = await axios.get('/api/modules', {
                    params: {
                        specialite_id: specialiteId,
                        annee_id: this.selectedAnnee,
                    },
                });
                this.modules = response.data;
            } catch (error) {
                console.error(
                    'Erreur lors de la récupération des modules :',
                    error
                );
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Impossible de charger les modules.',
                    life: 3000,
                });
            }
        },
        async fetchDocuments(specialiteId) {
            try {
                const response = await axios.get('/api/documents', {
                    params: {
                        specialite_id: specialiteId,
                        annee_id: this.selectedAnnee,
                    },
                });
                this.documents = response.data;
            } catch (error) {
                console.error(
                    'Erreur lors de la récupération des documents :',
                    error
                );
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Impossible de charger les documents.',
                    life: 3000,
                });
            }
        },
        openModulePopup(specialite) {
            this.selectedSpecialite = specialite;
            this.modulePopupVisible = true;
            this.fetchModules(specialite.id);
        },
        openDocumentPopup(specialite) {
            this.selectedSpecialite = specialite;
            this.documentPopupVisible = true;
            this.fetchDocuments(specialite.id);
        },

        async importSpecialites() {
            this.importPopupVisible = false;
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
                        '/api/specialites/importxls',
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
        async importInformationsSpecialites() {
            this.importPopupVisible = false;
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
        async exportXLS() {
            try {
                const data = this.specialites.map((specialite) => ({
                    Code: specialite.code,
                    'Nom (FR)': specialite.nom_fr,
                    'Nom (AR)': specialite.nom_ar,
                    'Secteur (AR)': this.getSecteurNomAr(specialite.secteur_id),
                    'Sous-Secteur (AR)': this.getSousSecteurNomAr(
                        specialite.sous_secteur_id
                    ),
                    Type: specialite.type,
                    Diplôme: specialite.diplome,
                    Statut: specialite.statut,
                    'Date création': this.formatDate(
                        specialite.date_creation_specialite
                    ),
                    Observation: specialite.observation,
                    'Date annulation': this.formatDate(
                        specialite.date_annulation_specialite
                    ),
                }));

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
                    { header: 'Type', key: 'Type', width: 15 },
                    { header: 'Diplôme', key: 'Diplôme', width: 30 },
                    {
                        header: 'Date création',
                        key: 'Date création',
                        width: 30,
                    },
                    { header: 'Statut', key: 'Statut', width: 15 },
                    { header: 'Observation', key: 'Observation', width: 30 },
                    {
                        header: 'Date annulation',
                        key: 'Date annulation',
                        width: 30,
                    },
                ];

                data.forEach((row) => worksheet.addRow(row));

                const buffer = await workbook.xlsx.writeBuffer();
                const blob = new Blob([buffer], {
                    type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                });

                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'specialites.xlsx';
                link.click();
                URL.revokeObjectURL(link.href);

                this.toast.add({
                    severity: 'success',
                    summary: 'Export XLS',
                    detail: "L'exportation des spécialités a été réalisée avec succès.",
                    life: 3000,
                });
            } catch (error) {
                console.error("Erreur lors de l'export XLS :", error);
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: "Une erreur s'est produite lors de l'export XLS.",
                    life: 3000,
                });
            }
        },
    },
};
</script>

<style scoped>
.card {
    overflow-x: auto;
}
.frozen-indicator {
    color: green;
    margin-right: 5px;
}

.arabic-normal {
    font-family: 'Noto Naskh Arabic', sans-serif; /* Police utilisée */
    font-size: 1em !important; /* Réduction de la taille de police */
    font-weight: normal !important; /* Suppression du gras */
}
.arabic-gras {
    font-family: 'Noto Naskh Arabic', sans-serif; /* Police utilisée */
    font-size: 1em !important; /* Réduction de la taille de police */
}
</style>
