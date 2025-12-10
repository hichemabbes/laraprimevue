<template>
    <Dialog
        :visible="visible"
        @update:visible="$emit('update:visible', $event)"
        :modal="true"
        :style="{ width: '600px' }"
        :breakpoints="{ '960px': '80vw', '640px': '95vw' }"
        :pt="{
            root: 'border-none',
            mask: { style: 'backdrop-filter: blur(2px)' },
            header: { class: 'surface-50 border-bottom-1 surface-border' },
            content: { class: 'surface-50 p-4' },
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
                    isEditing ? 'Modifier un secteur' : 'Ajouter un secteur'
                }}</span>
            </div>
        </template>

        <div class="flex flex-column gap-3">
            <!-- Row 1: Standardisation (AR) and Code -->
            <div class="grid">
                <div class="col-12 md:col-6">
                    <div class="field mb-0">
                        <label
                            for="standardisation_ar"
                            class="block font-medium mb-2"
                        >
                            Standardisation (AR)
                            <span class="text-red-500">*</span>
                        </label>
                        <Dropdown
                            id="standardisation_ar"
                            v-model="newElement.standardisation_ar"
                            :options="standardisations"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Sélectionner une standardisation"
                            class="w-full arabic-normal"
                            :class="{ 'p-invalid': errors.standardisation_ar }"
                            @change="validateStandardisationAr"
                        />
                        <small
                            v-if="errors.standardisation_ar"
                            class="p-error"
                            >{{ errors.standardisation_ar }}</small
                        >
                    </div>
                </div>
                <div class="col-12 md:col-6">
                    <div class="field mb-0">
                        <label for="code" class="block font-medium mb-2">
                            Code <span class="text-red-500">*</span>
                        </label>
                        <InputText
                            id="code"
                            v-model="newElement.code"
                            class="w-full"
                            :class="{ 'p-invalid': errors.code }"
                            @input="debouncedValidateCode"
                        />
                        <small v-if="errors.code" class="p-error">{{
                            errors.code
                        }}</small>
                    </div>
                </div>
            </div>

            <!-- Row 2: Nom (AR) and Nom (FR) -->
            <div class="grid">
                <div class="col-12 md:col-6">
                    <div class="field mb-0">
                        <label for="nom_ar" class="block font-medium mb-2">
                            Nom (AR) <span class="text-red-500">*</span>
                        </label>
                        <InputText
                            id="nom_ar"
                            v-model="newElement.nom_ar"
                            class="w-full arabic-normal"
                            :class="{ 'p-invalid': errors.nom_ar }"
                            @input="validateNomAr"
                        />
                        <small v-if="errors.nom_ar" class="p-error">{{
                            errors.nom_ar
                        }}</small>
                    </div>
                </div>
                <div class="col-12 md:col-6">
                    <div class="field mb-0">
                        <label for="nom_fr" class="block font-medium mb-2">
                            Nom (FR)
                            <i
                                class="pi pi-info-circle ml-1"
                                v-tooltip="'Optionnel'"
                            ></i>
                        </label>
                        <InputText
                            id="nom_fr"
                            v-model="newElement.nom_fr"
                            class="w-full"
                            :class="{ 'p-invalid': errors.nom_fr }"
                            @input="validateNomFr"
                        />
                        <small v-if="errors.nom_fr" class="p-error">{{
                            errors.nom_fr
                        }}</small>
                    </div>
                </div>
            </div>

            <!-- Row 3: Date Création and Date Annulation -->
            <div class="grid">
                <div class="col-12 md:col-6">
                    <div class="field mb-0">
                        <label
                            for="date_creation"
                            class="block font-medium mb-2"
                            >Date de création</label
                        >
                        <Calendar
                            id="date_creation"
                            v-model="newElement.date_creation"
                            dateFormat="yy-mm-dd"
                            class="w-full"
                            :class="{ 'p-invalid': errors.date_creation }"
                            @update:modelValue="validateDateCreation"
                        />
                        <small v-if="errors.date_creation" class="p-error">{{
                            errors.date_creation
                        }}</small>
                    </div>
                </div>
                <div class="col-12 md:col-6">
                    <div class="field mb-0">
                        <label
                            for="date_annulation"
                            class="block font-medium mb-2"
                            >Date d'annulation</label
                        >
                        <Calendar
                            id="date_annulation"
                            v-model="newElement.date_annulation"
                            dateFormat="yy-mm-dd"
                            class="w-full"
                            :class="{ 'p-invalid': errors.date_annulation }"
                            @update:modelValue="validateDateAnnulation"
                        />
                        <small v-if="errors.date_annulation" class="p-error">{{
                            errors.date_annulation
                        }}</small>
                    </div>
                </div>
            </div>

            <!-- Row 4: Statut and Observation -->
            <div class="grid">
                <div class="col-12 md:col-4">
                    <div class="field mb-0">
                        <label for="statut" class="block font-medium mb-2"
                            >Statut</label
                        >
                        <div class="flex align-items-center">
                            <Checkbox
                                id="statut"
                                :modelValue="newElement.statut === 'Actif'"
                                :binary="true"
                                class="modern-checkbox"
                                :class="{ 'p-invalid': errors.statut }"
                                @update:modelValue="
                                    newElement.statut = $event
                                        ? 'Actif'
                                        : 'Inactif';
                                    validateStatut();
                                "
                            />
                            <label for="statut" class="ml-2">{{
                                newElement.statut
                            }}</label>
                        </div>
                        <small v-if="errors.statut" class="p-error">{{
                            errors.statut
                        }}</small>
                    </div>
                </div>
                <div class="col-12 md:col-8">
                    <div class="field mb-0">
                        <label for="observation" class="block font-medium mb-2">
                            Observation
                            <i
                                class="pi pi-info-circle ml-1"
                                v-tooltip="'Optionnel'"
                            ></i>
                        </label>
                        <Textarea
                            id="observation"
                            v-model="newElement.observation"
                            class="w-full arabic-normal"
                            :class="{ 'p-invalid': errors.observation }"
                            rows="3"
                            @input="validateObservation"
                        />
                        <small v-if="errors.observation" class="p-error">{{
                            errors.observation
                        }}</small>
                    </div>
                </div>
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
                    @click="isEditing ? update() : save()"
                />
            </div>
        </template>
    </Dialog>
