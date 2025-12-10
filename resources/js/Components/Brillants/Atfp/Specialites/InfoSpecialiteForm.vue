<template>
    <Dialog
        :visible="visible"
        @update:visible="$emit('update:visible', $event)"
        :modal="true"
        :focusOnShow="false"
        :style="{ width: '50vw', maxWidth: '800px' }"
        :breakpoints="{ '960px': '95vw', '640px': '100vw' }"
        class="p-fluid"
        :pt="{
            root: 'border-round-xl shadow-5',
            mask: { style: 'backdrop-filter: blur(4px)' },
            header: { class: 'surface-50 border-bottom-1 surface-border p-4' },
            content: { class: 'surface-ground p-4' },
            footer: { class: 'surface-50 border-top-1 surface-border p-4' },
        }"
    >
        <!-- Header -->
        <template #header>
            <div class="flex align-items-center gap-2">
                <i class="pi pi-info-circle text-primary text-2xl"></i>
                <span class="font-bold text-2xl">{{
                    isEditMode
                        ? 'Modifier les Informations de Spécialité'
                        : 'Ajouter des Informations de Spécialité'
                }}</span>
            </div>
        </template>

        <!-- Loading State -->
        <div
            v-if="loading"
            class="flex flex-column align-items-center justify-content-center gap-3 p-6"
        >
            <ProgressSpinner
                style="width: 50px; height: 50px"
                strokeWidth="4"
            />
            <span class="text-color-secondary">Chargement des données...</span>
        </div>

        <!-- Form -->
        <div v-else class="surface-card p-4 shadow-2 border-round">
            <form @submit.prevent="submitForm">
                <TabView ref="tabview" v-model:activeIndex="activeTabIndex">
                    <!-- Informations Générales -->
                    <TabPanel header="Informations Générales">
                        <div class="grid p-fluid">
                            <!-- Code Spécialité (Verrouillé) -->
                            <div class="col-12 md:col-6 field">
                                <label for="code" class="block font-medium mb-2"
                                    >Code Spécialité
                                    <span class="text-red-500">*</span></label
                                >
                                <InputText
                                    id="code"
                                    :value="specialite?.code || ''"
                                    disabled
                                    :class="{
                                        'p-invalid': errors.specialite_id,
                                    }"
                                />
                                <small
                                    v-if="errors.specialite_id"
                                    class="p-error"
                                    >{{ errors.specialite_id[0] }}</small
                                >
                            </div>
                            <!-- Nom Français (Verrouillé) -->
                            <div class="col-12 md:col-6 field">
                                <label
                                    for="nom_fr"
                                    class="block font-medium mb-2"
                                    >Nom (Français)
                                    <span class="text-red-500">*</span></label
                                >
                                <InputText
                                    id="nom_fr"
                                    :value="specialite?.nom_fr || ''"
                                    disabled
                                    :class="{
                                        'p-invalid': errors.specialite_id,
                                    }"
                                />
                                <small
                                    v-if="errors.specialite_id"
                                    class="p-error"
                                    >{{ errors.specialite_id[0] }}</small
                                >
                            </div>
                            <!-- Nom Arabe (Verrouillé) -->
                            <div class="col-12 md:col-6 field">
                                <label
                                    for="nom_ar"
                                    class="block font-medium mb-2"
                                    >Nom (Arabe)
                                    <span class="text-red-500">*</span></label
                                >
                                <InputText
                                    id="nom_ar"
                                    :value="specialite?.nom_ar || ''"
                                    disabled
                                    dir="rtl"
                                    class="font-arabic"
                                    :class="{
                                        'p-invalid': errors.specialite_id,
                                    }"
                                />
                                <small
                                    v-if="errors.specialite_id"
                                    class="p-error"
                                    >{{ errors.specialite_id[0] }}</small
                                >
                            </div>
                            <!-- Diplôme (Verrouillé) -->
                            <div class="col-12 md:col-6 field">
                                <label
                                    for="diplome_fr"
                                    class="block font-medium mb-2"
                                    >Diplôme</label
                                >
                                <InputText
                                    id="diplome_fr"
                                    :value="specialite?.diplome_fr || ''"
                                    disabled
                                />
                            </div>
                            <!-- Spécialité (Caché mais nécessaire pour la soumission) -->
                            <input type="hidden" v-model="form.specialite_id" />
                            <!-- Année de Formation -->
                            <div class="col-12 md:col-6 field">
                                <label
                                    for="annee_formation_id"
                                    class="block font-medium mb-2"
                                    >Année de Formation
                                    <span class="text-red-500">*</span></label
                                >
                                <Dropdown
                                    id="annee_formation_id"
                                    v-model="form.annee_formation_id"
                                    :options="annees"
                                    optionLabel="intitule"
                                    optionValue="id"
                                    placeholder="Sélectionner une année"
                                    :class="{
                                        'p-invalid': errors.annee_formation_id,
                                    }"
                                    :loading="loadingLists"
                                />
                                <small
                                    v-if="errors.annee_formation_id"
                                    class="p-error"
                                    >{{ errors.annee_formation_id[0] }}</small
                                >
                            </div>
                            <!-- Homologation Français -->
                            <div class="col-12 md:col-6 field">
                                <label
                                    for="homologation_fr"
                                    class="block font-medium mb-2"
                                    >Homologation (Français)</label
                                >
                                <Dropdown
                                    id="homologation_fr"
                                    v-model="form.homologation_fr"
                                    :options="homologationOptions"
                                    placeholder="Sélectionner une homologation"
                                    :class="{
                                        'p-invalid': errors.homologation_fr,
                                    }"
                                />
                                <small
                                    v-if="errors.homologation_fr"
                                    class="p-error"
                                    >{{ errors.homologation_fr[0] }}</small
                                >
                            </div>
                            <!-- Statut -->
                            <div class="col-12 md:col-6 field">
                                <label
                                    for="statut"
                                    class="block font-medium mb-2"
                                    >Statut</label
                                >
                                <Dropdown
                                    id="statut"
                                    v-model="form.statut"
                                    :options="statuts"
                                    placeholder="Sélectionner un statut"
                                    :class="{ 'p-invalid': errors.statut }"
                                />
                                <small v-if="errors.statut" class="p-error">{{
                                    errors.statut[0]
                                }}</small>
                            </div>
                        </div>
                    </TabPanel>

                    <!-- Détails de Formation -->
                    <TabPanel header="Détails de Formation">
                        <div class="grid p-fluid">
                            <!-- Heures Enseignement Théorique -->
                            <div class="col-12 md:col-4 field">
                                <label
                                    for="heures_et"
                                    class="block font-medium mb-2"
                                    >Heures Enseignement Théorique</label
                                >
                                <InputNumber
                                    id="heures_et"
                                    v-model="form.heures_et"
                                    :min="0"
                                    :useGrouping="false"
                                    placeholder="Entrez les heures théoriques"
                                    :class="{ 'p-invalid': errors.heures_et }"
                                />
                                <small
                                    v-if="errors.heures_et"
                                    class="p-error"
                                    >{{ errors.heures_et[0] }}</small
                                >
                            </div>
                            <!-- Heures Enseignement Général -->
                            <div class="col-12 md:col-4 field">
                                <label
                                    for="heures_eg"
                                    class="block font-medium mb-2"
                                    >Heures Enseignement Général</label
                                >
                                <InputNumber
                                    id="heures_eg"
                                    v-model="form.heures_eg"
                                    :min="0"
                                    :useGrouping="false"
                                    placeholder="Entrez les heures générales"
                                    :class="{ 'p-invalid': errors.heures_eg }"
                                />
                                <small
                                    v-if="errors.heures_eg"
                                    class="p-error"
                                    >{{ errors.heures_eg[0] }}</small
                                >
                            </div>
                            <!-- Durée de Formation -->
                            <div class="col-12 md:col-4 field">
                                <label
                                    for="duree_formation"
                                    class="block font-medium mb-2"
                                    >Durée de Formation (années)</label
                                >
                                <InputNumber
                                    id="duree_formation"
                                    v-model="form.duree_formation"
                                    :min="0"
                                    :max="9.9"
                                    :step="0.1"
                                    :maxFractionDigits="1"
                                    placeholder="Ex: 2.5"
                                    :class="{
                                        'p-invalid': errors.duree_formation,
                                    }"
                                />
                                <small
                                    v-if="errors.duree_formation"
                                    class="p-error"
                                    >{{ errors.duree_formation[0] }}</small
                                >
                            </div>
                        </div>
                    </TabPanel>

                    <!-- Observations -->
                    <TabPanel header="Observations">
                        <div class="grid p-fluid">
                            <!-- Observation -->
                            <div class="col-12 field">
                                <label
                                    for="observation"
                                    class="block font-medium mb-2 text-right font-arabic"
                                    >الملاحظات</label
                                >
                                <Textarea
                                    id="observation"
                                    v-model="form.observation"
                                    rows="4"
                                    dir="rtl"
                                    :class="{ 'p-invalid': errors.observation }"
                                    placeholder="أدخل الملاحظات"
                                />
                                <small
                                    v-if="errors.observation"
                                    class="p-error"
                                    >{{ errors.observation[0] }}</small
                                >
                            </div>
                        </div>
                    </TabPanel>
                </TabView>
            </form>
        </div>

        <!-- Footer -->
        <template #footer>
            <div class="flex justify-content-end gap-2">
                <Button
                    label="Annuler"
                    icon="pi pi-times"
                    class="p-button-outlined p-button-secondary"
                    @click="cancelForm"
                />
                <Button
                    label="Enregistrer"
                    icon="pi pi-check"
                    class="p-button-primary"
                    @click="submitForm"
                    :loading="submitting"
                />
            </div>
        </template>
    </Dialog>
