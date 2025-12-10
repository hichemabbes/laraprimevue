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
                <span class="font-bold text-lg">Détails du Module</span>
            </div>
        </template>

        <div v-if="module" class="flex flex-column gap-4">
            <div class="field">
                <label class="block font-medium mb-2">Code</label>
                <InputText :value="module.code" class="w-full" disabled />
            </div>
            <div class="field">
                <label class="block font-medium mb-2">Titre Module</label>
                <InputText
                    :value="module.titre_module"
                    class="w-full"
                    disabled
                />
            </div>
            <div class="field">
                <label class="block font-medium mb-2">Type Module</label>
                <InputText
                    :value="module.type_module || '-'"
                    class="w-full"
                    disabled
                />
            </div>
            <div class="grid">
                <div class="col-12 md:col-4 field">
                    <label class="block font-medium mb-2">H. Théoriques</label>
                    <InputText
                        :value="module.heures_theoriques || 0"
                        class="w-full"
                        disabled
                    />
                </div>
                <div class="col-12 md:col-4 field">
                    <label class="block font-medium mb-2">H. Pratiques</label>
                    <InputText
                        :value="module.heures_pratiques || 0"
                        class="w-full"
                        disabled
                    />
                </div>
                <div class="col-12 md:col-4 field">
                    <label class="block font-medium mb-2">H. Évaluation</label>
                    <InputText
                        :value="module.heures_evaluation || 0"
                        class="w-full"
                        disabled
                    />
                </div>
            </div>
            <div class="field">
                <label class="block font-medium mb-2">Heures Totales</label>
                <InputText
                    :value="calculateTotalHours(module)"
                    class="w-full"
                    disabled
                />
            </div>
            <div class="field">
                <label class="block font-medium mb-2">Description</label>
                <Textarea
                    :value="module.description || '-'"
                    class="w-full"
                    rows="4"
                    disabled
                />
            </div>
            <div class="field">
                <label class="block font-medium mb-2">Observation</label>
                <Textarea
                    :value="module.observation || '-'"
                    class="w-full"
                    rows="4"
                    disabled
                />
            </div>
            <div class="field">
                <label class="block font-medium mb-2">Statut</label>
                <InputText
                    :value="module.statut || '-'"
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
        moduleId: { type: Number, required: true },
    },
    emits: ['update:visible', 'close'],
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            module: null,
            loading: false,
        };
    },
    watch: {
        visible(newVal) {
            if (newVal) {
                this.fetchModule();
            }
        },
    },
    methods: {
        async fetchModule() {
            this.loading = true;
            try {
                const response = await axios.get(`/modules/${this.moduleId}`);
                this.module = response.data;
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Erreur lors du chargement des détails du module',
                    life: 5000,
                });
            } finally {
                this.loading = false;
            }
        },
        calculateTotalHours(module) {
            return (
                (module.heures_theoriques || 0) +
                (module.heures_pratiques || 0) +
                (module.heures_evaluation || 0)
            );
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
