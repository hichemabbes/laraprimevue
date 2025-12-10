<template>
    <div
        class="flex flex-column align-items-center justify-content-center min-h-screen"
    >
        <div
            class="w-full surface-card py-8 px-5 sm:px-8"
            style="border-radius: 53px; max-width: 400px"
        >
            <div class="text-center mb-5">
                <div class="text-900 text-3xl font-medium mb-3">
                    Mot de passe oublié
                </div>
                <span class="text-600 font-medium"
                    >Entrez votre email pour réinitialiser</span
                >
            </div>

            <div>
                <label
                    for="email"
                    class="block text-900 text-xl font-medium mb-2"
                    >Email</label
                >
                <InputText
                    id="email"
                    type="email"
                    placeholder="Adresse email"
                    class="w-full mb-5"
                    style="padding: 1rem"
                    v-model="formData.email"
                    :class="{ 'p-invalid': errors.email }"
                />
                <small v-if="errors.email" class="text-red-500">{{
                    errors.email
                }}</small>

                <Button
                    label="Envoyer le lien"
                    class="w-full p-3 text-xl"
                    @click="sendResetLink"
                    :loading="isLoading"
                />
            </div>

            <div class="text-center mt-5">
                <router-link
                    to="/login"
                    class="font-medium no-underline text-primary"
                    >Retour à la connexion</router-link
                >
            </div>
        </div>
    </div>
</template>

<script>
import { useToast } from 'primevue/usetoast';
import axios from '@/axios.js';

export default {
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            formData: { email: '' },
            errors: {},
            isLoading: false,
        };
    },
    methods: {
        async sendResetLink() {
            this.errors = {};
            this.isLoading = true;

            try {
                const response = await axios.post(
                    'api/forgot-password',
                    this.formData
                );
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Lien de réinitialisation envoyé',
                    life: 3000,
                });
                this.$router.push({ name: 'login' });
            } catch (error) {
                if (error.response?.status === 422) {
                    this.errors = error.response.data.errors;
                } else {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail:
                            error.response?.data?.message || "Échec de l'envoi",
                        life: 3000,
                    });
                }
            } finally {
                this.isLoading = false;
            }
        },
    },
};
</script>
