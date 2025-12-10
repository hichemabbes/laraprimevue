```vue
<template>
    <Dialog
        :visible="visible"
        @update:visible="$emit('update:visible', $event)"
        :modal="true"
        :style="{ width: '50vw', maxWidth: '800px' }"
        :breakpoints="{ '960px': '75vw', '640px': '90vw' }"
        class="p-dialog p-fluid surface-card border-round-lg shadow-4"
        :pt="{
            mask: { style: 'backdrop-filter: blur(4px)' },
            header: { class: 'surface-50 border-bottom-1 surface-border p-3' },
            content: { class: 'surface-ground p-4' },
            footer: { class: 'surface-50 border-top-1 surface-border p-3' },
        }"
    >
        <template #header>
            <div class="flex align-items-center gap-2">
                <i class="pi pi-pencil text-primary text-2xl"></i>
                <span class="font-semibold text-2xl text-900">Corriger la ligne {{ errorData?.line }}</span>
            </div>
        </template>

        <div class="grid formgrid p-fluid">
            <div v-for="(field, index) in fields" :key="field.key" class="field col-12 md:col-6">
                <label :for="field.key" class="font-medium text-900">
                    {{ field.label }}
                    <span v-if="field.required" class="text-red-500">*</span>
                </label>
                <Dropdown
                    v-if="field.type === 'list'"
                    v-model="formData[field.key]"
                    :options="listOptions[field.key] || []"
                    :optionLabel="field.optionLabel || 'nom_fr'"
                    :optionValue="field.optionValue || 'nom_fr'"
                    :placeholder="`Sélectionner ${field.label}`"
                    class="w-full"
                    :class="{ 'p-invalid': errors[field.key], 'arabic-text': field.key.includes('_ar') }"
                    @change="validateField(field.key, index)"
                >
                    <template #option="slotProps">
                        <div class="flex align-items-center">
                            <span :class="{ 'arabic-text': field.key.includes('_ar') }">
                                {{ slotProps.option[field.optionLabel || 'nom_fr'] }}
                            </span>
                        </div>
                    </template>
                </Dropdown>
                <Calendar
                    v-else-if="field.type === 'date'"
                    v-model="formData[field.key]"
                    dateFormat="dd/mm/yy"
                    inputClass="w-full"
                    :showIcon="true"
                    :placeholder="field.label"
                    class="w-full"
                    :class="{ 'p-invalid': errors[field.key] }"
                    @update:modelValue="validateField(field.key, index)"
                />
                <InputText
                    v-else
                    v-model="formData[field.key]"
                    :type="field.type === 'email' ? 'email' : 'text'"
                    :placeholder="field.label"
                    class="w-full"
                    :class="{ 'p-invalid': errors[field.key], 'arabic-text': field.key.includes('_ar') }"
                    @input="validateField(field.key, index)"
                />
                <small v-if="errors[field.key]" class="p-error block mt-1">
                    <i class="pi pi-exclamation-circle mr-1"></i>
                    {{ errors[field.key].join(', ') }}
                </small>
            </div>
        </div>

        <template #footer>
            <div class="flex justify-content-end gap-2">
                <Button
                    label="Annuler"
                    icon="pi pi-times"
                    class="p-button-outlined p-button-secondary"
                    @click="$emit('update:visible', false)"
                />
                <Button
                    label="Valider"
                    icon="pi pi-check"
                    class="p-button-primary"
                    @click="submitCorrections"
                    :loading="submitting"
                />
            </div>
        </template>
    </Dialog>
</template>

<script>
import { ref, reactive, watch } from 'vue';
import axios from 'axios';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import InputText from 'primevue/inputtext';
import Calendar from 'primevue/calendar';
import { useToast } from 'primevue/usetoast';

export default {
    components: {
        Button,
        Dialog,
        Dropdown,
        InputText,
        Calendar,
    },
    props: {
        visible: {
            type: Boolean,
            required: true,
        },
        errorData: {
            type: Object,
            default: null,
        },
        fields: {
            type: Array,
            required: true,
        },
        listOptions: {
            type: Object,
            required: true,
        },
    },
    emits: ['update:visible', 'corrected'],
    setup(props, { emit }) {
        const toast = useToast();
        const submitting = ref(false);
        const formData = ref({});
        const errors = reactive({});

        const initializeFormData = () => {
            formData.value = {};
            Object.keys(errors).forEach(key => delete errors[key]); // Réinitialiser les erreurs
            if (props.errorData?.errors) {
                Object.assign(errors, props.errorData.errors); // Charger les erreurs initiales
            }
            props.fields.forEach((field, index) => {
                const value = props.errorData?.parsedData[index] || null;
                formData.value[field.key] = field.type === 'date' ? parseDate(value) : value;
            });
        };

        const parseDate = (value) => {
            if (!value) return null;

            // Gestion du format JJ/MM/AAAA ou MM/JJ/AAAA
            const dateRegex = /^(\d{1,2})[\/-](\d{1,2})[\/-](\d{4})$/;
            if (dateRegex.test(value)) {
                const [, part1, part2, year] = value.match(dateRegex);
                // Essayer JJ/MM/AAAA
                let day = part1.padStart(2, '0');
                let month = part2.padStart(2, '0');
                let formatted = `${year}-${month}-${day}`;
                if (isValidDate(formatted)) return formatted;

                // Essayer MM/JJ/AAAA
                day = part2.padStart(2, '0');
                month = part1.padStart(2, '0');
                formatted = `${year}-${month}-${day}`;
                if (isValidDate(formatted)) return formatted;

                return null;
            }

            // Gestion du format AAAA-MM-JJ
            if (isValidDate(value)) {
                return value;
            }

            // Gestion des numéros de série Excel
            if (!isNaN(value) && !/^\d{4}-\d{2}-\d{2}$/.test(value)) {
                return excelSerialToDate(parseFloat(value));
            }

            // Tentative de parsing avec Date
            const parsed = new Date(value);
            if (!isNaN(parsed.getTime())) {
                const year = parsed.getFullYear();
                const month = String(parsed.getMonth() + 1).padStart(2, '0');
                const day = String(parsed.getDate()).padStart(2, '0');
                const formatted = `${year}-${month}-${day}`;
                return isValidDate(formatted) ? formatted : null;
            }

            return null;
        };

        const excelSerialToDate = (serial) => {
            if (!serial || isNaN(serial) || serial <= 0) return null;
            const excelEpoch = new Date(1899, 11, 30);
            const date = new Date(excelEpoch.getTime() + serial * 24 * 60 * 60 * 1000);
            if (isNaN(date.getTime())) return null;
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        };

        const isValidDate = (dateStr) => {
            if (!dateStr || !/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) return false;
            const [year, month, day] = dateStr.split('-').map(Number);
            const date = new Date(year, month - 1, day);
            return (
                !isNaN(date.getTime()) &&
                date.getFullYear() === year &&
                date.getMonth() + 1 === month &&
                date.getDate() === day
            );
        };

        watch(
            () => props.errorData,
            () => {
                if (props.errorData) {
                    initializeFormData();
                }
            },
            { immediate: true }
        );

        return {
            toast,
            submitting,
            formData,
            errors,
            isValidDate,
            parseDate,
            excelSerialToDate,
        };
    },
    methods: {
        validateField(fieldKey, index) {
            const field = this.fields[index];
            const value = this.formData[fieldKey] || null;
            const newErrors = { ...this.errors };

            if (field.required && !value) {
                newErrors[field.key] = [`Le champ '${field.label}' est obligatoire.`];
            } else if (field.type === 'text' && value && value.length > 255) {
                newErrors[field.key] = [`Le champ '${field.label}' ne doit pas dépasser 255 caractères.`];
            } else if (field.key === 'matricule' && value && value.length > 20) {
                newErrors[field.key] = ['Le matricule ne doit pas dépasser 20 caractères.'];
            } else if (field.key === 'cin' && value && !/^[A-Za-z0-9]{8}$/.test(value)) {
                newErrors[field.key] = ['Le CIN doit contenir exactement 8 caractères alphanumériques.'];
            } else if (['telephone_1', 'telephone_2'].includes(field.key) && value) {
                const cleanValue = value.replace(/[^+\d]/g, '');
                if (!/^\+?\d{8,15}$/.test(cleanValue)) {
                    newErrors[field.key] = [`Le numéro '${field.label}' doit contenir entre 8 et 15 chiffres.`];
                }
            } else if (field.key === 'email' && value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                newErrors[field.key] = ["L'adresse email est invalide."];
            } else if (field.type === 'list' && value && !this.listOptions[field.key]?.some(opt => opt[field.optionValue || 'nom_fr'] === value)) {
                newErrors[field.key] = [`La valeur pour '${field.label}' n'est pas valide.`];
            } else if (field.type === 'date' && value) {
                const parsed = this.parseDate(value);
                if (!parsed || !this.isValidDate(parsed)) {
                    newErrors[field.key] = [`La date '${field.label}' doit être au format JJ/MM/AAAA et valide.`];
                } else {
                    const date = new Date(parsed);
                    if (field.key !== 'date_fin_service' && date > new Date()) {
                        newErrors[field.key] = [`La date '${field.label}' doit être antérieure à aujourd'hui.`];
                    } else {
                        delete newErrors[field.key];
                    }
                    this.formData[fieldKey] = parsed; // Stocker la date au format AAAA-MM-JJ
                }
            } else {
                delete newErrors[field.key];
            }

            const dateNaissance = this.parseDate(this.formData['date_naissance']) || null;
            const dateCin = this.parseDate(this.formData['date_cin']) || null;
            const dateFinService = this.parseDate(this.formData['date_fin_service']) || null;
            const dateRecrutement = this.parseDate(this.formData['date_recrutement']) || null;

            if (dateNaissance && dateCin && dateCin < dateNaissance) {
                newErrors.date_cin = ['La date CIN doit être postérieure ou égale à la date de naissance.'];
            } else if (!newErrors.date_cin && this.errors.date_cin?.includes('La date CIN doit être postérieure ou égale à la date de naissance.')) {
                delete newErrors.date_cin;
            }

            if (dateFinService && dateRecrutement && dateFinService < dateRecrutement) {
                newErrors.date_fin_service = ['La date de fin de service doit être postérieure ou égale à la date de recrutement.'];
            } else if (!newErrors.date_fin_service && this.errors.date_fin_service?.includes('La date de fin de service doit être postérieure ou égale à la date de recrutement.')) {
                delete newErrors.date_fin_service;
            }

            // Mettre à jour les erreurs de manière réactive
            Object.keys(this.errors).forEach(key => delete this.errors[key]);
            Object.assign(this.errors, newErrors);
        },
        validateAllFields() {
            this.fields.forEach((field, index) => this.validateField(field.key, index));
        },
        async checkEmailUniqueness(email) {
            if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                this.errors.email = ["L'email est invalide."];
                return false;
            }

            try {
                const response = await axios.post('/api/personnels_atfp/check-email', { email }, {
                    headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
                });

                if (response.data.exists) {
                    this.errors.email = ['Cet email est déjà utilisé.'];
                    return false;
                }
                delete this.errors.email;
                return true;
            } catch (err) {
                console.error('Erreur lors de la vérification de l\'email:', err);
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Impossible de vérifier l\'unicité de l\'email.',
                    life: 5000,
                });
                return false;
            }
        },
        async submitCorrections() {
            this.submitting = true;
            try {
                // Valider tous les champs avant soumission
                this.validateAllFields();

                // Vérifier si des erreurs existent
                if (Object.keys(this.errors).length > 0) {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'Veuillez corriger les erreurs dans le formulaire.',
                        life: 5000,
                    });
                    return;
                }

                // Vérifier l'unicité de l'email si nécessaire
                if (this.formData.email) {
                    const isEmailUnique = await this.checkEmailUniqueness(this.formData.email);
                    if (!isEmailUnique) {
                        this.toast.add({
                            severity: 'error',
                            summary: 'Erreur',
                            detail: 'Veuillez corriger les erreurs dans le formulaire.',
                            life: 5000,
                        });
                        return;
                    }
                }

                // Si aucune erreur, émettre les données corrigées
                const correctedData = {
                    ...this.errorData,
                    parsedData: this.fields.map(field => this.formData[field.key]),
                    errors: this.errors,
                };

                this.$emit('corrected', correctedData);
                this.$emit('update:visible', false); // Fermer la modale après succès
            } catch (error) {
                console.error('Erreur lors de la soumission:', error);
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Échec de la soumission des corrections.',
                    life: 5000,
                });
            } finally {
                this.submitting = false;
            }
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

