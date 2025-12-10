<template>
    <div class="flex flex-column align-items-center justify-content-center min-h-screen">
        <div class="flex align-items-top justify-content-between px-1 pt-1.5 flex-shrink-0">
            <span class="inline-flex align-items-center gap-2">
                <svg width="35" height="40" viewBox="0 0 35 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M25.87 18.05L23.16 17.45L25.27 20.46V29.78L32.49 23.76V13.53L29.18 14.73L25.87 18.04V18.05ZM25.27 35.49L29.18 31.58V27.67L25.27 30.98V35.49ZM20.16 17.14H20.03H20.17H20.16ZM30.1 5.19L34.89 4.81L33.08 12.33L24.1 15.67L30.08 5.2L30.1 5.19ZM5.72 14.74L2.41 13.54V23.77L9.63 29.79V20.47L11.74 17.46L9.03 18.06L5.72 14.75V14.74ZM9.63 30.98L5.72 27.67V31.58L9.63 35.49V30.98ZM4.8 5.2L10.78 15.67L1.81 12.33L0 4.81L4.79 5.19L4.8 5.2ZM24.37 21.05V34.59L22.56 37.29L20.46 39.4H14.44L12.34 37.29L10.53 34.59V21.05L12.42 18.23L17.45 26.8L22.48 18.23L24.37 21.05ZM22.85 0L22.57 0.69L17.45 13.08L12.33 0.69L12.05 0H22.85Z"
                        fill="var(--primary-color)" />
                    <path
                        d="M30.69 4.21L24.37 4.81L22.57 0.69L22.86 0H26.48L30.69 4.21ZM23.75 5.67L22.66 3.08L18.05 14.24V17.14H19.7H20.03H20.16H20.2L24.1 15.7L30.11 5.19L23.75 5.67ZM4.21002 4.21L10.53 4.81L12.33 0.69L12.05 0H8.43002L4.22002 4.21H4.21002ZM21.9 17.4L20.6 18.2H14.3L13 17.4L12.4 18.2L12.42 18.23L17.45 26.8L22.48 18.23L22.5 18.2L21.9 17.4ZM4.79002 5.19L10.8 15.7L14.7 17.14H14.74H15.2H16.85V14.24L12.24 3.09L11.15 5.68L4.79002 5.2V5.19Z"
                        fill="var(--text-color)" />
                </svg>
                <span class="font-semibold text-2xl text-primary">Iregister</span>
            </span>
        </div>
        <div style="border-radius: 56px; padding: 0.3rem; background: linear-gradient(180deg, var(--primary-color) 10%, rgba(33, 150, 243, 0) 30%);">
            <div class="w-full surface-card py-8 px-5 sm:px-8" style="border-radius: 53px">
                <form @submit.prevent="register">
                    <div class="text-center mb-5">
                        <div class="text-900 text-3xl font-medium mb-3">Créer un compte</div>
                        <span class="text-600 font-medium">Premier utilisateur : SuperAdmin</span>
                    </div>

                    <div>
                        <label htmlFor="nom_fr" class="block text-900 text-xl font-medium mb-2">Nom</label>
                        <InputText id="nom_fr" type="text" placeholder="Votre nom" class="w-full md:w-30rem mb-5" :style="{ padding: '1rem' }" v-model="formData.nom_fr" :class="{ 'p-invalid': errors.nom_fr }" />
                        <small v-if="errors.nom_fr" class="text-red-500">{{ errors.nom_fr }}</small>

                        <label htmlFor="prenom_fr" class="block text-900 text-xl font-medium mb-2">Prénom</label>
                        <InputText id="prenom_fr" type="text" placeholder="Votre prénom" class="w-full md:w-30rem mb-5" :style="{ padding: '1rem' }" v-model="formData.prenom_fr" :class="{ 'p-invalid': errors.prenom_fr }" />
                        <small v-if="errors.prenom_fr" class="text-red-500">{{ errors.prenom_fr }}</small>

                        <label htmlFor="email" class="block text-900 text-xl font-medium mb-2">Email</label>
                        <InputText id="email" type="email" placeholder="Adresse email" class="w-full md:w-30rem mb-5" :style="{ padding: '1rem' }" v-model="formData.email" :class="{ 'p-invalid': errors.email }" />
                        <small v-if="errors.email" class="text-red-500">{{ errors.email }}</small>

                        <label htmlFor="password" class="block text-900 font-medium text-xl mb-2">Mot de passe</label>
                        <Password id="password" v-model="formData.password" placeholder="Mot de passe" :toggleMask="true" class="w-full md:w-30rem mb-5" :inputStyle="{ padding: '1rem' }" inputClass="w-full" :class="{ 'p-invalid': errors.password }" />
                        <small v-if="errors.password" class="text-red-500">{{ errors.password }}</small>

                        <label htmlFor="password_confirmation" class="block text-900 font-medium text-xl mb-2">Confirmer le mot de passe</label>
                        <Password id="password_confirmation" v-model="formData.password_confirmation" placeholder="Confirmer le mot de passe" :toggleMask="true" class="w-full md:w-30rem mb-5" :inputStyle="{ padding: '1rem' }" inputClass="w-full" :class="{ 'p-invalid': errors.password_confirmation }" />
                        <small v-if="errors.password_confirmation" class="text-red-500">{{ errors.password_confirmation }}</small>

                        <Button label="S'inscrire" class="w-full p-3 text-xl" type="submit" :loading="isLoading" />
                    </div>
                </form>

                <div class="text-center mt-5">
                    <span class="text-600 font-medium">Vous avez déjà un compte ? </span>
                    <router-link to="/login" class="font-medium no-underline text-primary">Connectez-vous</router-link>
                </div>
            </div>
        </div>
    </div>

    <Dialog v-model:visible="showSuccessDialog" header="Inscription réussie" :style="{ width: '25rem' }" :modal="true">
        <div class="flex flex-column gap-3">
            <p>Votre compte SuperAdmin a été créé avec succès !</p>
            <p>Vous allez être redirigé vers le tableau de bord.</p>
        </div>
        <template #footer>
            <Button label="Continuer" @click="goToDashboard" class="p-button-success" />
        </template>
    </Dialog>

    <Toast />
