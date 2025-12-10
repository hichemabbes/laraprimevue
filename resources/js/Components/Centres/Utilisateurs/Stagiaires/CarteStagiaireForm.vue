<script setup>
import { ref, defineEmits } from 'vue';
import axios from '@/axios.js';
import { useToast } from 'primevue/usetoast';

const emit = defineEmits(['close']);
const toast = useToast();

// Données du formulaire
const form = ref({
    stagiaire_id: '',
    groupe_id: '',
    numero_carte: '',
    photo: null,
});

// Listes déroulantes (exemple)
const stagiaires = ref([]);
const groupes = ref([]);

// Charger les données pour les listes déroulantes
const loadOptions = async () => {
    try {
        const [stagiairesRes, groupesRes] = await Promise.all([
            axios.get('/api/centres/stagiaires'),
            axios.get('/api/centres/groupes'),
        ]);
        stagiaires.value = stagiairesRes.data.data;
        groupes.value = groupesRes.data.data;
    } catch (error) {
        console.error('Erreur lors du chargement des options:', error);
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: 'Impossible de charger les options.',
            life: 3000,
        });
    }
};

// Soumettre le formulaire
const submitForm = async () => {
    try {
        const formData = new FormData();
        formData.append('stagiaire_id', form.value.stagiaire_id);
        formData.append('groupe_id', form.value.groupe_id);
        formData.append('numero_carte', form.value.numero_carte);
        if (form.value.photo) formData.append('photo', form.value.photo);

        await axios.post('/api/centres/stagiaires/cartes', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        toast.add({
            severity: 'success',
            summary: 'Succès',
            detail: 'Carte créée avec succès.',
            life: 3000,
        });
        emit('close');
    } catch (error) {
        console.error('Erreur lors de la création de la carte:', error);
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: 'Échec de la création de la carte.',
            life: 3000,
        });
    }
};

// Gestion du fichier photo
const onFileChange = (event) => {
    form.value.photo = event.target.files[0];
};

loadOptions();
</script>

<template>
    <div class="carte-stagiaire-form">
        <form @submit.prevent="submitForm">
            <div class="form-group">
                <label>Stagiaire</label>
                <Dropdown
                    v-model="form.stagiaire_id"
                    :options="stagiaires"
                    optionLabel="nom"
                    optionValue="id"
                    placeholder="Sélectionner un stagiaire"
                    class="w-full"
                />
            </div>
            <div class="form-group">
                <label>Groupe</label>
                <Dropdown
                    v-model="form.groupe_id"
                    :options="groupes"
                    optionLabel="nom"
                    optionValue="id"
                    placeholder="Sélectionner un groupe"
                    class="w-full"
                />
            </div>
            <div class="form-group">
                <label>Numéro de Carte</label>
                <InputText
                    v-model="form.numero_carte"
                    placeholder="Entrez le numéro de carte"
                    class="w-full"
                />
            </div>
            <div class="form-group">
                <label>Photo (optionnel)</label>
                <input type="file" @change="onFileChange" accept="image/*" />
            </div>
            <div class="form-actions">
                <Button type="submit" label="Enregistrer" icon="pi pi-save" />
                <Button
                    label="Annuler"
                    icon="pi pi-times"
                    class="p-button-secondary"
                    @click="emit('close')"
                />
            </div>
        </form>
    </div>
</template>

<style scoped>
.carte-stagiaire-form {
    padding: 1rem;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
}
</style>
