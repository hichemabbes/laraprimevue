<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Button from 'primevue/button';
import axios from '@/axios.js';

const router = useRouter();
const email = ref('');
const password = ref('');
const error = ref('');

const submitLogin = async () => {
    try {
        const response = await axios.post('/api/login', { // Corrigé : suppression de "/user"
            email: email.value,
            password: password.value,
        });

        // Stocker les informations dans localStorage
        localStorage.setItem('token', response.data.token);
        localStorage.setItem('user_id', response.data.user.id);
        localStorage.setItem('user_email', response.data.user.email);
        localStorage.setItem('user_nom_fr', response.data.user.nom_fr);
        localStorage.setItem('user_prenom_fr', response.data.user.prenom_fr);
        localStorage.setItem('is_centre_role', response.data.is_centre_role ? '1' : '0');
        localStorage.setItem('centre_id', response.data.centre_id || '');
        localStorage.setItem('roles', JSON.stringify(response.data.user.roles));

        // Rediriger vers le tableau de bord
        router.push({ name: 'dashboard-atfp' });
    } catch (err) {
        console.error('Erreur de connexion:', err.response?.data);
        error.value = err.response?.data?.message || 'Une erreur est survenue lors de la connexion';
        if (err.response?.status === 422 && err.response?.data?.message.includes('Centre ID manquant')) {
            error.value = 'Votre compte n\'est pas correctement configuré. Veuillez contacter l\'administrateur.';
        }
    }
};
</script>

<template>
    <div class="flex flex-column gap-3">
        <h2>Connexion</h2>
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
        <Button label="Se connecter" @click="submitLogin" class="w-full" />
        <p v-if="error" class="text-red-500">{{ error }}</p>
        <router-link to="/forgot-password" class="text-blue-500"
        >Mot de passe oublié ?</router-link>
    </div>
</template>
