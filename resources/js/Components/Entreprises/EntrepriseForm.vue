<template>
    <Dialog
        :visible="visible"
        @update:visible="$emit('update:visible', $event)"
        modal
        :pt="{
            root: 'border-none',
            mask: { style: 'backdrop-filter: blur(2px)' },
        }"
    >
        <template #container="{ closeCallback }">
            <div class="relative">
                <span
                    class="absolute cursor-pointer p-button p-button-icon-only p-button-text"
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

                <div class="p-5 surface-card">
                    <h5 class="text-center font-bold text-lg mt-3">
                        {{
                            isEditing
                                ? 'Modifier une Entreprise'
                                : 'Ajouter une Entreprise'
                        }}
                    </h5>

                    <div class="flex flex-column gap-2">
                        <label for="code_entre" class="font-semibold"
                            >Code</label
                        >
                        <InputText
                            id="code_entre"
                            v-model="newEntreprise.code_entre"
                            class="w-full"
                            @input="validateCode"
                        />
                        <small v-if="errors.code_entre" class="text-red-500">{{
                            errors.code_entre
                        }}</small>
                    </div>

                    <div class="flex flex-column gap-2">
                        <label for="nom_fr" class="font-semibold"
                            >Nom (FR)</label
                        >
                        <InputText
                            id="nom_fr"
                            v-model="newEntreprise.nom_fr"
                            class="w-full"
                            @input="validateNomFr"
                        />
                        <small v-if="errors.nom_fr" class="text-red-500">{{
                            errors.nom_fr
                        }}</small>
                    </div>

                    <div class="flex flex-column gap-2">
                        <label for="nom_ar" class="font-semibold"
                            >Nom (AR)</label
                        >
                        <InputText
                            id="nom_ar"
                            v-model="newEntreprise.nom_ar"
                            class="w-full"
                            @input="validateNomAr"
                        />
                        <small v-if="errors.nom_ar" class="text-red-500">{{
                            errors.nom_ar
                        }}</small>
                    </div>

                    <div class="flex flex-column gap-2">
                        <label for="email_entr" class="font-semibold"
                            >Email</label
                        >
                        <InputText
                            id="email_entr"
                            v-model="newEntreprise.email_entr"
                            class="w-full"
                            @input="validateEmail"
                        />
                        <small v-if="errors.email_entr" class="text-red-500">{{
                            errors.email_entr
                        }}</small>
                    </div>

                    <div class="flex flex-column gap-2">
                        <label for="secteur_id" class="font-semibold"
                            >Secteur</label
                        >
                        <Dropdown
                            id="secteur_id"
                            v-model="newEntreprise.secteur_id"
                            :options="secteurs"
                            optionLabel="nom_fr"
                            optionValue="id"
                            placeholder="Sélectionner un secteur"
                            class="w-full"
                        />
                        <small v-if="errors.secteur_id" class="text-red-500">{{
                            errors.secteur_id
                        }}</small>
                    </div>

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
                            @click="
                                isEditing
                                    ? updateEntreprise()
                                    : saveEntreprise()
                            "
                            :disabled="isSaving"
                            class="w-full"
                        />
                    </div>
                </div>
            </div>
        </template>
    </Dialog>

    <Toast />
</template>

<script>
import { useToast } from 'primevue/usetoast';
import axios from 'axios';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';

export default {
    components: {
        InputText,
        Dropdown,
        Button,
        Dialog,
        Toast,
    },
    props: {
        visible: { type: Boolean, required: true },
        entrepriseToEdit: { type: Object, default: null },
    },
    emits: ['update:visible', 'close', 'save', 'update'],
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            newEntreprise: {
                code_entre: '',
                nom_fr: '',
                nom_ar: '',
                email_entr: '',
                secteur_id: null,
            },
            secteurs: [],
            errors: {
                code_entre: '',
                nom_fr: '',
                nom_ar: '',
                email_entr: '',
                secteur_id: '',
            },
            isSaving: false,
        };
    },
    computed: {
        isEditing() {
            return !!this.entrepriseToEdit;
        },
    },
    watch: {
        entrepriseToEdit: {
            immediate: true,
            handler(newVal) {
                if (newVal) {
                    this.newEntreprise = { ...newVal };
                } else {
                    this.resetForm();
                }
            },
        },
        visible(newVal) {
            if (newVal) {
                this.fetchSecteurs();
            } else {
                this.resetForm();
            }
        },
    },
    methods: {
        async fetchSecteurs() {
            try {
                const response = await axios.get('/api/secteurs');
                this.secteurs = response.data;
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Erreur lors du chargement des secteurs.',
                    life: 3000,
                });
            }
        },
        close() {
            this.resetForm();
            this.$emit('update:visible', false);
            this.$emit('close');
        },
        resetForm() {
            this.newEntreprise = {
                code_entre: '',
                nom_fr: '',
                nom_ar: '',
                email_entr: '',
                secteur_id: null,
            };
            this.errors = {
                code_entre: '',
                nom_fr: '',
                nom_ar: '',
                email_entr: '',
                secteur_id: '',
            };
        },
        validateCode() {
            if (!this.newEntreprise.code_entre) {
                this.errors.code_entre = 'Le code est obligatoire.';
            } else if (this.newEntreprise.code_entre.length > 50) {
                this.errors.code_entre =
                    'Le code ne doit pas dépasser 50 caractères.';
            } else {
                this.errors.code_entre = '';
            }
        },
        validateNomFr() {
            if (!this.newEntreprise.nom_fr) {
                this.errors.nom_fr = 'Le nom en français est obligatoire.';
            } else if (this.newEntreprise.nom_fr.length > 100) {
                this.errors.nom_fr =
                    'Le nom ne doit pas dépasser 100 caractères.';
            } else {
                this.errors.nom_fr = '';
            }
        },
        validateNomAr() {
            if (!this.newEntreprise.nom_ar) {
                this.errors.nom_ar = 'Le nom en arabe est obligatoire.';
            } else if (this.newEntreprise.nom_ar.length > 100) {
                this.errors.nom_ar =
                    'Le nom ne doit pas dépasser 100 caractères.';
            } else {
                this.errors.nom_ar = '';
            }
        },
        validateEmail() {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!this.newEntreprise.email_entr) {
                this.errors.email_entr = 'L’email est obligatoire.';
            } else if (!emailRegex.test(this.newEntreprise.email_entr)) {
                this.errors.email_entr = 'L’email n’est pas valide.';
            } else if (this.newEntreprise.email_entr.length > 100) {
                this.errors.email_entr =
                    'L’email ne doit pas dépasser 100 caractères.';
            } else {
                this.errors.email_entr = '';
            }
        },
        validateSecteur() {
            if (!this.newEntreprise.secteur_id) {
                this.errors.secteur_id = 'Le secteur est obligatoire.';
            } else {
                this.errors.secteur_id = '';
            }
        },
        async saveEntreprise() {
            this.isSaving = true;

            this.validateCode();
            this.validateNomFr();
            this.validateNomAr();
            this.validateEmail();
            this.validateSecteur();

            if (Object.values(this.errors).some((error) => error !== '')) {
                this.isSaving = false;
                return;
            }

            this.$emit('save', { ...this.newEntreprise });
            this.isSaving = false;
        },
        async updateEntreprise() {
            this.validateCode();
            this.validateNomFr();
            this.validateNomAr();
            this.validateEmail();
            this.validateSecteur();

            if (Object.values(this.errors).some((error) => error !== '')) {
                return;
            }

            this.$emit('update', { ...this.newEntreprise });
            this.close();
        },
    },
};
</script>
