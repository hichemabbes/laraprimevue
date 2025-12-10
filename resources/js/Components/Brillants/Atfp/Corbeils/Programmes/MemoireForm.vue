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
                <i
                    :class="isEditing ? 'pi pi-pencil' : 'pi pi-plus'"
                    class="text-primary"
                ></i>
                <span class="font-bold text-lg">{{
                    isEditing ? 'Modifier un Mémoire' : 'Ajouter un Mémoire'
                }}</span>
            </div>
        </template>

        <div class="flex flex-column gap-4">
            <!-- Référence Field -->
            <div class="field">
                <label for="reference" class="block font-medium mb-2"
                    >Référence <span class="text-red-500">*</span></label
                >
                <InputText
                    id="reference"
                    v-model="newMemoire.reference"
                    class="w-full"
                    :class="{ 'p-invalid': errors.reference }"
                    @input="validateReference"
                />
                <small v-if="errors.reference" class="p-error">{{
                    errors.reference
                }}</small>
            </div>

            <!-- Numéro Mémoire Field -->
            <div class="field">
                <label for="numero_memoire" class="block font-medium mb-2"
                    >Numéro Mémoire</label
                >
                <InputText
                    id="numero_memoire"
                    v-model="newMemoire.numero_memoire"
                    class="w-full"
                />
            </div>

            <!-- Date Mémoire Field -->
            <div class="field">
                <label for="date_memoire" class="block font-medium mb-2"
                    >Date Mémoire</label
                >
                <Calendar
                    id="date_memoire"
                    v-model="newMemoire.date_memoire"
                    dateFormat="dd/mm/yy"
                    class="w-full"
                    showIcon
                />
            </div>

            <!-- Date Fields -->
            <div class="grid">
                <div class="col-12 md:col-6 field">
                    <label for="date_debut" class="block font-medium mb-2"
                        >Date Début <span class="text-red-500">*</span></label
                    >
                    <Calendar
                        id="date_debut"
                        v-model="newMemoire.date_debut"
                        dateFormat="dd/mm/yy"
                        class="w-full"
                        :class="{ 'p-invalid': errors.date_debut }"
                        showIcon
                        @date-select="validateDateDebut"
                    />
                    <small v-if="errors.date_debut" class="p-error">{{
                        errors.date_debut
                    }}</small>
                </div>
                <div class="col-12 md:col-6 field">
                    <label for="date_fin" class="block font-medium mb-2"
                        >Date Fin <span class="text-red-500">*</span></label
                    >
                    <Calendar
                        id="date_fin"
                        v-model="newMemoire.date_fin"
                        dateFormat="dd/mm/yy"
                        class="w-full"
                        :class="{ 'p-invalid': errors.date_fin }"
                        showIcon
                        @date-select="validateDateFin"
                    />
                    <small v-if="errors.date_fin" class="p-error">{{
                        errors.date_fin
                    }}</small>
                </div>
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
                    v-model="newMemoire.description"
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
                    v-model="newMemoire.observation"
                    class="w-full"
                    rows="4"
                    autoResize
                />
            </div>

            <!-- Statut Field -->
            <div class="field">
                <label for="statut" class="block font-medium mb-2"
                    >Statut</label
                >
                <Dropdown
                    id="statut"
                    v-model="newMemoire.statut"
                    :options="statutOptions"
                    optionLabel="label"
                    optionValue="value"
                    placeholder="Sélectionner un statut"
                    class="w-full"
                />
            </div>
        </div>

        <template #footer>
            <div class="flex justify-content-end gap-2">
                <Button
                    label="Annuler"
                    icon="pi pi-times"
                    class="p-button-text"
                    @click="close"
                />
                <Button
                    :label="isEditing ? 'Modifier' : 'Enregistrer'"
                    :icon="isEditing ? 'pi pi-check' : 'pi pi-save'"
                    :loading="isSaving"
                    @click="isEditing ? updateMemoire() : saveMemoire()"
                />
            </div>
        </template>
    </Dialog>

    <Toast position="top-right" />
</template>

<script>
import { useToast } from 'primevue/usetoast';
import InputText from 'primevue/inputtext';
import Calendar from 'primevue/calendar';
import FileUpload from 'primevue/fileupload';
import Dropdown from 'primevue/dropdown';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';
import Toast from 'primevue/toast';

