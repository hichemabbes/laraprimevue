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
                                ? 'Modifier une Année'
                                : 'Ajouter une Année'
                        }}
                    </h5>

                    <div class="flex flex-column gap-2">
                        <label for="intitule" class="font-semibold"
                            >Intitulé</label
                        >
                        <InputText
                            id="intitule"
                            v-model="newAnneeFormation.intitule"
                            class="w-full"
                            @input="validateIntitule"
                        />
                        <small v-if="errors.intitule" class="text-red-500">{{
                            errors.intitule
                        }}</small>
                    </div>

                    <div class="flex flex-column gap-2">
                        <label for="date_debut" class="font-semibold"
                            >Date de Début</label
                        >
                        <Calendar
                            id="date_debut"
                            v-model="newAnneeFormation.date_debut"
                            dateFormat="dd/mm/yy"
                            class="w-full"
                            @date-select="validateDateDebut"
                        />
                        <small v-if="errors.date_debut" class="text-red-500">{{
                            errors.date_debut
                        }}</small>
                    </div>

                    <div class="flex flex-column gap-2">
                        <label for="date_fin" class="font-semibold"
                            >Date de Fin</label
                        >
                        <Calendar
                            id="date_fin"
                            v-model="newAnneeFormation.date_fin"
                            dateFormat="dd/mm/yy"
                            class="w-full"
                            @date-select="validateDateFin"
                        />
                        <small v-if="errors.date_fin" class="text-red-500">{{
                            errors.date_fin
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
                                    ? updateAnneeFormation()
                                    : saveAnneeFormation()
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

export default {
    props: {
        visible: { type: Boolean, required: true },
        anneeToEdit: { type: Object, default: null },
    },
    emits: ['update:visible', 'close', 'save', 'update'],
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            newAnneeFormation: {
                intitule: '',
                date_debut: null,
                date_fin: null,
            },
            errors: {
                intitule: '',
                date_debut: '',
                date_fin: '',
            },
            isSaving: false,
        };
    },
    computed: {
        isEditing() {
            return !!this.anneeToEdit;
        },
    },
    watch: {
        anneeToEdit: {
            immediate: true,
            handler(newVal) {
                if (newVal) {
                    this.newAnneeFormation = { ...newVal };
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
    },
    methods: {
        close() {
            this.resetForm();
            this.$emit('update:visible', false);
            this.$emit('close');
        },
        resetForm() {
            this.newAnneeFormation = {
                intitule: '',
                date_debut: null,
                date_fin: null,
            };
            this.errors = {
                intitule: '',
                date_debut: '',
                date_fin: '',
            };
        },
        formatDate(date) {
            if (!date || !(date instanceof Date)) return null;
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        },
        validateIntitule() {
            if (!this.newAnneeFormation.intitule) {
                this.errors.intitule = 'L’intitulé est obligatoire.';
            } else if (this.newAnneeFormation.intitule.length > 255) {
                this.errors.intitule =
                    'L’intitulé ne doit pas dépasser 255 caractères.';
            } else {
                this.errors.intitule = '';
            }
        },
        validateDateDebut() {
            if (!this.newAnneeFormation.date_debut) {
                this.errors.date_debut = 'La date de début est obligatoire.';
            } else {
                this.errors.date_debut = '';
            }
        },
        validateDateFin() {
            if (!this.newAnneeFormation.date_fin) {
                this.errors.date_fin = 'La date de fin est obligatoire.';
            } else if (
                this.newAnneeFormation.date_debut &&
                this.newAnneeFormation.date_fin <=
                    this.newAnneeFormation.date_debut
            ) {
                this.errors.date_fin =
                    'La date de fin doit être supérieure à la date de début.';
            } else {
                this.errors.date_fin = '';
            }
        },
        async saveAnneeFormation() {
            this.isSaving = true;

            this.validateIntitule();
            this.validateDateDebut();
            this.validateDateFin();

            if (Object.values(this.errors).some((error) => error !== '')) {
                this.isSaving = false;
                return;
            }

            const payload = {
                ...this.newAnneeFormation,
                date_debut: this.formatDate(this.newAnneeFormation.date_debut),
                date_fin: this.formatDate(this.newAnneeFormation.date_fin),
            };

            this.$emit('save', payload);
            this.isSaving = false;
        },
        async updateAnneeFormation() {
            this.validateIntitule();
            this.validateDateDebut();
            this.validateDateFin();

            if (Object.values(this.errors).some((error) => error !== '')) {
                return;
            }

            const payload = {
                ...this.newAnneeFormation,
                date_debut: this.formatDate(this.newAnneeFormation.date_debut),
                date_fin: this.formatDate(this.newAnneeFormation.date_fin),
            };

            this.$emit('update', payload);
            this.close();
        },
    },
};
</script>