</template>

<script>
import { useToast } from 'primevue/usetoast';
import axios from '@/axios.js';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Button from 'primevue/button';
import Toast from 'primevue/toast';

export default {
    components: {
        Dialog,
        InputText,
        Password,
        Button,
        Toast,
    },
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            formData: {
                nom_fr: '',
                prenom_fr: '',
                email: '',
                password: '',
                password_confirmation: '',
            },
            errors: {},
            isLoading: false,
            showSuccessDialog: false,
        };
    },
    methods: {
        async register() {
            this.errors = {};
            this.isLoading = true;

            if (this.formData.password !== this.formData.password_confirmation) {
                this.errors.password_confirmation = 'Les mots de passe ne correspondent pas';
                this.isLoading = false;
                return;
            }

            try {
                const response = await axios.post('api/store-super-admin', this.formData);

                if (response.data.status === 'success') {
                    localStorage.setItem('user_email', response.data.user.email);
                    localStorage.setItem('user_id', response.data.user.id);
                    localStorage.setItem('user_nom_fr', response.data.user.nom_fr);
                    localStorage.setItem('user_prenom_fr', response.data.user.prenom_fr);
                    localStorage.setItem('myRole', response.data.user.roles[0]?.name || 'SuperAdmin');
                    localStorage.setItem('token', response.data.token);

                    this.showSuccessDialog = true;

                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Inscription SuperAdmin réussie !',
                        life: 3000,
                    });
                }
            } catch (error) {
                if (error.response?.status === 422) {
                    this.errors = error.response.data.errors || {};
                } else if (error.response?.status === 403) {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: "L'application est déjà initialisée. Contactez un administrateur.",
                        life: 3000,
                    });
                } else {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: error.response?.data?.message || "Échec de l'inscription",
                        life: 3000,
                    });
                }
            } finally {
                this.isLoading = false;
            }
        },
        goToDashboard() {
            this.showSuccessDialog = false;
            this.$router.push({ name: 'dashboard' });
        },
    },
};
</script>
