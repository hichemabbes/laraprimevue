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
                <i class="pi pi-book text-primary text-2xl"></i>
                <span class="font-bold text-2xl">Modifier la Spécialité</span>
            </div>
        </template>

        <!-- Loading -->
        <div
            v-if="loading"
            class="flex flex-column align-items-center justify-content-center gap-3 p-6"
        >
            <ProgressSpinner style="width: 50px; height: 50px" strokeWidth="4" />
            <span class="text-color-secondary">Chargement des données...</span>
        </div>

        <!-- Formulaire -->
        <div v-else class="surface-card p-4 shadow-2 border-round">
            <form @submit.prevent="submitForm">
                <TabView v-model:activeIndex="activeTabIndex">
                    <!-- Onglet 1 : Informations générales -->
                    <TabPanel header="Informations Générales">
                        <div class="grid p-fluid">
                            <div class="col-12 md:col-4 field">
                                <label for="code" class="block font-medium mb-2">
                                    Code
                                </label>
                                <InputText
                                    id="code"
                                    v-model="form.code"
                                    :class="{ 'p-invalid': errors.code }"
                                    placeholder="Ex: GEN-CIV"
                                />
                                <small v-if="errors.code" class="p-error">
                                    {{ errors.code[0] }}
                                </small>
                            </div>

                            <div class="col-12 md:col-4 field">
                                <label for="nom_fr" class="block font-medium mb-2">
                                    Nom (Français) <span class="text-red-500">*</span>
                                </label>
                                <InputText
                                    id="nom_fr"
                                    v-model="form.nom_fr"
                                    :class="{ 'p-invalid': errors.nom_fr }"
                                    placeholder="Entrez le nom en français"
                                />
                                <small v-if="errors.nom_fr" class="p-error">
                                    {{ errors.nom_fr[0] }}
                                </small>
                            </div>

                            <div class="col-12 md:col-4 field">
                                <label
                                    for="nom_ar"
                                    class="block font-medium mb-2 text-right font-arabic"
                                >
                                    الاسم (العربية) <span class="text-red-500">*</span>
                                </label>
                                <InputText
                                    id="nom_ar"
                                    v-model="form.nom_ar"
                                    dir="rtl"
                                    :class="{ 'p-invalid': errors.nom_ar }"
                                    placeholder="أدخل الاسم بالعربية"
                                />
                                <small v-if="errors.nom_ar" class="p-error">
                                    {{ errors.nom_ar[0] }}
                                </small>
                            </div>

                            <div class="col-12 md:col-6 field">
                                <label for="niveau_fr" class="block font-medium mb-2">
                                    Niveau d'entrée (FR)
                                </label>
                                <InputText
                                    id="niveau_fr"
                                    v-model="form.niveau_fr"
                                    :class="{ 'p-invalid': errors.niveau_fr }"
                                    placeholder="Ex: Bac, 9ème, etc."
                                />
                                <small v-if="errors.niveau_fr" class="p-error">
                                    {{ errors.niveau_fr[0] }}
                                </small>
                            </div>

                            <div class="col-12 md:col-6 field">
                                <label
                                    for="niveau_ar"
                                    class="block font-medium mb-2 text-right font-arabic"
                                >
                                    المستوى المطلوب (العربية)
                                </label>
                                <InputText
                                    id="niveau_ar"
                                    v-model="form.niveau_ar"
                                    dir="rtl"
                                    :class="{ 'p-invalid': errors.niveau_ar }"
                                    placeholder="المستوى الدراسي المطلوب"
                                />
                                <small v-if="errors.niveau_ar" class="p-error">
                                    {{ errors.niveau_ar[0] }}
                                </small>
                            </div>

                            <div class="col-12 md:col-6 field">
                                <label for="diplome_fr" class="block font-medium mb-2">
                                    Diplôme (FR)
                                </label>
                                <InputText
                                    id="diplome_fr"
                                    v-model="form.diplome_fr"
                                    :class="{ 'p-invalid': errors.diplome_fr }"
                                    placeholder="Ex: Technicien, BTS..."
                                />
                                <small v-if="errors.diplome_fr" class="p-error">
                                    {{ errors.diplome_fr[0] }}
                                </small>
                            </div>

                            <div class="col-12 md:col-6 field">
                                <label
                                    for="diplome_ar"
                                    class="block font-medium mb-2 text-right font-arabic"
                                >
                                    الشهادة (العربية)
                                </label>
                                <InputText
                                    id="diplome_ar"
                                    v-model="form.diplome_ar"
                                    dir="rtl"
                                    :class="{ 'p-invalid': errors.diplome_ar }"
                                    placeholder="نوع الشهادة"
                                />
                                <small v-if="errors.diplome_ar" class="p-error">
                                    {{ errors.diplome_ar[0] }}
                                </small>
                            </div>

                            <div class="col-12 md:col-4 field">
                                <label
                                    for="duree_formation"
                                    class="block font-medium mb-2"
                                >
                                    Durée (années)
                                </label>
                                <InputNumber
                                    id="duree_formation"
                                    v-model="form.duree_formation"
                                    :class="{ 'p-invalid': errors.duree_formation }"
                                    mode="decimal"
                                    :minFractionDigits="1"
                                    :maxFractionDigits="1"
                                    placeholder="Ex: 2.5"
                                />
                                <small
                                    v-if="errors.duree_formation"
                                    class="p-error"
                                >
                                    {{ errors.duree_formation[0] }}
                                </small>
                            </div>

                            <div class="col-12 md:col-4 field">
                                <label for="statut" class="block font-medium mb-2">
                                    Statut <span class="text-red-500">*</span>
                                </label>
                                <Dropdown
                                    id="statut"
                                    v-model="form.statut"
                                    :options="['Actif', 'Inactif']"
                                    placeholder="Sélectionner un statut"
                                    :class="{ 'p-invalid': errors.statut }"
                                />
                                <small v-if="errors.statut" class="p-error">
                                    {{ errors.statut[0] }}
                                </small>
                            </div>

                            <div class="col-12 md:col-4 field flex align-items-end">
                                <div class="flex align-items-center gap-2">
                                    <Checkbox
                                        inputId="est_homologue"
                                        v-model="form.est_homologue"
                                        :binary="true"
                                    />
                                    <label
                                        for="est_homologue"
                                        class="font-medium"
                                    >
                                        Formation homologuée
                                    </label>
                                </div>
                            </div>
                        </div>
                    </TabPanel>

                    <!-- Onglet 2 : Volumes & homologation -->
                    <TabPanel header="Volumes & homologation">
                        <div class="grid p-fluid">
                            <div class="col-12 md:col-4 field">
                                <label for="heures_et" class="block font-medium mb-2">
                                    Heures ET
                                </label>
                                <InputNumber
                                    id="heures_et"
                                    v-model="form.heures_et"
                                    :class="{ 'p-invalid': errors.heures_et }"
                                    :min="0"
                                />
                                <small v-if="errors.heures_et" class="p-error">
                                    {{ errors.heures_et[0] }}
                                </small>
                            </div>

                            <div class="col-12 md:col-4 field">
                                <label for="heures_eg" class="block font-medium mb-2">
                                    Heures EG
                                </label>
                                <InputNumber
                                    id="heures_eg"
                                    v-model="form.heures_eg"
                                    :class="{ 'p-invalid': errors.heures_eg }"
                                    :min="0"
                                />
                                <small v-if="errors.heures_eg" class="p-error">
                                    {{ errors.heures_eg[0] }}
                                </small>
                            </div>

                            <div class="col-12 md:col-4 field">
                                <label
                                    for="heures_total"
                                    class="block font-medium mb-2"
                                >
                                    Heures totales
                                </label>
                                <InputNumber
                                    id="heures_total"
                                    v-model="form.heures_total"
                                    :class="{ 'p-invalid': errors.heures_total }"
                                    :min="0"
                                />
                                <small
                                    v-if="errors.heures_total"
                                    class="p-error"
                                >
                                    {{ errors.heures_total[0] }}
                                </small>
                            </div>

                            <div class="col-12 field">
                                <label
                                    for="reference_homologation"
                                    class="block font-medium mb-2"
                                >
                                    Référence d'homologation
                                </label>
                                <InputText
                                    id="reference_homologation"
                                    v-model="form.reference_homologation"
                                    :class="{
                                        'p-invalid': errors.reference_homologation,
                                    }"
                                    placeholder="N° ou référence officielle"
                                />
                                <small
                                    v-if="errors.reference_homologation"
                                    class="p-error"
                                >
                                    {{ errors.reference_homologation[0] }}
                                </small>
                            </div>
                        </div>
                    </TabPanel>

                    <!-- Onglet 3 : Descriptions & critères -->
                    <TabPanel header="Descriptions & critères">
                        <div class="grid p-fluid">
                            <div class="col-12 field">
                                <label
                                    for="description_fr"
                                    class="block font-medium mb-2"
                                >
                                    Description (Français)
                                </label>
                                <Textarea
                                    id="description_fr"
                                    v-model="form.description_fr"
                                    :class="{ 'p-invalid': errors.description_fr }"
                                    rows="4"
                                    autoResize
                                    placeholder="Entrez la description en français"
                                />
                                <small
                                    v-if="errors.description_fr"
                                    class="p-error"
                                >
                                    {{ errors.description_fr[0] }}
                                </small>
                            </div>

                            <div class="col-12 field">
                                <label
                                    for="description_ar"
                                    class="block font-medium mb-2 text-right font-arabic"
                                >
                                    الوصف (العربية)
                                </label>
                                <Textarea
                                    id="description_ar"
                                    v-model="form.description_ar"
                                    dir="rtl"
                                    :class="{ 'p-invalid': errors.description_ar }"
                                    rows="4"
                                    autoResize
                                    placeholder="أدخل الوصف بالعربية"
                                />
                                <small
                                    v-if="errors.description_ar"
                                    class="p-error"
                                >
                                    {{ errors.description_ar[0] }}
                                </small>
                            </div>

                            <div class="col-12 md:col-6 field">
                                <label
                                    for="criteres_admission_fr"
                                    class="block font-medium mb-2"
                                >
                                    Critères d'admission (FR)
                                </label>
                                <Textarea
                                    id="criteres_admission_fr"
                                    v-model="form.criteres_admission_fr_text"
                                    :class="{
                                        'p-invalid': errors.criteres_admission_fr,
                                    }"
                                    rows="4"
                                    autoResize
                                    placeholder="Un critère par ligne"
                                />
                                <small
                                    v-if="errors.criteres_admission_fr"
                                    class="p-error"
                                >
                                    {{ errors.criteres_admission_fr[0] }}
                                </small>
                            </div>

                            <div class="col-12 md:col-6 field">
                                <label
                                    for="criteres_admission_ar"
                                    class="block font-medium mb-2 text-right font-arabic"
                                >
                                    شروط القبول (العربية)
                                </label>
                                <Textarea
                                    id="criteres_admission_ar"
                                    v-model="form.criteres_admission_ar_text"
                                    dir="rtl"
                                    :class="{
                                        'p-invalid': errors.criteres_admission_ar,
                                    }"
                                    rows="4"
                                    autoResize
                                    placeholder="شرط في كل سطر"
                                />
                                <small
                                    v-if="errors.criteres_admission_ar"
                                    class="p-error"
                                >
                                    {{ errors.criteres_admission_ar[0] }}
                                </small>
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
                    @click="$emit('close')"
                />
                <Button
                    label="Mettre à jour"
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
import InputNumber from 'primevue/inputnumber';
import Checkbox from 'primevue/checkbox';
import { useToast } from 'primevue/usetoast';

