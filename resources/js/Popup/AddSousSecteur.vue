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
                <!-- Bouton de fermeture en haut à droite -->
                <i
                    class="pi pi-times absolute cursor-pointer text-primary-50 text-xl"
                    style="top: 10px; right: 10px"
                    @click="close"
                ></i>

                <div
                    class="flex flex-column px-5 py-5 gap-4"
                    style="border-radius: 12px; background: var(--surface-card)"
                >
                    <!-- Titre du formulaire -->
                    <h3
                        class="text-center text-primary font-bold text-2xl mt-3"
                    >
                        {{
                            isEditing
                                ? 'Modifier un Sous-Secteur'
                                : 'Ajouter un Sous-Secteur'
                        }}
                    </h3>

                    <!-- Champ pour le code -->
                    <div class="inline-flex flex-column gap-2">
                        <label for="code" class="text-primary font-semibold"
                            >Code</label
                        >
                        <InputText
                            id="code"
                            v-model="newSousSecteur.code"
                            class="w-20rem"
                            @input="validateCode"
                        ></InputText>
                        <small v-if="errors.code" class="text-red-500">{{
                            errors.code
                        }}</small>
                    </div>

                    <!-- Champ pour le nom en français -->
                    <div class="inline-flex flex-column gap-2">
                        <label for="nom_fr" class="text-primary font-semibold"
                            >Nom (FR)</label
                        >
                        <InputText
                            id="nom_fr"
                            v-model="newSousSecteur.nom_fr"
                            class="w-20rem"
                            @input="validateNomFr"
                        ></InputText>
                        <small v-if="errors.nom_fr" class="text-red-500">{{
                            errors.nom_fr
                        }}</small>
                    </div>

                    <!-- Champ pour le nom en arabe -->
                    <div class="inline-flex flex-column gap-2">
                        <label for="nom_ar" class="text-primary font-semibold"
                            >Nom (AR)</label
                        >
                        <InputText
                            id="nom_ar"
                            v-model="newSousSecteur.nom_ar"
                            class="w-20rem"
                            @input="validateNomAr"
                        ></InputText>
                        <small v-if="errors.nom_ar" class="text-red-500">{{
                            errors.nom_ar
                        }}</small>
                    </div>

                    <!-- Champ pour sélectionner le secteur parent -->
                    <div class="inline-flex flex-column gap-2">
                        <label
                            for="secteur_id"
                            class="text-primary font-semibold"
                            >Secteur Parent</label
                        >
                        <Dropdown
                            id="secteur_id"
                            v-model="newSousSecteur.secteur_id"
                            :options="secteurs"
                            optionLabel="nom_fr"
                            optionValue="id"
                            placeholder="Sélectionner un secteur"
                            class="w-20rem"
                            @change="validateSecteurId"
                        />
                        <small v-if="errors.secteur_id" class="text-red-500">{{
                            errors.secteur_id
                        }}</small>
                    </div>

                    <!-- Boutons d'action -->
                    <div class="flex align-items-center gap-3">
                        <Button
                            label="Annuler"
                            severity="secondary"
                            @click="close"
                            text
                            class="p-3 w-full text-primary border-1 border-surface-300 hover:bg-surface-100"
                        ></Button>
                        <Button
                            :label="isEditing ? 'Modifier' : 'Enregistrer'"
                            severity="secondary"
                            @click="
                                isEditing
                                    ? updateSousSecteur()
                                    : saveSousSecteur()
                            "
                            text
                            class="p-3 w-full text-primary border-1 border-surface-300 hover:bg-surface-100"
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
        sousSecteurToEdit: {
            type: Object,
            default: null,
        },
    },
    emits: ['update:visible', 'close', 'save', 'update'],
    data() {
        return {
            newSousSecteur: {
                code: '',
                nom_fr: '',
                nom_ar: '',
                secteur_id: null,
            },
            secteurs: [], // Liste des secteurs pour le dropdown
            errors: {
                code: '',
                nom_fr: '',
                nom_ar: '',
                secteur_id: '',
            },
        };
    },
    computed: {
        isEditing() {
            return !!this.sousSecteurToEdit;
        },
    },
    watch: {
        sousSecteurToEdit: {
            immediate: true,
            handler(newVal) {
                if (newVal) {
                    this.newSousSecteur = { ...newVal };
                } else {
                    this.resetForm();
                }
            },
        },
    },
    async created() {
        await this.fetchSecteurs(); // Charger la liste des secteurs au montage du composant
    },
    methods: {
        close() {
            this.resetForm(); // Réinitialiser le formulaire
            this.$emit('update:visible', false); // Fermer le popup
            this.$emit('close'); // Émettre l'événement de fermeture
        },
        resetForm() {
            this.newSousSecteur = {
                code: '',
                nom_fr: '',
                nom_ar: '',
                secteur_id: null,
            };
            this.errors = {
                code: '',
                nom_fr: '',
                nom_ar: '',
                secteur_id: '',
            };
        },
        async fetchSecteurs() {
            try {
                const response = await axios.get('/api/secteurs');
                this.secteurs = response.data;
            } catch (error) {
                console.error(
                    'Erreur lors du chargement des secteurs :',
                    error
                );
            }
        },
        async checkCodeUniqueness(code) {
            try {
                const response = await axios.get('/api/check-code-unique', {
                    params: {
                        code: code,
                        id: this.isEditing ? this.sousSecteurToEdit.id : null, // Envoyer l'ID en mode édition
                    },
                });
                return response.data.unique; // true si unique, false sinon
            } catch (error) {
                console.error(
                    "Erreur lors de la vérification de l'unicité du code :",
                    error
                );
                return false;
            }
        },
        async validateCode() {
            if (!this.newSousSecteur.code) {
                this.errors.code = 'Le code est obligatoire.';
            } else if (this.newSousSecteur.code.length > 255) {
                this.errors.code =
                    'Le code ne doit pas dépasser 255 caractères.';
            } else {
                // Vérifier l'unicité du code
                const isUnique = await this.checkCodeUniqueness(
                    this.newSousSecteur.code
                );
                if (!isUnique) {
                    this.errors.code =
                        'Le code est déjà utilisé pour un sous-secteur actif.';
                } else {
                    this.errors.code = '';
                }
            }
        },
        validateNomFr() {
            if (
                this.newSousSecteur.nom_fr &&
                this.newSousSecteur.nom_fr.length > 255
            ) {
                this.errors.nom_fr =
                    'Le nom en français ne doit pas dépasser 255 caractères.';
            } else {
                this.errors.nom_fr = '';
            }
        },
        validateNomAr() {
            if (!this.newSousSecteur.nom_ar) {
                this.errors.nom_ar = 'Le nom en arabe est obligatoire.';
            } else if (this.newSousSecteur.nom_ar.length > 255) {
                this.errors.nom_ar =
                    'Le nom en arabe ne doit pas dépasser 255 caractères.';
            } else {
                this.errors.nom_ar = '';
            }
        },
        validateSecteurId() {
            if (!this.newSousSecteur.secteur_id) {
                this.errors.secteur_id = 'Le secteur parent est obligatoire.';
            } else {
                this.errors.secteur_id = '';
            }
        },
        async saveSousSecteur() {
            await this.validateCode(); // Valider le code avant d'enregistrer
            this.validateNomFr();
            this.validateNomAr();
            this.validateSecteurId();

            if (Object.values(this.errors).some((error) => error !== '')) {
                return;
            }

            this.$emit('save', this.newSousSecteur);
            this.resetForm(); // Réinitialiser le formulaire après l'enregistrement
            this.close(); // Fermer le popup après l'enregistrement
        },
        async updateSousSecteur() {
            await this.validateCode(); // Valider le code avant de mettre à jour
            this.validateNomFr();
            this.validateNomAr();
            this.validateSecteurId();

            if (Object.values(this.errors).some((error) => error !== '')) {
                return;
            }

            this.$emit('update', this.newSousSecteur);
            this.resetForm(); // Réinitialiser le formulaire après la mise à jour
            this.close(); // Fermer le popup après la mise à jour
        },
    },
};
</script>