export default {
    components: {
        InputText,
        Calendar,
        FileUpload,
        Dropdown,
        Button,
        Dialog,
        Textarea,
        Toast,
    },
    props: {
        visible: { type: Boolean, required: true },
        diplomeId: { type: Number, required: true },
        memoireToEdit: { type: Object, default: null },
    },
    emits: ['update:visible', 'close', 'save', 'update'],
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            newMemoire: {
                diplome_fr_id: null,
                reference: '',
                numero_memoire: '',
                date_memoire: null,
                date_debut: null,
                date_fin: null,
                contenu_pdf: null,
                description: '',
                observation: '',
                statut: '',
            },
            errors: {
                reference: '',
                date_debut: '',
                date_fin: '',
            },
            statutOptions: [
                { label: 'Actif', value: 'Actif' },
                { label: 'Inactif', value: 'Inactif' },
                { label: 'En attente', value: 'En attente' },
                { label: 'Archivé', value: 'Archivé' },
            ],
            isSaving: false,
        };
    },
    computed: {
        isEditing() {
            return !!this.memoireToEdit;
        },
    },
    watch: {
        memoireToEdit: {
            immediate: true,
            handler(newVal) {
                if (newVal) {
                    this.newMemoire = {
                        ...newVal,
                        date_memoire: newVal.date_memoire
                            ? new Date(newVal.date_memoire)
                            : null,
                        date_debut: newVal.date_debut
                            ? new Date(newVal.date_debut)
                            : null,
                        date_fin: newVal.date_fin
                            ? new Date(newVal.date_fin)
                            : null,
                    };
                } else {
                    this.resetForm();
                }
            },
        },
        diplomeId: {
            immediate: true,
            handler(newVal) {
                this.newMemoire.diplome_fr_id = newVal;
            },
        },
        visible(newVal) {
            if (!newVal) {
                this.resetForm();
            }
        },
    },
    methods: {
        close() {
            this.resetForm();
            this.$emit('update:visible', false);
            this.$emit('close');
        },
        resetForm() {
            this.newMemoire = {
                diplome_fr_id: this.diplomeId,
                reference: '',
                numero_memoire: '',
                date_memoire: null,
                date_debut: null,
                date_fin: null,
                contenu_pdf: null,
                description: '',
                observation: '',
                statut: '',
            };
            this.errors = {
                reference: '',
                date_debut: '',
                date_fin: '',
            };
        },
        formatDate(date) {
            if (!date || !(date instanceof Date)) return null;
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        },
        onFileSelect(event, field) {
            this.newMemoire[field] = event.files[0];
        },
        validateReference() {
            if (!this.newMemoire.reference) {
                this.errors.reference = 'La référence est obligatoire';
            } else if (this.newMemoire.reference.length > 100) {
                this.errors.reference =
                    'La référence ne doit pas dépasser 100 caractères';
            } else {
                this.errors.reference = '';
            }
        },
        validateDateDebut() {
            if (!this.newMemoire.date_debut) {
                this.errors.date_debut = 'La date de début est obligatoire';
            } else {
                this.errors.date_debut = '';
            }
        },
        validateDateFin() {
            if (!this.newMemoire.date_fin) {
                this.errors.date_fin = 'La date de fin est obligatoire';
            } else if (
                this.newMemoire.date_debut &&
                this.newMemoire.date_fin &&
                this.newMemoire.date_fin <= this.newMemoire.date_debut
            ) {
                this.errors.date_fin =
                    'La date de fin doit être postérieure à la date de début';
            } else {
                this.errors.date_fin = '';
            }
        },
        async saveMemoire() {
            this.validateReference();
            this.validateDateDebut();
            this.validateDateFin();

            if (Object.values(this.errors).some((error) => error !== '')) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Veuillez corriger les erreurs dans le formulaire',
                    life: 5000,
                });
                return;
            }

            this.isSaving = true;
            const formData = new FormData();
            Object.keys(this.newMemoire).forEach((key) => {
                if (
                    key === 'date_memoire' ||
                    key === 'date_debut' ||
                    key === 'date_fin'
                ) {
                    formData.append(key, this.formatDate(this.newMemoire[key]));
                } else if (key === 'contenu_pdf' && this.newMemoire[key]) {
                    formData.append(key, this.newMemoire[key]);
                } else {
                    formData.append(key, this.newMemoire[key] || '');
                }
            });
            this.$emit('save', formData);
            this.isSaving = false;
        },
        async updateMemoire() {
            this.validateReference();
            this.validateDateDebut();
            this.validateDateFin();

            if (Object.values(this.errors).some((error) => error !== '')) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Veuillez corriger les erreurs dans le formulaire',
                    life: 5000,
                });
                return;
            }

            this.isSaving = true;
            const formData = new FormData();
            Object.keys(this.newMemoire).forEach((key) => {
                if (
                    key === 'date_memoire' ||
                    key === 'date_debut' ||
                    key === 'date_fin'
                ) {
                    formData.append(key, this.formatDate(this.newMemoire[key]));
                } else if (key === 'contenu_pdf' && this.newMemoire[key]) {
                    formData.append(key, this.newMemoire[key]);
                } else {
                    formData.append(key, this.newMemoire[key] || '');
                }
            });
            this.$emit('update', formData);
            this.isSaving = false;
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

.p-inputtext,
.p-calendar,
.p-fileupload,
.p-dropdown,
.p-inputtextarea {
    width: 100%;
}
</style>
