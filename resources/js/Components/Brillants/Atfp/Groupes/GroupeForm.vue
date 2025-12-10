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
                                ? 'Modifier un Groupe'
                                : 'Ajouter un Groupe'
                        }}
                    </h5>

                    <div class="flex flex-column gap-2">
                        <label for="code_groupe" class="font-semibold"
                            >Code</label
                        >
                        <InputText
                            id="code_groupe"
                            v-model="newGroupe.code_groupe"
                            class="w-full"
                            @input="validateCode"
                        />
                        <small v-if="errors.code_groupe" class="text-red-500">{{
                            errors.code_groupe
                        }}</small>
                    </div>

                    <div class="flex flex-column gap-2">
                        <label for="nom_fr" class="font-semibold"
                            >Nom (FR)</label
                        >
                        <InputText
                            id="nom_fr"
                            v-model="newGroupe.nom_fr"
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
                            v-model="newGroupe.nom_ar"
                            class="w-full"
                            @input="validateNomAr"
                        />
                        <small v-if="errors.nom_ar" class="text-red-500">{{
                            errors.nom_ar
                        }}</small>
                    </div>

                    <div class="flex flex-column gap-2">
                        <label for="programme_id" class="font-semibold"
                            >Programme</label
                        >
                        <Dropdown
                            id="programme_id"
                            v-model="newGroupe.programme_id"
                            :options="programmes"
                            optionLabel="nom_fr"
                            optionValue="id"
                            placeholder="Sélectionner un programme"
                            class="w-full"
                        />
                        <small
                            v-if="errors.programme_id"
                            class="text-red-500"
                            >{{ errors.programme_id }}</small
                        >
                    </div>

                    <div class="flex flex-column gap-2">
                        <label for="tuteur_id" class="font-semibold"
                            >Tuteur</label
                        >
                        <Dropdown
                            id="tuteur_id"
                            v-model="newGroupe.tuteur_id"
                            :options="tuteurs"
                            optionLabel="full_name"
                            optionValue="id"
                            placeholder="Sélectionner un tuteur"
                            class="w-full"
                        />
                        <small v-if="errors.tuteur_id" class="text-red-500">{{
                            errors.tuteur_id
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
                            @click="isEditing ? updateGroupe() : saveGroupe()"
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
        groupeToEdit: { type: Object, default: null },
    },
    emits: ['update:visible', 'close', 'save', 'update'],
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            newGroupe: {
                code_groupe: '',
                nom_fr: '',
                nom_ar: '',
                programme_id: null,
                tuteur_id: null,
            },
            programmes: [],
            tuteurs: [],
            errors: {
                code_groupe: '',
                nom_fr: '',
                nom_ar: '',
                programme_id: '',
                tuteur_id: '',
            },
            isSaving: false,
        };
    },
    computed: {
        isEditing() {
            return !!this.groupeToEdit;
        },
    },
    watch: {
        groupeToEdit: {
            immediate: true,
            handler(newVal) {
                if (newVal) {
                    this.newGroupe = { ...newVal };
                } else {
                    this.resetForm();
                }
            },
        },
        visible(newVal) {
            if (newVal) {
                this.fetchProgrammes();
                this.fetchTuteurs();
            } else {
                this.resetForm();
            }
        },
    },
    methods: {
        async fetchProgrammes() {
            try {
                const response = await axios.get('/api/programmes');
                this.programmes = response.data;
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Erreur lors du chargement des programmes.',
                    life: 3000,
                });
            }
        },
        async fetchTuteurs() {
            try {
                const response = await axios.get('/api/tuteurs');
                this.tuteurs = response.data.map((t) => ({
                    ...t,
                    full_name: `${t.nom_fr} ${t.prenom_fr}`,
                }));
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Erreur lors du chargement des tuteurs.',
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
            this.newGroupe = {
                code_groupe: '',
                nom_fr: '',
                nom_ar: '',
                programme_id: null,
                tuteur_id: null,
            };
            this.errors = {
                code_groupe: '',
                nom_fr: '',
                nom_ar: '',
                programme_id: '',
                tuteur_id: '',
            };
        },
        validateCode() {
            if (!this.newGroupe.code_groupe) {
                this.errors.code_groupe = 'Le code est obligatoire.';
            } else if (this.newGroupe.code_groupe.length > 50) {
                this.errors.code_groupe =
                    'Le code ne doit pas dépasser 50 caractères.';
            } else {
                this.errors.code_groupe = '';
            }
        },
        validateNomFr() {
            if (!this.newGroupe.nom_fr) {
                this.errors.nom_fr = 'Le nom en français est obligatoire.';
            } else if (this.newGroupe.nom_fr.length > 100) {
                this.errors.nom_fr =
                    'Le nom ne doit pas dépasser 100 caractères.';
            } else {
                this.errors.nom_fr = '';
            }
        },
        validateNomAr() {
            if (!this.newGroupe.nom_ar) {
                this.errors.nom_ar = 'Le nom en arabe est obligatoire.';
            } else if (this.newGroupe.nom_ar.length > 100) {
                this.errors.nom_ar =
                    'Le nom ne doit pas dépasser 100 caractères.';
            } else {
                this.errors.nom_ar = '';
            }
        },
        validateProgramme() {
            if (!this.newGroupe.programme_id) {
                this.errors.programme_id = 'Le programme est obligatoire.';
            } else {
                this.errors.programme_id = '';
            }
        },
        validateTuteur() {
            if (!this.newGroupe.tuteur_id) {
                this.errors.tuteur_id = 'Le tuteur est obligatoire.';
            } else {
                this.errors.tuteur_id = '';
            }
        },
        saveGroupe() {
            this.isSaving = true;

            this.validateCode();
            this.validateNomFr();
            this.validateNomAr();
            this.validateProgramme();
            this.validateTuteur();

            if (Object.values(this.errors).some((error) => error !== '')) {
                this.isSaving = false;
                return;
            }

            this.$emit('save', { ...this.newGroupe });
            this.isSaving = false;
        },
        updateGroupe() {
            this.validateCode();
            this.validateNomFr();
            this.validateNomAr();
            this.validateProgramme();
            this.validateTuteur();

            if (Object.values(this.errors).some((error) => error !== '')) {
                return;
            }

            this.$emit('update', { ...this.newGroupe });
            this.close();
        },
    },
};
</script>
