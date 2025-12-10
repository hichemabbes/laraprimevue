<script setup>
import { ref, defineProps, defineEmits, onMounted } from 'vue';
import axios from '@/axios.js';
import { useToast } from 'primevue/usetoast';

defineProps({
    seance: Object, // Séance à modifier (optionnel)
});

const emit = defineEmits(['close']);
const toast = useToast();

const form = ref({
    emploi_temps_id: '',
    date: '',
    heure_debut: '',
    heure_fin: '',
    salle: '',
    commentaire: '',
});

const emplois = ref([]);

const loadOptions = async () => {
    try {
        const response = await axios.get(
            '/api/centres/formation/emplois-temps-formateurs'
        );
        emplois.value = response.data.data;

        if (seance) {
            form.value.emploi_temps_id = seance.emploi_temps_id;
            form.value.date = seance.date;
            form.value.heure_debut = seance.heure_debut;
            form.value.heure_fin = seance.heure_fin;
            form.value.salle = seance.salle || '';
            form.value.commentaire = seance.commentaire || '';
        }
    } catch (error) {
        console.error('Erreur lors du chargement des emplois:', error);
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: 'Impossible de charger les emplois.',
            life: 3000,
        });
    }
};

const submitForm = async () => {
    try {
        if (seance && seance.id) {
            await axios.put(
                `/api/centres/formation/seances/${seance.id}`,
                form.value
            );
            toast.add({
                severity: 'success',
                summary: 'Succès',
                detail: 'Séance mise à jour.',
                life: 3000,
            });
        } else {
            await axios.post('/api/centres/formation/seances', form.value);
            toast.add({
                severity: 'success',
                summary: 'Succès',
                detail: 'Séance créée.',
                life: 3000,
            });
        }
        emit('close');
    } catch (error) {
        console.error("Erreur lors de l'enregistrement:", error);
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: "Échec de l'enregistrement.",
            life: 3000,
        });
    }
};

onMounted(() => {
    loadOptions();
});
</script>

<template>
    <div class="seance-formation-form">
        <form @submit.prevent="submitForm">
            <div class="form-group">
                <label>Emploi du temps</label>
                <Dropdown
                    v-model="form.emploi_temps_id"
                    :options="emplois"
                    optionLabel="formateur_nom"
                    optionValue="id"
                    placeholder="Sélectionner un emploi"
                    class="w-full"
                    required
                />
            </div>
            <div class="form-group">
                <label>Date</label>
                <InputText
                    v-model="form.date"
                    type="date"
                    class="w-full"
                    required
                />
            </div>
            <div class="form-group">
                <label>Heure de début</label>
                <InputText
                    v-model="form.heure_debut"
                    type="time"
                    class="w-full"
                    required
                />
            </div>
            <div class="form-group">
                <label>Heure de fin</label>
                <InputText
                    v-model="form.heure_fin"
                    type="time"
                    class="w-full"
                    required
                />
            </div>
            <div class="form-group">
                <label>Salle (optionnel)</label>
                <InputText
                    v-model="form.salle"
                    placeholder="Salle"
                    class="w-full"
                />
            </div>
            <div class="form-group">
                <label>Commentaire (optionnel)</label>
                <Textarea
                    v-model="form.commentaire"
                    placeholder="Commentaire"
                    rows="3"
                    class="w-full"
                />
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
.seance-formation-form {
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
