<template>
    <div class="container">
        <!-- Header Section -->
        <div class="header">
            <span class="centres-count">
                {{ trashedCentres.length }} centre(s) supprimé(s)
            </span>
            <Button
                v-if="trashedCentres.length > 0"
                label="Récupérer tous"
                icon="pi pi-undo"
                severity="secondary"
                raised
                @click="confirmRestoreAll"
                v-tooltip="'Restaurer tous les centres'"
            />
        </div>

        <!-- DataTable for Trashed Centres -->
        <div class="table-wrapper">
            <DataTable
                :value="trashedCentres"
                dataKey="id"
                size="small"
                paginator
                :rows="10"
                :rowsPerPageOptions="[10, 20, 50]"
                :loading="loading"
                scrollable
                scrollHeight="600px"
                :pt="{ table: { style: 'min-width: 80rem' } }"
                filterDisplay="menu"
                :globalFilterFields="[
                    'code',
                    'nom_fr',
                    'nom_ar',
                    'gouvernorat_fr',
                    'etat_fr',
                    'statut_fr',
                    'telephone_1',
                    'email',
                    'economat_fr',
                    'type_centre_fr',
                    'classe_fr',
                ]"
                v-model:filters="filters"
                class="datatable"
            >
                <template #empty>
                    <div class="empty-state">
                        <i
                            class="pi pi-inbox text-4xl text-color-secondary mb-2"
                        />
                        <p class="text-color-secondary">
                            Aucun centre supprimé trouvé.
                        </p>
                    </div>
                </template>

                <!-- Columns -->
                <Column header="Logo" style="width: 10rem">
                    <template #body="{ data }">
                        <img
                            v-if="data.logo"
                            :src="getLogoSrc(data.logo)"
                            alt="Logo du centre"
                            class="logo-image"
                            @error="onImageError"
                        />
                        <span v-else class="text-color-secondary">-</span>
                    </template>
                </Column>

                <Column
                    field="code"
                    header="Code"
                    sortable
                    style="width: 12rem"
                >
                    <template #body="{ data }">
                        <span class="flex align-items-center gap-2">
                            <i
                                class="pi pi-circle-fill"
                                :class="
                                    data.statut_fr === 'Fonctionnel'
                                        ? 'text-green-500'
                                        : 'text-red-500'
                                "
                                style="font-size: 0.5rem"
                            />
                            <span class="font-medium">{{ data.code }}</span>
                        </span>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter"
                            placeholder="Rechercher par code"
                            @input="filterCallback()"
                        />
                    </template>
                </Column>

                <Column
                    field="nom_fr"
                    header="Centre (FR)"
                    sortable
                    style="width: 30rem"
                >
                    <template #body="{ data }">
                        {{ data.nom_fr || '' }}
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter"
                            placeholder="Rechercher par nom (FR)"
                            @input="filterCallback()"
                        />
                    </template>
                </Column>

                <Column
                    field="nom_ar"
                    header="Centre (AR)"
                    sortable
                    style="width: 30rem"
                >
                    <template #body="{ data }">
                        <span class="font-arabic">{{ data.nom_ar }}</span>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter font-arabic"
                            dir="rtl"
                            placeholder="البحث بالاسم (العربية)"
                            @input="filterCallback()"
                        />
                    </template>
                </Column>

                <Column
                    field="gouvernorat_fr"
                    header="Gouvernorat"
                    sortable
                    style="width: 14rem"
                >
                    <template #body="{ data }">
                        <Tag
                            :value="data.gouvernorat_fr || '-'"
                            severity="info"
                        />
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-if="gouvernoratOptions.length > 0"
                            v-model="filterModel.value"
                            :options="gouvernoratOptions"
                            optionLabel="nom_fr"
                            optionValue="nom_fr"
                            placeholder="Sélectionner un gouvernorat"
                            class="p-column-filter"
                            :showClear="true"
                            @change="filterCallback()"
                        />
                        <InputText
                            v-else
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter"
                            placeholder="Filtre indisponible"
                            disabled
                        />
                    </template>
                </Column>

                <Column
                    field="telephone_1"
                    header="Téléphone"
                    sortable
                    style="width: 12rem"
                >
                    <template #body="{ data }">
                        <span>{{ data.telephone_1 || '-' }}</span>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter"
                            placeholder="Rechercher par téléphone"
                            @input="filterCallback()"
                        />
                    </template>
                </Column>

                <Column
                    field="email"
                    header="Email"
                    sortable
                    style="width: 14rem"
                >
                    <template #body="{ data }">
                        <span>{{ data.email || '-' }}</span>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter"
                            placeholder="Rechercher par email"
                            @input="filterCallback()"
                        />
                    </template>
                </Column>

                <Column
                    field="economat_fr"
                    header="Économat"
                    sortable
                    style="width: 14rem"
                >
                    <template #body="{ data }">
                        <Tag
                            :value="data.economat_fr || '-'"
                            severity="warning"
                        />
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-if="economatOptions.length > 0"
                            v-model="filterModel.value"
                            :options="economatOptions"
                            optionLabel="nom_fr"
                            optionValue="nom_fr"
                            placeholder="Sélectionner un économat"
                            class="p-column-filter"
                            :showClear="true"
                            @change="filterCallback()"
                        />
                        <InputText
                            v-else
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter"
                            placeholder="Filtre indisponible"
                            disabled
                        />
                    </template>
                </Column>

                <Column
                    field="type_centre_fr"
                    header="Type"
                    sortable
                    style="width: 14rem"
                >
                    <template #body="{ data }">
                        <Tag
                            :value="getTypeLabel(data.type_centre_fr)"
                            :severity="getTypeSeverity(data.type_centre_fr)"
                        />
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-if="typeCentreOptions.length > 0"
                            v-model="filterModel.value"
                            :options="typeCentreOptions"
                            optionLabel="nom_fr"
                            optionValue="nom_fr"
                            placeholder="Sélectionner un type"
                            class="p-column-filter"
                            :showClear="true"
                            @change="filterCallback()"
                        />
                        <InputText
                            v-else
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter"
                            placeholder="Filtre indisponible"
                            disabled
                        />
                    </template>
                </Column>

                <Column
                    field="classe_fr"
                    header="Classe"
                    sortable
                    style="width: 12rem"
                >
                    <template #body="{ data }">
                        <Tag
                            :value="data.classe_fr || '-'"
                            :severity="getClasseSeverity(data.classe_fr)"
                        />
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-if="classeOptions.length > 0"
                            v-model="filterModel.value"
                            :options="classeOptions"
                            optionLabel="nom_fr"
                            optionValue="nom_fr"
                            placeholder="Sélectionner une classe"
                            class="p-column-filter"
                            :showClear="true"
                            @change="filterCallback()"
                        />
                        <InputText
                            v-else
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter"
                            placeholder="Filtre indisponible"
                            disabled
                        />
                    </template>
                </Column>

                <Column
                    field="etat_fr"
                    header="État"
                    sortable
                    style="width: 12rem"
                >
                    <template #body="{ data }">
                        <Tag
                            :value="data.etat_fr || '-'"
                            :severity="
                                data.etat_fr === 'Ouvert' ? 'success' : 'danger'
                            "
                        />
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-if="etatOptions.length > 0"
                            v-model="filterModel.value"
                            :options="etatOptions"
                            optionLabel="nom_fr"
                            optionValue="nom_fr"
                            placeholder="Sélectionner un état"
                            class="p-column-filter"
                            :showClear="true"
                            @change="filterCallback()"
                        />
                        <InputText
                            v-else
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter"
                            placeholder="Filtre indisponible"
                            disabled
                        />
                    </template>
                </Column>

                <Column
                    field="statut_fr"
                    header="Statut"
                    sortable
                    style="width: 12rem"
                >
                    <template #body="{ data }">
                        <Tag
                            :value="data.statut_fr || '-'"
                            :severity="
                                data.statut_fr === 'Fonctionnel'
                                    ? 'success'
                                    : 'danger'
                            "
                        />
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-if="statutOptions.length > 0"
                            v-model="filterModel.value"
                            :options="statutOptions"
                            optionLabel="nom_fr"
                            optionValue="nom_fr"
                            placeholder="Sélectionner un statut"
                            class="p-column-filter"
                            :showClear="true"
                            @change="filterCallback()"
                        />
                        <InputText
                            v-else
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter"
                            placeholder="Filtre indisponible"
                            disabled
                        />
                    </template>
                </Column>

                <Column header="Actions" style="width: 10rem">
                    <template #body="{ data }">
                        <Button
                            icon="pi pi-undo"
                            severity="secondary"
                            raised
                            text
                            @click="restoreCentre(data)"
                            v-tooltip="'Restaurer'"
                        />
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- Dialog for Confirming Restore All -->
        <Dialog
            v-model:visible="restoreAllDialog"
            header="Confirmer la restauration de tous les centres"
            :modal="true"
            :style="{ width: '30rem' }"
            :breakpoints="{ '960px': '75vw', '640px': '90vw' }"
            class="restore-dialog"
        >
            <div class="dialog-content">
                <p>
                    Êtes-vous sûr de vouloir restaurer
                    <strong>tous les centres supprimés</strong> ?
                </p>
            </div>
            <template #footer>
                <Button
                    label="Annuler"
                    icon="pi pi-times"
                    severity="secondary"
                    raised
                    text
                    @click="restoreAllDialog = false"
                />
                <Button
                    label="Restaurer"
                    icon="pi pi-undo"
                    severity="secondary"
                    raised
                    @click="restoreAllCentres"
                    :loading="restoringAll"
                />
            </template>
        </Dialog>

        <Toast position="top-right" />
    </div>
