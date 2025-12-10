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
                <i class="pi pi-link text-primary text-2xl"></i>
                <span class="font-bold text-2xl">Associer à un Centre</span>
            </div>
        </template>

        <!-- Loading State -->
        <div
            v-if="rolesLoading || centresLoading"
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
            <form @submit.prevent="associate">
                <TabView ref="tabview" v-model:activeIndex="activeTabIndex">
                    <!-- Centre et Rôle -->
                    <TabPanel header="Centre et Rôle">
                        <div
                            class="surface-card p-3 shadow-2 border-round mb-4"
                        >
                            <div class="flex align-items-center gap-3">
                                <div>
                                    <img
                                        v-if="user && user.photo"
                                        :src="user.photo"
                                        alt="Photo de l'utilisateur"
                                        style="
                                            width: 50px;
                                            height: 50px;
                                            object-fit: cover;
                                            border-radius: 50%;
                                        "
                                    />
                                    <span v-else class="text-500"
                                        >Sans photo</span
                                    >
                                </div>
                                <div class="flex flex-column gap-1">
                                    <span class="font-bold"
                                        >{{ user?.nom_fr }}
                                        {{ user?.prenom_fr }}</span
                                    >
                                    <span class="text-sm"
                                        >Rôle:
                                        {{ formatRoles(user?.roles) }}</span
                                    >
                                    <span class="text-sm"
                                        >Affectation:
                                        {{ formatCentres(user?.centres) }}</span
                                    >
                                </div>
                            </div>
                        </div>
                        <div class="grid p-fluid">
                            <!-- Centre -->
                            <div class="col-12 md:col-6 field">
                                <label
                                    for="centre_id"
                                    class="block font-medium mb-2"
                                    >Centre
                                    <span class="text-red-500">*</span></label
                                >
                                <Dropdown
                                    id="centre_id"
                                    v-model="associateCentreData.centre_id"
                                    :options="centres"
                                    optionLabel="nom_fr"
                                    optionValue="id"
                                    placeholder="Sélectionner un centre"
                                    :class="{ 'p-invalid': errors.centre_id }"
                                    @change="validateCentre"
                                />
                                <small
                                    v-if="errors.centre_id"
                                    class="p-error"
                                    >{{ errors.centre_id }}</small
                                >
                            </div>
                            <!-- Rôle -->
                            <div class="col-12 md:col-6 field">
                                <label for="role" class="block font-medium mb-2"
                                    >Rôle
                                    <span class="text-red-500">*</span></label
                                >
                                <Dropdown
                                    id="role"
                                    v-model="associateCentreData.role"
                                    :options="roles"
                                    optionLabel="name"
                                    optionValue="name"
                                    placeholder="Sélectionner un rôle"
                                    :class="{ 'p-invalid': errors.role }"
                                    @change="validateRole"
                                />
                                <small v-if="errors.role" class="p-error">{{
                                    errors.role
                                }}</small>
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
                                    v-model="associateCentreData.statut"
                                    :options="statuts"
                                    optionLabel="label"
                                    optionValue="value"
                                    placeholder="Sélectionner un statut"
                                    :class="{ 'p-invalid': errors.statut }"
                                    @change="validateStatut"
                                />
                                <small v-if="errors.statut" class="p-error">{{
                                    errors.statut
                                }}</small>
                            </div>
                        </div>
                    </TabPanel>

                    <!-- Période d'Affectation -->
                    <TabPanel header="Période d'Affectation">
                        <div class="grid p-fluid">
                            <!-- Date de Début -->
                            <div class="col-12 md:col-6 field">
                                <label
                                    for="date_debut"
                                    class="block font-medium mb-2"
                                    >Date de Début</label
                                >
                                <Calendar
                                    id="date_debut"
                                    v-model="associateCentreData.date_debut"
                                    dateFormat="yy-mm-dd"
                                    showIcon
                                    :class="{ 'p-invalid': errors.date_debut }"
                                    placeholder="Sélectionner une date"
                                    @input="validateDateDebut"
                                />
                                <small
                                    v-if="errors.date_debut"
                                    class="p-error"
                                    >{{ errors.date_debut }}</small
                                >
                            </div>
                            <!-- Date de Fin -->
                            <div class="col-12 md:col-6 field">
                                <label
                                    for="date_fin"
                                    class="block font-medium mb-2"
                                    >Date de Fin</label
                                >
                                <Calendar
                                    id="date_fin"
                                    v-model="associateCentreData.date_fin"
                                    dateFormat="yy-mm-dd"
                                    showIcon
                                    :class="{ 'p-invalid': errors.date_fin }"
                                    placeholder="Sélectionner une date"
                                    @input="validateDateFin"
                                />
                                <small v-if="errors.date_fin" class="p-error">{{
                                    errors.date_fin
                                }}</small>
                            </div>
                        </div>
                    </TabPanel>

                    <!-- Type et Missions -->
                    <TabPanel header="Type et Missions">
                        <div class="grid p-fluid">
                            <!-- Type d'Affectation Français -->
                            <div class="col-12 md:col-6 field">
                                <label
                                    for="type_affectation_fr"
                                    class="block font-medium mb-2"
                                    >Type d'Affectation (Français)</label
                                >
                                <Dropdown
                                    id="type_affectation_fr"
                                    v-model="
                                        associateCentreData.type_affectation_fr
                                    "
                                    :options="typeAffectations"
                                    optionLabel="label"
                                    optionValue="value"
                                    placeholder="Sélectionner un type"
                                    :class="{
                                        'p-invalid': errors.type_affectation_fr,
                                    }"
                                    @change="validateTypeAffectationFr"
                                />
                                <small
                                    v-if="errors.type_affectation_fr"
                                    class="p-error"
                                    >{{ errors.type_affectation_fr }}</small
                                >
                            </div>
                            <!-- Établissement d'Origine Français -->
                            <div
                                v-if="
                                    associateCentreData.type_affectation_fr ===
                                    'D\'autre Etablissement'
                                "
                                class="col-12 md:col-6 field"
                            >
                                <label
                                    for="etablissement_origine_fr"
                                    class="block font-medium mb-2"
                                    >Établissement d'Origine (Français)
                                    <span class="text-red-500">*</span></label
                                >
                                <InputText
                                    id="etablissement_origine_fr"
                                    v-model.trim="
                                        associateCentreData.etablissement_origine_fr
                                    "
                                    :class="{
                                        'p-invalid':
                                            errors.etablissement_origine_fr,
                                    }"
                                    placeholder="Entrez l’établissement"
                                    @input="validateEtablissementOrigineFr"
                                />
                                <small
                                    v-if="errors.etablissement_origine_fr"
                                    class="p-error"
                                    >{{
                                        errors.etablissement_origine_fr
                                    }}</small
                                >
                            </div>
                            <!-- Établissement d'Origine Arabe -->
                            <div
                                v-if="
                                    associateCentreData.type_affectation_fr ===
                                    'D\'autre Etablissement'
                                "
                                class="col-12 md:col-6 field"
                            >
                                <label
                                    for="etablissement_origine_ar"
                                    class="block font-medium mb-2 text-right font-arabic"
                                    >مؤسسة الأصل (العربية)
                                    <span class="text-red-500">*</span></label
                                >
                                <InputText
                                    id="etablissement_origine_ar"
                                    v-model.trim="
                                        associateCentreData.etablissement_origine_ar
                                    "
                                    dir="rtl"
                                    :class="{
                                        'p-invalid':
                                            errors.etablissement_origine_ar,
                                    }"
                                    placeholder="أدخل المؤسسة"
                                    @input="validateEtablissementOrigineAr"
                                />
                                <small
                                    v-if="errors.etablissement_origine_ar"
                                    class="p-error"
                                    >{{
                                        errors.etablissement_origine_ar
                                    }}</small
                                >
                            </div>
                            <!-- Centre d'Origine Français -->
                            <div
                                v-if="
                                    associateCentreData.type_affectation_fr ===
                                    'D\'autre centre'
                                "
                                class="col-12 md:col-6 field"
                            >
                                <label
                                    for="centre_origine_fr"
                                    class="block font-medium mb-2"
                                    >Centre d'Origine (Français)
                                    <span class="text-red-500">*</span></label
                                >
                                <Dropdown
                                    id="centre_origine_fr"
                                    v-model="
                                        associateCentreData.centre_origine_fr
                                    "
                                    :options="centres"
                                    optionLabel="nom_fr"
                                    optionValue="nom_fr"
                                    placeholder="Sélectionner un centre"
                                    :class="{
                                        'p-invalid': errors.centre_origine_fr,
                                    }"
                                    @change="validateCentreOrigineFr"
                                />
                                <small
                                    v-if="errors.centre_origine_fr"
                                    class="p-error"
                                    >{{ errors.centre_origine_fr }}</small
                                >
                            </div>
                            <!-- Mission Français -->
                            <div class="col-12 md:col-6 field">
                                <label
                                    for="mission_fr"
                                    class="block font-medium mb-2"
                                    >Mission (Français)</label
                                >
                                <InputText
                                    id="mission_fr"
                                    v-model.trim="
                                        associateCentreData.mission_fr
                                    "
                                    :class="{ 'p-invalid': errors.mission_fr }"
                                    placeholder="Entrez la mission"
                                    @input="validateMissionFr"
                                />
                                <small
                                    v-if="errors.mission_fr"
                                    class="p-error"
                                    >{{ errors.mission_fr }}</small
                                >
                            </div>
                            <!-- Mission Arabe -->
                            <div class="col-12 md:col-6 field">
                                <label
                                    for="mission_ar"
                                    class="block font-medium mb-2 text-right font-arabic"
                                    >المهمة (العربية)</label
                                >
                                <InputText
                                    id="mission_ar"
                                    v-model.trim="
                                        associateCentreData.mission_ar
                                    "
                                    dir="rtl"
                                    :class="{ 'p-invalid': errors.mission_ar }"
                                    placeholder="أدخل المهمة"
                                    @input="validateMissionAr"
                                />
                                <small
                                    v-if="errors.mission_ar"
                                    class="p-error"
                                    >{{ errors.mission_ar }}</small
                                >
                            </div>
                            <!-- Observation Français -->
                            <div class="col-12 field">
                                <label
                                    for="observation_fr"
                                    class="block font-medium mb-2"
                                    >Observation (Français)</label
                                >
                                <Textarea
                                    id="observation_fr"
                                    v-model.trim="
                                        associateCentreData.observation_fr
                                    "
                                    rows="4"
                                    :class="{
                                        'p-invalid': errors.observation_fr,
                                    }"
                                    placeholder="Entrez une observation"
                                    @input="validateObservationFr"
                                />
                                <small
                                    v-if="errors.observation_fr"
                                    class="p-error"
                                    >{{ errors.observation_fr }}</small
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
                    @click="cancel"
                />
                <Button
                    label="Associer"
                    icon="pi pi-check"
                    class="p-button-primary"
                    :loading="loading"
                    @click="associate"
                />
            </div>
        </template>

        <Toast position="top-right" />
    </Dialog>
