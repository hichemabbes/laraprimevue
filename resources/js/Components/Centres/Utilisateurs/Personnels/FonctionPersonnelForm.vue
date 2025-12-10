```vue
<template>
    <div>
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
                    <i class="pi pi-file text-primary text-2xl"></i>
                    <span class="font-bold text-2xl">
                        {{ isEditMode ? 'Modifier la fonction' : 'Ajouter une fonction' }}
                    </span>
                </div>
            </template>
            <div v-if="loading" class="flex flex-column align-items-center justify-content-center gap-3 p-6">
                <ProgressSpinner style="width: 50px; height: 50px" strokeWidth="4" />
                <span class="text-color-secondary">Chargement des données...</span>
            </div>
            <div v-else class="surface-card p-4 shadow-2 border-round">
                <TabView ref="tabview" v-model:activeIndex="activeTab">
                    <TabPanel header="Ajouter une Fonction">
                        <form @submit.prevent="saveFonction" class="form-spacing">
                            <div class="grid p-fluid">
                                <div class="col-12 flex align-items-start gap-3 mb-2 user-data-container">
                                    <div class="photo-container" style="width: 80px; height: 80px;">
                                        <img
                                            v-if="userInfo.photo"
                                            :src="userInfo.photo"
                                            alt="Photo du personnel"
                                            class="border-round shadow-2"
                                            style="width: 80px; height: 80px; object-fit: cover; border-radius: 50%"
                                            @error="userInfo.photo = '/default-avatar.png'"
                                        />
                                        <i
                                            v-else
                                            class="pi pi-user text-4xl text-400 flex align-items-center justify-content-center"
                                            style="width: 80px; height: 80px; border-radius: 50%; background: var(--surface-100);"
                                        ></i>
                                    </div>
                                    <div style="display: flex; flex-direction: column; justify-content: space-between; height: 80px;">
                                        <div class="font-bold">{{ userInfo.prenom_fr || '-' }} {{ userInfo.nom_fr || '-' }}</div>
                                        <div>
                                            <Tag
                                                :value="activeFonction || 'Aucune fonction active'"
                                                severity="info"
                                                style="font-size: 0.75rem; padding: 0.25rem 0.5rem;"
                                            />
                                        </div>
                                        <div>
                                            <Tag
                                                :value="associatedCentre || 'Aucun centre associé'"
                                                severity="success"
                                                style="font-size: 0.75rem; padding: 0.25rem 0.5rem;"
                                            >
                                                <span class="text-sm">{{ associatedCentre || 'Aucun centre associé' }}</span>
                                            </Tag>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 md:col-6 field">
                                    <label for="fonction_fr" class="block font-medium mb-1" style="font-size: 0.875rem">
                                        Fonction (Français) <span class="text-red-500">*</span>
                                    </label>
                                    <Dropdown
                                        id="fonction_fr"
                                        v-model="fonction.fonction_fr"
                                        :options="fonctions"
                                        optionLabel="nom_fr"
                                        optionValue="nom_fr"
                                        placeholder="Sélectionner une fonction"
                                        :class="{ 'p-invalid': errors.fonction_fr }"
                                        style="font-size: 0.875rem"
                                    />
                                    <small v-if="errors.fonction_fr" class="p-error" style="font-size: 0.75rem">{{ errors.fonction_fr }}</small>
                                </div>
                                <div class="col-12 md:col-6 field">
                                    <label for="avantage_fonction_fr" class="block font-medium mb-1" style="font-size: 0.875rem">
                                        Avantage de Fonction (Français)
                                    </label>
                                    <Dropdown
                                        id="avantage_fonction_fr"
                                        v-model="fonction.avantage_fonction_fr"
                                        :options="avantages"
                                        optionLabel="nom_fr"
                                        optionValue="nom_fr"
                                        placeholder="Sélectionner un avantage"
                                        :class="{ 'p-invalid': errors.avantage_fonction_fr }"
                                        style="font-size: 0.875rem"
                                    />
                                    <small v-if="errors.avantage_fonction_fr" class="p-error" style="font-size: 0.75rem">{{ errors.avantage_fonction_fr }}</small>
                                </div>
                                <div class="col-12 md:col-6 field">
                                    <label for="taches_fr" class="block font-medium mb-1" style="font-size: 0.875rem">
                                        Tâches (Français)
                                    </label>
                                    <InputText
                                        id="taches_fr"
                                        v-model="fonction.taches_fr"
                                        :class="{ 'p-invalid': errors.taches_fr }"
                                        placeholder="Entrez les tâches en français"
                                        style="font-size: 0.875rem"
                                    />
                                    <small v-if="errors.taches_fr" class="p-error" style="font-size: 0.75rem">{{ errors.taches_fr }}</small>
                                </div>
                                <div class="col-12 md:col-6 field">
                                    <label for="taches_ar" class="block font-medium mb-1 font-arabic" style="font-size: 0.875rem">
                                        Tâches (Arabe)
                                    </label>
                                    <InputText
                                        id="taches_ar"
                                        v-model="fonction.taches_ar"
                                        :class="{ 'p-invalid': errors.taches_ar }"
                                        placeholder="ادخل المهام بالعربية"
                                        style="font-size: 0.875rem"
                                        class="font-arabic"
                                        dir="rtl"
                                    />
                                    <small v-if="errors.taches_ar" class="p-error font-arabic" style="font-size: 0.75rem">{{ errors.taches_ar }}</small>
                                </div>
                                <div class="col-12 md:col-6 field">
                                    <label for="date_debut" class="block font-medium mb-1" style="font-size: 0.875rem">
                                        Date de Début <span class="text-red-500">*</span>
                                    </label>
                                    <Calendar
                                        id="date_debut"
                                        v-model="fonction.date_debut"
                                        :class="{ 'p-invalid': errors.date_debut }"
                                        dateFormat="yy-mm-dd"
                                        showIcon
                                        placeholder="Sélectionner une date"
                                        style="font-size: 0.875rem"
                                    />
                                    <small v-if="errors.date_debut" class="p-error" style="font-size: 0.75rem">{{ errors.date_debut }}</small>
                                </div>
                                <div class="col-12 md:col-6 field">
                                    <label for="date_fin" class="block font-medium mb-1" style="font-size: 0.875rem">
                                        Date de Fin
                                    </label>
                                    <Calendar
                                        id="date_fin"
                                        v-model="fonction.date_fin"
                                        :class="{ 'p-invalid': errors.date_fin }"
                                        dateFormat="yy-mm-dd"
                                        showIcon
                                        placeholder="Sélectionner une date"
                                        style="font-size: 0.875rem"
                                    />
                                    <small v-if="errors.date_fin" class="p-error" style="font-size: 0.75rem">{{ errors.date_fin }}</small>
                                </div>
                            </div>
                        </form>
                    </TabPanel>
                    <TabPanel header="Observation">
                        <div class="grid p-fluid">
                            <div class="col-12 field">
                                <label for="observation" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Observation
                                </label>
                                <Textarea
                                    id="observation"
                                    v-model="fonction.observation"
                                    rows="4"
                                    :class="{ 'p-invalid': errors.observation }"
                                    placeholder="Entrez vos observations"
                                    style="width: 100%; font-size: 0.875rem"
                                />
                                <small v-if="errors.observation" class="p-error" style="font-size: 0.75rem">{{ errors.observation }}</small>
                            </div>
                        </div>
                    </TabPanel>
                    <TabPanel header="Liste des Fonctions (Fr)">
                        <div class="surface-card p-4 pt-2 border-round-lg shadow-2 border-1 surface-border">
                            <div class="flex justify-content-between align-items-center mb-3">
                                <h2 class="m-0 font-bold text-xl">
                                    Liste des Fonctions de <span class="highlight-name">{{ userInfo.nom_fr || '' }}</span> <span class="highlight-name">{{ userInfo.prenom_fr || '' }}</span>
                                </h2>
                                <div class="flex align-items-center gap-2" style="margin-left: 0">
                                    <span class="p-input-icon-left">
                                        <InputText
                                            v-model="searchQuery"
                                            placeholder="Rechercher une fonction..."
                                            class="w-full"
                                            style="font-size: 0.875rem"
                                        />
                                    </span>
                                    <Button
                                        icon="pi pi-filter-slash"
                                        class="p-button-outlined p-button-secondary p-button-sm"
                                        @click="searchQuery = ''"
                                        v-tooltip="'Réinitialiser le filtre'"
                                    />
                                </div>
                            </div>
                            <DataTable
                                :value="filteredPersonnelFonctions"
                                :loading="loadingFonctions"
                                dataKey="id"
                                size="small"
                                stripedRows
                                scrollable
                                scrollHeight="600px"
                                removableSort
                                class="p-datatable-sm border-round-lg"
                                :paginator="true"
                                :rows="10"
                                :rowsPerPageOptions="[10, 20, 50]"
                                paginatorTemplate="CurrentPageReport FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
                                currentPageReportTemplate="Affichage de {first} à {last} sur {totalRecords} fonctions"
                            >
                                <template #empty>
                                    <div class="text-center py-4">
                                        <i class="pi pi-inbox text-4xl text-400 mb-2" />
                                        <p class="text-600">Aucune fonction trouvée</p>
                                    </div>
                                </template>
                                <Column field="fonction_fr" header="Fonction" sortable style="min-width: 12rem">
                                    <template #body="{ data }">
                                        <span class="font-medium">{{ data.fonction_fr }}</span>
                                    </template>
                                </Column>
                                <Column field="avantage_fonction_fr" header="Avantage" sortable style="min-width: 8rem">
                                    <template #body="{ data }">
                                        <span>{{ data.avantage_fonction_fr || '-' }}</span>
                                    </template>
                                </Column>
                                <Column field="taches_fr" header="Tâches" sortable style="min-width: 8rem">
                                    <template #body="{ data }">
                                        <span>{{ data.taches_fr || '-' }}</span>
                                    </template>
                                </Column>
                                <Column field="date_debut" header="Date Début" sortable style="min-width: 10rem">
                                    <template #body="{ data }">
                                        <Tag :value="formatDate(data.date_debut)" icon="pi pi-calendar" />
                                    </template>
                                </Column>
                                <Column field="date_fin" header="Date Fin" sortable style="min-width: 10rem">
                                    <template #body="{ data }">
                                        <Tag :value="formatDate(data.date_fin) || '-'" icon="pi pi-calendar" />
                                    </template>
                                </Column>
                                <Column field="statut_fr" header="Statut" sortable style="min-width: 10rem">
                                    <template #body="{ data }">
                                        <Tag
                                            :value="data.statut_fr || 'Inactive'"
                                            :severity="getSeverity(data.statut_fr)"
                                            :icon="getStatutIcon(data.statut_fr)"
                                        />
                                    </template>
                                </Column>
                                <Column header="Actions" style="min-width: 10rem">
                                    <template #body="{ data }">
                                        <div class="flex gap-1">
                                            <Button
                                                icon="pi pi-pencil"
                                                class="p-button-rounded p-button-text p-button-primary p-button-sm"
                                                @click="editFonction(data, 2)"
                                                v-tooltip="'Modifier'"
                                            />
                                            <Button
                                                icon="pi pi-trash"
                                                class="p-button-rounded p-button-text p-button-danger p-button-sm"
                                                @click="confirmDeleteFonction(data)"
                                                v-tooltip="'Supprimer'"
                                            />
                                        </div>
                                    </template>
                                </Column>
                            </DataTable>
                        </div>
                    </TabPanel>
                    <TabPanel header="Liste des Fonctions (Ar)">
                        <div class="surface-card p-4 pt-2 border-round-lg shadow-2 border-1 surface-border">
                            <div class="flex justify-content-between align-items-center mb-3">
                                <h2 class="m-0 font-bold text-xl arabic-text arabic-text-header" dir="rtl">
                                    قائمة الوظائف لـ <span class="highlight-name">{{ userInfo.prenom_ar || '' }}</span> <span class="highlight-name">{{ userInfo.nom_ar || '' }}</span>
                                </h2>
                                <div class="flex align-items-center gap-2" style="margin-right: 0">
                                    <span class="p-input-icon-left">
                                        <InputText
                                            v-model="searchQueryAr"
                                            placeholder="ابحث عن وظيفة..."
                                            class="w-full arabic-text"
                                            style="font-size: 0.875rem"
                                            dir="rtl"
                                        />
                                    </span>
                                    <Button
                                        icon="pi pi-filter-slash"
                                        class="p-button-outlined p-button-secondary p-button-sm"
                                        @click="searchQueryAr = ''"
                                        v-tooltip="'إعادة تعيين الفلتر'"
                                    />
                                </div>
                            </div>
                            <DataTable
                                :value="filteredPersonnelFonctionsAr"
                                :loading="loadingFonctions"
                                dataKey="id"
                                size="small"
                                stripedRows
                                scrollable
                                scrollHeight="600px"
                                removableSort
                                class="p-datatable-sm border-round-lg arabic-text"
                                :paginator="true"
                                :rows="10"
                                :rowsPerPageOptions="[10, 20, 50]"
                                paginatorTemplate="CurrentPageReport FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
                                currentPageReportTemplate="عرض من {first} إلى {last} من {totalRecords} وظائف"
                                dir="rtl"
                            >
                                <template #empty>
                                    <div class="text-center py-4">
                                        <i class="pi pi-inbox text-4xl text-400 mb-2" />
                                        <p class="text-600 arabic-text">لا توجد وظائف</p>
                                    </div>
                                </template>
                                <Column field="fonction_ar" header="الوظيفة" sortable style="min-width: 12rem">
                                    <template #body="{ data }">
                                        <span class="arabic-text">{{ data.fonction_ar }}</span>
                                    </template>
                                </Column>
                                <Column field="avantage_fonction_ar" header="الامتياز" sortable style="min-width: 8rem">
                                    <template #body="{ data }">
                                        <span class="arabic-text">{{ data.avantage_fonction_ar || '-' }}</span>
                                    </template>
                                </Column>
                                <Column field="taches_ar" header="المهام" sortable style="min-width: 8rem">
                                    <template #body="{ data }">
                                        <span class="arabic-text">{{ data.taches_ar || '-' }}</span>
                                    </template>
                                </Column>
                                <Column field="date_debut" header="تاريخ البداية" sortable style="min-width: 10rem">
                                    <template #body="{ data }">
                                        <Tag :value="formatDate(data.date_debut)" icon="pi pi-calendar" class="arabic-text" />
                                    </template>
                                </Column>
                                <Column field="date_fin" header="تاريخ النهاية" sortable style="min-width: 10rem">
                                    <template #body="{ data }">
                                        <Tag :value="formatDate(data.date_fin) || '-'" icon="pi pi-calendar" class="arabic-text" />
                                    </template>
                                </Column>
                                <Column field="statut_ar" header="الحالة" sortable style="min-width: 10rem">
                                    <template #body="{ data }">
                                        <Tag
                                            :value="data.statut_ar || 'غير نشط'"
                                            :severity="getSeverity(data.statut_ar)"
                                            :icon="getStatutIcon(data.statut_ar)"
                                            class="arabic-text"
                                        />
                                    </template>
                                </Column>
                                <Column header="الإجراءات" style="min-width: 10rem">
                                    <template #body="{ data }">
                                        <div class="flex gap-1">
                                            <Button
                                                icon="pi pi-pencil"
                                                class="p-button-rounded p-button-text p-button-primary p-button-sm"
                                                @click="editFonction(data, 3)"
                                                v-tooltip="'تعديل'"
                                            />
                                            <Button
                                                icon="pi pi-trash"
                                                class="p-button-rounded p-button-text p-button-danger p-button-sm"
                                                @click="confirmDeleteFonction(data)"
                                                v-tooltip="'حذف'"
                                            />
                                        </div>
                                    </template>
                                </Column>
                            </DataTable>
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
                        @click="cancelForm"
                        style="font-size: 0.875rem"
                    />
                    <Button
                        :label="isEditMode ? 'Mettre à jour' : 'Enregistrer'"
                        icon="pi pi-check-circle"
                        class="p-button-primary p-button-sm"
                        @click="saveFonction"
                        :loading="loading"
                        style="font-size: 0.875rem"
                    />
                </div>
            </template>
        </Dialog>
        <Dialog
            header="Confirmer la suppression"
            v-model:visible="deleteFormVisible"
            :modal="true"
            :style="{ width: '30rem' }"
            :pt="{
                root: 'border-round-xl shadow-5',
                mask: { style: 'backdrop-filter: blur(4px)' },
                header: { class: 'surface-50 border-bottom-1 surface-border p-4' },
                content: { class: 'surface-ground p-4' },
                footer: { class: 'surface-50 border-top-1 surface-border p-4' }
            }"
        >
            <div class="grid p-fluid">
                <div class="col-12 field">
                    <label for="deletePassword" class="block font-medium mb-2" style="font-size: 0.875rem">
                        Mot de passe
                    </label>
                    <div class="p-inputgroup">
                        <InputText
                            id="deletePassword"
                            v-model="deletePassword"
                            :type="showPassword ? 'text' : 'password'"
                            :class="{ 'p-invalid': passwordError }"
                            placeholder="Entrez votre mot de passe"
                            style="font-size: 0.875rem;"
                        />
                        <Button
                            :icon="showPassword ? 'pi pi-eye-slash' : 'pi pi-eye'"
                            class="p-button-outlined"
                            @click="toggleShowPassword"
                            v-tooltip="showPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe'"
                        />
                    </div>
                    <small v-if="passwordError" class="p-error" style="font-size: 0.75rem">{{ passwordError }}</small>
                </div>
            </div>
            <template #footer>
                <div class="flex justify-content-end gap-3">
                    <Button
                        label="Annuler"
                        icon="pi pi-times-circle"
                        class="p-button-outlined p-button-secondary p-button-sm"
                        @click="cancelDelete"
                        style="font-size: 0.875rem"
                    />
                    <Button
                        label="Supprimer"
                        icon="pi pi-trash"
                        class="p-button-danger p-button-sm"
                        @click="deleteFonction"
                        :loading="loading"
                        style="font-size: 0.875rem"
                    />
                </div>
            </template>
        </Dialog>
        <Toast position="top-right" />
    </div>
</template>

<script>
import axios from 'axios';
import Button from 'primevue/button';
import Calendar from 'primevue/calendar';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import ProgressSpinner from 'primevue/progressspinner';
import Toast from 'primevue/toast';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import { useToast } from 'primevue/usetoast';

export default {
    name: 'FonctionPersonnelForm',
    components: {
        Button,
        Calendar,
        Dialog,
        Dropdown,
        InputText,
        Textarea,
        ProgressSpinner,
        Toast,
        TabView,
        TabPanel,
        DataTable,
        Column,
        Tag
    },
    props: {
        visible: { type: Boolean, default: false },
        personnelId: {
            type: Number,
            required: true,
            validator: (value) => {
                if (!Number.isInteger(value) || value <= 0) {
                    console.warn('personnelId doit être un entier positif:', value);
                    return false;
                }
                return true;
            }
        },
        isCentreRole: { type: Boolean, default: false },
        centreId: {
            type: [Number, String],
            required: false,
            default: null
        }
    },
    emits: ['update:visible', 'fonction-saved'],
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            fonction: {
                fonction_fr: null,
                avantage_fonction_fr: null,
                taches_fr: null,
                taches_ar: null,
                date_debut: null,
                date_fin: null,
                observation: null
            },
            fonctions: [],
            avantages: [],
            centres: [],
            personnelFonctions: [],
            userInfo: {
                nom_fr: '',
                prenom_fr: '',
                nom_ar: '',
                prenom_ar: '',
                photo: null,
                centre_id: null,
                is_super_admin: false
            },
            isEditMode: false,
            fonctionToEdit: null,
            errors: {},
            loading: false,
            loadingFonctions: false,
            deleteFormVisible: false,
            deletePassword: '',
            passwordError: '',
            showPassword: false,
            activeTab: 0,
            searchQuery: '',
            searchQueryAr: '',
            returnTab: 0
        };
    },
    computed: {
        formVisible: {
            get() {
                return this.visible;
            },
            set(value) {
                this.$emit('update:visible', value);
            }
        },
        filteredPersonnelFonctions() {
            if (!this.searchQuery.trim()) {
                return this.personnelFonctions;
            }
            const query = this.searchQuery.toLowerCase().trim();
            return this.personnelFonctions.filter(fonction =>
                (fonction.fonction_fr?.toLowerCase().includes(query) ||
                    fonction.taches_fr?.toLowerCase().includes(query))
            );
        },
        filteredPersonnelFonctionsAr() {
            if (!this.searchQueryAr.trim()) {
                return this.personnelFonctions;
            }
            const query = this.searchQueryAr.toLowerCase().trim();
            return this.personnelFonctions.filter(fonction =>
                (fonction.fonction_ar?.toLowerCase().includes(query) ||
                    fonction.taches_ar?.toLowerCase().includes(query))
            );
        },
        activeFonction() {
            const latestFunction = this.personnelFonctions
                .sort((a, b) => new Date(b.date_debut) - new Date(a.date_debut))[0];
            return latestFunction ? latestFunction.fonction_fr : null;
        },
        associatedCentre() {
            if (!this.centres.length) {
                console.warn('Liste des centres non chargée');
                return 'Aucun centre disponible';
            }
            if (!this.userInfo.centre_id) {
                return 'Aucun centre associé';
            }
            const centre = this.centres.find(centre => centre.id === this.userInfo.centre_id);
            if (!centre) {
                console.warn('Centre non trouvé pour centre_id:', this.userInfo.centre_id, 'dans centres:', this.centres);
                return 'Centre introuvable';
            }
            return centre.nom_fr || centre.nom_ar || 'Aucun centre associé';
        }
    },
    watch: {
        visible(newVal) {
            if (newVal) {
                this.fetchFormData();
                this.fetchPersonnelFonctions();
                this.activeTab = 0;
            } else {
                this.resetForm();
            }
        },
        personnelId(newVal, oldVal) {
            if (newVal !== oldVal && Number.isInteger(newVal) && newVal > 0) {
                this.fetchPersonnelFonctions();
            }
        }
    },
    created() {
        if (this.visible) {
            this.fetchFormData();
            this.fetchPersonnelFonctions();
        }
    },
    beforeUnmount() {
        this.resetForm();
    },
    methods: {
        async fetchFormData() {
            this.loading = true;
            try {
                const response = await axios.get('/api/fonctions-personnels/function-types', {
                    headers: this.getHeaders()
                });
                this.fonctions = response.data.fonctions || [];
                this.avantages = response.data.avantages || [];
                this.centres = response.data.centres || [];
                console.log('fetchFormData - response:', response.data);
                if (!this.fonctions.length) {
                    this.showWarn('Aucune fonction disponible.');
                }
                if (!this.avantages.length) {
                    this.showWarn('Aucun avantage disponible.');
                }
                if (!this.centres.length) {
                    this.showWarn('Aucun centre disponible. Veuillez vérifier la connexion au serveur.');
                }
            } catch (error) {
                console.error('Erreur lors de fetchFormData:', {
                    status: error.response?.status || 'N/A',
                    data: error.response?.data || null,
                    message: error.message,
                    stack: error.stack
                });
                this.handleApiError(error, 'Erreur lors du chargement des données du formulaire. Veuillez vérifier la connexion au serveur.');
            } finally {
                this.loading = false;
            }
        },
        async fetchPersonnelFonctions() {
            if (!this.personnelId) {
                this.showError('ID du personnel non valide.');
                return;
            }
            this.loadingFonctions = true;
            try {
                const response = await axios.get('/api/fonctions-personnels', {
                    headers: this.getHeaders(),
                    params: { personnel_id: this.personnelId }
                });
                if (typeof response.data === 'string') {
                    throw new Error('Réponse invalide : HTML reçu au lieu de JSON');
                }
                this.personnelFonctions = response.data.fonctions || [];
                this.userInfo = response.data.user_info || {
                    nom_fr: '',
                    prenom_fr: '',
                    nom_ar: '',
                    prenom_ar: '',
                    photo: null,
                    centre_id: null,
                    is_super_admin: false
                };
                this.centres = response.data.centres || this.centres;
                console.log('fetchPersonnelFonctions - response:', response.data);
                if (!this.userInfo.centre_id && !this.userInfo.is_super_admin) {
                    this.showInfo('Aucun centre associé à ce personnel. Cela peut être normal pour certains utilisateurs (ex. SuperAdmin).');
                }
            } catch (error) {
                console.error('Erreur lors de fetchPersonnelFonctions:', {
                    status: error.response?.status || 'N/A',
                    data: error.response?.data || null,
                    message: error.message,
                    stack: error.stack
                });
                this.handleApiError(error, 'Erreur lors du chargement des fonctions du personnel. Veuillez vérifier la connexion au serveur.');
            } finally {
                this.loadingFonctions = false;
            }
        },
        getHeaders() {
            const headers = {
                Authorization: `Bearer ${localStorage.getItem('token')}`,
                'X-Is-Centre-Role': this.isCentreRole ? 1 : 0
            };
            if (this.isCentreRole && this.centreId) {
                headers['X-Centre-Id'] = this.centreId;
            } else if (this.isCentreRole && !this.centreId) {
                console.warn('centreId est requis lorsque isCentreRole est true');
            }
            console.log('getHeaders - isCentreRole:', this.isCentreRole, 'centreId:', this.centreId);
            return headers;
        },
        resetForm() {
            this.fonction = {
                fonction_fr: null,
                avantage_fonction_fr: null,
                taches_fr: null,
                taches_ar: null,
                date_debut: null,
                date_fin: null,
                observation: null
            };
            this.errors = {};
            this.isEditMode = false;
            this.fonctionToEdit = null;
            this.deleteFormVisible = false;
            this.deletePassword = '';
            this.passwordError = '';
            this.searchQuery = '';
            this.searchQueryAr = '';
            this.returnTab = 0;
        },
        async saveFonction() {
            this.errors = {};
            if (!this.validateForm()) {
                this.showError('Veuillez corriger les erreurs dans le formulaire.');
                return;
            }
            this.loading = true;
            try {
                const payload = {
                    ...this.fonction,
                    personnel_id: this.personnelId,
                    date_debut: this.fonction.date_debut ? this.formatDateForApi(this.fonction.date_debut) : null,
                    date_fin: this.fonction.date_fin ? this.formatDateForApi(this.fonction.date_fin) : null
                };
                let response;
                if (this.isEditMode) {
                    response = await axios.patch(`/api/fonctions-personnels/${this.fonctionToEdit.id}`, payload, {
                        headers: this.getHeaders()
                    });
                } else {
                    response = await axios.post('/api/fonctions-personnels', payload, {
                        headers: this.getHeaders()
                    });
                }
                this.$emit('fonction-saved', response.data.data);
                this.showSuccess(this.isEditMode ? 'Fonction mise à jour avec succès.' : 'Fonction ajoutée avec succès.');
                this.fetchPersonnelFonctions();
                this.activeTab = this.isEditMode ? this.returnTab : 2; // Retourne à l'onglet d'origine pour édition, sinon à la liste française pour ajout
                this.resetForm();
            } catch (error) {
                console.error('Erreur lors de saveFonction:', {
                    status: error.response?.status || 'N/A',
                    data: error.response?.data || null,
                    message: error.message,
                    stack: error.stack
                });
                this.handleApiError(error, 'Erreur lors de l\'enregistrement de la fonction. Veuillez vérifier la connexion au serveur.');
                if (error.response?.status === 422) {
                    this.errors = error.response.data.errors || {};
                }
            } finally {
                this.loading = false;
            }
        },
        editFonction(fonction, tabIndex) {
            this.fonctionToEdit = { ...fonction };
            this.isEditMode = true;
            this.returnTab = tabIndex;
            this.fonction = {
                fonction_fr: fonction.fonction_fr,
                avantage_fonction_fr: fonction.avantage_fonction_fr,
                taches_fr: fonction.taches_fr,
                taches_ar: fonction.taches_ar,
                date_debut: fonction.date_debut ? new Date(fonction.date_debut) : null,
                date_fin: fonction.date_fin ? new Date(fonction.date_fin) : null,
                observation: fonction.observation
            };
            this.activeTab = 0;
        },
        confirmDeleteFonction(fonction) {
            this.fonctionToEdit = fonction;
            this.returnTab = this.activeTab; // Conserver l'onglet actuel comme onglet de retour
            this.deletePassword = '';
            this.passwordError = '';
            this.deleteFormVisible = true;
        },
        async deleteFonction() {
            if (!this.fonctionToEdit?.id) return;
            this.loading = true;
            try {
                await axios.delete(`/api/fonctions-personnels/${this.fonctionToEdit.id}`, {
                    headers: this.getHeaders(),
                    data: { password: this.deletePassword.trim() }
                });
                this.showSuccess('Fonction supprimée avec succès.');
                this.$emit('fonction-saved', null);
                this.fetchPersonnelFonctions();
                this.deleteFormVisible = false;
                this.activeTab = this.returnTab; // Retourne à l'onglet d'origine
                this.resetForm();
            } catch (error) {
                console.error('Erreur lors de deleteFonction:', {
                    status: error.response?.status || 'N/A',
                    data: error.response?.data || null,
                    message: error.message,
                    stack: error.stack
                });
                this.passwordError = error.response?.data?.error || 'Erreur lors de la suppression. Vérifiez votre mot de passe.';
                this.showError(this.passwordError);
            } finally {
                this.loading = false;
            }
        },
        cancelDelete() {
            this.deleteFormVisible = false;
            this.deletePassword = '';
            this.passwordError = '';
            this.fonctionToEdit = null;
            this.activeTab = this.returnTab; // Retourne à l'onglet d'origine
        },
        cancelForm() {
            this.formVisible = false; // Ferme complètement le formulaire
            this.resetForm();
        },
        validateForm() {
            this.errors = {};
            let isValid = true;
            if (!this.fonction.fonction_fr) {
                this.errors.fonction_fr = 'La fonction (Français) est requise.';
                isValid = false;
            }
            if (!this.fonction.date_debut) {
                this.errors.date_debut = 'La date de début est requise.';
                isValid = false;
            }
            if (this.fonction.date_fin && this.fonction.date_debut) {
                const startDate = new Date(this.fonction.date_debut);
                const endDate = new Date(this.fonction.date_fin);
                if (endDate <= startDate) {
                    this.errors.date_fin = 'La date de fin doit être strictement supérieure à la date de début.';
                    isValid = false;
                }
            }
            return isValid;
        },
        formatDate(date) {
            if (!date) return '';
            return new Date(date).toLocaleDateString('fr-FR', { year: 'numeric', month: '2-digit', day: '2-digit' });
        },
        formatDateForApi(date) {
            if (!date) return null;
            const d = new Date(date);
            return d.toISOString().split('T')[0];
        },
        showSuccess(message) {
            this.toast.add({ severity: 'success', summary: 'Succès', detail: message, life: 3000 });
        },
        showError(message) {
            this.toast.add({ severity: 'error', summary: 'Erreur', detail: message, life: 5000 });
        },
        showWarn(message) {
            this.toast.add({ severity: 'warn', summary: 'Avertissement', detail: message, life: 5000 });
        },
        showInfo(message) {
            this.toast.add({ severity: 'info', summary: 'Information', detail: message, life: 5000 });
        },
        handleVisibleUpdate(newVisible) {
            this.formVisible = newVisible; // Permet la fermeture via la croix du dialogue
            if (!newVisible) {
                this.resetForm();
            }
        },
        handleApiError(error, defaultMessage) {
            const errorDetails = {
                message: error.message || 'Erreur inconnue',
                status: error.response?.status || 'N/A',
                data: error.response?.data || null,
                stack: error.stack || 'N/A'
            };
            console.error('Erreur API:', errorDetails);
            this.showError(error.response?.data?.message || defaultMessage);
            if (error.response?.status === 401) {
                this.$router.push({ name: 'login' });
            }
        },
        toggleShowPassword() {
            this.showPassword = !this.showPassword;
        },
        getSeverity(statut) {
            const statusSeverity = {
                'Active': 'success',
                'Inactive': 'secondary',
                'نشط': 'success',
                'غير نشط': 'secondary'
            };
            return statusSeverity[statut] || 'secondary';
        },
        getStatutIcon(statut) {
            const statusIcons = {
                'Active': 'pi pi-check',
                'Inactive': 'pi pi-times',
                'نشط': 'pi pi-check',
                'غير نشط': 'pi pi-times'
            };
            return statusIcons[statut] || 'pi pi-info';
        }
    }
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
.highlight-name {
    color: var(--primary-color);
}
.arabic-text,
:deep(.p-inputtext[dir='rtl']),
:deep(.p-textarea[dir='rtl']) {
    font-family: 'Montserrat-Arabic-Regular', sans-serif;
    direction: rtl;
    text-align: right;
}
:deep(.p-datatable) {
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}
:deep(.p-datatable-sm .p-datatable-tbody > tr > td) {
    padding: 0.5rem 1rem;
}
:deep(.p-datatable .p-column-header-content) {
    justify-content: center;
}
:deep(.p-datatable.arabic-text .p-datatable-thead > tr > th),
:deep(.p-datatable.arabic-text .p-datatable-tbody > tr > td),
:deep(.p-datatable.arabic-text .p-paginator .p-paginator-current) {
    font-family: 'Montserrat-Arabic-Regular', sans-serif;
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
.w-25 {
    width: 25%;
}
.p-error {
    color: #dc3545;
    font-size: 0.875rem;
}
.arabic-text-header {
    margin-right: auto;
    order: 2;
}
.search-field-container {
    margin-left: auto;
    order: 1;
}
</style>
```
