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
                <!-- Bouton de fermeture -->
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

                <div class="p-5 surface-overlay" style="width: 500px">
                    <!-- Titre du formulaire -->
                    <h5 class="text-center font-bold text-lg mt-3">
                        {{
                            specialiteToEdit
                                ? 'Modifier une Spécialité'
                                : 'Ajouter une Spécialité'
                        }}
                    </h5>

                    <!-- Champ Code et Type -->
                    <div class="flex flex-column gap-2">
                        <label for="code" class="font-semibold">Code</label>
                        <InputText
                            id="code"
                            v-model="newSpecialite.code"
                            class="w-full"
                        />
                        <small v-if="errors.code" class="text-red-500">{{
                            errors.code
                        }}</small>
                    </div>

                    <div class="flex flex-column gap-2">
                        <label for="type" class="font-semibold">Type</label>
                        <Dropdown
                            id="type"
                            v-model="newSpecialite.type"
                            :options="types"
                            placeholder="Sélectionner un type"
                            class="w-full"
                            @change="onTypeChange"
                        />
                        <small v-if="errors.type" class="text-red-500">{{
                            errors.type
                        }}</small>
                    </div>

                    <!-- Champ Secteur et Sous-Secteur -->
                    <div
                        v-if="
                            newSpecialite.type && newSpecialite.type !== 'جديد'
                        "
                    >
                        <div class="flex flex-column gap-2">
                            <label for="secteur_id" class="font-semibold"
                                >Secteur</label
                            >
                            <Dropdown
                                id="secteur_id"
                                v-model="newSpecialite.secteur_id"
                                :options="filteredSecteurs"
                                optionLabel="nom_fr"
                                optionValue="id"
                                placeholder="Sélectionner un secteur"
                                class="w-full"
                            />
                            <small
                                v-if="errors.secteur_id"
                                class="text-red-500"
                                >{{ errors.secteur_id }}</small
                            >
                        </div>

                        <div
                            class="flex flex-column gap-2"
                            v-if="newSpecialite.type === 'مقيس'"
                        >
                            <label for="sous_secteur_id" class="font-semibold"
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
                    </div>

                    <!-- Champ Nom (AR) et Nom (FR) -->
                    <div class="flex flex-column gap-2">
                        <label for="nom_ar" class="font-semibold"
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

                    <div class="flex flex-column gap-2">
                        <label for="nom_fr" class="font-semibold"
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

                    <!-- Champ Diplôme et Date Création -->
                    <div class="flex flex-column gap-2">
                        <label for="diplome" class="font-semibold"
                            >Diplôme</label
                        >
                        <Dropdown
                            id="diplome"
                            v-model="newSpecialite.diplome"
                            :options="diplomes"
                            placeholder="Sélectionner un diplôme"
                            class="w-full"
                        />
                        <small v-if="errors.diplome" class="text-red-500">{{
                            errors.diplome
                        }}</small>
                    </div>

                    <div class="flex flex-column gap-2">
                        <label for="date_creation" class="font-semibold"
                            >Date Création</label
                        >
                        <Calendar
                            id="date_creation"
                            v-model="newSpecialite.date_creation"
                            dateFormat="dd/mm/yy"
                            class="w-full"
                        />
                        <small
                            v-if="errors.date_creation"
                            class="text-red-500"
                            >{{ errors.date_creation }}</small
                        >
                    </div>

                    <!-- Champ Statut et Observation -->
                    <div class="flex flex-column gap-2">
                        <label for="statut" class="font-semibold">Statut</label>
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

                    <div class="flex flex-column gap-2">
                        <label for="observation" class="font-semibold"
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

                    <!-- Champ Date Annulation -->
                    <div class="flex flex-column gap-2">
                        <label for="date_annulation" class="font-semibold"
                            >Date Annulation</label
                        >
                        <Calendar
                            id="date_annulation"
                            v-model="newSpecialite.date_annulation"
                            dateFormat="dd/mm/yy"
                            class="w-full"
                        />
                        <small
                            v-if="errors.date_annulation"
                            class="text-red-500"
                            >{{ errors.date_annulation }}</small
                        >
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
                                specialiteToEdit ? 'Modifier' : 'Enregistrer'
                            "
                            severity="success"
                            outlined
                            @click="submitForm"
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
        visible: { type: Boolean, required: true },
        specialiteToEdit: { type: Object, default: null },
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
                date_creation: null,
                statut: null,
                observation: '',
                date_annulation: null,
            },
            types: ['جديد', 'مقيس', 'غير مقيس'],
            statuts: ['En vigueur', 'Non validée', 'Annulée'],
            diplomes: ['BTS', 'BTP', 'CAP', 'CC', 'CFA'],
            secteurs: [],
            sousSecteurs: [],
            errors: {},
        };
    },
    computed: {
        filteredSecteurs() {
            if (!this.newSpecialite.type || this.newSpecialite.type === 'جديد')
                return [];
            return this.secteurs.filter((secteur) =>
                this.newSpecialite.type === 'مقيس'
                    ? secteur.type === 'مقيس'
                    : this.newSpecialite.type === 'غير مقيس'
                      ? secteur.type === 'غير مقيس'
                      : true
            );
        },
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
                    this.newSpecialite = {
                        ...newVal,
                        date_creation: newVal.date_creation
                            ? new Date(newVal.date_creation)
                            : null,
                        date_annulation: newVal.date_annulation
                            ? new Date(newVal.date_annulation)
                            : null,
                    };
                } else {
                    this.resetForm();
                }
            },
        },
        'newSpecialite.type': {
            handler() {
                if (this.newSpecialite.type === 'جديد') {
                    this.newSpecialite.secteur_id = null;
                    this.newSpecialite.sous_secteur_id = null;
                } else if (this.newSpecialite.type !== 'مقيس') {
                    this.newSpecialite.sous_secteur_id = null;
                }
            },
        },
    },
    async created() {
        await this.fetchSecteurs();
        await this.fetchSousSecteurs();
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
                date_creation: null,
                statut: '',
                observation: '',
                date_annulation: null,
            };
            this.errors = {};
        },
        close() {
            this.$emit('update:visible', false);
            this.$emit('close');
            this.resetForm();
        },
        onTypeChange() {
            this.newSpecialite.secteur_id = null;
            this.newSpecialite.sous_secteur_id = null;
        },
        validateForm() {
            this.errors = {};
            let isValid = true;

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

            if (this.newSpecialite.type !== 'جديد') {
                if (!this.newSpecialite.secteur_id) {
                    this.errors.secteur_id =
                        'Le secteur est requis pour ce type.';
                    isValid = false;
                } else {
                    const secteur = this.secteurs.find(
                        (s) => s.id === this.newSpecialite.secteur_id
                    );
                    if (
                        secteur &&
                        ((this.newSpecialite.type === 'مقيس' &&
                            secteur.type !== 'مقيس') ||
                            (this.newSpecialite.type === 'غير مقيس' &&
                                secteur.type !== 'غير مقيس'))
                    ) {
                        this.errors.secteur_id = `Le secteur doit être de type '${this.newSpecialite.type}'.`;
                        isValid = false;
                    }
                }
            }

            if (
                this.newSpecialite.type === 'مقيس' &&
                !this.newSpecialite.sous_secteur_id
            ) {
                this.errors.sous_secteur_id =
                    "Le sous-secteur est requis pour une spécialité 'مقيس'.";
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
            if (!date) return null;
            if (typeof date === 'string') date = new Date(date);
            if (!(date instanceof Date) || isNaN(date.getTime())) return null;
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        },
        async submitForm() {
            if (!this.validateForm()) return;

            const payload = {
                ...this.newSpecialite,
                observation: String(this.newSpecialite.observation),
                date_creation: this.formatDateForAPI(
                    this.newSpecialite.date_creation
                ),
                date_annulation: this.formatDateForAPI(
                    this.newSpecialite.date_annulation
                ),
            };

            try {
                if (this.specialiteToEdit) {
                    this.$emit('update', payload);
                } else {
                    this.$emit('save', payload);
                }
                this.$emit('update:visible', false);
            } catch (error) {
                console.error('Erreur lors de la soumission :', error);
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Une erreur est survenue.',
                    life: 3000,
                });
            }
        },
    },
};
</script>
