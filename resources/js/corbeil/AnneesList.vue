<template>
    <div>
        <DataTable
            v-model:expandedRows="expandedRows"
            v-model:filters="filters"
            showGridlines
            stripedRows
            v-model:selection="selectedAnnees"
            :value="anneesFormation"
            dataKey="id"
            size="small"
            :rows="20"
            filterDisplay="menu"
            :globalFilterFields="[
                'intitule',
                'statut',
                'date_debut',
                'date_fin',
            ]"
            :loading="loading"
            scrollable
            scrollHeight="700px"
            :pt="{ table: { style: 'min-width: 50rem' } }"
        >
            <template #header>
                <div
                    class="flex justify-content-between mb-2 align-items-center"
                >
                    <div class="flex align-items-center gap-2">
                        <Button
                            icon="pi pi-plus"
                            severity="success"
                            size="small"
                            @click="openForm"
                        />
                        <span class="p-input-icon-right mr-2">
                            <InputText
                                v-model="filters['global'].value"
                                size="small"
                                placeholder="Recherche..."
                            />
                            <i class="pi pi-search" />
                        </span>
                        <Button
                            type="button"
                            icon="pi pi-times"
                            size="small"
                            severity="danger"
                            outlined
                            @click="clearFilter"
                        />
                    </div>
                    <div class="flex align-items-center gap-3">
                        <Button
                            text
                            icon="pi pi-minus"
                            label="Tout Réduire"
                            @click="collapseAll"
                        />
                    </div>
                </div>
            </template>

            <template #empty>
                <div v-if="!loading" class="text-center p-3">
                    Aucune année de formation trouvée.
                </div>
                <div v-else class="text-center p-3">Chargement en cours...</div>
            </template>

            <Column expander style="width: 5rem" />
            <Column
                selectionMode="multiple"
                headerStyle="width: 3rem"
                frozen
                class="font-bold"
            ></Column>
            <Column
                field="intitule"
                header="Intitulé"
                sortable
                style="width: 15rem"
            >
                <template #body="{ data }">
                    <span>{{ data.intitule }}</span>
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
                header="Date de début"
                sortable
                dataType="date"
                style="width: 15rem"
            >
                <template #body="{ data }">
                    {{ formatDate(data.date_debut) }}
                </template>
                <template #filter="{ filterModel, filterCallback }">
                    <Calendar
                        v-model="filterModel.value"
                        dateFormat="dd/mm/yy"
                        placeholder="jj/mm/aaaa"
                        mask="99/99/9999"
                        @date-select="filterCallback"
                    />
                </template>
            </Column>
            <Column
                field="date_fin"
                header="Date de fin"
                sortable
                dataType="date"
                style="width: 15rem"
            >
                <template #body="{ data }">
                    {{ formatDate(data.date_fin) }}
                </template>
                <template #filter="{ filterModel, filterCallback }">
                    <Calendar
                        v-model="filterModel.value"
                        dateFormat="dd/mm/yy"
                        placeholder="jj/mm/aaaa"
                        mask="99/99/9999"
                        @date-select="filterCallback"
                    />
                </template>
            </Column>
            <Column
                field="statut"
                header="Statut"
                sortable
                style="width: 15rem"
            >
                <template #body="{ data }">
                    <div class="flex justify-content-center align-items-center">
                        <Tag
                            :value="data.statut"
                            :severity="getSeverity(data.statut)"
                        />
                    </div>
                </template>
                <template #filter="{ filterModel, filterCallback }">
                    <Dropdown
                        v-model="filterModel.value"
                        :options="statuts"
                        placeholder="Sélectionner un statut"
                        showClear
                        @change="filterCallback"
                    >
                        <template #option="slotProps">
                            <Tag
                                :value="slotProps.option"
                                :severity="getSeverity(slotProps.option)"
                            />
                        </template>
                    </Dropdown>
                </template>
            </Column>
            <Column
                field="formation_weeks"
                header="Semaines de Formation"
                sortable
                style="width: 15rem"
            >
                <template #body="{ data }">
                    {{ data.formation_weeks }}
                </template>
            </Column>
            <Column
                field="vacation_weeks"
                header="Semaines Vacances"
                sortable
                style="width: 15rem"
            >
                <template #body="{ data }">
                    {{ data.vacation_weeks }}
                </template>
            </Column>
            <Column header="Actions" style="width: 15rem" frozen>
                <template #body="{ data }">
                    <div class="flex gap-2">
                        <Button
                            icon="pi pi-pencil"
                            severity="info"
                            text
                            @click="editAnnee(data)"
                        />
                        <Button
                            icon="pi pi-trash"
                            severity="danger"
                            text
                            @click="confirmDeleteAnnee(data)"
                        />
                    </div>
                </template>
            </Column>

            <!-- Sous-table des périodes de repos -->
            <template #expansion="{ data }">
                <div class="p-3 surface-100">
                    <div
                        class="flex justify-content-between align-items-center mb-2"
                    >
                        <h5 style="font-weight: bold">
                            <span style="color: #93c5fd"
                                >Périodes de repos pour l'année de formation
                            </span>
                            <span style="color: #ef4444">{{
                                data.intitule
                            }}</span>
                        </h5>
                        <Button
                            icon="pi pi-plus"
                            severity="success"
                            size="small"
                            @click="openPeriodeForm(data)"
                        />
                    </div>
                    <DataTable :value="data.periodes_repos" size="small">
                        <Column
                            field="titre_vacance"
                            header="Titre"
                            style="width: 15rem"
                        ></Column>
                        <Column
                            field="type"
                            header="Type"
                            style="width: 15rem"
                        ></Column>
                        <Column header="Date/Période" style="width: 15rem">
                            <template #body="{ data }">
                                {{
                                    data.type === 'jour_ferie'
                                        ? formatDate(data.date)
                                        : `${formatDate(data.date_debut)} - ${formatDate(data.date_fin)}`
                                }}
                            </template>
                        </Column>
                        <Column header="Actions" style="width: 15rem">
                            <template #body="{ data }">
                                <div class="flex gap-2">
                                    <Button
                                        icon="pi pi-pencil"
                                        severity="info"
                                        text
                                        @click="editPeriode(data)"
                                    />
                                    <Button
                                        icon="pi pi-trash"
                                        severity="danger"
                                        text
                                        @click="confirmDeletePeriode(data)"
                                    />
                                </div>
                            </template>
                        </Column>
                    </DataTable>
                </div>
            </template>
        </DataTable>

        <Form
            :visible="formVisible"
            :anneeToEdit="anneeToEdit"
            @update:visible="formVisible = $event"
            @save="handleSaveAnnee"
            @update="handleUpdateAnnee"
            @close="closeForm"
        />

        <PeriodeForm
            :visible="periodeFormVisible"
            :annee="selectedAnnee"
            :periodeToEdit="periodeToEdit"
            @update:visible="periodeFormVisible = $event"
            @save="handleSavePeriode"
            @update="handleUpdatePeriode"
            @close="closePeriodeForm"
        />

        <Dialog
            v-model:visible="deleteFormVisible"
            header="Confirmation de suppression"
            :modal="true"
            :style="{ width: '450px' }"
        >
            <div class="p-fluid">
                <p v-if="anneeToDelete">
                    Êtes-vous sûr de vouloir supprimer l’année de formation
                    <strong>{{ anneeToDelete.intitule }}</strong> ?
                </p>
            </div>
            <template #footer>
                <Button
                    label="Non"
                    icon="pi pi-times"
                    text
                    @click="deleteFormVisible = false"
                />
                <Button
                    label="Oui"
                    icon="pi pi-check"
                    text
                    @click="deleteAnnee"
                />
            </template>
        </Dialog>

        <Dialog
            v-model:visible="deletePeriodeFormVisible"
            header="Confirmation de suppression"
            :modal="true"
            :style="{ width: '450px' }"
        >
            <div class="p-fluid">
                <p v-if="periodeToDelete">
                    Êtes-vous sûr de vouloir supprimer la période de repos
                    <strong>{{ periodeToDelete.titre_vacance }}</strong> ?
                </p>
            </div>
            <template #footer>
                <Button
                    label="Non"
                    icon="pi pi-times"
                    text
                    @click="deletePeriodeFormVisible = false"
                />
                <Button
                    label="Oui"
                    icon="pi pi-check"
                    text
                    @click="deletePeriode"
                />
            </template>
        </Dialog>

        <Toast />
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
import Calendar from 'primevue/calendar';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import Form from '@/corbeil/AnneeForm.vue';
import PeriodeForm from '@/corbeil/PeriodeForm.vue';

