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
                    {{ isEditing ? 'Modifier une Année' : 'Ajouter une Année' }}
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
                    <!-- Intitulé Field -->
                    <div class="field">
                        <label for="intitule" class="block font-medium mb-2">
                            Intitulé <span class="text-red-500">*</span>
                        </label>
                        <InputText
                            id="intitule"
                            v-model="form.intitule"
                            class="w-full"
                            :class="{ 'p-invalid': errors.intitule?.length }"
                            @input="validateIntitule"
                        />
                        <small v-if="errors.intitule?.length" class="p-error">
                            {{ errors.intitule[0] }}
                        </small>
                    </div>

                    <!-- Date Fields -->
                    <div class="grid">
                        <div class="col-12 md:col-6 field">
                            <label
                                for="date_debut"
                                class="block font-medium mb-2"
                            >
                                Date Début <span class="text-red-500">*</span>
                            </label>
                            <Calendar
                                id="date_debut"
                                v-model="form.date_debut"
                                dateFormat="yy-mm-dd"
                                class="w-full"
                                :class="{
                                    'p-invalid': errors.date_debut?.length,
                                }"
                                showIcon
                                @date-select="validateDateDebut"
                                @update:modelValue="validateDateDebut"
                            />
                            <small
                                v-if="errors.date_debut?.length"
                                class="p-error"
                            >
                                {{ errors.date_debut[0] }}
                            </small>
                        </div>
                        <div class="col-12 md:col-6 field">
                            <label
                                for="date_fin"
                                class="block font-medium mb-2"
                            >
                                Date Fin <span class="text-red-500">*</span>
                            </label>
                            <Calendar
                                id="date_fin"
                                v-model="form.date_fin"
                                dateFormat="yy-mm-dd"
                                class="w-full"
                                :class="{
                                    'p-invalid': errors.date_fin?.length,
                                }"
                                showIcon
                                :minDate="form.date_debut"
                                @date-select="validateDateFin"
                                @update:modelValue="validateDateFin"
                            />
                            <small
                                v-if="errors.date_fin?.length"
                                class="p-error"
                            >
                                {{ errors.date_fin[0] }}
                            </small>
                        </div>
                    </div>

                    <!-- Observation Field -->
                    <div class="field">
                        <label for="observation" class="block font-medium mb-2">
                            Observation
                        </label>
                        <Textarea
                            id="observation"
                            v-model="form.observation"
                            class="w-full"
                            rows="4"
                            :class="{ 'p-invalid': errors.observation?.length }"
                            autoResize
                            placeholder="Entrez une observation (optionnel)"
                        />
                        <small
                            v-if="errors.observation?.length"
                            class="p-error"
                        >
                            {{ errors.observation[0] }}
                        </small>
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
import axios from 'axios';
import InputText from 'primevue/inputtext';
import Calendar from 'primevue/calendar';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';
import Toast from 'primevue/toast';
import ProgressSpinner from 'primevue/progressspinner';

export default {
    components: {
        InputText,
        Calendar,
        Button,
        Dialog,
        Textarea,
        Toast,
        ProgressSpinner,
    },
    props: {
        visible: { type: Boolean, required: true },
        anneeToEdit: { type: Object, default: null },
    },
    emits: ['update:visible', 'close', 'save', 'update'],
    setup(props, { emit }) {
        const toast = useToast();
        const form = ref({
            intitule: '',
            date_debut: null,
            date_fin: null,
            observation: '',
        });
        const errors = ref({});
        const isSaving = ref(false);
        const loading = ref(false);

        const isEditing = computed(() => !!props.anneeToEdit);
        const isFormValid = computed(
            () =>
                !isSaving.value &&
                form.value.intitule &&
                form.value.date_debut &&
                form.value.date_fin &&
                Object.keys(errors.value).every(
                    (key) => !errors.value[key]?.length
                )
        );

        watch(
            () => props.visible,
            (newVal) => {
                if (newVal) {
                    loading.value = true;
                    if (props.anneeToEdit) {
                        form.value = {
                            ...props.anneeToEdit,
                            date_debut: props.anneeToEdit.date_debut
                                ? new Date(props.anneeToEdit.date_debut)
                                : null,
                            date_fin: props.anneeToEdit.date_fin
                                ? new Date(props.anneeToEdit.date_fin)
                                : null,
                        };
                    }
                    loading.value = false;
                } else {
                    resetForm();
                }
            }
        );

        function resetForm() {
            form.value = {
                intitule: '',
                date_debut: null,
                date_fin: null,
                observation: '',
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
        formatDate(date) {
            if (!date || !(date instanceof Date)) return null;
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        },
        validateIntitule() {
            if (!this.form.intitule) {
                this.errors.intitule = ["L'intitulé est obligatoire"];
            } else if (this.form.intitule.length > 255) {
                this.errors.intitule = [
                    "L'intitulé ne doit pas dépasser 255 caractères",
                ];
            } else {
                this.errors.intitule = [];
            }
        },
        validateDateDebut() {
            if (!this.form.date_debut) {
                this.errors.date_debut = ['La date de début est obligatoire'];
            } else {
                this.errors.date_debut = [];
                this.validateDateFin();
            }
        },
        validateDateFin() {
            if (!this.form.date_fin) {
                this.errors.date_fin = ['La date de fin est obligatoire'];
            } else if (
                this.form.date_debut &&
                this.form.date_fin <= this.form.date_debut
            ) {
                this.errors.date_fin = [
                    'La date de fin doit être postérieure à la date de début',
                ];
            } else {
                this.errors.date_fin = [];
            }
        },
        async submitForm() {
            this.validateIntitule();
            this.validateDateDebut();
            this.validateDateFin();

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
                intitule: this.form.intitule,
                date_debut: this.formatDate(this.form.date_debut),
                date_fin: this.formatDate(this.form.date_fin),
                observation: this.form.observation || '',
            };

            try {
                let response;
                if (this.isEditing) {
                    response = await axios.put(
                        `/api/annees-formation/${this.anneeToEdit.id}`,
                        payload
                    );
                    this.$emit('update', response.data);
                } else {
                    response = await axios.post(
                        `/api/annees-formation`,
                        payload
                    );
                    this.$emit('save', response.data);
                }
                this.close();
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: this.isEditing
                        ? 'Année de formation mise à jour avec succès'
                        : 'Année de formation créée avec succès',
                    life: 3000,
                });
            } catch (error) {
                if (error.response?.status === 422) {
                    this.errors = Object.keys(
                        error.response.data.errors
                    ).reduce((acc, key) => {
                        acc[key] = Array.isArray(
                            error.response.data.errors[key]
                        )
                            ? error.response.data.errors[key]
                            : [error.response.data.errors[key]];
                        return acc;
                    }, {});
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
                        life: 3000,
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

:deep(.p-inputtext),
:deep(.p-inputtextarea),
:deep(.p-calendar) {
    border-radius: 0.25rem;
    padding: 0.5rem;
    font-size: 0.875rem;
    border: 1px solid var(--surface-border);
}

:deep(.p-inputtext:focus),
:deep(.p-inputtextarea:focus),
:deep(.p-calendar:focus) {
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