</template>

<script>
import { FilterMatchMode } from 'primevue/api';
import axios from 'axios';
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import Dropdown from 'primevue/dropdown';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';

export default {
    name: 'TrashedCentres',
    components: {
        Button,
        Column,
        DataTable,
        InputText,
        Tag,
        Dropdown,
        Dialog,
        Toast,
    },
    emits: ['restored'], // Emit event to notify parent component
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            trashedCentres: [],
            loading: false,
            gouvernoratOptions: [],
            typeCentreOptions: [],
            classeOptions: [],
            economatOptions: [],
            etatOptions: [],
            statutOptions: [],
            lists: {},
            filters: {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                code: { value: null, matchMode: FilterMatchMode.CONTAINS },
                nom_fr: { value: null, matchMode: FilterMatchMode.CONTAINS },
                nom_ar: { value: null, matchMode: FilterMatchMode.CONTAINS },
                gouvernorat_fr: {
                    value: null,
                    matchMode: FilterMatchMode.EQUALS,
                },
                etat_fr: { value: null, matchMode: FilterMatchMode.EQUALS },
                statut_fr: { value: null, matchMode: FilterMatchMode.EQUALS },
                telephone_1: {
                    value: null,
                    matchMode: FilterMatchMode.CONTAINS,
                },
                email: { value: null, matchMode: FilterMatchMode.CONTAINS },
                economat_fr: { value: null, matchMode: FilterMatchMode.EQUALS },
                type_centre_fr: {
                    value: null,
                    matchMode: FilterMatchMode.EQUALS,
                },
                classe_fr: { value: null, matchMode: FilterMatchMode.EQUALS },
            },
            restoreAllDialog: false,
            restoringAll: false,
        };
    },
    async created() {
        await this.fetchTrashedCentres();
        await this.fetchOptions();
    },
    methods: {
        async fetchTrashedCentres() {
            this.loading = true;
            try {
                const response = await axios.get('/api/centres/trashed', {
                    headers: {
                        Authorization: `Bearer ${localStorage.getItem('token')}`,
                    },
                });
                this.trashedCentres = Array.isArray(response.data)
                    ? response.data
                    : [];
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Liste des centres supprimés chargée.',
                    life: 3000,
                });
            } catch (error) {
                console.error(
                    'Erreur lors du chargement des centres supprimés:',
                    error.response?.data || error
                );
                let errorMessage =
                    'Impossible de charger les centres supprimés.';
                if (error.response?.status === 401) {
                    errorMessage = 'Non autorisé. Veuillez vous connecter.';
                } else if (error.response?.status === 404) {
                    errorMessage =
                        'Endpoint non trouvé. Vérifiez la configuration des routes.';
                }
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: errorMessage,
                    life: 3000,
                });
            } finally {
                this.loading = false;
            }
        },
        async fetchOptions() {
            try {
                const response = await axios.get('/api/centres-with-options', {
                    headers: {
                        Authorization: `Bearer ${localStorage.getItem('token')}`,
                    },
                });
                this.lists = response.data.lists || {};
                this.gouvernoratOptions = this.lists['Gouvernorats'] || [];
                this.typeCentreOptions = this.lists['Types Centres'] || [];
                this.classeOptions = this.lists['Classes Centres'] || [];
                this.economatOptions = this.lists['Economats'] || [];
                this.etatOptions = this.lists['Etats Centres'] || [];
                this.statutOptions = this.lists['Statuts Centres'] || [];

                [
                    'Gouvernorats',
                    'Types Centres',
                    'Classes Centres',
                    'Economats',
                    'Etats Centres',
                    'Statuts Centres',
                ].forEach((list) => {
                    if (!this.lists[list] || this.lists[list].length === 0) {
                        this.toast.add({
                            severity: 'warn',
                            summary: 'Attention',
                            detail: `La liste "${list}" est indisponible.`,
                            life: 5000,
                        });
                    }
                });
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        'Impossible de charger les options.',
                    life: 3000,
                });
            }
        },
        async restoreCentre(centre) {
            if (!centre || !Number.isInteger(centre.id)) {
                console.error('ID de centre invalide:', centre);
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'ID de centre invalide.',
                    life: 3000,
                });
                return;
            }
            try {
                await axios.post(
                    `/api/centres/restore/${centre.id}`,
                    {},
                    {
                        headers: {
                            Authorization: `Bearer ${localStorage.getItem('token')}`,
                        },
                    }
                );
                this.trashedCentres = this.trashedCentres.filter(
                    (c) => c.id !== centre.id
                );
                this.$emit('restored'); // Notify parent to refresh centres
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Centre restauré.',
                    life: 3000,
                });
            } catch (error) {
                console.error(
                    'Erreur lors de la restauration du centre:',
                    error.response?.data || error
                );
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        'Impossible de restaurer le centre.',
                    life: 3000,
                });
            }
        },
        confirmRestoreAll() {
            this.restoreAllDialog = true;
        },
        async restoreAllCentres() {
            this.restoringAll = true;
            try {
                const response = await axios.post(
                    '/api/centres/restore-all',
                    {},
                    {
                        headers: {
                            Authorization: `Bearer ${localStorage.getItem('token')}`,
                        },
                    }
                );
                this.trashedCentres = [];
                this.restoreAllDialog = false;
                this.$emit('restored'); // Notify parent to refresh centres
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail:
                        response.data.message ||
                        'Tous les centres ont été restaurés.',
                    life: 3000,
                });
            } catch (error) {
                console.error(
                    'Erreur lors de la restauration de tous les centres:',
                    error.response?.data || error
                );
                let errorMessage = 'Erreur lors de la restauration.';
                if (error.response?.status === 401) {
                    errorMessage =
                        error.response.data.error ||
                        'Utilisateur non authentifié.';
                } else if (error.response?.status === 403) {
                    errorMessage =
                        'Accès non autorisé. Seuls les SuperAdmins peuvent effectuer cette action.';
                } else if (error.response?.status === 500) {
                    errorMessage =
                        'Erreur serveur. Veuillez réessayer plus tard.';
                }
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: errorMessage,
                    life: 3000,
                });
            } finally {
                this.restoringAll = false;
            }
        },
        getTypeLabel(type) {
            const typeObj = this.typeCentreOptions.find(
                (t) => t.nom_fr === type
            );
            return typeObj ? typeObj.nom_fr : type || '-';
        },
        getTypeSeverity(type) {
            if (!type) return 'secondary';
            switch (type) {
                case 'Centre Sectoriel de Formation':
                    return 'success';
                case "Centre de Formation et d'Apprentissage Professionnel":
                    return 'info';
                case 'Centre de Formation et de Promotion du Travail Indépendant':
                    return 'warning';
                case 'Centre de la Jeune Fille Rurale':
                    return 'danger';
                default:
                    return 'secondary';
            }
        },
        getClasseSeverity(classe) {
            if (!classe) return 'secondary';
            switch (classe) {
                case 'Classe A':
                    return 'success';
                case 'Classe B':
                    return 'info';
                case 'Classe C':
                    return 'secondary';
                default:
                    return 'secondary';
            }
        },
        getLogoSrc(logo) {
            if (logo && logo.startsWith('data:image/')) {
                return logo;
            }
            return logo || '';
        },
        onImageError(event) {
            this.toast.add({
                severity: 'warn',
                summary: 'Erreur',
                detail: "Impossible de charger l'image du logo.",
                life: 3000,
            });
        },
    },
};
</script>