</template>

<script setup>
import { ref, watch } from 'vue';
import { useToast } from 'primevue/usetoast';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import Dropdown from 'primevue/dropdown';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Calendar from 'primevue/calendar';
import ProgressSpinner from 'primevue/progressspinner';
import Toast from 'primevue/toast';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';

const props = defineProps({
    visible: { type: Boolean, required: true },
    user: { type: Object, default: null },
    roles: { type: Array, required: true },
    centres: { type: Array, required: true },
    rolesLoading: { type: Boolean, default: false },
    centresLoading: { type: Boolean, default: false },
});

const emit = defineEmits(['update:visible', 'associate', 'cancel']);

const toast = useToast();
const loading = ref(false);
const errors = ref({});
const activeTabIndex = ref(0);
const associateCentreData = ref({
    centre_id: null,
    role: null,
    type_affectation_fr: null,
    etablissement_origine_fr: null,
    etablissement_origine_ar: null,
    mission_fr: null,
    mission_ar: null,
    centre_origine_fr: null,
    observation_fr: null,
    date_debut: null,
    date_fin: null,
    statut: 'Actif',
});
const typeAffectations = ref([
    { label: 'Permanent', value: 'Permanent' },
    { label: 'Intérimaire', value: 'Intérimaire' },
    { label: 'Vacataire', value: 'Vacataire' },
    { label: "D'autre Etablissement", value: "D'autre Etablissement" },
    { label: "D'autre centre", value: "D'autre centre" },
]);
const statuts = ref([
    { label: 'Actif', value: 'Actif' },
    { label: 'Inactif', value: 'Inactif' },
]);
const typeAffectationMapping = {
    Permanent: 'قار',
    Intérimaire: 'بالنيابة',
    Vacataire: 'متعاقد',
    "D'autre Etablissement": 'من مؤسسة أخرى',
    "D'autre centre": 'من مركز آخر',
};

