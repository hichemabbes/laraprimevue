<template>
    <Dialog
        :visible="visible"
        @update:visible="$emit('update:visible', $event)"
        :style="{ width: '50vw' }"
        header="Gestion des documents"
        :modal="true"
    >
        <div>
            <div class="flex flex-column gap-3">
                <Button
                    label="Ajouter un document"
                    icon="pi pi-plus"
                    class="p-button-success"
                    @click="openDocumentForm"
                />
                <DataTable :value="documents" :rows="10" :paginator="true">
                    <Column field="Nom" header="Nom"></Column>
                    <Column field="Ref" header="Référence"></Column>
                    <Column field="statut" header="Statut"></Column>
                    <Column field="observation" header="Observation"></Column>
                    <Column header="Actions">
                        <template #body="{ data }">
                            <Button
                                icon="pi pi-pencil"
                                class="p-button-rounded p-button-text p-button-success mr-2"
                                @click="editDocument(data)"
                            />
                            <Button
                                icon="pi pi-trash"
                                class="p-button-rounded p-button-text p-button-danger"
                                @click="deleteDocument(data)"
                            />
                            <Button
                                icon="pi pi-download"
                                class="p-button-rounded p-button-text p-button-info"
                                @click="downloadDocument(data)"
                            />
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>
    </Dialog>

    <Dialog
        v-model:visible="documentFormVisible"
        :style="{ width: '40vw' }"
        header="Formulaire Document"
        :modal="true"
    >
        <div>
            <form @submit.prevent="saveDocument">
                <div class="p-fluid">
                    <div class="p-field">
                        <label for="Nom">Nom</label>
                        <Dropdown
                            v-model="documentForm.Nom"
                            :options="documentTypes"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Sélectionner un type"
                        />
                    </div>
                    <div class="p-field">
                        <label for="Ref">Référence</label>
                        <InputText v-model="documentForm.Ref" />
                    </div>
                    <div class="p-field">
                        <label for="Document">Fichier</label>
                        <FileUpload
                            mode="basic"
                            name="Document"
                            :auto="true"
                            :customUpload="true"
                            @uploader="handleFileUpload"
                        />
                    </div>
                    <div class="p-field">
                        <label for="statut">Statut</label>
                        <Dropdown
                            v-model="documentForm.statut"
                            :options="statutOptions"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Sélectionner un statut"
                        />
                    </div>
                    <div class="p-field">
                        <label for="observation">Observation</label>
                        <Textarea v-model="documentForm.observation" rows="3" />
                    </div>
                </div>
                <div class="flex justify-content-end mt-3">
                    <Button
                        label="Annuler"
                        icon="pi pi-times"
                        class="p-button-text"
                        @click="documentFormVisible = false"
                    />
                    <Button
                        type="submit"
                        label="Enregistrer"
                        icon="pi pi-check"
                        class="p-button-success"
                    />
                </div>
            </form>
        </div>
    </Dialog>
</template>

<script>
import axios from 'axios';

export default {
    props: {
        specialiteId: {
            type: Number,
            required: true,
        },
        selectedAnnee: {
            type: Number,
            required: true,
        },
        visible: {
            type: Boolean,
            required: true,
        },
    },
    data() {
        return {
            documents: [],
            documentFormVisible: false,
            documentForm: {
                Nom: null,
                Ref: null,
                Document: null,
                statut: null,
                observation: null,
            },
            documentTypes: [
                {
                    label: "Dossier d'homologation",
                    value: "Dossier d'homologation",
                },
                { label: "Programme d'étude", value: "Programme d'étude" },
                { label: 'Guide pédagogique', value: 'Guide pédagogique' },
                { label: 'Guide evaluation', value: 'Guide evaluation' },
                { label: 'Guide organisation', value: 'Guide organisation' },
                { label: 'Guide matériels', value: 'Guide matériels' },
                { label: 'Autres', value: 'Autres' },
            ],
            statutOptions: [
                { label: 'Actif', value: 'actif' },
                { label: 'Inactif', value: 'inactif' },
            ],
            documentToEdit: null,
        };
    },
    methods: {
        async fetchDocuments() {
            try {
                const response = await axios.get('/api/documents', {
                    params: {
                        specialite_id: this.specialiteId,
                        annee_id: this.selectedAnnee,
                    },
                });
                this.documents = response.data;
            } catch (error) {
                console.error(
                    'Erreur lors de la récupération des documents :',
                    error
                );
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Impossible de charger les documents.',
                    life: 3000,
                });
            }
        },
        openDocumentForm() {
            this.documentForm = {
                Nom: null,
                Ref: null,
                Document: null,
                statut: null,
                observation: null,
            };
            this.documentToEdit = null;
            this.documentFormVisible = true;
        },
        editDocument(document) {
            this.documentForm = { ...document };
            this.documentToEdit = document;
            this.documentFormVisible = true;
        },
        async saveDocument() {
            try {
                const formData = new FormData();
                Object.keys(this.documentForm).forEach((key) => {
                    if (this.documentForm[key] !== null) {
                        formData.append(key, this.documentForm[key]);
                    }
                });

                if (this.documentToEdit) {
                    await axios.put(
                        `/api/documents/${this.documentToEdit.id}`,
                        formData
                    );
                } else {
                    await axios.post('/api/documents', formData);
                }

                this.fetchDocuments();
                this.documentFormVisible = false;
                this.$toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'DocumentSpecialite enregistré avec succès.',
                    life: 3000,
                });
            } catch (error) {
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: "Erreur lors de l'enregistrement du document.",
                    life: 3000,
                });
            }
        },
        async deleteDocument(document) {
            try {
                await axios.delete(`/api/documents/${document.id}`);
                this.fetchDocuments();
                this.$toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'DocumentSpecialite supprimé avec succès.',
                    life: 3000,
                });
            } catch (error) {
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Erreur lors de la suppression du document.',
                    life: 3000,
                });
            }
        },
        downloadDocument(document) {
            window.open(document.Document, '_blank');
        },
        handleFileUpload(event) {
            this.documentForm.Document = event.files[0];
        },
    },
    watch: {
        visible(newVal) {
            if (newVal) {
                this.fetchDocuments();
            }
        },
    },
};
</script>