<style scoped>
/* Importer la police arabe */
@font-face {
    font-family: 'Montserrat-Arabic';
    src: url('/fonts/Montserrat-Arabic-Regular.otf') format('opentype');
    font-weight: normal;
    font-style: normal;
}

/* Conteneur principal */
.container {
    padding: 1.5rem;
    max-width: 100%;
    margin: 0 auto;
    background: var(--surface-ground);
    border-radius: var(--border-radius);
}

/* En-tête */
.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding: 1rem;
    background: var(--surface-card);
    border-radius: var(--border-radius);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

/* Compteur de centres supprimés */
.centres-count {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-color);
    background: var(--surface-section);
    padding: 0.5rem 1rem;
    border-radius: 8px;
    border: 1px solid var(--surface-border);
    transition: background-color 0.3s ease;
}

.centres-count:hover {
    background: var(--surface-hover);
}

/* Wrapper du tableau pour le défilement horizontal */
.table-wrapper {
    overflow-x: auto;
    margin: 1rem 0;
    border-radius: var(--border-radius);
    background: var(--surface-card);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* DataTable */
:deep(.datatable) {
    background: var(--surface-card);
    border: none;
    border-radius: var(--border-radius);
    width: 100%;
    min-width: 80rem;
}

:deep(.p-datatable-header) {
    background: var(--surface-section);
    border-bottom: 1px solid var(--surface-border);
    padding: 1rem;
    font-weight: 600;
    color: var(--text-color);
}

:deep(.p-datatable-tbody > tr) {
    transition:
        background-color 0.3s ease,
        transform 0.2s ease;
}

:deep(.p-datatable-tbody > tr:hover) {
    background: var(--surface-hover);
    transform: translateY(-1px);
}

:deep(.p-column-filter) {
    width: 100%;
    font-size: 0.9rem;
    border-radius: var(--border-radius);
    padding: 0.5rem;
    background: var(--surface-input);
    border: 1px solid var(--surface-border);
    transition: border-color 0.3s ease;
}

:deep(.p-column-filter:focus) {
    border-color: var(--primary-color);
    outline: none;
}

/* État vide */
.empty-state {
    text-align: center;
    padding: 3rem;
    color: var(--text-color-secondary);
    background: var(--surface-section);
    border-radius: var(--border-radius);
    font-size: 1.1rem;
}

.empty-state i {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
}

/* Image du logo */
.logo-image {
    width: 2.5rem;
    height: 2.5rem;
    object-fit: contain;
    border-radius: 8px;
    border: 1px solid var(--surface-border);
    transition: transform 0.3s ease;
}

.logo-image:hover {
    transform: scale(1.1);
}

/* Police arabe */
:deep(.font-arabic) {
    font-family: 'Montserrat-Arabic', sans-serif;
    font-size: 1rem;
}

/* Dialogue */
.restore-dialog :deep(.p-dialog) {
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    background: var(--surface-card);
}

.restore-dialog :deep(.p-dialog-header) {
    background: var(--surface-section);
    padding: 1.5rem;
    font-weight: 600;
    color: var(--text-color);
    border-top-left-radius: 12px;
    border-top-right-radius: 12px;
}

.restore-dialog :deep(.p-dialog-content) {
    padding: 1.5rem;
    background: var(--surface-card);
}

.dialog-content {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    font-size: 1rem;
    color: var(--text-color);
}

.dialog-content strong {
    color: var(--primary-color);
}

.restore-dialog :deep(.p-dialog-footer) {
    padding: 1rem 1.5rem;
    background: var(--surface-section);
    border-bottom-left-radius: 12px;
    border-bottom-right-radius: 12px;
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
}

/* Boutons */
:deep(.p-button) {
    padding: 0.75rem 1.25rem;
    font-size: 0.9rem;
    border-radius: 8px;
    transition: all 0.3s ease;
}

:deep(.p-button:hover) {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

:deep(.p-button.p-button-secondary) {
    background: var(--surface-200);
    color: var(--text-color);
    border: 1px solid var(--surface-border);
}

:deep(.p-button.p-button-secondary:hover) {
    background: var(--surface-300);
}

/* Responsive Design */
@media (max-width: 960px) {
    .container {
        padding: 1rem;
    }

    .header {
        padding: 0.75rem;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .centres-count {
        font-size: 0.9rem;
        padding: 0.4rem 0.8rem;
    }

    .table-wrapper {
        margin: 0.5rem 0;
    }

    :deep(.datatable) {
        font-size: 0.85rem;
        min-width: 60rem;
    }

    :deep(.p-column-filter) {
        font-size: 0.8rem;
        padding: 0.4rem;
    }

    .logo-image {
        width: 2rem;
        height: 2rem;
    }

    .empty-state {
        padding: 2rem;
        font-size: 1rem;
    }

    .restore-dialog :deep(.p-dialog) {
        width: 90vw;
    }
}

@media (max-width: 640px) {
    .container {
        padding: 0.5rem;
    }

    .header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }

    .centres-count {
        width: 100%;
        text-align: center;
    }

    :deep(.p-button) {
        width: 100%;
        justify-content: center;
    }

    :deep(.p-datatable) {
        font-size: 0.8rem;
    }

    .empty-state i {
        font-size: 2rem;
    }
}
</style>
