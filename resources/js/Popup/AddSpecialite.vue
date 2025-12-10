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
                    class="pi pi-times absolute cursor-pointer text-primary-50 text-xl"
                    style="top: 10px; right: 10px"
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

                    <!-- Champ pour le code -->
                    <div class="inline-flex flex-column gap-2">
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

                    <!-- Champ pour le type -->
                    <div class="inline-flex flex-column gap-2">
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

                    <!-- Champ pour l'homologation -->
                    <div class="inline-flex flex-column gap-2">
                        <label
                            for="homologation"
                            class="text-primary font-semibold"
                            >Homologation</label
                        >
                        <Dropdown
                            id="homologation"
                            v-model="newSpecialite.homologation"
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
                    <div class="inline-flex flex-column gap-2">
                        <label
                            for="date_arrete"
                            class="text-primary font-semibold"
                            >Date Arrêté</label
                        >
                        <Calendar
                            id="date_arrete"
                            v-model="newSpecialite.date_arrete"
                            dateFormat="dd/mm/yy"
                            class="w-full"
                        />
                        <small v-if="errors.date_arrete" class="text-red-500">{{
                            errors.date_arrete
                        }}</small>
                    </div>

                    <!-- Champ pour le numéro du journal officiel -->
                    <div class="inline-flex flex-column gap-2">
                        <label
                            for="num_journal_officiel"
                            class="text-primary font-semibold"
                            >Numéro Journal Officiel</label
                        >
                        <InputText
                            id="num_journal_officiel"
                            v-model="newSpecialite.num_journal_officiel"
                            class="w-full"
                        />
                        <small
                            v-if="errors.num_journal_officiel"
                            class="text-red-500"
                            >{{ errors.num_journal_officiel }}</small
                        >
                    </div>

                    <!-- Champ pour la date du journal officiel -->
                    <div class="inline-flex flex-column gap-2">
                        <label
                            for="date_journal_officiel"
                            class="text-primary font-semibold"
                            >Date Journal Officiel</label
                        >
                        <Calendar
                            id="date_journal_officiel"
                            v-model="newSpecialite.date_journal_officiel"
                            dateFormat="dd/mm/yy"
                            class="w-full"
                        />
                        <small
                            v-if="errors.date_journal_officiel"
                            class="text-red-500"
                            >{{ errors.date_journal_officiel }}</small
                        >
                    </div>

                    <!-- Champ pour la durée de formation -->
                    <div class="inline-flex flex-column gap-2">
                        <label
                            for="duree_formation"
                            class="text-primary font-semibold"
                            >Durée Formation</label
                        >
                        <InputText
                            id="duree_formation"
                            v-model="newSpecialite.duree_formation"
                            class="w-full"
                        />
                        <small
                            v-if="errors.duree_formation"
                            class="text-red-500"
                            >{{ errors.duree_formation }}</small
                        >
                    </div>

                    <!-- Champ pour le diplôme -->
                    <div class="inline-flex flex-column gap-2">
                        <label for="diplôme" class="text-primary font-semibold"
                            >Diplôme</label
                        >
                        <Dropdown
                            id="diplôme"
                            v-model="newSpecialite.diplôme"
                            :options="diplomes"
                            placeholder="Sélectionner Diplôme type"
                            class="w-full"
                        />
                        <small v-if="errors.diplôme" class="text-red-500">{{
                            errors.diplôme
                        }}</small>
                    </div>

                    <!-- Champ pour les heures techniques -->
                    <div class="inline-flex flex-column gap-2">
                        <label
                            for="heures_technique"
                            class="text-primary font-semibold"
                            >Heures Techniques</label
                        >
                        <InputText
                            id="heures_technique"
                            v-model="newSpecialite.heures_technique"
                            class="w-full"
                        />
                        <small
                            v-if="errors.heures_technique"
                            class="text-red-500"
                            >{{ errors.heures_technique }}</small
                        >
                    </div>

                    <!-- Champ pour les heures généraux -->
                    <div class="inline-flex flex-column gap-2">
                        <label
                            for="heures_generaux"
                            class="text-primary font-semibold"
                            >Heures Généraux</label
                        >
                        <InputText
                            id="heures_generaux"
                            v-model="newSpecialite.heures_generaux"
                            class="w-full"
                        />
                        <small
                            v-if="errors.heures_generaux"
                            class="text-red-500"
                            >{{ errors.heures_generaux }}</small
                        >
                    </div>

                    <!-- Champ pour les heures totales -->
                    <div class="inline-flex flex-column gap-2">
                        <label
                            for="heures_total"
                            class="text-primary font-semibold"
                            >Heures Totales</label
                        >
                        <InputText
                            id="heures_total"
                            v-model="newSpecialite.heures_total"
                            class="w-full"
                        />
                        <small
                            v-if="errors.heures_total"
                            class="text-red-500"
                            >{{ errors.heures_total }}</small
                        >
                    </div>

                    <!-- Champ pour la date de création de la spécialité -->
                    <div class="inline-flex flex-column gap-2">
                        <label
                            for="date_creation_specialité"
                            class="text-primary font-semibold"
                            >Date Création Spécialité</label
                        >
                        <Calendar
                            id="date_creation_specialité"
                            v-model="newSpecialite.date_creation_specialité"
                            dateFormat="dd/mm/yy"
                            class="w-full"
                        />
                        <small
                            v-if="errors.date_creation_specialité"
                            class="text-red-500"
                            >{{ errors.date_creation_specialité }}</small
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
                            for="date_annulation_specialité"
                            class="text-primary font-semibold"
                            >Date Annulation Spécialité</label
                        >
                        <Calendar
                            id="date_annulation_specialité"
                            v-model="newSpecialite.date_annulation_specialité"
                            dateFormat="dd/mm/yy"
                            class="w-full"
                        />
                        <small
                            v-if="errors.date_annulation_specialité"
                            class="text-red-500"
                            >{{ errors.date_annulation_specialité }}</small
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
                            :label="isEditing ? 'Modifier' : 'Enregistrer'"
                            severity="secondary"
                            @click="
                                isEditing
                                    ? updateSpecialite()
                                    : saveSpecialite()
                            "
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
            errors: {},
        };
    },
    computed: {
        isEditing() {
            return !!this.specialiteToEdit;
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
                        date_arrete: newVal.date_arrete
                            ? new Date(newVal.date_arrete)
                            : null,
                        date_journal_officiel: newVal.date_journal_officiel
                            ? new Date(newVal.date_journal_officiel)
                            : null,
                        date_creation_specialité:
                            newVal.date_creation_specialité
                                ? new Date(newVal.date_creation_specialité)
                                : null,
                        date_annulation_specialité:
                            newVal.date_annulation_specialité
                                ? new Date(newVal.date_annulation_specialité)
                                : null,
                    };
                } else {
                    this.resetForm();
                }
            },
        },
    },
    methods: {
        close() {
            this.$emit('update:visible', false);
            this.$emit('close');
            this.resetForm();
        },
        resetForm() {
            this.newSpecialite = {
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
            };
            this.errors = {};
        },
        formatDate(date) {
            if (!date || !(date instanceof Date)) {
                return null; // Retourne null si la date est invalide
            }
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        },
        validateForm() {
            this.errors = {};

            if (!this.newSpecialite.code) {
                this.errors.code = 'Le code est obligatoire.';
            }
            if (!this.newSpecialite.nom_ar) {
                this.errors.nom_ar = 'Le nom en arabe est obligatoire.';
            }
            if (!this.newSpecialite.nom_fr) {
                this.errors.nom_fr = 'Le nom en français est obligatoire.';
            }
            if (!this.newSpecialite.type) {
                this.errors.type = 'Le type est obligatoire.';
            }
            if (!this.newSpecialite.homologation) {
                this.errors.homologation = "L'homologation est obligatoire.";
            }
            if (!this.newSpecialite.date_arrete) {
                this.errors.date_arrete = "La date d'arrêté est obligatoire.";
            }
            if (!this.newSpecialite.num_journal_officiel) {
                this.errors.num_journal_officiel =
                    'Le numéro du journal officiel est obligatoire.';
            }
            if (!this.newSpecialite.date_journal_officiel) {
                this.errors.date_journal_officiel =
                    'La date du journal officiel est obligatoire.';
            }
            if (!this.newSpecialite.duree_formation) {
                this.errors.duree_formation =
                    'La durée de formation est obligatoire.';
            }
            if (!this.newSpecialite.diplôme) {
                this.errors.diplôme = 'Le diplôme est obligatoire.';
            }
            if (!this.newSpecialite.heures_technique) {
                this.errors.heures_technique =
                    'Les heures techniques sont obligatoires.';
            }
            if (!this.newSpecialite.heures_generaux) {
                this.errors.heures_generaux =
                    'Les heures généraux sont obligatoires.';
            }
            if (!this.newSpecialite.heures_total) {
                this.errors.heures_total =
                    'Les heures totales sont obligatoires.';
            }
            if (!this.newSpecialite.date_creation_specialité) {
                this.errors.date_creation_specialité =
                    'La date de création est obligatoire.';
            }
            if (!this.newSpecialite.statut) {
                this.errors.statut = 'Le statut est obligatoire.';
            }

            return Object.keys(this.errors).length === 0;
        },
        async saveSpecialite() {
            if (!this.validateForm()) {
                return;
            }

            const payload = {
                ...this.newSpecialite,
                date_arrete: this.formatDate(this.newSpecialite.date_arrete),
                date_journal_officiel: this.formatDate(
                    this.newSpecialite.date_journal_officiel
                ),
                date_creation_specialité: this.formatDate(
                    this.newSpecialite.date_creation_specialité
                ),
                date_annulation_specialité: this.formatDate(
                    this.newSpecialite.date_annulation_specialité
                ),
            };

            try {
                const response = await axios.post('/api/specialites', payload);
                this.$emit('save', response.data);
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'La spécialité a été ajoutée avec succès.',
                    life: 3000,
                });
                this.close();
            } catch (error) {
                console.error(
                    "Erreur lors de l'ajout de la spécialité:",
                    error
                );
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: "Une erreur s'est produite lors de l'ajout de la spécialité.",
                    life: 3000,
                });
            }
        },
        async updateSpecialite() {
            if (!this.validateForm()) {
                return;
            }

            const payload = {
                ...this.newSpecialite,
                date_arrete: this.formatDate(this.newSpecialite.date_arrete),
                date_journal_officiel: this.formatDate(
                    this.newSpecialite.date_journal_officiel
                ),
                date_creation_specialité: this.formatDate(
                    this.newSpecialite.date_creation_specialité
                ),
                date_annulation_specialité: this.formatDate(
                    this.newSpecialite.date_annulation_specialité
                ),
            };

            try {
                const response = await axios.put(
                    `/api/specialites/${this.newSpecialite.id}`,
                    payload
                );
                this.$emit('update', response.data);
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'La spécialité a été modifiée avec succès.',
                    life: 3000,
                });
                this.close();
            } catch (error) {
                console.error(
                    'Erreur lors de la modification de la spécialité:',
                    error
                );
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: "Une erreur s'est produite lors de la modification de la spécialité.",
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
