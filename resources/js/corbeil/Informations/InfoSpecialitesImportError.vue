<template>
    <Dialog
        :visible="visible"
        @update:visible="$emit('update:visible', $event)"
        :style="{ width: '70vw' }"
        header="Erreurs d'importation des informations de spécialités"
        :modal="true"
    >
        <div v-if="errors.length > 0">
            <p>Nombre total d'erreurs : {{ errors.length }}</p>
            <DataTable :value="errors" scrollable scrollHeight="400px">
                <!-- Colonne Ligne -->
                <Column
                    field="line"
                    header="Ligne"
                    style="min-width: 100px"
                ></Column>

                <!-- Colonne Données -->
                <Column field="data" header="Données" style="min-width: 300px">
                    <template #body="{ data }">
                        <pre>{{ JSON.stringify(data.data, null, 2) }}</pre>
                    </template>
                </Column>

                <!-- Colonne Message d'erreur -->
                <Column
                    field="message"
                    header="Message d'erreur"
                    style="min-width: 300px"
                ></Column>

                <!-- Colonne Actions -->
                <Column header="Actions" style="min-width: 150px">
                    <template #body="{ data }">
                        <Button
                            icon="pi pi-check"
                            class="p-button-rounded p-button-success mr-2"
                            @click="retryImport(data)"
                        />
                    </template>
                </Column>
            </DataTable>
        </div>
        <div v-else>
            <p>Aucune erreur détectée.</p>
        </div>
        <template #footer>
            <Button label="Fermer" icon="pi pi-times" @click="closePopup" />
        </template>
    </Dialog>
</template>

<script>
import axios from 'axios';
import { useToast } from 'primevue/usetoast';

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
    },
    emits: ['update:visible', 'line-imported', 'close'],
    setup() {
        const toast = useToast();
        return { toast };
    },
    methods: {
        async retryImport(error) {
            try {
                // Récupérer les données corrigées
                const correctedData = error.data;

                // Vérifier si la spécialité existe
                const specialiteResponse = await axios.get(
                    `/api/specialites/code/${correctedData.specialite_code}`
                );
                const specialiteId = specialiteResponse.data.id;

                // Vérifier si l'année existe
                const anneeResponse = await axios.get(
                    `/api/annees_formation/intitule/${correctedData.annee_intitule}`
                );
                const anneeId = anneeResponse.data.id;

                // Envoyer les données corrigées au backend
                const response = await axios.post(
                    '/api/informations_specialites',
                    {
                        specialite_id: specialiteId,
                        annee_id: anneeId,
                        homologation: correctedData.homologation,
                        heures_techniques: correctedData.heures_techniques,
                        heures_generaux: correctedData.heures_generaux,
                        heures_totales: correctedData.heures_totales,
                        duree_formation: correctedData.duree_formation,
                        observation: correctedData.observation,
                    }
                );

                // Émettre un événement pour informer le parent que la ligne a été importée avec succès
                this.$emit('line-imported', error);

                // Afficher un message de succès
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'La ligne a été importée avec succès.',
                    life: 3000,
                });
            } catch (error) {
                // Afficher un message d'erreur en cas d'échec
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: "Échec de l'importation de la ligne.",
                    life: 3000,
                });
            }
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
    background-color: #f5f5f5;
    padding: 8px;
    border-radius: 4px;
}
</style>
