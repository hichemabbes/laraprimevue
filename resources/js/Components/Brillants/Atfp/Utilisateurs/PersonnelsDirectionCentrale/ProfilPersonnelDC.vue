<script setup>
import { ref, computed, onMounted, defineProps, defineEmits } from 'vue';
import { useToast } from 'primevue/usetoast';
import Card from 'primevue/card';
import Avatar from 'primevue/avatar';
import Button from 'primevue/button';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import ProgressSpinner from 'primevue/progressspinner';
import Message from 'primevue/message';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import FileUpload from 'primevue/fileupload';
import InputText from 'primevue/inputtext';
import Calendar from 'primevue/calendar';
import Dropdown from 'primevue/dropdown';
import MultiSelect from 'primevue/multiselect';
import RadioButton from 'primevue/radiobutton';
import Password from 'primevue/password';
import Textarea from 'primevue/textarea';
import { Cropper } from 'vue-advanced-cropper';
import 'vue-advanced-cropper/dist/style.css';
import axios from '@/axios.js';
import { debounce } from 'lodash';
// Importer la fonction d'impression
import { generatePrintHTML } from './ImpressionProfil.js';

// Initialize
const toast = useToast();
const emit = defineEmits(['update', 'close']);
const props = defineProps({
    user: {
        type: Object,
        default: () => ({}),
    },
    isNew: {
        type: Boolean,
        default: false,
    },
});

// Reactive state
const loading = ref(false);
const saving = ref(false);
const error = ref(null);
const tabview = ref(null);
const showPhotoDialog = ref(false);
const fileUpload = ref(null);
const selectedFile = ref(null);
const previewImage = ref(null);
const croppedImage = ref(null);
const photoPreview = ref(null);
const imageError = ref(null);
const uploadingPhoto = ref(false);
const deletingPhoto = ref(false);
const errors = ref({});
const showCropper = ref(false);
const isEditing = ref(false);
const isClearing = ref(false);
const gouvernorats = ref([]);
const gouvernoratOptions = ref([]);
const availableRoles = ref([]);
// Nouvel état pour la popup d'impression
const showPrintDialog = ref(false);

// Form data
const form = ref({
    matricule: '',
    nom_fr: '',
    prenom_fr: '',
    nom_ar: '',
    prenom_ar: '',
    cin: '',
    date_cin: null,
    lieu_cin_fr: '',
    lieu_cin_ar: '',
    date_naissance: null,
    lieu_naissance_fr: '',
    lieu_naissance_ar: '',
    nationalite_fr: 'Tunisienne',
    nationalite_ar: 'تونسية',
    genre_fr: '',
    genre_ar: '',
    etat_civil_fr: '',
    etat_civil_ar: '',
    adresse_fr: '',
    adresse_ar: '',
    gouvernorat_fr: '',
    gouvernorat_ar: '',
    delegation_fr: '',
    delegation_ar: '',
    telephone_1: '',
    telephone_2: '',
    email: '',
    roles: [],
    statut: 'Actif',
    cause_inactivite_fr: '',
    cause_inactivite_ar: '',
    date_recrutement: null,
    date_fin_service: null,
    observation_fr: '',
    observation_ar: '',
    password: '',
    password_confirmation: '',
    ajouter_par: null,
});

// Options pour les listes déroulantes
const genreOptions = ['Homme', 'Femme', 'Autre'];
const etatCivilOptions = ['Célibataire', 'Marié', 'Divorcé', 'Veuf'];
const causeInactiviteOptions = [
    'Mise à la retraite',
    'Dispense / Exemption',
    'Révocation',
    'Perte de la nationalité tunisienne',
    'Perte des droits civils',
    'Non-retour de l\'agent après une période de détachement',
    'Non-prise de fonction',
    'Non-retour de l\'agent après avoir accompli le service militaire',
    'Non-retour de l\'agent à son poste après la fin d\'un congé pour création d\'entreprise, après un avertissement'
];

// Computed
const user = computed(() => ({
    ...props.user,
    roles: props.user.roles || [],
}));

// Lifecycle
onMounted(async () => {
    await fetchUserData();
    await fetchGouvernorats();
    await fetchRoles();
    resetForm();
});

// API functions
async function fetchUserData() {
    if (props.isNew) return;
    try {
        loading.value = true;
        const response = await axios.get(`/api/personnels_atfp/${props.user.id}`);
        const fetchedUser = response.data.data;
        form.value = { ...form.value, ...fetchedUser, roles: fetchedUser.roles ? fetchedUser.roles.map(r => r.id) : [] };
        if (fetchedUser.photo && isValidPhoto(fetchedUser.photo)) {
            photoPreview.value = await loadImage(getPhotoUrl(fetchedUser.photo));
        }
    } catch (err) {
        error.value = err.response?.data?.message || 'Erreur lors du chargement des données';
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: error.value,
            life: 5000,
        });
    } finally {
        loading.value = false;
    }
}

async function fetchGouvernorats() {
    try {
        const response = await axios.get('/api/personnels_atfp-with-options');
        gouvernoratOptions.value = response.data.data.lists.Gouvernorats || [];
        gouvernorats.value = gouvernoratOptions.value.map(option => option.nom_fr);
    } catch (err) {
        console.error('Erreur lors de la récupération des gouvernorats:', err);
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: err.response?.data?.message || 'Erreur lors de la récupération des gouvernorats',
            life: 5000,
        });
    }
}

async function fetchRoles() {
    try {
        const response = await axios.get('/api/roles');
        availableRoles.value = response.data.data || [];
    } catch (err) {
        console.error('Erreur lors de la récupération des rôles:', err);
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: err.response?.data?.message || 'Erreur lors de la récupération des rôles',
            life: 5000,
        });
    }
}

// Form handlers
function onGenreChange() {
    const genreMap = {
        'Homme': 'ذكر',
        'Femme': 'أنثى',
        'Autre': 'أخرى',
    };
    form.value.genre_ar = genreMap[form.value.genre_fr] || '';
    validateField('genre_fr');
    validateField('genre_ar');
}

function onEtatCivilChange() {
    const etatCivilMap = {
        'Célibataire': 'أعزب(اء)',
        'Marié': 'متزوج(ة)',
        'Divorcé': 'مطلق(ة)',
        'Veuf': 'أرمل(ة)',
    };
    form.value.etat_civil_ar = etatCivilMap[form.value.etat_civil_fr] || '';
    validateField('etat_civil_fr');
    validateField('etat_civil_ar');
}

function onGouvernoratChange() {
    const selectedGouvernorat = gouvernoratOptions.value.find(option => option.nom_fr === form.value.gouvernorat_fr);
    form.value.gouvernorat_ar = selectedGouvernorat ? selectedGouvernorat.nom_ar : '';
    validateField('gouvernorat_fr');
    validateField('gouvernorat_ar');
}

function onStatutChange() {
    if (form.value.statut === 'Actif') {
        form.value.cause_inactivite_fr = '';
        form.value.cause_inactivite_ar = '';
        form.value.date_fin_service = null;
    }
    validateField('statut');
}

function onCauseInactiviteChange() {
    const causeInactiviteMap = {
        'Mise à la retraite': 'الإحالة على التقاعد',
        'Dispense / Exemption': 'الإعفاء',
        'Révocation': 'العزل',
        'Perte de la nationalité tunisienne': 'فقدان الجنسية التونسية',
        'Perte des droits civils': 'فقدان الحقوق المدنية',
        'Non-retour de l\'agent après une période de détachement': 'عدم رجوع العون إثر فترة إلحاق',
        'Non-prise de fonction': 'عدم مباشرة',
        'Non-retour de l\'agent après avoir accompli le service militaire': 'عدم رجوع العون بعد قيامه بالخدمة الوطنية',
        'Non-retour de l\'agent à son poste après la fin d\'un congé pour création d\'entreprise, après un avertissement': 'عدم رجوع العون إلى عمله إثر انتهاء عطلة لبعث مؤسسة، وبعد التنبيه عليه'
    };
    form.value.cause_inactivite_ar = causeInactiviteMap[form.value.cause_inactivite_fr] || '';
    validateField('cause_inactivite_fr');
    validateField('cause_inactivite_ar');
}

function startEditing() {
    isEditing.value = true;
}

function cancelEditing() {
    isEditing.value = false;
    resetForm();
    errors.value = {};
}

function resetForm() {
    form.value = {
        matricule: props.user.matricule || '',
        nom_fr: props.user.nom_fr || '',
        prenom_fr: props.user.prenom_fr || '',
        nom_ar: props.user.nom_ar || '',
        prenom_ar: props.user.prenom_ar || '',
        cin: props.user.cin || '',
        date_cin: props.user.date_cin ? new Date(props.user.date_cin) : null,
        lieu_cin_fr: props.user.lieu_cin_fr || '',
        lieu_cin_ar: props.user.lieu_cin_ar || '',
        date_naissance: props.user.date_naissance ? new Date(props.user.date_naissance) : null,
        lieu_naissance_fr: props.user.lieu_naissance_fr || '',
        lieu_naissance_ar: props.user.lieu_naissance_ar || '',
        nationalite_fr: props.user.nationalite_fr || 'Tunisienne',
        nationalite_ar: props.user.nationalite_ar || 'تونسية',
        genre_fr: props.user.genre_fr || '',
        genre_ar: props.user.genre_ar || '',
        etat_civil_fr: props.user.etat_civil_fr || '',
        etat_civil_ar: props.user.etat_civil_ar || '',
        adresse_fr: props.user.adresse_fr || '',
        adresse_ar: props.user.adresse_ar || '',
        gouvernorat_fr: props.user.gouvernorat_fr || '',
        gouvernorat_ar: props.user.gouvernorat_ar || '',
        delegation_fr: props.user.delegation_fr || '',
        delegation_ar: props.user.delegation_ar || '',
        telephone_1: props.user.telephone_1 || '',
        telephone_2: props.user.telephone_2 || '',
        email: props.user.email || '',
        roles: props.user.roles ? props.user.roles.map(r => r.id) : [],
        statut: props.user.statut || 'Actif',
        cause_inactivite_fr: props.user.cause_inactivite_fr || '',
        cause_inactivite_ar: props.user.cause_inactivite_ar || '',
        date_recrutement: props.user.date_recrutement ? new Date(props.user.date_recrutement) : null,
        date_fin_service: props.user.date_fin_service ? new Date(props.user.date_fin_service) : null,
        observation_fr: props.user.observation_fr || '',
        observation_ar: props.user.observation_ar || '',
        password: '',
        password_confirmation: '',
        ajouter_par: props.user.ajouter_par || null,
    };
}

