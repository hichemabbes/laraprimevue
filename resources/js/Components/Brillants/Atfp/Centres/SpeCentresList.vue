<template>
    <div class="surface-ground pt-2 px-1.5 pb-1.5" style="margin-top: 0">
        <!-- Header Section with Navbar -->
        <div
            class="surface-card p-2 border-round border-1 surface-border layout-navbar-container"
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
                        class="p-button-text p-button-plain nav-item"
                        @click="$router.push({ name: 'dashboard' })"
                    />
                    <Button
                        label="Spécialités-Centres"
                        icon="pi pi-list"
                        class="p-button-text p-button-plain nav-item"
                        disabled
                    />
                </div>
                <div class="flex gap-2">
                    <Button
                        icon="pi pi-refresh"
                        class="p-button-text p-button-plain nav-item"
                        @click="refreshTable"
                        v-tooltip="'Rafraîchir'"
                    />
                    <Button
                        icon="pi pi-file-export"
                        class="p-button-text p-button-plain nav-item"
                        @click="exportXls"
                        v-tooltip="'Exporter XLS'"
                    />
                </div>
            </div>
        </div>

        <!-- Main Content Section -->
        <div
            class="surface-card p-4 pt-2 border-round shadow-2 border-1 surface-border layout-content-container"
        >
            <!-- Table Header with Actions -->
            <div class="flex justify-content-between align-items-center mb-4">
                <div class="flex align-items-center gap-2">
                    <span class="p-input-icon-left search-field">
                        <InputText
                            v-model="filters['global'].value"
                            placeholder="Rechercher..."
                            class="search-input"
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
                v-model:expandedRows="expandedRows"
                v-model:filters="filters"
                showGridlines
                stripedRows
                :value="centres"
                dataKey="id"
                size="small"
                paginator
                :rows="20"
                filterDisplay="menu"
                :globalFilterFields="[
                    'nom_fr',
                    'nom_ar',
                    'code',
                    'adresse_fr',
                    'telephone_1',
                    'email'
                ]"
                :loading="loading"
                scrollable
                scrollHeight="750px"
                :pt="{ table: { style: 'min-width: 50rem' } }"
            >
                <template #empty>
                    <div v-if="!loading" class="text-center p-3">
                        <span class="single-line-text">Aucun centre trouvé.</span>
                    </div>
                    <div v-else class="text-center p-3">
                        <span class="single-line-text">Chargement en cours...</span>
                    </div>
                </template>
                <Column expander style="width: 3rem" />
                <Column
                    field="code"
                    header="Code"
                    sortable
                    style="min-width: 10rem"
                >
                    <template #body="{ data }">
                        <span class="single-line-text">{{ data.code }}</span>
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
                <Column
                    field="nom_fr"
                    header="Nom"
                    sortable
                    style="min-width: 20rem"
                >
                    <template #body="{ data }">
                        <div class="flex flex-column">
                            <span class="single-line-text">{{ data.nom_fr }}</span>
                            <span class="single-line-text arabic-normal text-secondary">{{ data.nom_ar }}</span>
                        </div>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter"
                            placeholder="Rechercher par nom"
                            @input="filterCallback"
                        />
                    </template>
                </Column>
                <Column
                    field="adresse_fr"
                    header="Adresse"
                    sortable
                    style="min-width: 20rem"
                >
                    <template #body="{ data }">
                        <span class="single-line-text">{{ data.adresse_fr }}</span>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter"
                            placeholder="Rechercher par adresse"
                            @input="filterCallback"
                        />
                    </template>
                </Column>
                <Column
                    field="telephone_1"
                    header="Contact"
                    sortable
                    style="min-width: 15rem"
                >
                    <template #body="{ data }">
                        <div class="flex flex-column">
                            <span class="single-line-text">{{ data.telephone_1 }}</span>
                            <span class="single-line-text text-secondary">{{ data.email }}</span>
                        </div>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter"
                            placeholder="Rechercher par contact"
                            @input="filterCallback"
                        />
                    </template>
                </Column>
                <Column header="Actions" style="min-width: 14rem">
                    <template #body="{ data }">
                        <div class="flex gap-2">
                            <Button
                                icon="pi pi-link"
                                severity="success"
                                text
                                @click="openAssociateForm(data)"
                                v-tooltip="'Associer Spécialités'"
                            />
                            <Button
                                icon="pi pi-eye"
                                severity="info"
                                text
                                @click="openCentreDetails(data)"
                                v-tooltip="'Voir Détails'"
                            />
                            <Button
                                icon="pi pi-lock"
                                severity="warning"
                                text
                                @click="pinCentre(data)"
                                v-tooltip="'Fixer Ligne'"
                            />
                        </div>
                    </template>
                </Column>

                <!-- Expanded Row Template -->
                <template #expansion="{ data }">
                    <div class="p-3 surface-50 border-round">
                        <div
                            class="flex justify-content-between align-items-center mb-3"
                        >
                            <div class="flex align-items-center gap-2">
                                <i class="pi pi-list text-primary-500" />
                                <span class="font-semibold single-line-text">
                                    Spécialités associées au centre {{ data.nom_fr }}
                                </span>
                            </div>
                            <Button
                                icon="pi pi-link"
                                label="Associer Spécialité"
                                class="p-button-sm p-button-success"
                                @click="openAssociateForm(data)"
                            />
                        </div>
                        <DataTable
                            :value="data.specialites || []"
                            size="small"
                            :loading="loadingSpecialites"
                            dataKey="id"
                            class="p-datatable-sm"
                        >
                            <template #empty>
                                <div class="text-center py-4">
                                    <i
                                        class="pi pi-inbox text-4xl text-400 mb-2"
                                    />
                                    <p class="text-600 single-line-text">
                                        Aucune spécialité associée
                                    </p>
                                </div>
                            </template>
                            <Column
                                field="code"
                                header="Code"
                                style="min-width: 10rem"
                            >
                                <template #body="{ data }">
                                    <span class="single-line-text">{{ data.code }}</span>
                                </template>
                            </Column>
                            <Column
                                field="nom_fr"
                                header="Nom"
                                style="min-width: 12rem"
                            >
                                <template #body="{ data }">
                                    <div class="flex flex-column">
                                        <span class="single-line-text">{{ data.nom_fr }}</span>
                                        <span class="single-line-text arabic-normal text-secondary">{{ data.nom_ar }}</span>
                                    </div>
                                </template>
                            </Column>
                            <Column
                                field="pivot.statut"
                                header="Statut (FR)"
                                style="min-width: 10rem"
                            >
                                <template #body="{ data }">
                                    <Tag
                                        :value="data.pivot.statut || 'Non défini'"
                                        :severity="
                                            data.pivot.statut === 'Actif'
                                                ? 'success'
                                                : data.pivot.statut === 'Inactif'
                                                ? 'danger'
                                                : 'warning'
                                        "
                                    />
                                </template>
                            </Column>
                            <Column
                                field="pivot.statut"
                                header="Statut (AR)"
                                style="min-width: 10rem"
                            >
                                <template #body="{ data }">
                                    <Tag
                                        :value="translatedStatut(data.pivot.statut)"
                                        :severity="
                                            data.pivot.statut === 'Actif'
                                                ? 'success'
                                                : data.pivot.statut === 'Inactif'
                                                ? 'danger'
                                                : 'warning'
                                        "
                                    />
                                </template>
                            </Column>
                            <Column
                                field="pivot.observation"
                                header="Observation"
                                style="min-width: 15rem"
                            >
                                <template #body="{ data }">
                                    <span class="single-line-text">{{ data.pivot.observation || '-' }}</span>
                                </template>
                            </Column>
                            <Column header="Actions" style="min-width: 10rem">
                                <template #body="{ data }">
                                    <div class="flex gap-2">
                                        <Button
                                            icon="pi pi-unlink"
                                            class="p-button-rounded p-button-danger p-button-text"
                                            @click="dissociateSpecialite(data.pivot.centre_id, data)"
                                            v-tooltip="'Désassocier'"
                                        />
                                        <Button
                                            icon="pi pi-times-circle"
                                            class="p-button-rounded p-button-danger p-button-text"
                                            @click="deleteSpecialite(data.pivot.centre_id, data)"
                                            v-tooltip="'Supprimer définitivement'"
                                        />
                                    </div>
                                </template>
                            </Column>
                        </DataTable>
                    </div>
                </template>
            </DataTable>
            <SpeCentreForm
                :visible="formVisible"
                :centreToEdit="centreToEdit"
                :specialiteToEdit="specialiteToEdit"
                @update:visible="formVisible = $event"
                @save="onSpecialiteSaved"
                @update="onSpecialiteUpdated"
                @close="
                    formVisible = false;
                    centreToEdit = null;
                    specialiteToEdit = null;
                "
            />
            <SpeCentreDetail
                :showViewDialog="detailVisible"
                :selectedCentre="selectedCentre"
                @update:showViewDialog="detailVisible = $event"
                @refresh="refreshTable"
                @edit="openEditForm"
                @close="
                    detailVisible = false;
                    selectedCentre = null;
                "
            />
            <Toast />
        </div>
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
import SplitButton from 'primevue/splitbutton';
import Tag from 'primevue/tag';
import Toast from 'primevue/toast';
import SpeCentreForm from '@/Components/Atfp/Centres/SpeCentreForm.vue';
import SpeCentreDetail from '@/Components/Atfp/Centres/SpeCentreDetails.vue';
import ExcelJS from 'exceljs';

