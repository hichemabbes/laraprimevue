<template>
    <div class="surface-ground pt-2 px-1.5 pb-1.5" style="margin-top: 0">
        <!-- Header -->
        <div
            class="surface-card p-2 border-round border-1 surface-border"
            style="
                position: relative;
                top: -36px;
                box-shadow: none;
                margin-bottom: -32px;
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
                        label="Spécialités"
                        icon="pi pi-book"
                        class="p-button-text p-button-plain p-button-sm"
                        disabled
                    />
                </div>
                <div class="flex gap-2">
                    <Button
                        icon="pi pi-refresh"
                        class="p-button-text p-button-sm p-button-rounded p-button-secondary"
                        @click="fetchSpecialites"
                        v-tooltip="'Rafraîchir'"
                    />
                </div>
            </div>
        </div>

        <!-- Contenu principal -->
        <div
            class="surface-card p-4 pt-2 border-round shadow-2 border-1 surface-border"
        >
            <!-- En-tête table -->
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
                <Button
                    icon="pi pi-plus"
                    label=""
                    class="p-button-success p-button-sm p-button-rounded"
                    @click="openAddDialog"
                    v-tooltip="'Ajouter une spécialité'"
                />
            </div>

            <!-- DataTable spécialités -->
            <DataTable
                v-model:filters="filters"
                :value="specialites"
                dataKey="id"
                size="small"
                stripedRows
                paginator
                :rows="rows"
                :rowsPerPageOptions="[10, 20, 50]"
                filterDisplay="menu"
                :globalFilterFields="[
                    'code',
                    'nom_fr',
                    'nom_ar',
                    'diplome_fr',
                    'diplome_ar',
                    'statut'
                ]"
                :loading="loading"
                scrollable
                scrollHeight="700px"
                removableSort
                class="p-datatable-sm border-1 surface-border"
            >
                <template #empty>
                    <div class="text-center py-4">
                        <i class="pi pi-inbox text-4xl text-400 mb-2" />
                        <p class="text-600">Aucune spécialité trouvée</p>
                    </div>
                </template>

                <Column
                    field="code"
                    header="Code"
                    sortable
                    style="min-width: 10rem"
                >
                    <template #body="{ data }">
                        <span>{{ data.code || '-' }}</span>
                    </template>
                </Column>

                <Column
                    field="nom_fr"
                    header="Nom (FR)"
                    sortable
                    style="min-width: 20rem"
                    frozen
                >
                    <template #body="{ data }">
                        <span class="flex align-items-center gap-2">
                            <i
                                class="pi pi-circle-fill"
                                :class="
                                    data.statut === 'Actif'
                                        ? 'text-green-500'
                                        : 'text-red-500'
                                "
                                style="font-size: 0.5rem"
                            />
                            <span class="font-medium">{{ data.nom_fr || '-' }}</span>
                        </span>
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
                    header="Nom (AR)"
                    sortable
                    style="min-width: 20rem"
                >
                    <template #body="{ data }">
                        <span class="font-arabic">{{ data.nom_ar || '-' }}</span>
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
                    field="diplome_fr"
                    header="Diplôme (FR)"
                    style="min-width: 18rem"
                >
                    <template #body="{ data }">
                        <span>{{ data.diplome_fr || '-' }}</span>
                    </template>
                </Column>

                <Column
                    field="duree_formation"
                    header="Durée (années)"
                    style="min-width: 10rem"
                >
                    <template #body="{ data }">
                        <span>{{ data.duree_formation ?? '-' }}</span>
                    </template>
                </Column>

                <Column
                    field="heures_total"
                    header="Heures totales"
                    style="min-width: 10rem"
                >
                    <template #body="{ data }">
                        <span>{{ data.heures_total ?? '-' }}</span>
                    </template>
                </Column>

                <Column
                    field="est_homologue"
                    header="Homologué"
                    style="min-width: 10rem"
                >
                    <template #body="{ data }">
                        <Tag
                            :value="data.est_homologue ? 'Oui' : 'Non'"
                            :severity="data.est_homologue ? 'success' : 'secondary'"
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
                            :value="data.statut || '-'"
                            :severity="data.statut === 'Actif' ? 'success' : 'danger'"
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

                <Column header="Actions" style="min-width: 14rem" frozen>
                    <template #body="{ data }">
                        <div class="flex gap-2">
                            <Button
                                icon="pi pi-eye"
                                class="p-button-text p-button-rounded p-button-primary"
                                v-tooltip="'Voir les détails'"
                                @click="viewSpecialite(data)"
                            />
                            <Button
                                icon="pi pi-pencil"
                                class="p-button-text p-button-rounded p-button-warning"
                                v-tooltip="'Modifier'"
                                @click="openEditDialog(data)"
                            />
                            <Button
                                icon="pi pi-trash"
                                class="p-button-text p-button-rounded p-button-danger"
                                v-tooltip="'Supprimer'"
                                @click="confirmDelete(data)"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>

            <!-- Dialog Ajout -->
            <SpecialiteAdd
                :visible="showAddDialog"
                @update:visible="showAddDialog = $event"
                @save="onSpecialiteSaved"
                @close="showAddDialog = false"
            />

            <!-- Dialog Edition -->
            <SpecialiteEdit
                :visible="showEditDialog"
                :specialiteId="specialiteToEdit ? specialiteToEdit.id : null"
                :specialiteData="specialiteToEdit"
                @update:visible="showEditDialog = $event"
                @update="onSpecialiteUpdated"
                @close="showEditDialog = false"
            />

            <!-- Dialog Détail simple (lecture seule) -->
            <Dialog
                v-model:visible="showViewDialog"
                header="Détails de la spécialité"
                :modal="true"
                :style="{ width: '800px' }"
            >
                <div v-if="selectedSpecialite" class="grid">
                    <div class="col-12 md:col-6 mb-3">
                        <h4>Informations générales</h4>
                        <p><b>Code :</b> {{ selectedSpecialite.code || '-' }}</p>
                        <p><b>Nom (FR) :</b> {{ selectedSpecialite.nom_fr || '-' }}</p>
                        <p class="font-arabic">
                            <b>Nom (AR) :</b>
                            {{ selectedSpecialite.nom_ar || '-' }}
                        </p>
                        <p><b>Diplôme (FR) :</b> {{ selectedSpecialite.diplome_fr || '-' }}</p>
                        <p class="font-arabic">
                            <b>Diplôme (AR) :</b>
                            {{ selectedSpecialite.diplome_ar || '-' }}
                        </p>
                        <p><b>Niveau d'entrée (FR) :</b> {{ selectedSpecialite.niveau_fr || '-' }}</p>
                        <p class="font-arabic">
                            <b>Niveau d'entrée (AR) :</b>
                            {{ selectedSpecialite.niveau_ar || '-' }}
                        </p>
                    </div>
                    <div class="col-12 md:col-6 mb-3">
                        <h4>Volumes & Homologation</h4>
                        <p><b>Durée (années) :</b> {{ selectedSpecialite.duree_formation ?? '-' }}</p>
                        <p><b>Heures ET :</b> {{ selectedSpecialite.heures_et ?? '-' }}</p>
                        <p><b>Heures EG :</b> {{ selectedSpecialite.heures_eg ?? '-' }}</p>
                        <p><b>Heures totales :</b> {{ selectedSpecialite.heures_total ?? '-' }}</p>
                        <p>
                            <b>Homologuée :</b>
                            {{ selectedSpecialite.est_homologue ? 'Oui' : 'Non' }}
                        </p>
                        <p>
                            <b>Réf. homologation :</b>
                            {{ selectedSpecialite.reference_homologation || '-' }}
                        </p>
                        <p><b>Statut :</b> {{ selectedSpecialite.statut || '-' }}</p>
                    </div>
                    <div class="col-12">
                        <h4>Description (FR)</h4>
                        <p>{{ selectedSpecialite.description_fr || '-' }}</p>
                    </div>
                    <div class="col-12">
                        <h4 class="font-arabic">الوصف (العربية)</h4>
                        <p class="font-arabic">
                            {{ selectedSpecialite.description_ar || '-' }}
                        </p>
                    </div>
                </div>
                <template #footer>
                    <Button
                        label="Fermer"
                        icon="pi pi-times"
                        class="p-button-text"
                        @click="showViewDialog = false"
                    />
                </template>
            </Dialog>

            <!-- Dialog suppression -->
            <Dialog
                v-model:visible="showDeleteDialog"
                header="Confirmation de suppression"
                :modal="true"
                :style="{ width: '500px' }"
            >
                <div class="confirmation-content flex align-items-center gap-3">
                    <i class="pi pi-exclamation-triangle text-4xl text-red-500" />
                    <span v-if="specialiteToDelete">
                        Voulez-vous vraiment supprimer la spécialité
                        <b>{{ specialiteToDelete.nom_fr || specialiteToDelete.nom_ar }}</b> ?
                    </span>
                </div>
                <template #footer>
                    <div class="mt-4 flex justify-end gap-2">
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
                            @click="deleteSpecialite"
                        />
                    </div>
                </template>
            </Dialog>
        </div>
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
import { useRouter } from 'vue-router';



