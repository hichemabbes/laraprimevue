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
                    {{ isEditing ? 'Modifier un Rôle' : 'Ajouter un nouveau rôle' }}
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
                            <label for="name" class="block font-medium mb-2">
                                Nom <span class="text-red-500">*</span>
                            </label>
                            <InputText
                                id="name"
                                v-model="form.name"
                                class="w-full"
                                :class="{ 'p-invalid': errors.name?.length }"
                                @input="validateName"
                            />
                            <small v-if="errors.name?.length" class="p-error">
                                {{ errors.name[0] }}
                            </small>
                        </div>
                        <div class="col-12 md:col-6 field">
                            <label for="name_ar" class="block font-medium mb-2">
                                Nom (Arabe) <span class="text-red-500">*</span>
                            </label>
                            <InputText
                                id="name_ar"
                                v-model="form.name_ar"
                                class="w-full arabic-text"
                                :class="{ 'p-invalid': errors.name_ar?.length }"
                                @input="validateNameAr"
                            />
                            <small v-if="errors.name_ar?.length" class="p-error">
                                {{ errors.name_ar[0] }}
                            </small>
                        </div>
                    </div>
                    <div class="grid">
                        <div class="col-12 md:col-6 field">
                            <label for="guard_name" class="block font-medium mb-2">
                                Guard
                            </label>
                            <Dropdown
                                id="guard_name"
                                v-model="form.guard_name"
                                :options="guardOptions"
                                optionLabel="label"
                                optionValue="value"
                                placeholder="Sélectionner un guard (facultatif)"
                                :showClear="true"
                                class="w-full"
                            />
                        </div>
                        <div class="col-12 md:col-6 field">
                            <label for="is_centre_role" class="block font-medium mb-2">
                                Rôle lié à un centre
                            </label>
                            <InputSwitch
                                id="is_centre_role"
                                v-model="form.is_centre_role"
                                class="w-full"
                            />
                        </div>
                    </div>
                    <div class="grid">
                        <div class="col-12 md:col-6 field">
                            <label for="statut" class="block font-medium mb-2">
                                Statut <span class="text-red-500">*</span>
                            </label>
                            <Dropdown
                                id="statut"
                                v-model="form.statut"
                                :options="statutOptions"
                                optionLabel="label"
                                optionValue="value"
                                placeholder="Sélectionner un statut"
                                class="w-full"
                                :class="{ 'p-invalid': errors.statut?.length }"
                                @change="validateStatut"
                            />
                            <small v-if="errors.statut?.length" class="p-error">
                                {{ errors.statut[0] }}
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
import Dropdown from 'primevue/dropdown';
import InputSwitch from 'primevue/inputswitch';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import ProgressSpinner from 'primevue/progressspinner';

export default {
    components: {
        InputText,
        Dropdown,
        InputSwitch,
        Button,
        Dialog,
        Toast,
        ProgressSpinner,
    },
    props: {
        visible: { type: Boolean, required: true },
        roleToEdit: { type: [Object, null], default: null },
    },
    emits: ['update:visible', 'save', 'update', 'close'],
    setup(props, { emit }) {
        const toast = useToast();
        const form = ref({
            id: null,
            name: '',
            name_ar: '',
            guard_name: null,
            is_centre_role: false,
            statut: 'actif',
        });
        const errors = ref({});
        const isSaving = ref(false);
        const loading = ref(false);

        const isEditing = computed(() => {
            console.log('roleToEdit:', props.roleToEdit); // Debug log
            return !!props.roleToEdit && typeof props.roleToEdit === 'object' && props.roleToEdit.id !== null;
        });

        const isFormValid = computed(
            () =>
                !isSaving.value &&
                form.value.name &&
                form.value.name_ar &&
                form.value.statut &&
                Object.keys(errors.value).every(
                    (key) => !errors.value[key]?.length
                )
        );

        watch(
            () => props.visible,
            (newVal) => {
                console.log('Visible changed:', newVal, 'roleToEdit:', props.roleToEdit); // Debug log
                if (newVal) {
                    loading.value = true;
                    errors.value = {};
                    if (props.roleToEdit && props.roleToEdit.id) {
                        form.value = {
                            id: props.roleToEdit.id,
                            name: props.roleToEdit.name || '',
                            name_ar: props.roleToEdit.name_ar || '',
                            guard_name: props.roleToEdit.guard_name || null,
                            is_centre_role: !!props.roleToEdit.is_centre_role,
                            statut: props.roleToEdit.statut || 'actif',
                        };
                    } else {
                        form.value = {
                            id: null,
                            name: '',
                            name_ar: '',
                            guard_name: null,
                            is_centre_role: false,
                            statut: 'actif',
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
                name: '',
                name_ar: '',
                guard_name: null,
                is_centre_role: false,
                statut: 'actif',
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
    data() {
        return {
            guardOptions: [
                { label: 'Aucun', value: null },
                { label: 'Web', value: 'web' },
                { label: 'Sanctum', value: 'sanctum' },
            ],
            statutOptions: [
                { label: 'Actif', value: 'actif' },
                { label: 'Inactif', value: 'inactif' },
            ],
        };
    },
    methods: {
        validateName() {
            if (!this.form.name) {
                this.errors.name = ['Le nom est obligatoire'];
            } else if (this.form.name.length > 255) {
                this.errors.name = ['Le nom ne doit pas dépasser 255 caractères'];
            } else {
                this.errors.name = [];
            }
        },
        validateNameAr() {
            if (!this.form.name_ar) {
                this.errors.name_ar = ['Le nom en arabe est obligatoire'];
            } else if (this.form.name_ar.length > 255) {
                this.errors.name_ar = ['Le nom en arabe ne doit pas dépasser 255 caractères'];
            } else {
                this.errors.name_ar = [];
            }
        },
        validateStatut() {
            if (!this.form.statut) {
                this.errors.statut = ['Le statut est obligatoire'];
            } else {
                this.errors.statut = [];
            }
        },
        async submitForm() {
            this.errors = {}; // Reset errors before validation
            this.validateName();
            this.validateNameAr();
            this.validateStatut();

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
                name: this.form.name,
                name_ar: this.form.name_ar,
                guard_name: this.form.guard_name,
                is_centre_role: this.form.is_centre_role,
                statut: this.form.statut,
            };

            try {
                let response;
                if (this.isEditing) {
                    response = await axios.put(`/api/roles/${this.form.id}`, payload);
                    this.$emit('update', response.data.role);
                } else {
                    response = await axios.post('/api/roles', payload);
                    this.$emit('save', response.data.role);
                }
                this.close();
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: this.isEditing
                        ? 'Rôle mis à jour avec succès'
                        : 'Rôle créé avec succès',
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

:deep(.p-inputtext),
:deep(.p-dropdown),
:deep(.p-inputswitch) {
    border-radius: 0.25rem;
    padding: 0.5rem;
    font-size: 0.875rem;
    border: 1px solid var(--surface-border);
}

:deep(.p-inputtext:focus),
:deep(.p-dropdown:focus),
:deep(.p-inputswitch:focus) {
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
