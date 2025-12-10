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
                    {{
                        isEditing
                            ? `Modifier Période - ${annee?.intitule || 'Année'}`
                            : `Ajouter Période - ${annee?.intitule || 'Année'}`
                    }}
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
                    <!-- Type Repos Field -->
                    <div class="field">
                        <label
                            for="type_repos_administratif_fr"
                            class="block font-medium mb-2"
                        >
                            Type de Repos <span class="text-red-500">*</span>
                        </label>
                        <Dropdown
                            id="type_repos_administratif_fr"
                            v-model="form.type_repos_administratif_fr"
                            :options="typesRepos"
                            optionLabel="nom_fr"
                            optionValue="nom_fr"
                            placeholder="Sélectionner un type"
                            class="w-full"
                            :class="{
                                'p-invalid': errors.type_repos_administratif_fr,
                            }"
                            :disabled="!typesRepos.length || loadingTypes"
                            :loading="loadingTypes"
                            @change="validateTypeFr"
                        />
                        <small
                            v-if="errors.type_repos_administratif_fr"
                            class="p-error"
                        >
                            {{ errors.type_repos_administratif_fr[0] }}
                        </small>
                        <small
                            v-else-if="!typesRepos.length && !loadingTypes"
                            class="text-red-500"
                        >
                            Aucun type de repos disponible. Veuillez contacter
                            l'administrateur.
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
                                :class="{ 'p-invalid': errors.date_debut }"
                                showIcon
                                :minDate="minDate"
                                :maxDate="maxDate"
                                @date-select="validateDateDebut"
                                @update:modelValue="validateDateDebut"
                            />
                            <small v-if="errors.date_debut" class="p-error">{{
                                errors.date_debut[0]
                            }}</small>
                        </div>
                        <div class="col-12 md:col-6 field">
                            <label
                                for="date_fin"
                                class="block font-medium mb-2"
                            >
                                Date Fin
                            </label>
                            <Calendar
                                id="date_fin"
                                v-model="form.date_fin"
                                dateFormat="yy-mm-dd"
                                class="w-full"
                                :class="{ 'p-invalid': errors.date_fin }"
                                showIcon
                                :minDate="form.date_debut || minDate"
                                :maxDate="maxDate"
                                @date-select="validateDateFin"
                                @update:modelValue="validateDateFin"
                            />
                            <small v-if="errors.date_fin" class="p-error">{{
                                errors.date_fin[0]
                            }}</small>
                        </div>
                    </div>

                    <!-- Description Field -->
                    <div class="field">
                        <label for="description" class="block font-medium mb-2">
                            Description
                        </label>
                        <Textarea
                            id="description"
                            v-model="form.description"
                            class="w-full"
                            rows="4"
                            :class="{ 'p-invalid': errors.description }"
                            autoResize
                            placeholder="Entrez une description (optionnel)"
                        />
                        <small v-if="errors.description" class="p-error">{{
                            errors.description[0]
                        }}</small>
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
import Dropdown from 'primevue/dropdown';
import Calendar from 'primevue/calendar';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';
import Toast from 'primevue/toast';
import ProgressSpinner from 'primevue/progressspinner';

