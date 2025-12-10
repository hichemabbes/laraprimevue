```vue
<template>
    <Dialog
        :visible="visible"
        @update:visible="$emit('update:visible', $event)"
        :modal="true"
        :style="{ width: '600px' }"
        :breakpoints="{ '960px': '75vw', '640px': '90vw' }"
        :pt="dialogPt"
    >
        <template #header>
            <div class="flex align-items-center gap-2">
                <i
                    :class="isEditing ? 'pi pi-pencil' : 'pi pi-plus'"
                    class="text-primary-500 text-2xl"
                ></i>
                <span class="font-bold text-lg">
                    {{ isEditing ? "Modifier l'association" : `Associer des spécialités au centre : ${centreToEdit?.nom_fr || ''}` }}
                </span>
            </div>
        </template>

        <div class="flex flex-column gap-4">
            <!-- Filter Buttons -->
            <div class="flex gap-2">
                <Button
                    label="جديد"
                    :severity="selectedFilter === 'جديد' ? 'warning' : 'secondary'"
                    class="arabic-normal"
                    @click="setFilter('جديد')"
                    :disabled="loading"
                />
                <Button
                    label="مقيس"
                    :severity="selectedFilter === 'مقيس' ? 'success' : 'secondary'"
                    class="arabic-normal"
                    @click="setFilter('مقيس')"
                    :disabled="loading"
                />
                <Button
                    label="غير مقيس"
                    :severity="selectedFilter === 'غير مقيس' ? 'danger' : 'secondary'"
                    class="arabic-normal"
                    @click="setFilter('غير مقيس')"
                    :disabled="loading"
                />
            </div>

            <div class="grid formgrid">
                <!-- Secteur -->
                <div class="col-12 field" v-if="['غير مقيس', 'مقيس'].includes(selectedFilter) && !isEditing">
                    <label for="secteur_id" class="block font-medium mb-2">
                        Secteur <span class="text-red-500">*</span>
                    </label>
                    <span v-if="loading" class="pi pi-spin pi-spinner"></span>
                    <Dropdown
                        id="secteur_id"
                        v-model="form.secteur_id"
                        :options="filteredSecteurs"
                        optionLabel="nom_fr"
                        optionValue="id"
                        placeholder="Sélectionner un secteur"
                        class="w-full"
                        :class="{ 'p-invalid': errors.secteur_id }"
                        @change="onSecteurChange"
                        :disabled="loading"
                    />
                    <small v-if="errors.secteur_id" class="p-error">{{ errors.secteur_id }}</small>
                    <small v-else-if="!filteredSecteurs.length && !loading" class="p-error">
                        Aucun secteur disponible pour ce filtre.
                    </small>
                </div>

                <!-- Sous-secteur -->
                <div class="col-12 field" v-if="selectedFilter === 'مقيس' && !isEditing">
                    <label for="sous_secteur_id" class="block font-medium mb-2">
                        Sous-secteur <span class="text-red-500">*</span>
                    </label>
                    <span v-if="loading" class="pi pi-spin pi-spinner"></span>
                    <Dropdown
                        id="sous_secteur_id"
                        v-model="form.sous_secteur_id"
                        :options="filteredSousSecteurs"
                        optionLabel="nom_fr"
                        optionValue="id"
                        placeholder="Sélectionner un sous-secteur"
                        class="w-full"
                        :class="{ 'p-invalid': errors.sous_secteur_id }"
                        @change="onSousSecteurChange"
                        :disabled="loading"
                    />
                    <small v-if="errors.sous_secteur_id" class="p-error">{{ errors.sous_secteur_id }}</small>
                    <small v-else-if="!filteredSousSecteurs.length && !loading" class="p-error">
                        Aucun sous-secteur disponible pour ce secteur.
                    </small>
                </div>

                <!-- Spécialités -->
                <div class="col-12 field">
                    <label for="specialite_ids" class="block font-medium mb-2">
                        Spécialités <span class="text-red-500">*</span>
                    </label>
                    <MultiSelect
                        id="specialite_ids"
                        v-model="form.specialite_ids"
                        :options="filteredSpecialites"
                        optionLabel="nom_fr"
                        optionValue="id"
                        placeholder="Sélectionner des spécialités"
                        class="w-full"
                        :class="{ 'p-invalid': errors.specialite_ids }"
                        @change="validateSpecialiteIds"
                        :disabled="loading || isEditing"
                    />
                    <small v-if="errors.specialite_ids" class="p-error">{{ errors.specialite_ids }}</small>
                    <small v-else-if="!filteredSpecialites.length && !loading" class="p-error">
                        Aucune spécialité disponible pour le filtre {{ selectedFilter }}. Vérifiez les données de standardisation.
                    </small>
                </div>

                <!-- Statut -->
                <div class="col-12 field">
                    <label class="block font-medium mb-2">
                        Statut <span class="text-red-500">*</span>
                    </label>
                    <div class="flex gap-4">
                        <div class="flex align-items-center">
                            <RadioButton
                                v-model="form.statut"
                                inputId="statut_actif"
                                name="statut"
                                value="Actif"
                                @change="validateStatut"
                            />
                            <label for="statut_actif" class="ml-2">Actif</label>
                        </div>
                        <div class="flex align-items-center">
                            <RadioButton
                                v-model="form.statut"
                                inputId="statut_inactif"
                                name="statut"
                                value="Inactif"
                                @change="validateStatut"
                            />
                            <label for="statut_inactif" class="ml-2">Inactif</label>
                        </div>
                    </div>
                    <small v-if="errors.statut" class="p-error">{{ errors.statut }}</small>
                </div>

                <!-- Dates -->
                <div class="col-12 field">
                    <label class="block font-medium mb-2">
                        Période d'association <span class="text-red-500">*</span>
                    </label>
                    <div class="grid">
                        <div class="col-12 md:col-6 field">
                            <label for="date_debut" class="block font-medium mb-2">Date début</label>
                            <Calendar
                                id="date_debut"
                                v-model="form.date_debut"
                                dateFormat="dd/mm/yy"
                                class="w-full"
                                :class="{ 'p-invalid': errors.date_debut }"
                                @update:modelValue="validateDateDebut"
                            />
                            <small v-if="errors.date_debut" class="p-error">{{ errors.date_debut }}</small>
                        </div>
                        <div class="col-12 md:col-6 field">
                            <label for="date_fin" class="block font-medium mb-2">Date fin</label>
                            <Calendar
                                id="date_fin"
                                v-model="form.date_fin"
                                dateFormat="dd/mm/yy"
                                class="w-full"
                                :class="{ 'p-invalid': errors.date_fin }"
                                @update:modelValue="validateDateFin"
                            />
                            <small v-if="errors.date_fin" class="p-error">{{ errors.date_fin }}</small>
                        </div>
                    </div>
                </div>

                <!-- Observation -->
                <div class="col-12 field">
                    <label for="observation" class="block font-medium mb-2">Observation</label>
                    <Textarea
                        id="observation"
                        v-model="form.observation"
                        class="w-full"
                        rows="4"
                        autoResize
                        @input="validateObservation"
                    />
                    <small v-if="errors.observation" class="p-error">{{ errors.observation }}</small>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="flex justify-content-end gap-2">
                <Button
                    label="Annuler"
                    icon="pi pi-times"
                    class="p-button-text p-button-secondary"
                    @click="close"
                    :disabled="isSaving"
                />
                <Button
                    :label="isEditing ? 'Modifier' : 'Enregistrer'"
                    :icon="isEditing ? 'pi pi-check' : 'pi pi-save'"
                    class="p-button-primary"
                    :loading="isSaving"
                    @click="submitForm"
                />
            </div>
        </template>

        <Toast position="top-right" />
    </Dialog>
</template>

<script>
import { useToast } from 'primevue/usetoast';
import axios from 'axios';
import Dropdown from 'primevue/dropdown';
import MultiSelect from 'primevue/multiselect';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import Calendar from 'primevue/calendar';
import RadioButton from 'primevue/radiobutton';

export default {
    components: {
        Dropdown,
        MultiSelect,
        Textarea,
        Button,
        Dialog,
        Toast,
        Calendar,
        RadioButton,
    },
    props: {
        visible: { type: Boolean, required: true },
        centreToEdit: { type: Object, default: null },
        specialiteToEdit: { type: Object, default: null },
    },
    emits: ['update:visible', 'close', 'save', 'update'],
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            form: {
                secteur_id: null,
                sous_secteur_id: null,
                specialite_ids: [],
                statut: 'Actif',
                date_debut: new Date(),
                date_fin: null,
                observation: '',
            },
            errors: {},
            isSaving: false,
            loading: false,
            selectedFilter: 'جديد',
            secteurs: [],
            sousSecteurs: [],
            specialites: [],
            standardisations_ar: ['جديد', 'مقيس', 'غير مقيس'],
            dialogPt: {
                root: 'border-none',
                mask: { style: 'backdrop-filter: blur(2px)' },
                header: { class: 'surface-50 border-bottom-1 surface-border' },
                content: { class: 'surface-50 py-3' },
                footer: { class: 'surface-50 border-top-1 surface-border' }
            }
        };
    },
    computed: {
        isEditing() {
            return !!this.specialiteToEdit;
        },
        filteredSecteurs() {
            if (['غير مقيس', 'مقيس'].includes(this.selectedFilter)) {
                return this.secteurs.filter(s => s.standardisation_ar === this.selectedFilter);
            }
            return [];
        },
        filteredSousSecteurs() {
            if (!this.form.secteur_id || this.selectedFilter !== 'مقيس') return [];
            return this.sousSecteurs.filter(
                (sousSecteur) => sousSecteur.secteur_id === this.form.secteur_id
            );
        },
        filteredSpecialites() {
            let filtered = this.specialites;

            // Exclure les spécialités déjà associées au centre (sauf en mode édition)
            if (this.centreToEdit?.specialites?.length && !this.isEditing) {
                filtered = filtered.filter(
                    (s) => !this.centreToEdit.specialites.some((cs) => cs.id === s.id)
                );
            }

            // Appliquer le filtre de standardisation
            if (this.selectedFilter === 'جديد') {
                filtered = filtered.filter(s => s.standardisation_ar === 'جديد');
            } else if (this.selectedFilter === 'غير مقيس') {
                if (this.form.secteur_id) {
                    filtered = filtered.filter(
                        s => s.secteur_id === this.form.secteur_id && s.standardisation_ar === 'غير مقيس'
                    );
                } else {
                    filtered = filtered.filter(s => s.standardisation_ar === 'غير مقيس');
                }
            } else if (this.selectedFilter === 'مقيس') {
                if (this.form.sous_secteur_id) {
                    filtered = filtered.filter(
                        s => s.sous_secteur_id === this.form.sous_secteur_id && s.standardisation_ar === 'مقيس'
                    );
                } else if (this.form.secteur_id) {
                    filtered = filtered.filter(
                        s => s.secteur_id === this.form.secteur_id && s.standardisation_ar === 'مقيس'
                    );
                } else {
                    filtered = filtered.filter(s => s.standardisation_ar === 'مقيس');
                }
            }

            // En mode édition, inclure la spécialité éditée
            if (this.isEditing && this.specialiteToEdit) {
                const editSpecialite = this.specialites.find(s => s.id === this.specialiteToEdit.id);
                if (editSpecialite && !filtered.some(s => s.id === editSpecialite.id)) {
                    filtered.push(editSpecialite);
                }
            }

            console.log(`Spécialités filtrées pour ${this.selectedFilter}:`, filtered);
            return filtered;
        },
    },
    watch: {
        visible(newVal) {
            if (newVal) {
                this.fetchData();
                if (this.centreToEdit && this.specialiteToEdit) {
                    this.populateForm();
                } else {
                    this.setFilter('جديد');
                }
            }
        },
        'form.secteur_id': function () {
            this.validateSecteurId();
            this.form.sous_secteur_id = null;
            this.form.specialite_ids = [];
        },
        'form.sous_secteur_id': function () {
            this.validateSousSecteurId();
            this.form.specialite_ids = [];
        },
    },
    methods: {
        async fetchData() {
            this.loading = true;
            try {
                // Fetch secteurs
                const secteursResponse = await axios.get('/api/secteurs');
                this.secteurs = Array.isArray(secteursResponse.data) ? secteursResponse.data : [];
                console.log('Secteurs chargés:', this.secteurs);

                // Fetch sous-secteurs
                const sousSecteursResponse = await axios.get('/api/sous-secteurs');
                this.sousSecteurs = Array.isArray(sousSecteursResponse.data) ? sousSecteursResponse.data : [];
                console.log('Sous-secteurs chargés:', this.sousSecteurs);

                // Fetch spécialités avec all=true
                const specialitesResponse = await axios.get('/api/specialites?all=true');
                this.specialites = Array.isArray(specialitesResponse.data.data)
                    ? specialitesResponse.data.data
                    : Array.isArray(specialitesResponse.data)
                        ? specialitesResponse.data
                        : [];
                console.log('Spécialités chargées:', this.specialites);

                // Lister les valeurs uniques de standardisation_ar
                const standardisationValues = [...new Set(this.specialites.map(s => s.standardisation_ar))];
                console.log('Valeurs de standardisation_ar:', standardisationValues);

                // Vérifier les spécialités pour جديد et غير مقيس
                const newSpecialites = this.specialites.filter(s => s.standardisation_ar === 'جديد');
                const nonStandardSpecialites = this.specialites.filter(s => s.standardisation_ar === 'غير مقيس');
                console.log('Spécialités جديد:', newSpecialites);
                console.log('Spécialités غير مقيس:', nonStandardSpecialites);

                // Afficher un message si aucune spécialité n'est disponible pour les filtres
                if (!newSpecialites.length && this.selectedFilter === 'جديد') {
                    this.toast.add({
                        severity: 'warn',
                        summary: 'Aucune spécialité',
                        detail: 'Aucune spécialité avec standardisation "جديد" trouvée.',
                        life: 5000,
                    });
                }
                if (!nonStandardSpecialites.length && this.selectedFilter === 'غير مقيس') {
                    this.toast.add({
                        severity: 'warn',
                        summary: 'Aucune spécialité',
                        detail: 'Aucune spécialité avec standardisation "غير مقيس" trouvée.',
                        life: 5000,
                    });
                }
                if (!this.specialites.length) {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'Aucune spécialité disponible. Vérifiez les données.',
                        life: 5000,
                    });
                }
            } catch (error) {
                console.error('Erreur lors du chargement des données:', error);
                this.handleError(error, 'Erreur lors du chargement des données.');
            } finally {
                this.loading = false;
            }
        },
        setFilter(filter) {
            this.selectedFilter = filter;
            this.form.secteur_id = null;
            this.form.sous_secteur_id = null;
            this.form.specialite_ids = [];
            this.errors = {};
            this.validateSecteurId();
            this.validateSousSecteurId();
            this.validateSpecialiteIds();
        },
        onSecteurChange() {
            this.form.sous_secteur_id = null;
            this.form.specialite_ids = [];
            this.validateSecteurId();
        },
        onSousSecteurChange() {
            this.form.specialite_ids = [];
            this.validateSousSecteurId();
        },
        populateForm() {
            if (this.centreToEdit && this.specialiteToEdit) {
                this.form = {
                    secteur_id: this.specialiteToEdit.secteur_id || null,
                    sous_secteur_id: this.specialiteToEdit.sous_secteur_id || null,
                    specialite_ids: [this.specialiteToEdit.id],
                    statut: this.specialiteToEdit.pivot?.statut || 'Actif',
                    date_debut: this.specialiteToEdit.pivot?.date_debut
                        ? new Date(this.specialiteToEdit.pivot.date_debut)
                        : new Date(),
                    date_fin: this.specialiteToEdit.pivot?.date_fin
                        ? new Date(this.specialiteToEdit.pivot.date_fin)
                        : null,
                    observation: this.specialiteToEdit.pivot?.observation || '',
                };
                this.selectedFilter = this.specialiteToEdit.standardisation_ar || 'جديد';
                this.validateSecteurId();
                this.validateSousSecteurId();
                this.validateSpecialiteIds();
                this.errors = {};
            }
        },
        resetForm() {
            this.form = {
                secteur_id: null,
                sous_secteur_id: null,
                specialite_ids: [],
                statut: 'Actif',
                date_debut: new Date(),
                date_fin: null,
                observation: '',
            };
            this.errors = {};
            this.selectedFilter = 'جديد';
            this.loading = false;
        },
        validateSecteurId() {
            if (['غير مقيس', 'مقيس'].includes(this.selectedFilter) && !this.isEditing && !this.form.secteur_id) {
                this.errors.secteur_id = 'Le secteur est requis.';
            } else {
                delete this.errors.secteur_id;
            }
        },
        validateSousSecteurId() {
            if (this.selectedFilter === 'مقيس' && !this.isEditing && !this.form.sous_secteur_id) {
                this.errors.sous_secteur_id = 'Le sous-secteur est requis.';
            } else {
                delete this.errors.sous_secteur_id;
            }
        },
        validateSpecialiteIds() {
            if (this.form.specialite_ids.length === 0) {
                this.errors.specialite_ids = 'Au moins une spécialité est requise.';
            } else {
                delete this.errors.specialite_ids;
            }
        },
        validateStatut() {
            if (!this.form.statut) {
                this.errors.statut = 'Le statut est requis.';
            } else {
                delete this.errors.statut;
            }
        },
        validateDateDebut() {
            if (!this.form.date_debut) {
                this.errors.date_debut = 'La date de début est requise.';
            } else {
                delete this.errors.date_debut;
            }
            this.validateDateFin();
        },
        validateDateFin() {
            if (
                this.form.date_fin &&
                this.form.date_debut &&
                new Date(this.form.date_fin) < new Date(this.form.date_debut)
            ) {
                this.errors.date_fin = 'La date de fin doit être postérieure à la date de début.';
            } else {
                delete this.errors.date_fin;
            }
        },
        validateObservation() {
            delete this.errors.observation;
        },
        validateForm() {
            this.validateSecteurId();
            this.validateSousSecteurId();
            this.validateSpecialiteIds();
            this.validateStatut();
            this.validateDateDebut();
            this.validateDateFin();
            this.validateObservation();
            return Object.keys(this.errors).length === 0;
        },
        formatDateForAPI(date) {
            if (!date) return null;
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        },
        async submitForm() {
            if (!this.centreToEdit) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Aucun centre sélectionné.',
                    life: 5000,
                });
                return;
            }

            if (!this.validateForm()) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Veuillez corriger les erreurs dans le formulaire.',
                    life: 5000,
                });
                return;
            }

            this.isSaving = true;
            try {
                const payload = {
                    centre_id: this.centreToEdit.id,
                    specialite_ids: this.form.specialite_ids,
                    statut: this.form.statut,
                    date_debut: this.formatDateForAPI(this.form.date_debut),
                    date_fin: this.formatDateForAPI(this.form.date_fin),
                    observation: this.form.observation || null,
                };

                let response;
                if (this.isEditing) {
                    response = await axios.put(
                        `/api/centres/${this.centreToEdit.id}/specialites/${this.form.specialite_ids[0]}`,
                        {
                            statut: this.form.statut,
                            date_debut: this.formatDateForAPI(this.form.date_debut),
                            date_fin: this.formatDateForAPI(this.form.date_fin),
                            observation: this.form.observation || null,
                        }
                    );
                    this.$emit('update', response.data.data);
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Association mise à jour avec succès.',
                        life: 3000,
                    });
                } else {
                    response = await axios.post(
                        '/api/centres/associate-specialites',
                        payload
                    );
                    this.$emit('save', response.data.data);
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Spécialités associées avec succès.',
                        life: 3000,
                    });
                }

                this.close();
            } catch (error) {
                this.handleError(error, "Erreur lors de l'enregistrement de l'association.");
            } finally {
                this.isSaving = false;
            }
        },
        handleError(error, defaultMessage) {
            let errorMessage = defaultMessage;
            if (error.response?.data?.message) {
                errorMessage = error.response.data.message;
            }
            console.error('Erreur:', error);
            this.toast.add({
                severity: 'error',
                summary: 'Erreur',
                detail: errorMessage,
                life: 5000,
            });
        },
        close() {
            this.$emit('close');
            this.$emit('update:visible', false);
            this.resetForm();
        },
    },
};
</script>

<style scoped>
.arabic-normal {
    font-family: 'Arial', sans-serif;
    direction: rtl;
}
</style>
```
