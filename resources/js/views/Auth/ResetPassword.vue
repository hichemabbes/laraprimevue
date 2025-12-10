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
                    Réinitialiser le mot de passe
                </div>
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

                <label
                    for="password"
                    class="block text-900 font-medium text-xl mb-2"
                    >Nouveau mot de passe</label
                >
                <Password
                    id="password"
                    v-model="formData.password"
                    placeholder="Mot de passe"
                    :toggleMask="true"
                    class="w-full mb-5"
                    inputClass="w-full"
                    inputStyle="padding:1rem"
                    :class="{ 'p-invalid': errors.password }"
                />
                <small v-if="errors.password" class="text-red-500">{{
                    errors.password
                }}</small>

                <label
                    for="password_confirmation"
                    class="block text-900 font-medium text-xl mb-2"
                    >Confirmer</label
                >
                <Password
                    id="password_confirmation"
                    v-model="formData.password_confirmation"
                    placeholder="Confirmer"
                    :toggleMask="true"
                    class="w-full mb-5"
                    inputClass="w-full"
                    inputStyle="padding:1rem"
                    :class="{ 'p-invalid': errors.password_confirmation }"
                />
                <small
                    v-if="errors.password_confirmation"
                    class="text-red-500"
                    >{{ errors.password_confirmation }}</small
                >

                <Button
                    label="Réinitialiser"
                    class="w-full p-3 text-xl"
                    @click="resetPassword"
                    :loading="isLoading"
                />
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
            formData: {
                email: this.$route.query.email || '',
                token: this.$route.query.token || '',
                password: '',
                password_confirmation: '',
            },
            errors: {},
            isLoading: false,
        };
    },
    methods: {
        async resetPassword() {
            this.errors = {};
            this.isLoading = true;

            try {
                const response = await axios.post(
                    'api/reset-password',
                    this.formData
                );
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Mot de passe réinitialisé',
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
                            error.response?.data?.message ||
                            'Échec de la réinitialisation',
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
