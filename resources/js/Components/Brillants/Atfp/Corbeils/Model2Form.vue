<template>
    <Dialog
        :visible="visible"
        :header="isEditMode ? 'Modifier le centre' : 'Créer un centre'"
        :modal="true"
        :style="{ width: '80vw', maxWidth: '1200px' }"
        @update:visible="onUpdateVisible"
        class="p-fluid"
    >
        <div class="surface-card p-2 shadow-2 border-round">
            <form @submit.prevent="submitForm" class="flex flex-column gap-2">
                <Stepper v-model:activeStep="currentStep" class="w-full">
                    <!-- Étape 1: Informations en Français -->
                    <StepperPanel header="Informations en Français">
                        <template #content="{ nextCallback }">
                            <div class="flex flex-column gap-2">
                                <div class="grid gap-12">
                                    <!-- Code -->
                                    <div class="col-12 md:col-6">
                                        <label
                                            for="code"
                                            class="font-medium mb-6"
                                            >Code
                                            <span class="text-red-500"
                                                >*</span
                                            ></label
                                        >
                                        <InputText
                                            id="code"
                                            v-model="form.code"
                                            type="text"
                                            class="w-full"
                                            :class="{
                                                'p-invalid': errors.code,
                                            }"
                                            maxlength="20"
                                            placeholder="Entrer le code"
                                        />
                                        <small
                                            v-if="errors.code"
                                            class="p-error"
                                            >{{ errors.code }}</small
                                        >
                                    </div>
                                    <!-- Gouvernorat -->
                                    <div class="col-12 md:col-6">
                                        <label
                                            for="gouvernorat_fr"
                                            class="font-medium mb-6"
                                            >Gouvernorat
                                            <span class="text-red-500"
                                                >*</span
                                            ></label
                                        >
                                        <Dropdown
                                            id="gouvernorat_fr"
                                            v-model="form.gouvernorat_fr"
                                            :options="
                                                lists['Gouvernorats'] || []
                                            "
                                            optionLabel="nom_fr"
                                            optionValue="nom_fr"
                                            placeholder="Sélectionner un gouvernorat"
                                            class="w-full"
                                            :class="{
                                                'p-invalid':
                                                    errors.gouvernorat_fr,
                                            }"
                                            filter
                                        />
                                        <small
                                            v-if="errors.gouvernorat_fr"
                                            class="p-error"
                                            >{{ errors.gouvernorat_fr }}</small
                                        >
                                        <small
                                            v-if="
                                                !lists['Gouvernorats'] ||
                                                lists['Gouvernorats'].length ===
                                                    0
                                            "
                                            class="p-error"
                                            >Aucune option de gouvernorat
                                            disponible.</small
                                        >
                                    </div>

                                    <!-- Nom (FR) -->
                                    <div class="col-12 md:col-12">
                                        <label
                                            for="nom_fr"
                                            class="font-medium mb-2"
                                            >Nom (Français)
                                            <span class="text-red-500"
                                                >*</span
                                            ></label
                                        >
                                        <InputText
                                            id="nom_fr"
                                            v-model="form.nom_fr"
                                            type="text"
                                            class="w-full"
                                            :class="{
                                                'p-invalid': errors.nom_fr,
                                            }"
                                            maxlength="255"
                                            placeholder="Entrer le nom en français"
                                        />
                                        <small
                                            v-if="errors.nom_fr"
                                            class="p-error"
                                            >{{ errors.nom_fr }}</small
                                        >
                                    </div>

                                    <!-- Adresse (FR) -->
                                    <div class="col-12 md:col-12">
                                        <label
                                            for="adresse_fr"
                                            class="font-medium mb-2"
                                            >Adresse (Français)
                                            <span class="text-red-500"
                                                >*</span
                                            ></label
                                        >
                                        <InputText
                                            id="adresse_fr"
                                            v-model="form.adresse_fr"
                                            type="text"
                                            class="w-full"
                                            :class="{
                                                'p-invalid': errors.adresse_fr,
                                            }"
                                            maxlength="255"
                                            placeholder="Entrer l'adresse en français"
                                        />
                                        <small
                                            v-if="errors.adresse_fr"
                                            class="p-error"
                                            >{{ errors.adresse_fr }}</small
                                        >
                                    </div>

                                    <!-- Téléphone 1 -->
                                    <div class="col-12 md:col-3">
                                        <label
                                            for="telephone_1"
                                            class="font-medium mb-2"
                                            >Téléphone 1
                                            <span class="text-red-500"
                                                >*</span
                                            ></label
                                        >
                                        <InputText
                                            id="telephone_1"
                                            v-model="form.telephone_1"
                                            type="text"
                                            class="w-full"
                                            :class="{
                                                'p-invalid': errors.telephone_1,
                                            }"
                                            maxlength="20"
                                            placeholder="Entrer le numéro de téléphone"
                                        />
                                        <small
                                            v-if="errors.telephone_1"
                                            class="p-error"
                                            >{{ errors.telephone_1 }}</small
                                        >
                                    </div>

                                    <!-- Téléphone 2 -->
                                    <div class="col-12 md:col-3">
                                        <label
                                            for="telephone_2"
                                            class="font-medium mb-2"
                                            >Téléphone 2</label
                                        >
                                        <InputText
                                            id="telephone_2"
                                            v-model="form.telephone_2"
                                            type="text"
                                            class="w-full"
                                            :class="{
                                                'p-invalid': errors.telephone_2,
                                            }"
                                            maxlength="20"
                                            placeholder="Entrer un autre numéro (optionnel)"
                                        />
                                        <small
                                            v-if="errors.telephone_2"
                                            class="p-error"
                                            >{{ errors.telephone_2 }}</small
                                        >
                                    </div>

                                    <!-- Fax 1 -->
                                    <div class="col-12 md:col-3">
                                        <label
                                            for="fax_1"
                                            class="font-medium mb-2"
                                            >Fax 1</label
                                        >
                                        <InputText
                                            id="fax_1"
                                            v-model="form.fax_1"
                                            type="text"
                                            class="w-full"
                                            :class="{
                                                'p-invalid': errors.fax_1,
                                            }"
                                            maxlength="20"
                                            placeholder="Entrer le numéro de fax (optionnel)"
                                        />
                                        <small
                                            v-if="errors.fax_1"
                                            class="p-error"
                                            >{{ errors.fax_1 }}</small
                                        >
                                    </div>

                                    <!-- Fax 2 -->
                                    <div class="col-12 md:col-3">
                                        <label
                                            for="fax_2"
                                            class="font-medium mb-2"
                                            >Fax 2</label
                                        >
                                        <InputText
                                            id="fax_2"
                                            v-model="form.fax_2"
                                            type="text"
                                            class="w-full"
                                            :class="{
                                                'p-invalid': errors.fax_2,
                                            }"
                                            maxlength="20"
                                            placeholder="Entrer un autre fax (optionnel)"
                                        />
                                        <small
                                            v-if="errors.fax_2"
                                            class="p-error"
                                            >{{ errors.fax_2 }}</small
                                        >
                                    </div>

                                    <!-- Email -->
                                    <div class="col-12 md:col-4">
                                        <label
                                            for="email"
                                            class="font-medium mb-2"
                                            >Email
                                            <span class="text-red-500"
                                                >*</span
                                            ></label
                                        >
                                        <InputText
                                            id="email"
                                            v-model="form.email"
                                            type="email"
                                            class="w-full"
                                            :class="{
                                                'p-invalid': errors.email,
                                            }"
                                            maxlength="255"
                                            placeholder="Entrer l'adresse email"
                                        />
                                        <small
                                            v-if="errors.email"
                                            class="p-error"
                                            >{{ errors.email }}</small
                                        >
                                    </div>

                                    <!-- Type de centre -->
                                    <div class="col-12 md:col-4">
                                        <label
                                            for="type_centre_fr"
                                            class="font-medium mb-2"
                                            >Type de centre</label
                                        >
                                        <Dropdown
                                            id="type_centre_fr"
                                            v-model="form.type_centre_fr"
                                            :options="
                                                lists['Types Centres'] || []
                                            "
                                            optionLabel="nom_fr"
                                            optionValue="nom_fr"
                                            placeholder="Sélectionner un type de centre"
                                            class="w-full"
                                            :class="{
                                                'p-invalid':
                                                    errors.type_centre_fr,
                                            }"
                                            filter
                                        />
                                        <small
                                            v-if="errors.type_centre_fr"
                                            class="p-error"
                                            >{{ errors.type_centre_fr }}</small
                                        >
                                        <small
                                            v-if="
                                                !lists['Types Centres'] ||
                                                lists['Types Centres']
                                                    .length === 0
                                            "
                                            class="p-error"
                                            >Aucune option de type de centre
                                            disponible.</small
                                        >
                                    </div>

                                    <!-- Classe -->
                                    <div class="col-12 md:col-4">
                                        <label
                                            for="classe_fr"
                                            class="font-medium mb-2"
                                            >Classe</label
                                        >
                                        <Dropdown
                                            id="classe_fr"
                                            v-model="form.classe_fr"
                                            :options="
                                                lists['Classes Centres'] || []
                                            "
                                            optionLabel="nom_fr"
                                            optionValue="nom_fr"
                                            placeholder="Sélectionner une classe"
                                            class="w-full"
                                            :class="{
                                                'p-invalid': errors.classe_fr,
                                            }"
                                            filter
                                        />
                                        <small
                                            v-if="errors.classe_fr"
                                            class="p-error"
                                            >{{ errors.classe_fr }}</small
                                        >
                                        <small
                                            v-if="
                                                !lists['Classes Centres'] ||
                                                lists['Classes Centres']
                                                    .length === 0
                                            "
                                            class="p-error"
                                            >Aucune option de classe
                                            disponible.</small
                                        >
                                    </div>

                                    <!-- Économat -->
                                    <div class="col-12 md:col-4">
                                        <label
                                            for="economat_fr"
                                            class="font-medium mb-2"
                                            >Économat
                                            <span class="text-red-500"
                                                >*</span
                                            ></label
                                        >
                                        <Dropdown
                                            id="economat_fr"
                                            v-model="form.economat_fr"
                                            :options="lists['Economats'] || []"
                                            optionLabel="nom_fr"
                                            optionValue="nom_fr"
                                            placeholder="Sélectionner un économat"
                                            class="w-full"
                                            :class="{
                                                'p-invalid': errors.economat_fr,
                                            }"
                                            filter
                                        />
                                        <small
                                            v-if="errors.economat_fr"
                                            class="p-error"
                                            >{{ errors.economat_fr }}</small
                                        >
                                        <small
                                            v-if="
                                                !lists['Economats'] ||
                                                lists['Economats'].length === 0
                                            "
                                            class="p-error"
                                            >Aucune option d'économat
                                            disponible.</small
                                        >
                                    </div>

                                    <!-- État -->
                                    <div class="col-12 md:col-4">
                                        <label
                                            for="etat_fr"
                                            class="font-medium mb-2"
                                            >État</label
                                        >
                                        <Dropdown
                                            id="etat_fr"
                                            v-model="form.etat_fr"
                                            :options="
                                                lists['Etats Centres'] || []
                                            "
                                            optionLabel="nom_fr"
                                            optionValue="nom_fr"
                                            placeholder="Sélectionner un état"
                                            class="w-full"
                                            :class="{
                                                'p-invalid': errors.etat_fr,
                                            }"
                                            filter
                                        />
                                        <small
                                            v-if="errors.etat_fr"
                                            class="p-error"
                                            >{{ errors.etat_fr }}</small
                                        >
                                        <small
                                            v-if="
                                                !lists['Etats Centres'] ||
                                                lists['Etats Centres']
                                                    .length === 0
                                            "
                                            class="p-error"
                                            >Aucune option d'état
                                            disponible.</small
                                        >
                                    </div>

                                    <!-- Statut -->
                                    <div class="col-12 md:col-4">
                                        <label
                                            for="statut_fr"
                                            class="font-medium mb-2"
                                            >Statut</label
                                        >
                                        <Dropdown
                                            id="statut_fr"
                                            v-model="form.statut_fr"
                                            :options="
                                                lists['Statuts Centres'] || []
                                            "
                                            optionLabel="nom_fr"
                                            optionValue="nom_fr"
                                            placeholder="Sélectionner un statut"
                                            class="w-full"
                                            :class="{
                                                'p-invalid': errors.statut_fr,
                                            }"
                                            filter
                                        />
                                        <small
                                            v-if="errors.statut_fr"
                                            class="p-error"
                                            >{{ errors.statut_fr }}</small
                                        >
                                        <small
                                            v-if="
                                                !lists['Statuts Centres'] ||
                                                lists['Statuts Centres']
                                                    .length === 0
                                            "
                                            class="p-error"
                                            >Aucune option de statut
                                            disponible.</small
                                        >
                                    </div>

                                    <!-- Date de création -->
                                    <div class="col-12 md:col-4">
                                        <label
                                            for="date_creation"
                                            class="font-medium mb-2"
                                            >Date de création</label
                                        >
                                        <Calendar
                                            id="date_creation"
                                            v-model="form.date_creation"
                                            dateFormat="yy-mm-dd"
                                            class="w-full"
                                            :class="{
                                                'p-invalid':
                                                    errors.date_creation,
                                            }"
                                            showIcon
                                            iconDisplay="input"
                                            placeholder="Sélectionner une date"
                                        />
                                        <small
                                            v-if="errors.date_creation"
                                            class="p-error"
                                            >{{ errors.date_creation }}</small
                                        >
                                    </div>

                                    <!-- Date d'ouverture -->
                                    <div class="col-12 md:col-4">
                                        <label
                                            for="date_ouverture"
                                            class="font-medium mb-2"
                                            >Date d'ouverture</label
                                        >
                                        <Calendar
                                            id="date_ouverture"
                                            v-model="form.date_ouverture"
                                            dateFormat="yy-mm-dd"
                                            class="w-full"
                                            :class="{
                                                'p-invalid':
                                                    errors.date_ouverture,
                                            }"
                                            showIcon
                                            iconDisplay="input"
                                            placeholder="Sélectionner une date"
                                        />
                                        <small
                                            v-if="errors.date_ouverture"
                                            class="p-error"
                                            >{{ errors.date_ouverture }}</small
                                        >
                                    </div>

                                    <!-- Date de fermeture -->
                                    <div class="col-12 md:col-4">
                                        <label
                                            for="date_fermeture"
                                            class="font-medium mb-2"
                                            >Date de fermeture</label
                                        >
                                        <Calendar
                                            id="date_fermeture"
                                            v-model="form.date_fermeture"
                                            dateFormat="yy-mm-dd"
                                            class="w-full"
                                            :class="{
                                                'p-invalid':
                                                    errors.date_fermeture,
                                            }"
                                            showIcon
                                            iconDisplay="input"
                                            placeholder="Sélectionner une date"
                                        />
                                        <small
                                            v-if="errors.date_fermeture"
                                            class="p-error"
                                            >{{ errors.date_fermeture }}</small
                                        >
                                    </div>

                                    <!-- Logo -->
                                    <div class="col-12 md:col-12">
                                        <label
                                            for="logo"
                                            class="font-medium mb-2"
                                            >Logo (PNG/JPEG, max 2MB)</label
                                        >
                                        <div class="mt-1">
                                            <Button
                                                label="Choisir un fichier"
                                                icon="pi pi-upload"
                                                severity="secondary"
                                                raised
                                                @click="$refs.fileInput.click()"
                                            />
                                            <input
                                                ref="fileInput"
                                                type="file"
                                                accept="image/png,image/jpeg"
                                                class="hidden"
                                                @change="handleFileChange"
                                            />
                                        </div>
                                        <small
                                            v-if="errors.logo"
                                            class="p-error"
                                            >{{ errors.logo }}</small
                                        >
                                        <div
                                            v-if="logoPreview"
                                            class="mt-4 flex align-items-center gap-6"
                                        >
                                            <img
                                                :src="logoPreview"
                                                alt="Prévisualisation du logo"
                                                class="h-20 w-60 object-contain"
                                            />
                                            <Button
                                                label="Supprimer le logo"
                                                severity="danger"
                                                raised
                                                @click="clearLogo"
                                            />
                                        </div>
                                    </div>

                                    <!-- Observation (FR) -->
                                    <div class="col-12">
                                        <label
                                            for="observation_fr"
                                            class="font-medium mb-2"
                                            >Observation</label
                                        >
                                        <Textarea
                                            id="observation_fr"
                                            v-model="form.observation_fr"
                                            rows="4"
                                            class="w-full"
                                            placeholder="Entrer une observation"
                                        />
                                    </div>
                                </div>
                                <!-- Boutons pour Étape 1 -->
                                <div
                                    class="flex justify-content-between gap-3 pt-4"
                                >
                                    <Button
                                        label="Annuler"
                                        icon="pi pi-times"
                                        severity="secondary"
                                        raised
                                        @click="onUpdateVisible(false)"
                                    />
                                    <Button
                                        label="Suivant"
                                        icon="pi pi-arrow-right"
                                        iconPos="right"
                                        severity="primary"
                                        raised
                                        @click="nextStep(nextCallback)"
                                    />
                                </div>
                            </div>
                        </template>
                    </StepperPanel>

                    <!-- Étape 2: Informations en Arabe -->
                    <StepperPanel header="Informations en Arabe">
                        <template #content="{ prevCallback }">
                            <div class="flex flex-column gap-4">
                                <div class="grid gap-3">
                                    <!-- Nom (AR) -->
                                    <div class="col-12 md:col-12">
                                        <label
                                            for="nom_ar"
                                            class="font-medium mb-2"
                                            >Nom (Arabe)
                                            <span class="text-red-500"
                                                >*</span
                                            ></label
                                        >
                                        <InputText
                                            id="nom_ar"
                                            v-model="form.nom_ar"
                                            type="text"
                                            class="w-full"
                                            :class="{
                                                'p-invalid': errors.nom_ar,
                                            }"
                                            maxlength="255"
                                            placeholder="Entrer le nom en arabe"
                                        />
                                        <small
                                            v-if="errors.nom_ar"
                                            class="p-error"
                                            >{{ errors.nom_ar }}</small
                                        >
                                    </div>

                                    <!-- Adresse (AR) -->
                                    <div class="col-12 md:col-12">
                                        <label
                                            for="adresse_ar"
                                            class="font-medium mb-2"
                                            >Adresse (Arabe)
                                            <span class="text-red-500"
                                                >*</span
                                            ></label
                                        >
                                        <InputText
                                            id="adresse_ar"
                                            v-model="form.adresse_ar"
                                            type="text"
                                            class="w-full"
                                            :class="{
                                                'p-invalid': errors.adresse_ar,
                                            }"
                                            maxlength="255"
                                            placeholder="Entrer l'adresse en arabe"
                                        />
                                        <small
                                            v-if="errors.adresse_ar"
                                            class="p-error"
                                            >{{ errors.adresse_ar }}</small
                                        >
                                    </div>

                                    <!-- Observation (AR) -->
                                    <div class="col-12">
                                        <label
                                            for="observation_ar"
                                            class="font-medium mb-2"
                                            >Observation (Arabe)</label
                                        >
                                        <Textarea
                                            id="observation_ar"
                                            v-model="form.observation_ar"
                                            rows="4"
                                            class="w-full"
                                            placeholder="Entrer une observation en arabe"
                                        />
                                    </div>
                                </div>
                                <!-- Boutons pour Étape 2 -->
                                <div
                                    class="flex justify-content-between gap-3 pt-4"
                                >
                                    <Button
                                        label="Retour"
                                        icon="pi pi-arrow-left"
                                        severity="secondary"
                                        raised
                                        @click="previousStep(prevCallback)"
                                    />
                                    <Button
                                        :label="
                                            isSubmitting
                                                ? 'Enregistrement...'
                                                : isEditMode
                                                  ? 'Mettre à jour'
                                                  : 'Enregistrer'
                                        "
                                        icon="pi pi-check"
                                        iconPos="right"
                                        severity="success"
                                        raised
                                        :disabled="isSubmitting"
                                        type="submit"
                                    />
                                </div>
                            </div>
                        </template>
                    </StepperPanel>
                </Stepper>
            </form>
        </div>
    </Dialog>
