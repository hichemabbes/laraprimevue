<template>
    <Dialog
        :visible="visible"
        @update:visible="$emit('update:visible', $event)"
        :modal="true"
        :focusOnShow="false"
        :style="{ width: '80vw', maxWidth: '600px' }"
        :breakpoints="{ '960px': '95vw', '640px': '100vw' }"
        class="p-fluid"
        :pt="{
            root: 'border-round-xl shadow-5',
            mask: { style: 'backdrop-filter: blur(4px)' },
            header: { class: 'surface-50 border-bottom-1 surface-border p-4' },
            content: { class: 'surface-ground p-4' },
            footer: { class: 'surface-50 border-top-1 surface-border p-4' },
        }"
    >
        <template #header>
            <div class="flex align-items-center gap-2">
                <i
                    :class="isEditing ? 'pi pi-pencil' : 'pi pi-plus'"
                    class="text-primary text-2xl"
                ></i>
                <span class="font-bold text-2xl">
                    {{ isEditing ? 'Modifier un Grade' : 'Ajouter un nouveau grade' }}
                </span>
            </div>
        </template>

        <div
            v-if="loading"
            class="flex flex-column align-items-center justify-content-center gap-3 p-6"
        >
            <ProgressSpinner
                style="width: 50px; height: 50px"
                strokeWidth="4"
            />
            <span class="text-color-secondary">Chargement des données...</span>
        </div>

        <div v-else class="surface-card p-4 shadow-2 border-round">
            <form @submit.prevent="submitForm">
                <div class="flex flex-column gap-4">
                    <div class="grid">
                        <div class="col-12 md:col-6 field">
                            <label for="filiere_fr" class="block font-medium mb-2">
                                Filière (FR) <span class="text-red-500">*</span>
                            </label>
                            <InputText
                                id="filiere_fr"
                                v-model="form.filiere_fr"
                                class="w-full"
                                :class="{ 'p-invalid': errors.filiere_fr?.length }"
                                @input="validateFiliereFr"
                            />
                            <small v-if="errors.filiere_fr?.length" class="p-error">
                                {{ errors.filiere_fr[0] }}
                            </small>
                        </div>
                        <div class="col-12 md:col-6 field">
                            <label for="filiere_ar" class="block font-medium mb-2">
                                Filière (AR) <span class="text-red-500">*</span>
                            </label>
                            <InputText
                                id="filiere_ar"
                                v-model="form.filiere_ar"
                                class="w-full arabic-text"
                                :class="{ 'p-invalid': errors.filiere_ar?.length }"
                                @input="validateFiliereAr"
                            />
                            <small v-if="errors.filiere_ar?.length" class="p-error">
                                {{ errors.filiere_ar[0] }}
                            </small>
                        </div>
                    </div>
                    <div class="grid">
                        <div class="col-12 md:col-6 field">
                            <label for="grade_fr" class="block font-medium mb-2">
                                Grade (FR) <span class="text-red-500">*</span>
                            </label>
                            <InputText
                                id="grade_fr"
                                v-model="form.grade_fr"
                                class="w-full"
                                :class="{ 'p-invalid': errors.grade_fr?.length }"
                                @input="validateGradeFr"
                            />
                            <small v-if="errors.grade_fr?.length" class="p-error">
                                {{ errors.grade_fr[0] }}
                            </small>
                        </div>
                        <div class="col-12 md:col-6 field">
                            <label for="grade_ar" class="block font-medium mb-2">
                                Grade (AR) <span class="text-red-500">*</span>
                            </label>
                            <InputText
                                id="grade_ar"
                                v-model="form.grade_ar"
                                class="w-full arabic-text"
                                :class="{ 'p-invalid': errors.grade_ar?.length }"
                                @input="validateGradeAr"
                            />
                            <small v-if="errors.grade_ar?.length" class="p-error">
                                {{ errors.grade_ar[0] }}
                            </small>
                        </div>
                    </div>
                    <div class="grid">
                        <div class="col-12 md:col-6 field">
                            <label for="poste_fr" class="block font-medium mb-2">
                                Poste (FR) <span class="text-red-500">*</span>
                            </label>
                            <InputText
                                id="poste_fr"
                                v-model="form.poste_fr"
                                class="w-full"
                                :class="{ 'p-invalid': errors.poste_fr?.length }"
                                @input="validatePosteFr"
                            />
                            <small v-if="errors.poste_fr?.length" class="p-error">
                                {{ errors.poste_fr[0] }}
                            </small>
                        </div>
                        <div class="col-12 md:col-6 field">
                            <label for="poste_ar" class="block font-medium mb-2">
                                Poste (AR) <span class="text-red-500">*</span>
                            </label>
                            <InputText
                                id="poste_ar"
                                v-model="form.poste_ar"
                                class="w-full arabic-text"
                                :class="{ 'p-invalid': errors.poste_ar?.length }"
                                @input="validatePosteAr"
                            />
                            <small v-if="errors.poste_ar?.length" class="p-error">
                                {{ errors.poste_ar[0] }}
                            </small>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <template #footer>
            <div class="flex justify-content-end gap-2">
                <Button
                    label="Annuler"
                    icon="pi pi-times"
                    class="p-button-outlined p-button-secondary"
                    @click="close"
                />
                <Button
                    :label="isEditing ? 'Modifier' : 'Enregistrer'"
                    :icon="isEditing ? 'pi pi-check' : 'pi pi-save'"
                    class="p-button-primary"
                    :loading="isSaving"
                    :disabled="!isFormValid || isSaving"
                    @click="submitForm"
                />
            </div>
        </template>
    </Dialog>

    <Toast position="top-right" />
