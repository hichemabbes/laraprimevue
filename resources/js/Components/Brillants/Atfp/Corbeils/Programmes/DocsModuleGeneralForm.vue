<template>
    <Dialog
        :visible="visible"
        :header="`Gestion des documents - Module Général ${module?.code || ''}`"
        :style="{ width: '600px' }"
        :modal="true"
        @update:visible="$emit('update:visible', $event)"
    >
        <div class="p-fluid">
            <!-- Upload Section -->
            <div class="field mb-4">
                <label class="font-medium mb-2">Type de document</label>
                <Dropdown
                    v-model="documentType"
                    :options="documentTypes"
                    optionLabel="label"
                    optionValue="value"
                    placeholder="Sélectionner le type de document"
                    class="w-full"
                />
            </div>

            <div class="field mb-4">
                <label class="font-medium mb-2">Fichier</label>
                <FileUpload
                    mode="basic"
                    accept=".pdf"
                    :maxFileSize="10000000"
                    chooseLabel="Choisir un fichier"
                    @select="onFileSelect"
                    :disabled="!documentType"
                    class="w-full"
                />
                <small v-if="fileError" class="p-error">{{ fileError }}</small>
            </div>

            <div class="field mb-4">
                <Button
                    label="Ajouter le document"
                    icon="pi pi-upload"
                    class="p-button-success"
                    :disabled="!selectedFile || !documentType"
                    :loading="uploading"
                    @click="uploadDocument"
                />
            </div>

            <!-- Existing Documents -->
            <div class="field">
                <label class="font-medium mb-2">Documents existants</label>
                <DataTable
                    :value="existingDocuments"
                    size="small"
                    stripedRows
                    :loading="loading"
                    class="p-datatable-sm"
                >
                    <template #empty>
                        <div class="text-center py-4">
                            <i class="pi pi-inbox text-4xl text-400 mb-2" />
                            <p class="text-600">Aucun document trouvé</p>
                        </div>
                    </template>
                    <Column field="type" header="Type" style="min-width: 12rem">
                        <template #body="{ data }">
                            <span>{{ data.typeLabel }}</span>
                        </template>
                    </Column>
                    <Column
                        field="filename"
                        header="Nom du fichier"
                        style="min-width: 15rem"
                    >
                        <template #body="{ data }">
                            <a
                                :href="data.url"
                                target="_blank"
                                class="text-primary"
                                >{{ data.filename }}</a
                            >
                        </template>
                    </Column>
                    <Column header="Actions" style="min-width: 8rem">
                        <template #body="{ data }">
                            <Button
                                icon="pi pi-trash"
                                class="p-button-rounded p-button-text p-button-danger p-button-sm"
                                @click="confirmDeleteDocument(data)"
                                v-tooltip="'Supprimer'"
                            />
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>

        <template #footer>
            <Button
                label="Fermer"
                icon="pi pi-times"
                class="p-button-text"
                @click="$emit('update:visible', false)"
            />
        </template>

        <!-- Delete Confirmation Dialog -->
        <Dialog
            v-model:visible="deleteDialogVisible"
            header="Confirmer la suppression"
            :style="{ width: '450px' }"
            :modal="true"
        >
            <div class="confirmation-content flex align-items-center gap-3">
                <i class="pi pi-exclamation-triangle text-4xl text-red-500" />
                <span>
                    Voulez-vous vraiment supprimer le document
                    <b>{{ documentToDelete?.filename }}</b> ?
                </span>
            </div>
            <template #footer>
                <Button
                    label="Annuler"
                    icon="pi pi-times"
                    class="p-button-text"
                    @click="deleteDialogVisible = false"
                />
                <Button
                    label="Supprimer"
                    icon="pi pi-check"
                    class="p-button-danger"
                    @click="deleteDocument"
                />
            </template>
        </Dialog>
    </Dialog>
</template>

<script>
import axios from 'axios';
import { useToast } from 'primevue/usetoast';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import FileUpload from 'primevue/fileupload';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';

