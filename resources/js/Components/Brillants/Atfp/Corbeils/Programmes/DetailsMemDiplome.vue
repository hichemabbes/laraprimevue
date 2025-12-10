<template>
    <Dialog
        :visible="visible"
        @update:visible="$emit('update:visible', $event)"
        :modal="true"
        :style="{ width: '600px' }"
        :breakpoints="{ '960px': '75vw', '640px': '90vw' }"
        :pt="{
            root: 'border-none',
            mask: { style: 'backdrop-filter: blur(2px)' },
            header: { class: 'surface-50 border-bottom-1 surface-border' },
            content: { class: 'surface-50 py-0' },
            footer: { class: 'surface-50 border-top-1 surface-border' },
        }"
    >
        <template #header>
            <div class="flex align-items-center gap-2">
                <i class="pi pi-eye text-primary"></i>
                <span class="font-bold text-lg">Détails du Diplôme</span>
            </div>
        </template>

        <div v-if="diplome" class="flex flex-column gap-4">
            <div class="field">
                <label class="block font-medium mb-2">Diplôme</label>
                <InputText :value="diplome.nom_fr" class="w-full" disabled />
            </div>
            <div class="field">
                <label class="block font-medium mb-2">Nombre de Mémoires</label>
                <InputText
                    :value="diplome.nombre_memoires || 0"
                    class="w-full"
                    disabled
                />
            </div>
        </div>
        <div v-else class="text-center py-4">
            <i class="pi pi-spin pi-spinner text-4xl text-400 mb-2" />
            <p class="text-600">Chargement...</p>
        </div>

        <template #footer>
            <Button
                label="Fermer"
                icon="pi pi-times"
                class="p-button-text"
                @click="$emit('close')"
            />
        </template>
    </Dialog>
</template>

<script>
import { useToast } from 'primevue/usetoast';
import axios from 'axios';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';

export default {
    components: {
        InputText,
        Button,
        Dialog,
    },
    props: {
        visible: { type: Boolean, required: true },
        diplomeId: { type: Number, required: true },
    },
    emits: ['update:visible', 'close'],
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            diplome: null,
            loading: false,
        };
    },
    watch: {
        visible(newVal) {
            if (newVal) {
                this.fetchDiplome();
            }
        },
    },
    methods: {
        async fetchDiplome() {
            this.loading = true;
            try {
                const response = await axios.get(`/diplomes/${this.diplomeId}`);
                this.diplome = response.data;
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Erreur lors du chargement des détails du diplôme',
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
    margin-bottom: 1rem;
}

.p-dialog .p-dialog-content {
    padding: 1.5rem;
}

.p-inputtext {
    width: 100%;
}
</style>