</template>

<script>
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import InputNumber from 'primevue/inputnumber';
import InputText from 'primevue/inputtext';
import ProgressSpinner from 'primevue/progressspinner';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import Textarea from 'primevue/textarea';
import { useToast } from 'primevue/usetoast';

export default {
    name: 'InfosSpecialitesForm',
    components: {
        Button,
        Dialog,
        Dropdown,
        InputNumber,
        InputText,
        ProgressSpinner,
        TabView,
        TabPanel,
        Textarea,
    },
    props: {
        visible: { type: Boolean, required: true },
        specialite: { type: Object, default: null },
        anneeId: { type: [Number, String], default: null },
    },
    emits: ['update:visible', 'save', 'cancel'],
    setup() {
        const toast = useToast();
        const form = ref({
            specialite_id: null,
            annee_formation_id: null,
            homologation_fr: null,
            homologation_ar: null,
            heures_et: null,
            heures_eg: null,
            duree_formation: null,
            statut: null,
            observation: null,
        });
        const errors = ref({});
        const loading = ref(false);
        const loadingLists = ref(false);
        const submitting = ref(false);
        const tabview = ref(null);
        const activeTabIndex = ref(0);

        return {
            toast,
            form,
            errors,
            loading,
            loadingLists,
            submitting,
            tabview,
            activeTabIndex,
        };
    },
    data() {
        return {
            specialites: [],
            annees: [],
            homologationOptions: ['Homologuée', 'Non Homologuée'],
            statuts: ['Active', 'Non Active', 'Annulée', 'Remplacée'],
            homologationMapping: {
                Homologuée: 'منظر',
                'Non Homologuée': 'غير منظر',
            },
        };
    },
    computed: {
        isEditMode() {
            return (
                !!this.specialite &&
                !!this.specialite.infos_specialites?.find(
                    (info) => info.annee_formation_id === this.anneeId
                )
            );
        },
    },
    watch: {
        visible(newValue) {
            if (newValue && !this.isEditMode) {
                this.resetForm();
            }
        },
        'form.homologation_fr'(newValue) {
            this.form.homologation_ar =
                this.homologationMapping[newValue] || null;
        },
        specialite: {
            handler(newSpecialite) {
                if (newSpecialite) {
                    const info =
                        newSpecialite.infos_specialites?.find(
                            (info) => info.annee_formation_id === this.anneeId
                        ) || {};
                    this.form = {
                        specialite_id: newSpecialite.id || null,
                        annee_formation_id: this.anneeId,
                        homologation_fr: info.homologation_fr || null,
                        homologation_ar: info.homologation_ar || null,
                        heures_et:
                            info.heures_et !== null &&
                            info.heures_et !== undefined
                                ? parseInt(info.heures_et)
                                : null,
                        heures_eg:
                            info.heures_eg !== null &&
                            info.heures_eg !== undefined
                                ? parseInt(info.heures_eg)
                                : null,
                        duree_formation:
                            info.duree_formation !== null &&
                            info.duree_formation !== undefined
                                ? parseFloat(info.duree_formation)
                                : null,
                        statut: info.statut || null,
                        observation: info.observation || null,
                    };
                }
            },
            immediate: true,
        },
        anneeId(newValue) {
            this.form.annee_formation_id = newValue;
        },
    },
    created() {
        this.fetchOptions();
    },
    methods: {
        async fetchOptions() {
            this.loadingLists = true;
            try {
                const [specialitesRes, anneesRes] = await Promise.all([
                    axios.get('/api/specialites'),
                    axios.get('/api/annees-formation'),
                ]);
                this.specialites =
                    specialitesRes.data.data || specialitesRes.data;
                this.annees = anneesRes.data;
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        'Impossible de charger les options.',
                    life: 3000,
                });
            } finally {
                this.loadingLists = false;
            }
        },
        resetForm() {
            this.form = {
                specialite_id: this.specialite?.id || null,
                annee_formation_id: this.anneeId,
                homologation_fr: null,
                homologation_ar: null,
                heures_et: null,
                heures_eg: null,
                duree_formation: null,
                statut: null,
                observation: null,
            };
            this.errors = {};
            this.activeTabIndex = 0;
        },
        validateForm() {
            this.errors = {};
            let isValid = true;

            if (!this.form.specialite_id) {
                this.errors.specialite_id = [
                    'Veuillez sélectionner une spécialité.',
                ];
                isValid = false;
            }
            if (!this.form.annee_formation_id) {
                this.errors.annee_formation_id = [
                    'Veuillez sélectionner une année de formation.',
                ];
                isValid = false;
            }
            if (
                this.form.homologation_fr &&
                !['Homologuée', 'Non Homologuée'].includes(
                    this.form.homologation_fr
                )
            ) {
                this.errors.homologation_fr = ['Homologation invalide.'];
                isValid = false;
            }
            if (
                this.form.heures_et !== null &&
                (isNaN(this.form.heures_et) || this.form.heures_et < 0)
            ) {
                this.errors.heures_et = [
                    'Les heures doivent être positives ou nulles.',
                ];
                isValid = false;
            }
            if (
                this.form.heures_eg !== null &&
                (isNaN(this.form.heures_eg) || this.form.heures_eg < 0)
            ) {
                this.errors.heures_eg = [
                    'Les heures doivent être positives ou nulles.',
                ];
                isValid = false;
            }
            if (
                this.form.duree_formation !== null &&
                (isNaN(this.form.duree_formation) ||
                    this.form.duree_formation < 0 ||
                    this.form.duree_formation > 9.9)
            ) {
                this.errors.duree_formation = [
                    'La durée doit être entre 0 et 9.9.',
                ];
                isValid = false;
            }
            if (
                this.form.statut &&
                !['Active', 'Non Active', 'Annulée', 'Remplacée'].includes(
                    this.form.statut
                )
            ) {
                this.errors.statut = ['Statut invalide.'];
                isValid = false;
            }
            if (this.form.observation && this.form.observation.length > 65535) {
                this.errors.observation = ["L'observation est trop longue."];
                isValid = false;
            }

            return isValid;
        },
        async submitForm() {
            this.submitting = true;
            this.errors = {};

            if (!this.validateForm()) {
                const errorFields = Object.keys(this.errors);
                if (
                    errorFields.some((field) =>
                        [
                            'specialite_id',
                            'annee_formation_id',
                            'homologation_fr',
                            'statut',
                        ].includes(field)
                    )
                ) {
                    this.activeTabIndex = 0; // Informations Générales
                } else if (
                    errorFields.some((field) =>
                        ['heures_et', 'heures_eg', 'duree_formation'].includes(
                            field
                        )
                    )
                ) {
                    this.activeTabIndex = 1; // Détails de Formation
                } else if (errorFields.includes('observation')) {
                    this.activeTabIndex = 2; // Observations
                }

                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur de validation',
                    detail: 'Veuillez vérifier les champs du formulaire.',
                    life: 3000,
                });
                this.submitting = false;
                return;
            }

            try {
                // Valider et formater duree_formation
                let dureeFormation = this.form.duree_formation;
                if (dureeFormation !== null && dureeFormation !== undefined) {
                    dureeFormation = parseFloat(dureeFormation);
                    if (!isNaN(dureeFormation)) {
                        dureeFormation = parseFloat(dureeFormation.toFixed(1));
                    } else {
                        dureeFormation = null;
                    }
                } else {
                    dureeFormation = null;
                }

                const data = {
                    specialite_id: this.form.specialite_id,
                    annee_formation_id: this.form.annee_formation_id,
                    homologation_fr: this.form.homologation_fr,
                    homologation_ar: this.form.homologation_ar,
                    heures_et:
                        this.form.heures_et !== null
                            ? parseInt(this.form.heures_et)
                            : null,
                    heures_eg:
                        this.form.heures_eg !== null
                            ? parseInt(this.form.heures_eg)
                            : null,
                    duree_formation: dureeFormation,
                    statut: this.form.statut,
                    observation: this.form.observation,
                };

                let response;
                const existingInfo = this.specialite?.infos_specialites?.find(
                    (info) => info.annee_formation_id === this.anneeId
                );
                if (this.isEditMode && existingInfo) {
                    response = await axios.put(
                        `/api/infos-specialites/${existingInfo.id}`,
                        data
                    );
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Informations de spécialité mises à jour avec succès.',
                        life: 3000,
                    });
                } else {
                    response = await axios.post('/api/infos-specialites', data);
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Informations de spécialité ajoutées avec succès.',
                        life: 3000,
                    });
                }

                this.$emit('save', response.data);
                this.cancelForm();
            } catch (error) {
                if (error.response?.status === 422) {
                    this.errors = error.response.data.errors;
                    const errorFields = Object.keys(this.errors);
                    if (
                        errorFields.some((field) =>
                            [
                                'specialite_id',
                                'annee_formation_id',
                                'homologation_fr',
                                'statut',
                            ].includes(field)
                        )
                    ) {
                        this.activeTabIndex = 0;
                    } else if (
                        errorFields.some((field) =>
                            [
                                'heures_et',
                                'heures_eg',
                                'duree_formation',
                            ].includes(field)
                        )
                    ) {
                        this.activeTabIndex = 1;
                    } else if (errorFields.includes('observation')) {
                        this.activeTabIndex = 2;
                    }
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur de validation',
                        detail: 'Veuillez vérifier les champs du formulaire.',
                        life: 3000,
                    });
                } else {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail:
                            error.response?.data?.message ||
                            "Erreur lors de l'enregistrement.",
                        life: 3000,
                    });
                }
            } finally {
                this.submitting = false;
            }
        },
        cancelForm() {
            this.resetForm();
            this.$emit('cancel');
            this.$emit('update:visible', false);
        },
    },
};
</script>

