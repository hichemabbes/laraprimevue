<script setup>
import { ref } from 'vue';
import InputText from 'primevue/inputtext';
import Calendar from 'primevue/calendar';
import Dropdown from 'primevue/dropdown';
import Button from 'primevue/button';
import axios from '@/axios.js';

const emit = defineEmits(['submit']);

const form = ref({
    nom_fr: '',
    prenom_fr: '',
    email: '',
    matricule: '',
    cin: '',
    date_naissance: null,
    is_centre: false,
});

const submitForm = async () => {
    try {
        const response = await axios.post('/api/personnels-atfp', form.value);
        emit('submit', response.data);
        form.value = {
            nom_fr: '',
            prenom_fr: '',
            email: '',
            matricule: '',
            cin: '',
            date_naissance: null,
            is_centre: false,
        };
    } catch (err) {
        console.error("Erreur lors de l'ajout du personnel ATFP", err);
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
            <InputText id="matricule" v-model="form.matricule" class="w-full" />
            <label for="matricule">Matricule</label>
        </span>
        <span class="p-float-label">
            <InputText id="cin" v-model="form.cin" class="w-full" />
            <label for="cin">CIN</label>
        </span>
        <span class="p-float-label">
            <Calendar
                id="date_naissance"
                v-model="form.date_naissance"
                dateFormat="dd/mm/yy"
                class="w-full"
            />
            <label for="date_naissance">Date de naissance</label>
        </span>
        <Button label="Enregistrer" @click="submitForm" class="w-full" />
    </div>
</template>
