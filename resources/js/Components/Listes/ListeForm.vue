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
                    :class="listeToEdit ? 'pi pi-pencil' : 'pi pi-plus'"
                    class="text-primary text-2xl"
                ></i>
                <span class="font-bold text-2xl">
                    {{
                        listeToEdit ? 'Modifier une Liste' : 'Ajouter une Liste'
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
                        v-model="liste.nom_fr"
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
                        v-model="liste.nom_ar"
                        :class="{ 'p-invalid': errors.nom_ar }"
                        class="w-full"
                        @input="validateNomAr"
                    />
                    <small v-if="errors.nom_ar" class="p-error">{{
                        errors.nom_ar
                    }}</small>
                </div>
                <div class="field">
                    <label for="description" class="block font-medium mb-2"
                        >Description</label
                    >
                    <Textarea
                        id="description"
                        v-model="liste.description"
                        rows="3"
                        class="w-full"
                        :class="{ 'p-invalid': errors.description }"
                        @input="validateDescription"
                        autoResize
                    />
                    <small v-if="errors.description" class="p-error">{{
                        errors.description
                    }}</small>
                </div>
                <div class="grid">
                    <div class="col-12 md:col-6 field">
                        <label for="couleur" class="block font-medium mb-2"
                            >Couleur</label
                        >
                        <ColorPicker
                            id="couleur"
                            v-model="liste.couleur"
                            format="hex"
                            class="w-full"
                            @change="normalizeAndValidateCouleur"
                        />
                        <small v-if="errors.couleur" class="p-error">{{
                            errors.couleur
                        }}</small>
                    </div>
                    <div class="col-12 md:col-6 field">
                        <label for="fond" class="block font-medium mb-2"
                            >Fond</label
                        >
                        <ColorPicker
                            id="fond"
                            v-model="liste.fond"
                            format="hex"
                            class="w-full"
                            @change="normalizeAndValidateFond"
                        />
                        <small v-if="errors.fond" class="p-error">{{
                            errors.fond
                        }}</small>
                    </div>
                </div>
                <div class="field">
                    <label for="icone" class="block font-medium mb-2"
                        >Icône</label
                    >
                    <InputText
                        id="icone"
                        v-model="liste.icone"
                        :class="{ 'p-invalid': errors.icone }"
                        class="w-full"
                        @input="validateIcone"
                    />
                    <small v-if="errors.icone" class="p-error">{{
                        errors.icone
                    }}</small>
                </div>
                <div class="field">
                    <label for="ordre" class="block font-medium mb-2"
                        >Ordre</label
                    >
                    <InputNumber
                        id="ordre"
                        v-model:ordre="liste.ordre"
                        :class="{ 'p-invalid': errors.ordre }"
                        class="w-full"
                        :min="0"
                        @input="validateOrdre"
                    />
                    <small v-if="errors.ordre" class="p-error">{{
                        errors.ordre
                    }}</small>
                </div>
                <div class="field">
                    <label for="statut" class="block font-medium mb-2"
                        >Statut <span class="text-red-500">*</span></label
                    >
                    <div class="flex align-items-center gap-2">
                        <Checkbox
                            id="statut"
                            v-model="statutChecked"
                            :binary="true"
                            @change="updateStatut"
                        />
                        <label for="statut" class="ml-2">{{
                            liste.statut
                        }}</label>
                    </div>
                    <small v-if="errors.statut" class="p-error">{{
                        errors.statut
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
                    :label="listeToEdit ? 'Mettre à jour' : 'Créer'"
                    :icon="listeToEdit ? 'pi pi-check' : 'pi pi-save'"
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
import Textarea from 'primevue/textarea';
import ColorPicker from 'primevue/colorpicker';
import Checkbox from 'primevue/checkbox';
import InputNumber from 'primevue/inputnumber';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';

export default {
    components: {
        Button,
        InputText,
        Textarea,
        ColorPicker,
        Checkbox,
        InputNumber,
        Dialog,
        Toast,
    },
    props: {
        visible: { type: Boolean, required: true },
        listeToEdit: { type: Object, default: null },
    },
    emits: ['update:visible', 'save', 'update', 'close'],
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            liste: {
                nom_fr: '',
                nom_ar: '',
                description: '',
                couleur: '',
                fond: '',
                icone: '',
                ordre: 0,
                statut: 'Actif',
                options: [],
            },
            statutChecked: true,
            errors: {
                nom_fr: '',
                nom_ar: '',
                description: '',
                couleur: '',
                fond: '',
                icone: '',
                ordre: '',
                statut: '',
            },
            isSaving: false,
        };
    },
    watch: {
        listeToEdit(newVal) {
            if (newVal) {
                this.liste = {
                    ...newVal,
                    couleur: newVal.couleur
                        ? this.normalizeHexColor(newVal.couleur)
                        : '',
                    fond: newVal.fond
                        ? this.normalizeHexColor(newVal.fond)
                        : '',
                    options: newVal.options ? [...newVal.options] : [],
                };
                this.statutChecked = newVal.statut === 'Actif';
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
        normalizeHexColor(color) {
            if (!color) return '';
            let cleanColor = color.replace(/^#/, '');
            if (cleanColor.length === 3) {
                cleanColor = cleanColor
                    .split('')
                    .map((char) => char + char)
                    .join('');
            }
            if (cleanColor.length === 6 && /^[0-9A-F]{6}$/i.test(cleanColor)) {
                return `#${cleanColor.toLowerCase()}`;
            }
            return color;
        },
        validateNomFr() {
            if (!this.liste.nom_fr) {
                this.errors.nom_fr = 'Le nom (FR) est requis';
            } else if (this.liste.nom_fr.length > 255) {
                this.errors.nom_fr =
                    'Le nom (FR) ne doit pas dépasser 255 caractères';
            } else {
                this.errors.nom_fr = '';
            }
        },
        validateNomAr() {
            if (!this.liste.nom_ar) {
                this.errors.nom_ar = 'Le nom (AR) est requis';
            } else if (this.liste.nom_ar.length > 255) {
                this.errors.nom_ar =
                    'Le nom (AR) ne doit pas dépasser 255 caractères';
            } else {
                this.errors.nom_ar = '';
            }
        },
        validateDescription() {
            if (this.liste.description && this.liste.description.length > 255) {
                this.errors.description =
                    'La description ne doit pas dépasser 255 caractères';
            } else {
                this.errors.description = '';
            }
        },
        normalizeAndValidateCouleur() {
            this.liste.couleur = this.normalizeHexColor(this.liste.couleur);
            if (
                this.liste.couleur &&
                !/^#[0-9A-F]{6}$/i.test(this.liste.couleur)
            ) {
                this.errors.couleur =
                    'La couleur doit être un code hexadécimal valide à 6 chiffres (ex. #ffffff)';
            } else {
                this.errors.couleur = '';
            }
        },
        normalizeAndValidateFond() {
            this.liste.fond = this.normalizeHexColor(this.liste.fond);
            if (this.liste.fond && !/^#[0-9A-F]{6}$/i.test(this.liste.fond)) {
                this.errors.fond =
                    'Le fond doit être un code hexadécimal valide à 6 chiffres (ex. #ffffff)';
            } else {
                this.errors.fond = '';
            }
        },
        validateIcone() {
            if (this.liste.icone && this.liste.icone.length > 255) {
                this.errors.icone =
                    "L'icône ne doit pas dépasser 255 caractères";
            } else {
                this.errors.icone = '';
            }
        },
        validateOrdre() {
            if (this.liste.ordre < 0) {
                this.errors.ordre = "L'ordre ne peut pas être négatif";
            } else {
                this.errors.ordre = '';
            }
        },
        validateStatut() {
            if (!this.liste.statut) {
                this.errors.statut = 'Le statut est requis';
            } else {
                this.errors.statut = '';
            }
        },
        updateStatut() {
            this.liste.statut = this.statutChecked ? 'Actif' : 'Inactif';
            this.validateStatut();
        },
        saveOrUpdate() {
            this.validateNomFr();
            this.validateNomAr();
            this.validateDescription();
            this.normalizeAndValidateCouleur();
            this.normalizeAndValidateFond();
            this.validateIcone();
            this.validateOrdre();
            this.validateStatut();

            if (
                this.errors.nom_fr ||
                this.errors.nom_ar ||
                this.errors.description ||
                this.errors.couleur ||
                this.errors.fond ||
                this.errors.icone ||
                this.errors.ordre ||
                this.errors.statut
            ) {
                this.isSaving = false;
                return;
            }

            this.isSaving = true;
            if (this.listeToEdit) {
                this.$emit('update', this.liste);
            } else {
                this.$emit('save', this.liste);
            }
            this.isSaving = false;
        },
        close() {
            this.$emit('close');
            this.$emit('update:visible', false);
            this.resetForm();
        },
        resetForm() {
            this.liste = {
                nom_fr: '',
                nom_ar: '',
                description: '',
                couleur: '',
                fond: '',
                icone: '',
                ordre: 0,
                statut: 'Actif',
                options: [],
            };
            this.statutChecked = true;
            this.errors = {
                nom_fr: '',
                nom_ar: '',
                description: '',
                couleur: '',
                fond: '',
                icone: '',
                ordre: '',
                statut: '',
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