export default {
    components: {
        Dialog,
        TabView,
        TabPanel,
        InputText,
        Textarea,
        Dropdown,
        Button,
        ProgressSpinner,
        InputNumber,
        Checkbox,
    },
    props: {
        visible: Boolean,
        specialiteId: [Number, String, null],
        specialiteData: Object,
    },
    emits: ['update:visible', 'close', 'update'],
    setup() {
        return { toast: useToast() };
    },
    data() {
        return {
            form: {
                code: '',
                nom_fr: '',
                nom_ar: '',
                niveau_fr: '',
                niveau_ar: '',
                diplome_fr: '',
                diplome_ar: '',
                duree_formation: null,
                heures_et: null,
                heures_eg: null,
                heures_total: null,
                est_homologue: false,
                reference_homologation: '',
                description_fr: '',
                description_ar: '',
                criteres_admission_fr_text: '',
                criteres_admission_ar_text: '',
                statut: 'Actif',
            },
            errors: {},
            saving: false,
            loading: false,
            activeTabIndex: 0,
        };
    },
    watch: {
        specialiteData: {
            handler(newData) {
                if (newData) {
                    this.populateForm(newData);
                } else {
                    this.resetForm();
                }
            },
            immediate: true,
            deep: true,
        },
        visible(newVal) {
            if (!newVal) {
                this.errors = {};
                this.activeTabIndex = 0;
            }
        },
    },
    methods: {
        resetForm() {
            this.form = {
                code: '',
                nom_fr: '',
                nom_ar: '',
                niveau_fr: '',
                niveau_ar: '',
                diplome_fr: '',
                diplome_ar: '',
                duree_formation: null,
                heures_et: null,
                heures_eg: null,
                heures_total: null,
                est_homologue: false,
                reference_homologation: '',
                description_fr: '',
                description_ar: '',
                criteres_admission_fr_text: '',
                criteres_admission_ar_text: '',
                statut: 'Actif',
            };
        },
        populateForm(data) {
            this.form.code = data.code || '';
            this.form.nom_fr = data.nom_fr || '';
            this.form.nom_ar = data.nom_ar || '';
            this.form.niveau_fr = data.niveau_fr || '';
            this.form.niveau_ar = data.niveau_ar || '';
            this.form.diplome_fr = data.diplome_fr || '';
            this.form.diplome_ar = data.diplome_ar || '';
            this.form.duree_formation = data.duree_formation ?? null;
            this.form.heures_et = data.heures_et ?? null;
            this.form.heures_eg = data.heures_eg ?? null;
            this.form.heures_total = data.heures_total ?? null;
            this.form.est_homologue = !!data.est_homologue;
            this.form.reference_homologation =
                data.reference_homologation || '';
            this.form.description_fr = data.description_fr || '';
            this.form.description_ar = data.description_ar || '';
            this.form.criteres_admission_fr_text = Array.isArray(
                data.criteres_admission_fr
            )
                ? data.criteres_admission_fr.join('\n')
                : '';
            this.form.criteres_admission_ar_text = Array.isArray(
                data.criteres_admission_ar
            )
                ? data.criteres_admission_ar.join('\n')
                : '';
            this.form.statut = data.statut || 'Actif';
        },
        buildPayload() {
            return {
                code: this.form.code || null,
                nom_fr: this.form.nom_fr || null,
                nom_ar: this.form.nom_ar || null,
                niveau_fr: this.form.niveau_fr || null,
                niveau_ar: this.form.niveau_ar || null,
                diplome_fr: this.form.diplome_fr || null,
                diplome_ar: this.form.diplome_ar || null,
                duree_formation: this.form.duree_formation,
                heures_et: this.form.heures_et,
                heures_eg: this.form.heures_eg,
                heures_total: this.form.heures_total,
                est_homologue: !!this.form.est_homologue,
                reference_homologation:
                    this.form.reference_homologation || null,
                description_fr: this.form.description_fr || null,
                description_ar: this.form.description_ar || null,
                criteres_admission_fr: this.form.criteres_admission_fr_text
                    ? this.form.criteres_admission_fr_text
                        .split('\n')
                        .map((s) => s.trim())
                        .filter((s) => s.length > 0)
                    : [],
                criteres_admission_ar: this.form.criteres_admission_ar_text
                    ? this.form.criteres_admission_ar_text
                        .split('\n')
                        .map((s) => s.trim())
                        .filter((s) => s.length > 0)
                    : [],
                statut: this.form.statut || 'Actif',
            };
        },
        async submitForm() {
            if (!this.specialiteId) return;
            this.saving = true;
            this.errors = {};
            try {
                const payload = this.buildPayload();
                const response = await axios.put(
                    `/api/specialites/${this.specialiteId}`,
                    payload
                );
                const specialite = response.data;

                this.$emit('update', specialite);
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Spécialité mise à jour avec succès.',
                    life: 3000,
                });
                this.$emit('close');
            } catch (error) {
                if (error.response?.status === 422) {
                    this.errors = error.response.data.errors || {};
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'Veuillez corriger les erreurs du formulaire.',
                        life: 5000,
                    });
                } else {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail:
                            error.response?.data?.message ||
                            'Une erreur est survenue.',
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
:deep(.p-inputtext[dir='rtl']),
:deep(textarea[dir='rtl']) {
    font-family: 'Montserrat-Arabic-Regular', sans-serif;
}
</style>
