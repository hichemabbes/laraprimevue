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
                <i class="pi pi-building text-primary text-2xl"></i>
                <span class="font-bold text-2xl">{{
                    isEditMode ? 'Modifier le Centre' : 'Ajouter un Centre'
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
                                <small
                                    v-if="
                                        errors.code &&
                                        !(
                                            isEditMode &&
                                            errors.code.includes(
                                                'Ce code est déjà utilisé par un autre centre.'
                                            )
                                        )
                                    "
                                    class="p-error"
                                >
                                    {{ errors.code[0] }}
                                </small>
                            </div>
                            <!-- Nom Français -->
                            <div class="col-12 md:col-6 field">
                                <label
                                    for="nom_fr"
                                    class="block font-medium mb-2"
                                    >Nom (Français)
                                    <span class="text-red-500">*</span></label
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
                            <!-- Nom Arabe -->
                            <div class="col-12 md:col-6 field">
                                <label
                                    for="nom_ar"
                                    class="block font-medium mb-2 text-right font-arabic"
                                    >الاسم (العربية)</label
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
                            <!-- Adresse Français -->
                            <div class="col-12 md:col-6 field">
                                <label
                                    for="adresse_fr"
                                    class="block font-medium mb-2"
                                    >Adresse (Français)
                                    <span class="text-red-500">*</span></label
                                >
                                <InputText
                                    id="adresse_fr"
                                    v-model="form.adresse_fr"
                                    :class="{ 'p-invalid': errors.adresse_fr }"
                                    placeholder="Entrez l'adresse en français"
                                />
                                <small
                                    v-if="errors.adresse_fr"
                                    class="p-error"
                                    >{{ errors.adresse_fr[0] }}</small
                                >
                            </div>
                            <!-- Adresse Arabe -->
                            <div class="col-12 md:col-6 field">
                                <label
                                    for="adresse_ar"
                                    class="block font-medium mb-2 text-right font-arabic"
                                    >العنوان (العربية)</label
                                >
                                <InputText
                                    id="adresse_ar"
                                    v-model="form.adresse_ar"
                                    dir="rtl"
                                    :class="{ 'p-invalid': errors.adresse_ar }"
                                    placeholder="أدخل العنوان بالعربية"
                                />
                                <small
                                    v-if="errors.adresse_ar"
                                    class="p-error"
                                    >{{ errors.adresse_ar[0] }}</small
                                >
                            </div>
                            <!-- Gouvernorat -->
                            <div class="col-12 md:col-6 field">
                                <label
                                    for="gouvernorat_fr"
                                    class="block font-medium mb-2"
                                    >Gouvernorat
                                    <span class="text-red-500">*</span></label
                                >
                                <Dropdown
                                    id="gouvernorat_fr"
                                    v-model="form.gouvernorat_fr"
                                    :options="gouvernoratOptions"
                                    optionLabel="nom_fr"
                                    optionValue="nom_fr"
                                    placeholder="Sélectionner un gouvernorat"
                                    :class="{
                                        'p-invalid': errors.gouvernorat_fr,
                                    }"
                                    :loading="loadingLists"
                                />
                                <small
                                    v-if="errors.gouvernorat_fr"
                                    class="p-error"
                                    >{{ errors.gouvernorat_fr[0] }}</small
                                >
                            </div>
                            <!-- Type Centre -->
                            <div class="col-12 md:col-6 field">
                                <label
                                    for="type_centre_fr"
                                    class="block font-medium mb-2"
                                    >Type de Centre</label
                                >
                                <Dropdown
                                    id="type_centre_fr"
                                    v-model="form.type_centre_fr"
                                    :options="typeCentreOptions"
                                    optionLabel="nom_fr"
                                    optionValue="nom_fr"
                                    placeholder="Sélectionner un type"
                                    :class="{
                                        'p-invalid': errors.type_centre_fr,
                                    }"
                                    :loading="loadingLists"
                                />
                                <small
                                    v-if="errors.type_centre_fr"
                                    class="p-error"
                                    >{{ errors.type_centre_fr[0] }}</small
                                >
                            </div>
                            <!-- Classe -->
                            <div class="col-12 md:col-6 field">
                                <label
                                    for="classe_fr"
                                    class="block font-medium mb-2"
                                    >Classe</label
                                >
                                <Dropdown
                                    id="classe_fr"
                                    v-model="form.classe_fr"
                                    :options="classeOptions"
                                    optionLabel="nom_fr"
                                    optionValue="nom_fr"
                                    placeholder="Sélectionner une classe"
                                    :class="{ 'p-invalid': errors.classe_fr }"
                                    :loading="loadingLists"
                                />
                                <small
                                    v-if="errors.classe_fr"
                                    class="p-error"
                                    >{{ errors.classe_fr[0] }}</small
                                >
                            </div>
                            <!-- Économat -->
                            <div class="col-12 md:col-6 field">
                                <label
                                    for="economat_fr"
                                    class="block font-medium mb-2"
                                    >Économat</label
                                >
                                <Dropdown
                                    id="economat_fr"
                                    v-model="form.economat_fr"
                                    :options="economatOptions"
                                    optionLabel="nom_fr"
                                    optionValue="nom_fr"
                                    placeholder="Sélectionner un économat"
                                    :class="{ 'p-invalid': errors.economat_fr }"
                                    :loading="loadingLists"
                                />
                                <small
                                    v-if="errors.economat_fr"
                                    class="p-error"
                                    >{{ errors.economat_fr[0] }}</small
                                >
                            </div>
                            <!-- État -->
                            <div class="col-12 md:col-6 field">
                                <label
                                    for="etat_fr"
                                    class="block font-medium mb-2"
                                    >État</label
                                >
                                <Dropdown
                                    id="etat_fr"
                                    v-model="form.etat_fr"
                                    :options="etatOptions"
                                    optionLabel="nom_fr"
                                    optionValue="nom_fr"
                                    placeholder="Sélectionner un état"
                                    :class="{ 'p-invalid': errors.etat_fr }"
                                    :loading="loadingLists"
                                />
                                <small v-if="errors.etat_fr" class="p-error">{{
                                    errors.etat_fr[0]
                                }}</small>
                            </div>
                            <!-- Statut -->
                            <div class="col-12 md:col-6 field">
                                <label
                                    for="statut_fr"
                                    class="block font-medium mb-2"
                                    >Statut
                                    <span class="text-red-500">*</span></label
                                >
                                <Dropdown
                                    id="statut_fr"
                                    v-model="form.statut_fr"
                                    :options="statutOptions"
                                    optionLabel="nom_fr"
                                    optionValue="nom_fr"
                                    placeholder="Sélectionner un statut"
                                    :class="{ 'p-invalid': errors.statut_fr }"
                                    :loading="loadingLists"
                                />
                                <small
                                    v-if="errors.statut_fr"
                                    class="p-error"
                                    >{{ errors.statut_fr[0] }}</small
                                >
                            </div>
                        </div>
                    </TabPanel>

                    <!-- Coordonnées -->
                    <TabPanel header="Coordonnées">
                        <div class="grid p-fluid">
                            <!-- Téléphone 1 -->
                            <div class="col-12 md:col-6 field">
                                <label
                                    for="telephone_1"
                                    class="block font-medium mb-2"
                                    >Téléphone 1</label
                                >
                                <InputText
                                    id="telephone_1"
                                    v-model="form.telephone_1"
                                    :class="{ 'p-invalid': errors.telephone_1 }"
                                    placeholder="Entrez le numéro de téléphone 1"
                                />
                                <small
                                    v-if="errors.telephone_1"
                                    class="p-error"
                                    >{{ errors.telephone_1[0] }}</small
                                >
                            </div>
                            <!-- Téléphone 2 -->
                            <div class="col-12 md:col-6 field">
                                <label
                                    for="telephone_2"
                                    class="block font-medium mb-2"
                                    >Téléphone 2</label
                                >
                                <InputText
                                    id="telephone_2"
                                    v-model="form.telephone_2"
                                    :class="{ 'p-invalid': errors.telephone_2 }"
                                    placeholder="Entrez le numéro de téléphone 2"
                                />
                                <small
                                    v-if="errors.telephone_2"
                                    class="p-error"
                                    >{{ errors.telephone_2[0] }}</small
                                >
                            </div>
                            <!-- Fax 1 -->
                            <div class="col-12 md:col-6 field">
                                <label
                                    for="fax_1"
                                    class="block font-medium mb-2"
                                    >Fax 1</label
                                >
                                <InputText
                                    id="fax_1"
                                    v-model="form.fax_1"
                                    :class="{ 'p-invalid': errors.fax_1 }"
                                    placeholder="Entrez le numéro de fax 1"
                                />
                                <small v-if="errors.fax_1" class="p-error">{{
                                    errors.fax_1[0]
                                }}</small>
                            </div>
                            <!-- Fax 2 -->
                            <div class="col-12 md:col-6 field">
                                <label
                                    for="fax_2"
                                    class="block font-medium mb-2"
                                    >Fax 2</label
                                >
                                <InputText
                                    id="fax_2"
                                    v-model="form.fax_2"
                                    :class="{ 'p-invalid': errors.fax_2 }"
                                    placeholder="Entrez le numéro de fax 2"
                                />
                                <small v-if="errors.fax_2" class="p-error">{{
                                    errors.fax_2[0]
                                }}</small>
                            </div>
                            <!-- Email -->
                            <div class="col-12 md:col-6 field">
                                <label
                                    for="email"
                                    class="block font-medium mb-2"
                                    >Email</label
                                >
                                <InputText
                                    id="email"
                                    v-model="form.email"
                                    :class="{ 'p-invalid': errors.email }"
                                    placeholder="Entrez l'adresse email"
                                />
                                <small v-if="errors.email" class="p-error">{{
                                    errors.email[0]
                                }}</small>
                            </div>
                        </div>
                    </TabPanel>

                    <!-- Dates -->
                    <TabPanel header="Dates">
                        <div class="grid p-fluid">
                            <!-- Date de Création -->
                            <div class="col-12 md:col-4 field">
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
                            <!-- Date d'Ouverture -->
                            <div class="col-12 md:col-4 field">
                                <label
                                    for="date_ouverture"
                                    class="block font-medium mb-2"
                                    >Date d'Ouverture</label
                                >
                                <Calendar
                                    id="date_ouverture"
                                    v-model="form.date_ouverture"
                                    :class="{
                                        'p-invalid': errors.date_ouverture,
                                    }"
                                    dateFormat="yy-mm-dd"
                                    showIcon
                                />
                                <small
                                    v-if="errors.date_ouverture"
                                    class="p-error"
                                    >{{ errors.date_ouverture[0] }}</small
                                >
                            </div>
                            <!-- Date de Fermeture -->
                            <div class="col-12 md:col-4 field">
                                <label
                                    for="date_fermeture"
                                    class="block font-medium mb-2"
                                    >Date de Fermeture</label
                                >
                                <Calendar
                                    id="date_fermeture"
                                    v-model="form.date_fermeture"
                                    :class="{
                                        'p-invalid': errors.date_fermeture,
                                    }"
                                    dateFormat="yy-mm-dd"
                                    showIcon
                                />
                                <small
                                    v-if="errors.date_fermeture"
                                    class="p-error"
                                    >{{ errors.date_fermeture[0] }}</small
                                >
                            </div>
                        </div>
                    </TabPanel>

                    <!-- Observations -->
                    <TabPanel header="Observations">
                        <div class="grid p-fluid">
                            <!-- Observation Français -->
                            <div class="col-12 md:col-6 field">
                                <label
                                    for="observation_fr"
                                    class="block font-medium mb-2"
                                    >Observation (Français)</label
                                >
                                <Textarea
                                    id="observation_fr"
                                    v-model="form.observation_fr"
                                    rows="4"
                                    :class="{
                                        'p-invalid': errors.observation_fr,
                                    }"
                                    placeholder="Entrez l'observation en français"
                                />
                                <small
                                    v-if="errors.observation_fr"
                                    class="p-error"
                                    >{{ errors.observation_fr[0] }}</small
                                >
                            </div>
                            <!-- Observation Arabe -->
                            <div class="col-12 md:col-6 field">
                                <label
                                    for="observation_ar"
                                    class="block font-medium mb-2 text-right font-arabic"
                                    >الملاحظات (العربية)</label
                                >
                                <Textarea
                                    id="observation_ar"
                                    v-model="form.observation_ar"
                                    rows="4"
                                    dir="rtl"
                                    :class="{
                                        'p-invalid': errors.observation_ar,
                                    }"
                                    placeholder="أدخل الملاحظات بالعربية"
                                />
                                <small
                                    v-if="errors.observation_ar"
                                    class="p-error"
                                    >{{ errors.observation_ar[0] }}</small
                                >
                            </div>
                        </div>
                    </TabPanel>

                    <!-- Logo -->
                    <TabPanel header="Logo">
                        <div class="grid p-fluid">
                            <div class="col-12 md:col-6 field">
                                <label for="logo" class="block font-medium mb-2"
                                    >Logo</label
                                >
                                <FileUpload
                                    ref="fileUpload"
                                    name="logo"
                                    accept="image/png,image/jpeg,image/jpg,image/svg+xml"
                                    :maxFileSize="2000000"
                                    :customUpload="true"
                                    chooseLabel="Choisir"
                                    uploadLabel="Télécharger"
                                    cancelLabel="Annuler"
                                    @select="onFileSelect"
                                    @uploader="onUploader"
                                    @clear="onFileClear"
                                    :class="{ 'p-invalid': errors.logo }"
                                >
                                    <template #empty>
                                        <p>
                                            Glissez-déposez une image ici ou
                                            cliquez pour sélectionner (PNG, JPG,
                                            JPEG, SVG, max 2Mo).
                                        </p>
                                    </template>
                                </FileUpload>
                                <small v-if="errors.logo" class="p-error">{{
                                    errors.logo[0]
                                }}</small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label class="block font-medium mb-2"
                                    >Aperçu et Recadrage du Logo</label
                                >
                                <!-- Affichage du Cropper pour recadrage -->
                                <div
                                    v-if="showCropper"
                                    class="cropper-container"
                                >
                                    <Cropper
                                        :src="previewImage"
                                        :stencil-props="{
                                            aspectRatio: null,
                                            movable: true,
                                            resizable: true,
                                            resizeHandlers: {
                                                enabled: true,
                                                positions: ['bottom-right'],
                                            },
                                            style: {
                                                border: '2px solid #3b82f6',
                                                borderRadius: '0',
                                                background: 'transparent',
                                            },
                                            initialSize: {
                                                width: 200,
                                                height: 100,
                                            },
                                        }"
                                        :canvas="{
                                            minWidth: 100,
                                            minHeight: 100,
                                        }"
                                        @change="onCropChange"
                                        @ready="onCropperReady"
                                    />
                                    <div class="flex gap-2 mt-3">
                                        <Button
                                            label="Confirmer"
                                            icon="pi pi-check"
                                            class="p-button-success"
                                            @click="confirmCrop"
                                        />
                                        <Button
                                            label="Annuler"
                                            icon="pi pi-times"
                                            class="p-button-secondary"
                                            @click="cancelCrop"
                                        />
                                    </div>
                                </div>
                                <!-- Affichage de l'aperçu du logo -->
                                <div
                                    v-else-if="
                                        logoPreview ||
                                        (form.logo && isValidLogo(form.logo))
                                    "
                                    class="flex align-items-center gap-3"
                                >
                                    <img
                                        :src="
                                            logoPreview || getLogoUrl(form.logo)
                                        "
                                        alt="Aperçu du logo"
                                        class="border-round shadow-2"
                                        style="
                                            max-width: 200px;
                                            max-height: 200px;
                                            object-fit: contain;
                                        "
                                    />
                                    <div class="flex flex-column gap-2">
                                        <Button
                                            label="Recadrer"
                                            icon="pi pi-crop"
                                            class="p-button-info p-button-outlined"
                                            @click="startCroppingExistingLogo"
                                            v-if="
                                                form.logo &&
                                                isValidLogo(form.logo)
                                            "
                                        />
                                        <Button
                                            label="Supprimer le logo"
                                            icon="pi pi-trash"
                                            class="p-button-danger p-button-outlined"
                                            @click="deleteLogo"
                                        />
                                    </div>
                                </div>
                                <!-- État par défaut -->
                                <div
                                    v-else
                                    class="flex flex-column align-items-center gap-2"
                                >
                                    <i
                                        class="pi pi-image text-5xl text-surface-300"
                                    ></i>
                                    <small
                                        class="text-error"
                                        v-if="imageError"
                                        >{{ imageError }}</small
                                    >
                                    <p class="text-500">
                                        Aucune image sélectionnée ou existante.
                                    </p>
                                </div>
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
                    label="Enregistrer"
                    icon="pi pi-check"
                    class="p-button-primary"
                    @click="submitForm"
                    :loading="submitting"
                />
            </div>
        </template>
    </Dialog>
