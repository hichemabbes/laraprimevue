<script setup>
import { ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Button from 'primevue/button';
import axios from '@/axios.js';

const router = useRouter();
const route = useRoute();
const email = ref(route.query.email || '');
const token = ref(route.query.token || '');
const password = ref('');
const passwordConfirmation = ref('');
const message = ref('');
const error = ref('');

const submitResetPassword = async () => {
    if (password.value !== passwordConfirmation.value) {
        error.value = 'Les mots de passe ne correspondent pas';
        return;
    }
    try {
        const response = await axios.post('/api/reset-password', {
            email: email.value,
            token: token.value,
            password: password.value,
            password_confirmation: passwordConfirmation.value,
        });
        message.value = response.data.message;
        error.value = '';
        setTimeout(() => router.push({ name: 'login' }), 2000);
    } catch (err) {
        error.value = 'Erreur lors de la réinitialisation';
    }
};
</script>

<template>
    <div class="flex flex-column gap-3">
        <h2>Réinitialiser le mot de passe</h2>
        <span class="p-float-label">
            <InputText id="email" v-model="email" class="w-full" disabled />
            <label for="email">Email</label>
        </span>
        <span class="p-float-label">
            <Password
                id="password"
                v-model="password"
                :feedback="false"
                class="w-full"
            />
            <label for="password">Nouveau mot de passe</label>
        </span>
        <span class="p-float-label">
            <Password
                id="password_confirmation"
                v-model="passwordConfirmation"
                :feedback="false"
                class="w-full"
            />
            <label for="password_confirmation">Confirmer le mot de passe</label>
        </span>
        <Button
            label="Réinitialiser"
            @click="submitResetPassword"
            class="w-full"
        />
        <p v-if="message" class="text-green-500">{{ message }}</p>
        <p v-if="error" class="text-red-500">{{ error }}</p>
    </div>
</template>
