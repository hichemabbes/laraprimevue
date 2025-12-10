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
                    :class="optionToEdit ? 'pi pi-pencil' : 'pi pi-plus'"
                    class="text-primary text-2xl"
                ></i>
                <span class="font-bold text-2xl">
                    {{
                        optionToEdit
                            ? 'Modifier une Option'
                            : 'Ajouter une Option'
                    }}
                </span>
            </div>
        </template>

        <div class="surface-card p-4 shadow-2 border-round">
            <div class="flex flex-column gap-4">
                <div class="field">
                    <label for="nom_fr" class="block font-medium mb-2"
                        >Nom (FR) <span class="text-red-500">*</span></label
                    >
                    <InputText
                        id="nom_fr"
                        v-model="option.nom_fr"
                        :class="{ 'p-invalid': errors.nom_fr }"
                        class="w-full"
                        @input="validateNomFr"
                    />
                    <small v-if="errors.nom_fr" class="p-error">{{
                        errors.nom_fr
                    }}</small>
                </div>
                <div class="field">
                    <label for="nom_ar" class="block font-medium mb-2"
                        >Nom (AR) <span class="text-red-500">*</span></label
                    >
                    <InputText
                        id="nom_ar"
                        v-model="option.nom_ar"
                        :class="{ 'p-invalid': errors.nom_ar }"
                        class="w-full"
                        @input="validateNomAr"
                    />
                    <small v-if="errors.nom_ar" class="p-error">{{
                        errors.nom_ar
                    }}</small>
                </div>
                <div class="field">
                    <label for="valeur" class="block font-medium mb-2"
                        >Valeur <span class="text-red-500">*</span></label
                    >
                    <InputText
                        id="valeur"
                        v-model="option.valeur"
                        :class="{ 'p-invalid': errors.valeur }"
                        class="w-full"
                        @input="validateValeur"
                    />
                    <small v-if="errors.valeur" class="p-error">{{
                        errors.valeur
                    }}</small>
                </div>
            </div>
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
                    :label="optionToEdit ? 'Mettre à jour' : 'Créer'"
                    :icon="optionToEdit ? 'pi pi-check' : 'pi pi-save'"
                    class="p-button-primary"
                    :loading="isSaving"
                    @click="saveOrUpdate"
                />
            </div>
        </template>
    </Dialog>

    <Toast />
</template>

<script>
import { useToast } from 'primevue/usetoast';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';

export default {
    components: {
        Button,
        InputText,
        Dialog,
        Toast,
    },
    props: {
        visible: { type: Boolean, required: true },
        optionToEdit: { type: Object, default: null },
        selectedListe: { type: Object, default: null },
    },
    emits: ['update:visible', 'save', 'update', 'close'],
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            option: {
                nom_fr: '',
                nom_ar: '',
                valeur: '',
            },
            errors: {
                nom_fr: '',
                nom_ar: '',
                valeur: '',
            },
            isSaving: false,
        };
    },
    watch: {
        optionToEdit(newVal) {
            if (newVal) {
                this.option = { ...newVal };
            } else {
                this.resetForm();
            }
        },
        visible(newVal) {
            if (!newVal) {
                this.resetForm();
            }
        },
    },
    methods: {
        validateNomFr() {
            if (!this.option.nom_fr) {
                this.errors.nom_fr = 'Le nom (FR) est requis';
            } else if (this.option.nom_fr.length > 255) {
                this.errors.nom_fr =
                    'Le nom (FR) ne doit pas dépasser 255 caractères';
            } else {
                this.errors.nom_fr = '';
            }
        },
        validateNomAr() {
            if (!this.option.nom_ar) {
                this.errors.nom_ar = 'Le nom (AR) est requis';
            } else if (this.option.nom_ar.length > 255) {
                this.errors.nom_ar =
                    'Le nom (AR) ne doit pas dépasser 255 caractères';
            } else {
                this.errors.nom_ar = '';
            }
        },
        validateValeur() {
            if (!this.option.valeur) {
                this.errors.valeur = 'La valeur est requise';
            } else if (this.option.valeur.length > 255) {
                this.errors.valeur =
                    'La valeur ne doit pas dépasser 255 caractères';
            } else if (!/^[a-zA-Z0-9_-]+$/.test(this.option.valeur)) {
                this.errors.valeur =
                    'La valeur ne doit contenir que des lettres, chiffres, tirets ou underscores';
            } else if (
                !this.optionToEdit &&
                this.selectedListe?.options?.some(
                    (opt) => opt.valeur === this.option.valeur
                )
            ) {
                this.errors.valeur = 'Cette valeur existe déjà dans la liste';
            } else {
                this.errors.valeur = '';
            }
        },
        saveOrUpdate() {
            this.validateNomFr();
            this.validateNomAr();
            this.validateValeur();

            if (
                this.errors.nom_fr ||
                this.errors.nom_ar ||
                this.errors.valeur
            ) {
                this.isSaving = false;
                return;
            }

            this.isSaving = true;
            if (this.optionToEdit) {
                this.$emit('update', this.option);
            } else {
                this.$emit('save', this.option);
            }
            this.isSaving = false;
        },
        close() {
            this.$emit('close');
            this.$emit('update:visible', false);
            this.resetForm();
        },
        resetForm() {
            this.option = {
                nom_fr: '',
                nom_ar: '',
                valeur: '',
            };
            this.errors = {
                nom_fr: '',
                nom_ar: '',
                valeur: '',
            };
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
