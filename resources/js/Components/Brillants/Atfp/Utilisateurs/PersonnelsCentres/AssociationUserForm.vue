<template>
    <Dialog
        :visible="visible"
        @update:visible="handleVisibleUpdate"
        :modal="true"
        :focusOnShow="false"
        :style="{ width: '90vw', maxWidth: '1200px' }"
        :breakpoints="{ '960px': '98vw', '640px': '100vw' }"
        class="p-fluid"
        :pt="{
            root: 'border-round-xl shadow-5',
            mask: { style: 'backdrop-filter: blur(4px)' },
            header: { class: 'surface-50 border-bottom-1 surface-border p-4' },
            content: { class: 'surface-ground p-4' },
            footer: { class: 'surface-50 border-top-1 surface-border p-4' }
        }"
    >
        <template #header>
            <div class="flex align-items-center gap-2">
                <i :class="isEditing ? 'pi pi-pencil text-primary text-2xl' : 'pi pi-plus text-primary text-2xl'"></i>
                <span class="font-bold text-2xl">
                    {{ isEditing ? `Modifier l'association pour ${user?.nom_fr || ''}` : `Associer ${user?.nom_fr || ''} à un centre` }}
                </span>
            </div>
        </template>
        <div v-if="loading" class="flex flex-column align-items-center justify-content-center gap-3 p-6">
            <ProgressSpinner style="width: 50px; height: 50px" strokeWidth="4" />
            <span class="text-color-secondary">Chargement des données...</span>
        </div>
        <div v-else class="surface-card p-4 shadow-2 border-round">
            <TabView ref="tabview" v-model:activeIndex="activeTab">
                <TabPanel header="Associer un Centre">
                    <form @submit.prevent="saveAssociation" class="form-spacing">
                        <div class="grid p-fluid">
                            <div class="col-12 flex align-items-start gap-3 mb-2 user-data-container">
                                <div class="photo-container" style="width: 80px; height: 80px;">
                                    <img
                                        v-if="user?.photo"
                                        :src="getPhotoSrc(user?.photo)"
                                        alt="Photo de l'utilisateur"
                                        class="border-round shadow-2"
                                        style="width: 80px; height: 80px; object-fit: cover; border-radius: 50%"
                                        @error="onImageError"
                                    />
                                    <i
                                        v-else
                                        class="pi pi-user text-4xl text-400 flex align-items-center justify-content-center"
                                        style="width: 80px; height: 80px; border-radius: 50%; background: var(--surface-100);"
                                    ></i>
                                </div>
                                <div style="display: flex; flex-direction: column; justify-content: space-between; height: 80px;">
                                    <div class="font-bold">{{ user?.nom_fr || '-' }} {{ user?.prenom_fr || '-' }}</div>
                                    <div>
                                        <Tag
                                            :value="user?.roles[0]?.name || 'Aucun rôle'"
                                            severity="info"
                                            style="font-size: 0.75rem; padding: 0.25rem 0.5rem;"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="fiche_pdf_fr" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Fiche PDF (Français) <span v-if="!form.fiche_pdf_ar && !isEditing" class="text-red-500">*</span>
                                </label>
                                <div class="p-inputgroup">
                                    <InputText
                                        :value="form.fiche_pdf_fr_name"
                                        placeholder="Aucun fichier sélectionné"
                                        class="w-full"
                                        readonly
                                        :class="{ 'p-invalid': submitted && errors.fiche_pdf_fr?.length }"
                                        style="font-size: 0.875rem"
                                    />
                                    <Button
                                        icon="pi pi-upload"
                                        class="p-button-primary"
                                        @click="$refs.fileUploadFr.click()"
                                    />
                                    <input
                                        ref="fileUploadFr"
                                        type="file"
                                        accept="application/pdf"
                                        style="display: none"
                                        @change="onFileSelect($event, 'fiche_pdf_fr')"
                                    />
                                    <Button
                                        v-if="isEditing && associationToEdit?.fiche_pdf_fr"
                                        icon="pi pi-eye"
                                        class="p-button-text p-button-rounded"
                                        @click="previewFile(associationToEdit.id, 'fiche_pdf_fr')"
                                        v-tooltip="'Prévisualiser'"
                                    />
                                </div>
                                <small v-if="submitted && errors.fiche_pdf_fr?.length" class="p-error" style="font-size: 0.75rem">
                                    {{ errors.fiche_pdf_fr[0] }}
                                </small>
                                <small v-if="form.fiche_pdf_fr_name && !errors.fiche_pdf_fr?.length" class="text-green-500" style="font-size: 0.75rem">
                                    Fichier sélectionné : {{ form.fiche_pdf_fr_name }}
                                </small>
                                <small v-else-if="isEditing && associationToEdit?.fiche_pdf_fr && !form.fiche_pdf_fr" class="text-green-500" style="font-size: 0.75rem">
                                    Fichier existant conservé
                                </small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="fiche_pdf_ar" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Fiche PDF (Arabe) <span v-if="!form.fiche_pdf_fr && !isEditing" class="text-red-500">*</span>
                                </label>
                                <div class="p-inputgroup">
                                    <InputText
                                        :value="form.fiche_pdf_ar_name"
                                        placeholder="Aucun fichier sélectionné"
                                        class="w-full font-arabic"
                                        readonly
                                        :class="{ 'p-invalid': submitted && errors.fiche_pdf_ar?.length }"
                                        style="font-size: 0.875rem"
                                        dir="rtl"
                                    />
                                    <Button
                                        icon="pi pi-upload"
                                        class="p-button-primary"
                                        @click="$refs.fileUploadAr.click()"
                                    />
                                    <input
                                        ref="fileUploadAr"
                                        type="file"
                                        accept="application/pdf"
                                        style="display: none"
                                        @change="onFileSelect($event, 'fiche_pdf_ar')"
                                    />
                                    <Button
                                        v-if="isEditing && associationToEdit?.fiche_pdf_ar"
                                        icon="pi pi-eye"
                                        class="p-button-text p-button-rounded"
                                        @click="previewFile(associationToEdit.id, 'fiche_pdf_ar')"
                                        v-tooltip="'Prévisualiser'"
                                    />
                                </div>
                                <small v-if="submitted && errors.fiche_pdf_ar?.length" class="p-error font-arabic" style="font-size: 0.75rem">
                                    {{ errors.fiche_pdf_ar[0] }}
                                </small>
                                <small v-if="form.fiche_pdf_ar_name && !errors.fiche_pdf_ar?.length" class="text-green-500 font-arabic" style="font-size: 0.75rem">
                                    الملف المختار: {{ form.fiche_pdf_ar_name }}
                                </small>
                                <small v-else-if="isEditing && associationToEdit?.fiche_pdf_ar && !form.fiche_pdf_ar" class="text-green-500 font-arabic" style="font-size: 0.75rem">
                                    الملف الحالي محفوظ
                                </small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="centre_id" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Centre <span class="text-red-500">*</span>
                                </label>
                                <Dropdown
                                    id="centre_id"
                                    v-model="form.centre_id"
                                    :options="centres"
                                    optionLabel="nom_fr"
                                    optionValue="id"
                                    placeholder="Sélectionner un centre"
                                    :class="{ 'p-invalid': submitted && errors.centre_id?.length }"
                                    style="font-size: 0.875rem"
                                    @change="validateCentre"
                                />
                                <small v-if="submitted && errors.centre_id?.length" class="p-error" style="font-size: 0.75rem">
                                    {{ errors.centre_id[0] }}
                                </small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="role_id" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Rôle
                                </label>
                                <Dropdown
                                    id="role_id"
                                    v-model="form.role_id"
                                    :options="roles"
                                    optionLabel="name"
                                    optionValue="id"
                                    placeholder="Sélectionner un rôle"
                                    :class="{ 'p-invalid': submitted && errors.role_id?.length }"
                                    style="font-size: 0.875rem"
                                    @change="validateRole"
                                />
                                <small v-if="submitted && errors.role_id?.length" class="p-error" style="font-size: 0.75rem">
                                    {{ errors.role_id[0] }}
                                </small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="type_affectation_fr" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Type d'affectation <span class="text-red-500">*</span>
                                </label>
                                <Dropdown
                                    id="type_affectation_fr"
                                    v-model="form.type_affectation_fr"
                                    :options="typeAffectationOptions"
                                    optionLabel="label"
                                    optionValue="value"
                                    placeholder="Sélectionner un type d'affectation"
                                    :class="{ 'p-invalid': submitted && errors.type_affectation_fr?.length }"
                                    style="font-size: 0.875rem"
                                    @change="validateTypeAffectation"
                                />
                                <small v-if="submitted && errors.type_affectation_fr?.length" class="p-error" style="font-size: 0.75rem">
                                    {{ errors.type_affectation_fr[0] }}
                                </small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="etablissement_origine_fr" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Établissement d'origine (Français)
                                </label>
                                <InputText
                                    id="etablissement_origine_fr"
                                    v-model="form.etablissement_origine_fr"
                                    :class="{ 'p-invalid': submitted && errors.etablissement_origine_fr?.length }"
                                    placeholder="Saisissez l'établissement d'origine en français"
                                    style="font-size: 0.875rem"
                                />
                                <small v-if="submitted && errors.etablissement_origine_fr?.length" class="p-error" style="font-size: 0.75rem">
                                    {{ errors.etablissement_origine_fr[0] }}
                                </small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="etablissement_origine_ar" class="block font-medium mb-1 font-arabic" style="font-size: 0.875rem">
                                    Établissement d'origine (Arabe)
                                </label>
                                <InputText
                                    id="etablissement_origine_ar"
                                    v-model="form.etablissement_origine_ar"
                                    :class="{ 'p-invalid': submitted && errors.etablissement_origine_ar?.length }"
                                    placeholder="أدخل المؤسسة الأصلية بالعربية"
                                    style="font-size: 0.875rem"
                                    class="font-arabic"
                                    dir="rtl"
                                />
                                <small v-if="submitted && errors.etablissement_origine_ar?.length" class="p-error font-arabic" style="font-size: 0.75rem">
                                    {{ errors.etablissement_origine_ar[0] }}
                                </small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="centre_origine_fr" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Centre d'origine (Français)
                                </label>
                                <InputText
                                    id="centre_origine_fr"
                                    v-model="form.centre_origine_fr"
                                    :class="{ 'p-invalid': submitted && errors.centre_origine_fr?.length }"
                                    placeholder="Saisissez le centre d'origine en français"
                                    style="font-size: 0.875rem"
                                />
                                <small v-if="submitted && errors.centre_origine_fr?.length" class="p-error" style="font-size: 0.75rem">
                                    {{ errors.centre_origine_fr[0] }}
                                </small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="centre_origine_ar" class="block font-medium mb-1 font-arabic" style="font-size: 0.875rem">
                                    Centre d'origine (Arabe)
                                </label>
                                <InputText
                                    id="centre_origine_ar"
                                    v-model="form.centre_origine_ar"
                                    :class="{ 'p-invalid': submitted && errors.centre_origine_ar?.length }"
                                    placeholder="أدخل مركز المنشأ بالعربية"
                                    style="font-size: 0.875rem"
                                    class="font-arabic"
                                    dir="rtl"
                                />
                                <small v-if="submitted && errors.centre_origine_ar?.length" class="p-error font-arabic" style="font-size: 0.75rem">
                                    {{ errors.centre_origine_ar[0] }}
                                </small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="date_debut" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Date de Début <span class="text-red-500">*</span>
                                </label>
                                <Calendar
                                    id="date_debut"
                                    v-model="form.date_debut"
                                    :class="{ 'p-invalid': submitted && errors.date_debut?.length }"
                                    dateFormat="yy-mm-dd"
                                    showIcon
                                    placeholder="Sélectionner une date"
                                    style="font-size: 0.875rem"
                                    @date-select="validateDateDebut"
                                    @update:modelValue="validateDateDebut"
                                />
                                <small v-if="submitted && errors.date_debut?.length" class="p-error" style="font-size: 0.75rem">
                                    {{ errors.date_debut[0] }}
                                </small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="date_fin" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Date de Fin
                                </label>
                                <Calendar
                                    id="date_fin"
                                    v-model="form.date_fin"
                                    :class="{ 'p-invalid': submitted && errors.date_fin?.length }"
                                    dateFormat="yy-mm-dd"
                                    showIcon
                                    placeholder="Sélectionner une date"
                                    :minDate="form.date_debut"
                                    style="font-size: 0.875rem"
                                    @date-select="validateDateFin"
                                    @update:modelValue="validateDateFin"
                                />
                                <small v-if="submitted && errors.date_fin?.length" class="p-error" style="font-size: 0.75rem">
                                    {{ errors.date_fin[0] }}
                                </small>
                            </div>
                        </div>
                    </form>
                </TabPanel>
                <TabPanel header="Détails de l'association">
                    <div class="grid p-fluid">
                        <div class="col-12 md:col-6 field">
                            <label for="mission_fr" class="block font-medium mb-1" style="font-size: 0.875rem">
                                Mission (Français)
                            </label>
                            <Textarea
                                id="mission_fr"
                                v-model="form.mission_fr"
                                :class="{ 'p-invalid': submitted && errors.mission_fr?.length }"
                                placeholder="Saisissez la mission en français"
                                rows="4"
                                style="font-size: 0.875rem; width: 100%"
                            />
                            <small v-if="submitted && errors.mission_fr?.length" class="p-error" style="font-size: 0.75rem">
                                {{ errors.mission_fr[0] }}
                            </small>
                        </div>
                        <div class="col-12 md:col-6 field">
                            <label for="mission_ar" class="block font-medium mb-1 font-arabic" style="font-size: 0.875rem">
                                Mission (Arabe)
                            </label>
                            <Textarea
                                id="mission_ar"
                                v-model="form.mission_ar"
                                :class="{ 'p-invalid': submitted && errors.mission_ar?.length }"
                                placeholder="أدخل المهمة بالعربية"
                                rows="4"
                                style="font-size: 0.875rem; width: 100%"
                                class="font-arabic"
                                dir="rtl"
                            />
                            <small v-if="submitted && errors.mission_ar?.length" class="p-error font-arabic" style="font-size: 0.75rem">
                                {{ errors.mission_ar[0] }}
                            </small>
                        </div>
                        <div class="col-12 field">
                            <label for="statut" class="block font-medium mb-1" style="font-size: 0.875rem">
                                Statut <span class="text-red-500">*</span>
                            </label>
                            <div class="flex flex-wrap gap-3">
                                <div v-for="option in statutOptions" :key="option.value" class="flex align-items-center">
                                    <RadioButton
                                        v-model="form.statut"
                                        :inputId="'statut_' + option.value.toLowerCase()"
                                        name="statut"
                                        :value="option.value"
                                        :class="{ 'p-invalid': submitted && errors.statut?.length }"
                                    />
                                    <label :for="'statut_' + option.value.toLowerCase()" class="ml-2" style="font-size: 0.875rem">{{ option.label }}</label>
                                </div>
                            </div>
                            <small v-if="submitted && errors.statut?.length" class="p-error" style="font-size: 0.75rem">
                                {{ errors.statut[0] }}
                            </small>
                        </div>
                        <div class="col-12 field">
                            <label for="observation" class="block font-medium mb-1" style="font-size: 0.875rem">
                                Observation
                            </label>
                            <Textarea
                                id="observation"
                                v-model="form.observation"
                                :class="{ 'p-invalid': submitted && errors.observation?.length }"
                                placeholder="Saisissez vos observations"
                                rows="4"
                                style="font-size: 0.875rem; width: 100%"
                            />
                            <small v-if="submitted && errors.observation?.length" class="p-error" style="font-size: 0.75rem">
                                {{ errors.observation[0] }}
                            </small>
                        </div>
                    </div>
                </TabPanel>
            </TabView>
        </div>
        <template #footer>
            <div class="flex justify-content-end gap-3">
                <Button
                    label="Annuler"
                    icon="pi pi-times-circle"
                    class="p-button-outlined p-button-secondary p-button-sm"
                    @click="close"
                    style="font-size: 0.875rem"
                />
                <Button
                    :label="isEditing ? 'Mettre à jour' : 'Enregistrer'"
                    icon="pi pi-check-circle"
                    class="p-button-primary p-button-sm"
                    :loading="isSaving"
                    :disabled="!isFormValid || isSaving"
                    @click="saveAssociation"
                    style="font-size: 0.875rem"
                />
            </div>
        </template>
    </Dialog>
    <Dialog
        v-model:visible="showPreviewDialog"
        :modal="true"
        :style="{ width: '80vw', height: '90vh' }"
        :breakpoints="{ '960px': '95vw', '640px': '100vw' }"
        :pt="{
            root: 'border-round-xl shadow-5',
            mask: { style: 'backdrop-filter: blur(4px)' },
            header: { class: 'surface-50 border-bottom-1 surface-border p-4' },
            content: { class: 'p-0 h-full' },
            footer: { class: 'surface-50 border-top-1 surface-border p-4' }
        }"
    >
        <template #header>
            <div class="flex align-items-center gap-2">
                <i class="pi pi-file-pdf text-red-500 text-2xl"></i>
                <span class="font-bold text-2xl">{{ previewDocumentName || 'Prévisualisation du document' }}</span>
            </div>
        </template>
        <div v-if="previewLoading" class="flex justify-content-center align-items-center h-full">
            <ProgressSpinner style="width: 50px; height: 50px" strokeWidth="4" />
            <span class="ml-3 text-color-secondary">Chargement du PDF...</span>
        </div>
        <div v-else-if="previewError" class="flex justify-content-center align-items-center h-full flex-column gap-3">
            <i class="pi pi-exclamation-circle text-red-500 text-4xl"></i>
            <span class="text-color-secondary">{{ previewError }}</span>
        </div>
        <iframe
            v-else
            :src="previewDocumentUrl"
            class="w-full h-full border-none"
            title="Prévisualisation du document PDF"
        ></iframe>
        <template #footer>
            <div class="flex justify-content-end gap-3">
                <Button
                    label="Fermer"
                    icon="pi pi-times-circle"
                    class="p-button-outlined p-button-secondary p-button-sm"
                    @click="closeDialogPreview"
                    style="font-size: 0.875rem"
                />
            </div>
        </template>
    </Dialog>
    <Toast position="top-right" />
