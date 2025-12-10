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
                <!-- Bouton de fermeture en haut à droite avec fond circulaire transparent et effet de survol -->
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
                            isEditing
                                ? 'Modifier une Spécialité'
                                : 'Ajouter une Spécialité'
                        }}
                    </h3>
                    <!-- Champs des formulaire -->

                    <!-- Conteneur pour les trois premiers champs (Année, Type, Homologation) -->
                    <div class="flex flex-nowrap justify-between gap-4">
                        <!-- Champ pour le type -->
                        <div class="flex flex-column w-1/3 min-w-0 gap-2">
                            <label for="type" class="text-primary">Type</label>
                            <Dropdown
                                id="type"
                                v-model="newElement.type"
                                :options="types"
                                placeholder="Sélectionner un type"
                                class="w-full"
                            />
                            <small v-if="errors.type" class="text-red-500">{{
                                errors.type
                            }}</small>
                        </div>

                        <!-- Champ pour le code -->
                        <div class="flex flex-column w-1/3 min-w-0 gap-2">
                            <label for="code" class="text-primary">Code</label>
                            <InputText
                                id="code"
                                v-model="newElement.code"
                                class="w-full"
                            />
                            <small v-if="errors.code" class="text-red-500">{{
                                errors.code
                            }}</small>
                        </div>
                    </div>

                    <!-- Division en deux colonnes avec espacement -->
                    <div class="flex flex-nowrap justify-between gap-4">
                        <!-- Champ pour sélectionner le secteur parent -->
                        <div class="flex flex-column w-1/2 min-w-0 gap-2">
                            <label for="secteur_id" class="text-primary"
                                >Secteur</label
                            >
                            <Dropdown
                                id="secteur_id"
                                v-model="newElement.secteur_id"
                                :options="filterSecteursByType(newElement.type)"
                                optionLabel="nom_fr"
                                optionValue="id"
                                placeholder="Sélectionner un secteur"
                                class="w-full"
                                @change="updateSousSecteurs"
                            />
                            <small
                                v-if="errors.secteur_id"
                                class="text-red-500"
                                >{{ errors.secteur_id }}</small
                            >
                        </div>
                        <!-- Champ pour sélectionner le sous secteur parent -->
                        <div
                            v-if="newElement.type === 'مقيس'"
                            class="flex flex-column w-1/2 min-w-0 gap-2"
                        >
                            <label for="sous_secteur_id" class="text-primary"
                                >Sous Secteur</label
                            >
                            <Dropdown
                                id="sous_secteur_id"
                                v-model="newElement.sous_secteur_id"
                                :options="sousSecteurs"
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
                    </div>

                    <!-- Division en deux colonnes avec espacement -->
                    <div class="flex flex-nowrap justify-between gap-4">
                        <!-- Champ pour le diplôme -->
                        <div class="flex flex-column w-1/2 min-w-0 gap-2">
                            <label for="diplôme" class="text-primary"
                                >Diplôme</label
                            >
                            <Dropdown
                                id="diplôme"
                                v-model="newElement.diplôme"
                                :options="diplomes"
                                placeholder="Sélectionner Diplôme type"
                                class="w-full"
                            />
                            <small v-if="errors.diplôme" class="text-red-500">{{
                                errors.diplôme
                            }}</small>
                        </div>
                        <!-- Champ pour la durée de formation -->
                        <div class="flex flex-column w-1/2 gap-2">
                            <label for="duree_formation" class="text-primary"
                                >Durée Formation</label
                            >
                            <InputText
                                id="duree_formation"
                                v-model="newElement.duree_formation"
                                class="w-full"
                            />
                            <small
                                v-if="errors.duree_formation"
                                class="text-red-500"
                                >{{ errors.duree_formation }}</small
                            >
                        </div>
                    </div>

                    <!-- Champ pour le nom (AR) -->
                    <div class="inline-flex flex-column gap-2">
                        <label for="nom_ar" class="text-primary"
                            >Nom (AR)</label
                        >
                        <InputText
                            id="nom_ar"
                            v-model="newElement.nom_ar"
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
                            v-model="newElement.nom_fr"
                            class="w-full"
                        />
                        <small v-if="errors.nom_fr" class="text-red-500">{{
                            errors.nom_fr
                        }}</small>
                    </div>

                    <!-- Division en deux colonnes avec espacement -->
                    <div class="flex justify-content-between gap-4">
                        <!-- Champ pour l'homologation -->
                        <div class="flex flex-column w-1/2 min-w-0 gap-2">
                            <label for="homologation" class="text-primary"
                                >Homologation</label
                            >
                            <Dropdown
                                id="homologation"
                                v-model="newElement.homologation"
                                :options="homologation"
                                placeholder="Sélectionner Homologation type"
                                class="w-full"
                            />
                            <small
                                v-if="errors.homologation"
                                class="text-red-500"
                                >{{ errors.homologation }}</small
                            >
                        </div>
                        <!-- Champ pour la date d'arrêté -->
                        <div class="flex flex-column w-1/2 gap-2">
                            <label for="date_arrete" class="text-primary"
                                >Date Arrêté</label
                            >
                            <Calendar
                                id="date_arrete"
                                v-model="newElement.date_arrete"
                                dateFormat="dd/mm/yy"
                                class="w-full"
                            />
                            <small
                                v-if="errors.date_arrete"
                                class="text-red-500"
                                >{{ errors.date_arrete }}</small
                            >
                        </div>
                    </div>

                    <!-- Division en deux colonnes avec espacement -->
                    <div class="flex justify-content-between gap-4">
                        <!-- Champ pour le numéro du journal officiel -->
                        <div class="flex flex-column w-1/2 gap-2">
                            <label
                                for="num_journal_officiel"
                                class="text-primary"
                                >Numéro Journal Officiel</label
                            >
                            <InputText
                                id="num_journal_officiel"
                                v-model="newElement.num_journal_officiel"
                                class="w-full"
                            />
                            <small
                                v-if="errors.num_journal_officiel"
                                class="text-red-500"
                                >{{ errors.num_journal_officiel }}</small
                            >
                        </div>

                        <!-- Champ pour la date du journal officiel -->
                        <div class="flex flex-column w-1/2 gap-2">
                            <label
                                for="date_journal_officiel"
                                class="text-primary"
                                >Date Journal Officiel</label
                            >
                            <Calendar
                                id="date_journal_officiel"
                                v-model="newElement.date_journal_officiel"
                                dateFormat="dd/mm/yy"
                                class="w-full"
                            />
                            <small
                                v-if="errors.date_journal_officiel"
                                class="text-red-500"
                                >{{ errors.date_journal_officiel }}</small
                            >
                        </div>
                    </div>

                    <!-- Division en deux colonnes avec espacement -->
                    <div class="flex justify-content-between gap-4">
                        <!-- Champ pour les heures techniques -->
                        <div class="flex flex-column w-1/3 gap-2">
                            <label for="heures_technique" class="text-primary"
                                >Heures ET</label
                            >
                            <InputText
                                id="heures_technique"
                                v-model="newElement.heures_technique"
                                class="w-full"
                            />
                            <small
                                v-if="errors.heures_technique"
                                class="text-red-500"
                                >{{ errors.heures_technique }}</small
                            >
                        </div>

                        <!-- Champ pour les heures généraux -->
                        <div class="flex flex-column w-1/3 gap-2">
                            <label for="heures_generaux" class="text-primary"
                                >Heures EG</label
                            >
                            <InputText
                                id="heures_generaux"
                                v-model="newElement.heures_generaux"
                                class="w-full"
                            />
                            <small
                                v-if="errors.heures_generaux"
                                class="text-red-500"
                                >{{ errors.heures_generaux }}</small
                            >
                        </div>

                        <!-- Champ pour les heures totales -->
                        <div class="flex flex-column w-1/3 gap-2">
                            <label for="heures_total" class="text-primary"
                                >Heures Totales</label
                            >
                            <InputText
                                id="heures_total"
                                v-model="newElement.heures_total"
                                class="w-full"
                            />
                            <small
                                v-if="errors.heures_total"
                                class="text-red-500"
                                >{{ errors.heures_total }}</small
                            >
                        </div>
                    </div>

                    <!-- Division en deux colonnes avec espacement -->
                    <div class="flex flex-nowrap justify-between gap-4">
                        <!-- Champ pour le statut -->
                        <div class="flex flex-column w-1/3 min-w-0 gap-2">
                            <label for="statut" class="text-primary"
                                >Statut</label
                            >
                            <Dropdown
                                id="statut"
                                v-model="newElement.statut"
                                :options="statuts"
                                placeholder="Sélectionner un statut"
                                class="w-full"
                            />
                            <small v-if="errors.statut" class="text-red-500">{{
                                errors.statut
                            }}</small>
                        </div>

                        <!-- Champ pour la date de création de la spécialité -->
                        <div class="flex flex-column w-1/3 min-w-0 gap-2">
                            <label
                                for="date_creation_specialité"
                                class="text-primary"
                                >Date Création</label
                            >
                            <Calendar
                                id="date_creation_specialité"
                                v-model="newElement.date_creation_specialite"
                                dateFormat="dd/mm/yy"
                                class="w-full"
                            />
                            <small
                                v-if="errors.date_creation_specialite"
                                class="text-red-500"
                                >{{ errors.date_creation_specialite }}</small
                            >
                        </div>

                        <!-- Champ pour la date d'annulation de la spécialité -->
                        <div class="flex flex-column w-1/3 min-w-0 gap-2">
                            <label
                                for="date_annulation_specialité"
                                class="text-primary"
                                >Date Annulation</label
                            >
                            <Calendar
                                id="date_annulation_specialité"
                                v-model="newElement.date_annulation_specialite"
                                dateFormat="dd/mm/yy"
                                class="w-full"
                            />
                            <small
                                v-if="errors.date_annulation_specialite"
                                class="text-red-500"
                                >{{ errors.date_annulation_specialite }}</small
                            >
                        </div>
                    </div>

                    <!-- Champ pour l'observation -->
                    <div class="inline-flex flex-column gap-2">
                        <label for="observation" class="text-primary"
                            >Observation</label
                        >
                        <Textarea
                            id="observation"
                            v-model="newElement.observation"
                            class="w-full h-20 p-2 border rounded-md"
                            rows="4"
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
    </Sidebar>
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
                nom_ar: '',
                nom_fr: '',
                type: '',
                homologation: '',
                date_arrete: null,
                num_journal_officiel: '',
                date_journal_officiel: null,
                duree_formation: '',
                diplôme: '',
                heures_technique: '',
                heures_generaux: '',
                heures_total: '',
                date_creation_specialité: null,
                statut: null,
                observation: '',
                date_annulation_specialité: null,
            },
            statuts: ['نشط', 'غير نشط', 'ملغى'],
            types: ['مقيس', 'غير مقيس'],
            homologation: ['منظر', 'غير منظر'],
            diplomes: [
                'شهادة مؤهل تقني سامي',
                'شهادة مؤهل تقني مهني',
                'شهادة الكفاءة المهنية',
                'شهادة مهارة',
                'شهادة إنهاء التدريب',
            ],

            secteurs: [], // Liste des secteurs
            soussecteurs: [], // Liste des secteurs
            errors: {
                code: '',
                nom_ar: '',
                nom_fr: '',
                type: '',
                homologation: '',
                date_arrete: '',
                num_journal_officiel: '',
                date_journal_officiel: '',
                duree_formation: '',
                diplôme: '',
                heures_technique: '',
                heures_generaux: '',
                heures_total: '',
                date_creation_specialite: '',
                statut: null,
                observation: '',
                date_annulation_specialite: '',

                secteurs: '',
                soussecteurs: '',
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
        await this.fetchSecteurs(); // Charger les secteurs
        await this.fetchSousSecteurs(); // Charger les sous-secteurs
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
                secteur_id: null,
                sous_secteur_id: null,
                nom_ar: '',
                nom_fr: '',
                type: '',
                homologation: '',
                date_arrete: null,
                num_journal_officiel: '',
                date_journal_officiel: null,
                duree_formation: '',
                diplôme: '',
                heures_technique: '',
                heures_generaux: '',
                heures_total: '',
                date_creation_specialité: null,
                statut: null,
                observation: '',
                date_annulation_specialité: null,

                secteurs: '',
                soussecteurs: '',
            };
            this.errors = {
                code: '',
                nom_ar: '',
                nom_fr: '',
                type: '',
                homologation: '',
                date_arrete: '',
                num_journal_officiel: '',
                date_journal_officiel: '',
                duree_formation: '',
                diplôme: '',
                heures_technique: '',
                heures_generaux: '',
                heures_total: '',
                date_creation_specialite: '',
                statut: null,
                observation: '',
                date_annulation_specialite: '',

                secteurs: '',
                soussecteurs: '',
            };
        },

        // Méthode pour filtrer les secteurs en fonction du type de spécialité
        filterSecteursByType(type) {
            return this.secteurs.filter((secteur) => secteur.type === type);
        },

        // Méthode pour filtrer les sous-secteurs en fonction du secteur sélectionné
        filterSousSecteursBySecteur(secteurId) {
            return this.sousSecteurs.filter(
                (sousSecteur) => sousSecteur.secteur_id === secteurId
            );
        },

        // Méthode pour mettre à jour les sous-secteurs en fonction du secteur sélectionné
        updateSousSecteurs() {
            if (this.newElement.secteur_id) {
                this.sousSecteurs = this.filterSousSecteursBySecteur(
                    this.newElement.secteur_id
                );
            } else {
                this.sousSecteurs = [];
            }
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

        formatDate(date) {
            if (!date || !(date instanceof Date)) {
                return null;
            }
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
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
        validateFields() {
            this.errors = {};
            if (!this.newElement.nom_ar)
                this.errors.nom_ar = 'Le nom en arabe est obligatoire.';
            if (!this.newElement.nom_fr)
                this.errors.nom_fr = 'Le nom en français est obligatoire.';
            if (!this.newElement.secteur_id)
                this.errors.secteur_id = 'Le secteur est obligatoire.';
            return Object.keys(this.errors).length === 0;
        },
        async save() {
            if (!(await this.validateCode()) || !this.validateFields()) return;

            try {
                const response = await axios.post(
                    '/api/specialites',
                    this.newElement
                );
                this.$emit('save', response.data);
                this.$toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Spécialité ajoutée.',
                    life: 3000,
                });
                this.close();
            } catch (error) {
                console.error(
                    "Erreur lors de l'ajout de la spécialité :",
                    error
                );
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: "Une erreur s'est produite.",
                    life: 3000,
                });
            }
        },
        async update() {
            if (!(await this.validateCode()) || !this.validateFields()) return;

            try {
                const response = await axios.put(
                    `/api/specialites/${this.newElement.id}`,
                    this.newElement
                );
                this.$emit('update', response.data);
                this.$toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Spécialité mise à jour.',
                    life: 3000,
                });
                this.close();
            } catch (error) {
                console.error(
                    'Erreur lors de la mise à jour de la spécialité :',
                    error
                );
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: "Une erreur s'est produite.",
                    life: 3000,
                });
            }
        },
    },
};
</script>