// Watchers
watch(
    () => associateCentreData.value.type_affectation_fr,
    (newTypeFr) => {
        if (newTypeFr !== "D'autre Etablissement") {
            associateCentreData.value.etablissement_origine_fr = null;
            associateCentreData.value.etablissement_origine_ar = null;
            errors.value.etablissement_origine_fr = '';
            errors.value.etablissement_origine_ar = '';
        }
        if (newTypeFr !== "D'autre centre") {
            associateCentreData.value.centre_origine_fr = null;
            errors.value.centre_origine_fr = '';
        }
        validateTypeAffectationFr();
    }
);

watch(
    () => associateCentreData.value.centre_origine_fr,
    (newCentreFr) => {
        if (
            newCentreFr &&
            associateCentreData.value.type_affectation_fr === "D'autre centre"
        ) {
            validateCentreOrigineFr();
        }
    }
);

// Reset form when dialog is opened
watch(
    () => props.visible,
    (newValue) => {
        if (newValue) {
            resetForm();
        }
    }
);

function resetForm() {
    associateCentreData.value = {
        centre_id: null,
        role: null,
        type_affectation_fr: null,
        etablissement_origine_fr: null,
        etablissement_origine_ar: null,
        mission_fr: null,
        mission_ar: null,
        centre_origine_fr: null,
        observation_fr: null,
        date_debut: null,
        date_fin: null,
        statut: 'Actif',
    };
    errors.value = {};
    activeTabIndex.value = 0;
}

