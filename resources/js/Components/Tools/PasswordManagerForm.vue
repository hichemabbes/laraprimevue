<template>
    <div
        class="flex flex-column align-items-center justify-content-center min-h-screen"
    >
        <div
            class="flex align-items-top justify-content-between px-1 pt-1.5 flex-shrink-0"
        >
            <span class="inline-flex align-items-center gap-2">
                <svg
                    width="35"
                    height="40"
                    viewBox="0 0 35 40"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path
                        d="M25.87 18.05L23.16 17.45L25.27 20.46V29.78L32.49 23.76V13.53L29.18 14.73L25.87 18.04V18.05ZM25.27 35.49L29.18 31.58V27.67L25.27 30.98V35.49ZM20.16 17.14H20.03H20.17H20.16ZM30.1 5.19L34.89 4.81L33.08 12.33L24.1 15.67L30.08 5.2L30.1 5.19ZM5.72 14.74L2.41 13.54V23.77L9.63 29.79V20.47L11.74 17.46L9.03 18.06L5.72 14.75V14.74ZM9.63 30.98L5.72 27.67V31.58L9.63 35.49V30.98ZM4.8 5.2L10.78 15.67L1.81 12.33L0 4.81L4.79 5.19L4.8 5.2ZM24.37 21.05V34.59L22.56 37.29L20.46 39.4H14.44L12.34 37.29L10.53 34.59V21.05L12.42 18.23L17.45 26.8L22.48 18.23L24.37 21.05ZM22.85 0L22.57 0.69L17.45 13.08L12.33 0.69L12.05 0H22.85Z"
                        fill="var(--primary-color)"
                    />
                    <path
                        d="M30.69 4.21L24.37 4.81L22.57 0.69L22.86 0H26.48L30.69 4.21ZM23.75 5.67L22.66 3.08L18.05 14.24V17.14H19.7H20.03H20.16H20.2L24.1 15.7L30.11 5.19L23.75 5.67ZM4.21002 4.21L10.53 4.81L12.33 0.69L12.05 0H8.43002L4.22002 4.21H4.21002ZM21.9 17.4L20.6 18.2H14.3L13 17.4L12.4 18.2L12.42 18.23L17.45 26.8L22.48 18.23L22.5 18.2L21.9 17.4ZM4.79002 5.19L10.8 15.7L14.7 17.14H14.74H15.2H16.85V14.24L12.24 3.09L11.15 5.68L4.79002 5.2V5.19Z"
                        fill="var(--text-color)"
                    />
                </svg>
                <span class="font-semibold text-2xl text-primary"
                    >Iregister</span
                >
            </span>
        </div>
        <div
            style="
                border-radius: 56px;
                padding: 0.3rem;
                background: linear-gradient(
                    180deg,
                    var(--primary-color) 10%,
                    rgba(33, 150, 243, 0) 30%
                );
            "
        >
            <div
                class="w-full surface-card py-8 px-5 sm:px-8"
                style="border-radius: 53px"
            >
                <div class="text-center mb-5">
                    <div class="text-900 text-3xl font-medium mb-3">
                        Gestion du mot de passe de suppression
                    </div>
                    <span class="text-600 font-medium"
                        >Configurez le mot de passe requis pour les
                        suppressions</span
                    >
                </div>

                <div v-if="!isSuperAdmin" class="text-center p-4">
                    <i
                        class="pi pi-exclamation-triangle text-4xl text-red-500 mb-3"
                    ></i>
                    <p class="text-600 mb-4">
                        Accès non autorisé. Seuls les SuperAdmin peuvent accéder
                        à cette page.
                    </p>
                    <Button
                        label="Retour"
                        icon="pi pi-arrow-left"
                        class="p-button-text"
                        @click="$router.push({ name: 'dashboard' })"
                    />
                </div>

                <div v-else class="flex flex-column gap-4">
                    <div class="field">
                        <label
                            for="currentPassword"
                            class="block text-900 text-xl font-medium mb-2"
                        >
                            Mot de passe actuel
                            <span
                                v-if="!hasPassword"
                                class="text-500 font-normal"
                                >(aucun mot de passe défini actuellement)</span
                            >
                        </label>
                        <Password
                            id="currentPassword"
                            v-model="currentPassword"
                            placeholder="Entrez le mot de passe actuel"
                            :toggleMask="true"
                            class="w-full mb-3"
                            :inputStyle="{ padding: '1rem' }"
                            inputClass="w-full"
                            :class="{ 'p-invalid': errors.current }"
                            :disabled="!hasPassword"
                            :feedback="false"
                        />
                        <small v-if="errors.current" class="text-red-500">{{
                            errors.current
                        }}</small>
                    </div>

                    <div v-if="hasPassword" class="field">
                        <label
                            for="deletePassword"
                            class="block text-900 text-xl font-medium mb-2"
                            >Supprimer le mot de passe</label
                        >
                        <Button
                            label="Supprimer le mot de passe"
                            icon="pi pi-trash"
                            class="p-button-danger"
                            :disabled="loading"
                            :loading="loading"
                            @click="deletePassword"
                        />
                    </div>

                    <div class="field">
                        <label
                            for="newPassword"
                            class="block text-900 text-xl font-medium mb-2"
                            >Nouveau mot de passe</label
                        >
                        <Password
                            id="newPassword"
                            v-model="newPassword"
                            placeholder="Entrez le nouveau mot de passe"
                            :toggleMask="true"
                            class="w-full mb-3"
                            :inputStyle="{ padding: '1rem' }"
                            inputClass="w-full"
                            :class="{ 'p-invalid': errors.new }"
                            :feedback="true"
                            @input="validatePassword"
                        />
                        <small v-if="errors.new" class="text-red-500">{{
                            errors.new
                        }}</small>
                        <div v-if="passwordStrength" class="mt-3">
                            <div class="flex align-items-center gap-2 mb-2">
                                <span class="text-sm font-medium"
                                    >Complexité :</span
                                >
                                <ProgressBar
                                    :value="passwordStrength.score * 25"
                                    :showValue="false"
                                    :class="passwordStrength.class"
                                    style="height: 6px; flex-grow: 1"
                                />
                                <span
                                    class="text-sm font-medium"
                                    :class="passwordStrength.textClass"
                                >
                                    {{ passwordStrength.label }}
                                </span>
                            </div>
                            <ul class="text-sm mt-2 ml-2 text-600">
                                <li
                                    v-for="(
                                        warning, index
                                    ) in passwordStrength.warnings"
                                    :key="'warn-' + index"
                                    class="flex align-items-center mb-1"
                                >
                                    <i
                                        class="pi pi-exclamation-circle text-yellow-500 mr-2"
                                    ></i
                                    >{{ warning }}
                                </li>
                                <li
                                    v-for="(
                                        suggestion, index
                                    ) in passwordStrength.suggestions"
                                    :key="'sugg-' + index"
                                    class="flex align-items-center mb-1"
                                >
                                    <i
                                        class="pi pi-check-circle text-green-500 mr-2"
                                    ></i
                                    >{{ suggestion }}
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="field">
                        <label
                            for="confirmNewPassword"
                            class="block text-900 text-xl font-medium mb-2"
                            >Confirmer le mot de passe</label
                        >
                        <Password
                            id="confirmNewPassword"
                            v-model="confirmNewPassword"
                            placeholder="Confirmez le nouveau mot de passe"
                            :toggleMask="true"
                            class="w-full mb-3"
                            :inputStyle="{ padding: '1rem' }"
                            inputClass="w-full"
                            :class="{ 'p-invalid': errors.confirm }"
                            :feedback="false"
                        />
                        <small v-if="errors.confirm" class="text-red-500">{{
                            errors.confirm
                        }}</small>
                    </div>

                    <div class="flex justify-content-end gap-3 mt-4">
                        <Button
                            label="Annuler"
                            icon="pi pi-times"
                            class="p-button-text"
                            @click="$router.push({ name: 'dashboard' })"
                        />
                        <Button
                            label="Mettre à jour"
                            icon="pi pi-check"
                            class="p-button-primary"
                            :disabled="loading || !isFormValid"
                            :loading="loading"
                            @click="updatePassword"
                        />
                    </div>
                </div>
            </div>
        </div>
        <Toast position="top-right" />
    </div>
