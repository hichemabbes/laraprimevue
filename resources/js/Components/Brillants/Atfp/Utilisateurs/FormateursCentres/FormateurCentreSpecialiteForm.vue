<script setup>
import { ref, defineProps, defineEmits, onMounted } from 'vue';
import axios from '@/axios.js';
import { useToast } from 'primevue/usetoast';

defineProps({
    formateur: Object,
});

const emit = defineEmits(['close']);
const toast = useToast();

const form = ref({
    formateur_id: '',
    specialite_id: '',
    module_id: null,
});
const formateurs = ref([]);
const specialites = ref([]);
const modules = ref([]);

const loadOptions = async () => {
    try {
        const [formateursRes, specialitesRes, modulesRes] = await Promise.all([
            axios.get('/api/centres/personnels/formateurs'),
            axios.get('/api/atfp/specialites'),
            axios.get('/api/atfp/modules'),
        ]);
        formateurs.value = formateursRes.data.data;
        specialites.value = specialitesRes.data.data;
        modules.value = modulesRes.data.data;

        if (formateur) {
            form.value.formateur_id = formateur.formateur_id;
            form.value.specialite_id = formateur.specialite_id;
            form.value.module_id = formateur.module_id || null;
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
        if (formateur && formateur.id) {
            await axios.put(
                `/api/centres/personnels/formateurs-specialites/${formateur.id}`,
                form.value
            );
        } else {
            await axios.post(
                '/api/centres/personnels/formateurs-specialites',
                form.value
            );
        }
        toast.add({
            severity: 'success',
            summary: 'Succès',
            detail: 'Spécialité enregistrée.',
            life: 3000,
        });
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
    <div class="formateur-specialite-form">
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
                />
            </div>
            <div class="form-group">
                <label>Spécialité</label>
                <Dropdown
                    v-model="form.specialite_id"
                    :options="specialites"
                    optionLabel="nom"
                    optionValue="id"
                    placeholder="Sélectionner une spécialité"
                    class="w-full"
                />
            </div>
            <div class="form-group">
                <label>Module (optionnel)</label>
                <Dropdown
                    v-model="form.module_id"
                    :options="modules"
                    optionLabel="nom"
                    optionValue="id"
                    placeholder="Sélectionner un module"
                    class="w-full"
                    :showClear="true"
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
.formateur-specialite-form {
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