</template>

<script>
import { ref, computed, watch } from 'vue';
import { nextTick } from 'vue';
import axios from 'axios';
import Button from 'primevue/button';
import Calendar from 'primevue/calendar';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import FileUpload from 'primevue/fileupload';
import InputText from 'primevue/inputtext';
import ProgressSpinner from 'primevue/progressspinner';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import Textarea from 'primevue/textarea';
import { useToast } from 'primevue/usetoast';
import { Cropper } from 'vue-advanced-cropper';
import 'vue-advanced-cropper/dist/style.css';

export default {
    components: {
        Button,
        Calendar,
        Dialog,
        Dropdown,
        FileUpload,
        InputText,
        ProgressSpinner,
        TabView,
        TabPanel,
        Textarea,
        Cropper,
    },
    props: {
        visible: { type: Boolean, required: true },
        centreId: { type: Number, default: null },
        centreData: { type: Object, default: null },
    },
    emits: ['update:visible', 'close', 'save', 'update'],
    setup(props, { emit }) {
        const toast = useToast();
        const form = ref({
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
        });
        const errors = ref({});
        const loading = ref(false);
        const loadingLists = ref(false);
        const submitting = ref(false);
        const logoPreview = ref(null);
        const selectedFile = ref(null);
        const previewImage = ref(null);
        const croppedImage = ref(null);
        const imageError = ref(null);
        const showCropper = ref(false);
        const fileUpload = ref(null);
        const tabview = ref(null);
        const activeTabIndex = ref(0);
        const gouvernoratOptions = ref([]);
        const typeCentreOptions = ref([]);
        const classeOptions = ref([]);
        const economatOptions = ref([]);
        const etatOptions = ref([]);
        const statutOptions = ref([]);

        const isEditMode = computed(
            () => !!props.centreId || !!props.centreData
        );

        // Réinitialiser le formulaire à chaque ouverture pour un nouvel ajout
        watch(
            () => props.visible,
            (newValue) => {
                if (newValue && !isEditMode.value) {
                    resetForm();
                }
                if (newValue) {
                    nextTick(() => {
                        const codeInput = document.getElementById('code');
                        if (codeInput && !isEditMode.value) {
                            codeInput.focus();
                        }
                    });
                }
            }
        );

        watch(
            () => props.centreId,
            async (newId) => {
                if (newId) {
                    await fetchCentreData(newId);
                }
            },
            { immediate: true }
        );

        watch(
            () => props.centreData,
            (newData) => {
                if (newData) {
                    Object.assign(form.value, newData);
                    if (newData.logo) {
                        logoPreview.value = newData.logo;
                    }
                }
            },
            { immediate: true }
        );

        async function fetchCentreData(id) {
            loading.value = true;
            try {
                const response = await axios.get(
                    `/api/centres/${id}/with-options`
                );
                Object.assign(form.value, {
                    ...response.data.centre,
                    code: response.data.centre.code ?? '',
                });
                gouvernoratOptions.value =
                    response.data.lists['Gouvernorats'] || [];
                typeCentreOptions.value =
                    response.data.lists['Types Centres'] || [];
                classeOptions.value =
                    response.data.lists['Classes Centres'] || [];
                economatOptions.value = response.data.lists['Economats'] || [];
                etatOptions.value = response.data.lists['Etats Centres'] || [];
                statutOptions.value =
                    response.data.lists['Statuts Centres'] || [];
                if (response.data.centre.logo) {
                    logoPreview.value = response.data.centre.logo;
                }
            } catch (error) {
                toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        'Impossible de charger les données du centre.',
                    life: 3000,
                });
            } finally {
                loading.value = false;
            }
        }

        async function fetchLists() {
            loadingLists.value = true;
            try {
                const response = await axios.get('/api/centres-with-options');
                gouvernoratOptions.value =
                    response.data.lists['Gouvernorats'] || [];
                typeCentreOptions.value =
                    response.data.lists['Types Centres'] || [];
                classeOptions.value =
                    response.data.lists['Classes Centres'] || [];
                economatOptions.value = response.data.lists['Economats'] || [];
                etatOptions.value = response.data.lists['Etats Centres'] || [];
                statutOptions.value =
                    response.data.lists['Statuts Centres'] || [];
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

        if (!isEditMode.value) {
            fetchLists();
        }

        function resetForm() {
            form.value = {
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
            errors.value = {};
            logoPreview.value = null;
            selectedFile.value = null;
            previewImage.value = null;
            croppedImage.value = null;
            imageError.value = null;
            showCropper.value = false;
            activeTabIndex.value = 0;
            if (fileUpload.value) {
                fileUpload.value.clear();
            }
        }

        return {
            toast,
            form,
            errors,
            loading,
            loadingLists,
            submitting,
            logoPreview,
            selectedFile,
            previewImage,
            croppedImage,
            imageError,
            showCropper,
            fileUpload,
            tabview,
            activeTabIndex,
            gouvernoratOptions,
            typeCentreOptions,
            classeOptions,
            economatOptions,
            etatOptions,
            statutOptions,
            isEditMode,
            resetForm,
        };
    },
    methods: {
        async submitForm() {
            this.submitting = true;
            this.errors = {};

            // Validation côté client pour les champs obligatoires
            const requiredFields = {
                code: 'Le code est requis.',
                nom_fr: 'Le nom en français est requis.',
                adresse_fr: "L'adresse en français est requise.",
                gouvernorat_fr: 'Le gouvernorat est requis.',
                statut_fr: 'Le statut est requis.',
            };

            for (const [field, message] of Object.entries(requiredFields)) {
                if (
                    !this.form[field] ||
                    (typeof this.form[field] === 'string' &&
                        this.form[field].trim() === '')
                ) {
                    this.errors[field] = [message];
                }
            }

            // Validation côté client pour les champs optionnels (telephone_1, telephone_2, fax_1, fax_2, email)
            const phoneFaxRegex = /^\+?\d{8,15}$/;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (
                this.form.telephone_1 &&
                !phoneFaxRegex.test(
                    this.form.telephone_1.replace(/[^+\d]/g, '')
                )
            ) {
                this.errors.telephone_1 = [
                    'Le numéro de téléphone 1 doit contenir entre 8 et 15 chiffres.',
                ];
            }
            if (
                this.form.telephone_2 &&
                !phoneFaxRegex.test(
                    this.form.telephone_2.replace(/[^+\d]/g, '')
                )
            ) {
                this.errors.telephone_2 = [
                    'Le numéro de téléphone 2 doit contenir entre 8 et 15 chiffres.',
                ];
            }
            if (
                this.form.fax_1 &&
                !phoneFaxRegex.test(this.form.fax_1.replace(/[^+\d]/g, ''))
            ) {
                this.errors.fax_1 = [
                    'Le numéro de fax 1 doit contenir entre 8 et 15 chiffres.',
                ];
            }
            if (
                this.form.fax_2 &&
                !phoneFaxRegex.test(this.form.fax_2.replace(/[^+\d]/g, ''))
            ) {
                this.errors.fax_2 = [
                    'Le numéro de fax 2 doit contenir entre 8 et 15 chiffres.',
                ];
            }
            if (this.form.email && !emailRegex.test(this.form.email)) {
                this.errors.email = ["L'adresse email doit être valide."];
            }

            // Validation des longueurs maximales
            if (this.form.code && this.form.code.length > 20) {
                this.errors.code = [
                    'Le code ne doit pas dépasser 20 caractères.',
                ];
            }
            if (this.form.nom_fr && this.form.nom_fr.length > 255) {
                this.errors.nom_fr = [
                    'Le nom en français ne doit pas dépasser 255 caractères.',
                ];
            }
            if (this.form.nom_ar && this.form.nom_ar.length > 255) {
                this.errors.nom_ar = [
                    'Le nom en arabe ne doit pas dépasser 255 caractères.',
                ];
            }
            if (this.form.adresse_fr && this.form.adresse_fr.length > 255) {
                this.errors.adresse_fr = [
                    "L'adresse en français ne doit pas dépasser 255 caractères.",
                ];
            }
            if (this.form.adresse_ar && this.form.adresse_ar.length > 255) {
                this.errors.adresse_ar = [
                    "L'adresse en arabe ne doit pas dépasser 255 caractères.",
                ];
            }
            if (this.form.telephone_1 && this.form.telephone_1.length > 20) {
                this.errors.telephone_1 = [
                    'Le numéro de téléphone 1 ne doit pas dépasser 20 caractères.',
                ];
            }
            if (this.form.telephone_2 && this.form.telephone_2.length > 20) {
                this.errors.telephone_2 = [
                    'Le numéro de téléphone 2 ne doit pas dépasser 20 caractères.',
                ];
            }
            if (this.form.fax_1 && this.form.fax_1.length > 20) {
                this.errors.fax_1 = [
                    'Le numéro de fax 1 ne doit pas dépasser 20 caractères.',
                ];
            }
            if (this.form.fax_2 && this.form.fax_2.length > 20) {
                this.errors.fax_2 = [
                    'Le numéro de fax 2 ne doit pas dépasser 20 caractères.',
                ];
            }
            if (this.form.email && this.form.email.length > 255) {
                this.errors.email = [
                    "L'email ne doit pas dépasser 255 caractères.",
                ];
            }

            // Validation des dates
            if (
                this.form.date_ouverture &&
                this.form.date_creation &&
                new Date(this.form.date_ouverture) <
                    new Date(this.form.date_creation)
            ) {
                this.errors.date_ouverture = [
                    "La date d'ouverture doit être postérieure ou égale à la date de création.",
                ];
            }
            if (
                this.form.date_fermeture &&
                this.form.date_ouverture &&
                new Date(this.form.date_fermeture) <
                    new Date(this.form.date_ouverture)
            ) {
                this.errors.date_fermeture = [
                    "La date de fermeture doit être postérieure ou égale à la date d'ouverture.",
                ];
            }

            if (Object.keys(this.errors).length > 0) {
                // Déterminer l'onglet à afficher en fonction des erreurs
                const errorFields = Object.keys(this.errors);
                if (
                    errorFields.some((field) =>
                        [
                            'code',
                            'nom_fr',
                            'nom_ar',
                            'adresse_fr',
                            'adresse_ar',
                            'gouvernorat_fr',
                            'type_centre_fr',
                            'classe_fr',
                            'economat_fr',
                            'etat_fr',
                            'statut_fr',
                        ].includes(field)
                    )
                ) {
                    this.activeTabIndex = 0; // Informations Générales
                } else if (
                    errorFields.some((field) =>
                        [
                            'telephone_1',
                            'telephone_2',
                            'fax_1',
                            'fax_2',
                            'email',
                        ].includes(field)
                    )
                ) {
                    this.activeTabIndex = 1; // Coordonnées
                } else if (
                    errorFields.some((field) =>
                        [
                            'date_creation',
                            'date_ouverture',
                            'date_fermeture',
                        ].includes(field)
                    )
                ) {
                    this.activeTabIndex = 2; // Dates
                } else if (
                    errorFields.some((field) =>
                        ['observation_fr', 'observation_ar'].includes(field)
                    )
                ) {
                    this.activeTabIndex = 3; // Observations
                } else if (errorFields.includes('logo')) {
                    this.activeTabIndex = 4; // Logo
                }

                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur de validation',
                    detail: 'Veuillez vérifier les champs du formulaire.',
                    life: 3000,
                });
                this.submitting = false;
                return;
            }

            // Préparer les données JSON
            const data = {
                code: this.form.code ?? '',
                nom_fr: this.form.nom_fr,
                nom_ar: this.form.nom_ar,
                adresse_fr: this.form.adresse_fr,
                adresse_ar: this.form.adresse_ar,
                telephone_1: this.form.telephone_1
                    ? this.form.telephone_1.replace(/[^+\d]/g, '')
                    : null,
                telephone_2: this.form.telephone_2
                    ? this.form.telephone_2.replace(/[^+\d]/g, '')
                    : null,
                fax_1: this.form.fax_1
                    ? this.form.fax_1.replace(/[^+\d]/g, '')
                    : null,
                fax_2: this.form.fax_2
                    ? this.form.fax_2.replace(/[^+\d]/g, '')
                    : null,
                email: this.form.email,
                gouvernorat_fr: this.form.gouvernorat_fr,
                type_centre_fr: this.form.type_centre_fr,
                classe_fr: this.form.classe_fr,
                economat_fr: this.form.economat_fr,
                etat_fr: this.form.etat_fr,
                statut_fr: this.form.statut_fr,
                observation_fr: this.form.observation_fr,
                observation_ar: this.form.observation_ar,
                logo: this.croppedImage || this.form.logo, // Utiliser l'image recadrée si disponible
            };

            // Formatter les dates
            ['date_creation', 'date_ouverture', 'date_fermeture'].forEach(
                (key) => {
                    if (this.form[key]) {
                        const date = new Date(this.form[key]);
                        if (!isNaN(date)) {
                            data[key] = date.toISOString().split('T')[0];
                        }
                    }
                }
            );

            try {
                let response;
                if (this.isEditMode) {
                    response = await axios.put(
                        `/api/centres/${this.centreId}`,
                        data
                    );
                    this.$emit('update', response.data);
                } else {
                    response = await axios.post('/api/centres', data);
                    this.$emit('save', response.data);
                }

                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: this.isEditMode
                        ? 'Centre mis à jour avec succès.'
                        : 'Centre ajouté avec succès.',
                    life: 3000,
                });

                this.cancelForm();
            } catch (error) {
                if (error.response?.status === 422) {
                    this.errors = error.response.data.errors;
                    // Déterminer l'onglet à afficher en fonction des erreurs serveur
                    const errorFields = Object.keys(this.errors);
                    if (
                        errorFields.some((field) =>
                            [
                                'code',
                                'nom_fr',
                                'nom_ar',
                                'adresse_fr',
                                'adresse_ar',
                                'gouvernorat_fr',
                                'type_centre_fr',
                                'classe_fr',
                                'economat_fr',
                                'etat_fr',
                                'statut_fr',
                            ].includes(field)
                        )
                    ) {
                        this.activeTabIndex = 0; // Informations Générales
                    } else if (
                        errorFields.some((field) =>
                            [
                                'telephone_1',
                                'telephone_2',
                                'fax_1',
                                'fax_2',
                                'email',
                            ].includes(field)
                        )
                    ) {
                        this.activeTabIndex = 1; // Coordonnées
                    } else if (
                        errorFields.some((field) =>
                            [
                                'date_creation',
                                'date_ouverture',
                                'date_fermeture',
                            ].includes(field)
                        )
                    ) {
                        this.activeTabIndex = 2; // Dates
                    } else if (
                        errorFields.some((field) =>
                            ['observation_fr', 'observation_ar'].includes(field)
                        )
                    ) {
                        this.activeTabIndex = 3; // Observations
                    } else if (errorFields.includes('logo')) {
                        this.activeTabIndex = 4; // Logo
                    }
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
        onFileSelect(event) {
            try {
                this.imageError = null;
                if (event.files && event.files.length > 0) {
                    const file = event.files[0];
                    if (file.size > 2000000) {
                        this.imageError =
                            "La taille de l'image ne doit pas dépasser 2 Mo";
                        this.toast.add({
                            severity: 'error',
                            summary: 'Erreur',
                            detail: this.imageError,
                            life: 3000,
                        });
                        return;
                    }
                    if (!file.type.match('image/(jpg|jpeg|png|svg\+xml)')) {
                        this.imageError =
                            'Veuillez sélectionner une image valide (JPG, PNG, SVG)';
                        this.toast.add({
                            severity: 'error',
                            summary: 'Erreur',
                            detail: this.imageError,
                            life: 3000,
                        });
                        return;
                    }
                    this.selectedFile = file;
                    this.previewImage = URL.createObjectURL(file);
                    this.showCropper = true;
                    this.logoPreview = null;
                    this.croppedImage = null;
                    this.toast.add({
                        severity: 'info',
                        summary: 'Fichier sélectionné',
                        detail: 'Recadrez l\'image et confirmez, ou cliquez sur "Télécharger" pour continuer.',
                        life: 3000,
                    });
                }
            } catch (err) {
                this.imageError =
                    err.message || "Erreur lors de la sélection de l'image";
                this.selectedFile = null;
                this.previewImage = null;
                this.showCropper = false;
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: this.imageError,
                    life: 3000,
                });
            }
        },
        onUploader(event) {
            if (this.selectedFile) {
                const imageToUse = this.croppedImage || this.previewImage;
                if (imageToUse) {
                    this.form.logo = imageToUse;
                    this.logoPreview = imageToUse;
                    this.errors.logo = null;
                    this.selectedFile = null;
                    this.previewImage = null;
                    this.croppedImage = null;
                    this.showCropper = false;
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Logo téléchargé avec succès. Enregistrez pour sauvegarder.',
                        life: 3000,
                    });
                } else {
                    this.toast.add({
                        severity: 'warn',
                        summary: 'Aucun fichier',
                        detail: 'Veuillez confirmer le recadrage ou sélectionner une image.',
                        life: 3000,
                    });
                }
            } else {
                this.toast.add({
                    severity: 'warn',
                    summary: 'Aucun fichier',
                    detail: 'Veuillez sélectionner un fichier avant de télécharger.',
                    life: 3000,
                });
            }
        },
        onFileClear() {
            this.selectedFile = null;
            this.previewImage = null;
            this.croppedImage = null;
            this.imageError = null;
            this.showCropper = false;
            this.toast.add({
                severity: 'info',
                summary: 'Annulé',
                detail: 'Sélection du fichier annulée.',
                life: 3000,
            });
        },
        deleteLogo() {
            this.form.logo = null;
            this.logoPreview = null;
            this.selectedFile = null;
            this.previewImage = null;
            this.croppedImage = null;
            this.imageError = null;
            this.showCropper = false;
            if (this.fileUpload) {
                this.fileUpload.clear();
            }
            this.toast.add({
                severity: 'info',
                summary: 'Supprimé',
                detail: 'Le logo a été supprimé.',
                life: 3000,
            });
        },
        startCroppingExistingLogo() {
            this.previewImage = this.getLogoUrl(this.form.logo);
            this.showCropper = true;
            this.selectedFile = null;
            this.croppedImage = null;
            this.logoPreview = null;
            this.toast.add({
                severity: 'info',
                summary: 'Recadrage',
                detail: "Recadrez l'image et confirmez.",
                life: 3000,
            });
        },
        confirmCrop() {
            if (this.croppedImage) {
                this.form.logo = this.croppedImage;
                this.logoPreview = this.croppedImage;
                this.previewImage = null;
                this.showCropper = false;
                this.selectedFile = null;
                this.toast.add({
                    severity: 'success',
                    summary: 'Recadrage confirmé',
                    detail: 'Image recadrée avec succès. Enregistrez pour sauvegarder.',
                    life: 3000,
                });
            } else {
                this.toast.add({
                    severity: 'warn',
                    summary: 'Aucun recadrage',
                    detail: "Veuillez recadrer l'image avant de confirmer.",
                    life: 3000,
                });
            }
        },
        cancelCrop() {
            this.previewImage = null;
            this.croppedImage = null;
            this.showCropper = false;
            this.selectedFile = null;
            this.logoPreview = this.form.logo;
            this.toast.add({
                severity: 'info',
                summary: 'Recadrage annulé',
                detail: "Recadrage de l'image annulé.",
                life: 3000,
            });
        },
        onCropperReady() {
            console.log('Cropper prêt : le composant est chargé et rendu');
        },
        onCropChange({ coordinates, canvas }) {
            try {
                this.croppedImage = canvas.toDataURL('image/png');
            } catch (err) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: "Erreur lors du recadrage de l'image",
                    life: 3000,
                });
            }
        },
        isValidLogo(logo) {
            if (!logo) return false;
            if (logo.startsWith('data:image/')) return true;
            const validFormats = ['.jpg', '.jpeg', '.png', '.svg'];
            return validFormats.some((format) =>
                logo.toLowerCase().endsWith(format)
            );
        },
        getLogoUrl(logo) {
            if (!logo) return '';
            if (logo.startsWith('data:image/')) return logo;
            if (logo.startsWith('/storage/')) {
                return `${window.location.origin}${logo}`;
            }
            return `${window.location.origin}/storage/logos/${logo}`;
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

.cropper-container {
    width: 300px;
    height: 300px;
    position: relative;
    overflow: hidden;
    background: transparent;
}

/* Style pour afficher uniquement la poignée en bas à droite */
:deep(.cropper-handler) {
    display: none !important;
}

:deep(.cropper-handler.bottom-right) {
    display: block !important;
    background: #3b82f6 !important;
    border: 2px solid #ffffff !important;
    width: 16px !important;
    height: 16px !important;
    border-radius: 50%;
    cursor: se-resize;
    box-shadow: 0 0 5px rgba(0, 0, 0, 0.5);
}

/* Supprimer tout cadre ou fond indésirable autour du cropper */
:deep(.cropper-viewport) {
    background: transparent !important;
    border: none !important;
}

:deep(.cropper-face) {
    background: transparent !important;
}

.text-error {
    color: var(--red-500);
}

@media (max-width: 960px) {
    .cropper-container {
        width: 250px;
        height: 250px;
    }
}

@media (max-width: 768px) {
    .cropper-container {
        width: 200px;
        height: 200px;
    }
}
</style>
