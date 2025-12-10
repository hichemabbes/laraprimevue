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
                <!-- Bouton de fermeture en haut à droite avec animation de survol -->
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
                        class="text-center text-primary font-bold text-2xl mt-3"
                    >
                        {{
                            isEditing
                                ? 'Modifier un Secteur'
                                : 'Ajouter un Secteur'
                        }}
                    </h3>

                    <!-- Champ pour le code -->
                    <div class="inline-flex flex-column gap-2">
                        <label for="code" class="text-primary font-semibold"
                            >Code</label
                        >
                        <InputText
                            id="code"
                            v-model="newSecteur.code"
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
                            v-model="newSecteur.nom_fr"
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
                            v-model="newSecteur.nom_ar"
                            class="w-20rem"
                            @input="validateNomAr"
                        ></InputText>
                        <small v-if="errors.nom_ar" class="text-red-500">{{
                            errors.nom_ar
                        }}</small>
                    </div>

                    <!-- Champ pour le type -->
                    <div class="inline-flex flex-column gap-2">
                        <label for="type" class="text-primary font-semibold"
                            >Type</label
                        >
                        <Dropdown
                            id="type"
                            v-model="newSecteur.type"
                            :options="types"
                            placeholder="Sélectionner un type"
                            class="w-20rem"
                            @change="validateType"
                        />
                        <small v-if="errors.type" class="text-red-500">{{
                            errors.type
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
                            @click="isEditing ? updateSecteur() : saveSecteur()"
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
        secteurToEdit: {
            type: Object,
            default: null,
        },
    },
    emits: ['update:visible', 'close', 'save', 'update'],
    data() {
        return {
            newSecteur: {
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
            isSaving: false, // Ajoutez cette variable pour gérer l'état de sauvegarde
        };
    },
    computed: {
        isEditing() {
            return !!this.secteurToEdit;
        },
    },
    watch: {
        secteurToEdit: {
            immediate: true,
            handler(newVal) {
                if (newVal) {
                    this.newSecteur = { ...newVal };
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
            this.newSecteur = {
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
        async secteurCodeUnique(code) {
            try {
                const response = await axios.get('/api/code-secteur', {
                    params: {
                        code: code,
                        id: this.isEditing ? this.secteurToEdit.id : null,
                    },
                });
                return response.data.unique;
            } catch (error) {
                console.error(
                    "Erreur lors de la vérification de l'unicité de code:",
                    error
                );
                return false;
            }
        },
        async validateCode() {
            if (!this.newSecteur.code) {
                this.errors.code = 'Le code est obligatoire.';
            } else if (this.newSecteur.code.length > 255) {
                this.errors.code =
                    'Le code ne doit pas dépasser 255 caractères.';
            } else {
                const isUnique = await this.secteurCodeUnique(
                    this.newSecteur.code
                );
                if (!isUnique) {
                    this.errors.code =
                        'Le code est déjà utilisé pour un secteur actif.';
                } else {
                    this.errors.code = '';
                }
            }
        },
        validateNomFr() {
            if (this.newSecteur.nom_fr && this.newSecteur.nom_fr.length > 255) {
                this.errors.nom_fr =
                    'Le nom en français ne doit pas dépasser 255 caractères.';
            } else {
                this.errors.nom_fr = '';
            }
        },
        validateNomAr() {
            if (!this.newSecteur.nom_ar) {
                this.errors.nom_ar = 'Le nom en arabe est obligatoire.';
            } else if (this.newSecteur.nom_ar.length > 255) {
                this.errors.nom_ar =
                    'Le nom en arabe ne doit pas dépasser 255 caractères.';
            } else {
                this.errors.nom_ar = '';
            }
        },
        validateType() {
            if (!this.newSecteur.type) {
                this.errors.type = 'Le type est obligatoire.';
            } else if (!this.types.includes(this.newSecteur.type)) {
                this.errors.type = "Le type doit être 'مقيس' ou 'غير مقيس'.";
            } else {
                this.errors.type = '';
            }
        },
        async saveSecteur() {
            this.isSaving = true; // Désactiver le bouton pour éviter les doubles clics

            await this.validateCode();
            this.validateNomFr();
            this.validateNomAr();
            this.validateType();

            if (Object.values(this.errors).some((error) => error !== '')) {
                this.isSaving = false; // Réactiver le bouton si des erreurs existent
                return;
            }

            this.$emit('save', this.newSecteur);
            this.resetForm(); // Réinitialiser le formulaire après l'enregistrement
            this.isSaving = false; // Réactiver le bouton après la sauvegarde
        },
        async updateSecteur() {
            // Valider tous les champs avant de mettre à jour
            await this.validateCode();
            this.validateNomFr();
            this.validateNomAr();
            this.validateType();

            // Vérifier s'il y a des erreurs de validation
            if (Object.values(this.errors).some((error) => error !== '')) {
                return; // Ne pas continuer si des erreurs existent
            }

            // Préparer les données à envoyer
            const payload = {
                ...this.newSecteur,
                // Si nécessaire, formater les données ici (comme les dates dans le cas des années de formation)
            };

            // Émettre l'événement pour informer le parent
            this.$emit('update', payload);
            this.resetForm(); // Réinitialiser le formulaire après la mise à jour
            this.close(); // Fermer le popup après la mise à jour
        },
    },
};
</script>
