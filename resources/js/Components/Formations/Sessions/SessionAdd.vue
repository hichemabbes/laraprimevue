<template>
    <Dialog
        :visible="visible"
        @update:visible="$emit('update:visible', $event)"
        :modal="true"
        :focusOnShow="false"
        :style="{ width: '80vw', maxWidth: '900px' }"
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
                <i class="pi pi-calendar-plus text-primary text-2xl"></i>
                <span class="font-bold text-2xl">Ajouter une Session</span>
            </div>
        </template>

        <div v-if="loading" class="flex flex-column align-items-center justify-content-center gap-3 p-6">
            <ProgressSpinner style="width: 50px; height: 50px" strokeWidth="4" />
            <span class="text-color-secondary">Chargement des données...</span>
        </div>

        <div v-else class="surface-card p-4 shadow-2 border-round">
            <form @submit.prevent="submitForm">
                <TabView v-model:activeIndex="activeTabIndex">
                    <!-- Infos générales -->
                    <TabPanel header="Informations Générales">
                        <div class="grid p-fluid">
                            <!-- Année de formation -->
                            <div class="col-12 md:col-6 field">
                                <label class="block font-medium mb-2">
                                    Année de formation
                                    <span class="text-red-500">*</span>
                                </label>
                                <Dropdown
                                    v-model="form.annee_formation_id"
                                    :options="annees"
                                    optionLabel="intitule"
                                    optionValue="id"
                                    placeholder="Sélectionner une année"
                                    :class="{ 'p-invalid': errors.annee_formation_id }"
                                />
                                <small v-if="errors.annee_formation_id" class="p-error">
                                    {{ errors.annee_formation_id[0] }}
                                </small>
                            </div>

                            <!-- Code -->
                            <div class="col-12 md:col-6 field">
                                <label class="block font-medium mb-2">
                                    Code
                                </label>
                                <InputText
                                    v-model="form.code"
                                    placeholder="Ex: AUTOCAD-AVR-2025"
                                    :class="{ 'p-invalid': errors.code }"
                                />
                                <small v-if="errors.code" class="p-error">
                                    {{ errors.code[0] }}
                                </small>
                            </div>

                            <!-- Intitulé FR -->
                            <div class="col-12 md:col-6 field">
                                <label class="block font-medium mb-2">
                                    Intitulé (FR)
                                </label>
                                <InputText
                                    v-model="form.intitule_fr"
                                    placeholder="Session Avril 2025"
                                    :class="{ 'p-invalid': errors.intitule_fr }"
                                />
                                <small v-if="errors.intitule_fr" class="p-error">
                                    {{ errors.intitule_fr[0] }}
                                </small>
                            </div>

                            <!-- Intitulé AR -->
                            <div class="col-12 md:col-6 field">
                                <label class="block font-medium mb-2 text-right font-arabic">
                                    عنوان الدورة (العربية)
                                </label>
                                <InputText
                                    v-model="form.intitule_ar"
                                    dir="rtl"
                                    :class="{ 'p-invalid': errors.intitule_ar }"
                                />
                                <small v-if="errors.intitule_ar" class="p-error">
                                    {{ errors.intitule_ar[0] }}
                                </small>
                            </div>

                            <!-- Dates -->
                            <div class="col-12 md:col-6 field">
                                <label class="block font-medium mb-2">
                                    Date début
                                </label>
                                <Calendar
                                    v-model="form.date_debut"
                                    dateFormat="dd/mm/yy"
                                    showIcon
                                    :class="{ 'p-invalid': errors.date_debut }"
                                />
                                <small v-if="errors.date_debut" class="p-error">
                                    {{ errors.date_debut[0] }}
                                </small>
                            </div>

                            <div class="col-12 md:col-6 field">
                                <label class="block font-medium mb-2">
                                    Date fin
                                </label>
                                <Calendar
                                    v-model="form.date_fin"
                                    dateFormat="dd/mm/yy"
                                    showIcon
                                    :class="{ 'p-invalid': errors.date_fin }"
                                />
                                <small v-if="errors.date_fin" class="p-error">
                                    {{ errors.date_fin[0] }}
                                </small>
                            </div>

                            <!-- Capacités -->
                            <div class="col-12 md:col-6 field">
                                <label class="block font-medium mb-2">
                                    Capacité minimale
                                </label>
                                <InputNumber
                                    v-model="form.capacite_min"
                                    :min="0"
                                    :showButtons="true"
                                />
                            </div>

                            <div class="col-12 md:col-6 field">
                                <label class="block font-medium mb-2">
                                    Capacité maximale
                                </label>
                                <InputNumber
                                    v-model="form.capacite_max"
                                    :min="0"
                                    :showButtons="true"
                                />
                            </div>

                            <!-- Statut -->
                            <div class="col-12 md:col-6 field">
                                <label class="block font-medium mb-2">
                                    Statut
                                    <span class="text-red-500">*</span>
                                </label>
                                <Dropdown
                                    v-model="form.statut"
                                    :options="statutOptions"
                                    optionLabel="label"
                                    optionValue="value"
                                    placeholder="Sélectionner un statut"
                                    :class="{ 'p-invalid': errors.statut }"
                                />
                                <small v-if="errors.statut" class="p-error">
                                    {{ errors.statut[0] }}
                                </small>
                            </div>
                        </div>
                    </TabPanel>

                    <!-- Observations -->
                    <TabPanel header="Observations">
                        <div class="grid p-fluid">
                            <div class="col-12 field">
                                <label class="block font-medium mb-2">
                                    Observation (FR)
                                </label>
                                <Textarea
                                    v-model="form.observation_fr"
                                    rows="4"
                                    autoResize
                                />
                            </div>
                            <div class="col-12 field">
                                <label class="block font-medium mb-2 text-right font-arabic">
                                    ملاحظات (العربية)
                                </label>
                                <Textarea
                                    v-model="form.observation_ar"
                                    rows="4"
                                    autoResize
                                    dir="rtl"
                                />
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
                    class="p-button-text"
                    @click="$emit('update:visible', false)"
                />
                <Button
                    label="Enregistrer"
                    icon="pi pi-check"
                    class="p-button-success"
                    :loading="saving"
                    @click="submitForm"
                />
            </div>
        </template>
    </Dialog>
