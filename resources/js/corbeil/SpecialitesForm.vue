<template>
    <Sidebar
        :visible="visible"
        @update:visible="$emit('update:visible', $event)"
        position="right"
        :style="{ width: '25vw' }"
        :pt="{
            root: 'border-none',
            mask: {
                style: 'backdrop-filter: blur(2px)',
            },
        }"
    >
        <template #container="{ closeCallback }">
            <div class="relative h-full">
                <!-- Bouton de fermeture en haut à droite -->
                <i
                    class="pi pi-times absolute cursor-pointer text-gray-400 text-xl hover:bg-gray-500 hover:bg-opacity-30 transition-all"
                    style="
                        top: 20px;
                        right: 30px;
                        width: 30px;
                        height: 30px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        background-color: rgba(0, 0, 0, 0.3);
                        border-radius: 50%;
                    "
                    @click="closeCallback"
                ></i>

                <!-- Conteneur du formulaire avec barre de défilement -->
                <div
                    class="flex flex-column px-5 py-5 gap-4 h-full"
                    style="
                        border-radius: 12px;
                        background: var(--surface-card);
                        overflow-y: auto;
                    "
                >
                    <!-- Titre du formulaire -->
                    <h3
                        class="text-center text-primary font-bold text-2xl mt-3"
                    >
                        {{
                            specialiteToEdit
                                ? 'Modifier une Spécialité'
                                : 'Ajouter une Spécialité'
                        }}
                    </h3>

                    <!-- Champs du formulaire -->
                    <div class="flex flex-nowrap justify-between gap-4">
                        <!-- Champ pour le code -->
                        <div class="flex flex-column w-1/2 min-w-0 gap-2">
                            <label for="code" class="text-primary font-semibold"
                                >Code</label
                            >
                            <InputText
                                id="code"
                                v-model="newSpecialite.code"
                                class="w-full"
                            />
                            <small v-if="errors.code" class="text-red-500">{{
                                errors.code
                            }}</small>
                        </div>

                        <!-- Champ pour le type -->
                        <div class="flex flex-column w-1/2 min-w-0 gap-2">
                            <label for="type" class="text-primary font-semibold"
                                >Type</label
                            >
                            <Dropdown
                                id="type"
                                v-model="newSpecialite.type"
                                :options="types"
                                placeholder="Sélectionner un type"
                                class="w-full"
                            />
                            <small v-if="errors.type" class="text-red-500">{{
                                errors.type
                            }}</small>
                        </div>
                    </div>

                    <!-- Champ pour le secteur -->
                    <div
                        class="flex flex-column gap-2"
                        v-if="newSpecialite.type !== 'جديد'"
                    >
                        <label for="secteur_id" class="text-primary"
                            >Secteur</label
                        >
                        <Dropdown
                            id="secteur_id"
                            v-model="newSpecialite.secteur_id"
                            :options="secteurs"
                            optionLabel="nom_fr"
                            optionValue="id"
                            placeholder="Sélectionner un secteur"
                            class="w-full"
                        />
                        <small v-if="errors.secteur_id" class="text-red-500">{{
                            errors.secteur_id
                        }}</small>
                    </div>

                    <!-- Champ pour le sous-secteur (si type est "مقيس") -->
                    <div
                        v-if="newSpecialite.type === 'مقيس'"
                        class="flex flex-column gap-2"
                    >
                        <label for="sous_secteur_id" class="text-primary"
                            >Sous Secteur</label
                        >
                        <Dropdown
                            id="sous_secteur_id"
                            v-model="newSpecialite.sous_secteur_id"
                            :options="filteredSousSecteurs"
                            optionLabel="nom_fr"
                            optionValue="id"
                            placeholder="Sélectionner un sous secteur"
                            class="w-full"
                        />
                        <small
                            v-if="errors.sous_secteur_id"
                            class="text-red-500"
                            >{{ errors.sous_secteur_id }}</small
                        >
                    </div>

                    <!-- Champ pour le nom (AR) -->
                    <div class="inline-flex flex-column gap-2">
                        <label for="nom_ar" class="text-primary font-semibold"
                            >Nom (AR)</label
                        >
                        <InputText
                            id="nom_ar"
                            v-model="newSpecialite.nom_ar"
                            class="w-full"
                        />
                        <small v-if="errors.nom_ar" class="text-red-500">{{
                            errors.nom_ar
                        }}</small>
                    </div>

                    <!-- Champ pour le nom (FR) -->
                    <div class="inline-flex flex-column gap-2">
                        <label for="nom_fr" class="text-primary font-semibold"
                            >Nom (FR)</label
                        >
                        <InputText
                            id="nom_fr"
                            v-model="newSpecialite.nom_fr"
                            class="w-full"
                        />
                        <small v-if="errors.nom_fr" class="text-red-500">{{
                            errors.nom_fr
                        }}</small>
                    </div>

                    <!-- Champ pour le diplôme -->
                    <div class="inline-flex flex-column gap-2">
                        <label for="diplome" class="text-primary font-semibold"
                            >Diplôme</label
                        >
                        <Dropdown
                            id="diplome"
                            v-model="newSpecialite.diplome"
                            :options="diplomes"
                            placeholder="Sélectionner Diplôme type"
                            class="w-full"
                        />
                        <small v-if="errors.diplome" class="text-red-500">{{
                            errors.diplome
                        }}</small>
                    </div>

                    <!-- Champ pour la date de création de la spécialité -->
                    <div class="inline-flex flex-column gap-2">
                        <label
                            for="date_creation_specialite"
                            class="text-primary font-semibold"
                            >Date Création Spécialité</label
                        >
                        <Calendar
                            id="date_creation_specialite"
                            v-model="newSpecialite.date_creation_specialite"
                            dateFormat="dd/mm/yy"
                            class="w-full"
                        />
                        <small
                            v-if="errors.date_creation_specialite"
                            class="text-red-500"
                            >{{ errors.date_creation_specialite }}</small
                        >
                    </div>

                    <!-- Champ pour le statut -->
                    <div class="inline-flex flex-column gap-2">
                        <label for="statut" class="text-primary font-semibold"
                            >Statut</label
                        >
                        <Dropdown
                            id="statut"
                            v-model="newSpecialite.statut"
                            :options="statuts"
                            placeholder="Sélectionner un statut"
                            class="w-full"
                        />
                        <small v-if="errors.statut" class="text-red-500">{{
                            errors.statut
                        }}</small>
                    </div>

                    <!-- Champ pour l'observation -->
                    <div class="inline-flex flex-column gap-2">
                        <label
                            for="observation"
                            class="text-primary font-semibold"
                            >Observation</label
                        >
                        <InputText
                            id="observation"
                            v-model="newSpecialite.observation"
                            class="w-full"
                        />
                        <small v-if="errors.observation" class="text-red-500">{{
                            errors.observation
                        }}</small>
                    </div>

                    <!-- Champ pour la date d'annulation de la spécialité -->
                    <div class="inline-flex flex-column gap-2">
                        <label
                            for="date_annulation_specialite"
                            class="text-primary font-semibold"
                            >Date Annulation Spécialité</label
                        >
                        <Calendar
                            id="date_annulation_specialite"
                            v-model="newSpecialite.date_annulation_specialite"
                            dateFormat="dd/mm/yy"
                            class="w-full"
                        />
                        <small
                            v-if="errors.date_annulation_specialite"
                            class="text-red-500"
                            >{{ errors.date_annulation_specialite }}</small
                        >
                    </div>

                    <!-- Boutons d'action -->
                    <div class="flex align-items-center gap-3 mt-4">
                        <Button
                            label="Annuler"
                            severity="secondary"
                            @click="closeCallback"
                            text
                            class="p-3 w-full text-primary border-1 border-surface-300 hover:bg-surface-100"
                        ></Button>
                        <Button
                            :label="
                                specialiteToEdit ? 'Modifier' : 'Enregistrer'
                            "
                            severity="secondary"
                            @click="submitForm"
                            text
                            class="p-3 w-full text-primary border-1 border-surface-300 hover:bg-surface-100"
                        ></Button>
                    </div>
                </div>
            </div>
        </template>
    </Sidebar>