// Validation functions
const debouncedValidateCin = debounce(async () => {
    try {
        if (!form.value.cin) {
            errors.value.cin = ['Le CIN est requis'];
            return;
        }
        if (!/^\d{8}$/.test(form.value.cin)) {
            errors.value.cin = ['Le CIN doit contenir exactement 8 chiffres'];
            return;
        }
        const response = await axios.get(`/api/validate-cin/${form.value.cin}`, {
            params: { exclude_id: props.user.id },
        });
        if (!response.data.is_unique) {
            errors.value.cin = ['Ce CIN existe déjà'];
        } else {
            errors.value.cin = [];
        }
    } catch (err) {
        errors.value.cin = [err.response?.data?.message || 'Erreur lors de la validation du CIN'];
    }
}, 500);

const debouncedValidateEmail = debounce(async () => {
    try {
        if (!form.value.email) {
            errors.value.email = ["L'email est requis"];
            return;
        }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email)) {
            errors.value.email = ["L'email n'est pas valide"];
            return;
        }
        if (form.value.email.length > 255) {
            errors.value.email = ["L'email ne doit pas dépasser 255 caractères"];
            return;
        }
        const response = await axios.get(`/api/validate-email/${form.value.email}`, {
            params: { exclude_id: props.user.id },
        });
        if (!response.data.is_unique) {
            errors.value.email = ['Cet email existe déjà'];
        } else {
            errors.value.email = [];
        }
    } catch (err) {
        errors.value.email = [err.response?.data?.message || "Erreur lors de la validation de l'email"];
    }
}, 500);

function validateField(field) {
    const maxLengthFields = {
        nom_fr: 255,
        prenom_fr: 255,
        nom_ar: 255,
        prenom_ar: 255,
        lieu_cin_fr: 255,
        lieu_cin_ar: 255,
        lieu_naissance_fr: 255,
        lieu_naissance_ar: 255,
        nationalite_fr: 255,
        nationalite_ar: 255,
        adresse_fr: 255,
        adresse_ar: 255,
        gouvernorat_fr: 255,
        gouvernorat_ar: 255,
        delegation_fr: 255,
        delegation_ar: 255,
        observation_fr: 1000,
        observation_ar: 1000,
    };
    errors.value[field] = [];
    if (['nom_fr', 'prenom_fr', 'nom_ar', 'prenom_ar'].includes(field) && !form.value[field]) {
        errors.value[field] = [`Le ${field.replace('_', ' ')} est requis`];
    } else if (maxLengthFields[field] && form.value[field]?.length > maxLengthFields[field]) {
        errors.value[field] = [`Le ${field.replace('_', ' ')} ne doit pas dépasser ${maxLengthFields[field]} caractères`];
    }
}

function validateTelephone(field) {
    errors.value[field] = [];
    if (form.value[field] && !/^\+?\d{8,15}$/.test(form.value[field])) {
        errors.value[field] = [`Le ${field.replace('_', ' ')} doit contenir entre 8 et 15 chiffres`];
    }
}

function validateDateCin() {
    errors.value.date_cin = [];
    if (form.value.date_cin && new Date(form.value.date_cin) > new Date()) {
        errors.value.date_cin = ['La date CIN ne peut pas être dans le futur'];
    }
}

function validateDateNaissance() {
    errors.value.date_naissance = [];
    if (form.value.date_naissance && new Date(form.value.date_naissance) > new Date()) {
        errors.value.date_naissance = ['La date de naissance ne peut pas être dans le futur'];
    }
}

function validateDateRecrutement() {
    errors.value.date_recrutement = [];
    if (form.value.date_recrutement && new Date(form.value.date_recrutement) > new Date()) {
        errors.value.date_recrutement = ['La date de recrutement ne peut pas être dans le futur'];
    }
    if (form.value.date_recrutement && form.value.date_fin_service) {
        if (new Date(form.value.date_recrutement) > new Date(form.value.date_fin_service)) {
            errors.value.date_fin_service = ['La date de fin de service doit être postérieure ou égale à la date de recrutement'];
        }
    }
}

function validateDateFinService() {
    errors.value.date_fin_service = [];
    if (form.value.date_recrutement && form.value.date_fin_service) {
        if (new Date(form.value.date_recrutement) > new Date(form.value.date_fin_service)) {
            errors.value.date_fin_service = ['La date de fin de service doit être postérieure ou égale à la date de recrutement'];
        }
    }
}

function validateRoles() {
    errors.value.roles = [];
    if (!form.value.roles || form.value.roles.length === 0) {
        errors.value.roles = ['Au moins un rôle est requis'];
    }
}

function validatePassword() {
    errors.value.password = [];
    if (form.value.password && form.value.password.length < 8) {
        errors.value.password = ['Le mot de passe doit contenir au moins 8 caractères'];
    }
    validatePasswordConfirmation();
}

function validatePasswordConfirmation() {
    errors.value.password_confirmation = [];
    if (form.value.password && !form.value.password_confirmation) {
        errors.value.password_confirmation = ['La confirmation du mot de passe est requise'];
    } else if (form.value.password !== form.value.password_confirmation) {
        errors.value.password_confirmation = ['Les mots de passe ne correspondent pas'];
    }
}

// Save user
async function saveUser() {
    try {
        saving.value = true;
        errors.value = {};

        // Validate all fields
        validateField('nom_fr');
        validateField('prenom_fr');
        validateField('nom_ar');
        validateField('prenom_ar');
        validateField('lieu_cin_fr');
        validateField('lieu_cin_ar');
        validateField('lieu_naissance_fr');
        validateField('lieu_naissance_ar');
        validateField('nationalite_fr');
        validateField('nationalite_ar');
        validateField('adresse_fr');
        validateField('adresse_ar');
        validateField('gouvernorat_fr');
        validateField('gouvernorat_ar');
        validateField('delegation_fr');
        validateField('delegation_ar');
        validateField('observation_fr');
        validateField('observation_ar');
        validateTelephone('telephone_1');
        validateTelephone('telephone_2');
        validateDateCin();
        validateDateNaissance();
        validateDateRecrutement();
        validateDateFinService();
        validateRoles();
        validatePassword();
        validatePasswordConfirmation();

        if (!form.value.cin) errors.value.cin = ['Le CIN est requis'];
        if (!form.value.email) errors.value.email = ["L'email est requis"];
        if (!form.value.genre_fr) errors.value.genre_fr = ['Le genre est requis'];
        if (!form.value.statut) errors.value.statut = ['Le statut est requis'];
        if (form.value.statut === 'Inactif' && !form.value.cause_inactivite_fr) {
            errors.value.cause_inactivite_fr = ['La cause d\'inactivité est requise pour un statut inactif'];
        }

        // Wait for async validations
        await Promise.all([debouncedValidateCin.flush(), debouncedValidateEmail.flush()]);

        // Check for errors
        if (Object.values(errors.value).some(err => err.length > 0)) {
            const firstErrorTab = Object.keys(errors.value).find(key => errors.value[key].length > 0);
            if (firstErrorTab) {
                const tabIndex = getTabIndexForField(firstErrorTab);
                if (tabIndex !== null && tabview.value) {
                    // Utiliser nextTick pour éviter la mutation directe de la prop
                    setTimeout(() => {
                        if (tabview.value && tabview.value.$refs && tabview.value.$refs.tabview) {
                            tabview.value.$refs.tabview.activeIndex = tabIndex;
                        }
                    }, 0);
                }
            }
            toast.add({
                severity: 'error',
                summary: 'Erreur de validation',
                detail: 'Veuillez vérifier les champs du formulaire',
                life: 5000,
            });
            return;
        }

        const payload = {
            ...form.value,
            date_cin: form.value.date_cin ? new Date(form.value.date_cin).toISOString().split('T')[0] : null,
            date_naissance: form.value.date_naissance ? new Date(form.value.date_naissance).toISOString().split('T')[0] : null,
            date_recrutement: form.value.date_recrutement ? new Date(form.value.date_recrutement).toISOString().split('T')[0] : null,
            date_fin_service: form.value.date_fin_service ? new Date(form.value.date_fin_service).toISOString().split('T')[0] : null,
            photo: croppedImage.value || (selectedFile.value ? await toBase64(selectedFile.value) : props.user.photo),
            ajouter_par: form.value.ajouter_par || auth().user.id,
        };

        if (!props.isNew && !payload.password) {
            delete payload.password;
            delete payload.password_confirmation;
        }

        let response;
        if (props.isNew) {
            delete payload.matricule;
            response = await axios.post('/api/personnels_atfp', payload);
        } else {
            response = await axios.put(`/api/personnels_atfp/${props.user.id}`, payload);
        }

        emit('update', response.data.data);
        toast.add({
            severity: 'success',
            summary: 'Succès',
            detail: props.isNew ? 'Utilisateur créé avec succès' : 'Utilisateur mis à jour avec succès',
            life: 3000,
        });
        isEditing.value = false;
        resetForm();
    } catch (err) {
        if (err.response?.status === 422) {
            errors.value = err.response.data.errors;
            const firstErrorTab = Object.keys(errors.value).find(key => errors.value[key].length > 0);
            if (firstErrorTab) {
                const tabIndex = getTabIndexForField(firstErrorTab);
                if (tabIndex !== null && tabview.value) {
                    // Utiliser nextTick pour éviter la mutation directe de la prop
                    setTimeout(() => {
                        if (tabview.value && tabview.value.$refs && tabview.value.$refs.tabview) {
                            tabview.value.$refs.tabview.activeIndex = tabIndex;
                        }
                    }, 0);
                }
            }
            toast.add({
                severity: 'error',
                summary: 'Erreur de validation',
                detail: 'Veuillez vérifier les champs du formulaire',
                life: 5000,
            });
        } else {
            toast.add({
                severity: 'error',
                summary: 'Erreur',
                detail: err.response?.data?.message || 'Erreur lors de la sauvegarde',
                life: 5000,
            });
        }
    } finally {
        saving.value = false;
    }
}

