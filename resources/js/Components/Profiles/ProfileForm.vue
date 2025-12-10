<script setup>
import { ref } from 'vue';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import axios from '@/axios.js';

const emit = defineEmits(['submit']);

const form = ref({
    nom_fr: '',
    prenom_fr: '',
    email: '',
    type: '',
});

const submitForm = async () => {
    try {
        const response = await axios.post('/api/profiles', form.value);
        emit('submit', response.data);
        form.value = { nom_fr: '', prenom_fr: '', email: '', type: '' };
    } catch (err) {
        console.error("Erreur lors de l'ajout du profil", err);
    }
};
</script>

<template>
    <div class="flex flex-column gap-3">
        <span class="p-float-label">
            <InputText id="nom_fr" v-model="form.nom_fr" class="w-full" />
            <label for="nom_fr">Nom (FR)</label>
        </span>
        <span class="p-float-label">
            <InputText id="prenom_fr" v-model="form.prenom_fr" class="w-full" />
            <label for="prenom_fr">Prénom (FR)</label>
        </span>
        <span class="p-float-label">
            <InputText id="email" v-model="form.email" class="w-full" />
            <label for="email">Email</label>
        </span>
        <span class="p-float-label">
            <InputText id="type" v-model="form.type" class="w-full" />
            <label for="type">Type de Profil</label>
        </span>
        <Button label="Enregistrer" @click="submitForm" class="w-full" />
    </div>
</template>