/* Styles existants inchangés */
.font-arabic,
:deep(.p-inputtext[dir='rtl']),
:deep(.p-textarea[dir='rtl']) {
    font-family: 'Montserrat-Arabic-Regular', sans-serif;
}

:deep(.p-tabview .p-tabview-nav) {
    background: var(--surface-ground);
    border-bottom: 1px solid var(--surface-border);
}

:deep(.p-tabview .p-tabview-nav li .p-tabview-nav-link) {
    background: transparent;
    border-color: transparent;
    color: var(--text-color-secondary);
    font-weight: 500;
    padding: 1rem 1.25rem;
    transition: all 0.2s ease;
}

:deep(.p-tabview .p-tabview-nav li.p-highlight .p-tabview-nav-link) {
    background-color: var(--primary-color);
    color: white;
}

:deep(.p-tabview .p-tabview-nav li .p-tabview-nav-link:not(.p-disabled):hover) {
    background-color: var(--surface-100);
}

.cropper-container {
    max-width: 400px;
    margin: 0 auto;
}

/* Style spécifique pour les messages d'erreur PrimeVue en arabe */
:deep(.p-error.font-arabic),
:deep(.p-error[dir='rtl']) {
    display: block;
    text-align: right;
    direction: rtl;
    font-family: 'Montserrat-Arabic-Regular', sans-serif;
    width: 100%;
    margin-right: 0;
    padding-right: 0;
}

/* Pour les textareas en arabe - inchangé */
:deep(.p-inputtext[dir='rtl']),
:deep(.p-textarea[dir='rtl']) {
    font-family: 'Montserrat-Arabic-Regular', sans-serif;
    text-align: right;
    direction: rtl;
}

.border-b-1 {
    border-bottom: 1px solid #4483c2; /* Light gray for a subtle, thin line */
}
</style>
```
