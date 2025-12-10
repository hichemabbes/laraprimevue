<template>
    <div class="surface-ground pt-2 px-1.5 pb-1.5" style="margin-top: 0">
        <!-- Header -->
        <div
            class="surface-card p-2 border-round border-1 surface-border"
            style="position: relative; top: -36px; margin-bottom: -32px"
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
                        label="Formations"
                        icon="pi pi-users"
                        class="p-button-text p-button-plain p-button-sm"
                        disabled
                    />
                </div>

                <div class="flex gap-2">
                    <Button
                        icon="pi pi-refresh"
                        class="p-button-text p-button-sm p-button-rounded p-button-secondary"
                        @click="fetchFormations"
                        v-tooltip="'Rafraîchir'"
                    />
                    <Button
                        icon="pi pi-plus"
                        class="p-button-success p-button-sm p-button-rounded"
                        @click="openAddFormation"
                        v-tooltip="'Ajouter une formation'"
                    />
                </div>
            </div>
        </div>

        <!-- Contenu principal -->
        <div
            class="surface-card p-4 pt-2 border-round shadow-2 border-1 surface-border"
        >
            <!-- Barre de recherche -->
            <div class="flex justify-content-between align-items-center mb-4">
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

            <!-- Tableau Formations -->
            <DataTable
                v-model:filters="filters"
                :value="formations"
                dataKey="id"
                size="small"
                stripedRows
                paginator
                :rows="rows"
                :rowsPerPageOptions="[20, 50, 100]"
                :totalRecords="totalRecords"
                :loading="loading"
                :lazy="true"
                filterDisplay="menu"
                :globalFilterFields="[
                    'code',
                    'nom_fr',
                    'nom_ar',
                    'session_label',
                    'specialite_label',
                    'theme_label',
                    'statut',
                ]"
                @page="onPage($event)"
                @update:rows="onRowsUpdate($event)"
                scrollable
                scrollHeight="700px"
                class="p-datatable-sm border-1 surface-border"
            >
                <template #empty>
                    <div class="py-4 text-center">
                        <i class="pi pi-inbox text-4xl text-400 mb-2" />
                        <p class="text-600">Aucune formation trouvée</p>
                    </div>
                </template>

                <Column
                    field="code"
                    header="Code"
                    sortable
                    style="min-width: 8rem"
                >
                    <template #body="{ data }">
                        <span>{{ data.code || '-' }}</span>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter"
                            placeholder="Code"
                            @input="filterCallback()"
                        />
                    </template>
                </Column>

                <Column
                    field="nom_fr"
                    header="Nom (FR)"
                    sortable
                    style="min-width: 18rem"
                >
                    <template #body="{ data }">
                        <span class="font-medium">{{ data.nom_fr || '-' }}</span>
                    </template>
                </Column>

                <Column
                    field="nom_ar"
                    header="Nom (AR)"
                    sortable
                    style="min-width: 18rem"
                >
                    <template #body="{ data }">
                        <span class="font-arabic">{{ data.nom_ar || '-' }}</span>
                    </template>
                </Column>

                <Column
                    field="session_label"
                    header="Session"
                    style="min-width: 14rem"
                >
                    <template #body="{ data }">
                        <span>{{ data.session_label || '-' }}</span>
                    </template>
                </Column>

                <Column
                    field="specialite_label"
                    header="Spécialité"
                    style="min-width: 16rem"
                >
                    <template #body="{ data }">
                        <span>{{ data.specialite_label || '-' }}</span>
                    </template>
                </Column>

                <Column
                    field="theme_label"
                    header="Thème"
                    style="min-width: 16rem"
                >
                    <template #body="{ data }">
                        <span>{{ data.theme_label || '-' }}</span>
                    </template>
                </Column>

                <Column
                    field="cible_fr"
                    header="Cible (FR)"
                    style="min-width: 14rem"
                >
                    <template #body="{ data }">
                        <span>{{ data.cible_fr || '-' }}</span>
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
                            :value="data.statut || '-'"
                            :severity="data.statut === 'Actif' ? 'success' : 'secondary'"
                        />
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-model="filterModel.value"
                            :options="['Actif', 'Inactif']"
                            placeholder="Sélectionner un statut"
                            class="p-column-filter"
                            :showClear="true"
                            @change="filterCallback()"
                        />
                    </template>
                </Column>

                <Column header="Actions" style="min-width: 12rem">
                    <template #body="{ data }">
                        <div class="flex gap-2">
                            <Button
                                icon="pi pi-pencil"
                                class="p-button-text p-button-rounded p-button-warning"
                                v-tooltip="'Modifier la formation'"
                                @click="openEditFormation(data)"
                            />
                            <Button
                                icon="pi pi-trash"
                                class="p-button-text p-button-rounded p-button-danger"
                                v-tooltip="'Supprimer la formation'"
                                @click="confirmDeleteFormation(data)"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>

            <!-- Dialog Ajout -->
            <FormationAdd
                :visible="showAddDialog"
                @update:visible="showAddDialog = $event"
                @save="onFormationSaved"
            />

            <!-- Dialog Edition -->
            <FormationEdit
                :visible="showEditDialog"
                :formationId="formationToEdit ? formationToEdit.id : null"
                :formationData="formationToEdit"
                @update:visible="showEditDialog = $event"
                @update="onFormationUpdated"
            />

            <!-- Dialog Suppression -->
            <Dialog
                v-model:visible="showDeleteDialog"
                header="Suppression de formation"
                :modal="true"
                :style="{ width: '500px' }"
            >
                <p v-if="formationToDelete">
                    Voulez-vous vraiment supprimer la formation
                    <b>{{ formationToDelete.nom_fr || formationToDelete.code }}</b> ?
                </p>
                <template #footer>
                    <Button
                        label="Annuler"
                        icon="pi pi-times"
                        class="p-button-text"
                        @click="showDeleteDialog = false"
                    />
                    <Button
                        label="Supprimer"
                        icon="pi pi-check"
                        class="p-button-danger"
                        :loading="deleting"
                        @click="deleteFormation"
                    />
                </template>
            </Dialog>
        </div>

        <Toast position="top-right" />
    </div>
