<template>
    <Dialog
        :visible="visible"
        @update:visible="$emit('update:visible', $event)"
        modal
        :pt="{
            root: 'border-none bg-transparent',
            mask: { style: 'backdrop-filter: blur(2px)' },
        }"
    >
        <template #container="{ closeCallback }">
            <div class="relative">
                <!-- Bouton de fermeture -->
                <span
                    class="absolute p-button p-button-icon-only p-button-text"
                    style="top: 12px; right: 12px"
                    @click="close"
                >
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

                <div class="p-5 surface-overlay" style="width: 500px">
                    <!-- Titre du formulaire -->
                    <h5 class="text-center font-bold text-lg mt-3">
                        {{
                            isEditing
                                ? 'Modifier un Secteur'
                                : 'Ajouter un Secteur'
                        }}
                    </h5>

                    <!-- Champ pour le code -->
                    <div class="flex flex-column gap-2">
                        <label for="code" class="font-semibold">Code</label>
                        <InputText
                            id="code"
                            v-model="newElement.code"
                            class="w-full"
                            @input="validateCode"
                        />
                        <small v-if="errors.code" class="text-red-500">{{
                            errors.code
                        }}</small>
                    </div>

                    <!-- Champ pour le nom en français -->
                    <div class="flex flex-column gap-2">
                        <label for="nom_fr" class="font-semibold"
                            >Nom (FR)</label
                        >
                        <InputText
                            id="nom_fr"
                            v-model="newElement.nom_fr"
                            class="w-full"
                            @input="validateNomFr"
                        />
                        <small v-if="errors.nom_fr" class="text-red-500">{{
                            errors.nom_fr
                        }}</small>
                    </div>

                    <!-- Champ pour le nom en arabe -->
                    <div class="flex flex-column gap-2">
                        <label for="nom_ar" class="font-semibold"
                            >Nom (AR)</label
                        >
                        <InputText
                            id="nom_ar"
                            v-model="newElement.nom_ar"
                            class="w-full"
                            @input="validateNomAr"
                        />
                        <small v-if="errors.nom_ar" class="text-red-500">{{
                            errors.nom_ar
                        }}</small>
                    </div>

                    <!-- Champ pour le type -->
                    <div class="flex flex-column gap-2">
                        <label for="type" class="font-semibold">Type</label>
                        <Dropdown
                            id="type"
                            v-model="newElement.type"
                            :options="types"
                            placeholder="Sélectionner un type"
                            class="w-full"
                            @change="validateType"
                        />
                        <small v-if="errors.type" class="text-red-500">{{
                            errors.type
                        }}</small>
                    </div>

                    <!-- Boutons d'action -->
                    <div class="flex gap-5 mt-4">
                        <Button
                            label="Annuler"
                            severity="danger"
                            outlined
                            @click="close"
                            class="w-full"
                        />
                        <Button
                            :label="isEditing ? 'Modifier' : 'Enregistrer'"
                            severity="success"
                            outlined
                            @click="isEditing ? update() : save()"
                            :disabled="isSaving"
                            class="w-full"
                        />
                    </div>
                </div>
            </div>
        </template>
    </Dialog>
</template>

<script>
import axios from 'axios';

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
        existingSecteurs: {
            type: Array,
            required: true,
        },
    },
    emits: ['update:visible', 'close', 'save', 'update'],
    data() {
        return {
            newElement: {
                code: '',
                nom_fr: '',
                nom_ar: '',
                type: null,
            },
            types: ['مقيس', 'غير مقيس'],
            errors: {
                code: '',
                nom_fr: '',
                nom_ar: '',
                type: '',
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
                    this.newElement = { ...newVal };
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
        'newElement.type'(newVal) {
            if (this.newElement.code && newVal) {
                this.validateCode();
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
                code: '',
                nom_fr: '',
                nom_ar: '',
                type: null,
            };
            this.errors = {
                code: '',
                nom_fr: '',
                nom_ar: '',
                type: '',
            };
        },
        validateCode() {
            if (!this.newElement.code) {
                this.errors.code = 'Le code est requis.';
            } else if (this.newElement.code.length > 255) {
                this.errors.code =
                    'Le code ne doit pas dépasser 255 caractères.';
            } else if (this.newElement.type) {
                const isUnique = !this.existingSecteurs.some(
                    (secteur) =>
                        secteur.code === this.newElement.code &&
                        secteur.type === this.newElement.type &&
                        (!this.isEditing || secteur.id !== this.initialData.id)
                );
                this.errors.code = isUnique
                    ? ''
                    : 'Ce code existe déjà pour ce type.';
            } else {
                this.errors.code = '';
            }
        },
        validateNomFr() {
            if (this.newElement.nom_fr && this.newElement.nom_fr.length > 255) {
                this.errors.nom_fr =
                    'Le nom en français ne doit pas dépasser 255 caractères.';
            } else {
                this.errors.nom_fr = '';
            }
        },
        validateNomAr() {
            if (!this.newElement.nom_ar) {
                this.errors.nom_ar = 'Le nom en arabe est requis.';
            } else if (this.newElement.nom_ar.length > 255) {
                this.errors.nom_ar =
                    'Le nom en arabe ne doit pas dépasser 255 caractères.';
            } else {
                this.errors.nom_ar = '';
            }
        },
        validateType() {
            if (!this.newElement.type) {
                this.errors.type = 'Le type est requis.';
            } else if (!this.types.includes(this.newElement.type)) {
                this.errors.type = "Le type doit être 'مقيس' ou 'غير مقيس'.";
            } else {
                this.errors.type = '';
            }
        },
        async save() {
            this.isSaving = true;

            this.validateCode();
            this.validateNomFr();
            this.validateNomAr();
            this.validateType();

            if (Object.values(this.errors).some((error) => error !== '')) {
                this.isSaving = false;
                return;
            }

            this.$emit('save', this.newElement);
            this.resetForm();
            this.isSaving = false;
        },
        async update() {
            this.isSaving = true;

            this.validateCode();
            this.validateNomFr();
            this.validateNomAr();
            this.validateType();

            if (Object.values(this.errors).some((error) => error !== '')) {
                this.isSaving = false;
                return;
            }

            const payload = { ...this.newElement };
            this.$emit('update', payload);
            this.resetForm();
            this.close();
            this.isSaving = false;
        },
    },
};
</script>
