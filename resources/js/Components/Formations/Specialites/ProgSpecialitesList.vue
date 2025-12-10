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
                        label="Programmes (Spécialités)"
                        icon="pi pi-book"
                        class="p-button-text p-button-plain p-button-sm"
                        disabled
                    />
                </div>

                <div class="flex gap-2">
                    <Button
                        icon="pi pi-refresh"
                        class="p-button-text p-button-sm p-button-rounded p-button-secondary"
                        @click="fetchProgrammes"
                        v-tooltip="'Rafraîchir'"
                    />
                    <Button
                        icon="pi pi-plus"
                        class="p-button-success p-button-sm p-button-rounded"
                        @click="openAddProgramme"
                        v-tooltip="'Ajouter un programme'"
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

            <!-- DataTable Programmes -->
            <DataTable
                :value="programmes"
                v-model:filters="filters"
                v-model:expandedRows="expandedRows"
                dataKey="id"
                size="small"
                paginator
                :rows="rows"
                :loading="loading"
                stripedRows
                filterDisplay="menu"
                :globalFilterFields="['version', 'statut', 'specialite_nom']"
                class="p-datatable-sm border-1 surface-border"
                scrollable
                scrollHeight="700px"
                @row-expand="onRowExpand"
            >
                <template #empty>
                    <div class="py-4 text-center">
                        <i class="pi pi-inbox text-4xl text-400 mb-2" />
                        <p class="text-600">Aucun programme trouvé</p>
                    </div>
                </template>

                <!-- Colonne expansion -->
                <Column expander style="width: 3rem" />

                <!-- Spécialité -->
                <Column field="specialite_nom" header="Spécialité" sortable>
                    <template #body="{ data }">
                        <span>{{ data.specialite_nom || '-' }}</span>
                    </template>
                </Column>

                <!-- Version -->
                <Column field="version" header="Version" sortable>
                    <template #body="{ data }">
                        <span>{{ data.version || '-' }}</span>
                    </template>
                </Column>

                <!-- Dates -->
                <Column header="Période">
                    <template #body="{ data }">
                        <span>
                            {{ formatDate(data.date_debut) }}
                            -
                            {{ formatDate(data.date_fin) }}
                        </span>
                    </template>
                </Column>

                <!-- Statut -->
                <Column field="statut" header="Statut" sortable>
                    <template #body="{ data }">
                        <Tag
                            :value="data.statut || '-'"
                            :severity="data.statut === 'Actif' ? 'success' : 'danger'"
                        />
                    </template>
                </Column>

                <!-- Actions -->
                <Column header="Actions" style="min-width: 16rem">
                    <template #body="{ data }">
                        <div class="flex gap-2">
                            <Button
                                icon="pi pi-file-pdf"
                                class="p-button-text p-button-rounded p-button-help"
                                v-tooltip="'Documents du programme'"
                                @click="openProgrammeDocs(data)"
                            />
                            <Button
                                icon="pi pi-pencil"
                                class="p-button-text p-button-rounded p-button-warning"
                                v-tooltip="'Modifier le programme'"
                                @click="openEditProgramme(data)"
                            />
                            <Button
                                icon="pi pi-trash"
                                class="p-button-text p-button-rounded p-button-danger"
                                v-tooltip="'Supprimer le programme'"
                                @click="confirmDeleteProgramme(data)"
                            />
                        </div>
                    </template>
                </Column>

                <!-- Sous-table : modules -->
                <template #expansion="slotProps">
                    <div class="p-3 surface-50 border-round-lg">
                        <div class="flex justify-content-between align-items-center mb-3">
                            <div class="flex align-items-center gap-2">
                                <i class="pi pi-list"></i>
                                <span class="font-semibold">
                                    Modules du programme :
                                    <span class="text-primary">
                                        {{ slotProps.data.version || slotProps.data.id }}
                                    </span>
                                </span>
                            </div>

                            <Button
                                icon="pi pi-plus"
                                label="Ajouter un module"
                                class="p-button-sm p-button-rounded p-button-success"
                                @click="openAddModule(slotProps.data)"
                            />
                        </div>

                        <DataTable
                            :value="modulesMap[slotProps.data.id] || []"
                            dataKey="id"
                            size="small"
                            stripedRows
                            class="p-datatable-sm border-1 surface-border"
                            :loading="modulesLoadingId === slotProps.data.id"
                        >
                            <template #empty>
                                <div class="py-3 text-center">
                                    <i class="pi pi-info-circle text-2xl text-400 mb-2" />
                                    <p class="text-600">
                                        Aucun module pour ce programme.
                                    </p>
                                </div>
                            </template>

                            <Column field="code" header="Code" style="min-width: 8rem">
                                <template #body="{ data }">
                                    <span>{{ data.code || '-' }}</span>
                                </template>
                            </Column>

                            <Column field="titre_module_fr" header="Titre (FR)" style="min-width: 16rem">
                                <template #body="{ data }">
                                    <span>{{ data.titre_module_fr || '-' }}</span>
                                </template>
                            </Column>

                            <Column field="titre_module_ar" header="Titre (AR)" style="min-width: 16rem">
                                <template #body="{ data }">
                                    <span class="font-arabic">{{ data.titre_module_ar || '-' }}</span>
                                </template>
                            </Column>

                            <Column field="type_module_fr" header="Type" style="min-width: 12rem">
                                <template #body="{ data }">
                                    <span>{{ data.type_module_fr || '-' }}</span>
                                </template>
                            </Column>

                            <Column header="Heures" style="min-width: 12rem">
                                <template #body="{ data }">
                                    <span>
                                        T: {{ data.heures_theoriques || 0 }},
                                        P: {{ data.heures_pratiques || 0 }},
                                        E: {{ data.heures_evaluation || 0 }}
                                    </span>
                                </template>
                            </Column>

                            <Column field="statut" header="Statut" style="min-width: 8rem">
                                <template #body="{ data }">
                                    <Tag
                                        :value="data.statut || '-'"
                                        :severity="data.statut === 'Actif' ? 'success' : 'danger'"
                                    />
                                </template>
                            </Column>

                            <Column header="Actions" style="min-width: 14rem">
                                <template #body="{ data }">
                                    <div class="flex gap-2">
                                        <Button
                                            icon="pi pi-file-pdf"
                                            class="p-button-text p-button-rounded p-button-help"
                                            v-tooltip="'Documents du module'"
                                            @click="openModuleDocs(data)"
                                        />
                                        <Button
                                            icon="pi pi-pencil"
                                            class="p-button-text p-button-rounded p-button-warning"
                                            v-tooltip="'Modifier le module'"
                                            @click="openEditModule(slotProps.data, data)"
                                        />
                                        <Button
                                            icon="pi pi-trash"
                                            class="p-button-text p-button-rounded p-button-danger"
                                            v-tooltip="'Supprimer le module'"
                                            @click="confirmDeleteModule(slotProps.data, data)"
                                        />
                                    </div>
                                </template>
                            </Column>
                        </DataTable>
                    </div>
                </template>
            </DataTable>

            <!-- Dialogs Programmes -->
            <ProgSpecialiteAdd
                :visible="showAddProgramme"
                @update:visible="showAddProgramme = $event"
                @save="onProgrammeSaved"
            />

            <ProgSpecialiteEdit
                :visible="showEditProgramme"
                :programmeId="programmeToEdit ? programmeToEdit.id : null"
                :programmeData="programmeToEdit"
                @update:visible="showEditProgramme = $event"
                @update="onProgrammeUpdated"
            />

            <!-- Dialog Suppression Programme -->
            <Dialog
                v-model:visible="showDeleteProgrammeDialog"
                header="Suppression programme"
                :modal="true"
                :style="{ width: '500px' }"
            >
                <p v-if="programmeToDelete">
                    Voulez-vous vraiment supprimer le programme
                    <b>{{ programmeToDelete.version || programmeToDelete.id }}</b> ?
                </p>
                <template #footer>
                    <Button
                        label="Annuler"
                        icon="pi pi-times"
                        class="p-button-text"
                        @click="showDeleteProgrammeDialog = false"
                    />
                    <Button
                        label="Supprimer"
                        icon="pi pi-check"
                        class="p-button-danger"
                        @click="deleteProgramme"
                    />
                </template>
            </Dialog>

            <!-- Dialogs Modules -->
            <ModulesSpecialiteAdd
                :visible="showAddModuleDialog"
                :programmeId="currentProgrammeForModule ? currentProgrammeForModule.id : null"
                @update:visible="showAddModuleDialog = $event"
                @save="onModuleSaved"
            />

            <ModulesSpecialiteEdit
                :visible="showEditModuleDialog"
                :programmeId="currentProgrammeForModule ? currentProgrammeForModule.id : null"
                :moduleId="moduleToEdit ? moduleToEdit.id : null"
                :moduleData="moduleToEdit"
                @update:visible="showEditModuleDialog = $event"
                @update="onModuleUpdated"
            />

            <!-- Dialog suppression module -->
            <Dialog
                v-model:visible="showDeleteModuleDialog"
                header="Suppression module"
                :modal="true"
                :style="{ width: '500px' }"
            >
                <p v-if="moduleToDelete">
                    Voulez-vous vraiment supprimer le module
                    <b>{{ moduleToDelete.titre_module_fr || moduleToDelete.code }}</b> ?
                </p>
                <template #footer>
                    <Button
                        label="Annuler"
                        icon="pi pi-times"
                        class="p-button-text"
                        @click="showDeleteModuleDialog = false"
                    />
                    <Button
                        label="Supprimer"
                        icon="pi pi-check"
                        class="p-button-danger"
                        @click="deleteModule"
                    />
                </template>
            </Dialog>

            <!-- Dialog documents programme -->
            <Dialog
                v-model:visible="showProgrammeDocsDialog"
                header="Documents du programme"
                :modal="true"
                :style="{ width: '70vw', maxWidth: '900px' }"
            >
                <div v-if="loadingProgrammeDocs" class="py-4 text-center">
                    <i class="pi pi-spin pi-spinner text-2xl" />
                    <p class="mt-2 text-600">Chargement des documents...</p>
                </div>
                <div v-else>
                    <DataTable
                        :value="programmeDocs"
                        dataKey="id"
                        size="small"
                        stripedRows
                        class="p-datatable-sm border-1 surface-border"
                    >
                        <template #empty>
                            <div class="py-3 text-center">
                                <i class="pi pi-info-circle text-2xl text-400 mb-2" />
                                <p class="text-600">Aucun document pour ce programme.</p>
                            </div>
                        </template>

                        <Column field="titre_fr" header="Titre (FR)" />
                        <Column field="type_document_fr" header="Type" />
                        <Column field="statut" header="Statut">
                            <template #body="{ data }">
                                <Tag
                                    :value="data.statut || '-'"
                                    :severity="data.statut === 'Actif' ? 'success' : 'info'"
                                />
                            </template>
                        </Column>
                        <Column header="Actions" style="min-width: 10rem">
                            <template #body="{ data }">
                                <div class="flex gap-2">
                                    <Button
                                        icon="pi pi-eye"
                                        class="p-button-text p-button-rounded p-button-primary"
                                        v-tooltip="'Voir le document'"
                                        @click="viewDoc(data)"
                                    />
                                    <Button
                                        icon="pi pi-download"
                                        class="p-button-text p-button-rounded p-button-success"
                                        v-tooltip="'Télécharger le document'"
                                        @click="downloadDoc(data)"
                                    />
                                </div>
                            </template>
                        </Column>
                    </DataTable>
                </div>
                <template #footer>
                    <Button
                        label="Fermer"
                        icon="pi pi-times"
                        class="p-button-text"
                        @click="showProgrammeDocsDialog = false"
                    />
                </template>
            </Dialog>

            <!-- Dialog documents module -->
            <Dialog
                v-model:visible="showModuleDocsDialog"
                header="Documents du module"
                :modal="true"
                :style="{ width: '70vw', maxWidth: '900px' }"
            >
                <div v-if="loadingModuleDocs" class="py-4 text-center">
                    <i class="pi pi-spin pi-spinner text-2xl" />
                    <p class="mt-2 text-600">Chargement des documents...</p>
                </div>
                <div v-else>
                    <DataTable
                        :value="moduleDocs"
                        dataKey="id"
                        size="small"
                        stripedRows
                        class="p-datatable-sm border-1 surface-border"
                    >
                        <template #empty>
                            <div class="py-3 text-center">
                                <i class="pi pi-info-circle text-2xl text-400 mb-2" />
                                <p class="text-600">Aucun document pour ce module.</p>
                            </div>
                        </template>

                        <Column field="titre_fr" header="Titre (FR)" />
                        <Column field="type_document_fr" header="Type" />
                        <Column field="statut" header="Statut">
                            <template #body="{ data }">
                                <Tag
                                    :value="data.statut || '-'"
                                    :severity="data.statut === 'Actif' ? 'success' : 'info'"
                                />
                            </template>
                        </Column>
                        <Column header="Actions" style="min-width: 10rem">
                            <template #body="{ data }">
                                <div class="flex gap-2">
                                    <Button
                                        icon="pi pi-eye"
                                        class="p-button-text p-button-rounded p-button-primary"
                                        v-tooltip="'Voir le document'"
                                        @click="viewDoc(data)"
                                    />
                                    <Button
                                        icon="pi pi-download"
                                        class="p-button-text p-button-rounded p-button-success"
                                        v-tooltip="'Télécharger le document'"
                                        @click="downloadDoc(data)"
                                    />
                                </div>
                            </template>
                        </Column>
                    </DataTable>
                </div>
                <template #footer>
                    <Button
                        label="Fermer"
                        icon="pi pi-times"
                        class="p-button-text"
                        @click="showModuleDocsDialog = false"
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
import Dialog from 'primevue/dialog';
import Tag from 'primevue/tag';
import Toast from 'primevue/toast';

