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

                <div class="p-5 surface-card">
                    <h5 class="text-center font-bold text-lg mt-3 mb-6">
                        {{
                            isEditing
                                ? 'Modifier un ModuleGeneral'
                                : 'Ajouter un ModuleGeneral'
                        }}
                    </h5>

                    <div class="flex gap-4">
                        <div class="flex flex-column gap-2 flex-1">
                            <label for="code">Code</label>
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
                        <div class="flex flex-column gap-2 flex-1">
                            <label for="titre">Titre</label>
                            <InputText
                                id="titre"
                                v-model="newElement.titre"
                                class="w-full"
                                @input="validateTitre"
                            />
                            <small v-if="errors.titre" class="text-red-500">{{
                                errors.titre
                            }}</small>
                        </div>
                    </div>

                    <div class="flex gap-4 mt-2">
                        <div class="flex flex-column gap-2 flex-1">
                            <label for="type">Type</label>
                            <Dropdown
                                id="type"
                                v-model="newElement.type"
                                :options="typeOptions"
                                optionLabel="label"
                                optionValue="value"
                                placeholder="Sélectionner un type"
                                class="w-full"
                                @change="validateType"
                            />
                            <small v-if="errors.type" class="text-red-500">{{
                                errors.type
                            }}</small>
                        </div>
                        <div class="flex flex-column gap-2 flex-1">
                            <label for="heures_theoriques"
                                >Heures Théoriques</label
                            >
                            <InputNumber
                                id="heures_theoriques"
                                v-model="newElement.heures_theoriques"
                                class="w-full"
                                @input="validateHeuresTheoriques"
                            />
                            <small
                                v-if="errors.heures_theoriques"
                                class="text-red-500"
                                >{{ errors.heures_theoriques }}</small
                            >
                        </div>
                    </div>

                    <div class="flex gap-4 mt-2">
                        <div class="flex flex-column gap-2 flex-1">
                            <label for="heures_pratiques"
                                >Heures Pratiques</label
                            >
                            <InputNumber
                                id="heures_pratiques"
                                v-model="newElement.heures_pratiques"
                                class="w-full"
                                @input="validateHeuresPratiques"
                            />
                            <small
                                v-if="errors.heures_pratiques"
                                class="text-red-500"
                                >{{ errors.heures_pratiques }}</small
                            >
                        </div>
                        <div class="flex flex-column gap-2 flex-1">
                            <label for="heures_evaluation"
                                >Heures Évaluation</label
                            >
                            <InputNumber
                                id="heures_evaluation"
                                v-model="newElement.heures_evaluation"
                                class="w-full"
                                @input="validateHeuresEvaluation"
                            />
                            <small
                                v-if="errors.heures_evaluation"
                                class="text-red-500"
                                >{{ errors.heures_evaluation }}</small
                            >
                        </div>
                    </div>

                    <div class="flex flex-column gap-2 mt-2">
                        <label for="contenu_pdf">Contenu PDF</label>
                        <InputText
                            type="file"
                            id="contenu_pdf"
                            @change="handleFileUpload"
                            accept="application/pdf"
                            class="w-full"
                        />
                        <small v-if="errors.contenu_pdf" class="text-red-500">{{
                            errors.contenu_pdf
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
                            label="Enregistrer"
                            severity="success"
                            outlined
                            @click="save"
                            :loading="loading"
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
import { useToast } from 'primevue/usetoast';

export default {
    props: {
        visible: Boolean,
        initialData: Object,
        programmeId: {
            type: Number,
            default: null,
        },
    },
    emits: ['update:visible', 'save', 'update'],
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            newElement: {
                id: null,
                code: '',
                titre: '',
                type: 'Enseignement spécifique',
                heures_theoriques: null,
                heures_pratiques: null,
                heures_evaluation: null,
                contenu_pdf: null,
                observation: '',
                programme_id: this.programmeId,
            },
            errors: {
                code: '',
                titre: '',
                type: '',
                heures_theoriques: '',
                heures_pratiques: '',
                heures_evaluation: '',
                contenu_pdf: '',
            },
            loading: false,
            typeOptions: [
                {
                    label: 'Enseignement spécifique',
                    value: 'Enseignement spécifique',
                },
                {
                    label: 'Enseignement général',
                    value: 'Enseignement général',
                },
            ],
        };
    },
    computed: {
        isEditing() {
            return !!this.initialData && !!this.initialData.id;
        },
    },
    watch: {
        initialData: {
            immediate: true,
            handler(newVal) {
                if (newVal) {
                    this.newElement = {
                        ...newVal,
                        programme_id: this.programmeId,
                    };
                    this.clearErrors();
                } else {
                    this.resetForm();
                }
            },
        },
        programmeId(newVal) {
            this.newElement.programme_id = newVal;
        },
    },
    methods: {
        close() {
            this.$emit('update:visible', false);
            this.resetForm();
        },
        resetForm() {
            this.newElement = {
                id: null,
                code: '',
                titre: '',
                type: 'Enseignement spécifique',
                heures_theoriques: null,
                heures_pratiques: null,
                heures_evaluation: null,
                contenu_pdf: null,
                observation: '',
                programme_id: this.programmeId,
            };
            this.clearErrors();
        },
        clearErrors() {
            this.errors = {
                code: '',
                titre: '',
                type: '',
                heures_theoriques: '',
                heures_pratiques: '',
                heures_evaluation: '',
                contenu_pdf: '',
            };
        },
        validateCode() {
            if (!this.newElement.code) {
                this.errors.code = 'Le code est obligatoire.';
            } else {
                this.errors.code = '';
            }
        },
        validateTitre() {
            if (!this.newElement.titre) {
                this.errors.titre = 'Le titre est obligatoire.';
            } else {
                this.errors.titre = '';
            }
        },
        validateType() {
            if (!this.newElement.type) {
                this.errors.type = 'Le type est obligatoire.';
            } else {
                this.errors.type = '';
            }
        },
        validateHeuresTheoriques() {
            if (
                this.newElement.heures_theoriques === null ||
                this.newElement.heures_theoriques < 0
            ) {
                this.errors.heures_theoriques =
                    'Les heures théoriques doivent être un nombre positif ou zéro.';
            } else {
                this.errors.heures_theoriques = '';
            }
        },
        validateHeuresPratiques() {
            if (
                this.newElement.heures_pratiques === null ||
                this.newElement.heures_pratiques < 0
            ) {
                this.errors.heures_pratiques =
                    'Les heures pratiques doivent être un nombre positif ou zéro.';
            } else {
                this.errors.heures_pratiques = '';
            }
        },
        validateHeuresEvaluation() {
            if (
                this.newElement.heures_evaluation === null ||
                this.newElement.heures_evaluation < 0
            ) {
                this.errors.heures_evaluation =
                    "Les heures d'évaluation doivent être un nombre positif ou zéro.";
            } else {
                this.errors.heures_evaluation = '';
            }
        },
        handleFileUpload(event) {
            const file = event.target.files[0];
            if (file && file.type === 'application/pdf') {
                this.newElement.contenu_pdf = file;
                this.errors.contenu_pdf = '';
            } else {
                this.errors.contenu_pdf =
                    'Veuillez sélectionner un fichier PDF valide.';
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Veuillez sélectionner un fichier PDF valide.',
                    life: 3000,
                });
            }
        },
        async save() {
            this.loading = true;
            this.validateCode();
            this.validateTitre();
            this.validateType();
            this.validateHeuresTheoriques();
            this.validateHeuresPratiques();
            this.validateHeuresEvaluation();

            if (Object.values(this.errors).some((error) => error !== '')) {
                this.loading = false;
                return;
            }

            const formData = new FormData();
            formData.append('programme_id', this.newElement.programme_id);
            formData.append('code', this.newElement.code);
            formData.append('titre', this.newElement.titre || '');
            formData.append('type', this.newElement.type);
            formData.append(
                'heures_theoriques',
                this.newElement.heures_theoriques || 0
            );
            formData.append(
                'heures_pratiques',
                this.newElement.heures_pratiques || 0
            );
            formData.append(
                'heures_evaluation',
                this.newElement.heures_evaluation || 0
            );
            formData.append('observation', this.newElement.observation || '');

            if (this.newElement.contenu_pdf instanceof File) {
                formData.append('contenu_pdf', this.newElement.contenu_pdf);
            }

            if (this.isEditing) {
                formData.append('_method', 'PUT');
            }

            try {
                const url = this.isEditing
                    ? `/api/modules/${this.newElement.id}`
                    : '/api/modules';
                const response = await axios({
                    method: 'post',
                    url: url,
                    data: formData,
                    headers: { 'Content-Type': 'multipart/form-data' },
                });

                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: this.isEditing
                        ? 'ModuleGeneral modifié avec succès'
                        : 'ModuleGeneral ajouté avec succès',
                    life: 3000,
                });

                this.$emit(
                    this.isEditing ? 'update' : 'save',
                    response.data.data
                );
                this.close();
            } catch (error) {
                if (error.response && error.response.data.errors) {
                    Object.keys(error.response.data.errors).forEach((key) => {
                        this.errors[key] = error.response.data.errors[key][0];
                    });
                } else {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: "Échec de l'enregistrement du module",
                        life: 3000,
                    });
                }
            } finally {
                this.loading = false;
            }
        },
    },
};
</script>