</template>

<script>
import axios from 'axios';
import Dialog from 'primevue/dialog';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Dropdown from 'primevue/dropdown';
import Calendar from 'primevue/calendar';
import InputNumber from 'primevue/inputnumber';
import Button from 'primevue/button';
import ProgressSpinner from 'primevue/progressspinner';
import { useToast } from 'primevue/usetoast';

export default {
    props: {
        visible: Boolean,
    },
    emits: ['update:visible', 'save'],
    components: {
        Dialog,
        TabView,
        TabPanel,
        InputText,
        Textarea,
        Dropdown,
        Calendar,
        InputNumber,
        Button,
        ProgressSpinner,
    },
    setup() {
        return { toast: useToast() };
    },
    data() {
        return {
            form: {
                annee_formation_id: null,
                code: '',
                intitule_fr: '',
                intitule_ar: '',
                date_debut: null,
                date_fin: null,
                capacite_min: null,
                capacite_max: null,
                statut: 'Planifiee',
                observation_fr: '',
                observation_ar: '',
            },
            annees: [],
            statutOptions: [
                { label: 'Planifiée', value: 'Planifiee' },
                { label: 'Ouverte aux inscriptions', value: 'Ouverte_inscription' },
                { label: 'En cours', value: 'En_cours' },
                { label: 'Terminée', value: 'Terminee' },
                { label: 'Annulée', value: 'Annulee' },
            ],
            errors: {},
            saving: false,
            loading: false,
            activeTabIndex: 0,
        };
    },
    watch: {
        visible(newVal) {
            if (newVal) {
                this.loadAnnees();
            } else {
                this.resetForm();
            }
        },
    },
    methods: {
        resetForm() {
            this.form = {
                annee_formation_id: null,
                code: '',
                intitule_fr: '',
                intitule_ar: '',
                date_debut: null,
                date_fin: null,
                capacite_min: null,
                capacite_max: null,
                statut: 'Planifiee',
                observation_fr: '',
                observation_ar: '',
            };
            this.errors = {};
            this.activeTabIndex = 0;
        },
        async loadAnnees() {
            this.loading = true;
            try {
                // Adapter l'URL selon ton contrôleur AnneeFormation
                const res = await axios.get('/api/annees-formations');
                // si ton API renvoie { annees: [...] }
                this.annees = res.data.annees || res.data || [];
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        'Impossible de charger les années de formation.',
                    life: 4000,
                });
            } finally {
                this.loading = false;
            }
        },
        async submitForm() {
            this.saving = true;
            this.errors = {};
            try {
                const payload = {
                    ...this.form,
                    date_debut: this.form.date_debut
                        ? this.form.date_debut.toISOString().slice(0, 10)
                        : null,
                    date_fin: this.form.date_fin
                        ? this.form.date_fin.toISOString().slice(0, 10)
                        : null,
                };
                const res = await axios.post('/api/sessions', payload);

                this.$emit('save', res.data);
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Session ajoutée avec succès.',
                    life: 3000,
                });
                this.$emit('update:visible', false);
            } catch (error) {
                if (error.response?.status === 422) {
                    this.errors = error.response.data.errors || {};
                    this.toast.add({
                        severity: 'error',
                        summary: 'Validation',
                        detail: 'Veuillez corriger les erreurs du formulaire.',
                        life: 5000,
                    });
                } else {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail:
                            error.response?.data?.message ||
                            'Erreur lors de la création de la session.',
                        life: 5000,
                    });
                }
            } finally {
                this.saving = false;
            }
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
.font-arabic,
:deep(.p-inputtext[dir='rtl']) {
    font-family: 'Montserrat-Arabic-Regular', sans-serif;
}
</style>