</template>

<script>
import { ref, computed, watch } from 'vue';
import { useToast } from 'primevue/usetoast';
import axios from '@/axios.js';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import ProgressSpinner from 'primevue/progressspinner';

export default {
    components: {
        InputText,
        Button,
        Dialog,
        Toast,
        ProgressSpinner,
    },
    props: {
        visible: { type: Boolean, required: true },
        gradeToEdit: { type: [Object, null], default: null },
    },
    emits: ['update:visible', 'save', 'update', 'close'],
    setup(props, { emit }) {
        const toast = useToast();
        const form = ref({
            id: null,
            filiere_fr: '',
            filiere_ar: '',
            grade_fr: '',
            grade_ar: '',
            poste_fr: '',
            poste_ar: '',
        });
        const errors = ref({});
        const isSaving = ref(false);
        const loading = ref(false);

        const isEditing = computed(() => {
            return !!props.gradeToEdit && typeof props.gradeToEdit === 'object' && props.gradeToEdit.id !== null;
        });

        const isFormValid = computed(
            () =>
                !isSaving.value &&
                form.value.filiere_fr &&
                form.value.filiere_ar &&
                form.value.grade_fr &&
                form.value.grade_ar &&
                form.value.poste_fr &&
                form.value.poste_ar &&
                Object.keys(errors.value).every(
                    (key) => !errors.value[key]?.length
                )
        );

        watch(
            () => props.visible,
            (newVal) => {
                if (newVal) {
                    loading.value = true;
                    errors.value = {};
                    if (props.gradeToEdit && props.gradeToEdit.id) {
                        form.value = {
                            id: props.gradeToEdit.id,
                            filiere_fr: props.gradeToEdit.filiere_fr || '',
                            filiere_ar: props.gradeToEdit.filiere_ar || '',
                            grade_fr: props.gradeToEdit.grade_fr || '',
                            grade_ar: props.gradeToEdit.grade_ar || '',
                            poste_fr: props.gradeToEdit.poste_fr || '',
                            poste_ar: props.gradeToEdit.poste_ar || '',
                        };
                    } else {
                        form.value = {
                            id: null,
                            filiere_fr: '',
                            filiere_ar: '',
                            grade_fr: '',
                            grade_ar: '',
                            poste_fr: '',
                            poste_ar: '',
                        };
                    }
                    loading.value = false;
                } else {
                    resetForm();
                }
            },
            { immediate: true }
        );

        function resetForm() {
            form.value = {
                id: null,
                filiere_fr: '',
                filiere_ar: '',
                grade_fr: '',
                grade_ar: '',
                poste_fr: '',
                poste_ar: '',
            };
            errors.value = {};
            isSaving.value = false;
        }

        return {
            toast,
            form,
            errors,
            isSaving,
            loading,
            isEditing,
            isFormValid,
            resetForm,
        };
    },
    methods: {
        validateFiliereFr() {
            if (!this.form.filiere_fr) {
                this.errors.filiere_fr = ['La filière en français est obligatoire'];
            } else if (this.form.filiere_fr.length > 255) {
                this.errors.filiere_fr = ['La filière en français ne doit pas dépasser 255 caractères'];
            } else {
                this.errors.filiere_fr = [];
            }
        },
        validateFiliereAr() {
            if (!this.form.filiere_ar) {
                this.errors.filiere_ar = ['La filière en arabe est obligatoire'];
            } else if (this.form.filiere_ar.length > 255) {
                this.errors.filiere_ar = ['La filière en arabe ne doit pas dépasser 255 caractères'];
            } else {
                this.errors.filiere_ar = [];
            }
        },
        validateGradeFr() {
            if (!this.form.grade_fr) {
                this.errors.grade_fr = ['Le grade en français est obligatoire'];
            } else if (this.form.grade_fr.length > 255) {
                this.errors.grade_fr = ['Le grade en français ne doit pas dépasser 255 caractères'];
            } else {
                this.errors.grade_fr = [];
            }
        },
        validateGradeAr() {
            if (!this.form.grade_ar) {
                this.errors.grade_ar = ['Le grade en arabe est obligatoire'];
            } else if (this.form.grade_ar.length > 255) {
                this.errors.grade_ar = ['Le grade en arabe ne doit pas dépasser 255 caractères'];
            } else {
                this.errors.grade_ar = [];
            }
        },
        validatePosteFr() {
            if (!this.form.poste_fr) {
                this.errors.poste_fr = ['Le poste en français est obligatoire'];
            } else if (this.form.poste_fr.length > 255) {
                this.errors.poste_fr = ['Le poste en français ne doit pas dépasser 255 caractères'];
            } else {
                this.errors.poste_fr = [];
            }
        },
        validatePosteAr() {
            if (!this.form.poste_ar) {
                this.errors.poste_ar = ['Le poste en arabe est obligatoire'];
            } else if (this.form.poste_ar.length > 255) {
                this.errors.poste_ar = ['Le poste en arabe ne doit pas dépasser 255 caractères'];
            } else {
                this.errors.poste_ar = [];
            }
        },
        async submitForm() {
            this.errors = {};
            this.validateFiliereFr();
            this.validateFiliereAr();
            this.validateGradeFr();
            this.validateGradeAr();
            this.validatePosteFr();
            this.validatePosteAr();

            if (Object.values(this.errors).some((error) => error?.length > 0)) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur de validation',
                    detail: 'Veuillez corriger les erreurs dans le formulaire',
                    life: 3000,
                });
                return;
            }

            this.isSaving = true;

            const payload = {
                filiere_fr: this.form.filiere_fr,
                filiere_ar: this.form.filiere_ar,
                grade_fr: this.form.grade_fr,
                grade_ar: this.form.grade_ar,
                poste_fr: this.form.poste_fr,
                poste_ar: this.form.poste_ar,
            };

            try {
                let response;
                if (this.isEditing) {
                    response = await axios.put(`/api/grades/${this.form.id}`, payload);
                    this.$emit('update', response.data.grade);
                } else {
                    response = await axios.post('/api/grades', payload);
                    this.$emit('save', response.data.grade);
                }
                this.close();
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: this.isEditing
                        ? 'Grade mis à jour avec succès'
                        : 'Grade créé avec succès',
                    life: 3000,
                });
            } catch (error) {
                if (error.response?.status === 422) {
                    this.errors = Object.keys(error.response.data.errors).reduce(
                        (acc, key) => {
                            acc[key] = Array.isArray(error.response.data.errors[key])
                                ? error.response.data.errors[key]
                                : [error.response.data.errors[key]];
                            return acc;
                        },
                        {}
                    );
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur de validation',
                        detail: 'Veuillez vérifier les champs du formulaire',
                        life: 3000,
                    });
                } else {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail:
                            error.response?.data?.message ||
                            "Erreur lors de l'enregistrement",
                        life: 5000,
                    });
                }
            } finally {
                this.isSaving = false;
            }
        },
        close() {
            this.resetForm();
            this.$emit('update:visible', false);
            this.$emit('close');
        },
    },
};
</script>

<style scoped>
@font-face {
    font-family: 'NotoNaskhArabic';
    src: url('/fonts/NotoNaskhArabic-VariableFont_wght.ttf') format('opentype');
    font-weight: normal;
    font-style: normal;
    font-display: swap;
}

.arabic-text {
    font-family: 'NotoNaskhArabic', sans-serif;
    direction: rtl;
    text-align: right;
}

.field {
    margin-bottom: 1.5rem;
}

label {
    margin-bottom: 0.5rem;
    display: block;
    color: var(--text-color);
    font-weight: 500;
}

:deep(.p-error) {
    font-size: 0.75rem;
    color: var(--red-500);
}

:deep(.p-inputtext) {
    border-radius: 0.25rem;
    padding: 0.5rem;
    font-size: 0.875rem;
    border: 1px solid var(--surface-border);
}

:deep(.p-inputtext:focus) {
    box-shadow: 0 0 0 0.2rem var(--primary-color);
    border-color: var(--primary-color);
}

:deep(.p-invalid) {
    border-color: var(--red-500) !important;
}

@media (max-width: 640px) {
    .field {
        margin-bottom: 1rem;
    }
}
</style>