export default {
    components: {
        Dialog,
        Dropdown,
        FileUpload,
        Button,
        DataTable,
        Column,
    },
    props: {
        visible: {
            type: Boolean,
            required: true,
        },
        moduleId: {
            type: [Number, String],
            required: true,
        },
    },
    setup() {
        return { toast: useToast() };
    },
    data() {
        return {
            documentType: null,
            documentTypes: [
                { label: 'Manuel', value: 'manuels' },
                { label: 'Support de cours', value: 'supports_cours' },
                { label: 'Exercice', value: 'exercices' },
                { label: 'Contenu PDF', value: 'contenu_pdf' },
            ],
            selectedFile: null,
            fileError: null,
            uploading: false,
            loading: false,
            module: null,
            existingDocuments: [],
            deleteDialogVisible: false,
            documentToDelete: null,
        };
    },
    watch: {
        visible(newVal) {
            if (newVal) {
                this.fetchModule();
            } else {
                this.resetForm();
            }
        },
    },
    methods: {
        async fetchModule() {
            this.loading = true;
            try {
                const response = await axios.get(
                    `/modules-generaux/${this.moduleId}`
                );
                this.module = response.data;
                this.loadExistingDocuments();
            } catch (error) {
                this.showError('Erreur lors du chargement du module');
            } finally {
                this.loading = false;
            }
        },
        loadExistingDocuments() {
            this.existingDocuments = [];
            const types = [
                'manuels',
                'supports_cours',
                'exercices',
                'contenu_pdf',
            ];
            types.forEach((type) => {
                if (type === 'contenu_pdf' && this.module.contenu_pdf) {
                    this.existingDocuments.push({
                        type,
                        typeLabel: 'Contenu PDF',
                        filename: this.module.contenu_pdf.split('/').pop(),
                        url: this.module.contenu_pdf,
                    });
                } else if (
                    this.module[type] &&
                    Array.isArray(this.module[type])
                ) {
                    this.module[type].forEach((file) => {
                        this.existingDocuments.push({
                            type,
                            typeLabel: this.documentTypes.find(
                                (dt) => dt.value === type
                            )?.label,
                            filename: file.split('/').pop(),
                            url: file,
                        });
                    });
                }
            });
        },
        onFileSelect(event) {
            this.selectedFile = event.files[0];
            this.fileError = null;
            if (
                this.selectedFile &&
                this.selectedFile.type !== 'application/pdf'
            ) {
                this.fileError = 'Seuls les fichiers PDF sont acceptés';
                this.selectedFile = null;
            }
        },
        async uploadDocument() {
            if (!this.selectedFile || !this.documentType) return;
            this.uploading = true;
            const formData = new FormData();
            formData.append('file', this.selectedFile);
            formData.append('type', this.documentType);
            try {
                await this.$emit('save', formData);
                this.resetForm();
            } catch (error) {
                this.showError("Erreur lors de l'upload du document");
            } finally {
                this.uploading = false;
            }
        },
        confirmDeleteDocument(document) {
            this.documentToDelete = document;
            this.deleteDialogVisible = true;
        },
        async deleteDocument() {
            try {
                await axios.delete(
                    `/modules-generaux/${this.moduleId}/documents`,
                    {
                        data: {
                            type: this.documentToDelete.type,
                            filename: this.documentToDelete.filename,
                        },
                    }
                );
                this.showSuccess('Document supprimé avec succès');
                await this.fetchModule();
            } catch (error) {
                this.showError('Erreur lors de la suppression du document');
            } finally {
                this.deleteDialogVisible = false;
                this.documentToDelete = null;
            }
        },
        resetForm() {
            this.documentType = null;
            this.selectedFile = null;
            this.fileError = null;
            this.existingDocuments = [];
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
    },
};
</script>

<style scoped>
.confirmation-content {
    display: flex;
    align-items: center;
    padding: 1rem;
}
</style>
