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
                    <h5 class="text-center text-primary font-bold text-l mt-3">
                        {{
                            isEditing
                                ? 'Modifier un Sous Secteur'
                                : 'Ajouter un Sous Secteur'
                        }}
                    </h5>

                    <!-- Champ pour le code -->
                    <div class="inline-flex flex-column gap-2">
                        <label for="code" class="text-primary font-semibold"
                            >Code</label
                        >
                        <InputText
                            id="code"
                            v-model="newElement.code"
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
                            v-model="newElement.nom_fr"
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
                            v-model="newElement.nom_ar"
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
                            v-model="newElement.secteur_id"
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
                            @click="isEditing ? update() : save()"
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
import Toast from 'primevue/toast';

export default {
    props: {
        visible: {
            type: Boolean,
            required: true,
        },
        initialData: {
            type: Object,
            default: null,
        },
    },
    emits: ['update:visible', 'close', 'save', 'update'],
    data() {
        return {
            newElement: {
                code: '',
                nom_fr: '',
                nom_ar: '',
                secteur_id: null,
            },
            secteurs: [], // Liste des secteurs
            errors: {
                code: '',
                nom_fr: '',
                nom_ar: '',
                secteur_id: '',
            },
            isSaving: false,
        };
    },
    computed: {
        isEditing() {
            return !!this.initialData; // Vérifie si initialData contient des données
        },
    },
    watch: {
        // Surveiller les changements de initialData
        initialData: {
            immediate: true,
            handler(newVal) {
                if (newVal) {
                    // Copier les données de initialData dans newElement
                    this.newElement = { ...newVal };
                } else {
                    // Réinitialiser le formulaire si initialData est null
                    this.resetForm();
                }
            },
        },
        // Surveiller les changements de visible
        visible(newVal) {
            if (!newVal) {
                // Réinitialiser le formulaire lorsqu'il est fermé
                this.resetForm();
            }
        },
    },
    async created() {
        await this.fetchSecteurs(); // Charger la liste des secteurs au montage du composant
    },
    methods: {
        close() {
            this.resetForm();
            this.$emit('update:visible', false);
            this.$emit('close');
        },
        resetForm() {
            this.newElement = {
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
                // Filtrer les secteurs où type === "مقيس"
                this.secteurs = response.data.filter(
                    (secteur) => secteur.type === 'مقيس'
                );
            } catch (error) {
                console.error(
                    'Erreur lors du chargement des secteurs :',
                    error
                );
            }
        },
        async CodeUnique(code) {
            try {
                const response = await axios.get('/api/check-code-unique', {
                    params: {
                        code: code,
                        id: this.isEditing ? this.newElement.id : null,
                    },
                });
                return response.data.unique; // Doit retourner true si le code est unique, sinon false
            } catch (error) {
                console.error(
                    "Erreur lors de la vérification de l'unicité du code :",
                    error
                );
                return false;
            }
        },
        async validateCode() {
            if (!this.newElement.code) {
                this.errors.code = 'Le code est obligatoire.';
            } else if (this.newElement.code.length > 255) {
                this.errors.code =
                    'Le code ne doit pas dépasser 255 caractères.';
            } else {
                const isUnique = await this.CodeUnique(this.newElement.code);
                if (!isUnique) {
                    this.errors.code =
                        'Le code est déjà utilisé pour un sous secteur actif.';
                } else {
                    this.errors.code = '';
                }
            }
        },
        validateNomFr() {
            if (this.newElement.nom_fr && this.newElement.nom_fr.length > 255) {
                this.errors.nom_fr =
                    'Le nom en français ne doit pas dépasser 255 caractères.';
            } else {
                this.errors.nom_fr = '';
            }
        },
        validateNomAr() {
            if (!this.newElement.nom_ar) {
                this.errors.nom_ar = 'Le nom en arabe est obligatoire.';
            } else if (this.newElement.nom_ar.length > 255) {
                this.errors.nom_ar =
                    'Le nom en arabe ne doit pas dépasser 255 caractères.';
            } else {
                this.errors.nom_ar = '';
            }
        },
        validateSecteurId() {
            if (!this.newElement.secteur_id) {
                this.errors.secteur_id = 'Le secteur parent est obligatoire.';
            } else {
                this.errors.secteur_id = '';
            }
        },
        async save() {
            this.isSaving = true;

            await this.validateCode();
            this.validateNomFr();
            this.validateNomAr();
            this.validateSecteurId();

            if (Object.values(this.errors).some((error) => error !== '')) {
                this.isSaving = false;
                return;
            }

            this.$emit('save', this.newElement);
            this.resetForm();
            this.isSaving = false;
        },

        async update() {
            await this.validateCode();
            this.validateNomFr();
            this.validateNomAr();
            this.validateSecteurId();

            if (Object.values(this.errors).some((error) => error !== '')) {
                return;
            }

            try {
                const response = await axios.put(
                    `/api/sous-secteurs/${this.newElement.id}`,
                    this.newElement
                );
                this.$emit('update', response.data);
                this.resetForm();
                this.close();
            } catch (error) {
                console.error(
                    'Erreur lors de la mise à jour du sous-secteur :',
                    error
                );
            }
        },
    },
};
</script>