</template>

<script>
import axios from 'axios';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Textarea from 'primevue/textarea';
import Calendar from 'primevue/calendar';
import Stepper from 'primevue/stepper';
import StepperPanel from 'primevue/stepperpanel';
import { useToast } from 'primevue/usetoast';

export default {
    name: 'CentresForm',
    components: {
        Button,
        Dialog,
        InputText,
        Dropdown,
        Textarea,
        Calendar,
        Stepper,
        StepperPanel,
    },
    props: {
        visible: {
            type: Boolean,
            default: false,
        },
        centreId: {
            type: [String, Number],
            default: null,
        },
    },
    emits: ['update:visible', 'close', 'save', 'update'],
    setup() {
        return { toast: useToast() };
    },
    data() {
        return {
            form: {
                code: '',
                nom_fr: '',
                nom_ar: '',
                adresse_fr: '',
                adresse_ar: '',
                telephone_1: '',
                telephone_2: '',
                fax_1: '',
                fax_2: '',
                email: '',
                gouvernorat_fr: null,
                type_centre_fr: null,
                classe_fr: null,
                economat_fr: null,
                etat_fr: null,
                statut_fr: null,
                date_creation: null,
                date_ouverture: null,
                date_fermeture: null,
                observation_fr: '',
                observation_ar: '',
                logo: null,
            },
            logoFile: null,
            logoPreview: null,
            lists: {},
            errors: {},
            isSubmitting: false,
            isEditMode: !!this.centreId,
            currentStep: 0,
        };
    },
    watch: {
        centreId(newVal) {
            this.isEditMode = !!newVal;
            if (newVal) {
                this.fetchCentre();
            } else {
                this.resetForm();
                this.fetchLists();
            }
        },
        visible(newVal) {
            if (newVal) {
                if (this.isEditMode) {
                    this.fetchCentre();
                } else {
                    this.fetchLists();
                }
            }
        },
    },
    methods: {
        async fetchLists() {
            try {
                const response = await axios.get('/api/centres-with-options');
                this.lists = response.data.lists || {};
                this.$forceUpdate();

                const requiredLists = [
                    'Gouvernorats',
                    'Types Centres',
                    'Classes Centres',
                    'Economats',
                    'Etats Centres',
                    'Statuts Centres',
                ];
                requiredLists.forEach((list) => {
                    if (
                        !this.lists[list] ||
                        !Array.isArray(this.lists[list]) ||
                        this.lists[list].length === 0
                    ) {
                        this.toast.add({
                            severity: 'warn',
                            summary: 'Attention',
                            detail: `La liste "${list}" est vide ou non disponible.`,
                            life: 5000,
                        });
                    }
                });
            } catch (error) {
                console.error(
                    'Erreur lors de la récupération des listes:',
                    error
                );
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        'Erreur serveur lors du chargement des listes.',
                    life: 5000,
                });
                this.lists = {};
            }
        },
        async fetchCentre() {
            try {
                const response = await axios.get(
                    `/api/centres-with-options/${this.centreId}`
                );
                const centre = response.data.centre;
                this.lists = response.data.lists || {};

                const listFields = [
                    { fr: 'gouvernorat_fr', list: 'Gouvernorats' },
                    { fr: 'type_centre_fr', list: 'Types Centres' },
                    { fr: 'classe_fr', list: 'Classes Centres' },
                    { fr: 'economat_fr', list: 'Economats' },
                    { fr: 'etat_fr', list: 'Etats Centres' },
                    { fr: 'statut_fr', list: 'Statuts Centres' },
                ];

                listFields.forEach(({ fr, list }) => {
                    if (
                        centre[fr] &&
                        this.lists[list] &&
                        Array.isArray(this.lists[list])
                    ) {
                        const option = this.lists[list].find(
                            (opt) => opt.nom_fr === centre[fr]
                        );
                        this.form[fr] = option ? option.nom_fr : null;
                    } else {
                        this.form[fr] = null;
                    }
                });

                Object.keys(this.form).forEach((key) => {
                    if (!listFields.some((field) => field.fr === key)) {
                        if (
                            [
                                'date_creation',
                                'date_ouverture',
                                'date_fermeture',
                            ].includes(key)
                        ) {
                            this.form[key] = centre[key]
                                ? new Date(centre[key])
                                : null;
                        } else if (key === 'logo') {
                            this.logoPreview = centre[key] || null;
                            this.form.logo = null;
                        } else {
                            this.form[key] = centre[key] || '';
                        }
                    }
                });

                this.$forceUpdate();
            } catch (error) {
                console.error(
                    'Erreur lors de la récupération du centre:',
                    error
                );
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        'Erreur lors du chargement du centre.',
                    life: 5000,
                });
            }
        },
        handleFileChange(event) {
            const file = event.target.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    this.errors.logo = "L'image ne doit pas dépasser 2 Mo.";
                    return;
                }
                if (!['image/png', 'image/jpeg'].includes(file.type)) {
                    this.errors.logo =
                        'Seuls les formats PNG et JPEG sont autorisés.';
                    return;
                }
                this.logoFile = file;
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.logoPreview = e.target.result;
                    this.errors.logo = null;
                };
                reader.readAsDataURL(file);
            }
        },
        clearLogo() {
            this.logoFile = null;
            this.logoPreview = null;
            this.form.logo = null;
        },
        async submitForm() {
            this.errors = {};
            this.isSubmitting = true;

            try {
                const formData = new FormData();
                Object.keys(this.form).forEach((key) => {
                    if (
                        [
                            'date_creation',
                            'date_ouverture',
                            'date_fermeture',
                        ].includes(key)
                    ) {
                        if (this.form[key]) {
                            formData.append(
                                key,
                                new Date(this.form[key])
                                    .toISOString()
                                    .split('T')[0]
                            );
                        }
                    } else if (key !== 'logo') {
                        formData.append(key, this.form[key] || '');
                    }
                });
                if (this.logoFile) {
                    formData.append('logo', this.logoFile);
                }

                let response;
                if (this.isEditMode) {
                    response = await axios.put(
                        `/api/centres/${this.centreId}`,
                        formData,
                        {
                            headers: { 'Content-Type': 'multipart/form-data' },
                        }
                    );
                    this.$emit('update', response.data);
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Centre mis à jour avec succès.',
                        life: 3000,
                    });
                } else {
                    response = await axios.post('/api/centres', formData, {
                        headers: { 'Content-Type': 'multipart/form-data' },
                    });
                    this.$emit('save', response.data);
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Centre créé avec succès.',
                        life: 3000,
                    });
                }
                this.onUpdateVisible(false);
            } catch (error) {
                if (error.response && error.response.status === 422) {
                    this.errors = error.response.data.errors || {};
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'Veuillez corriger les erreurs dans le formulaire.',
                        life: 3000,
                    });
                } else {
                    console.error(
                        "Erreur lors de l'enregistrement du centre:",
                        error
                    );
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail:
                            error.response?.data?.message ||
                            "Erreur serveur lors de l'enregistrement du centre.",
                        life: 5000,
                    });
                }
            } finally {
                this.isSubmitting = false;
            }
        },
        onUpdateVisible(value) {
            this.$emit('update:visible', value);
            if (!value) {
                this.resetForm();
                this.$emit('close');
            }
        },
        resetForm() {
            this.form = {
                code: '',
                nom_fr: '',
                nom_ar: '',
                adresse_fr: '',
                adresse_ar: '',
                telephone_1: '',
                telephone_2: '',
                fax_1: '',
                fax_2: '',
                email: '',
                gouvernorat_fr: null,
                type_centre_fr: null,
                classe_fr: null,
                economat_fr: null,
                etat_fr: null,
                statut_fr: null,
                date_creation: null,
                date_ouverture: null,
                date_fermeture: null,
                observation_fr: '',
                observation_ar: '',
                logo: null,
            };
            this.logoFile = null;
            this.logoPreview = null;
            this.errors = {};
            this.isSubmitting = false;
            this.currentStep = 0;
            this.$forceUpdate();
        },
        nextStep(nextCallback) {
            this.errors = {};
            const requiredFields = [
                'code',
                'nom_fr',
                'adresse_fr',
                'telephone_1',
                'email',
                'gouvernorat_fr',
                'economat_fr',
            ];
            let hasError = false;

            requiredFields.forEach((field) => {
                if (!this.form[field]) {
                    this.errors[field] = 'Ce champ est requis.';
                    hasError = true;
                }
            });

            if (!hasError) {
                nextCallback();
            } else {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Veuillez remplir tous les champs obligatoires.',
                    life: 3000,
                });
            }
        },
        previousStep(prevCallback) {
            prevCallback();
        },
    },
};
</script>

<style scoped>
.p-stepper {
    flex-basis: 100%;
}

:deep(.p-button) {
    max-width: 200px;
    padding: 0.8rem 1rem;
    font-size: 0.875rem;
}

:deep(.p-inputtext::placeholder),
:deep(.p-dropdown .p-placeholder),
:deep(.p-calendar .p-inputtext::placeholder) {
    color: var(--text-color-secondary);
    opacity: 0.5;
    font-weight: 200;
}

.p-error {
    font-size: 0.75rem;
    margin-top: 0.25rem;
}

:deep(.p-calendar .p-inputtext) {
    border: none;
    padding: 0.75rem;
}

:deep(.p-calendar .p-button) {
    border-radius: 0.25rem;
}

.grid {
    margin: 2rem;
    padding: 1rem;
}

:deep(.p-inputtext),
:deep(.p-dropdown),
:deep(.p-textarea),
:deep(.p-calendar) {
    opacity: 1;
    border-radius: 0.25rem;
}

label.font-medium {
    color: #a9b6c2;
}
</style>
