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
                <span class="font-bold text-lg">Détails du Mémoire</span>
            </div>
        </template>

        <div v-if="memoire" class="flex flex-column gap-4">
            <div class="field">
                <label class="block font-medium mb-2">Référence</label>
                <InputText :value="memoire.reference" class="w-full" disabled />
            </div>
            <div class="field">
                <label class="block font-medium mb-2">Numéro Mémoire</label>
                <InputText
                    :value="memoire.numero_memoire || '-'"
                    class="w-full"
                    disabled
                />
            </div>
            <div class="field">
                <label class="block font-medium mb-2">Date Mémoire</label>
                <InputText
                    :value="formatDate(memoire.date_memoire)"
                    class="w-full"
                    disabled
                />
            </div>
            <div class="grid">
                <div class="col-12 md:col-6 field">
                    <label class="block font-medium mb-2">Date Début</label>
                    <InputText
                        :value="formatDate(memoire.date_debut)"
                        class="w-full"
                        disabled
                    />
                </div>
                <div class="col-12 md:col-6 field">
                    <label class="block font-medium mb-2">Date Fin</label>
                    <InputText
                        :value="formatDate(memoire.date_fin)"
                        class="w-full"
                        disabled
                    />
                </div>
            </div>
            <div class="field">
                <label class="block font-medium mb-2">Description</label>
                <Textarea
                    :value="memoire.description || '-'"
                    class="w-full"
                    rows="4"
                    disabled
                />
            </div>
            <div class="field">
                <label class="block font-medium mb-2">Observation</label>
                <Textarea
                    :value="memoire.observation || '-'"
                    class="w-full"
                    rows="4"
                    disabled
                />
            </div>
            <div class="field">
                <label class="block font-medium mb-2">Statut</label>
                <InputText
                    :value="memoire.statut || '-'"
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
        memoireId: { type: Number, required: true },
    },
    emits: ['update:visible', 'close'],
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            memoire: null,
            loading: false,
        };
    },
    watch: {
        visible(newVal) {
            if (newVal) {
                this.fetchMemoire();
            }
        },
    },
    methods: {
        async fetchMemoire() {
            this.loading = true;
            try {
                const response = await axios.get(
                    `/memoires-mg/${this.memoireId}`
                );
                this.memoire = response.data;
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Erreur lors du chargement des détails du mémoire',
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