</template>

<script>
import axios from 'axios';
import { useToast } from 'primevue/usetoast';

export default {
    props: {
        visible: {
            type: Boolean,
            required: true,
        },
        specialiteToEdit: {
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
            newSpecialite: {
                code: '',
                secteur_id: null,
                sous_secteur_id: null,
                nom_ar: '',
                nom_fr: '',
                type: '',
                diplome: '',

                date_creation_specialite: null,
                statut: null,
                observation: '',
                date_annulation_specialite: null,
            },
            statuts: ['معتمد', 'غير معتمد', 'ملغى'],
            types: ['جديد', 'مقيس', 'غير مقيس'],
            homologation: ['منظر', 'غير منظر'],
            diplomes: [
                'شهادة مؤهل تقني سامي',
                'شهادة مؤهل تقني مهني',
                'شهادة الكفاءة المهنية',
                'شهادة مهارة',
                'شهادة إنهاء التدريب',
            ],
            secteurs: [], // Liste des secteurs
            sousSecteurs: [], // Liste des sous-secteurs
            errors: {},
        };
    },
    computed: {
        // Filtre les sous-secteurs en fonction du secteur sélectionné
        filteredSousSecteurs() {
            if (!this.newSpecialite.secteur_id) return [];
            return this.sousSecteurs.filter(
                (sousSecteur) =>
                    sousSecteur.secteur_id === this.newSpecialite.secteur_id
            );
        },
    },
    watch: {
        specialiteToEdit: {
            immediate: true,
            handler(newVal) {
                if (newVal) {
                    // Convertir les dates en objets Date si elles sont des chaînes
                    this.newSpecialite = {
                        ...newVal,

                        date_creation_specialite:
                            newVal.date_creation_specialite
                                ? new Date(newVal.date_creation_specialite)
                                : null,
                        date_annulation_specialite:
                            newVal.date_annulation_specialite
                                ? new Date(newVal.date_annulation_specialite)
                                : null,
                    };
                } else {
                    this.resetForm();
                }
            },
        },
    },
    async created() {
        await this.fetchSecteurs(); // Charger les secteurs
        await this.fetchSousSecteurs(); // Charger les sous-secteurs
    },
    methods: {
        resetForm() {
            this.newSpecialite = {
                id: null,
                code: '',
                type: '',
                secteur_id: null,
                sous_secteur_id: null,
                nom_ar: '',
                nom_fr: '',

                diplome: '',

                date_creation_specialite: null,
                statut: '',
                observation: '',
                date_annulation_specialite: null,
            };
            this.errors = {};
        },
        close() {
            this.$emit('update:visible', false);
            this.$emit('close');
            this.resetForm();
        },
        validateForm() {
            this.errors = {};
            let isValid = true;

            // Validation des champs obligatoires
            if (!this.newSpecialite.code) {
                this.errors.code = 'Le code est requis.';
                isValid = false;
            }
            if (!this.newSpecialite.nom_ar) {
                this.errors.nom_ar = 'Le nom en arabe est requis.';
                isValid = false;
            }
            if (!this.newSpecialite.type) {
                this.errors.type = 'Le type est requis.';
                isValid = false;
            }

            if (!this.newSpecialite.diplome) {
                this.errors.diplome = 'Le diplôme est requis.';
                isValid = false;
            }
            if (!this.newSpecialite.statut) {
                this.errors.statut = 'Le statut est requis.';
                isValid = false;
            }

            return isValid;
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
        async fetchSousSecteurs() {
            try {
                const response = await axios.get('/api/sous-secteurs');
                this.sousSecteurs = response.data;
            } catch (error) {
                console.error(
                    'Erreur lors du chargement des sous-secteurs :',
                    error
                );
            }
        },

        formatDateForAPI(date) {
            if (!date) return null; // Si la date est null ou undefined, retourne null
            if (typeof date === 'string') date = new Date(date); // Convertit une chaîne en objet Date si nécessaire
            if (!(date instanceof Date) || isNaN(date.getTime())) return null; // Valide l'objet Date

            // Formate la date en YYYY-MM-DD
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0'); // Les mois sont indexés à partir de 0
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        },
        async submitForm() {
            if (!this.validateForm()) {
                return;
            }

            // Formate les dates avant de les envoyer
            const payload = {
                ...this.newSpecialite,
                observation: String(this.newSpecialite.observation), // Convertit en chaîne

                date_creation_specialite: this.formatDateForAPI(
                    this.newSpecialite.date_creation_specialite
                ),
                date_annulation_specialite: this.formatDateForAPI(
                    this.newSpecialite.date_annulation_specialite
                ), // Formate la date (gère null)
            };

            try {
                if (this.specialiteToEdit) {
                    this.$emit('update', payload); // Émet l'événement pour la mise à jour
                } else {
                    this.$emit('save', payload); // Émet l'événement pour la sauvegarde
                }
                this.$emit('update:visible', false); // Ferme le formulaire
            } catch (error) {
                console.error(
                    'Erreur lors de la soumission du formulaire :',
                    error
                );
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Une erreur est survenue lors de la soumission du formulaire.',
                    life: 3000,
                });
            }
        },
    },
};
</script>

<style scoped>
/* Ajoutez des styles personnalisés ici si nécessaire */
</style>
