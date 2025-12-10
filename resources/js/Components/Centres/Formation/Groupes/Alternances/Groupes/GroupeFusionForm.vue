<script setup>
import { ref, defineEmits, onMounted } from 'vue';
import axios from '@/axios.js';
import { useToast } from 'primevue/usetoast';

const emit = defineEmits(['close']);
const toast = useToast();

// Données du formulaire
const form = ref({
    groupe1_id: '',
    groupe2_id: '',
    nouveau_nom: '',
});

const groupes = ref([]);

// Charger les groupes disponibles
const loadGroupes = async () => {
    try {
        const response = await axios.get('/api/centres/groupes');
        groupes.value = response.data.data;
    } catch (error) {
        console.error('Erreur lors du chargement des groupes:', error);
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: 'Impossible de charger les groupes.',
            life: 3000,
        });
    }
};

// Soumettre le formulaire
const submitForm = async () => {
    if (form.value.groupe1_id === form.value.groupe2_id) {
        toast.add({
            severity: 'warn',
            summary: 'Attention',
            detail: 'Veuillez sélectionner deux groupes différents.',
            life: 3000,
        });
        return;
    }
    try {
        await axios.post('/api/centres/groupes/fusion', form.value);
        toast.add({
            severity: 'success',
            summary: 'Succès',
            detail: 'Groupes fusionnés avec succès.',
            life: 3000,
        });
        emit('close');
    } catch (error) {
        console.error('Erreur lors de la fusion des groupes:', error);
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: 'Échec de la fusion des groupes.',
            life: 3000,
        });
    }
};

// Initialisation
onMounted(() => {
    loadGroupes();
});
</script>

<template>
    <div class="groupe-fusion-form">
        <form @submit.prevent="submitForm">
            <div class="form-group">
                <label>Groupe 1</label>
                <Dropdown
                    v-model="form.groupe1_id"
                    :options="groupes"
                    optionLabel="nom"
                    optionValue="id"
                    placeholder="Sélectionner le premier groupe"
                    class="w-full"
                />
            </div>
            <div class="form-group">
                <label>Groupe 2</label>
                <Dropdown
                    v-model="form.groupe2_id"
                    :options="groupes"
                    optionLabel="nom"
                    optionValue="id"
                    placeholder="Sélectionner le deuxième groupe"
                    class="w-full"
                />
            </div>
            <div class="form-group">
                <label>Nouveau Nom du Groupe</label>
                <InputText
                    v-model="form.nouveau_nom"
                    placeholder="Nom du groupe fusionné"
                    class="w-full"
                />
            </div>
            <div class="form-actions">
                <Button type="submit" label="Fusionner" icon="pi pi-sync" />
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
.groupe-fusion-form {
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
