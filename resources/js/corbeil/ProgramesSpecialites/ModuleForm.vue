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
        :style="{ width: '700px' }"
    >
        <template #container="{ closeCallback }">
            <div class="relative">
                <!-- Bouton de fermeture en haut à droite -->
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
                    <h5 class="text-center text-primary font-bold text-l mt-3">
                        Ajouter un Module
                    </h5>

                    <!-- Ligne 1 : Code et Type -->
                    <div class="flex gap-4">
                        <div
                            class="inline-flex flex-column gap-2"
                            style="flex: 1"
                        >
                            <label for="code" class="text-primary font-semibold"
                                >Code</label
                            >
                            <InputText
                                id="code"
                                v-model="module.code"
                                class="w-full"
                                @input="validateCode"
                            ></InputText>
                            <small v-if="errors.code" class="text-red-500">{{
                                errors.code
                            }}</small>
                        </div>
                        <div
                            class="inline-flex flex-column gap-2"
                            style="flex: 1"
                        >
                            <label for="type" class="text-primary font-semibold"
                                >Type</label
                            >
                            <Dropdown
                                id="type"
                                v-model="module.type"
                                :options="[
                                    'Enseignement spécifique',
                                    'Enseignement général',
                                ]"
                                placeholder="Sélectionner un type"
                                class="w-full"
                                @change="validateType"
                            />
                            <small v-if="errors.type" class="text-red-500">{{
                                errors.type
                            }}</small>
                        </div>
                    </div>
                    <!-- Champ pour le titre -->
                    <div class="inline-flex flex-column gap-2">
                        <label for="titre" class="text-primary font-semibold"
                            >Titre</label
                        >
                        <InputText
                            id="titre"
                            v-model="module.titre"
                            class="w-full"
                            @input="validateTitre"
                        ></InputText>
                        <small v-if="errors.titre" class="text-red-500">{{
                            errors.titre
                        }}</small>
                    </div>
                    <!-- Ligne 2 : Heures Théoriques et Heures Pratiques -->
                    <div class="flex gap-4">
                        <div
                            class="inline-flex flex-column gap-2"
                            style="flex: 1"
                        >
                            <label
                                for="heures_theorique"
                                class="text-primary font-semibold"
                                >Heures Théoriques</label
                            >
                            <InputNumber
                                id="heures_theorique"
                                v-model="module.heures_theorique"
                                class="w-full"
                            />
                            <small
                                v-if="errors.heures_theorique"
                                class="text-red-500"
                                >{{ errors.heures_theorique }}</small
                            >
                        </div>
                        <div
                            class="inline-flex flex-column gap-2"
                            style="flex: 1"
                        >
                            <label
                                for="heures_pratiques"
                                class="text-primary font-semibold"
                                >Heures Pratiques</label
                            >
                            <InputNumber
                                id="heures_pratiques"
                                v-model="module.heures_pratiques"
                                class="w-full"
                            />
                            <small
                                v-if="errors.heures_pratiques"
                                class="text-red-500"
                                >{{ errors.heures_pratiques }}</small
                            >
                        </div>
                    </div>

                    <!-- Ligne 3 : Heures Evaluation et Heures Totales -->
                    <div class="flex gap-4">
                        <div
                            class="inline-flex flex-column gap-2"
                            style="flex: 1"
                        >
                            <label
                                for="heures_evaluation"
                                class="text-primary font-semibold"
                                >Heures Evaluation</label
                            >
                            <InputNumber
                                id="heures_evaluation"
                                v-model="module.heures_evaluation"
                                class="w-full"
                            />
                            <small
                                v-if="errors.heures_evaluation"
                                class="text-red-500"
                                >{{ errors.heures_evaluation }}</small
                            >
                        </div>
                        <div
                            class="inline-flex flex-column gap-2"
                            style="flex: 1"
                        >
                            <label
                                for="heures_totales"
                                class="text-primary font-semibold"
                                >Heures Totales</label
                            >
                            <InputNumber
                                id="heures_totales"
                                v-model="module.heures_totales"
                                class="w-full"
                            />
                            <small
                                v-if="errors.heures_totales"
                                class="text-red-500"
                                >{{ errors.heures_totales }}</small
                            >
                        </div>
                    </div>

                    <!-- Ligne 4 : Années et Contenu (PDF) -->
                    <div class="flex gap-4">
                        <!-- Champ Années -->
                        <div
                            class="inline-flex flex-column gap-2"
                            style="flex: 1"
                        >
                            <label
                                for="annees"
                                class="text-primary font-semibold"
                                >Années</label
                            >
                            <MultiSelect
                                id="annees"
                                v-model="module.annees"
                                :options="anneesOptions"
                                optionLabel="intitule"
                                placeholder="Sélectionner les années"
                                class="w-full"
                            />
                            <small v-if="errors.annees" class="text-red-500">{{
                                errors.annees
                            }}</small>
                        </div>

                        <!-- Champ Contenu (PDF) -->
                        <div
                            class="inline-flex flex-column gap-2"
                            style="flex: 1"
                        >
                            <label
                                for="contenu"
                                class="text-primary font-semibold"
                                >Contenu (PDF)</label
                            >
                            <input
                                type="file"
                                id="contenu"
                                accept=".pdf"
                                @change="handleFileUpload"
                                class="w-full"
                            />
                            <small v-if="errors.contenu" class="text-red-500">{{
                                errors.contenu
                            }}</small>
                        </div>
                    </div>

                    <!-- Champ pour l'observation -->
                    <div class="inline-flex flex-column gap-2">
                        <label
                            for="observation"
                            class="text-primary font-semibold"
                            >Observation</label
                        >
                        <Textarea
                            id="observation"
                            v-model="module.observation"
                            rows="2"
                            class="w-full"
                        />
                        <small v-if="errors.observation" class="text-red-500">{{
                            errors.observation
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
                            label="Enregistrer"
                            severity="success"
                            outlined
                            @click="save"
                            :disabled="isSaving"
                            class="p-2.3 w-full border-1 border-surface-200"
                        ></Button>
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
        specialiteId: {
            type: Number,
            required: true,
        },
    },
    emits: ['update:visible', 'close', 'save'],
    data() {
        return {
            module: {
                code: '',
                titre: '',
                type: '',
                heures_theorique: null,
                heures_pratiques: null,
                heures_evaluation: null,
                heures_totales: null,
                contenu: '',
                annees: [],
                observation: '',
            },
            anneesOptions: [],
            errors: {
                code: '',
                titre: '',
                type: '',
                heures_theorique: '',
                heures_pratiques: '',
                heures_evaluation: '',
                heures_totales: '',
                contenu: '',
                annees: '',
                observation: '',
            },
            isSaving: false,
        };
    },
    methods: {
        async fetchAnnees() {
            try {
                const response = await axios.get('/api/annees-formation');
                this.anneesOptions = response.data;
            } catch (error) {
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Impossible de charger les années.',
                    life: 3000,
                });
            }
        },
        handleFileUpload(event) {
            const file = event.target.files[0];
            if (file && file.type === 'application/pdf') {
                this.module.contenu = file;
                this.errors.contenu = '';
            } else {
                this.errors.contenu =
                    'Veuillez télécharger un fichier PDF valide.';
            }
        },
        open() {
            this.$emit('update:visible', true);
            this.fetchAnnees();
        },
        close() {
            this.$emit('update:visible', false);
            this.resetForm();
        },
        resetForm() {
            this.module = {
                code: '',
                titre: '',
                type: '',
                heures_theorique: null,
                heures_pratiques: null,
                heures_evaluation: null,
                heures_totales: null,
                contenu: '',
                annees: [],
                observation: '',
            };
            this.errors = {
                code: '',
                titre: '',
                type: '',
                heures_theorique: '',
                heures_pratiques: '',
                heures_evaluation: '',
                heures_totales: '',
                contenu: '',
                annees: '',
                observation: '',
            };
        },
        validateCode() {
            if (!this.module.code) {
                this.errors.code = 'Le code est obligatoire.';
            } else if (this.module.code.length > 255) {
                this.errors.code =
                    'Le code ne doit pas dépasser 255 caractères.';
            } else {
                this.errors.code = '';
            }
        },
        validateTitre() {
            if (!this.module.titre) {
                this.errors.titre = 'Le titre est obligatoire.';
            } else if (this.module.titre.length > 255) {
                this.errors.titre =
                    'Le titre ne doit pas dépasser 255 caractères.';
            } else {
                this.errors.titre = '';
            }
        },
        validateType() {
            if (!this.module.type) {
                this.errors.type = 'Le type est obligatoire.';
            } else if (
                !['Enseignement spécifique', 'Enseignement général'].includes(
                    this.module.type
                )
            ) {
                this.errors.type =
                    "Le type doit être 'Enseignement spécifique' ou 'Enseignement général'.";
            } else {
                this.errors.type = '';
            }
        },
        validateHeures(field, value) {
            if (value === null || value < 0) {
                this.errors[field] =
                    `Les heures doivent être un nombre positif valide.`;
            } else {
                this.errors[field] = '';
            }
        },
        validateAnnees() {
            if (this.module.annees.length === 0) {
                this.errors.annees =
                    'Au moins une année doit être sélectionnée.';
            } else {
                this.errors.annees = '';
            }
        },
        async save() {
            this.isSaving = true;

            // Valider tous les champs
            this.validateCode();
            this.validateTitre();
            this.validateType();
            this.validateHeures(
                'heures_theorique',
                this.module.heures_theorique
            );
            this.validateHeures(
                'heures_pratiques',
                this.module.heures_pratiques
            );
            this.validateHeures(
                'heures_evaluation',
                this.module.heures_evaluation
            );
            this.validateHeures('heures_totales', this.module.heures_totales);
            this.validateAnnees();

            // Vérifier s'il y a des erreurs
            if (Object.values(this.errors).some((error) => error !== '')) {
                this.isSaving = false;
                return;
            }

            // Convertir `annees` en tableau d'IDs si ce n'est pas déjà le cas
            const annees = Array.isArray(this.module.annees)
                ? this.module.annees.map((annee) => annee.id) // Assurez-vous que chaque élément a une propriété `id`
                : [];

            // Préparer les données à envoyer
            const moduleData = {
                code: this.module.code,
                titre: this.module.titre,
                type: this.module.type,
                heures_theorique: this.module.heures_theorique,
                heures_pratiques: this.module.heures_pratiques,
                heures_evaluation: this.module.heures_evaluation,
                heures_totales: this.module.heures_totales,
                contenu: this.module.contenu,
                annees: annees, // Envoyer un tableau d'IDs
                observation: this.module.observation,
                specialite_id: this.specialiteId,
            };

            // Émettre l'événement save avec les données du module
            this.$emit('save', moduleData);
            this.resetForm();
            this.isSaving = false;
        },
    },
    created() {
        this.fetchAnnees();
    },
};
</script>

<style scoped>
/* Réduire la hauteur du formulaire */
.p-dialog-content {
    max-height: 500px; /* Ajustez selon vos besoins */
    overflow-y: auto;
}

/* Réduire la hauteur du champ Observation */
textarea {
    height: 80px; /* Ajustez selon vos besoins */
}

/* Style pour le champ de téléchargement de fichier */
input[type='file'] {
    padding: 0.5rem;
    border: 1px solid #ccc;
    border-radius: 4px;
    background-color: #f9f9f9;
}
</style>
