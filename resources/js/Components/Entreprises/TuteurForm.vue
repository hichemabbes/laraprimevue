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
                                ? 'Modifier un Tuteur'
                                : 'Ajouter un Tuteur'
                        }}
                    </h5>

                    <div class="flex flex-column gap-2">
                        <label for="nom_fr" class="font-semibold"
                            >Nom (FR)</label
                        >
                        <InputText
                            id="nom_fr"
                            v-model="newTuteur.nom_fr"
                            class="w-full"
                            @input="validateNomFr"
                        />
                        <small v-if="errors.nom_fr" class="text-red-500">{{
                            errors.nom_fr
                        }}</small>
                    </div>

                    <div class="flex flex-column gap-2">
                        <label for="prenom_fr" class="font-semibold"
                            >Prénom (FR)</label
                        >
                        <InputText
                            id="prenom_fr"
                            v-model="newTuteur.prenom_fr"
                            class="w-full"
                            @input="validatePrenomFr"
                        />
                        <small v-if="errors.prenom_fr" class="text-red-500">{{
                            errors.prenom_fr
                        }}</small>
                    </div>

                    <div class="flex flex-column gap-2">
                        <label for="cin" class="font-semibold">CIN</label>
                        <InputText
                            id="cin"
                            v-model="newTuteur.cin"
                            class="w-full"
                            @input="validateCin"
                        />
                        <small v-if="errors.cin" class="text-red-500">{{
                            errors.cin
                        }}</small>
                    </div>

                    <div class="flex flex-column gap-2">
                        <label for="telephone" class="font-semibold"
                            >Téléphone</label
                        >
                        <InputText
                            id="telephone"
                            v-model="newTuteur.telephone"
                            class="w-full"
                            @input="validateTelephone"
                        />
                        <small v-if="errors.telephone" class="text-red-500">{{
                            errors.telephone
                        }}</small>
                    </div>

                    <div class="flex flex-column gap-2">
                        <label for="email" class="font-semibold">Email</label>
                        <InputText
                            id="email"
                            v-model="newTuteur.email"
                            class="w-full"
                            @input="validateEmail"
                        />
                        <small v-if="errors.email" class="text-red-500">{{
                            errors.email
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
                            @click="isEditing ? updateTuteur() : saveTuteur()"
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
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';

export default {
    components: {
        InputText,
        Button,
        Dialog,
        Toast,
    },
    props: {
        visible: { type: Boolean, required: true },
        entreprise: { type: Object, required: true }, // L'entreprise à laquelle le tuteur est lié
        tuteurToEdit: { type: Object, default: null }, // Tuteur à modifier (si édition)
    },
    emits: ['update:visible', 'close', 'save', 'update'],
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            newTuteur: {
                nom_fr: '',
                prenom_fr: '',
                cin: '',
                telephone: '',
                email: '',
                entreprise_id: null,
            },
            errors: {
                nom_fr: '',
                prenom_fr: '',
                cin: '',
                telephone: '',
                email: '',
            },
            isSaving: false,
        };
    },
    computed: {
        isEditing() {
            return !!this.tuteurToEdit;
        },
    },
    watch: {
        tuteurToEdit: {
            immediate: true,
            handler(newVal) {
                if (newVal) {
                    this.newTuteur = { ...newVal };
                } else {
                    this.resetForm();
                }
            },
        },
        entreprise: {
            immediate: true,
            handler(newVal) {
                if (newVal) {
                    this.newTuteur.entreprise_id = newVal.id;
                }
            },
        },
        visible(newVal) {
            if (!newVal) {
                this.resetForm();
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
            this.newTuteur = {
                nom_fr: '',
                prenom_fr: '',
                cin: '',
                telephone: '',
                email: '',
                entreprise_id: this.entreprise ? this.entreprise.id : null,
            };
            this.errors = {
                nom_fr: '',
                prenom_fr: '',
                cin: '',
                telephone: '',
                email: '',
            };
        },
        validateNomFr() {
            if (!this.newTuteur.nom_fr) {
                this.errors.nom_fr = 'Le nom en français est obligatoire.';
            } else if (this.newTuteur.nom_fr.length > 100) {
                this.errors.nom_fr =
                    'Le nom ne doit pas dépasser 100 caractères.';
            } else {
                this.errors.nom_fr = '';
            }
        },
        validatePrenomFr() {
            if (!this.newTuteur.prenom_fr) {
                this.errors.prenom_fr =
                    'Le prénom en français est obligatoire.';
            } else if (this.newTuteur.prenom_fr.length > 100) {
                this.errors.prenom_fr =
                    'Le prénom ne doit pas dépasser 100 caractères.';
            } else {
                this.errors.prenom_fr = '';
            }
        },
        validateCin() {
            const cinRegex = /^\d{8}$/;
            if (!this.newTuteur.cin) {
                this.errors.cin = 'Le CIN est obligatoire.';
            } else if (!cinRegex.test(this.newTuteur.cin)) {
                this.errors.cin = 'Le CIN doit être un numéro de 8 chiffres.';
            } else {
                this.errors.cin = '';
            }
        },
        validateTelephone() {
            const phoneRegex = /^\d{8}$/;
            if (!this.newTuteur.telephone) {
                this.errors.telephone = 'Le téléphone est obligatoire.';
            } else if (!phoneRegex.test(this.newTuteur.telephone)) {
                this.errors.telephone =
                    'Le téléphone doit être un numéro de 8 chiffres.';
            } else {
                this.errors.telephone = '';
            }
        },
        validateEmail() {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!this.newTuteur.email) {
                this.errors.email = 'L’email est obligatoire.';
            } else if (!emailRegex.test(this.newTuteur.email)) {
                this.errors.email = 'L’email n’est pas valide.';
            } else if (this.newTuteur.email.length > 100) {
                this.errors.email =
                    'L’email ne doit pas dépasser 100 caractères.';
            } else {
                this.errors.email = '';
            }
        },
        saveTuteur() {
            this.isSaving = true;

            this.validateNomFr();
            this.validatePrenomFr();
            this.validateCin();
            this.validateTelephone();
            this.validateEmail();

            if (Object.values(this.errors).some((error) => error !== '')) {
                this.isSaving = false;
                return;
            }

            this.$emit('save', { ...this.newTuteur });
            this.isSaving = false;
        },
        updateTuteur() {
            this.validateNomFr();
            this.validatePrenomFr();
            this.validateCin();
            this.validateTelephone();
            this.validateEmail();

            if (Object.values(this.errors).some((error) => error !== '')) {
                return;
            }

            this.$emit('update', { ...this.newTuteur });
            this.close();
        },
    },
};
</script>
