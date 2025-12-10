<template>
    <div
        class="flex flex-column className-items-center justify-content-center min-h-screen"
    >
        <Button
            label="Se déconnecter"
            class="p-3 text-xl"
            @click="logout"
            :loading="isLoading"
        />
    </div>
</template>

<script>
import axios from '@/axios.js';
import { useToast } from 'primevue/usetoast';

export default {
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            isLoading: false,
        };
    },
    methods: {
        async logout() {
            this.isLoading = true;
            try {
                await axios.post('/api/logout');
                localStorage.clear();
                this.$router.push({ name: 'login' });
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Déconnexion réussie',
                    life: 3000,
                });
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Échec de la déconnexion',
                    life: 3000,
                });
            } finally {
                this.isLoading = false;
            }
        },
    },
};
</script>
