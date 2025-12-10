<template>
    <div>
        <!-- Start Table -->
        <div class="card">
            <DataTable
                v-model:filters="filters"
                showGridlines
                stripedRows
                v-model:selection="selectedSpecialites"
                :frozenValue="FixLignes"
                :value="specialites"
                dataKey="id"
                size="small"
                paginator
                :rows="20"
                filterDisplay="menu"
                :globalFilterFields="[
                    'code',
                    'secteur_id',
                    'sous_secteur_id',
                    'annees_formation_id',
                    'nom_ar',
                    'nom_fr',
                    'type',
                    'homologation',
                    'date_arrete',
                    'num_journal_officiel',
                    'date_journal_officiel',
                    'duree_formation',
                    'diplôme',
                    'heures_technique',
                    'heures_generaux',
                    'heures_total',
                    'date_creation_specialité',
                    'statut',
                    'observation',
                    'date_annulation_specialité',
                ]"
                :loading="loading"
                scrollable
                scrollHeight="650px"
                :pt="{
                    table: { style: 'min-width: 50rem' },
                    bodyrow: ({ props }) => ({
                        class: [
                            {
                                'font-bold': props.frozenRow,
                                'text-blue-500': props.frozenRow,
                            },
                        ],
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
                            <!-- Bouton pour ouvrir la Popup d'ajout -->
                            <Button
                                icon="pi pi-plus"
                                severity="success"
                                size="small"
                                class="mr-2"
                                @click="openAddSpecialitePopup"
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
                        </div>
                        <!-- Section droite -->
                        <div class="flex align-items-center">
                            <!-- Bouton pour accéder à la page des erreurs d'importation -->
                            <Button
                                icon="pi pi-file-import"
                                severity="success"
                                size="small"
                                class="mr-2"
                                label="Import XLS"
                                @click="importXLS"
                            />
                            <!-- Bouton pour afficher les erreurs d'importation -->
                            <Button
                                v-if="errors.length > 0"
                                icon="pi pi-exclamation-triangle"
                                severity="warning"
                                size="small"
                                class="mr-2"
                                label="Erreur Import"
                                @click="showImportErrorPopup = true"
                            />
                            <Button
                                icon="pi pi-file-export"
                                severity="info"
                                size="small"
                                class="mr-2"
                                label="Export XLS"
                                @click="exportXLS"
                            />
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
                    <div v-if="!loading">Aucun specialite trouvé.</div>
                    <div v-else>Chargement en cours...</div>
                </template>
                <!-- Columns -->
                <Column
                    selectionMode="multiple"
                    headerStyle="width: 3rem"
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
                <!-- Colonne nom_ar_secteur -->
                <Column
                    field="nom_ar_secteur"
                    header="Secteur"
                    sortable
                    style="min-width: 12rem"
                >
                    <template #body="{ data }">
                        {{ getNomArSecteur(data.secteur_id) }}
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter"
                            placeholder="Rechercher par Secteur"
                            @input="filterCallback"
                        />
                    </template>
                </Column>
                <!-- Colonne nom_ar_sous_secteur -->
                <Column
                    field="nom_ar_sous_secteur"
                    header="Sous Secteur"
                    sortable
                    style="min-width: 12rem"
                >
                    <template #body="{ data }">
                        {{ getNomArSousSecteur(data.sous_secteur_id) }}
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter"
                            placeholder="Rechercher par Sous Secteur"
                            @input="filterCallback"
                        />
                    </template>
                </Column>
                <!-- Colonne intitule_annees_formation -->
                <Column
                    field="intitule_annees_formation"
                    header="Année de Formation"
                    sortable
                    style="min-width: 12rem"
                >
                    <template #body="{ data }">
                        {{
                            getIntituleAnneesFormation(data.annees_formation_id)
                        }}
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter"
                            placeholder="Rechercher par Année de Formation"
                            @input="filterCallback"
                        />
                    </template>
                </Column>
                <!-- Colonne Nom (AR) -->
                <Column
                    field="nom_ar"
                    header="Nom (AR)"
                    sortable
                    style="min-width: 10rem"
                >
                    <template #body="{ data }">
                        {{ data.nom_ar }}
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter"
                            placeholder="Rechercher par nom_ar"
                            @input="filterCallback"
                        />
                    </template>
                </Column>
                <!-- Colonne Nom (FR) -->
                <Column
                    field="nom_fr"
                    header="Nom (FR)"
                    sortable
                    style="min-width: 10rem"
                >
                    <template #body="{ data }">
                        {{ data.nom_fr }}
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter"
                            placeholder="Rechercher par nom_fr"
                            @input="filterCallback"
                        />
                    </template>
                </Column>
                <!-- Colonne type -->
                <Column
                    header="Type"
                    field="type"
                    sortable
                    :filterMenuStyle="{ width: '14rem' }"
                    style="min-width: 12rem"
                >
                    <template #body="{ data }">
                        <div class="centered-content arabic-normal">
                            <Tag
                                :value="data.type"
                                :severity="getSeverity(data.type)"
                                class="arabic-normal"
                            />
                        </div>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-model="filterModel.value"
                            :options="types"
                            placeholder="Sélectionner un type"
                            class="p-column-filter arabic-text"
                            showClear
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
                <!-- Colonne homologation -->
                <Column
                    header="Homologation"
                    field="homologation"
                    sortable
                    :filterMenuStyle="{ width: '14rem' }"
                    style="min-width: 12rem"
                >
                    <template #body="{ data }">
                        <div class="centered-content arabic-normal">
                            <Tag
                                :value="data.homologation"
                                :severity="getSeverity(data.homologation)"
                                class="arabic-normal"
                            />
                        </div>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-model="filterModel.value"
                            :options="homologations"
                            placeholder="Sélectionner un homologation"
                            class="p-column-filter arabic-text"
                            showClear
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
                <!-- Colonne Date Arrêté -->
                <Column
                    field="date_arrete"
                    header="Date Arrêté"
                    sortable
                    style="min-width: 10rem"
                >
                    <template #body="{ data }">
                        {{ formatDate(data.date_arrete) }}
                    </template>
                </Column>
                <!-- Colonne Numéro Journal Officiel -->
                <Column
                    field="num_journal_officiel"
                    header="Numéro Journal Officiel"
                    sortable
                    style="min-width: 14rem"
                >
                    <template #body="{ data }">
                        {{ data.num_journal_officiel }}
                    </template>
                </Column>
                <!-- Colonne Date Journal Officiel -->
                <Column
                    field="date_journal_officiel"
                    header="Date Journal Officiel"
                    sortable
                    style="min-width: 10rem"
                >
                    <template #body="{ data }">
                        {{ formatDate(data.date_journal_officiel) }}
                    </template>
                </Column>
                <!-- Colonne Durée Formation -->
                <Column
                    field="duree_formation"
                    header="Durée Formation"
                    sortable
                    style="min-width: 10rem"
                >
                    <template #body="{ data }">
                        {{ data.duree_formation }}
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter"
                            placeholder="Rechercher par duree_formation"
                            @input="filterCallback"
                        />
                    </template>
                </Column>
                <!-- Colonne diplôme -->
                <Column
                    header="Diplôme"
                    field="diplôme"
                    sortable
                    :filterMenuStyle="{ width: '14rem' }"
                    style="min-width: 12rem"
                >
                    <template #body="{ data }">
                        <div class="centered-content arabic-normal">
                            <Tag
                                :value="data.diplôme"
                                :severity="getSeverity(data.diplôme)"
                                class="arabic-normal"
                            />
                        </div>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-model="filterModel.value"
                            :options="diplômes"
                            placeholder="Sélectionner un diplôme"
                            class="p-column-filter arabic-text"
                            showClear
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
                <!-- Colonne Heures Techniques -->
                <Column
                    field="heures_technique"
                    header="Heures Techniques"
                    sortable
                    style="min-width: 14rem"
                >
                    <template #body="{ data }">
                        {{ data.heures_technique }}
                    </template>
                </Column>
                <!-- Colonne Heures Généraux -->
                <Column
                    field="heures_generaux"
                    header="Heures Généraux"
                    sortable
                    style="min-width: 14rem"
                >
                    <template #body="{ data }">
                        {{ data.heures_generaux }}
                    </template>
                </Column>
                <!-- Colonne Heures Totales -->
                <Column
                    field="heures_total"
                    header="Heures Totales"
                    sortable
                    style="min-width: 14rem"
                >
                    <template #body="{ data }">
                        {{ data.heures_total }}
                    </template>
                </Column>
                <!-- Colonne Date Création Spécialité -->
                <Column
                    field="date_creation_specialité"
                    header="Date Création Spécialité"
                    sortable
                    style="min-width: 10rem"
                >
                    <template #body="{ data }">
                        {{ formatDate(data.date_creation_specialité) }}
                    </template>
                </Column>
                <!-- Colonne statut -->
                <Column
                    header="Statut"
                    field="statut"
                    sortable
                    :filterMenuStyle="{ width: '14rem' }"
                    style="min-width: 12rem"
                >
                    <template #body="{ data }">
                        <div class="centered-content arabic-normal">
                            <Tag
                                :value="data.statut"
                                :severity="getSeverity(data.statut)"
                                class="arabic-normal"
                            />
                        </div>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-model="filterModel.value"
                            :options="statuts"
                            placeholder="Sélectionner un statut"
                            class="p-column-filter arabic-text"
                            showClear
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
                <!-- Colonne Observation -->
                <Column
                    field="observation"
                    header="Observation"
                    sortable
                    style="min-width: 14rem"
                >
                    <template #body="{ data }">
                        {{ data.observation }}
                    </template>
                </Column>
                <!-- Colonne Date Annulation Spécialité -->
                <Column
                    field="date_annulation_specialité"
                    header="Date Annulation Spécialité"
                    sortable
                    style="min-width: 10rem"
                >
                    <template #body="{ data }">
                        {{ formatDate(data.date_annulation_specialité) }}
                    </template>
                </Column>
                <!-- Colonne Actions -->
                <Column header="Actions" style="min-width: 10rem">
                    <template #body="{ data, frozenRow, index }">
                        <Button
                            type="button"
                            :icon="frozenRow ? 'pi pi-lock-open' : 'pi pi-lock'"
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
        </div>
        <!-- End Table -->
        <!-- Add/Edit Specialite Popup -->
        <AddSpecialite
            :visible="addSpecialitePopupVisible"
            :specialiteToEdit="specialiteToEdit"
            @update:visible="addSpecialitePopupVisible = $event"
            @save="handleSaveSpecialite"
            @update="handleUpdateSpecialite"
            @close="closeAddSpecialitePopup"
        />
        <!-- Popup de confirmation de suppression -->
        <Dialog
            v-model:visible="deletePopupVisible"
            :style="{ width: '450px' }"
            header="Confirmation"
            :modal="true"
        >
            <div class="confirmation-content">
                <i
                    class="pi pi-exclamation-triangle mr-3"
                    style="font-size: 2rem"
                />
                <span v-if="specialiteToDelete">
                    Êtes-vous sûr de vouloir supprimer le specialite
                    <strong>{{ specialiteToDelete.nom_fr }}</strong> ?
                </span>
                <span v-else>
                    Êtes-vous sûr de vouloir supprimer les
                    {{ selectedSpecialites.length }} Specialites sélectionnés ?
                </span>
            </div>
            <template #footer>
                <Button
                    label="Non"
                    icon="pi pi-times"
                    class="p-button-text"
                    @click="deletePopupVisible = false"
                />
                <Button
                    label="Oui"
                    icon="pi pi-check"
                    class="p-button-text"
                    @click="confirmDeleteAction"
                />
            </template>
        </Dialog>
        <!-- Popup pour les erreurs d'importation -->
        <ImportError
            :errors="errors"
            :visible="showImportErrorPopup"
            @update:visible="showImportErrorPopup = $event"
            @line-imported="handleLineImported"
            @close="refreshTable"
        />
        <!-- Toast Component -->
        <Toast />
    </div>
</template>
<style scoped>
.arabic-normal {
    font-family: 'Scheherazade New', sans-serif !important;
    font-size: 1em !important;
    font-weight: normal !important;
}
.arabic-gras {
    font-family: 'Scheherazade New', sans-serif !important;
    font-size: 1em !important;
    font-weight: bold !important;
}
</style>

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
import AddSpecialite from '@/Popup/AddSpecialite.vue';
import * as XLSX from 'xlsx';
import ImportError from '@/Popup/ImportError.vue';
import Calendar from 'primevue/calendar';

export default {
    components: {
        Button,
        InputText,
        DataTable,
        Column,
        Tag,
        Calendar,
        Dropdown,
        SplitButton,
        Dialog,
        Toast,
        AddSpecialite,
        ImportError,
    },
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            FixLignes: [],
            specialites: [],
            selectedSpecialites: null,
            filters: null,
            types: ['Choix1', 'Choix2'],
            homologations: ['Choix1', 'Choix2'],
            diplômes: ['Choix1', 'Choix2'],
            statuts: ['Choix1', 'Choix2'],
            actions: [
                {
                    label: 'Modifier',
                    icon: 'pi pi-pencil',
                    command: () => this.editSelectedSpecialite(),
                },
                {
                    label: 'Supprimer',
                    icon: 'pi pi-trash',
                    command: () => this.confirmDeleteSelectedSpecialites(),
                },
            ],
            addSpecialitePopupVisible: false,
            deletePopupVisible: false,
            specialiteToEdit: null,
            specialiteToDelete: null,
            loading: true,
            secteurs: [],
            sousSecteurs: [],
            anneesFormation: [],
            showImportErrorPopup: false,
            errors: [],
        };
    },
    created() {
        this.initFilters();
        this.fetchSpecialites();
        this.fetchSecteurs();
        this.fetchSousSecteurs();
        this.fetchAnneesFormation();
    },
    methods: {
        clearFilter() {
            this.initFilters();
        },
        getNomArSecteur(SecteurId) {
            if (!this.secteurs || !Array.isArray(this.secteurs)) {
                return 'Non défini';
            }
            const Secteurs = this.secteurs.find((s) => s.id === SecteurId);
            return Secteurs ? Secteurs.nom_ar : 'Non défini';
        },
        async fetchSecteurs() {
            try {
                const response = await axios.get('/api/secteurs');
                console.log("Données reçues de l'API :", response.data);
                this.secteurs = response.data;
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Impossible de charger les .',
                    life: 3000,
                });
            }
        },
        getNomArSousSecteur(SousSecteurId) {
            if (!this.sousSecteurs || !Array.isArray(this.sousSecteurs)) {
                return 'Non défini';
            }
            const SousSecteurs = this.sousSecteurs.find(
                (s) => s.id === SousSecteurId
            );
            return SousSecteurs ? SousSecteurs.nom_ar : 'Non défini';
        },
        async fetchSousSecteurs() {
            try {
                const response = await axios.get('/api/sous_secteurs');
                console.log("Données reçues de l'API :", response.data);
                this.sousSecteurs = response.data;
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Impossible de charger les .',
                    life: 3000,
                });
            }
        },
        getIntituleAnneesFormation(AnneesFormationId) {
            if (!this.anneesFormation || !Array.isArray(this.anneesFormation)) {
                return 'Non défini';
            }
            const AnneesFormation = this.anneesFormation.find(
                (s) => s.id === AnneesFormationId
            );
            return AnneesFormation ? AnneesFormation.intitule : 'Non défini';
        },
        async fetchAnneesFormation() {
            try {
                const response = await axios.get('/api/annees_formation');
                console.log("Données reçues de l'API :", response.data);
                this.anneesFormation = response.data;
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Impossible de charger les .',
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
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
                secteur_id: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
                sous_secteur_id: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
                annees_formation_id: {
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
                nom_fr: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
                type: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
                homologation: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
                duree_formation: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
                diplôme: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
                statut: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
            };
        },

        handleLineImported(importedLine) {
            this.errors = this.errors.filter(
                (err) => err.line !== importedLine.line
            );
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
                    detail: 'Échec du chargement des Specialite.',
                    life: 3000,
                });
            } finally {
                this.loading = false;
            }
        },

        formatDate(date) {
            if (!date) return null;
            if (typeof date === 'string') {
                date = new Date(date);
            }
            if (!(date instanceof Date) || isNaN(date.getTime())) {
                return null;
            }
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        },

        getSeverity(type) {
            switch (type) {
                case 'Choix1':
                    return 'success';
                case 'Choix2':
                    return 'danger';
                default:
                    return null;
            }
        },

        openAddSpecialitePopup() {
            this.specialiteToEdit = null; // Réinitialiser specialiteToEdit
            this.addSpecialitePopupVisible = true;
        },

        closeAddSpecialitePopup() {
            this.addSpecialitePopupVisible = false;
            this.specialiteToEdit = null; // Réinitialiser specialiteToEdit
        },

        async fetchSpecialites() {
            this.loading = true;
            try {
                const response = await axios.get('/api/specialites');
                this.specialites = response.data;
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Échec du chargement des specialites.',
                    life: 3000,
                });
            } finally {
                this.loading = false;
            }
        },

        editSpecialite(specialite) {
            this.specialiteToEdit = specialite;
            this.addSpecialitePopupVisible = true;
        },

        editSelectedSpecialite() {
            if (
                this.selectedSpecialites &&
                this.selectedSpecialites.length === 1
            ) {
                const selectedSpecialite = this.selectedSpecialites[0];
                // Vérifier si la ligne sélectionnée est dans FixLignes ou specialites
                const specialiteToEdit =
                    this.FixLignes.find(
                        (s) => s.id === selectedSpecialite.id
                    ) ||
                    this.specialites.find(
                        (s) => s.id === selectedSpecialite.id
                    );
                if (specialiteToEdit) {
                    this.editSpecialite(specialiteToEdit);
                }
            } else {
                this.toast.add({
                    severity: 'warn',
                    summary: 'Aucune sélection',
                    detail: 'Veuillez sélectionner un seul specialite à modifier.',
                    life: 3000,
                });
            }
        },

        async handleSaveSpecialite(newSpecialite) {
            try {
                const response = await axios.post(
                    '/api/specialites',
                    newSpecialite
                );
                this.specialites.unshift(response.data);
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Le specialite a été ajouté avec succès.',
                    life: 3000,
                });
            } catch (error) {
                console.error(
                    'Erreur lors de la création du specialite :',
                    error
                );
                if (error.response && error.response.status === 422) {
                    const validationErrors = error.response.data.errors;
                    for (const [key, value] of Object.entries(
                        validationErrors
                    )) {
                        this.toast.add({
                            severity: 'error',
                            summary: 'Erreur',
                            detail: `${key}: ${value[0]}`,
                            life: 5000,
                        });
                    }
                } else {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: "Échec de l'ajout du specialite.",
                        life: 3000,
                    });
                }
            } finally {
                this.closeAddSpecialitePopup();
            }
        },

        async handleUpdateSpecialite(updatedSpecialite) {
            try {
                const response = await axios.put(
                    `/api/specialites/${updatedSpecialite.id}`,
                    updatedSpecialite
                );
                // Mettre à jour la ligne dans les specialites normaux
                const indexSpecialites = this.specialites.findIndex(
                    (s) => s.id === updatedSpecialite.id
                );
                if (indexSpecialites !== -1) {
                    this.specialites.splice(indexSpecialites, 1, response.data);
                }
                // Mettre à jour la ligne dans les lignes gelées
                const indexFixLignes = this.FixLignes.findIndex(
                    (s) => s.id === updatedSpecialite.id
                );
                if (indexFixLignes !== -1) {
                    this.FixLignes.splice(indexFixLignes, 1, response.data);
                }
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Le specialite a été modifié avec succès.',
                    life: 3000,
                });
            } catch (error) {
                console.error(
                    'Erreur lors de la modification du specialite :',
                    error
                );
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Échec de la modification du specialite.',
                    life: 3000,
                });
            } finally {
                this.closeAddSpecialitePopup();
            }
        },

        confirmDeleteAction() {
            if (this.specialiteToDelete) {
                this.deleteSpecialite(); // Supprimer un seul specialite
            } else if (
                this.selectedSpecialites &&
                this.selectedSpecialites.length > 0
            ) {
                this.deleteSelectedSpecialites(); // Supprimer les specialites sélectionnés
            }
            this.deletePopupVisible = false; // Fermer le popup de confirmation
        },

        async deleteSpecialite() {
            if (this.specialiteToDelete) {
                try {
                    await axios.delete(
                        `/api/specialites/${this.specialiteToDelete.id}`
                    );
                    // Supprimer la ligne des specialites normaux
                    this.specialites = this.specialites.filter(
                        (s) => s.id !== this.specialiteToDelete.id
                    );
                    // Supprimer la ligne des lignes gelées
                    this.FixLignes = this.FixLignes.filter(
                        (s) => s.id !== this.specialiteToDelete.id
                    );
                    this.toast.add({
                        severity: 'success',
                        summary: 'Supprimé',
                        detail: 'Le specialite a été supprimé avec succès.',
                        life: 3000,
                    });
                } catch (error) {
                    console.error(
                        'Erreur lors de la suppression du specialite :',
                        error
                    );
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'Échec de la suppression du specialite.',
                        life: 3000,
                    });
                } finally {
                    this.specialiteToDelete = null;
                }
            }
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
                    // Supprimer les lignes sélectionnées des specialites normaux
                    this.specialites = this.specialites.filter(
                        (s) => !this.selectedSpecialites.includes(s)
                    );
                    // Supprimer les lignes sélectionnées des lignes gelées
                    this.FixLignes = this.FixLignes.filter(
                        (s) => !this.selectedSpecialites.includes(s)
                    );
                    this.toast.add({
                        severity: 'success',
                        summary: 'Supprimé',
                        detail: 'Les specialites sélectionnés ont été supprimés avec succès.',
                        life: 3000,
                    });
                    this.selectedSpecialites = null;
                } catch (error) {
                    console.error(
                        'Erreur lors de la suppression des specialites :',
                        error
                    );
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'Échec de la suppression des specialites sélectionnés.',
                        life: 3000,
                    });
                }
            }
        },

        confirmDeleteSpecialite(specialite) {
            this.specialiteToDelete = specialite;
            this.deletePopupVisible = true;
        },

        confirmDeleteSelectedSpecialites() {
            if (
                this.selectedSpecialites &&
                this.selectedSpecialites.length > 0
            ) {
                this.specialiteToDelete = null;
                this.deletePopupVisible = true;
            } else {
                this.toast.add({
                    severity: 'warn',
                    summary: 'Aucune sélection',
                    detail: 'Veuillez sélectionner un ou plusieurs specialites à supprimer.',
                    life: 3000,
                });
            }
        },

        toggleLock(data, frozen, index) {
            if (frozen) {
                // Retirer la ligne gelée et la remettre dans les specialites normaux
                this.FixLignes = this.FixLignes.filter((c, i) => i !== index);
                this.specialites.push(data);
            } else {
                // Retirer la ligne normale et la mettre dans les lignes gelées
                this.specialites = this.specialites.filter(
                    (c, i) => i !== index
                );
                this.FixLignes.push(data);
            }

            // Trier les specialites pour maintenir l'ordre
            this.specialites.sort((val1, val2) => {
                return val1.id < val2.id ? -1 : 1;
            });
        },

        async importXLS() {
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

                    if (response.data.success) {
                        let message = 'Importation réussie.';
                        let severity = 'success';

                        if (this.errors.length > 0) {
                            message =
                                'Importation terminée avec des erreurs. Veuillez vérifier les lignes concernées.';
                            severity = 'warn';
                            this.showImportErrorPopup = true;
                        }

                        this.toast.add({
                            severity: severity,
                            summary: 'Import XLS',
                            detail: message,
                            life: 10000,
                        });
                    } else {
                        this.toast.add({
                            severity: 'error',
                            summary: 'Erreur',
                            detail:
                                response.data.message ||
                                "Une erreur s'est produite lors de l'importation.",
                            life: 10000,
                        });
                    }
                } catch (error) {
                    console.error("Erreur lors de l'importation :", error);
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: "Une erreur s'est produite lors de l'importation.",
                        life: 10000,
                    });
                } finally {
                    fileInput.remove();
                }
            };

            document.body.appendChild(fileInput);
            fileInput.click();
        },

        exportXLS() {
            try {
                const data = this.specialites.map((specialite) => ({
                    Code: specialite.code,
                    Secteur: specialite.secteur_id,
                    'Sous Secteur': specialite.sous_secteur_id,
                    'Année de Formation': specialite.annees_formation_id,
                    'Nom (AR)': specialite.nom_ar,
                    'Nom (FR)': specialite.nom_fr,
                    Type: specialite.type,
                    Homologation: specialite.homologation,
                    'Date Arrêté': specialite.date_arrete,
                    'Numéro Journal Officiel': specialite.num_journal_officiel,
                    'Date Journal Officiel': specialite.date_journal_officiel,
                    'Durée Formation': specialite.duree_formation,
                    Diplôme: specialite.diplôme,
                    'Heures Techniques': specialite.heures_technique,
                    'Heures Généraux': specialite.heures_generaux,
                    'Heures Totales': specialite.heures_total,
                    'Date Création Spécialité':
                        specialite.date_creation_specialité,
                    Statut: specialite.statut,
                    Observation: specialite.observation,
                    'Date Annulation Spécialité':
                        specialite.date_annulation_specialité,
                }));

                const worksheet = XLSX.utils.json_to_sheet(data);
                const workbook = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(
                    workbook,
                    worksheet,
                    'Specialites'
                );
                XLSX.writeFile(workbook, 'specialites.xlsx');

                this.toast.add({
                    severity: 'success',
                    summary: 'Export XLS',
                    detail: "L'exportation des specialites a été réalisée avec succès.",
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
