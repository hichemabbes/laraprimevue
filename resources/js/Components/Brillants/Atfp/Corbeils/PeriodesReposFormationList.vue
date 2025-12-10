<template>
    <div class="surface-ground pt-2 px-1.5 pb-1.5" style="margin-top: 0">
        <!-- Header Section with Navbar -->
        <div
            class="surface-card p-2 border-round border-1 surface-border"
            style="
                position: relative;
                top: -28px;
                box-shadow: none;
                margin-bottom: -22px;
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
                        label="Années de Formation"
                        icon="pi pi-calendar"
                        class="p-button-text p-button-plain"
                        @click="$router.push({ name: 'annees-formation' })"
                    />
                    <Button
                        label="Vacances & Jours Fériés"
                        icon="pi pi-sitemap"
                        class="p-button-text p-button-plain"
                        disabled
                    />
                </div>
                <div class="flex gap-2">
                    <Button
                        icon="pi pi-refresh"
                        class="p-button-text p-button-plain"
                        @click="fetchPeriodesRepos"
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
                        <i class="pi pi-search" />
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
                        label="Ajouter"
                        class="p-button-success"
                        @click="openForm"
                    />
                    <SplitButton
                        label="Actions"
                        icon="pi pi-ellipsis-v"
                        :model="actions"
                        class="p-button-text"
                    />
                </div>
            </div>

            <!-- Main DataTable -->
            <DataTable
                v-model:filters="filters"
                v-model:selection="selectedPeriodes"
                :value="periodesRepos"
                dataKey="id"
                size="small"
                stripedRows
                paginator
                :rows="10"
                :rowsPerPageOptions="[10, 25, 50]"
                filterDisplay="menu"
                :globalFilterFields="[
                    'titre_vacance',
                    'annee_formation.intitule',
                    'type',
                    'date_debut',
                    'date_fin',
                    'nombre_jour',
                    'statut',
                ]"
                :loading="loading"
                scrollable
                scrollHeight="600px"
                removableSort
                class="p-datatable-sm border-1 surface-border"
            >
                <template #empty>
                    <div class="text-center py-4">
                        <i class="pi pi-inbox text-4xl text-400 mb-2" />
                        <p class="text-600">Aucune période de repos trouvée</p>
                    </div>
                </template>
                <Column selectionMode="multiple" headerStyle="width: 3rem" />
                <Column
                    field="titre_vacance"
                    header="Titre de Vacance"
                    sortable
                    style="min-width: 15rem"
                >
                    <template #body="{ data }">
                        <span class="font-medium">{{
                            data.titre_vacance
                        }}</span>
                    </template>
                </Column>
                <Column
                    field="annee_formation.intitule"
                    header="Année"
                    sortable
                    style="min-width: 12rem"
                >
                    <template #body="{ data }">
                        <span>{{ data.annee_formation?.intitule || '-' }}</span>
                    </template>
                </Column>
                <Column
                    field="option_repos_formation_fr.nom_fr"
                    header="Nature"
                    sortable
                    style="min-width: 12rem"
                >
                    <template #body="{ data }">
                        <Tag
                            :value="
                                data.option_repos_formation_fr?.nom_fr || '-'
                            "
                        />
                    </template>
                </Column>
                <Column
                    field="date_debut"
                    header="Date/Période"
                    sortable
                    style="min-width: 15rem"
                >
                    <template #body="{ data }">
                        <Tag
                            :value="
                                data.date_fin
                                    ? `${formatDate(data.date_debut)} - ${formatDate(data.date_fin)}`
                                    : formatDate(data.date_debut)
                            "
                            icon="pi pi-calendar"
                        />
                    </template>
                </Column>
                <Column
                    field="nombre_jour"
                    header="Nb Jours"
                    sortable
                    style="min-width: 8rem"
                >
                    <template #body="{ data }">
                        <Chip
                            :label="data.nombre_jour || '-'"
                            class="bg-primary-100 text-primary-800"
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
                            :value="data.statut"
                            :severity="
                                data.statut === 'Actif' ? 'success' : 'danger'
                            "
                            :icon="
                                data.statut === 'Actif'
                                    ? 'pi pi-check'
                                    : 'pi pi-times'
                            "
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
                                v-tooltip="'Modifier'"
                            />
                            <Button
                                icon="pi pi-trash"
                                class="p-button-rounded p-button-text p-button-danger p-button-sm"
                                @click="confirmDeletePeriode(data)"
                                v-tooltip="'Supprimer'"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- Modals -->
        <PeriodeReposFormationForm
            :visible="formVisible"
            :periodeToEdit="periodeToEdit"
            :optionsRepos="optionsRepos"
            @update:visible="formVisible = $event"
            @save="handleSavePeriode"
            @update="handleUpdatePeriode"
            @close="closeForm"
        />

        <Dialog
            v-model:visible="deleteFormVisible"
            header="Confirmer la suppression"
            :style="{ width: '450px' }"
            :modal="true"
        >
            <div class="confirmation-content flex align-items-center gap-3">
                <i class="pi pi-exclamation-triangle text-4xl text-red-500" />
                <span>
                    Voulez-vous vraiment supprimer la période
                    <b>{{ periodeToDelete?.titre_vacance || 'N/A' }}</b> ? Cette
                    action est irréversible.
                </span>
            </div>
            <template #footer>
                <Button
                    label="Annuler"
                    icon="pi pi-times"
                    class="p-button-text"
                    @click="deleteFormVisible = false"
                />
                <Button
                    label="Supprimer"
                    icon="pi pi-check"
                    class="p-button-danger"
                    @click="deletePeriode"
                />
            </template>
        </Dialog>

        <Toast position="top-right" />
    </div>
