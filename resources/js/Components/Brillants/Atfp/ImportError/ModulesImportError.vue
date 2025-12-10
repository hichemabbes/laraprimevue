<template>
    <Dialog
        :visible="visible"
        @update:visible="$emit('update:visible', $event)"
        :style="{ width: '70vw' }"
        header="Erreurs d'importation des modules"
        :modal="true"
        :pt="{
            root: { class: 'border-round shadow-4' },
            header: { class: 'surface-50 border-bottom-1 surface-border p-3' },
            content: { class: 'p-3' },
            footer: { class: 'surface-50 border-top-1 surface-border p-3' },
        }"
    >
        <div v-if="errors.length > 0">
            <p>Nombre total d'erreurs : {{ errors.length }}</p>
            <DataTable
                :value="errors"
                scrollable
                scrollHeight="400px"
                size="small"
                stripedRows
            >
                <Column
                    field="line"
                    header="Ligne"
                    style="min-width: 100px"
                ></Column>
                <Column field="data" header="Données" style="min-width: 300px">
                    <template #body="{ data }">
                        <pre>{{ JSON.stringify(data.data, null, 2) }}</pre>
                    </template>
                </Column>
                <Column
                    field="message"
                    header="Message d'erreur"
                    style="min-width: 300px"
                ></Column>
                <Column header="Actions" style="min-width: 150px">
                    <template #body="{ data }">
                        <Button
                            icon="pi pi-check"
                            class="p-button-rounded p-button-success p-button-sm mr-2"
                            @click="retryImport(data)"
                            v-tooltip="'Réessayer l\'importation'"
                        />
                    </template>
                </Column>
            </DataTable>
        </div>
        <div v-else>
            <p>Aucune erreur détectée.</p>
        </div>
        <template #footer>
            <Button
                label="Fermer"
                icon="pi pi-times"
                class="p-button-text"
                @click="closePopup"
            />
        </template>
    </Dialog>
</template>

<script>
import { useToast } from 'primevue/usetoast';
import axios from 'axios';

export default {
    props: {
        errors: {
            type: Array,
            required: true,
        },
        visible: {
            type: Boolean,
            required: true,
        },
        programmeId: {
            type: [Number, String],
            required: true,
        },
    },
    emits: ['update:visible', 'line-imported', 'close'],
    setup() {
        const toast = useToast();
        return { toast };
    },
    methods: {
        async retryImport(error) {
            try {
                const data = error.data; // Les données sont un tableau provenant de l'Excel

                // Normalisation des données
                const type_module_fr = data[2] ? trim(data[2]) : null;
                const normalized_type_module_fr = type_module_fr ? ucfirst(strtolower(type_module_fr)) : null;

                console.log('Retry import with type_module_fr:', {
                    raw: data[2],
                    normalized: normalized_type_module_fr,
                });

                const response = await axios.post('/api/modules', {
                    code: data[0] ? trim(data[0]) : null,
                    titre_module: data[1] ? trim(data[1]) : null,
                    type_module_fr: normalized_type_module_fr,
                    type_module_ar: normalized_type_module_fr ? this.mapTypeModuleAr(normalized_type_module_fr) : null,
                    heures_theoriques: data[3] ? parseInt(trim(data[3])) : null,
                    heures_pratiques: data[4] ? parseInt(trim(data[4])) : null,
                    heures_evaluation: data[5] ? parseInt(trim(data[5])) : null,
                    programme_id: this.programmeId,
                    statut: data[6] ? trim(data[6]) : 'Actif',
                    description: data[7] ? trim(data[7]) : null,
                    observation: data[8] ? trim(data[8]) : null,
                });

                this.$emit('line-imported', error);
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'La ligne a été importée avec succès.',
                    life: 3000,
                });
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        "Échec de l'importation de la ligne.",
                    life: 5000,
                });
            }
        },
        mapTypeModuleAr(type_module_fr) {
            const mapping = {
                Spécifique: 'تقني',
                Général: 'عام',
                Stage: 'تربص',
            };
            return mapping[type_module_fr] || type_module_fr;
        },
        closePopup() {
            this.$emit('close');
            this.$emit('update:visible', false);
        },
    },
};
</script>

<style scoped>
pre {
    white-space: pre-wrap;
    word-wrap: break-word;
}
</style>