</template>

<script>
import axios from '@/axios';
import { useToast } from 'primevue/usetoast';
import Button from 'primevue/button';
import Password from 'primevue/password';
import Toast from 'primevue/toast';
import ProgressBar from 'primevue/progressbar';
import zxcvbn from 'zxcvbn';

export default {
    components: {
        Button,
        Password,
        Toast,
        ProgressBar,
    },
    data() {
        return {
            isSuperAdmin: false,
            hasPassword: false,
            currentPassword: '',
            newPassword: '',
            confirmNewPassword: '',
            passwordStrength: null,
            errors: {
                current: '',
                new: '',
                confirm: '',
            },
            loading: false,
        };
    },
    computed: {
        isFormValid() {
            return (
                this.newPassword &&
                this.confirmNewPassword &&
                (!this.hasPassword || this.currentPassword) &&
                this.newPassword === this.confirmNewPassword &&
                (!this.passwordStrength || this.passwordStrength.score >= 2)
            );
        },
    },
    setup() {
        const toast = useToast();
        return { toast };
    },
    async created() {
        await this.checkUserRole();
        if (this.isSuperAdmin) {
            await this.checkPasswordStatus();
        }
    },
    methods: {
        async checkUserRole() {
            try {
                const userRole = localStorage.getItem('myRole');
                this.isSuperAdmin =
                    userRole && userRole.toLowerCase() === 'superadmin';

                if (!this.isSuperAdmin) {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Accès refusé',
                        detail: 'Seuls les SuperAdmin peuvent accéder à cette page.',
                        life: 5000,
                    });
                }
            } catch (error) {
                console.error('Erreur dans checkUserRole:', error);
                this.isSuperAdmin = false;

                let errorMessage = 'Erreur lors de la vérification du rôle';
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: errorMessage,
                    life: 5000,
                });
            }
        },

        async checkPasswordStatus() {
            try {
                const response = await axios.get('api/delete-password/status');
                this.hasPassword = response.data.hasPassword;
            } catch (error) {
                console.error('Erreur dans checkPasswordStatus:', error);
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Impossible de vérifier le statut du mot de passe actuel.',
                    life: 3000,
                });
            }
        },

        validatePassword() {
            if (!this.newPassword) {
                this.passwordStrength = null;
                return;
            }

            const result = zxcvbn(this.newPassword);
            const strengthMap = [
                {
                    label: 'Très faible',
                    class: 'bg-red-500',
                    textClass: 'text-red-500',
                },
                {
                    label: 'Faible',
                    class: 'bg-orange-500',
                    textClass: 'text-orange-500',
                },
                {
                    label: 'Moyen',
                    class: 'bg-yellow-500',
                    textClass: 'text-yellow-500',
                },
                {
                    label: 'Fort',
                    class: 'bg-green-500',
                    textClass: 'text-green-500',
                },
                {
                    label: 'Très fort',
                    class: 'bg-teal-500',
                    textClass: 'text-teal-500',
                },
            ];

            this.passwordStrength = {
                score: result.score,
                label: strengthMap[result.score].label,
                class: strengthMap[result.score].class,
                textClass: strengthMap[result.score].textClass,
                warnings: result.feedback.warning
                    ? [result.feedback.warning]
                    : [],
                suggestions: result.feedback.suggestions || [],
            };

            if (this.newPassword.length < 8) {
                this.passwordStrength.warnings.push(
                    'Le mot de passe doit contenir au moins 8 caractères'
                );
            }
            if (!/[A-Z]/.test(this.newPassword)) {
                this.passwordStrength.warnings.push(
                    'Ajoutez une majuscule pour renforcer le mot de passe'
                );
            }
            if (!/[0-9]/.test(this.newPassword)) {
                this.passwordStrength.warnings.push(
                    'Ajoutez un chiffre pour renforcer le mot de passe'
                );
            }
            if (!/[^A-Za-z0-9]/.test(this.newPassword)) {
                this.passwordStrength.warnings.push(
                    'Ajoutez un caractère spécial pour renforcer le mot de passe'
                );
            }
        },

        async updatePassword() {
            this.errors = { current: '', new: '', confirm: '' };

            if (this.hasPassword && !this.currentPassword) {
                this.errors.current = 'Le mot de passe actuel est requis';
                return;
            }

            if (this.newPassword !== this.confirmNewPassword) {
                this.errors.confirm = 'Les mots de passe ne correspondent pas';
                return;
            }

            if (this.newPassword.length < 8) {
                this.errors.new =
                    'Le mot de passe doit contenir au moins 8 caractères';
                return;
            }

            if (this.passwordStrength && this.passwordStrength.score < 2) {
                this.errors.new =
                    'Le mot de passe est trop faible. Veuillez le renforcer.';
                return;
            }

            try {
                this.loading = true;
                const response = await axios.post('api/delete-password/set', {
                    current_password: this.currentPassword || null,
                    new_password: this.newPassword,
                    new_password_confirmation: this.confirmNewPassword,
                    has_password: this.hasPassword,
                });

                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Mot de passe mis à jour avec succès',
                    life: 3000,
                });

                this.currentPassword = '';
                this.newPassword = '';
                this.confirmNewPassword = '';
                this.passwordStrength = null;
                this.hasPassword = true;

                setTimeout(() => {
                    this.$router.push({ name: 'dashboard' });
                }, 1500);
            } catch (error) {
                console.error('Erreur dans updatePassword:', error);

                let errorMessage =
                    'Une erreur est survenue lors de la mise à jour';
                const errorData = error.response?.data;

                if (error.response?.status === 403) {
                    errorMessage = 'Accès non autorisé';
                } else if (error.response?.status === 422) {
                    if (errorData.errors?.current_password) {
                        this.errors.current =
                            errorData.errors.current_password[0];
                    }
                    if (errorData.errors?.new_password) {
                        this.errors.new = errorData.errors.new_password[0];
                    }
                } else if (errorData?.message) {
                    errorMessage = errorData.message;
                }

                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: errorMessage,
                    life: 5000,
                });
            } finally {
                this.loading = false;
            }
        },

        async deletePassword() {
            this.errors = { current: '', new: '', confirm: '' };

            if (!this.currentPassword) {
                this.errors.current =
                    'Le mot de passe actuel est requis pour supprimer';
                return;
            }

            try {
                this.loading = true;
                const response = await axios.delete('api/delete-password', {
                    data: { current_password: this.currentPassword },
                });

                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Mot de passe supprimé avec succès',
                    life: 3000,
                });

                this.currentPassword = '';
                this.newPassword = '';
                this.confirmNewPassword = '';
                this.passwordStrength = null;
                this.hasPassword = false;

                setTimeout(() => {
                    this.$router.push({ name: 'dashboard' });
                }, 1500);
            } catch (error) {
                console.error('Erreur dans deletePassword:', error);

                let errorMessage =
                    'Une erreur est survenue lors de la suppression';
                const errorData = error.response?.data;

                if (error.response?.status === 403) {
                    errorMessage = 'Accès non autorisé';
                } else if (error.response?.status === 404) {
                    errorMessage = 'Aucun mot de passe à supprimer';
                } else if (error.response?.status === 422) {
                    if (errorData.errors?.current_password) {
                        this.errors.current =
                            errorData.errors.current_password[0];
                    }
                } else if (errorData?.message) {
                    errorMessage = errorData.message;
                }

                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: errorMessage,
                    life: 5000,
                });
            } finally {
                this.loading = false;
            }
        },
    },
};
</script>

<style scoped>
.field {
    margin-bottom: 1.5rem;
}

.text-red-500 {
    color: var(--red-500);
}

:deep(.p-progressbar) {
    height: 0.375rem;
    background-color: #e9ecef;
}

:deep(.p-progressbar .p-progressbar-value) {
    transition: width 0.3s ease;
    background-color: var(--primary-color);
}

.pi-exclamation-circle {
    font-size: 0.875rem;
}

.pi-check-circle {
    font-size: 0.875rem;
}
</style>