export default {
    components: {
        Button,
        InputText,
        DataTable,
        Column,
        SplitButton,
        Tag,
        Toast,
        SpeCentreForm,
        SpeCentreDetail,
    },
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            centres: [],
            selectedCentres: null,
            filters: null,
            loading: false,
            loadingSpecialites: false,
            formVisible: false,
            detailVisible: false,
            centreToEdit: null,
            specialiteToEdit: null,
            selectedCentre: null,
            expandedRows: [],
            pinnedCentres: [],
            actions: [
                {
                    label: 'Associer Spécialités',
                    icon: 'pi pi-link',
                    command: () => this.associateSelectedCentres(),
                    disabled: () => !this.selectedCentres || this.selectedCentres.length !== 1,
                },
                {
                    label: 'Désassocier Spécialités',
                    icon: 'pi pi-unlink',
                    command: () => this.dissociateSelectedCentres(),
                    disabled: () => !this.selectedCentres || this.selectedCentres.length === 0,
                },
            ],
        };
    },
    computed: {
        translatedStatut() {
            return (statut) => {
                if (!statut) return 'غير محدد';
                return statut === 'Actif' ? 'نشط' : 'غير نشط';
            };
        },
    },
    async created() {
        this.initFilters();
        await this.fetchCentres();
    },
    methods: {
        initFilters() {
            this.filters = {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                code: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
                nom_fr: {
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
                adresse_fr: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.CONTAINS },
                    ],
                },
                telephone_1: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.CONTAINS },
                    ],
                },
                email: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.CONTAINS },
                    ],
                },
            };
        },
        async fetchCentres() {
            this.loading = true;
            try {
                const response = await axios.get('/api/get-centres');
                this.centres = response.data.data.map((centre) => ({
                    ...centre,
                    specialites: centre.specialites || [],
                }));
                console.log('Centres chargés:', this.centres);
            } catch (error) {
                let errorMessage = 'Erreur lors du chargement des centres.';
                if (error.response) {
                    errorMessage = error.response.data?.message || errorMessage;
                    if (error.response.status === 500) {
                        console.error('Erreur serveur:', error.response.data);
                    }
                } else if (error.request) {
                    errorMessage = 'Aucune réponse du serveur. Vérifiez votre connexion.';
                }
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: errorMessage,
                    life: 5000,
                });
                console.error('Erreur fetchCentres:', error);
            } finally {
                this.loading = false;
            }
        },
        async refreshTable() {
            await this.fetchCentres();
        },
        openAssociateForm(centre) {
            this.centreToEdit = centre;
            this.specialiteToEdit = null;
            this.formVisible = true;
        },
        openCentreDetails(centre) {
            const fullCentre = this.centres.find((c) => c.id === centre.id);
            this.selectedCentre = fullCentre || centre;
            this.detailVisible = true;
        },
        openEditForm(centre) {
            this.centreToEdit = centre;
            this.formVisible = true;
            this.detailVisible = false;
        },
        pinCentre(centre) {
            if (!this.pinnedCentres.includes(centre.id)) {
                this.pinnedCentres.push(centre.id);
                this.centres = [
                    ...this.centres.filter((c) =>
                        this.pinnedCentres.includes(c.id)
                    ),
                    ...this.centres.filter(
                        (c) => !this.pinnedCentres.includes(c.id)
                    ),
                ];
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: `Centre ${centre.nom_fr} épinglé.`,
                    life: 3000,
                });
            } else {
                this.pinnedCentres = this.pinnedCentres.filter(
                    (id) => id !== centre.id
                );
                this.refreshTable();
                this.toast.add({
                    severity: 'info',
                    summary: 'Info',
                    detail: `Centre ${centre.nom_fr} désépinglé.`,
                    life: 3000,
                });
            }
        },
        async dissociateSpecialite(centreId, specialite) {
            try {
                this.loadingSpecialites = true;
                await axios.delete(
                    `/api/centres/${centreId}/specialites/${specialite.id}`
                );
                const centreIndex = this.centres.findIndex(
                    (c) => c.id === centreId
                );
                if (centreIndex !== -1) {
                    const centre = { ...this.centres[centreIndex] };
                    centre.specialites = centre.specialites.filter(
                        (s) => s.id !== specialite.id
                    );
                    this.centres.splice(centreIndex, 1, centre);
                }
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Spécialité désassociée avec succès.',
                    life: 3000,
                });
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        'Erreur lors de la désassociation de la spécialité.',
                    life: 5000,
                });
            } finally {
                this.loadingSpecialites = false;
            }
        },
        async deleteSpecialite(centreId, specialite) {
            try {
                this.loadingSpecialites = true;
                // Supposons une API pour supprimer définitivement une spécialité
                await axios.delete(`/api/specialites/${specialite.id}`);
                const centreIndex = this.centres.findIndex(
                    (c) => c.id === centreId
                );
                if (centreIndex !== -1) {
                    const centre = { ...this.centres[centreIndex] };
                    centre.specialites = centre.specialites.filter(
                        (s) => s.id !== specialite.id
                    );
                    this.centres.splice(centreIndex, 1, centre);
                }
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Spécialité supprimée définitivement.',
                    life: 3000,
                });
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        'Erreur lors de la suppression de la spécialité.',
                    life: 5000,
                });
            } finally {
                this.loadingSpecialites = false;
            }
        },
        associateSelectedCentres() {
            if (this.selectedCentres?.length === 1) {
                this.openAssociateForm(this.selectedCentres[0]);
            } else {
                this.toast.add({
                    severity: 'warn',
                    summary: 'Attention',
                    detail: 'Veuillez sélectionner un seul centre pour associer des spécialités.',
                    life: 3000,
                });
            }
        },
        async dissociateSelectedCentres() {
            if (this.selectedCentres?.length > 0) {
                this.toast.add({
                    severity: 'warn',
                    summary: 'Non implémenté',
                    detail: "La désassociation multiple n'est pas encore prise en charge.",
                    life: 3000,
                });
            } else {
                this.toast.add({
                    severity: 'warn',
                    summary: 'Attention',
                    detail: 'Sélectionnez au moins un centre.',
                    life: 3000,
                });
            }
        },
        onSpecialiteSaved(centre) {
            const centreIndex = this.centres.findIndex(
                (c) => c.id === centre.id
            );
            if (centreIndex !== -1) {
                this.centres.splice(centreIndex, 1, {
                    ...centre,
                    specialites: centre.specialites || [],
                });
            } else {
                this.centres.push({
                    ...centre,
                    specialites: centre.specialites || [],
                });
            }
            this.formVisible = false;
            this.centreToEdit = null;
            this.refreshTable();
        },
        onSpecialiteUpdated(centre) {
            const centreIndex = this.centres.findIndex(
                (c) => c.id === centre.id
            );
            if (centreIndex !== -1) {
                this.centres.splice(centreIndex, 1, {
                    ...centre,
                    specialites: centre.specialites || [],
                });
            }
            this.formVisible = false;
            this.centreToEdit = null;
            this.specialiteToEdit = null;
            this.refreshTable();
        },
        async exportXls() {
            try {
                const workbook = new ExcelJS.Workbook();
                const worksheet = workbook.addWorksheet('Centres');

                worksheet.columns = [
                    { header: 'Code', key: 'code', width: 15 },
                    { header: 'Nom (FR)', key: 'nom_fr', width: 30 },
                    { header: 'Nom (AR)', key: 'nom_ar', width: 30 },
                    { header: 'Adresse', key: 'adresse_fr', width: 30 },
                    { header: 'Téléphone', key: 'telephone_1', width: 15 },
                    { header: 'Email', key: 'email', width: 20 },
                    { header: 'Spécialités', key: 'specialites', width: 50 },
                ];

                this.centres.forEach((centre) => {
                    worksheet.addRow({
                        code: centre.code,
                        nom_fr: centre.nom_fr,
                        nom_ar: centre.nom_ar,
                        adresse_fr: centre.adresse_fr,
                        telephone_1: centre.telephone_1,
                        email: centre.email,
                        specialites:
                            centre.specialites
                                ?.map((s) => `${s.nom_fr} (${s.nom_ar})`)
                                .join(', ') || '',
                    });
                });

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
                link.download = `centres_${new Date().toISOString().split('T')[0]}.xlsx`;
                link.click();
                URL.revokeObjectURL(link.href);

                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Exportation réussie.',
                    life: 3000,
                });
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: "Erreur lors de l'exportation XLS.",
                    life: 5000,
                });
            }
        },
        clearFilter() {
            this.initFilters();
        },
    },
};
</script>