export default {
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
    setup() {
        const toast = useToast();
        const router = useRouter();
        return { toast, router };
    },
    data() {
        return {
            specialites: [],
            loading: false,
            filters: {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                nom_fr: { value: null, matchMode: FilterMatchMode.CONTAINS },
                nom_ar: { value: null, matchMode: FilterMatchMode.CONTAINS },
                statut: { value: null, matchMode: FilterMatchMode.EQUALS },
            },
            rows: 20,
            showAddDialog: false,
            showEditDialog: false,
            specialiteToEdit: null,
            showViewDialog: false,
            selectedSpecialite: null,
            showDeleteDialog: false,
            specialiteToDelete: null,
            deleting: false,
        };
    },
    created() {
        this.fetchSpecialites();
    },
    methods: {
        async fetchSpecialites() {
            this.loading = true;
            try {
                const response = await axios.get('/api/specialites');
                this.specialites = response.data || [];
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        'Impossible de charger les spécialités.',
                    life: 3000,
                });
            } finally {
                this.loading = false;
            }
        },
        clearFilter() {
            this.filters = {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                nom_fr: { value: null, matchMode: FilterMatchMode.CONTAINS },
                nom_ar: { value: null, matchMode: FilterMatchMode.CONTAINS },
                statut: { value: null, matchMode: FilterMatchMode.EQUALS },
            };
        },
        openAddDialog() {
            this.showAddDialog = true;
        },
        openEditDialog(specialite) {
            this.specialiteToEdit = { ...specialite };
            this.showEditDialog = true;
        },
        viewSpecialite(specialite) {
            this.selectedSpecialite = { ...specialite };
            this.showViewDialog = true;
        },
        confirmDelete(specialite) {
            this.specialiteToDelete = specialite;
            this.showDeleteDialog = true;
        },
        async deleteSpecialite() {
            if (!this.specialiteToDelete) return;
            this.deleting = true;
            try {
                await axios.delete(`/api/specialites/${this.specialiteToDelete.id}`);
                this.specialites = this.specialites.filter(
                    (s) => s.id !== this.specialiteToDelete.id
                );
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Spécialité supprimée avec succès.',
                    life: 3000,
                });
                this.showDeleteDialog = false;
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        'Erreur lors de la suppression.',
                    life: 3000,
                });
            } finally {
                this.deleting = false;
            }
        },
        onSpecialiteSaved(specialite) {
            this.specialites.push(specialite);
            this.showAddDialog = false;
        },
        onSpecialiteUpdated(specialite) {
            const index = this.specialites.findIndex((s) => s.id === specialite.id);
            if (index !== -1) {
                this.specialites.splice(index, 1, specialite);
            }
            this.showEditDialog = false;
        },
    },
};
</script>

<style scoped>
@font-face {
    font-family: 'Montserrat-Arabic-Regular';
    src: url('/fonts/Montserrat-Arabic-Regular.otf') format('opentype');
    font-weight: normal;
    font-style: normal;
}

.search-field {
    min-width: 250px;
}

.font-arabic,
:deep(.p-inputtext[dir='rtl']) {
    font-family: 'Montserrat-Arabic-Regular', sans-serif;
}
</style>