function getTabIndexForField(field) {
    const tabFields = {
        'Informations Personnelles': ['matricule', 'cin', 'date_cin', 'lieu_cin_fr', 'lieu_cin_ar', 'nom_fr', 'prenom_fr', 'nom_ar', 'prenom_ar', 'genre_fr', 'genre_ar', 'date_naissance', 'lieu_naissance_fr', 'lieu_naissance_ar', 'nationalite_fr', 'nationalite_ar', 'etat_civil_fr', 'etat_civil_ar'],
        'Contact': ['adresse_fr', 'adresse_ar', 'gouvernorat_fr', 'gouvernorat_ar', 'delegation_fr', 'delegation_ar', 'telephone_1', 'telephone_2'],
        'Rôles': ['roles'],
        'Connexion': ['email', 'password', 'password_confirmation'],
        'Observations': ['observation_fr', 'observation_ar'],
        'Statut': ['statut', 'cause_inactivite_fr', 'cause_inactivite_ar', 'date_recrutement', 'date_fin_service'],
    };
    for (const [tab, fields] of Object.entries(tabFields)) {
        if (fields.includes(field)) {
            return Object.keys(tabFields).indexOf(tab);
        }
    }
    return null;
}

// Utility functions
function formatDate(date) {
    if (!date) return 'Non défini';
    return new Date(date).toLocaleDateString('fr-FR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}

function formatRoles(roles, language = 'fr') {
    return roles && Array.isArray(roles) && roles.length > 0
        ? roles.map((r) => language === 'ar' ? (r.name_ar || r.name || 'N/A') : (r.name || 'N/A')).join(', ')
        : language === 'ar' ? 'لا توجد خطة' : 'Aucun rôle';
}

function isValidPhoto(photo) {
    if (!photo) return false;
    if (photo.startsWith('data:image/') || photo.startsWith('blob:')) return true;
    const validFormats = ['.jpg', '.jpeg', '.png', '.gif', '.bmp', '.webp'];
    return validFormats.some((format) => photo.toLowerCase().endsWith(format));
}

function getPhotoUrl(photo) {
    if (!photo) return '/default-avatar.png';
    if (photo.startsWith('data:image/') || photo.startsWith('blob:')) {
        try {
            if (photo.startsWith('data:image/')) {
                const base64Part = photo.split(',')[1];
                atob(base64Part);
            }
            return photo;
        } catch (e) {
            console.error('Invalid base64 image:', e);
            return '/default-avatar.png';
        }
    }
    if (photo.startsWith('/storage/')) {
        return `${window.location.origin}${photo}`;
    }
    return `${window.location.origin}/storage/photos/${photo}`;
}

async function loadImage(src) {
    return new Promise((resolve, reject) => {
        const img = new Image();
        img.src = src;
        img.onload = () => resolve(src);
        img.onerror = () => {
            console.warn(`Failed to load image: ${src}`);
            resolve('/default-avatar.png');
        };
    });
}

// Photo handling functions
function onFileSelect(event) {
    try {
        imageError.value = null;
        errors.value.photo = [];
        if (event.files && event.files.length > 0) {
            const file = event.files[0];
            if (file.size > 2000000) {
                imageError.value = "La taille de l'image ne doit pas dépasser 2 Mo";
                errors.value.photo = [imageError.value];
                toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: imageError.value,
                    life: 3000,
                });
                return;
            }
            if (!file.type.match('image/(jpg|jpeg|png|gif|bmp|webp)')) {
                imageError.value = 'Veuillez sélectionner une image valide (JPG, PNG, GIF, BMP, WEBP)';
                errors.value.photo = [imageError.value];
                toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: imageError.value,
                    life: 3000,
                });
                return;
            }
            selectedFile.value = file;
            const imageUrl = URL.createObjectURL(file);
            photoPreview.value = imageUrl;
            showCropper.value = false;
            croppedImage.value = null;
            previewImage.value = null;
            toast.add({
                severity: 'success',
                summary: 'Photo sélectionnée',
                detail: 'Vous pouvez recadrer ou supprimer la photo.',
                life: 3000,
            });
        }
    } catch (err) {
        imageError.value = err.message || "Erreur lors de la sélection de l'image";
        errors.value.photo = [imageError.value];
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: imageError.value,
            life: 3000,
        });
    }
}

function onFileClear() {
    if (isClearing.value) return;
    isClearing.value = true;
    try {
        selectedFile.value = null;
        photoPreview.value = props.user.photo ? getPhotoUrl(props.user.photo) : null;
        croppedImage.value = null;
        previewImage.value = null;
        imageError.value = null;
        errors.value.photo = [];
        showCropper.value = false;
        if (fileUpload.value) {
            fileUpload.value.clear();
        }
        toast.add({
            severity: 'info',
            summary: 'Annulé',
            detail: 'Sélection du fichier annulée.',
            life: 3000,
        });
    } finally {
        isClearing.value = false;
    }
}

function startCroppingExistingPhoto() {
    if (showCropper.value) return;
    if (!photoPreview.value && !props.user.photo) {
        imageError.value = 'Aucune image sélectionnée. Veuillez choisir une image d\'abord.';
        errors.value.photo = [imageError.value];
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: imageError.value,
            life: 3000,
        });
        showCropper.value = false;
        return;
    }
    if (photoPreview.value && !isValidPhoto(photoPreview.value)) {
        imageError.value = 'L\'image n\'est pas dans un format valide (JPG, PNG, GIF, BMP, WEBP).';
        errors.value.photo = [imageError.value];
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: imageError.value,
            life: 3000,
        });
        showCropper.value = false;
        return;
    }
    previewImage.value = photoPreview.value || getPhotoUrl(props.user.photo);
    showCropper.value = true;
    selectedFile.value = null;
    croppedImage.value = null;
    toast.add({
        severity: 'info',
        summary: 'Recadrage',
        detail: 'Recadrez l\'image et confirmez ou annulez.',
        life: 3000,
    });
}

function confirmCrop() {
    if (croppedImage.value) {
        photoPreview.value = croppedImage.value;
        previewImage.value = null;
        showCropper.value = false;
        selectedFile.value = null;
        toast.add({
            severity: 'success',
            summary: 'Recadrage confirmé',
            detail: 'Image recadrée avec succès. Enregistrez pour sauvegarder.',
            life: 3000,
        });
    } else {
        toast.add({
            severity: 'warn',
            summary: 'Aucun recadrage',
            detail: 'Veuillez recadrer l\'image avant de confirmer.',
            life: 3000,
        });
    }
}

function cancelCrop() {
    previewImage.value = null;
    croppedImage.value = null;
    showCropper.value = false;
    selectedFile.value = null;
    photoPreview.value = props.user.photo ? getPhotoUrl(props.user.photo) : null;
    toast.add({
        severity: 'info',
        summary: 'Recadrage annulé',
        detail: 'Recadrage de l\'image annulé.',
        life: 3000,
    });
}

async function deletePhoto() {
    try {
        deletingPhoto.value = true;
        photoPreview.value = null;
        selectedFile.value = null;
        previewImage.value = null;
        croppedImage.value = null;
        imageError.value = null;
        errors.value.photo = [];
        showCropper.value = false;
        if (fileUpload.value) {
            fileUpload.value.clear();
        }
        if (!props.isNew) {
            const payload = { photo: null };
            const response = await axios.put(`/api/personnels_atfp/${props.user.id}`, payload);
            emit('update', response.data.data);
        }
        toast.add({
            severity: 'info',
            summary: 'Supprimé',
            detail: 'La photo a été supprimée.',
            life: 3000,
        });
    } catch (err) {
        console.error('Erreur lors de deletePhoto:', err);
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: err.response?.data?.message || 'Erreur lors de la suppression de la photo',
            life: 5000,
        });
    } finally {
        deletingPhoto.value = false;
    }
}

function onCropperReady() {}

function onCropChange({ canvas }) {
    try {
        croppedImage.value = canvas.toDataURL('image/png');
    } catch (err) {
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: "Erreur lors du recadrage de l'image",
            life: 3000,
        });
    }
}