export default {
    components: {
        Button,
        InputText,
        DataTable,
        Column,
        Tag,
        Dropdown,
        Calendar,
        Dialog,
        Toast,
        Form,
        PeriodeForm,
    },
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            anneesFormation: [],
            selectedAnnees: null,
            filters: null,
            statuts: ['En cours', 'Archivée', 'Prochaine'],
            formVisible: false,
            periodeFormVisible: false,
            deleteFormVisible: false,
            deletePeriodeFormVisible: false,
            anneeToEdit: null,
            anneeToDelete: null,
            selectedAnnee: null,
            periodeToEdit: null,
            periodeToDelete: null,
            loading: true,
            expandedRows: [],
        };
    },
    created() {
        this.initFilters();
        this.fetchAnneesFormation();
    },
    methods: {
        formatDate(date) {
            if (!date) return null;
            if (typeof date === 'string') date = new Date(date);
            if (!(date instanceof Date) || isNaN(date.getTime())) return null;
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            return `${day}/${month}/${year}`;
        },
        clearFilter() {
            this.initFilters();
        },
        initFilters() {
            this.filters = {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                intitule: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
                date_debut: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.DATE_IS },
                    ],
                },
                date_fin: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.DATE_IS },
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
        getSeverity(statut) {
            switch (statut) {
                case 'En cours':
                    return 'success';
                case 'Archivée':
                    return 'danger';
                case 'Prochaine':
                    return 'warning';
                default:
                    return 'secondary';
            }
        },
        openForm() {
            this.anneeToEdit = null;
            this.formVisible = true;
        },
        closeForm() {
            this.formVisible = false;
            this.anneeToEdit = null;
            this.fetchAnneesFormation();
        },
        openPeriodeForm(annee) {
            this.selectedAnnee = annee;
            this.periodeToEdit = null; // Réinitialiser pour création
            this.periodeFormVisible = true;
        },
        closePeriodeForm() {
            this.periodeFormVisible = false;
            this.selectedAnnee = null;
            this.periodeToEdit = null;
            this.fetchAnneesFormation();
        },

        collapseAll() {
            this.expandedRows = [];
        },
        async fetchAnneesFormation() {
            this.loading = true;
            try {
                const response = await axios.get('/api/annees-formation');
                this.anneesFormation = response.data.map((annee) => ({
                    ...annee,
                    date_debut: new Date(annee.date_debut),
                    date_fin: new Date(annee.date_fin),
                    statut: annee.statut,
                    formation_weeks: annee.formation_weeks,
                    vacation_weeks: annee.vacation_weeks,
                }));
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Erreur lors du chargement des années.',
                    life: 3000,
                });
            } finally {
                this.loading = false;
            }
        },
        editAnnee(annee) {
            this.anneeToEdit = { ...annee };
            this.formVisible = true;
        },
        editSelectedAnnee() {
            if (this.selectedAnnees?.length === 1) {
                this.editAnnee(this.selectedAnnees[0]);
            } else {
                this.toast.add({
                    severity: 'warn',
                    summary: 'Attention',
                    detail: 'Sélectionnez une seule année.',
                    life: 3000,
                });
            }
        },
        async handleSaveAnnee(newAnnee) {
            try {
                const response = await axios.post(
                    '/api/annees-formation',
                    newAnnee
                );
                this.anneesFormation.unshift(response.data);
                this.formVisible = false;
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Année ajoutée.',
                    life: 3000,
                });
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        'Erreur lors de l’ajout.',
                    life: 3000,
                });
            }
        },
        async handleUpdateAnnee(updatedAnnee) {
            try {
                const response = await axios.put(
                    `/api/annees-formation/${updatedAnnee.id}`,
                    updatedAnnee
                );
                const index = this.anneesFormation.findIndex(
                    (a) => a.id === updatedAnnee.id
                );
                if (index !== -1)
                    this.anneesFormation.splice(index, 1, response.data);
                this.formVisible = false;
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Année mise à jour.',
                    life: 3000,
                });
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Erreur lors de la mise à jour.',
                    life: 3000,
                });
            }
        },
        async handleSavePeriode(newPeriode) {
            try {
                const response = await axios.post(
                    '/api/periodes-repos',
                    newPeriode
                );
                this.closePeriodeForm();
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Période ajoutée.',
                    life: 3000,
                });
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.error ||
                        'Erreur lors de l’ajout.',
                    life: 3000,
                });
            }
        },
        async handleUpdatePeriode(updatedPeriode) {
            try {
                const response = await axios.put(
                    `/api/periodes-repos/${updatedPeriode.id}`,
                    updatedPeriode
                );
                const annee = this.anneesFormation.find(
                    (a) => a.id === updatedPeriode.annee_formation_id
                );
                if (annee) {
                    const index = annee.periodes_repos.findIndex(
                        (p) => p.id === updatedPeriode.id
                    );
                    if (index !== -1) {
                        annee.periodes_repos.splice(index, 1, response.data);
                    }
                }
                this.periodeFormVisible = false;
                this.periodeToEdit = null;
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Période mise à jour.',
                    life: 3000,
                });
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Erreur lors de la mise à jour.',
                    life: 3000,
                });
            }
        },
        confirmDeleteAnnee(annee) {
            this.anneeToDelete = annee;
            this.deleteFormVisible = true;
        },
        async deleteAnnee() {
            if (this.anneeToDelete) {
                try {
                    await axios.delete(
                        `/api/annees-formation/${this.anneeToDelete.id}`
                    );
                    this.anneesFormation = this.anneesFormation.filter(
                        (a) => a.id !== this.anneeToDelete.id
                    );
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Année supprimée.',
                        life: 3000,
                    });
                } catch (error) {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'Erreur lors de la suppression.',
                        life: 3000,
                    });
                } finally {
                    this.deleteFormVisible = false;
                    this.anneeToDelete = null;
                }
            }
        },
        async deleteSelectedAnnees() {
            if (this.selectedAnnees?.length > 0) {
                try {
                    await Promise.all(
                        this.selectedAnnees.map((a) =>
                            axios.delete(`/api/annees-formation/${a.id}`)
                        )
                    );
                    this.anneesFormation = this.anneesFormation.filter(
                        (a) =>
                            !this.selectedAnnees.some((sel) => sel.id === a.id)
                    );
                    this.selectedAnnees = null;
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Années supprimées.',
                        life: 3000,
                    });
                } catch (error) {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'Erreur lors de la suppression.',
                        life: 3000,
                    });
                }
            } else {
                this.toast.add({
                    severity: 'warn',
                    summary: 'Attention',
                    detail: 'Sélectionnez au moins une année.',
                    life: 3000,
                });
            }
        },
        editPeriode(periode) {
            this.periodeToEdit = { ...periode };
            this.selectedAnnee = this.anneesFormation.find(
                (a) => a.id === periode.annee_formation_id
            );
            this.periodeFormVisible = true;
        },
        confirmDeletePeriode(periode) {
            this.periodeToDelete = periode;
            this.deletePeriodeFormVisible = true;
        },
        async deletePeriode() {
            if (this.periodeToDelete) {
                try {
                    await axios.delete(
                        `/api/periodes-repos/${this.periodeToDelete.id}`
                    );
                    const annee = this.anneesFormation.find(
                        (a) => a.id === this.periodeToDelete.annee_formation_id
                    );
                    if (annee) {
                        annee.periodes_repos = annee.periodes_repos.filter(
                            (p) => p.id !== this.periodeToDelete.id
                        );
                    }
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Période supprimée.',
                        life: 3000,
                    });
                } catch (error) {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'Erreur lors de la suppression.',
                        life: 3000,
                    });
                } finally {
                    this.deletePeriodeFormVisible = false;
                    this.periodeToDelete = null;
                }
            }
        },
    },
};
</script>