</template>

<script>
import { FilterMatchMode } from 'primevue/api';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';

// Components
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Chip from 'primevue/chip';
import Dialog from 'primevue/dialog';
import SplitButton from 'primevue/splitbutton';
import Toast from 'primevue/toast';
import PeriodeReposFormationForm from '@/Components/Atfp/Annees/PeriodeReposFormationForm.vue';

export default {
    components: {
        Button,
        InputText,
        DataTable,
        Column,
        Tag,
        Chip,
        Dialog,
        SplitButton,
        Toast,
        PeriodeReposFormationForm,
    },
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            periodesRepos: [],
            selectedPeriodes: [],
            filters: null,
            actions: [
                {
                    label: 'Modifier la sélection',
                    icon: 'pi pi-pencil',
                    command: () => this.editSelectedPeriode(),
                    disabled: () => this.selectedPeriodes.length !== 1,
                },
                {
                    label: 'Supprimer la sélection',
                    icon: 'pi pi-trash',
                    command: () => this.deleteSelectedPeriodes(),
                    disabled: () => this.selectedPeriodes.length === 0,
                },
            ],
            formVisible: false,
            deleteFormVisible: false,
            periodeToEdit: null,
            periodeToDelete: null,
            loading: true,
            optionsRepos: [],
        };
    },
    created() {
        this.initFilters();
        this.fetchPeriodesRepos();
        this.fetchOptionsRepos();
    },
    methods: {
        initFilters() {
            this.filters = {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                titre_vacance: {
                    value: null,
                    matchMode: FilterMatchMode.STARTS_WITH,
                },
                'annee_formation.intitule': {
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
            if (!date) return 'N/A';
            const d = new Date(date);
            return d.toLocaleDateString('fr-FR');
        },
        async fetchPeriodesRepos() {
            this.loading = true;
            try {
                const response = await axios.get('/api/periodes-repos');
                this.periodesRepos = response.data.map((periode) => ({
                    ...periode,
                    date_debut: new Date(periode.date_debut),
                    date_fin: periode.date_fin
                        ? new Date(periode.date_fin)
                        : null,
                }));
            } catch (error) {
                this.showError('Erreur lors du chargement des périodes');
            } finally {
                this.loading = false;
            }
        },
        async fetchOptionsRepos() {
            try {
                const response = await axios.get('/api/options-listes', {
                    params: { type_categorie_nom_fr: 'Nature repos formation' },
                });
                this.optionsRepos = response.data;
            } catch (error) {
                this.showError(
                    'Erreur lors du chargement des options de repos'
                );
            }
        },
        openForm() {
            this.periodeToEdit = null;
            this.formVisible = true;
        },
        closeForm() {
            this.formVisible = false;
            this.periodeToEdit = null;
        },
        editPeriode(periode) {
            this.periodeToEdit = { ...periode };
            this.formVisible = true;
        },
        editSelectedPeriode() {
            if (this.selectedPeriodes.length === 1) {
                this.editPeriode(this.selectedPeriodes[0]);
            } else {
                this.showWarn('Sélectionnez une seule période à modifier');
            }
        },
        confirmDeletePeriode(periode) {
            this.periodeToDelete = periode;
            this.deleteFormVisible = true;
        },
        async deletePeriode() {
            try {
                await axios.delete(
                    `/api/periodes-repos/${this.periodeToDelete.id}`
                );
                this.periodesRepos = this.periodesRepos.filter(
                    (p) => p.id !== this.periodeToDelete.id
                );
                this.showSuccess('Période supprimée avec succès');
            } catch (error) {
                this.showError('Erreur lors de la suppression');
            } finally {
                this.deleteFormVisible = false;
                this.periodeToDelete = null;
            }
        },
        async deleteSelectedPeriodes() {
            try {
                await Promise.all(
                    this.selectedPeriodes.map((p) =>
                        axios.delete(`/api/periodes-repos/${p.id}`)
                    )
                );
                this.periodesRepos = this.periodesRepos.filter(
                    (p) => !this.selectedPeriodes.some((s) => s.id === p.id)
                );
                this.selectedPeriodes = [];
                this.showSuccess('Sélection supprimée avec succès');
            } catch (error) {
                this.showError('Erreur lors de la suppression');
            }
        },
        async handleSavePeriode(newPeriode) {
            try {
                const response = await axios.post(
                    '/api/periodes-repos',
                    newPeriode
                );
                this.periodesRepos.unshift(response.data);
                this.formVisible = false;
                this.showSuccess('Période créée avec succès');
            } catch (error) {
                this.showError('Erreur lors de la création');
            }
        },
        async handleUpdatePeriode(updatedPeriode) {
            try {
                const response = await axios.put(
                    `/api/periodes-repos/${updatedPeriode.id}`,
                    updatedPeriode
                );
                const index = this.periodesRepos.findIndex(
                    (p) => p.id === updatedPeriode.id
                );
                if (index !== -1)
                    this.periodesRepos.splice(index, 1, response.data);
                this.formVisible = false;
                this.showSuccess('Période mise à jour avec succès');
            } catch (error) {
                this.showError('Erreur lors de la mise à jour');
            }
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
                life: 3000,
            });
        },
    },
};
</script>

<style scoped>
.confirmation-content {
    display: flex;
    align-items: center;
    padding: 1rem;
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
</style>