// Format functions from UserListe.vue
function formatRoles(roles) {
    return roles && Array.isArray(roles) && roles.length > 0
        ? roles.map((role) => role.name).join(', ')
        : 'Aucun rôle attribué';
}

function formatCentres(centres) {
    return centres && Array.isArray(centres) && centres.length > 0
        ? centres[0].nom_fr
        : 'Non Affecté';
}

// Validation Methods
function validateCentre() {
    if (!associateCentreData.value.centre_id) {
        errors.value.centre_id = 'Le centre est obligatoire.';
    } else {
        errors.value.centre_id = '';
    }
}

function validateRole() {
    if (!associateCentreData.value.role) {
        errors.value.role = 'Le rôle est obligatoire.';
    } else {
        errors.value.role = '';
    }
}

function validateTypeAffectationFr() {
    errors.value.type_affectation_fr = '';
}

function validateEtablissementOrigineFr() {
    if (
        associateCentreData.value.type_affectation_fr ===
            "D'autre Etablissement" &&
        !associateCentreData.value.etablissement_origine_fr
    ) {
        errors.value.etablissement_origine_fr =
            'L’établissement d’origine (Français) est obligatoire.';
    } else if (
        associateCentreData.value.etablissement_origine_fr &&
        associateCentreData.value.etablissement_origine_fr.length > 255
    ) {
        errors.value.etablissement_origine_fr =
            'L’établissement d’origine (Français) ne doit pas dépasser 255 caractères.';
    } else {
        errors.value.etablissement_origine_fr = '';
    }
}

function validateEtablissementOrigineAr() {
    if (
        associateCentreData.value.type_affectation_fr ===
            "D'autre Etablissement" &&
        !associateCentreData.value.etablissement_origine_ar
    ) {
        errors.value.etablissement_origine_ar = 'مؤسسة الأصل (العربية) مطلوبة.';
    } else if (
        associateCentreData.value.etablissement_origine_ar &&
        associateCentreData.value.etablissement_origine_ar.length > 255
    ) {
        errors.value.etablissement_origine_ar =
            'مؤسسة الأصل (العربية) يجب ألا تتجاوز 255 حرفًا.';
    } else {
        errors.value.etablissement_origine_ar = '';
    }
}

function validateCentreOrigineFr() {
    if (
        associateCentreData.value.type_affectation_fr === "D'autre centre" &&
        !associateCentreData.value.centre_origine_fr
    ) {
        errors.value.centre_origine_fr =
            'Le centre d’origine (Français) est obligatoire.';
    } else {
        errors.value.centre_origine_fr = '';
    }
}

function validateMissionFr() {
    if (
        associateCentreData.value.mission_fr &&
        associateCentreData.value.mission_fr.length > 255
    ) {
        errors.value.mission_fr =
            'La mission (Français) ne doit pas dépasser 255 caractères.';
    } else {
        errors.value.mission_fr = '';
    }
}

function validateMissionAr() {
    if (
        associateCentreData.value.mission_ar &&
        associateCentreData.value.mission_ar.length > 255
    ) {
        errors.value.mission_ar = 'المهمة (العربية) يجب ألا تتجاوز 255 حرفًا.';
    } else {
        errors.value.mission_ar = '';
    }
}

function validateObservationFr() {
    if (
        associateCentreData.value.observation_fr &&
        associateCentreData.value.observation_fr.length > 255
    ) {
        errors.value.observation_fr =
            'L’observation (Français) ne doit pas dépasser 255 caractères.';
    } else {
        errors.value.observation_fr = '';
    }
}

function validateDateDebut() {
    if (associateCentreData.value.date_debut) {
        const today = new Date();
        const dateDebut = new Date(associateCentreData.value.date_debut);
        if (dateDebut > today) {
            errors.value.date_debut =
                'La date de début ne peut pas être dans le futur.';
        } else {
            errors.value.date_debut = '';
        }
    } else {
        errors.value.date_debut = '';
    }
}

