<template>
    <div class="surface-ground pt-2 px-1.5 pb-1.5" style="margin-top: 0">
        <!-- Header Section with Navbar -->
        <div
            class="surface-card p-2 border-round border-1 surface-border"
            style="
                position: relative;
                top: -37px;
                box-shadow: none;
                margin-bottom: -33px;
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
                        label="Vacances & Jours Fériés"
                        icon="pi pi-calendar"
                        class="p-button-text p-button-plain"
                        @click="
                            $router.push({
                                name: 'periodes-repos-administratif',
                            })
                        "
                    />
                    <Button
                        label="Années Administratives"
                        icon="pi pi-sitemap"
                        class="p-button-text p-button-plain"
                        disabled
                    />
                </div>
                <div class="flex gap-2">
                    <Button
                        icon="pi pi-refresh"
                        class="p-button-rounded p-button-text"
                        @click="fetchAnneesAdministrative"
                        v-tooltip="'Rafraîchir'"
                    />
                </div>
            </div>
        </div>

        <!-- Main Content Section -->
        <div
            class="surface-card p-4 pt-2 border-round shadow-2 border-1 surface-border"
        >
            <!-- Table Header with Actions -->
            <div class="flex justify-content-between align-items-center mb-4">
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
                <div class="flex gap-2">
                    <Button
                        icon="pi pi-plus"
                        label="Ajouter Année"
                        class="p-button-success"
                        @click="openForm"
                    />
                    <SplitButton
                        label="Actions"
                        icon="pi pi-ellipsis-v"
                        :model="actions"
                        class="p-button-secondary"
                    />
                </div>
            </div>

            <!-- Main DataTable -->
            <DataTable
                v-model:expandedRows="expandedRows"
                v-model:filters="filters"
                v-model:selection="selectedAnnees"
                :value="anneesAdministratives"
                dataKey="id"
                size="small"
                stripedRows
                paginator
                :rows="10"
                :rowsPerPageOptions="[10, 25, 50]"
                filterDisplay="menu"
                :globalFilterFields="[
                    'intitule',
                    'statut',
                    'date_debut',
                    'date_fin',
                ]"
                :loading="loading"
                scrollable
                scrollHeight="600px"
                removableSort
                class="p-datatable-sm border-round-lg"
            >
                <template #empty>
                    <div class="text-center py-4">
                        <i class="pi pi-inbox text-4xl text-400 mb-2" />
                        <p class="text-600">
                            Aucune année administrative trouvée
                        </p>
                    </div>
                </template>

                <Column expander style="width: 3rem" />
                <Column selectionMode="multiple" headerStyle="width: 3rem" />

                <Column
                    field="intitule"
                    header="Intitulé"
                    sortable
                    style="min-width: 12rem"
                >
                    <template #body="{ data }">
                        <span class="font-medium">{{ data.intitule }}</span>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter"
                            placeholder="Rechercher par intitulé"
                            @input="filterCallback"
                        />
                    </template>
                </Column>

                <Column
                    field="date_debut"
                    header="Début"
                    sortable
                    style="min-width: 10rem"
                >
                    <template #body="{ data }">
                        <Tag
                            :value="formatDate(data.date_debut)"
                            icon="pi pi-calendar"
                        />
                    </template>
                </Column>

                <Column
                    field="date_fin"
                    header="Fin"
                    sortable
                    style="min-width: 10rem"
                >
                    <template #body="{ data }">
                        <Tag
                            :value="formatDate(data.date_fin)"
                            icon="pi pi-calendar"
                        />
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
                            :value="formatStatut(data.statut)"
                            :severity="getSeverity(data.statut)"
                            :icon="getStatutIcon(data.statut)"
                        />
                    </template>
                </Column>

                <Column
                    field="formation_weeks"
                    header="Sem. Travail"
                    sortable
                    style="min-width: 8rem"
                >
                    <template #body="{ data }">
                        <Chip
                            :label="(data.formation_weeks || 0).toString()"
                            class="bg-primary-100 text-primary-800"
                        />
                    </template>
                </Column>

                <Column
                    field="vacation_weeks"
                    header="Sem. Repos"
                    sortable
                    style="min-width: 8rem"
                >
                    <template #body="{ data }">
                        <Chip
                            :label="(data.vacation_weeks || 0).toString()"
                            class="bg-orange-100 text-orange-800"
                        />
                    </template>
                </Column>

                <Column
                    header="Actions"
                    :exportable="false"
                    style="min-width: 10rem"
                >
                    <template #body="{ data }">
                        <div class="flex gap-1">
                            <Button
                                icon="pi pi-pencil"
                                class="p-button-rounded p-button-text p-button-sm"
                                @click="editAnnee(data)"
                                v-tooltip="'Modifier'"
                            />
                            <Button
                                icon="pi pi-trash"
                                class="p-button-rounded p-button-text p-button-danger p-button-sm"
                                @click="confirmDeleteAnnee(data)"
                                v-tooltip="'Supprimer'"
                            />
                            <Button
                                icon="pi pi-plus"
                                class="p-button-rounded p-button-text p-button-success p-button-sm"
                                @click="openPeriodeForm(data)"
                                v-tooltip="'Ajouter période'"
                            />
                        </div>
                    </template>
                </Column>

                <!-- Expanded Row Template -->
                <template #expansion="{ data }">
                    <div class="p-3 surface-50 border-round-lg">
                        <div
                            class="flex justify-content-between align-items-center mb-3"
                        >
                            <div class="flex align-items-center gap-2">
                                <i class="pi pi-clock text-primary-500" />
                                <span class="font-semibold"
                                    >Périodes de repos pour l'année
                                    {{ data.intitule }}</span
                                >
                            </div>
                            <Button
                                icon="pi pi-plus"
                                label="Ajouter Période"
                                class="p-button-sm p-button-success"
                                @click="openPeriodeForm(data)"
                            />
                        </div>

                        <DataTable
                            :value="data.repos_administratifs || []"
                            size="small"
                            :loading="loadingPeriodes"
                            dataKey="id"
                            class="p-datatable-sm border-round-lg"
                        >
                            <template #empty>
                                <div class="text-center py-4">
                                    <i
                                        class="pi pi-calendar-times text-4xl text-400 mb-2"
                                    />
                                    <p class="text-600">
                                        Aucune période de repos trouvée pour
                                        cette année.
                                    </p>
                                </div>
                            </template>

                            <Column
                                field="type_repos_administratif_fr"
                                header="Nature (FR)"
                                style="min-width: 15rem"
                            >
                                <template #body="{ data }">
                                    <div class="flex align-items-center gap-2">
                                        <i class="pi pi-tag text-primary-500" />
                                        {{
                                            data.type_repos_administratif_fr ||
                                            'Non défini'
                                        }}
                                    </div>
                                </template>
                            </Column>

                            <Column
                                field="type_repos_administratif_ar"
                                header="Nature (AR)"
                                style="min-width: 15rem"
                            >
                                <template #body="{ data }">
                                    <div class="flex align-items-center gap-2">
                                        <i class="pi pi-tag text-primary-500" />
                                        <span class="arabic-text">{{
                                            data.type_repos_administratif_ar ||
                                            'غير محدد'
                                        }}</span>
                                    </div>
                                </template>
                            </Column>

                            <Column
                                header="Date Début"
                                style="min-width: 15rem"
                            >
                                <template #body="{ data }">
                                    <div class="flex align-items-center gap-2">
                                        <i
                                            class="pi pi-calendar text-blue-500"
                                        />
                                        {{ formatDate(data.date_debut) }}
                                    </div>
                                </template>
                            </Column>

                            <Column header="Date Fin" style="min-width: 15rem">
                                <template #body="{ data }">
                                    <div class="flex align-items-center gap-2">
                                        <i
                                            class="pi pi-calendar text-blue-500"
                                        />
                                        {{ formatDate(data.date_fin) || '-' }}
                                    </div>
                                </template>
                            </Column>

                            <Column header="Jours" style="min-width: 8rem">
                                <template #body="{ data }">
                                    <Badge
                                        :value="data.nombre_jour || 1"
                                        severity="info"
                                    />
                                </template>
                            </Column>

                            <Column
                                field="statut"
                                header="Statut"
                                style="min-width: 10rem"
                            >
                                <template #body="{ data }">
                                    <Tag
                                        :value="formatReposStatut(data.statut)"
                                        :severity="
                                            getReposSeverity(data.statut)
                                        "
                                        :icon="getReposStatutIcon(data.statut)"
                                    />
                                </template>
                            </Column>

                            <Column header="Actions" style="min-width: 10rem">
                                <template #body="{ data }">
                                    <div class="flex gap-1">
                                        <Button
                                            icon="pi pi-pencil"
                                            class="p-button-rounded p-button-text p-button-sm"
                                            @click="editPeriode(data)"
                                            v-tooltip="'Modifier période'"
                                        />
                                        <Button
                                            icon="pi pi-trash"
                                            class="p-button-rounded p-button-text p-button-danger p-button-sm"
                                            @click="confirmDeletePeriode(data)"
                                            v-tooltip="'Annuler période'"
                                        />
                                    </div>
                                </template>
                            </Column>
                        </DataTable>
                    </div>
                </template>
            </DataTable>

            <!-- Modals -->
            <AnneeAdministrativeForm
                :visible="formVisible"
                :anneeToEdit="anneeToEdit"
                @update:visible="formVisible = $event"
                @save="handleSaveAnnee"
                @update="handleUpdateAnnee"
                @close="closeForm"
            />

            <PeriodeReposAdministrativeForm
                :visible="periodeFormVisible"
                :annee="selectedAnnee"
                :periodeToEdit="periodeToEdit"
                @update:visible="periodeFormVisible = $event"
                @save="handleSavePeriode"
                @update="handleUpdatePeriode"
                @close="closePeriodeForm"
            />

            <!-- Delete Confirmation Dialog for Annee -->
            <Dialog
                v-model:visible="deleteFormVisible"
                header="Confirmer la suppression"
                :modal="true"
                :style="{ width: '450px' }"
                :breakpoints="{ '960px': '75vw', '640px': '90vw' }"
                class="p-dialog surface-card border-round-lg shadow-4"
                :pt="{
                    mask: { style: 'backdrop-filter: blur(4px)' },
                    header: {
                        class: 'surface-50 border-bottom-1 surface-border p-3',
                    },
                    content: { class: 'surface-ground p-4' },
                    footer: {
                        class: 'surface-50 border-top-1 surface-border p-3',
                    },
                }"
            >
                <div class="flex flex-column gap-3">
                    <div class="flex align-items-center gap-3">
                        <i
                            class="pi pi-exclamation-triangle text-4xl text-red-500"
                        />
                        <span>
                            Voulez-vous vraiment supprimer l'année
                            administrative
                            <b>{{ anneeToDelete?.intitule }}</b> ?<br />
                            <small class="text-red-500"
                                >Attention : Cela supprimera également toutes
                                les périodes associées !</small
                            >
                        </span>
                    </div>
                    <div class="field">
                        <label for="deleteAnneePassword" class="font-semibold"
                            >Mot de passe de confirmation</label
                        >
                        <InputText
                            id="deleteAnneePassword"
                            v-model="deletePassword"
                            type="password"
                            class="w-full"
                            :class="{ 'p-invalid': passwordError }"
                            placeholder="Entrez le mot de passe"
                            @input="passwordError = ''"
                        />
                        <small v-if="passwordError" class="p-error">{{
                            passwordError
                        }}</small>
                    </div>
                </div>
                <template #footer>
                    <Button
                        label="Annuler"
                        icon="pi pi-times"
                        class="p-button-text"
                        @click="cancelDeleteAnnee"
                    />
                    <Button
                        label="Supprimer"
                        icon="pi pi-check"
                        class="p-button-danger"
                        :disabled="loading || !deletePassword"
                        @click="deleteAnnee"
                    />
                </template>
            </Dialog>

            <!-- Delete Confirmation Dialog for Periode -->
            <Dialog
                v-model:visible="deletePeriodeFormVisible"
                header="Confirmer l'annulation"
                :modal="true"
                :style="{ width: '450px' }"
                :breakpoints="{ '960px': '75vw', '640px': '90vw' }"
                class="p-dialog surface-card border-round-lg shadow-4"
                :pt="{
                    mask: { style: 'backdrop-filter: blur(4px)' },
                    header: {
                        class: 'surface-50 border-bottom-1 surface-border p-3',
                    },
                    content: { class: 'surface-ground p-4' },
                    footer: {
                        class: 'surface-50 border-top-1 surface-border p-3',
                    },
                }"
            >
                <div class="flex flex-column gap-3">
                    <div class="flex align-items-center gap-3">
                        <i
                            class="pi pi-exclamation-triangle text-4xl text-red-500"
                        />
                        <span>
                            Voulez-vous vraiment annuler la période
                            <b>{{
                                periodeToDelete?.type_repos_administratif_fr ||
                                'Non défini'
                            }}</b>
                            ?
                        </span>
                    </div>
                    <div class="field">
                        <label for="deletePeriodePassword" class="font-semibold"
                            >Mot de passe de confirmation</label
                        >
                        <InputText
                            id="deletePeriodePassword"
                            v-model="deletePassword"
                            type="password"
                            class="w-full"
                            :class="{ 'p-invalid': passwordError }"
                            placeholder="Entrez le mot de passe"
                            @input="passwordError = ''"
                        />
                        <small v-if="passwordError" class="p-error">{{
                            passwordError
                        }}</small>
                    </div>
                </div>
                <template #footer>
                    <Button
                        label="Annuler"
                        icon="pi pi-times"
                        class="p-button-text"
                        @click="cancelDeletePeriode"
                    />
                    <Button
                        label="Annuler la période"
                        icon="pi pi-check"
                        class="p-button-danger"
                        :disabled="loading || !deletePassword"
                        @click="deletePeriode"
                    />
                </template>
            </Dialog>

            <!-- Toast Notification -->
            <Toast position="top-right" />
        </div>
    </div>