async function uploadPhoto() {
    try {
        if (!props.user.id && !props.isNew) {
            throw new Error('Aucun utilisateur sélectionné');
        }
        uploadingPhoto.value = true;
        const payload = {
            ...form.value,
            date_cin: form.value.date_cin ? new Date(form.value.date_cin).toISOString().split('T')[0] : null,
            date_naissance: form.value.date_naissance ? new Date(form.value.date_naissance).toISOString().split('T')[0] : null,
            date_recrutement: form.value.date_recrutement ? new Date(form.value.date_recrutement).toISOString().split('T')[0] : null,
            date_fin_service: form.value.date_fin_service ? new Date(form.value.date_fin_service).toISOString().split('T')[0] : null,
            photo: croppedImage.value || (selectedFile.value ? await toBase64(selectedFile.value) : props.user.photo),
            ajouter_par: form.value.ajouter_par || auth().user.id,
        };
        if (!props.isNew && !payload.password) {
            delete payload.password;
            delete payload.password_confirmation;
        }
        let response;
        if (props.isNew) {
            delete payload.matricule;
            response = await axios.post('/api/personnels_atfp', payload);
        } else {
            response = await axios.put(`/api/personnels_atfp/${props.user.id}`, payload);
        }
        emit('update', response.data.data);
        toast.add({
            severity: 'success',
            summary: 'Succès',
            detail: 'Photo mise à jour',
            life: 3000,
        });
        photoPreview.value = payload.photo;
        showPhotoDialog.value = false;
    } catch (err) {
        let errorMessage = "Erreur lors de l'upload de la photo";
        if (err.response?.status === 422) {
            errors.value = err.response.data.errors;
            errorMessage = errors.value.photo ? errors.value.photo.join(', ') : 'Erreur de validation';
        } else if (err.response?.status === 404) {
            errorMessage = 'Utilisateur non trouvé';
        } else if (err.response?.status === 405) {
            errorMessage = 'Méthode non autorisée pour cette route';
        } else if (err.response?.status === 500) {
            errorMessage = err.response.data.error || 'Erreur serveur interne';
        } else if (err.message) {
            errorMessage = err.message;
        }
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: errorMessage,
            life: 3000,
        });
    } finally {
        uploadingPhoto.value = false;
        selectedFile.value = null;
        previewImage.value = null;
        croppedImage.value = null;
        imageError.value = null;
        if (fileUpload.value) {
            fileUpload.value.clear();
        }
    }
}

async function toBase64(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => resolve(reader.result);
        reader.onerror = (error) => reject(error);
        reader.readAsDataURL(file);
    });
}

function closePhotoDialog() {
    showPhotoDialog.value = false;
    selectedFile.value = null;
    previewImage.value = null;
    croppedImage.value = null;
    imageError.value = null;
    errors.value.photo = [];
    showCropper.value = false;
    photoPreview.value = props.user.photo ? getPhotoUrl(props.user.photo) : null;
    if (fileUpload.value) {
        fileUpload.value.clear();
    }
}

// Print functions - mises à jour pour utiliser le module externe avec sélection de langue
async function printProfile() {
    // Afficher la popup de sélection de langue d'impression
    showPrintDialog.value = true;
}

async function printProfileFrench() {
    try {
        showPrintDialog.value = false;
        const printWindow = window.open('', '_blank');
        if (!printWindow) {
            throw new Error('Impossible d\'ouvrir une nouvelle fenêtre. Vérifiez les paramètres de votre navigateur.');
        }
        // Utiliser la fonction importée du module externe avec la langue française
        const printHTML = await generatePrintHTML(user.value, photoPreview.value, 'fr');
        printWindow.document.open();
        printWindow.document.write(printHTML);
        printWindow.document.close();
        await new Promise((resolve) => {
            printWindow.onload = resolve;
            setTimeout(resolve, 1000);
        });
        const images = printWindow.document.querySelectorAll('img');
        const imagePromises = Array.from(images).map(img => {
            return new Promise((resolve) => {
                if (img.complete) {
                    resolve();
                } else {
                    img.onload = resolve;
                    img.onerror = () => {
                        console.warn(`Failed to load image: ${img.src}`);
                        resolve();
                    };
                }
            });
        });
        await Promise.all(imagePromises);
        printWindow.print();
        setTimeout(() => printWindow.close(), 20000);
    } catch (err) {
        console.error('Erreur lors de l\'affichage du profil:', err);
        toast.add({
            severity: 'error',
            summary: 'Erreur d\'affichage',
            detail: err.message || 'Une erreur s\'est produite lors de l\'affichage du profil.',
            life: 5000,
        });
    }
}

async function printProfileArabic() {
    try {
        showPrintDialog.value = false;
        const printWindow = window.open('', '_blank');
        if (!printWindow) {
            throw new Error('Impossible d\'ouvrir une nouvelle fenêtre. Vérifiez les paramètres de votre navigateur.');
        }
        // Utiliser la fonction importée du module externe avec la langue arabe
        const printHTML = await generatePrintHTML(user.value, photoPreview.value, 'ar');
        printWindow.document.open();
        printWindow.document.write(printHTML);
        printWindow.document.close();
        await new Promise((resolve) => {
            printWindow.onload = resolve;
            setTimeout(resolve, 1000);
        });
        const images = printWindow.document.querySelectorAll('img');
        const imagePromises = Array.from(images).map(img => {
            return new Promise((resolve) => {
                if (img.complete) {
                    resolve();
                } else {
                    img.onload = resolve;
                    img.onerror = () => {
                        console.warn(`Failed to load image: ${img.src}`);
                        resolve();
                    };
                }
            });
        });
        await Promise.all(imagePromises);
        printWindow.print();
        setTimeout(() => printWindow.close(), 20000);
    } catch (err) {
        console.error('Erreur lors de l\'affichage du profil:', err);
        toast.add({
            severity: 'error',
            summary: 'Erreur d\'affichage',
            detail: err.message || 'Une erreur s\'est produite lors de l\'affichage du profil.',
            life: 5000,
        });
    }
}
</script>