<style scoped>
/* Police pour le texte arabe */
.arabic-normal {
    font-family: 'Amiri', Arial, sans-serif;
    font-weight: normal;
    direction: rtl;
}

/* Texte secondaire (nom_ar, email) */
.text-secondary {
    color: var(--text-color-secondary);
}

/* Main Navbar Container */
.layout-navbar-container {
    background: var(--surface-a);
    transition: all 0.3s ease;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

/* Navbar Item Styling */
.nav-item {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    border-radius: 6px;
    transition: background-color 0.2s ease, color 0.2s;
    color: var(--text-color);
    text-decoration: none;
}

.nav-item:hover {
    background-color: var(--surface-hover);
}

/* Main Content Container */
.layout-content-container {
    animation: contentEntry 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94) both;
    background: var(--surface-a);
}

/* Content Entry Animation */
@keyframes contentEntry {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Single Line Text */
.single-line-text {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
}

/* DataTable Styling */
.p-datatable {
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
}

.p-datatable-sm .p-datatable-tbody > tr > td {
    padding: 0.5rem 1rem;
}

.p-datatable .p-column-header-content {
    justify-content: center;
}

/* Search Input Styling */
.search-input {
    border-radius: 6px;
    transition: border-color 0.2s;
    padding: 0.5rem;
    background: var(--surface-a);
    color: var(--text-color);
}

.search-input:hover {
    border-color: var(--primary-color);
}

/* Button Styling */
.p-button {
    transition: background-color 0.2s, color 0.2s;
}

.p-button:hover {
    background-color: var(--surface-hover);
}

/* PrimeVue Theme Variables */
:root {
    --primary-color: #6366f1; /* Indigo */
    --primary-color-text: #ffffff;
    --surface-a: #ffffff;
    --surface-hover: #f5f5f5;
    --text-color: #1f2a44;
    --text-color-secondary: #6b7280;
    --surface-border: #e5e7eb;
}
</style>
