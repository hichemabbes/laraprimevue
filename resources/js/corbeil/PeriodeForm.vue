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

                <div
                    class="p-5 surface-overlay"
                    style="background-color: rgba(255, 255, 255, 0.4)"
                >
                    <h5 class="text-center font-bold text-lg mt-3">
                        {{
                            periodeToEdit
                                ? 'Modifier Période de Repos'
                                : 'Nouvelle Période de Repos'
                        }}
                    </h5>

                    <!-- Champ Type en premier -->
                    <div class="flex flex-column gap-2">
                        <label for="type" class="font-semibold">Type</label>
                        <Dropdown
                            id="type"
                            v-model="newPeriode.type"
                            :options="types"
                            placeholder="Sélectionner un type"
                            class="w-full"
                            @change="validateType"
                        />
                        <small v-if="errors.type" class="text-red-500">{{
                            errors.type
                        }}</small>
                    </div>

                    <!-- Champ Titre (dynamique selon le type) -->
                    <div class="flex flex-column gap-2">
                        <label for="titre_vacance" class="font-semibold"
                            >Titre</label
                        >
                        <Dropdown
                            v-if="
                                newPeriode.type === 'vacance' ||
                                newPeriode.type === 'jour_ferie'
                            "
                            id="titre_vacance"
                            v-model="newPeriode.titre_vacance"
                            :options="titreOptions"
                            placeholder="Sélectionner un titre"
                            class="w-full"
                            @change="validateTitre"
                        />
                        <InputText
                            v-else
                            id="titre_vacance"
                            v-model="newPeriode.titre_vacance"
                            class="w-full"
                            @input="validateTitre"
                        />
                        <small
                            v-if="errors.titre_vacance"
                            class="text-red-500"
                            >{{ errors.titre_vacance }}</small
                        >
                    </div>

                    <!-- Champ Date pour jour_ferie -->
                    <div
                        v-if="newPeriode.type === 'jour_ferie'"
                        class="flex flex-column gap-2"
                    >
                        <label for="date" class="font-semibold">Date</label>
                        <Calendar
                            id="date"
                            v-model="newPeriode.date"
                            dateFormat="dd/mm/yy"
                            class="w-full"
                            :minDate="annee ? annee.date_debut : null"
                            :maxDate="annee ? annee.date_fin : null"
                            @date-select="validateDate"
                        />
                        <small v-if="errors.date" class="text-red-500">{{
                            errors.date
                        }}</small>
                    </div>

                    <!-- Champs Date Début et Date Fin pour vacance/autre -->
                    <div v-else class="flex flex-column gap-2">
                        <label for="date_debut" class="font-semibold"
                            >Date de Début</label
                        >
                        <Calendar
                            id="date_debut"
                            v-model="newPeriode.date_debut"
                            dateFormat="dd/mm/yy"
                            class="w-full"
                            :minDate="annee ? annee.date_debut : null"
                            :maxDate="annee ? annee.date_fin : null"
                            @date-select="validateDateDebut"
                        />
                        <small v-if="errors.date_debut" class="text-red-500">{{
                            errors.date_debut
                        }}</small>
                    </div>
                    <div
                        v-if="newPeriode.type !== 'jour_ferie'"
                        class="flex flex-column gap-2"
                    >
                        <label for="date_fin" class="font-semibold"
                            >Date de Fin</label
                        >
                        <Calendar
                            id="date_fin"
                            v-model="newPeriode.date_fin"
                            dateFormat="dd/mm/yy"
                            class="w-full"
                            :minDate="newPeriode.date_debut"
                            :maxDate="annee ? annee.date_fin : null"
                            @date-select="validateDateFin"
                        />
                        <small v-if="errors.date_fin" class="text-red-500">{{
                            errors.date_fin
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
                            :label="
                                periodeToEdit ? 'Mettre à jour' : 'Enregistrer'
                            "
                            severity="success"
                            outlined
                            @click="saveOrUpdatePeriode"
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
        annee: { type: Object, required: false, default: null },
        periodeToEdit: { type: Object, required: false, default: null },
    },
    emits: ['update:visible', 'close', 'save', 'update'],
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            newPeriode: {
                id: null,
                annee_formation_id: null,
                titre_vacance: '',
                type: null,
                date: null,
                date_debut: null,
                date_fin: null,
            },
            types: ['vacance', 'jour_ferie', 'autre'],
            titreOptionsVacance: [
                'Vacance de la mi-trimestre (1er trimestre)',
                "Vacances d'hiver et du Nouvel An",
                'Vacance de la mi-trimestre (2e trimestre)',
                'Vacance du printemps',
                'Vacance Aïd El Fitr',
                'Vacance Aïd El Adha',
            ],
            titreOptionsJourFerie: [
                "Jour de l'Année",
                "Fête de l'Indépendance",
                'Fête des Martyrs',
                'Fête du Travail',
                'Fête de la Femme',
                "Fête de l'Évacuation",
                'Fête de la Révolution',
            ],
            errors: {
                titre_vacance: '',
                type: '',
                date: '',
                date_debut: '',
                date_fin: '',
            },
            isSaving: false,
        };
    },
    computed: {
        titreOptions() {
            if (this.newPeriode.type === 'vacance')
                return this.titreOptionsVacance;
            if (this.newPeriode.type === 'jour_ferie')
                return this.titreOptionsJourFerie;
            return [];
        },
    },
    watch: {
        annee: {
            immediate: true,
            handler(newVal) {
                if (newVal) {
                    this.newPeriode.annee_formation_id = newVal.id;
                } else {
                    this.newPeriode.annee_formation_id = null;
                }
            },
        },
        periodeToEdit: {
            immediate: true,
            handler(newVal) {
                if (newVal) {
                    this.newPeriode = {
                        ...newVal,
                        date: newVal.date ? new Date(newVal.date) : null,
                        date_debut: newVal.date_debut
                            ? new Date(newVal.date_debut)
                            : null,
                        date_fin: newVal.date_fin
                            ? new Date(newVal.date_fin)
                            : null,
                    };
                } else {
                    this.resetForm();
                }
            },
        },
        'newPeriode.type'(newVal) {
            if (!this.periodeToEdit) {
                // Ne réinitialiser que si ce n'est pas une édition
                this.newPeriode.titre_vacance = '';
                if (newVal === 'jour_ferie') {
                    this.newPeriode.date_debut = null;
                    this.newPeriode.date_fin = null;
                } else {
                    this.newPeriode.date = null;
                }
            }
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
            this.newPeriode = {
                id: null,
                annee_formation_id: this.annee ? this.annee.id : null,
                titre_vacance: '',
                type: null,
                date: null,
                date_debut: null,
                date_fin: null,
            };
            this.errors = {
                titre_vacance: '',
                type: '',
                date: '',
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
        validateTitre() {
            if (!this.newPeriode.titre_vacance) {
                this.errors.titre_vacance = 'Le titre est obligatoire.';
            } else if (this.newPeriode.titre_vacance.length > 255) {
                this.errors.titre_vacance =
                    'Le titre ne doit pas dépasser 255 caractères.';
            } else {
                this.errors.titre_vacance = '';
            }
        },
        validateType() {
            if (!this.newPeriode.type) {
                this.errors.type = 'Le type est obligatoire.';
            } else if (!this.types.includes(this.newPeriode.type)) {
                this.errors.type = 'Type invalide.';
            } else {
                this.errors.type = '';
            }
        },
        validateDate() {
            if (
                this.newPeriode.type === 'jour_ferie' &&
                !this.newPeriode.date
            ) {
                this.errors.date =
                    'La date est obligatoire pour un jour férié.';
            } else {
                this.errors.date = '';
            }
        },
        validateDateDebut() {
            if (
                this.newPeriode.type !== 'jour_ferie' &&
                !this.newPeriode.date_debut
            ) {
                this.errors.date_debut = 'La date de début est obligatoire.';
            } else {
                this.errors.date_debut = '';
            }
        },
        validateDateFin() {
            if (this.newPeriode.type !== 'jour_ferie') {
                if (!this.newPeriode.date_fin) {
                    this.errors.date_fin = 'La date de fin est obligatoire.';
                } else if (
                    this.newPeriode.date_debut &&
                    this.newPeriode.date_fin <= this.newPeriode.date_debut
                ) {
                    this.errors.date_fin =
                        'La date de fin doit être après la date de début.';
                } else {
                    this.errors.date_fin = '';
                }
            } else {
                this.errors.date_fin = '';
            }
        },
        async saveOrUpdatePeriode() {
            this.isSaving = true;

            this.validateTitre();
            this.validateType();
            if (this.newPeriode.type === 'jour_ferie') {
                this.validateDate();
            } else {
                this.validateDateDebut();
                this.validateDateFin();
            }

            if (Object.values(this.errors).some((error) => error !== '')) {
                this.isSaving = false;
                return;
            }

            const payload = {
                ...this.newPeriode,
                date:
                    this.newPeriode.type === 'jour_ferie'
                        ? this.formatDate(this.newPeriode.date)
                        : null,
                date_debut:
                    this.newPeriode.type !== 'jour_ferie'
                        ? this.formatDate(this.newPeriode.date_debut)
                        : null,
                date_fin:
                    this.newPeriode.type !== 'jour_ferie'
                        ? this.formatDate(this.newPeriode.date_fin)
                        : null,
            };

            if (this.periodeToEdit) {
                this.$emit('update', payload);
            } else {
                this.$emit('save', payload);
            }
            this.isSaving = false;
        },
    },
};
</script>
