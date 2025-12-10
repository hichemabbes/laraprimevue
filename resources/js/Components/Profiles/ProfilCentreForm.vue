<script setup>
import { ref } from 'vue';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import axios from '@/axios.js';
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const emit = defineEmits(['submit']);

const form = ref({
    code: '',
    nom_fr: '',
    email: '',
    telephone_1: '',
    adresse_fr: '',
    nom_ar: '',
    adresse_ar: '',
    telephone_2: '',
    fax: '',
    gouvernorat_id: null,
    type_id: null,
    economat_fr: '',
    economat_ar: '',
    logo: null,
    statut_id: null,
    date_creation: '',
    date_fermeture: '',
    observation: '',
});

const loading = ref(false);
const gouvernoratOptions = ref([]);
const typeOptions = ref([]);
const statutOptions = ref([]);

// Charger les options pour les listes déroulantes
const loadOptions = async () => {
    try {
        const [gouvernorats, types, statuts] = await Promise.all([
            axios.get('/api/options-listes', {
                params: { type_categorie_id: 'Gouvernorat' },
            }),
            axios.get('/api/options-listes', {
                params: { type_categorie_id: 'TypeCentre' },
            }),
            axios.get('/api/options-listes', {
                params: { type_categorie_id: 'StatutCentre' },
            }),
        ]);

        gouvernoratOptions.value = gouvernorats.data;
        typeOptions.value = types.data;
        statutOptions.value = statuts.data;
    } catch (error) {
        console.error('Erreur lors du chargement des options:', error);
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: 'Impossible de charger les options',
            life: 3000,
        });
    }
};

// Charger les options au montage du composant
loadOptions();

const submitForm = async () => {
    loading.value = true;
    try {
        const response = await axios.post('/api/centres', form.value);
        emit('submit', response.data);

        // Réinitialiser le formulaire après soumission réussie
        form.value = {
            code: '',
            nom_fr: '',
            email: '',
            telephone_1: '',
            adresse_fr: '',
            nom_ar: '',
            adresse_ar: '',
            telephone_2: '',
            fax: '',
            gouvernorat_id: null,
            type_id: null,
            economat_fr: '',
            economat_ar: '',
            logo: null,
            statut_id: null,
            date_creation: '',
            date_fermeture: '',
            observation: '',
        };

        toast.add({
            severity: 'success',
            summary: 'Succès',
            detail: 'Centre créé avec succès',
            life: 3000,
        });
    } catch (error) {
        console.error("Erreur lors de l'ajout du centre", error);

        let errorMessage = 'Erreur lors de la création du centre';
        if (error.response?.data?.errors) {
            errorMessage = Object.values(error.response.data.errors).join('\n');
        } else if (error.response?.data?.message) {
            errorMessage = error.response.data.message;
        }

        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: errorMessage,
            life: 5000,
        });
    } finally {
        loading.value = false;
    }
};

// Gestion du téléchargement de logo
const onLogoUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            form.value.logo = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};
</script>

<template>
    <div class="flex flex-column gap-3">
        <span class="p-float-label">
            <InputText
                id="code"
                v-model="form.code"
                class="w-full"
                :disabled="loading"
            />
            <label for="code">Code</label>
        </span>

        <span class="p-float-label">
            <InputText
                id="nom_fr"
                v-model="form.nom_fr"
                class="w-full"
                :disabled="loading"
            />
            <label for="nom_fr">Nom (FR)</label>
        </span>

        <span class="p-float-label">
            <InputText
                id="nom_ar"
                v-model="form.nom_ar"
                class="w-full"
                :disabled="loading"
            />
            <label for="nom_ar">Nom (AR)</label>
        </span>

        <span class="p-float-label">
            <InputText
                id="adresse_fr"
                v-model="form.adresse_fr"
                class="w-full"
                :disabled="loading"
            />
            <label for="adresse_fr">Adresse (FR)</label>
        </span>

        <span class="p-float-label">
            <InputText
                id="adresse_ar"
                v-model="form.adresse_ar"
                class="w-full"
                :disabled="loading"
            />
            <label for="adresse_ar">Adresse (AR)</label>
        </span>

        <span class="p-float-label">
            <InputText
                id="telephone_1"
                v-model="form.telephone_1"
                class="w-full"
                :disabled="loading"
            />
            <label for="telephone_1">Téléphone 1</label>
        </span>

        <span class="p-float-label">
            <InputText
                id="telephone_2"
                v-model="form.telephone_2"
                class="w-full"
                :disabled="loading"
            />
            <label for="telephone_2">Téléphone 2</label>
        </span>

        <span class="p-float-label">
            <InputText
                id="fax"
                v-model="form.fax"
                class="w-full"
                :disabled="loading"
            />
            <label for="fax">Fax</label>
        </span>

        <span class="p-float-label">
            <InputText
                id="email"
                v-model="form.email"
                class="w-full"
                :disabled="loading"
            />
            <label for="email">Email</label>
        </span>

        <div class="field">
            <label for="gouvernorat_id">Gouvernorat</label>
            <Dropdown
                id="gouvernorat_id"
                v-model="form.gouvernorat_id"
                :options="gouvernoratOptions"
                optionLabel="nom"
                optionValue="id"
                placeholder="Sélectionner un gouvernorat"
                :disabled="loading"
                class="w-full"
            />
        </div>

        <div class="field">
            <label for="type_id">Type de centre</label>
            <Dropdown
                id="type_id"
                v-model="form.type_id"
                :options="typeOptions"
                optionLabel="nom"
                optionValue="id"
                placeholder="Sélectionner un type"
                :disabled="loading"
                class="w-full"
            />
        </div>

        <span class="p-float-label">
            <InputText
                id="economat_fr"
                v-model="form.economat_fr"
                class="w-full"
                :disabled="loading"
            />
            <label for="economat_fr">Économat (FR)</label>
        </span>

        <span class="p-float-label">
            <InputText
                id="economat_ar"
                v-model="form.economat_ar"
                class="w-full"
                :disabled="loading"
            />
            <label for="economat_ar">Économat (AR)</label>
        </span>

        <div class="field">
            <label for="statut_id">Statut</label>
            <Dropdown
                id="statut_id"
                v-model="form.statut_id"
                :options="statutOptions"
                optionLabel="nom"
                optionValue="id"
                placeholder="Sélectionner un statut"
                :disabled="loading"
                class="w-full"
            />
        </div>

        <div class="field">
            <label for="logo">Logo</label>
            <input
                type="file"
                id="logo"
                accept="image/*"
                @change="onLogoUpload"
                :disabled="loading"
                class="w-full"
            />
        </div>

        <span class="p-float-label">
            <Calendar
                id="date_creation"
                v-model="form.date_creation"
                dateFormat="dd/mm/yy"
                :disabled="loading"
                class="w-full"
            />
            <label for="date_creation">Date de création</label>
        </span>

        <span class="p-float-label">
            <Calendar
                id="date_fermeture"
                v-model="form.date_fermeture"
                dateFormat="dd/mm/yy"
                :disabled="loading"
                class="w-full"
            />
            <label for="date_fermeture">Date de fermeture</label>
        </span>

        <div class="field">
            <label for="observation">Observation</label>
            <Textarea
                id="observation"
                v-model="form.observation"
                :disabled="loading"
                rows="3"
                class="w-full"
            />
        </div>

        <Button
            label="Enregistrer"
            icon="pi pi-check"
            :loading="loading"
            @click="submitForm"
            class="w-full"
        />
    </div>
</template>
