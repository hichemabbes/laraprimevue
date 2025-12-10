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
                <span class="font-bold text-lg">Détails du Programme</span>
            </div>
        </template>

        <div v-if="programme" class="flex flex-column gap-4">
            <div class="field">
                <label class="block font-medium mb-2">Version</label>
                <InputText :value="programme.version" class="w-full" disabled />
            </div>
            <div class="field">
                <label class="block font-medium mb-2">Date Début</label>
                <InputText
                    :value="formatDate(programme.date_debut)"
                    class="w-full"
                    disabled
                />
            </div>
            <div class="field">
                <label class="block font-medium mb-2">Date Fin</label>
                <InputText
                    :value="formatDate(programme.date_fin)"
                    class="w-full"
                    disabled
                />
            </div>
            <div class="field">
                <label class="block font-medium mb-2">Description</label>
                <Textarea
                    :value="programme.description || '-'"
                    class="w-full"
                    rows="4"
                    disabled
                />
            </div>
            <div class="field">
                <label class="block font-medium mb-2">Observation</label>
                <Textarea
                    :value="programme.observation || '-'"
                    class="w-full"
                    rows="4"
                    disabled
                />
            </div>
            <div class="field">
                <label class="block font-medium mb-2">Statut</label>
                <InputText
                    :value="programme.statut || '-'"
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
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';

export default {
    components: {
        InputText,
        Textarea,
        Button,
        Dialog,
    },
    props: {
        visible: { type: Boolean, required: true },
        programmeId: { type: Number, required: true },
    },
    emits: ['update:visible', 'close'],
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            programme: null,
            loading: false,
        };
    },
    watch: {
        visible(newVal) {
            if (newVal) {
                this.fetchProgramme();
            }
        },
    },
    methods: {
        async fetchProgramme() {
            this.loading = true;
            try {
                const response = await axios.get(
                    `/programmes/${this.programmeId}`
                );
                this.programme = response.data;
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Erreur lors du chargement des détails du programme',
                    life: 5000,
                });
            } finally {
                this.loading = false;
            }
        },
        formatDate(date) {
            if (!date) return '-';
            const d = new Date(date);
            return d.toLocaleDateString('fr-FR');
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

.p-inputtext,
.p-inputtextarea {
    width: 100%;
}
</style>
