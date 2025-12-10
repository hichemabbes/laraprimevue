<template>
    <div class="card">
        <Toolbar class="mb-4">
            <template #start>
                <Button
                    label="Nouveau"
                    icon="pi pi-plus"
                    class="mr-2"
                    @click="showAddModuleDialog"
                />
                <Button
                    label="Supprimer"
                    icon="pi pi-trash"
                    class="p-button-danger"
                    :disabled="!selectedModules || !selectedModules.length"
                    @click="confirmDeleteSelected"
                />
            </template>
            <template #end>
                <FileUpload
                    mode="basic"
                    accept=".csv"
                    :maxFileSize="1000000"
                    label="Importer"
                    chooseLabel="Importer"
                    class="mr-2"
                    @select="importCSV"
                />
                <Button
                    label="Exporter"
                    icon="pi pi-upload"
                    class="p-button-help"
                    @click="exportCSV"
                />
            </template>
        </Toolbar>

        <DataTable
            ref="dt"
            :value="modules"
            v-model:selection="selectedModules"
            :paginator="true"
            :rows="10"
            :rowsPerPageOptions="[5, 10, 25]"
            paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
            currentPageReportTemplate="{first} à {last} de {totalRecords} modules"
            :filters="filters"
            :loading="loading"
            dataKey="id"
            responsiveLayout="scroll"
            :globalFilterFields="['code', 'titre_module', 'statut']"
        >
            <template #header>
                <div class="flex justify-content-between align-items-center">
                    <h5 class="m-0">Liste des Modules Généraux</h5>
                    <span class="p-input-icon-left">
                        <i class="pi pi-search" />
                        <InputText
                            v-model="filters['global'].value"
                            placeholder="Rechercher..."
                        />
                    </span>
                </div>
            </template>

            <Column selectionMode="multiple" headerStyle="width: 3rem"></Column>
            <Column field="code" header="Code" sortable filter></Column>
            <Column
                field="titre_module"
                header="Titre Module"
                sortable
                filter
            ></Column>
            <Column
                field="heures_theoriques"
                header="H. Théoriques"
                sortable
            ></Column>
            <Column
                field="heures_pratiques"
                header="H. Pratiques"
                sortable
            ></Column>
            <Column
                field="heures_evaluation"
                header="H. Évaluation"
                sortable
            ></Column>
            <Column field="statut" header="Statut" sortable filter>
                <template #body="{ data }">
                    <Tag
                        :value="data.statut"
                        :severity="getStatusSeverity(data.statut)"
                    />
                </template>
            </Column>
            <Column header="Actions" headerStyle="width: 10rem">
                <template #body="{ data }">
                    <Button
                        icon="pi pi-eye"
                        class="p-button-rounded p-button-info p-button-text mr-2"
                        @click="showDetails(data)"
                    />
                    <Button
                        icon="pi pi-pencil"
                        class="p-button-rounded p-button-success p-button-text mr-2"
                        @click="showEditModuleDialog(data)"
                    />
                    <Button
                        icon="pi pi-trash"
                        class="p-button-rounded p-button-danger p-button-text"
                        @click="confirmDelete(data)"
                    />
                </template>
            </Column>
        </DataTable>

        <!-- Add/Edit DocumentProgrammeSpecialite Dialog -->
        <ModuleGeneralForm
            :visible="showModuleDialog"
            :moduleToEdit="moduleToEdit"
            @update:visible="showModuleDialog = $event"
            @close="showModuleDialog = false"
            @save="saveModule"
            @update="updateModule"
        />

        <!-- Details Dialog -->
        <Dialog
            :visible="showDetailsDialog"
            @update:visible="showDetailsDialog = $event"
            :modal="true"
            :style="{ width: '600px' }"
            :breakpoints="{ '960px': '75vw', '640px': '90vw' }"
            :pt="{
                root: 'border-none',
                mask: { style: 'backdrop-filter: blur(2px)' },
                header: { class: 'surface-50 border-bottom-1 surface-border' },
                content: { class: 'surface-50 py-0' },
                footer: { class: 'surface-50 border-top-1 surface-border' },
            }"
        >
            <template #header>
                <div class="flex align-items-center gap-2">
                    <i class="pi pi-eye text-primary"></i>
                    <span class="font-bold text-lg"
                        >Détails du Module Général</span
                    >
                </div>
            </template>

            <div v-if="selectedModule" class="flex flex-column gap-4">
                <div class="field">
                    <label class="block font-medium mb-2">Code</label>
                    <InputText
                        :value="selectedModule.code"
                        class="w-full"
                        disabled
                    />
                </div>
                <div class="field">
                    <label class="block font-medium mb-2">Titre Module</label>
                    <InputText
                        :value="selectedModule.titre_module"
                        class="w-full"
                        disabled
                    />
                </div>
                <div class="grid">
                    <div class="col-12 md:col-4 field">
                        <label class="block font-medium mb-2"
                            >H. Théoriques</label
                        >
                        <InputText
                            :value="selectedModule.heures_theoriques || 0"
                            class="w-full"
                            disabled
                        />
                    </div>
                    <div class="col-12 md:col-4 field">
                        <label class="block font-medium mb-2"
                            >H. Pratiques</label
                        >
                        <InputText
                            :value="selectedModule.heures_pratiques || 0"
                            class="w-full"
                            disabled
                        />
                    </div>
                    <div class="col-12 md:col-4 field">
                        <label class="block font-medium mb-2"
                            >H. Évaluation</label
                        >
                        <InputText
                            :value="selectedModule.heures_evaluation || 0"
                            class="w-full"
                            disabled
                        />
                    </div>
                </div>
                <div class="field">
                    <label class="block font-medium mb-2">Description</label>
                    <Textarea
                        :value="selectedModule.description || '-'"
                        class="w-full"
                        rows="4"
                        disabled
                    />
                </div>
                <div class="field">
                    <label class="block font-medium mb-2">Observation</label>
                    <Textarea
                        :value="selectedModule.observation || '-'"
                        class="w-full"
                        rows="4"
                        disabled
                    />
                </div>
                <div class="field">
                    <label class="block font-medium mb-2">Statut</label>
                    <InputText
                        :value="selectedModule.statut || '-'"
                        class="w-full"
                        disabled
                    />
                </div>
            </div>

            <template #footer>
                <Button
                    label="Fermer"
                    icon="pi pi-times"
                    class="p-button-text"
                    @click="showDetailsDialog = false"
                />
            </template>
        </Dialog>

        <!-- Confirm Delete Dialog -->
        <Dialog
            :visible="deleteModuleDialog"
            @update:visible="deleteModuleDialog = $event"
            :modal="true"
            header="Confirmer la suppression"
            :style="{ width: '450px' }"
            :breakpoints="{ '960px': '75vw', '640px': '90vw' }"
        >
            <div class="flex align-items-center gap-2">
                <i class="pi pi-exclamation-triangle text-red-500 text-2xl"></i>
                <span v-if="moduleToDelete"
                    >Êtes-vous sûr de vouloir supprimer le module "{{
                        moduleToDelete.titre_module
                    }}" ?</span
                >
            </div>
            <template #footer>
                <Button
                    label="Non"
                    icon="pi pi-times"
                    class="p-button-text"
                    @click="deleteModuleDialog = false"
                />
                <Button
                    label="Oui"
                    icon="pi pi-check"
                    class="p-button-danger"
                    @click="deleteModule"
                />
            </template>
        </Dialog>

        <!-- Confirm Delete Selected Dialog -->
        <Dialog
            :visible="deleteModulesDialog"
            @update:visible="deleteModulesDialog = $event"
            :modal="true"
            header="Confirmer la suppression multiple"
            :style="{ width: '450px' }"
            :breakpoints="{ '960px': '75vw', '640px': '90vw' }"
        >
            <div class="flex align-items-center gap-2">
                <i class="pi pi-exclamation-triangle text-red-500 text-2xl"></i>
                <span
                    >Êtes-vous sûr de vouloir supprimer les modules sélectionnés
                    ?</span
                >
            </div>
            <template #footer>
                <Button
                    label="Non"
                    icon="pi pi-times"
                    class="p-button-text"
                    @click="deleteModulesDialog = false"
                />
                <Button
                    label="Oui"
                    icon="pi pi-check"
                    class="p-button-danger"
                    @click="deleteSelectedModules"
                />
            </template>
        </Dialog>

        <Toast position="top-right" />
    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useToast } from 'primevue/usetoast';
