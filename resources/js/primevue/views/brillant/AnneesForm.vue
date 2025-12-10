<template>
    <Dialog
        :visible="visible"
        @update:visible="$emit('update:visible', $event)"
        modal
        :pt="{
            root: 'border-none',
            mask: {
                style: 'backdrop-filter: blur(2px)',
            },
        }"
    >
        <template #container="{ closeCallback }">
            <div class="relative">
                <!-- Bouton de fermeture en haut à droite avec fond circulaire transparent et effet de survol -->
                <i
                    class="pi pi-times absolute cursor-pointer text-gray-400 text-xl hover:bg-gray-500 hover:bg-opacity-30 transition-all"
                    style="
                        top: 10px;
                        right: 10px;
                        width: 30px;
                        height: 30px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        background-color: rgba(0, 0, 0, 0.3);
                        border-radius: 50%;
                    "
                    @click="close"
                ></i>

                <div
                    class="flex flex-column px-5 py-5 gap-4"
                    style="border-radius: 12px; background: var(--surface-card)"
                >
                    <!-- Titre du formulaire -->
                    <h3
                        class="text-center text-primary font-bold text-2xl mt-2"
                    >
                        {{
                            isEditing
                                ? 'Modifier une année'
                                : 'Ajouter une année'
                        }}
                    </h3>
                    <!-- Champ pour l'intitulé -->
                    <div class="inline-flex flex-column gap-2 mt-16">
                        <!-- Ajout de mt-16 pour laisser de la place au titre -->
                        <label for="intitule" class="text-primary font-semibold"
                            >Intitulé</label
                        >
                        <InputText
                            id="intitule"
                            v-model="newAnneeFormation.intitule"
                            class="w-20rem"
                            @input="validateIntitule"
                        ></InputText>
                        <small v-if="errors.intitule" class="text-red-500">{{
                            errors.intitule
                        }}</small>
                    </div>

                    <!-- Champ pour la date de début -->
                    <div class="inline-flex flex-column gap-2">
                        <label
                            for="date_debut"
                            class="text-primary font-semibold"
                            >Date de Début</label
                        >
                        <Calendar
                            id="date_debut"
                            v-model="newAnneeFormation.date_debut"
                            dateFormat="yy-mm-dd"
                            class="w-20rem"
                            @date-select="validateDateDebut"
                        />
                        <small v-if="errors.date_debut" class="text-red-500">{{
                            errors.date_debut
                        }}</small>
                    </div>

                    <!-- Champ pour la date de fin -->
                    <div class="inline-flex flex-column gap-2">
                        <label for="date_fin" class="text-primary font-semibold"
                            >Date de Fin</label
                        >
                        <Calendar
                            id="date_fin"
                            v-model="newAnneeFormation.date_fin"
                            dateFormat="yy-mm-dd"
                            class="w-20rem"
                            @date-select="validateDateFin"
                        />
                        <small v-if="errors.date_fin" class="text-red-500">{{
                            errors.date_fin
                        }}</small>
                    </div>

                    <!-- Champ pour le statut -->
                    <div class="inline-flex flex-column gap-2 mb-3">
                        <!-- Ajoutez mb-5 pour une marge en bas -->
                        <label for="statut" class="text-primary font-semibold"
                            >Statut</label
                        >
                        <Dropdown
                            id="statut"
                            v-model="newAnneeFormation.statut"
                            :options="statuts"
                            placeholder="Sélectionner un statut"
                            class="w-20rem"
                            @change="validateStatut"
                        />
                        <small v-if="errors.statut" class="text-red-500">{{
                            errors.statut
                        }}</small>
                    </div>

                    <!-- Boutons d'action -->
                    <div class="flex align-items-center gap-5">
                        <Button
                            label="Annuler"
                            severity="danger"
                            outlined
                            @click="close"
                            class="p-2.3 w-full border-1 border-surface-200"
                        ></Button>
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
                            class="p-2.3 w-full border-1 border-surface-200"
                        ></Button>
                    </div>
                </div>
            </div>
        </template>
    </Dialog>

    <!-- Toast pour les notifications -->
    <Toast />
</template>

<script>
import { useToast } from 'primevue/usetoast';
import axios from 'axios';

export default {
    props: {
        visible: {
            type: Boolean,
            required: true,
        },
        anneeToEdit: {
            type: Object,
            default: null,
        },
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
                date_debut: '',
                date_fin: '',
                statut: null,
            },
            statuts: ['قادمة', 'منقضية', 'حالية'],
            errors: {
                intitule: '',
                date_debut: '',
                date_fin: '',
                statut: '',
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
                statut: null,
            };
            this.errors = {
                intitule: '',
                date_debut: '',
                date_fin: '',
                statut: '',
            };
        },
        formatDate(date) {
            if (!date || !(date instanceof Date)) {
                return null;
            }
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
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
                this.newAnneeFormation.date_fin <=
                this.newAnneeFormation.date_debut
            ) {
                this.errors.date_fin =
                    'La date de fin doit être supérieure à la date de début.';
            } else {
                this.errors.date_fin = '';
            }
        },
        validateStatut() {
            if (!this.newAnneeFormation.statut) {
                this.errors.statut = 'Le statut est obligatoire.';
            } else if (!this.statuts.includes(this.newAnneeFormation.statut)) {
                this.errors.statut =
                    "Le statut doit être 'قادمة', 'منقضية', ou 'حالية'.";
            } else {
                this.errors.statut = '';
            }
        },
        async checkIntitulUniqueness(intitule) {
            try {
                const response = await axios.get('/api/check-intitul-unique', {
                    params: {
                        intitule: intitule,
                        id: this.isEditing ? this.anneeToEdit.id : null,
                    },
                });
                return response.data.unique;
            } catch (error) {
                console.error(
                    "Erreur lors de la vérification de l'unicité de l'intitulé :",
                    error
                );
                return false;
            }
        },
        async validateIntitule() {
            if (!this.newAnneeFormation.intitule) {
                this.errors.intitule = "L'intitulé est obligatoire.";
            } else if (this.newAnneeFormation.intitule.length > 255) {
                this.errors.intitule =
                    "L'intitulé ne doit pas dépasser 255 caractères.";
            } else {
                const isUnique = await this.checkIntitulUniqueness(
                    this.newAnneeFormation.intitule
                );
                if (!isUnique) {
                    this.errors.intitule =
                        "L'intitulé est déjà utilisé pour une année de formation active.";
                } else {
                    this.errors.intitule = '';
                }
            }
        },
        async saveAnneeFormation() {
            this.isSaving = true;
            this.validateIntitule();
            this.validateDateDebut();
            this.validateDateFin();
            this.validateStatut();

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
            await this.validateIntitule();
            this.validateDateDebut();
            this.validateDateFin();
            this.validateStatut();

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
