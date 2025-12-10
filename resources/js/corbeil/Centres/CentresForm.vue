<template>
    <Dialog
        :visible="visible"
        @update:visible="$emit('update:visible', $event)"
        modal
        :header="isEditing ? 'Modifier un Centre' : 'Ajouter un Centre'"
        :style="{ width: '50vw' }"
        :pt="{
            root: 'border-none bg-transparent',
            mask: {
                style: 'backdrop-filter: blur(2px); background-color: rgba(128, 128, 128, 0.2);',
            },
        }"
    >
        <template #container="{ closeCallback }">
            <div class="relative p-5 surface-card">
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

                <div class="flex flex-column gap-4">
                    <h5 class="text-center font-bold text-lg mt-3">
                        {{
                            isEditing
                                ? 'Mettre à jour un Centre de formation'
                                : 'Ajout un centre de formation'
                        }}
                    </h5>
                    <!-- Zone de défilement commence ici -->
                    <div class="form-scroll-container">
                        <div class="p-fluid form-content">
                            <div class="p-fluid">
                                <!-- 1st Line: Code / Classe / Type -->
                                <div class="grid">
                                    <div class="col-4">
                                        <label for="code" class="field-label"
                                            >Code</label
                                        >
                                        <InputText
                                            id="code"
                                            v-model="newCentre.code"
                                            @input="validateCode"
                                        />
                                        <small
                                            v-if="errors.code"
                                            class="p-error"
                                            >{{ errors.code }}</small
                                        >
                                    </div>
                                    <div class="col-4">
                                        <label for="classe" class="field-label"
                                            >Classe</label
                                        >
                                        <InputText
                                            id="classe"
                                            v-model="newCentre.classe"
                                            @input="validateClasse"
                                        />
                                        <small
                                            v-if="errors.classe"
                                            class="p-error"
                                            >{{ errors.classe }}</small
                                        >
                                    </div>
                                    <div class="col-4">
                                        <label for="type" class="field-label"
                                            >Type</label
                                        >
                                        <InputText
                                            id="type"
                                            v-model="newCentre.type"
                                            @input="validateType"
                                        />
                                        <small
                                            v-if="errors.type"
                                            class="p-error"
                                            >{{ errors.type }}</small
                                        >
                                    </div>
                                </div>

                                <!-- 2nd Line: Nom Ar -->
                                <div class="field">
                                    <label for="nom_ar" class="field-label"
                                        >Nom (AR)</label
                                    >
                                    <InputText
                                        id="nom_ar"
                                        v-model="newCentre.nom_ar"
                                        @input="validateNomAr"
                                    />
                                    <small
                                        v-if="errors.nom_ar"
                                        class="p-error"
                                        >{{ errors.nom_ar }}</small
                                    >
                                </div>

                                <!-- 3rd Line: Adresse Ar -->
                                <div class="field">
                                    <label for="adresse_ar" class="field-label"
                                        >Adresse (AR)</label
                                    >
                                    <InputText
                                        id="adresse_ar"
                                        v-model="newCentre.adresse_ar"
                                        @input="validateAdresseAr"
                                    />
                                    <small
                                        v-if="errors.adresse_ar"
                                        class="p-error"
                                        >{{ errors.adresse_ar }}</small
                                    >
                                </div>

                                <!-- 4th Line: Nom Fr -->
                                <div class="field">
                                    <label for="nom_fr" class="field-label"
                                        >Nom (FR)</label
                                    >
                                    <InputText
                                        id="nom_fr"
                                        v-model="newCentre.nom_fr"
                                        @input="validateNomFr"
                                    />
                                    <small
                                        v-if="errors.nom_fr"
                                        class="p-error"
                                        >{{ errors.nom_fr }}</small
                                    >
                                </div>

                                <!-- 5th Line: Adresse Fr -->
                                <div class="field">
                                    <label for="adresse_fr" class="field-label"
                                        >Adresse (FR)</label
                                    >
                                    <InputText
                                        id="adresse_fr"
                                        v-model="newCentre.adresse_fr"
                                        @input="validateAdresseFr"
                                    />
                                    <small
                                        v-if="errors.adresse_fr"
                                        class="p-error"
                                        >{{ errors.adresse_fr }}</small
                                    >
                                </div>

                                <!-- 6th Line: Tele 1 / Tel 2 / Fax / Email -->
                                <div class="grid">
                                    <div class="col-3">
                                        <label
                                            for="telephone_1"
                                            class="field-label"
                                            >Téléphone 1</label
                                        >
                                        <InputText
                                            id="telephone_1"
                                            v-model="newCentre.telephone_1"
                                            @input="validateTelephone1"
                                        />
                                        <small
                                            v-if="errors.telephone_1"
                                            class="p-error"
                                            >{{ errors.telephone_1 }}</small
                                        >
                                    </div>
                                    <div class="col-3">
                                        <label
                                            for="telephone_2"
                                            class="field-label"
                                            >Téléphone 2</label
                                        >
                                        <InputText
                                            id="telephone_2"
                                            v-model="newCentre.telephone_2"
                                            @input="validateTelephone2"
                                        />
                                        <small
                                            v-if="errors.telephone_2"
                                            class="p-error"
                                            >{{ errors.telephone_2 }}</small
                                        >
                                    </div>
                                    <div class="col-3">
                                        <label for="fax_1" class="field-label"
                                            >Fax</label
                                        >
                                        <InputText
                                            id="fax_1"
                                            v-model="newCentre.fax_1"
                                            @input="validateFax1"
                                        />
                                        <small
                                            v-if="errors.fax_1"
                                            class="p-error"
                                            >{{ errors.fax_1 }}</small
                                        >
                                    </div>
                                    <div class="col-3">
                                        <label for="email" class="field-label"
                                            >Email</label
                                        >
                                        <InputText
                                            id="email"
                                            v-model="newCentre.email"
                                            @input="validateEmail"
                                        />
                                        <small
                                            v-if="errors.email"
                                            class="p-error"
                                            >{{ errors.email }}</small
                                        >
                                    </div>
                                </div>

                                <!-- 7th Line: Economat Ar / Economat Fr -->
                                <div class="grid">
                                    <div class="col-4">
                                        <label
                                            for="gouvernerat_fr"
                                            class="field-label"
                                            >Gouvernorat</label
                                        >
                                        <Dropdown
                                            id="gouvernerat_fr"
                                            v-model="newCentre.gouvernerat_fr"
                                            :options="gouvernorats"
                                            placeholder="Sélectionner un gouvernorat"
                                            class="w-full"
                                            @change="validateGouvernerat"
                                        />
                                        <small
                                            v-if="errors.gouvernerat_fr"
                                            class="text-red-500"
                                            >{{ errors.gouvernerat_fr }}</small
                                        >
                                    </div>
                                    <div class="col-4">
                                        <label
                                            for="economat_ar"
                                            class="field-label"
                                            >Économat (AR)</label
                                        >
                                        <InputText
                                            id="economat_ar"
                                            v-model="newCentre.economat_ar"
                                            @input="validateEconomatAr"
                                        />
                                        <small
                                            v-if="errors.economat_ar"
                                            class="p-error"
                                            >{{ errors.economat_ar }}</small
                                        >
                                    </div>
                                    <div class="col-4">
                                        <label
                                            for="economat_fr"
                                            class="field-label"
                                            >Économat (FR)</label
                                        >
                                        <InputText
                                            id="economat_fr"
                                            v-model="newCentre.economat_fr"
                                            @input="validateEconomatFr"
                                        />
                                        <small
                                            v-if="errors.economat_fr"
                                            class="p-error"
                                            >{{ errors.economat_fr }}</small
                                        >
                                    </div>
                                </div>

                                <!-- 8th Line: Date Creation / Date Fermeture / Statut -->
                                <div class="grid">
                                    <div class="col-4">
                                        <label
                                            for="date_creation"
                                            class="field-label"
                                            >Date de création</label
                                        >
                                        <Calendar
                                            id="date_creation"
                                            v-model="newCentre.date_creation"
                                            dateFormat="dd/mm/yy"
                                            @update:modelValue="
                                                validateDateCreation
                                            "
                                        />
                                        <small
                                            v-if="errors.date_creation"
                                            class="p-error"
                                            >{{ errors.date_creation }}</small
                                        >
                                    </div>
                                    <div class="col-4">
                                        <label
                                            for="date_fermeture"
                                            class="field-label"
                                            >Date de fermeture</label
                                        >
                                        <Calendar
                                            id="date_fermeture"
                                            v-model="newCentre.date_fermeture"
                                            dateFormat="dd/mm/yy"
                                            @update:modelValue="
                                                validateDateFermeture
                                            "
                                        />
                                        <small
                                            v-if="errors.date_fermeture"
                                            class="p-error"
                                            >{{ errors.date_fermeture }}</small
                                        >
                                    </div>
                                    <div class="col-4">
                                        <label for="statut" class="field-label"
                                            >Statut</label
                                        >
                                        <Dropdown
                                            id="statut"
                                            v-model="newCentre.statut"
                                            :options="statuts"
                                            placeholder="Sélectionner un statut"
                                            @change="validateStatut"
                                        />
                                        <small
                                            v-if="errors.statut"
                                            class="p-error"
                                            >{{ errors.statut }}</small
                                        >
                                    </div>
                                </div>

                                <!-- 9th Line: Logo / Observation -->
                                <div class="grid">
                                    <div class="col-6">
                                        <label for="logo" class="field-label"
                                            >Logo</label
                                        >
                                        <div class="p-inputgroup">
                                            <InputText
                                                id="logo"
                                                v-model="newCentre.logo"
                                                @input="validateLogo"
                                                placeholder="URL ou téléchargez une image"
                                            />
                                            <Button
                                                icon="pi pi-upload"
                                                @click="$refs.logoInput.click()"
                                            />
                                        </div>
                                        <small
                                            v-if="errors.logo"
                                            class="p-error"
                                            >{{ errors.logo }}</small
                                        >
                                    </div>
                                    <div class="col-6">
                                        <label
                                            for="observation"
                                            class="field-label"
                                            >Observation</label
                                        >
                                        <Textarea
                                            id="observation"
                                            v-model="newCentre.observation"
                                            rows="2"
                                            @input="validateObservation"
                                        />
                                        <small
                                            v-if="errors.observation"
                                            class="p-error"
                                            >{{ errors.observation }}</small
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Zone de défilement termine ici -->
                    <div class="flex justify-content-between gap-3 mt-4">
                        <Button
                            label="Annuler"
                            severity="danger"
                            outlined
                            @click="closeCallback"
                            class="w-full"
                        />
                        <Button
                            :label="isEditing ? 'Modifier' : 'Enregistrer'"
                            severity="success"
                            outlined
                            @click="isEditing ? updateCentre() : saveCentre()"
                            :disabled="isSaving"
                            class="w-full"
                        />
                    </div>
                </div>
            </div>
        </template>
    </Dialog>

    <Toast />
    <input
        type="file"
        ref="logoInput"
        @change="handleLogoUpload"
        style="display: none"
        accept="image/*"
    />