export default {
    components: {
        Dropdown,
        Calendar,
        Button,
        Dialog,
        Textarea,
        Toast,
        ProgressSpinner,
    },
    props: {
        visible: { type: Boolean, required: true },
        annee: { type: Object, default: null },
        periodeToEdit: { type: Object, default: null },
    },
    emits: ['update:visible', 'close', 'save', 'update'],
    setup(props, { emit }) {
        const toast = useToast();
        const form = ref({
            annee_administrative_id: null,
            type_repos_administratif_fr: null,
            type_repos_administratif_ar: null,
            date_debut: null,
            date_fin: null,
            description: '',
        });
        const errors = ref({});
        const typesRepos = ref([]);
        const loadingTypes = ref(false);
        const isSaving = ref(false);
        const loading = ref(false);

        const isEditing = computed(() => !!props.periodeToEdit);
        const minDate = computed(() =>
            props.annee?.date_debut ? new Date(props.annee.date_debut) : null
        );
        const maxDate = computed(() =>
            props.annee?.date_fin ? new Date(props.annee.date_fin) : null
        );
        const isFormValid = computed(
            () =>
                !isSaving.value &&
                form.value.type_repos_administratif_fr &&
                form.value.date_debut &&
                Object.keys(errors.value).every(
                    (key) => !errors.value[key]?.length
                )
        );

        watch(
            () => props.visible,
            async (newVal) => {
                if (newVal) {
                    loading.value = true;
                    await fetchTypesRepos();
                    if (props.annee) {
                        form.value.annee_administrative_id = props.annee.id;
                    }
                    if (props.periodeToEdit) {
                        form.value = {
                            ...props.periodeToEdit,
                            date_debut: props.periodeToEdit.date_debut
                                ? new Date(props.periodeToEdit.date_debut)
                                : null,
                            date_fin: props.periodeToEdit.date_fin
                                ? new Date(props.periodeToEdit.date_fin)
                                : null,
                        };
                    }
                    loading.value = false;
                } else {
                    resetForm();
                }
            }
        );

        watch(
            () => props.annee,
            (newVal) => {
                if (newVal) {
                    form.value.annee_administrative_id = newVal.id;
                }
            }
        );

        watch(
            () => form.value.type_repos_administratif_fr,
            (newVal) => {
                if (newVal) {
                    const selectedType = typesRepos.value.find(
                        (type) => type.nom_fr === newVal
                    );
                    form.value.type_repos_administratif_ar = selectedType
                        ? selectedType.nom_ar
                        : null;
                } else {
                    form.value.type_repos_administratif_ar = null;
                }
            }
        );

        async function fetchTypesRepos() {
            loadingTypes.value = true;
            try {
                const response = await axios.get('/api/lists/options', {
                    params: { lists: 'Vacances et JF' },
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                    },
                });

                console.log('API Response:', response.data); // Debugging log

                const types = response.data['Vacances et JF'] || [];
                if (!Array.isArray(types)) {
                    throw new Error(
                        'La réponse ne contient pas un tableau valide pour "Vacances et JF".'
                    );
                }

                typesRepos.value = types;

                if (types.length === 0) {
                    toast.add({
                        severity: 'warn',
                        summary: 'Aucune donnée',
                        detail: "Aucun type de repos disponible. Veuillez contacter l'administrateur.",
                        life: 5000,
                    });
                }
            } catch (error) {
                console.error(
                    'Erreur lors du chargement des types de repos:',
                    error
                );
                let errorMessage =
                    error.response?.data?.message ||
                    error.message ||
                    'Impossible de charger les types de repos.';
                if (
                    typeof error.response?.data === 'string' &&
                    error.response.data.includes('<!DOCTYPE html')
                ) {
                    errorMessage =
                        "Erreur serveur: réponse HTML reçue au lieu de JSON. Vérifiez la configuration de l'API.";
                }
                toast.add({
                    severity: 'error',
                    summary: 'Erreur de chargement',
                    detail: errorMessage,
                    life: 5000,
                });
                typesRepos.value = [];
            } finally {
                loadingTypes.value = false;
            }
        }

        function resetForm() {
            form.value = {
                annee_administrative_id: props.annee?.id || null,
                type_repos_administratif_fr: null,
                type_repos_administratif_ar: null,
                date_debut: null,
                date_fin: null,
                description: '',
            };
            errors.value = {};
            isSaving.value = false;
        }

        return {
            toast,
            form,
            errors,
            typesRepos,
            loadingTypes,
            isSaving,
            loading,
            isEditing,
            minDate,
            maxDate,
            isFormValid,
            resetForm,
            fetchTypesRepos,
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
        validateTypeFr() {
            if (!this.form.type_repos_administratif_fr) {
                this.errors.type_repos_administratif_fr = [
                    'Le type de repos est obligatoire',
                ];
            } else if (
                !this.typesRepos.some(
                    (type) =>
                        type.nom_fr === this.form.type_repos_administratif_fr
                )
            ) {
                this.errors.type_repos_administratif_fr = [
                    'Type de repos invalide',
                ];
            } else {
                this.errors.type_repos_administratif_fr = [];
            }
        },
        validateDateDebut() {
            if (!this.form.date_debut) {
                this.errors.date_debut = ['La date de début est obligatoire'];
            } else if (this.minDate && this.form.date_debut < this.minDate) {
                this.errors.date_debut = [
                    `La date de début doit être après ${this.formatDate(this.minDate)}`,
                ];
            } else if (this.maxDate && this.form.date_debut > this.maxDate) {
                this.errors.date_debut = [
                    `La date de début doit être avant ${this.formatDate(this.maxDate)}`,
                ];
            } else {
                this.errors.date_debut = [];
                this.validateDateFin();
            }
        },
        validateDateFin() {
            if (this.form.date_fin && this.form.date_debut) {
                if (this.form.date_fin < this.form.date_debut) {
                    this.errors.date_fin = [
                        'La date de fin doit être postérieure ou égale à la date de début',
                    ];
                } else if (this.maxDate && this.form.date_fin > this.maxDate) {
                    this.errors.date_fin = [
                        `La date de fin doit être avant ${this.formatDate(this.maxDate)}`,
                    ];
                } else {
                    this.errors.date_fin = [];
                }
            } else {
                this.errors.date_fin = [];
            }
        },
        async submitForm() {
            this.validateTypeFr();
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
                ...this.form,
                date_debut: this.formatDate(this.form.date_debut),
                date_fin: this.form.date_fin
                    ? this.formatDate(this.form.date_fin)
                    : null,
            };

            try {
                let response;
                if (this.isEditing) {
                    response = await axios.put(
                        `/api/periodes-repos-administratif/${this.periodeToEdit.id}`,
                        payload
                    );
                    this.$emit('update', response.data);
                } else {
                    response = await axios.post(
                        `/api/periodes-repos-administratif`,
                        payload
                    );
                    this.$emit('save', response.data);
                }
                this.close();
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: this.isEditing
                        ? 'Période mise à jour avec succès'
                        : 'Période créée avec succès',
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
:deep(.p-dropdown),
:deep(.p-inputtextarea),
:deep(.p-calendar) {
    border-radius: 0.25rem;
    padding: 0.5rem;
    font-size: 0.875rem;
    border: 1px solid var(--surface-border);
}

:deep(.p-inputtext:focus),
:deep(.p-dropdown:focus),
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
