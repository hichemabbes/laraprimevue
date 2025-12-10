<script setup>
import { ref, defineProps, defineEmits, onMounted } from 'vue';
import axios from '@/axios.js';
import { useToast } from 'primevue/usetoast';

defineProps({
    emploi: Object, // Emploi à modifier (optionnel)
});

const emit = defineEmits(['close']);
const toast = useToast();

const form = ref({
    formateur_id: '',
    jour: '',
    heure_debut: '',
    heure_fin: '',
    groupe_id: '',
    module_id: '',
});

const formateurs = ref([]);
const groupes = ref([]);
const modules = ref([]);
const jours = ref([
    { label: 'Lundi', value: 'Lundi' },
    { label: 'Mardi', value: 'Mardi' },
    { label: 'Mercredi', value: 'Mercredi' },
    { label: 'Jeudi', value: 'Jeudi' },
    { label: 'Vendredi', value: 'Vendredi' },
    { label: 'Samedi', value: 'Samedi' },
]);

const loadOptions = async () => {
    try {
        const [formateursRes, groupesRes, modulesRes] = await Promise.all([
            axios.get('/api/centres/personnels/formateurs'),
            axios.get('/api/centres/groupes'),
            axios.get('/api/atfp/modules'),
        ]);
        formateurs.value = formateursRes.data.data;
        groupes.value = groupesRes.data.data;
        modules.value = modulesRes.data.data;

        if (emploi) {
            form.value.formateur_id = emploi.formateur_id;
            form.value.jour = emploi.jour;
            form.value.heure_debut = emploi.heure_debut;
            form.value.heure_fin = emploi.heure_fin;
            form.value.groupe_id = emploi.groupe_id;
            form.value.module_id = emploi.module_id;
        }
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

const submitForm = async () => {
    try {
        if (emploi && emploi.id) {
            await axios.put(
                `/api/centres/formation/emplois-temps-formateurs/${emploi.id}`,
                form.value
            );
            toast.add({
                severity: 'success',
                summary: 'Succès',
                detail: 'Emploi mis à jour.',
                life: 3000,
            });
        } else {
            await axios.post(
                '/api/centres/formation/emplois-temps-formateurs',
                form.value
            );
            toast.add({
                severity: 'success',
                summary: 'Succès',
                detail: 'Emploi créé.',
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
    <div class="emploi-temps-formateur-form">
        <form @submit.prevent="submitForm">
            <div class="form-group">
                <label>Formateur</label>
                <Dropdown
                    v-model="form.formateur_id"
                    :options="formateurs"
                    optionLabel="nom"
                    optionValue="id"
                    placeholder="Sélectionner un formateur"
                    class="w-full"
                    required
                />
            </div>
            <div class="form-group">
                <label>Jour</label>
                <Dropdown
                    v-model="form.jour"
                    :options="jours"
                    optionLabel="label"
                    optionValue="value"
                    placeholder="Sélectionner un jour"
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
                <label>Groupe</label>
                <Dropdown
                    v-model="form.groupe_id"
                    :options="groupes"
                    optionLabel="nom"
                    optionValue="id"
                    placeholder="Sélectionner un groupe"
                    class="w-full"
                    required
                />
            </div>
            <div class="form-group">
                <label>Module</label>
                <Dropdown
                    v-model="form.module_id"
                    :options="modules"
                    optionLabel="nom"
                    optionValue="id"
                    placeholder="Sélectionner un module"
                    class="w-full"
                    required
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
.emploi-temps-formateur-form {
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
