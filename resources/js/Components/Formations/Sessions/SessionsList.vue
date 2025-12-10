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
                        label="Sessions de Formation"
                        icon="pi pi-calendar"
                        class="p-button-text p-button-plain p-button-sm"
                        disabled
                    />
                </div>

                <div class="flex gap-2">
                    <Button
                        icon="pi pi-refresh"
                        class="p-button-text p-button-sm p-button-rounded p-button-secondary"
                        @click="fetchSessions"
                        v-tooltip="'Rafraîchir'"
                    />
                    <Button
                        icon="pi pi-plus"
                        class="p-button-success p-button-sm p-button-rounded"
                        @click="openAddSession"
                        v-tooltip="'Ajouter une session'"
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

            <!-- DataTable Sessions -->
            <DataTable
                v-model:filters="filters"
                :value="sessions"
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
                    'intitule_fr',
                    'intitule_ar',
                    'annee_label',
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
                        <p class="text-600">Aucune session trouvée</p>
                    </div>
                </template>

                <Column field="annee_label" header="Année" sortable style="min-width: 10rem">
                    <template #body="{ data }">
                        <span>{{ data.annee_label || '-' }}</span>
                    </template>
                </Column>

                <Column field="code" header="Code" sortable style="min-width: 8rem">
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

                <Column field="intitule_fr" header="Intitulé (FR)" sortable style="min-width: 18rem">
                    <template #body="{ data }">
                        <span>{{ data.intitule_fr || '-' }}</span>
                    </template>
                </Column>

                <Column field="intitule_ar" header="Intitulé (AR)" sortable style="min-width: 18rem">
                    <template #body="{ data }">
                        <span class="font-arabic">{{ data.intitule_ar || '-' }}</span>
                    </template>
                </Column>

                <Column header="Période" style="min-width: 14rem">
                    <template #body="{ data }">
                        <span>
                            {{ formatDate(data.date_debut) }}
                            -
                            {{ formatDate(data.date_fin) }}
                        </span>
                    </template>
                </Column>

                <Column header="Capacité" style="min-width: 10rem">
                    <template #body="{ data }">
                        <span>
                            {{ data.capacite_min || 0 }} / {{ data.capacite_max || 0 }}
                        </span>
                    </template>
                </Column>

                <Column field="statut" header="Statut" sortable style="min-width: 12rem">
                    <template #body="{ data }">
                        <Tag
                            :value="formatStatut(data.statut)"
                            :severity="statutSeverity(data.statut)"
                        />
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-model="filterModel.value"
                            :options="statutOptions"
                            placeholder="Statut"
                            class="p-column-filter"
                            optionLabel="label"
                            optionValue="value"
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
                                v-tooltip="'Modifier la session'"
                                @click="openEditSession(data)"
                            />
                            <Button
                                icon="pi pi-trash"
                                class="p-button-text p-button-rounded p-button-danger"
                                v-tooltip="'Supprimer la session'"
                                @click="confirmDeleteSession(data)"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>

            <!-- Dialogs Ajout / Edition -->
            <SessionAdd
                :visible="showAddDialog"
                @update:visible="showAddDialog = $event"
                @save="onSessionSaved"
            />

            <SessionEdit
                :visible="showEditDialog"
                :sessionId="sessionToEdit ? sessionToEdit.id : null"
                :sessionData="sessionToEdit"
                @update:visible="showEditDialog = $event"
                @update="onSessionUpdated"
            />

            <!-- Dialog Suppression -->
            <Dialog
                v-model:visible="showDeleteDialog"
                header="Suppression de session"
                :modal="true"
                :style="{ width: '500px' }"
            >
                <p v-if="sessionToDelete">
                    Voulez-vous vraiment supprimer la session
                    <b>{{ sessionToDelete.intitule_fr || sessionToDelete.code }}</b> ?
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
                        @click="deleteSession"
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

