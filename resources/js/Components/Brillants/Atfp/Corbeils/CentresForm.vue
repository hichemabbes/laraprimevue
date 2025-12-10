<template>
    <Dialog
        :visible="visible"
        :header="isEditMode ? 'Modifier le centre' : 'Créer un centre'"
        :modal="true"
        :style="{ width: '80vw', maxWidth: '1200px' }"
        @update:visible="onUpdateVisible"
    >
        <div class="surface-ground p-4">
            <form @submit.prevent="submitForm" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Code -->
                    <div>
                        <label
                            for="code"
                            class="block text-sm font-medium text-gray-700"
                            >Code *</label
                        >
                        <InputText
                            id="code"
                            v-model="form.code"
                            type="text"
                            class="mt-1 w-full"
                            :class="{ 'p-invalid': errors.code }"
                            maxlength="20"
                        />
                        <small v-if="errors.code" class="p-error">{{
                            errors.code
                        }}</small>
                    </div>

                    <!-- Nom (AR) -->
                    <div>
                        <label
                            for="nom_ar"
                            class="block text-sm font-medium text-gray-700"
                            >Nom (Arabe) *</label
                        >
                        <InputText
                            id="nom_ar"
                            v-model="form.nom_ar"
                            type="text"
                            class="mt-1 w-full"
                            :class="{ 'p-invalid': errors.nom_ar }"
                            maxlength="255"
                        />
                        <small v-if="errors.nom_ar" class="p-error">{{
                            errors.nom_ar
                        }}</small>
                    </div>

                    <!-- Nom (FR) -->
                    <div>
                        <label
                            for="nom_fr"
                            class="block text-sm font-medium text-gray-700"
                            >Nom (Français)</label
                        >
                        <InputText
                            id="nom_fr"
                            v-model="form.nom_fr"
                            type="text"
                            class="mt-1 w-full"
                            maxlength="255"
                        />
                    </div>

                    <!-- Adresse (FR) -->
                    <div>
                        <label
                            for="adresse_fr"
                            class="block text-sm font-medium text-gray-700"
                            >Adresse (Français)</label
                        >
                        <InputText
                            id="adresse_fr"
                            v-model="form.adresse_fr"
                            type="text"
                            class="mt-1 w-full"
                            maxlength="255"
                        />
                    </div>

                    <!-- Adresse (AR) -->
                    <div>
                        <label
                            for="adresse_ar"
                            class="block text-sm font-medium text-gray-700"
                            >Adresse (Arabe)</label
                        >
                        <InputText
                            id="adresse_ar"
                            v-model="form.adresse_ar"
                            type="text"
                            class="mt-1 w-full"
                            maxlength="255"
                        />
                    </div>

                    <!-- Téléphone 1 -->
                    <div>
                        <label
                            for="telephone_1"
                            class="block text-sm font-medium text-gray-700"
                            >Téléphone 1</label
                        >
                        <InputText
                            id="telephone_1"
                            v-model="form.telephone_1"
                            type="text"
                            class="mt-1 w-full"
                            :class="{ 'p-invalid': errors.telephone_1 }"
                            maxlength="20"
                        />
                        <small v-if="errors.telephone_1" class="p-error">{{
                            errors.telephone_1
                        }}</small>
                    </div>

                    <!-- Téléphone 2 -->
                    <div>
                        <label
                            for="telephone_2"
                            class="block text-sm font-medium text-gray-700"
                            >Téléphone 2</label
                        >
                        <InputText
                            id="telephone_2"
                            v-model="form.telephone_2"
                            type="text"
                            class="mt-1 w-full"
                            :class="{ 'p-invalid': errors.telephone_2 }"
                            maxlength="20"
                        />
                        <small v-if="errors.telephone_2" class="p-error">{{
                            errors.telephone_2
                        }}</small>
                    </div>

                    <!-- Fax 1 -->
                    <div>
                        <label
                            for="fax_1"
                            class="block text-sm font-medium text-gray-700"
                            >Fax 1</label
                        >
                        <InputText
                            id="fax_1"
                            v-model="form.fax_1"
                            type="text"
                            class="mt-1 w-full"
                            :class="{ 'p-invalid': errors.fax_1 }"
                            maxlength="20"
                        />
                        <small v-if="errors.fax_1" class="p-error">{{
                            errors.fax_1
                        }}</small>
                    </div>

                    <!-- Fax 2 -->
                    <div>
                        <label
                            for="fax_2"
                            class="block text-sm font-medium text-gray-700"
                            >Fax 2</label
                        >
                        <InputText
                            id="fax_2"
                            v-model="form.fax_2"
                            type="text"
                            class="mt-1 w-full"
                            :class="{ 'p-invalid': errors.fax_2 }"
                            maxlength="20"
                        />
                        <small v-if="errors.fax_2" class="p-error">{{
                            errors.fax_2
                        }}</small>
                    </div>

                    <!-- Email -->
                    <div>
                        <label
                            for="email"
                            class="block text-sm font-medium text-gray-700"
                            >Email</label
                        >
                        <InputText
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="mt-1 w-full"
                            :class="{ 'p-invalid': errors.email }"
                            maxlength="255"
                        />
                        <small v-if="errors.email" class="p-error">{{
                            errors.email
                        }}</small>
                    </div>

                    <!-- Gouvernorat -->
                    <div>
                        <label
                            for="gouvernorat_fr"
                            class="block text-sm font-medium text-gray-700"
                            >Gouvernorat</label
                        >
                        <MultiSelect
                            id="gouvernorat_fr"
                            v-model="form.gouvernorat_fr"
                            :options="lists.Gouvernorats || []"
                            option-label="nom_fr"
                            option-value="valeur"
                            placeholder="Sélectionner un gouvernorat"
                            class="mt-1 w-full"
                            :class="{ 'p-invalid': errors.gouvernorat_fr }"
                            filter
                        />
                        <small v-if="errors.gouvernorat_fr" class="p-error">{{
                            errors.gouvernorat_fr
                        }}</small>
                        <small
                            v-if="
                                !lists.Gouvernorats ||
                                lists.Gouvernorats.length === 0
                            "
                            class="p-error"
                            >Aucune option de gouvernorat disponible.</small
                        >
                    </div>

                    <!-- Type de centre -->
                    <div>
                        <label
                            for="type_centre_fr"
                            class="block text-sm font-medium text-gray-700"
                            >Type de centre</label
                        >
                        <MultiSelect
                            id="type_centre_fr"
                            v-model="form.type_centre_fr"
                            :options="lists['Types de centre'] || []"
                            option-label="nom_fr"
                            option-value="valeur"
                            placeholder="Sélectionner un type de centre"
                            class="mt-1 w-full"
                            :class="{ 'p-invalid': errors.type_centre_fr }"
                            filter
                        />
                        <small v-if="errors.type_centre_fr" class="p-error">{{
                            errors.type_centre_fr
                        }}</small>
                        <small
                            v-if="
                                !lists['Types de centre'] ||
                                lists['Types de centre'].length === 0
                            "
                            class="p-error"
                            >Aucune option de type de centre disponible.</small
                        >
                    </div>

                    <!-- Classe -->
                    <div>
                        <label
                            for="classe_fr"
                            class="block text-sm font-medium text-gray-700"
                            >Classe</label
                        >
                        <MultiSelect
                            id="classe_fr"
                            v-model="form.classe_fr"
                            :options="lists['Classes centres'] || []"
                            option-label="nom_fr"
                            option-value="valeur"
                            placeholder="Sélectionner une classe"
                            class="mt-1 w-full"
                            :class="{ 'p-invalid': errors.classe_fr }"
                            filter
                        />
                        <small v-if="errors.classe_fr" class="p-error">{{
                            errors.classe_fr
                        }}</small>
                        <small
                            v-if="
                                !lists['Classes centres'] ||
                                lists['Classes centres'].length === 0
                            "
                            class="p-error"
                            >Aucune option de classe disponible.</small
                        >
                    </div>

                    <!-- Économat -->
                    <div>
                        <label
                            for="economat_fr"
                            class="block text-sm font-medium text-gray-700"
                            >Économat</label
                        >
                        <MultiSelect
                            id="economat_fr"
                            v-model="form.economat_fr"
                            :options="lists.Economats || []"
                            option-label="nom_fr"
                            option-value="valeur"
                            placeholder="Sélectionner un économat"
                            class="mt-1 w-full"
                            :class="{ 'p-invalid': errors.economat_fr }"
                            filter
                        />
                        <small v-if="errors.economat_fr" class="p-error">{{
                            errors.economat_fr
                        }}</small>
                        <small
                            v-if="
                                !lists.Economats || lists.Economats.length === 0
                            "
                            class="p-error"
                            >Aucune option d'économat disponible.</small
                        >
                    </div>

                    <!-- État -->
                    <div>
                        <label
                            for="etat_fr"
                            class="block text-sm font-medium text-gray-700"
                            >État</label
                        >
                        <MultiSelect
                            id="etat_fr"
                            v-model="form.etat_fr"
                            :options="lists['État du centre'] || []"
                            option-label="nom_fr"
                            option-value="valeur"
                            placeholder="Sélectionner un état"
                            class="mt-1 w-full"
                            :class="{ 'p-invalid': errors.etat_fr }"
                            filter
                        />
                        <small v-if="errors.etat_fr" class="p-error">{{
                            errors.etat_fr
                        }}</small>
                        <small
                            v-if="
                                !lists['État du centre'] ||
                                lists['État du centre'].length === 0
                            "
                            class="p-error"
                            >Aucune option d'état disponible.</small
                        >
                    </div>

                    <!-- Statut -->
                    <div>
                        <label
                            for="statut_fr"
                            class="block text-sm font-medium text-gray-700"
                            >Statut</label
                        >
                        <MultiSelect
                            id="statut_fr"
                            v-model="form.statut_fr"
                            :options="lists['Statut du centre'] || []"
                            option-label="nom_fr"
                            option-value="valeur"
                            placeholder="Sélectionner un statut"
                            class="mt-1 w-full"
                            :class="{ 'p-invalid': errors.statut_fr }"
                            filter
                        />
                        <small v-if="errors.statut_fr" class="p-error">{{
                            errors.statut_fr
                        }}</small>
                        <small
                            v-if="
                                !lists['Statut du centre'] ||
                                lists['Statut du centre'].length === 0
                            "
                            class="p-error"
                            >Aucune option de statut disponible.</small
                        >
                    </div>

                    <!-- Date de création -->
                    <div>
                        <label
                            for="date_creation"
                            class="block text-sm font-medium text-gray-700"
                            >Date de création</label
                        >
                        <InputText
                            id="date_creation"
                            v-model="form.date_creation"
                            type="date"
                            class="mt-1 w-full"
                        />
                    </div>

                    <!-- Date d'ouverture -->
                    <div>
                        <label
                            for="date_ouverture"
                            class="block text-sm font-medium text-gray-700"
                            >Date d'ouverture</label
                        >
                        <InputText
                            id="date_ouverture"
                            v-model="form.date_ouverture"
                            type="date"
                            class="mt-1 w-full"
                            :class="{ 'p-invalid': errors.date_ouverture }"
                        />
                        <small v-if="errors.date_ouverture" class="p-error">{{
                            errors.date_ouverture
                        }}</small>
                    </div>

                    <!-- Date de fermeture -->
                    <div>
                        <label
                            for="date_fermeture"
                            class="block text-sm font-medium text-gray-700"
                            >Date de fermeture</label
                        >
                        <InputText
                            id="date_fermeture"
                            v-model="form.date_fermeture"
                            type="date"
                            class="mt-1 w-full"
                            :class="{ 'p-invalid': errors.date_fermeture }"
                        />
                        <small v-if="errors.date_fermeture" class="p-error">{{
                            errors.date_fermeture
                        }}</small>
                    </div>

                    <!-- Logo -->
                    <div>
                        <label
                            for="logo"
                            class="block text-sm font-medium text-gray-700"
                            >Logo (PNG/JPEG, max 2MB)</label
                        >
                        <input
                            id="logo"
                            type="file"
                            accept="image/png,image/jpeg"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                            :class="{ 'border-red-500': errors.logo }"
                            @change="handleFileChange"
                        />
                        <small v-if="errors.logo" class="p-error">{{
                            errors.logo
                        }}</small>
                        <div v-if="form.logo" class="mt-2">
                            <img
                                :src="form.logo"
                                alt="Prévisualisation du logo"
                                class="h-20 w-20 object-contain"
                            />
                            <Button
                                label="Supprimer le logo"
                                class="mt-2 p-button-text p-button-danger"
                                @click="form.logo = null"
                            />
                        </div>
                    </div>
                </div>

                <!-- Observation (FR) -->
                <div>
                    <label
                        for="observation_fr"
                        class="block text-sm font-medium text-gray-700"
                        >Observation (Français)</label
                    >
                    <Textarea
                        id="observation_fr"
                        v-model="form.observation_fr"
                        rows="4"
                        class="mt-1 w-full"
                    />
                </div>

                <!-- Observation (AR) -->
                <div>
                    <label
                        for="observation_ar"
                        class="block text-sm font-medium text-gray-700"
                        >Observation (Arabe)</label
                    >
                    <Textarea
                        id="observation_ar"
                        v-model="form.observation_ar"
                        rows="4"
                        class="mt-1 w-full"
                    />
                </div>

                <!-- Boutons Enregistrer et Annuler -->
                <div class="flex justify-end gap-4 mt-6">
                    <Button
                        label="Annuler"
                        icon="pi pi-times"
                        class="p-button-text p-button-secondary"
                        @click="onUpdateVisible(false)"
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
                        class="p-button-success"
                        :disabled="isSubmitting"
                        @click="submitForm"
                    />
                </div>
            </form>
        </div>
    </Dialog>

    <!-- Modale pour mot de passe (suppression) -->
    <Dialog
        v-model:visible="showPasswordModal"
        header="Confirmer la suppression"
        :modal="true"
        :style="{ width: '400px' }"
    >
        <div class="flex align-items-center gap-2">
            <i class="pi pi-exclamation-triangle text-red-500 text-2xl"></i>
            <span
                >Veuillez entrer le mot de passe pour confirmer la suppression
                du centre.</span
            >
        </div>
        <div class="mt-3">
            <label for="password" class="block mb-2">Mot de passe</label>
            <InputText
                id="password"
                v-model="password"
                type="password"
                class="w-full"
                :class="{ 'p-invalid': errors.password }"
            />
            <small v-if="errors.password" class="p-error">{{
                errors.password
            }}</small>
        </div>
        <div class="flex justify-end gap-4 mt-4">
            <Button
                label="Annuler"
                icon="pi pi-times"
                class="p-button-text p-button-secondary"
                @click="showPasswordModal = false"
            />
            <Button
                :label="isSubmitting ? 'Suppression...' : 'Supprimer'"
                icon="pi pi-check"
                class="p-button-danger"
                :disabled="isSubmitting"
                @click="deleteCentre"
            />
        </div>
    </Dialog>