function validateDateFin() {
    if (
        associateCentreData.value.date_fin &&
        associateCentreData.value.date_debut
    ) {
        const startDate = new Date(associateCentreData.value.date_debut);
        const endDate = new Date(associateCentreData.value.date_fin);
        if (endDate < startDate) {
            errors.value.date_fin =
                'La date de fin doit être postérieure ou égale à la date de début.';
        } else {
            errors.value.date_fin = '';
        }
    } else {
        errors.value.date_fin = '';
    }
}

function validateStatut() {
    if (!associateCentreData.value.statut) {
        errors.value.statut = 'Le statut est obligatoire.';
    } else if (
        !['Actif', 'Inactif'].includes(associateCentreData.value.statut)
    ) {
        errors.value.statut = 'Le statut doit être Actif ou Inactif.';
    } else {
        errors.value.statut = '';
    }
}

function validateAll() {
    validateCentre();
    validateRole();
    validateTypeAffectationFr();
    validateEtablissementOrigineFr();
    validateEtablissementOrigineAr();
    validateCentreOrigineFr();
    validateMissionFr();
    validateMissionAr();
    validateObservationFr();
    validateDateDebut();
    validateDateFin();
    validateStatut();

    // Set active tab based on errors
    if (Object.values(errors.value).some((error) => error)) {
        if (
            ['centre_id', 'role', 'statut'].some((field) => errors.value[field])
        ) {
            activeTabIndex.value = 0;
        } else if (
            ['date_debut', 'date_fin'].some((field) => errors.value[field])
        ) {
            activeTabIndex.value = 1;
        } else if (
            [
                'type_affectation_fr',
                'etablissement_origine_fr',
                'etablissement_origine_ar',
                'centre_origine_fr',
                'mission_fr',
                'mission_ar',
                'observation_fr',
            ].some((field) => errors.value[field])
        ) {
            activeTabIndex.value = 2;
        }
    }
}

async function associate() {
    if (loading.value) return;
    validateAll();

    if (Object.values(errors.value).some((error) => error)) {
        toast.add({
            severity: 'error',
            summary: 'Erreur de validation',
            detail: 'Veuillez vérifier les champs du formulaire.',
            life: 3000,
        });
        return;
    }

    if (!props.user || !props.user.id) {
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: 'Utilisateur invalide pour association.',
            life: 3000,
        });
        return;
    }

    loading.value = true;
    try {
        // Derive centre_origine_ar from centre_origine_fr
        let centre_origine_ar = null;
        if (
            associateCentreData.value.centre_origine_fr &&
            associateCentreData.value.type_affectation_fr === "D'autre centre"
        ) {
            const selectedCentre = props.centres.find(
                (centre) =>
                    centre.nom_fr ===
                    associateCentreData.value.centre_origine_fr
            );
            centre_origine_ar = selectedCentre ? selectedCentre.nom_ar : null;
        }

        const payload = {
            ...associateCentreData.value,
            type_affectation_ar:
                typeAffectationMapping[
                    associateCentreData.value.type_affectation_fr
                ] || null,
            centre_origine_ar,
            date_debut: associateCentreData.value.date_debut
                ? new Date(associateCentreData.value.date_debut)
                      .toISOString()
                      .split('T')[0]
                : null,
            date_fin: associateCentreData.value.date_fin
                ? new Date(associateCentreData.value.date_fin)
                      .toISOString()
                      .split('T')[0]
                : null,
        };
        emit('associate', { userId: props.user.id, payload });
        toast.add({
            severity: 'success',
            summary: 'Succès',
            detail: 'Utilisateur associé au centre avec succès.',
            life: 3000,
        });
        cancel();
    } catch (error) {
        errors.value = error.response?.data?.errors || {};
        validateAll(); // Update tab based on server-side errors
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail:
                error.response?.data?.message ||
                'Erreur lors de l’association.',
            life: 3000,
        });
    } finally {
        loading.value = false;
    }
}

function cancel() {
    resetForm();
    emit('cancel');
    emit('update:visible', false);
}
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
:deep(.p-textarea),
:deep(.p-calendar) {
    border-radius: 0.25rem;
}

:deep(.p-dialog-header) {
    border-top-left-radius: 12px;
    border-top-right-radius: 12px;
}

:deep(.p-dialog-content) {
    border-bottom-left-radius: 12px;
    border-bottom-right-radius: 12px;
}
</style>