import ProgSpecialiteAdd from '@/Components/Formations/Specialites/ProgSpecialiteAdd.vue';
import ProgSpecialiteEdit from '@/Components/Formations/Specialites/ProgSpecialiteEdit.vue';
import ModulesSpecialiteAdd from '@/Components/Formations/Specialites/ModulesSpecialiteAdd.vue';
import ModulesSpecialiteEdit from '@/Components/Formations/Specialites/ModulesSpecialiteEdit.vue';

import { useToast } from 'primevue/usetoast';

export default {
    components: {
        Button,
        Column,
        DataTable,
        InputText,
        Dialog,
        Tag,
        Toast,
        ProgSpecialiteAdd,
        ProgSpecialiteEdit,
        ModulesSpecialiteAdd,
        ModulesSpecialiteEdit,
    },
    setup() {
        return { toast: useToast() };
    },
    data() {
        return {
            programmes: [],
            loading: false,
            filters: {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
            },
            rows: 20,
            expandedRows: [],
            // Programmes
            showAddProgramme: false,
            showEditProgramme: false,
            programmeToEdit: null,
            showDeleteProgrammeDialog: false,
            programmeToDelete: null,
            // Modules
            modulesMap: {}, // { programmeId: [modules] }
            modulesLoadingId: null,
            showAddModuleDialog: false,
            showEditModuleDialog: false,
            showDeleteModuleDialog: false,
            currentProgrammeForModule: null,
            moduleToEdit: null,
            moduleToDelete: null,
            // Documents programme
            showProgrammeDocsDialog: false,
            selectedProgrammeForDocs: null,
            programmeDocs: [],
            loadingProgrammeDocs: false,
            // Documents module
            showModuleDocsDialog: false,
            selectedModuleForDocs: null,
            moduleDocs: [],
            loadingModuleDocs: false,
        };
    },
    created() {
        this.fetchProgrammes();
    },
    methods: {
        async fetchProgrammes() {
            this.loading = true;
            try {
                const res = await axios.get('/api/programmes-specialites');
                this.programmes = (res.data || []).map((p) => ({
                    ...p,
                    specialite_nom: p.specialite?.nom_fr || p.specialite_nom || null,
                }));
            } catch (e) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Impossible de charger les programmes.',
                    life: 4000,
                });
            } finally {
                this.loading = false;
            }
        },
        clearFilter() {
            this.filters.global.value = null;
        },
        formatDate(date) {
            if (!date) return '-';
            return new Date(date).toLocaleDateString('fr-FR');
        },
        /* Programmes */
        openAddProgramme() {
            this.showAddProgramme = true;
        },
        openEditProgramme(programme) {
            this.programmeToEdit = { ...programme };
            this.showEditProgramme = true;
        },
        confirmDeleteProgramme(programme) {
            this.programmeToDelete = programme;
            this.showDeleteProgrammeDialog = true;
        },
        async deleteProgramme() {
            try {
                await axios.delete(`/api/programmes-specialites/${this.programmeToDelete.id}`);
                this.programmes = this.programmes.filter(
                    (p) => p.id !== this.programmeToDelete.id
                );
                delete this.modulesMap[this.programmeToDelete.id];
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Programme supprimé avec succès.',
                    life: 3000,
                });
                this.showDeleteProgrammeDialog = false;
            } catch (e) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Erreur lors de la suppression du programme.',
                    life: 4000,
                });
            }
        },
        onProgrammeSaved(programme) {
            this.programmes.push({
                ...programme,
                specialite_nom: programme.specialite?.nom_fr || programme.specialite_nom || null,
            });
        },
        onProgrammeUpdated(programme) {
            const index = this.programmes.findIndex((p) => p.id === programme.id);
            if (index !== -1) {
                this.programmes.splice(index, 1, {
                    ...programme,
                    specialite_nom: programme.specialite?.nom_fr || programme.specialite_nom || null,
                });
            }
        },
        /* Modules */
        async onRowExpand(event) {
            const programme = event.data;
            await this.loadModules(programme.id);
        },
        async loadModules(programmeId) {
            if (!programmeId) return;
            this.modulesLoadingId = programmeId;
            try {
                const res = await axios.get('/api/modules-specialites', {
                    params: { programme_specialite_id: programmeId },
                });
                this.modulesMap[programmeId] = res.data || [];
            } catch (e) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Impossible de charger les modules de ce programme.',
                    life: 4000,
                });
            } finally {
                this.modulesLoadingId = null;
            }
        },
        openAddModule(programme) {
            this.currentProgrammeForModule = programme;
            this.showAddModuleDialog = true;
        },
        openEditModule(programme, module) {
            this.currentProgrammeForModule = programme;
            this.moduleToEdit = { ...module };
            this.showEditModuleDialog = true;
        },
        confirmDeleteModule(programme, module) {
            this.currentProgrammeForModule = programme;
            this.moduleToDelete = module;
            this.showDeleteModuleDialog = true;
        },
        async deleteModule() {
            try {
                await axios.delete(`/api/modules-specialites/${this.moduleToDelete.id}`);
                const pid = this.currentProgrammeForModule.id;
                this.modulesMap[pid] = (this.modulesMap[pid] || []).filter(
                    (m) => m.id !== this.moduleToDelete.id
                );
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Module supprimé avec succès.',
                    life: 3000,
                });
                this.showDeleteModuleDialog = false;
            } catch (e) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Erreur lors de la suppression du module.',
                    life: 4000,
                });
            }
        },
        onModuleSaved(module) {
            const pid = module.programme_specialite_id;
            if (!this.modulesMap[pid]) {
                this.modulesMap[pid] = [];
            }
            this.modulesMap[pid].push(module);
        },
        onModuleUpdated(module) {
            const pid = module.programme_specialite_id;
            if (!this.modulesMap[pid]) return;
            const index = this.modulesMap[pid].findIndex((m) => m.id === module.id);
            if (index !== -1) {
                this.modulesMap[pid].splice(index, 1, module);
            }
        },

        /* Documents Programme */
        async openProgrammeDocs(programme) {
            this.selectedProgrammeForDocs = programme;
            this.showProgrammeDocsDialog = true;
            this.programmeDocs = [];
            await this.loadProgrammeDocs(programme.id);
        },
        async loadProgrammeDocs(programmeId) {
            if (!programmeId) return;
            this.loadingProgrammeDocs = true;
            try {
                const res = await axios.get('/api/documents-programmes-specialites', {
                    params: { programme_specialite_id: programmeId },
                });
                this.programmeDocs = res.data || [];
            } catch (e) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Impossible de charger les documents du programme.',
                    life: 4000,
                });
            } finally {
                this.loadingProgrammeDocs = false;
            }
        },

        /* Documents Module */
        async openModuleDocs(module) {
            this.selectedModuleForDocs = module;
            this.showModuleDocsDialog = true;
            this.moduleDocs = [];
            await this.loadModuleDocs(module.id);
        },
        async loadModuleDocs(moduleId) {
            if (!moduleId) return;
            this.loadingModuleDocs = true;
            try {
                const res = await axios.get('/api/documents-modules-specialites', {
                    params: { module_specialite_id: moduleId },
                });
                this.moduleDocs = res.data || [];
            } catch (e) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Impossible de charger les documents du module.',
                    life: 4000,
                });
            } finally {
                this.loadingModuleDocs = false;
            }
        },

        // URL de fichier compatible base64 + routes download du backend
        getFileUrl(doc) {
            if (doc.fichier_url) return doc.fichier_url;
            if (!doc.id) return null;

            // document de module
            if (doc.module_specialite_id) {
                return `/api/documents-modules-specialites/${doc.id}/download`;
            }

            // document de programme
            if (doc.programme_specialite_id) {
                return `/api/documents-programmes-specialites/${doc.id}/download`;
            }

            return null;
        },

        viewDoc(doc) {
            const url = this.getFileUrl(doc);
            if (!url) {
                this.toast.add({
                    severity: 'warn',
                    summary: 'Attention',
                    detail: 'Aucun fichier associé à ce document.',
                    life: 3000,
                });
                return;
            }
            window.open(url, '_blank', 'noopener');
        },
        downloadDoc(doc) {
            const url = this.getFileUrl(doc);
            if (!url) {
                this.toast.add({
                    severity: 'warn',
                    summary: 'Attention',
                    detail: 'Aucun fichier associé à ce document.',
                    life: 3000,
                });
                return;
            }
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', (doc.titre_fr || 'document') + '.pdf');
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
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
