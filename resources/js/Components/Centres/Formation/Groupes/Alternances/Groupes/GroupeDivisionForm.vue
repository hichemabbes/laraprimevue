<script setup>
import { ref, defineProps, defineEmits } from 'vue';
import axios from '@/axios.js';
import { useToast } from 'primevue/usetoast';

defineProps({
    groupe: Object,
});

const emit = defineEmits(['close']);
const toast = useToast();

// Données du formulaire
const form = ref({
    groupe_id: '',
    nom_groupe1: '',
    nom_groupe2: '',
    stagiaires_groupe1: [],
    stagiaires_groupe2: [],
});

// Liste des stagiaires du groupe
const stagiaires = ref([]);

// Charger les stagiaires du groupe
const loadStagiaires = async () => {
    if (groupe) {
        try {
            const response = await axios.get(
                `/api/centres/groupes/${groupe.id}/stagiaires`
            );
            stagiaires.value = response.data.data;
            form.value.groupe_id = groupe.id;
            form.value.nom_groupe1 = `${groupe.nom}-A`;
            form.value.nom_groupe2 = `${groupe.nom}-B`;
        } catch (error) {
            console.error('Erreur lors du chargement des stagiaires:', error);
            toast.add({
                severity: 'error',
                summary: 'Erreur',
                detail: 'Impossible de charger les stagiaires.',
                life: 3000,
            });
        }
    }
};

// Gestion des stagiaires dans les groupes
const moveToGroupe1 = (stagiaire) => {
    if (!form.value.stagiaires_groupe1.includes(stagiaire.id)) {
        form.value.stagiaires_groupe1.push(stagiaire.id);
        form.value.stagiaires_groupe2 = form.value.stagiaires_groupe2.filter(
            (id) => id !== stagiaire.id
        );
    }
};

const moveToGroupe2 = (stagiaire) => {
    if (!form.value.stagiaires_groupe2.includes(stagiaire.id)) {
        form.value.stagiaires_groupe2.push(stagiaire.id);
        form.value.stagiaires_groupe1 = form.value.stagiaires_groupe1.filter(
            (id) => id !== stagiaire.id
        );
    }
};

// Soumettre le formulaire
const submitForm = async () => {
    try {
        await axios.post('/api/centres/groupes/division', form.value);
        toast.add({
            severity: 'success',
            summary: 'Succès',
            detail: 'Groupe divisé avec succès.',
            life: 3000,
        });
        emit('close');
    } catch (error) {
        console.error('Erreur lors de la division du groupe:', error);
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: 'Échec de la division du groupe.',
            life: 3000,
        });
    }
};

// Initialisation
loadStagiaires();
</script>

<template>
    <div class="groupe-division-form">
        <form @submit.prevent="submitForm">
            <div class="form-group">
                <label>Nom du Groupe 1</label>
                <InputText
                    v-model="form.nom_groupe1"
                    placeholder="Nom du premier groupe"
                    class="w-full"
                />
            </div>
            <div class="form-group">
                <label>Nom du Groupe 2</label>
                <InputText
                    v-model="form.nom_groupe2"
                    placeholder="Nom du deuxième groupe"
                    class="w-full"
                />
            </div>
            <div class="form-group">
                <label>Stagiaires</label>
                <div class="stagiaires-split">
                    <div class="stagiaires-list">
                        <h4>Tous les stagiaires</h4>
                        <ul>
                            <li
                                v-for="stagiaire in stagiaires"
                                :key="stagiaire.id"
                            >
                                {{ stagiaire.nom }}
                                <Button
                                    icon="pi pi-arrow-right"
                                    class="p-button-text"
                                    @click="moveToGroupe1(stagiaire)"
                                />
                                <Button
                                    icon="pi pi-arrow-left"
                                    class="p-button-text"
                                    @click="moveToGroupe2(stagiaire)"
                                />
                            </li>
                        </ul>
                    </div>
                    <div class="groupe-list">
                        <h4>Groupe 1</h4>
                        <ul>
                            <li
                                v-for="stagiaire in stagiaires.filter((s) =>
                                    form.stagiaires_groupe1.includes(s.id)
                                )"
                                :key="stagiaire.id"
                            >
                                {{ stagiaire.nom }}
                            </li>
                        </ul>
                    </div>
                    <div class="groupe-list">
                        <h4>Groupe 2</h4>
                        <ul>
                            <li
                                v-for="stagiaire in stagiaires.filter((s) =>
                                    form.stagiaires_groupe2.includes(s.id)
                                )"
                                :key="stagiaire.id"
                            >
                                {{ stagiaire.nom }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="form-actions">
                <Button type="submit" label="Diviser" icon="pi pi-split" />
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
.groupe-division-form {
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

.stagiaires-split {
    display: flex;
    gap: 1rem;
}

.stagiaires-list,
.groupe-list {
    flex: 1;
}

.stagiaires-list ul,
.groupe-list ul {
    list-style: none;
    padding: 0;
}

.stagiaires-list li {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem;
    border-bottom: 1px solid var(--surface-border);
}

.groupe-list li {
    padding: 0.5rem;
    border-bottom: 1px solid var(--surface-border);
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
}
</style>
