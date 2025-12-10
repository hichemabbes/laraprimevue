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
                <i class="pi pi-users text-primary text-2xl"></i>
                <span class="font-bold text-2xl">Ajouter une Formation</span>
            </div>
        </template>

        <div
            v-if="loading"
            class="flex flex-column align-items-center justify-content-center gap-3 p-6"
        >
            <ProgressSpinner style="width: 50px; height: 50px" strokeWidth="4" />
            <span class="text-color-secondary">Chargement des données...</span>
        </div>

        <div v-else class="surface-card p-4 shadow-2 border-round">
            <form @submit.prevent="submitForm">
                <TabView v-model:activeIndex="activeTabIndex">
                    <!-- Infos générales -->
                    <TabPanel header="Informations Générales">
                        <div class="grid p-fluid">
                            <!-- Session -->
                            <div class="col-12 md:col-6 field">
                                <label class="block font-medium mb-2">
                                    Session
                                    <span class="text-red-500">*</span>
                                </label>
                                <Dropdown
                                    v-model="form.session_id"
                                    :options="sessions"
                                    optionLabel="label"
                                    optionValue="id"
                                    placeholder="Sélectionner une session"
                                    :class="{ 'p-invalid': errors.session_id }"
                                />
                                <small v-if="errors.session_id" class="p-error">
                                    {{ errors.session_id[0] }}
                                </small>
                            </div>

                            <!-- Spécialité (pour formation diplômante) -->
                            <div class="col-12 md:col-6 field">
                                <label class="block font-medium mb-2">
                                    Spécialité (si formation diplômante)
                                </label>
                                <Dropdown
                                    v-model="form.specialite_id"
                                    :options="specialites"
                                    optionLabel="nom_fr"
                                    optionValue="id"
                                    placeholder="Sélectionner une spécialité"
                                    :showClear="true"
                                />
                            </div>

                            <!-- Thème (pour formation certif / continue) -->
                            <div class="col-12 md:col-6 field">
                                <label class="block font-medium mb-2">
                                    Thème (si formation par thème)
                                </label>
                                <Dropdown
                                    v-model="form.theme_id"
                                    :options="themes"
                                    optionLabel="nom_fr"
                                    optionValue="id"
                                    placeholder="Sélectionner un thème"
                                    :showClear="true"
                                />
                            </div>

                            <!-- Code -->
                            <div class="col-12 md:col-6 field">
                                <label class="block font-medium mb-2">
                                    Code
                                </label>
                                <InputText
                                    v-model="form.code"
                                    placeholder="Ex : FORM-AUTOCAD-01"
                                    :class="{ 'p-invalid': errors.code }"
                                />
                                <small v-if="errors.code" class="p-error">
                                    {{ errors.code[0] }}
                                </small>
                            </div>

                            <!-- Nom FR -->
                            <div class="col-12 md:col-6 field">
                                <label class="block font-medium mb-2">
                                    Nom (FR)
                                </label>
                                <InputText
                                    v-model="form.nom_fr"
                                    placeholder="Nom de la formation"
                                    :class="{ 'p-invalid': errors.nom_fr }"
                                />
                                <small v-if="errors.nom_fr" class="p-error">
                                    {{ errors.nom_fr[0] }}
                                </small>
                            </div>

                            <!-- Nom AR -->
                            <div class="col-12 md:col-6 field">
                                <label class="block font-medium mb-2 text-right font-arabic">
                                    اسم التكوين (العربية)
                                </label>
                                <InputText
                                    v-model="form.nom_ar"
                                    dir="rtl"
                                    :class="{ 'p-invalid': errors.nom_ar }"
                                />
                                <small v-if="errors.nom_ar" class="p-error">
                                    {{ errors.nom_ar[0] }}
                                </small>
                            </div>

                            <!-- Cible FR -->
                            <div class="col-12 md:col-6 field">
                                <label class="block font-medium mb-2">
                                    Cible (FR)
                                </label>
                                <InputText
                                    v-model="form.cible_fr"
                                    placeholder="Public, personnels d'entreprise..."
                                    :class="{ 'p-invalid': errors.cible_fr }"
                                />
                                <small v-if="errors.cible_fr" class="p-error">
                                    {{ errors.cible_fr[0] }}
                                </small>
                            </div>

                            <!-- Cible AR -->
                            <div class="col-12 md:col-6 field">
                                <label class="block font-medium mb-2 text-right font-arabic">
                                    الفئة المستهدفة (العربية)
                                </label>
                                <InputText
                                    v-model="form.cible_ar"
                                    dir="rtl"
                                    :class="{ 'p-invalid': errors.cible_ar }"
                                />
                                <small v-if="errors.cible_ar" class="p-error">
                                    {{ errors.cible_ar[0] }}
                                </small>
                            </div>

                            <!-- Statut -->
                            <div class="col-12 md:col-6 field">
                                <label class="block font-medium mb-2">
                                    Statut
                                </label>
                                <Dropdown
                                    v-model="form.statut"
                                    :options="['Actif', 'Inactif']"
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
        Button,
        ProgressSpinner,
    },
    setup() {
        return { toast: useToast() };
    },
    data() {
        return {
            form: {
                session_id: null,
                specialite_id: null,
                theme_id: null,
                code: '',
                nom_fr: '',
                nom_ar: '',
                cible_fr: '',
                cible_ar: '',
                statut: 'Actif',
                observation_fr: '',
                observation_ar: '',
            },
            sessions: [],
            specialites: [],
            themes: [],
            errors: {},
            saving: false,
            loading: false,
            activeTabIndex: 0,
        };
    },
    watch: {
        visible(newVal) {
            if (newVal) {
                this.loadOptions();
            } else {
                this.resetForm();
            }
        },
    },
    methods: {
        resetForm() {
            this.form = {
                session_id: null,
                specialite_id: null,
                theme_id: null,
                code: '',
                nom_fr: '',
                nom_ar: '',
                cible_fr: '',
                cible_ar: '',
                statut: 'Actif',
                observation_fr: '',
                observation_ar: '',
            };
            this.errors = {};
            this.activeTabIndex = 0;
        },
        async loadOptions() {
            this.loading = true;
            try {
                const [sessionsRes, specialitesRes, themesRes] = await Promise.all([
                    axios.get('/api/sessions/all'),
                    axios.get('/api/specialites/all'),
                    axios.get('/api/themes/all'),
                ]);

                // Adapter selon tes réponses backend
                this.sessions =
                    sessionsRes.data.sessions ||
                    sessionsRes.data ||
                    [];

                this.specialites =
                    specialitesRes.data.specialites ||
                    specialitesRes.data ||
                    [];

                this.themes =
                    themesRes.data.themes ||
                    themesRes.data ||
                    [];

                // Pour les sessions, si tu renvoies juste {id, intitule / code}
                this.sessions = this.sessions.map((s) => ({
                    id: s.id,
                    label: s.intitule_fr || s.code || `Session #${s.id}`,
                }));
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        'Impossible de charger les listes (sessions/spécialités/thèmes).',
                    life: 5000,
                });
            } finally {
                this.loading = false;
            }
        },
        async submitForm() {
            this.saving = true;
            this.errors = {};
            try {
                const res = await axios.post('/api/formations', this.form);

                this.$emit('save', res.data);
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Formation ajoutée avec succès.',
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
                            'Erreur lors de la création de la formation.',
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