<template>
    <div class="profile-container surface-card p-4 border-round shadow-2 border-1 surface-border">
        <!-- Loading State -->
        <div v-if="loading" class="flex justify-content-center align-items-center h-20rem">
            <ProgressSpinner style="width: 50px; height: 50px" />
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="p-4">
            <Message severity="error" :closable="false">{{ error }}</Message>
        </div>

        <!-- Main Content -->
        <div v-else-if="user" class="grid">
            <!-- Left Column: Profile Card -->
            <div class="col-12 lg:col-4">
                <Card class="profile-card mb-4">
                    <template #header>
                        <div class="flex justify-content-center p-3 relative">
                            <Avatar
                                :image="photoPreview || (user.photo && isValidPhoto(user.photo) ? getPhotoUrl(user.photo) : '/default-avatar.png')"
                                shape="circle"
                                class="custom-avatar"
                            />
                            <Button
                                icon="pi pi-camera"
                                class="p-button-rounded p-button-sm photo-upload-button"
                                severity="primary"
                                v-tooltip="'Changer la photo'"
                                @click="showPhotoDialog = true"
                            />
                        </div>
                    </template>
                    <template #title>
                        <div class="text-center">
                            {{ form.nom_fr || 'Non défini' }} {{ form.prenom_fr || '' }}
                        </div>
                    </template>
                    <template #content>
                        <div class="field mb-3 text-center">
                            <Tag :value="formatRoles(user.roles)" severity="info" />
                        </div>
                        <div class="field mb-3 text-center">
                            <label class="font-semibold">Matricule</label>
                            <div>{{ user.matricule || 'Non défini' }}</div>
                        </div>
                        <div class="field mt-3 text-center">
                            <Tag
                                :value="form.statut || 'Inactif'"
                                :severity="form.statut === 'Actif' ? 'success' : 'danger'"
                            />
                        </div>
                    </template>
                </Card>

                <!-- Contact Info Card -->
                <Card class="mb-4">
                    <template #title>Informations de contact</template>
                    <template #content>
                        <div class="grid">
                            <div class="col-12 field">
                                <label class="font-semibold">Email</label>
                                <div v-if="!isEditing" class="flex align-items-center gap-2">
                                    <i class="pi pi-envelope"></i>
                                    <a :href="`mailto:${form.email}`">{{ form.email || 'Non défini' }}</a>
                                </div>
                                <InputText
                                    v-else
                                    v-model="form.email"
                                    class="field-input"
                                    :class="{ 'p-invalid': errors.email }"
                                    @input="debouncedValidateEmail"
                                />
                                <small v-if="errors.email" class="p-error">{{ errors.email[0] }}</small>
                            </div>
                            <div class="col-12 field">
                                <label class="font-semibold">Téléphone 1</label>
                                <div v-if="!isEditing">{{ form.telephone_1 || 'Non défini' }}</div>
                                <InputText
                                    v-else
                                    v-model="form.telephone_1"
                                    class="field-input"
                                    :class="{ 'p-invalid': errors.telephone_1 }"
                                    @input="validateTelephone('telephone_1')"
                                />
                                <small v-if="errors.telephone_1" class="p-error">{{ errors.telephone_1[0] }}</small>
                            </div>
                            <div class="col-12 field">
                                <label class="font-semibold">Adresse (FR)</label>
                                <div v-if="!isEditing">{{ form.adresse_fr || 'Non défini' }}</div>
                                <InputText
                                    v-else
                                    v-model="form.adresse_fr"
                                    class="field-input"
                                    :class="{ 'p-invalid': errors.adresse_fr }"
                                    @input="validateField('adresse_fr')"
                                />
                                <small v-if="errors.adresse_fr" class="p-error">{{ errors.adresse_fr[0] }}</small>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Right Column: Detailed Info -->
            <div class="col-12 lg:col-8">
                <div class="surface-card p-4 shadow-2 border-round">
                    <TabView class="profile-tabs" ref="tabview">
                        <!-- Personal Information Tab -->
                        <TabPanel header="Informations Personnelles">
                            <Card>
                                <template #content>
                                    <div class="grid">
                                        <div class="col-12 lg:col-6">
                                            <div class="field mb-3">
                                                <label class="font-semibold">Matricule</label>
                                                <div>{{ user.matricule || 'Non défini' }}</div>
                                            </div>
                                        </div>
                                        <div class="col-12 lg:col-6">
                                            <div class="field mb-3">
                                                <label class="font-semibold">CIN</label>
                                                <div v-if="!isEditing">{{ form.cin || 'Non défini' }}</div>
                                                <InputText
                                                    v-else
                                                    v-model="form.cin"
                                                    class="field-input"
                                                    :class="{ 'p-invalid': errors.cin }"
                                                    @input="debouncedValidateCin"
                                                />
                                                <small v-if="errors.cin" class="p-error">{{ errors.cin[0] }}</small>
                                            </div>
                                        </div>
                                        <div class="col-12 lg:col-6">
                                            <div class="field mb-3">
                                                <label class="font-semibold">Date CIN</label>
                                                <div v-if="!isEditing">{{ formatDate(form.date_cin) || 'Non défini' }}</div>
                                                <Calendar
                                                    v-else
                                                    v-model="form.date_cin"
                                                    dateFormat="yy-mm-dd"
                                                    class="field-input"
                                                    :class="{ 'p-invalid': errors.date_cin }"
                                                    @update:modelValue="validateDateCin"
                                                />
                                                <small v-if="errors.date_cin" class="p-error">{{ errors.date_cin[0] }}</small>
                                            </div>
                                        </div>
                                        <div class="col-12 lg:col-6">
                                            <div class="field mb-3">
                                                <label class="font-semibold">Lieu CIN (FR)</label>
                                                <div v-if="!isEditing">{{ form.lieu_cin_fr || 'Non défini' }}</div>
                                                <InputText
                                                    v-else
                                                    v-model="form.lieu_cin_fr"
                                                    class="field-input"
                                                    :class="{ 'p-invalid': errors.lieu_cin_fr }"
                                                    @input="validateField('lieu_cin_fr')"
                                                />
                                                <small v-if="errors.lieu_cin_fr" class="p-error">{{ errors.lieu_cin_fr[0] }}</small>
                                            </div>
                                        </div>
                                        <div class="col-12 lg:col-6">
                                            <div class="field mb-3">
                                                <label class="font-semibold">Lieu CIN (AR)</label>
                                                <div v-if="!isEditing" class="arabic-text">{{ form.lieu_cin_ar || 'غير محدد' }}</div>
                                                <InputText
                                                    v-else
                                                    v-model="form.lieu_cin_ar"
                                                    class="field-input arabic-text"
                                                    :class="{ 'p-invalid': errors.lieu_cin_ar }"
                                                    @input="validateField('lieu_cin_ar')"
                                                />
                                                <small v-if="errors.lieu_cin_ar" class="p-error">{{ errors.lieu_cin_ar[0] }}</small>
                                            </div>
                                        </div>
                                        <div class="col-12 lg:col-6">
                                            <div class="field mb-3">
                                                <label class="font-semibold">Nom (FR)</label>
                                                <div v-if="!isEditing">{{ form.nom_fr || 'Non défini' }}</div>
                                                <InputText
                                                    v-else
                                                    v-model="form.nom_fr"
                                                    class="field-input"
                                                    :class="{ 'p-invalid': errors.nom_fr }"
                                                    @input="validateField('nom_fr')"
                                                />
                                                <small v-if="errors.nom_fr" class="p-error">{{ errors.nom_fr[0] }}</small>
                                            </div>
                                        </div>
                                        <div class="col-12 lg:col-6">
                                            <div class="field mb-3">
                                                <label class="font-semibold">Prénom (FR)</label>
                                                <div v-if="!isEditing">{{ form.prenom_fr || 'Non défini' }}</div>
                                                <InputText
                                                    v-else
                                                    v-model="form.prenom_fr"
                                                    class="field-input"
                                                    :class="{ 'p-invalid': errors.prenom_fr }"
                                                    @input="validateField('prenom_fr')"
                                                />
                                                <small v-if="errors.prenom_fr" class="p-error">{{ errors.prenom_fr[0] }}</small>
                                            </div>
                                        </div>
                                        <div class="col-12 lg:col-6">
                                            <div class="field mb-3">
                                                <label class="font-semibold">Nom (AR)</label>
                                                <div v-if="!isEditing" class="arabic-text">{{ form.nom_ar || 'غير محدد' }}</div>
                                                <InputText
                                                    v-else
                                                    v-model="form.nom_ar"
                                                    class="field-input arabic-text"
                                                    :class="{ 'p-invalid': errors.nom_ar }"
                                                    @input="validateField('nom_ar')"
                                                />
                                                <small v-if="errors.nom_ar" class="p-error">{{ errors.nom_ar[0] }}</small>
                                            </div>
                                        </div>
                                        <div class="col-12 lg:col-6">
                                            <div class="field mb-3">
                                                <label class="font-semibold">Prénom (AR)</label>
                                                <div v-if="!isEditing" class="arabic-text">{{ form.prenom_ar || 'غير محدد' }}</div>
                                                <InputText
                                                    v-else
                                                    v-model="form.prenom_ar"
                                                    class="field-input arabic-text"
                                                    :class="{ 'p-invalid': errors.prenom_ar }"
                                                    @input="validateField('prenom_ar')"
                                                />
                                                <small v-if="errors.prenom_ar" class="p-error">{{ errors.prenom_ar[0] }}</small>
                                            </div>
                                        </div>
                                        <div class="col-12 lg:col-6">
                                            <div class="field mb-3">
                                                <label class="font-semibold">Genre (FR)</label>
                                                <div v-if="!isEditing">{{ form.genre_fr || 'Non défini' }}</div>
                                                <Dropdown
                                                    v-else
                                                    v-model="form.genre_fr"
                                                    :options="genreOptions"
                                                    class="field-input"
                                                    placeholder="Sélectionner le genre"
                                                    :class="{ 'p-invalid': errors.genre_fr }"
                                                    @change="onGenreChange"
                                                />
                                                <small v-if="errors.genre_fr" class="p-error">{{ errors.genre_fr[0] }}</small>
                                            </div>
                                        </div>
                                        <div class="col-12 lg:col-6">
                                            <div class="field mb-3">
                                                <label class="font-semibold">Date de Naissance</label>
                                                <div v-if="!isEditing">{{ formatDate(form.date_naissance) || 'Non défini' }}</div>
                                                <Calendar
                                                    v-else
                                                    v-model="form.date_naissance"
                                                    dateFormat="yy-mm-dd"
                                                    class="field-input"
                                                    :class="{ 'p-invalid': errors.date_naissance }"
                                                    @update:modelValue="validateDateNaissance"
                                                />
                                                <small v-if="errors.date_naissance" class="p-error">{{ errors.date_naissance[0] }}</small>
                                            </div>
                                        </div>
                                        <div class="col-12 lg:col-6">
                                            <div class="field mb-3">
                                                <label class="font-semibold">Lieu de Naissance (FR)</label>
                                                <div v-if="!isEditing">{{ form.lieu_naissance_fr || 'Non défini' }}</div>
                                                <InputText
                                                    v-else
                                                    v-model="form.lieu_naissance_fr"
                                                    class="field-input"
                                                    :class="{ 'p-invalid': errors.lieu_naissance_fr }"
                                                    @input="validateField('lieu_naissance_fr')"
                                                />
                                                <small v-if="errors.lieu_naissance_fr" class="p-error">{{ errors.lieu_naissance_fr[0] }}</small>
                                            </div>
                                        </div>
                                        <div class="col-12 lg:col-6">
                                            <div class="field mb-3">
                                                <label class="font-semibold">Lieu de Naissance (AR)</label>
                                                <div v-if="!isEditing" class="arabic-text">{{ form.lieu_naissance_ar || 'غير محدد' }}</div>
                                                <InputText
                                                    v-else
                                                    v-model="form.lieu_naissance_ar"
                                                    class="field-input arabic-text"
                                                    :class="{ 'p-invalid': errors.lieu_naissance_ar }"
                                                    @input="validateField('lieu_naissance_ar')"
                                                />
                                                <small v-if="errors.lieu_naissance_ar" class="p-error">{{ errors.lieu_naissance_ar[0] }}</small>
                                            </div>
                                        </div>
                                        <div class="col-12 lg:col-6">
                                            <div class="field mb-3">
                                                <label class="font-semibold">Nationalité (FR)</label>
                                                <div v-if="!isEditing">{{ form.nationalite_fr || 'Tunisienne' }}</div>
                                                <InputText
                                                    v-else
                                                    v-model="form.nationalite_fr"
                                                    class="field-input"
                                                    :class="{ 'p-invalid': errors.nationalite_fr }"
                                                    @input="validateField('nationalite_fr')"
                                                />
                                                <small v-if="errors.nationalite_fr" class="p-error">{{ errors.nationalite_fr[0] }}</small>
                                            </div>
                                        </div>
                                        <div class="col-12 lg:col-6">
                                            <div class="field mb-3">
                                                <label class="font-semibold">Nationalité (AR)</label>
                                                <div v-if="!isEditing" class="arabic-text">{{ form.nationalite_ar || 'تونسية' }}</div>
                                                <InputText
                                                    v-else
                                                    v-model="form.nationalite_ar"
                                                    class="field-input arabic-text"
                                                    :class="{ 'p-invalid': errors.nationalite_ar }"
                                                    @input="validateField('nationalite_ar')"
                                                />
                                                <small v-if="errors.nationalite_ar" class="p-error">{{ errors.nationalite_ar[0] }}</small>
                                            </div>
                                        </div>
                                        <div class="col-12 lg:col-6">
                                            <div class="field mb-3">
                                                <label class="font-semibold">État Civil (FR)</label>
                                                <div v-if="!isEditing">{{ form.etat_civil_fr || 'Non défini' }}</div>
                                                <Dropdown
                                                    v-else
                                                    v-model="form.etat_civil_fr"
                                                    :options="etatCivilOptions"
                                                    class="field-input"
                                                    placeholder="Sélectionner l'état civil"
                                                    :class="{ 'p-invalid': errors.etat_civil_fr }"
                                                    @change="onEtatCivilChange"
                                                />
                                                <small v-if="errors.etat_civil_fr" class="p-error">{{ errors.etat_civil_fr[0] }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </Card>
                        </TabPanel>

                        <!-- Contact Tab -->
                        <TabPanel header="Contact">
                            <Card>
                                <template #content>
                                    <div class="grid">
                                        <div class="col-12 lg:col-6">
                                            <div class="field mb-3">
                                                <label class="font-semibold">Adresse (FR)</label>
                                                <div v-if="!isEditing">{{ form.adresse_fr || 'Non défini' }}</div>
                                                <InputText
                                                    v-else
                                                    v-model="form.adresse_fr"
                                                    class="field-input"
                                                    :class="{ 'p-invalid': errors.adresse_fr }"
                                                    @input="validateField('adresse_fr')"
                                                />
                                                <small v-if="errors.adresse_fr" class="p-error">{{ errors.adresse_fr[0] }}</small>
                                            </div>
                                        </div>
                                        <div class="col-12 lg:col-6">
                                            <div class="field mb-3">
                                                <label class="font-semibold">Adresse (AR)</label>
                                                <div v-if="!isEditing" class="arabic-text">{{ form.adresse_ar || 'غير محدد' }}</div>
                                                <InputText
                                                    v-else
                                                    v-model="form.adresse_ar"
                                                    class="field-input arabic-text"
                                                    :class="{ 'p-invalid': errors.adresse_ar }"
                                                    @input="validateField('adresse_ar')"
                                                />
                                                <small v-if="errors.adresse_ar" class="p-error">{{ errors.adresse_ar[0] }}</small>
                                            </div>
                                        </div>
                                        <div class="col-12 lg:col-6">
                                            <div class="field mb-3">
                                                <label class="font-semibold">Gouvernorat (FR)</label>
                                                <div v-if="!isEditing">{{ form.gouvernorat_fr || 'Non défini' }}</div>
                                                <Dropdown
                                                    v-else
                                                    v-model="form.gouvernorat_fr"
                                                    :options="gouvernorats"
                                                    class="field-input"
                                                    placeholder="Sélectionner le gouvernorat"
                                                    :class="{ 'p-invalid': errors.gouvernorat_fr }"
                                                    @change="onGouvernoratChange"
                                                />
                                                <small v-if="errors.gouvernorat_fr" class="p-error">{{ errors.gouvernorat_fr[0] }}</small>
                                            </div>
                                        </div>
                                        <div class="col-12 lg:col-6">
                                            <div class="field mb-3">
                                                <label class="font-semibold">Gouvernorat (AR)</label>
                                                <div v-if="!isEditing" class="arabic-text">{{ form.gouvernorat_ar || 'غير محدد' }}</div>
                                                <InputText
                                                    v-else
                                                    v-model="form.gouvernorat_ar"
                                                    class="field-input arabic-text"
                                                    :class="{ 'p-invalid': errors.gouvernorat_ar }"
                                                    disabled
                                                />
                                                <small v-if="errors.gouvernorat_ar" class="p-error">{{ errors.gouvernorat_ar[0] }}</small>
                                            </div>
                                        </div>
                                        <div class="col-12 lg:col-6">
                                            <div class="field mb-3">
                                                <label class="font-semibold">Délégation (FR)</label>
                                                <div v-if="!isEditing">{{ form.delegation_fr || 'Non défini' }}</div>
                                                <InputText
                                                    v-else
                                                    v-model="form.delegation_fr"
                                                    class="field-input"
                                                    :class="{ 'p-invalid': errors.delegation_fr }"
                                                    @input="validateField('delegation_fr')"
                                                />
                                                <small v-if="errors.delegation_fr" class="p-error">{{ errors.delegation_fr[0] }}</small>
                                            </div>
                                        </div>
                                        <div class="col-12 lg:col-6">
                                            <div class="field mb-3">
                                                <label class="font-semibold">Délégation (AR)</label>
                                                <div v-if="!isEditing" class="arabic-text">{{ form.delegation_ar || 'غير محدد' }}</div>
                                                <InputText
                                                    v-else
                                                    v-model="form.delegation_ar"
                                                    class="field-input arabic-text"
                                                    :class="{ 'p-invalid': errors.delegation_ar }"
                                                    @input="validateField('delegation_ar')"
                                                />
                                                <small v-if="errors.delegation_ar" class="p-error">{{ errors.delegation_ar[0] }}</small>
                                            </div>
                                        </div>
                                        <div class="col-12 lg:col-6">
                                            <div class="field mb-3">
                                                <label class="font-semibold">Téléphone 1</label>
                                                <div v-if="!isEditing">{{ form.telephone_1 || 'Non défini' }}</div>
                                                <InputText
                                                    v-else
                                                    v-model="form.telephone_1"
                                                    class="field-input"
                                                    :class="{ 'p-invalid': errors.telephone_1 }"
                                                    @input="validateTelephone('telephone_1')"
                                                />
                                                <small v-if="errors.telephone_1" class="p-error">{{ errors.telephone_1[0] }}</small>
                                            </div>
                                        </div>
                                        <div class="col-12 lg:col-6">
                                            <div class="field mb-3">
                                                <label class="font-semibold">Téléphone 2</label>
                                                <div v-if="!isEditing">{{ form.telephone_2 || 'Non défini' }}</div>
                                                <InputText
                                                    v-else
                                                    v-model="form.telephone_2"
                                                    class="field-input"
                                                    :class="{ 'p-invalid': errors.telephone_2 }"
                                                    @input="validateTelephone('telephone_2')"
                                                />
                                                <small v-if="errors.telephone_2" class="p-error">{{ errors.telephone_2[0] }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </Card>
                        </TabPanel>

                        <!-- Roles Tab -->
                        <TabPanel header="Rôles">
                            <Card>
                                <template #content>
                                    <div class="grid">
                                        <div class="col-12">
                                            <div class="field mb-3">
                                                <label class="font-semibold">Rôles</label>
                                                <div v-if="!isEditing">{{ formatRoles(user.roles) }}</div>
                                                <MultiSelect
                                                    v-else
                                                    v-model="form.roles"
                                                    :options="availableRoles"
                                                    optionLabel="name"
                                                    optionValue="id"
                                                    class="field-input"
                                                    placeholder="Sélectionner les rôles"
                                                    :class="{ 'p-invalid': errors.roles }"
                                                    @change="validateRoles"
                                                />
                                                <small v-if="errors.roles" class="p-error">{{ errors.roles[0] }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </Card>
                        </TabPanel>

                        <!-- Login Tab -->
                        <TabPanel header="Connexion">
                            <Card>
                                <template #content>
                                    <div class="grid">
                                        <div class="col-12 lg:col-6">
                                            <div class="field mb-3">
                                                <label class="font-semibold">Email</label>
                                                <div v-if="!isEditing">{{ form.email || 'Non défini' }}</div>
                                                <InputText
                                                    v-else
                                                    v-model="form.email"
                                                    class="field-input"
                                                    :class="{ 'p-invalid': errors.email }"
                                                    @input="debouncedValidateEmail"
                                                />
                                                <small v-if="errors.email" class="p-error">{{ errors.email[0] }}</small>
                                            </div>
                                        </div>
                                        <div class="col-12 lg:col-6">
                                            <div class="field mb-3">
                                                <label class="font-semibold">Mot de passe</label>
                                                <div v-if="!isEditing">********</div>
                                                <Password
                                                    v-else
                                                    v-model="form.password"
                                                    class="field-input"
                                                    toggleMask
                                                    :class="{ 'p-invalid': errors.password }"
                                                    @input="validatePassword"
                                                />
                                                <small v-if="errors.password" class="p-error">{{ errors.password[0] }}</small>
                                            </div>
                                        </div>
                                        <div class="col-12 lg:col-6">
                                            <div class="field mb-3">
                                                <label class="font-semibold">Confirmation du mot de passe</label>
                                                <div v-if="!isEditing">********</div>
                                                <Password
                                                    v-else
                                                    v-model="form.password_confirmation"
                                                    class="field-input"
                                                    toggleMask
                                                    :class="{ 'p-invalid': errors.password_confirmation }"
                                                    @input="validatePasswordConfirmation"
                                                />
                                                <small v-if="errors.password_confirmation" class="p-error">{{ errors.password_confirmation[0] }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </Card>
                        </TabPanel>

                        <!-- Observations Tab -->
                        <TabPanel header="Observations">
                            <Card>
                                <template #content>
                                    <div class="grid">
                                        <div class="col-12">
                                            <div class="field mb-3">
                                                <label class="font-semibold">Observation (FR)</label>
                                                <div v-if="!isEditing">{{ form.observation_fr || 'Non défini' }}</div>
                                                <Textarea
                                                    v-else
                                                    v-model="form.observation_fr"
                                                    rows="4"
                                                    class="field-input"
                                                    :class="{ 'p-invalid': errors.observation_fr }"
                                                    @input="validateField('observation_fr')"
                                                />
                                                <small v-if="errors.observation_fr" class="p-error">{{ errors.observation_fr[0] }}</small>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="field mb-3">
                                                <label class="font-semibold">Observation (AR)</label>
                                                <div v-if="!isEditing" class="arabic-text">{{ form.observation_ar || 'غير محدد' }}</div>
                                                <Textarea
                                                    v-else
                                                    v-model="form.observation_ar"
                                                    rows="4"
                                                    class="field-input arabic-text"
                                                    :class="{ 'p-invalid': errors.observation_ar }"
                                                    @input="validateField('observation_ar')"
                                                />
                                                <small v-if="errors.observation_ar" class="p-error">{{ errors.observation_ar[0] }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </Card>
                        </TabPanel>

                        <!-- Status Tab -->
                        <TabPanel header="Statut">
                            <Card>
                                <template #content>
                                    <div class="grid">
                                        <div class="col-12 lg:col-6">
                                            <div class="field mb-3">
                                                <label class="font-semibold">Statut</label>
                                                <div v-if="!isEditing">{{ form.statut || 'Inactif' }}</div>
                                                <div v-else class="flex gap-3">
                                                    <RadioButton v-model="form.statut" inputId="actif" value="Actif" @change="onStatutChange" />
                                                    <label for="actif">Actif</label>
                                                    <RadioButton v-model="form.statut" inputId="inactif" value="Inactif" @change="onStatutChange" />
                                                    <label for="inactif">Inactif</label>
                                                </div>
                                                <small v-if="errors.statut" class="p-error">{{ errors.statut[0] }}</small>
                                            </div>
                                        </div>
                                        <div class="col-12 lg:col-6">
                                            <div class="field mb-3">
                                                <label class="font-semibold">Cause d'inactivité (FR)</label>
                                                <div v-if="!isEditing">{{ form.cause_inactivite_fr || 'Non défini' }}</div>
                                                <Dropdown
                                                    v-else
                                                    v-model="form.cause_inactivite_fr"
                                                    :options="causeInactiviteOptions"
                                                    class="field-input"
                                                    placeholder="Sélectionner la cause"
                                                    :disabled="form.statut !== 'Inactif'"
                                                    :class="{ 'p-invalid': errors.cause_inactivite_fr }"
                                                    @change="onCauseInactiviteChange"
                                                />
                                                <small v-if="errors.cause_inactivite_fr" class="p-error">{{ errors.cause_inactivite_fr[0] }}</small>
                                            </div>
                                        </div>
                                        <div class="col-12 lg:col-6">
                                            <div class="field mb-3">
                                                <label class="font-semibold">Cause d'inactivité (AR)</label>
                                                <div v-if="!isEditing" class="arabic-text">{{ form.cause_inactivite_ar || 'غير محدد' }}</div>
                                                <InputText
                                                    v-else
                                                    v-model="form.cause_inactivite_ar"
                                                    class="field-input arabic-text"
                                                    :disabled="form.statut !== 'Inactif'"
                                                    :class="{ 'p-invalid': errors.cause_inactivite_ar }"
                                                />
                                                <small v-if="errors.cause_inactivite_ar" class="p-error">{{ errors.cause_inactivite_ar[0] }}</small>
                                            </div>
                                        </div>
                                        <div class="col-12 lg:col-6">
                                            <div class="field mb-3">
                                                <label class="font-semibold">Date de recrutement</label>
                                                <div v-if="!isEditing">{{ formatDate(form.date_recrutement) || 'Non défini' }}</div>
                                                <Calendar
                                                    v-else
                                                    v-model="form.date_recrutement"
                                                    dateFormat="yy-mm-dd"
                                                    class="field-input"
                                                    :class="{ 'p-invalid': errors.date_recrutement }"
                                                    @update:modelValue="validateDateRecrutement"
                                                />
                                                <small v-if="errors.date_recrutement" class="p-error">{{ errors.date_recrutement[0] }}</small>
                                            </div>
                                        </div>
                                        <div class="col-12 lg:col-6">
                                            <div class="field mb-3">
                                                <label class="font-semibold">Date de fin de service</label>
                                                <div v-if="!isEditing">{{ formatDate(form.date_fin_service) || 'Non défini' }}</div>
                                                <Calendar
                                                    v-else
                                                    v-model="form.date_fin_service"
                                                    dateFormat="yy-mm-dd"
                                                    class="field-input"
                                                    :class="{ 'p-invalid': errors.date_fin_service }"
                                                    @update:modelValue="validateDateFinService"
                                                />
                                                <small v-if="errors.date_fin_service" class="p-error">{{ errors.date_fin_service[0] }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </Card>
                        </TabPanel>
                    </TabView>

                    <!-- Action Buttons -->
                    <div class="flex justify-content-end mt-4 gap-2">
                        <Button v-if="!isEditing" label="Modifier" @click="startEditing" />
                        <Button
                            v-if="isEditing"
                            label="Enregistrer"
                            icon="pi pi-check"
                            class="p-button-success"
                            :loading="saving"
                            @click="saveUser"
                        />
                        <Button
                            v-if="isEditing"
                            label="Annuler"
                            icon="pi pi-times"
                            class="p-button-secondary"
                            @click="cancelEditing"
                        />
                        <Button
                            label="Imprimer"
                            icon="pi pi-print"
                            class="p-button-info"
                            @click="printProfile"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Fallback for undefined user -->
        <div v-else class="p-4">
            <Message severity="warn" :closable="false">
                Aucune information d'utilisateur disponible.
            </Message>
        </div>

        <!-- Photo Upload Dialog -->
        <Dialog
            v-model:visible="showPhotoDialog"
            header="Changer la photo de l'utilisateur"
            :modal="true"
            :style="{ width: '80rem', minHeight: showCropper ? '600px' : 'auto' }"
            :pt="{
                root: 'border-round-xl shadow-5',
                mask: { style: 'backdrop-filter: blur(4px)' },
                header: { class: 'surface-50 border-bottom-1 surface-border p-4' },
                content: { class: 'surface-ground p-4' },
                footer: { class: 'surface-50 border-top-1 surface-border p-4' },
            }"
        >
            <div class="grid p-fluid">
                <div class="col-12 md:col-6 field">
                    <label class="block font-medium mb-2">Photo</label>
                    <FileUpload
                        ref="fileUpload"
                        name="photo"
                        accept="image/png,image/jpeg,image/jpg,image/gif,image/bmp,image/webp"
                        :maxFileSize="2000000"
                        :customUpload="true"
                        chooseLabel="Choisir"
                        cancelLabel="Annuler"
                        @select="onFileSelect"
                        @clear="onFileClear"
                        :class="{ 'p-invalid': errors.photo }"
                    >
                        <template #empty>
                            <p>Glissez-déposez une image ici ou cliquez pour sélectionner (PNG, JPG, JPEG, GIF, BMP, WEBP, max 2Mo).</p>
                        </template>
                    </FileUpload>
                    <small v-if="errors.photo" class="p-error">{{ errors.photo[0] }}</small>
                </div>
                <div class="col-12 md:col-6 field">
                    <label class="block font-medium mb-2">Aperçu et Recadrage</label>
                    <div v-if="showCropper" class="cropper-wrapper">
                        <div class="cropper-container">
                            <div v-if="!previewImage" class="flex flex-column align-items-center justify-content-center h-full">
                                <i class="pi pi-exclamation-triangle text-5xl text-red-500"></i>
                                <p class="text-error">Aucune image à recadrer. Veuillez sélectionner une image valide.</p>
                            </div>
                            <Cropper
                                v-else
                                :src="previewImage"
                                :stencil-props="{
                                    aspectRatio: null,
                                    movable: true,
                                    resizable: true,
                                    resizeHandlers: { enabled: true, positions: ['bottom-right'] },
                                    style: { border: '2px solid #007bff', borderRadius: '0', background: 'transparent' },
                                    initialSize: { width: 200, height: 200 },
                                }"
                                :canvas="{ minWidth: 100, minHeight: 100 }"
                                @change="onCropChange"
                                @ready="onCropperReady"
                                class="cropper-component"
                            />
                        </div>
                        <div class="cropper-actions">
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
                    <div v-else-if="photoPreview || (user.photo && isValidPhoto(user.photo))" class="flex align-items-center gap-3">
                        <img
                            :src="photoPreview || getPhotoUrl(user.photo)"
                            alt="Aperçu de la photo"
                            class="border-round shadow-2"
                            style="max-width: 200px; max-height: 200px; object-fit: contain;"
                        />
                        <div class="flex flex-column gap-2">
                            <Button
                                label="Recadrer"
                                icon="pi pi-crop"
                                class="p-button-success"
                                @click="startCroppingExistingPhoto"
                            />
                            <Button
                                label="Supprimer"
                                icon="pi pi-trash"
                                class="p-button-secondary"
                                :loading="deletingPhoto"
                                @click="deletePhoto"
                            />
                        </div>
                    </div>
                    <div v-else class="flex flex-column align-items-center gap-2">
                        <i class="pi pi-image text-5xl text-surface-300"></i>
                        <small class="text-error" v-if="imageError">{{ imageError }}</small>
                        <p class="text-500">Aucune image sélectionnée ou existante.</p>
                    </div>
                </div>
            </div>
            <template #footer>
                <Button
                    label="Annuler"
                    icon="pi pi-times"
                    severity="secondary"
                    raised
                    @click="closePhotoDialog"
                />
                <Button
                    label="Enregistrer"
                    icon="pi pi-check"
                    severity="primary"
                    raised
                    :disabled="!croppedImage && !selectedFile && !user.photo"
                    :loading="uploadingPhoto"
                    @click="uploadPhoto"
                />
            </template>
        </Dialog>

        <!-- Print Language Selection Dialog -->
        <Dialog
            v-model:visible="showPrintDialog"
            header="Sélectionner la version d'impression"
            :modal="true"
            :style="{ width: '40rem' }"
            :pt="{
                root: 'border-round-xl shadow-5',
                mask: { style: 'backdrop-filter: blur(4px)' },
                header: { class: 'surface-50 border-bottom-1 surface-border p-4' },
                content: { class: 'surface-ground p-4' },
                footer: { class: 'surface-50 border-top-1 surface-border p-4' },
            }"
        >
            <div class="flex flex-column align-items-center gap-4">
                <p>Veuillez sélectionner la langue pour l'impression du profil :</p>
                <div class="flex gap-3 w-full">
                    <Button
                        label="Version Française"
                        icon="pi pi-file"
                        class="p-button-success flex-1"
                        @click="printProfileFrench"
                    />
                    <Button
                        label="النسخة العربية"
                        icon="pi pi-file"
                        class="p-button-success flex-1 arabic-text"
                        @click="printProfileArabic"
                    />
                </div>
            </div>
            <template #footer>
                <Button
                    label="Annuler"
                    icon="pi pi-times"
                    severity="secondary"
                    raised
                    @click="showPrintDialog = false"
                />
            </template>
        </Dialog>
    </div>
</template>

<style scoped>
@font-face {
    font-family: 'Montserrat-Arabic-Regular';
    src: url('/fonts/Montserrat-Arabic-Regular.otf') format('opentype');
    font-weight: normal;
    font-style: normal;
    font-display: swap;
}

/* Modern container styling */
.profile-container {
    width: 100%;
    max-width: 1400px;
    margin: 0 auto;
    padding: 1.5rem 1rem;
    box-sizing: border-box;
    overflow-x: hidden;
    background-color: var(--surface-ground);
    min-height: 100vh;
}

@media (max-width: 980px) {
    .profile-container {
        width: 100%;
        padding: 1.5rem 1rem;
    }
}

/* Card styling with modern effects */
.profile-card {
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    overflow: hidden;
    background: var(--surface-card);
}

.profile-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

.profile-card :deep(.p-card-title) {
    font-size: 1.25rem;
    margin-bottom: 0.5rem;
    color: white;
    font-weight: 600;
}

/* Tab styling with modern look */
.profile-tabs {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    background: var(--surface-card);
}

.profile-tabs :deep(.p-tabview .p-tabview-nav) {
    background: var(--surface-section);
    border: 1px solid var(--surface-border);
    padding: 0.5rem;
}

.profile-tabs :deep(.p-tabview .p-tabview-nav li .p-tabview-nav-link) {
    background: transparent;
    border: 1px solid transparent;
    color: var(--text-color-secondary);
    font-weight: 500;
    padding: 0.75rem 1.25rem;
    border-radius: 6px;
    transition: all 0.2s ease;
    margin: 0 0.25rem;
}

.profile-tabs :deep(.p-tabview .p-tabview-nav li .p-tabview-nav-link:not(.p-disabled):hover) {
    background: var(--surface-hover);
    color: var(--text-color);
}

.profile-tabs :deep(.p-tabview .p-tabview-nav li.p-highlight .p-tabview-nav-link) {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}

.profile-tabs :deep(.p-tabview-panels) {
    background: transparent;
    padding: 1rem;
}

.profile-tabs :deep(.p-card) {
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    border: 1px solid var(--surface-border);
    margin-bottom: 1rem;
}

/* Field styling */
.field {
    margin-bottom: 1.25rem;
    position: relative;
}

.field label {
    display: block;
    margin-bottom: 0.5rem;
    color: var(--text-color-secondary);
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.field div,
.field-input {
    padding: 0.75rem 1rem;
    background: var(--surface-input);
    border-radius: 8px;
    border: 1px solid var(--surface-border);
    transition: all 0.2s ease;
    width: 100%;
    box-sizing: border-box;
    font-size: 0.875rem;
    color: var(--text-color);
}

.field div:hover,
.field-input:hover {
    border-color: var(--primary-color);
}

.field-input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
}

.field:last-child div,
.field:last-child .field-input {
    border-bottom: none;
}

/* Avatar and photo styling */
.custom-avatar {
    width: 200px !important;
    height: 200px !important;
    border: 4px solid white;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transition: transform 0.3s ease;
    /* Ajoutez ces propriétés */
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background-color: #f8f9fa; /* Couleur de fond pour les espaces vides */
}

.custom-avatar :deep(img) {
    width: 100%;
    height: 100%;
    object-fit: contain; /* L'image conserve ses proportions sans être étirée */
    object-position: center; /* Centre l'image dans le conteneur */
}

.custom-avatar:hover {
    transform: scale(1.05);
}

.photo-upload-button {
    position: absolute;
    bottom: -5px;
    right: 10px;
    background: var(--primary-color);
    border: none;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
}

.photo-upload-button:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

/* Arabic text styling */
.arabic-text {
    font-family: 'Montserrat-Arabic-Regular', sans-serif;
    direction: rtl;
    text-align: right;
}

/* Button styling */
:deep(.p-button) {
    border-radius: 8px;
    font-weight: 500;
    padding: 1rem 1.5rem; /* Augmentation de la hauteur */
    transition: all 0.2s ease;
    min-height: 48px; /* Hauteur minimale */
}

:deep(.p-button:hover) {
    transform: translateY(-1px);
}

:deep(.p-button-primary) {
    background: var(--primary-color);
    border: 1px solid var(--primary-color);
}

:deep(.p-button-success) {
    background: var(--green-500);
    border: 1px solid var(--green-500);
}

:deep(.p-button-secondary) {
    background: var(--surface-500);
    border: 1px solid var(--surface-500);
}

:deep(.p-button-info) {
    background: var(--blue-500);
    border: 1px solid var(--blue-500);
}

/* Input styling */
:deep(.p-inputtext),
:deep(.p-calendar),
:deep(.p-dropdown),
:deep(.p-multiselect),
:deep(.p-password),
:deep(.p-textarea) {
    border-radius: 8px;
    border: 1px solid var(--surface-border);
    padding: 0.75rem 1rem;
    transition: all 0.2s ease;
    background: var(--surface-input);
    color: var(--text-color);
}

:deep(.p-inputtext:focus),
:deep(.p-calendar:focus),
:deep(.p-dropdown:focus),
:deep(.p-multiselect:focus),
:deep(.p-password:focus),
:deep(.p-textarea:focus) {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
}

:deep(.p-invalid) {
    border-color: var(--red-500) !important;
}

:deep(.p-error) {
    color: var(--red-500);
    font-size: 0.75rem;
    margin-top: 0.25rem;
    display: block;
}

/* Responsive design */
@media (max-width: 960px) {
    .custom-avatar {
        width: 160px !important;
        height: 160px !important;
    }

    .profile-tabs :deep(.p-tabview-nav-link) {
        padding: 0.5rem 0.75rem;
        font-size: 0.8rem;
    }
}

@media (max-width: 768px) {
    .field div,
    .field-input {
        font-size: 0.8rem;
        padding: 0.6rem 0.8rem;
    }

    .profile-card :deep(.p-card-title) {
        font-size: 1.1rem;
    }

    .profile-tabs :deep(.p-tabview-nav-link) {
        padding: 0.4rem 0.6rem;
        font-size: 0.75rem;
    }

    .custom-avatar {
        width: 140px !important;
        height: 140px !important;
    }

    :deep(.p-button) {
        padding: 0.75rem 1rem; /* Ajustement pour mobile */
        min-height: 44px; /* Hauteur minimale pour mobile */
    }
}

/* Cropper styling */
.cropper-wrapper {
    display: flex;
    flex-direction: column;
    height: 450px;
    gap: 1rem;
}

.cropper-container {
    flex: 1;
    position: relative;
    overflow: hidden;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    background: var(--surface-card);
    min-height: 350px;
}

.cropper-component {
    width: 100%;
    height: 100%;
}

.cropper-actions {
    display: flex;
    justify-content: center;
    gap: 1rem;
    padding: 0.5rem 0;
    background: var(--surface-card);
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

:deep(.cropper-handler) {
    display: none !important;
}

:deep(.cropper-handler.bottom-right) {
    display: block !important;
    background: var(--primary-color) !important;
    border: 2px solid #ffffff !important;
    width: 16px !important;
    height: 16px !important;
    border-radius: 50%;
    cursor: se-resize;
    box-shadow: 0 0 8px rgba(0, 0, 0, 0.3);
}

:deep(.cropper-viewport) {
    background: transparent !important;
    border: none !important;
}

:deep(.cropper-face) {
    background: transparent !important;
}

.text-error {
    color: var(--red-500);
    font-weight: 500;
}

/* Tag styling */
:deep(.p-tag) {
    border-radius: 16px;
    font-weight: 500;
    padding: 0.25rem 0.75rem;
    font-size: 0.75rem;
}

:deep(.p-tag.p-tag-success) {
    background: var(--green-100);
    color: var(--green-700);
}

:deep(.p-tag.p-tag-danger) {
    background: var(--red-100);
    color: var(--red-700);
}

:deep(.p-tag.p-tag-info) {
    background: var(--blue-100);
    color: var(--blue-700);
}

/* Dialog styling */
:deep(.p-dialog) {
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    overflow: hidden;
}

:deep(.p-dialog .p-dialog-header) {
    background: var(--primary-color);
    color: white;
    border: none;
    padding: 1rem 1.5rem;
}

:deep(.p-dialog .p-dialog-content) {
    padding: 1.5rem;
    background: var(--surface-ground);
}

:deep(.p-dialog .p-dialog-footer) {
    background: var(--surface-card);
    border: none;
    padding: 1rem 1.5rem;
}

/* File upload styling */
:deep(.p-fileupload) {
    border-radius: 8px;
    border: 2px dashed var(--surface-border);
    background: var(--surface-card);
    transition: all 0.2s ease;
}

:deep(.p-fileupload:hover) {
    border-color: var(--primary-color);
}

:deep(.p-fileupload-content) {
    padding: 1.5rem;
}

/* Animation improvements */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.profile-container {
    animation: fadeIn 0.5s ease-out;
}

.profile-card,
.profile-tabs {
    animation: fadeIn 0.6s ease-out;
}

/* Loading spinner styling */
:deep(.p-progress-spinner-circle) {
    stroke: var(--primary-color);
    stroke-width: 3;
}

/* Message styling */
:deep(.p-message) {
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    border: none;
}

:deep(.p-message.p-message-error) {
    background: var(--red-50);
    color: var(--red-700);
    border: 1px solid var(--red-200);
}

:deep(.p-message.p-message-warn) {
    background: var(--yellow-50);
    color: var(--yellow-700);
    border: 1px solid var(--yellow-200);
}

/* Modern card hover effects */
:deep(.p-card:hover) {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
}

/* Radio button styling */
:deep(.p-radiobutton .p-radiobutton-box) {
    border-radius: 50%;
    border: 2px solid var(--surface-border);
    transition: all 0.2s ease;
}

:deep(.p-radiobutton .p-radiobutton-box:hover) {
    border-color: var(--primary-color);
}

:deep(.p-radiobutton.p-highlight .p-radiobutton-box) {
    background: var(--primary-color);
    border-color: var(--primary-color);
}

/* Responsive grid improvements */
@media (max-width: 768px) {
    :deep(.p-dialog) {
        width: 95% !important;
        margin: 1rem;
    }

    .cropper-container {
        width: 100%;
        max-width: 300px;
        height: 300px;
    }
}

/* Modern scrollbar styling */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: var(--surface-ground);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: var(--surface-border);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: var(--primary-color);
}
</style>