import SessionAdd from '@/Components/Formations/Sessions/SessionAdd.vue';
import SessionEdit from '@/Components/Formations/Sessions/SessionEdit.vue';

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
        SessionAdd,
        SessionEdit,
    },
    setup() {
        return { toast: useToast() };
    },
    data() {
        return {
            sessions: [],
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
            sessionToEdit: null,

            showDeleteDialog: false,
            sessionToDelete: null,

            statutOptions: [
                { label: 'Planifiée', value: 'Planifiee' },
                { label: 'Ouverte aux inscriptions', value: 'Ouverte_inscription' },
                { label: 'En cours', value: 'En_cours' },
                { label: 'Terminée', value: 'Terminee' },
                { label: 'Annulée', value: 'Annulee' },
            ],
        };
    },
    created() {
        this.fetchSessions();
    },
    methods: {
        async fetchSessions() {
            this.loading = true;
            try {
                const response = await axios.get('/api/sessions', {
                    params: {
                        page: this.currentPage,
                        per_page: this.rows,
                    },
                });

                if (!response.data?.sessions?.data) {
                    throw new Error('Structure de réponse invalide pour /api/sessions');
                }

                this.sessions = (response.data.sessions.data || []).map((s) => ({
                    ...s,
                    annee_label: s.annee_formation?.intitule || s.annee_label || null,
                }));
                this.totalRecords = response.data.sessions.total || 0;
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        'Impossible de charger les sessions.',
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
        formatDate(date) {
            if (!date) return '-';
            return new Date(date).toLocaleDateString('fr-FR');
        },
        formatStatut(statut) {
            switch (statut) {
                case 'Planifiee':
                    return 'Planifiée';
                case 'Ouverte_inscription':
                    return 'Ouverte aux inscriptions';
                case 'En_cours':
                    return 'En cours';
                case 'Terminee':
                    return 'Terminée';
                case 'Annulee':
                    return 'Annulée';
                default:
                    return statut || '-';
            }
        },
        statutSeverity(statut) {
            switch (statut) {
                case 'Planifiee':
                    return 'info';
                case 'Ouverte_inscription':
                    return 'success';
                case 'En_cours':
                    return 'warning';
                case 'Terminee':
                    return 'success';
                case 'Annulee':
                    return 'danger';
                default:
                    return 'secondary';
            }
        },
        onPage(event) {
            this.currentPage = event.page + 1;
            this.rows = event.rows;
            this.fetchSessions();
        },
        onRowsUpdate(rows) {
            this.rows = rows;
            this.currentPage = 1;
            this.fetchSessions();
        },

        /* Ajout / Edition */
        openAddSession() {
            this.sessionToEdit = null;
            this.showAddDialog = true;
        },
        openEditSession(session) {
            this.sessionToEdit = { ...session };
            this.showEditDialog = true;
        },

        /* Suppression */
        confirmDeleteSession(session) {
            this.sessionToDelete = session;
            this.showDeleteDialog = true;
        },
        async deleteSession() {
            if (!this.sessionToDelete) return;
            this.deleting = true;
            try {
                await axios.delete(`/api/sessions/${this.sessionToDelete.id}`);
                this.sessions = this.sessions.filter(
                    (s) => s.id !== this.sessionToDelete.id
                );
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Session supprimée avec succès.',
                    life: 3000,
                });
                this.showDeleteDialog = false;
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        'Erreur lors de la suppression de la session.',
                    life: 4000,
                });
            } finally {
                this.deleting = false;
            }
        },

        /* Callbacks enfants */
        onSessionSaved(session) {
            // Si backend renvoie { session: {...} }, adapter ici
            const s = session.session || session;
            this.sessions.push({
                ...s,
                annee_label: s.annee_formation?.intitule || s.annee_label || null,
            });
        },
        onSessionUpdated(session) {
            const s = session.session || session;
            const index = this.sessions.findIndex((x) => x.id === s.id);
            if (index !== -1) {
                this.sessions.splice(index, 1, {
                    ...s,
                    annee_label: s.annee_formation?.intitule || s.annee_label || null,
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
