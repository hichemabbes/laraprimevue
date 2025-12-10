<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Button from 'primevue/button';
import axios from '@/axios.js';

const router = useRouter();
const name = ref('');
const email = ref('');
const password = ref('');
const passwordConfirmation = ref('');
const error = ref('');

const submitRegister = async () => {
    if (password.value !== passwordConfirmation.value) {
        error.value = 'Les mots de passe ne correspondent pas';
        return;
    }
    try {
        const response = await axios.post('/api/register', {
            name: name.value,
            email: email.value,
            password: password.value,
            password_confirmation: passwordConfirmation.value,
        });
        localStorage.setItem('token', response.data.token);
        localStorage.setItem('user_id', response.data.user.id);
        router.push({ name: 'dashboard-atfp' });
    } catch (err) {
        error.value = "Erreur lors de l'inscription";
    }
};
</script>

<template>
    <div class="flex flex-column gap-3">
        <h2>Inscription</h2>
        <span class="p-float-label">
            <InputText id="name" v-model="name" class="w-full" />
            <label for="name">Nom</label>
        </span>
        <span class="p-float-label">
            <InputText id="email" v-model="email" class="w-full" />
            <label for="email">Email</label>
        </span>
        <span class="p-float-label">
            <Password
                id="password"
                v-model="password"
                :feedback="false"
                class="w-full"
            />
            <label for="password">Mot de passe</label>
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
        <Button label="S'inscrire" @click="submitRegister" class="w-full" />
        <p v-if="error" class="text-red-500">{{ error }}</p>
    </div>
</template>