</template>

<script>
import axios from 'axios';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import MultiSelect from 'primevue/multiselect';
import Textarea from 'primevue/textarea';
import { useToast } from 'primevue/usetoast';

export default {
    name: 'CentresForm',
    components: {
        Button,
        Dialog,
        InputText,
        MultiSelect,
        Textarea,
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
                gouvernorat_ar: '',
                type_centre_fr: null,
                type_centre_ar: '',
                classe_fr: null,
                classe_ar: '',
                economat_fr: null,
                economat_ar: '',
                logo: null,
                etat_fr: null,
                etat_ar: '',
                statut_fr: null,
                statut_ar: '',
                date_creation: '',
                date_ouverture: '',
                date_fermeture: '',
                observation_fr: '',
                observation_ar: '',
            },
            lists: {},
            errors: {},
            isSubmitting: false,
            showPasswordModal: false,
            password: '',
            isEditMode: !!this.centreId,
        };
    },
    watch: {
        centreId(newVal) {
            this.isEditMode = !!newVal;
            if (newVal) {
                this.fetchCentre();
            } else {
                this.resetForm();
            }
        },
        visible(newVal) {
            if (newVal) {
                this.fetchLists();
                if (this.isEditMode) {
                    this.fetchCentre();
                }
            }
        },
    },
    methods: {
        async fetchLists() {
            try {
                const response = await axios.get('/api/listes/options', {
                    params: {
                        lists: [
                            'Gouvernorats',
                            'Types de centre',
                            'Classes centres',
                            'Economats',
                            'État du centre',
                            'Statut du centre',
                        ].join(','),
                    },
                });
                this.lists = response.data;
                console.log('Réponse API /api/listes/options:', response.data);

                const requiredLists = [
                    'Gouvernorats',
                    'Types de centre',
                    'Classes centres',
                    'Economats',
                    'État du centre',
                    'Statut du centre',
                ];
                requiredLists.forEach((list) => {
                    if (!this.lists[list] || this.lists[list].length === 0) {
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
                        'Erreur serveur lors du chargement des listes. Veuillez vérifier la configuration backend.',
                    life: 5000,
                });
            }
        },
        async fetchCentre() {
            try {
                const response = await axios.get(
                    `/api/centres/${this.centreId}`
                );
                const centre = response.data;

                const listFields = [
                    { fr: 'gouvernorat_fr', list: 'Gouvernorats' },
                    { fr: 'type_centre_fr', list: 'Types de centre' },
                    { fr: 'classe_fr', list: 'Classes centres' },
                    { fr: 'economat_fr', list: 'Economats' },
                    { fr: 'etat_fr', list: 'État du centre' },
                    { fr: 'statut_fr', list: 'Statut du centre' },
                ];

                listFields.forEach(({ fr, list }) => {
                    if (centre[fr] && this.lists[list]) {
                        const option = this.lists[list].find(
                            (opt) => opt.nom_fr === centre[fr]
                        );
                        this.form[fr] = option ? option.valeur : null;
                    }
                });

                Object.keys(this.form).forEach((key) => {
                    if (
                        !listFields.some((field) => field.fr === key) &&
                        key !== 'gouvernorat_ar' &&
                        key !== 'type_centre_ar' &&
                        key !== 'classe_ar' &&
                        key !== 'economat_ar' &&
                        key !== 'etat_ar' &&
                        key !== 'statut_ar'
                    ) {
                        this.form[key] = centre[key] || '';
                    }
                });
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
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.form.logo = e.target.result;
                    this.errors.logo = null;
                };
                reader.readAsDataURL(file);
            }
        },
        async submitForm() {
            this.errors = {};
            this.isSubmitting = true;

            try {
                const payload = { ...this.form };
                let response;
                if (this.isEditMode) {
                    response = await axios.put(
                        `/api/centres/${this.centreId}`,
                        payload
                    );
                    this.$emit('update', response.data);
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Centre mis à jour avec succès.',
                        life: 3000,
                    });
                } else {
                    response = await axios.post('/api/centres', payload);
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
                        life: 3000,
                    });
                }
            } finally {
                this.isSubmitting = false;
            }
        },
        async deleteCentre() {
            this.errors = {};
            this.isSubmitting = true;

            try {
                await axios.delete(`/api/centres/${this.centreId}`, {
                    data: { password: this.password },
                });
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Centre supprimé avec succès.',
                    life: 3000,
                });
                this.showPasswordModal = false;
                this.onUpdateVisible(false);
                this.$emit('close');
            } catch (error) {
                if (error.response && error.response.status === 403) {
                    this.errors.password = 'Mot de passe incorrect.';
                } else {
                    console.error(
                        'Erreur lors de la suppression du centre:',
                        error
                    );
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail:
                            error.response?.data?.message ||
                            'Erreur lors de la suppression du centre.',
                        life: 3000,
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
                gouvernorat_ar: '',
                type_centre_fr: null,
                type_centre_ar: '',
                classe_fr: null,
                classe_ar: '',
                economat_fr: null,
                economat_ar: '',
                logo: null,
                etat_fr: null,
                etat_ar: '',
                statut_fr: null,
                statut_ar: '',
                date_creation: '',
                date_ouverture: '',
                date_fermeture: '',
                observation_fr: '',
                observation_ar: '',
            };
            this.errors = {};
            this.isSubmitting = false;
            this.showPasswordModal = false;
            this.password = '';
        },
    },
};
</script>

<style scoped>
.p-multiselect {
    width: 100%;
}
.p-multiselect.p-invalid {
    border-color: #ef4444;
}
</style>
