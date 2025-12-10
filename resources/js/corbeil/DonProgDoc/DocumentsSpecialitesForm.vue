<template>
    <Dialog
        :visible="visible"
        @update:visible="$emit('update:visible', $event)"
        modal
        :pt="{
            root: 'border-none bg-transparent',
            mask: {
                style: 'backdrop-filter: blur(2px); background-color: rgba(128, 128, 128, 0.2);',
            },
        }"
    >
        <template #container="{ closeCallback }">
            <div class="p-5 surface-card">
                <!-- Bouton de fermeture modernisé -->
                <span
                    class="absolute cursor-pointer text-gray-600 hover:text-gray-900 transition-all duration-200"
                    style="
                        top: 12px;
                        right: 12px;
                        width: 32px;
                        height: 32px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        background-color: rgba(0, 0, 0, 0.1);
                        border-radius: 8px;
                    "
                    @click="close"
                >
                    <!-- Icône moderne SVG -->
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="20"
                        height="20"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path d="M18 6L6 18" />
                        <path d="M6 6l12 12" />
                    </svg>
                </span>

                <div
                    class="flex flex-column px-5 py-5 gap-4 rounded-[12px] bg-white/90 dark:bg-gray-700/90"
                >
                    <!-- Titre du formulaire -->
                    <h5 class="text-center text-black font-bold text-l mt-3">
                        {{
                            isEditing
                                ? 'Modifier un Document.php'
                                : 'Ajouter un Document.php'
                        }}
                    </h5>

                    <!-- Ligne 1: Version + Type de Document.php -->
                    <div class="flex gap-4">
                        <div class="flex flex-column gap-2 flex-1">
                            <label
                                for="version"
                                class="text-black font-semibold"
                                >Version</label
                            >
                            <InputText
                                id="version"
                                v-model="newElement.version"
                                class="w-full"
                                @input="validateVersion"
                            />
                            <small v-if="errors.version" class="text-red-500">{{
                                errors.version
                            }}</small>
                        </div>
                        <div class="flex flex-column gap-2 flex-1">
                            <label
                                for="type_document"
                                class="text-black font-semibold"
                                >Type de Document</label
                            >
                            <Dropdown
                                id="type_document"
                                v-model="newElement.type_document"
                                :options="documentTypes"
                                optionLabel="label"
                                optionValue="value"
                                placeholder="Sélectionner un type"
                                class="w-full"
                                @change="validateTypeDocument"
                            />
                            <small
                                v-if="errors.type_document"
                                class="text-red-500"
                                >{{ errors.type_document }}</small
                            >
                        </div>
                    </div>

                    <!-- Ligne 2: Date Début + Date Fin -->
                    <div class="flex gap-4">
                        <div class="flex flex-column gap-2 flex-1">
                            <label
                                for="date_debut"
                                class="text-black font-semibold"
                                >Date Début</label
                            >
                            <Calendar
                                id="date_debut"
                                v-model="newElement.date_debut"
                                dateFormat="yy-mm-dd"
                                class="w-full"
                                @input="validateDateDebut"
                            />
                            <small
                                v-if="errors.date_debut"
                                class="text-red-500"
                                >{{ errors.date_debut }}</small
                            >
                        </div>
                        <div class="flex flex-column gap-2 flex-1">
                            <label
                                for="date_fin"
                                class="text-black font-semibold"
                                >Date Fin</label
                            >
                            <Calendar
                                id="date_fin"
                                v-model="newElement.date_fin"
                                dateFormat="yy-mm-dd"
                                class="w-full"
                                @input="validateDateFin"
                            />
                            <small
                                v-if="errors.date_fin"
                                class="text-red-500"
                                >{{ errors.date_fin }}</small
                            >
                        </div>
                    </div>

                    <!-- Ligne 3: Fichier + Actif -->
                    <div class="flex gap-4 align-items-end">
                        <div class="flex flex-column gap-2 flex-1">
                            <label
                                for="fichier"
                                class="text-black font-semibold"
                                >Fichier</label
                            >
                            <InputText
                                type="file"
                                id="fichier"
                                @change="onFileSelect"
                                accept=".pdf"
                                class="w-full"
                            />
                            <small
                                v-if="newElement.fichier && !fileToUpload"
                                class="text-gray-500"
                                >Fichier actuel: {{ newElement.fichier }}</small
                            >
                            <small v-if="errors.fichier" class="text-red-500">{{
                                errors.fichier
                            }}</small>
                        </div>
                        <div class="flex flex-column gap-2">
                            <label for="actif" class="text-black font-semibold"
                                >Actif</label
                            >
                            <Checkbox
                                id="actif"
                                v-model="newElement.actif"
                                :binary="true"
                                @change="validateActif"
                                :disabled="newElement.date_fin"
                            />
                            <small v-if="errors.actif" class="text-red-500">{{
                                errors.actif
                            }}</small>
                        </div>
                    </div>

                    <!-- Ligne 4: Observation -->
                    <div class="flex flex-column gap-2">
                        <label
                            for="observation"
                            class="text-black font-semibold"
                            >Observation</label
                        >
                        <Textarea
                            id="observation"
                            v-model="newElement.observation"
                            rows="2"
                            class="w-full"
                            @input="validateObservation"
                        />
                        <small v-if="errors.observation" class="text-red-500">{{
                            errors.observation
                        }}</small>
                    </div>

                    <!-- Boutons d'action -->
                    <div class="flex align-items-center gap-5">
                        <Button
                            label="Annuler"
                            severity="danger"
                            outlined
                            @click="close"
                            class="p-2.3 w-full border-1 border-surface-200 hover:bg-red-500 hover:text-white transition-colors duration-200"
                        />
                        <Button
                            label="Enregistrer"
                            severity="success"
                            outlined
                            @click="save"
                            :loading="loading"
                            class="p-2.3 w-full border-1 border-surface-200 hover:bg-green-500 hover:text-white transition-colors duration-200"
                        />
                    </div>
                </div>
            </div>
        </template>
    </Dialog>
