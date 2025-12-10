<script setup>
import { ref } from 'vue';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import axios from '@/axios.js';

const email = ref('');
const message = ref('');
const error = ref('');

const submitForgotPassword = async () => {
    try {
        const response = await axios.post('/api/forgot-password', {
            email: email.value,
        });
        message.value = response.data.message;
        error.value = '';
    } catch (err) {
        error.value = 'Erreur lors de la demande';
        message.value = '';
    }
};
</script>

<template>
    <div class="flex flex-column gap-3">
        <h2>Mot de passe oublié</h2>
        <span class="p-float-label">
            <InputText id="email" v-model="email" class="w-full" />
            <label for="email">Email</label>
        </span>
        <Button
            label="Envoyer le lien de réinitialisation"
            @click="submitForgotPassword"
            class="w-full"
        />
        <p v-if="message" class="text-green-500">{{ message }}</p>
        <p v-if="error" class="text-red-500">{{ error }}</p>
        <router-link to="/login" class="text-blue-500"
            >Retour à la connexion</router-link
        >
    </div>
</template>