</template>

<script>
import { FilterMatchMode } from 'primevue/api';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Chip from 'primevue/chip';
import Badge from 'primevue/badge';
import Dialog from 'primevue/dialog';
import SplitButton from 'primevue/splitbutton';
import Toast from 'primevue/toast';
import AnneeAdministrativeForm from '@/Components/Annees/AnneeAdministrativeForm.vue';
import PeriodeReposAdministrativeForm from '@/Components/Annees/PeriodeReposAdministrativeForm.vue';

export default {
    components: {
        Button,
        InputText,
        DataTable,
        Column,
        Tag,
        Chip,
        Badge,
        Dialog,
        SplitButton,
        Toast,
        AnneeAdministrativeForm,
        PeriodeReposAdministrativeForm,
    },
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            anneesAdministratives: [],
            selectedAnnees: [],
            filters: null,
            actions: [
                {
                    label: 'Exporter en Excel',
                    icon: 'pi pi-file-excel',
                    command: () => this.exportExcel(),
                },
                {
                    label: 'Modifier la sélection',
                    icon: 'pi pi-pencil',
                    command: () => this.editSelectedAnnee(),
                    disabled: () => this.selectedAnnees.length !== 1,
                },
                {
                    label: 'Supprimer la sélection',
                    icon: 'pi pi-trash',
                    command: () => this.deleteSelectedAnnees(),
                    disabled: () => this.selectedAnnees.length === 0,
                },
            ],
            formVisible: false,
            periodeFormVisible: false,
            deleteFormVisible: false,
            deletePeriodeFormVisible: false,
            anneeToEdit: null,
            anneeToDelete: null,
            selectedAnnee: null,
            periodeToEdit: null,
            periodeToDelete: null,
            deletePassword: '',
            passwordError: '',
            loading: false,
            loadingPeriodes: false,
            expandedRows: [],
        };
    },
    created() {
        this.initFilters();
        this.fetchAnneesAdministrative();
    },
    methods: {
        initFilters() {
            this.filters = {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                intitule: {
                    value: null,
                    matchMode: FilterMatchMode.STARTS_WITH,
                },
                statut: { value: null, matchMode: FilterMatchMode.EQUALS },
            };
        },
        clearFilter() {
            this.initFilters();
        },
        formatDate(date) {
            if (!date) return '-';
            const d = new Date(date);
            return d.toLocaleDateString('fr-FR');
        },
        normalizeStatut(statut) {
            if (!statut) {
                return 'archivee';
            }
            const statutMap = {
                'En cours': 'en_cours',
                'De base': 'de_base',
                Archivée: 'archivee',
                Prochaine: 'prochaine',
                'en cours': 'en_cours',
                'de base': 'de_base',
                archivée: 'archivee',
                prochaine: 'prochaine',
            };
            return statutMap[statut] || statut.toLowerCase();
        },
        formatStatut(statut) {
            const normalizedStatut = this.normalizeStatut(statut);
            const statusLabels = {
                en_cours: 'En cours',
                de_base: 'De base',
                archivee: 'Archivée',
                prochaine: 'Prochaine',
            };
            return statusLabels[normalizedStatut] || statut || 'Archivée';
        },
        getSeverity(statut) {
            const normalizedStatut = this.normalizeStatut(statut);
            const statusSeverity = {
                en_cours: 'success',
                de_base: 'secondary',
                archivee: 'info',
                prochaine: 'warning',
            };
            return statusSeverity[normalizedStatut] || 'info';
        },
        getStatutIcon(statut) {
            const normalizedStatut = this.normalizeStatut(statut);
            const statusIcons = {
                en_cours: 'pi pi-check',
                de_base: 'pi pi-book',
                archivee: 'pi pi-folder',
                prochaine: 'pi pi-clock',
            };
            return statusIcons[normalizedStatut] || 'pi pi-info';
        },
        formatReposStatut(statut) {
            const statusLabels = {
                a_venir: 'À venir',
                en_cours: 'En cours',
                termine: 'Terminé',
                annule: 'Annulé',
            };
            return statusLabels[statut] || statut || 'Annulé';
        },
        getReposSeverity(statut) {
            const statusSeverity = {
                a_venir: 'warning',
                en_cours: 'success',
                termine: 'info',
                annule: 'danger',
            };
            return statusSeverity[statut] || 'secondary';
        },
        getReposStatutIcon(statut) {
            const statusIcons = {
                a_venir: 'pi pi-clock',
                en_cours: 'pi pi-check',
                termine: 'pi pi-check-circle',
                annule: 'pi pi-times',
            };
            return statusIcons[statut] || 'pi pi-info';
        },
        async fetchAnneesAdministrative() {
            this.loading = true;
            try {
                const response = await axios.get('/api/annees-administratives');
                this.anneesAdministratives = response.data;
                if (
                    !this.anneesAdministratives.some(
                        (annee) => annee.repos_administratifs?.length > 0
                    )
                ) {
                    this.toast.add({
                        severity: 'info',
                        summary: 'Information',
                        detail: 'Aucune période de repos trouvée pour les années administratives.',
                        life: 3000,
                    });
                }
            } catch (error) {
                console.error('Erreur lors du chargement des années:', {
                    status: error.response?.status,
                    message: error.response?.data?.message || error.message,
                });
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Erreur lors du chargement des années administratives',
                    life: 3000,
                });
            } finally {
                this.loading = false;
            }
        },
        openForm() {
            this.anneeToEdit = null;
            this.formVisible = true;
        },
        closeForm() {
            this.formVisible = false;
            this.anneeToEdit = null;
        },
        editAnnee(annee) {
            this.anneeToEdit = { ...annee };
            this.formVisible = true;
        },
        editSelectedAnnee() {
            if (this.selectedAnnees.length === 1) {
                this.editAnnee(this.selectedAnnees[0]);
            } else {
                this.showWarn(
                    'Veuillez sélectionner une seule année à modifier.'
                );
            }
        },
        confirmDeleteAnnee(annee) {
            this.anneeToDelete = annee;
            this.deletePassword = '';
            this.passwordError = '';
            this.deleteFormVisible = true;
        },
        cancelDeleteAnnee() {
            this.deleteFormVisible = false;
            this.anneeToDelete = null;
            this.deletePassword = '';
            this.passwordError = '';
        },
        async deleteAnnee() {
            if (!this.anneeToDelete) return;
            try {
                this.loading = true;
                const cleanedPassword = this.deletePassword.trim();
                const response = await axios.delete(
                    `/api/annees-administratives/${this.anneeToDelete.id}`,
                    {
                        data: { password: cleanedPassword },
                    }
                );
                this.anneesAdministratives = this.anneesAdministratives.filter(
                    (a) => a.id !== this.anneeToDelete.id
                );
                this.showSuccess(
                    response.data.message ||
                        'Année administrative supprimée avec succès.'
                );
                this.deleteFormVisible = false;
                this.anneeToDelete = null;
                this.deletePassword = '';
                this.passwordError = '';
            } catch (error) {
                this.passwordError =
                    error.response?.data?.error ||
                    'Erreur lors de la suppression. Vérifiez le mot de passe.';
                this.showError(this.passwordError);
            } finally {
                this.loading = false;
            }
        },
        async deleteSelectedAnnees() {
            if (this.selectedAnnees.length > 0) {
                this.anneeToDelete = this.selectedAnnees[0];
                this.deletePassword = '';
                this.passwordError = '';
                this.deleteFormVisible = true;
            } else {
                this.showWarn(
                    'Veuillez sélectionner au moins une année à supprimer.'
                );
            }
        },
        openPeriodeForm(annee) {
            this.selectedAnnee = annee;
            this.periodeToEdit = null;
            this.periodeFormVisible = true;
        },
        closePeriodeForm() {
            this.periodeFormVisible = false;
            this.selectedAnnee = null;
            this.periodeToEdit = null;
        },
        editPeriode(periode) {
            this.periodeToEdit = { ...periode };
            this.selectedAnnee = this.anneesAdministratives.find(
                (a) => a.id === periode.annee_administrative_id
            );
            this.periodeFormVisible = true;
        },
        confirmDeletePeriode(periode) {
            this.periodeToDelete = periode;
            this.deletePassword = '';
            this.passwordError = '';
            this.deletePeriodeFormVisible = true;
        },
        cancelDeletePeriode() {
            this.deletePeriodeFormVisible = false;
            this.periodeToDelete = null;
            this.deletePassword = '';
            this.passwordError = '';
        },
        async deletePeriode() {
            if (!this.periodeToDelete) return;
            try {
                this.loadingPeriodes = true;
                const cleanedPassword = this.deletePassword.trim();
                const response = await axios.delete(
                    `/api/periodes-repos-administratif/${this.periodeToDelete.id}`,
                    {
                        data: { password: cleanedPassword },
                    }
                );
                const annee = this.anneesAdministratives.find(
                    (a) => a.id === this.periodeToDelete.annee_administrative_id
                );
                if (annee) {
                    annee.repos_administratifs =
                        annee.repos_administratifs.filter(
                            (p) => p.id !== this.periodeToDelete.id
                        );
                }
                this.showSuccess(
                    response.data.message || 'Période annulée avec succès.'
                );
                this.deletePeriodeFormVisible = false;
                this.periodeToDelete = null;
                this.deletePassword = '';
                this.passwordError = '';
            } catch (error) {
                this.passwordError =
                    error.response?.data?.error ||
                    "Erreur lors de l'annulation. Vérifiez le mot de passe.";
                this.showError(this.passwordError);
            } finally {
                this.loadingPeriodes = false;
            }
        },
        handleSaveAnnee(annee) {
            this.anneesAdministratives.push({
                ...annee,
                date_debut: new Date(annee.date_debut),
                date_fin: new Date(annee.date_fin),
                statut: this.normalizeStatut(annee.statut),
                repos_administratifs: annee.repos_administratifs || [],
            });
            this.closeForm();
        },
        handleUpdateAnnee(updatedAnnee) {
            const index = this.anneesAdministratives.findIndex(
                (a) => a.id === updatedAnnee.id
            );
            if (index !== -1) {
                this.anneesAdministratives[index] = {
                    ...updatedAnnee,
                    date_debut: new Date(updatedAnnee.date_debut),
                    date_fin: new Date(updatedAnnee.date_fin),
                    statut: this.normalizeStatut(updatedAnnee.statut),
                    repos_administratifs:
                        this.anneesAdministratives[index]
                            .repos_administratifs || [],
                };
            }
            this.closeForm();
        },
        handleSavePeriode(periode) {
            const annee = this.anneesAdministratives.find(
                (a) => a.id === periode.annee_administrative_id
            );
            if (annee) {
                annee.repos_administratifs = annee.repos_administratifs || [];
                annee.repos_administratifs.push(periode);
                this.showSuccess('Période de repos créée avec succès.');
            }
            this.closePeriodeForm();
        },
        handleUpdatePeriode(updatedPeriode) {
            const annee = this.anneesAdministratives.find(
                (a) => a.id === updatedPeriode.annee_administrative_id
            );
            if (annee) {
                const index = annee.repos_administratifs.findIndex(
                    (p) => p.id === updatedPeriode.id
                );
                if (index !== -1) {
                    annee.repos_administratifs[index] = updatedPeriode;
                    this.showSuccess(
                        'Période de repos mise à jour avec succès.'
                    );
                }
            }
            this.closePeriodeForm();
        },
        exportExcel() {
            this.showWarn(
                "Fonctionnalité d'exportation en Excel non implémentée."
            );
        },
        showSuccess(message) {
            this.toast.add({
                severity: 'success',
                summary: 'Succès',
                detail: message,
                life: 3000,
            });
        },
        showError(message) {
            this.toast.add({
                severity: 'error',
                summary: 'Erreur',
                detail: message,
                life: 5000,
            });
        },
        showWarn(message) {
            this.toast.add({
                severity: 'warn',
                summary: 'Attention',
                detail: message,
                life: 5000,
            });
        },
    },
};
</script>

<style scoped>
@font-face {
    font-family: 'NotoNaskhArabic';
    src: url('/fonts/NotoNaskhArabic-VariableFont_wght.ttf') format('opentype');
    font-weight: normal;
    font-style: normal;
    font-display: swap;
}

.arabic-text {
    font-family: 'NotoNaskhArabic', sans-serif;
    direction: rtl;
    text-align: right;
}

.p-datatable {
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}
.p-datatable-sm .p-datatable-tbody > tr > td {
    padding: 0.5rem 1rem;
}
.p-datatable .p-column-header-content {
    justify-content: center;
}
.field {
    margin-bottom: 1rem;
}
.p-error {
    color: #dc3545;
    font-size: 0.875rem;
}
label {
    margin-bottom: 0.5rem;
    display: block;
    color: #495057;
}
@media (prefers-color-scheme: dark) {
    label {
        color: #1f2a44;
    }
}
</style>