</template>

<script>
import axios from 'axios';
import { useToast } from 'primevue/usetoast';
import { format } from 'date-fns';

export default {
    props: {
        visible: {
            type: Boolean,
            required: true,
        },
        initialData: {
            type: Object,
            default: null,
        },
        specialiteId: {
            type: Number,
            required: true,
        },
    },
    emits: ['update:visible', 'save', 'update'],
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            newElement: {
                id: null,
                version: '',
                type_document: null,
                fichier: null,
                date_debut: null,
                date_fin: null,
                actif: false,
                specialite_id: this.specialiteId,
                observation: '',
            },
            fileToUpload: null,
            errors: {
                version: '',
                type_document: '',
                fichier: '',
                date_debut: '',
                date_fin: '',
                actif: '',
                observation: '',
            },
            loading: false,
            documentTypes: [
                {
                    label: 'Dossier Homologation',
                    value: 'Dossier Homologation',
                },
                { label: 'Programme Étude', value: 'Programme Etude' },
                { label: 'Guide Pédagogique', value: 'Guide Pédagogique' },
                { label: 'Guide Évaluation', value: 'Guide Evaluation' },
                {
                    label: 'Guide Organisation Matériels',
                    value: 'Guide Organisation Matériels',
                },
                { label: 'Référentiel', value: 'Référentiel' },
                { label: 'Autres', value: 'Autres' },
            ],
        };
    },
    computed: {
        isEditing() {
            return !!this.initialData && !!this.initialData.id;
        },
    },
    watch: {
        initialData: {
            immediate: true,
            handler(newVal) {
                if (newVal) {
                    this.newElement = {
                        ...newVal,
                        date_debut: newVal.date_debut
                            ? new Date(newVal.date_debut)
                            : null,
                        date_fin: newVal.date_fin
                            ? new Date(newVal.date_fin)
                            : null,
                        specialite_id: this.specialiteId,
                    };
                    this.fileToUpload = null;
                    this.clearErrors();
                } else {
                    this.resetForm();
                }
            },
        },
        'newElement.date_fin'(newVal) {
            if (newVal) {
                this.newElement.actif = false;
            }
        },
    },
    methods: {
        close() {
            this.$emit('update:visible', false);
            this.resetForm();
        },
        resetForm() {
            this.newElement = {
                id: null,
                version: '',
                type_document: null,
                fichier: null,
                date_debut: null,
                date_fin: null,
                actif: false,
                specialite_id: this.specialiteId,
                observation: '',
            };
            this.fileToUpload = null;
            this.clearErrors();
        },
        clearErrors() {
            this.errors = {
                version: '',
                type_document: '',
                fichier: '',
                date_debut: '',
                date_fin: '',
                actif: '',
                observation: '',
            };
        },
        validateVersion() {
            if (!this.newElement.version) {
                this.errors.version = 'La version est obligatoire.';
            } else if (this.newElement.version.length > 255) {
                this.errors.version =
                    'La version ne doit pas dépasser 255 caractères.';
            } else {
                this.errors.version = '';
            }
        },
        validateTypeDocument() {
            if (!this.newElement.type_document) {
                this.errors.type_document =
                    'Le type de document est obligatoire.';
            } else {
                this.errors.type_document = '';
            }
        },
        validateFichier() {
            if (!this.isEditing && !this.fileToUpload) {
                this.errors.fichier =
                    'Un fichier est requis pour un nouveau document.';
            } else if (this.fileToUpload && this.fileToUpload.size > 10000000) {
                this.errors.fichier = 'Le fichier ne doit pas dépasser 10 Mo.';
            } else {
                this.errors.fichier = '';
            }
        },
        validateDateDebut() {
            if (!this.newElement.date_debut) {
                this.errors.date_debut = 'La date de début est obligatoire.';
            } else if (
                this.newElement.date_fin &&
                new Date(this.newElement.date_debut) >
                    new Date(this.newElement.date_fin)
            ) {
                this.errors.date_debut =
                    'La date de début doit être antérieure à la date de fin.';
            } else {
                this.errors.date_debut = '';
            }
        },
        validateDateFin() {
            if (
                this.newElement.date_fin &&
                new Date(this.newElement.date_fin) <
                    new Date(this.newElement.date_debut)
            ) {
                this.errors.date_fin =
                    'La date de fin doit être postérieure à la date de début.';
            } else {
                this.errors.date_fin = '';
            }
        },
        validateActif() {
            if (this.newElement.date_fin && this.newElement.actif) {
                this.errors.actif =
                    'Un document avec une date de fin ne peut pas être actif.';
                this.newElement.actif = false;
            } else {
                this.errors.actif = '';
            }
        },
        validateObservation() {
            if (
                this.newElement.observation &&
                this.newElement.observation.length > 1000
            ) {
                this.errors.observation =
                    'L’observation ne doit pas dépasser 1000 caractères.';
            } else {
                this.errors.observation = '';
            }
        },
        onFileSelect(event) {
            this.fileToUpload = event.target.files[0];
            this.validateFichier();
        },
        async save() {
            this.loading = true;
            this.validateVersion();
            this.validateTypeDocument();
            this.validateFichier();
            this.validateDateDebut();
            this.validateDateFin();
            this.validateActif();
            this.validateObservation();

            if (Object.values(this.errors).some((error) => error !== '')) {
                this.loading = false;
                return;
            }

            const formData = new FormData();
            formData.append(
                'specialite_id',
                this.newElement.specialite_id || ''
            );
            formData.append('version', this.newElement.version || '');
            formData.append(
                'type_document',
                this.newElement.type_document || ''
            );
            if (this.fileToUpload) {
                formData.append('fichier', this.fileToUpload);
            }
            formData.append(
                'date_debut',
                this.newElement.date_debut
                    ? format(new Date(this.newElement.date_debut), 'yyyy-MM-dd')
                    : ''
            );
            formData.append(
                'date_fin',
                this.newElement.date_fin
                    ? format(new Date(this.newElement.date_fin), 'yyyy-MM-dd')
                    : ''
            );
            formData.append('actif', this.newElement.actif ? '1' : '0');
            formData.append('observation', this.newElement.observation || '');

            try {
                if (this.isEditing) {
                    formData.append('_method', 'PUT');
                    const response = await axios.post(
                        `/api/documents/${this.newElement.id}`,
                        formData,
                        {
                            headers: { 'Content-Type': 'multipart/form-data' },
                        }
                    );
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Document.php modifié avec succès',
                        life: 3000,
                    });
                    this.$emit('update', response.data.data);
                } else {
                    const response = await axios.post(
                        '/api/documents',
                        formData,
                        {
                            headers: { 'Content-Type': 'multipart/form-data' },
                        }
                    );
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Document.php ajouté avec succès',
                        life: 3000,
                    });
                    this.$emit('save', response.data.data);
                }
                this.close();
            } catch (error) {
                if (error.response && error.response.data.errors) {
                    Object.keys(error.response.data.errors).forEach((key) => {
                        this.errors[key] = error.response.data.errors[key][0];
                    });
                } else {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: "Échec de l'enregistrement du document",
                        life: 3000,
                    });
                }
            } finally {
                this.loading = false;
            }
        },
    },
};
</script>

<style scoped>
/* Pas de styles supplémentaires nécessaires, tout est inline ou géré par PrimeVue */
</style>
