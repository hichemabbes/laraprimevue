```vue
<template>
    <Dialog
        :visible="visible"
        @update:visible="$emit('update:visible', $event)"
        :modal="true"
        :focusOnShow="false"
        :style="{ width: '80vw', maxWidth: '1200px' }"
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
                <i
                    :class="
                        isEditMode
                            ? 'pi pi-pencil text-primary text-2xl'
                            : 'pi pi-plus text-primary text-2xl'
                    "
                ></i>
                <span class="font-bold text-2xl">{{
                    isEditMode
                        ? 'Modifier une Spécialité'
                        : 'Ajouter une Spécialité'
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
                            <!-- Code -->
                            <div class="col-12 md:col-6 field">
                                <label for="code" class="block font-medium mb-2"
                                    >Code
                                    <span class="text-red-500">*</span></label
                                >
                                <InputText
                                    id="code"
                                    v-model.trim="form.code"
                                    :disabled="isEditMode"
                                    :class="{ 'p-invalid': errors.code }"
                                    placeholder="Entrez le code"
                                />
                                <small v-if="errors.code" class="p-error">{{
                                    errors.code[0]
                                }}</small>
                            </div>
                            <!-- Type -->
                            <div class="col-12 md:col-6 field">
                                <label
                                    for="standardisation_ar"
                                    class="block font-medium mb-2"
                                    >Type
                                    <span class="text-red-500">*</span></label
                                >
                                <Dropdown
                                    id="standardisation_ar"
                                    v-model="form.standardisation_ar"
                                    :options="
                                        standardisations.map((s) => s.nom_ar)
                                    "
                                    placeholder="Sélectionner un type"
                                    :class="{
                                        'p-invalid': errors.standardisation_ar,
                                    }"
                                    :loading="loadingLists"
                                />
                                <small
                                    v-if="errors.standardisation_ar"
                                    class="p-error"
                                    >{{ errors.standardisation_ar[0] }}</small
                                >
                            </div>
                            <!-- Secteur -->
                            <div
                                class="col-12 md:col-6 field"
                                v-if="form.standardisation_ar !== 'جديد'"
                            >
                                <label
                                    for="secteur_id"
                                    class="block font-medium mb-2"
                                    >Secteur
                                    <span class="text-red-500">*</span></label
                                >
                                <Dropdown
                                    id="secteur_id"
                                    v-model="form.secteur_id"
                                    :options="secteurs"
                                    optionLabel="nom_fr"
                                    optionValue="id"
                                    placeholder="Sélectionner un secteur"
                                    :class="{ 'p-invalid': errors.secteur_id }"
                                    :loading="loadingLists"
                                />
                                <small
                                    v-if="errors.secteur_id"
                                    class="p-error"
                                    >{{ errors.secteur_id[0] }}</small
                                >
                            </div>
                            <!-- Sous Secteur -->
                            <div
                                class="col-12 md:col-6 field"
                                v-if="form.standardisation_ar === 'مقيس'"
                            >
                                <label
                                    for="sous_secteur_id"
                                    class="block font-medium mb-2"
                                    >Sous Secteur
                                    <span class="text-red-500">*</span></label
                                >
                                <Dropdown
                                    id="sous_secteur_id"
                                    v-model="form.sous_secteur_id"
                                    :options="filteredSousSecteurs"
                                    optionLabel="nom_fr"
                                    optionValue="id"
                                    placeholder="Sélectionner un sous-secteur"
                                    :class="{
                                        'p-invalid': errors.sous_secteur_id,
                                    }"
                                    :loading="loadingLists"
                                />
                                <small
                                    v-if="errors.sous_secteur_id"
                                    class="p-error"
                                    >{{ errors.sous_secteur_id[0] }}</small
                                >
                            </div>
                            <!-- Nom Arabe -->
                            <div class="col-12 md:col-6 field">
                                <label
                                    for="nom_ar"
                                    class="block font-medium mb-2 text-right font-arabic"
                                    >الاسم (العربية)
                                    <span class="text-red-500">*</span></label
                                >
                                <InputText
                                    id="nom_ar"
                                    v-model="form.nom_ar"
                                    dir="rtl"
                                    :class="{ 'p-invalid': errors.nom_ar }"
                                    placeholder="أدخل الاسم بالعربية"
                                />
                                <small v-if="errors.nom_ar" class="p-error">{{
                                    errors.nom_ar[0]
                                }}</small>
                            </div>
                            <!-- Nom Français -->
                            <div class="col-12 md:col-6 field">
                                <label
                                    for="nom_fr"
                                    class="block font-medium mb-2"
                                    >Nom (Français)</label
                                >
                                <InputText
                                    id="nom_fr"
                                    v-model="form.nom_fr"
                                    :class="{ 'p-invalid': errors.nom_fr }"
                                    placeholder="Entrez le nom en français"
                                />
                                <small v-if="errors.nom_fr" class="p-error">{{
                                    errors.nom_fr[0]
                                }}</small>
                            </div>
                            <!-- Diplôme -->
                            <div class="col-12 md:col-6 field">
                                <label
                                    for="diplome_fr"
                                    class="block font-medium mb-2"
                                    >Diplôme
                                    <span class="text-red-500">*</span></label
                                >
                                <Dropdown
                                    id="diplome_fr"
                                    v-model="form.diplome_fr"
                                    :options="diplomes.map((d) => d.nom_fr)"
                                    placeholder="Sélectionner un diplôme"
                                    :class="{ 'p-invalid': errors.diplome_fr }"
                                    :loading="loadingLists"
                                    @change="updateDiplomeAr"
                                />
                                <small
                                    v-if="errors.diplome_fr"
                                    class="p-error"
                                    >{{ errors.diplome_fr[0] }}</small
                                >
                            </div>
                            <!-- Statut -->
                            <div class="col-12 md:col-6 field">
                                <label
                                    for="statut"
                                    class="block font-medium mb-2"
                                    >Statut
                                    <span class="text-red-500">*</span></label
                                >
                                <Dropdown
                                    id="statut"
                                    v-model="form.statut"
                                    :options="[
                                        'Active',
                                        'Annulée',
                                        'Remplacée',
                                    ]"
                                    placeholder="Sélectionner un statut"
                                    :class="{ 'p-invalid': errors.statut }"
                                    :loading="loadingLists"
                                />
                                <small v-if="errors.statut" class="p-error">{{
                                    errors.statut[0]
                                }}</small>
                            </div>
                        </div>
                    </TabPanel>
                    <!-- Dates et Observations -->
                    <TabPanel header="Dates et Observations">
                        <div class="grid p-fluid">
                            <!-- Date de Création -->
                            <div class="col-12 md:col-6 field">
                                <label
                                    for="date_creation"
                                    class="block font-medium mb-2"
                                    >Date de Création</label
                                >
                                <Calendar
                                    id="date_creation"
                                    v-model="form.date_creation"
                                    :class="{
                                        'p-invalid': errors.date_creation,
                                    }"
                                    dateFormat="yy-mm-dd"
                                    showIcon
                                />
                                <small
                                    v-if="errors.date_creation"
                                    class="p-error"
                                    >{{ errors.date_creation[0] }}</small
                                >
                            </div>
                            <!-- Date d'Annulation -->
                            <div class="col-12 md:col-6 field">
                                <label
                                    for="date_annulation"
                                    class="block font-medium mb-2"
                                    >Date d'Annulation</label
                                >
                                <Calendar
                                    id="date_annulation"
                                    v-model="form.date_annulation"
                                    :class="{
                                        'p-invalid': errors.date_annulation,
                                    }"
                                    dateFormat="yy-mm-dd"
                                    showIcon
                                />
                                <small
                                    v-if="errors.date_annulation"
                                    class="p-error"
                                    >{{ errors.date_annulation[0] }}</small
                                >
                            </div>
                            <!-- Observation -->
                            <div class="col-12 field">
                                <label
                                    for="observation"
                                    class="block font-medium mb-2"
                                    >Observation</label
                                >
                                <Textarea
                                    id="observation"
                                    v-model="form.observation"
                                    rows="4"
                                    :class="{ 'p-invalid': errors.observation }"
                                    placeholder="Entrez l'observation"
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
                    :label="isEditMode ? 'Modifier' : 'Enregistrer'"
                    icon="pi pi-check"
                    class="p-button-primary"
                    @click="submitForm"
                    :loading="submitting"
                    :disabled="submitting"
                />
            </div>
        </template>
    </Dialog>

    <Toast position="top-right" />
</template>

<script>
import { ref, computed, watch } from 'vue';
import axios from '@/axios';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Calendar from 'primevue/calendar';
import Button from 'primevue/button';
import Toast from 'primevue/toast';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import Textarea from 'primevue/textarea';
import ProgressSpinner from 'primevue/progressspinner';
import { useToast } from 'primevue/usetoast';

export default {
    components: {
        Dialog,
        InputText,
        Dropdown,
        Calendar,
        Button,
        Toast,
        TabView,
        TabPanel,
        Textarea,
        ProgressSpinner,
    },
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
    setup(props, { emit }) {
        const toast = useToast();
        const form = ref({
            id: null,
            code: '',
            secteur_id: null,
            sous_secteur_id: null,
            nom_ar: '',
            nom_fr: '',
            standardisation_ar: '',
            standardisation_fr: '',
            diplome_fr: null,
            diplome_ar: null,
            date_creation: null,
            statut: 'Active',
            observation: '',
            date_annulation: null,
        });
        const errors = ref({});
        const loading = ref(false);
        const loadingLists = ref(false);
        const submitting = ref(false);
        const tabview = ref(null);
        const activeTabIndex = ref(0);
        const secteurs = ref([]);
        const sousSecteurs = ref([]);
        const standardisations = ref([]);
        const diplomes = ref([]);

        const isEditMode = computed(() => !!props.specialiteToEdit);

        const filteredSousSecteurs = computed(() => {
            if (!form.value.secteur_id) return [];
            return sousSecteurs.value.filter(
                (sousSecteur) =>
                    sousSecteur.secteur_id === form.value.secteur_id
            );
        });

        watch(
            () => props.visible,
            (newValue) => {
                if (newValue && !isEditMode.value) {
                    resetForm();
                }
            }
        );

        watch(
            () => props.specialiteToEdit,
            (newVal) => {
                if (newVal) {
                    form.value = {
                        id: newVal.id || null,
                        code: newVal.code || '',
                        secteur_id: newVal.secteur_id || null,
                        sous_secteur_id: newVal.sous_secteur_id || null,
                        nom_ar: newVal.nom_ar || '',
                        nom_fr: newVal.nom_fr || '',
                        standardisation_ar: newVal.standardisation_ar || '',
                        standardisation_fr: newVal.standardisation_fr || '',
                        diplome_fr: newVal.diplome_fr || null,
                        diplome_ar: newVal.diplome_ar || null,
                        date_creation: newVal.date_creation
                            ? new Date(newVal.date_creation)
                            : null,
                        statut: newVal.statut || 'Active',
                        observation: newVal.observation || '',
                        date_annulation: newVal.date_annulation
                            ? new Date(newVal.date_annulation)
                            : null,
                    };
                    if (
                        !secteurs.value.length ||
                        !sousSecteurs.value.length ||
                        !standardisations.value.length ||
                        !diplomes.value.length
                    ) {
                        fetchLists();
                    }
                }
            },
            { immediate: true }
        );

        watch(
            () => form.value.standardisation_ar,
            (newVal) => {
                const standardisation = standardisations.value.find(
                    (s) => s.nom_ar === newVal
                );
                form.value.standardisation_fr = standardisation
                    ? standardisation.nom_fr
                    : '';
                if (newVal === 'جديد') {
                    form.value.secteur_id = null;
                    form.value.sous_secteur_id = null;
                } else if (newVal !== 'مقيس') {
                    form.value.sous_secteur_id = null;
                }
            }
        );

        async function fetchLists() {
            loadingLists.value = true;
            try {
                const [secteursResponse, sousSecteursResponse, listesResponse] =
                    await Promise.all([
                        axios.get('/api/secteurs'),
                        axios.get('/api/sous-secteurs'),
                        axios.get('/api/listes'),
                    ]);
                secteurs.value = secteursResponse.data || [];
                sousSecteurs.value = sousSecteursResponse.data || [];
                const listes = listesResponse.data || [];
                standardisations.value =
                    listes.find((l) => l.nom_fr === 'Standardisation')
                        ?.options || [];
                diplomes.value =
                    listes.find((l) => l.nom_fr === 'Diplomes')?.options || [];
            } catch (error) {
                toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        'Impossible de charger les listes.',
                    life: 3000,
                });
            } finally {
                loadingLists.value = false;
            }
        }

        async function fetchSpecialiteData(id) {
            loading.value = true;
            try {
                const response = await axios.get(`/api/specialites/${id}`);
                Object.assign(form.value, {
                    id: response.data.id || null,
                    code: response.data.code || '',
                    secteur_id: response.data.secteur_id || null,
                    sous_secteur_id: response.data.sous_secteur_id || null,
                    nom_ar: response.data.nom_ar || '',
                    nom_fr: response.data.nom_fr || '',
                    standardisation_ar: response.data.standardisation_ar || '',
                    standardisation_fr: response.data.standardisation_fr || '',
                    diplome_fr: response.data.diplome_fr || null,
                    diplome_ar: response.data.diplome_ar || null,
                    date_creation: response.data.date_creation
                        ? new Date(response.data.date_creation)
                        : null,
                    statut: response.data.statut || 'Active',
                    observation: response.data.observation || '',
                    date_annulation: response.data.date_annulation
                        ? new Date(response.data.date_annulation)
                        : null,
                });
                if (
                    !secteurs.value.length ||
                    !sousSecteurs.value.length ||
                    !standardisations.value.length ||
                    !diplomes.value.length
                ) {
                    await fetchLists();
                }
            } catch (error) {
                toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        'Impossible de charger les données de la spécialité.',
                    life: 3000,
                });
            } finally {
                loading.value = false;
            }
        }

        if (isEditMode.value && props.specialiteToEdit?.id) {
            fetchSpecialiteData(props.specialiteToEdit.id);
        } else {
            fetchLists();
        }

        function resetForm() {
            form.value = {
                id: null,
                code: '',
                secteur_id: null,
                sous_secteur_id: null,
                nom_ar: '',
                nom_fr: '',
                standardisation_ar: '',
                standardisation_fr: '',
                diplome_fr: null,
                diplome_ar: null,
                date_creation: null,
                statut: 'Active',
                observation: '',
                date_annulation: null,
            };
            errors.value = {};
            activeTabIndex.value = 0;
            submitting.value = false;
        }

        return {
            toast,
            form,
            errors,
            loading,
            loadingLists,
            submitting,
            tabview,
            activeTabIndex,
            secteurs,
            sousSecteurs,
            standardisations,
            diplomes,
            isEditMode,
            resetForm,
            filteredSousSecteurs,
        };
    },
    methods: {
        async submitForm() {
            this.submitting = true;
            this.errors = {};

            // Validation côté client
            const requiredFields = {
                code: 'Le code est requis.',
                standardisation_ar: 'Le type est requis.',
                nom_ar: 'Le nom en arabe est requis.',
                diplome_fr: 'Le diplôme est requis.',
                statut: 'Le statut est requis.',
            };

            if (this.form.standardisation_ar !== 'جديد') {
                requiredFields.secteur_id = 'Le secteur est requis.';
            }
            if (this.form.standardisation_ar === 'مقيس') {
                requiredFields.sous_secteur_id = 'Le sous-secteur est requis.';
            }

            for (const [field, message] of Object.entries(requiredFields)) {
                if (
                    !this.form[field] ||
                    (typeof this.form[field] === 'string' &&
                        this.form[field].trim() === '')
                ) {
                    this.errors[field] = [message];
                }
            }

            if (this.form.code && this.form.code.length > 20) {
                this.errors.code = [
                    'Le code ne doit pas dépasser 20 caractères.',
                ];
            }
            if (this.form.nom_ar && this.form.nom_ar.length > 255) {
                this.errors.nom_ar = [
                    'Le nom en arabe ne doit pas dépasser 255 caractères.',
                ];
            }
            if (this.form.nom_fr && this.form.nom_fr.length > 255) {
                this.errors.nom_fr = [
                    'Le nom en français ne doit pas dépasser 255 caractères.',
                ];
            }
            if (this.form.observation && this.form.observation.length > 1000) {
                this.errors.observation = [
                    "L'observation ne doit pas dépasser 1000 caractères.",
                ];
            }

            if (Object.keys(this.errors).length > 0) {
                this.activeTabIndex = 0;
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur de validation',
                    detail: 'Veuillez vérifier les champs du formulaire.',
                    life: 3000,
                });
                this.submitting = false;
                return;
            }

            // Préparer les données
            const data = {
                code: this.form.code,
                secteur_id: this.form.secteur_id,
                sous_secteur_id: this.form.sous_secteur_id,
                nom_ar: this.form.nom_ar,
                nom_fr: this.form.nom_fr,
                standardisation_ar: this.form.standardisation_ar,
                standardisation_fr: this.form.standardisation_fr,
                diplome_fr: this.form.diplome_fr,
                diplome_ar: this.form.diplome_ar,
                statut: this.form.statut,
                observation: this.form.observation,
                date_creation: this.form.date_creation
                    ? this.form.date_creation.toISOString().split('T')[0]
                    : null,
                date_annulation: this.form.date_annulation
                    ? this.form.date_annulation.toISOString().split('T')[0]
                    : null,
            };

            try {
                let response;
                if (this.isEditMode) {
                    response = await axios.put(
                        `/api/specialites/${this.form.id}`,
                        data
                    );
                    this.$emit('update', response.data);
                } else {
                    response = await axios.post('/api/specialites', data);
                    this.$emit('save', response.data);
                }

                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: this.isEditMode
                        ? 'Spécialité mise à jour avec succès.'
                        : 'Spécialité ajoutée avec succès.',
                    life: 3000,
                });

                this.cancelForm();
            } catch (error) {
                if (error.response?.status === 422) {
                    this.errors = error.response.data.errors || {};
                    this.activeTabIndex = 0;
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
            this.$emit('close');
            this.$emit('update:visible', false);
        },
        updateDiplomeAr() {
            const selectedDiplome = this.diplomes.find(
                (d) => d.nom_fr === this.form.diplome_fr
            );
            this.form.diplome_ar = selectedDiplome
                ? selectedDiplome.nom_ar
                : null;
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

.font-arabic {
    font-family: 'Montserrat-Arabic-Regular', sans-serif;
    direction: rtl;
}
</style>
```