</template>

<script>
import { ref, computed, watch } from 'vue';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import Calendar from 'primevue/calendar';
import Textarea from 'primevue/textarea';
import InputText from 'primevue/inputtext';
import RadioButton from 'primevue/radiobutton';
import Toast from 'primevue/toast';
import ProgressSpinner from 'primevue/progressspinner';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import Tag from 'primevue/tag';

export default {
    components: {
        Button,
        Dialog,
        Dropdown,
        Calendar,
        Textarea,
        InputText,
        RadioButton,
        Toast,
        ProgressSpinner,
        TabView,
        TabPanel,
        Tag
    },
    props: {
        visible: { type: Boolean, required: true },
        user: { type: Object, default: null },
        associationToEdit: { type: Object, default: null },
    },
    emits: ['update:visible', 'close', 'save', 'update'],
    setup(props, { emit }) {
        const toast = useToast();
        const form = ref({
            fiche_pdf_fr: null,
            fiche_pdf_fr_name: '',
            fiche_pdf_ar: null,
            fiche_pdf_ar_name: '',
            centre_id: null,
            role_id: null,
            type_affectation_fr: null,
            type_affectation_ar: null,
            etablissement_origine_fr: '',
            etablissement_origine_ar: '',
            mission_fr: '',
            mission_ar: '',
            centre_origine_fr: '',
            centre_origine_ar: null,
            observation: '',
            date_debut: null,
            date_fin: null,
            statut: 'Actif',
        });
        const centres = ref([]);
        const roles = ref([]);
        const errors = ref({});
        const isSaving = ref(false);
        const loading = ref(false);
        const submitted = ref(false);
        const imageError = ref(false);
        const showPreviewDialog = ref(false);
        const previewDocumentUrl = ref(null);
        const previewDocumentName = ref('');
        const previewLoading = ref(false);
        const previewError = ref(null);
        const activeTab = ref(0);
        const typeAffectationOptions = ref([
            { label: 'Permanent', value: 'Permanent' },
            { label: 'Temporaire', value: 'Temporaire' },
        ]);
        const statutOptions = ref([
            { label: 'Actif', value: 'Actif' },
            { label: 'Inactif', value: 'Inactif' },
        ]);

        const isEditing = computed(() => !!props.associationToEdit);

        const isFormValid = computed(() => {
            const hasFile = form.value.fiche_pdf_fr || form.value.fiche_pdf_ar ||
                (isEditing.value && (props.associationToEdit?.fiche_pdf_fr || props.associationToEdit?.fiche_pdf_ar));
            return (
                !isSaving.value &&
                !!form.value.centre_id &&
                !!form.value.type_affectation_fr &&
                !!form.value.date_debut &&
                form.value.date_debut instanceof Date &&
                !isNaN(form.value.date_debut.getTime()) &&
                !!form.value.statut &&
                ['Actif', 'Inactif'].includes(form.value.statut) &&
                hasFile &&
                Object.keys(errors.value).every((key) => !errors.value[key]?.length)
            );
        });

        watch(
            () => props.visible,
            async (newVal) => {
                if (newVal) {
                    loading.value = true;
                    await fetchCentres();
                    await fetchRoles();
                    if (props.associationToEdit) {
                        form.value = {
                            fiche_pdf_fr: null,
                            fiche_pdf_fr_name: props.associationToEdit.fiche_pdf_fr ? 'Fichier existant' : '',
                            fiche_pdf_ar: null,
                            fiche_pdf_ar_name: props.associationToEdit.fiche_pdf_ar ? 'Fichier existant' : '',
                            centre_id: props.associationToEdit.centre_id || null,
                            role_id: props.associationToEdit.role_id || null,
                            type_affectation_fr: props.associationToEdit.type_affectation_fr || '',
                            type_affectation_ar: props.associationToEdit.type_affectation_ar || '',
                            etablissement_origine_fr: props.associationToEdit.etablissement_origine_fr || '',
                            etablissement_origine_ar: props.associationToEdit.etablissement_origine_ar || '',
                            mission_fr: props.associationToEdit.mission_fr || '',
                            mission_ar: props.associationToEdit.mission_ar || '',
                            centre_origine_fr: props.associationToEdit.centre_origine_fr || '',
                            centre_origine_ar: props.associationToEdit.centre_origine_ar || '',
                            observation: props.associationToEdit.observation || '',
                            date_debut: props.associationToEdit.date_debut ? new Date(props.associationToEdit.date_debut) : null,
                            date_fin: props.associationToEdit.date_fin ? new Date(props.associationToEdit.date_fin) : null,
                            statut: props.associationToEdit.statut || 'Actif',
                        };
                    } else {
                        resetForm();
                    }
                    validateFiles();
                    validateCentre();
                    validateRole();
                    validateTypeAffectation();
                    validateDateDebut();
                    validateDateFin();
                    validateStatut();
                    loading.value = false;
                } else {
                    resetForm();
                }
            },
            { immediate: true }
        );

        async function fetchCentres() {
            try {
                const response = await axios.get('/api/centres', {
                    headers: getHeaders()
                });
                centres.value = response.data.data || [];
                if (!centres.value.length) {
                    toast.add({
                        severity: 'warn',
                        summary: 'Avertissement',
                        detail: 'Aucun centre disponible.',
                        life: 5000,
                    });
                }
            } catch (error) {
                handleApiError(error, 'Impossible de charger les centres.');
            }
        }

        async function fetchRoles() {
            try {
                const response = await axios.get('/api/roles', {
                    headers: getHeaders()
                });
                roles.value = response.data.data || [];
                if (!roles.value.length) {
                    toast.add({
                        severity: 'warn',
                        summary: 'Avertissement',
                        detail: 'Aucun rôle disponible.',
                        life: 5000,
                    });
                }
            } catch (error) {
                handleApiError(error, 'Impossible de charger les rôles.');
            }
        }

        function getHeaders() {
            return {
                Authorization: `Bearer ${localStorage.getItem('token')}`,
            };
        }

        function getPhotoSrc(photo) {
            if (!photo || typeof photo !== 'string') {
                return '/default-avatar.png';
            }
            if (photo.startsWith('data:image/')) {
                return photo;
            }
            return '/default-avatar.png';
        }

        function onImageError() {
            if (!imageError.value) {
                imageError.value = true;
                toast.add({
                    severity: 'warn',
                    summary: 'Erreur d\'image',
                    detail: 'Impossible de charger la photo de l\'utilisateur.',
                    life: 3000,
                });
            }
        }

        function resetForm() {
            form.value = {
                fiche_pdf_fr: null,
                fiche_pdf_fr_name: '',
                fiche_pdf_ar: null,
                fiche_pdf_ar_name: '',
                centre_id: null,
                role_id: null,
                type_affectation_fr: null,
                type_affectation_ar: null,
                etablissement_origine_fr: '',
                etablissement_origine_ar: '',
                mission_fr: '',
                mission_ar: '',
                centre_origine_fr: '',
                centre_origine_ar: null,
                observation: '',
                date_debut: null,
                date_fin: null,
                statut: 'Actif',
            };
            errors.value = {};
            isSaving.value = false;
            submitted.value = false;
            imageError.value = false;
            showPreviewDialog.value = false;
            previewDocumentUrl.value = null;
            previewDocumentName.value = '';
            previewError.value = null;
            activeTab.value = 0;
        }

        function validateFiles() {
            errors.value.fiche_pdf_fr = [];
            errors.value.fiche_pdf_ar = [];
            if (!isEditing.value && !form.value.fiche_pdf_fr && !form.value.fiche_pdf_ar) {
                errors.value.fiche_pdf_fr = ['Au moins un fichier PDF (FR ou AR) est requis'];
                errors.value.fiche_pdf_ar = ['Au moins un fichier PDF (FR ou AR) est requis'];
            }
            if (form.value.fiche_pdf_fr) {
                if (form.value.fiche_pdf_fr.type !== 'application/pdf') {
                    errors.value.fiche_pdf_fr = ['Le fichier doit être un PDF'];
                } else if (form.value.fiche_pdf_fr.size > 10240000) {
                    errors.value.fiche_pdf_fr = ['Le fichier ne doit pas dépasser 10MB'];
                }
            }
            if (form.value.fiche_pdf_ar) {
                if (form.value.fiche_pdf_ar.type !== 'application/pdf') {
                    errors.value.fiche_pdf_ar = ['Le fichier doit être un PDF'];
                } else if (form.value.fiche_pdf_ar.size > 10240000) {
                    errors.value.fiche_pdf_ar = ['Le fichier ne doit pas dépasser 10MB'];
                }
            }
        }

        function validateCentre() {
            errors.value.centre_id = [];
            if (!form.value.centre_id) {
                errors.value.centre_id = ['Le centre est obligatoire'];
            }
        }

        function validateRole() {
            errors.value.role_id = [];
            // Le rôle est optionnel
        }

        function validateTypeAffectation() {
            errors.value.type_affectation_fr = [];
            if (!form.value.type_affectation_fr) {
                errors.value.type_affectation_fr = ['Le type d\'affectation est obligatoire'];
            } else {
                form.value.type_affectation_ar = form.value.type_affectation_fr === 'Permanent' ? 'دائم' : 'مؤقت';
            }
        }

        function validateDateDebut() {
            errors.value.date_debut = [];
            if (!form.value.date_debut || !(form.value.date_debut instanceof Date) || isNaN(form.value.date_debut.getTime())) {
                errors.value.date_debut = ['La date de début est obligatoire et doit être valide'];
            } else {
                validateDateFin();
            }
        }

        function validateDateFin() {
            errors.value.date_fin = [];
            if (form.value.date_fin && form.value.date_debut && form.value.date_fin < form.value.date_debut) {
                errors.value.date_fin = ['La date de fin doit être postérieure ou égale à la date de début'];
            }
        }

        function validateStatut() {
            errors.value.statut = [];
            if (!form.value.statut || !['Actif', 'Inactif'].includes(form.value.statut)) {
                errors.value.statut = ['Le statut est obligatoire et doit être "Actif" ou "Inactif"'];
            }
        }

        function formatDate(date) {
            if (!date || !(date instanceof Date) || isNaN(date.getTime())) {
                return '';
            }
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        function onFileSelect(event, field) {
            const file = event.target.files[0];
            if (!file) {
                form.value[field] = null;
                form.value[`${field}_name`] = '';
                validateFiles();
                toast.add({
                    severity: 'warn',
                    summary: 'Attention',
                    detail: 'Aucun fichier sélectionné.',
                    life: 3000,
                });
                return;
            }
            if (file.type !== 'application/pdf') {
                errors.value[field] = ['Seuls les fichiers PDF sont acceptés'];
                form.value[field] = null;
                form.value[`${field}_name`] = '';
                validateFiles();
                toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Seuls les fichiers PDF sont acceptés.',
                    life: 3000,
                });
                return;
            }
            if (file.size > 10240000) {
                errors.value[field] = ['Le fichier ne doit pas dépasser 10MB'];
                form.value[field] = null;
                form.value[`${field}_name`] = '';
                validateFiles();
                toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Le fichier ne doit pas dépasser 10MB.',
                    life: 3000,
                });
                return;
            }
            form.value[field] = file;
            form.value[`${field}_name`] = file.name;
            validateFiles();
        }

        async function previewFile(associationId, field) {
            if (!associationId || !field) {
                toast.add({
                    severity: 'warn',
                    summary: 'Attention',
                    detail: 'Aucun fichier à prévisualiser.',
                    life: 3000,
                });
                return;
            }
            try {
                previewLoading.value = true;
                previewError.value = null;
                previewDocumentName.value = `${field === 'fiche_pdf_fr' ? 'Fiche FR' : 'Fiche AR'} - ${associationId}.pdf`;
                const response = await axios.get(`/api/users-centres/preview/${associationId}/${field}`, {
                    headers: getHeaders(),
                    responseType: 'blob',
                });
                if (response.status !== 200) {
                    throw new Error(`Erreur HTTP ${response.status}: Impossible de charger le fichier.`);
                }
                const blob = new Blob([response.data], { type: 'application/pdf' });
                if (previewDocumentUrl.value) {
                    URL.revokeObjectURL(previewDocumentUrl.value);
                }
                previewDocumentUrl.value = URL.createObjectURL(blob);
                showPreviewDialog.value = true;
            } catch (error) {
                handleApiError(error, error.response?.status === 404
                    ? 'Document non trouvé.'
                    : `Échec de chargement du document PDF: ${error.message}`);
                previewError.value = error.response?.status === 404
                    ? 'Document non trouvé.'
                    : `Échec de chargement du document PDF: ${error.message}`;
                showPreviewDialog.value = true;
            } finally {
                previewLoading.value = false;
            }
        }

        async function saveAssociation() {
            submitted.value = true;
            validateFiles();
            validateCentre();
            validateRole();
            validateTypeAffectation();
            validateDateDebut();
            validateDateFin();
            validateStatut();
            if (Object.values(errors.value).some(error => error?.length > 0)) {
                toast.add({
                    severity: 'error',
                    summary: 'Erreur de validation',
                    detail: 'Veuillez corriger les erreurs dans le formulaire.',
                    life: 3000,
                });
                return;
            }
            if (!isEditing.value && !form.value.fiche_pdf_fr && !form.value.fiche_pdf_ar) {
                toast.add({
                    severity: 'warn',
                    summary: 'Validation',
                    detail: 'Au moins un fichier PDF (FR ou AR) est requis.',
                    life: 3000,
                });
                return;
            }
            isSaving.value = true;
            const formData = new FormData();
            formData.append('user_id', props.user?.id || '');
            formData.append('centre_id', form.value.centre_id || '');
            formData.append('role_id', form.value.role_id || '');
            formData.append('type_affectation_fr', form.value.type_affectation_fr || '');
            formData.append('type_affectation_ar', form.value.type_affectation_fr === 'Permanent' ? 'دائم' : 'مؤقت');
            formData.append('etablissement_origine_fr', form.value.etablissement_origine_fr || '');
            formData.append('etablissement_origine_ar', form.value.etablissement_origine_ar || '');
            formData.append('mission_fr', form.value.mission_fr || '');
            formData.append('mission_ar', form.value.mission_ar || '');
            formData.append('centre_origine_fr', form.value.centre_origine_fr || '');
            formData.append('centre_origine_ar', form.value.centre_origine_ar || '');
            formData.append('observation', form.value.observation || '');
            if (form.value.fiche_pdf_fr) {
                formData.append('fiche_pdf_fr', form.value.fiche_pdf_fr);
            }
            if (form.value.fiche_pdf_ar) {
                formData.append('fiche_pdf_ar', form.value.fiche_pdf_ar);
            }
            formData.append('date_debut', form.value.date_debut ? formatDate(form.value.date_debut) : '');
            formData.append('date_fin', form.value.date_fin ? formatDate(form.value.date_fin) : '');
            formData.append('statut', form.value.statut || '');
            formData.append('statut_ar', form.value.statut === 'Actif' ? 'نشط' : 'غير نشط');
            if (isEditing.value) {
                formData.append('_method', 'PUT');
            }
            try {
                let response;
                if (isEditing.value) {
                    response = await axios.post(`/api/users-centres/${props.associationToEdit.id}`, formData, {
                        headers: { 'Content-Type': 'multipart/form-data', ...getHeaders() },
                    });
                    emit('update', response.data.data);
                } else {
                    response = await axios.post('/api/users-centres', formData, {
                        headers: { 'Content-Type': 'multipart/form-data', ...getHeaders() },
                    });
                    emit('save', response.data.data);
                }
                toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: isEditing.value ? 'Association mise à jour avec succès' : 'Association créée avec succès',
                    life: 3000,
                });
                close();
            } catch (error) {
                handleApiError(error, error.response?.data?.message || "Erreur lors de l'enregistrement");
                if (error.response?.status === 422) {
                    errors.value = error.response.data.errors || error.response.data;
                }
            } finally {
                isSaving.value = false;
            }
        }

        function closeDialogPreview() {
            showPreviewDialog.value = false;
            if (previewDocumentUrl.value) {
                URL.revokeObjectURL(previewDocumentUrl.value);
            }
            previewDocumentUrl.value = null;
            previewDocumentName.value = '';
            previewError.value = null;
        }

        function close() {
            resetForm();
            emit('update:visible', false);
            emit('close');
        }

        function handleVisibleUpdate(newVisible) {
            if (!newVisible) {
                close();
            }
        }

        function handleApiError(error, defaultMessage) {
            const errorDetails = {
                message: error.message || 'Erreur inconnue',
                status: error.response?.status || 'N/A',
                data: error.response?.data || null,
                stack: error.stack || 'N/A'
            };
            console.error('Erreur API:', errorDetails);
            toast.add({
                severity: 'error',
                summary: 'Erreur',
                detail: error.response?.data?.message || defaultMessage,
                life: 5000,
            });
            if (error.response?.status === 401) {
                this.$router.push({ name: 'login' });
            }
        }

        return {
            toast,
            form,
            centres,
            roles,
            errors,
            isSaving,
            loading,
            submitted,
            imageError,
            showPreviewDialog,
            previewDocumentUrl,
            previewDocumentName,
            previewLoading,
            previewError,
            activeTab,
            typeAffectationOptions,
            statutOptions,
            isEditing,
            isFormValid,
            getPhotoSrc,
            onImageError,
            validateFiles,
            validateCentre,
            validateRole,
            validateTypeAffectation,
            validateDateDebut,
            validateDateFin,
            validateStatut,
            formatDate,
            onFileSelect,
            previewFile,
            saveAssociation,
            closeDialogPreview,
            close,
            handleVisibleUpdate,
        };
    },
};
</script>

<style scoped>
@font-face {
    font-family: 'Montserrat-Arabic-Regular';
    src: url('/fonts/Montserrat-Arabic-Regular.otf') format('opentype');
    font-weight: normal;
    font-style: normal;
    font-display: swap;
}
:deep(.p-tabview .p-tabview-panels) {
    padding-top: 1.5rem !important;
}
.field {
    margin-bottom: 0.5rem;
}
.form-spacing {
    margin-bottom: 1rem;
}
label.font-medium {
    color: var(--text-color, #2c3e50);
    font-weight: 500;
}
.arabic-text,
:deep(.p-inputtext[dir='rtl']),
:deep(.p-textarea[dir='rtl']) {
    font-family: 'Montserrat-Arabic-Regular', sans-serif;
    direction: rtl;
    text-align: right;
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
.p-error {
    color: #dc3545;
    font-size: 0.875rem;
}
</style>
