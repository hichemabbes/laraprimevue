<template>
    <Dialog
        :visible="visible"
        @update:visible="$emit('update:visible', $event)"
        :modal="true"
        :style="{ width: '550px' }"
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
                <i class="pi pi-file-pdf text-primary"></i>
                <span class="font-bold text-lg"
                    >Ajouter des Documents au Module</span
                >
            </div>
        </template>

        <div class="flex flex-column gap-4">
            <!-- Manuels Field -->
            <div class="field">
                <label for="manuels" class="block font-medium mb-2"
                    >Manuels</label
                >
                <FileUpload
                    id="manuels"
                    mode="basic"
                    accept=".pdf"
                    :maxFileSize="10000000"
                    @select="onFileSelect($event, 'manuels')"
                    chooseLabel="Sélectionner un fichier"
                />
            </div>

            <!-- Supports Cours Field -->
            <div class="field">
                <label for="supports_cours" class="block font-medium mb-2"
                    >Supports de Cours</label
                >
                <FileUpload
                    id="supports_cours"
                    mode="basic"
                    accept=".pdf"
                    :maxFileSize="10000000"
                    @select="onFileSelect($event, 'supports_cours')"
                    chooseLabel="Sélectionner un fichier"
                />
            </div>

            <!-- Exercices Field -->
            <div class="field">
                <label for="exercices" class="block font-medium mb-2"
                    >Exercices</label
                >
                <FileUpload
                    id="exercices"
                    mode="basic"
                    accept=".pdf"
                    :maxFileSize="10000000"
                    @select="onFileSelect($event, 'exercices')"
                    chooseLabel="Sélectionner un fichier"
                />
            </div>

            <!-- Contenu PDF Field -->
            <div class="field">
                <label for="contenu_pdf" class="block font-medium mb-2"
                    >Contenu PDF</label
                >
                <FileUpload
                    id="contenu_pdf"
                    mode="basic"
                    accept=".pdf"
                    :maxFileSize="10000000"
                    @select="onFileSelect($event, 'contenu_pdf')"
                    chooseLabel="Sélectionner un fichier"
                />
            </div>

            <!-- Description Field -->
            <div class="field">
                <label for="description" class="block font-medium mb-2"
                    >Description</label
                >
                <Textarea
                    id="description"
                    v-model="formData.description"
                    class="w-full"
                    rows="4"
                    autoResize
                />
            </div>

            <!-- Observation Field -->
            <div class="field">
                <label for="observation" class="block font-medium mb-2"
                    >Observation</label
                >
                <Textarea
                    id="observation"
                    v-model="formData.observation"
                    class="w-full"
                    rows="4"
                    autoResize
                />
            </div>
        </div>

        <template #footer>
            <div class="flex justify-content-end gap-2">
                <Button
                    label="Annuler"
                    icon="pi pi-times"
                    class="p-button-text"
                    @click="$emit('close')"
                />
                <Button
                    label="Enregistrer"
                    icon="pi pi-save"
                    :loading="isSaving"
                    @click="saveDocuments"
                />
            </div>
        </template>
    </Dialog>

    <Toast position="top-right" />
</template>

<script>
import { useToast } from 'primevue/usetoast';
import FileUpload from 'primevue/fileupload';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';
import Toast from 'primevue/toast';

export default {
    components: {
        FileUpload,
        Button,
        Dialog,
        Textarea,
        Toast,
    },
    props: {
        visible: { type: Boolean, required: true },
        moduleId: { type: Number, required: true },
    },
    emits: ['update:visible', 'close', 'save'],
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            formData: {
                manuels: null,
                supports_cours: null,
                exercices: null,
                contenu_pdf: null,
                description: '',
                observation: '',
            },
            isSaving: false,
        };
    },
    methods: {
        onFileSelect(event, field) {
            this.formData[field] = event.files[0];
        },
        async saveDocuments() {
            const formData = new FormData();
            if (this.formData.manuels)
                formData.append('manuels', this.formData.manuels);
            if (this.formData.supports_cours)
                formData.append('supports_cours', this.formData.supports_cours);
            if (this.formData.exercices)
                formData.append('exercices', this.formData.exercices);
            if (this.formData.contenu_pdf)
                formData.append('contenu_pdf', this.formData.contenu_pdf);
            formData.append('description', this.formData.description);
            formData.append('observation', this.formData.observation);

            this.isSaving = true;
            try {
                this.$emit('save', this.moduleId, formData);
                this.resetForm();
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: "Erreur lors de l'enregistrement des documents",
                    life: 5000,
                });
            } finally {
                this.isSaving = false;
            }
        },
        resetForm() {
            this.formData = {
                manuels: null,
                supports_cours: null,
                exercices: null,
                contenu_pdf: null,
                description: '',
                observation: '',
            };
        },
    },
};
</script>

<style scoped>
.field {
    margin-bottom: 1rem;
}

.p-dialog .p-dialog-content {
    padding: 1.5rem;
}

.p-fileupload,
.p-inputtextarea {
    width: 100%;
}
</style>
