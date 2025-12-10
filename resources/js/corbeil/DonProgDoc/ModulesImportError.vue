<template>
    <Dialog
        :visible="visible"
        @update:visible="$emit('update:visible', $event)"
        :style="{ width: '70vw' }"
        header="Erreurs d'importation"
        :modal="true"
    >
        <div v-if="errors.length > 0">
            <p>Nombre total d'erreurs : {{ errors.length }}</p>
            <DataTable :value="errors" scrollable scrollHeight="400px">
                <Column
                    field="line"
                    header="Ligne"
                    style="min-width: 100px"
                ></Column>
                <Column field="data" header="Données" style="min-width: 300px">
                    <template #body="{ data }">
                        <InputText
                            v-model="data.data"
                            type="text"
                            placeholder="Modifier les données"
                            style="width: 100%"
                        />
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
import Dialog from 'primevue/dialog';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import axios from 'axios';

export default {
    components: {
        Dialog,
        DataTable,
        Column,
        InputText,
        Button,
    },
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
    methods: {
        async retryImport(error) {
            try {
                const correctedData = error.data
                    .split(',')
                    .map((item) => item.trim());
                const payload = {
                    programme_id: error.programme_id, // Utilise programme_id spécifique à l’erreur
                    code: correctedData[0],
                    titre: correctedData[1],
                    type: correctedData[2],
                    heures_theoriques: correctedData[3]
                        ? parseInt(correctedData[3])
                        : null,
                    heures_pratiques: correctedData[4]
                        ? parseInt(correctedData[4])
                        : null,
                    heures_evaluation: correctedData[5]
                        ? parseInt(correctedData[5])
                        : null,
                    contenu_pdf: correctedData[6] || null,
                    observation: correctedData[7] || null,
                };

                if (!payload.code) {
                    throw new Error("Le champ 'code' est requis.");
                }
                if (
                    ![
                        'Enseignement spécifique',
                        'Enseignement général',
                    ].includes(payload.type)
                ) {
                    throw new Error(
                        "Le champ 'type' doit être 'Enseignement spécifique' ou 'Enseignement général'."
                    );
                }

                const response = await axios.post(
                    '/api/modules/import-row',
                    payload
                );

                this.$emit('line-imported', error);
                this.$toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Ligne importée avec succès.',
                    life: 3000,
                });
            } catch (error) {
                console.error('Retry import error:', error.response || error);
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        'Échec de l’importation de la ligne corrigée.',
                    life: 5000,
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
    margin-bottom: 8px;
}
</style>