</template>

<script>
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Calendar from 'primevue/calendar';
import Textarea from 'primevue/textarea';
import Checkbox from 'primevue/checkbox';
import axios from 'axios';
import debounce from 'lodash/debounce';

export default {
    components: {
        InputText,
        Dropdown,
        Button,
        Dialog,
        Calendar,
        Textarea,
        Checkbox,
    },
    props: {
        visible: { type: Boolean, required: true },
        initialData: { type: Object, default: null },
    },
    emits: ['update:visible', 'close', 'save', 'update', 'error'],
    data() {
        return {
            newElement: {
                id: null,
                code: '',
                nom_fr: '',
                nom_ar: '',
                standardisation_ar: 'مقيس',
                statut: 'Actif',
                date_creation: null,
                date_annulation: null,
                observation: '',
            },
            standardisations: [
                { label: 'مقيس', value: 'مقيس' },
                { label: 'غير مقيس', value: 'غير مقيس' },
            ],
            statuts: [
                { label: 'Actif', value: 'Actif' },
                { label: 'Inactif', value: 'Inactif' },
            ],
            errors: {
                code: '',
                nom_fr: '',
                nom_ar: '',
                standardisation_ar: '',
                statut: '',
                date_creation: '',
                date_annulation: '',
                observation: '',
            },
            isSaving: false,
        };
    },
    computed: {
        isEditing() {
            return !!this.initialData;
        },
    },
    watch: {
        initialData: {
            immediate: true,
            handler(newVal) {
                if (newVal) {
                    this.newElement = {
                        id: newVal.id || null,
                        code: newVal.code || '',
                        nom_fr: newVal.nom_fr || '',
                        nom_ar: newVal.nom_ar || '',
                        standardisation_ar: newVal.standardisation_ar || 'مقيس',
                        statut: newVal.statut || 'Actif',
                        date_creation: newVal.date_creation
                            ? new Date(newVal.date_creation)
                            : null,
                        date_annulation: newVal.date_annulation
                            ? new Date(newVal.date_annulation)
                            : null,
                        observation: newVal.observation || '',
                    };
                    this.$nextTick(() => {
                        this.validateCode(true);
                        this.validateNomFr();
                        this.validateNomAr();
                        this.validateStandardisationAr();
                        this.validateStatut();
                        this.validateDateCreation();
                        this.validateDateAnnulation();
                        this.validateObservation();
                    });
                } else {
                    this.resetForm();
                }
            },
        },
        visible(newVal) {
            if (!newVal) {
                this.resetForm();
            }
        },
        'newElement.standardisation_ar'() {
            this.validateStandardisationAr();
            if (this.newElement.code && !this.isEditing) {
                this.debouncedValidateCode();
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
            this.newElement = {
                id: null,
                code: '',
                nom_fr: '',
                nom_ar: '',
                standardisation_ar: 'مقيس',
                statut: 'Actif',
                date_creation: null,
                date_annulation: null,
                observation: '',
            };
            this.errors = {
                code: '',
                nom_fr: '',
                nom_ar: '',
                standardisation_ar: '',
                statut: '',
                date_creation: '',
                date_annulation: '',
                observation: '',
            };
        },
        validateCode: debounce(async function (silent = false) {
            this.errors.code = '';

            if (!this.newElement.code) {
                this.errors.code = silent ? '' : 'Le code est requis.';
                return;
            }
            if (this.newElement.code.length > 20) {
                this.errors.code =
                    'Le code ne doit pas dépasser 20 caractères.';
                return;
            }
            if (!this.newElement.standardisation_ar) {
                this.errors.code = silent
                    ? ''
                    : 'Sélectionnez une standardisation avant de vérifier le code.';
                return;
            }

            if (this.isEditing) {
                return;
            }

            try {
                const response = await axios.post('/api/secteurs/check-code', {
                    code: this.newElement.code,
                    standardisation_ar: this.newElement.standardisation_ar,
                    id: this.newElement.id,
                });
                this.errors.code = response.data.isUnique
                    ? ''
                    : response.data.message;
            } catch (error) {
                this.errors.code = silent
                    ? ''
                    : 'Erreur lors de la vérification du code.';
                this.$emit('error', 'Impossible de vérifier le code.');
            }
        }, 500),
        debouncedValidateCode() {
            this.validateCode();
        },
        validateNomFr() {
            this.errors.nom_fr =
                this.newElement.nom_fr && this.newElement.nom_fr.length > 255
                    ? 'Le nom en français ne doit pas dépasser 255 caractères.'
                    : '';
        },
        validateNomAr() {
            this.errors.nom_ar = !this.newElement.nom_ar
                ? 'Le nom en arabe est requis.'
                : this.newElement.nom_ar.length > 255
                  ? 'Le nom en arabe ne doit pas dépasser 255 caractères.'
                  : '';
        },
        validateStandardisationAr(silent = false) {
            this.errors.standardisation_ar = !this.newElement.standardisation_ar
                ? silent
                    ? ''
                    : 'La standardisation est requise.'
                : !['مقيس', 'غير مقيس'].includes(
                        this.newElement.standardisation_ar
                    )
                  ? 'Standardisation invalide.'
                  : '';
        },
        validateStatut() {
            this.errors.statut =
                this.newElement.statut &&
                !['Actif', 'Inactif'].includes(this.newElement.statut)
                    ? 'Le statut doit être Actif ou Inactif.'
                    : '';
        },
        validateDateCreation() {
            if (
                this.newElement.date_creation &&
                !(this.newElement.date_creation instanceof Date)
            ) {
                this.errors.date_creation = 'La date de création est invalide.';
            } else if (
                this.newElement.date_creation &&
                this.newElement.date_annulation &&
                this.newElement.date_creation > this.newElement.date_annulation
            ) {
                this.errors.date_creation =
                    "La date de création doit être antérieure à la date d'annulation.";
            } else {
                this.errors.date_creation = '';
            }
        },
        validateDateAnnulation() {
            if (
                this.newElement.date_annulation &&
                !(this.newElement.date_annulation instanceof Date)
            ) {
                this.errors.date_annulation =
                    "La date d'annulation est invalide.";
            } else if (
                this.newElement.date_creation &&
                this.newElement.date_annulation &&
                this.newElement.date_creation > this.newElement.date_annulation
            ) {
                this.errors.date_annulation =
                    "La date d'annulation doit être postérieure à la date de création.";
            } else {
                this.errors.date_annulation = '';
            }
        },
        validateObservation() {
            this.errors.observation =
                this.newElement.observation &&
                this.newElement.observation.length > 1000
                    ? "L'observation ne doit pas dépasser 1000 caractères."
                    : '';
        },
        async save() {
            this.isSaving = true;

            await this.validateCode();
            this.validateNomFr();
            this.validateNomAr();
            this.validateStandardisationAr();
            this.validateStatut();
            this.validateDateCreation();
            this.validateDateAnnulation();
            this.validateObservation();

            if (Object.values(this.errors).some((error) => error)) {
                this.isSaving = false;
                this.$emit('error', 'Corrigez les erreurs dans le formulaire.');
                return;
            }

            const payload = {
                code: this.newElement.code,
                nom_fr: this.newElement.nom_fr,
                nom_ar: this.newElement.nom_ar,
                standardisation_ar: this.newElement.standardisation_ar,
                statut: this.newElement.statut,
                date_creation: this.newElement.date_creation
                    ? this.formatDate(this.newElement.date_creation)
                    : null,
                date_annulation: this.newElement.date_annulation
                    ? this.formatDate(this.newElement.date_annulation)
                    : null,
                observation: this.newElement.observation,
            };

            try {
                this.$emit('save', payload);
                this.isSaving = false;
            } catch (error) {
                this.isSaving = false;
                this.$emit(
                    'error',
                    error.response?.data?.message ||
                        "Impossible d'ajouter le secteur."
                );
            }
        },
        async update() {
            this.isSaving = true;

            await this.validateCode();
            this.validateNomFr();
            this.validateNomAr();
            this.validateStandardisationAr();
            this.validateStatut();
            this.validateDateCreation();
            this.validateDateAnnulation();
            this.validateObservation();

            if (Object.values(this.errors).some((error) => error)) {
                this.isSaving = false;
                this.$emit('error', 'Corrigez les erreurs dans le formulaire.');
                return;
            }

            const payload = {
                id: this.newElement.id,
                code: this.newElement.code,
                nom_fr: this.newElement.nom_fr,
                nom_ar: this.newElement.nom_ar,
                standardisation_ar: this.newElement.standardisation_ar,
                statut: this.newElement.statut,
                date_creation: this.newElement.date_creation
                    ? this.formatDate(this.newElement.date_creation)
                    : null,
                date_annulation: this.newElement.date_annulation
                    ? this.formatDate(this.newElement.date_annulation)
                    : null,
                observation: this.newElement.observation,
            };

            try {
                this.$emit('update', payload);
                this.isSaving = false;
            } catch (error) {
                this.isSaving = false;
                this.$emit(
                    'error',
                    error.response?.data?.message ||
                        'Impossible de modifier le secteur.'
                );
            }
        },
        formatDate(date) {
            if (!date) return null;
            if (!(date instanceof Date)) date = new Date(date);
            if (isNaN(date.getTime())) return null;
            return date.toISOString().split('T')[0];
        },
    },
};
</script>

<style scoped>
.field {
    margin-bottom: 1rem;
}
.p-error {
    font-size: 0.875rem;
    color: #dc3545;
}
.arabic-normal {
    font-family: 'Amiri', Arial, sans-serif;
    font-weight: normal;
    direction: rtl;
}
.p-textarea {
    height: 80px;
}
.modern-checkbox {
    transform: scale(1.2);
    margin-right: 8px;
}
.modern-checkbox:hover {
    cursor: pointer;
    opacity: 0.8;
}
@media (max-width: 640px) {
    .grid .col-12 {
        margin-bottom: 1rem;
    }
}
</style>
