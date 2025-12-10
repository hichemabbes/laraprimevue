```vue
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
                        label="Calcul des Années"
                        icon="pi pi-calculator"
                        class="p-button-text p-button-plain"
                        disabled
                    />
                </div>
                <div class="flex gap-2">
                    <Button
                        icon="pi pi-refresh"
                        class="p-button-rounded p-button-text"
                        @click="fetchAnneesCalcul"
                        v-tooltip="'Rafraîchir'"
                    />
                </div>
            </div>
        </div>

        <!-- Main Content Section -->
        <div
            class="surface-card p-4 pt-2 border-round shadow-2 border-1 surface-border"
        >
            <!-- Table Header with Search -->
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
            </div>

            <!-- Main DataTable -->
            <DataTable
                v-model:expandedRows="expandedRows"
                v-model:filters="filters"
                :value="anneesCalcul"
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
                        <p class="text-600">Aucune année trouvée</p>
                    </div>
                </template>

                <Column expander style="width: 3rem" />

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

                <!-- Sub-table for Repos Formations -->
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
                        </div>

                        <DataTable
                            :value="data.repos_formations || []"
                            size="small"
                            :loading="loading"
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
                                field="type_repos_formation_fr"
                                header="Nature (FR)"
                                style="min-width: 15rem"
                            >
                                <template #body="{ data }">
                                    <div class="flex align-items-center gap-2">
                                        <i class="pi pi-tag text-primary-500" />
                                        {{
                                            data.type_repos_formation_fr ||
                                            'Non défini'
                                        }}
                                    </div>
                                </template>
                            </Column>

                            <Column
                                field="type_repos_formation_ar"
                                header="Nature (AR)"
                                style="min-width: 15rem"
                            >
                                <template #body="{ data }">
                                    <div class="flex align-items-center gap-2">
                                        <i class="pi pi-tag text-primary-500" />
                                        <span class="arabic-text">{{
                                            data.type_repos_formation_ar ||
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
                        </DataTable>
                    </div>
                </template>
            </DataTable>

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
import Toast from 'primevue/toast';

export default {
    name: 'AnneeCalculList',
    components: {
        Button,
        InputText,
        DataTable,
        Column,
        Tag,
        Chip,
        Badge,
        Toast,
    },
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            anneesCalcul: [],
            filters: null,
            loading: false,
            expandedRows: [],
        };
    },
    created() {
        this.initFilters();
        this.fetchAnneesCalcul();
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
                actif: 'Actif',
            };
            return statusLabels[statut] || statut || 'Annulé';
        },
        getReposSeverity(statut) {
            const statusSeverity = {
                a_venir: 'warning',
                en_cours: 'success',
                termine: 'info',
                annule: 'danger',
                actif: 'success',
            };
            return statusSeverity[statut] || 'secondary';
        },
        getReposStatutIcon(statut) {
            const statusIcons = {
                a_venir: 'pi pi-clock',
                en_cours: 'pi pi-check',
                termine: 'pi pi-check-circle',
                annule: 'pi pi-times',
                actif: 'pi pi-check',
            };
            return statusIcons[statut] || 'pi pi-info';
        },
        async fetchAnneesCalcul() {
            this.loading = true;
            try {
                const response = await axios.get('/api/annees-calcul');
                console.log('Réponse API /api/annees-calcul:', response.data);
                if (Array.isArray(response.data)) {
                    this.anneesCalcul = response.data;
                    if (
                        !this.anneesCalcul.some(
                            (annee) => annee.repos_formations?.length > 0
                        )
                    ) {
                        this.toast.add({
                            severity: 'info',
                            summary: 'Information',
                            detail: 'Aucune période de repos trouvée pour les années.',
                            life: 3000,
                        });
                    }
                } else {
                    throw new Error(
                        'Réponse API invalide : un tableau est attendu'
                    );
                }
            } catch (error) {
                console.error('Erreur lors du chargement des années:', {
                    status: error.response?.status,
                    message: error.message,
                    data: error.response?.data,
                });
                this.anneesCalcul = [];
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        'Erreur lors du chargement des années. Veuillez réessayer.',
                    life: 5000,
                });
            } finally {
                this.loading = false;
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
</style>
```