</template>

<script>
import axios from 'axios';
import { FilterMatchMode } from 'primevue/api';

import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Dialog from 'primevue/dialog';
import Tag from 'primevue/tag';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';

import FormationAdd from '@/Components/Formations/FormationAdd.vue';
import FormationEdit from '@/Components/Formations/FormationEdit.vue';

export default {
    components: {
        Button,
        Column,
        DataTable,
        InputText,
        Dropdown,
        Dialog,
        Tag,
        Toast,
        FormationAdd,
        FormationEdit,
    },
    setup() {
        return { toast: useToast() };
    },
    data() {
        return {
            formations: [],
            loading: false,
            deleting: false,
            filters: {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                code: { value: null, matchMode: FilterMatchMode.CONTAINS },
                statut: { value: null, matchMode: FilterMatchMode.EQUALS },
            },
            rows: 20,
            currentPage: 1,
            totalRecords: 0,

            showAddDialog: false,
            showEditDialog: false,
            formationToEdit: null,

            showDeleteDialog: false,
            formationToDelete: null,
        };
    },
    created() {
        this.fetchFormations();
    },
    methods: {
        async fetchFormations() {
            this.loading = true;
            try {
                const response = await axios.get('/api/formations', {
                    params: {
                        page: this.currentPage,
                        per_page: this.rows,
                    },
                });

                if (!response.data?.formations?.data) {
                    throw new Error('Structure de réponse invalide pour /api/formations');
                }

                this.formations = (response.data.formations.data || []).map((f) => ({
                    ...f,
                    session_label:
                        f.session?.intitule_fr ||
                        f.session?.code ||
                        f.session_label ||
                        null,
                    specialite_label:
                        f.specialite?.nom_fr || f.specialite_label || null,
                    theme_label: f.theme?.nom_fr || f.theme_label || null,
                }));
                this.totalRecords = response.data.formations.total || 0;
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        'Impossible de charger les formations.',
                    life: 4000,
                });
            } finally {
                this.loading = false;
            }
        },
        clearFilter() {
            this.filters = {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                code: { value: null, matchMode: FilterMatchMode.CONTAINS },
                statut: { value: null, matchMode: FilterMatchMode.EQUALS },
            };
        },
        onPage(event) {
            this.currentPage = event.page + 1;
            this.rows = event.rows;
            this.fetchFormations();
        },
        onRowsUpdate(rows) {
            this.rows = rows;
            this.currentPage = 1;
            this.fetchFormations();
        },

        /* Ajout / Edition */
        openAddFormation() {
            this.formationToEdit = null;
            this.showAddDialog = true;
        },
        openEditFormation(formation) {
            this.formationToEdit = { ...formation };
            this.showEditDialog = true;
        },

        /* Suppression */
        confirmDeleteFormation(formation) {
            this.formationToDelete = formation;
            this.showDeleteDialog = true;
        },
        async deleteFormation() {
            if (!this.formationToDelete) return;
            this.deleting = true;
            try {
                await axios.delete(`/api/formations/${this.formationToDelete.id}`);
                this.formations = this.formations.filter(
                    (f) => f.id !== this.formationToDelete.id
                );
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Formation supprimée avec succès.',
                    life: 3000,
                });
                this.showDeleteDialog = false;
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        'Erreur lors de la suppression de la formation.',
                    life: 4000,
                });
            } finally {
                this.deleting = false;
            }
        },

        /* Callbacks enfants */
        onFormationSaved(formationPayload) {
            // si backend renvoie { formation: {...} }
            const f = formationPayload.formation || formationPayload;
            this.formations.push({
                ...f,
                session_label:
                    f.session?.intitule_fr || f.session?.code || f.session_label || null,
                specialite_label:
                    f.specialite?.nom_fr || f.specialite_label || null,
                theme_label: f.theme?.nom_fr || f.theme_label || null,
            });
        },
        onFormationUpdated(formationPayload) {
            const f = formationPayload.formation || formationPayload;
            const index = this.formations.findIndex((x) => x.id === f.id);
            if (index !== -1) {
                this.formations.splice(index, 1, {
                    ...f,
                    session_label:
                        f.session?.intitule_fr || f.session?.code || f.session_label || null,
                    specialite_label:
                        f.specialite?.nom_fr || f.specialite_label || null,
                    theme_label: f.theme?.nom_fr || f.theme_label || null,
                });
            }
        },
    },
};
</script>

<style scoped>
.search-field {
    min-width: 250px;
}
.font-arabic {
    font-family: 'Montserrat-Arabic-Regular', sans-serif;
}
</style>