</template>

<style scoped>
.field-label {
    display: block; /* Assure que le label est sur une ligne séparée */
    margin-bottom: 8px; /* Espacement vertical uniforme entre le label et le champ */
}
</style>

<script>
import { useToast } from 'primevue/usetoast';
import axios from 'axios';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Calendar from 'primevue/calendar';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';

export default {
    components: {
        InputText,
        Dropdown,
        Calendar,
        Textarea,
        Button,
        Dialog,
        Toast,
    },
    props: {
        visible: { type: Boolean, required: true },
        centreToEdit: { type: Object, default: null },
    },
    emits: ['update:visible', 'close', 'save', 'update'],
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            newCentre: {
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
                gouvernerat_fr: null,
                type: '',
                economat_fr: '',
                economat_ar: '',
                classe: '',
                logo: '',
                statut: null,
                date_creation: null,
                date_fermeture: null,
                observation: '',
            },
            errors: {
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
                gouvernerat_fr: '',
                type: '',
                economat_fr: '',
                economat_ar: '',
                classe: '',
                logo: '',
                statut: '',
                date_creation: '',
                date_fermeture: '',
                observation: '',
            },
            isSaving: false,
            gouvernorats: [
                'ariana',
                'beja',
                'ben_arous',
                'bizerte',
                'gabes',
                'gafsa',
                'jendouba',
                'kairouan',
                'kasserine',
                'kebili',
                'manouba',
                'kef',
                'mahdia',
                'medenine',
                'monastir',
                'nabeul',
                'sfax',
                'sidi_bouzid',
                'siliana',
                'sousse',
                'tataouine',
                'tozeur',
                'tunis',
                'zaghouan',
            ],
            statuts: [
                'Actif',
                'Inactif',
                'En construction',
                'En rénovation',
                'Fermé définitivement',
            ],
        };
    },
    computed: {
        isEditing() {
            return !!this.centreToEdit;
        },
    },
    watch: {
        centreToEdit: {
            immediate: true,
            handler(newVal) {
                if (newVal) {
                    this.newCentre = {
                        ...newVal,
                        date_creation: newVal.date_creation
                            ? new Date(newVal.date_creation)
                            : null,
                        date_fermeture: newVal.date_fermeture
                            ? new Date(newVal.date_fermeture)
                            : null,
                    };
                } else {
                    this.resetForm();
                }
            },
        },
        visible(newVal) {
            if (!newVal) {
                this.resetForm();
            }
        },
    },
    methods: {
        close() {
            this.resetForm();
            this.$emit('update:visible', false);
            this.$emit('close');
        },
        resetForm() {
            this.newCentre = {
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
                gouvernerat_fr: null,
                type: '',
                economat_fr: '',
                economat_ar: '',
                classe: '',
                logo: '',
                statut: null,
                date_creation: null,
                date_fermeture: null,
                observation: '',
            };
            this.errors = {
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
                gouvernerat_fr: '',
                type: '',
                economat_fr: '',
                economat_ar: '',
                classe: '',
                logo: '',
                statut: '',
                date_creation: '',
                date_fermeture: '',
                observation: '',
            };
        },
        formatDate(date) {
            if (!date) return null;
            const d = new Date(date);
            if (isNaN(d.getTime())) return null;
            const year = d.getFullYear();
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        },
        validateCode() {
            if (!this.newCentre.code)
                this.errors.code = 'Le code est obligatoire.';
            else if (this.newCentre.code.length > 100)
                this.errors.code =
                    'Le code ne doit pas dépasser 100 caractères.';
            else this.errors.code = '';
        },
        validateNomFr() {
            if (this.newCentre.nom_fr && this.newCentre.nom_fr.length > 100)
                this.errors.nom_fr =
                    'Le nom (FR) ne doit pas dépasser 100 caractères.';
            else this.errors.nom_fr = '';
        },
        validateNomAr() {
            if (this.newCentre.nom_ar && this.newCentre.nom_ar.length > 100)
                this.errors.nom_ar =
                    'Le nom (AR) ne doit pas dépasser 100 caractères.';
            else this.errors.nom_ar = '';
        },
        validateAdresseFr() {
            if (
                this.newCentre.adresse_fr &&
                this.newCentre.adresse_fr.length > 100
            )
                this.errors.adresse_fr =
                    'L’adresse (FR) ne doit pas dépasser 100 caractères.';
            else this.errors.adresse_fr = '';
        },
        validateAdresseAr() {
            if (
                this.newCentre.adresse_ar &&
                this.newCentre.adresse_ar.length > 100
            )
                this.errors.adresse_ar =
                    'L’adresse (AR) ne doit pas dépasser 100 caractères.';
            else this.errors.adresse_ar = '';
        },
        validateTelephone1() {
            if (this.newCentre.telephone_1) {
                const phoneRegex = /^\d{8}$/;
                if (!phoneRegex.test(this.newCentre.telephone_1))
                    this.errors.telephone_1 =
                        'Le téléphone 1 doit contenir exactement 8 chiffres.';
                else this.errors.telephone_1 = '';
            } else {
                this.errors.telephone_1 = '';
            }
        },
        validateTelephone2() {
            if (this.newCentre.telephone_2) {
                const phoneRegex = /^\d{8}$/;
                if (!phoneRegex.test(this.newCentre.telephone_2))
                    this.errors.telephone_2 =
                        'Le téléphone 2 doit contenir exactement 8 chiffres.';
                else this.errors.telephone_2 = '';
            } else {
                this.errors.telephone_2 = '';
            }
        },
        validateFax1() {
            if (this.newCentre.fax_1) {
                const faxRegex = /^\d{8}$/;
                if (!faxRegex.test(this.newCentre.fax_1))
                    this.errors.fax_1 =
                        'Le fax 1 doit contenir exactement 8 chiffres.';
                else this.errors.fax_1 = '';
            } else {
                this.errors.fax_1 = '';
            }
        },
        validateFax2() {
            if (this.newCentre.fax_2) {
                const faxRegex = /^\d{8}$/;
                if (!faxRegex.test(this.newCentre.fax_2))
                    this.errors.fax_2 =
                        'Le fax 2 doit contenir exactement 8 chiffres.';
                else this.errors.fax_2 = '';
            } else {
                this.errors.fax_2 = '';
            }
        },
        validateEmail() {
            if (this.newCentre.email) {
                const emailRegex =
                    /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
                if (!emailRegex.test(this.newCentre.email))
                    this.errors.email = 'L’email doit être une adresse valide.';
                else if (this.newCentre.email.length > 100)
                    this.errors.email =
                        'L’email ne doit pas dépasser 100 caractères.';
                else this.errors.email = '';
            } else {
                this.errors.email = '';
            }
        },
        validateGouvernerat() {
            if (!this.newCentre.gouvernerat_fr)
                this.errors.gouvernerat_fr = 'Le gouvernorat est obligatoire.';
            else this.errors.gouvernerat_fr = '';
        },
        validateType() {
            if (this.newCentre.type && this.newCentre.type.length > 100)
                this.errors.type =
                    'Le type ne doit pas dépasser 100 caractères.';
            else this.errors.type = '';
        },
        validateEconomatFr() {
            if (
                this.newCentre.economat_fr &&
                this.newCentre.economat_fr.length > 100
            )
                this.errors.economat_fr =
                    'L’économat (FR) ne doit pas dépasser 100 caractères.';
            else this.errors.economat_fr = '';
        },
        validateEconomatAr() {
            if (
                this.newCentre.economat_ar &&
                this.newCentre.economat_ar.length > 100
            )
                this.errors.economat_ar =
                    'L’économat (AR) ne doit pas dépasser 100 caractères.';
            else this.errors.economat_ar = '';
        },
        validateClasse() {
            if (this.newCentre.classe && this.newCentre.classe.length > 100)
                this.errors.classe =
                    'La classe ne doit pas dépasser 100 caractères.';
            else this.errors.classe = '';
        },
        validateLogo() {
            if (this.newCentre.logo && this.newCentre.logo.length > 255)
                this.errors.logo =
                    'L’URL du logo ne doit pas dépasser 255 caractères.';
            else this.errors.logo = '';
        },
        validateStatut() {
            if (!this.newCentre.statut)
                this.errors.statut = 'Le statut est obligatoire.';
            else this.errors.statut = '';
        },
        validateDateCreation() {
            if (
                this.newCentre.date_creation &&
                isNaN(new Date(this.newCentre.date_creation).getTime())
            ) {
                this.errors.date_creation = 'Date de création invalide.';
            } else {
                this.errors.date_creation = '';
            }
        },
        validateDateFermeture() {
            if (
                this.newCentre.date_fermeture &&
                isNaN(new Date(this.newCentre.date_fermeture).getTime())
            ) {
                this.errors.date_fermeture = 'Date de fermeture invalide.';
            } else if (
                this.newCentre.date_creation &&
                this.newCentre.date_fermeture &&
                new Date(this.newCentre.date_fermeture) <
                    new Date(this.newCentre.date_creation)
            ) {
                this.errors.date_fermeture =
                    'La date de fermeture doit être postérieure à la date de création.';
            } else {
                this.errors.date_fermeture = '';
            }
        },
        validateObservation() {
            if (
                this.newCentre.observation &&
                this.newCentre.observation.length > 65535
            )
                this.errors.observation =
                    'L’observation ne doit pas dépasser 65 535 caractères.';
            else this.errors.observation = '';
        },
        async saveCentre() {
            this.isSaving = true;
            this.validateAll();

            if (Object.values(this.errors).some((error) => error !== '')) {
                this.isSaving = false;
                return;
            }

            const payload = {
                ...this.newCentre,
                date_creation: this.formatDate(this.newCentre.date_creation),
                date_fermeture: this.formatDate(this.newCentre.date_fermeture),
            };
            this.$emit('save', payload);
            this.isSaving = false;
        },
        async updateCentre() {
            this.validateAll();

            if (Object.values(this.errors).some((error) => error !== '')) {
                return;
            }

            const payload = {
                ...this.newCentre,
                date_creation: this.formatDate(this.newCentre.date_creation),
                date_fermeture: this.formatDate(this.newCentre.date_fermeture),
            };
            this.$emit('update', payload);
            this.close();
        },
        validateAll() {
            this.validateCode();
            this.validateNomFr();
            this.validateNomAr();
            this.validateAdresseFr();
            this.validateAdresseAr();
            this.validateTelephone1();
            this.validateTelephone2();
            this.validateFax1();
            this.validateFax2();
            this.validateEmail();
            this.validateGouvernerat();
            this.validateType();
            this.validateEconomatFr();
            this.validateEconomatAr();
            this.validateClasse();
            this.validateLogo();
            this.validateStatut();
            this.validateDateCreation();
            this.validateDateFermeture();
            this.validateObservation();
        },
        handleLogoUpload(event) {
            const file = event.target.files[0];
            if (file) {
                const formData = new FormData();
                formData.append('logo', file);
                axios
                    .post('/api/upload-logo', formData, {
                        headers: { 'Content-Type': 'multipart/form-data' },
                    })
                    .then((response) => {
                        this.newCentre.logo = response.data.url;
                        this.toast.add({
                            severity: 'success',
                            summary: 'Succès',
                            detail: 'Logo téléchargé avec succès.',
                            life: 3000,
                        });
                    })
                    .catch((error) => {
                        this.errors.logo =
                            'Erreur lors du téléchargement du logo.';
                        this.toast.add({
                            severity: 'error',
                            summary: 'Erreur',
                            detail: 'Erreur lors du téléchargement.',
                            life: 3000,
                        });
                    });
            }
        },
    },
};
</script>

<style scoped>
/* Styles pour la zone de défilement */
.form-scroll-container {
    max-height: 60vh;
    overflow-y: auto;
    overflow-x: hidden;
    margin: 1rem 0;
    padding-right: 0.5rem;
}

.form-content {
    padding-right: 1rem; /* Espace pour la barre de défilement */
}

/* Style de la barre de défilement */
.form-scroll-container::-webkit-scrollbar {
    width: 8px;
}

.form-scroll-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.form-scroll-container::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

.form-scroll-container::-webkit-scrollbar-thumb:hover {
    background: #555;
}

.field-label {
    display: block;
    margin-bottom: 8px;
}
</style>