import { FilterMatchMode } from 'primevue/api';
import axios from 'axios';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Toolbar from 'primevue/toolbar';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import FileUpload from 'primevue/fileupload';
import Dialog from 'primevue/dialog';
import Tag from 'primevue/tag';
import Toast from 'primevue/toast';
import ModuleGeneralForm from './ModuleGeneralForm.vue';

export default {
    components: {
        DataTable,
        Column,
        Toolbar,
        Button,
        InputText,
        FileUpload,
        Dialog,
        Tag,
        Toast,
        ModuleGeneralForm,
    },
    setup() {
        const toast = useToast();
        const dt = ref(null);
        const modules = ref([]);
        const selectedModules = ref([]);
        const moduleToEdit = ref(null);
        const moduleToDelete = ref(null);
        const selectedModule = ref(null);
        const showModuleDialog = ref(false);
        const showDetailsDialog = ref(false);
        const deleteModuleDialog = ref(false);
        const deleteModulesDialog = ref(false);
        const loading = ref(false);
        const filters = ref({
            global: { value: null, matchMode: FilterMatchMode.CONTAINS },
            code: { value: null, matchMode: FilterMatchMode.CONTAINS },
            titre_module: { value: null, matchMode: FilterMatchMode.CONTAINS },
            statut: { value: null, matchMode: FilterMatchMode.EQUALS },
        });

        onMounted(() => {
            fetchModules();
        });

        const fetchModules = async () => {
            loading.value = true;
            try {
                const response = await axios.get('/modules-generaux');
                modules.value = response.data;
            } catch (error) {
                toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Erreur lors du chargement des modules',
                    life: 5000,
                });
            } finally {
                loading.value = false;
            }
        };

        return {
            toast,
            dt,
            modules,
            selectedModules,
            moduleToEdit,
            moduleToDelete,
            selectedModule,
            showModuleDialog,
            showDetailsDialog,
            deleteModuleDialog,
            deleteModulesDialog,
            loading,
            filters,
            fetchModules,
        };
    },
    methods: {
        showAddModuleDialog() {
            this.moduleToEdit = null;
            this.showModuleDialog = true;
        },
        showEditModuleDialog(module) {
            this.moduleToEdit = { ...module };
            this.showModuleDialog = true;
        },
        showDetails(module) {
            this.selectedModule = { ...module };
            this.showDetailsDialog = true;
        },
        confirmDelete(module) {
            this.moduleToDelete = module;
            this.deleteModuleDialog = true;
        },
        confirmDeleteSelected() {
            this.deleteModulesDialog = true;
        },
        async deleteModule() {
            try {
                await axios.delete(
                    `/modules-generaux/${this.moduleToDelete.id}`
                );
                this.modules = this.modules.filter(
                    (m) => m.id !== this.moduleToDelete.id
                );
                this.deleteModuleDialog = false;
                this.moduleToDelete = null;
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'DocumentProgrammeSpecialite supprimé avec succès',
                    life: 3000,
                });
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Erreur lors de la suppression du module',
                    life: 5000,
                });
            }
        },
        async deleteSelectedModules() {
            try {
                const ids = this.selectedModules.map((m) => m.id);
                await axios.post('/modules-generaux/delete-multiple', { ids });
                this.modules = this.modules.filter((m) => !ids.includes(m.id));
                this.selectedModules = [];
                this.deleteModulesDialog = false;
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Modules supprimés avec succès',
                    life: 3000,
                });
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Erreur lors de la suppression des modules',
                    life: 5000,
                });
            }
        },
        async saveModule(module) {
            try {
                const response = await axios.post('/modules-generaux', module);
                this.modules.push(response.data);
                this.showModuleDialog = false;
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'DocumentProgrammeSpecialite ajouté avec succès',
                    life: 3000,
                });
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: "Erreur lors de l'ajout du module",
                    life: 5000,
                });
            }
        },
        async updateModule(module) {
            try {
                const response = await axios.put(
                    `/modules-generaux/${module.id}`,
                    module
                );
                const index = this.modules.findIndex((m) => m.id === module.id);
                this.modules[index] = response.data;
                this.showModuleDialog = false;
                this.moduleToEdit = null;
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'DocumentProgrammeSpecialite modifié avec succès',
                    life: 3000,
                });
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Erreur lors de la modification du module',
                    life: 5000,
                });
            }
        },
        async importCSV(event) {
            const file = event.files[0];
            const formData = new FormData();
            formData.append('file', file);
            try {
                const response = await axios.post(
                    '/modules-generaux/import',
                    formData
                );
                this.modules = response.data;
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Modules importés avec succès',
                    life: 3000,
                });
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: "Erreur lors de l'importation du fichier",
                    life: 5000,
                });
            }
        },
        exportCSV() {
            this.dt.exportCSV();
        },
        getStatusSeverity(statut) {
            switch (statut) {
                case 'Actif':
                    return 'success';
                case 'Inactif':
                    return 'danger';
                case 'En attente':
                    return 'warning';
                case 'Archivé':
                    return 'info';
                default:
                    return 'secondary';
            }
        },
    },
};
</script>

<style scoped>
.card {
    padding: 1rem;
}

.field {
    margin-bottom: 1rem;
}

.p-datatable .p-datatable-thead > tr > th {
    background-color: #f8f9fa;
}

.p-dialog .p-dialog-content {
    padding: 1.5rem;
}

.p-inputtext,
.p-inputtextarea {
    width: 100%;
}
</style>