<style scoped>
@font-face {
    font-family: 'Montserrat-Arabic-Regular';
    src: url('/fonts/Montserrat-Arabic-Regular.otf') format('opentype');
    font-weight: normal;
    font-style: normal;
}

.field {
    margin-bottom: 1.25rem;
}

label.font-medium {
    color: #6c757d;
}

.font-arabic,
:deep(.p-inputtext[dir='rtl']),
:deep(.p-textarea[dir='rtl']) {
    font-family: 'Montserrat-Arabic-Regular', sans-serif;
}

:deep(.p-button) {
    max-width: 200px;
    padding: 0.8rem 1rem;
    font-size: 0.875rem;
    border-radius: 0.25rem;
}

:deep(.p-tabview .p-tabview-nav) {
    background: var(--surface-ground);
    border-bottom: 1px solid var(--surface-border);
}

:deep(.p-tabview .p-tabview-nav li .p-tabview-nav-link) {
    background: transparent;
    border-color: transparent;
    color: var(--text-color-secondary);
    font-weight: 500;
    padding: 1rem 1.25rem;
    transition: all 0.2s ease;
}

:deep(.p-tabview .p-tabview-nav li .p-tabview-nav-link:not(.p-disabled):focus) {
    box-shadow: none;
}

:deep(.p-tabview .p-tabview-nav li.p-highlight .p-tabview-nav-link) {
    color: var(--primary-color);
    border-bottom: 2px solid var(--primary-color);
}

:deep(.p-tabview .p-tabview-panels) {
    background: transparent;
    padding: 0;
}

:deep(.p-inputtext),
:deep(.p-dropdown),
:deep(.p-textarea) {
    border-radius: 0.25rem;
}
</style>
